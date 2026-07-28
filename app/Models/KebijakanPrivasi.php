<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KebijakanPrivasi extends Model
{
    protected $table = 'mst_kebijakan_privasi';

    protected $fillable = ['nama', 'isi_konten'];
}
