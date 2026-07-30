<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\RewardType;
use App\Models\Role;
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
            ['name' => 'Master Type',         'slug' => 'reward-types', 'url' => 'reward-types', 'icon' => 'bx bx-purchase-tag',     'group_label' => 'Master Data Mobile', 'urutan' => 7],
            ['name' => 'Master Video',        'slug' => 'reward-videos', 'url' => 'reward-videos', 'icon' => 'bx bx-video',          'group_label' => 'Master Data Mobile', 'urutan' => 8],
            ['name' => 'Reward Harian',       'slug' => 'daily-rewards', 'url' => 'daily-rewards', 'icon' => 'bx bx-coin-stack',    'group_label' => 'Master Data Mobile', 'urutan' => 9],
            ['name' => 'Pusat Bantuan',       'slug' => 'pusat-bantuan', 'url' => 'pusat-bantuan', 'icon' => 'bx bx-help-circle',    'group_label' => 'Konten Aplikasi',  'urutan' => 10],
            ['name' => 'FAQ',                 'slug' => 'faqs',        'url' => 'faqs',       'icon' => 'bx bx-question-mark',    'group_label' => 'Konten Aplikasi',  'urutan' => 11],
            ['name' => 'Syarat Ketentuan',    'slug' => 'syarat-ketentuan', 'url' => 'syarat-ketentuan', 'icon' => 'bx bx-file',     'group_label' => 'Konten Aplikasi',  'urutan' => 12],
            ['name' => 'Kebijakan Privasi',   'slug' => 'kebijakan-privasi', 'url' => 'kebijakan-privasi', 'icon' => 'bx bx-shield-quarter', 'group_label' => 'Konten Aplikasi', 'urutan' => 13],
            ['name' => 'Fitur Store',         'slug' => 'fitur-store', 'url' => 'fitur-store', 'icon' => 'bx bx-store',             'group_label' => 'Konten Aplikasi',  'urutan' => 14],
            ['name' => 'Notifikasi',          'slug' => 'notifikasi',  'url' => 'notifikasi', 'icon' => 'bx bx-bell',             'group_label' => 'Master Data Mobile',      'urutan' => 15],
            ['name' => 'Action',              'slug' => 'action',      'url' => 'action',     'icon' => 'bx bx-list-check',       'group_label' => 'Master Data Mobile',      'urutan' => 16],
            ['name' => 'Versi Aplikasi',      'slug' => 'versi',       'url' => 'versi',      'icon' => 'bx bx-code-block',       'group_label' => 'Master Data Mobile',      'urutan' => 17],
            ['name' => 'Manajemen Pengguna',  'slug' => 'pengguna',    'url' => 'pengguna',   'icon' => 'bx bx-group',            'group_label' => 'Pengguna',         'urutan' => 18],
            ['name' => 'Manajemen Roles',     'slug' => 'roles',       'url' => 'roles',      'icon' => 'bx bx-shield-quarter',   'group_label' => 'Pengguna',         'urutan' => 19],
            ['name' => 'Profil Saya',         'slug' => 'profile',     'url' => 'profile',    'icon' => 'bx bx-user-circle',      'group_label' => 'Akun',             'urutan' => 20],
        ];
        foreach ($menus as $menu) {
            Menu::updateOrCreate(['slug' => $menu['slug']], $menu);
        }
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['description' => 'Akses penuh ke semua menu', 'is_super_admin' => true]
        );
        $allMenus = Menu::all(['id', 'slug']);
        $allMenuIds = $allMenus->pluck('id');
        $sync = [];
        foreach ($allMenuIds as $id) {
            $sync[$id] = [
                'can_view' => true,
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
        foreach ($allMenus as $menu) {
            $viewOnly[$menu->id] = [
                'can_view' => ! str_ends_with($menu->slug, '-create'),
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
