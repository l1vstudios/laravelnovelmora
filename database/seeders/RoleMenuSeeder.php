<?php
namespace Database\Seeders;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RewardType;
use Illuminate\Database\Seeder;
class RoleMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['name' => 'Dashboard',           'slug' => 'dashboard',   'url' => '/',          'icon' => 'bx bx-home-smile',       'group_label' => null,               'urutan' => 1],
            ['name' => 'Analitik',            'slug' => 'analytics',   'url' => 'analytics',  'icon' => 'bx bx-bar-chart-alt-2',  'group_label' => null,               'urutan' => 2],
            ['name' => 'Cerita',              'slug' => 'cerita',      'url' => 'cerita',     'icon' => 'bx bx-book-open',        'group_label' => 'Manajemen Konten', 'urutan' => 3],
            ['name' => 'Kategori',            'slug' => 'kategori',    'url' => 'kategori',   'icon' => 'bx bx-category',         'group_label' => 'Manajemen Konten', 'urutan' => 4],
            ['name' => 'Slider',              'slug' => 'slider',      'url' => 'slider',     'icon' => 'bx bx-image-alt',        'group_label' => 'Manajemen Konten', 'urutan' => 5],
            ['name' => 'Ads',                 'slug' => 'ads',         'url' => 'ads',        'icon' => 'bx bx-purchase-tag-alt', 'group_label' => 'Manajemen Konten', 'urutan' => 6],
            ['name' => 'Reward Type',         'slug' => 'reward-types','url' => 'reward-types','icon' => 'bx bx-purchase-tag',     'group_label' => 'Master Data Mobile', 'urutan' => 7],
            ['name' => 'Reward Video',        'slug' => 'reward-videos','url' => 'reward-videos','icon' => 'bx bx-video',          'group_label' => 'Master Data Mobile', 'urutan' => 8],
            ['name' => 'Reward Harian',       'slug' => 'daily-rewards','url' => 'daily-rewards','icon' => 'bx bx-coin-stack',    'group_label' => 'Master Data Mobile', 'urutan' => 9],
            ['name' => 'Notifikasi',          'slug' => 'notifikasi',  'url' => 'notifikasi', 'icon' => 'bx bx-bell',             'group_label' => 'Master Data Mobile',      'urutan' => 10],
            ['name' => 'Action',              'slug' => 'action',      'url' => 'action',     'icon' => 'bx bx-list-check',       'group_label' => 'Master Data Mobile',      'urutan' => 11],
            ['name' => 'Versi Aplikasi',      'slug' => 'versi',       'url' => 'versi',      'icon' => 'bx bx-code-block',       'group_label' => 'Master Data Mobile',      'urutan' => 12],
            ['name' => 'Manajemen Pengguna',  'slug' => 'pengguna',    'url' => 'pengguna',   'icon' => 'bx bx-group',            'group_label' => 'Pengguna',         'urutan' => 13],
            ['name' => 'Manajemen Roles',     'slug' => 'roles',       'url' => 'roles',      'icon' => 'bx bx-shield-quarter',   'group_label' => 'Pengguna',         'urutan' => 14],
            ['name' => 'Profil Saya',         'slug' => 'profile',     'url' => 'profile',    'icon' => 'bx bx-user-circle',      'group_label' => 'Akun',             'urutan' => 15],
        ];
        foreach ($menus as $menu) {
            Menu::updateOrCreate(['slug' => $menu['slug']], $menu);
        }
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['description' => 'Akses penuh ke semua menu', 'is_super_admin' => true]
        );
        $allMenuIds = Menu::pluck('id');
        $sync = [];
        foreach ($allMenuIds as $id) {
            $sync[$id] = [
                'can_view'   => true,
                'can_insert' => true,
                'can_update' => true,
                'can_delete' => true,
            ];
        }
        $superAdmin->menus()->sync($sync);
        $viewer = Role::firstOrCreate(
            ['name' => 'Viewer'],
            ['description' => 'Hanya dapat melihat data', 'is_super_admin' => false]
        );
        $viewOnly = [];
        foreach ($allMenuIds as $id) {
            $viewOnly[$id] = [
                'can_view'   => true,
                'can_insert' => false,
                'can_update' => false,
                'can_delete' => false,
            ];
        }
        $viewer->menus()->sync($viewOnly);

        RewardType::updateOrCreate(
            ['name' => 'nonton_iklan'],
            ['label' => 'Nonton Iklan', 'description' => 'Reward harian dengan menonton video iklan.', 'status' => true]
        );

        RewardType::updateOrCreate(
            ['name' => 'follow_sosmed'],
            ['label' => 'Follow Sosmed', 'description' => 'Reward harian untuk mengikuti akun sosial media.', 'status' => true]
        );
    }
}
