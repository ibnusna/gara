<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenance
{
    



    public function handle(Request $request, Closure $next): Response
    {
        
        if ($request->is('super-admin/*') || $request->is('super-admin')) {
            return $next($request);
        }

        
        if ($request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        try {
            $maintenance = DB::connection('mysql_apps')
                ->table('app_settings')
                ->where('setting_key', 'maintenance_mode')
                ->value('setting_value');

            if ($maintenance == '1') {
                return response()->view('maintenance', [], 200);
            }
        } catch (\Exception $e) {
            
        }

        return $next($request);
    }
}
