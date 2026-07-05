<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsOperator
{
    




    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error_message', 'Anda harus login terlebih dahulu.');
        }

        $user = Auth::user();

        if (optional($user->role)->role_name !== 'operator') {
            return redirect()->route('login')->with('error_message', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
