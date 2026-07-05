<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;







class ExamStudentApiController extends Controller
{
    private function jsonResponse($success, $message, $data = [])
    {
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data]);
    }

    public function handle(Request $request)
    {
        $action = $request->input('action', '');

        try {
            switch ($action) {

                
                
                
                
                case 'check_token':
                    $token = strtoupper(trim($request->input('token', '')));
                    $nis = trim($request->input('nis', ''));

                    
                    $jadwal = DB::connection('mysql_asesmen')
                        ->selectOne(
                            "SELECT * FROM jadwal_ujian WHERE token = ? AND tanggal_ujian = CURDATE() LIMIT 1",
                            [$token]
                        );

                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Token tidak valid atau jadwal tidak tersedia hari ini.');
                    }

                    
                    $siswa = DB::connection('mysql_auth')
                        ->selectOne(
                            "SELECT s.*, k.nama_kelas
                             FROM siswa s
                             LEFT JOIN kelas k ON k.id = s.kelas_id
                             WHERE s.nis = ? LIMIT 1",
                            [$nis]
                        );

                    if (!$siswa) {
                        return $this->jsonResponse(false, 'NIS tidak ditemukan dalam database siswa.');
                    }

                    
                    $kelasJadwal = trim($jadwal->kelas);
                    $kelasSiswa = trim($siswa->nama_kelas ?? '');

                    if ($kelasSiswa === '' || strtoupper($kelasSiswa) !== strtoupper($kelasJadwal)) {
                        return $this->jsonResponse(
                            false,
                            "Token tidak sesuai untuk kelas Anda.\n" .
                            "Token ini untuk Kelas {$kelasJadwal} — Mata Pelajaran: {$jadwal->mapel}.\n" .
                            "Kelas Anda: " . ($kelasSiswa ?: 'tidak diketahui') . ".\n" .
                            "Harap minta token yang sesuai kepada pengawas."
                        );
                    }

                    
                    $gate = DB::connection('mysql_asesmen')
                        ->selectOne("SELECT status_pintu_siswa FROM asesmen_config LIMIT 1");

                    if (!$gate || (int) $gate->status_pintu_siswa !== 1) {
                        return $this->jsonResponse(false, 'Gerbang ujian belum dibuka oleh operator. Tunggu instruksi pengawas.');
                    }

                    
                    if (strtoupper($jadwal->pengulangan ?? 'YA') === 'TIDAK') {
                        $sudahSubmit = DB::connection('mysql_asesmen')
                            ->selectOne(
                                "SELECT id_hasil FROM hasil_ujian WHERE id_siswa = ? AND id_jadwal = ? LIMIT 1",
                                [$siswa->user_id, $jadwal->id_jadwal]
                            );

                        if ($sudahSubmit) {
                            return $this->jsonResponse(false,
                                'Anda sudah mengerjakan ujian ini dan pengulangan tidak diizinkan. ' .
                                'Hubungi pengawas jika terdapat kesalahan.'
                            );
                        }
                    }

                    
                    session([
                        'exam_student_id' => $siswa->user_id,
                        'exam_nis'        => $nis,
                        'exam_token'      => $token,
                    ]);

                    return $this->jsonResponse(true, 'Token valid', [
                        'jadwal' => $jadwal,
                        'siswa'  => [
                            'nama'  => $siswa->nama,
                            'nis'   => $siswa->nis,
                            'kelas' => $kelasSiswa,
                        ],
                        'ujian' => [
                            'topik'            => $jadwal->mapel,
                            'durasi'           => $jadwal->durasi,
                            'sheetSoal'        => 'DYNAMIC',
                            'pengulangan'      => $jadwal->pengulangan ?? 'YA',
                            'tampilkanJawaban' => $jadwal->tampilkan_jawaban ?? 'TIDAK',
                            'tampilkanNilai'   => $jadwal->tampilkan_nilai ?? 'YA',
                            'modeSubmit'       => $jadwal->mode_submit ?? 'MANDIRI',
                        ],
                    ]);


                
                
                
                
                case 'get_exam_paper':
                    $token = $request->input('token', '');

                    $jadwal = DB::connection('mysql_asesmen')
                        ->selectOne(
                            "SELECT mapel, kelas FROM jadwal_ujian WHERE token = ? LIMIT 1",
                            [$token]
                        );

                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Invalid Session');
                    }

                    $soalList = DB::connection('mysql_asesmen')
                        ->select(
                            "SELECT id_soal, tipe_soal, konten_soal as soal,
                                    opsi_a as a, opsi_b as b, opsi_c as c, opsi_d as d, opsi_e as e, bobot
                             FROM bank_soal
                             WHERE mapel = ? AND kelas = ? AND status_soal = 'VALIDATED'
                             ORDER BY id_soal ASC",
                            [$jadwal->mapel, $jadwal->kelas]
                        );

                    
                    $soalIds = array_column($soalList, 'id_soal');
                    $assets = [];
                    if (!empty($soalIds)) {
                        $assetsRows = DB::connection('mysql_asesmen')
                            ->table('soal_assets')
                            ->whereIn('bank_soal_id', $soalIds)
                            ->get();
                        
                        foreach ($assetsRows as $row) {
                            if (!isset($assets[$row->bank_soal_id])) {
                                $assets[$row->bank_soal_id] = [];
                            }
                            $assets[$row->bank_soal_id][] = $row;
                        }
                    }

                    
                    foreach ($soalList as &$s) {
                        $s->assets = $assets[$s->id_soal] ?? [];
                    }

                    
                    $soal = collect($soalList)->shuffle()->values()->toArray();

                    return $this->jsonResponse(true, 'Questions fetched', [
                        'jadwal' => $jadwal,
                        'soal' => $soal,
                    ]);

                
                
                
                
                case 'submit_exam':
                    $siswaId = session('exam_student_id', 0);
                    if (!$siswaId) {
                        return $this->jsonResponse(false, 'Session Expired');
                    }

                    $token = $request->input('token', '');
                    $jawabanRaw = $request->input('jawaban', '[]');
                    $jawaban = json_decode($jawabanRaw, true);

                    
                    $jadwal = DB::connection('mysql_asesmen')
                        ->selectOne(
                            "SELECT id_jadwal, mapel, kelas FROM jadwal_ujian WHERE token = ? LIMIT 1",
                            [$token]
                        );

                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Invalid Token');
                    }

                    
                    $keys = DB::connection('mysql_asesmen')
                        ->select(
                            "SELECT id_soal, tipe_soal, kunci_jawaban, bobot
                             FROM bank_soal
                             WHERE mapel = ? AND kelas = ?",
                            [$jadwal->mapel, $jadwal->kelas]
                        );

                    $totalScore = 0;
                    $totalBobot = 0;
                    $keyMap = [];
                    foreach ($keys as $k) {
                        $keyMap[$k->id_soal] = $k;
                        $totalBobot += $k->bobot;
                    }

                    $countCorrect = 0;
                    $countWrong = 0;

                    foreach ($jawaban as $ans) {
                        $qId = $ans['id_soal'] ?? null;
                        if (!$qId || !isset($keyMap[$qId]))
                            continue;

                        $soalParams = $keyMap[$qId];
                        $kunci = strtoupper(trim($soalParams->kunci_jawaban));
                        $jawabUser = is_array($ans['jawaban'])
                            ? $ans['jawaban']
                            : strtoupper(trim($ans['jawaban']));

                        if (is_array($jawabUser)) {
                            $jawabUser = implode(',', $jawabUser);
                        }
                        $jawabUser = strtoupper($jawabUser);
                        $bobot = $soalParams->bobot;
                        $isCorrect = false;

                        
                        if (in_array($soalParams->tipe_soal, ['PG', 'ISIAN', 'BS', 'BENAR_SALAH'])) {

                            if (in_array($soalParams->tipe_soal, ['BS', 'BENAR_SALAH']) && is_array($ans['jawaban'])) {
                                $jawabAsString = implode(',', array_map('strtoupper', $ans['jawaban']));
                                if ($jawabAsString === $kunci)
                                    $isCorrect = true;
                            } else {
                                if ($jawabUser === $kunci)
                                    $isCorrect = true;
                            }

                            if ($isCorrect) {
                                $totalScore += $bobot;
                                $countCorrect++;
                            } else {
                                $countWrong++;
                            }

                            
                        } elseif (in_array($soalParams->tipe_soal, ['PGK', 'PG_KOMPLEKS'])) {

                            $kArr = array_map('trim', array_filter(explode(',', $kunci)));
                            $uArr = array_map('trim', array_filter(explode(',', $jawabUser)));

                            $correctCount = 0;
                            $wrongCount = 0;
                            foreach ($uArr as $u) {
                                if (in_array($u, $kArr))
                                    $correctCount++;
                                else
                                    $wrongCount++;
                            }

                            $totalCorrect = count($kArr);
                            $scoreRatio = ($totalCorrect > 0) ? ($correctCount / $totalCorrect) : 0;
                            $totalScore += ($bobot * $scoreRatio);

                            if ($scoreRatio == 1)
                                $countCorrect++;
                            else
                                $countWrong++;
                        }
                    }

                    
                    $finalScore = ($totalBobot > 0) ? ($totalScore / $totalBobot) * 100 : 0;

                    
                    DB::connection('mysql_asesmen')->insert(
                        "INSERT INTO hasil_ujian (id_siswa, id_jadwal, jawaban_user, skor_akhir, waktu_selesai)
                         VALUES (?, ?, ?, ?, NOW())",
                        [$siswaId, $jadwal->id_jadwal, $jawabanRaw, $finalScore]
                    );

                    return $this->jsonResponse(true, 'Exam submitted', [
                        'score' => $finalScore,
                        'benar' => $countCorrect,
                        'salah' => $countWrong,
                    ]);

                
                
                
                
                case 'getSoalWithKey':
                    $token = session('exam_token', '');
                    if (!$token) {
                        return $this->jsonResponse(false, 'Session Expired');
                    }

                    $jadwal = DB::connection('mysql_asesmen')
                        ->selectOne(
                            "SELECT mapel, kelas FROM jadwal_ujian WHERE token = ? LIMIT 1",
                            [$token]
                        );

                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Invalid Session');
                    }

                    $soalList = DB::connection('mysql_asesmen')
                        ->select(
                            "SELECT id_soal, tipe_soal, konten_soal as soal,
                                    opsi_a as a, opsi_b as b, opsi_c as c, opsi_d as d, opsi_e as e,
                                    kunci_jawaban as kunci
                             FROM bank_soal
                             WHERE mapel = ? AND kelas = ?
                             ORDER BY id_soal ASC",
                            [$jadwal->mapel, $jadwal->kelas]
                        );

                    $soalIds = array_column($soalList, 'id_soal');
                    $assets = [];
                    if (!empty($soalIds)) {
                        $assetsRows = DB::connection('mysql_asesmen')
                            ->table('soal_assets')
                            ->whereIn('bank_soal_id', $soalIds)
                            ->get();
                        
                        foreach ($assetsRows as $row) {
                            if (!isset($assets[$row->bank_soal_id])) {
                                $assets[$row->bank_soal_id] = [];
                            }
                            $assets[$row->bank_soal_id][] = $row;
                        }
                    }

                    foreach ($soalList as &$s) {
                        $s->assets = $assets[$s->id_soal] ?? [];
                    }

                    return $this->jsonResponse(true, 'Soal Key', ['soal' => $soalList]);

                
                
                
                case 'save_draft':
                    $siswaId = session('exam_student_id', 0);
                    $token = $request->input('token', '');
                    $jawabanDraft = $request->input('jawaban', '[]');

                    if (!$siswaId || !$token) {
                        return $this->jsonResponse(false, 'Unauthorized or Missing Token');
                    }

                    $jadwal = DB::connection('mysql_asesmen')
                        ->selectOne("SELECT id_jadwal FROM jadwal_ujian WHERE token = ? LIMIT 1", [$token]);

                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Invalid Schedule');
                    }

                    DB::connection('mysql_asesmen')->statement(
                        "INSERT INTO ujian_drafts (id_siswa, id_jadwal, jawaban_draft, last_sync) 
                         VALUES (?, ?, ?, NOW()) 
                         ON DUPLICATE KEY UPDATE jawaban_draft = VALUES(jawaban_draft), last_sync = NOW()",
                        [$siswaId, $jadwal->id_jadwal, $jawabanDraft]
                    );

                    return $this->jsonResponse(true, 'Draft saved');

                
                
                
                case 'get_draft':
                    $siswaId = session('exam_student_id', 0);
                    $token = $request->input('token', '');

                    if (!$siswaId || !$token) {
                        return $this->jsonResponse(false, 'Unauthorized');
                    }

                    $jadwal = DB::connection('mysql_asesmen')
                        ->selectOne("SELECT id_jadwal FROM jadwal_ujian WHERE token = ? LIMIT 1", [$token]);

                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Invalid Schedule');
                    }

                    $draft = DB::connection('mysql_asesmen')
                        ->selectOne("SELECT jawaban_draft FROM ujian_drafts WHERE id_siswa = ? AND id_jadwal = ? LIMIT 1", [$siswaId, $jadwal->id_jadwal]);

                    return $this->jsonResponse(true, 'Draft fetched', ['jawaban' => $draft ? $draft->jawaban_draft : '[]']);

                
                
                
                
                
                case 'check_submission_status':
                    $token = $request->input('token', session('exam_token', ''));
                    if (!$token) {
                        return $this->jsonResponse(false, 'Token tidak tersedia');
                    }

                    $jadwal = DB::connection('mysql_asesmen')
                        ->selectOne(
                            "SELECT id_jadwal, pengulangan, tampilkan_jawaban, tampilkan_nilai, mode_submit
                             FROM jadwal_ujian WHERE token = ? LIMIT 1",
                            [$token]
                        );

                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Jadwal tidak ditemukan');
                    }

                    $siswaId = session('exam_student_id', 0);
                    $sudahSubmit = false;
                    $hasilData   = null;

                    if ($siswaId) {
                        $hasilRow = DB::connection('mysql_asesmen')
                            ->selectOne(
                                "SELECT id_hasil, skor_akhir, waktu_selesai
                                 FROM hasil_ujian WHERE id_siswa = ? AND id_jadwal = ? LIMIT 1",
                                [$siswaId, $jadwal->id_jadwal]
                            );
                        if ($hasilRow) {
                            $sudahSubmit = true;
                            $hasilData   = $hasilRow;
                        }
                    }

                    return $this->jsonResponse(true, 'Status checked', [
                        'sudah_submit'      => $sudahSubmit,
                        'hasil'             => $hasilData,
                        'pengulangan'       => $jadwal->pengulangan ?? 'YA',
                        'tampilkan_jawaban' => $jadwal->tampilkan_jawaban ?? 'TIDAK',
                        'tampilkan_nilai'   => $jadwal->tampilkan_nilai ?? 'YA',
                        'mode_submit'       => $jadwal->mode_submit ?? 'MANDIRI',
                    ]);

                default:
                    return $this->jsonResponse(false, 'Action not recognized: ' . $action);

            }
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Error: ' . $e->getMessage());
        }
    }
}
