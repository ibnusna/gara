<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RuangKompetensiController extends Controller
{
    public function index()
    {
        $evaluasi = \App\Models\RuangKompetensi::with('guru')->where('status', 'aktif')->orderBy('created_at', 'desc')->get();

        return view('siswa.ruang_kompetensi.index', compact('evaluasi'));
    }

    public function ujian($id)
    {
        $evaluasi = \App\Models\RuangKompetensi::where('status', 'aktif')->findOrFail($id);

        return view('siswa.ruang_kompetensi.ujian', compact('evaluasi'));
    }
}
