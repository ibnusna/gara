<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    


    public function index(Request $request)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');
        $siswa_id = auth()->user()->id; 

        if (!$mapel_id || !$kelas_id) {
            return redirect()->route('student.pilih-mapel')->with('error', 'Silakan pilih mata pelajaran terlebih dahulu.');
        }

        date_default_timezone_set('Asia/Jakarta');

        try {
            $sql = "SELECT t.*, 
                    tp.id as submission_id, tp.link_pengumpulan, tp.nilai, tp.catatan_siswa, tp.dikumpulkan_pada, tp.feedback_guru, tp.metode
                    FROM tugas t
                    LEFT JOIN tugas_pengumpulan tp ON t.id = tp.tugas_id AND tp.siswa_id = ?
                    WHERE t.mapel_id = ? AND t.kelas_id = ? AND t.status = 'aktif' AND t.deskripsi IS NOT NULL
                    ORDER BY t.created_at DESC";


            $all_tugas = DB::connection('mysql_apps')->select($sql, [$siswa_id, $mapel_id, $kelas_id]);

        } catch (\Exception $e) {
            $all_tugas = [];
        }

        $tugas_aktif = [];
        $tugas_selesai = [];
        $tugas_terlewat = [];
        $current_time = time();

        foreach ($all_tugas as $tInfo) {
            $t = (array) $tInfo;
            $deadline_ts = $t['batas_waktu'] ? strtotime($t['batas_waktu']) : null;
            $is_submitted = !empty($t['submission_id']);

            if ($is_submitted) {
                
                $tugas_selesai[] = $t;
            } else {
                
                if ($deadline_ts && $current_time > $deadline_ts) {
                    
                    $tugas_terlewat[] = $t;
                } else {
                    
                    $tugas_aktif[] = $t;
                }
            }
        }

        return view('siswa.tugas.index', compact('tugas_aktif', 'tugas_selesai', 'tugas_terlewat'));
    }

    


    public function submit(Request $request)
    {
        $siswa_id = auth()->user()->id;
        $mapel_id = session('mapel_id');
        $tugas_id = $request->input('tugas_id');
        $metode = $request->input('metode', 'link');
        $catatan = $request->input('catatan', '');

        
        if ($metode === 'manual') {
            DB::connection('mysql_apps')->table('tugas_pengumpulan')->updateOrInsert(
                ['tugas_id' => $tugas_id, 'siswa_id' => $siswa_id],
                [
                    'link_pengumpulan' => '-',
                    'catatan_siswa' => 'Sudah dibaca',
                    'metode' => 'link',
                    'dikumpulkan_pada' => now()
                ]
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Tugas ditandai sudah dibaca',
                'data' => [
                    'link' => '-',
                    'catatan' => 'Sudah dibaca',
                    'metode' => 'manual'
                ]
            ]);
        }

        $link_final = '';

        if ($metode === 'link') {
            $link_final = $request->input('link_pengumpulan');
        } elseif ($metode === 'file') {
            if ($request->hasFile('file_upload')) {
                $file = $request->file('file_upload');

                
                if ($file->getSize() >= 5 * 1024 * 1024) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Ukuran file melebihi batas maksimal 5 MB. File Anda: ' . round($file->getSize() / (1024 * 1024), 2) . ' MB.'
                    ]);
                }

                
                $allowedExts  = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                $allowedMimes = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg', 'image/jpg', 'image/png',
                ];
                $ext      = strtolower($file->getClientOriginalExtension());
                $fileMime = $file->getMimeType();

                if (!in_array($ext, $allowedExts) || !in_array($fileMime, $allowedMimes)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Format file tidak diizinkan. Hanya PDF, DOC, DOCX, JPG, dan PNG yang diterima.'
                    ]);
                }

                
                if (!file_exists(public_path('lms/uploads/tugas'))) {
                    mkdir(public_path('lms/uploads/tugas'), 0755, true);
                }
                $filename = time() . '_' . $siswa_id . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('lms/uploads/tugas'), $filename);
                $link_final = $filename;
            } else {
                
                
                $existing = DB::connection('mysql_apps')->table('tugas_pengumpulan')
                    ->where('tugas_id', $tugas_id)
                    ->where('siswa_id', $siswa_id)
                    ->first();
                if ($existing && $existing->metode === 'file') {
                    $link_final = $existing->link_pengumpulan;
                } else {
                    return response()->json(['status' => 'error', 'message' => 'File wajib diupload']);
                }
            }
        }

        DB::connection('mysql_apps')->table('tugas_pengumpulan')->updateOrInsert(
            ['tugas_id' => $tugas_id, 'siswa_id' => $siswa_id],
            [
                'link_pengumpulan' => $link_final,
                'catatan_siswa' => $catatan,
                'metode' => $metode,
                'dikumpulkan_pada' => now()
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil disimpan',
            'data' => [
                'link' => $link_final,
                'catatan' => $catatan,
                'metode' => $metode
            ]
        ]);
    }

    


    public function deleteSubmission(Request $request)
    {
        $siswa_id = auth()->user()->id;
        $tugas_id = $request->input('tugas_id');

        $submission = DB::connection('mysql_apps')->table('tugas_pengumpulan')
            ->where('tugas_id', $tugas_id)
            ->where('siswa_id', $siswa_id)
            ->first();

        if ($submission) {
            
            if ($submission->nilai !== null) {
                return response()->json(['status' => 'error', 'message' => 'Tugas sudah dinilai, tidak dapat dibatalkan']);
            }

            
            if ($submission->metode === 'file' && !empty($submission->link_pengumpulan)) {
                Storage::disk('public')->delete('tugas_siswa/' . $submission->link_pengumpulan);
            }

            DB::connection('mysql_apps')->table('tugas_pengumpulan')
                ->where('id', $submission->id)
                ->delete();

            return response()->json(['status' => 'success', 'message' => 'Pengumpulan berhasil dibatalkan']);
        }

        return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    }
}
