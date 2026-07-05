<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsKepsek
{
    




    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && optional(auth()->user()->role)->role_name === 'kepsek') {
            return $next($request);
        }

        abort(403, 'Access Denied: You do not have Principal privileges.');
    }
}
