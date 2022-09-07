<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if is admin===========>>
        $userRole = $request->user()->load(['roles'])->roles;
        $isAdmin = collect($userRole)->contains('title', 'admin');
        if ($isAdmin) return $next($request);

        // if has permission===========>>
        $uri = $request->route()->uri();
        $httpMethod = strtolower($request->method());
        $roles = $request->user()->load(['roles.permissions'])->roles;
        $permissions = [];
        foreach ($roles as $key => $role) {
            foreach ($role->permissions as $k => $permission) {
                array_push($permissions, $permission);
            }
        }
        $hasPermission = collect($permissions)->contains(function ($val, $key) use ($uri, $httpMethod) {
            return $val->path == $uri && $val->method == $httpMethod;
        });
        if ($hasPermission) return $next($request);

        // If there is no permission ===========>>
        return response()->json([
            'message' => 'شما به این قسمت دسترسی ندارید',
        ] , 403);
    }
}
