<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'mst_roles';

    protected $fillable = ['name', 'description', 'is_super_admin'];

    protected $casts = ['is_super_admin' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'mst_role_menu', 'role_id', 'menu_id')
            ->withPivot('can_view', 'can_insert', 'can_update', 'can_delete')
            ->withTimestamps();
    }

    public function getPermission(string $slug): ?object
    {
        return $this->menus->firstWhere('slug', $slug)?->pivot;
    }

    public function can(string $slug, string $action = 'view'): bool
    {
        if ($this->is_super_admin) return true;
        $perm = $this->getPermission($slug);
        if (!$perm) return false;
        return match($action) {
            'view'   => (bool) $perm->can_view,
            'insert' => (bool) $perm->can_insert,
            'update' => (bool) $perm->can_update,
            'delete' => (bool) $perm->can_delete,
            default  => false,
        };
    }
}
