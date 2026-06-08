<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'mst_notifikasi';

    protected $fillable = ['title', 'message'];
}
