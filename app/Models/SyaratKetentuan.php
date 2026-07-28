<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyaratKetentuan extends Model
{
    protected $table = 'mst_syarat_ketentuan';

    public $timestamps = false;

    protected $fillable = ['konten'];
}
