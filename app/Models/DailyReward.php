<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DailyReward extends Model
{
    protected $table = 'mst_daily_rewards';

    protected $fillable = [
        'reward_type_id',
        'title',
        'coin_reward',
        'target_url',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function type()
    {
        return $this->belongsTo(RewardType::class, 'reward_type_id');
    }

    public function videoSchedules()
    {
        return $this->hasMany(DailyRewardVideoSchedule::class, 'daily_reward_id');
    }

    public function claims()
    {
        return $this->hasMany(DailyRewardClaim::class, 'daily_reward_id');
    }

    public function videoForDay(int $dayOfWeek): ?RewardVideo
    {
        return $this->videosForDay($dayOfWeek)->first();
    }

    public function videosForDay(int $dayOfWeek): Collection
    {
        return $this->videoSchedules
            ->where('day_of_week', $dayOfWeek)
            ->pluck('video')
            ->filter()
            ->values();
    }
}
