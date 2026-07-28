<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'mst_faqs';

    protected $fillable = ['question', 'answer', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];
}
