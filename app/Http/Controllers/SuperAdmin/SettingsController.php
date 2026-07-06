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
        $request->validate([
            'settings' => 'required|array',
            'settings.sekolah_nama' => 'required|string',
            'settings.tahun_ajaran' => 'required|string',
            'settings.semester_aktif' => 'required|in:1,2',
            'settings.session_timeout' => 'required|integer|min:5',
            'settings.jenjang_sekolah' => 'required|in:SD,SMP,SMA,SMK',
            'settings.logo_mode' => 'nullable|in:default,custom,preset_tutwuri',
            'custom_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        try {
            $settingsData = $request->input('settings', []);

            if ($request->hasFile('custom_logo_file')) {
                $file = $request->file('custom_logo_file');
                $ext = $file->getClientOriginalExtension();
                $filename = 'logo_sekolah.' . $ext;
                
                // Pindahkan ke direktori logo sentral (di luar public)
                $file->move(base_path('../logo'), $filename);
                
                // Simpan nama file ke settings array agar ikut terupdate
                $settingsData['custom_logo_filename'] = $filename;
            }

            foreach ($settingsData as $key => $value) {
                AppSetting::updateOrCreate(
                    ['setting_key' => $key],
                    ['setting_value' => $value]
                );
            }
            
            // Clear cache for logo
            \Illuminate\Support\Facades\Cache::forget('logo_mode');
            \Illuminate\Support\Facades\Cache::forget('custom_logo_filename');

            return redirect()->back()->with('success_message', 'Pengaturan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }
}
