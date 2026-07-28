<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'mst_sliders';

    protected $fillable = ['image_url', 'image_path', 'cerita_id', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function cerita()
    {
        return $this->belongsTo(Cerita::class, 'cerita_id');
    }
}
