# Panduan Penggunaan Sistem (User Guide) — Peran Guru

Selamat datang di Dokumentasi Resmi Guru GARA (Garuda Akademi). Panduan ini disusun secara terstruktur untuk membantu peran Guru dalam mengelola pembelajaran online, memantau tugas siswa, menginput absensi dengan generator jurnal cerdas, mengelola agenda mengajar harian, serta melakukan penyusunan dan pengawasan ujian siswa secara mandiri.

Dokumen ini dirancang sebagai panduan bantuan mandiri (help desk) tekstual terintegrasi yang siap dipasang secara langsung pada sistem berbasis web. Navigasi antarmuka disajikan secara terperinci untuk memudahkan operasional harian Anda.

---

## Daftar Isi

- [1. Alur Masuk & Manajemen Sesi Aktif](#1-alur-masuk--manajemen-sesi-aktif)
    - [1.1 Prosedur Login Sistem](#11-prosedur-login-sistem)
    - [1.2 Pemilihan Sesi Aktif (Kelas & Mapel)](#12-pemilihan-sesi-aktif-kelas--mapel)
    - [1.3 Prosedur Ganti Kelas/Sesi Aktif](#13-prosedur-ganti-kelassesi-aktif)
- [2. Struktur Navigasi Sidebar](#2-struktur-navigasi-sidebar)
    - [2.1 Widget Sesi Aktif](#21-widget-sesi-aktif)
    - [2.2 Pembagian Menu Utama](#22-pembagian-menu-utama)
- [3. Dashboard Utama & Pengumuman Kelas](#3-dashboard-utama--pengumuman-kelas)
    - [3.1 Pemantau Waktu Nyata (Live Clock)](#31-pemantau-waktu-nyata-live-clock)
    - [3.2 Chart Analitik Kehadiran & Nilai](#32-chart-analitik-kehadiran--nilai)
    - [3.3 Fitur Broadcast Pengumuman Kelas](#33-fitur-broadcast-pengumuman-kelas)
- [4. Modul Akademik & Kelas](#4-modul-akademik--kelas)
    - [4.1 Ruang Materi Pembelajaran](#41-ruang-materi-pembelajaran)
    - [4.2 Ruang Diskusi Interaktif](#42-ruang-diskusi-interaktif)
    - [4.3 Ruang Tugas & Monitoring Pengumpulan](#43-ruang-tugas--monitoring-pengumpulan)
    - [4.4 Ruang Kompetensi Guru](#44-ruang-kompetensi-guru)
- [5. Modul Penilaian & Laporan](#5-modul-penilaian--laporan)
    - [5.1 Ruang Ujian & Manajemen Bank Soal](#51-ruang-ujian--manajemen-bank-soal)
    - [5.2 Input Nilai Tugas Mandiri](#52-input-nilai-tugas-mandiri)
    - [5.3 Rekap Nilai Tugas Komprehensif](#53-rekap-nilai-tugas-komprehensif)
- [6. Modul Absensi & Agenda Mengajar](#6-modul-absensi--agenda-mengajar)
    - [6.1 Input Absensi & Generator Jurnal Otomatis](#61-input-absensi--generator-jurnal-otomatis)
    - [6.2 Rekapitulasi Absensi & Quick Edit](#62-rekapitulasi-absensi--quick-edit)
    - [6.3 Agenda Mengajar Harian](#63-agenda-mengajar-harian)
- [7. Pengelolaan Akun & Sandi Mandiri](#7-pengelolaan-akun--sandi-mandiri)
    - [7.1 Profil Saya & Unggah Foto (AJAX)](#71-profil-saya--unggah-foto-ajax)
    - [7.2 Pembaharuan Kata Sandi Mandiri](#72-pembaharuan-kata-sandi-mandiri)
- [8. Panduan Troubleshooting & Solusi Mandiri](#8-panduan-troubleshooting--solusi-mandiri)

---

## 1. Alur Masuk & Manajemen Sesi Aktif

Akses awal dan ruang lingkup kerja Guru di dalam platform GARA diatur secara ketat melalui middleware autentikasi dan otorisasi sesi pengajaran aktif.

### 1.1 Prosedur Login Sistem

Untuk masuk ke dalam portal Guru:

1. Buka peramban web (browser) di komputer atau perangkat mobile Anda.
2. Masukkan alamat URL portal resmi GARA sekolah Anda.
3. Pada halaman login, masukkan **Username** dan **Password** Guru Anda yang telah terdaftar.
4. Klik tombol **Login**. Sistem akan memverifikasi peran Anda dan mengarahkan Anda ke portal Guru.

### 1.2 Pemilihan Sesi Aktif (Kelas & Mapel)

Setelah sukses melakukan login, sistem akan menahan Anda pada halaman **Pemilihan Sesi Aktif**. Ini merupakan gerbang wajib untuk menentukan ruang kelas dan mata pelajaran yang akan Anda kelola saat itu.

- **Cara Memilih Sesi Aktif**:
    1. Di halaman pemilihan sesi, Anda akan melihat daftar **Mata Pelajaran** beserta **Kelas** yang didelegasikan oleh Operator kepada Anda.
    2. Pilih kombinasi Kelas dan Mata Pelajaran tujuan (contoh: _Biologi - Kelas VIII-A_).
    3. Klik tombol **Masuk Workspace**.
    4. Sistem menyimpan parameter identifikasi kelas (`session('kelas_id')`) dan mata pelajaran (`session('mapel_id')`) ke dalam memori sesi Anda secara aman, kemudian mengarahkan Anda menuju Dashboard Utama.

### 1.3 Prosedur Ganti Kelas/Sesi Aktif

Apabila Anda mengajar lebih dari satu kelas atau mata pelajaran, Anda dapat beralih ruang kerja kapan saja tanpa harus keluar (logout) dari sistem.

- **Cara Mengganti Kelas**:
    1. Pada menu navigasi sidebar sebelah kiri, geser ke bagian paling bawah.
    2. Klik menu **Ganti Kelas** yang ditandai dengan latar belakang abu-abu gelap.
    3. Sistem akan mengembalikan Anda ke halaman pemilihan sesi awal.
    4. Pilih mata pelajaran dan kelas baru yang ingin dikelola, lalu klik **Masuk Workspace**.

---

## 2. Struktur Navigasi Sidebar

Seluruh fitur pengajaran dikelompokkan secara ergonomis di dalam menu sidebar kiri untuk menjaga fokus kerja Guru berdasarkan konteks ruang kelas yang sedang aktif.

### 2.1 Widget Sesi Aktif

Di bagian atas menu sidebar, tepat di bawah informasi profil Anda, terdapat sebuah panel widget khusus **Sesi Aktif**:

- Widget ini menampilkan teks tebal berwarna putih berisi nama mata pelajaran aktif yang sedang Anda kelola (contoh: _Biologi_).
- Widget ini menampilkan ikon gedung sekolah kecil yang didampingi oleh nama kelas aktif (contoh: _Kelas VIII-A_).
- Informasi visual ini membantu memastikan Anda tidak melakukan kesalahan input nilai atau materi di kelas yang salah.

### 2.2 Pembagian Menu Utama

Struktur menu diatur dalam bentuk akordeon interaktif yang dikelompokkan sebagai berikut:

- **Dashboard**: Halaman pemantauan umum kelas, pengumuman broadcast, dan grafik kehadiran siswa.
- **Akademik & Kelas** (Akordeon Submenu):
    - **Ruang Materi**: Tempat mengunggah, menyusun, dan mendistribusikan bab bacaan atau modul ajar kepada siswa.
    - **Ruang Diskusi**: Forum tanya jawab dua arah terstruktur mengenai materi ajar di kelas aktif.
    - **Ruang Tugas**: Portal pembuatan instruksi tugas mandiri, tautan referensi eksternal, dan pengumpulan file siswa.
    - **Ruang Kompetensi**: Lembar pemantauan target kompetensi akademik dasar mata pelajaran Anda.
- **Penilaian & Laporan** (Akordeon Submenu):
    - **Ruang Ujian**: Pusat perancangan lembar ujian menggunakan Smart Input (mode teks), penarikan data Google Docs, pembuatan media aset soal, dan proses pratinjau Quality Control.
    - **Input Nilai Tugas**: Antarmuka manual untuk memasukkan nilai tugas siswa per lembar penilaian.
    - **Rekap Tugas**: Rekapitulasi nilai tugas secara keseluruhan untuk satu semester dalam bentuk matriks.
    - **Input Absensi**: Portal mencatat jurnal harian kehadiran siswa dilengkapi alat pembuat pokok bahasan otomatis (KKO).
    - **Rekap Absensi**: Pemantau akumulasi persentase kehadiran siswa, rekap bulanan, dan fitur Quick Edit kehadiran.
    - **Agenda Mengajar**: Rekaman riwayat pencapaian mengajar Anda secara periodik yang sinkron dengan absensi kelas.
- **Lainnya**:
    - **Tentang Saya**: Pengaturan profil pribadi dan antarmuka unggah foto profil Guru.
    - **Ganti Kelas**: Akses cepat beralih ruang kerja subjek/kelas mengajar lainnya.
    - **Ubah Password**: Formulir mandiri merubah kata sandi login secara aman.

---

## 3. Dashboard Utama & Pengumuman Kelas

Halaman beranda utama menyajikan rangkuman visual kondisi siswa terkini pada kelas dan subjek terpilih.

### 3.1 Pemantau Waktu Nyata (Live Clock)

Di bagian kanan banner sambutan utama, terdapat komponen **Live Clock & Date** berbasis Waktu Indonesia Barat (WIB). Fitur ini berjalan secara real-time dan disinkronkan langsung dengan waktu server GARA. Live Clock berfungsi sebagai patokan mutlak saat Anda mengatur batas waktu (deadline) tugas atau jadwal pembukaan ujian agar tidak terjadi perbedaan waktu pengerjaan.

### 3.2 Chart Analitik Kehadiran & Nilai

Dashboard Guru dilengkapi dengan dua visualisasi data cerdas (Chart.js) untuk mempermudah pemantauan progres kelas:

- **Attendance Doughnut Chart**: Menampilkan rasio kumulatif kehadiran siswa (Hadir, Sakit, Izin, Alpha) untuk semester berjalan pada kelas aktif.
- **Average Assignment Grades Bar Chart**: Menampilkan bagan perkembangan nilai rata-rata kelas dari tugas ke tugas berikutnya, memudahkan Anda mendeteksi materi yang sulit dipahami oleh mayoritas siswa di kelas tersebut.

### 3.3 Fitur Broadcast Pengumuman Kelas

Guru dapat menyiarkan pengumuman langsung kepada seluruh siswa di kelas aktif. Pengumuman ini akan terpampang di dashboard siswa seketika setelah diterbitkan.

- **Cara Menerbitkan Pengumuman**:
    1. Temukan kartu **Pengumuman Kelas** di dashboard Guru.
    2. Masukkan **Judul Pengumuman** (contoh: _Persiapan Ulangan Harian Bab 2_).
    3. Masukkan pesan pada kolom **Isi Pengumuman** secara lengkap.
    4. Klik tombol biru **Kirim Pengumuman**.
    5. Pengumuman aktif akan muncul di panel tersebut lengkap dengan tanggal publikasi, dan pengumuman lama akan digantikan secara otomatis.
    6. Gunakan tombol merah **Hapus Pengumuman** jika informasi yang disiarkan sudah tidak relevan.

---

## 4. Modul Akademik & Kelas

Modul pengelolaan harian siswa yang berpusat pada transfer materi, interaksi kolaboratif, dan pengumpulan berkas tugas.

### 4.1 Ruang Materi Pembelajaran

Digunakan untuk membagikan modul ajar terstruktur kepada siswa agar dapat dipelajari secara mandiri.

- **Cara Mengunggah Materi Baru**:
    1. Akses menu **Akademik & Kelas**, pilih **Ruang Materi**.
    2. Klik tombol **Tambah Materi** di pojok kanan atas.
    3. Pada form yang muncul:
        - Masukkan **Judul Materi** (contoh: _Bab 1: Struktur Sel Hewan_).
        - Masukkan **Nomor Bab** sebagai indeks urutan bahan ajar.
        - Masukkan deskripsi ringkas atau ringkasan kompetensi materi.
        - Unggah berkas materi (format didukung: PDF, DOCX, PPTX dengan batas maksimal 10 MB).
    4. Klik **Publikasikan**. Materi kini dapat diunduh dan dibaca langsung oleh seluruh siswa di kelas Anda.

### 4.2 Ruang Diskusi Interaktif

Fasilitas tanya jawab asinkronus antara Guru dan Siswa yang terikat pada topik pembahasan kelas.

- **Cara Membuka Topik Diskusi Baru**:
    1. Akses menu **Ruang Diskusi**.
    2. Klik tombol **Buat Diskusi Baru**.
    3. Tulis **Judul Diskusi** (contoh: _Tanya Jawab Mengenai Tugas Fotosintesis_).
    4. Ketik instruksi pemancing atau pertanyaan pemantik diskusi pada kolom deskripsi.
    5. Klik **Buka Forum**. Siswa kini dapat memberikan respons, bertanya, atau melampirkan berkas jawaban mereka pada forum tersebut.
- **Cara Membalas Pertanyaan Siswa**:
    1. Klik judul diskusi aktif untuk melihat seluruh alur pesan masuk.
    2. Baca pesan dari siswa, ketik tanggapan Anda di kolom balasan yang tersedia.
    3. Anda dapat melampirkan gambar referensi jika diperlukan dengan mengeklik tombol lampiran.
    4. Klik **Kirim Balasan**. Balasan Guru akan ditandai dengan lencana khusus sebagai penegasan jawaban resmi.

### 4.3 Ruang Tugas & Monitoring Pengumpulan

Pusat kendali pembuatan tugas, pemantauan pengumpulan file siswa secara real-time, dan penilaian terintegrasi.

- **Cara Membuat Tugas Baru**:
    1. Pilih menu **Ruang Tugas**, klik tombol **Buat Tugas Baru** untuk menampilkan jendela modal.
    2. Masukkan **Judul Tugas / Materi** (contoh: _Praktikum Mandiri Struktur Tumbuhan_).
    3. Tulis **Instruksi / Deskripsi Singkat** secara runtut dan jelas.
    4. Konfigurasikan tautan referensi pendukung di kolom **Link Lampiran (Opsional)**. Anda dapat memanfaatkan ikon Google Drive di sebelah kanan kolom untuk menarik dokumen secara instan dari Drive Anda.
    5. Masukkan **Batas Waktu Pengumpulan (Opsional)** menggunakan pemilih tanggal dan waktu terintegrasi.
    6. Sesuaikan **Pengaturan Tambahan**:
        - **Izinkan Pengumpulan File/Link (Switch)**: Hidupkan jika siswa wajib mengumpulkan berkas/tautan pengerjaan. Matikan apabila tugas ini hanya bersifat instruksi bacaan mandiri tanpa feedback berkas.
        - **Kunci Otomatis Setelah Deadline (Switch)**: Jika diaktifkan, sistem akan mengunci formulir pengumpulan siswa seketika setelah melewati batas waktu yang ditentukan.
    7. Klik tombol biru **Publikasikan**.

- **Alur Monitoring Pengumpulan & Penilaian**:
    1. Pada halaman Ruang Tugas, temukan tugas yang ingin dinilai, lalu klik tombol **Monitoring & File Siswa**.
    2. Halaman monitoring menyajikan tabel **DataTable** berisi daftar lengkap siswa di kelas bersangkutan beserta status pengumpulan mereka:
        - _Belum Mengumpulkan (Lencana Merah)_
        - _Sudah Mengumpulkan (Lencana Hijau)_ lengkap dengan tanggal dan waktu presisi pengumpulan siswa.
    3. Untuk siswa yang sudah mengumpulkan, klik tombol biru **Pratinjau / Unduh Dokumen** atau **Buka Link Jawaban**.
    4. Sistem akan menampilkan **Premium Fullscreen Viewer Modal**:
        - Jika berupa berkas dokumen/Drive, pratinjau isi dokumen akan tampil langsung tanpa mengunduh berkas.
        - Jika berupa video YouTube, sistem secara otomatis mengekstrak ID video dan menampilkan video player di dalam modal.
        - Jika berupa gambar lokal, sistem menampilkan visualisasi gambar secara proporsional.
        - Tutup modal untuk mematikan player audio/video guna mencegah suara bertumpuk di browser Anda.
    5. Di kolom **Penilaian** sebelah kanan baris siswa:
        - Masukkan skor angka pada kolom input nilai (rentang 0-100).
        - Klik tombol hijau **Simpan**.
        - Pesan sukses akan berkedip singkat di pojok kanan atas, dan tombol akan berubah menjadi _Tersimpan_ berwarna biru.
    6. **Memasukkan ke Rekap Global**: Setelah semua siswa yang mengumpulkan telah Anda nilai, klik tombol biru besar **Masukkan ke Rekap Tugas** di pojok kanan atas halaman monitoring. Tindakan ini memindahkan seluruh nilai tugas yang tersimpan di dalam modul monitoring ini secara massal ke dalam database rekapitulasi semester siswa.

### 4.4 Ruang Kompetensi Guru

Halaman khusus untuk melihat pemetaan kompetensi dasar mata pelajaran Anda terhadap target kurikulum kelas berjalan. Data ini sinkron dengan data kurikulum yang didefinisikan oleh Operator sekolah dan menjadi panduan saat Anda menyusun rencana mengajar di agenda harian.

---

## 5. Modul Penilaian & Laporan

Pusat kendali asesmen ujian terstruktur, penginputan nilai manual, dan kompilasi rekapitulasi laporan nilai akhir.

### 5.1 Ruang Ujian & Manajemen Bank Soal

Portal cerdas bagi Guru untuk menginputkan paket soal ujian. Akses penginputan ini diproteksi oleh gerbang penginputan (**Gate Status**) yang dikelola secara terpusat oleh Operator.

- **Peringatan Penting (Gerbang Ditutup)**:
  Apabila Operator menutup gerbang akses penginputan, halaman Ruang Ujian Anda akan menampilkan lencana gembok merah bertuliskan _"Gerbang Penginputan Ditutup / Input Ditangguhkan"_. Pada kondisi ini, Anda diblokir dari membuat, mengubah, atau mengunggah soal baru ke sistem. Silakan berkoordinasi dengan Operator jika Anda membutuhkan pembukaan akses.

Apabila gerbang penginputan berstatus terbuka (Open), Anda dapat memilih metode penginputan berikut:

#### A. Metode 1: Smart Input (Mode Teks Cerdas)

Metode revolusioner yang memproses salinan teks soal dari dokumen Microsoft Word atau Notepad secara massal dan mengklasifikasikannya ke dalam tipe soal yang tepat secara otomatis.

1. Buka Ruang Ujian, klik kartu **Smart Input**.
2. Anda akan melihat editor teks besar di sebelah kiri.
3. Anda dapat mengeklik tombol **Tarik GDocs** untuk membuka jendela **Google Picker API**. Masukkan dokumen soal langsung dari Google Drive Anda secara aman. Sistem akan mengekstrak teks dokumen tersebut ke dalam editor secara instan.
4. Pastikan teks soal Anda ditulis mengikuti standar penulisan parser platform GARA berikut:

```text
1. Siapakah presiden pertama Indonesia?
A. Soekarno
B. Hatta
C. Soeharto
D. Habibie
E. Gus Dur
Kunci Jawaban: A

2. Pilih 2 negara ASEAN yang berbatasan darat dengan Indonesia!
A. Malaysia
B. Singapura
C. Papua Nugini
D. Filipina
E. Thailand
Kunci Jawaban: A, C

3. [TIPE:BENAR-SALAH]
Pancasila adalah dasar negara Indonesia : BENAR
Jakarta terletak di pulau Sumatra : SALAH
Kunci Jawaban: BENAR, SALAH

4. Tuliskan rumus kimia dari air!
Kunci Jawaban: H2O
```

5. Penjelasan Tipe Soal yang Terbaca Otomatis oleh Sistem:
    - **Pilihan Ganda (PG)**: Default format jika terdapat 1 kunci jawaban tunggal.
    - **Pilihan Ganda Kompleks (PG Kompleks)**: Terbaca otomatis jika kunci jawaban dipisahkan dengan tanda koma (contoh: _Kunci Jawaban: A, C_).
    - **Benar/Salah**: Dipicu wajib dengan baris pembuka `[TIPE:BENAR-SALAH]` diikuti pernyataan dan diakhiri kunci boolean.
    - **Isian Singkat**: Terbaca otomatis jika soal tidak memiliki opsi pilihan huruf (A-E) namun langsung mencantumkan nilai kunci jawaban di bawahnya.
6. Klik tombol **PARSE & PREVIEW**.
7. Sistem menyaring teks secara instan dan menampilkan hasil konversi di panel kanan berupa daftar tabel soal lengkap dengan tipe yang berhasil dideteksi dan lencana cek validasi.
8. Klik tombol **Simpan Langsung** untuk memproses soal ke dalam bank soal draf, atau klik **QC Preview** untuk melakukan penyuntingan tingkat lanjut.

#### B. Metode 2: Quality Control (QC) Soal & Penyuntingan Inline

Guru wajib melakukan kontrol kualitas mandiri untuk meninjau kecocokan visual soal persis seperti yang akan dilihat oleh siswa saat ujian sebelum diserahkan ke Operator.

1. Akses halaman **Quality Control (QC)** dari beranda Ruang Ujian.
2. Anda akan melihat antarmuka interaktif: sidebar kiri berisi navigasi kotak nomor soal, dan panel kanan berisi kartu soal yang dapat disunting secara instan.
3. **Penyuntingan Soal Cepat (Inline Edit)**:
    - **Mengubah Teks Soal**: Cukup arahkan kursor dan klik pada area teks soal di dalam kartu. Teks tersebut langsung berstatus aktif untuk diketik (_contenteditable_). Ketik perubahan yang diinginkan di sana secara langsung.
    - **Mengubah Opsi Pilihan**: Klik dan sunting teks pilihan jawaban A-E dengan mudah.
    - **Mengubah Kunci Jawaban**:
        - Pada soal PG/PG Kompleks: Klik pada kartu opsi pilihan. Opsi yang terpilih sebagai kunci akan ditandai dengan warna biru cerah dan tanda centang secara real-time. Klik kembali untuk membatalkan status kunci.
        - Pada soal Benar/Salah: Klik lencana tombol hijau _BENAR_ atau tombol merah _SALAH_ di bawah masing-masing pernyataan untuk memetakan kunci jawaban secara instan.
        - Pada soal Isian Singkat: Klik dan sunting teks kunci jawaban isian yang berlatar hijau secara manual.
4. **Penyematan Media Aset pada Soal**:
    - Klik tombol **+ Aset** di pojok kanan atas kartu soal. Jendela modal aset interaktif akan terbuka.
    - Pilih tipe aset yang ingin disematkan:
        - **Upload Gambar (Lokal)**: Pilih berkas gambar JPG/PNG dari komputer Anda, sistem akan memuat pratinjau dinamis.
        - **Link Gambar (Eksternal)**: Masukkan tautan gambar internet, gambar langsung terintegrasi.
        - **Video YouTube**: Masukkan URL video YouTube. Sistem secara cerdas mengonversinya menjadi frame video player interaktif.
        - **File Audio (MP3)**: Unggah file audio, pemutar audio MP3 asli dari browser akan langsung disematkan pada soal (cocok untuk materi listening).
        - **Google Drive**: Hubungkan berkas Drive Anda via Google Picker API.
    - Klik **Simpan Aset**. Media aset kini tampil eksklusif tepat di atas teks soal.
    - Gunakan tombol **Hapus Aset** (ikon tanda silang merah) di atas media jika ingin mencabut aset tersebut.
5. **Penyerahan Hasil QC ke Operator**:
    - Setelah seluruh soal dirasa sempurna dan tidak ada kesalahan penulisan, klik tombol **Kirim ke Operator** di bagian bawah sidebar kiri.
    - Konfirmasikan tindakan pada popup SweetAlert2 yang muncul.
    - Soal dikirimkan ke antrean validasi Operator dan status soal berubah dari _DRAFT_ menjadi _VALIDATED_.

#### C. Metode 3: Manual Input

Metode penyusunan soal konvensional satu per satu menggunakan form asisten interaktif. Cocok untuk Guru yang ingin menyusun soal secara perlahan dan terstruktur tanpa perlu mengingat format Smart Input.

### 5.2 Input Nilai Tugas Mandiri

Digunakan apabila Anda ingin menginput nilai tugas secara manual tanpa melalui sistem pengumpulan berkas digital (misalnya nilai tugas fisik di buku latihan).

1. Klik menu **Input Nilai Tugas** pada akordeon Penilain & Laporan.
2. Pilih nomor tugas yang ingin diinputkan nilainya.
3. Daftar seluruh siswa akan ditampilkan secara urut abjad.
4. Isi skor nilai pada kolom masing-masing siswa.
5. Klik **Simpan Nilai**.

### 5.3 Rekap Nilai Tugas Komprehensif

Halaman rekapitulasi yang menyajikan rangkuman akumulasi nilai tugas seluruh siswa dalam satu kelas terpilih sepanjang semester berjalan. Menyediakan fitur pencarian nama siswa, penyaringan urutan nilai tertinggi ke terendah per tugas, serta tombol ekspor laporan massal untuk mempermudah pengolahan rapor akhir.

---

## 6. Modul Absensi & Agenda Mengajar

Pusat administrasi harian untuk memantau presensi kelas dan menyusun laporan pelaksanaan mengajar terintegrasi.

### 6.1 Input Absensi & Generator Jurnal Otomatis

Setiap kali Anda selesai melakukan tatap muka pembelajaran, Anda wajib mencatatkan riwayat kehadiran siswa sekaligus menyusun Jurnal Mengajar.

- **Cara Menginput Kehadiran Siswa**:
    1. Akses menu **Input Absensi** pada sidebar.
    2. Sistem otomatis membaca tanggal hari ini dan menentukan nomor pertemuan pembelajaran berikutnya secara berurutan (misalnya: _Pertemuan ke-3_).
    3. Tentukan **Metode Pembelajaran** melalui dropdown: _Tatap Muka, Daring (Online),_ atau _Hybrid_.
    4. **Penyusunan Jurnal Cerdas (Generator Jurnal Otomatis)**:
        - GARA menyediakan asisten cerdas untuk menghemat waktu Anda menulis Jurnal Mengajar (Pokok Bahasan).
        - Pada kotak **Generator Jurnal Otomatis**, pilih jenis **Aktivitas (KKO)** pembelajaran (contoh: _Menjelaskan konsep_ atau _Melaksanakan Ulangan Harian_).
        - Pilih **Materi (Sesuai RPP)** pada dropdown materi. Dropdown ini memuat daftar materi yang telah Anda upload sebelumnya.
        - Sistem secara cerdas menyusun kalimat baku di kolom **Review Akhir (Pokok Bahasan)** (contoh: _Menjelaskan konsep mengenai materi Bab 1: Struktur Sel Hewan_). Anda dapat mengedit kembali teks hasil generate otomatis ini untuk menambahkan catatan spesifik.
    5. **Pengisian Presensi Siswa**:
        - Pada tabel daftar siswa, gunakan tombol pintas **All H** di bagian header jika seluruh siswa di kelas hadir tanpa keterangan sakit/izin. Seluruh radio button siswa akan langsung terpilih ke opsi **H** (Hadir).
        - Jika ada siswa yang berhalangan, klik radio button status yang sesuai pada baris nama siswa bersangkutan:
            - **H** (Hadir - Hijau)
            - **I** (Izin - Biru)
            - **S** (Sakit - Kuning)
            - **A** (Alpha - Merah)
    6. Klik tombol **Simpan Absensi & Buat Agenda** di bawah halaman.
    7. Konfirmasikan di popup SweetAlert2.
    8. Sistem menyimpan absensi siswa, _sekaligus meregistrasikan lembar Agenda Mengajar harian secara otomatis_ ke database.

### 6.2 Rekapitulasi Absensi & Quick Edit

Halaman pusat rekap presensi kelas terpadu dalam bentuk matriks Pivot Table.

- **Fitur Utama Rekap Absensi**:
    - **Penyaringan Laporan**: Terdapat form filter di bagian atas halaman untuk menyaring rekap kehadiran berdasarkan **Bulan** (menyediakan opsi _Semua Bulan_ untuk melihat akumulasi kehadiran satu semester penuh) dan **Tahun**.
    - **Matriks Matang (Pivot Table)**: Menyajikan tabel komprehensif berisi Nama Siswa, NIS, daftar status kehadiran per pertemuan (P-1, P-2, P-3, dst.), total kumulatif status kehadiran (H, S, I, A), dan hitungan persentase presensi (`% Hadir` dihitung otomatis: `(Hadir / Total Pertemuan) * 100`).
    - **Fitur Quick Edit Kehadiran**:
        1. Jika terjadi kesalahan input absensi di masa lalu, Anda tidak perlu menghapus agenda. Cukup klik langsung pada lencana status huruf (H/S/I/A) di sel pertemuan bersangkutan di dalam tabel rekap.
        2. Jendela popup input interaktif dari SweetAlert2 akan muncul secara instan menanyakan status kehadiran baru untuk siswa tersebut pada pertemuan terpilih.
        3. Pilih status kehadiran pengganti pada menu dropdown, lalu klik **Simpan**.
        4. Sistem memproses pembaruan data secara asinkronus (AJAX) dan memuat ulang halaman dengan kalkulasi persentase kehadiran baru yang telah terhitung otomatis.
    - **Ekspor Laporan Cetak Terpadu**:
        - GARA menyediakan tiga tombol ekspor tangguh di atas tabel rekap:
            - **Excel**: Mengunduh berkas spreadsheet mentah untuk pengolahan data lokal.
            - **PDF**: Mengompilasi rekap ke dalam dokumen PDF siap cetak dengan ukuran A4.
            - **Cetak**: Membuka antarmuka cetak browser bawaan. Tombol cetak ini dilengkapi dengan skrip pengatur gaya khusus (_landscape forced_) untuk memaksa visualisasi tabel rekap yang lebar tercetak rapi secara mendatar (landscape) dan ramah tinta (_ink-saving printer setup_), sehingga tidak akan terpotong saat dicetak ke kertas fisik.

### 6.3 Agenda Mengajar Harian

Menampilkan jurnal pelaksanaan mengajar yang tercatat rapi secara kronologis.

- **Cara Mengelola Agenda Mengajar**:
    1. Akses menu **Agenda Mengajar**.
    2. Gunakan filter rentang tanggal (_Dari - Sampai_) untuk membatasi rentang pencarian log mengajar.
    3. Tabel menyajikan Tanggal, Jam, Rencana Kegiatan (diambil otomatis dari Jurnal Mengajar saat input absensi), Catatan Pelaksanaan, dan daftar siswa tidak hadir pada hari itu (lencana kuning bertuliskan jumlah siswa tidak hadir dilengkapi info detail nama jika didekatkan kursor).
    4. **Mengubah Agenda**:
        - Klik tombol kuning **Edit Agenda** di baris terkait.
        - Jendela modal sunting agenda akan terbuka. Anda dapat menyunting isi rencana kegiatan atau menambahkan **Catatan Pelaksanaan** riil di kelas (contoh: _Pembelajaran kondusif, siswa antusias berdiskusi_).
        - Klik **Simpan**.
    5. **Menghapus Agenda**:
        - Klik tombol merah **Hapus**. Konfirmasikan tindakan. Tindakan ini akan menghapus log agenda mengajar tersebut secara permanen.

---

## 7. Pengelolaan Akun & Sandi Mandiri

Fasilitas keamanan bagi Guru untuk memutakhirkan identitas diri dan menjaga kerahasiaan kredensial login.

### 7.1 Profil Saya & Unggah Foto (AJAX)

Halaman personalisasi data profil Anda di dalam platform GARA.

- **Cara Memperbaharui Foto Profil**:
    1. Akses menu **Tentang Saya** pada kelompok menu navigasi bawah.
    2. Halaman profil menampilkan kartu identitas dinamis Anda. Arahkan kursor tepat di atas area lingkaran foto profil Anda.
    3. Ikon kamera kecil akan muncul di atas foto profil Anda. **Klik lingkaran foto** tersebut.
    4. Jendela pemilih file komputer akan terbuka. Pilih file foto profil baru Anda.
        - _Spesifikasi File_: Wajib berformat **JPG, JPEG, atau PNG** dengan ukuran berkas **maksimal 2 Megabyte (MB)**.
    5. Sistem secara otomatis menjalankan proses unggah latar belakang (AJAX Upload).
    6. Ketika selesai, foto profil baru Anda langsung diperbarui seketika pada kartu profil, header navbar atas, dan sidebar navigasi tanpa perlu memuat ulang (refresh) halaman browser Anda.

### 7.2 Pembaharuan Kata Sandi Mandiri

Menjaga kerahasiaan kredensial akun adalah kewajiban setiap Guru demi mencegah penyalahgunaan hak akses penilaian siswa.

- **Cara Mengubah Password**:
    1. Klik menu **Ubah Password** di sidebar kiri.
    2. Masukkan kata sandi lama Anda saat ini pada kolom **Password Lama** (pastikan tombol Caps Lock di keyboard Anda berada pada status yang tepat).
    3. Masukkan kata sandi baru minimal 6 karakter pada kolom **Password Baru**. Sangat disarankan mengombinasikan huruf besar, huruf kecil, dan angka.
    4. Ketik ulang kata sandi baru tersebut pada kolom **Konfirmasi Password Baru**.
    5. Klik tombol **Ubah Password**.
    6. Sistem akan melakukan validasi keamanan dan menampilkan notifikasi sukses hijau. Sesi login Anda tetap aman dan sandi baru telah aktif untuk sesi login berikutnya.

---

## 8. Panduan Troubleshooting & Solusi Mandiri

Berikut adalah rangkuman kendala operasional yang sering dialami oleh Guru beserta solusi mandiri yang dapat Anda lakukan secara instan untuk mengatasinya:

| Kejadian / Masalah                                                                   | Solusi Pengguna                                                                                                                                                                                                                                                                              |
| :----------------------------------------------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Gembok merah muncul di Ruang Ujian dan tidak bisa klik tombol input soal baru**    | Hal ini terjadi karena Operator sedang menutup Gerbang Akses Penginputan Soal secara global untuk persiapan sistem. Silakan hubungi Operator sekolah untuk berkoordinasi meminta pembukaan gerbang asesmen Anda.                                                                             |
| **Tombol Tarik GDocs tidak aktif atau menampilkan tulisan peringatan sambungan**     | Fitur Google Docs memerlukan integrasi OAuth Google pada akun Anda. Pastikan Anda telah masuk ke menu _Tentang Saya_ dan ikuti panduan menghubungkan akun Google Drive Anda di bagian integrasi profil terlebih dahulu.                                                                      |
| **Hasil Parse teks di Smart Input berantakan atau tipe soal tidak terdeteksi tepat** | Periksa kembali kerapihan pengetikan soal Anda. Pastikan setiap baris opsi dimulai dengan huruf besar dan titik (contoh: _A. Pilihan_) serta tulisan kunci ditulis presisi tanpa spasi berlebih (contoh: _Kunci Jawaban: A_).                                                                |
| **Tombol Simpan Absensi macet atau loading berputar tanpa henti**                    | Hal ini biasanya disebabkan oleh hilangnya koneksi internet sesaat. Coba segarkan halaman browser Anda (tekan `Ctrl + R`), centang kembali kehadiran siswa menggunakan shortcut _All H_, lalu klik simpan ulang.                                                                             |
| **Tabel Rekap Absensi atau Agenda terpotong sebelah kanan saat dicetak fisik**       | Pastikan Anda menggunakan tombol _Cetak_ resmi yang disediakan oleh sistem GARA (bukan menu cetak bawaan browser di pojok atas peramban). Sistem kami secara otomatis mendeteksi lebar tabel dan memaksakan tata letak horizontal (landscape) ramah tinta agar tercetak rapi secara presisi. |
| **Pesan error muncul saat menyimpan nilai: "Format Nilai Tidak Valid"**              | Pastikan nilai tugas yang Anda inputkan di kolom nilai merupakan bilangan bulat di antara rentang angka 0 hingga 100. Sistem secara otomatis menolak karakter huruf, simbol koma, atau angka di atas 100 demi akurasi database.                                                              |
| **Data Rencana Pembelajaran tidak muncul di pilihan Generator Jurnal**               | Generator Jurnal Otomatis membaca data pokok bahasan RPP yang telah diunggah sebelumnya. Pastikan RPP semester aktif mata pelajaran Anda di kelas tersebut sudah selesai dipetakan pada menu Ruang Materi terlebih dahulu.                                                                   |

---

_Panduan Penggunaan Sistem Guru ini disusun secara akurat berdasarkan antarmuka aktual platform GARA._
_Semoga membantu meningkatkan efisiensi dan kelancaran proses pembelajaran Anda di sekolah!_
