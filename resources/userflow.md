# 🔄 GARA — User Flow & Program Logic

Dokumen ini menjelaskan alur interaksi setiap role (pengguna) dengan sistem GARA, beserta logika program di balik layar yang menangani interaksi tersebut.

---

## 🗂️ Daftar Isi

1. [Alur Global: Autentikasi & Routing](#1-alur-global-autentikasi--routing)
2. [User Flow: Super Admin](#2-user-flow-super-admin)
3. [User Flow: Operator](#3-user-flow-operator)
4. [User Flow: Kepala Sekolah (Kepsek)](#4-user-flow-kepala-sekolah-kepsek)
5. [User Flow: Guru](#5-user-flow-guru)
6. [User Flow: Siswa](#6-user-flow-siswa)
7. [User Flow: Ujian Online (Public + Token)](#7-user-flow-ujian-online-public--token)

---

## 1. Alur Global: Autentikasi & Routing

Semua pengguna memasuki sistem melalui satu pintu utama, namun dialihkan berdasarkan identitas mereka.

### 🌊 Flow (Sisi Pengguna)

1. Buka halaman utama `/`.
2. Sistem mengarahkan otomatis ke `/login`.
3. Masukkan `username` dan `password`.
4. Sistem memverifikasi data dan menampilkan loading/error message.
5. Jika berhasil, pengguna langsung diarahkan ke Dashboard khusus peran masing-masing.

### ⚙️ Logika Program (Sisi Sistem)

- **Middleware Global (`CheckMaintenance`)**: Mengecek tabel `app_settings` (`maintenance_mode`). Jika = '1', _bypass_ hanya untuk `super_admin`, yang lain dilempar error 503.
- **Validasi Kredensial**: `AuthController` memvalidasi tabel `users` (pada `mysql_auth`). Syarat: `password_hash` cocok DAN `status_aktif = 1`.
- **Session Setup**: Jika Auth sukses, sistem men-generate session ID baru (`regenerate()`) dan menyimpan `user_id`, `role_id`, dan string `role` di _PHP Session Storage_.
- **Role Director**: Sebuah fungsi internal `redirectBasedOnRole($role)` menentukan URL _redirect_ spesifik untuk setiap peran.

---

## 2. User Flow: Super Admin

Super admin mengelola infrastruktur sistem tingkat atas terkait akses dan visibilitas keseluruhan.

### 🌊 Flow (Sisi Pengguna)

- **Login** ➔ `/super-admin/dashboard`
- **Tugas Harian**:
    - Memantau jumlah _Users_ aktif.
    - Membuka halaman **Keamanan / Pengaturan** untuk memblokir IP perusuh.
    - Mengubah **Maintenance Mode** (misal saat malam minggu maintenance server).
    - Melakukan **Backup DB** via tombol UI.

### ⚙️ Logika Program (Sisi Sistem)

- **Middleware `IsSuperAdmin`**: Mengecek session role. Jika bukan `super_admin`, kirim ke 403 Forbidden.
- **Logika Keamanan/IP**: `SecurityController` memodifikasi tabel `ip_blocks` di `mysql_apps`.
- **Logika Pengaturan**: Membaca/menulis ke tabel `app_settings` yang sifatnya global untuk seluruh website.
- **Logika Backup**: `BackupController` secara _programmatic_ memanggil utilitas command shell (`mysqldump`) atau file archiver untuk menghasilkan file backup lalu menyedotnya ke direktori lokal sistem sebelum menjadikannya tautan _download_.

---

## 3. User Flow: Operator

Operator bertugas sebagai TU/Administrator yang menyiapkan _Environment_ sebelum Guru dan Siswa masuk sistem.

### 🌊 Flow (Sisi Pengguna)

- **Login** ➔ `/operator/dashboard`
- **Kegiatan 1: Setup Awal Semester**
    - Input/Upload Master Data Kelas dan Mata Pelajaran.
    - Buat akun Guru dan Siswa (beserta paspor/password-nya).
- **Kegiatan 2: Mapping Akademik**
    - Menggabungkan data: Kelas X mendapat mapel A dan masuk kelas X/MIPA-1 (ClassSubject).
    - Menggabungkan Guru A untuk mengajar Mapel B di Kelas X (TeachingAssignment).
- **Kegiatan 3: Musim Ujian**
    - Mengubah status pintu ujian ("BUKA PINTU").
    - Pantau live dashboard siapa yang sedang ujian.

### ⚙️ Logika Program (Sisi Sistem)

- **Logika Mapping (Core)**: Aplikasi melakukan operasi INSERT multiple ke Pivot Table (`class_subjects`, `teacher_subjects`, `teaching_assignments`) yang akan digunakan sebagai basis _Sesi_ saat guru/siswa login nanti.
- **Logika Ujian (Pintu)**: Meng-update nilai `status_pintu` di tabel `asesmen_config` (`mysql_asesmen`). Nilai ini dibaca terus-menerus oleh mesin ujian siswa saat mereka klik tombol "Mulai Ujian".

---

## 4. User Flow: Kepala Sekolah (Kepsek)

Kepala Sekolah tidak mengubah modul data primer (hanya Reviewer/Approver).

### 🌊 Flow (Sisi Pengguna)

- **Login** ➔ `/kepsek/dashboard`
- **Kegiatan 1: Persetujuan Ujian**
    - Masuk ke "Persetujuan Soal", melihat _Bank Soal_ bernotifikasi "Draft/Waiting".
    - Lihat kelayakan soal, lalu klik "Approve" (Hijau) atau "Reject" (Merah).
- **Kegiatan 2: Monitoring (Akhir Semester)**
    - Masuk ke "Ujian & Nilai".
    - Review seluruh skor siswa dari layar.
    - Saat ujian beres semua, tekan tombol "Grade Lock".
    - Download Laporan PDF.

### ⚙️ Logika Program (Sisi Sistem)

- **Persetujuan State**: Mengubah `status_soal` di tabel `bank_soal` ("DRAFT" ➔ "APPROVED"). Selama belum "APPROVED", soal tidak bisa dimunculkan di halaman ujian siswa.
- **Grade Lock**: Toggle setting global atau per-ujian. Setelah Locked, Middleware API Guru akan me-_reject_ metode `PUT /update_nilai` dari Guru.
- **Exporter PDF**: Sistem mem-fetch ribuan data, mem-pass data array ke _view PDF_, me-render HTML ke engine domPDF, dan memaksa stream ke browser pengguna.

---

## 5. User Flow: Guru

Role Guru adalah arsitektur dengan level logika paling mendalam. Seluruh pekerjaan tertambat pada ikatan "Sesi Mengajar".

### 🌊 Flow (Sisi Pengguna)

- **Langkah Wajib Pertama:**
    - Login berhasil ➔ `/guru/pilih-sesi` (Tidak langsung ke dashboard!).
    - Guru dihadapkan dropdown: "Pilih Mapel" & "Pilih Kelas". (Misal: Fisika - Kelas XI-1).
    - Submit. (Sekarang Guru masuk alam 'Sesi Fisika XI-1').
- **Rutinitas KBM:**
    - Buka Tab **Absensi**: Input yang hadir hari itu, simpan.
    - Buka Tab **Materi**: Cetak tombol "Upload Dokumen", pilih _File_.
    - Buka Tab **Ruang Tugas**: Buka slot penugasan, set deadline.
    - Waktu istirahat: Masuk mapel/kelas lain lewat ganti sesi di _Topbar_.

### ⚙️ Logika Program (Sisi Sistem)

- **Sesi Session-Storage**: `POST /pilih-sesi` menyimpan `sesi_mapel` dan `sesi_kelas` ke sesi PHP server RAM.
- **Middleware `CheckGuruSession`**: Segala klik guru ke (`/guru/absensi`, `/guru/tugas`, dsb.) harus lolos middleware ini. Jika variabel sesi PHP stringnya hilang/kosong, tolak akses dan pulangkan ke halaman `/pilih-sesi`.
- **Query Bounded**: Di Controller, kueri database (misal tarik absen siswa) tidak memakai hardcode, melainkan:
  `DB::table('siswa')->where('kelas_id', session('sesi_kelas'))->get();`
  Ini logika proteksi level 1 dari resiko salah-kamar pengisian nilai/file.

---

## 6. User Flow: Siswa

Siswa memiliki alur _User Experience_ khusus agar distraksi belajar tereduksi (konsep _Ruang Belajar_).

### 🌊 Flow (Sisi Pengguna)

- **Langkah Wajib**: Login ➔ `/student/pilih-mapel` (Klik Kotak: _Biologi_).
- **Rutinitas Harian**:
    - Klik notifikasi Lonceng: "Ada tugas Biologi baru!".
    - Buka Ruang Materi: Download PDF.
    - Buka Ruang Tugasku: Tarik berkas Word (Upload Jawaban). Submit.
    - Pengen Fokus: Ubah mode layar ke _Ruang Fokus_ (full screen text, notif di-mute).

### ⚙️ Logika Program (Sisi Sistem)

- **Validasi Mapel Aktif**: Setipe dengan Sesi Guru, variabel diset saat siswa klik mapel.
- **Interaksi API (Ruang Diskusi & Notifikasi)**: Mengandalkan JavaScript internal via fetch AJAX. Saat siswa mem-poll notifikasi/chat, terjadi request GET mikroskopis ke `/student/api/...` secara konstan/event-driven di balik layar.
- **Submit Tugas (File Handling)**: `POST /student/tugas/submit` melempar attachment. Controller mendaratkan file tersebut `Storage::put()`, membuat hash namanya (hindari bentrok nama tugas), lalu _path file_ (`storage/app/public/...`) dicatat stringnya ke database `nilai_tugas`.

---

## 7. User Flow: Ujian Online (Public + Token)

Ini adalah Modul _Exam Engine_ mandiri, yang bersifat _stateful machine_ untuk menjamin sekuritas penilain tinggi.

### 🌊 Flow (Sisi Pengguna)

1. Siswa mendapat Token Unik (contoh: "XJ9K12") dari Operator/Guru di Grup WhatsApp.
2. Siswa ke dashboard GARA >> Masuk ke Ruang Ujian (Sistem otomatis cross-check identitas sesi).
3. "Portal Ujian" muncul (jika Kepsek/Operator belum BUKA PINTU, layar ter-lock).
4. Pintu Buka: Muncul Ringkasan Aturan Ujian. Tombol "MULAI" ditekan.
5. Hitung Mundur Javascript mulai (misal 90 menit). Siswa memilih A/B/C/D/E.
6. Otomatis: Tiap 3 kali klik soal, _Saving..._ berkedip kecil di kiri layar.
7. Waktu habis (00:00) ➔ Layar "Force Submit", ditendang ke halaman "Selesai".

### ⚙️ Logika Program (Sisi Sistem)

- **Session Asing (Lepas Auth)**: Modul ujian route prefix `/ruang-ujian` DIBEBASKAN dari kungkungan Middleware autentikasi global, MELAINKAN memakai custom validasi session: `exam_student_id` & `token_valid`.
- **API Auto-Save**: Siswa klik Opsi A di Soal no 4 ➔ Browser kirim `POST /api/exam/student {save, no_4, opsi: A}`. Controller langsung _UPSERT_ (update bila ada, insert bila baru) temporary file response ke array DB. Tujuannya kalau siswa mati listrik, jawaban sebelumnya tetap utuh.
- **Grader Logic (Auto Submit)**: Fungsi terakhir `action=submit`. DB mencocokkan iterasi array opsi siswa dengan iterasi array `kunci_jawaban` `bank_soal`. Kalkulasi: `if(siswaAns == kunci) bobotSoal ++, skor+=bobot`. Hasil dimasukkan final ke `hasil_ujian`. Data jawaban tempo dihapus. Sesi Exam Student dihancurkan.
