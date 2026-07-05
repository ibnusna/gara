<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGuruSession
{
    




    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('kelas_id') || !$request->session()->has('mapel_id')) {
            return redirect()->route('guru.sesi.index')->with('warning', 'Silakan pilih Mata Pelajaran dan Kelas terlebih dahulu.');
        }

        return $next($request);
    }
}
