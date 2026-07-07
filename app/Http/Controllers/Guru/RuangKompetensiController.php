<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RuangKompetensiController extends Controller
{
    public function index()
    {
        $evaluasiAktif = \App\Models\RuangKompetensi::where('guru_id', auth()->id())->where('status', 'aktif')->orderBy('created_at', 'desc')->get();
        $evaluasiArsip = \App\Models\RuangKompetensi::where('guru_id', auth()->id())->where('status', 'arsip')->orderBy('created_at', 'desc')->get();

        return view('guru.ruang_kompetensi.index', compact('evaluasiAktif', 'evaluasiArsip'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'link_evaluasi' => 'required|url',
            'waktu_menit' => 'required|integer|min:1',
        ]);

        \App\Models\RuangKompetensi::create([
            'guru_id' => auth()->id(),
            'kelas_id' => session('kelas_id'),
            'mapel_id' => session('mapel_id'),
            'judul' => $request->judul,
            'link_evaluasi' => $request->link_evaluasi,
            'waktu_menit' => $request->waktu_menit,
            'status' => 'aktif',
        ]);

        return redirect()->route('guru.ruang_kompetensi.index')->with('success', 'Evaluasi berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $evaluasi = \App\Models\RuangKompetensi::where('guru_id', auth()->id())->findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'link_evaluasi' => 'required|url',
            'waktu_menit' => 'required|integer|min:1',
        ]);

        $evaluasi->update([
            'judul' => $request->judul,
            'link_evaluasi' => $request->link_evaluasi,
            'waktu_menit' => $request->waktu_menit,
        ]);

        return redirect()->route('guru.ruang_kompetensi.index')->with('success', 'Evaluasi berhasil diupdate!');
    }

    public function toggleArchive(Request $request, $id)
    {
        $evaluasi = \App\Models\RuangKompetensi::where('guru_id', auth()->id())->findOrFail($id);

        $current = $request->input('current_status', $evaluasi->status);
        $newStatus = $current === 'aktif' ? 'arsip' : 'aktif';

        $evaluasi->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'aktif' ? 'dipublikasikan' : 'diarsipkan';
        return redirect()->route('guru.ruang_kompetensi.index')->with('success', "Evaluasi berhasil {$statusLabel}!");
    }

    public function destroy($id)
    {
        $evaluasi = \App\Models\RuangKompetensi::where('guru_id', auth()->id())->findOrFail($id);
        $evaluasi->delete();

        return redirect()->route('guru.ruang_kompetensi.index')->with('success', 'Evaluasi berhasil dihapus!');
    }
}
