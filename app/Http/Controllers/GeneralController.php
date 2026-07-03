<?php

namespace App\Http\Controllers;

class GeneralController extends Controller
{
    public function analytics()
    {
        $latestVersion = \App\Models\Versi::orderByDesc('id')->first(['version_name', 'version_code']);

        $stats = [
            'cerita'     => \App\Models\Cerita::count(),
            'kategori'   => \App\Models\Kategori::count(),
            'slider'     => \App\Models\Slider::count(),
            'notifikasi' => \App\Models\Notifikasi::count(),
            'action'     => \App\Models\MstAction::count(),
            'versi'      => $latestVersion?->version_name ?? '-',
            'pengguna'   => \App\Models\MstUser::count(),
            'total_read' => \App\Models\Cerita::sum('total_read'),
            'total_vote' => \App\Models\Cerita::sum('total_vote'),
        ];

        $latestCeritas = \App\Models\Cerita::with('kategori')->latest()->limit(5)->get();

        return view('content.dashboard.dashboards-analytics', compact('stats', 'latestCeritas'));
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
