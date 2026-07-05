<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Integrations\Google\GoogleDocsParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankSoalController extends Controller
{
    public function index()
    {
        $gateQuery = DB::connection('asesmen_gara')->table('asesmen_config')->orderBy('updated_at', 'desc')->first();
        $gateOpen = isset($gateQuery->status_pintu) ? (int) $gateQuery->status_pintu === 1 : false;

        $mapel = session('nama_mapel');
        $kelas = session('nama_kelas');
        $guruId = auth()->id();

        $statusSummary = null;
        $questions = collect();
        $jadwal_hasil = null;
        $hasil_siswa = [];
        if ($mapel && $kelas) {
            $questions = DB::connection('asesmen_gara')->table('bank_soal')
                ->where('id_guru', $guruId)
                ->where('mapel', $mapel)
                ->where('kelas', $kelas)
                ->get();

            if ($questions->count() > 0) {
                $totalCount = $questions->count();
                $draftCount = $questions->where('status_soal', 'DRAFT')->count();
                $validatedCount = $questions->where('status_soal', 'VALIDATED')->count();

                $statusSummary = [
                    'total' => $totalCount,
                    'draft' => $draftCount,
                    'validated' => $validatedCount
                ];

                if ($validatedCount > 0) {
                    $jadwal_hasil = DB::connection('asesmen_gara')->table('jadwal_ujian')
                        ->where('mapel', $mapel)
                        ->where('kelas', $kelas)
                        ->orderBy('tanggal_ujian', 'desc')
                        ->orderBy('jam_mulai', 'desc')
                        ->first();

                    if ($jadwal_hasil && $jadwal_hasil->hasil_dirilis_ke_guru === 'YA') {
                        $hasilRows = DB::connection('asesmen_gara')->table('hasil_ujian')
                            ->where('id_jadwal', $jadwal_hasil->id_jadwal)
                            ->orderBy('skor_akhir', 'desc')
                            ->get();

                        if ($hasilRows->isNotEmpty()) {
                            $idSiswa = $hasilRows->pluck('id_siswa')->unique()->toArray();
                            $siswaMap = DB::connection('mysql_auth')->table('siswa')
                                ->whereIn('user_id', $idSiswa)
                                ->get()
                                ->keyBy('user_id');

                            foreach ($hasilRows as $h) {
                                $s = $siswaMap->get($h->id_siswa);
                                $hasil_siswa[] = (object) [
                                    'nis' => $s ? $s->nis : '-',
                                    'nama' => $s ? $s->nama : 'Siswa Tidak Diketahui',
                                    'skor' => $h->skor_akhir,
                                    'keterangan' => $h->skor_akhir >= 75 ? 'Tuntas' : 'Tidak Tuntas'
                                ];
                            }
                        }
                    }
                }
            }
        }

        return view('guru.ujian.input_soal', compact('gateOpen', 'statusSummary', 'mapel', 'kelas', 'questions', 'jadwal_hasil', 'hasil_siswa'));
    }

    public function destroyPacket()
    {
        $mapel = session('nama_mapel');
        $kelas = session('nama_kelas');
        $guruId = auth()->id();

        if (!$mapel || !$kelas) {
            return redirect()->route('guru.ujian')->with('error', 'Sesi tidak valid.');
        }

        $drafts = DB::connection('asesmen_gara')->table('bank_soal')
            ->where('id_guru', $guruId)
            ->where('mapel', $mapel)
            ->where('kelas', $kelas)
            ->where('status_soal', 'DRAFT')
            ->get();

        if ($drafts->count() === 0) {
            return redirect()->route('guru.ujian')->with('error', 'Tidak ada soal draft untuk dihapus atau soal sudah divalidasi.');
        }

        foreach ($drafts as $draft) {
            DB::connection('asesmen_gara')->table('soal_assets')->where('bank_soal_id', $draft->id_soal)->delete();
            DB::connection('asesmen_gara')->table('bank_soal')->where('id_soal', $draft->id_soal)->delete();
        }

        return redirect()->route('guru.ujian')->with('success', 'Seluruh soal draft berhasil dihapus.');
    }

    public function manualInput()
    {
        $gateQuery = DB::connection('asesmen_gara')->table('asesmen_config')->orderBy('updated_at', 'desc')->first();
        $gateOpen = isset($gateQuery->status_pintu) ? (int) $gateQuery->status_pintu === 1 : false;

        return view('guru.ujian.manual_input', compact('gateOpen'));
    }

    public function qcPreview(Request $request)
    {
        $mapel = session('nama_mapel', '-');
        $kelas = session('nama_kelas', '-');
        $guruId = auth()->id();

        
        $soalDraft = session('soal_draft', []);

        if (empty($soalDraft) && $mapel != '-' && $kelas != '-') {
            $dbDrafts = DB::connection('asesmen_gara')->table('bank_soal')
                ->where('id_guru', $guruId)
                ->where('mapel', $mapel)
                ->where('kelas', $kelas)
                ->where('status_soal', 'DRAFT')
                ->orderBy('id_soal', 'asc')
                ->get();
            
            $formatted = [];
            foreach ($dbDrafts as $idx => $dbSoal) {
                $assets = DB::connection('asesmen_gara')->table('soal_assets')
                    ->where('bank_soal_id', $dbSoal->id_soal)
                    ->get()
                    ->map(function($a) {
                        return [
                            'asset_type' => $a->asset_type,
                            'asset_source' => $a->asset_source,
                            'original_name' => $a->original_name
                        ];
                    })->toArray();
                    
                $formatted[] = [
                    'no' => $idx + 1,
                    'tipe' => $dbSoal->tipe_soal,
                    'soal' => $dbSoal->konten_soal,
                    'a' => $dbSoal->opsi_a,
                    'b' => $dbSoal->opsi_b,
                    'c' => $dbSoal->opsi_c,
                    'd' => $dbSoal->opsi_d,
                    'e' => $dbSoal->opsi_e,
                    'kunci' => $dbSoal->kunci_jawaban,
                    'assets' => $assets
                ];
            }
            $soalDraft = $formatted;
        }

        return view('guru.ujian.qc', compact('soalDraft', 'mapel', 'kelas'));
    }

    public function qcKirim(Request $request)
    {
        $mapel = $request->input('mapel');
        $kelas = $request->input('kelas');
        $guruId = auth()->id();
        $list = json_decode($request->input('soal_list'), true);

        if (!is_array($list) || count($list) === 0) {
            return redirect()->route('guru.ujian.qc')
                ->with('error', 'Data soal tidak valid atau kosong.');
        }

        DB::connection('asesmen_gara')->beginTransaction();
        try {
            
            $oldDrafts = DB::connection('asesmen_gara')->table('bank_soal')
                ->where('id_guru', $guruId)
                ->where('mapel', $mapel)
                ->where('kelas', $kelas)
                ->where('status_soal', 'DRAFT')
                ->get();
            
            foreach ($oldDrafts as $od) {
                DB::connection('asesmen_gara')->table('soal_assets')->where('bank_soal_id', $od->id_soal)->delete();
                DB::connection('asesmen_gara')->table('bank_soal')->where('id_soal', $od->id_soal)->delete();
            }

            foreach ($list as $item) {
                $idSoal = DB::connection('asesmen_gara')->table('bank_soal')->insertGetId([
                    'id_guru'       => $guruId,
                    'mapel'         => $mapel,
                    'kelas'         => $kelas,
                    'tipe_soal'     => $item['tipe'],
                    'konten_soal'   => $item['soal'],
                    'opsi_a'        => $item['a'] ?? '',
                    'opsi_b'        => $item['b'] ?? '',
                    'opsi_c'        => $item['c'] ?? '',
                    'opsi_d'        => $item['d'] ?? '',
                    'opsi_e'        => $item['e'] ?? '',
                    'kunci_jawaban' => $item['kunci'],
                    'bobot'         => 1,
                    'status_soal'   => 'DRAFT',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                if (!empty($item['assets']) && is_array($item['assets'])) {
                    foreach ($item['assets'] as $asset) {
                        DB::connection('asesmen_gara')->table('soal_assets')->insert([
                            'bank_soal_id'  => $idSoal,
                            'asset_type'    => $asset['asset_type'],
                            'asset_source'  => $asset['asset_source'],
                            'original_name' => $asset['original_name'] ?? null,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }

            DB::connection('asesmen_gara')->commit();
            return redirect()->route('guru.ujian')
                ->with('success', count($list) . ' soal berhasil dikirim ke operator.');
        } catch (\Exception $e) {
            DB::connection('asesmen_gara')->rollBack();
            return redirect()->route('guru.ujian.qc')
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function storePaket(Request $request)
    {
        $mapel = $request->input('mapel');
        $kelas = $request->input('kelas');
        $guruId = auth()->id();
        $list = json_decode($request->input('soal_list'), true);

        if (!is_array($list)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON']);
        }

        DB::connection('asesmen_gara')->beginTransaction();
        try {
            foreach ($list as $item) {
                $idSoal = DB::connection('asesmen_gara')->table('bank_soal')->insertGetId([
                    'id_guru' => $guruId,
                    'mapel' => $mapel,
                    'kelas' => $kelas,
                    'tipe_soal' => $item['tipe'],
                    'konten_soal' => $item['soal'],
                    'opsi_a' => $item['a'] ?? '',
                    'opsi_b' => $item['b'] ?? '',
                    'opsi_c' => $item['c'] ?? '',
                    'opsi_d' => $item['d'] ?? '',
                    'opsi_e' => $item['e'] ?? '',
                    'kunci_jawaban' => $item['kunci'],
                    'bobot' => 1,
                    'status_soal' => 'DRAFT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (!empty($item['assets']) && is_array($item['assets'])) {
                    foreach ($item['assets'] as $asset) {
                        DB::connection('asesmen_gara')->table('soal_assets')->insert([
                            'bank_soal_id'  => $idSoal,
                            'asset_type'    => $asset['asset_type'],
                            'asset_source'  => $asset['asset_source'],
                            'original_name' => $asset['original_name'] ?? null,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }

            DB::connection('asesmen_gara')->commit();
            return response()->json(['success' => true, 'message' => 'Paket soal berhasil disimpan']);
        } catch (\Exception $e) {
            DB::connection('asesmen_gara')->rollBack();
            return response()->json(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }

    public function extractFromGdocs(Request $request)
    {
        $docId = $request->input('doc_id');

        if (!$docId) {
            return response()->json(['success' => false, 'message' => 'ID dokumen tidak valid.']);
        }

        $parser = new GoogleDocsParser();
        $result = $parser->extractQuestionsFromDoc(Auth::id(), $docId);

        if (isset($result['error'])) {
            return response()->json(['success' => false, 'message' => $result['error']]);
        }

        if (empty($result['parsed'])) {
            return response()->json(['success' => false, 'message' => 'Tidak ada soal yang berhasil diekstrak. Pastikan dokumen menggunakan format template yang benar.']);
        }

        return response()->json([
            'success' => true,
            'raw_text' => $result['raw_text'],
            'data' => $result['parsed'],
            'count' => count($result['parsed'])
        ]);
    }

    public function saveAsset(Request $request)
    {
        $type = $request->input('asset_type');
        
        if (in_array($type, ['local_image', 'audio_mp3'])) {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                
                if ($type === 'local_image' && !in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'webp'])) {
                    return response()->json(['success' => false, 'message' => 'Format gambar tidak didukung']);
                }
                if ($type === 'audio_mp3' && $file->getClientOriginalExtension() !== 'mp3') {
                    return response()->json(['success' => false, 'message' => 'Format audio harus MP3']);
                }

                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('exam_assets', $filename, 'public');
                
                return response()->json([
                    'success' => true,
                    'asset_type' => $type,
                    'asset_source' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'url' => asset('storage/' . $path)
                ]);
            }
        }
        
        if ($type === 'google_drive') {
            $fileId = $request->input('asset_source');
            $originalName = $request->input('original_name', 'GoogleDriveImage.jpg');
            
            $tokenManager = new \App\Integrations\Google\GoogleTokenManager();
            $accessToken = $tokenManager->getValidAccessToken(Auth::id());
            
            if (!$accessToken) {
                return response()->json(['success' => false, 'message' => 'Gagal mengunduh dari Google Drive. Akun Google belum dihubungkan.']);
            }
            
            try {
                $client = new \Google\Client();
                $client->setAccessToken(['access_token' => $accessToken, 'token_type' => 'Bearer']);
                $driveService = new \Google\Service\Drive($client);
                
                $response = $driveService->files->get($fileId, ['alt' => 'media']);
                $content = $response->getBody()->getContents();
                
                
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                if (!$ext) $ext = 'jpg';
                
                $filename = time() . '_gd_' . uniqid() . '.' . $ext;
                
                \Illuminate\Support\Facades\Storage::disk('public')->put('exam_assets/' . $filename, $content);
                
                return response()->json([
                    'success' => true,
                    'asset_type' => 'local_image',
                    'asset_source' => $filename,
                    'original_name' => $originalName,
                    'url' => asset('storage/exam_assets/' . $filename)
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Gagal mengunduh file dari Drive. Pastikan file dibagikan atau bisa diakses: ' . $e->getMessage()]);
            }
        }

        
        if (in_array($type, ['external_image', 'youtube_link'])) {
            return response()->json([
                'success' => true,
                'asset_type' => $type,
                'asset_source' => $request->input('asset_source'),
                'original_name' => null
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Tipe aset atau file tidak valid']);
    }
}
