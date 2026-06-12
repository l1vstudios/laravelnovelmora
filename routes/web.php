<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CeritaController;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\DailyRewardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\RewardTypeController;
use App\Http\Controllers\RewardVideoController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\MstActionController;
use App\Http\Controllers\VersiController;
use App\Http\Controllers\MstUserController;
use App\Http\Controllers\RoleController;

// Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Protected routes
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard — always accessible
    Route::get('/', [GeneralController::class, 'analytics'])->name('dashboard-analytics');

    // Profile — always accessible
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Permission-checked routes
    Route::middleware('role.permission')->group(function () {

        // Manajemen Konten
        Route::resource('cerita', CeritaController::class)
            ->parameters(['cerita' => 'cerita']);

        Route::resource('kategori', KategoriController::class)
            ->parameters(['kategori' => 'kategori']);

        Route::resource('slider', SliderController::class)
            ->parameters(['slider' => 'slider']);

        Route::resource('ads', AdsController::class);

        Route::resource('reward-types', RewardTypeController::class)
            ->parameters(['reward-types' => 'rewardType']);

        Route::resource('reward-videos', RewardVideoController::class)
            ->parameters(['reward-videos' => 'rewardVideo']);

        Route::resource('daily-rewards', DailyRewardController::class)
            ->parameters(['daily-rewards' => 'dailyReward']);
        Route::post('daily-rewards/{dailyReward}/claim', [DailyRewardController::class, 'claim'])
            ->name('daily-rewards.claim');

        // Master Data
        Route::resource('notifikasi', NotifikasiController::class)
            ->parameters(['notifikasi' => 'notifikasi']);

        Route::resource('action', MstActionController::class)
            ->parameters(['action' => 'action']);

        Route::resource('versi', VersiController::class)
            ->parameters(['versi' => 'versi']);

        // Pengguna
        Route::resource('pengguna', MstUserController::class)
            ->parameters(['pengguna' => 'pengguna']);

        // Roles
        Route::resource('roles', RoleController::class)
            ->parameters(['roles' => 'role']);
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])
            ->name('roles.permissions');
        Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
            ->name('roles.update-permissions');

        // Analitik
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    });
});
