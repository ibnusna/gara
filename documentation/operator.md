# Panduan Operasional Operator Sistem GARA (Garuda Akademi)

Selamat datang di Dokumentasi Resmi Operator GARA. Panduan ini dirancang secara sistematis untuk mempermudah Operator dalam mengoperasikan, mengelola, dan mengonfigurasi seluruh modul akademik serta sistem asesmen ujian terpadu di dalam platform GARA.

Dokumen ini disusun sebagai panduan tekstual terintegrasi yang siap dipasang pada modul pusat bantuan (help desk) berbasis web, lengkap dengan daftar isi navigasi interaktif.

---

## Daftar Isi

- [1. Ikhtisar &amp; Navigasi Antarmuka](#1-ikhtisar--navigasi-antarmuka)
    - [1.1 Akses &amp; Login Operator](#11-akses--login-operator)
    - [1.2 Struktur Navigasi Sidebar](#12-struktur-navigasi-sidebar)
- [2. Dashboard &amp; Pemantauan Sistem](#2-dashboard--pemantauan-sistem)
    - [2.1 Metrik Ringkasan Utama](#21-metrik-ringkasan-utama)
    - [2.2 Pemantauan Waktu Nyata (Live Clock)](#22-pemantauan-waktu-nyata-live-clock)
- [3. Manajemen Akun (Guru &amp; Siswa)](#3-manajemen-akun-guru--siswa)
    - [3.1 Pengelolaan Akun Guru](#31-pengelolaan-akun-guru)
    - [3.2 Pengelolaan Akun Siswa](#32-pengelolaan-akun-siswa)
    - [3.3 Fitur Reset Password &amp; Keamanan Akun](#33-fitur-reset-password--keamanan-akun)
- [4. Data Master (Kelas &amp; Mata Pelajaran)](#4-data-master-kelas--mata-pelajaran)
    - [4.1 Pengelolaan Data Kelas](#41-pengelolaan-data-kelas)
    - [4.2 Pengelolaan Data Mata Pelajaran (Mapel)](#42-pengelolaan-data-mata-pelajaran-mapel)
    - [4.3 Peringatan Dampak Penghapusan Data Master](#43-peringatan-dampak-penghapusan-data-master)
- [5. Akademik, Kurikulum &amp; Mapping Penugasan](#5-akademik-kurikulum--mapping-penugasan)
    - [5.1 Pemetaan Kurikulum Kelas](#51-pemetaan-kurikulum-kelas)
    - [5.2 Pemetaan Kompetensi Mata Pelajaran Guru](#52-pemetaan-kompetensi-mata-pelajaran-guru)
    - [5.3 Penugasan Mengajar Guru](#53-penugasan-mengajar-guru)
    - [5.4 Deteksi &amp; Resolusi Konflik Penugasan Mengajar](#54-deteksi--resolusi-konflik-penugasan-mengajar)
- [6. Ruang Asesmen &amp; Ujian](#6-ruang-asesmen--ujian)
    - [6.1 Manajemen Gerbang Akses (Gate Controller)](#61-manajemen-gerbang-akses-gate-controller)
    - [6.2 Pengaturan Jenis Asesmen Global (ASTS &amp; ASAS)](#62-pengaturan-jenis-asesmen-global-asts--asas)
    - [6.3 Pembuatan Jadwal Ujian Secara Masal (Batch Scheduling)](#63-pembuatan-jadwal-ujian-secara-masal-batch-scheduling)
    - [6.4 Pengelolaan &amp; Pembuatan Token Ujian](#64-pengelolaan--pembuatan-token-ujian)
    - [6.5 Portal Pengawasan Ujian Aktif (Live Exam Monitoring)](#65-portal-pengawasan-ujian-aktif-live-exam-monitoring)
    - [6.6 Pengolahan Nilai &amp; Ekspor Hasil Ujian](#66-pengolahan-nilai-ekspor-hasil-ujian)
- [7. Modul Quality Control (QC) Soal](#7-modul-quality-control-qc-soal)
    - [7.1 Fungsi &amp; Peran QC Soal](#71-fungsi--peran-qc-soal)
    - [7.2 Antarmuka Panel QC Soal](#72-antarmuka-panel-qc-soal)
    - [7.3 Tinjauan Karakteristik Tipe Soal](#73-tinjauan-karakteristik-tipe-soal)
    - [7.4 Prosedur Validasi Masal Paket Soal](#74-prosedur-validasi-masal-paket-soal)
- [8. Pengelolaan Profil &amp; Akun Operator](#8-pengelolaan-profil--akun-operator)
    - [8.1 Pembaruan Foto Profil (AJAX Upload)](#81-pembaruan-foto-profil-ajax-upload)
    - [8.2 Prosedur Keamanan Mengubah Password](#82-prosedur-keamanan-mengubah-password)
- [9. Panduan Troubleshooting &amp; Solusi Masalah Kerja](#9-panduan-troubleshooting--solusi-masalah-kerja)

---

## 1. Ikhtisar & Navigasi Antarmuka

Portal Operator merupakan pusat kendali operasional harian yang menghubungkan manajemen administrasi akademik dengan pelaksanaan ujian online. Operator memiliki peran krusial sebagai validator kualitas konten (QC) dan pengawas jalannya asesmen secara _real-time_.

### 1.1 Akses & Login Operator

Untuk masuk ke dalam panel Operator:

1. Buka peramban (browser) dan akses alamat URL portal login GARA sekolah Anda.
2. Masukkan nama pengguna (**Username**) dan kata sandi (**Password**) akun Operator Anda yang telah terdaftar.
3. Sistem secara otomatis akan mendeteksi tingkat otorisasi Anda melalui middleware `IsOperator` dan mengarahkan Anda ke halaman Dashboard Operator.

### 1.2 Struktur Navigasi Sidebar

Sidebar kiri merupakan navigasi utama untuk mengakses seluruh fungsi sistem GARA. Struktur menu dirancang secara logis sebagai berikut:

- **Dashboard**: Menampilkan statistik umum dan jalan pintas cepat ke berbagai fitur utama.
- **Manajemen User**:
    - **Data Guru & Siswa**: Kelola data pengguna, pencarian NIS/NIP, pendaftaran akun baru, hingga reset kata sandi default.
- **Asesmen**:
    - **Dashboard Asesmen**: Pusat kendali gerbang akses guru/siswa, jenis asesmen aktif (ASTS/ASAS), dan ringkasan antrean soal.
    - **Jadwal Ujian**: Halaman untuk merencanakan waktu ujian secara masal (batch), konfigurasi default aturan pengerjaan, dan pembuatan token.
    - **Hasil Ujian**: Panel pencarian rekap skor akhir siswa, riwayat waktu pengerjaan, penghapusan lembar jawaban, dan ekspor data nilai terpadu.
- **Akademik**:
    - **Kurikulum Kelas**: Menentukan relasi penugasan mata pelajaran wajib pada masing-masing kelas.
    - **Kompetensi Guru**: Pengaturan hak mengajar guru terhadap subjek pelajaran tertentu.
    - **Penugasan Guru**: Lembar rekapitulasi relasi mengajar aktif serta jalur hapus penugasan mengajar individu.
- **Data Master (Mapel/Kelas)**: Konfigurasi dasar sistem untuk menambah, mengubah, atau menghapus entitas Kelas dan Mata Pelajaran secara global.
- **Akun**:
    - **Tentang Saya**: Menampilkan detail profil Operator saat ini dan antarmuka unggah foto profil mandiri.
    - **Ubah Password**: Formulir mandiri untuk memperbarui kata sandi demi menjaga keamanan kredensial akun.

---

## 2. Dashboard & Pemantauan Sistem

Halaman Dashboard merupakan beranda utama Operator saat pertama kali berhasil melakukan autentikasi ke dalam sistem. Desain antarmuka menggunakan konsep modern yang interaktif, menyajikan data metrik ringkas secara visual serta informatif.

### 2.1 Metrik Ringkasan Utama

Terdapat empat kartu statistik utama di bagian atas Dashboard yang berfungsi sebagai pemantauan cepat performa operasional:

1. **Pengguna (Guru & Siswa)**: Menunjukkan statistik jumlah pengguna aktif di dalam sistem. Kartu ini terhubung langsung ke modul Manajemen User.
2. **Akademik Mapping**: Menampilkan informasi ringkas terkait status penugasan pengajaran guru di kelas. Kartu ini terhubung langsung ke modul Penugasan Guru.
3. **Ruang Asesmen**: Menyajikan status pelaksanaan ujian atau jumlah agenda asesmen saat ini. Kartu ini terhubung langsung ke Dashboard Asesmen.
4. **Data Master**: Menampilkan jumlah entitas data dasar (Mata Pelajaran dan Kelas) yang terdaftar. Kartu ini terhubung langsung ke Data Master.

### 2.2 Pemantauan Waktu Nyata (Live Clock)

Di dalam banner sambutan utama, terdapat komponen **Live Clock** yang memperlihatkan indikasi waktu server saat ini dalam format jam, menit, dan detik secara presisi. Indikator jam server ini sangat krusial sebagai acuan Operator saat mencocokkan waktu mulai dan selesai pada penjadwalan ujian di sistem agar sinkron dengan waktu pelaksanaan fisik di sekolah.

---

## 3. Manajemen Akun (Guru & Siswa)

Modul ini memfasilitasi Operator untuk melakukan tata kelola administrasi akun bagi Guru dan Siswa, memastikan setiap civitas akademika memiliki hak akses yang valid dan aman.

### 3.1 Pengelolaan Akun Guru

Operator memiliki otoritas penuh untuk mendaftarkan dan memelihara profil Guru:

- **Menambah Akun Guru**:
    1. Akses menu **Manajemen User**, kemudian klik tab **Guru**.
    2. Klik tombol **Tambah Guru** untuk memunculkan jendela pop-up formulir pembuatan akun baru.
    3. Isi data identitas secara lengkap: Nama Lengkap, NIP (Nomor Induk Pegawai), Username unik, dan Password awal.
    4. Klik **Simpan** untuk mendaftarkan akun baru ke dalam database.
- **Mengubah Akun Guru**:
    1. Cari nama Guru pada tabel data menggunakan fitur filter pencarian.
    2. Klik tombol **Edit** (ikon pensil kuning) pada baris Guru bersangkutan.
    3. Perbarui informasi yang diperlukan pada formulir, lalu klik **Simpan Perubahan**.

### 3.2 Pengelolaan Akun Siswa

Mirip dengan akun Guru, Operator mengelola data seluruh Siswa yang berhak menggunakan platform:

- **Menambah Akun Siswa**:
    1. Pilih tab **Siswa** di halaman data user, lalu klik tombol **Tambah Siswa**.
    2. Isi formulir pendaftaran: Nama Lengkap, NIS (Nomor Induk Siswa), Kelas Aktif (pilih dari menu dropdown kelas master), Username, dan Password.
    3. Klik **Simpan** untuk memproses data.
- **Mengubah Akun Siswa**:
    1. Gunakan kolom pencarian dengan memasukkan NIS atau Nama Siswa.
    2. Klik tombol **Edit** di samping kanan baris data siswa tersebut.
    3. Sesuaikan data profil atau pindahkan kelas siswa jika terjadi mutasi kelas, kemudian klik **Simpan Perubahan**.

### 3.3 Fitur Reset Password & Keamanan Akun

Apabila Guru atau Siswa mengalami kendala lupa kata sandi login, Operator dapat memulihkan akses mereka dengan cepat melalui prosedur berikut:

1. Buka daftar pengguna pada menu **Data Guru & Siswa**.
2. Temukan nama pengguna yang terkendala, lalu klik tombol **Reset Password** (ikon kunci) yang berada di kolom aksi sebelah kanan.
3. Jendela konfirmasi SweetAlert akan muncul menanyakan persetujuan Anda untuk melakukan pengaturan ulang kata sandi pengguna ke nilai standar/default sistem.
4. Klik **Ya, Reset** untuk menyetujui. Sistem akan merestorasi password akun tersebut menjadi password bawaan (seperti `123456` atau sesuai format default sekolah).
5. Berikan password default tersebut kepada pengguna bersangkutan, dan informasikan agar mereka segera memperbarui kata sandi secara mandiri pada menu profil masing-masing setelah berhasil login.

---

## 4. Data Master (Kelas & Mata Pelajaran)

Menu Data Master merupakan basis fondasi entitas akademik di dalam sistem GARA. Sebelum memetakan kurikulum atau membuat jadwal ujian, Operator wajib memastikan entitas Kelas dan Mata Pelajaran telah terkonfigurasi dengan benar di modul ini.

### 4.1 Pengelolaan Data Kelas

Konfigurasi daftar ruang kelas aktif di sekolah:

- **Tambah Kelas**:
    1. Pilih menu **Data Master (Mapel/Kelas)** di sidebar kiri.
    2. Sistem akan menampilkan tab **Data Kelas** sebagai tampilan default. Klik tombol **Tambah Kelas**.
    3. Ketik nama kelas baru yang ingin didaftarkan pada kolom input yang tersedia (Contoh: `VII-A`, `VIII-B`, `IX-C`).
    4. Klik tombol **Simpan** untuk merekam data.
- **Edit Kelas**:
    1. Klik tombol **Edit** (ikon kertas dengan pena warna kuning) di samping kelas yang ingin disesuaikan.
    2. Ubah format penulisan nama kelas pada kolom input modal yang muncul.
    3. Klik **Simpan Perubahan** untuk memperbarui nama kelas di seluruh sistem.

### 4.2 Pengelolaan Data Mata Pelajaran (Mapel)

Konfigurasi mata pelajaran kurikulum sekolah:

- **Tambah Mata Pelajaran**:
    1. Klik tab **Data Mata Pelajaran** pada antarmuka Data Master.
    2. Klik tombol **Tambah Mapel** untuk memunculkan form input sederhana.
    3. Masukkan nama mata pelajaran baru secara formal (Contoh: `Matematika`, `Bahasa Indonesia`, `Ilmu Pengetahuan Alam`).
    4. Klik tombol **Simpan** untuk mengaktifkan Mapel baru.
- **Edit Mata Pelajaran**:
    1. Tekan ikon **Edit** pada baris data Mata Pelajaran yang akan diperbarui.
    2. Sesuaikan penulisan nama pelajaran pada jendela edit modal, lalu klik **Simpan Perubahan**.

### 4.3 Peringatan Dampak Penghapusan Data Master

Operator harus bersikap sangat hati-hati dan penuh ketelitian saat menggunakan fungsi hapus (tombol sampah warna merah) pada menu Data Master. Sistem memiliki aturan integritas database yang ketat dengan konsekuensi sebagai berikut:

> [!CAUTION]
> **Dampak Penghapusan Kelas**:
> Menghapus satu entitas Kelas akan menyebabkan seluruh siswa yang terdaftar di dalam kelas tersebut kehilangan data keanggotaan kelasnya secara otomatis (status kelas siswa menjadi kosong/tidak terdefinisi).

> [!CAUTION]
> **Dampak Penghapusan Mata Pelajaran**:
> Menghapus entitas Mata Pelajaran akan berakibat fatal pada hilangnya seluruh data Rencana Pelaksanaan Pembelajaran (RPP), materi ajar, penugasan harian guru, diskusi kelas, bank soal, dan riwayat ujian yang berhubungan dengan mata pelajaran tersebut dari database sistem secara permanen.

---

## 5. Akademik, Kurikulum & Mapping Penugasan

Modul Akademik memfasilitasi Operator untuk merangkai struktur kurikulum pengajaran sekolah, mendefinisikan kualifikasi mata pelajaran masing-masing pendidik, serta memetakan tanggung jawab mengajar guru di tiap-tiap kelas secara dinamis.

### 5.1 Pemetaan Kurikulum Kelas

Modul ini digunakan untuk menentukan mata pelajaran apa saja yang wajib diajarkan pada suatu kelas tertentu:

1. Navigasi ke menu **Mapping & Penugasan**, lalu pilih submenu **Kurikulum Kelas**.
2. Pilih salah satu kelas yang ingin dikonfigurasi melalui sidebar pemilih kelas di sebelah kiri layar.
3. Di panel sebelah kanan, daftar seluruh Mata Pelajaran master akan ditampilkan dalam bentuk daftar pilihan (checklist).
4. Beri tanda centang pada kotak di samping nama mata pelajaran yang diajarkan pada kelas tersebut. Hilangkan centang jika mata pelajaran tersebut tidak diajarkan di kelas tersebut.
5. Setelah penyesuaian selesai, klik tombol **Simpan Perubahan** di sudut kanan atas untuk menerapkan pemetaan kurikulum kelas.

### 5.2 Pemetaan Kompetensi Mata Pelajaran Guru

Modul Kompetensi Guru berfungsi untuk mendaftarkan keahlian pengajaran spesifik dari masing-masing pendidik:

1. Klik submenu **Kompetensi Guru** di dalam menu **Mapping & Penugasan**.
2. Di sisi kiri, sistem menyediakan daftar seluruh Guru yang terdaftar. Pilih salah satu Guru.
3. Panel kanan akan menampilkan daftar Mata Pelajaran yang sudah dipetakan ke dalam kurikulum kelas.
4. Beri tanda centang pada satu atau beberapa mata pelajaran yang merupakan kompetensi ajar guru bersangkutan.
5. Beri centang pula pada kelas-kelas yang bersesuaian yang akan diajar oleh guru tersebut untuk mata pelajaran terpilih.
6. Klik tombol **Simpan Perubahan** untuk mengunci pemetaan kompetensi pendidik tersebut.

### 5.3 Penugasan Mengajar Guru

Untuk meninjau rekapitulasi data penugasan mengajar aktif, Operator dapat mengakses submenu **Penugasan Guru**:

- Halaman ini menampilkan seluruh data penugasan mengajar aktif yang dikelompokkan secara rapi berdasarkan nama masing-masing Guru (dalam format accordion menu).
- Setiap baris nama guru yang di-expand akan memunculkan tabel berisi: Mata Pelajaran yang diampu dan daftar lencana (badge) nama kelas tempat ia ditugaskan mengajar.
- Jika Operator ingin mencabut atau menghapus salah satu tugas mengajar spesifik dari seorang guru, Operator cukup mengeklik tombol **tanda silang kecil (x)** di dalam lencana nama kelas yang bersangkutan.
- Konfirmasi SweetAlert akan muncul meminta validasi Anda. Klik **Ya, Hapus** untuk memproses penghapusan relasi mengajar tersebut sehingga guru tersebut tidak lagi memiliki akses kelas tersebut untuk mata pelajaran terkait.

### 5.4 Deteksi & Resolusi Konflik Penugasan Mengajar

Sistem GARA dilengkapi dengan sistem proteksi konflik penugasan mengajar berbasis AJAX (`operator.mapping.api.check-assignment`). Fitur ini mencegah terjadinya duplikasi guru pengampu mata pelajaran yang sama pada suatu kelas.

- **Cara Kerja Proteksi**:
  Ketika Operator sedang mencentang sebuah kelas untuk ditugaskan kepada Guru A pada subjek Matematika di halaman **Kompetensi Guru**, sistem secara otomatis mengirimkan permintaan pengecekan ke database di latar belakang.
- **Deteksi Konflik**:
  If sistem menemukan bahwa kelas tersebut ternyata telah ditugaskan kepada Guru B untuk mata pelajaran Matematika, sistem akan menghentikan proses centang secara langsung.
- **Resolusi Sistem**:
  SweetAlert akan muncul di layar menampilkan pesan peringatan: **"Konflik Penugasan! Kelas ini sudah diajar oleh Guru B untuk mata pelajaran ini."**. Kotak centang kelas tersebut akan otomatis dinonaktifkan kembali (uncheck) oleh sistem untuk menghindari kerusakan integrasi jadwal dan pelaporan. Operator harus menghapus penugasan Guru B terlebih dahulu di menu Penugasan Guru jika ingin menggantikannya dengan Guru A.

---

## 6. Ruang Asesmen & Ujian

Modul Ruang Asesmen merupakan fitur vital bagi Operator dalam merencanakan, memonitor, dan merekapitulasi jalannya evaluasi belajar siswa secara online.

### 6.1 Manajemen Gerbang Akses (Gate Controller)

Di halaman **Dashboard Asesmen**, terdapat dua sakelar elektronik utama yang berfungsi untuk mengendalikan hak akses pengguna ke sistem ujian secara global:

1. **Gate Guru**:
    - **TERTUTUP (Warna Merah)**: Seluruh hak akses guru untuk menginput, mengedit, atau menghapus bank soal ditangguhkan sementara. Pilihan terbaik saat masa penyusunan soal telah habis dan masa ujian akan segera dimulai.
    - **TERBUKA (Warna Hijau)**: Guru diberikan izin penuh untuk masuk ke modul bank soal dan menyusun instrumen ujian.
2. **Gate Siswa**:
    - **TERTUTUP (Warna Merah)**: Siswa diblokir dan tidak dapat mengakses halaman portal pengerjaan ujian, meskipun jadwal ujian telah aktif. Pilihan aman untuk mencegah kebocoran soal sebelum waktu rilis.
    - **TERBUKA (Warna Hijau)**: Siswa diberikan izin penuh untuk mengakses halaman portal ujian dan mulai mengerjakan soal ujian sesuai jadwal.

- **Cara Mengubah Status Gate**:
  Klik tombol **Buka Gate** / **Tutup Gate** di bawah masing-masing panel kontrol. Lakukan verifikasi pada jendela dialog konfirmasi SweetAlert untuk mengeksekusi perubahan status.

### 6.2 Pengaturan Jenis Asesmen Global (ASTS & ASAS)

Sebelum merancang jadwal baru, Operator wajib menetapkan jenis ujian aktif secara global pada kartu **Jenis Asesmen Aktif**:

- Pilih tombol **ASTS** jika agenda evaluasi yang berjalan adalah _Asesmen Sumatif Tengah Semester_.
- Pilih tombol **ASAS** jika agenda evaluasi yang berjalan adalah _Asesmen Sumatif Akhir Semester_.
- Pilihan ini akan otomatis melekat sebagai penanda metadata pada setiap jadwal ujian baru yang dibuat setelah pengaturan disimpan.

### 6.3 Pembuatan Jadwal Ujian Secara Masal (Batch Scheduling)

Operator dapat menghemat waktu dengan menyusun jadwal ujian secara masal dalam satu kali proses (batch) di menu **Jadwal Ujian** melalui 4 tahapan sistematis:

1. **Tahap 1: Pilih Tanggal Ujian**
   Pilih tanggal pelaksanaan ujian menggunakan kalender interaktif. Sistem secara otomatis akan mendeteksi dan menampilkan nama hari bersangkutan dalam Bahasa Indonesia (Contoh: `Senin`, `Selasa`).
2. **Tahap 2: Pilih Kelas**
   Beri tanda centang pada daftar kotak pilihan kelas yang akan mengikuti ujian di tanggal tersebut. Tersedia tombol pintasan **Pilih Semua** untuk mencentang seluruh kelas secara instan.
3. **Tahap 3: Pilih Mata Pelajaran**
   Centang satu atau beberapa mata pelajaran yang akan diujikan pada hari tersebut.
    - _Indikator Kesiapan Soal_: Di samping tiap nama mata pelajaran, terdapat titik indikator warna:
        - **Titik Hijau**: Soal lengkap dan telah berstatus VALIDATED (Siap diujikan).
        - **Titik Kuning**: Soal sudah diinput guru namun berstatus DRAFT / belum divalidasi penuh oleh Operator.
        - **Titik Merah**: Belum ada soal yang diinput oleh guru untuk subjek terkait.
    - _Proteksi Duplikasi_: Sistem akan otomatis mematikan (disable) pilihan mata pelajaran yang telah terjadwal sebelumnya di tanggal yang sama untuk mencegah tumpang tindih pengerjaan.
4. **Tahap 4: Atur Waktu per Mata Pelajaran**
   Setelah langkah 1, 2, dan 3 terisi, tabel konfigurasi waktu ujian akan otomatis ter-render di bawahnya.
    - Operator cukup memasukkan **Jam Mulai** (Contoh: `08:00`) dan durasi waktu pengerjaan dalam satuan menit (Contoh: `90` menit).
    - Sistem secara otomatis akan melakukan kalkulasi waktu selesai di latar belakang dan menampilkan **Jam Selesai** (Contoh: `09:30`) secara real-time pada kolom preview selesai.
5. **Simpan Batch**:
   Klik tombol **Simpan Semua Jadwal** di bagian bawah. Jendela konfirmasi akan menampilkan detail ringkasan jumlah entitas jadwal yang akan terbit. Klik **Ya, Simpan** untuk menyelesaikan.

### 6.4 Pengelolaan & Pembuatan Token Ujian

Siswa membutuhkan Token sebagai kunci masuk ke lembar soal ujian. Tata kelola token diatur oleh Operator dengan ketentuan sebagai berikut:

- **Syarat Token**: Token hanya dapat digenerate/dibuat jika soal ujian untuk kelas dan mata pelajaran tersebut telah divalidasi penuh oleh Operator (titik indikator status soal berwarna hijau).
- **Generate Token**: Klik tombol **Kunci** di tabel Daftar Jadwal Ujian pada baris jadwal yang diinginkan untuk memunculkan 6 karakter kode unik (Token).
- **Reset Token Masal**: Jika pelaksanaan ujian hari ini membutuhkan pembaruan keamanan, Operator dapat mengeklik tombol **Reset Token Hari Ini** di atas tabel untuk memperbarui seluruh token ujian yang terjadwal di hari yang sama secara instan.

### 6.5 Portal Pengawasan Ujian Aktif (Live Exam Monitoring)

Saat ujian sedang berlangsung, Operator bertindak sebagai pengawas digital utama melalui menu **Portal Ujian Aktif**:

- Akses halaman ini dengan mengeklik tombol **Portal** di samping jadwal ujian yang sedang aktif berjalan.
- **Panel Kiri (Informasi Sesi)**: Menampilkan detail status durasi sesi, jumlah siswa yang telah mengumpulkan lembar jawaban berbanding total siswa di kelas (`Siswa Submit`), status pengerjaan, serta Token sesi aktif.
- **Regenerate Token Darurat**: Jika dicurigai terjadi kebocoran token antar kelas, Operator dapat mengeklik tombol **Regenerate Token Sesi Ini** di panel kiri untuk mematikan token lama dan meluncurkan token pengganti baru secara langsung.
- **Panel Kanan (Live Monitor Submit)**: Menampilkan tabel daftar 10 siswa terakhir yang sukses mengumpulkan ujian lengkap dengan NIS, Nama, catatan waktu mulai pengerjaan, waktu submit selesai, dan skor akhir mereka. Data pada tabel pengawasan ini melakukan pembaruan otomatis (**Auto-Refresh**) setiap **10 detik** untuk menyajikan data paling mutakhir dari server.

### 6.6 Pengolahan Nilai & Ekspor Hasil Ujian

Setelah sesi ujian berakhir, Operator masuk ke halaman **Hasil Ujian** untuk merekapitulasi perolehan skor siswa:

1. **Filter Hasil**: Pilih sesi ujian pada kolom dropdown yang tersedia (hanya memuat sesi ujian yang telah memiliki Token), kemudian klik **Tampilkan Hasil**.
2. **Statistik Rata-rata**: Sistem akan mengkalkulasi instan metrik total peserta submit, skor nilai rata-rata kelas, nilai tertinggi, dan mendeteksi jumlah siswa yang memperoleh nilai di bawah KKM standar kelulusan (KKM: `75`).
3. **Hapus Jawaban Siswa Individu**: Jika seorang siswa mengalami force close akibat kendala perangkat keras atau diizinkan melakukan ujian susulan, Operator dapat menghapus data pengerjaan siswa tersebut dengan mengeklik tombol **Hapus** (ikon tempat sampah merah) di samping namanya agar status ujiannya kembali bersih dan ia dapat masuk kembali ke portal ujian menggunakan token aktif.
4. **Hapus Jawaban Masal**: Jika terjadi kesalahan teknis masal, Operator dapat mengeklik tombol **Hapus Semua Jawaban** di atas tabel. Demi aspek keamanan, Operator diwajibkan mengetik kata konfirmasi **"HAPUS"** menggunakan huruf kapital pada kolom dialog pop-up yang muncul sebelum sistem menghapus seluruh lembar jawaban sesi tersebut dari database.
5. **Ekspor Hasil**: Tabel hasil dilengkapi dengan tombol ekspor profesional yang telah diintegrasikan dengan pustaka DataTables:
    - **Excel**: Mengunduh rekap nilai dalam format `.xlsx`. Sistem secara cerdas mengonversi kolom NIS dengan menambahkan karakter tanda petik tunggal (`'`) di depan angka agar format kode NIS siswa tidak terpotong (menghindari angka nol di depan hilang akibat format numerik Excel otomatis).
    - **PDF**: Menghasilkan dokumen rekap nilai berorientasi lanskap ukuran A4 yang rapi dan siap didistribusikan.
    - **Cetak**: Membuka dialog print browser bawaan dengan optimasi CSS cetak `@media print` yang secara otomatis menyembunyikan elemen sidebar, tombol aksi, header portal, dan hanya menyisakan lembar dokumen tabel nilai bersih berwarna hitam putih.

---

## 7. Modul Quality Control (QC) Soal

Modul QC Soal merupakan antarmuka eksklusif Operator untuk memeriksa kelayakan instrumen ujian sebelum dirilis secara resmi ke siswa.

### 7.1 Fungsi & Peran QC Soal

Sebagai penjamin mutu ujian, Operator bertindak sebagai penilai kelayakan naskah soal. Guru memiliki hak membuat soal, namun soal tersebut tidak akan pernah bisa diakses oleh siswa sebelum disetujui dan divalidasi oleh Operator melalui proses QC Soal ini.

### 7.2 Antarmuka Panel QC Soal

Ketika mengeklik tombol **QC** pada tabel monitoring soal di Dashboard Asesmen, sistem akan membuka halaman baru yang independen dengan tata letak profesional:

- **Bagian Header**: Menampilkan informasi subjek ujian secara komprehensif, mencakup nama Guru penyusun, Mata Pelajaran, tingkatan Kelas, serta lencana otorisasi Operator berwarna kuning.
- **Sidebar Kiri (Navigasi Soal)**:
    - Menampilkan jumlah total soal yang terdeteksi dalam paket.
    - Menyediakan kisi-kisi nomor soal berbentuk kotak navigasi (nav pills). Kotak ini berwarna hijau jika soal bersangkutan sudah berstatus validasi, dan berwarna kuning jika masih berstatus draft.
    - Mengeklik salah satu kotak nomor akan mengarahkan fokus layar secara mulus (_smooth scroll_) langsung menuju kartu soal bersangkutan di panel utama sebelah kanan.
- **Panel Utama Kanan (Konten Preview)**:
  Menyajikan daftar lengkap instrumen soal dengan tata letak visual menyerupai lembar ujian asli siswa.

### 7.3 Tinjauan Karakteristik Tipe Soal

Operator dapat memantau dengan tepat visualisasi konten naskah berdasarkan empat jenis tipe soal yang didukung oleh sistem GARA:

1. **Pilihan Ganda (PG) Biasa**:
   Menampilkan naskah pertanyaan beserta lima opsi jawaban (A, B, C, D, E). Opsi yang merupakan kunci jawaban yang benar akan otomatis disorot dengan latar belakang warna biru muda dan diberi penanda lencana **"Kunci"**.
2. **Pilihan Ganda (PG) Kompleks**:
   Digunakan untuk soal dengan opsi jawaban benar lebih dari satu. Sistem menyajikan opsi jawaban dengan kotak pilihan (checkbox) interaktif di samping kiri tiap kalimat jawaban, serta melabeli seluruh opsi jawaban benar dengan tanda centang aktif dan badge biru.
3. **Benar / Salah**:
   Menyajikan daftar kalimat pernyataan di mana setiap baris kalimat dilengkapi dengan dua tombol pilihan jawaban mutlak: **BENAR** (Hijau dengan ikon centang) atau **SALAH** (Merah dengan ikon silang) yang menyorot kunci jawaban yang ditentukan guru.
4. **Isian Singkat**:
   Menampilkan kolom pertanyaan terbuka beserta sebuah kotak kunci jawaban teks bertuliskan jawaban presisi yang harus diinput siswa pada lembar ujian.

- **Pengecekan Aset Multimedia**:
  Operator wajib memvalidasi keterbacaan aset media pendukung soal yang disematkan guru pada naskah soal:
    - _Gambar Lokal & Eksternal_: Harus tampil proporsional tanpa memotong teks naskah soal.
    - _Video YouTube_: Panel pemutar video harus responsif dan dapat diputar.
    - _Audio MP3_: Tombol kontrol pemutar audio lokal harus aktif (berfungsi untuk uji mendengarkan/listening).
    - _Google Drive_: Tautan aset eksternal harus menampilkan lencana ikon Google Drive biru yang valid.

### 7.4 Prosedur Validasi Masal Paket Soal

Apabila setelah diteliti seluruh butir soal dalam paket tersebut dinyatakan layak dan bebas dari kesalahan ketik atau kunci jawaban:

1. Arahkan kursor ke bagian bawah Sidebar Kiri halaman QC Soal.
2. Klik tombol berwarna ungu bertuliskan **Validasi Semua Soal**.
3. Konfirmasi SweetAlert akan muncul menginformasikan rincian jumlah butir soal berstatus draft yang akan ditingkatkan statusnya menjadi validated.
4. Klik **Ya, Validasi!** untuk memproses.
5. Sistem akan mengeksekusi pembaruan status masal pada database. Setelah sukses, indikator nav pills nomor soal akan berubah menjadi warna hijau secara merata, menandakan paket soal tersebut telah siap dan berstatus aman untuk dijadwalkan dalam ujian.

---

## 8. Pengelolaan Profil & Akun Operator

Untuk kenyamanan penggunaan sistem harian, Operator memiliki akses khusus untuk memperbarui profil dan kredensial keamanan mereka sendiri.

### 8.1 Pembaruan Foto Profil (AJAX Upload)

Untuk menjaga keaslian identitas akun Operator pada kop surat atau laporan penugasan sistem:

1. Klik menu **Tentang Saya** pada navigasi sidebar kiri.
2. Arahkan kursor ke arah foto avatar bulat di bagian atas hero banner profil. Ikon overlay kamera akan muncul menandakan foto dapat diubah. Klik foto tersebut.
3. Jendela penjelajah berkas komputer Anda akan otomatis terbuka. Pilih berkas foto baru Anda (Format yang diizinkan: `.jpg`, `.jpeg`, atau `.png` dengan ukuran maksimal 2MB).
4. Setelah berkas dipilih, sistem secara otomatis mengeksekusi perintah unggah berbasis AJAX ke server di latar belakang tanpa memuat ulang keseluruhan halaman web.
5. Visual foto profil akan memudar sementara (opacity 50%) selama proses pengiriman data berjalan. Setelah berhasil, notifikasi sukses SweetAlert akan muncul, dan foto profil Anda yang baru akan langsung terpasang di bar navigasi atas dan sidebar sistem secara real-time.

### 8.2 Prosedur Keamanan Mengubah Password

Demi menjaga kerahasiaan hak akses administratif Operator dari penyalahgunaan pihak tidak bertanggung jawab, disarankan untuk melakukan pembaruan kata sandi secara berkala:

1. Klik menu **Ubah Password** di baris menu Akun pada sidebar kiri.
2. Isi tiga kolom wajib yang tersedia pada formulir perubahan password:
    - **Password Lama**: Masukkan kata sandi yang Anda gunakan saat ini untuk validasi identitas asli.
    - **Password Baru**: Masukkan kombinasi kata sandi baru yang kuat (minimal terdiri atas 8 karakter, direkomendasikan perpaduan huruf besar, huruf kecil, angka, dan simbol khusus).
    - **Konfirmasi Password Baru**: Masukkan kembali kata sandi baru Anda dengan karakter yang identik untuk menghindari kesalahan penulisan.
3. Klik tombol **Ubah Password** untuk memproses pembaruan kredensial Anda pada sistem keamanan auth GARA.

---

## 9. Panduan Troubleshooting & Solusi Masalah Kerja

Berikut adalah ikhtisar ringkas kendala operasional harian beserta solusi cepatnya:

| Kendala & Masalah Utama                 | Ringkasan Solusi Pemecahan                                |
| :-------------------------------------- | :-------------------------------------------------------- |
| **Akses Ujian Siswa Terblokir**         | Buka**Gate Siswa** di Dashboard Asesmen.                  |
| **Pesan Eror "Token Tidak Valid"**      | Gunakan Token terbaru dari**Portal Ujian Aktif**.         |
| **Mata Pelajaran Tidak Bisa Dicentang** | Hapus jadwal lama yang bentrok pada tanggal bersangkutan. |
| **Tombol "Kunci" (Token) Terkunci**     | Lakukan validasi paket soal di halaman**QC Soal**.        |
| **Live Monitor Berhenti Update**        | Periksa koneksi internet lalu**Segarkan Halaman (F5)**.   |
| **Eror "Konflik Penugasan" Guru**       | Cabut kelas guru lama di menu**Penugasan Guru**.          |

### Penjelasan Detail Prosedur Solusi

#### 1. Mengatasi Akses Ujian Siswa Terblokir

Jika siswa tidak dapat mengakses portal pengerjaan meskipun jadwal pengerjaan sudah aktif:

- **Penyebab**: Status _Gate Siswa_ di Dashboard Asesmen masih berstatus TERTUTUP (Merah).
- **Langkah Solusi**: Buka menu **Dashboard Asesmen**, cari panel kontrol **Gate Siswa**, lalu klik tombol **Buka Gate Siswa** hingga indikator status berubah menjadi TERBUKA (Hijau).

#### 2. Mengatasi Eror "Token Tidak Valid"

Jika siswa mengeluhkan kode token ditolak saat mencoba masuk ke lembar ujian:

- **Penyebab**: Kode token sesi telah diperbarui (regenerate) secara darurat oleh Operator, atau siswa salah memasukkan karakter.
- **Langkah Solusi**: Buka halaman **Portal Ujian Aktif** atau **Jadwal Ujian** untuk menyalin kode 6 karakter terbaru. Bagikan token tersebut ke pengawas fisik kelas dan pastikan siswa mengetik menggunakan huruf kapital tanpa spasi tambahan.

#### 3. Mengatasi Mata Pelajaran Tidak Bisa Dicentang Saat Membuat Jadwal

Jika kotak pilihan mata pelajaran berwarna abu-abu (disable) dan tidak dapat diklik:

- **Penyebab**: Mata pelajaran terkait sudah terdaftar dalam jadwal aktif pada tanggal yang sama di database.
- **Langkah Solusi**: Periksa tabel **Daftar Jadwal Ujian** di bagian bawah halaman. Hapus terlebih dahulu jadwal lama yang bentrok jika Anda ingin menggantinya dengan jam atau sesi pengerjaan yang baru.

#### 4. Mengatasi Tombol "Kunci" (Token) Terkunci

Jika tombol bergambar kunci di tabel daftar jadwal tidak dapat diklik untuk membuat token:

- **Penyebab**: Instrumen soal dari guru pengampu mata pelajaran tersebut belum divalidasi penuh oleh Operator (status soal masih draft / titik indikator berwarna kuning/merah).
- **Langkah Solusi**:
    1. Klik tautan **QC** pada baris data soal terkait di Dashboard Asesmen.
    2. Periksa kelayakan soal di lembar QC, lalu klik tombol **Validasi Semua Soal** di bagian bawah sidebar kiri agar statusnya naik menjadi validated (titik berubah hijau).
    3. Kembali ke menu Jadwal Ujian dan tombol Kunci generate token sekarang telah aktif.

#### 5. Mengatasi Live Monitor Berhenti Melakukan Pembaruan Otomatis

Jika tabel daftar siswa submit di Portal Ujian aktif tidak memperbarui data secara berkala:

- **Penyebab**: Koneksi jaringan internet terputus sementara atau sesi masuk (login) Operator telah kedaluwarsa.
- **Langkah Solusi**: Periksa stabilitas jaringan internet Anda, kemudian muat ulang halaman web browser Anda (tekan **F5** atau klik tombol Refresh). Jika terlempar ke halaman login, silakan login kembali ke akun Operator Anda.

#### 6. Mengatasi Eror "Konflik Penugasan" Guru

Jika muncul pesan peringatan dari sistem saat Anda menyimpan konfigurasi kompetensi guru baru:

- **Penyebab**: Kelas yang dipilih telah ditugaskan ke guru lain untuk mengampu mata pelajaran yang sama.
- **Langkah Solusi**:
    1. Masuk ke menu **Penugasan Guru** di bawah menu Akademik.
    2. Cari nama guru lama yang memicu konflik, lalu klik tombol **tanda silang (x)** pada lencana kelas bersangkutan untuk mencabut hak mengajar lamanya.
    3. Kembali ke menu **Kompetensi Guru** dan silakan beri tanda centang pada kelas tersebut untuk guru yang baru.

---

## Penutup & Dukungan Sistem

Panduan operasional ini diharapkan dapat menjadi acuan utama bagi Operator dalam menjaga kelancaran administrasi akademik serta pelaksanaan asesmen ujian online di platform GARA.

Apabila Anda menemui kendala teknis di luar panduan troubleshooting ini, silakan hubungi tim **System Administrator** atau layangkan tiket bantuan melalui saluran dukungan resmi pengembang GARA. Selamat bertugas!
