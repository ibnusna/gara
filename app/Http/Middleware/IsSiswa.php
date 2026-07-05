<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSiswa
{
    




    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && optional(auth()->user()->role)->role_name === 'siswa') {

            
            if (!$request->routeIs('student.pilih-mapel') && !$request->routeIs('student.set-mapel') && !$request->routeIs('logout')) {
                if (!session()->has('mapel_id')) {
                    return redirect()->route('student.pilih-mapel');
                }
            }

            return $next($request);
        }

        abort(403, 'Access Denied: You do not have Student privileges.');
    }
}
