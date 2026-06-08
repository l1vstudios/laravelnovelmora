<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'mst_kategori';

    protected $fillable = ['default_title'];

    public function ceritas()
    {
        return $this->hasMany(Cerita::class, 'id_kategori');
    }
}
