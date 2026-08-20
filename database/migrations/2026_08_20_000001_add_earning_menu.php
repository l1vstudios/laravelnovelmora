<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert the Earning menu item
        $exists = DB::table('mst_menu')->where('slug', 'earning')->exists();
        if (!$exists) {
            $menuId = DB::table('mst_menu')->insertGetId([
                'name' => 'Earning',
                'slug' => 'earning',
                'url' => 'earning',
                'icon' => 'bx bx-wallet',
                'group_label' => null,
                'urutan' => 3,
            ]);

            // Grant full access to all existing roles (so admins can see it immediately)
            $roles = DB::table('mst_roles')->pluck('id');
            foreach ($roles as $roleId) {
                DB::table('mst_role_menu')->insert([
                    'role_id' => $roleId,
                    'menu_id' => $menuId,
                    'can_view' => true,
                    'can_insert' => true,
                    'can_update' => true,
                    'can_delete' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        $menu = DB::table('mst_menu')->where('slug', 'earning')->first();
        if ($menu) {
            DB::table('mst_role_menu')->where('menu_id', $menu->id)->delete();
            DB::table('mst_menu')->where('id', $menu->id)->delete();
        }
    }
};
