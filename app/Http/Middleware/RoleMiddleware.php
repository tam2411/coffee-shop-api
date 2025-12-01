<?php

namespace App\Http\MIddleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['ok' => false, 'msg' => 'Unauthenticated'], 401);
        }

        $allowed = explode('|', $role);

        if (!in_array($user->role, $allowed)) {
            return response()->json(['ok' => false, 'msg' => 'Permission denied'], 403);
        }

        return $next($request);
    }
}
