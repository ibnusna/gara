<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckDatabaseInstalled
{
    






    public function handle(Request $request, Closure $next)
    {
        
        
        
        
        if (app()->environment('production')) {
            return $next($request);
        }

        
        
        
        
        
        $host = $request->getHost();
        $isLocalhost = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local');

        if (!$isLocalhost) {
            return $next($request);
        }

        
        
        if ($request->is('install') || $request->is('install/*') || $request->is('_debugbar*')) {
            config(['session.driver' => 'array']); 
            return $next($request);
        }

        
        try {
            DB::connection('mysql_auth')->getPdo();
            if (!Schema::connection('mysql_auth')->hasTable('users')) {
                return redirect()->route('install');
            }
        } catch (\Exception $e) {
            
            
            return redirect()->route('install');
        }

        return $next($request);
    }
}
