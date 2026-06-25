<?php

namespace App\Http\Controllers;

use App\Models\RewardVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RewardVideoController extends Controller
{
    public function index(Request $request)
    {
        $query = RewardVideo::withCount('schedules')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }

        $rewardVideos = $query->paginate(10)->withQueryString();

        return view('content.reward-videos.index', compact('rewardVideos'));
    }

    public function create()
    {
        return view('content.reward-videos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'video_file' => 'nullable|required_without:video_url|file|mimes:mp4,webm,mov|max:51200',
            'video_url'  => 'nullable|required_without:video_file|url|max:2048',
            'status'     => 'required|boolean',
        ]);

        if ($request->hasFile('video_file')) {
            $data['video_path'] = $request->file('video_file')->store('reward-videos', 'public');
            $data['video_url'] = Storage::url($data['video_path']);
        }

        $rewardVideo = RewardVideo::create($data);

        return redirect()->route('reward-videos.index')->with('success', 'Video reward berhasil ditambahkan.');
    }

    public function show(RewardVideo $rewardVideo)
    {
        $rewardVideo->load('schedules.dailyReward');

        return view('content.reward-videos.show', compact('rewardVideo'));
    }

    public function edit(RewardVideo $rewardVideo)
    {
        return view('content.reward-videos.edit', compact('rewardVideo'));
    }

    public function update(Request $request, RewardVideo $rewardVideo)
    {
        $videoUrlRule = $rewardVideo->video_url || $rewardVideo->video_path
            ? 'nullable|url|max:2048'
            : 'nullable|required_without:video_file|url|max:2048';

        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'video_file' => 'nullable|file|mimes:mp4,webm,mov|max:20480',
            'video_url'  => $videoUrlRule,
            'status'     => 'required|boolean',
        ]);

        if ($request->hasFile('video_file')) {
            if ($rewardVideo->video_path) {
                Storage::disk('public')->delete($rewardVideo->video_path);
            }

            $data['video_path'] = $request->file('video_file')->store('reward-videos', 'public');
            $data['video_url'] = Storage::url($data['video_path']);
        } elseif ($request->filled('video_url')) {
            if ($rewardVideo->video_path) {
                Storage::disk('public')->delete($rewardVideo->video_path);
            }

            $data['video_path'] = null;
        } else {
            unset($data['video_url']);
        }

        unset($data['video_file']);
        $data['status'] = $request->boolean('status');

        $rewardVideo->update($data);

        return redirect()->route('reward-videos.index')->with('success', 'Video reward berhasil diperbarui.');
    }

    public function destroy(RewardVideo $rewardVideo)
    {
        if ($rewardVideo->video_path) {
            Storage::disk('public')->delete($rewardVideo->video_path);
        }

        $rewardVideo->delete();

        return redirect()->route('reward-videos.index')->with('success', 'Video reward berhasil dihapus.');
    }
}
