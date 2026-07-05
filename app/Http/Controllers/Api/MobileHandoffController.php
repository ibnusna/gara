<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GaraPersonalAccessToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


















class MobileHandoffController extends Controller
{
    



    private const ALLOWED_PREFIXES = [
        '/student/',
        '/ruang-ujian',   
        '/guru/',
        '/operator/',
        '/kepsek/',
        '/super-admin/',
    ];

    private const DEFAULT_TARGET = '/student/dashboard';

    





    public function handoff(Request $request): RedirectResponse|Response
    {
        $rawToken = $request->query('token');
        $target   = $request->query('target', self::DEFAULT_TARGET);
        $mapelId  = $request->query('mapel_id'); 

        
        $target = '/' . ltrim($target, '/');
        $isAllowed = collect(self::ALLOWED_PREFIXES)
            ->contains(fn($prefix) => str_starts_with($target, $prefix));
        if (!$isAllowed) {
            $target = self::DEFAULT_TARGET;
        }

        
        if (!$rawToken) {
            \Log::warning("[GARA Handoff] Missing token in request.");
            return redirect('/login')->withErrors(['message' => 'Token tidak ditemukan.']);
        }

        $tokenModel = GaraPersonalAccessToken::findToken($rawToken);

        if (!$tokenModel || !$tokenModel->tokenable) {
            \Log::error("[GARA Handoff] Invalid or expired token: $rawToken");
            return redirect('/login')->withErrors(['message' => 'Sesi tidak valid atau sudah kadaluarsa.']);
        }

        
        $user = $tokenModel->tokenable->load('role');
        \Log::info("[GARA Handoff] Success: User {$user->username} (Role: {$user->role->role_name}) authenticated via WebView.");

        
        Auth::login($user);
        
        
        
        

        
        session([
            'user_id'  => $user->id,
            'username' => $user->username,
            'role_id'  => $user->role_id,
            'role'     => optional($user->role)->role_name,
        ]);

        
        if (optional($user->role)->role_name === 'siswa') {
            $siswa = \App\Models\Siswa::where('user_id', $user->id)->first();

            if ($siswa) {
                $kelas = \Illuminate\Support\Facades\DB::connection('mysql_auth')
                    ->table('kelas')->where('id', $siswa->kelas_id)->first();

                
                
                $mapel = null;
                if ($mapelId) {
                    $mapel = \Illuminate\Support\Facades\DB::connection('mysql_auth')
                        ->table('mata_pelajaran')->where('id', $mapelId)->first();
                }

                
                if (!$mapel) {
                    $mapel = \Illuminate\Support\Facades\DB::connection('mysql_apps')
                        ->table('class_subjects as cs')
                        ->join(
                            config('database.connections.mysql_auth.database') . '.mata_pelajaran as m',
                            'm.id', '=', 'cs.subject_id'
                        )
                        ->where('cs.class_id', $siswa->kelas_id)
                        ->select('m.*')
                        ->orderBy('m.nama_mapel', 'asc')
                        ->first();
                }

                session([
                    'siswa_id'   => $siswa->id,
                    'nama'       => $siswa->nama,
                    'kelas_id'   => $siswa->kelas_id,
                    'nama_kelas' => $kelas ? $kelas->nama_kelas : 'Belum Ada Kelas',
                    'mapel_id'   => $mapel ? $mapel->id : null,
                    'nama_mapel' => $mapel ? $mapel->nama_mapel : null,
                ]);

                \Log::info("[GARA Handoff] Student session: Siswa={$siswa->id}, Kelas={$siswa->kelas_id}, Mapel=" . ($mapel ? $mapel->nama_mapel : 'NONE'));

                
                if (!$mapel && str_starts_with($target, '/student/') && !str_contains($target, 'pilih-mapel') && !str_contains($target, 'dashboard')) {
                    $target = '/student/pilih-mapel';
                    \Log::warning("[GARA Handoff] No mapel found, redirecting to pilih-mapel");
                }
            } else {
                \Log::warning("[GARA Handoff] Student profile not found for user: {$user->id}");
            }
        }

        
        $request->session()->save();

        \Log::info("[GARA Handoff] Final Redirect to: $target (Instant — no bridge page)");

        
        
        
        
        return redirect()->to($target);
    }
}
