# 📚 GARA — Garuda Akademi Platform

### Dokumentasi Teknis Komprehensif · Versi 2.0 · Mei 2026

---

> **GARA** (_Garuda Akademi_) adalah platform manajemen akademik berbasis web dan mobile API yang dirancang untuk lingkungan sekolah menengah. Platform ini mengintegrasikan seluruh ekosistem akademik — mulai dari manajemen pengguna, proses belajar-mengajar, sistem forum diskusi real-time, sistem asesmen/ujian digital dengan draft-recovery, hingga pelaporan kepala sekolah — dalam satu sistem terpadu dengan arsitektur multi-database, Laravel Sanctum stateless API (Flutter), PWA offline capabilities, dan kontrol akses berbasis peran (_Role-Based Access Control_).

---

## 🗂️ Daftar Isi

- [Tech Stack](#-tech-stack)
- [Arsitektur Sistem](#️-arsitektur-sistem)
- [Arsitektur Database Terperinci](#-arsitektur-database-terperinci)
- [Sistem RBAC (Role-Based Access Control)](#-sistem-rbac-role-based-access-control)
- [Modul Fitur per Role](#-modul-fitur-per-role)
- [Sistem Mobile API (Flutter Backend)](#-sistem-mobile-api-flutter-backend)
- [WebView Handoff Bridge (Session Sync)](#-webview-handoff-bridge-session-sync)
- [Mesin Ujian (Exam Engine) &amp; Draft Recovery](#-mesin-ujian-exam-engine--draft-recovery)
- [Fitur Keamanan &amp; Sistem Proteksi](#-fitur-keamanan--sistem-proteksi)
- [Progressive Web App (PWA)](#-progressive-web-app-pwa)
- [Storage &amp; Manajemen Assets](#-storage--manajemen-assets)
- [Struktur Routing Lengkap](#-struktur-routing-lengkap)
- [Struktur Direktori &amp; Statistik Proyek](#-struktur-diretori--statistik-proyek)

---

## 🛠️ Tech Stack

### Backend & Core

- **Framework:** Laravel `^12.0` (modern typed properties, modern accessors)
- **Bahasa Pemrograman:** PHP `^8.2`
- **Database Engine:** MySQL `^8.0` (3 koneksi terpisah)
- **API Authentication:** Laravel Sanctum (Bearer Token)
- **Google Integration:** `google/apiclient` & `google/apiclient-services` (OAuth 2.0, Drive API, Docs API)

### Frontend & PWA

- **Template Engine:** Blade (Native Laravel layouting)
- **Asset Bundler:** Vite (modern fast asset compilation)
- **Styling:** Vanilla CSS (Custom Harmony Design System)
- **Scripting:** Vanilla JavaScript (event listeners, asynchronous fetch, modular structure)
- **PWA Core:** Service Worker (`sw.js`) & Web App Manifest (`manifest.json`)

### Infrastructure & Operations

- **Server Environment:** Linux (Apache / Nginx)
- **Localhost / Dev Domain:** `.test`, `.local`
- **Database Backup System:** Native PHP-PDO Schema & Data Dump Engine (tanpa process spawning `mysqldump`, cocok untuk shared hosting)

---

## 🏗️ Arsitektur Sistem

GARA dirancang sebagai aplikasi **Stateful-Stateless Hybrid**. Aplikasi web konvensional berjalan sebagai stateful session monolith, sementara aplikasi mobile (Flutter) menggunakan stateless token-based API yang disinkronkan melalui WebView Session Bridge.

```
                  ┌────────────────────────────────────────────────────────┐
                  │              GARA Platform (Hybrid Monolith)           │
                  │                                                        │
                  │  ┌──────────┐  ┌──────────┐  ┌────────────┐            │
                  │  │ Web App  │  │  PWA     │  │ Mobile API │            │
                  │  │ (Session)│  │ (Offline)│  │  (Sanctum) │            │
                  │  └────┬─────┘  └────┬─────┘  └─────┬──────┘            │
                  │       │             │              │                   │
                  │  ┌────▼─────────────▼──────────────▼──────────────┐    │
                  │  │        HTTP Kernel & Middleware Stack          │    │
                  │  │  [CheckMaintenance] [CheckIpBlock] [Auth]      │    │
                  │  │  [IsGuru] [IsSiswa] [CheckGuruSession]         │    │
                  │  └────────────────────────┬───────────────────────┘    │
                  │                           │                            │
                  │  ┌────────────────────────▼───────────────────────┐    │
                  │  │               Controller Layer                 │    │
                  │  │   AuthController │ MobileHandoffController     │    │
                  │  └────────────────────────┬───────────────────────┘    │
                  │                           │                            │
                  │  ┌────────────────────────▼───────────────────────┐    │
                  │  │                 Eloquent ORM                   │    │
                  │  │     (Eager-loaded configurations & relations)  │    │
                  │  └──────┬─────────────────┬──────────────────────┬┘    │
                  │         │                 │                      │     │
                  │    mysql_auth        mysql_apps           mysql_asesmen│
                  │  (Users, RBAC,   (Academic Ops,        (Exam Systems,  │
                  │   AuditLogs,      Materi, Absensi,      JadwalUjian,   │
                  │   IP Blocks)      Pengumuman)           BankSoal,      │
                  │                                         UjianDrafts)   │
                  └────────────────────────────────────────────────────────┘
```

---

## 🗄️ Arsitektur Database

GARA menggunakan **tiga koneksi database fisik terisolasi** untuk menjamin keamanan, performa tinggi, dan kemudahan deployment modular.

### 1. Database `mysql_auth` (Koneksi: `mysql_auth`)

> Berisi data identitas pengguna, hak akses (RBAC), audit logging, dan manajemen IP Block.

- `users`: Menyimpan kredensial login seluruh pengguna.
    - _Kolom:_ `id`, `username`, `nama_lengkap`, `password_hash`, `role_id`, `status_aktif`, `profile_photo`
- `roles`: Menyimpan peran sistem (super_admin, operator, kepsek, guru, siswa).
- `permissions`: Menyimpan daftar hak akses granular.
- `role_permissions`: Pivot table pemetaan `roles` ↔ `permissions`.
- `personal_access_tokens`: Token autentikasi API Sanctum milik pengguna (diarahkan ke database auth agar terpusat).
- `guru_oauth_tokens`: Menyimpan token Google OAuth 2.0 milik guru.
    - _Kolom:_ `id_user` (Primary Key), `google_email`, `access_token`, `refresh_token`, `token_expiry`, `updated_at`
- `audit_logs`: Log aktivitas sistem demi keamanan sistem.
    - _Kolom:_ `id`, `user_id`, `user_type`, `action`, `module`, `ip_address`
- `ip_blocks`: Daftar alamat IP yang diblokir oleh Super Admin.
    - _Kolom:_ `id`, `ip_address`, `reason`

### 2. Database `mysql_apps` (Koneksi: `mysql_apps`)

> Mengelola data operasional harian sekolah, rekapitulasi, materi, dan pengumuman.

- `kelas` & `mata_pelajaran`: Master data kelas dan mapel.
- `guru` & `siswa`: Profil data akademik pelengkap yang berelasi ke `users`.
- `class_subjects` & `teacher_subjects`: Pemetaan kurikulum.
- `teaching_assignments`: Penugasan aktif guru per kelas + mapel.
- `absensi` & `absensi_detail`: Header dan detail kehadiran siswa per pertemuan.
- `agenda_harian`: Jurnal mengajar guru yang terikat langsung dengan data absensi.
- `rpp_materi`: Berkas/konten RPP dan materi pembelajaran guru.
- `nilai_tugas` & `tugas`: Soal tugas kelas dan rekap nilai pengumpulan tugas siswa.
- `tugas_pengumpulan`: File pengumpulan tugas yang dikirim oleh siswa.
- `ruang_kompetensis`: Sumber daya pembelajaran tambahan atau link evaluasi guru.
- `pengumuman`: Pengumuman penting sekolah / mata pelajaran.
    - _Kolom:_ `id`, `mapel_id` (nullable), `kelas_id` (nullable), `judul`, `isi`
- `app_settings`: Konfigurasi global key-value (misal `sekolah_nama`, `maintenance_mode`).

### 3. Database `mysql_asesmen` (Koneksi: `mysql_asesmen`)

> Mengelola data bank soal, aset soal, jadwal pelaksanaan ujian, dan rekapitulasi hasil ujian.

- `bank_soal`: Repository soal ujian yang diinput guru dan divalidasi operator.
    - _Kolom:_ `id_soal` (Primary), `id_guru`, `mapel`, `kelas`, `tipe_soal` (`PG`, `PGK`, `ISIAN`, `BS`, `BENAR_SALAH`), `konten_soal`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `opsi_e`, `kunci_jawaban`, `bobot`, `status_soal` (`DRAFT`, `VALIDATED`)
- `soal_assets`: Manajemen konten multimedia pendukung soal (gambar, audio, tautan external).
    - _Kolom:_ `id` (Primary), `bank_soal_id` (Foreign Key), `asset_type` (`local_image`, `external_image`, `youtube_link`, `audio_mp3`, `google_drive`), `asset_source` (path file/URL), `original_name`, `created_at`, `updated_at`
- `jadwal_ujian`: Jadwal pelaksanaan ujian beserta aturan pengerjaan.
    - _Kolom:_ `id_jadwal`, `mapel`, `kelas`, `tanggal_ujian`, `hari`, `jam_mulai`, `jam_selesai`, `durasi`, `jenis_asesmen`, `pengulangan` (`YA`/`TIDAK`), `tampilkan_jawaban` (`YA`/`TIDAK`), `tampilkan_nilai` (`YA`/`TIDAK`), `mode_submit` (`AUTO`/`MANDIRI`), `token`, `created_by`
- `asesmen_config`: Konfigurasi gerbang akses ujian.
    - _Kolom:_ `status_pintu` (0/1), `status_pintu_siswa` (0/1)
- `hasil_ujian`: Log pengumpulan jawaban akhir siswa beserta nilainya.
    - _Kolom:_ `id_hasil`, `id_siswa`, `id_jadwal`, `jawaban_user` (JSON), `skor_akhir`, `waktu_selesai`
- `ujian_drafts`: Fitur auto-save pengerjaan ujian real-time untuk mencegah kehilangan progress.
    - _Kolom:_ `id_siswa`, `id_jadwal`, `jawaban_draft` (JSON), `last_sync` (Primary gabungan `id_siswa` + `id_jadwal`)

---

## 🛡️ Sistem RBAC (Role-Based Access Control)

RBAC diimplementasikan secara berlapis untuk memastikan tingkat keamanan maksimal:

1. **Lapis Rute (Middleware):** Mengecek role user dari session/token.
2. **Lapis Model & Data:** Menyaring data berdasarkan kepemilikan/relasi user.

### Matriks Akses Fitur Terupdate

| Fitur / Modul                     | Super Admin |   Operator    |  Kepsek   |  Guru   |     Siswa     |
| :-------------------------------- | :---------: | :-----------: | :-------: | :-----: | :-----------: |
| **User CRUD & Status**            |   ✅ Full   | ✅ Siswa/Guru |    ❌     |   ❌    |      ❌       |
| **Role & Permission Custom**      |   ✅ Full   |      ❌       |    ❌     |   ❌    |      ❌       |
| **System Security & IP Block**    |   ✅ Full   |      ❌       |    ❌     |   ❌    |      ❌       |
| **Database Backup & Audit Logs**  |   ✅ Full   |      ❌       |    ❌     |   ❌    |      ❌       |
| **Emergency Control Toggle**      |   ✅ Full   |      ❌       |    ❌     |   ❌    |      ❌       |
| **Setup & Install Wizard Bypass** |   ✅ Full   |      ❌       |    ❌     |   ❌    |      ❌       |
| **Master Kurikulum & Mapping**    |     ❌      |    ✅ Full    |    ❌     |   ❌    |      ❌       |
| **Kontrol Pintu Ujian Global**    |     ❌      |    ✅ Full    |    ❌     |   ❌    |      ❌       |
| **Review & Approve Bank Soal**    |     ❌      |      ❌       | ✅ Review |   ❌    |      ❌       |
| **Grade Lock Control**            |     ❌      |      ❌       | ✅ Toggle |   ❌    |      ❌       |
| **Laporan Akademik & Export**     |     ❌      |      ❌       | ✅ Export |   ❌    |      ❌       |
| **Absensi & Agenda Harian**       |     ❌      |      ❌       |    ❌     | ✅ CRUD |      ❌       |
| **RPP, Tugas & Forum Diskusi**    |     ❌      |      ❌       |    ❌     | ✅ CRUD | ✅ Read/Write |
| **Google Drive Asset Picker**     |     ❌      |      ❌       |    ❌     | ✅ Full |      ❌       |
| **Google Doc Exam Extraction**    |     ❌      |      ❌       |    ❌     | ✅ Full |      ❌       |
| **Ujian Online & Draft Sync**     |     ❌      |      ❌       |    ❌     |   ❌    |    ✅ Full    |
| **Native Flutter API Suite**      |     ❌      |      ❌       |    ❌     |   ❌    |    ✅ Full    |

---

## 📦 Modul Fitur per Role

### 1. 👑 Super Admin

- **Prefix Route:** `/super-admin/` | **Middleware:** `IsSuperAdmin`
- **Panel Kontrol Global:** Mengelola konfigurasi `app_settings` (seperti toggle mode maintenance, perubahan nama sekolah).
- **Keamanan Lanjutan & IP Block:** Mencegah serangan brute-force atau scraping dengan mengelola daftar IP address yang diblokir langsung ke database `mysql_auth.ip_blocks`.
- **Emergency Mode:** Halaman khusus status darurat untuk membekukan sistem jika terjadi anomali atau kebocoran data.
- **Native PHP Database Backup:** System backup database cerdas berbasis PDO yang mengekspor schema + data dari database `auth_gara`, `lms_pembelajaran`, dan `asesmen_gara` secara dinamis dan aman ke folder `storage/app/backups/` tanpa memicu pemblokiran shell eksekusi oleh hosting provider.

### 2. 🖥️ Operator

- **Prefix Route:** `/operator/` | **Middleware:** `IsOperator`
- **Master Akademik:** CRUD Kelas, Mata Pelajaran, dan pemetaan guru pengajar (`teaching_assignments`).
- **Master Ujian (Asesmen):** Menyusun `jadwal_ujian` dan mengontrol "pintu masuk" ujian bagi siswa (`status_pintu` global dan `status_pintu_siswa`).
- **Hasil Evaluasi:** Mengakses dan mengekspor rekapitulasi nilai akhir seluruh siswa secara menyeluruh.

### 3. 🎓 Kepala Sekolah (Kepsek)

- **Prefix Route:** `/kepsek/` | **Middleware:** `IsKepsek`
- **Quality Control Ujian:** Menerima atau menolak draf soal yang dikirimkan guru dari menu persetujuan soal sebelum dirilis dalam ujian.
- **Grade Lock:** Melakukan penguncian rekapitulasi nilai agar guru pengajar tidak dapat memanipulasi atau mengubah nilai siswa setelah masa pengerjaan berakhir.
- **Report Generation:** Melakukan ekspor data rekapitulasi kinerja guru dan nilai siswa secara instan ke format PDF dan Excel.

### 4. 👨‍🏫 Guru

- **Prefix Route:** `/guru/` | **Middleware:** `IsGuru` + `CheckGuruSession`
- **Sistem Sesi Aktif:** Guru wajib memilih kelas dan mapel yang akan diajar terlebih dahulu untuk mengaktifkan sesi mengajar di state controller.
- **Absensi & Jurnal Mengajar:** Melakukan pengisian absensi kehadiran siswa terintegrasi langsung dengan agenda mengajar harian.
- **Materi & Tugas:** Mengupload modul pembelajaran RPP terintegrasi dengan Google Docs dan mengelola pengumpulan tugas siswa.
- **Manajemen Diskusi:** Bertindak sebagai moderator forum diskusi kelas (fitur pin, tutup thread, dan hapus balasan).
- **Exam Creator & Assets Integration:**
    - _Google Docs Parser:_ Mengekstrak draf pertanyaan ujian langsung dari dokumen Google Docs milik Guru secara cerdas.
    - _Google Drive Media Picker:_ Menghubungkan Google Drive Guru untuk mengambil gambar soal secara langsung dan menyimpannya sebagai file lokal di server.
    - _Multimedia Assets Manager:_ Menambahkan audio (MP3), video (YouTube Link), dan gambar eksternal yang dihubungkan ke record `soal_assets` MySQL.

### 5. 👨‍🎓 Siswa

- **Prefix Route:** `/student/` | **Middleware:** `IsSiswa`
- **Ruang Belajar Mandiri:** Mengakses ruang materi, tugas kelas, forum diskusi interaktif, ruang catatan personal, dan ruang fokus belajar distraction-free.
- **Ruang Asesmen:** Mengerjakan ujian terjadwal yang sedang dibuka pintunya oleh Operator menggunakan token resmi.
- **Notifikasi Real-time:** Menampilkan notifikasi pemberitahuan tugas baru, materi baru, thread diskusi baru, dan ruang evaluasi secara dinamis.

---

## 📱 Sistem Mobile API (Flutter Backend)

GARA menyediakan backend API berbasis token stateless menggunakan **Laravel Sanctum** untuk aplikasi mobile native (khususnya untuk dashboard dan notifikasi siswa).

### Rute API Terproteksi Sanctum (`routes/api.php`)

#### 1. Autentikasi Mobile (`POST /api/mobile/login`)

- _Deskripsi:_ Mengautentikasi pengguna secara stateless.
- _Mekanisme Keamanan:_
    1. Mengecek kecocokan kredensial di `mysql_auth.users`.
    2. Memvalidasi akun aktif (`status_aktif = 1`).
    3. Mengecek status maintenance mode (hanya super_admin yang lolos bypass).
    4. Menerapkan kebijakan **Single Device Login** (menghapus token Sanctum lama milik pengguna tersebut sebelum menerbitkan token baru).
    5. Menghasilkan Sanctum Bearer Token yang disimpan di `mysql_auth.personal_access_tokens`.
- _Response Data:_
    ```json
    {
        "status": "success",
        "token": "1|sanctum_generated_bearer_token_here",
        "user": {
            "id": 12,
            "username": "siswa123",
            "name": "Budi Santoso",
            "role": "siswa",
            "display_role": "Siswa"
        }
    }
    ```

#### 2. Logout Mobile (`POST /api/mobile/logout`)

- _Deskripsi:_ Menghapus token Sanctum aktif milik pengguna agar sesi mobile berakhir sepenuhnya.

#### 3. Detail Profil Siswa (`GET /api/mobile/profile`)

- _Deskripsi:_ Mengembalikan rangkuman performa siswa untuk dashboard aplikasi Flutter.
- _Response Data:_ Nama siswa, NIS, kelas aktif, poin prestasi, persentase kehadiran, jumlah tugas mandiri yang belum dikerjakan, dan URL avatar (DiceBear fallback).

#### 4. Mata Pelajaran Aktif (`GET /api/mobile/mapel`)

- _Deskripsi:_ Mengambil daftar pelajaran aktif untuk semester ini, mendeteksi nama sekolah dari `app_settings` dan bendera status pintu asesmen (`status_pintu_siswa`).

#### 5. Informasi Akun Terperinci (`GET /api/mobile/account-detail`)

- _Deskripsi:_ Mengambil detail nama lengkap, NIS, kelas, bendera pendeteksi password bawaan (apakah user masih menggunakan password default seperti `siswa123`), serta tahun ajaran & semester aktif saat ini.

#### 6. Notifikasi Agregat Native (`GET /api/student/notifications`)

- _Deskripsi:_ Menggabungkan data notifikasi dari 4 sumber operasional terpisah (`tugas`, `diskusi_threads`, `rpp_materi`, `RuangKompetensi`) dalam format data JSON yang siap dikonsumsi mobile.
- _Kebijakan Baca:_ Notifikasi ditandai belum dibaca (`is_read = 0`) secara cerdas jika usianya di bawah 24 jam.
- _Endpoints Pendukung:_
    - `POST /api/student/notifications/{id}/read`: Menandai status notifikasi telah dibaca.
    - `GET /api/student/notifications/unread-count`: Mengembalikan angka jumlah notifikasi baru di bawah 24 jam yang belum dibaca siswa.

---

## 🔗 WebView Handoff Bridge (Session Sync)

Salah satu keunggulan arsitektur GARA adalah **WebView Handoff Bridge** (`MobileHandoffController`). Fitur ini memungkinkan aplikasi mobile native (Flutter) membuka halaman web GARA yang kompleks (seperti Ujian Arena, Forum Diskusi Web, atau Ruang Fokus) di dalam WebView tanpa mengharuskan pengguna melakukan login ulang.

### Alur Kerja Handoff

```
[Flutter Native App]
  │  Mengambil Sanctum Token aktif milik pengguna.
  │  Membuka URL handoff di WebView:
  │  GET /auth/webview-handoff?token=TOKEN&target=/student/diskusi
  ▼
[Laravel Web Controller - MobileHandoffController@handoff]
  │
  ├─► 1. Validasi Token:
  │      Mencocokkan parameter token ke personal_access_tokens di database mysql_auth.
  │      Jika invalid: Redirect ke halaman error/login.
  │
  ├─► 2. Autentikasi Sesi Web:
  │      Melakukan Auth::login($user) untuk membuat sesi Cookie (session-based) pada PHP.
  │
  ├─► 3. Sinkronisasi Variabel Sesi:
  │      Mengisi $_SESSION dengan atribut krusial:
  │        - user_id, username, role_id, role
  │      Jika perannya adalah SISWA, merekonstruksi data akademik:
  │        - siswa_id, nama, kelas_id, nama_kelas
  │
  ├─► 4. Validasi Keamanan Redirect:
  │      Memvalidasi parameter target terhadap daftar putih (Allowlist Prefix).
  │      Hanya mengizinkan redirect ke folder internal:
  │      - /student/*, /ruang-ujian/*, /guru/*, /operator/*, /kepsek/*, /super-admin/*
  │      Mencegah Open Redirect Vulnerability.
  │
  ▼
[WebView Redirect]
  Memuat target url dengan autentikasi sesi web yang sudah aktif penuh.
```

---

## 🎯 Mesin Ujian (Exam Engine) & Draft Recovery

Exam Engine GARA adalah modul terisolasi tinggi di bawah prefix `/ruang-ujian/` yang beroperasi semi-independen.

### Alur Arena Ujian & Kompleksitas Scoring

Siswa memasuki arena pengerjaan soal dengan validasi ganda (Token Jurnal Ujian + Verifikasi NIS). Sistem mendukung variasi penilaian soal:

1. **PG (Pilihan Ganda):** Pencocokan string kunci tunggal (bobot penuh jika benar).
2. **PGK (Pilihan Ganda Kompleks):** Input jawaban berupa comma-separated values (CSV). Penilaian dihitung secara proporsional berdasarkan rasio jawaban yang benar dari total kunci jawaban.
3. **ISIAN & BS / Benar-Salah:** Pencocokan nilai string eksplisit.

### Mekanisme Draft Auto-Save & Recovery

Guna melindungi siswa dari kehilangan jawaban akibat putusnya koneksi internet atau baterai perangkat mati ditengah ujian, Exam Engine menerapkan **Draft Recovery System**:

- _Asynchronous Sync:_ Setiap kali siswa memilih/mengubah jawaban pada lembar soal web, client JavaScript mengirim request AJAX `POST` ke endpoint `save_draft` dengan payload data JSON dari seluruh daftar jawaban yang sudah dipilih.
- _Insert/Update Atomic:_ Di server, data disimpan di tabel `mysql_asesmen.ujian_drafts` menggunakan query `ON DUPLICATE KEY UPDATE` agar sinkronisasi draf berjalan instan tanpa overhead memori.
- _Failsafe Recovery:_ Jika perangkat siswa mati atau ter-refresh, sistem client akan memanggil `get_draft` saat masuk kembali ke arena ujian, memulihkan seluruh jawaban siswa ke keadaan terakhir sebelum terputus.

---

## 🔒 Fitur Keamanan & Sistem Proteksi

### 1. Sistem Proteksi IP Block (`CheckIpBlock`)

Aplikasi memfilter seluruh request masuk melalui middleware global `CheckIpBlock`.

- _Fungsi:_ Membandingkan IP Address pengirim dengan data hitam di `mysql_auth.ip_blocks`.
- _Reverse Proxy Support:_ Mendeteksi IP asli pengguna di balik Cloudflare/Nginx proxy menggunakan mapping `$request->ip()`.
- _Bypass Failsafe:_ Middleware otomatis melompati pemblokiran pada rute penting (halaman login, logout, emergency status, install wizard, static assets, dan rute super_admin) agar administrator tidak terkunci secara tidak sengaja.
- _Fail-Open Database Protection:_ Jika koneksi database mati, middleware mendeteksi exception dan meloloskan request (fail-open) untuk mencegah seluruh website tumbang akibat kendala koneksi database auth.

### 2. Bypass Maintenance Mode (`CheckMaintenance`)

- _Fungsi:_ Mengunci sistem untuk perawatan total.
- _Bypass:_ Menyediakan bypass khusus bagi pengguna dengan role `super_admin`. Super Admin tetap dapat login, mengakses dashboard pengaturannya, dan menonaktifkan maintenance mode saat sistem dinilai telah stabil.

### 3. Setup Configuration Wizard & Failsafe (`InstallController`)

- _Fungsi:_ Halaman `/install` mengonfigurasi awal database sistem lewat Artisan Command `gara:setup`.
- _Failsafe Protection:_
    - Wizard ini **wajib diblokir total** di lingkungan produksi (`APP_ENV=production`) atau ketika diakses dari host publik (hanya mengizinkan hostname localhost/loopback e.g. `127.0.0.1`, `localhost`, `.test`, `.local`).
    - Jika sistem mendeteksi database `mysql_auth` telah memiliki tabel `users`, sistem langsung me-redirect akses `/install` kembali ke halaman utama untuk mencegah instalasi ulang yang destruktif.

---

## 📱 Progressive Web App (PWA)

GARA dilengkapi dengan fitur PWA penuh untuk memberikan user experience premium layaknya native mobile app di browser, terutama saat koneksi internet tidak stabil.

### 1. Web App Manifest (`public/manifest.json`)

- Menyediakan instalabilitas aplikasi pada layar beranda perangkat Android/iOS/Desktop.
- _Konfigurasi:_ Diatur dalam orientasi `portrait-primary` dengan latar belakang gelap elegan `#0b0f19` dan warna tema biru premium `#0056b3`.
- _Branding:_ Menggunakan asset SVG modern resolusi tinggi: `FARA_BLACK.svg` (any purpose) dan `GARA_WHITE.svg` (maskable purpose).

### 2. Service Worker (`public/sw.js`)

Service worker menggunakan beberapa strategi caching cerdas untuk menjamin kecepatan pemuatan dan akses luring:

- **Bypass Dynamic Calls:** Seluruh request metode `POST`, serta rute API (`/api/`), modul ujian (`/exam/`, `/asesmen/`), Livewire (`/livewire/`), dan debug script otomatis dilewati tanpa caching agar data ujian tetap akurat dan dinamis.
- **Pre-caching Core Assets:** Menyimpan aset penting (seperti favicon, SVG logo, dan rute `/offline`) langsung pada fase instalasi service worker.
- **Cache-First (CDN Libraries):** Memprioritaskan pembacaan cache lokal untuk pustaka CDN pihak ketiga (seperti FontAwesome, Google Fonts, jsDelivr, unpkg) guna menghemat kuota internet dan mempercepat load time.
- **Stale-While-Revalidate (Static Local Assets):** Melakukan render instan dari cache lokal untuk gambar, CSS, dan file JS lokal di bawah folder `/assets/`, `/img/`, `/css/`, `/js/`, sembari melakukan fetch di background untuk memperbarui cache tersebut.
- **Offline Fallback Page:** Jika koneksi internet terputus sepenuhnya saat navigasi halaman, service worker menangkap error jaringan dan menyajikan halaman `/offline` yang dirancang elegan agar siswa mengetahui status koneksinya dengan jelas.

---

## 📦 Storage & Manajemen Assets

GARA mengelola direktori penyimpanan lokal secara ketat dengan validasi format berkas guna menghindari serangan unggahan shell script jahat.

### 1. Foto Profil Pengguna

- _Lokasi:_ `storage/app/public/profile_photos/`
- _Validasi:_ Hanya menerima format ekstensi `.jpg`, `.jpeg`, dan `.png` dengan batasan ukuran maksimal 2 MB per file (`ProfileController@updatePhoto`).
- _Fallback Avatar:_ Jika user belum mengupload foto, sistem menggunakan dynamic avatar API dari DiceBear dengan parameter nama lengkap pengguna agar tampilan UI tetap premium dan personal.

### 2. Aset Ujian (Exam Assets)

- _Lokasi:_ `storage/app/public/exam_assets/`
- _Validasi Lokal:_ Modul upload membagi tipe aset secara tegas (`BankSoalController@saveAsset`):
    - `local_image`: Hanya menerima ekstensi `.jpg`, `.jpeg`, `.png`, dan `.webp`.
    - `audio_mp3`: Wajib berformat audio `.mp3`.
- _Google Drive Integration:_ Ketika guru memilih berkas menggunakan Google Drive Picker, server mengunduh berkas tersebut lewat Google Drive API menggunakan access token guru, menyimpannya sebagai file lokal di direktori `exam_assets/`, dan memformat ulang penamaannya agar unik (`time() . '_gd_' . uniqid() . '.' . $ext`).

---

## 🗺️ Struktur Routing Lengkap

Berikut adalah ringkasan rute sistem GARA yang terbagi berdasarkan middleware pengaman:

```
Rute Publik / Setup
├── /                             → Redirect ke /login
├── /login                        → Form & Aksi Login (GET/POST)
├── /logout                       → Aksi Logout Sesi Web (POST)
├── /offline                      → Halaman Fallback Offline PWA
└── /install                      → Setup Wizard (Dilindungi host check & status DB)

Sesi Terotentikasi Web (session-based cookies)
├── /super-admin/                 → [IsSuperAdmin]
│   ├── /dashboard                → Panel Utama & Statistik
│   ├── /users                    → CRUD Pengguna Global
│   ├── /roles                    → CRUD Role & Pemetaan Permission
│   ├── /security                 → Manajemen IP Block
│   ├── /emergency                → Bekukan / Aktifkan status darurat
│   ├── /settings                 → Pengaturan Global app_settings
│   ├── /audit                    → Audit Trail Logs
│   └── /backup                   → Buat, Download, Hapus Backup DB (Native PDO)
│
├── /operator/                    → [IsOperator]
│   ├── /dashboard                → Panel Data & Ujian
│   ├── /master                   → Master Kelas & Mapel
│   ├── /users                    → CRUD Siswa & Guru
│   ├── /asesmen/dashboard        → Pemantauan Ujian
│   ├── /asesmen/jadwal           → Penyusunan Jadwal Asesmen
│   ├── /asesmen/hasil            → Rekap Nilai Ujian Siswa
│   ├── /asesmen/portal           → Buka/Tutup Pintu Ujian (Gate Control)
│   ├── /api/asesmen              → Endpoint AJAX Operasional Ujian
│   └── /mapping/...              → Pemetaan Kurikulum & Tugas Mengajar
│
├── /guru/                        → [IsGuru]
│   ├── /pilih-sesi               → Pilih Mapel + Kelas (Wajib di awal)
│   ├── /dashboard                → Dashboard & Pengumuman Kelas
│   └── [+ CheckGuruSession]      → Sesi Mengajar Aktif:
│       ├── /absensi              → CRUD Absensi & Detail Kehadiran Siswa
│       ├── /agenda               → Jurnal Mengajar Harian
│       ├── /materi               → Upload Modul & Bab Pembelajaran
│       ├── /ruang-tugas          → CRUD Slot Pengumpulan Tugas
│       ├── /diskusi              → Moderasi Forum Diskusi Kelas
│       ├── /ruang-kompetensi     → CRUD Link / Dokumen Pendukung Belajar
│       ├── /ujian                → Manual Input & Buat Soal Ujian (PG/PGK/dll)
│       ├── /ujian/extract-gdocs  → Ekstrak Soal dari Google Docs
│       ├── /ujian/save-asset     → Upload Media Soal (Local/Drive Picker)
│       └── /tugas/input|katrol   → Input, Rekap, & Katrol Nilai Siswa
│
├── /kepsek/                      → [IsKepsek]
│   ├── /dashboard                → Statistik Real-time AJAX Kinerja Sekolah
│   ├── /kinerja-guru             → Pemantauan Agenda & Kehadiran Guru
│   ├── /persetujuan              → Review & Approve Soal dari Guru
│   ├── /ujian-nilai              → Monitoring Nilai & Toggle Grade Lock
│   └── /laporan                  → Export Nilai Akhir ke PDF & Excel
│
└── /student/                     → [IsSiswa]
    ├── /pilih-mapel              → Pilih Mapel Aktif
    ├── /dashboard                → Notifikasi & Pengumuman
    ├── /materi                   → Akses Modul Pembelajaran per Bab
    ├── /tugas                    → Pengumpulan Tugas Kelas
    ├── /diskusi                  → Partisipasi Forum Diskusi Kelas
    ├── /ruang-kompetensi         → Akses Sumber Belajar Pilihan
    ├── /ruang-fokus              │   dan pengerjaan Ruang Ujian
    ├── /ruang-catatan            │   (dengan WebView Handoff Bridge)
    ├── /tentang-saya             → Profil Mandiri & Update Password
    └── /notifikasi               → Halaman Notifikasi In-App

Stateless Mobile API & Bridge (Sanctum Token Protected)
├── /auth/webview-handoff         → Bridge Sesi Web via Token Sanctum
└── /api/
    ├── /mobile/login             → Token Generation (Single-device policy)
    ├── /mobile/logout            → Token Revocation
    ├── /mobile/user              → User Identity Info
    ├── /mobile/mapel             → Subjects & Gate Status
    ├── /mobile/profile           → Dashboard Summary Siswa
    ├── /mobile/account-detail    → Akademik & Password Flag Info
    ├── /student/exam-status      → Lightweight Gate Status Check
    └── /student/notifications/...→ Agregat Notifikasi (CRUD & count)

Sesi Arena Ujian (Token & NIS Protected)
└── /ruang-ujian/
    ├── /                         → Validasi Token & NIS Login
    ├── /konfirmasi               → Konfirmasi Durasi & Mapel
    ├── /arena                    → Pengerjaan Soal (Auto-save draft)
    ├── /hasil                    → Tampilan Skor Akhir (Jika diizinkan)
    └── /api/exam/student         → Core Exam System Processing API
```

---

## 📁 Struktur Direktori & Statistik Proyek

```
gara/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php (Web Session Authenticator)
│   │   │   ├── InstallController.php (Setup Wizard Control)
│   │   │   ├── ProfileController.php (Global Profile & Avatar Update)
│   │   │   ├── SuperAdmin/ (7 controllers - security, emergency, backups)
│   │   │   ├── Operator/ (6 controllers - curriculum mapping, exam gates)
│   │   │   ├── Guru/ (11 controllers - sessions, Google integrations)
│   │   │   ├── Kepsek/ (5 controllers - reports, grade lock, QC bank soal)
│   │   │   ├── Siswa/ (12 controllers - student dashboards, exam engine)
│   │   │   └── Api/ (Stateless Sanctum endpoints, WebView handoff)
│   │   └── Middleware/ (7 Core Middlewares - CheckIpBlock, CheckMaintenance)
│   ├── Models/ (25 models - GaraPersonalAccessToken, Pengumuman, SoalAsset)
│   ├── Integrations/
│   │   └── Google/ (GoogleDocsParser, GoogleOAuthService, GoogleTokenManager)
│   └── Providers/ (AppServiceProvider - Sanctum model custom registration)
├── public/ (PWA sw.js, manifest.json, FARA_BLACK.svg, GARA_WHITE.svg)
├── resources/views/ (layouts, super_admin, operator, guru, kepsek, siswa, install)
├── routes/ (web.php, api.php)
└── composer.json (Laravel 12, PHP ^8.2, google/apiclient)
```

### Ringkasan Statistik Proyek Terbaru

- **Framework:** Laravel 12.x Monolith
- **Minimum PHP Engine:** PHP 8.2+
- **Total Struktur Role:** 5 Hak Akses Utama
- **Jumlah Koneksi Database:** 3 Database Terpisah (`mysql_auth`, `mysql_apps`, `mysql_asesmen`)
- **Middleware Keamanan Khusus:** 7 Berkas Middleware
- **Total Model Eloquent:** 25 Model Terdaftar
- **Total Controllers Kontrol:** 40+ Berkas Controller Aktif
- **Total Public Aset & PWA files:** 1,180+ Berkas Pendukung

---

_Dokumentasi teknis ini diperbarui secara menyeluruh dan komprehensif berdasarkan audit aktual kode sumber sistem GARA._
_Terakhir diperbarui: Mei 2026_
