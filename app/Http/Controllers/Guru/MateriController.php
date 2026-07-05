<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    public function index()
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        $materiList = \DB::connection('mysql_apps')->table('rpp_materi')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->orderBy('semester', 'asc')
            ->orderBy('bab', 'asc')
            ->orderBy('bagian', 'asc')
            ->get();

        return view('guru.materi.index', compact('materiList'));
    }

    private function generateIdMateri($semester, $bab, $bagian)
    {
        $namaMapel = session('nama_mapel');
        $namaKelas = session('nama_kelas');

        $mapelAbbr = strtoupper(substr($namaMapel, 0, 3));
        $kelasRoman = $namaKelas;

        return "{$mapelAbbr}-{$kelasRoman}-S{$semester}-B{$bab}-P{$bagian}";
    }

    public function store(Request $request)
    {
        $request->validate([
            'semester' => 'required',
            'bab' => 'required|integer',
            'bagian' => 'required|integer',
            'judul_materi' => 'required|string',
            'link_ppt' => 'nullable|url',
            'link_youtube' => 'nullable|url',
            'link_modul' => 'nullable|url',
            'link_tugas' => 'nullable|url',
            'link_notebook' => 'nullable|url',
        ]);

        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');

        
        $duplicate = DB::connection('mysql_apps')->table('rpp_materi')
            ->where('mapel_id', $mapel_id)
            ->where('kelas_id', $kelas_id)
            ->where('semester', $request->semester)
            ->where('bab', $request->bab)
            ->where('bagian', $request->bagian)
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'bagian' => 'Materi dengan Semester ' . $request->semester
                    . ', Bab ' . $request->bab
                    . ', Bagian "' . $request->bagian . '" sudah ada untuk mata pelajaran dan kelas ini.'
            ])->withInput();
        }

        $idMateri = $this->generateIdMateri($request->semester, $request->bab, $request->bagian);

        $exists = \DB::connection('mysql_apps')->table('rpp_materi')->where('id_materi', $idMateri)->exists();
        if ($exists) {
            return back()->with('error', "Materi dengan urutan tersebut (ID: $idMateri) sudah ada. Silakan cek semester/bab/bagian.");
        }

        \DB::connection('mysql_apps')->table('rpp_materi')->insert([
            'mapel_id' => session('mapel_id'),
            'kelas_id' => session('kelas_id'),
            'id_materi' => $idMateri,
            'semester' => $request->semester,
            'bab' => $request->bab,
            'bagian' => $request->bagian,
            'judul_materi' => trim($request->judul_materi),
            'link_ppt' => $request->link_ppt,
            'link_youtube' => $request->link_youtube,
            'link_modul' => $request->link_modul,
            'link_tugas' => $request->link_tugas,
            'link_notebook' => $request->link_notebook,
        ]);

        return back()->with('success', 'Materi berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'semester' => 'required',
            'bab' => 'required|integer',
            'bagian' => 'required|integer',
            'judul_materi' => 'required|string',
            'link_ppt' => 'nullable|url',
            'link_youtube' => 'nullable|url',
            'link_modul' => 'nullable|url',
            'link_tugas' => 'nullable|url',
            'link_notebook' => 'nullable|url',
        ]);

        $idMateri = $this->generateIdMateri($request->semester, $request->bab, $request->bagian);

        \DB::connection('mysql_apps')->table('rpp_materi')
            ->where('id', $id)
            ->where('mapel_id', session('mapel_id'))
            ->where('kelas_id', session('kelas_id'))
            ->update([
                'id_materi' => $idMateri,
                'semester' => $request->semester,
                'bab' => $request->bab,
                'bagian' => $request->bagian,
                'judul_materi' => trim($request->judul_materi),
                'link_ppt' => $request->link_ppt,
                'link_youtube' => $request->link_youtube,
                'link_modul' => $request->link_modul,
                'link_tugas' => $request->link_tugas,
                'link_notebook' => $request->link_notebook,
            ]);

        return back()->with('success', 'Materi berhasil diperbarui!');
    }

    public function destroy($id)
    {
        \DB::connection('mysql_apps')->table('rpp_materi')
            ->where('id', $id)
            ->where('mapel_id', session('mapel_id'))
            ->where('kelas_id', session('kelas_id'))
            ->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}
