<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $verticalMenuData = json_decode(file_get_contents(base_path('resources/menu/verticalMenu.json')));

        View::share('menuData', [$verticalMenuData]);

        // Share user's viewable menu slugs so the blade can filter sidebar items
        View::composer('layouts.sections.menu.verticalMenu', function ($view) {
            $user = Auth::user();
            $allowedSlugs = null;

            if ($user && $user->role && !$user->role->is_super_admin) {
                $user->load('role.menus');
                $allowedSlugs = $user->role->menus
                    ->filter(fn($m) => $m->pivot->can_view)
                    ->pluck('slug')
                    ->flip(); // flip for O(1) lookup
            }

            $view->with('menuAllowedSlugs', $allowedSlugs);
        });
    }
}
