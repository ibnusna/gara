<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index()
    {
        $logs = DB::connection('mysql_auth')
            ->table('audit_logs as a')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('siswa as s', 'u.id', '=', 's.user_id')
            ->leftJoin('guru as g', 'u.id', '=', 'g.user_id')
            ->select(
                'a.*',
                DB::raw("COALESCE(NULLIF(u.nama_lengkap, ''), s.nama, g.nama_lengkap, u.username, 'System / Anonymous') AS actor_name"),
                'u.username AS actor_username'
            )
            ->orderBy('a.created_at', 'desc')
            ->limit(1000)
            ->get();

        return view('super_admin.audit.index', compact('logs'));
    }

    public function clearAll()
    {
        try {
            DB::connection('mysql_auth')->table('audit_logs')->truncate();
            return redirect()->back()->with('success_message', 'Seluruh log aktivitas berhasil dihapus secara permanen.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Gagal menghapus log aktivitas: ' . $e->getMessage());
        }
    }
}
