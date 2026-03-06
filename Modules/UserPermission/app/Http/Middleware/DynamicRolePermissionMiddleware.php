<?php
namespace Modules\UserPermission\App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;

class DynamicRolePermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        dd(123);
        $user = auth()->user();

        // Ensure the user is logged in
        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        // Get the current route name
        $routeName = Route::currentRouteName(); // Example: 'customers.index'

        // Define dynamic permissions based on route groups
        $routePermissions = [
            'customers.create' => 'create_customer',

        ];

        // Check if the route has a defined permission
        foreach ($routePermissions as $route => $permission) {
            if (str_contains($routeName, $route)) {
                if (!$user->can($permission)) {
                    throw UnauthorizedException::forPermissions([$permission]);
                }
                break; // Exit once a match is found
            }
        }

        return $next($request);
    }
}
