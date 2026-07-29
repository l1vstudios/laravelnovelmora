<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GeneralController extends Controller
{
    public function analytics()
    {
        $latestVersion = \App\Models\Versi::orderByDesc('id')->first(['version_name', 'version_code']);
        $mobileUserCountQuery = DB::table('mst_users');

        if (Schema::hasColumn('mst_users', 'deleted')) {
            $mobileUserCountQuery->where(function ($query) {
                $query->whereNull('deleted')->orWhere('deleted', '!=', 1);
            });
        }

        $stats = [
            'cerita'     => \App\Models\Cerita::count(),
            'kategori'   => \App\Models\Kategori::count(),
            'slider'     => \App\Models\Slider::count(),
            'notifikasi' => \App\Models\Notifikasi::count(),
            'action'     => \App\Models\MstAction::count(),
            'versi'      => $latestVersion?->version_name ?? '-',
            'pengguna'   => $mobileUserCountQuery->count(),
            'total_read' => \App\Models\Cerita::sum('total_read'),
            'total_vote' => \App\Models\Cerita::sum('total_vote'),
        ];

        $latestCeritas = \App\Models\Cerita::with('kategori')->latest()->limit(5)->get();

        return view('content.dashboard.dashboards-analytics', compact('stats', 'latestCeritas'));
    }

    public function mobileUsers(Request $request)
    {
        $table = 'mst_users';
        $columns = collect([
            'id',
            'name',
            'email',
            'auth_provider',
            'points',
            'koin',
            'email_verified_at',
            'last_login_at',
            'created_at',
        ])->filter(fn (string $column): bool => Schema::hasColumn($table, $column))->values();

        $query = DB::table($table)->select($columns->all());

        if (Schema::hasColumn($table, 'deleted')) {
            $query->where(function ($query) {
                $query->whereNull('deleted')->orWhere('deleted', '!=', 1);
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $searchableColumns = $columns->intersect(['name', 'email']);

            if ($searchableColumns->isNotEmpty()) {
                $query->where(function ($query) use ($searchableColumns, $search) {
                    foreach ($searchableColumns as $column) {
                        $query->orWhere($column, 'like', "%{$search}%");
                    }
                });
            }
        }

        if ($columns->contains('created_at')) {
            $query->orderByDesc('created_at');
        } else {
            $query->orderByDesc('id');
        }

        $users = $query->paginate(15)->withQueryString();

        return view('content.dashboard.mobile-users', compact('users', 'columns'));
    }

    public function accountSettingsAccount()
    {
        return view('content.pages.pages-account-settings-account');
    }

    public function accountSettingsNotifications()
    {
        return view('content.pages.pages-account-settings-notifications');
    }

    public function accountSettingsConnections()
    {
        return view('content.pages.pages-account-settings-connections');
    }
}
