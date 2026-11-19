# PANDUAN PENGGUNAAN SISTEM (USER GUIDE)

## PERAN: KEPALA SEKOLAH (PRINCIPAL / KEPEK) - GARA LMS

### Garuda Akademi - Portal Pemantauan & Pelaporan Eksekutif Terintegrasi

---

## DAFTAR ISI

1. [Pendahuluan & Kedudukan Peran Kepsek](#1-pendahuluan--kedudukan-peran-kepsek)
2. [Executive Dashboard & Akses Cepat Layanan Nasional](#2-executive-dashboard--akses-cepat-layanan-nasional)
3. [Panduan Pemantauan Kinerja Guru (Teacher Performance)](#3-panduan-pemantauan-kinerja-guru-teacher-performance)
4. [Panduan Monitoring Ujian & Distribusi Nilai](#4-panduan-monitoring-ujian--distribusi-nilai)
5. [Panduan Laporan Eksekutif Formal & Generator Kop Surat](#5-panduan-laporan-eksekutif-formal--generator-kop-surat)
6. [Panduan Keamanan Akun & Pengubahan Kata Sandi](#6-panduan-keamanan-akun--pengubahan-kata-sandi)
7. [Troubleshooting & Solusi Masalah Mandiri Kepala Sekolah](#7-troubleshooting--solusi-masalah-mandiri-kepala-sekolah)

---

## 1. PENDAHULUAN & KEDUDUKAN PERAN KEPSEK

Dalam ekosistem GARA LMS, peran **Kepala Sekolah (Kepsek)** diposisikan sebagai pemegang otoritas pengawas tertinggi (_Executive Monitor_). Akun Kepala Sekolah dirancang secara khusus bersifat **Read-Only (Pantau Saja)** untuk menjaga keaslian data operasional dan akademik, namun dilengkapi dengan instrumen analitik canggih untuk mengukur kualitas pembelajaran.

### Kedudukan Teknis Peran Kepsek:

- **Akses Pantauan Penuh (Supervisory Privilege)**: Kepala Sekolah dapat melihat seluruh aktivitas pengajaran guru, interaksi diskusi kelas, pelaksanaan ujian, serta pencapaian nilai seluruh kelas tanpa dapat memanipulasi data tersebut.
- **Audit Kepatuhan (Compliance Audit)**: Sistem mengolah data mentah RPP, Tugas, dan Soal menjadi skor kepatuhan guru berbentuk persentase untuk memudahkan penilaian kinerja GTK.
- **Legalisasi Laporan Formal (Authorized Signatory)**: Menyediakan mesin cetak laporan eksekutif formal yang secara otomatis menyusun Kop Surat resmi sekolah dan menyisipkan Lembar Pengesahan tanda tangan.

---

## 2. EXECUTIVE DASHBOARD & AKSES CEPAT LAYANAN NASIONAL

Saat pertama kali berhasil masuk ke dalam sistem, Kepala Sekolah akan langsung diarahkan menuju **Executive Dashboard** yang menyajikan visualisasi data aktivitas sekolah secara seketika (_real-time_).

### Panel Informasi Dashboard:

1.  **Live Clock & Salam Pembuka**: Sambutan formal yang dilengkapi waktu server aktual (_WIB_) untuk memastikan akurasi pantauan aktivitas harian.
2.  **Widgets Statistik Utama**:
    - **Total Guru**: Jumlah seluruh guru pengampu aktif di sekolah (klik untuk lompat ke pemantauan Kinerja Guru).
    - **Total Siswa**: Jumlah kumulatif siswa aktif terdaftar di database sekolah.
    - **Laporan Eksekutif**: Tautan cepat menuju wadah pencetakan laporan formal.
    - **Ujian Hari Ini**: Jumlah sesi ujian aktif yang sedang atau akan berlangsung hari ini.
3.  **Grafik Tren Aktivitas LMS (7 Hari Terakhir)**: Grafik garis (_Line Chart_) interaktif berbasis Chart.js yang memetakan tren volume interaksi harian siswa untuk mengukur tingkat keaktifan pemanfaatan LMS.
4.  **Tabel Jadwal Ujian Mendatang**: Menampilkan 10 antrean ujian mendatang lengkap dengan informasi mata pelajaran, kelas, tanggal, jam, dan status pelaksanaannya.

### Akses Cepat Layanan Nasional:

Bilah dashboard juga mengintegrasikan portal layanan resmi eksternal dari Kementerian Pendidikan Dasar dan Menengah guna mempermudah koordinasi administrasi nasional:

- **Kategori Superaplikasi & Sekolah**:
    - _Rumah Pendidikan_: Ekosistem resmi platform pendidikan nasional.
    - _Ruang GTK_: Portal pengembangan kompetensi Guru dan Tenaga Kependidikan.
    - _KSPSTENDIK_: Manajemen kepangkatan kepala sekolah dan pengawas kependidikan.
- **Kategori Data & Administrasi**:
    - _Dapodik_: Referensi data pokok pendidikan nasional terintegrasi.
    - _Belajar.id_: Layanan aktivasi akun pembelajaran resmi Kemendikdasmen.
    - _SIMPKB_: Portal sistem pengembangan keprofesian guru berkelanjutan.
- **Kategori Kebijakan & Regulasi**:
    - _Dirjen GTK_: Pusat informasi direktorat jenderal guru dan tenaga kependidikan.
    - _JDIH_: Jaringan dokumentasi hukum regulasi pendidikan nasional.
    - _Kemendikdasmen_: Portal berita dan kebijakan resmi kementerian.

---

## 3. PANDUAN PEMANTAUAN KINERJA GURU (TEACHER PERFORMANCE)

Menu **Kinerja Guru** menyediakan instrumen audit kepatuhan pengajaran berbasis grafik komparatif dan skor performa numerik.

### Fitur Utama Analisis Kinerja:

- **Grafik Perbandingan Aset (Kinerja Chart)**: Grafik batang (_Bar Chart_) tiga warna yang menyandingkan data kuantitatif materi diunggah, tugas diberikan, dan jumlah soal ujian tervalidasi dari masing-masing guru.
- **Skor Kepatuhan Otomatis (Compliance Indicator)**: Sistem menghitung tingkat kepatuhan guru berdasarkan aktivitas riil di database:
    - Formula skor mengombinasikan kehadiran unggahan materi (bobot 50%), pemberian tugas (bobot 30%), dan keaktifan memandu forum diskusi kelas (bobot 20%).
    - Hasil kalkulasi diwujudkan dalam bilah progres berwarna (_Compliance Progress Bar_) yang dinamis:
        - _Hijau (Baik)_: Skor kepatuhan >= 70%.
        - _Jingga (Cukup)_: Skor kepatuhan antara 40% - 69%.
        - _Merah (Perlu Perhatian)_: Skor kepatuhan < 40%.
- **Tabel Kinerja Guru (DataTables)**:
    - Menampilkan data NIP, Nama Guru, Jabatan, Kelas Diajar, jumlah berkas Materi, jumlah berkas Tugas, jumlah soal tervalidasi (validated) vs draf, serta keaktifan forum diskusi.
    - Tabel dilengkapi fitur penyaringan instan (_search filter_) dan pembagian halaman (_paging_) otomatis.

---

## 4. PANDUAN MONITORING UJIAN & DISTRIBUSI NILAI

Menu **Monitoring Ujian & Nilai** dirancang khusus untuk memantau integritas pelaksanaan ujian yang sedang berjalan serta melihat statistik pemerataan nilai hasil belajar siswa.

### Panel Pemantauan Jadwal Ujian Aktif:

Menampilkan jadwal pelaksanaan seluruh ujian dalam rentang waktu 90 hari terakhir.
Kepala Sekolah dapat memantau:

- _Mata Pelajaran & Kelas Ujian_.
- _Jenis Asesmen_ (Tipe penilaian seperti Penilaian Harian, UTS, atau UAS).
- _Waktu & Durasi Ujian_ (Pukul mulai dan selesai, serta durasi menit).
- _Mode Submit_: Memantau apakah ujian menggunakan sistem pengumpulan serentak (_SERENTAK_) atau regular.
- _Status Pelaksanaan_: Berlangsung (aktif), Mendatang (terjadwal), Selesai Hari Ini, atau Selesai secara permanen.

### Panel Distribusi & Rata-Rata Nilai Asesmen:

Menyajikan rangkuman statistik nilai dari setiap pelaksanaan ujian di sekolah untuk mengukur tingkat keberhasilan penyampaian materi oleh guru:

- _Jumlah Peserta_: Menampilkan berapa banyak siswa yang telah menyelesaikan sesi ujian.
- _Rata-Rata Kelas_: Ditampilkan dengan angka berukuran besar dan tebal, serta diberikan indikator warna performa:
    - _Hijau_: Rata-rata kelas memuaskan (>= 75).
    - _Jingga_: Rata-rata kelas standar cukup (60 - 74).
    - _Merah_: Rata-rata kelas rendah di bawah KKM (< 60).
- _Pencapaian Ekstrim_: Menampilkan perbandingan nilai tertinggi (_tertinggi_) dan nilai terendah (_terendah_) di kelas tersebut secara transparan.

---

## 5. PANDUAN LAPORAN EKSEKUTIF FORMAL & GENERATOR KOP SURAT

Modul **Laporan Eksekutif** adalah alat bantu utama Kepala Sekolah untuk mencetak atau menyimpan berkas pelaporan formal berstandar kementerian, lengkap dengan kop surat sekolah dan tanda tangan pengesahan resmi.

### Struktur Tiga Tab Laporan Eksekutif:

1.  **Tab Jurnal Mengajar**: Menampilkan seluruh data supervisi jurnal harian mengajar guru (hari/tanggal, jam, identitas guru/NIP, kelas/mapel, pokok bahasan kegiatan, catatan kendala pelaksanaan, serta daftar siswa yang tidak hadir).
2.  **Tab Kelengkapan Perangkat**: Menampilkan checklist kelengkapan administrasi ajar guru (jumlah RPP terunggah, perbandingan soal bank soal tervalidasi vs draf, serta lencana status kelengkapan otomatis "Lengkap" atau "Perlu Perbaikan").
3.  **Tab Rekap Presensi & Akademik**: Menampilkan rekapitulasi kumulatif per kelas (jumlah siswa, total pertemuan tatap muka, total kehadiran terperinci: Hadir/Sakit/Izin/Alpa, persen kehadiran keseluruhan kelas, serta rata-rata tugas dan asesmen).

### Prosedur Ekspor Dokumen & Generator Lembar Pengesahan:

Kepala Sekolah dapat mengekspor data laporan ke tiga jenis format keluaran menggunakan tombol khusus:

- **Tombol Excel**: Menyimpan salinan tabel ke format Spreadsheet guna keperluan olah data statistik lanjutan di komputer lokal.
- **Tombol Cetak**: Menghapus seluruh dekorasi halaman web dan mencetak laporan langsung ke mesin printer fisik menggunakan tata letak ramah tinta (landscape default).
- **Tombol PDF Formal (Mesin Generator PDF)**:
    - Sistem secara dinamis menyusun **Kop Surat Resmi** di halaman pertama dokumen, memuat Nama Sekolah (dalam huruf kapital tebal) serta informasi Tahun Ajaran dan Semester aktif.
    - Menyisipkan garis pemisah tebal (_divider line_) di bawah kop surat sebelum judul laporan.
    - Mengatur warna baris header tabel menjadi warna biru gelap formal (`#1a3c6e`) dengan tulisan putih.
    - Menyisipkan **Lembar Pengesahan Otomatis (Dual-Signature Footer)** pada akhir dokumen secara rapi:
        - _Sisi Kiri_: Tanda tangan pengesahan dari koordinator bidang (misal: Waka Kurikulum atau Wali Kelas).
        - _Sisi Rangan_: Tanda tangan persetujuan resmi Kepala Sekolah yang dilengkapi kota administrasi, tanggal hari pencetakan, dan garis pembatas nama serta kolom NIP.

---

## 6. PANDUAN KEAMANAN AKUN & PENGUBAHAN KATA SANDI

Mengingat akun Kepala Sekolah memiliki hak akses terhadap seluruh laporan internal sekolah, menjaga keamanan sandi merupakan hal yang sangat krusial.

### Langkah-Langkah Mengubah Kata Sandi:

1.  Klik menu **Ubah Password** pada bilah navigasi samping.
2.  **Isi Data Keamanan**:
    - _Password Saat Ini_: Masukkan kata sandi yang saat ini sedang aktif digunakan.
    - _Password Baru_: Ketikkan kata sandi baru yang kuat (disarankan kombinasi huruf kapital, angka, dan simbol).
    - _Konfirmasi Password Baru_: Ketik ulang kata sandi baru untuk memastikan kesesuaian pengetikan.
3.  Klik tombol **Simpan Password**.
4.  Sistem akan segera memvalidasi kecocokan sandi lama. Jika berhasil, popup notifikasi hijau (_Berhasil_) akan muncul dan sistem mencatat pembaruan data secara aman.

---

## 7. TROUBLESHOOTING & SOLUSI MASALAH MANDIRI KEPALA SEKOLAH

Berikut adalah daftar kendala operasional yang sering dialami oleh Kepala Sekolah beserta solusi pemecahan masalah teknisnya secara mandiri:

| Kejadian / Masalah                                                                          | Solusi Mandiri Kepala Sekolah                                                                                                                                                                                                                                                                        |
| :------------------------------------------------------------------------------------------ | :--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Data statistik atau grafik interaktif di dashboard tidak muncul (loading terus-menerus)** | Hal ini terjadi karena kegagalan pemuatan data asinkron akibat koneksi internet terputus di tengah jalan. Silakan lakukan penyegaran halaman browser (tekan tombol F5 atau klik Reload), sistem akan memanggil kembali API statik eksekutif ke database pusat.                                       |
| **Format Kop Surat atau Lembar Pengesahan tanda tangan terpotong saat diekspor ke PDF**     | Pastikan orientasi kertas printer Anda disesuaikan dengan jenis laporan. Laporan Jurnal Mengajar dan Kelengkapan Perangkat memiliki kolom yang lebar dan wajib menggunakan orientasi _Landscape (Mendatar)_. Sedangkan Rekap Presensi Kelas sangat optimal menggunakan orientasi _Portrait (Tegak)_. |
| **Muncul pesan kesalahan "Password lama tidak cocok" saat melakukan perubahan kata sandi**  | Silakan periksa kembali ketikan kata sandi lama Anda dan pastikan tombol _Caps Lock_ pada keyboard Anda tidak dalam keadaan aktif. Masukkan kembali sandi lama dengan presisi huruf besar dan kecil yang sesuai.                                                                                     |
| **Tidak dapat menekan tombol edit atau mengubah status persetujuan soal ujian guru**        | Hak akses akun Kepala Sekolah sepenuhnya diatur sebagai _Read-Only (Hanya Pantau)_. Tindakan pengubahan data, pengeditan draf soal, penyuntingan presensi, atau penjadwalan ujian merupakan wewenang penuh Operator Sekolah dan Guru Mata Pelajaran.                                                 |
| **Laporan eksekutif menampilkan nama sekolah atau tahun ajaran yang tidak sesuai**          | Data identitas sekolah dan tahun ajaran ditarik langsung dari konfigurasi pusat di sistem Dapodik sekolah. Mintalah Operator Sekolah atau Super Admin untuk mengoreksi data identitas tersebut pada menu _Pengaturan Aplikasi (App Settings)_ di portal admin mereka.                                |
| **Tabel laporan eksekutif kosong atau tidak menampilkan data guru sama sekali**             | Periksa apakah guru bersangkutan sudah mengisi jurnal harian mengajar atau belum. Jika guru belum mengunggah perangkat ajar atau belum melaksanakan presensi, tabel laporan supervisi secara otomatis akan menampilkan baris kosong menunggu data masuk dari guru bersangkutan.                      |
