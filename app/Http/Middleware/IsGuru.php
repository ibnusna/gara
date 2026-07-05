<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsGuru
{
    




    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || optional(auth()->user()->role)->role_name !== 'guru') {
            return redirect()->route('login')->with('error', 'Akses ditolak. Anda tidak memiliki izin sebagai Guru.');
        }

        
        if (auth()->user()->status === 'suspended') {
            \Illuminate\Support\Facades\Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda telah ditangguhkan.');
        }

        return $next($request);
    }
}
