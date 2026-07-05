<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckIpBlock
{
    










    public function handle(Request $request, Closure $next): Response
    {
        
        if ($request->is('super-admin/*') || $request->is('super-admin')) {
            return $next($request);
        }

        
        if ($request->is('login') || $request->is('logout') ||
            $request->is('install') || $request->is('install/*') ||
            $request->is('offline') || $request->is('maintenance')) {
            return $next($request);
        }

        
        $clientIp = $request->ip();

        try {
            $isBlocked = DB::connection('mysql_auth')
                ->table('ip_blocks')
                ->where('ip_address', $clientIp)
                ->exists();

            if ($isBlocked) {
                
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied.',
                    ], 403);
                }

                return response()->view('errors.403', ['reason' => 'ip_blocked'], 403);
            }
        } catch (\Exception $e) {
            
            
            
        }

        return $next($request);
    }
}
