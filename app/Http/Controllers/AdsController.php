<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;

class AdsController extends Controller
{
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
        return view('content.ads.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'media_type' => 'required|in:image,video',
            'media_url'  => 'required|url|max:2048',
            'target_url' => 'nullable|url|max:2048',
            'status'     => 'required|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        Ad::create($data);

        return redirect()->route('ads.index')->with('success', 'Ads berhasil ditambahkan.');
    }

    public function show(Ad $ad)
    {
        $ad->load(['placements.cerita']);

        return view('content.ads.show', compact('ad'));
    }

    public function edit(Ad $ad)
    {
        return view('content.ads.edit', compact('ad'));
    }

    public function update(Request $request, Ad $ad)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'media_type' => 'required|in:image,video',
            'media_url'  => 'required|url|max:2048',
            'target_url' => 'nullable|url|max:2048',
            'status'     => 'required|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        $ad->update($data);

        return redirect()->route('ads.index')->with('success', 'Ads berhasil diperbarui.');
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();

        return redirect()->route('ads.index')->with('success', 'Ads berhasil dihapus.');
    }
}
