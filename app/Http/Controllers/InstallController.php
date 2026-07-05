<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallController extends Controller
{
    


    public function index()
    {

        $host = request()->getHost();
        $isLocalhost = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local');

        if (app()->environment('production') || !$isLocalhost) {
            return redirect()->route('login');
        }

        
        try {
            DB::connection('mysql_auth')->getPdo();
            if (\Illuminate\Support\Facades\Schema::connection('mysql_auth')->hasTable('users')) {
                return redirect('/');
            }
        } catch (\Exception $e) {
        }

        return view('install');
    }

    


    public function run()
    {
        try {
            
            
            set_time_limit(0);

            Artisan::call('gara:setup');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi dan Create Database berhasil dilakukan!',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proses Setup gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
