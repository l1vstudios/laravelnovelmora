<?php

namespace App\Http\Controllers;

use App\Models\DailyReward;
use App\Models\DailyRewardClaim;
use App\Models\DailyRewardVideoSchedule;
use App\Models\RewardType;
use App\Models\RewardVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyRewardController extends Controller
{
    private const MAX_UNSIGNED_INTEGER = 4294967295;

    private const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function index(Request $request)
    {
        $query = DailyReward::with(['type', 'videoSchedules.video'])->withCount('claims')->latest();

        if ($request->filled('reward_type_id')) {
            $query->where('reward_type_id', $request->reward_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }

        $dailyRewards = $query->paginate(10)->withQueryString();
        $rewardTypes = RewardType::orderBy('label')->get();

        return view('content.daily-rewards.index', compact('dailyRewards', 'rewardTypes'));
    }

    public function create()
    {
        return view('content.daily-rewards.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $videoSchedules = $data['video_schedules'] ?? [];
        unset($data['video_schedules']);

        $data['status'] = $request->boolean('status');

        $dailyReward = DailyReward::create($data);
        $this->syncVideoSchedules($dailyReward, $videoSchedules);

        return redirect()->route('daily-rewards.index')->with('success', 'Reward harian berhasil ditambahkan.');
    }

    public function show(DailyReward $dailyReward)
    {
        $dailyReward->load(['type', 'videoSchedules.video', 'claims.user']);
        $days = self::DAYS;

        return view('content.daily-rewards.show', compact('dailyReward', 'days'));
    }

    public function edit(DailyReward $dailyReward)
    {
        $dailyReward->load('videoSchedules');

        return view('content.daily-rewards.edit', $this->formData($dailyReward));
    }

    public function update(Request $request, DailyReward $dailyReward)
    {
        $data = $this->validatedData($request);
        $videoSchedules = $data['video_schedules'] ?? [];
        unset($data['video_schedules']);

        $data['status'] = $request->boolean('status');

        $dailyReward->update($data);
        $this->syncVideoSchedules($dailyReward, $videoSchedules);

        return redirect()->route('daily-rewards.index')->with('success', 'Reward harian berhasil diperbarui.');
    }

    public function destroy(DailyReward $dailyReward)
    {
        $dailyReward->delete();

        return redirect()->route('daily-rewards.index')->with('success', 'Reward harian berhasil dihapus.');
    }

    public function claim(Request $request, DailyReward $dailyReward)
    {
        $user = $request->user();
        $today = now()->toDateString();
        $dayOfWeek = (int) now()->isoWeekday();

        if (!$dailyReward->status) {
            return back()->with('error', 'Reward harian tidak aktif.');
        }

        $dailyReward->load(['type', 'videoSchedules.video']);
        $video = $dailyReward->type?->name === 'nonton_iklan'
            ? $dailyReward->videoForDay($dayOfWeek)
            : null;

        try {
            DB::transaction(function () use ($user, $dailyReward, $today, $video) {
                DailyRewardClaim::create([
                    'user_id'         => $user->id,
                    'daily_reward_id' => $dailyReward->id,
                    'reward_video_id' => $video?->id,
                    'claim_date'      => $today,
                    'coin_reward'     => $dailyReward->coin_reward,
                ]);

                $user->increment('coin_balance', $dailyReward->coin_reward);
            });
        } catch (\Illuminate\Database\QueryException) {
            return back()->with('error', 'Reward ini sudah dikerjakan hari ini. Coba lagi besok.');
        }

        return back()->with('success', 'Reward berhasil diklaim.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'reward_type_id'       => 'required|exists:mst_reward_types,id',
            'title'                => 'required|string|max:255',
            'coin_reward'          => 'required|integer|min:0|max:' . self::MAX_UNSIGNED_INTEGER,
            'target_url'           => 'nullable|url|max:2048',
            'status'               => 'required|boolean',
            'video_schedules'      => 'nullable|array',
            'video_schedules.*'    => 'nullable|integer|exists:mst_reward_videos,id',
        ], [
            'coin_reward.max' => 'Maaf, angka yang dimasukkan terlalu besar.',
        ]);
    }

    private function formData(?DailyReward $dailyReward = null): array
    {
        return [
            'dailyReward' => $dailyReward,
            'rewardTypes' => RewardType::active()->orderBy('label')->get(),
            'rewardVideos' => RewardVideo::active()->orderBy('title')->get(),
            'days' => self::DAYS,
        ];
    }

    private function syncVideoSchedules(DailyReward $dailyReward, array $videoSchedules): void
    {
        $dailyReward->videoSchedules()->delete();

        $rows = [];
        $now = now();

        foreach ($videoSchedules as $day => $videoId) {
            if (!$videoId) {
                continue;
            }

            $dayNumber = (int) $day;

            if ($dayNumber < 1 || $dayNumber > 7) {
                continue;
            }

            $rows[] = [
                'daily_reward_id' => $dailyReward->id,
                'reward_video_id' => (int) $videoId,
                'day_of_week'     => $dayNumber,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if ($rows) {
            DailyRewardVideoSchedule::insert($rows);
        }
    }
}
