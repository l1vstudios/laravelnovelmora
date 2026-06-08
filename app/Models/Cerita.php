<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cerita extends Model
{
    protected $table = 'mst_cerita';

    protected $fillable = [
        'judul', 'cover', 'parts', 'isi_cerita', 'status',
        'total_read', 'total_vote', 'total_share',
        'recomendation', 'wajib_dibaca', 'id_kategori', 'lock',
    ];

    protected $casts = [
        'isi_cerita'    => 'array',
        'lock'          => 'array',
        'status'        => 'boolean',
        'recomendation' => 'boolean',
        'wajib_dibaca'  => 'boolean',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
}
