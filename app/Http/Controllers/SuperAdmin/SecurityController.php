<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IpBlock;
use App\Models\AppSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SecurityController extends Controller
{
    public function index()
    {
        $ip_blocks = IpBlock::orderBy('created_at', 'desc')->get();
        $settings_db = AppSetting::whereIn('setting_key', ['session_timeout', 'maintenance_mode'])
            ->pluck('setting_value', 'setting_key')->toArray();

        $session_timeout = $settings_db['session_timeout'] ?? 30;
        $maintenance_mode = $settings_db['maintenance_mode'] ?? '0';

        return view('super_admin.security.index', compact('ip_blocks', 'session_timeout', 'maintenance_mode'));
    }

    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        try {
            IpBlock::create([
                'ip_address' => $request->ip_address,
                'reason' => $request->reason,
            ]);
            return redirect()->back()->with('success_message', "IP '{$request->ip_address}' berhasil diblokir.");
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error_message', 'IP tersebut sudah ada dalam daftar blokir.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Terjadi kesalahan sistem.');
        }
    }

    public function unblockIp($id)
    {
        try {
            IpBlock::findOrFail($id)->delete();
            return redirect()->back()->with('success_message', 'IP berhasil dihapus dari daftar blokir.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Gagal membuka blokir IP.');
        }
    }

    public function emergencyView()
    {
        $is_maintenance = AppSetting::where('setting_key', 'maintenance_mode')->value('setting_value') == '1';
        return view('super_admin.emergency.view', compact('is_maintenance'));
    }

    public function toggleEmergency(Request $request)
    {
        $actorId = Auth::id() ?? 1;

        if ($request->action === 'force_logout_all') {
            try {
                
                DB::table('sessions')->truncate();

                
                DB::connection('mysql_auth')->table('audit_logs')->insert([
                    'user_id' => $actorId,
                    'user_type' => 'super_admin',
                    'action' => 'TRIGGERED FORCE LOGOUT ALL',
                    'module' => 'Emergency',
                    'ip_address' => request()->ip(),
                ]);

                return redirect()->back()->with('success_message', 'Semua sesi pengguna berhasil diakhiri.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error_message', 'Gagal memutus sesi: ' . $e->getMessage());
            }

        } elseif ($request->action === 'toggle_maintenance') {
            try {
                $new_val = $request->input('maintenance_status', '0'); 

                AppSetting::updateOrCreate(
                    ['setting_key' => 'maintenance_mode'],
                    ['setting_value' => $new_val]
                );

                $state = $new_val == '1' ? 'ENABLED' : 'DISABLED';

                DB::connection('mysql_auth')->table('audit_logs')->insert([
                    'user_id' => $actorId,
                    'user_type' => 'super_admin',
                    'action' => "Maintenance mode {$state}",
                    'module' => 'Emergency',
                    'ip_address' => request()->ip(),
                ]);

                $msg = "Maintenance mode berhasil " . ($new_val == '1' ? 'DIAKTIFKAN' : 'DIMATIKAN') . ".";
                return redirect()->back()->with('success_message', $msg);

            } catch (\Exception $e) {
                return redirect()->back()->with('error_message', 'Gagal mengubah mode maintenance: ' . $e->getMessage());
            }
        }

        return redirect()->back();
    }
}
