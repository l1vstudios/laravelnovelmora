<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cerita extends Model
{
    protected $table = 'mst_cerita';

    protected $fillable = [
        'judul', 'cover', 'parts', 'isi_cerita', 'status',
        'sinopsis', 'total_read', 'total_vote', 'total_share',
        'recomendation', 'wajib_dibaca', 'id_kategori', 'positions_index', 'lock',
    ];

    protected $casts = [
        'isi_cerita' => 'array',
        'lock' => 'array',
        'status' => 'boolean',
        'recomendation' => 'boolean',
        'wajib_dibaca' => 'boolean',
        'positions_index' => 'integer',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function adPlacements()
    {
        return $this->hasMany(CeritaAd::class, 'cerita_id')
            ->orderBy('after_chapter')
            ->orderBy('placement_position')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
