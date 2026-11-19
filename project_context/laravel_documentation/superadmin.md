# 📖 Panduan Penggunaan Sistem (User Guide) — Peran Super Admin

### Platform GARA (Garuda Akademi) · Edisi Pengguna · Mei 2026

---

## 1. Pendahuluan

Selamat datang di Panduan Penggunaan Sistem untuk peran **Super Admin** pada platform **GARA** (_Garuda Akademi_). Platform ini dirancang untuk mempermudah administrasi sekolah tingkat menengah secara menyeluruh. Sebagai Super Admin, Anda memegang hak akses tertinggi dan kendali penuh atas konfigurasi inti, keamanan, peran (_role_), akun administratif, pengawasan log aktivitas (_audit trail_), serta keselamatan data (_database backup_).

Panduan ini disusun dari sudut pandang **pengguna akhir (non-teknis)** berdasarkan antarmuka pengguna (UI/UX) nyata, alur interaksi visual, serta journey yang Anda lalui saat mengoperasikan sistem dari awal hingga akhir.

---

## 2. Hak Akses Super Admin

Sebagai Super Admin, wewenang Anda difokuskan pada pengelolaan platform dan administrasi tingkat sistem:

- **Mengatur Konfigurasi Global:** Mengubah nama sekolah, tahun ajaran, semester aktif, dan batas waktu mati sesi (_session timeout_).
- **Mengelola Pengguna Administratif:** Menambah, menghapus, atau mereset password untuk akun Admin dan Operator.
- **Mengelola Peran & Izin (_Roles & Permissions_):** Menentukan tingkat wewenang bagi setiap grup pengguna.
- **Memantau Log Aktivitas:** Melihat dan meninjau setiap tindakan penting yang dilakukan oleh seluruh pengguna di sistem.
- **Manajemen Pemulihan & Backup:** Membuat cadangan data (_SQL Backup_) untuk seluruh database dan mengunduhnya ke komputer lokal.
- **Menjaga Keamanan Sistem:** Memblokir alamat IP yang mencurigakan serta mengaktifkan mode pemeliharaan (_Maintenance Mode_) ketika sistem sedang diperbaiki.

> 💡 **Informasi Penting:** Super Admin _tidak mengelola_ data kurikulum, jadwal ujian, bank soal, absensi siswa, atau tugas harian. Hal-hal akademik tersebut sepenuhnya didelegasikan kepada peran **Operator** dan **Guru**.

---

## 3. Cara Login (Alur Masuk Sistem)

Sebelum dapat mengakses dashboard Super Admin, Anda harus melewati alur autentikasi aman:

```
[Halaman Login Web] ──► Input Username & Password ──► Cek Status Aktif & Maintenance ──► Redirect ke Dashboard
```

### Langkah-langkah Login:

1. Buka web browser dan akses alamat portal GARA sekolah Anda (biasanya otomatis diarahkan ke halaman login).
2. Di layar login yang menampilkan identitas sekolah, masukkan **Username** Anda (misalnya: `admin01`).
3. Masukkan **Password** Anda.
    - _Catatan:_ Jika ini adalah pertama kalinya Anda masuk menggunakan akun baru bawaan sistem, password default Anda adalah **`admingara776`**. Anda akan langsung melihat peringatan untuk segera mengubahnya.
4. Klik tombol **Login**.
5. Sistem akan menampilkan animasi pemuatan (_loading animation_), memverifikasi bahwa akun Anda aktif (`status_aktif = 1`), dan mengarahkan Anda secara otomatis ke halaman **Super Admin Dashboard**.

---

## 4. Dashboard Utama

Setelah berhasil login, Anda akan disambut oleh halaman dashboard dengan visualisasi ringkas mengenai kondisi sistem terkini.

![Dashboard Overview](file:///var/www/html/eduap/v2/gara/assets/img/FARA_BLACK.svg) _Logo Platform GARA_

### Informasi yang Ditampilkan:

- **Info Box Statistik Cepat:**
    - **Total Pengguna:** Menampilkan jumlah total pengguna administratif aktif yang terdaftar di sistem. Memiliki tombol pintas _"Info lebih lanjut"_ untuk langsung menuju manajemen pengguna.
    - **Total Peran:** Menampilkan jumlah peran (_role_) yang terdaftar. Dilengkapi tombol pintas _"Info lebih lanjut"_ ke manajemen peran.
    - **Log Aktivitas:** Jumlah rekaman log aktivitas sistem. Dilengkapi tombol pintas _"Info lebih lanjut"_ ke riwayat log.
- **Status Koneksi Layanan Utama (Status Sistem):**
    - Card yang menunjukkan status kesehatan koneksi database secara real-time:
        - **`Auth_Gara`** (Online - Hijau)
        - **`LMS_Pembelajaran`** (Online - Hijau)
        - **`Asesmen_Gara`** (Online - Hijau)
- **Shortcut Tindakan Cepat:**
    - Tombol pintas biru **Tambah Pengguna** untuk mempercepat penambahan akun baru.
    - Tombol pintas hijau **Konfigurasi Sistem** untuk langsung membuka pengaturan global.
    - Tombol pintas abu-abu **Backup Data** untuk mengamankan salinan database sekolah.

---

## 5. Penjelasan Menu Sidebar (Navigasi Sistem)

Navigasi Anda dikelompokkan secara teratur pada panel menu kiri (_sidebar_):

- **DASHBOARD:** Halaman beranda utama dengan statistik cepat dan pemantau status sistem.
- **SYSTEM (Pengaturan Utama):**
    - **Pengaturan Sistem:** Mengonfigurasi parameter global seperti nama sekolah, tahun ajaran aktif, dan masa kedaluwarsa sesi idle.
    - **Role & Permission:** Mengelola peran pengguna dan memberikan atau mencabut hak akses (_permissions_) secara dinamis.
    - **Admin & Operator:** Halaman khusus mengelola akun administrative sekolah.
    - **Audit Log:** Riwayat rekaman seluruh aksi pengguna demi transparansi.
    - **Database & Backup:** Panel kendali pencadangan data murni SQL secara lokal.
    - **Keamanan Sistem:** Mengelola pemblokiran IP dan menyalakan mode pemeliharaan sistem.
- **LAINNYA (Akun Personal):**
    - **Tentang Saya:** Profil ringkas Anda dan area pembaruan foto profil.
    - **Ubah Password:** Area penting untuk memperbarui kata sandi akun Anda.
    - **Logout:** Tombol cepat keluar dari sistem.

---

## 6. Tata Cara Mengelola Data

Setiap modul pengelolaan data (seperti Peran, Akun, dan IP Blokir) disajikan dengan komponen **DataTable** interaktif yang mempermudah Anda dalam menyaring data, mencari data dengan cepat lewat kolom pencarian (_search_), dan mengatur urutan tampilan per kolom.

Berikut adalah panduan aksi yang dapat Anda lakukan pada sistem:

### A. Tata Cara Menambah Data

#### 1. Menambah Role Baru

- Akses menu **Role & Permission** pada sidebar.
- Klik tombol biru **Tambah Role** di pojok kanan atas halaman.
- Pada jendela modal yang muncul:
    - Masukkan **Nama Role** (gunakan huruf kecil tanpa spasi, contoh: `proktor`).
    - Centang daftar **Hak Akses (Permissions)** yang ingin diberikan kepada role tersebut menggunakan tombol switch geser yang tersedia.
- Klik tombol **Simpan Role**. Jendela akan tertutup dan data baru akan langsung muncul di tabel.

#### 2. Menambah Akun Admin atau Operator Baru

- Akses menu **Admin & Operator** pada sidebar.
- Klik tombol **Tambah Pengguna** di pojok kanan atas.
- Pada jendela modal tambahkan data pengguna:
    - Masukkan **Nama Lengkap** pengguna baru (misalnya: `Rudi Hermawan`).
    - Masukkan **ID Login (Username)** yang unik tanpa spasi (misalnya: `rudi_ops`).
    - Pilih **Peran (Role)** melalui menu dropdown (pilih antara `ADMIN` atau `OPERATOR`).
    - _Perhatikan Informasi Sandi:_ Sistem secara otomatis menetapkan kata sandi default awal:
        - Jika role adalah Admin: **`admingara776`**
        - Jika role adalah Operator: **`garaop457`**
- Klik tombol **Simpan User**.

#### 3. Memblokir IP Address Baru

- Akses menu **Keamanan Sistem** pada sidebar.
- Pada kartu "Daftar IP Diblokir", klik tombol merah **Tambah IP Blokir**.
- Pada jendela popup:
    - Masukkan **IP Address** yang ingin diblokir (contoh: `192.168.10.15` atau IP publik tertentu).
    - Masukkan **Alasan / Keterangan** pemblokiran agar admin lain mengetahuinya (contoh: `Percobaan pembobolan login berulang`).
- Klik tombol merah **Blokir IP**.

---

### B. Tata Cara Mengubah Data

#### 1. Mengubah Hak Akses Role (Permissions)

- Akses menu **Role & Permission**.
- Temukan role yang ingin diubah dalam tabel, lalu klik tombol kuning dengan ikon pensil (**Edit**) di kolom Aksi.
- Pada jendela modal edit role:
    - Ubah nama role jika diperlukan.
    - Gunakan tombol switch geser untuk menambah atau mencabut **Hak Akses (Permissions)**.
- Klik tombol **Update Role**. Perubahan akan langsung berdampak instan kepada seluruh pengguna yang memegang role tersebut.

#### 2. Mereset Password Akun Admin / Operator

- Jika salah satu admin atau operator lupa kata sandi mereka, Anda dapat meresetnya dari menu **Admin & Operator**.
- Cari nama pengguna dalam tabel, lalu klik tombol biru dengan ikon kunci (**Reset Password**).
- Pada jendela popup:
    - Sistem akan menampilkan username yang bersangkutan (kolom dinonaktifkan demi keamanan).
    - Masukkan **Password Baru** yang aman (minimal 6 karakter).
- Klik tombol **Reset Password**. Beritahukan kata sandi baru tersebut secara aman kepada pengguna terkait.

#### 3. Mengubah Foto Profil Anda

- Akses menu **Tentang Saya**.
- Arahkan kursor ke foto profil bundar besar Anda di hero banner, lalu **klik foto tersebut** (ikon kamera akan muncul).
- Pilih file foto baru dari komputer Anda (Wajib berformat **JPG/PNG** dan ukuran **maksimal 2 MB**).
- Sistem secara otomatis mengunggah foto melalui AJAX. Setelah selesai, foto profil baru Anda akan langsung diperbarui di sidebar dan navbar atas.

---

### C. Tata Cara Menghapus Data

#### 1. Menghapus Peran (Role)

- Akses menu **Role & Permission**.
- Klik tombol merah bergambar tempat sampah (**Delete**) di samping role yang ingin Anda hapus.
    - _Aturan Proteksi:_ Peran utama dengan ID 1 tidak dapat dihapus.
- Jendela konfirmasi peringatan dari _SweetAlert_ akan muncul: _"Hapus Role? Role yang dihapus tidak bisa dikembalikan!"_.
- Klik **Ya, hapus!** untuk menyetujui, atau **Batal** jika ingin membatalkan tindakan.

#### 2. Menghapus Akun Pengguna Administratif

- Akses menu **Admin & Operator**.
- Temukan nama pengguna yang ingin dihapus, klik tombol merah bergambar tempat sampah (**Hapus Pengguna**).
    - _Aturan Proteksi:_ Akun utama Super Admin (ID 1) dilindungi secara mutlak dan tombol hapusnya tidak akan aktif.
- Pada popup konfirmasi SweetAlert _"Hapus Pengguna? Akun ini akan dihapus secara permanen!"_, klik **Ya, hapus!**.

#### 3. Membuka Blokir IP Address (Unblock)

- Akses menu **Keamanan Sistem**.
- Pada tabel "Daftar IP Diblokir", temukan alamat IP terkait dan klik tombol hijau **Unblock** (ikon gembok terbuka).
- Konfirmasikan tindakan pada popup browser dengan mengeklik **OK**. Pengguna dari alamat IP tersebut kini dapat kembali mengakses portal GARA.

---

## 7. Tata Cara Monitoring Sistem

Untuk menjamin transparansi operasional dan mendeteksi aktivitas mencurigakan, Anda dapat melakukan pemantauan sistem secara berkala:

### Memantau Audit Log (Riwayat Aktivitas):

1. Klik menu **Audit Log** pada sidebar.
2. Di layar utama, Anda akan melihat maksimal 1000 rekaman log aktivitas terbaru.
3. Setiap baris log menampilkan detail informasi berikut:
    - **Waktu:** Kapan aktivitas dilakukan (tanggal & jam tepat).
    - **Pelaku:** Nama lengkap, Username (`@username`), dan Peran akun yang bertindak.
    - **Modul:** Area sistem yang diakses (misalnya: `Security`, `User`, `Role`, `Settings`).
    - **Aktivitas:** Tindakan spesifik yang dilakukan (contoh: `Logged In`, `Added User 'rudi'`, `Blocked IP '192.168.1.5'`).
    - **URL / Deskripsi Tambahan:** Halaman target atau link file jika ada.
    - **IP Address:** Alamat jaringan asal yang digunakan oleh pelaku.
4. **Menghapus Log:** Jika database log dirasa terlalu penuh, Anda dapat mengeklik tombol merah **Hapus Semua Log** di pojok kanan atas.
    - ⚠️ _Peringatan Kritis:_ Tindakan ini akan menghapus permanen seluruh log dari database. Konfirmasikan melalui SweetAlert _"Ya, hapus semua!"_ hanya jika Anda sangat yakin.

---

## 8. Tata Cara Pengaturan Sistem (Konfigurasi Global)

Modul ini digunakan untuk mengatur dasar operasional portal sekolah:

1. Akses menu **Pengaturan Sistem** pada sidebar.
2. Ubah data pada kolom yang tersedia:
    - **Nama Institusi:** Nama sekolah menengah yang akan ditampilkan di seluruh header aplikasi dan halaman login (misalnya: `SMP Garuda Bangsa`).
    - **Tahun Ajaran:** Tahun akademik aktif (contoh: `2025/2026`).
    - **Semester Aktif:** Pilih antara `Ganjil (1)` atau `Genap (2)`.
    - **Session Timeout (Menit):** Batas waktu idle (tidak ada aktivitas klik/input) sebelum pengguna otomatis dikeluarkan demi keamanan privasi (minimal 5 menit, disarankan `30` menit).
3. Klik tombol biru **Simpan Pengaturan**. Pesan sukses berwarna hijau _"Berhasil"_ akan muncul di layar.

---

## 9. Tata Cara Manajemen Database & Backup

Demi menghindari kehilangan data akibat kendala server, biasakan untuk mencadangkan database secara berkala:

```
[Menu Database & Backup] ──► Pilih Nama Database ──► Klik Buat Backup SQL ──► Unduh File Backup
```

### Langkah-langkah Pembuatan Backup:

1. Klik menu **Database & Backup** pada sidebar.
2. Di kolom **Buat Backup Baru** (sisi kiri):
    - Pilih database yang ingin dicadangkan pada menu dropdown:
        - `Admin & Accounts (auth_gara)` — data akun dan keamanan.
        - `LMS Pembelajaran (lms_pembelajaran)` — materi, tugas, dan absensi harian.
        - `Assessment/Ujian (asesmen_gara)` — bank soal, aset, dan hasil ujian siswa.
        - `Semua Database (All-in-One)` — mencadangkan ketiga database sekaligus dalam satu file terpadu.
3. Klik tombol biru **Buat Backup SQL**.
4. Tunggu beberapa saat (sistem sedang menyusun skema dan data murni SQL secara native). Setelah berhasil, tabel **Daftar File Backup** di sisi kanan akan terisi berkas baru dengan format nama `backup_n_database_tanggal.sql`.

### Mengelola File Backup:

- **Mengunduh ke Komputer:** Klik tombol hijau bergambar panah ke bawah (**Download**) di kolom Aksi pada baris file backup yang diinginkan. Simpan file `.sql` tersebut di media penyimpanan aman Anda.
- **Menghapus Backup Lama:** Klik tombol merah bergambar tempat sampah (**Hapus**). Konfirmasikan penghapusan saat ditanya browser.

---

## 10. Tata Cara Mengelola Keamanan & Maintenance Mode

Ketika Anda harus melakukan pembaruan sistem besar atau impor data massal yang rentan terhadap gangguan pengguna, Anda dapat mengaktifkan **Mode Pemeliharaan (Maintenance Mode)**:

1. Klik menu **Keamanan Sistem** pada sidebar.
2. Di kartu **Pengaturan Keamanan Global** (sisi kiri), Anda akan melihat tombol geser (_switch_) **Mode Maintenance**.
3. Geser tombol ke arah kanan untuk **mengaktifkan** (atau klik langsung label _"Aktifkan Maintenance"_).
4. Klik tombol **Ubah Aturan Maintenance**.
    - _Dampak Sistem:_ Seluruh pengguna (Operator, Kepsek, Guru, Siswa) yang sedang aktif akan langsung diarahkan keluar dan mendapati halaman HTTP 503 (Under Maintenance).
    - _Bypass Khusus:_ Hanya akun Anda (Super Admin) yang diizinkan untuk tetap login dan mengoperasikan dashboard.
5. **Menonaktifkan:** Jika pekerjaan pemeliharaan telah selesai, cukup matikan switch geser tersebut ke kiri dan klik **Ubah Aturan Maintenance** kembali agar sistem online untuk umum.

---

## 11. Tata Cara Logout (Keluar Sistem)

Untuk menghindari penyalahgunaan komputer ketika Anda meninggalkan meja kerja, pastikan untuk selalu keluar dari sistem dengan aman:

1. Temukan opsi **Logout** di sistem. Osi ini dapat diakses melalui dua area:
    - Link berwarna merah **Logout** di pojok kanan atas Navbar.
    - Menu berwarna merah **Logout** (ikon pintu keluar) di bagian paling bawah Sidebar.
2. Klik opsi tersebut. Sistem akan secara otomatis menghancurkan token sesi aktif Anda, meregenerasi CSRF token, dan mengarahkan Anda kembali ke halaman Login dalam kondisi steril.

---

## 12. Catatan Penggunaan (Perhatian Khusus)

- **Kebijakan Password Aman:** Jangan biarkan akun Admin atau Operator baru menggunakan sandi default (`admingara776` / `garaop457`) terlalu lama. Desak mereka untuk segera menggantinya di menu _Ubah Password_.
- **Akses Portabilitas:** Hindari memblokir IP lokal loopback (`127.0.0.1` atau `localhost`) karena dapat melumpuhkan akses administratif server lokal Anda sendiri.
- **Cadangan Berkala:** Selalu unduh file backup `.sql` ke komputer lokal Anda setelah dibuat. Jangan membiarkan puluhan file backup menumpuk di folder server karena akan memakan ruang kapasitas disk server web Anda.

---

## 13. Kemungkinan Error yang Ditemui User & Solusinya

| Kejadian / Masalah                                                       | Penyebab                                                                                                           | Solusi Pengguna                                                                                                                        |
| :----------------------------------------------------------------------- | :----------------------------------------------------------------------------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------- |
| **Pesan Error: "IP tersebut sudah ada dalam daftar blokir"**             | Anda mencoba memblokir alamat IP yang sebelumnya sudah terdaftar dalam tabel cek blokir.                           | Tinjau tabel daftar IP diblokir di sebelah kanan untuk memastikan IP tersebut telah aktif dalam daftar hitam.                          |
| **Gagal membuat Backup Database**                                        | Memori server penuh atau kapasitas kuota penyimpanan hosting web Anda telah mencapai batas maksimal (_Disk Full_). | Hapus beberapa file backup lama yang sudah tidak terpakai dari tabel Daftar File Backup, kemudian coba jalankan kembali.               |
| **Form Ubah Password menampilkan error "Password lama salah"**           | Kata sandi lama yang Anda masukkan pada form ganti kata sandi tidak cocok dengan yang terdaftar di database.       | Pastikan tombol Caps Lock Anda mati, lalu masukkan kembali kata sandi lama Anda dengan benar (sensitif terhadap huruf besar-kecil).    |
| **Jendela popup modal (seperti Tambah User) tidak merespon saat diklik** | Terdapat kendala muat file script visual atau tumpang tindih visual element (layout modal freeze).                 | Segarkan (_refresh_) halaman browser Anda dengan menekan tombol `F5` atau `Ctrl + R` untuk merestart event listener visual JavaScript. |

---

## 14. Tips Penggunaan Sistem

1. **Gunakan Kolom Search Pada DataTable:** Saat mengelola pengguna administratif yang banyak, manfaatkan kolom pencarian di pojok kanan atas tabel untuk menyaring nama secara instan tanpa perlu membalik halaman satu per satu.
2. **Manfaatkan Shortcut Dashboard:** Gunakan panel _Shortcut Tindakan Cepat_ di beranda untuk memotong alur klik navigasi menu saat Anda baru saja masuk ke sistem.
3. **Cek Log Secara Berkala:** Sempatkan waktu setidaknya seminggu sekali untuk memeriksa riwayat log di menu _Audit Log_ guna memastikan tidak ada aktivitas mencurigakan dari akun pengguna lainnya.
4. **Ganti Password Berkala:** Lindungi wewenang mutlak Anda sebagai Super Admin dengan memperbarui kata sandi pribadi Anda secara berkala melalui menu _Ubah Password_ di sidebar.

---

_Panduan Penggunaan Sistem ini disusun secara komprehensif berdasarkan antarmuka visual aktual platform GARA._
_Semoga mempermudah pekerjaan administratif Anda!_
