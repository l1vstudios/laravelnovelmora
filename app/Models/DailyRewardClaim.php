<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyRewardClaim extends Model
{
    public const DAILY_CLAIM_KEY = 'daily';

    protected $table = 'mst_daily_reward_claims';

    protected $fillable = [
        'user_id',
        'daily_reward_id',
        'reward_video_id',
        'claim_key',
        'claim_date',
        'coin_reward',
    ];

    protected $casts = [
        'claim_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyReward()
    {
        return $this->belongsTo(DailyReward::class, 'daily_reward_id');
    }

    public function video()
    {
        return $this->belongsTo(RewardVideo::class, 'reward_video_id');
    }

    public static function videoClaimKey(int $videoId): string
    {
        return 'video:' . $videoId;
    }
}
