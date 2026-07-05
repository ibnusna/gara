<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AsesmenConfig;
use App\Models\JadwalUjian;
use App\Models\BankSoal;
use App\Models\HasilUjian;

class AsesmenApiController extends Controller
{
    
    private function jsonResponse($success, $message, $data = [])
    {
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data]);
    }

    public function handle(Request $request)
    {
        $action = $request->input('action');
        $operatorId = auth()->id() ?? 1;

        try {
            switch ($action) {
                

                case 'get_gate_status':
                    $config = AsesmenConfig::orderBy('updated_at', 'desc')->first();
                    return $this->jsonResponse(true, 'Data fetched', $config);

                case 'toggle_gate':
                    $status = (int) $request->input('status'); 
                    $target = $request->input('target', 'guru'); 

                    $config = AsesmenConfig::first();
                    $column = ($target === 'siswa') ? 'status_pintu_siswa' : 'status_pintu';

                    if ($config) {
                        $config->$column = $status;
                        $config->opened_by = $operatorId;
                        $config->updated_at = now();
                        $config->save();
                    } else {
                        AsesmenConfig::create([
                            'status_pintu' => ($target === 'guru') ? $status : 0,
                            'status_pintu_siswa' => ($target === 'siswa') ? $status : 0,
                            'opened_by' => $operatorId,
                            'jenis_asesmen' => 'ASTS',
                            'updated_at' => now()
                        ]);
                    }
                    return $this->jsonResponse(true, 'Gate status updated');

                case 'save_config':
                case 'update_jenis_asesmen':
                    $jenis = $request->input('jenis', $request->input('jenis_asesmen'));
                    if (!in_array($jenis, ['ASTS', 'ASAS'])) {
                        return $this->jsonResponse(false, 'Jenis asesmen tidak valid');
                    }

                    $config = AsesmenConfig::first();
                    if ($config) {
                        $config->jenis_asesmen = $jenis;
                        $config->opened_by = $operatorId;
                        $config->updated_at = now();
                        $config->save();
                    } else {
                        AsesmenConfig::create([
                            'jenis_asesmen' => $jenis,
                            'status_pintu' => 0,
                            'status_pintu_siswa' => 0,
                            'opened_by' => $operatorId,
                            'updated_at' => now()
                        ]);
                    }
                    return $this->jsonResponse(true, 'Jenis asesmen berhasil diubah ke ' . $jenis);

                case 'get_jadwal_list':
                    $sql = "SELECT j.*,
                                CASE
                                  WHEN j.token IS NULL THEN 'belum_token'
                                  WHEN NOW() < CONCAT(j.tanggal_ujian,' ',j.jam_mulai) THEN 'akan_datang'
                                  WHEN NOW() BETWEEN CONCAT(j.tanggal_ujian,' ',j.jam_mulai)
                                       AND CONCAT(j.tanggal_ujian,' ',j.jam_selesai) THEN 'berlangsung'
                                  ELSE 'selesai'
                                END AS status_waktu
                            FROM jadwal_ujian j
                            ORDER BY j.tanggal_ujian DESC, j.jam_mulai ASC";
                    $data = DB::connection('mysql_asesmen')->select($sql);
                    return $this->jsonResponse(true, 'Jadwal list', $data);

                case 'get_guru_soal_status':
                    
                    $gurus = DB::connection('mysql_auth')->table('guru')->get();
                    $bankSoalData = DB::connection('mysql_asesmen')
                        ->select("SELECT id_guru, mapel, kelas, COUNT(id_soal) AS total_soal, SUM(status_soal = 'VALIDATED') AS soal_validated, SUM(status_soal = 'SUBMITTED') AS soal_submitted, SUM(status_soal = 'DRAFT') AS soal_draft FROM bank_soal GROUP BY id_guru, mapel, kelas");
                    
                    $bankSoalGrouped = [];
                    foreach ($bankSoalData as $b) {
                        $bankSoalGrouped[$b->id_guru][] = $b;
                    }

                    $data = [];
                    foreach ($gurus as $g) {
                        if (isset($bankSoalGrouped[$g->user_id])) {
                            foreach ($bankSoalGrouped[$g->user_id] as $b) {
                                $data[] = [
                                    'guru_user_id' => $g->user_id,
                                    'guru_nama' => $g->nama_lengkap,
                                    'mapel' => $b->mapel,
                                    'kelas' => $b->kelas,
                                    'total_soal' => $b->total_soal,
                                    'soal_validated' => $b->soal_validated,
                                    'soal_submitted' => $b->soal_submitted,
                                    'soal_draft' => $b->soal_draft
                                ];
                            }
                        } else {
                            $data[] = [
                                'guru_user_id' => $g->user_id,
                                'guru_nama' => $g->nama_lengkap,
                                'mapel' => null,
                                'kelas' => null,
                                'total_soal' => 0,
                                'soal_validated' => 0,
                                'soal_submitted' => 0,
                                'soal_draft' => 0
                            ];
                        }
                    }
                    usort($data, function($a, $b) { return strcmp($a['guru_nama'], $b['guru_nama']); });
                    
                    return $this->jsonResponse(true, 'Guru soal status', $data);

                case 'validate_soal':
                    $mapel = $request->input('mapel');
                    BankSoal::where('mapel', $mapel)->update(['status_soal' => 'VALIDATED']);
                    return $this->jsonResponse(true, 'Soal validated');

                case 'get_soal_by_paket':
                    $guruId = (int) $request->input('guru_id', 0);
                    $mapel  = trim($request->input('mapel', ''));
                    $kelas  = trim($request->input('kelas', ''));

                    $query = DB::connection('mysql_asesmen')->table('bank_soal')
                        ->where('mapel', $mapel)
                        ->where('kelas', $kelas);
                    if ($guruId) $query->where('id_guru', $guruId);

                    $soalList = $query->orderBy('id_soal', 'asc')->get();
                    
                    
                    $soalIds = collect($soalList)->pluck('id_soal')->toArray();
                    $assets = [];
                    if (!empty($soalIds)) {
                        $assetsRows = DB::connection('mysql_asesmen')
                            ->table('soal_assets')
                            ->whereIn('bank_soal_id', $soalIds)
                            ->get();
                        foreach ($assetsRows as $row) {
                            $assets[$row->bank_soal_id][] = $row;
                        }
                    }
                    foreach ($soalList as &$s) {
                        $s->assets = $assets[$s->id_soal] ?? [];
                    }

                    return $this->jsonResponse(true, 'Soal list', $soalList);

                case 'validate_paket_soal':
                    $guruId = (int) $request->input('guru_id', 0);
                    $mapel  = trim($request->input('mapel', ''));
                    $kelas  = trim($request->input('kelas', ''));

                    $query = DB::connection('mysql_asesmen')->table('bank_soal')
                        ->where('mapel', $mapel)
                        ->where('kelas', $kelas);
                    if ($guruId) $query->where('id_guru', $guruId);
                    $updated = $query->update(['status_soal' => 'VALIDATED', 'updated_at' => now()]);
                    return $this->jsonResponse(true, "$updated soal berhasil divalidasi", ['updated' => $updated]);

                case 'delete_paket_soal':
                    $mapel = trim($request->input('mapel', ''));
                    $kelas = trim($request->input('kelas', ''));
                    $idGuru = (int) $request->input('id_guru', 0);

                    if (!$mapel || !$kelas) {
                        return $this->jsonResponse(false, 'Mapel dan kelas harus diisi');
                    }

                    $query = BankSoal::where('mapel', $mapel)->where('kelas', $kelas);
                    if ($idGuru) {
                        $query->where('id_guru', $idGuru);
                    }
                    
                    $soalIds = $query->pluck('id_soal')->toArray();
                    
                    
                    $assets = DB::connection('mysql_asesmen')->table('soal_assets')->whereIn('bank_soal_id', $soalIds)->get();
                    foreach ($assets as $asset) {
                        if (in_array($asset->asset_type, ['local_image', 'audio_mp3'])) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete('exam_assets/' . $asset->asset_source);
                        }
                    }

                    $deleted = $query->delete();
                    return $this->jsonResponse(true, "$deleted soal berhasil dihapus beserta asetnya", ['deleted' => $deleted]);

                case 'get_rekap_nilai':
                    $hasilRows = DB::connection('mysql_asesmen')->select("SELECT id_hasil, skor_akhir, waktu_selesai, id_siswa FROM hasil_ujian ORDER BY skor_akhir DESC");
                    if (empty($hasilRows)) {
                        return $this->jsonResponse(true, 'Rekap fetched', []);
                    }
                    
                    $idSiswa = array_unique(array_column($hasilRows, 'id_siswa'));
                    $siswaMap = DB::connection('mysql_auth')->table('siswa')->whereIn('user_id', $idSiswa)->get()->keyBy('user_id');
                    
                    $results = [];
                    foreach ($hasilRows as $h) {
                        $s = $siswaMap->get($h->id_siswa);
                        $results[] = [
                            'id_hasil' => $h->id_hasil,
                            'skor_akhir' => $h->skor_akhir,
                            'waktu_selesai' => $h->waktu_selesai,
                            'nama' => $s ? $s->nama : 'Unknown',
                            'nis' => $s ? $s->nis : '-'
                        ];
                    }
                    return $this->jsonResponse(true, 'Rekap fetched', $results);

                case 'get_kelas_mapel_list':
                    $kelasList = DB::connection('mysql_auth')->select("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas");
                    $mapelList = DB::connection('mysql_auth')->select("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel");
                    return $this->jsonResponse(true, 'Lists fetched', ['kelas' => $kelasList, 'mapel' => $mapelList]);

                case 'save_jadwal':
                    $mapel = trim($request->input('mapel', ''));
                    $kelas = trim($request->input('kelas', ''));
                    $tanggal = $request->input('tanggal', '');
                    $jamMulai = $request->input('jam_mulai', '');
                    $durasi = (int) $request->input('durasi', 0);
                    $jenis = $request->input('jenis_asesmen', 'ASTS');
                    $pengulangan = $request->input('pengulangan', 'TIDAK');
                    $tampilkanJawaban = $request->input('tampilkan_jawaban', 'TIDAK');
                    $tampilkanNilai = in_array($request->input('tampilkan_nilai'), ['YA', 'TIDAK']) ? $request->input('tampilkan_nilai') : 'YA';
                    $modeSubmit = in_array($request->input('mode_submit'), ['MANDIRI', 'SERENTAK']) ? $request->input('mode_submit') : 'MANDIRI';

                    if (!$mapel || !$kelas || !$tanggal || !$jamMulai || $durasi <= 0) {
                        return $this->jsonResponse(false, 'Data jadwal tidak lengkap');
                    }

                    $hariArr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $hari = $hariArr[date('w', strtotime($tanggal))];
                    $jamSelesai = date('H:i:s', strtotime($jamMulai) + $durasi * 60);

                    $jadwal = JadwalUjian::create([
                        'mapel' => $mapel,
                        'kelas' => $kelas,
                        'tanggal_ujian' => $tanggal,
                        'hari' => $hari,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                        'durasi' => $durasi,
                        'jenis_asesmen' => $jenis,
                        'pengulangan' => $pengulangan,
                        'tampilkan_jawaban' => $tampilkanJawaban,
                        'tampilkan_nilai' => $tampilkanNilai,
                        'mode_submit' => $modeSubmit,
                        'created_by' => $operatorId,
                        'created_at' => now()
                    ]);

                    return $this->jsonResponse(true, "Jadwal $mapel - Kelas $kelas disimpan", ['id_jadwal' => $jadwal->id_jadwal, 'jam_selesai' => $jamSelesai]);

                case 'update_jadwal_config':
                    $id = (int) $request->input('id_jadwal', 0);
                    $pengulangan = $request->input('pengulangan', 'TIDAK');
                    $tampilkanJawaban = $request->input('tampilkan_jawaban', 'TIDAK');
                    $tampilkanNilai = in_array($request->input('tampilkan_nilai'), ['YA', 'TIDAK']) ? $request->input('tampilkan_nilai') : 'YA';
                    $modeSubmit = in_array($request->input('mode_submit'), ['MANDIRI', 'SERENTAK']) ? $request->input('mode_submit') : 'MANDIRI';

                    $tanggal = $request->input('tanggal_ujian');
                    $jamMulai = $request->input('jam_mulai');
                    $durasi = (int) $request->input('durasi', 0);

                    $jadwal = JadwalUjian::find($id);
                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Jadwal tidak ditemukan');
                    }

                    $updateData = [
                        'pengulangan' => $pengulangan,
                        'tampilkan_jawaban' => $tampilkanJawaban,
                        'tampilkan_nilai' => $tampilkanNilai,
                        'mode_submit' => $modeSubmit
                    ];

                    if ($tanggal && $jamMulai && $durasi > 0) {
                        $hariArr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        $hari = $hariArr[date('w', strtotime($tanggal))];
                        $jamSelesai = date('H:i:s', strtotime($jamMulai) + $durasi * 60);

                        $updateData['tanggal_ujian'] = $tanggal;
                        $updateData['hari'] = $hari;
                        $updateData['jam_mulai'] = $jamMulai;
                        $updateData['jam_selesai'] = $jamSelesai;
                        $updateData['durasi'] = $durasi;
                    }

                    $jadwal->update($updateData);

                    return $this->jsonResponse(true, 'Konfigurasi jadwal berhasil diperbarui');

                case 'delete_jadwal':
                    $id = (int) $request->input('id_jadwal', 0);
                    if (HasilUjian::where('id_jadwal', $id)->exists()) {
                        return $this->jsonResponse(false, 'Jadwal sudah memiliki hasil ujian, tidak dapat dihapus');
                    }
                    JadwalUjian::where('id_jadwal', $id)->delete();
                    return $this->jsonResponse(true, 'Jadwal dihapus');

                case 'generate_token_for_jadwal':
                    $id = (int) $request->input('id_jadwal', 0);
                    if (!$id)
                        return $this->jsonResponse(false, 'ID Jadwal tidak valid');

                    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    $token = null;
                    for ($i = 0; $i < 20; $i++) {
                        $candidate = '';
                        for ($j = 0; $j < 5; $j++) {
                            $candidate .= $chars[random_int(0, strlen($chars) - 1)];
                        }
                        if (!JadwalUjian::where('token', $candidate)->exists()) {
                            $token = $candidate;
                            break;
                        }
                    }
                    if (!$token)
                        return $this->jsonResponse(false, 'Gagal generate token unik');

                    JadwalUjian::where('id_jadwal', $id)->update(['token' => $token]);
                    return $this->jsonResponse(true, 'Token generated', ['token' => $token, 'id_jadwal' => $id]);

                case 'reset_all_tokens':
                    $jadwals = JadwalUjian::where('tanggal_ujian', DB::raw('CURDATE()'))->get();
                    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    $usedTokens = [];
                    $updated = 0;

                    foreach ($jadwals as $jadwal) {
                        for ($i = 0; $i < 30; $i++) {
                            $candidate = '';
                            for ($j = 0; $j < 5; $j++) {
                                $candidate .= $chars[random_int(0, strlen($chars) - 1)];
                            }
                            if (!in_array($candidate, $usedTokens)) {
                                if (!JadwalUjian::where('token', $candidate)->where('id_jadwal', '!=', $jadwal->id_jadwal)->exists()) {
                                    JadwalUjian::where('id_jadwal', $jadwal->id_jadwal)->update(['token' => $candidate]);
                                    $usedTokens[] = $candidate;
                                    $updated++;
                                    break;
                                }
                            }
                        }
                    }
                    return $this->jsonResponse(true, "$updated token berhasil direset", ['updated' => $updated]);

                case 'get_jadwal_for_portal':
                    $idJadwal = (int) $request->input('id_jadwal', 0);
                    if (!$idJadwal)
                        return $this->jsonResponse(false, 'ID Jadwal tidak valid');

                    $jadwal = JadwalUjian::find($idJadwal);
                    if (!$jadwal)
                        return $this->jsonResponse(false, 'Jadwal tidak ditemukan');

                    $row = $jadwal->toArray();
                    try {
                        $cntSiswa = DB::connection('mysql_auth')->selectOne("SELECT COUNT(*) as count FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE k.nama_kelas = ?", [$row['kelas']]);
                        $row['jumlah_siswa'] = $cntSiswa->count;
                    } catch (\Exception $e) {
                        $row['jumlah_siswa'] = '-';
                    }
                    $row['jumlah_submit'] = HasilUjian::where('id_jadwal', $idJadwal)->count();

                    return $this->jsonResponse(true, 'Jadwal portal data', $row);

                case 'get_hasil_by_jadwal':
                    $idJadwal = (int) $request->input('id_jadwal', 0);
                    if (!$idJadwal)
                        return $this->jsonResponse(false, 'ID Jadwal harus diisi');

                    $jadwal = JadwalUjian::find($idJadwal);
                    if (!$jadwal)
                        return $this->jsonResponse(false, 'Jadwal tidak ditemukan');

                    $hasilRows = DB::connection('mysql_asesmen')->select("SELECT id_hasil, skor_akhir, waktu_mulai, waktu_selesai, id_siswa FROM hasil_ujian WHERE id_jadwal = ? ORDER BY skor_akhir DESC", [$idJadwal]);
                    
                    $results = [];
                    if (!empty($hasilRows)) {
                        $idSiswa = array_unique(array_column($hasilRows, 'id_siswa'));
                        $siswaMap = DB::connection('mysql_auth')->table('siswa')->whereIn('user_id', $idSiswa)->get()->keyBy('user_id');
                        
                        foreach ($hasilRows as $h) {
                            $s = $siswaMap->get($h->id_siswa);
                            $results[] = [
                                'id_hasil' => $h->id_hasil,
                                'skor_akhir' => $h->skor_akhir,
                                'waktu_mulai' => $h->waktu_mulai,
                                'waktu_selesai' => $h->waktu_selesai,
                                'nama' => $s ? $s->nama : 'Unknown',
                                'nis' => $s ? $s->nis : '-'
                            ];
                        }
                    }

                    return $this->jsonResponse(true, 'Hasil ujian', ['jadwal' => $jadwal, 'hasil' => $results]);

                case 'delete_hasil_siswa':
                    $idHasil = (int) $request->input('id_hasil', 0);
                    if (!$idHasil)
                        return $this->jsonResponse(false, 'ID hasil tidak valid');
                    HasilUjian::where('id_hasil', $idHasil)->delete();
                    return $this->jsonResponse(true, 'Jawaban siswa berhasil dihapus');

                case 'delete_semua_hasil':
                    $idJadwal = (int) $request->input('id_jadwal', 0);
                    if (!$idJadwal)
                        return $this->jsonResponse(false, 'ID jadwal tidak valid');
                    $deleted = HasilUjian::where('id_jadwal', $idJadwal)->delete();
                    return $this->jsonResponse(true, "$deleted jawaban siswa berhasil dihapus", ['deleted' => $deleted]);

                case 'publish_hasil_ke_guru':
                    $idJadwal = (int) $request->input('id_jadwal', 0);
                    if (!$idJadwal)
                        return $this->jsonResponse(false, 'ID jadwal tidak valid');
                    
                    $jadwal = JadwalUjian::find($idJadwal);
                    if (!$jadwal) {
                        return $this->jsonResponse(false, 'Jadwal tidak ditemukan');
                    }
                    
                    $jadwal->hasil_dirilis_ke_guru = 'YA';
                    $jadwal->save();
                    
                    return $this->jsonResponse(true, "Hasil ujian berhasil dirilis ke Guru Mapel");

                case 'delete_jadwal_force':
                    $id = (int) $request->input('id_jadwal', 0);
                    if (!$id)
                        return $this->jsonResponse(false, 'ID jadwal tidak valid');

                    DB::connection('mysql_asesmen')->beginTransaction();
                    try {
                        $delHasil = HasilUjian::where('id_jadwal', $id)->delete();
                        JadwalUjian::where('id_jadwal', $id)->delete();
                        DB::connection('mysql_asesmen')->commit();
                        return $this->jsonResponse(true, "Jadwal dan $delHasil jawaban siswa berhasil dihapus", ['hasil_deleted' => $delHasil]);
                    } catch (\Exception $e) {
                        DB::connection('mysql_asesmen')->rollBack();
                        return $this->jsonResponse(false, 'Gagal hapus: ' . $e->getMessage());
                    }

                case 'get_monitoring_soal':
                    
                    $gurus = DB::connection('mysql_auth')->table('guru')->get();
                    $bankSoalData = DB::connection('mysql_asesmen')
                        ->select("SELECT id_guru, mapel, kelas, COUNT(id_soal) AS total_soal, SUM(status_soal = 'VALIDATED') AS soal_validated, SUM(status_soal = 'SUBMITTED') AS soal_submitted, SUM(status_soal = 'DRAFT') AS soal_draft FROM bank_soal GROUP BY id_guru, mapel, kelas");
                    
                    $bankSoalGrouped = [];
                    foreach ($bankSoalData as $b) {
                        $bankSoalGrouped[$b->id_guru][] = $b;
                    }

                    $rows = [];
                    foreach ($gurus as $g) {
                        if (isset($bankSoalGrouped[$g->user_id])) {
                            foreach ($bankSoalGrouped[$g->user_id] as $b) {
                                $rows[] = [
                                    'guru_user_id' => $g->user_id,
                                    'guru_nama' => $g->nama_lengkap,
                                    'mapel' => $b->mapel,
                                    'kelas' => $b->kelas,
                                    'total_soal' => $b->total_soal,
                                    'soal_validated' => $b->soal_validated,
                                    'soal_submitted' => $b->soal_submitted,
                                    'soal_draft' => $b->soal_draft
                                ];
                            }
                        } else {
                            $rows[] = [
                                'guru_user_id' => $g->user_id,
                                'guru_nama' => $g->nama_lengkap,
                                'mapel' => null,
                                'kelas' => null,
                                'total_soal' => 0,
                                'soal_validated' => 0,
                                'soal_submitted' => 0,
                                'soal_draft' => 0
                            ];
                        }
                    }
                    usort($rows, function($a, $b) { return strcmp($a['guru_nama'], $b['guru_nama']); });

                    
                    $data = array_map(function ($r) {
                        $r = (array) $r;
                        $total     = (int) $r['total_soal'];
                        $validated = (int) ($r['soal_validated'] ?? 0);
                        $draft     = (int) ($r['soal_draft']     ?? 0);
                        $submitted = (int) ($r['soal_submitted'] ?? 0);

                        if ($total === 0) {
                            $r['status_kategori'] = 'BELUM_INPUT';
                        } elseif ($validated === $total) {
                            $r['status_kategori'] = 'LENGKAP';
                        } else {
                            $r['status_kategori'] = 'PERLU_VALIDASI';
                        }

                        $r['pct_validated'] = $total > 0 ? round(($validated / $total) * 100) : 0;

                        
                        try {
                            $cnt = DB::connection('mysql_auth')
                                ->selectOne("SELECT COUNT(*) AS cnt FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE k.nama_kelas = ?", [$r['kelas']]);
                            $r['jumlah_siswa'] = $cnt ? (int) $cnt->cnt : 0;
                        } catch (\Exception $e) {
                            $r['jumlah_siswa'] = 0;
                        }

                        return $r;
                    }, $rows);

                    
                    $summary = [
                        'total_guru'      => count(array_unique(array_column($data, 'guru_user_id'))),
                        'sudah_submit'    => count(array_filter($data, fn($r) => (int)$r['total_soal'] > 0)),
                        'perlu_validasi'  => count(array_filter($data, fn($r) => $r['status_kategori'] === 'PERLU_VALIDASI')),
                        'lengkap'         => count(array_filter($data, fn($r) => $r['status_kategori'] === 'LENGKAP')),
                        'belum_input'     => count(array_filter($data, fn($r) => $r['status_kategori'] === 'BELUM_INPUT')),
                    ];

                    return $this->jsonResponse(true, 'Monitoring data', ['rows' => $data, 'summary' => $summary]);

                default:
                    return $this->jsonResponse(false, 'Action not recognized for Operator API: ' . $action);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Error: ' . $e->getMessage());
        }
    }
}
