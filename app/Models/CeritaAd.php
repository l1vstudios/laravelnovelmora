<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CeritaAd extends Model
{
    protected $table = 'mst_cerita_ads';

    protected $fillable = [
        'cerita_id',
        'ad_id',
        'after_chapter',
    ];

    public function cerita()
    {
        return $this->belongsTo(Cerita::class, 'cerita_id');
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }
}
