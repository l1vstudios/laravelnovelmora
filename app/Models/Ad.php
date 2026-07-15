<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Ad extends Model
{
    protected $table = 'mst_ads';

    protected $fillable = [
        'title',
        'media_type',
        'media_url',
        'media_path',
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
        return $this->hasMany(CeritaAd::class, 'ad_id')
            ->orderBy('cerita_id')
            ->orderBy('after_chapter')
            ->orderBy('placement_position');
    }

    public function getMediaSrcAttribute(): string
    {
        if ($this->media_path) {
            return Storage::url($this->media_path);
        }

        return $this->media_url;
    }
}
