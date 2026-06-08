<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MstAction extends Model
{
    protected $table = 'mst_action';

    protected $fillable = ['action_name'];
}
