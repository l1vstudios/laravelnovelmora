<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyRewardVideoSchedule extends Model
{
    protected $table = 'mst_daily_reward_video_schedules';

    protected $fillable = [
        'daily_reward_id',
        'reward_video_id',
        'day_of_week',
    ];

    public function dailyReward()
    {
        return $this->belongsTo(DailyReward::class, 'daily_reward_id');
    }

    public function video()
    {
        return $this->belongsTo(RewardVideo::class, 'reward_video_id');
    }
}
