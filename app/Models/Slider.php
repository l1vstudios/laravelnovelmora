<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'mst_sliders';

    protected $fillable = ['image_url', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];
}
