<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TugasPengumpulanController extends Controller
{
    


    public function index(Request $request)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');

        if (!$mapel_id || !$kelas_id) {
            return redirect()->route('guru.dashboard')->with('error', 'Silakan pilih sesi mengajar terlebih dahulu.');
        }

        
        $all_tugas = DB::connection('mysql_apps')->table('tugas')
            ->where('mapel_id', $mapel_id)
            ->where('kelas_id', $kelas_id)
            ->whereNotNull('deskripsi')
            ->orderBy('created_at', 'desc')
            ->get();

        $tugas_aktif = [];
        $tugas_draft = [];

        
        foreach ($all_tugas as $tugas) {
            $tugas->total_dikumpulkan = DB::connection('mysql_apps')->table('tugas_pengumpulan')
                ->where('tugas_id', $tugas->id)
                ->count();

            if ($tugas->status === 'aktif') {
                $tugas_aktif[] = $tugas;
            } else {
                $tugas_draft[] = $tugas;
            }
        }

        return view('guru.ruang_tugas.index', compact('tugas_aktif', 'tugas_draft'));
    }

    


    public function store(Request $request)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'batas_waktu' => 'nullable|date',
        ]);

        DB::connection('mysql_apps')->table('tugas')->insert([
            'mapel_id' => $mapel_id,
            'kelas_id' => $kelas_id,
            'judul' => trim($request->judul),
            'deskripsi' => trim($request->deskripsi),
            'link_lampiran' => $request->link_lampiran ? trim($request->link_lampiran) : null,
            'batas_waktu' => $request->batas_waktu,
            'is_auto_close' => $request->has('is_auto_close') ? 1 : 0,
            'allow_upload' => $request->has('allow_upload') ? 1 : 0,
            'status' => 'aktif',
            'created_at' => now()
        ]);

        return redirect()->route('guru.ruang_tugas.index')->with('success', 'Tugas berhasil diterbitkan.');
    }

    


    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        DB::connection('mysql_apps')->table('tugas')
            ->where('id', $id)
            ->where('mapel_id', session('mapel_id'))
            ->where('kelas_id', session('kelas_id'))
            ->update([
                'judul' => trim($request->judul),
                'deskripsi' => trim($request->deskripsi),
                'link_lampiran' => $request->link_lampiran ? trim($request->link_lampiran) : null,
                'batas_waktu' => $request->batas_waktu,
                'is_auto_close' => $request->has('is_auto_close') ? 1 : 0,
                'allow_upload' => $request->has('allow_upload') ? 1 : 0,
            ]);

        return redirect()->route('guru.ruang_tugas.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    


    public function toggleStatus(Request $request)
    {
        $id = $request->id;
        $current = $request->current_status;
        $new_status = ($current === 'aktif') ? 'draft' : 'aktif';

        $updated = DB::connection('mysql_apps')->table('tugas')
            ->where('id', $id)
            ->where('mapel_id', session('mapel_id'))
            ->where('kelas_id', session('kelas_id'))
            ->update(['status' => $new_status]);

        if ($updated) {
            $msg = ($new_status === 'aktif') ? 'Tugas diaktifkan (Muncul di Siswa).' : 'Tugas diarsipkan ke Draft (Disembunyikan).';
            return redirect()->route('guru.ruang_tugas.index')->with('success', $msg);
        }

        return redirect()->route('guru.ruang_tugas.index')->with('error', 'Gagal merubah status.');
    }

    


    public function show($id)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');

        $tugas = DB::connection('mysql_apps')->table('tugas')
            ->where('id', $id)
            ->where('mapel_id', $mapel_id)
            ->where('kelas_id', $kelas_id)
            ->first();

        if (!$tugas) {
            return response()->json(['status' => 'error', 'message' => 'Tugas tidak ditemukan']);
        }

        
        
        
        $submissions = DB::connection('mysql_auth')->table('siswa as s')
            ->select(
                's.id as siswa_id',      
                's.user_id as uid',       
                's.nama',
                's.nis',
                'tp.id as submission_id',
                'tp.metode',
                'tp.link_pengumpulan',
                'tp.file_type',
                'tp.catatan_siswa',
                'tp.dikumpulkan_pada',
                'tp.nilai',
                'tp.feedback_guru'
            )
            ->leftJoin(config('database.connections.mysql_apps.database') . '.tugas_pengumpulan as tp', function ($join) use ($id) {
                $join->on('s.user_id', '=', 'tp.siswa_id')  
                     ->where('tp.tugas_id', '=', $id);
            })
            ->where('s.kelas_id', $kelas_id)
            ->orderBy('s.nama', 'asc')
            ->get();

        return view('guru.ruang_tugas.show', compact('tugas', 'submissions'));
    }

    


    public function destroy($id)
    {
        $tugas = DB::connection('mysql_apps')->table('tugas')
            ->where('id', $id)
            ->where('mapel_id', session('mapel_id'))
            ->where('kelas_id', session('kelas_id'))
            ->first();

        if (!$tugas) {
            return redirect()->route('guru.ruang_tugas.index')->with('error', 'Tugas tidak valid.');
        }

        
        $submissions = DB::connection('mysql_apps')->table('tugas_pengumpulan')
            ->where('tugas_id', $id)
            ->where('metode', 'file')
            ->get();

        foreach ($submissions as $sub) {
            if (!empty($sub->link_pengumpulan)) {
                $path = base_path('lms/uploads/tugas/' . $sub->link_pengumpulan);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        }

        
        DB::connection('mysql_apps')->table('tugas_pengumpulan')->where('tugas_id', $id)->delete();
        DB::connection('mysql_apps')->table('tugas')->where('id', $id)->delete();

        return redirect()->route('guru.ruang_tugas.index')->with('success', 'Tugas dan seluruh data pengumpulannya berhasil dihapus.');
    }

    



    public function saveNilai(Request $request, $id)
    {
        $request->validate([
            'siswa_id' => 'required|integer',
            'nilai'    => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string'
        ]);

        try {
            
            $exists = DB::connection('mysql_apps')->table('tugas_pengumpulan')
                ->where('tugas_id', $id)
                ->where('siswa_id', $request->siswa_id)
                ->exists();

            if (!$exists) {
                
                $insertData = [
                    'tugas_id'         => $id,
                    'siswa_id'         => $request->siswa_id,
                    'metode'           => 'link',
                    'link_pengumpulan' => '',
                    'catatan_siswa'    => 'Dinilai langsung oleh guru (manual)',
                    'dikumpulkan_pada' => now(),
                    'nilai'            => $request->nilai,
                    'feedback_guru'    => $request->feedback
                ];

                $kkm = 75;
                if ((int) $request->nilai < $kkm) {
                    $insertData['status_revisi'] = 'revisi';
                    $insertData['catatan_revisi'] = $request->feedback ?? 'Nilai di bawah KKM. Harap perbaiki dan kumpulkan ulang.';
                } else {
                    $insertData['status_revisi'] = null;
                    $insertData['catatan_revisi'] = null;
                }

                DB::connection('mysql_apps')->table('tugas_pengumpulan')->insert($insertData);
            } else {
                
                
                $updateData = [
                    'nilai'         => $request->nilai,
                    'feedback_guru' => $request->feedback,
                ];

                $kkm = 75;
                if ((int) $request->nilai < $kkm) {
                    $updateData['status_revisi'] = 'revisi';
                    $updateData['catatan_revisi'] = $request->feedback ?? 'Nilai di bawah KKM. Harap perbaiki dan kumpulkan ulang.';
                } else {
                    $updateData['status_revisi'] = null;
                    $updateData['catatan_revisi'] = null;
                }

                DB::connection('mysql_apps')->table('tugas_pengumpulan')
                    ->where('tugas_id', $id)
                    ->where('siswa_id', $request->siswa_id)
                    ->update($updateData);
            }

            
            $tugasOnline = DB::connection('mysql_apps')->table('tugas')->find($id);
            if ($tugasOnline) {
                
                if (is_null($tugasOnline->tugas_ke)) {
                    $maxTugasKe = DB::connection('mysql_apps')->table('tugas')
                        ->where('mapel_id', $tugasOnline->mapel_id)
                        ->where('kelas_id', $tugasOnline->kelas_id)
                        ->whereNotNull('tugas_ke')
                        ->max('tugas_ke');
                    $nextKe = $maxTugasKe ? $maxTugasKe + 1 : 1;

                    DB::connection('mysql_apps')->table('tugas')
                        ->where('id', $id)
                        ->update([
                            'tugas_ke' => $nextKe,
                            'tanggal' => date('Y-m-d'),
                            'tipe_tugas' => 'Individual',
                            'kategori_asesmen' => 'Formatif',
                            'teknik_penilaian' => 'Lainnya',
                            'pokok_bahasan' => $tugasOnline->judul
                        ]);
                }

                $siswaRow = DB::connection('mysql_auth')->table('siswa')
                    ->where('user_id', $request->siswa_id)
                    ->first(['id']);

                if ($siswaRow) {
                    DB::connection('mysql_apps')->table('nilai_tugas')->updateOrInsert(
                        ['tugas_id' => $id, 'siswa_id' => $siswaRow->id],
                        ['nilai' => $request->nilai, 'updated_at' => now()]
                    );
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Nilai berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()]);
        }
    }

    




    public function rekapMasukkan(Request $request, $id)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');

        try {
            $tugasOnline = DB::connection('mysql_apps')->table('tugas')
                ->where('id', $id)
                ->where('mapel_id', $mapel_id)
                ->where('kelas_id', $kelas_id)
                ->first();

            if (!$tugasOnline) {
                return response()->json(['status' => 'error', 'message' => 'Tugas tidak ditemukan']);
            }

            
            $submissions = DB::connection('mysql_apps')->table('tugas_pengumpulan')
                ->where('tugas_id', $id)
                ->whereNotNull('nilai')
                ->get(['siswa_id', 'nilai']);

            if ($submissions->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Belum ada siswa yang sudah dinilai untuk tugas ini.']);
            }

            
            if (is_null($tugasOnline->tugas_ke)) {
                $maxTugasKe = DB::connection('mysql_apps')->table('tugas')
                    ->where('mapel_id', $mapel_id)
                    ->where('kelas_id', $kelas_id)
                    ->whereNotNull('tugas_ke')
                    ->max('tugas_ke');
                $nextKe = $maxTugasKe ? $maxTugasKe + 1 : 1;

                DB::connection('mysql_apps')->table('tugas')
                    ->where('id', $id)
                    ->update([
                        'tugas_ke' => $nextKe,
                        'tanggal' => date('Y-m-d'),
                        'tipe_tugas' => 'Individual',
                        'kategori_asesmen' => 'Formatif',
                        'teknik_penilaian' => 'Lainnya',
                        'pokok_bahasan' => $tugasOnline->judul
                    ]);
            }

            
            
            
            
            $inserted = 0;
            foreach ($submissions as $sub) {
                
                $siswaRow = DB::connection('mysql_auth')->table('siswa')
                    ->where('user_id', $sub->siswa_id)
                    ->first(['id']);

                if (!$siswaRow) continue;

                DB::connection('mysql_apps')->table('nilai_tugas')->updateOrInsert(
                    ['tugas_id' => $id, 'siswa_id' => $siswaRow->id],
                    ['nilai' => $sub->nilai, 'updated_at' => now()]
                );
                $inserted++;
            }

            
            DB::connection('mysql_apps')->table('tugas')
                ->where('id', $id)
                ->update(['is_finalized' => 1]);

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil memasukkan {$inserted} nilai ke Rekap Tugas dengan judul \"" . $tugasOnline->judul . "\". Ruang tugas telah dikunci."
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    


    public function exportZip(Request $request, $id)
    {
        try {
            $tugas = DB::connection('mysql_apps')->table('tugas')->where('id', $id)->first();
            if (!$tugas) {
                return back()->with('error', 'Tugas tidak ditemukan.');
            }

            
            $submissions = DB::connection('mysql_apps')->table('tugas_pengumpulan as tp')
                ->join(config('database.connections.mysql_auth.database') . '.siswa as s', 'tp.siswa_id', '=', 's.user_id')
                ->where('tp.tugas_id', $id)
                ->where('tp.metode', 'file')
                ->whereNotNull('tp.link_pengumpulan')
                ->where('tp.link_pengumpulan', '!=', '')
                ->select('tp.link_pengumpulan', 's.nis', 's.nama_lengkap')
                ->get();

            if ($submissions->isEmpty()) {
                return back()->with('error', 'Tidak ada file pengumpulan untuk diekspor.');
            }

            if (!class_exists('ZipArchive')) {
                return back()->with('error', 'ZipArchive tidak tersedia di server ini.');
            }

            $zipFileName = 'tugas_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $tugas->judul) . '_export.zip';
            $zipPath     = sys_get_temp_dir() . '/' . $zipFileName;

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'Gagal membuat file ZIP.');
            }

            $addedCount = 0;
            foreach ($submissions as $sub) {
                $filePath = public_path('lms/uploads/tugas/' . $sub->link_pengumpulan);
                if (file_exists($filePath)) {
                    $ext         = pathinfo($sub->link_pengumpulan, PATHINFO_EXTENSION);
                    $safeNis     = preg_replace('/[^A-Za-z0-9]/', '', $sub->nis ?? 'unknown');
                    $safeName    = preg_replace('/[^A-Za-z0-9_ ]/', '', $sub->nama_lengkap ?? 'Siswa');
                    $inZipName   = "{$safeNis}_{$safeName}.{$ext}";
                    $zip->addFile($filePath, $inZipName);
                    $addedCount++;
                }
            }
            $zip->close();

            if ($addedCount === 0) {
                @unlink($zipPath);
                return back()->with('error', 'File siswa tidak ditemukan di server.');
            }

            return response()->download($zipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal ekspor ZIP: ' . $e->getMessage());
        }
    }

    


    public function setRevisi(Request $request, $id)
    {
        $request->validate([
            'siswa_id'       => 'required|integer',
            'catatan_revisi' => 'nullable|string|max:1000',
            'action'         => 'required|in:set,clear',
        ]);

        try {
            $updateData = $request->action === 'set'
                ? ['status_revisi' => 'revisi', 'catatan_revisi' => $request->catatan_revisi ?? 'Harap perbaiki dan kumpulkan ulang.']
                : ['status_revisi' => null,     'catatan_revisi' => null];

            $affected = DB::connection('mysql_apps')->table('tugas_pengumpulan')
                ->where('tugas_id', $id)
                ->where('siswa_id', $request->siswa_id)
                ->update($updateData);

            if ($affected === 0) {
                return response()->json(['status' => 'error', 'message' => 'Data pengumpulan tidak ditemukan.']);
            }

            $msg = $request->action === 'set'
                ? 'Status revisi berhasil ditetapkan. Siswa dapat mengumpulkan ulang.'
                : 'Status revisi berhasil dihapus.';

            return response()->json(['status' => 'success', 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
}
