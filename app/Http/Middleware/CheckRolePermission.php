<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePermission
{
    // URL segments that bypass permission checks (always accessible)
    private const BYPASS = ['profile', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) return $next($request);

        // No role assigned → only dashboard accessible
        if (!$user->role) {
            if ($request->is('/') || $request->is('')) return $next($request);
            return redirect('/')->with('error', 'Anda belum memiliki role. Hubungi administrator.');
        }

        // Super admin bypasses all checks
        if ($user->role->is_super_admin) return $next($request);

        $slug = $this->resolveSlug($request);

        // Routes with no menu mapping always pass
        if (!$slug) return $next($request);

        $user->load('role.menus');
        $method = strtolower($request->method());

        $action = match(true) {
            in_array($method, ['put', 'patch']) => 'update',
            $method === 'post'                  => 'insert',
            $method === 'delete'                => 'delete',
            default                             => 'view',
        };

        // Always need view; mutating actions also need their own permission
        if (!$user->role->can($slug, 'view') || ($action !== 'view' && !$user->role->can($slug, $action))) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }

    private function resolveSlug(Request $request): ?string
    {
        $segment = $request->segment(1) ?? '';

        if ($segment === '' || $segment === null) return 'dashboard';
        if (in_array($segment, self::BYPASS)) return null;

        $menu = Menu::where('slug', $segment)
            ->orWhere('url', $segment)
            ->first();

        return $menu?->slug;
    }
}
