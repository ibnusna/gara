<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    


    public function index(Request $request)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        $from = $request->query('from', date('Y-m-01'));
        $to = $request->query('to', date('Y-m-d'));

        $agendaList = DB::connection('mysql_apps')->table('agenda_harian')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->whereBetween('tanggal', [$from, $to])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc')
            ->get();

        
        $siswaMap = DB::connection('mysql_auth')->table('siswa')
            ->where('kelas_id', $kelasId)
            ->pluck('nama', 'id');

        $agendaList = $agendaList->map(function ($a) use ($siswaMap) {
            $ids = json_decode($a->siswa_tidak_hadir ?? '[]', true) ?? [];
            $names = collect($ids)->map(fn($id) => $siswaMap[$id] ?? "ID:{$id}")->implode(', ');
            $a->siswa_tidak_hadir_names = $names ?: '-';
            $a->siswa_tidak_hadir_count = count($ids);
            return $a;
        });

        return view('guru.agenda.index', compact('agendaList', 'from', 'to'))
            ->with(['active_menu' => 'agenda', 'use_datatables' => true]);
    }

    


    public function update(Request $request, int $id)
    {
        $request->validate([
            'rencana_kegiatan' => 'required|string|max:2000',
            'catatan_pelaksanaan' => 'nullable|string|max:2000',
        ]);

        DB::connection('mysql_apps')->table('agenda_harian')
            ->where('id', $id)
            ->update([
                'rencana_kegiatan' => $request->rencana_kegiatan,
                'catatan_pelaksanaan' => $request->catatan_pelaksanaan,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Agenda berhasil diperbarui.');
    }

    


    public function destroy(int $id)
    {
        DB::connection('mysql_apps')->table('agenda_harian')->where('id', $id)->delete();
        return back()->with('success', 'Agenda berhasil dihapus.');
    }
}
