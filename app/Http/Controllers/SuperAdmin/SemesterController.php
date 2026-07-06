<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SemesterController extends Controller
{
    public function index()
    {
        $tahun_ajaran = DB::connection('mysql_apps')->table('app_settings')->where('setting_key', 'tahun_ajaran')->value('setting_value');
        $semester = DB::connection('mysql_apps')->table('app_settings')->where('setting_key', 'semester_aktif')->value('setting_value');
        $semester_label = $semester == '1' ? 'Ganjil' : 'Genap';

        if ($semester == '1') {
            $next_semester_code = '2';
            $next_semester_label = 'Genap';
            $next_tahun_ajaran = $tahun_ajaran;
            $mode_aksi = 'ganti_semester';
            $tombol_text = 'Lanjut ke Semester Genap';
            $tombol_class = 'btn-warning';
            $icon = 'fas fa-arrow-right';
        } else {
            $next_semester_code = '1';
            $next_semester_label = 'Ganjil';
            $thn_pecah = explode('/', $tahun_ajaran);
            if (count($thn_pecah) == 2) {
                $thn_awal = intval($thn_pecah[0]) + 1;
                $thn_akhir = intval($thn_pecah[1]) + 1;
                $next_tahun_ajaran = $thn_awal . '/' . $thn_akhir;
            } else {
                $next_tahun_ajaran = $tahun_ajaran;
            }
            $mode_aksi = 'naik_kelas';
            $tombol_text = 'Naik Kelas & Ganti Tahun Ajaran';
            $tombol_class = 'btn-danger';
            $icon = 'fas fa-level-up-alt';
        }

        // Cek validitas backup (max 10 minutes ago)
        $backupDir = storage_path('app/backups/');
        $is_backup_valid = false;
        
        if (File::exists($backupDir)) {
            $files = File::files($backupDir);
            $now = time();
            foreach ($files as $file) {
                if ($file->getExtension() == 'sql' && strpos($file->getFilename(), 'all_databases_backup_') === 0) {
                    $mtime = $file->getMTime();
                    // 600 detik = 10 menit
                    if (($now - $mtime) <= 600) {
                        $is_backup_valid = true;
                        break;
                    }
                }
            }
        }

        return view('super_admin.semester.index', compact(
            'tahun_ajaran', 'semester_label', 'next_semester_code', 'next_semester_label', 
            'next_tahun_ajaran', 'mode_aksi', 'tombol_text', 'tombol_class', 'icon', 'is_backup_valid'
        ));
    }

    public function upgrade(Request $request)
    {
        $mode = $request->input('mode');
        $next_tahun = $request->input('next_tahun');
        $next_semester = $request->input('next_semester');

        if (empty($mode) || empty($next_tahun) || empty($next_semester)) {
            return redirect()->back()->with('error_message', 'Data input tidak lengkap.');
        }

        // Cek validitas backup sekali lagi untuk keamanan
        $backupDir = storage_path('app/backups/');
        $is_backup_valid = false;
        if (File::exists($backupDir)) {
            $files = File::files($backupDir);
            $now = time();
            foreach ($files as $file) {
                if ($file->getExtension() == 'sql' && strpos($file->getFilename(), 'all_databases_backup_') === 0) {
                    $mtime = $file->getMTime();
                    if (($now - $mtime) <= 600) {
                        $is_backup_valid = true;
                        break;
                    }
                }
            }
        }

        if (!$is_backup_valid) {
            return redirect()->back()->with('error_message', 'Backup database keseluruhan (all) tidak valid atau belum dilakukan dalam 10 menit terakhir. Silakan lakukan backup terlebih dahulu.');
        }

        // Jalankan DDL (ALTER TABLE) di luar transaksi karena DDL memicu implicit commit di MySQL
        try {
            DB::connection('mysql_apps')->statement("ALTER TABLE tugas MODIFY COLUMN status ENUM('aktif', 'draft', 'arsip') DEFAULT 'aktif'");
        } catch (\Exception $e) {}
        try {
            DB::connection('mysql_apps')->statement("ALTER TABLE diskusi_threads MODIFY COLUMN status ENUM('aktif', 'draft', 'arsip') DEFAULT 'aktif'");
        } catch (\Exception $e) {}

        try {
            DB::connection('mysql_apps')->beginTransaction();
            DB::connection('mysql_asesmen')->beginTransaction();
            DB::connection('mysql_auth')->beginTransaction();

            // Matikan foreign key checks sementara jika diperlukan
            DB::connection('mysql_apps')->statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::connection('mysql_asesmen')->statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::connection('mysql_auth')->statement('SET FOREIGN_KEY_CHECKS=0;');

            $tables_apps = [
                'absensi_detail', 'absensi', 'agenda_harian', 'catatan_kelas',
                'nilai_tugas', 'tugas_pengumpulan', 'raw_scores', 'final_scores',
                'nilai_uts', 'nilai_uas', 'pengumuman'
            ];

            foreach ($tables_apps as $table) {
                DB::connection('mysql_apps')->table($table)->delete();
            }

            $tables_asesmen = ['hasil_ujian', 'ujian_drafts'];
            foreach ($tables_asesmen as $table) {
                DB::connection('mysql_asesmen')->table($table)->delete();
            }

            // 2. Arsipkan Tugas dan Diskusi
            DB::connection('mysql_apps')->table('tugas')->update(['status' => 'arsip']);
            
            // Arsipkan diskusi yang diupload guru
            DB::connection('mysql_apps')->table('diskusi_threads')->where('role_pembuat', 'guru')->update(['status' => 'arsip']);
            
            // Opsional: Hapus diskusi siswa jika ingin dibersihkan sepenuhnya
            DB::connection('mysql_apps')->table('diskusi_threads')->where('role_pembuat', 'siswa')->delete();
            // Jika ingin membersihkan reply diskusi
            DB::connection('mysql_apps')->table('diskusi_replies')->delete();

            // 3. Logika Naik Kelas
            if ($mode === 'naik_kelas') {
                $kelas_9 = DB::connection('mysql_auth')->table('kelas')->where('nama_kelas', 'IX')->value('id');
                $kelas_8 = DB::connection('mysql_auth')->table('kelas')->where('nama_kelas', 'VIII')->value('id');
                $kelas_7 = DB::connection('mysql_auth')->table('kelas')->where('nama_kelas', 'VII')->value('id');

                if ($kelas_9 && $kelas_8 && $kelas_7) {
                    // A. Luluskan Kelas 9
                    // Ambil ID User untuk dihapus dari tabel users
                    $siswa_lulus_user_ids = DB::connection('mysql_auth')->table('siswa')->where('kelas_id', $kelas_9)->pluck('user_id')->filter();
                    
                    // Hapus dari tabel siswa
                    DB::connection('mysql_auth')->table('siswa')->where('kelas_id', $kelas_9)->delete();
                    
                    // Hapus dari tabel users
                    if ($siswa_lulus_user_ids->isNotEmpty()) {
                        DB::connection('mysql_auth')->table('users')->whereIn('id', $siswa_lulus_user_ids)->delete();
                    }
                    
                    // B. Naikkan 8 ke 9
                    DB::connection('mysql_auth')->table('siswa')->where('kelas_id', $kelas_8)->update(['kelas_id' => $kelas_9]);
                    
                    // C. Naikkan 7 ke 8
                    DB::connection('mysql_auth')->table('siswa')->where('kelas_id', $kelas_7)->update(['kelas_id' => $kelas_8]);
                } else {
                    throw new \Exception("Data kelas Master (VII, VIII, IX) tidak ditemukan di database Auth.");
                }
            }

            // 4. Update Konfigurasi Semester
            DB::connection('mysql_apps')->table('app_settings')->updateOrInsert(
                ['setting_key' => 'tahun_ajaran'],
                ['setting_value' => $next_tahun]
            );
            
            DB::connection('mysql_apps')->table('app_settings')->updateOrInsert(
                ['setting_key' => 'semester_aktif'],
                ['setting_value' => $next_semester]
            );

            // Audit Log
            DB::connection('mysql_auth')->table('audit_logs')->insert([
                'user_id' => auth()->id() ?? 1,
                'user_type' => 'super_admin',
                'action' => "Ganti semester ke $next_tahun Semester $next_semester",
                'module' => 'Pengaturan Semester',
                'ip_address' => request()->ip()
            ]);

            DB::connection('mysql_apps')->statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::connection('mysql_asesmen')->statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::connection('mysql_auth')->statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::connection('mysql_apps')->commit();
            DB::connection('mysql_asesmen')->commit();
            DB::connection('mysql_auth')->commit();

            return redirect()->route('superadmin.semester.index')->with('success_message', "Sistem berhasil beralih ke Tahun Ajaran $next_tahun Semester $next_semester. Data transaksi lama telah dibersihkan/diarsipkan.");
        } catch (\Exception $e) {
            DB::connection('mysql_apps')->rollBack();
            DB::connection('mysql_asesmen')->rollBack();
            DB::connection('mysql_auth')->rollBack();
            Log::error("Ganti Semester Error: " . $e->getMessage());
            
            // Restore FK checks
            try { DB::connection('mysql_apps')->statement('SET FOREIGN_KEY_CHECKS=1;'); } catch (\Exception $ex) {}
            try { DB::connection('mysql_asesmen')->statement('SET FOREIGN_KEY_CHECKS=1;'); } catch (\Exception $ex) {}
            try { DB::connection('mysql_auth')->statement('SET FOREIGN_KEY_CHECKS=1;'); } catch (\Exception $ex) {}

            return redirect()->back()->with('error_message', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
