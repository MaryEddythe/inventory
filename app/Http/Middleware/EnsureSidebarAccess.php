<?php

namespace App\Http\Middleware;

use App\Models\SidebarItem;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureSidebarAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $routeName || $user->isSuperAdmin()) {
            return $next($request);
        }

        $items = SidebarItem::query()
            ->whereNotNull('route_pattern')
            ->with('roles')
            ->get();

        foreach ($items as $item) {
            $pattern = $item->route_pattern ?: $item->route_name;

            if ($pattern && Str::is($pattern, $routeName) && $user->canAccessSidebarItem($item)) {
                return $next($request);
            }
        }

        return abort(403);
    }
}
