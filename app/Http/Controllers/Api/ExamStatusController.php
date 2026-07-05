<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;












class ExamStatusController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $user   = $request->user();
        $siswa  = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data siswa tidak ditemukan.',
                'is_active' => false,
            ], 404);
        }

        $isActive = false;

        try {
            
            $config = DB::connection('mysql_asesmen')
                ->selectOne("SELECT status_pintu_siswa FROM asesmen_config LIMIT 1");

            if ($config && (int) $config->status_pintu_siswa === 1) {
                $isActive = true;
            }
        } catch (\Exception $e) {
            
            \Log::warning('[ExamStatus] asesmen_config query failed: ' . $e->getMessage());
        }

        return response()->json([
            'status'    => 'success',
            'is_active' => $isActive,
        ]);
    }
}
