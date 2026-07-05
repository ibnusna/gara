<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\MataPelajaran;

class MasterDataController extends Controller
{
    public function index()
    {
        $kelases = Kelas::getSortedClasses();
        $mapels = MataPelajaran::orderBy('kategori', 'asc')->orderBy('nama_mapel', 'asc')->get();

        $settingsDB = \App\Models\AppSetting::pluck('setting_value', 'setting_key')->toArray();
        $jenjang = $settingsDB['jenjang_sekolah'] ?? 'SMP';
        
        $tingkatList = [];
        if ($jenjang === 'SD') $tingkatList = ['I', 'II', 'III', 'IV', 'V', 'VI'];
        elseif ($jenjang === 'SMP') $tingkatList = ['VII', 'VIII', 'IX'];
        elseif (in_array($jenjang, ['SMA', 'SMK'])) $tingkatList = ['X', 'XI', 'XII'];

        return view('operator.master.index', compact('kelases', 'mapels', 'tingkatList', 'settingsDB'));
    }

    public function manage(Request $request)
    {
        $type = $request->input('type');
        $action = $request->input('action');
        $id = $request->input('id');

        try {
            if ($type === 'kelas') {
                if ($action === 'add') {
                    $request->validate([
                        'tingkat' => 'required|string|max:10',
                        'nama_kelas' => 'nullable|string|max:255'
                    ]);
                    
                    $namaKelas = $request->nama_kelas;
                    if (empty($namaKelas)) {
                        $format = \App\Models\AppSetting::where('setting_key', 'format_rombel')->value('setting_value') ?? 'abjad';
                        $existingCount = Kelas::where('tingkat', $request->tingkat)->count();
                        if ($format === 'abjad') {
                            $suffix = chr(65 + $existingCount);
                            $namaKelas = $request->tingkat . ' ' . $suffix;
                        } else {
                            $suffix = $existingCount + 1;
                            $namaKelas = $request->tingkat . ' ' . $suffix;
                        }
                    }

                    Kelas::create([
                        'nama_kelas' => $namaKelas,
                        'tingkat' => $request->tingkat
                    ]);
                    return redirect()->back()->with('success_message', 'Kelas berhasil ditambahkan.');
                } elseif ($action === 'update') {
                    $request->validate([
                        'nama_kelas' => 'required|string|max:255',
                        'tingkat' => 'required|string|max:10'
                    ]);
                    $kelas = Kelas::findOrFail($id);
                    $kelas->update([
                        'nama_kelas' => $request->nama_kelas,
                        'tingkat' => $request->tingkat
                    ]);
                    return redirect()->back()->with('success_message', 'Kelas berhasil diupdate.');
                } elseif ($action === 'delete') {
                    $kelas = Kelas::findOrFail($id);
                    $kelas->delete();
                    return redirect()->back()->with('success_message', 'Kelas berhasil dihapus.');
                }
            } elseif ($type === 'mapel') {
                if ($action === 'add') {
                    $request->validate([
                        'nama_mapel' => 'required|string|max:255',
                        'kategori' => 'required|string|max:50'
                    ]);
                    MataPelajaran::create([
                        'nama_mapel' => $request->nama_mapel,
                        'kategori' => $request->kategori
                    ]);
                    return redirect()->back()->with('success_message', 'Mata Pelajaran berhasil ditambahkan.');
                } elseif ($action === 'update') {
                    $request->validate([
                        'nama_mapel' => 'required|string|max:255',
                        'kategori' => 'required|string|max:50'
                    ]);
                    $mapel = MataPelajaran::findOrFail($id);
                    $mapel->update([
                        'nama_mapel' => $request->nama_mapel,
                        'kategori' => $request->kategori
                    ]);
                    return redirect()->back()->with('success_message', 'Mata Pelajaran berhasil diupdate.');
                } elseif ($action === 'delete') {
                    $mapel = MataPelajaran::findOrFail($id);
                    $mapel->delete();
                    return redirect()->back()->with('success_message', 'Mata Pelajaran berhasil dihapus.');
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->back()->with('error_message', 'Aksi tidak valid.');
    }
}
