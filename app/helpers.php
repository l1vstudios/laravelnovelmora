<?php

if (!function_exists('user_can')) {
    /**
     * Check if the authenticated user's role has a permission for a menu slug.
     *
     * @param string $slug   Menu slug (e.g. 'cerita', 'kategori')
     * @param string $action view | insert | update | delete
     */
    function user_can(string $slug, string $action = 'view'): bool
    {
        $user = auth()->user();
        if (!$user || !$user->role) return false;
        if ($user->role->is_super_admin) return true;
        if (!$user->role->relationLoaded('menus')) {
            $user->load('role.menus');
        }
        return $user->role->can($slug, $action);
    }
}
