<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->latest()->get();
        return view('content.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('content.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:mst_roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        Role::create([
            'name'           => $request->name,
            'description'    => $request->description,
            'is_super_admin' => $request->boolean('is_super_admin'),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        return view('content.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:mst_roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'name'           => $request->name,
            'description'    => $request->description,
            'is_super_admin' => $request->boolean('is_super_admin'),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }

    public function permissions(Role $role)
    {
        $menus = Menu::orderBy('urutan')->get()->groupBy('group_label');
        $role->load('menus');

        // Map existing permissions by menu_id for easy lookup in view
        $perms = $role->menus->keyBy('id')->map(fn($m) => $m->pivot);

        return view('content.roles.permissions', compact('role', 'menus', 'perms'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $incoming = $request->input('permissions', []);
        $allMenus = Menu::pluck('id');

        $sync = [];
        foreach ($allMenus as $menuId) {
            $p = $incoming[$menuId] ?? [];
            $sync[$menuId] = [
                'can_view'   => isset($p['can_view']),
                'can_insert' => isset($p['can_insert']),
                'can_update' => isset($p['can_update']),
                'can_delete' => isset($p['can_delete']),
            ];
        }

        $role->menus()->sync($sync);

        return redirect()->route('roles.permissions', $role)->with('success', 'Hak akses berhasil disimpan.');
    }
}
