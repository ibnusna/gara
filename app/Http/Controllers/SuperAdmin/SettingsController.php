<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class SettingsController extends Controller
{
    public function index()
    {
        $settingsDB = AppSetting::pluck('setting_value', 'setting_key')->toArray();
        return view('super_admin.settings.index', compact('settingsDB'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.sekolah_nama' => 'required|string',
            'settings.tahun_ajaran' => 'required|string',
            'settings.semester_aktif' => 'required|in:1,2',
            'settings.session_timeout' => 'required|integer|min:5',
            'settings.jenjang_sekolah' => 'required|in:SD,SMP,SMA,SMK',
        ]);

        try {
            foreach ($validated['settings'] as $key => $value) {
                AppSetting::updateOrCreate(
                    ['setting_key' => $key],
                    ['setting_value' => $value]
                );
            }
            return redirect()->back()->with('success_message', 'Pengaturan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }
}
