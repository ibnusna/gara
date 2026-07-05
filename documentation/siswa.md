# PANDUAN PENGGUNAAN SISTEM (USER GUIDE)

## PERAN: SISWA (STUDENT) - GARA LMS

### Garuda Akademi - Portal Pembelajaran Digital Terintegrasi

---

## DAFTAR ISI

1. [Pendahuluan & Konsep Dasar Sesi Kerja](#1-pendahuluan--konsep-dasar-sesi-kerja)
2. [Halaman Utama & Teras Ilmu](#2-halaman-utama--teras-ilmu)
3. [Panduan Operasional Ruang Belajar](#3-panduan-operasional-ruang-belajar)
4. [Panduan Pengumpulan & Revisi Ruang Tugas](#4-panduan-pengumpulan--revisi-ruang-tugas)
5. [Panduan Gamifikasi Waktu Konsentrasi (Ruang Fokus)](#5-panduan-gamifikasi-waktu-konsentrasi-ruang-fokus)
6. [Panduan Platform Mikroblogging Kelas (Ruang Diskusi)](#6-panduan-platform-mikroblogging-kelas-ruang-diskusi)
7. [Panduan Catatan Akademik Blok (Ruang Catatan)](#7-panduan-catatan-akademik-blok-ruang-catatan)
8. [Modul Produktivitas (Jadwal Sholat & Trivia Brain Warmup)](#8-modul-produktivitas-jadwal-sholat--trivia-brain-warmup)
9. [Panduan Profil, Keamanan Akun & Penggantian Mata Pelajaran](#9-panduan-profil-keamanan-akun--penggantian-mata-pelajaran)
10. [Panduan Evaluasi & Arena Ujian Anti-Kecurangan (Ruang Kompetensi)](#10-panduan-evaluasi--arena-ujian-anti-kecurangan-ruang-kompetensi)
11. [Troubleshooting & Solusi Masalah Mandiri Siswa](#11-troubleshooting--solusi-masalah-mandiri-siswa)

---

## 1. PENDAHULUAN & KONSEP DASAR SESI KERJA

Platform Pembelajaran GARA dirancang khusus untuk memberikan pengalaman belajar yang dinamis, interaktif, dan terfokus bagi Siswa. Agar dapat mengakses seluruh fitur pembelajaran, sistem GARA beroperasi menggunakan konsep **Sesi Kerja Aktif**.

Sistem secara ketat mengunci akses fitur berdasarkan kelas dan mata pelajaran yang dipilih oleh siswa pada awal masuk sistem. Sesi kerja ini disimpan dalam enkripsi server (`session('mapel_id')` dan `session('kelas_id')`) guna memastikan seluruh konten akademik yang disajikan bersifat relevan.

### Karakteristik Utama Sistem:

- **Keamanan Multi-Sesi**: Siswa hanya dapat mempelajari satu mata pelajaran aktif dalam satu waktu belajar guna menghindari distraksi kognitif.
- **PWA (Progressive Web App) Responsive**: Antarmuka dioptimalkan secara mendalam untuk perangkat seluler maupun komputer meja dengan layout yang menyesuaikan ukuran layar secara instan.
- **Interaktivitas Cepat**: Menggunakan teknologi rendering AJAX dan manipulasi DOM lokal agar perpindahan menu berlangsung secara instan tanpa membebani kuota data siswa.

---

## 2. HALAMAN UTAMA & TERAS ILMU

Saat siswa berhasil melewati gerbang masuk dan memilih mata pelajaran, halaman utama **Dashboard Siswa** akan menyajikan panel kontrol pembelajaran komprehensif.

### Elemen Utama Dashboard:

1.  **Hero Banner Sapaan**: Menampilkan salam ramah berdasarkan waktu setempat, nama lengkap siswa (`session('nama')`), lencana kelas aktif (`session('nama_kelas')`), dan lencana pintas mata pelajaran aktif (`session('nama_mapel')`).
2.  **Kutipan Motivasi Harian (Daily Quote)**: Kalimat penyemangat terpilih yang berganti setiap hari guna membangun motivasi belajar mandiri siswa.
3.  **Wadah Pengumuman Kelas (Broadcast)**: Area pengumuman dinamis dari guru mata pelajaran yang dimuat secara asinkron. Jika guru menerbitkan pengumuman baru, panel ini otomatis muncul di bagian atas untuk memberikan peringatan dini.
4.  **Teras Ilmu (Grid Navigasi Utama)**: Enam ikon akses instan menuju modul pembelajaran utama (Belajar, Tugas, Kompetensi, Fokus, Diskusi, dan Catatan).
5.  **Zona Produktif**: Widget interaktif yang terdiri atas pendeteksi Jadwal Sholat berbasis lokasi serta kuis asah otak harian (Brain Warmup).
6.  **Jelajah Ilmu (External Resources)**: Grid akses cepat menuju tautan pustaka eksternal tepercaya, seperti Bank Soal Kemdikbud, E-Book Kemendikdasmen, Google Cendekia, Games Edukasi, KBBI, dan Perpustakaan Nasional.

---

## 3. PANDUAN OPERASIONAL RUANG BELAJAR

**Ruang Belajar** adalah pusat materi pembelajaran digital tempat siswa membaca modul, mengunduh bahan ajar, serta menonton video pemaparan guru secara terintegrasi.

### Alur Mempelajari Materi:

1.  Masuk ke menu **Ruang Belajar** dari halaman utama Dashboard.
2.  Sistem menyajikan daftar **Bab** yang tersedia. Klik pada salah satu Bab untuk membuka daftar topik/bagian materi.
3.  Pilih sub-topik materi yang ingin dipelajari untuk masuk ke halaman detail.

### Fitur Detail Materi & Pemutar Video Modern:

- **Layout Kolom Ganda (Split Layout)**: Pada komputer meja, video penjelasan tampil di sisi kiri dan deskripsi/bahan ajar di sisi kanan. Pada seluler, antarmuka bertumpuk secara vertikal yang sangat nyaman dibaca.
- **Mode Masking Penonton Video**: Video YouTube disajikan menggunakan _Cover Masking_ gambar latar bawaan YouTube dengan ikon putar besar. Cukup klik pada gambar sampul tersebut, sistem akan langsung memuat pemutar video dengan opsi pemutaran otomatis (`autoplay=1`).
- **Optimasi Layar Seluler**: Jika materi memuat video pendukung, sistem secara otomatis menyembunyikan header navigasi utama saat diakses dari ponsel. Hal ini bertujuan memberikan ruang baca dan area menonton yang maksimal tanpa terpotong menu navigasi browser.
- **Grid Sumber Daya Belajar (Resources Card)**:
    - **Slide PPT**: Klik untuk membuka salinan file presentasi guru langsung di dalam penampil dokumen terintegrasi.
    - **E-Modul**: Klik untuk membaca dokumen buku paket atau berkas PDF panduan belajar.
    - **Notebook**: Klik untuk mengakses tautan berkas catatan interaktif eksternal.
    - **E-Book**: Klik untuk membaca lembar kegiatan siswa atau berkas teks pendukung utama.
- **Bottom Sheet Viewer (Layar Geser Dokumen)**: Seluruh slide presentasi dan berkas PDF akan terbuka di layar geser bawah khusus secara instan tanpa memaksa browser mengunduh berkas. Siswa juga dapat mengklik tombol "Buka Tab Baru" jika ingin membacanya di layar penuh browser.

---

## 4. PANDUAN PENGUMPULAN & REVISI RUANG TUGAS

**Ruang Tugas** membantu siswa melacak seluruh kewajiban akademik, memantau batas waktu pengerjaan, mengumpulkan hasil kerja, serta melihat umpan balik nilai dari guru.

### Navigasi Tab Status Tugas:

- **Tugas Baru**: Menampilkan seluruh tugas yang belum dikerjakan dan masih berada dalam rentang waktu pengerjaan aktif.
- **Selesai**: Menampilkan daftar tugas yang telah berhasil dikirimkan oleh siswa, baik yang sedang menunggu koreksi maupun yang sudah dinilai oleh guru.
- **Terlewat**: Menampilkan daftar tugas yang batas waktunya telah berakhir dan belum sempat dikumpulkan.

### Penghitung Waktu Mundur Real-Time (Countdown Timer):

Setiap kartu tugas di tab _Tugas Baru_ dilengkapi dengan penghitung waktu mundur yang terus berdetak setiap detik.
Waktu ditunjukkan dengan format `[Hari]h [Jam]j [Menit]m [Detik]d` berwarna merah menyala. Jika waktu pengerjaan habis, kartu tugas otomatis terkunci, bertransisi menjadi transparan, dan berpindah ke tab _Terlewat_ secara otomatis.

### Prosedur Pengumpulan Tugas (Bottom Sheet Form):

1.  Klik tombol **Kerjakan / Kumpulkan** pada kartu tugas pilihan. Layar geser pengumpulan tugas akan naik dari bawah.
2.  **Pilih Metode Pengumpulan**:
    - **Link / Tautan**: Masukkan alamat URL hasil pekerjaan (misalnya tautan Google Drive, Google Docs, atau Canva). Pastikan pengaturan tautan tersebut sudah diatur sebagai "Publik/Dapat Dilihat Semua Orang" agar guru bisa memeriksa.
    - **Upload File**: Klik kotak unggah file untuk memilih berkas dari memori lokal (ukuran maksimum dibatasi 5 MB). Format berkas yang didukung adalah PDF, DOC, DOCX, PPT, PPTX, JPG, JPEG, PNG, WEBP.
3.  **Tulis Catatan Tambahan (Opsional)**: Tulis pesan pribadi atau penjelasan singkat mengenai hasil kerja Anda kepada guru pada kotak teks yang disediakan.
4.  Klik tombol **Kirim Tugas**. Tombol akan menampilkan animasi memuat (_Menyimpan..._) selama proses pengiriman data via AJAX berlangsung. Sistem kemudian menampilkan notifikasi melayang (_Toast_) keberhasilan dan memuat ulang halaman secara otomatis dalam 1,5 detik.

### Pengumpulan Tugas Terlambat (Late Mode):

Bila guru mengaktifkan fitur pengumpulan susulan pada tugas yang telah melewati tenggat waktu, kartu tugas akan muncul di tab _Terlewat_ dengan tombol **Kirim Susulan**. Saat tombol ini diklik, layar pengumpulan akan memunculkan peringatan merah menyala: _"Anda mengumpulkan terlambat. Sistem akan mencatat waktu keterlambatan."_ Tombol aksi utama juga berubah warna menjadi jingga gelap dengan label **Kirim Susulan**.

### Pembatalan & Revisi Pengumpulan:

Jika siswa menyadari ada kesalahan pada berkas yang dikirim dan guru belum melakukan penilaian, siswa dapat merevisi tugas tersebut.
Caranya, klik kartu tugas bersangkutan, lalu pilih tombol merah **Batalkan Pengumpulan**. Sistem akan memunculkan konfirmasi keamanan. Setelah disetujui, berkas lama terhapus dari server dan siswa dapat mengunggah kembali berkas tugas yang benar.

### Penugasan Non-Berkas (Mark As Read):

Untuk tugas yang bersifat instruksional (misalnya perintah untuk membaca buku halaman tertentu atau menghafal teks tanpa perlu mengirim berkas), kartu tugas menyediakan tombol **Tandai Sudah Dibaca**. Mengklik tombol ini akan langsung mengirimkan status penyelesaian manual ke server tanpa memerlukan unggahan berkas atau tautan.

---

## 5. PANDUAN GAMIFIKASI WAKTU KONSENTRASI (RUANG FOKUS)

**Ruang Fokus** adalah modul produktivitas berbasis teknik Pomodoro untuk membantu siswa membangun disiplin belajar bebas distraksi melalui elemen gamifikasi penanaman pohon digital.

### Mekanisme Penggunaan Timer Pomodoro:

- **Siklus Fokus & Istirahat**: Secara baku, satu sesi fokus berlangsung selama 25 menit. Setelah selesai, siswa berhak mendapatkan Istirahat Pendek selama 5 menit. Setelah menyelesaikan 4 sesi fokus, siswa mendapatkan Istirahat Panjang selama 15 menit.
- **Visualisasi Progres Melingkar**: Lingkaran progres di sekeliling waktu akan menyusut perlahan seiring berjalannya waktu.
- **Tombol Kendali**:
    - **Mulai**: Memulai hitung mundur waktu fokus.
    - **Jeda**: Menghentikan sementara hitung mundur apabila ada kendala mendesak.
    - **Lanjut**: Meneruskan kembali sesi belajar yang dijeda.
    - **Reset**: Mengatur ulang timer kembali ke durasi awal (menghapus progres sesi berjalan setelah menyetujui peringatan).
    - **Tombol Pengaturan (Ikon Roda Gigi)**: Membuka jendela pengaturan durasi mandiri. Siswa dapat menyesuaikan durasi menit fokus (1-60 menit), Istirahat Pendek (1-30 menit), dan Istirahat Panjang (1-60 menit) via modal interaktif.

### Sistem Poin Air & 5 Level Pertumbuhan Tanaman:

Setiap kali siswa berhasil menyelesaikan satu sesi fokus penuh tanpa interupsi, siswa akan mendapatkan **1 Poin Air**. Poin air ini digunakan untuk menyiram dan menumbuhkan tanaman digital di dalam akun siswa.

Tahapan pertumbuhan tanaman diwakili oleh gambar grafik vektor interaktif:

1.  **Level 1: Bibit (Sprout)**: Biji kecil di dalam tanah cokelat yang baru bertunas (Poin air: 0 - 7).
2.  **Level 2: Tunas (Seedling)**: Batang hijau muda kecil dengan dua daun pertama (Poin air: 8 - 14).
3.  **Level 3: Tanaman Kecil (Small Plant)**: Batang kokoh dengan cabang daun yang mulai menyebar (Poin air: 15 - 24).
4.  **Level 4: Tanaman Dewasa (Mature Plant)**: Rimbun daun hijau lebat dengan warna bayangan kontras (Poin air: 25 - 39).
5.  **Level 5: Tanaman Subur (Lush Plant)**: Pohon hijau lebat dengan dedaunan berlapis yang sangat indah (Poin air: >= 40).

Setiap kali tanaman naik kelas, sistem akan menampilkan perayaan grafis dengan pesan keberhasilan penanaman.

### Fitur Perlindungan Streak Harian:

- **Streak Hari (Daily Streak)**: Menghitung berapa hari berturut-turut siswa menyelesaikan minimal satu sesi fokus setiap hari.
- **Hukuman Konsistensi**: Jika siswa melewatkan satu hari penuh tanpa melakukan sesi fokus sama sekali, sistem akan memecahkan streak kembali ke angka 0.
- **Penurunan Level Tanaman**: Akibat dari hilangnya konsistensi belajar, level tanaman siswa otomatis diturunkan satu tingkat sebagai bentuk konsekuensi logis untuk memicu motivasi belajar kembali.
- **Target Harian**: Setiap kali siswa berhasil mengumpulkan kelipatan 4 sesi fokus dalam satu hari yang sama, sistem akan memicu jendela perayaan khusus _Streak Harian Tercapai_.

---

## 6. PANDUAN PLATFORM MIKROBLOGGING KELAS (RUANG DISKUSI)

**Ruang Diskusi** adalah media sosial akademis internal kelas yang mengadaptasi gaya mikroblogging modern untuk interaksi tanya jawab antara siswa, rekan sekelas, dan guru.

### Alur Utama Interaksi Sosial:

- **Penyegaran Halaman Sentuh (Pull To Refresh)**: Pada layar ponsel, siswa dapat menyentuh layar dan menariknya ke bawah. Sistem akan menampilkan animasi panah berputar dan memuat ulang daftar diskusi terbaru secara instan tanpa memuat ulang seluruh halaman web.
- **Pemuatan Tak Terbatas (Infinite Feed)**: Siswa dapat menelusuri diskusi tanpa batas. Gunakan tombol _Muat lebih banyak_ di bawah feed untuk mengambil kiriman diskusi yang lebih lama secara asinkron.
- **Pembuatan Utas (Create Thread)**:
    1.  Klik kotak input bertuliskan _"Apa yang sedang terjadi?"_ di bagian atas feed. Jendela popup postingan akan terbuka.
    2.  Tuliskan pertanyaan atau topik bahasan pada area teks yang tersedia.
    3.  **Penyematan Media**: Klik ikon gambar di bagian bawah untuk melampirkan file foto atau gambar penjelas materi. Sistem menyajikan kotak pratinjau gambar instan lengkap dengan tombol hapus jika berkas ingin dibatalkan sebelum diunggah.
    4.  Klik **Posting**.
- **Membaca & Membalas Diskusi (Replies Thread)**:
    1.  Klik pada salah satu kartu diskusi untuk membuka halaman detail diskusi.
    2.  Halaman detail akan menyajikan utas utama secara lengkap di bagian atas, diikuti dengan daftar seluruh balasan rekan kelas di bawahnya.
    3.  **Kirim Balasan**: Tulis pesan balasan pada kolom bawah, sematkan gambar jika diperlukan, lalu klik tombol **Balas**. Balasan Anda akan langsung muncul di daftar tanpa jeda reload halaman.
- **Penyuntingan & Penghapusan Mandiri**: Siswa dapat mengedit isi konten postingan utas atau komentar balasan miliknya sendiri dengan mengklik menu opsi pada kiriman, memilih _Edit_, mengubah teks pada kotak teks khusus, lalu menekan _Simpan_.

---

## 7. PANDUAN CATATAN AKADEMIK BLOK (RUANG CATATAN)

**Ruang Catatan** adalah ruang jurnal pribadi siswa yang menerapkan konsep editor berbasis blok (Notion-like Editor) untuk menyusun rangkuman pelajaran secara rapi dan terstruktur.

### Editor Blok Interaktif:

Siswa dapat menyusun dokumen bukan hanya sebagai teks paragraf biasa, melainkan sebagai susunan blok informasi interaktif yang dinamis.
Klik tombol **Tambah Blok** untuk memilih jenis blok yang ingin disisipkan:

- **Paragraf**: Format teks standar untuk menulis penjelasan panjang.
- **Heading 1 / 2 / 3**: Judul bab, sub-bab, atau bagian materi dengan ukuran huruf hirarkis.
- **Bullet List**: Daftar poin penting menggunakan penanda lingkaran.
- **Numbered List**: Daftar urutan proses belajar menggunakan angka berurutan.
- **Checklist**: Daftar tugas mandiri yang dilengkapi kotak centang interaktif yang dapat dicentang langsung di layar.
- **Quote**: Blok kutipan menjorok ke dalam dengan garis pembatas vertikal untuk menandai rumus atau ucapan guru yang penting.
- **Code Block**: Area khusus berlatar gelap untuk menuliskan baris kode pemrograman atau rumus matematika khusus.
- **Divider**: Garis pembatas horizontal tipis untuk memisahkan bagian pembahasan catatan agar lebih rapi.

Setiap blok yang dibuat memiliki kontrol kontrol independen berupa tombol ikon tempat sampah di sisi kiri untuk menghapus blok tersebut secara instan tanpa mengganggu blok lainnya.

### Template Rangkuman Siap Pakai (Preset Templates):

Siswa tidak perlu menyusun catatan dari nol. GARA menyediakan 5 template akademis terstruktur:

1.  **Catatan Mata Pelajaran**: Format baku untuk mencatat materi harian pelajaran (memuat kolom nama pelajaran, topik utama, tanggal, poin utama materi, dan catatan tambahan).
2.  **Ringkasan Bab**: Format terstruktur untuk meringkas isi bab buku paket (memuat konsep utama, rumus penting di blok kode, dan kesimpulan akhir).
3.  **To-Do Tugas**: Pengelola daftar tugas pribadi (memuat tabel checklist prioritas tugas utama dan target batas waktu).
4.  **Catatan Guru**: Khusus untuk mencatat ceramah langsung guru (memuat identitas guru, tanggal kelas, isi pelajaran penting, dan kutipan ucapan guru).
5.  **Jurnal Belajar**: Catatan refleksi pribadi pasca-belajar (memuat poin materi yang dipelajari hari ini, kendala pemahaman yang dihadapi, serta rencana tindak lanjut belajar berikutnya).

### Fitur Pencarian Cepat & Ekspor Dokumen:

- **Pencarian Global**: Klik tombol cari (ikon kaca pembesar), ketikkan kata kunci yang dicari. Sistem akan menelusuri seluruh dokumen catatan, mencocokkan judul maupun isi di dalam blok, lalu menampilkan hasil pencarian berupa kartu rangkuman yang dapat langsung diklik untuk melompat ke halaman tersebut.
- **Ekspor Cetak / PDF**: Klik tombol _Print / PDF_ di bagian bawah menu samping untuk mencetak catatan atau menyimpannya langsung sebagai berkas PDF rapi yang ramah tinta printer.

---

## 8. MODUL PRODUKTIVITAS (JADWAL SHOLAT & TRIVIA BRAIN WARMUP)

GARA LMS mengintegrasikan widget produktivitas di halaman dashboard utama untuk merangsang kesiapan belajar dan kedisiplinan beribadah harian.

### Widget Jadwal Sholat Terintegrasi API:

- Sistem menggunakan antarmuka pemograman aplikasi (API) geografis pihak ketiga guna mendeteksi titik koordinat perangkat siswa secara asinkron.
- Menampilkan nama wilayah deteksi terkini beserta tanggal berjalan secara akurat.
- Menyajikan grid lima waktu sholat wajib harian (Subuh, Dzuhur, Ashar, Maghrib, Isya) yang disesuaikan secara dinamis dengan zona waktu lokasi siswa berada.

### Widget Kuis Trivia (Brain Warmup):

- Merupakan kuis pengetahuan sains dan ilmu komputer singkat untuk melatih daya fokus otak sebelum memulai belajar.
- Pertanyaan trivia diunggah secara dinamis menggunakan database kuis terenkripsi.
- Siswa memilih salah satu dari 4 pilihan tombol jawaban yang disajikan.
- Sistem langsung memberikan verifikasi instan: tombol akan berubah warna menjadi hijau jika jawaban benar, atau merah jika salah, disertai dengan penjelasan singkat jawaban yang benar.

---

## 9. PANDUAN PROFIL, KEAMANAN AKUN & PENGGANTIAN MATA PELAJARAN

Siswa memiliki kendali penuh atas privasi akun dan konfigurasi navigasi mata pelajaran melalui halaman **Tentang Saya**.

### Panel Informasi Akun:

- Menampilkan foto profil avatar yang dihasilkan secara otomatis menggunakan inisial nama siswa secara profesional.
- Menampilkan nama lengkap, nomor induk siswa (NIS), kelas terdaftar, dan status keaktifan akun.
- Menampilkan rincian profil sekolah lengkap dengan Tahun Ajaran dan Semester aktif yang sedang berjalan di database pusat.

### Peringatan Dini Keamanan Kata Sandi (Default Password Alert):

Apabila siswa baru pertama kali masuk sistem dan masih menggunakan kata sandi bawaan pabrik dari sekolah, halaman profil akan memunculkan spanduk peringatan jingga tebal bertuliskan: _"Password bawaan terdeteksi. Segera ubah password Anda untuk keamanan akun."_ Siswa dapat langsung mengklik tombol **Ubah** untuk menuju formulir penggantian kata sandi baru. Formulir dilengkapi dengan tombol mata penyembunyi sandi guna menghindari intipan orang lain saat mengetik.

### Tombol Alih Sesi Pintas:

- **Ganti Mapel**: Jika siswa ingin beralih mempelajari mata pelajaran lain, siswa cukup mengklik tombol merah **Ganti Mapel** di bagian bawah halaman profil. Sistem akan menghapus enkripsi sesi lama secara aman dan menampilkan kembali daftar mata pelajaran sekolah untuk dipilih kembali.
- **Logout Aman**: Tombol keluar yang secara permanen menghancurkan seluruh cache sesi kerja aktif siswa di server sekolah guna mencegah penyalahgunaan akun di komputer publik perpustakaan sekolah.

---

## 10. PANDUAN EVALUASI & ARENA UJIAN ANTI-KECURANGAN (RUANG KOMPETENSI)

**Ruang Kompetensi** adalah modul pengerjaan ujian resmi sekolah yang dirancang menggunakan sistem pengawasan berlapis untuk menjamin integritas kejujuran akademik siswa selama ujian berlangsung.

Platform GARA mengimplementasikan dua mode ujian yang berjalan secara paralel bergantung pada jenis paket soal yang disiapkan oleh guru:

---

### A. MODE 1: ARENA UJIAN INTERNAL GARA (NATIVE EXAM ARENA)

Mode ini digunakan untuk paket soal yang diinput langsung di bank soal sekolah. Menyajikan antarmuka ujian yang sangat modern, bersih, dan cepat.

#### 1. Mekanisme Keamanan Berlapis (Anti-Cheat Engine):

- **Kunci Layar Penuh Wajib (Mandatory Fullscreen)**: Ujian hanya dapat dimulai jika siswa menyetujui akses layar penuh browser. Selama ujian berlangsung, navigasi browser, tombol kembali, dan tombol beranda diblokir penuh oleh sistem.
- **Deteksi Perpindahan Tab (Tab Switching Lock)**: Sensor `visibilitychange` dan `window.blur` mendeteksi setiap upaya siswa keluar dari halaman ujian (misalnya membuka tab baru untuk mencari jawaban di mesin pencari, meminimalkan browser, atau membuka aplikasi perpesanan).
- **Alarm Pelanggaran (Violation Audio)**: Jika siswa kedapatan keluar dari area ujian, sistem secara otomatis akan membunyikan alarm suara sirene melengking (`pelanggaran.mp3`) berulang-ulang tanpa henti dari pengeras suara perangkat, merekam log pelanggaran ke database pengawas ujian, dan menampilkan popup merah yang mengunci antarmuka pengerjaan.
- **Kunci Salin-Tempel (Copy-Paste Lock)**: Klik kanan mouse, fitur blok teks, pintasan keyboard salin (Ctrl+C), dan tempel (Ctrl+V) dinonaktifkan sepenuhnya di seluruh area ujian guna mencegah penyebaran soal ujian ke luar sistem.

#### 2. Fitur Interaktivitas Pengerjaan:

- **Kontrol Huruf Mandiri (Font Sizers)**: Tombol ukuran `A` (Kecil, Sedang, Besar) di bagian atas untuk memperbesar ukuran tulisan soal bagi siswa yang mengalami kendala mata tanpa memecah susunan visual halaman.
- **Satu Klik Zoom Gambar (Interactive Zoom Modal)**: Jika soal atau pilihan opsi memuat bagan, grafik ilmiah, atau bacaan teks panjang berbentuk gambar, siswa cukup mengklik gambar tersebut sekali. Sistem akan memunculkan modal melayang dengan visual gambar ukuran asli yang tajam sehingga detail terkecil dapat dibaca dengan mudah.
- **Penanda Ragu-Ragu**: Centang kotak _Ragu_ pada bilah navigasi bawah untuk menandai soal bersangkutan dengan warna kuning pada papan navigasi agar mudah ditemukan kembali untuk ditinjau ulang sebelum mengakhiri ujian.
- **Status Warna Navigasi Soal**:
    - **Hijau**: Soal sudah selesai dijawab.
    - **Kuning**: Soal sudah dijawab namun ditandai ragu-ragu.
    - **Abu-Abu**: Soal masih kosong belum diisi jawaban.
- **Tombol Navigasi Mobile Drawer**: Pada layar seluler, daftar nomor soal disembunyikan agar layar tidak sesak. Papan navigasi nomor dapat diakses kapan saja dengan mengetuk tombol _Daftar Soal_ untuk memunculkan laci geser bawah nomor soal.

#### 3. Sinkronisasi Waktu Server Nyata (Epoch Server Clock):

- Waktu hitung mundur ujian disinkronkan langsung menggunakan waktu epoch server sekolah (`window.__SERVER_TIMESTAMP__`). Siswa tidak dapat memanipulasi waktu ujian dengan cara mengubah jam internal pada pengaturan perangkat komputer mereka.
- Jika waktu pengerjaan habis, sistem secara paksa menutup ujian, membatalkan seluruh status klik ragu-ragu menjadi jawaban final, mengirimkan paket jawaban ke database server secara otomatis, dan mengembalikan siswa ke halaman utama dengan aman.

---

### B. MODE 2: IN-APP BROWSER EXAM (EXTERNAL EMBEDDED EXAM)

Mode ini digunakan jika guru mengintegrasikan ujian menggunakan platform eksternal resmi, seperti Google Forms, Microsoft Forms, Quizizz, atau Kahoot.

#### 1. Mekanisme Kerja Iframe Sandboxed:

- Halaman evaluasi dimuat di dalam bingkai kontainer terenkripsi (`#exam-iframe`) yang aman dan terisolasi dari sistem utama.
- Menyediakan tombol **Reload** manual khusus di header toolbar. Jika koneksi internet siswa sempat terputus saat memuat formulir eksternal, siswa dapat memuat ulang bingkai soal tanpa kehilangan waktu ujian.
- Menampilkan indikator loading melayang berputar yang estetik sebelum bingkai formulir siap ditampilkan sepenuhnya.

#### 2. Kunci Layar Penuh Warning Layar Merah (Fullscreen Enforcement):

- Siswa wajib mengklik tombol biru **Mulai Ujian Sekarang** di halaman persiapan untuk memicu mode layar penuh browser.
- Jika siswa memaksa keluar dari mode layar penuh atau meminimalkan browser, sistem langsung mendeteksi tindakan tersebut dan memunculkan **Layar Peringatan Merah Total (Warning Overlay)** yang memblokir visual bingkai ujian sepenuhnya disertai dengan tulisan tebal: _"PELANGGARAN TERDETEKSI! Anda telah keluar dari mode layar penuh (Fullscreen)..."_
- Satu-satunya cara untuk melanjutkan kembali ujian adalah dengan mengklik tombol putih **Kembali ke Ujian (Fullscreen)** untuk memulihkan kembali mode layar penuh penuh.

#### 3. Header Toolbar Lipat Mengambang (Floating Chip Timer):

- Guna memberikan kenyamanan pandangan visual yang maksimal saat membaca soal ujian, siswa dapat menyembunyikan bilah navigasi atas (toolbar) dengan menekan tombol chevron atas.
- Toolbar akan melipat secara elegan dan bertransisi menjadi sebuah tombol gelembung kecil berlatar transparan blur yang melayang di tengah atas layar (`#restore-header-btn`).
- Meskipun dilipat, tombol gelembung tersebut tetap menampilkan penunjuk waktu mundur jam ujian yang terus berdetik secara _real-time_.
- Bila waktu ujian tersisa kurang dari 60 detik, angka jam pada tombol gelembung melayang tersebut otomatis berkedip merah menyala sebagai pengingat batas waktu krusial. Siswa dapat mengetuk kembali tombol gelembung tersebut untuk memunculkan kembali toolbar menu navigasi penuh.

---

## 11. TROUBLESHOOTING & SOLUSI MASALAH MANDIRI SISWA

Berikut adalah panduan penyelesaian masalah teknis operasional yang sering dialami oleh Siswa beserta solusi mandiri yang dapat segera dipraktikkan secara instan:

| Kejadian / Masalah                                                                            | Solusi Mandiri Siswa                                                                                                                                                                                                                                                                                                                                                            |
| :-------------------------------------------------------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Muncul spanduk merah tebal koneksi putus saat pengerjaan ujian**                            | Jangan panik dan jangan memuat ulang browser Anda. Hal ini terjadi karena jaringan internet Anda terputus sementara. Sensor pemantau jaringan GARA mendeteksi status luring. Silakan cari posisi sinyal internet yang stabil, maka spanduk merah akan otomatis menghilang dan progres jawaban Anda akan segera disinkronisasikan kembali ke server utama tanpa kehilangan data. |
| **Gambar bagan atau grafik soal ujian terlalu kecil dan tidak terbaca jelas di layar ponsel** | Cukup klik satu kali langsung pada bagian gambar yang ingin dilihat. Sistem GARA akan segera memicu jendela zoom melayang instan berlatar transparan yang menampilkan gambar tersebut dalam ukuran penuh yang sangat tajam tanpa memecah layout halaman ujian Anda. Klik tombol silang atau area luar gambar untuk menutup kembali.                                             |
| **Layar peringatan merah total mengunci halaman ujian eksternal Anda**                        | Masalah ini muncul karena Anda tidak sengaja keluar dari mode layar penuh browser (fullscreen) akibat menyentuh tombol beranda ponsel atau ada notifikasi aplikasi lain masuk. Segera klik tombol putih bertuliskan "Kembali ke Ujian (Fullscreen)" untuk membuka kembali akses pengerjaan soal ujian Anda.                                                                     |
| **Tombol Kirim Tugas tidak merespon saat diklik di halaman pengumpulan tugas**                | Pastikan ukuran file berkas tugas Anda tidak melebihi batas maksimal 5 megabyte. Jika Anda memilih metode tautan Google Drive, pastikan format tautan diawali dengan karakter penulisan "https://" yang valid. Periksa juga kembali apakah metode pengumpulan yang dipilih sudah sesuai dengan jenis berkas yang Anda unggah.                                                   |
| **Level tanaman di Ruang Fokus menurun dan poin streak harian hilang secara misterius**       | Hal ini terjadi sebagai bentuk hukuman konsistensi belajar karena Anda melewatkan satu hari penuh kemarin tanpa melakukan aktivitas belajar fokus sama sekali di Ruang Fokus. Sistem secara otomatis mereset streak hari menjadi nol dan menurunkan level tanaman satu tingkat untuk memicu kedisiplinan Anda kembali hari ini.                                                 |
| **Daftar soal dan kuis tidak muncul di Ruang Kompetensi atau halaman beranda blank**          | Periksa sesi mata pelajaran aktif Anda. Akses halaman profil "Tentang Saya", klik tombol merah "Ganti Mapel", lalu pilih kembali mata pelajaran sekolah yang ingin dipelajari untuk memulihkan cache sesi aktif browser Anda ke database pusat sekolah.                                                                                                                         |
