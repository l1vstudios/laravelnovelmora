<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Versi extends Model
{
    protected $table = 'mst_versions';

    protected $fillable = ['version_name', 'version_code'];
}
