<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AsesmenConfig;

class AsesmenController extends Controller
{
    public function dashboard()
    {
        return view('operator.asesmen.dashboard');
    }

    public function jadwal()
    {
        return view('operator.asesmen.jadwal');
    }

    public function hasil(Request $request)
    {
        $id_jadwal = $request->get('id_jadwal', 0);
        return view('operator.asesmen.hasil', compact('id_jadwal'));
    }

    public function portal(Request $request)
    {
        $id_jadwal = $request->get('id_jadwal', 0);
        return view('operator.asesmen.portal', compact('id_jadwal'));
    }

    public function qcSoal(Request $request)
    {
        $guruId = $request->get('guru_id', 0);
        $mapel  = $request->get('mapel', '');
        $kelas  = $request->get('kelas', '');
        $guruNama = $request->get('guru_nama', 'Guru');

        return view('operator.asesmen.qc_soal', compact('guruId', 'mapel', 'kelas', 'guruNama'));
    }

    public function monitoring()
    {
        return view('operator.asesmen.monitoring');
    }
}

