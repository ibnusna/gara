<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RuangKompetensiController extends Controller
{
    public function index()
    {
        $kelas_id = session('kelas_id');
        $mapel_id = session('mapel_id');

        // Guard: siswa harus sudah memilih mapel aktif
        if (!$kelas_id || !$mapel_id) {
            return redirect()->route('student.pilih-mapel')
                ->with('error', 'Silakan pilih mata pelajaran terlebih dahulu.');
        }

        // Filter berdasarkan kelas dan mapel siswa yang aktif (fix privasi)
        $evaluasi = \App\Models\RuangKompetensi::with('guru')
            ->where('status', 'aktif')
            ->where('kelas_id', $kelas_id)
            ->where('mapel_id', $mapel_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('siswa.ruang_kompetensi.index', compact('evaluasi'));
    }

    public function ujian($id)
    {
        $kelas_id = session('kelas_id');
        $mapel_id = session('mapel_id');

        // Validasi akses: pastikan ujian memang milik kelas dan mapel siswa ini
        $evaluasi = \App\Models\RuangKompetensi::where('status', 'aktif')
            ->where('kelas_id', $kelas_id)
            ->where('mapel_id', $mapel_id)
            ->findOrFail($id);

        return view('siswa.ruang_kompetensi.ujian', compact('evaluasi'));
    }
}
