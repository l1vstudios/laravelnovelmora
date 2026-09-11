<?php

namespace App\Http\Controllers;

use App\Models\Cerita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    private const DETAIL_LIMIT = 50;

    private const STORY_COLUMNS = [
        'id',
        'judul',
        'total_read',
        'total_vote',
        'total_share',
        'parts',
        'status',
        'id_kategori',
    ];

    public function index(Request $request)
    {
        $requestedSort = $request->query('sort', $request->query('sort_by'));
        $sortBy = in_array($requestedSort, ['judul', 'parts', 'status', 'total_read', 'total_vote', 'total_share', 'engagement'])
          ? $requestedSort
          : 'total_read';
        $requestedDirection = $request->query('direction', $request->query('sort_dir'));
        $sortDir = $requestedDirection === 'asc' ? 'asc' : 'desc';
        $year = $this->integerInRange($request->query('year'), 1, 9999);
        $month = $this->integerInRange($request->query('month'), 1, 12);
        $week = $this->integerInRange($request->query('week'), 1, 53);

        // Tahun yang tersedia di database
        $availableYears = Cerita::selectRaw('EXTRACT(YEAR FROM created_at)::integer as yr')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr');

        // Base query dengan filter waktu
        $base = Cerita::query();
        $this->applyDateFilters($base, $year, $month, $week);

        $topRead = (clone $base)->orderBy('total_read', $sortDir)->limit(10)->get(self::STORY_COLUMNS);
        $topVote = (clone $base)->orderBy('total_vote', $sortDir)->limit(10)->get(self::STORY_COLUMNS);

        // Detail Cerita (paginate 10)
        $perPage = 10;
        $detailQuery = (clone $base)->select(self::STORY_COLUMNS);
        if ($sortBy === 'engagement') {
            $detailQuery->orderByRaw($this->engagementExpression().' '.$sortDir);
        } else {
            $detailQuery->orderBy($sortBy, $sortDir);
        }

        $detailCerita = $detailQuery->paginate($perPage)->withQueryString();

        // Distribusi kategori cukup membawa jumlah; daftar detail diambil saat chart diklik.
        $byKategori = (clone $base)
            ->join('mst_kategori', 'mst_cerita.id_kategori', '=', 'mst_kategori.id')
            ->selectRaw('mst_kategori.id, mst_kategori.default_title, COUNT(*) as total')
            ->groupBy('mst_kategori.id', 'mst_kategori.default_title')
            ->orderByDesc('total')
            ->get();

        $aggregates = (clone $base)
            ->selectRaw('
        COUNT(*) as total_cerita,
        COALESCE(SUM(total_read), 0) as total_read,
        COALESCE(SUM(total_vote), 0) as total_vote,
        COALESCE(SUM(total_share), 0) as total_share,
        COALESCE(AVG(parts), 0) as avg_parts,
        COALESCE(SUM(CASE WHEN wajib_dibaca = true THEN 1 ELSE 0 END), 0) as wajib_dibaca,
        COALESCE(SUM(CASE WHEN status = true THEN 1 ELSE 0 END), 0) as cerita_aktif,
        COALESCE(SUM(CASE WHEN status = false THEN 1 ELSE 0 END), 0) as cerita_nonaktif,
        COALESCE(SUM(CASE WHEN recomendation = true THEN 1 ELSE 0 END), 0) as cerita_rekomen,
        COALESCE(SUM(CASE WHEN recomendation = false THEN 1 ELSE 0 END), 0) as cerita_non_rekomen
      ')
            ->first();

        // Summary berdasarkan filter
        $summary = [
            'total_cerita' => (int) $aggregates->total_cerita,
            'total_read' => (int) $aggregates->total_read,
            'total_vote' => (int) $aggregates->total_vote,
            'total_share' => (int) $aggregates->total_share,
            'avg_parts' => round((float) $aggregates->avg_parts, 1),
            'wajib_dibaca' => (int) $aggregates->wajib_dibaca,
        ];

        $statusGroups = collect([
            ['label' => 'Aktif', 'total' => (int) $aggregates->cerita_aktif, 'group' => 'status', 'value' => 1],
            ['label' => 'Nonaktif', 'total' => (int) $aggregates->cerita_nonaktif, 'group' => 'status', 'value' => 0],
        ]);
        $rekomenGroups = collect([
            ['label' => 'Rekomendasi', 'total' => (int) $aggregates->cerita_rekomen, 'group' => 'recommendation', 'value' => 1],
            ['label' => 'Tidak', 'total' => (int) $aggregates->cerita_non_rekomen, 'group' => 'recommendation', 'value' => 0],
        ]);

        return view(
            'content.analytics.index',
            compact(
                'topRead',
                'topVote',
                'detailCerita',
                'byKategori',
                'statusGroups',
                'rekomenGroups',
                'summary',
                'availableYears',
                'sortBy',
                'sortDir',
                'year',
                'month',
                'week'
            )
        );
    }

    public function details(Request $request): JsonResponse
    {
        $year = $this->integerInRange($request->query('year'), 1, 9999);
        $month = $this->integerInRange($request->query('month'), 1, 12);
        $week = $this->integerInRange($request->query('week'), 1, 53);
        $group = $request->query('group');
        $value = $request->query('value');

        $query = Cerita::query();
        $this->applyDateFilters($query, $year, $month, $week);

        if ($group === 'category') {
            $categoryId = $this->integerInRange($value, 1, PHP_INT_MAX);
            if ($categoryId === null) {
                return response()->json(['message' => 'Kategori tidak valid.'], 422);
            }

            $query->where('id_kategori', $categoryId);
        } elseif ($group === 'status') {
            $status = $this->integerInRange($value, 0, 1);
            if ($status === null) {
                return response()->json(['message' => 'Status tidak valid.'], 422);
            }

            $query->where('status', (bool) $status);
        } elseif ($group === 'recommendation') {
            $recommendation = $this->integerInRange($value, 0, 1);
            if ($recommendation === null) {
                return response()->json(['message' => 'Rekomendasi tidak valid.'], 422);
            }

            $query->where('recomendation', (bool) $recommendation);
        } else {
            return response()->json(['message' => 'Grup analitik tidak valid.'], 422);
        }

        $total = (clone $query)->count();
        $ceritas = $query
            ->orderByDesc('total_read')
            ->limit(self::DETAIL_LIMIT)
            ->get(self::STORY_COLUMNS)
            ->map(fn ($cerita) => [
                'id' => $cerita->id,
                'judul' => $cerita->judul,
                'parts' => (int) $cerita->parts,
                'total_read' => (int) $cerita->total_read,
                'total_vote' => (int) $cerita->total_vote,
                'total_share' => (int) $cerita->total_share,
            ]);

        return response()->json([
            'total' => $total,
            'limit' => self::DETAIL_LIMIT,
            'truncated' => $total > self::DETAIL_LIMIT,
            'ceritas' => $ceritas,
        ]);
    }

    private function applyDateFilters($query, ?int $year, ?int $month, ?int $week): void
    {
        if ($year) {
            $query->whereYear('created_at', $year);
        }
        if ($month) {
            $query->whereMonth('created_at', $month);
        }
        if ($week) {
            $query->whereRaw('EXTRACT(WEEK FROM created_at)::integer = ?', [$week]);
        }
    }

    private function integerInRange(mixed $value, int $min, int $max): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = filter_var($value, FILTER_VALIDATE_INT);

        return $value !== false && $value >= $min && $value <= $max ? $value : null;
    }

    private function engagementExpression(): string
    {
        return 'CASE WHEN total_read > 0 THEN total_vote * 1.0 / total_read ELSE 0 END';
    }
}
