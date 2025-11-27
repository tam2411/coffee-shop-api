<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // QUAN TRỌNG: API không redirect
        if (! $request->expectsJson()) {
            abort(401, 'Unauthorized');
        }
    }
}
