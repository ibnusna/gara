<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\AppSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    
    
    
    const REMEMBER_COOKIE = 'gara_remember';

    
    const REMEMBER_LIFETIME = 31536000;

    public function showLoginForm(Request $request)
    {
        
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role->role_name ?? null);
        }

        
        
        
        $cookieValue = $request->cookie(self::REMEMBER_COOKIE);
        if ($cookieValue) {
            $parts = explode('|', $cookieValue, 2);
            if (count($parts) === 2) {
                [$userId, $plainToken] = $parts;
                $hashedToken = hash('sha256', $plainToken);

                $user = User::with('role')
                    ->where('id', $userId)
                    ->where('remember_token', $hashedToken)
                    ->where('status_aktif', 1)
                    ->first();

                if ($user) {
                    
                    Auth::login($user);
                    $request->session()->regenerate();

                    session(['user_id'  => $user->id]);
                    session(['username' => $user->username]);
                    session(['role_id'  => $user->role_id]);
                    session(['role'     => optional($user->role)->role_name]);

                    
                    $newPlainToken = Str::random(64);
                    $user->remember_token = hash('sha256', $newPlainToken);
                    $user->save();

                    $newCookieValue = $user->id . '|' . $newPlainToken;
                    return $this->redirectBasedOnRole(optional($user->role)->role_name)
                        ->withCookie(cookie(
                            self::REMEMBER_COOKIE,
                            $newCookieValue,
                            self::REMEMBER_LIFETIME / 60, 
                            '/',
                            null,
                            $request->secure(),
                            true,   
                            false,
                            'Lax'
                        ));
                }
            }

            
            Cookie::queue(Cookie::forget(self::REMEMBER_COOKIE));
        }

        $sekolah_nama = AppSetting::where('setting_key', 'sekolah_nama')->value('setting_value') ?? 'Garuda Akademi';

        return view('auth.login', compact('sekolah_nama'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->identifier,
            'password' => $request->password,
        ];

        
        $throttleKey = Str::transliterate(Str::lower($request->identifier).'|'.$request->ip());
        $attemptsKey = 'login_attempts_' . $throttleKey;
        $lockoutKey = 'login_lockout_' . $throttleKey;

        
        if (\Illuminate\Support\Facades\Cache::has($lockoutKey)) {
            $lockoutEnd = \Illuminate\Support\Facades\Cache::get($lockoutKey);
            $seconds = $lockoutEnd - time();
            if ($seconds > 0) {
                return response()->json([
                    'status' => 'error',
                    'lockout' => true,
                    'penalty_seconds' => $seconds,
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . ceil($seconds/60) . ' menit.'
                ]);
            } else {
                \Illuminate\Support\Facades\Cache::forget($lockoutKey);
            }
        }

        
        $user = User::with('role')->where('username', $request->identifier)->where('status_aktif', 1)->first();

        if (!$user) {
            
            $attempts = \Illuminate\Support\Facades\Cache::get($attemptsKey, 0) + 1;
            \Illuminate\Support\Facades\Cache::put($attemptsKey, $attempts, now()->addHours(24));

            if ($attempts >= 5) {
                $penalties = [1, 2, 5, 10, 20, 40, 60];
                $penaltyIndex = $attempts - 5;
                $penaltyMinutes = $penalties[$penaltyIndex] ?? 1440; 
                
                \Illuminate\Support\Facades\Cache::put($lockoutKey, time() + ($penaltyMinutes * 60), now()->addMinutes($penaltyMinutes));

                return response()->json([
                    'status' => 'error',
                    'lockout' => true,
                    'penalty_seconds' => $penaltyMinutes * 60,
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $penaltyMinutes . ' menit.'
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Akun tidak ditemukan atau tidak aktif.']);
        }

        
        $maintenance_mode = AppSetting::where('setting_key', 'maintenance_mode')->value('setting_value');
        if ($maintenance_mode == '1' && optional($user->role)->role_name !== 'super_admin') {
            return response()->json(['status' => 'success', 'role' => optional($user->role)->role_name, 'redirect' => url('/maintenance')]);
        }

        if (Auth::attempt($credentials)) {
            \Illuminate\Support\Facades\Cache::forget($attemptsKey);
            \Illuminate\Support\Facades\Cache::forget($lockoutKey);

            $request->session()->regenerate();

            
            session(['user_id'  => $user->id]);
            session(['username' => $user->username]);
            session(['role_id'  => $user->role_id]);
            session(['role'     => optional($user->role)->role_name]);

            
            
            
            $plainToken = Str::random(64);
            $user->remember_token = hash('sha256', $plainToken);
            $user->save();

            $cookieValue = $user->id . '|' . $plainToken;
            $rememberCookie = cookie(
                self::REMEMBER_COOKIE,
                $cookieValue,
                self::REMEMBER_LIFETIME / 60, 
                '/',
                null,
                $request->secure(),
                true,   
                false,
                'Lax'
            );

            return response()->json([
                'status'   => 'success',
                'role'     => optional($user->role)->role_name,
                'redirect' => $this->getRedirectPath($user)
            ])->withCookie($rememberCookie);
        }

        
        $attempts = \Illuminate\Support\Facades\Cache::get($attemptsKey, 0) + 1;
        \Illuminate\Support\Facades\Cache::put($attemptsKey, $attempts, now()->addHours(24));

        if ($attempts >= 5) {
            $penalties = [1, 2, 5, 10, 20, 40, 60];
            $penaltyIndex = $attempts - 5;
            $penaltyMinutes = $penalties[$penaltyIndex] ?? 1440; 
            
            \Illuminate\Support\Facades\Cache::put($lockoutKey, time() + ($penaltyMinutes * 60), now()->addMinutes($penaltyMinutes));

            return response()->json([
                'status' => 'error',
                'lockout' => true,
                'penalty_seconds' => $penaltyMinutes * 60,
                'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $penaltyMinutes . ' menit.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Password salah.']);
    }

    public function logout(Request $request)
    {
        
        
        
        $user = Auth::user();
        if ($user) {
            $user->remember_token = null;
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        
        return redirect()->route('login')
            ->withCookie(Cookie::forget(self::REMEMBER_COOKIE));
    }

    protected function redirectBasedOnRole($role)
    {
        if ($role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        } elseif ($role === 'operator') {
            return redirect()->route('operator.dashboard');
        } elseif ($role === 'kepsek') {
            return redirect()->route('kepsek.dashboard');
        } elseif ($role === 'guru') {
            return redirect()->route('guru.sesi.index');
        } elseif ($role === 'siswa') {
            return redirect()->route('student.pilih-mapel');
        }
        return redirect('/');
    }

    protected function getRedirectPath($user)
    {
        $role = optional($user->role)->role_name;
        if ($role === 'super_admin') {
            return route('superadmin.dashboard');
        } elseif ($role === 'operator') {
            return route('operator.dashboard');
        } elseif ($role === 'kepsek') {
            return route('kepsek.dashboard');
        } elseif ($role === 'guru') {
            return route('guru.sesi.index');
        } elseif ($role === 'siswa') {
            return route('student.pilih-mapel');
        }
        return url('/');
    }
}
