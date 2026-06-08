<?php

namespace App\Http\Controllers;

use App\Models\Cerita;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AnalyticsController extends Controller
{
  public function index(Request $request)
  {
    $sortBy = in_array($request->sort_by, ['total_read', 'total_vote', 'total_share', 'engagement'])
      ? $request->sort_by
      : 'total_read';
    $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
    $year = $request->year;
    $month = $request->month;
    $week = $request->week;

    // Tahun yang tersedia di database
    $availableYears = Cerita::selectRaw('EXTRACT(YEAR FROM created_at)::integer as yr')
      ->whereNotNull('created_at')
      ->distinct()
      ->orderByDesc('yr')
      ->pluck('yr');

    // Base query dengan filter waktu
    $base = Cerita::query();
    if ($year) {
      $base->whereYear('created_at', $year);
    }
    if ($month) {
      $base->whereMonth('created_at', $month);
    }
    if ($week) {
      $base->whereRaw('EXTRACT(WEEK FROM created_at)::integer = ?', [(int) $week]);
    }

    $cols = ['id', 'judul', 'total_read', 'total_vote', 'total_share', 'parts', 'status', 'id_kategori'];

    // Top 10 by sort field
    $topRead = (clone $base)->with('kategori')->orderBy('total_read', $sortDir)->limit(10)->get($cols);
    $topVote = (clone $base)->with('kategori')->orderBy('total_vote', $sortDir)->limit(10)->get($cols);

    // Detail Cerita (paginate 10)
    $perPage = 10;
    $currentPage = (int) ($request->page ?? 1);

    if ($sortBy === 'engagement') {
      $all = (clone $base)
        ->get($cols)
        ->sortBy(
          function ($c) {
            return $c->total_read > 0 ? $c->total_vote / $c->total_read : 0;
          },
          SORT_REGULAR,
          $sortDir === 'desc'
        )
        ->values();

      $detailCerita = new LengthAwarePaginator(
        $all->slice(($currentPage - 1) * $perPage, $perPage)->values(),
        $all->count(),
        $perPage,
        $currentPage,
        ['path' => $request->url(), 'query' => $request->query()]
      );
    } else {
      $detailCerita = (clone $base)->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();
    }

    // Distribusi kategori (berdasarkan filter)
    $filteredIds = (clone $base)->pluck('id');
    $byKategori = Kategori::with([
      'ceritas' => fn($q) => $q
        ->whereIn('id', $filteredIds)
        ->select('id', 'judul', 'total_read', 'total_vote', 'parts', 'status', 'id_kategori'),
    ])
      ->withCount(['ceritas' => fn($q) => $q->whereIn('id', $filteredIds)])
      ->get(['default_title', 'id'])
      ->filter(fn($k) => $k->ceritas_count > 0)
      ->values();

    // Status
    $ceritaAktif = (clone $base)->where('status', true)->get(['id', 'judul', 'total_read', 'total_vote', 'parts']);
    $ceritaNonaktif = (clone $base)->where('status', false)->get(['id', 'judul', 'total_read', 'total_vote', 'parts']);

    // Rekomendasi
    $ceritaRekomen = (clone $base)
      ->where('recomendation', true)
      ->get(['id', 'judul', 'total_read', 'total_vote', 'parts']);
    $ceritaNonRekomen = (clone $base)
      ->where('recomendation', false)
      ->get(['id', 'judul', 'total_read', 'total_vote', 'parts']);

    // Summary berdasarkan filter
    $summary = [
      'total_cerita' => (clone $base)->count(),
      'total_read' => (clone $base)->sum('total_read'),
      'total_vote' => (clone $base)->sum('total_vote'),
      'total_share' => (clone $base)->sum('total_share'),
      'avg_parts' => round((clone $base)->avg('parts') ?? 0, 1),
      'wajib_dibaca' => (clone $base)->where('wajib_dibaca', true)->count(),
    ];

    return view(
      'content.analytics.index',
      compact(
        'topRead',
        'topVote',
        'detailCerita',
        'byKategori',
        'ceritaAktif',
        'ceritaNonaktif',
        'ceritaRekomen',
        'ceritaNonRekomen',
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
}
