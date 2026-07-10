## \# Product Requirement Document (PRD)

## Multi-Tenant School Management: Activation, Registry & Auto Update System

---

## 1\. Judul Fitur

Flutter School Activation, Registry & Auto-Update System (Non-Play Store Distribution)

---

## 2\. Tujuan Fitur

1. **Multi-Tenancy Mandiri:** Membangun sistem aktivasi sekolah pada aplikasi Flutter menggunakan **School Key** sehingga satu *source code* aplikasi GARA dapat melayani banyak sekolah dengan konfigurasi basis URL server Laravel yang berbeda-beda.  
2. **Auto-Update Latar Belakang:** Menyediakan mekanisme pengecekan versi dan unduhan APK versi terbaru secara otomatis langsung dari **GitHub API** ke dalam aplikasi tanpa melempar pengguna ke browser luar dan tanpa mendistribusikannya melalui Google Play Store.  
3. **Sentralisasi Kontrol Gratis:** Memanfaatkan ekosistem Google Firebase (*Free Tier*) sebagai pusat registrasi sekolah dan kontrol versi aplikasi yang 100% bebas biaya operasional bagi sekolah.

---

## 3\. Deskripsi Singkat

Saat aplikasi pertama kali dipasang, pengguna wajib mengaktifkan aplikasi menggunakan **School Key**. Aplikasi memvalidasi kunci tersebut langsung ke Firebase Firestore menggunakan *Document ID* untuk efisiensi kuota. Jika valid, konfigurasi sekolah (seperti nama sekolah dan basis URL) disimpan secara permanen di penyimpanan lokal (`SharedPreferences`).

Pada setiap siklus hidup aplikasi dijalankan (*Splash Screen*), Flutter akan melakukan deteksi internet, sinkronisasi status sekolah (*Active/Maintenance/Suspend*), serta pengecekan *Build Number* aplikasi melalui Firebase Remote Config. Jika ditemukan pembaruan (terutama *Force Update*), aplikasi akan menembak **API GitHub Releases** untuk mengunduh APK terbaru secara diam-diam di latar belakang (*background stream*) melalui library `Dio` dan langsung mengeksekusi pemasangan otomatis via `OpenFilex`.

---

## 4\. Pengguna / Stakeholder

* **Primary User:** Pengguna internal ekosistem sekolah (Siswa, Guru, Wali Murid).  
* **Secondary User:** Developer GARA / Administrator Sistem (Mengendalikan konfigurasi global via Firebase Console & unggahan via GitHub CLI).

---

## 5\. Bahasa Pemrograman, Framework & Library

* **Framework:** Flutter (Android Platform Target)  
* **State Management:** Provider / Riverpod / BLoC *(Menyesuaikan arsitektur proyek saat ini)*  
* **Local Storage:** `shared_preferences` (Untuk menyimpan sesi aktivasi sekolah)  
* **Backend Cloud Service:** `firebase_core`, `cloud_firestore`, `firebase_remote_config`  
* **HTTP Client:** `dio` (Untuk melakukan request API GitHub dan *byte stream downloading*)  
* **File Opener & Installer:** `open_filex` (Menggantikan library lama `install_plugin` demi kompatibilitas Android 13+)  
* **Utility:**  
  * `package_info_plus` (Membaca informasi *version name* dan *build number* lokal perangkat)  
  * `path_provider` (Menemukan direktori penyimpanan aman untuk file APK sementara)

---

## 6\. User Flow & System Logic

## A. First Install (Aktivasi Sekolah)

Install APK ➔ Splash Screen ➔ Cek SharedPreferences (Belum Ada Data)  
                                            │  
                                            ▼  
                                 Halaman Aktivasi Sekolah  
                                            │  
                                            ▼  
                                  Input School Key oleh User  
                                            │  
                                            ▼  
                             Validasi Menggunakan Document ID Firestore  
                                            │  
                    ┌───────────────────────┴───────────────────────┐  
                    ▼ (Valid)                                       ▼ (Tidak Valid)  
       Simpan Konfigurasi Lokal                               Tampilkan Pesan Error  
  (school\_key, school\_name, base\_url)                             (Input Ulang)  
                    │  
                    ▼  
          Masuk Halaman Login

## B. Siklus Hidup Pembukaan Aplikasi (Splash Screen Logic)

*Setiap kali aplikasi dibuka, alur pengecekan wajib mengikuti urutan ketat berikut untuk menangani kondisi offline:*

\[Aplikasi Dibuka\] ➔ Splash Screen ➔ Ambil Data SharedPreferences (Sudah Ada)  
                                                 │  
                                                 ▼  
                                      Cek Koneksi Internet  
                                                 │  
                        ┌────────────────────────┴────────────────────────┐  
                        ▼ (Ada Internet)                                  ▼ (OFFLINE)  
         1\. Sinkronisasi Firestore (Cek Status)                  Bypass langsung ke Login  
         2\. Sinkronisasi Remote Config (Cek Update)              (Gunakan Base URL Lokal)  
                        │  
                        ▼  
       \[Evaluasi Status dari Firebase\]  
         ├─► Status: Maintenance ➔ Pindah ke Halaman Maintenance  
         ├─► Status: Suspend     ➔ Pindah ke Halaman Suspend  
         └─► Status: Active      ➔ Lanjut ke Evaluasi Auto-Update (Flow G)

## C. Ganti Sekolah

Menu Pengaturan Aplikasi ➔ Klik "Ganti Sekolah" ➔ Muncul Pop-up Konfirmasi  
                                                             │  
                                                             ▼  
                                                Hapus Data SharedPreferences  
                                                             │  
                                                             ▼  
                                                Restart ke Halaman Aktivasi

## D. Mekanisme Detil Auto-Update & Siklus Pembersihan Installer

\[Evaluasi Auto-Update\] ➔ Bandingkan Build Number Lokal vs Firebase Remote Config  
                                                 │  
                       ┌─────────────────────────┴─────────────────────────┐  
                       ▼ (Build Number SAMA)                               ▼ (Build Number LOKAL \< FIREBASE)  
                Lanjut ke Halaman Login                       Cek Variabel \`force\_update\` dari Firebase  
                                                                           │  
                                     ┌─────────────────────────────────────┴─────────────────────────────────────┐  
                                     ▼ (force\_update \= true)                                                     ▼ (force\_update \= false)  
                        Kunci Layar (Halaman Wajib Update)                                           Tampilkan Dialog Opsional Update  
                        \- Tanpa tombol close/nanti                                                   \- Ada opsi "Update" & "Nanti"  
                                     │                                                                           │  
                                     └─────────────────────────────────────┬─────────────────────────────────────┘  
                                                                           ▼  
                                                          \[User Klik / Terpicu Update\]  
                                                                           │  
                                                                           ▼  
                                                         Panggil GitHub API Release Latest  
                                                                           │  
                                                                           ▼  
                                                       Download APK via Latar Belakang (Dio)  
                                                                           │  
                                                                           ▼  
                                                       Download Selesai 100% ➔ Picu OpenFilex  
                                                                           │  
                                                                           ▼  
                                                    Sistem Android Memulai Proses Pemasangan  
                                            (Aplikasi Lama Dimatikan Paksa oleh OS Android / Killed)  
                                                                           │  
                                                                           ▼  
                                                     \[Aplikasi Versi Baru Dibuka Pertama Kali\]  
                                                                           │  
                                                                           ▼  
                                                    Splash Screen Mendeteksi File APK Sisa Download  
                                                                           │  
                                                                           ▼  
                                                       Hapus File APK Lama Secara Permanen  
                                                     (Siklus Pembersihan Penyimpanan Selesai)

---

## 7\. Technical Implementation Details (Mekanisme GitHub Auto-Download)

Untuk menghindari keharusan admin memasukkan URL unduhan baru secara manual setiap kali merilis update, sistem Flutter memanfaatkan arsitektur API Publik GitHub.

## A. Alur Pemanggilan API dan Ekstraksi Data

Aplikasi Flutter akan menembak endpoint HTTP GET berikut:

GET https://github.com{github\_username}/{repository\_name}/releases/latest

* **Input Variabel:** `github_username` dan `repository_name` dibaca secara dinamis dari Firebase Remote Config (`repo_path`).

## B. Payload Response JSON & Mapping Logic

GitHub API akan mengembalikan payload teks mentah. Kode Flutter diwajibkan melakukan parsing pada properti JSON spesifik berikut untuk melakukan *silent downloading*:

1. **Validasi Versi:** Mengambil nilai dari *key* `tag_name` untuk dicatat sebagai informasi kosmetik versi di aplikasi.  
2. **Pencarian Direct Link:** Melakukan perulangan (*looping*) di dalam array `assets` untuk mencari objek file yang memiliki akhiran nama `.apk`.  
3. **Ekstraksi URL:** Mengambil string dari *key* `browser_download_url` yang berada di dalam objek aset APK tersebut. Link inilah yang bertindak sebagai *Direct Download URL*.

*Visualisasi Struktur JSON GitHub API:*

{  
  "tag\_name": "v2.1.0",  
  "assets": \[  
    {  
      "name": "app-release.apk",  
      "browser\_download\_url": "https://github.com"  
    }  
  \]  
}

## C. Logika Pengunduhan dan Manajemen File Lokal

1. Menggunakan library `path_provider` untuk mengambil direktori penyimpanan eksternal aman aplikasi: `getExternalStorageDirectory()`.  
2. Menentukan nama file tujuan absolut secara statis agar mempermudah proses pembersihan, contoh: `"${tempDir.path}/update_lms_sekolah.apk"`.  
3. Menginisialisasi `Dio().download()` dengan menyertakan fungsi callback `onReceiveProgress` untuk dikirimkan ke *State Management* (Provider/Riverpod) guna menampilkan indikator persentase kemajuan unduhan (*linear progress bar*) pada komponen UI Halaman Wajib Update.

---

## 8\. Spesifikasi Struktur Data & Database (Firebase)

## A. Firebase Firestore (`Collection: schools`)

*Optimasi Performa: Kolom Kunci `school_key` wajib didaftarkan sebagai **Document ID** (Bukan sebagai field teks biasa di dalam dokumen) untuk memangkas konsumsi Read Query.*

/schools (Collection)  
   └── GRA82LPA (Document ID / Berfungsi sebagai School Key)  
          ├── school\_name : "SMP IT Darul Hidayah"  
          ├── url         : "https://gara.id"  
          └── status      : "active"

## B. Firebase Remote Config (`Global Variables`)

*Variabel kendali yang digunakan untuk membandingkan status rilis global dengan kondisi lokal pada gawai siswa.*

| Parameter Key | Tipe Data | Contoh Nilai | Deskripsi |
| :---- | :---- | :---- | :---- |
| `build_number` | Integer | `21` | Angka acuan utama pembanding versi aktif |
| `latest_version` | String | `"2.1.0"` | Tampilan label teks versi terbaru |
| `repo_path` | String | `"ibnusna/gara"` | Jalur repositori target untuk konsumsi API GitHub |
| `force_update` | Boolean | `true` | Penentu jenis update (Wajib / Opsional) |
| `release_notes` | String | `"Perbaikan bug modul ujian"` | Informasi ringkas mengenai hal baru di rilis ini |

---

## 9\. Aturan Keamanan & Batasan Sistem (Constraints)

1. **Izin Sistem Operasi:** Berkas `AndroidManifest.xml` wajib mendeklarasikan `<uses-permission android:name="android:permission.REQUEST_INSTALL_PACKAGES" />`. Tanpa izin ini, fungsi eksekusi file installer dari `OpenFilex` akan diblokir oleh arsitektur keamanan Android.  
2. **Unknown Sources:** Pengguna (Siswa) akan menghadapi dialog pop-up proteksi sistem Android untuk memberikan izin pemasangan aplikasi di luar Play Store saat pertama kali proses update berjalan. Aplikasi harus memberikan panduan visual sederhana di dalam modal jika proses instalasi diblokir oleh sistem HP siswa.  
3. **Idempotensi Pembersihan Berkas:** Logika penghapusan file `.apk` sisa installer pada fungsi *Splash Screen* tidak boleh memicu kegagalan sistem (*crash*) jika file target ternyata tidak ditemukan (Gunakan pengecekan `if (await file.exists()) { await file.delete(); }`).

---

## 10\. Output yang Diharapkan

* Sistem Multi-Tenant berjalan mulus; aplikasi secara dinamis merutekan seluruh fungsi HTTP request (Login, Tugas, Ujian) ke server Laravel sekolah yang bersangkutan tanpa ada kebocoran data antar-sekolah.  
* Proses pemeliharaan aplikasi terpusat; developer cukup melakukan push dari terminal via GitHub CLI, dan mengubah parameter sakelar di Firebase Remote Config untuk memperbarui ratusan aplikasi sekolah secara serentak.  
* Penyimpanan internal gawai siswa tetap optimal dan terjaga kebersihannya dari tumpukan berkas sampah APK berukuran besar karena adanya sistem otomasi *auto-clean* pasca-update.

---

*Dokumen ini merupakan spesifikasi final dan siap diturunkan ke dalam implementasi baris kode program.*

---

Apakah struktur alur teknis dan metode **ekstraksi dari API GitHub** pada PRD terbaru ini sudah dirasa sangat jelas dan sesuai dengan rancangan sistem yang Anda inginkan?

