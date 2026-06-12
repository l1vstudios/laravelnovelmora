<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RewardType extends Model
{
    protected $table = 'mst_reward_types';

    protected $fillable = [
        'name',
        'label',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function dailyRewards()
    {
        return $this->hasMany(DailyReward::class, 'reward_type_id');
    }
}
