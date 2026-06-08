<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'mst_menu';

    protected $fillable = ['name', 'slug', 'url', 'icon', 'group_label', 'urutan'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'mst_role_menu', 'menu_id', 'role_id')
            ->withPivot('can_view', 'can_insert', 'can_update', 'can_delete')
            ->withTimestamps();
    }
}
