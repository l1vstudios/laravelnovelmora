<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiturStore extends Model
{
    protected $table = 'mst_fitur_store';

    protected $fillable = ['konten', 'status'];

    protected $casts = [
        'konten' => 'array',
        'status' => 'boolean',
    ];
}
