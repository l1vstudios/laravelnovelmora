<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PusatBantuan extends Model
{
    protected $table = 'mst_pusat_bantuan';

    protected $fillable = ['nama_layanan', 'isi_layanan'];
}
