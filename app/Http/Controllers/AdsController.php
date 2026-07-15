<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Cerita;
use App\Models\CeritaAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdsController extends Controller
{
    private const MAX_UNSIGNED_INTEGER = 4294967295;

    public function index(Request $request)
    {
        $query = Ad::withCount('placements')->latest();

        if ($request->filled('media_type')) {
            $query->where('media_type', $request->media_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }

        $ads = $query->paginate(10)->withQueryString();

        return view('content.ads.index', compact('ads'));
    }

    public function create()
    {
        $ceritas = $this->getPlacementCeritas();

        return view('content.ads.create', compact('ceritas'));
    }

    public function store(Request $request)
    {
        $mediaFileRule = $request->input('media_type') === 'video'
            ? 'nullable|required_without:media_url|file|mimes:mp4,webm,mov|max:20480'
            : 'nullable|required_without:media_url|file|mimes:jpg,jpeg,png,webp,gif|max:5120';

        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'media_type'        => 'required|in:image,video',
            'media_file'        => $mediaFileRule,
            'media_url'         => 'nullable|required_without:media_file|url|max:2048',
            'target_url'        => 'nullable|url|max:2048',
            'status'            => 'required|boolean',
            'placements'        => 'nullable|array',
            'placements.*'      => 'nullable|array',
            'placements.*.*'    => 'nullable|array',
            'placements.*.*.*'  => 'integer|min:1|max:' . self::MAX_UNSIGNED_INTEGER,
            'placement_global'  => 'nullable|array',
        ], [
            'placements.*.*.*.max' => 'Maaf, angka yang dimasukkan terlalu besar.',
        ]);

        if ($request->hasFile('media_file')) {
            $data['media_path'] = $request->file('media_file')->store('ads', 'public');
            $data['media_url'] = Storage::url($data['media_path']);
        }

        unset($data['media_file'], $data['placements'], $data['placement_global']);
        $data['status'] = $request->boolean('status');

        $ad = Ad::create($data);
        $this->syncPlacements($ad, $request);

        return redirect()->route('ads.index')->with('success', 'Ads berhasil ditambahkan.');
    }

    public function show(Ad $ad)
    {
        $ad->load(['placements.cerita']);

        return view('content.ads.show', compact('ad'));
    }

    public function edit(Ad $ad)
    {
        $ad->load('placements');
        $ceritas = $this->getPlacementCeritas();

        return view('content.ads.edit', compact('ad', 'ceritas'));
    }

    public function update(Request $request, Ad $ad)
    {
        $mediaUrlRule = $ad->media_url || $ad->media_path
            ? 'nullable|url|max:2048'
            : 'nullable|required_without:media_file|url|max:2048';
        $mediaFileRule = $request->input('media_type') === 'video'
            ? 'nullable|file|mimes:mp4,webm,mov|max:20480'
            : 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120';

        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'media_type'        => 'required|in:image,video',
            'media_file'        => $mediaFileRule,
            'media_url'         => $mediaUrlRule,
            'target_url'        => 'nullable|url|max:2048',
            'status'            => 'required|boolean',
            'placements'        => 'nullable|array',
            'placements.*'      => 'nullable|array',
            'placements.*.*'    => 'nullable|array',
            'placements.*.*.*'  => 'integer|min:1|max:' . self::MAX_UNSIGNED_INTEGER,
            'placement_global'  => 'nullable|array',
        ], [
            'placements.*.*.*.max' => 'Maaf, angka yang dimasukkan terlalu besar.',
        ]);

        if ($request->hasFile('media_file')) {
            if ($ad->media_path) {
                Storage::disk('public')->delete($ad->media_path);
            }

            $data['media_path'] = $request->file('media_file')->store('ads', 'public');
            $data['media_url'] = Storage::url($data['media_path']);
        } elseif ($request->filled('media_url')) {
            if ($ad->media_path) {
                Storage::disk('public')->delete($ad->media_path);
            }

            $data['media_path'] = null;
        } else {
            unset($data['media_url']);
        }

        unset($data['media_file'], $data['placements'], $data['placement_global']);
        $data['status'] = $request->boolean('status');

        $ad->update($data);
        $this->syncPlacements($ad, $request);

        return redirect()->route('ads.index')->with('success', 'Ads berhasil diperbarui.');
    }

    public function destroy(Ad $ad)
    {
        if ($ad->media_path) {
            Storage::disk('public')->delete($ad->media_path);
        }

        $ad->delete();

        return redirect()->route('ads.index')->with('success', 'Ads berhasil dihapus.');
    }

    private function getPlacementCeritas()
    {
        return Cerita::orderBy('judul')->get(['id', 'judul', 'parts', 'isi_cerita']);
    }

    private function syncPlacements(Ad $ad, Request $request): void
    {
        $ad->placements()->delete();

        $ceritaIds = collect($request->input('placements', []))
            ->keys()
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values();

        if ($ceritaIds->isEmpty()) {
            return;
        }

        $ceritas = Cerita::whereIn('id', $ceritaIds)->get(['id', 'parts', 'isi_cerita'])->keyBy('id');
        $placements = [];
        $now = now();

        $globalFlags = $request->input('placement_global', []);

        foreach ($request->input('placements', []) as $ceritaId => $positions) {
            $cerita = $ceritas->get((int) $ceritaId);

            if (!$cerita || !is_array($positions)) {
                continue;
            }

            $chapterTotal = max((int) $cerita->parts, count($cerita->isi_cerita ?? []));

            foreach ($positions as $position => $chapters) {
                if (!in_array($position, ['before', 'after'], true) || !is_array($chapters)) {
                    continue;
                }

                foreach (array_unique($chapters) as $chapter) {
                    $chapterNumber = (int) $chapter;

                    if ($chapterNumber < 1 || $chapterNumber > $chapterTotal) {
                        continue;
                    }

                    $placements[] = [
                        'cerita_id'           => $cerita->id,
                        'ad_id'               => $ad->id,
                        'after_chapter'       => $chapterNumber,
                        'placement_position'  => $position,
                        'is_global'           => isset($globalFlags[$cerita->id][$position][$chapterNumber]),
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ];
                }
            }
        }

        if ($placements) {
            CeritaAd::insert($placements);
        }
    }
}
