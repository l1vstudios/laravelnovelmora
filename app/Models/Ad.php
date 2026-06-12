<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $table = 'mst_ads';

    protected $fillable = [
        'title',
        'media_type',
        'media_url',
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

    public function placements()
    {
        return $this->hasMany(CeritaAd::class, 'ad_id');
    }
}
