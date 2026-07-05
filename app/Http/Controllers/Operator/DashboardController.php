<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('operator.dashboard');
    }

    public function updateSettings(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.format_rombel' => 'nullable|in:angka,abjad',
            'settings.jam_masuk_sekolah' => 'nullable|date_format:H:i',
            'settings.durasi_jp_menit' => 'nullable|integer|min:15|max:120',
        ]);

        try {
            // Include custom days custom duration settings dynamically if present
            foreach ($request->input('settings') as $key => $value) {
                \App\Models\AppSetting::updateOrCreate(
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
