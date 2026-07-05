<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AppSetting;

class LaporanController extends Controller
{
    public function index()
    {
        $authDb = config('database.connections.mysql_auth.database');
        $appsDb = config('database.connections.mysql_apps.database');
        $asesmenDb = config('database.connections.asesmen_gara.database', 'asesmen_gara');

        
        
        
        $jurnalMengajar = DB::connection('mysql_apps')
            ->table('agenda_harian as ah')
            ->join('teaching_assignments as ta', function ($join) {
                $join->on('ah.mapel_id', '=', 'ta.subject_id')
                    ->on('ah.kelas_id', '=', 'ta.class_id');
            })
            ->join("{$authDb}.guru as g", 'ta.teacher_id', '=', 'g.id')
            ->join("{$authDb}.kelas as k", 'ah.kelas_id', '=', 'k.id')
            ->join("{$authDb}.mata_pelajaran as m", 'ah.mapel_id', '=', 'm.id')
            ->select(
                'ah.id',
                'ah.tanggal',
                'ah.jam',
                'ah.rencana_kegiatan',
                'ah.catatan_pelaksanaan',
                'ah.siswa_tidak_hadir',
                'g.nama_lengkap as nama_guru',
                'g.nip',
                'k.nama_kelas',
                'm.nama_mapel'
            )
            ->orderBy('ah.tanggal', 'desc')
            ->orderBy('g.nama_lengkap')
            ->get();

        
        
        
        
        
        $guruList = DB::connection('mysql_auth')
            ->table('guru')
            ->select('id', 'nama_lengkap as nama', 'nip')
            ->orderBy('nama_lengkap')
            ->get();

        $kelengkapanAjar = collect();

        
        $allBankSoal = DB::connection('asesmen_gara')
            ->table('bank_soal')
            ->select('id_guru', 'mapel', 'status_soal')
            ->get()
            ->groupBy('id_guru');

        
        $mapelNames = DB::connection('mysql_apps')
            ->table('mata_pelajaran')
            ->pluck('nama_mapel', 'id'); 

        foreach ($guruList as $guru) {
            
            $guruMapels = DB::connection('mysql_apps')
                ->table('teaching_assignments as ta')
                ->join("{$authDb}.mata_pelajaran as m", 'ta.subject_id', '=', 'm.id')
                ->where('ta.teacher_id', $guru->id)
                ->select('m.id as mapel_id', 'm.nama_mapel', 'ta.class_id as kelas_id')
                ->get();

            if ($guruMapels->isEmpty()) {
                continue;
            }

            
            $mapelGroup = $guruMapels->groupBy('mapel_id');

            foreach ($mapelGroup as $mapelId => $assignments) {
                $namaMapel = $assignments->first()->nama_mapel;
                $kelasIds = $assignments->pluck('kelas_id')->unique()->toArray();

                
                
                $rppCount = DB::connection('mysql_apps')
                    ->table('rpp_materi')
                    ->where('mapel_id', $mapelId)
                    ->whereIn('kelas_id', $kelasIds)
                    ->count();

                
                $soalGuru = $allBankSoal->get($guru->id, collect())
                    ->filter(fn($s) => $s->mapel === $namaMapel);

                $validated = $soalGuru->where('status_soal', 'VALIDATED')->count();
                $draft = $soalGuru->where('status_soal', 'DRAFT')->count();
                $submitted = $soalGuru->where('status_soal', 'SUBMITTED')->count();

                $keterangan = ($rppCount > 0 && $validated > 0) ? 'Lengkap' : 'Perlu Perbaikan';

                $kelengkapanAjar->push((object) [
                    'nama_guru' => $guru->nama,
                    'nip' => $guru->nip,
                    'nama_mapel' => $namaMapel,
                    'rpp_count' => $rppCount,
                    'soal_validated' => $validated,
                    'soal_draft' => $draft,
                    'soal_submitted' => $submitted,
                    'keterangan' => $keterangan,
                ]);
            }
        }

        
        
        
        

        
        $kelasData = DB::connection('mysql_auth')
            ->table('kelas')
            ->orderBy('nama_kelas')
            ->get();

        $rekapAkademik = collect();

        foreach ($kelasData as $kelas) {
            
            $siswaDiKelas = DB::connection('mysql_auth')
                ->table('siswa')
                ->where('kelas_id', $kelas->id)
                ->pluck('id')
                ->toArray();

            if (empty($siswaDiKelas)) {
                continue;
            }

            $jumlahSiswa = count($siswaDiKelas);

            
            $presensi = DB::connection('mysql_apps')
                ->table('absensi_detail')
                ->whereIn('siswa_id', $siswaDiKelas)
                ->selectRaw('SUM(CASE WHEN status = "H" THEN 1 ELSE 0 END) as hadir')
                ->selectRaw('SUM(CASE WHEN status = "S" THEN 1 ELSE 0 END) as sakit')
                ->selectRaw('SUM(CASE WHEN status = "I" THEN 1 ELSE 0 END) as izin')
                ->selectRaw('SUM(CASE WHEN status = "A" OR status = "T" THEN 1 ELSE 0 END) as alpa')
                ->first();

            
            $totalPertemuan = DB::connection('mysql_apps')
                ->table('absensi')
                ->where('kelas_id', $kelas->id)
                ->count();

            
            $rataTugas = DB::connection('mysql_apps')
                ->table('nilai_tugas')
                ->whereIn('siswa_id', $siswaDiKelas)
                ->avg('nilai');

            
            $rataAsesmen = 0;
            if (!empty($siswaDiKelas)) {
                try {
                    $rataAsesmen = DB::connection('asesmen_gara')
                        ->table('hasil_ujian')
                        ->whereIn('id_siswa', $siswaDiKelas)
                        ->avg('skor_akhir');
                } catch (\Exception $e) {
                    $rataAsesmen = 0;
                }
            }

            
            $totalAbsensi = ($presensi->hadir ?? 0) + ($presensi->sakit ?? 0)
                + ($presensi->izin ?? 0) + ($presensi->alpa ?? 0);
            $pctHadir = $totalAbsensi > 0
                ? round(($presensi->hadir / $totalAbsensi) * 100, 1)
                : 0;

            $rekapAkademik->push((object) [
                'nama_kelas' => $kelas->nama_kelas,
                'jumlah_siswa' => $jumlahSiswa,
                'total_pertemuan' => $totalPertemuan,
                'hadir' => $presensi->hadir ?? 0,
                'sakit' => $presensi->sakit ?? 0,
                'izin' => $presensi->izin ?? 0,
                'alpa' => $presensi->alpa ?? 0,
                'pct_hadir' => $pctHadir,
                'rata_tugas' => round($rataTugas ?? 0, 1),
                'rata_asesmen' => round($rataAsesmen ?? 0, 1),
            ]);
        }

        return view('kepsek.laporan.index', compact('jurnalMengajar', 'kelengkapanAjar', 'rekapAkademik'));
    }

    


    public function export()
    {
        return response()->json(['success' => false, 'message' => 'Gunakan halaman laporan untuk export.']);
    }

    


    public function exportPdf()
    {
        
        $type  = request('type', 'jurnal'); 

        $authDb   = config('database.connections.mysql_auth.database');
        $appsDb   = config('database.connections.mysql_apps.database');

        $sekolahNama  = \App\Models\AppSetting::where('setting_key', 'sekolah_nama')->value('setting_value') ?? 'GARUDA AKADEMI';
        $tahunAjaran  = \App\Models\AppSetting::where('setting_key', 'tahun_ajaran')->value('setting_value') ?? date('Y') . '/' . (date('Y') + 1);
        $semester     = \App\Models\AppSetting::where('setting_key', 'semester')->value('setting_value') ?? 'Ganjil';
        $tanggalCetak = now()->translatedFormat('d F Y');

        $title = match($type) {
            'perangkat' => 'Laporan Kelengkapan Perangkat Ajar',
            'presensi'  => 'Rekapitulasi Presensi & Nilai Akademik',
            default     => 'Laporan Supervisi Jurnal Mengajar Guru',
        };

        
        switch ($type) {
            case 'perangkat':
                $colsHtml = '<th>No</th><th>Nama Guru</th><th>NIP</th><th>Mata Pelajaran</th><th>Modul/Materi (RPP)</th><th>Bank Soal Valid</th><th>Keterangan</th>';
                $rows     = collect(); 
                $rowsHtml = '<tr><td colspan="7" style="text-align:center">Gunakan tombol PDF di halaman Laporan untuk export dengan data lengkap.</td></tr>';
                break;
            case 'presensi':
                $colsHtml = '<th>No</th><th>Kelas</th><th>Jml Siswa</th><th>H</th><th>S</th><th>I</th><th>A</th><th>% Hadir</th><th>Rata Tugas</th><th>Rata Asesmen</th>';
                $rowsHtml = '<tr><td colspan="10" style="text-align:center">Gunakan tombol PDF di halaman Laporan untuk export dengan data lengkap.</td></tr>';
                break;
            default: 
                $colsHtml = '<th>No</th><th>Tanggal</th><th>Guru</th><th>Kelas &amp; Mapel</th><th>Pokok Bahasan</th><th>Catatan</th><th>Siswa Absen</th>';
                $rowsHtml = '<tr><td colspan="7" style="text-align:center">Gunakan tombol PDF di halaman Laporan untuk export dengan data lengkap.</td></tr>';
        }

        return view('kepsek.laporan.pdf', compact(
            'title', 'sekolahNama', 'tahunAjaran', 'semester',
            'tanggalCetak', 'colsHtml', 'rowsHtml'
        ));
    }

    


    public function exportExcel()
    {
        return response()->json(['success' => false, 'message' => 'Fitur export Excel segera hadir.']);
    }
}
