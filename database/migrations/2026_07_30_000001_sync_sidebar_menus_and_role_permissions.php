<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_menu') || ! Schema::hasTable('mst_roles') || ! Schema::hasTable('mst_role_menu')) {
            return;
        }

        if (! Schema::hasColumn('mst_menu', 'parent_slug')) {
            Schema::table('mst_menu', function (Blueprint $table) {
                $table->string('parent_slug')->nullable()->after('group_label');
            });
        }

        $now = now();
        $menus = $this->menus();

        foreach ($menus as $menu) {
            $payload = [
                'name' => $menu['name'],
                'url' => $menu['url'],
                'icon' => $menu['icon'],
                'group_label' => $menu['group_label'],
                'parent_slug' => $menu['parent_slug'],
                'urutan' => $menu['urutan'],
                'updated_at' => $now,
            ];

            $exists = DB::table('mst_menu')->where('slug', $menu['slug'])->exists();

            if ($exists) {
                DB::table('mst_menu')->where('slug', $menu['slug'])->update($payload);
            } else {
                DB::table('mst_menu')->insert(array_merge($payload, [
                    'slug' => $menu['slug'],
                    'created_at' => $now,
                ]));
            }
        }

        $menuRows = DB::table('mst_menu')->whereIn('slug', collect($menus)->pluck('slug'))->get()->keyBy('slug');
        $roles = DB::table('mst_roles')->get();

        foreach ($roles as $role) {
            foreach ($menus as $menu) {
                $menuId = $menuRows[$menu['slug']]->id ?? null;

                if (! $menuId || DB::table('mst_role_menu')->where('role_id', $role->id)->where('menu_id', $menuId)->exists()) {
                    continue;
                }

                $permissions = $this->defaultPermissionsFor($role, $menu, $menuRows);

                DB::table('mst_role_menu')->insert(array_merge($permissions, [
                    'role_id' => $role->id,
                    'menu_id' => $menuId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('mst_menu') || ! Schema::hasTable('mst_role_menu')) {
            return;
        }

        $submenuSlugs = collect($this->menus())
            ->filter(fn (array $menu): bool => $menu['parent_slug'] !== null)
            ->pluck('slug');

        $submenuIds = DB::table('mst_menu')->whereIn('slug', $submenuSlugs)->pluck('id');

        if ($submenuIds->isNotEmpty()) {
            DB::table('mst_role_menu')->whereIn('menu_id', $submenuIds)->delete();
            DB::table('mst_menu')->whereIn('id', $submenuIds)->delete();
        }

        if (Schema::hasColumn('mst_menu', 'parent_slug')) {
            Schema::table('mst_menu', function (Blueprint $table) {
                $table->dropColumn('parent_slug');
            });
        }
    }

    private function defaultPermissionsFor(object $role, array $menu, $menuRows): array
    {
        if ((bool) $role->is_super_admin) {
            return ['can_view' => true, 'can_insert' => true, 'can_update' => true, 'can_delete' => true];
        }

        if ($menu['parent_slug']) {
            $parentId = $menuRows[$menu['parent_slug']]->id ?? null;
            $parentPerm = $parentId
                ? DB::table('mst_role_menu')->where('role_id', $role->id)->where('menu_id', $parentId)->first()
                : null;

            $canView = (bool) ($parentPerm?->can_view ?? false);
            $canInsert = (bool) ($parentPerm?->can_insert ?? false);

            if (str_ends_with($menu['slug'], '-create')) {
                return ['can_view' => $canInsert, 'can_insert' => $canInsert, 'can_update' => false, 'can_delete' => false];
            }

            return ['can_view' => $canView, 'can_insert' => false, 'can_update' => false, 'can_delete' => false];
        }

        if ($role->name === 'Viewer') {
            return ['can_view' => true, 'can_insert' => false, 'can_update' => false, 'can_delete' => false];
        }

        return ['can_view' => false, 'can_insert' => false, 'can_update' => false, 'can_delete' => false];
    }

    private function menus(): array
    {
        $items = [
            ['Dashboard', 'dashboard', '/', 'bx bx-home-smile', null, null],
            ['Analitik', 'analytics', 'analytics', 'bx bx-bar-chart-alt-2', null, null],
            ['Cerita', 'cerita', null, 'bx bx-book-open', 'Manajemen Konten', null],
            ['Semua Cerita', 'cerita-index', 'cerita', null, 'Manajemen Konten', 'cerita'],
            ['Tambah Cerita', 'cerita-create', 'cerita/create', null, 'Manajemen Konten', 'cerita'],
            ['Kategori', 'kategori', null, 'bx bx-category', 'Manajemen Konten', null],
            ['Semua Kategori', 'kategori-index', 'kategori', null, 'Manajemen Konten', 'kategori'],
            ['Tambah Kategori', 'kategori-create', 'kategori/create', null, 'Manajemen Konten', 'kategori'],
            ['Slider', 'slider', null, 'bx bx-image-alt', 'Manajemen Konten', null],
            ['Semua Slider', 'slider-index', 'slider', null, 'Manajemen Konten', 'slider'],
            ['Tambah Slider', 'slider-create', 'slider/create', null, 'Manajemen Konten', 'slider'],
            ['Ads', 'ads', null, 'bx bx-purchase-tag-alt', 'Manajemen Konten', null],
            ['Semua Ads', 'ads-index', 'ads', null, 'Manajemen Konten', 'ads'],
            ['Tambah Ads', 'ads-create', 'ads/create', null, 'Manajemen Konten', 'ads'],
            ['Master Type', 'reward-types', null, 'bx bx-purchase-tag', 'Master Data Mobile', null],
            ['Semua Type', 'reward-types-index', 'reward-types', null, 'Master Data Mobile', 'reward-types'],
            ['Tambah Type', 'reward-types-create', 'reward-types/create', null, 'Master Data Mobile', 'reward-types'],
            ['Master Video', 'reward-videos', null, 'bx bx-video', 'Master Data Mobile', null],
            ['Semua Video', 'reward-videos-index', 'reward-videos', null, 'Master Data Mobile', 'reward-videos'],
            ['Tambah Video', 'reward-videos-create', 'reward-videos/create', null, 'Master Data Mobile', 'reward-videos'],
            ['Reward Harian', 'daily-rewards', null, 'bx bx-coin-stack', 'Master Data Mobile', null],
            ['Semua Reward', 'daily-rewards-index', 'daily-rewards', null, 'Master Data Mobile', 'daily-rewards'],
            ['Tambah Reward', 'daily-rewards-create', 'daily-rewards/create', null, 'Master Data Mobile', 'daily-rewards'],
            ['Pusat Bantuan', 'pusat-bantuan', null, 'bx bx-help-circle', 'Konten Aplikasi', null],
            ['Semua Bantuan', 'pusat-bantuan-index', 'pusat-bantuan', null, 'Konten Aplikasi', 'pusat-bantuan'],
            ['Tambah Bantuan', 'pusat-bantuan-create', 'pusat-bantuan/create', null, 'Konten Aplikasi', 'pusat-bantuan'],
            ['FAQ', 'faqs', null, 'bx bx-question-mark', 'Konten Aplikasi', null],
            ['Semua FAQ', 'faqs-index', 'faqs', null, 'Konten Aplikasi', 'faqs'],
            ['Tambah FAQ', 'faqs-create', 'faqs/create', null, 'Konten Aplikasi', 'faqs'],
            ['Syarat Ketentuan', 'syarat-ketentuan', null, 'bx bx-file', 'Konten Aplikasi', null],
            ['Semua Syarat', 'syarat-ketentuan-index', 'syarat-ketentuan', null, 'Konten Aplikasi', 'syarat-ketentuan'],
            ['Tambah Syarat', 'syarat-ketentuan-create', 'syarat-ketentuan/create', null, 'Konten Aplikasi', 'syarat-ketentuan'],
            ['Kebijakan Privasi', 'kebijakan-privasi', null, 'bx bx-shield-quarter', 'Konten Aplikasi', null],
            ['Semua Kebijakan', 'kebijakan-privasi-index', 'kebijakan-privasi', null, 'Konten Aplikasi', 'kebijakan-privasi'],
            ['Tambah Kebijakan', 'kebijakan-privasi-create', 'kebijakan-privasi/create', null, 'Konten Aplikasi', 'kebijakan-privasi'],
            ['Fitur Store', 'fitur-store', null, 'bx bx-store', 'Konten Aplikasi', null],
            ['Semua Fitur', 'fitur-store-index', 'fitur-store', null, 'Konten Aplikasi', 'fitur-store'],
            ['Tambah Fitur', 'fitur-store-create', 'fitur-store/create', null, 'Konten Aplikasi', 'fitur-store'],
            ['Notifikasi', 'notifikasi', null, 'bx bx-bell', 'Master Data Mobile', null],
            ['Semua Notifikasi', 'notifikasi-index', 'notifikasi', null, 'Master Data Mobile', 'notifikasi'],
            ['Tambah Notifikasi', 'notifikasi-create', 'notifikasi/create', null, 'Master Data Mobile', 'notifikasi'],
            ['Action', 'action', null, 'bx bx-list-check', 'Master Data Mobile', null],
            ['Semua Action', 'action-index', 'action', null, 'Master Data Mobile', 'action'],
            ['Tambah Action', 'action-create', 'action/create', null, 'Master Data Mobile', 'action'],
            ['Versi Aplikasi', 'versi', null, 'bx bx-code-block', 'Master Data Mobile', null],
            ['Semua Versi', 'versi-index', 'versi', null, 'Master Data Mobile', 'versi'],
            ['Tambah Versi', 'versi-create', 'versi/create', null, 'Master Data Mobile', 'versi'],
            ['Manajemen Pengguna', 'pengguna', null, 'bx bx-group', 'Pengguna', null],
            ['Semua Pengguna', 'pengguna-index', 'pengguna', null, 'Pengguna', 'pengguna'],
            ['Tambah Pengguna', 'pengguna-create', 'pengguna/create', null, 'Pengguna', 'pengguna'],
            ['Manajemen Roles', 'roles', null, 'bx bx-shield-quarter', 'Pengguna', null],
            ['Semua Roles', 'roles-index', 'roles', null, 'Pengguna', 'roles'],
            ['Tambah Role', 'roles-create', 'roles/create', null, 'Pengguna', 'roles'],
            ['Profil Saya', 'profile', 'profile', 'bx bx-user-circle', 'Akun', null],
        ];

        return collect($items)->map(function (array $item, int $index): array {
            return [
                'name' => $item[0],
                'slug' => $item[1],
                'url' => $item[2],
                'icon' => $item[3],
                'group_label' => $item[4],
                'parent_slug' => $item[5],
                'urutan' => ($index + 1) * 10,
            ];
        })->all();
    }
};
