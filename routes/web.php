<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsSuperAdmin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\SettingsController;
use App\Http\Controllers\SuperAdmin\AuditController;
use App\Http\Controllers\SuperAdmin\BackupController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileHandoffController;
use App\Http\Controllers\Api\MobileDataController;






Route::prefix('api/mobile')->name('api.mobile.')->group(function () {
    
    Route::post('/login', [MobileAuthController::class, 'login'])->name('login');

    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');
        Route::get('/user', [MobileAuthController::class, 'me'])->name('user');
        Route::get('/mapel', [MobileDataController::class, 'getMapel'])->name('mapel');
        Route::get('/profile', [MobileDataController::class, 'getProfile'])->name('profile');
        
        Route::get('/account-detail', [MobileDataController::class, 'getAccountDetail'])->name('account-detail');
    });
});






Route::get('/auth/webview-handoff', [MobileHandoffController::class, 'handoff'])
    ->name('auth.handoff')
    ->middleware('throttle:30,1');




Route::get('/install', [\App\Http\Controllers\InstallController::class, 'index'])->name('install')->withoutMiddleware('web');
Route::post('/install/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run')->withoutMiddleware('web');

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/offline', function () {
    return view('errors.offline');
})->name('offline');


Route::get('/maintenance', function () {
    try {
        $maintenance = \Illuminate\Support\Facades\DB::connection('mysql_apps')
            ->table('app_settings')
            ->where('setting_key', 'maintenance_mode')
            ->value('setting_value');
    } catch (\Exception $e) {
        $maintenance = '0';
    }

    if ($maintenance == '1') {
        return response()->view('maintenance', [], 503);
    }

    
    return redirect()->route('login');
})->name('maintenance');

Route::middleware(['auth', IsSuperAdmin::class])->prefix('super-admin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{id}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');

    
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

    
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::post('/audit/clear', [AuditController::class, 'clearAll'])->name('audit.clear');
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup/store', [BackupController::class, 'store'])->name('backup.store');
    Route::get('/backup/download', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/destroy', [BackupController::class, 'destroy'])->name('backup.destroy');

    
    Route::get('/ubah-password', [PasswordController::class, 'showForm'])->name('password.form');
    Route::post('/ubah-password', [PasswordController::class, 'update'])->name('password.update');

    
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'superAdminIndex'])->name('profile.index');
    Route::post('/profil/foto', [\App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profil/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    
    Route::get('/security', [\App\Http\Controllers\SuperAdmin\SecurityController::class, 'index'])->name('security.index');
    Route::post('/security/block', [\App\Http\Controllers\SuperAdmin\SecurityController::class, 'blockIp'])->name('security.block');
    Route::delete('/security/unblock/{id}', [\App\Http\Controllers\SuperAdmin\SecurityController::class, 'unblockIp'])->name('security.unblock');
    Route::post('/emergency/toggle', [\App\Http\Controllers\SuperAdmin\SecurityController::class, 'toggleEmergency'])->name('emergency.toggle');
});

use App\Http\Middleware\IsOperator;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\MasterDataController;
use App\Http\Controllers\Operator\UserController as OperatorUserController;
use App\Http\Controllers\Operator\AcademicMappingController;
use App\Http\Controllers\Operator\AsesmenController;
use App\Http\Controllers\Operator\AsesmenApiController;

Route::middleware(['auth', IsOperator::class])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
    Route::post('/settings', [OperatorDashboardController::class, 'updateSettings'])->name('settings.update');

    
    Route::get('/master', [MasterDataController::class, 'index'])->name('master.index');
    Route::post('/master/manage', [MasterDataController::class, 'manage'])->name('master.manage');

    
    Route::get('/users', [OperatorUserController::class, 'index'])->name('users.index');
    Route::post('/users/guru', [OperatorUserController::class, 'storeGuru'])->name('users.guru.store');
    Route::put('/users/guru', [OperatorUserController::class, 'updateGuru'])->name('users.guru.update');
    Route::post('/users/siswa', [OperatorUserController::class, 'storeSiswa'])->name('users.siswa.store');
    Route::put('/users/siswa', [OperatorUserController::class, 'updateSiswa'])->name('users.siswa.update');
    Route::post('/users/import', [OperatorUserController::class, 'import'])->name('users.import');
    Route::get('/users/reset-password/{id}', [OperatorUserController::class, 'resetPassword'])->name('users.reset_password');
    Route::delete('/users/{id}', [OperatorUserController::class, 'destroy'])->name('users.destroy');

    
    Route::prefix('asesmen')->name('asesmen.')->group(function () {
        Route::get('/dashboard', [AsesmenController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [AsesmenController::class, 'jadwal'])->name('jadwal');
        Route::get('/hasil', [AsesmenController::class, 'hasil'])->name('hasil');
        Route::get('/portal', [AsesmenController::class, 'portal'])->name('portal');
        Route::get('/qc-soal', [AsesmenController::class, 'qcSoal'])->name('qc_soal');
        Route::get('/monitoring', [AsesmenController::class, 'monitoring'])->name('monitoring');

    });

    
    Route::post('/api/asesmen', [AsesmenApiController::class, 'handle'])->name('api.asesmen');

    
    Route::prefix('mapping')->name('mapping.')->group(function () {
        Route::get('/api/check-assignment', [AcademicMappingController::class, 'checkClassAvailability'])->name('api.check-assignment');
        Route::get('/curriculum', [AcademicMappingController::class, 'curriculumIndex'])->name('curriculum');
        Route::post('/curriculum', [AcademicMappingController::class, 'updateCurriculum'])->name('curriculum.update');
        Route::get('/competency', [AcademicMappingController::class, 'competencyIndex'])->name('competency');
        Route::post('/competency', [AcademicMappingController::class, 'updateCompetency'])->name('competency.update');
        Route::get('/assignments', [AcademicMappingController::class, 'assignmentsIndex'])->name('assignments');
        Route::post('/assignments', [AcademicMappingController::class, 'storeAssignment'])->name('assignments.store');
        Route::delete('/assignments/{id}', [AcademicMappingController::class, 'destroyAssignment'])->name('assignments.destroy');
        Route::get('/jadwal', [AcademicMappingController::class, 'jadwalIndex'])->name('jadwal');
        Route::post('/jadwal/store', [AcademicMappingController::class, 'storeJadwal'])->name('jadwal.store');
        Route::delete('/jadwal/{id}', [AcademicMappingController::class, 'destroyJadwal'])->name('jadwal.destroy');

        Route::get('/waktu', [AcademicMappingController::class, 'waktuIndex'])->name('waktu');
        Route::post('/waktu', [AcademicMappingController::class, 'storeWaktu'])->name('waktu.store');
        Route::put('/waktu/{id}', [AcademicMappingController::class, 'updateWaktu'])->name('waktu.update');
        Route::delete('/waktu/{id}', [AcademicMappingController::class, 'destroyWaktu'])->name('waktu.destroy');
    });

    
    Route::get('/ubah-password', [PasswordController::class, 'showForm'])->name('password.form');
    Route::post('/ubah-password', [PasswordController::class, 'update'])->name('password.update');

    
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'operatorIndex'])->name('profile.index');
    Route::post('/profil/foto', [\App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profil/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
});

use App\Http\Controllers\Guru\RuangKompetensiController as GuruRuangKompetensiController;
use App\Http\Controllers\Guru\SesiController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\DiskusiController;
use App\Http\Controllers\Guru\TugasController;
use App\Http\Controllers\Guru\MateriController;
use App\Http\Controllers\Guru\BankSoalController;
use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\Guru\AgendaController;
use App\Http\Controllers\Guru\RekapController;

Route::middleware(['auth', \App\Http\Middleware\IsGuru::class])->prefix('guru')->name('guru.')->group(function () {
    
    Route::get('/pilih-sesi', [SesiController::class, 'index'])->name('sesi.index');
    Route::post('/pilih-sesi', [SesiController::class, 'store'])->name('sesi.store');

    
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/pengumuman', [GuruDashboardController::class, 'storePengumuman'])->name('pengumuman.store');
    Route::delete('/dashboard/pengumuman', [GuruDashboardController::class, 'destroyPengumuman'])->name('pengumuman.destroy');

    
    Route::prefix('google')->name('google.')->group(function () {
        Route::get('/connect', [\App\Http\Controllers\Guru\GoogleAuthController::class, 'redirect'])->name('connect');
        Route::get('/callback', [\App\Http\Controllers\Guru\GoogleAuthController::class, 'callback'])->name('callback');
        Route::post('/disconnect', [\App\Http\Controllers\Guru\GoogleAuthController::class, 'disconnect'])->name('disconnect');
        Route::get('/picker-token', [\App\Http\Controllers\Guru\GoogleAuthController::class, 'getPickerToken'])->name('picker_token');
        Route::get('/resolve-form', [\App\Http\Controllers\Guru\GoogleAuthController::class, 'resolveForm'])->name('resolve_form');
    });

    
    Route::middleware([\App\Http\Middleware\CheckGuruSession::class])->group(function () {
        
        Route::get('/diskusi', [DiskusiController::class, 'index'])->name('diskusi');
        Route::get('/diskusi/arsip', [DiskusiController::class, 'arsip'])->name('diskusi.arsip');
        Route::post('/diskusi', [DiskusiController::class, 'store'])->name('diskusi.store');
        Route::post('/diskusi/{id}/reply', [DiskusiController::class, 'reply'])->name('diskusi.reply');
        Route::patch('/diskusi/{id}/toggle-pin', [DiskusiController::class, 'togglePin'])->name('diskusi.toggle_pin');
        Route::patch('/diskusi/{id}/toggle-status', [DiskusiController::class, 'toggleStatus'])->name('diskusi.toggle_status');
        Route::put('/diskusi/{id}', [DiskusiController::class, 'update'])->name('diskusi.update');
        Route::delete('/diskusi/{id}', [DiskusiController::class, 'destroy'])->name('diskusi.destroy');
        Route::delete('/diskusi/reply/{id}', [DiskusiController::class, 'destroyReply'])->name('diskusi.destroy_reply');
        Route::post('/diskusi/download-media', [DiskusiController::class, 'downloadMedia'])->name('diskusi.download_media');

        
        Route::get('/ruang-tugas', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'index'])->name('ruang_tugas.index');
        Route::post('/ruang-tugas', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'store'])->name('ruang_tugas.store');
        Route::get('/ruang-tugas/{id}', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'show'])->name('ruang_tugas.show');
        Route::put('/ruang-tugas/{id}', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'update'])->name('ruang_tugas.update');
        Route::delete('/ruang-tugas/{id}', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'destroy'])->name('ruang_tugas.destroy');
        Route::post('/ruang-tugas/toggle', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'toggleStatus'])->name('ruang_tugas.toggle');
        Route::post('/ruang-tugas/nilai/{id}', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'saveNilai'])->name('ruang_tugas.save_nilai');
        Route::post('/ruang-tugas/rekap/{id}', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'rekapMasukkan'])->name('ruang_tugas.rekap_masukkan');
        
        Route::get('/ruang-tugas/{id}/export-zip', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'exportZip'])->name('ruang_tugas.export_zip');
        
        Route::post('/ruang-tugas/revisi/{id}', [\App\Http\Controllers\Guru\TugasPengumpulanController::class, 'setRevisi'])->name('ruang_tugas.set_revisi');

        
        Route::get('/ujian', [BankSoalController::class, 'index'])->name('ujian');
        Route::post('/ujian/save-paket', [BankSoalController::class, 'storePaket'])->name('ujian.save_paket');
        Route::get('/ujian/manual', [BankSoalController::class, 'manualInput'])->name('ujian.manual');
        Route::get('/ujian/qc', [BankSoalController::class, 'qcPreview'])->name('ujian.qc');
        Route::post('/ujian/qc/kirim', [BankSoalController::class, 'qcKirim'])->name('ujian.qc.kirim');
        Route::post('/ujian/extract-gdocs', [BankSoalController::class, 'extractFromGdocs'])->name('ujian.extract_gdocs');
        Route::post('/ujian/save-asset', [BankSoalController::class, 'saveAsset'])->name('ujian.save_asset');
        Route::delete('/ujian/hapus-paket', [BankSoalController::class, 'destroyPacket'])->name('ujian.destroy_paket');

        
        Route::get('/ruang-kompetensi', [GuruRuangKompetensiController::class, 'index'])->name('ruang_kompetensi.index');
        Route::post('/ruang-kompetensi', [GuruRuangKompetensiController::class, 'store'])->name('ruang_kompetensi.store');
        Route::put('/ruang-kompetensi/{id}', [GuruRuangKompetensiController::class, 'update'])->name('ruang_kompetensi.update');
        Route::delete('/ruang-kompetensi/{id}', [GuruRuangKompetensiController::class, 'destroy'])->name('ruang_kompetensi.destroy');
        Route::patch('/ruang-kompetensi/toggle/{id}', [GuruRuangKompetensiController::class, 'toggleArchive'])->name('ruang_kompetensi.toggle');

        
        Route::get('/materi', [MateriController::class, 'index'])->name('materi');
        Route::post('/materi', [MateriController::class, 'store'])->name('materi.store');
        Route::put('/materi/{id}', [MateriController::class, 'update'])->name('materi.update');
        Route::delete('/materi/{id}', [MateriController::class, 'destroy'])->name('materi.destroy');

        
        Route::get('/absensi', [AbsensiController::class, 'create'])->name('absensi.create');
        Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');
        Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
        Route::get('/absensi/{id}/detail', [AbsensiController::class, 'getDetail'])->name('absensi.detail');
        Route::put('/absensi/{id}', [AbsensiController::class, 'update'])->name('absensi.update');
        Route::patch('/absensi/quick-update', [AbsensiController::class, 'quickUpdate'])->name('absensi.quick_update');
        Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');

        
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
        Route::put('/agenda/{id}', [AgendaController::class, 'update'])->name('agenda.update');
        Route::delete('/agenda/{id}', [AgendaController::class, 'destroy'])->name('agenda.destroy');

        
        Route::get('/tugas/input', [\App\Http\Controllers\Guru\TugasController::class, 'create'])->name('tugas.create');
        Route::post('/tugas/input', [\App\Http\Controllers\Guru\TugasController::class, 'store'])->name('tugas.store');
        Route::get('/tugas/rpp-data', [\App\Http\Controllers\Guru\TugasController::class, 'getRppData'])->name('tugas.rpp');

        Route::get('/tugas/rekap', [\App\Http\Controllers\Guru\RekapController::class, 'index'])->name('rekap.index');
        Route::get('/tugas/katrol-preview', [\App\Http\Controllers\Guru\RekapController::class, 'katrolPreview'])->name('rekap.katrol.preview');
        Route::post('/tugas/katrol-save', [\App\Http\Controllers\Guru\RekapController::class, 'katrolSave'])->name('rekap.katrol.save');
        Route::post('/tugas/update', [\App\Http\Controllers\Guru\RekapController::class, 'updateTugas'])->name('rekap.tugas.update');
        Route::delete('/tugas/delete/{id}', [\App\Http\Controllers\Guru\RekapController::class, 'destroyTugas'])->name('rekap.tugas.destroy');

        
        Route::get('/tugas/koreksi-siswa/{id}', [\App\Http\Controllers\Guru\RekapController::class, 'getNilaiSiswa'])->name('rekap.koreksi.get');
        Route::post('/tugas/koreksi-siswa/{id}', [\App\Http\Controllers\Guru\RekapController::class, 'saveNilaiSiswa'])->name('rekap.koreksi.save');
    });

    
    Route::get('/ubah-password', [PasswordController::class, 'showForm'])->name('password.form');
    Route::post('/ubah-password', [PasswordController::class, 'update'])->name('password.update');

    
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'guruIndex'])->name('profile.index');
    Route::post('/profil/foto', [\App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profil/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
});

use App\Http\Controllers\Kepsek\DashboardController as KepsekDashboardController;
use App\Http\Controllers\Kepsek\KinerjaController;
use App\Http\Controllers\Kepsek\UjianController as KepsekUjianController;
use App\Http\Controllers\Kepsek\LaporanController;

Route::middleware(['auth', \App\Http\Middleware\IsKepsek::class])->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('/dashboard', [KepsekDashboardController::class, 'index'])->name('dashboard');
    Route::get('/kinerja-guru', [KinerjaController::class, 'index'])->name('kinerja_guru');
    Route::get('/ujian-nilai', [KepsekUjianController::class, 'index'])->name('ujian_nilai');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan_akademik');

    
    Route::get('/ubah-password', [PasswordController::class, 'showForm'])->name('password.form');
    Route::post('/ubah-password', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware(['auth', \App\Http\Middleware\IsKepsek::class])->prefix('api/kepsek')->name('api.kepsek.')->group(function () {
    Route::get('/stats', [KepsekDashboardController::class, 'stats'])->name('stats');
    Route::get('/kinerja', [KinerjaController::class, 'data'])->name('kinerja');
    Route::get('/ujian', [KepsekUjianController::class, 'data'])->name('ujian');
    Route::get('/export', [LaporanController::class, 'export'])->name('export');
    
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
    
    Route::post('/soal/{id}/approve', [KepsekUjianController::class, 'approveSoal'])->name('soal.approve');
    Route::post('/soal/{id}/reject', [KepsekUjianController::class, 'rejectSoal'])->name('soal.reject');
});





use App\Http\Controllers\Api\ExamStatusController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\PilihMapelController;
use App\Http\Controllers\Siswa\TentangSayaController;
use App\Http\Controllers\Siswa\RuangFokusController;
use App\Http\Controllers\Siswa\RuangCatatanController;
use App\Http\Controllers\Siswa\RuangKompetensiController as SiswaRuangKompetensiController;

Route::middleware(['auth', \App\Http\Middleware\IsSiswa::class])->prefix('student')->name('student.')->group(function () {
    Route::get('/pilih-mapel', [PilihMapelController::class, 'index'])->name('pilih-mapel');
    Route::post('/set-mapel', [PilihMapelController::class, 'setMapel'])->name('set-mapel');

    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

    
    Route::get('/materi', [\App\Http\Controllers\Siswa\MateriController::class, 'index'])->name('materi.index');
    Route::get('/materi/bab/{bab}', [\App\Http\Controllers\Siswa\MateriController::class, 'bab'])->name('materi.bab');
    Route::get('/materi/detail/{id}', [\App\Http\Controllers\Siswa\MateriController::class, 'detail'])->name('materi.detail');

    
    Route::get('/tugas', [\App\Http\Controllers\Siswa\TugasController::class, 'index'])->name('tugas.index');
    Route::post('/tugas/submit', [\App\Http\Controllers\Siswa\TugasController::class, 'submit'])->name('tugas.submit');
    Route::post('/tugas/delete', [\App\Http\Controllers\Siswa\TugasController::class, 'deleteSubmission'])->name('tugas.delete');

    
    Route::get('/diskusi', [\App\Http\Controllers\Siswa\DiskusiController::class, 'index'])->name('diskusi.index');
    Route::match(['GET', 'POST'], '/diskusi/api', [\App\Http\Controllers\Siswa\DiskusiController::class, 'api'])->name('diskusi.api');

    
    Route::get('/tentang-saya', [TentangSayaController::class, 'index'])->name('tentang-saya.index');

    
    Route::get('/ubah-password', [PasswordController::class, 'showForm'])->name('password.form');
    Route::post('/ubah-password', [PasswordController::class, 'update'])->name('password.update');

    
    Route::get('/ruang-fokus', [RuangFokusController::class, 'index'])->name('ruang-fokus.index');

    
    Route::get('/ruang-catatan', [RuangCatatanController::class, 'index'])->name('ruang-catatan.index');

    
    Route::get('/ruang-kompetensi', [SiswaRuangKompetensiController::class, 'index'])->name('ruang_kompetensi.index');
    Route::get('/ruang-kompetensi/ujian/{id}', [SiswaRuangKompetensiController::class, 'ujian'])->name('ruang_kompetensi.ujian');

    
    Route::get('/notifikasi', [\App\Http\Controllers\Siswa\NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/redirect/{type}/{id}', [\App\Http\Controllers\Siswa\NotifikasiController::class, 'redirect'])->name('notifikasi.redirect');

    
    Route::get('/notifications/stream', [\App\Http\Controllers\Siswa\NotificationController::class, 'stream'])->name('notifications.stream');
});

Route::middleware(['auth', \App\Http\Middleware\IsSiswa::class])->prefix('api/student')->name('api.student.')->group(function () {
    Route::get('/pengumuman', [SiswaDashboardController::class, 'apiPengumuman'])->name('pengumuman');
    
    Route::get('/exam-status', [ExamStatusController::class, 'check'])->name('exam-status');
});





use App\Http\Controllers\Siswa\ExamController;
use App\Http\Controllers\Siswa\ExamStudentApiController;

Route::prefix('ruang-ujian')->name('exam.')->group(function () {
    Route::get('/', [ExamController::class, 'login'])->name('login');
    Route::get('/konfirmasi', [ExamController::class, 'summary'])->name('summary');
    Route::get('/arena', [ExamController::class, 'ujian'])->name('ujian');
    Route::get('/hasil', [ExamController::class, 'hasil'])->name('hasil');
});


Route::post('/api/exam/student', [ExamStudentApiController::class, 'handle'])->name('api.exam.student');




use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


Route::get('/emergency-migrate', function () {
    try {
        set_time_limit(300);

        
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        
        $jsonPath = base_path('database/seeders/ruang_kompetensi.json');
        if (File::exists($jsonPath)) {
            $jsonContent = File::get($jsonPath);
            $lines = explode("\n", trim($jsonContent));
            $inserted = 0;
            foreach ($lines as $line) {
                if (empty(trim($line)))
                    continue;
                $data = json_decode($line, true);
                if ($data && !DB::connection('mysql_apps')->table('ruang_kompetensi')->where('id', $data['id'])->exists()) {
                    DB::connection('mysql_apps')->table('ruang_kompetensi')->insert($data);
                    $inserted++;
                }
            }
            $output .= "\n\n[SEEDER] Berhasil merestore {$inserted} data ruang_kompetensi dari backup database 'laravel' lokal ke database 'lms_pembelajaran_database'!";
        }

        return '<h1>Migrasi Database & Sinkronisasi Berhasil!</h1><pre>' . $output . '</pre>';
    } catch (\Exception $e) {
        return '<h1>Error Migrasi:</h1><pre>' . $e->getMessage() . '</pre>';
    }
});
