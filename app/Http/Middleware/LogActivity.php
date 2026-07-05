<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jalankan request terlebih dahulu agar auth state/session bisa terdeteksi dengan baik
        $response = $next($request);

        // Hanya catat request yang bersifat modifikasi data (menghindari database bloat dari GET request)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = null;

            // Periksa guard mana yang digunakan (web session atau mobile sanctum token)
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
            } elseif (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
            }

            if ($user) {
                // Tentukan user type (role)
                $userType = 'unknown';
                if (isset($user->role_id)) {
                    // Ambil nama role dari model jika ada, jika tidak, gunakan raw role_id
                    $userType = $user->role->role_name ?? 'Role ID: ' . $user->role_id;
                }

                // Tentukan nama modul dari segmen pertama URL
                $segment = $request->segment(1);
                $module = $segment ? ucfirst($segment) : 'System';

                // Tentukan nama action
                $action = $request->method() . ' action on ' . $request->path();

                try {
                    AuditLog::create([
                        'user_id' => $user->id,
                        'user_type' => $userType,
                        'action' => $action,
                        'module' => $module,
                        'url' => substr($request->fullUrl(), 0, 255), // Batasi max length text url walau di DB bertipe TEXT/VARCHAR 
                        'ip_address' => $request->ip(),
                    ]);
                } catch (\Exception $e) {
                    // Jika gagal log (misal DB error), jangan hentikan aplikasi
                }
            }
        }

        return $response;
    }
}
