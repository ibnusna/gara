# Software Requirement Specification (SRS)

## GARA — Garuda Akademi Learning Management System

> **Versi Dokumen:** 1.0
> **Tanggal:** April 2026
> **Disusun dari:** Analisis kode sumber, skema database, dan dokumentasi teknis sistem GARA
> **Cakupan Platform:** Sistem Manajemen Akademik berbasis Web untuk lingkungan Sekolah Menengah

---

## 1. Daftar Aktor dan Kebutuhan Fungsional

GARA mengimplementasikan **Role-Based Access Control (RBAC) dua lapis**: proteksi di tingkat rute (middleware) dan proteksi di tingkat izin (permission matrix). Sistem mendefinisikan **5 peran pengguna** yang masing-masing memiliki scope akses dan fitur eksklusif.

---

### 1.1 Aktor: Super Admin

**Prefix Rute:** `/super-admin/` | **Middleware:** `IsSuperAdmin`
**Deskripsi:** Pengelola platform tertinggi dengan akses penuh ke seluruh konfigurasi sistem.

| No    | Kebutuhan Fungsional                     | Deskripsi Teknis                                                                                                                        |
| ----- | ---------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| SA-01 | **Manajemen Pengguna (Full CRUD)** | Membuat, mengedit, menonaktifkan, dan menghapus akun seluruh role. Melakukan reset password paksa. Filter & pencarian berdasarkan role. |
| SA-02 | **Manajemen Role & Permissions**   | Membuat role baru secara dinamis. Assign/revoke permission ke role via tabel pivot `role_permissions`.                                |
| SA-03 | **Audit Log**                      | Membaca log seluruh aktivitas penting pengguna (tersimpan di `audit_logs`). Tidak dapat dihapus dari UI.                              |
| SA-04 | **Backup & Restore Database**      | Membuat backup ketiga database (`auth_gara`, `lms_pembelajaran`, `asesmen_gara`) via UI. Download file backup. Hapus backup lama. |
| SA-05 | **Toggle Maintenance Mode**        | Mengaktifkan/menonaktifkan maintenance mode via `app_settings`. Hanya Super Admin yang dapat login saat maintenance aktif.            |
| SA-06 | **IP Blocking**                    | Memblokir dan membuka blokir alamat IP mencurigakan. Data tersimpan di tabel `ip_blocks`.                                             |
| SA-07 | **Emergency Mode**                 | Mengaktifkan mode darurat sistem dengan satu klik (`POST /super-admin/emergency/toggle`).                                             |
| SA-08 | **Pengaturan Aplikasi**            | Mengonfigurasi nama sekolah dan pengaturan global lainnya via tabel `app_settings` (pola key-value).                                  |

---

### 1.2 Aktor: Operator

**Prefix Rute:** `/operator/` | **Middleware:** `IsOperator`
**Deskripsi:** Petugas tata usaha yang mengelola data akademik dan mengoperasikan sistem asesmen.

| No    | Kebutuhan Fungsional                           | Deskripsi Teknis                                                                                                        |
| ----- | ---------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| OP-01 | **Manajemen Master Data Kelas**          | CRUD data kelas (VII, VIII, IX) pada tabel `kelas`.                                                                   |
| OP-02 | **Manajemen Master Data Mata Pelajaran** | CRUD data mata pelajaran pada tabel `mata_pelajaran`.                                                                 |
| OP-03 | **Manajemen Akun Guru**                  | CRUD akun guru (nama, NIP, jabatan, username, password). Data tersimpan di tabel `guru` dan `users`.                |
| OP-04 | **Manajemen Akun Siswa**                 | CRUD akun siswa (nama, NIS, kelas, username, password). Data tersimpan di tabel `siswa` dan `users`.                |
| OP-05 | **Pemetaan Kurikulum**                   | Mapping kelas ↔ mata pelajaran (`/operator/mapping/curriculum`).                                                     |
| OP-06 | **Pemetaan Standar Kompetensi**          | Mapping standar kompetensi ke kurikulum (`/operator/mapping/competency`).                                             |
| OP-07 | **Penugasan Mengajar**                   | Assign guru ke kombinasi kelas + mata pelajaran (`/operator/mapping/assignments`).                                    |
| OP-08 | **Manajemen Jadwal Ujian**               | Membuat dan mengelola jadwal ujian per mapel/kelas. Data tersimpan di tabel `jadwal_ujian` pada DB `mysql_asesmen`. |
| OP-09 | **Dashboard & Rekap Hasil Ujian**        | Melihat rekap nilai semua siswa. Akses dashboard asesmen menyeluruh.                                                    |
| OP-10 | **Kontrol Portal Ujian (Pintu Ujian)**   | Membuka/menutup akses ruang ujian via kolom `status_pintu` pada tabel `asesmen_config`.                             |

---

### 1.3 Aktor: Kepala Sekolah (Kepsek)

**Prefix Rute:** `/kepsek/` + `/api/kepsek/` | **Middleware:** `IsKepsek`
**Deskripsi:** Peran monitoring dan persetujuan. Tidak dapat mengubah data akademik secara langsung, namun memiliki visibilitas penuh dan wewenang persetujuan/penguncian.

| No    | Kebutuhan Fungsional                          | Deskripsi Teknis                                                                                                                                       |
| ----- | --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| KS-01 | **Dashboard Real-Time**                 | Melihat statistik kinerja sekolah secara keseluruhan via AJAX (`GET /api/kepsek/stats`).                                                             |
| KS-02 | **Monitoring Kinerja Guru**             | Melihat data absensi mengajar, agenda jurnal, dan rekap ketepatan waktu guru (`GET /api/kepsek/kinerja`).                                            |
| KS-03 | **Review & Persetujuan Bank Soal**      | Mereview bank soal yang dibuat guru. Approve (`VALIDATED`) atau Reject soal ujian (`POST /api/kepsek/soal`).                                       |
| KS-04 | **Monitoring Hasil Ujian & Grade Lock** | Melihat hasil ujian semua siswa secara real-time. Mengunci/membuka nilai ujian (`POST /api/kepsek/gradelock`) sehingga tidak dapat diubah oleh guru. |
| KS-05 | **Ekspor Laporan Akademik**             | Mengekspor laporan akademik ke format**PDF** dan **Excel** (`GET /api/kepsek/laporan/export-pdf` & `export-excel`).                    |

---

### 1.4 Aktor: Guru

**Prefix Rute:** `/guru/` | **Middleware:** `IsGuru` + `CheckGuruSession`
**Deskripsi:** Role dengan fitur pembelajaran paling kaya. Seluruh fitur utama memerlukan **sesi aktif** (mapel & kelas yang dipilih) yang divalidasi oleh middleware `CheckGuruSession`.

| No    | Kebutuhan Fungsional                  | Deskripsi Teknis                                                                                                                                                                              |
| ----- | ------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| GR-01 | **Pemilihan Sesi Mengajar**     | Wajib memilih sesi (mapel + kelas) sebelum mengakses fitur lain (`POST /guru/pilih-sesi`). Sesi disimpan di PHP Session storage.                                                            |
| GR-02 | **Dashboard & Pengumuman**      | Melihat info sesi aktif. CRUD pengumuman yang ditampilkan kepada siswa (`POST/DELETE /guru/dashboard/pengumuman`).                                                                          |
| GR-03 | **Manajemen Absensi**           | Membuat sesi absensi baru, mengedit pokok bahasan & status kehadiran, menghapus sesi, melihat rekap dan detail per-sesi. Data tersimpan di `absensi` dan `absensi_detail`.                |
| GR-04 | **Agenda Mengajar (Jurnal)**    | Mencatat dan mengelola agenda/jurnal mengajar harian yang terasosiasi dengan record absensi. Data di tabel `agenda_harian`.                                                                 |
| GR-05 | **Manajemen Materi Belajar**    | Upload dan kelola konten materi pembelajaran terorganisir per-bab. CRUD pada `GET/POST/PUT/DELETE /guru/materi`. Data di tabel `rpp_materi`.                                              |
| GR-06 | **Ruang Tugas (Task Room)**     | Membuat slot pengumpulan tugas, toggle buka/tutup pengumpulan, melihat dan menilai hasil pengumpulan siswa.                                                                                   |
| GR-07 | **Ruang Diskusi (Moderator)**   | Membuat thread diskusi baru, membalas thread, me-pin/unpin, membuka/menutup thread, menghapus thread.                                                                                         |
| GR-08 | **Ruang Kompetensi**            | Mengelola sumber belajar (link, dokumen, referensi). Toggle arsip (aktif/nonaktif). CRUD pada `/guru/ruang-kompetensi`.                                                                     |
| GR-09 | **Bank Soal & Paket Ujian**     | Membuat soal (tipe PG, PG_KOMPLEKS, BENAR_SALAH, ISIAN) dengan kunci jawaban & bobot. Paketkan soal untuk ujian. Soal memiliki `status_soal` (DRAFT → SUBMITTED → VALIDATED oleh Kepsek). |
| GR-10 | **Input & Rekap Nilai Tugas**   | Menginput nilai siswa per tugas (`POST /guru/tugas/input`). Melihat rekap nilai lengkap.                                                                                                    |
| GR-11 | **Katrol Nilai (Normalisasi)**  | Preview dan simpan penyesuaian/katrol nilai akhir sebelum dikunci Kepsek.                                                                                                                     |
| GR-12 | **Edit/Hapus Nilai Individual** | Mengubah atau menghapus nilai individual siswa sebelum Grade Lock diaktifkan.                                                                                                                 |

---

### 1.5 Aktor: Siswa

**Prefix Rute:** `/student/` + `/api/student/` | **Middleware:** `IsSiswa`
**Deskripsi:** Pengguna akhir sistem pembelajaran dengan dashboard multi-ruang terfokus dan aman.

| No    | Kebutuhan Fungsional                     | Deskripsi Teknis                                                                                                                                                                             |                              |
| ----- | ---------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------- |
| SW-01 | **Pemilihan Mata Pelajaran Aktif** | Wajib memilih mata pelajaran sebelum masuk dashboard (`POST /student/set-mapel`). Pilihan disimpan di session.                                                                             |                              |
| SW-02 | **Dashboard Siswa**                | Melihat informasi akademik personal dan pengumuman dari guru secara real-time (AJAX:`GET /api/student/pengumuman`).                                                                        |                              |
| SW-03 | **Ruang Belajar (Materi)**         | Navigasi dan membaca materi per-bab. Akses detail materi individual.                                                                                                                         |                              |
| SW-04 | **Ruang Tugas**                    | Melihat daftar tugas dari guru. Submit file/jawaban tugas. Menghapus submission (sebelum deadline).                                                                                          |                              |
| SW-05 | **Ruang Diskusi**                  | Berpartisipasi dalam thread diskusi yang dibuka guru via API endpoint (`GET                                                                                                                  | POST /student/diskusi/api`). |
| SW-06 | **Ruang Kompetensi**               | Mengakses sumber belajar yang dikurasi guru dalam lingkungan aman (tanpa footer/tab baru).                                                                                                   |                              |
| SW-07 | **Ujian Online (Exam Engine)**     | Mengikuti ujian online diawali dengan login menggunakan token ujian + NIS, melewati halaman konfirmasi, mengerjakan soal pada arena ujian (dengan countdown timer), dan melihat hasil/nilai. |                              |
| SW-08 | **Ruang Fokus**                    | Area belajar distraction-free untuk konsentrasi.                                                                                                                                             |                              |
| SW-09 | **Ruang Catatan**                  | Area pencatatan personal siswa.                                                                                                                                                              |                              |
| SW-10 | **Profil & Ganti Password**        | Melihat profil pribadi dan mengubah password (`POST /student/tentang-saya/password`).                                                                                                      |                              |
| SW-11 | **Notifikasi In-App**              | Menerima dan membaca notifikasi dari sistem (`GET /student/notifikasi`).                                                                                                                   |                              |

---

## 2. Struktur Database (Skema Relasional)

GARA menggunakan **arsitektur multi-database** dengan **3 koneksi MySQL terpisah** untuk memisahkan domain data secara tegas demi keamanan dan skalabilitas.

---

### 2.1 Database `auth_gara` — Autentikasi & RBAC

Database inti yang mengelola identitas pengguna dan sistem kontrol akses.

#### Tabel `users` (PK: `id`)

| Kolom             | Tipe                    | Keterangan                                           |
| ----------------- | ----------------------- | ---------------------------------------------------- |
| `id`            | `INT AUTO_INCREMENT`  | Primary Key                                          |
| `username`      | `VARCHAR(100) UNIQUE` | Identifier login (NIS/NIP/username admin)            |
| `nama_lengkap`  | `VARCHAR(100)`        | Nama lengkap pengguna                                |
| `password_hash` | `VARCHAR(255)`        | Password di-hash menggunakan Bcrypt (`$2y$10$...`) |
| `role_id`       | `INT`                 | **FK → `roles.id`**                         |
| `status_aktif`  | `TINYINT(1)`          | `1` = aktif, `0` = nonaktif (login diblokir)     |
| `created_at`    | `TIMESTAMP`           | Waktu pembuatan akun                                 |

#### Tabel `roles` (PK: `id`)

| Kolom         | Tipe                   | Keterangan                                                           |
| ------------- | ---------------------- | -------------------------------------------------------------------- |
| `id`        | `INT AUTO_INCREMENT` | Primary Key                                                          |
| `role_name` | `VARCHAR(50) UNIQUE` | Nilai:`super_admin`, `operator`, `kepsek`, `guru`, `siswa` |

#### Tabel `permissions` (PK: `id`)

| Kolom           | Tipe                   | Keterangan                                                           |
| --------------- | ---------------------- | -------------------------------------------------------------------- |
| `id`          | `INT AUTO_INCREMENT` | Primary Key                                                          |
| `name`        | `VARCHAR(50) UNIQUE` | Nama izin (e.g.,`manage_users`, `grade.lock`, `report.export`) |
| `description` | `VARCHAR(255)`       | Deskripsi izin                                                       |

#### Tabel `role_permissions` (PK: Composite `role_id, permission_id`)

> Tabel pivot many-to-many antara Role dan Permission

| Kolom             | Tipe    | Keterangan                                           |
| ----------------- | ------- | ---------------------------------------------------- |
| `role_id`       | `INT` | **FK → `roles.id` ON DELETE CASCADE**       |
| `permission_id` | `INT` | **FK → `permissions.id` ON DELETE CASCADE** |

#### Tabel `guru` (PK: `id`)

| Kolom            | Tipe                   | Keterangan                                     |
| ---------------- | ---------------------- | ---------------------------------------------- |
| `id`           | `INT AUTO_INCREMENT` | Primary Key                                    |
| `user_id`      | `INT UNIQUE`         | **FK → `users.id` ON DELETE CASCADE** |
| `nip`          | `VARCHAR(20)`        | Nomor Induk Pegawai                            |
| `nama_lengkap` | `VARCHAR(100)`       | Nama lengkap guru                              |
| `jabatan`      | `VARCHAR(50)`        | Jabatan fungsional                             |
| `no_hp`        | `VARCHAR(20)`        | Nomor telepon                                  |
| `alamat`       | `TEXT`               | Alamat rumah                                   |

#### Tabel `siswa` (PK: `id`)

| Kolom        | Tipe                   | Keterangan                                       |
| ------------ | ---------------------- | ------------------------------------------------ |
| `id`       | `INT AUTO_INCREMENT` | Primary Key                                      |
| `user_id`  | `INT UNIQUE`         | **FK → `users.id`** (nullable)          |
| `nis`      | `VARCHAR(20) UNIQUE` | Nomor Induk Siswa                                |
| `nama`     | `VARCHAR(100)`       | Nama lengkap siswa                               |
| `kelas_id` | `INT`                | **FK → `kelas.id`**                     |
| `password` | `VARCHAR(255)`       | Password hash (redundan, untuk backward compat.) |

#### Tabel `kelas` (PK: `id`)

| Kolom          | Tipe                        | Keterangan      |
| -------------- | --------------------------- | --------------- |
| `id`         | `INT AUTO_INCREMENT`      | Primary Key     |
| `nama_kelas` | `ENUM('VII','VIII','IX')` | Tingkatan kelas |

#### Tabel `mata_pelajaran` (PK: `id`)

| Kolom          | Tipe                   | Keterangan          |
| -------------- | ---------------------- | ------------------- |
| `id`         | `INT AUTO_INCREMENT` | Primary Key         |
| `nama_mapel` | `VARCHAR(100)`       | Nama mata pelajaran |

#### Tabel `ip_blocks` (PK: `id`)

| Kolom          | Tipe                   | Keterangan              |
| -------------- | ---------------------- | ----------------------- |
| `id`         | `INT AUTO_INCREMENT` | Primary Key             |
| `ip_address` | `VARCHAR(45) UNIQUE` | Alamat IP yang diblokir |
| `reason`     | `VARCHAR(255)`       | Alasan pemblokiran      |

#### Tabel `audit_logs` (PK: `id`)

| Kolom          | Tipe                   | Keterangan                                                 |
| -------------- | ---------------------- | ---------------------------------------------------------- |
| `id`         | `INT AUTO_INCREMENT` | Primary Key                                                |
| `user_id`    | `INT`                | ID pengguna yang melakukan aksi                            |
| `user_type`  | `VARCHAR(50)`        | Role pengguna saat aksi dilakukan                          |
| `action`     | `VARCHAR(255)`       | Deskripsi aksi (e.g., "Maintenance mode ENABLED")          |
| `module`     | `VARCHAR(100)`       | Modul yang diakses (e.g., "Login/Auth", "Database Backup") |
| `ip_address` | `VARCHAR(45)`        | Alamat IP client                                           |
| `created_at` | `TIMESTAMP`          | Waktu aksi                                                 |

#### Diagram Relasi `auth_gara`

```
users ─────── belongsTo ─────► roles
roles ──── belongsToMany ─────► permissions (via role_permissions)
guru ──────── belongsTo ──────► users (user_id → users.id)
siswa ─────── belongsTo ──────► users (user_id → users.id)
siswa ─────── belongsTo ──────► kelas (kelas_id → kelas.id)
```

---

### 2.2 Database `lms_pembelajaran` — Data Operasional Akademik

Database yang menampung seluruh data aktivitas belajar-mengajar harian.

#### Tabel `absensi` (PK: `id`)

| Kolom             | Tipe                   | Keterangan                                              |
| ----------------- | ---------------------- | ------------------------------------------------------- |
| `id`            | `INT AUTO_INCREMENT` | Primary Key                                             |
| `mapel_id`      | `INT`                | **FK → `mata_pelajaran.id` ON DELETE CASCADE** |
| `kelas_id`      | `INT`                | **FK → `kelas.id` ON DELETE CASCADE**          |
| `tanggal`       | `DATE`               | Tanggal pertemuan                                       |
| `jam_mulai`     | `TIME`               | Jam mulai mengajar                                      |
| `jam_selesai`   | `TIME`               | Jam selesai mengajar                                    |
| `pertemuan_ke`  | `INT`                | Urutan pertemuan                                        |
| `pokok_bahasan` | `TEXT`               | Materi yang diajarkan                                   |
| `rangkuman`     | `TEXT`               | Rangkuman pembelajaran                                  |
| `metode`        | `VARCHAR(50)`        | Metode pengajaran (default: 'Tatap Muka')               |

#### Tabel `absensi_detail` (PK: `id`)

> Detail kehadiran per-siswa per-sesi absensi

| Kolom          | Tipe                          | Keterangan                                       |
| -------------- | ----------------------------- | ------------------------------------------------ |
| `id`         | `INT AUTO_INCREMENT`        | Primary Key                                      |
| `absensi_id` | `INT`                       | **FK → `absensi.id` ON DELETE CASCADE** |
| `siswa_id`   | `INT`                       | **FK → `siswa.id` ON DELETE CASCADE**   |
| `status`     | `ENUM('H','S','I','A','T')` | H=Hadir, S=Sakit, I=Izin, A=Alpha, T=Terlambat   |
| `keterangan` | `VARCHAR(255)`              | Keterangan tambahan                              |
| UNIQUE         | `(absensi_id, siswa_id)`    | Satu siswa satu status per sesi                  |

#### Tabel `agenda_harian` (PK: `id`)

| Kolom                   | Tipe                   | Keterangan                            |
| ----------------------- | ---------------------- | ------------------------------------- |
| `id`                  | `INT AUTO_INCREMENT` | Primary Key                           |
| `mapel_id`            | `INT`                | **FK → `mata_pelajaran.id`** |
| `kelas_id`            | `INT`                | **FK → `kelas.id`**          |
| `tanggal`             | `DATE`               | Tanggal agenda                        |
| `jam`                 | `TIME`               | Jam pelaksanaan                       |
| `rencana_kegiatan`    | `TEXT`               | Rencana kegiatan pembelajaran         |
| `catatan_pelaksanaan` | `TEXT`               | Catatan hasil pelaksanaan             |
| `siswa_tidak_hadir`   | `LONGTEXT`           | JSON array ID siswa yang tidak hadir  |

#### Tabel `rpp_materi` (PK: `id`)

> Konten materi belajar yang diorganisir per-bab dan per-bagian

| Kolom            | Tipe                   | Keterangan                            |
| ---------------- | ---------------------- | ------------------------------------- |
| `id`           | `INT AUTO_INCREMENT` | Primary Key                           |
| `mapel_id`     | `INT`                | **FK → `mata_pelajaran.id`** |
| `kelas_id`     | `INT`                | **FK → `kelas.id`**          |
| `semester`     | `INT`                | Semester 1 atau 2                     |
| `id_materi`    | `VARCHAR(50) UNIQUE` | Kode unik materi                      |
| `bab`          | `INT`                | Nomor bab                             |
| `bagian`       | `INT`                | Nomor bagian dalam bab                |
| `judul_materi` | `VARCHAR(255)`       | Judul materi                          |
| `link_ppt`     | `TEXT`               | Link ke file presentasi               |
| `link_youtube` | `TEXT`               | Link ke video YouTube                 |
| `link_modul`   | `TEXT`               | Link ke modul PDF                     |
| `link_tugas`   | `TEXT`               | Link tugas terkait                    |

#### Tabel `tugas` (PK: `id`)

| Kolom                | Tipe                                           | Keterangan                                              |
| -------------------- | ---------------------------------------------- | ------------------------------------------------------- |
| `id`               | `INT AUTO_INCREMENT`                         | Primary Key                                             |
| `mapel_id`         | `INT`                                        | **FK → `mata_pelajaran.id` ON DELETE CASCADE** |
| `kelas_id`         | `INT`                                        | **FK → `kelas.id` ON DELETE CASCADE**          |
| `rpp_id`           | `INT`                                        | FK ke RPP terkait (nullable)                            |
| `tugas_ke`         | `INT`                                        | Urutan tugas                                            |
| `tanggal`          | `DATE`                                       | Tanggal penugasan                                       |
| `tipe_tugas`       | `ENUM('Individual','Kelompok')`              | Tipe pengerjaan                                         |
| `kategori_asesmen` | `ENUM('Formatif','Sumatif','P5','Remedial')` | Kategori penilaian                                      |
| `teknik_penilaian` | `ENUM('Tes Tulis','Performa','Proyek',...)`  | Teknik evaluasi                                         |
| UNIQUE               | `(mapel_id, kelas_id, tugas_ke)`             | Mencegah duplikasi nomor tugas                          |

#### Tabel `nilai_tugas` (PK: `id`)

| Kolom        | Tipe                     | Keterangan                                     |
| ------------ | ------------------------ | ---------------------------------------------- |
| `id`       | `INT AUTO_INCREMENT`   | Primary Key                                    |
| `tugas_id` | `INT`                  | **FK → `tugas.id` ON DELETE CASCADE** |
| `siswa_id` | `INT`                  | **FK → `siswa.id` ON DELETE CASCADE** |
| `nilai`    | `DECIMAL(5,2)`         | Nilai siswa untuk tugas tersebut               |
| UNIQUE       | `(tugas_id, siswa_id)` | Satu nilai per siswa per tugas                 |

#### Tabel `ujian_sesi` (PK: `id`)

| Kolom               | Tipe                       | Keterangan                                    |
| ------------------- | -------------------------- | --------------------------------------------- |
| `id`              | `INT AUTO_INCREMENT`     | Primary Key                                   |
| `judul_ujian`     | `VARCHAR(255)`           | Judul sesi ujian                              |
| `kode_unik_sesi`  | `VARCHAR(50)`            | Kode mapping ke target kelas (e.g.,`IPA_7`) |
| `mapel_id`        | `INT`                    | **FK → `mata_pelajaran.id`**         |
| `kelas_id`        | `INT`                    | **FK → `kelas.id`**                  |
| `durasi_menit`    | `INT`                    | Durasi ujian dalam menit                      |
| `kode_akses`      | `VARCHAR(20)`            | Token akses ujian                             |
| `tampilkan_kunci` | `ENUM('YA','TIDAK')`     | Tampilkan kunci jawaban setelah ujian         |
| `izinkan_ulang`   | `ENUM('YA','TIDAK')`     | Izinkan pengulangan ujian                     |
| `status`          | `ENUM('MUNCUL','TIDAK')` | Visibilitas sesi                              |

#### Tabel `ujian_bank_soal` (PK: `id`)

> Soal-soal yang terasosiasi dengan satu sesi ujian

| Kolom                        | Tipe                                               | Keterangan                                            |
| ---------------------------- | -------------------------------------------------- | ----------------------------------------------------- |
| `id`                       | `INT AUTO_INCREMENT`                             | Primary Key                                           |
| `ujian_id`                 | `INT`                                            | **FK → `ujian_sesi.id` ON DELETE CASCADE**   |
| `nomor_urut`               | `INT`                                            | Urutan tampil soal                                    |
| `tipe_soal`                | `ENUM('PG','PG_KOMPLEKS','BENAR_SALAH','ISIAN')` | Tipe soal                                             |
| `konten_soal`              | `LONGTEXT`                                       | Isi soal (mendukung HTML/Base64 gambar)               |
| `opsi_a` hingga `opsi_e` | `TEXT`                                           | Pilihan jawaban A–E                                  |
| `kunci_jawaban`            | `TEXT`                                           | Kunci jawaban (bisa koma-separated untuk PG_KOMPLEKS) |

#### Tabel `nilai_ujian_cbt` (PK: `id`)

| Kolom            | Tipe                   | Keterangan                              |
| ---------------- | ---------------------- | --------------------------------------- |
| `id`           | `INT AUTO_INCREMENT` | Primary Key                             |
| `ujian_id`     | `INT`                | ID sesi ujian                           |
| `siswa_id`     | `INT`                | ID siswa                                |
| `nis`          | `VARCHAR(20)`        | Snapshot NIS saat ujian                 |
| `nama_siswa`   | `VARCHAR(100)`       | Snapshot nama saat ujian                |
| `jawaban_full` | `LONGTEXT`           | JSON string array seluruh jawaban siswa |
| `nilai_akhir`  | `DECIMAL(5,2)`       | Nilai hasil ujian                       |
| `percobaan_ke` | `INT`                | Nomor percobaan ujian                   |

#### Tabel `raw_scores` dan `final_scores`

> Menyimpan komponen nilai buku rapor per siswa per mapel per semester

| Kolom Utama                                                         | Keterangan                          |
| ------------------------------------------------------------------- | ----------------------------------- |
| `siswa_id`                                                        | FK ke siswa                         |
| `mapel_id` / `kelas_id`                                         | Konteks akademik                    |
| `nilai_absensi` / `nilai_tugas` / `nilai_uts` / `nilai_uas` | Komponen nilai mentah               |
| `nilai_akhir_raw` / `nilai_akhir_final`                         | Agregasi nilai                      |
| `batas_kkm`                                                       | Batas nilai KKM                     |
| `status`                                                          | `ENUM('Lulus','Butuh Pembinaan')` |

#### Diagram Relasi `lms_pembelajaran`

```
absensi ─────── hasMany ──────► absensi_detail
absensi ─────── belongsTo ────► mata_pelajaran (mapel_id)
absensi ─────── belongsTo ────► kelas (kelas_id)
agenda_harian ─ belongsTo ────► mata_pelajaran, kelas
rpp_materi ──── belongsTo ────► mata_pelajaran, kelas
tugas ──────────belongsTo ────► mata_pelajaran, kelas
nilai_tugas ─── belongsTo ────► tugas (tugas_id), siswa (siswa_id)
ujian_sesi ──── hasMany ──────► ujian_bank_soal (ujian_id)
nilai_ujian_cbt belongsTo ────► ujian_sesi
raw_scores ──── belongsTo ────► siswa, mata_pelajaran, kelas
final_scores ── hasOne ───────► raw_scores (raw_score_id)
```

---

### 2.3 Database `asesmen_gara` — Engine Ujian Online

Database terpisah khusus untuk sistem ujian formal berbasis token.

#### Tabel `jadwal_ujian` (PK: `id_jadwal`)

| Kolom                 | Tipe                   | Keterangan                             |
| --------------------- | ---------------------- | -------------------------------------- |
| `id_jadwal`         | `INT AUTO_INCREMENT` | Primary Key                            |
| `mapel`             | `VARCHAR(100)`       | Nama mata pelajaran (denormalized)     |
| `kelas`             | `VARCHAR(50)`        | Nama kelas (denormalized)              |
| `tanggal_ujian`     | `DATE`               | Tanggal pelaksanaan ujian              |
| `jam_mulai`         | `TIME`               | Jam mulai ujian                        |
| `durasi`            | `INT`                | Durasi dalam menit                     |
| `pengulangan`       | `ENUM('YA','TIDAK')` | Izinkan pengerjaan ulang               |
| `tampilkan_jawaban` | `ENUM('YA','TIDAK')` | Tampilkan kunci jawaban setelah submit |
| `token`             | `VARCHAR(10) UNIQUE` | Token akses ujian (unik per jadwal)    |
| `created_by`        | `INT`                | ID Operator yang membuat jadwal        |

#### Tabel `bank_soal` (PK: `id_soal`)

| Kolom                        | Tipe                                                          | Keterangan                          |
| ---------------------------- | ------------------------------------------------------------- | ----------------------------------- |
| `id_soal`                  | `INT AUTO_INCREMENT`                                        | Primary Key                         |
| `id_guru`                  | `INT`                                                       | **FK → `users.id` (Guru)** |
| `mapel`                    | `VARCHAR(100)`                                              | Mata pelajaran soal                 |
| `kelas`                    | `VARCHAR(50)`                                               | Target kelas                        |
| `tipe_soal`                | `ENUM('PG','PG_KOMPLEKS','ISIAN','BENAR_SALAH','PGK','BS')` | Tipe soal                           |
| `konten_soal`              | `LONGTEXT`                                                  | Isi soal (mendukung HTML)           |
| `opsi_a` hingga `opsi_e` | `TEXT`                                                      | Pilihan jawaban                     |
| `kunci_jawaban`            | `TEXT`                                                      | Kunci (bisa koma-separated)         |
| `bobot`                    | `INT`                                                       | Bobot nilai soal                    |
| `status_soal`              | `ENUM('DRAFT','SUBMITTED','VALIDATED')`                     | Status validasi soal oleh Kepsek    |

#### Tabel `hasil_ujian` (PK: `id_hasil`)

| Kolom             | Tipe                   | Keterangan                                           |
| ----------------- | ---------------------- | ---------------------------------------------------- |
| `id_hasil`      | `INT AUTO_INCREMENT` | Primary Key                                          |
| `id_siswa`      | `INT`                | **FK → user_id Siswa**                        |
| `id_jadwal`     | `INT`                | **FK → `jadwal_ujian.id_jadwal`**           |
| `jawaban_user`  | `LONGTEXT`           | JSON array jawaban siswa (divalidasi `JSON_VALID`) |
| `skor_akhir`    | `FLOAT`              | Nilai akhir hasil ujian                              |
| `waktu_mulai`   | `TIMESTAMP`          | Waktu mulai mengerjakan                              |
| `waktu_selesai` | `TIMESTAMP`          | Waktu submit jawaban                                 |

#### Tabel `asesmen_config` (PK: `id_config`)

| Kolom             | Tipe                    | Keterangan                                          |
| ----------------- | ----------------------- | --------------------------------------------------- |
| `id_config`     | `INT AUTO_INCREMENT`  | Primary Key                                         |
| `status_pintu`  | `TINYINT(1)`          | `0` = Close, `1` = Open (gerbang masuk ujian)   |
| `jenis_asesmen` | `ENUM('ASTS','ASAS')` | Jenis asesmen yang sedang berlangsung               |
| `opened_by`     | `INT`                 | **FK → user_id Operator** yang membuka pintu |

#### Diagram Relasi `asesmen_gara`

```
jadwal_ujian ─── hasMany ─────► hasil_ujian (id_jadwal)
bank_soal ─────── (dimiliki guru via id_guru, di-package ke jadwal_ujian)
asesmen_config ── (singleton config, dikontrol Operator)
hasil_ujian.jawaban_user → JSON terstruktur [{id_soal, jawaban}]
```

---

## 3. Tech Stack dan Aturan Sistem (Non-Fungsional)

### 3.1 Technology Stack

#### Backend

| Komponen   | Teknologi                             | Versi             |
| ---------- | ------------------------------------- | ----------------- |
| Framework  | **Laravel**                     | `^12.0`         |
| Language   | **PHP**                         | `^8.2`          |
| ORM        | **Eloquent ORM**                | Native Laravel 12 |
| Auth Guard | **Laravel Native Session Auth** | Multi-Guard       |
| Queue      | **Laravel Queue Worker**        | Native            |
| Testing    | PHPUnit, Mockery                      | `^11.5.3`       |
| Dev Tools  | Laravel Tinker, Pail, Pint, Sail      | Latest            |

#### Frontend

| Komponen        | Teknologi                                    |
| --------------- | -------------------------------------------- |
| Template Engine | **Blade** (Laravel native)             |
| Asset Bundler   | **Vite**                               |
| Styling         | **Vanilla CSS** + Custom Design System |
| Scripting       | **Vanilla JavaScript**                 |
| Package Manager | **npm**                                |

#### Infrastructure

| Komponen         | Teknologi                                       |
| ---------------- | ----------------------------------------------- |
| Web Server       | **Apache / Nginx** (`/var/www/html/`)   |
| Database Engine  | **MySQL 8.0** (3 instance terpisah)       |
| Database Charset | `utf8mb4_0900_ai_ci` / `utf8mb4_unicode_ci` |
| Session Storage  | **PHP Session** (file-based, server-side) |
| File Storage     | **Laravel `storage/`** (local disk)     |
| Background Jobs  | `php artisan queue:listen`                    |

### 3.2 Arsitektur Multi-Database

Setiap model Eloquent mendeklarasikan `$connection` secara eksplisit:

| Koneksi           | Database             | Model yang Menggunakan                                                                                   |
| ----------------- | -------------------- | -------------------------------------------------------------------------------------------------------- |
| `mysql_auth`    | `auth_gara`        | `User`, `Role`, `Permission`, `Guru`, `Siswa`                                                  |
| `mysql_apps`    | `lms_pembelajaran` | `Absensi`, `RppMateri`, `Tugas`, `NilaiTugas`, `AppSetting`, `AuditLog`, `RuangKompetensi` |
| `mysql_asesmen` | `asesmen_gara`     | `BankSoal`, `JadwalUjian`, `AsesmenConfig`, `HasilUjian`                                         |

### 3.3 Batasan Sistem (Constraints Non-Fungsional)

#### Keamanan (Security)

| Aturan                         | Implementasi Teknis                                                                                                                      |
| ------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------- |
| **Password Hashing**     | Algoritma Bcrypt (`$2y$10$` / `$2y$12$`) via `Hash::make()` Laravel                                                                |
| **Session Regenerate**   | `$request->session()->regenerate()` setiap login berhasil                                                                              |
| **Session Invalidate**   | `$request->session()->invalidate()` saat logout                                                                                        |
| **CSRF Protection**      | Token CSRF wajib pada seluruh form `POST`, `PUT`, `PATCH`, `DELETE`                                                              |
| **Status Akun**          | Login hanya diizinkan jika `status_aktif = 1`. Pengecekan dilakukan **sebelum** `Auth::attempt()`                              |
| **Grade Lock**           | Nilai yang dikunci Kepsek tidak dapat diubah oleh guru (immutable flag)                                                                  |
| **IP Blocking**          | Alamat IP mencurigakan dapat diblokir secara manual oleh Super Admin                                                                     |
| **Exam Token Isolation** | Ruang ujian (`/ruang-ujian/`) berjalan dengan proteksi token + exam session, **tidak bergantung** pada cookie auth utama Laravel |

#### Kontrol Akses (Access Control)

| Aturan                       | Implementasi Teknis                                                                                                              |
| ---------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **Route-Level Guard**  | Seluruh kelompok rute diproteksi middleware role-spesifik sebelum masuk controller                                               |
| **Session Guru**       | Guru wajib memiliki sesi mapel+kelas aktif (`CheckGuruSession`) untuk mengakses fitur pembelajaran                             |
| **Exam Session**       | `exam_student_id` disimpan di PHP session setelah login ujian; API `POST /api/exam/student` hanya valid jika session ini ada |
| **Maintenance Bypass** | Super Admin dapat melewati halaman maintenance; seluruh role lain mendapat HTTP 503                                              |

#### Performa & Skalabilitas

| Aturan                       | Detail                                                                                                               |
| ---------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| **Response Strategy**  | Controller mengembalikan**Blade view** untuk navigasi halaman dan **JSON** untuk interaksi AJAX (hybrid) |
| **Eager Loading**      | Model `User` selalu _eager-load_ relasi `role` untuk menghindari query N+1                                     |
| **Database Indexing**  | Index diimplementasikan pada seluruh kolom FK (`mapel_id`, `kelas_id`, `siswa_id`, `nis`)                    |
| **Database Isolation** | Tiga database terpisah memungkinkan setiap DB di-_host_ di server berbeda secara independen                        |

#### Audit & Integritas Data

| Aturan                       | Detail                                                                                                                                                     |
| ---------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Audit Trail**        | Seluruh aksi penting dicatat otomatis di `audit_logs` (tidak dapat dihapus dari UI non-admin)                                                            |
| **JSON Validation**    | Kolom `jawaban_user` pada `hasil_ujian` divalidasi dengan `CHECK (json_valid(...))` di level database                                                |
| **UNIQUE Constraints** | Diterapkan pada:`users.username`, `siswa.nis`, `jadwal_ujian.token`, `nilai_tugas (tugas_id, siswa_id)`, `absensi_detail (absensi_id, siswa_id)` |
| **Cascade Delete**     | Relasi anak (detail absensi, nilai tugas) dihapus otomatis saat data induk dihapus                                                                         |

---

## 4. Logika Sistem dan Flow Program

### 4.1 Alur Registrasi & Autentikasi (Login Flow)

Sistem menggunakan **single login form** (`/login`) dengan redirect dinamis berbasis role.

```
[1] User membuka aplikasi
         │
         ▼
[2] GET /login
    → Tampilkan form login
    → Nama sekolah diambil dari DB: AppSetting WHERE setting_key='sekolah_nama'
         │
  [User mengisi username + password]
         │
         ▼
[3] POST /login (Proses backend):
    a. Cek status_aktif = 1 (sebelum Auth::attempt)
       → Jika 0: Kembalikan respons 401 tanpa detail
    b. Cek maintenance_mode (dari app_settings)
       → Jika aktif dan bukan super_admin: Redirect ke halaman maintenance (503)
    c. Auth::attempt({username, password})
       → Jika gagal: Kembalikan pesan "Kredensial salah"
    d. Session::regenerate() untuk mencegah session fixation
    e. Set session: user_id, username, role_id, role_name
    f. Catat ke audit_logs: "User berhasil login"
    g. Return JSON: { status: 'success', role: '...', redirect: '/...' }
         │
         ▼
[4] Frontend menangkap JSON dan redirect ke URL berdasarkan role:
    super_admin → /super-admin/dashboard
    operator    → /operator/dashboard
    kepsek      → /kepsek/dashboard
    guru        → /guru/pilih-sesi        ← Wajib pilih sesi dulu
    siswa       → /student/pilih-mapel    ← Wajib pilih mapel dulu
         │
         ▼
[5] POST /logout:
    Auth::logout()
    Session::invalidate()
    Session::regenerateToken()
    → Redirect ke /
```

---

### 4.2 Alur Sesi Kontekstual Guru

Guru tidak langsung masuk dashboard setelah login, melainkan **wajib memilih konteks** (mapel + kelas):

```
Login berhasil → Redirect ke /guru/pilih-sesi
                        │
              [Guru memilih mapel & kelas]
                        │
                        ▼
             POST /guru/pilih-sesi
             → Simpan ke PHP session:
               session('guru_mapel_id') = X
               session('guru_kelas_id') = Y
                        │
                        ▼
             Redirect ke /guru/dashboard
             (Middleware CheckGuruSession sekarang valid)
                        │
                        ▼
   Seluruh akses fitur guru (absensi, materi, tugas, bank soal)
   → Difilter berdasarkan mapel_id & kelas_id dari session
```

---

### 4.3 Alur Distribusi Materi & Pengumpulan Tugas

```
GURU:
[1] Guru akses /guru/materi
[2] Upload/buat konten materi (judul, bab, link PPT/YouTube/modul)
[3] Data tersimpan di tabel rpp_materi
         │
         ▼
SISWA:
[4] Siswa pilih mata pelajaran aktif → /student/set-mapel
[5] Siswa akses /student/materi
    → Materi difilter berdasarkan mapel & kelas siswa
[6] Siswa buka materi per-bab → detail konten tersedia

TUGAS:
[7] Guru membuat slot tugas di /guru/ruang-tugas
    → Data tersimpan di tabel tugas (mapel_id, kelas_id, kategori, teknik)
[8] Siswa melihat daftar tugas di /student/tugas
[9] Siswa submit file/jawaban → data pengumpulan tersimpan
[10] Guru melihat submission → menginput nilai di /guru/tugas/input
     → Data tersimpan di tabel nilai_tugas (tugas_id, siswa_id, nilai)
[11] Guru dapat melakukan katrol (normalisasi) nilai sebelum dikunci Kepsek
[12] Kepsek mengaktifkan Grade Lock → nilai tidak dapat diubah
```

---

### 4.4 Alur Ujian Online (Exam Engine Flow)

Exam Engine adalah modul semi-mandiri yang beroperasi di prefix `/ruang-ujian/` **tanpa bergantung pada middleware Auth Laravel standar**:

```
[1] Operator buat jadwal ujian di /operator/asesmen/jadwal
    → Token unik (5-10 karakter) digenerate → Simpan di jadwal_ujian.token
    → Soal dipilih dari bank_soal yang sudah VALIDATED oleh Kepsek

[2] Operator buka "Pintu Ujian" di /operator/asesmen/portal
    → UPDATE asesmen_config SET status_pintu = 1

[3] Siswa akses /student/ruang-kompetensi/ujian/{id}
    → Diarahkan ke /ruang-ujian/ (public prefix)

[4] GET /ruang-ujian/ → Halaman Login Ujian
    Siswa memasukkan: NIS + Token Ujian
         │
         ▼
[5] Validasi backend:
    a. Cek token valid → cari di jadwal_ujian WHERE token = ?
    b. Cek asesmen_config.status_pintu = 1 (pintu terbuka)
    c. Cek identitas NIS siswa
    → Berhasil: Set exam session (exam_student_id, exam_jadwal_id)
         │
         ▼
[6] GET /ruang-ujian/konfirmasi
    → Review soal & instruksi ujian
         │
         ▼
[7] GET /ruang-ujian/arena
    → Tampilkan lembar soal dengan countdown timer
    → Soal diacak dari bank_soal yang terpilih
    → Siswa menjawab soal PG / PG_KOMPLEKS / BENAR_SALAH / ISIAN

[8] POST /api/exam/student (Submit Jawaban)
    → Validasi: exam session harus valid (hanya bisa dipanggil dari dalam arena)
    → Simpan jawaban_user (JSON array) ke tabel hasil_ujian
    → Hitung skor_akhir berdasarkan bobot soal
         │
         ▼
[9] GET /ruang-ujian/hasil
    → Tampilkan nilai akhir
    → Tampilkan kunci jawaban (jika jadwal_ujian.tampilkan_jawaban = 'YA')
    → Exam session di-clear
```

---

### 4.5 Alur Persetujuan Soal (Kepsek Workflow)

```
Guru buat soal → status_soal = 'DRAFT'
        │
        ▼
Guru submit soal → status_soal = 'SUBMITTED'
        │
        ▼
Kepsek review di /kepsek/persetujuan
GET /api/kepsek/soal → Tampilkan soal berstatus SUBMITTED
        │
   ┌────┴────┐
APPROVE    REJECT
   │          │
   ▼          ▼
'VALIDATED'  'DRAFT'
(Siap dipakai   (Dikembalikan
 dalam ujian)    ke guru)
```

---

### 4.6 Middleware Stack & Request Lifecycle

```
HTTP Request Masuk
        │
        ▼
[Global] CheckMaintenance
    → Cek app_settings WHERE setting_key='maintenance_mode'
    → Jika '1' dan bukan super_admin → Tampilkan halaman 503
        │
        ▼
[Route Group] auth (Laravel default)
    → Verifikasi session dan token autentikasi
        │
        ▼
[Route Specific] IsSuperAdmin / IsOperator / IsKepsek / IsGuru / IsSiswa
    → Verifikasi role_name pengguna dari relasi users→roles
    → Jika tidak sesuai → abort(403)
        │
        ▼
[Sub-Route, Guru Only] CheckGuruSession
    → Verifikasi session('guru_mapel_id') dan session('guru_kelas_id') ada
    → Jika tidak ada → Redirect ke /guru/pilih-sesi
        │
        ▼
Controller → Model (Eloquent, multi-DB) → Database → Response
(Blade View / JSON)
```

---

_Dokumen ini disusun berdasarkan analisis langsung terhadap kode sumber GARA Platform, termasuk skema database (`auth_gara_schema.sql`, `lms_pembelajaran_schema.sql`, `asesmen_gara_schema.sql`) dan dokumentasi teknis komprehensif (`GARA_DOKUMENTASI.md`)._
_Dibuat: April 2026_
