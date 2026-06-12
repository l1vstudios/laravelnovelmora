<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RewardVideo extends Model
{
    protected $table = 'mst_reward_videos';

    protected $fillable = [
        'title',
        'video_url',
        'video_path',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function schedules()
    {
        return $this->hasMany(DailyRewardVideoSchedule::class, 'reward_video_id');
    }

    public function getVideoSrcAttribute(): string
    {
        if ($this->video_path) {
            return Storage::url($this->video_path);
        }

        return $this->video_url;
    }
}
