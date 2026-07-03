<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MstUser extends Model
{
    protected $table = 'mst_users';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password'];
}
