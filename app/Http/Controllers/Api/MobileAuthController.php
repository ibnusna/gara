<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;












class MobileAuthController extends Controller
{
    





    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        
        $user = User::with('role')
            ->where('username', $request->identifier)
            ->where('status_aktif', 1)
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun tidak ditemukan atau tidak aktif.',
            ], 401);
        }

        
        if (!Hash::check($request->password, $user->getAuthPassword())) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Password salah.',
            ], 401);
        }

        
        $maintenance = \App\Models\AppSetting::where('setting_key', 'maintenance_mode')
            ->value('setting_value');

        if ($maintenance == '1' && optional($user->role)->role_name !== 'super_admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sistem sedang dalam pemeliharaan. Coba lagi nanti.',
                'maintenance' => true,
            ], 503);
        }

        
        $user->tokens()->delete();

        
        $token = $user->createToken('gara-mobile', ['*'])->plainTextToken;

        $role = optional($user->role)->role_name ?? 'siswa';

        
        $displayName = $user->nama_lengkap ?? $user->username;
        if ($role === 'siswa') {
            $siswa = \App\Models\Siswa::where('user_id', $user->id)->first();
            if ($siswa) $displayName = $siswa->nama;
        } elseif ($role === 'guru') {
            $guru = DB::connection('mysql_auth')->table('guru')->where('user_id', $user->id)->first();
            if ($guru) $displayName = $guru->nama_lengkap;
        }

        return response()->json([
            'status'          => 'success',
            'token'           => $token,
            'role'            => $role,
            'nama'            => $displayName,
            'username'        => $user->username,
            'profile_photo'   => $user->profile_photo_url,
        ]);
    }

    




    public function logout(Request $request): JsonResponse
    {
        
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil keluar.',
        ]);
    }

    





    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return response()->json([
            'status'        => 'success',
            'id'            => $user->id,
            'nama'          => $user->nama_lengkap,
            'username'      => $user->username,
            'role'          => optional($user->role)->role_name,
            'profile_photo' => $user->profile_photo_url,
            'status_aktif'  => $user->status_aktif,
        ]);
    }
}
