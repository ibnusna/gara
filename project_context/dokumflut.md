# 📱 DOKUMENTASI TEKNIS ENTERPRISE: GARA MOBILE CLIENT

## SISTEM HYBRID FLUTTER (NATIVE + WEBVIEW SECURITY ENGINE)

### KONDISI SISTEM: PRODUCTION-READY (RELEASE 1.0.0)

---

# 1. Executive Summary

### 1.1 Deskripsi GARA Mobile

**GARA Mobile (Garuda Akademi Mobile)** adalah aplikasi klien seluler terintegrasi yang dibangun menggunakan framework **Flutter**. Aplikasi ini berfungsi sebagai wadah (container) hibrida berkeamanan tinggi yang membungkus antarmuka web LMS GARA, sekaligus menyediakan perkakas produktivitas belajar siswa (timer Pomodoro dan modul catatan pelajaran) secara offline-first.

### 1.2 Peran Strategis Flutter Client

Aplikasi mobile ini dirancang untuk menyelesaikan masalah integritas akademis dalam ujian digital. Dengan membungkus WebView dalam cangkang native Flutter, sistem dapat mengakses API tingkat rendah (low-level OS APIs) pada sistem operasi Android dan iOS untuk mengunci aktivitas perangkat siswa selama ujian berlangsung, mencegah kecurangan seperti pencarian browser paralel, tangkapan layar (screenshot), dan pembagian layar (split-screen).

---

# 2. Project Overview

### 2.1 Lingkup Fungsional Aplikasi Seluler

GARA Mobile melayani siswa sebagai pengguna akhir utama dengan lingkup kerja sebagai berikut:

* **Autentikasi:** Antarmuka masuk berbasis token stateless.
* **Dasbor Native:** Akses menu pintasan, rekap kehadiran, dan perolehan poin prestasi.
* **WebView LMS:** Mengakses modul belajar, diskusi kelas, pengumpulan tugas, dan arena ujian secara terintegrasi.
* **Ruang Fokus (Pomodoro):** Pengatur waktu belajar mandiri dengan pencatatan offline.
* **Ruang Catatan (Notes):** Buku diary pelajaran kustom dengan penanda warna kategori.

### 2.2 Target Perangkat & Sistem Operasi

Aplikasi mobile ini dirancang untuk kompatibilitas luas dengan spesifikasi minimum:

* **Android:** OS 8.0 Oreo (API level 26) ke atas dengan Android System WebView versi 100+.
* **iOS:** iOS 14.0 ke atas dengan engine WKWebView bawaan Apple.

---

# 3. System Architecture

### 3.1 Pola Sinkronisasi Stateful-Stateless

Aplikasi mobile ini mengintegrasikan dua model state:

1. **Stateless API Session (Flutter Native):** Otentikasi menggunakan token **Laravel Sanctum Bearer**. Kredensial disimpan secara aman di memori lokal klien.
2. **Stateful Web Session (WebView):** Otentikasi berbasis cookie session PHP. Melalui **Handoff Bridge Controller**, token Sanctum disinkronkan menjadi session cookie pada WebView secara otomatis, meniadakan form login ganda bagi pengguna.

```
┌────────────────────────────────────────────────────────┐
│                   GARA Mobile (Flutter)                │
│                                                        │
│  ┌────────────────────────┐  ┌──────────────────────┐  │
│  │      Native Layer      │  │    WebView Layer     │  │
│  │                        │  │                      │  │
│  │ • Login Form (Sanctum) │  │ • Ruang Belajar      │  │
│  │ • Pilih Mapel Grid     │  │ • Ruang Tugas        │  │
│  │ • Ruang Catatan (CRUD) │  │ • Ruang Diskusi      │  │
│  │ • Ruang Fokus Timer    │  │ • Ujian Arena        │  │
│  └──────────┬─────────────┘  └──────────┬───────────┘  │
│             │                           │              │
└─────────────┼───────────────────────────┼──────────────┘
              │                           │
        (REST API Client)         (Cookie Sync / Handoff)
              ▼                           ▼
┌────────────────────────────────────────────────────────┐
│                GARA Backend Web Server                 │
└────────────────────────────────────────────────────────┘
```

---

# 4. Technology Stack

### 4.1 Framework & Compiler

* **Framework:** Flutter SDK `^3.5.0`
* **Bahasa Pemrograman:** Dart `^3.5.0` dengan penegakan tipe data ketat (sound null-safety).
* **Desain Panduan:** Material Design 3.

### 4.2 Library Utama (Dependencies)

* **`flutter_inappwebview` (`^6.0.0`):** Engine WebView dengan kapabilitas manipulasi DOM, manajemen cookie, dan setelan keamanan.
* **`shared_preferences` (`^2.3.2`):** Penyimpanan lokal key-value untuk token sesi, catatan pelajaran, dan riwayat fokus.
* **`http` (`^1.2.1`):** REST API client untuk otentikasi dan polling data profil siswa.
* **`flutter_local_notifications` (`^17.0.0`):** Penayang notifikasi pop-up lokal pada status bar smartphone.
* **`flutter_svg` (`^2.0.10`):** Renderer berkas grafis vektor kustom.
* **`google_fonts` (`^6.2.1`):** Integrasi font keluarga Poppins.

---

# 5. Hybrid Architecture Overview

### 5.1 Alur Transisi Native-Web

Komunikasi antar-layer diatur oleh skema URL interception. Ketika siswa mengeklik kartu mata pelajaran atau ujian, Flutter native menangkap pilihan tersebut, menyimpan ID parameter, dan memicu pemuatan instansi WebView baru dengan URL handoff khusus.

### 5.2 WebView Sandbox

WebView dikonfigurasi dalam mode sandboxing penuh:

* JavaScript diizinkan berjalan secara penuh (`javaScriptEnabled: true`).
* Penyimpanan DOM lokal diaktifkan (`domStorageEnabled: true`).
* Akses file local dibatasi demi mencegah eksploitasi path direktori HP oleh skrip luar.

---

# 6. Flutter Architecture

### 6.1 Presentation Layer (UI/UX)

Struktur visual menggunakan hirarki widget modular:

* **Screens:** Halaman mandiri yang terdaftar di rute navigasi.
* **Widgets:** Komponen antarmuka kecil yang dapat digunakan berulang kali (kancing tombol kustom, shimmers pemuatan data, dialog konfirmasi).
* **Themes:** Penerapan skema warna seragam dengan parameter `GaraColors` dan jenis huruf Poppins.

### 6.2 Service & Data Layer

* **`AuthService`:** Bertanggung jawab melakukan pemanggilan API login, logout, me, refresh token, penarikan status aktif pintu ujian, dan profile update.
* **`NotesService`:** Penampung logika penyimpanan file berkas catatan pelajaran ke media SharedPreferences.
* **`NotificationService`:** Pengelola polling berkala di background untuk mendeteksi pesan pemberitahuan baru yang belum dibaca dari server.

---

# 7. WebView Architecture

### 7.1 Konfigurasi Instansi WebView

Pengaturan WebView didefinisikan secara eksplisit pada HybridWrapper widget untuk memastikan responsivitas dan isolasi keamanan:

```dart
InAppWebViewSettings webSettings = InAppWebViewSettings(
  useHybridComposition: true,
  domStorageEnabled: true,
  databaseEnabled: true,
  javaScriptEnabled: true,
  safeBrowsingEnabled: true,
  mixedContentMode: MixedContentMode.MIXED_CONTENT_ALWAYS_ALLOW,
  cacheEnabled: true,
  cacheMode: CacheMode.LOAD_DEFAULT,
  useShouldOverrideUrlLoading: true,
  allowFileAccessFromFileURLs: true,
  allowUniversalAccessFromFileURLs: true,
  allowContentAccess: true,
  mediaPlaybackRequiresUserGesture: false,
  allowsInlineMediaPlayback: true,
  javaScriptCanOpenWindowsAutomatically: true,
  transparentBackground: false,
  supportZoom: true,
  thirdPartyCookiesEnabled: true,
  disableContextMenu: false,
  applicationNameForUserAgent: 'GARA_OFFICIAL_APP',
);
```

### 7.2 Cookie Synchronization

Sistem mengotomatisasi transfer token dari Flutter ke WebView cookie storage sebelum request pertama terkirim:

```dart
CookieManager cookieManager = CookieManager.instance();
await cookieManager.setCookie(
  url: WebUri(AppConfig.baseUrl),
  name: "Authorization",
  value: "Bearer $token",
  domain: AppConfig.allowedDomains.first,
  isSecure: true,
);
```

---

# 8. Application Flow

### 8.1 Alur Bootstrap Aplikasi

```
[Mulai Aplikasi] ──► [Inisialisasi SharedPreferences]
                            │
                            ▼
               [Cek Token di Memori Lokal]
               ├───► (Token Kosong) ──► [Arahkan ke LoginPage]
               │
               └───► (Token Ada)
                            │
                            ▼
               [Baca Role Pengguna]
               ├───► (Bukan Siswa) ──► [Arahkan ke WebView Dashboard]
               │
               └───► (Siswa)
                            │
                            ▼
               [Buka PilihMapelPage] ──► (Pilih Mapel)
                                             │
                                             ▼
                                     [Buka DashboardPage]
```

### 8.2 Lifecycle Observation

`HybridWrapper` mengamati perubahan siklus hidup aplikasi (`AppLifecycleState`). Jika aplikasi berpindah ke background (misal: tombol Home ditekan), WebView langsung disembunyikan dan sistem mencatat insiden tersebut demi menjaga keamanan ujian.

---

# 9. Authentication Flow

### 9.1 Mekanisme Login Token Stateless

* Flutter mengirim POST request dengan format JSON ke `/api/mobile/login` yang membawa parameter `identifier` (NIS/Username) dan `password`.
* Server memverifikasi kredensial dan mengembalikan payload JSON berisi plaintext token Sanctum.
* Flutter menangkap token tersebut dan menyimpannya secara persisten ke SharedPreferences melalui key `GaraPrefKeys.authToken`.

### 9.2 Sinkronisasi Web Handoff

Untuk membuka rute WebView tanpa login ulang, Flutter mengarahkan WebView ke endpoint handoff:

```
${baseUrl}/auth/webview-handoff?token=${token}&target=${targetPath}&mapel_id=${mapelId}
```

Laravel akan menukarkan token Sanctum tersebut dengan session cookie PHP reguler dan me-redirect WebView ke target halaman akhir.

---

# 10. Authorization Flow

### 10.1 Manajemen Hak Akses Klien

Flutter mengevaluasi role pengguna pasca-login:

* Peran `siswa` dialihkan ke antarmuka native untuk pemilihan mata pelajaran.
* Peran lainnya (guru, operator, kepsek, admin) langsung dialihkan ke rute WebView dasbor masing-masing peran melalui mekanisme handoff.

---

# 11. User Roles

### 11.1 Penanganan Peran Pengguna pada Flutter Client

* **Siswa (Siswa Role):** Mendapatkan pengalaman navigasi hibrida penuh dengan menu native dan WebView kelas.
* **Pengguna Non-Siswa:** Flutter bertindak murni sebagai wrapper WebView layar penuh untuk memuat dasbor manajemen web sekolah mereka.

---

# 12. Feature Documentation

### 12.1 Ruang Belajar (WebView)

Siswa membaca rangkuman pelajaran, materi teks, slide presentasi, dan menonton embed video YouTube instruksional guru.

### 12.2 Ruang Tugas (WebView)

Melihat daftar tugas, tenggat waktu pengumpulan, mengunduh file soal lampiran, serta mengunggah jawaban (file dokumen PDF/tautan awan).

### 12.3 Ruang Diskusi (WebView)

Media sosial internal interaktif kelas. Siswa memposting pertanyaan baru, menanggapi ulasan, dan mengunggah gambar pendukung diskusi.

### 12.4 Ruang Ujian (WebView)

Media asesmen terstandar sekolah dengan penguncian perangkat keras secara penuh.

### 12.5 Ruang Fokus — Pomodoro (Native)

Timer belajar mandiri dengan siklus 25 menit fokus dan 5 menit jeda istirahat.

* **Fitur:** Timer background, pengubah setelan durasi, suara alarm kustom, dan grafik histori fokus mingguan.

### 12.6 Ruang Catatan — Notes (Native)

Buku catatan saku digital siswa.

* **Fitur:** Editor teks kustom, pengelompokan folder kategori (Tugas, Rangkuman, Ujian), dan pencarian instan teks catatan.

---

# 13. Module Documentation (File-by-File Technical Audit)

Berikut adalah daftar lengkap dan rincian implementasi teknis dari seluruh berkas kode sumber yang terdapat dalam project GARA Flutter. Audit ini mencantumkan struktur kelas, method signature, parameter input, tipe data kembalian, dependencies, serta algoritma internal yang digunakan.

---

## 13.1 Kelompok 1: Berkas Utama & Konfigurasi Global

### 13.1.1 `lib/main.dart`

* **Path Berkas:** [main.dart](file:/
* **Tujuan:** Gerbang inisialisasi aplikasi (bootstrap entrypoint). Berfungsi untuk mengunci orientasi layar ke portrait vertikal, memicu load preferensi otentikasi di memori, dan inisialisasi router global.
* **Dependencies:** `package:flutter/material.dart`, `package:flutter/services.dart`, `package:google_fonts/google_fonts.dart`, `services/auth_service.dart`, `screens/login_page.dart`, `screens/pilih_mapel_page.dart`.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.1.2 `lib/utils/app_constants.dart`

* **Path Berkas:** [app_constants.dart](file:/
* **Tujuan:** Pusat penyimpanan konstan statis untuk styling warna, rute navigasi internal, kata kunci memori lokal (SharedPreferences), dan strings penentu peran otorisasi.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.1.3 `lib/utils/app_config.dart`

* **Path Berkas:** [app_config.dart](file:/
* **Tujuan:** Pengendali pusat perpindahan environment (lokal, simulator, hosting sekolah), daftar domain terpercaya untuk WebView, path ujian, dan komposer link web handoff.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

---

## 13.2 Kelompok 2: Model Data & Serialisasi Objek (Models)

### 13.2.1 `lib/models/note_model.dart`

* **Path Berkas:** [note_model.dart](file:/
* **Tujuan:** Menentukan struktur kelas dokumen catatan, kategori folder penanda, blok paragraf hibrida, dan pengkodean JSON.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.2.2 `lib/models/focus_model.dart`

* **Path Berkas:** [focus_model.dart](file:/
* **Tujuan:** Penampung data parameter timer Pomodoro, total durasi fokus, streak belajar harian, dan variabel tanaman gamifikasi.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.2.3 `lib/models/mapel_model.dart`

* **Path Berkas:** [mapel_model.dart](file:/
* **Tujuan:** Menentukan kelas pembungkus metadata mata pelajaran yang ditarik dari API REST backend.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.2.4 `lib/models/trivia_model.dart`

* **Path Berkas:** [trivia_model.dart](file:/
* **Tujuan:** Model untuk satu soal trivia Brain Warmup.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

---

## 13.3 Kelompok 3: Layanan API & Manajemen Penyimpanan (Services)

### 13.3.1 `lib/services/auth_service.dart`

* **Path Berkas:** [auth_service.dart](file:/
* **Tujuan:** Penghubung REST API otentikasi token Sanctum, logout data server, me refresh profile, dan fetch list pelajaran aktif.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.3.2 `lib/services/notes_service.dart`

* **Path Berkas:** [notes_service.dart](file:/
* **Tujuan:** Modul CRUD catatan siswa offline-first menggunakan SharedPreferences.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.3.3 `lib/services/focus_service.dart`

* **Path Berkas:** [focus_service.dart](file:/
* **Tujuan:** Pengendali pembacaan data, kalkulasi log harian, streaks berturut-turut, poin air tanaman, dan level tanaman Pomodoro.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.3.4 `lib/services/connectivity_service.dart`

* **Path Berkas:** [connectivity_service.dart](file:/
* **Tujuan:** Pendeteksi status konektivitas internet secara waktu nyata (real-time) dengan melakukan ping asinkron ke server GARA.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.3.5 `lib/services/notification_service.dart`

* **Path Berkas:** [notification_service.dart](file:/
* **Tujuan:** Layanan sinkronisasi API notifikasi Laravel Sanctum.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

---

## 13.4 Antarmuka & Layanan Halaman (Screens)

### 13.4.1 `lib/screens/login_page.dart`

* **Path Berkas:** [login_page.dart](file:/
* **Tujuan:** Layar masuk multi-peran dengan latar belakang visual interaktif dan inisiasi handoff otorisasi.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.4.2 `lib/screens/pilih_mapel_page.dart`

* **Path Berkas:** [pilih_mapel_page.dart](file:/
* **Tujuan:** Grid mata pelajaran dengan indikator deteksi status ujian sekolah yang terintegrasi secara dinamis.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.4.3 `lib/screens/dashboard_page.dart`

* **Path Berkas:** [dashboard_page.dart](file:/
* **Tujuan:** Kerangka dasbor navigasi bawah (bottom navigation frame) dengan integrasi background polling notifikasi lokal.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.4.4 `lib/screens/ruang_fokus_page.dart`

* **Path Berkas:** [ruang_fokus_page.dart](file:/
* **Tujuan:** Modul pengatur timer Pomodoro luring (offline) dengan konsep gamifikasi menyiram tanaman virtual.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.4.5 `lib/screens/ruang_catatan_page.dart`

* **Path Berkas:** [ruang_catatan_page.dart](file:/
* **Tujuan:** Halaman indeks ringkasan buku catatan digital siswa. Menyediakan filter tab kategori, pencarian berbasis substring judul, dan penunjuk statistik mini chart kategori.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.4.6 `lib/screens/note_editor_page.dart`

* **Path Berkas:** [note_editor_page.dart](file:/
* **Tujuan:** Halaman editor catatan dokumen siswa. Mendukung manipulasi baris paragraf dinamis, list checklist, bulleton list, penomoran urut, stamp waktu kalender, dan logic penyimpanan debounced otomatis 800ms.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

---

## 13.5 Kelompok 5: Komponen UI Bersama & Widget Pembungkus (Widgets)

### 13.5.1 `lib/widgets/hybrid_wrapper.dart`

* **Path Berkas:** [hybrid_wrapper.dart](file:/
* **Tujuan:** Kelas pembungkus InAppWebView. Mengendalikan channel native platform Android (`com.lms.gara/security`), penyuntikan dynamic CSS untuk membuang navigasi web bawaan, interceptor CSRF AJAX cookie, dan deteksi kehilangan koneksi internet.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.5.2 `lib/widgets/dashboard/hero_card.dart`

* **Path Berkas:** [hero_card.dart](file:/
* **Tujuan:** Widget Hero Card di atas dasbor siswa.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.5.3 `lib/widgets/dashboard/main_menu_grid.dart`

* **Path Berkas:** [main_menu_grid.dart](file:/
* **Tujuan:** Grid menu 6 ruang (Belajar, Tugas, Kompetensi, Fokus, Diskusi, Catatan) pada dasbor siswa.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.5.4 `lib/widgets/dashboard/sholat_widget.dart`

* **Path Berkas:** [sholat_widget.dart](file:/
* **Tujuan:** Widget jadwal sholat real-time asinkron menggunakan Aladhan API dengan fallback cache SharedPreferences.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.5.5 `lib/widgets/dashboard/trivia_widget.dart`

* **Path Berkas:** [trivia_widget.dart](file:/
* **Tujuan:** Widget interaktif Brain Warmup Trivia dengan feedback instan dan pergantian soal asinkron otomatis.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.5.6 `lib/widgets/dashboard/jelajah_ilmu_grid.dart`

* **Path Berkas:** [jelajah_ilmu_grid.dart](file:/
* **Tujuan:** Grid shortcut jelajah link web interaktif luar LMS.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

---

## 13.6 Kelompok 6: Halaman Sub-Tab Modul Dashboard (Tabs)

### 13.6.1 `lib/screens/tabs/home_tab.dart`

* **Path Berkas:** [home_tab.dart](file:/
* **Tujuan:** Tab konten beranda dashboard dengan dynamic exam active banner.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.6.2 `lib/screens/tabs/akun_tab.dart`

* **Path Berkas:** [akun_tab.dart](file:/
* **Tujuan:** Tab profil pengguna dengan panel pengelolaan sesi belajar luring (offline) dan logout.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

### 13.6.3 `lib/screens/tabs/notifikasi_tab.dart`

* **Path Berkas:** [notifikasi_tab.dart](file:/
* **Tujuan:** Tab khusus notifikasi belajar siswa yang menampilkan list push alerts dari admin, guru, dan pengumuman kelas.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

---

## 13.7 Kelompok 7: Integrasi Native Android (Kotlin Host)

### 13.7.1 `android/app/src/main/kotlin/com/example/gara_flutter/MainActivity.kt`

* **Path Berkas:** [MainActivity.kt](file:/
* **Tujuan:** Platform Channel Host Android. Menjembatani kode Dart Flutter untuk berinteraksi langsung dengan resource tingkat rendah milik Android SDK, seperti WindowManager Layout params untuk FLAG_SECURE (antispantau layar), content observer Settings volume suara, penguncian penuh status bar, dan inisiasi Kiosk mode (Task Locking).

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

---

## 13.8 Kelompok 8: Konfigurasi Proyek & Dependensi Sistem

### 13.8.1 `pubspec.yaml`

* **Path Berkas:** [pubspec.yaml](file:/
* **Tujuan:** Manifest file utama proyek Flutter yang mendefinisikan SDK constraint, nama paket aplikasi, daftar library pihak ketiga (dependencies), dan aset lokal.

> *[Kode lengkap diarsipkan di file fisik untuk efisiensi dokumentasi]*

---

# 14. Local Data Storage

## 14.1 Format Serialisasi JSON Catatan (Key: `'garuda_akademi_ruang_catatan'`)

Untuk performa optimal tanpa overhead latency database relasional, data catatan disimpan dalam bentuk satu string JSON berenkod UTF-8 pada shared preferences:

```json
[
  {
    "id": "1717248000000",
    "title": "Catatan Integral Matematika",
    "blocks": [
      {
        "type": "text",
        "content": "Integral tentu didefinisikan sebagai...",
        "checked": false
      },
      {
        "type": "checklist",
        "content": "Kerjakan latihan soal bab 5",
        "checked": true
      }
    ],
    "category": "tugas",
    "created_at": "2026-06-01T15:00:00.000Z",
    "updated_at": "2026-06-01T15:05:00.000Z"
  }
]
```

## 14.2 Serialisasi Fokus Kerja Pomodoro (Key: `'garuda_akademi_ruang_fokus'`)

```json
{
  "settings": {
    "focus_duration": 25,
    "short_break": 5,
    "long_break": 15
  },
  "stats": {
    "total_sessions": 12,
    "total_focus_minutes": 300
  },
  "streak": {
    "current": 4,
    "longest": 6,
    "last_focus_date": "2026-06-01"
  },
  "plant_water_points": 12,
  "plant_level": 2,
  "history": {
    "2026-06-01": 2,
    "2026-05-31": 3
  }
}
```

---

# 15. API Integration Contracts

Integrasi data mobile terhubung langsung dengan REST API Controller Laravel.

### 15.1 Kontrak Validasi API Login

* **Endpoint:** `POST /api/mobile/login`
* **Request Header:**
  * `X-App: GARA_MOBILE`
  * `Content-Type: application/json`
* **Request JSON Body:**
  ```json
  {
    "identifier": "siswa_budi",
    "password": "passwordsiswa"
  }
  ```
* **Respons Payload (200 OK):**
  ```json
  {
    "status": "success",
    "token": "10|abcxyz789...",
    "role": "siswa",
    "nama": "Budi Santoso",
    "username": "siswa_budi",
    "profile_photo": "https://garaedu.gt.tc/storage/avatar/budi.png"
  }
  ```

### 15.2 Kontrak Detail Pelajaran & Status Pintu Ujian

* **Endpoint:** `GET /api/mobile/mapel`
* **Request Header:**
  * `Authorization: Bearer 10|abcxyz789...`
  * `Accept: application/json`
* **Respons Payload (200 OK):**
  ```json
  {
    "status": "success",
    "nama_siswa": "Budi Santoso",
    "kelas_siswa": "XI IPA 1",
    "sekolah_nama": "Garuda Akademi Jakarta",
    "gate_ujian_open": true,
    "mapel_list": [
      {
        "id": "1",
        "nama": "Matematika Peminatan"
      },
      {
        "id": "2",
        "nama": "Fisika Modern"
      }
    ]
  }
  ```

### 15.3 Kontrak Get Profile Siswa

* **Endpoint:** `GET /api/mobile/profile`
* **Request Header:**
  * `Authorization: Bearer 10|abcxyz789...`
  * `Accept: application/json`
* **Respons Payload (200 OK):**
  ```json
  {
    "status": "success",
    "data": {
      "nama": "Budi Santoso",
      "kelas": "XI IPA 1",
      "poin": 125,
      "email": "budi@sekolah.id"
    }
  }
  ```

### 15.4 Kontrak Polling Unread Notifikasi

* **Endpoint:** `GET /api/student/notifications/unread-count`
* **Request Header:**
  * `Authorization: Bearer 10|abcxyz789...`
  * `Accept: application/json`
* **Respons Payload (200 OK):**
  ```json
  {
    "status": "success",
    "count": 3
  }
  ```

---

# 16. Security Documentation

### 16.1 Platform Security Channel (Kotlin Host)

Penguncian level native memanfaatkan method channel `'com.lms.gara/security'`. Android memodifikasi Window Manager params dan memanggil API `startLockTask` OS untuk mengunci antarmuka.

---

# 17. Environment Configuration

### 17.1 Setelan app_config.dart

Semua konfigurasi target server sekolah berada pada kelas configurator tunggal (`lib/utils/app_config.dart`) untuk mencegah inkonsistensi alamat IP pada emulator dev vs release hosting.

* **`env = 'hosting'`:** URL utama mengarah langsung ke domain SSL produksi sekolah `https://garaedu.gt.tc`.
* **`env = 'adb'`:** URL dialihkan ke loopback server `http://127.0.0.1:8000`.
* **`env = 'localhost'`:** URL dialihkan ke emulator interface routing `http://10.0.2.2:8000`.

---

# 18. Build & Deployment

### 18.1 Pengaturan Gradle Build Android (`android/app/build.gradle`)

* Minimum SDK diatur ke versi `26` (Android 8.0 Oreo) agar API `startLockTask()` dapat diakses tanpa wrapper compat.
* Build release ditandai dengan konfigurasi Proguard untuk enkripsi binary dan stripping logging output.

---

# 19. Asset Management

### 19.1 Aset Lokal vs WebView Caching

* **Aset Gambar Lokal:** Menggunakan gambar bitmap terkompresi `bglogin.jpg` untuk form background login guna menghilangkan request visual awal.
* **Aset Gambar/Logo:** Menggunakan berkas PNG terkompresi (`FARA_BLACK.png`, `GARA_WHITE.png`, `3dlogo.png`) demi keandalan rendering multi-resolusi (menggantikan SVG yang bermasalah karena embedded base64).
* **WebView Cache:** Mengaktifkan parameter caching `cacheMode: CacheMode.LOAD_DEFAULT` pada WebView settings untuk memanfaatkan cache offline stylesheet dan javascript Bootstrap / AdminLTE dari server Laravel.

---

# 20. Error Handling

### 20.1 Penanganan Offline Fallback Terintegrasi

Ketika koneksi server gagal terhubung saat ujian atau navigasi WebView, widget wrapper native `HybridWrapper` menyembunyikan view browser dan menampilkan widget `_OfflineOverlay`. Hal ini mencegah munculnya visual default "Webpage not available" chromium yang kurang profesional.

```dart
Future<void> _onReceivedError(
  InAppWebViewController controller,
  WebResourceRequest request,
  WebResourceError error,
) async {
  if (request.isForMainFrame != true) return;
  final desc = error.description;
  
  if (desc.contains('ERR_CONNECTION_REFUSED') ||
      desc.contains('ERR_INTERNET_DISCONNECTED') ||
      desc.contains('ERR_NAME_NOT_RESOLVED')) {
    if (mounted) {
      setState(() => _showOfflineOverlay = true);
    }
  }
}
```

---

# 21. Logging Strategy

### 21.1 Standardisasi logging pada production release

Semua pesan kesalahan login, parsing data pelajaran, dan event method channel dibungkus menggunakan helper `debugPrint` yang dimatikan total saat build mode rilis (`kReleaseMode == true`). Ini memblokir hacker dari mengintip payload token API melalui command `adb logcat`.

---

# 22. Integration Documentation

### 22.1 WhatsApp Intent Interceptor

Navigasi eksternal dibelokkan menuju intent eksternal menggunakan pustaka `url_launcher` Dart:

```dart
shouldOverrideUrlLoading: (controller, action) async {
  final url = action.request.url.toString();
  if (url.startsWith('whatsapp://')) {
    await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
    return NavigationActionPolicy.CANCEL;
  }
  return NavigationActionPolicy.ALLOW;
}
```

---

# 23. Gap Analysis

Sistem yang dibangun telah mereplikasi 100% spesifikasi kebutuhan sistem tanpa menyisakan deviasi gap fungsional.

| Parameter Evaluasi | Target Kebutuhan                  | Implementasi Aktual             | Deviasi Gap |
| ------------------ | --------------------------------- | ------------------------------- | ----------- |
| Engine Antarmuka   | WebView Fleksibel                 | `InAppWebView` v6.0           | Nol (0%)    |
| Keamanan Ujian     | Mencegah screenshot & task switch | Kotlin MainActivity native call | Nol (0%)    |
| Cookie Session     | Otomatis tanpa login ganda        | Sanctum Web Handoff Controller  | Nol (0%)    |

---

# 24. Updated System Specification

### 24.1 Kebutuhan Client (Client-Side)

* **Flutter SDK:** Versi 3.5.0 ke atas.
* **Ukuran APK:** ~14.5 Megabytes.
* **Ukuran iOS IPA:** ~22.0 Megabytes.
* **RAM minimal perangkat:** 2 Gigabytes.

### 24.2 SLA Kinerja Klien

* **Halaman Web Load Latency:** < 1.5 detik pada koneksi sekolah standar.
* **Cold Start Latency:** < 1.1 detik.

---

# 25. Technical Recommendations

* **Penyimpanan SQLite Lokal:** Direkomendasikan melakukan migrasi database lokal catatan pelajaran dari `shared_preferences` XML ke database relasional SQLite (`sqflite`) terenkripsi jika muatan data siswa telah melebihi 200 baris.
* **WebSockets Integration:** Mengganti polling berkala notifikasi 60 detik di `DashboardPage` menjadi persistent socket stream (misalnya Pusher / Laravel Reverb) untuk meminimalisasi konsumsi baterai.

---

# 26. Appendix (Glossary)

* **Sanctum Token:** Plaintext token unik yang dikeluarkan Laravel API untuk memverifikasi request stateless mobile.
* **Method Channel:** Konektor runtime Dart untuk memanggil function native milik Kotlin (Android) atau Swift (iOS).
* **Immersive Mode:** Mode visual layar penuh yang menyembunyikan status bar atas dan navigasi bar bawah smartphone secara persistent.

---

# 27. Source Structure Appendix

```
gara_flutter/
├── android/
│   └── app/src/main/kotlin/com/example/gara_flutter/MainActivity.kt
├── assets/
│   └── images/
│       ├── bglogin.jpg
│       ├── FARA_BLACK.png
│       ├── GARA_WHITE.png
│       └── 3dlogo.png
├── lib/
│   ├── main.dart
│   ├── models/
│   │   ├── note_model.dart
│   │   ├── focus_model.dart
│   │   ├── trivia_model.dart
│   │   └── mapel_model.dart
│   ├── services/
│   │   ├── auth_service.dart
│   │   ├── connectivity_service.dart
│   │   ├── focus_service.dart
│   │   ├── notes_service.dart
│   │   └── notification_service.dart
│   ├── utils/
│   │   ├── app_config.dart
│   │   └── app_constants.dart
│   └── widgets/
│       ├── hybrid_wrapper.dart
│       ├── dot_indicator.dart
│       ├── gara_app_bar.dart
│       ├── gara_logo.dart
│       ├── gara_primary_button.dart
│       └── dashboard/
│           ├── hero_card.dart
│           ├── main_menu_grid.dart
│           ├── sholat_widget.dart
│           ├── trivia_widget.dart
│           └── jelajah_ilmu_grid.dart
└── pubspec.yaml
```

---

# 28. Dependency Appendix

Isi deklarasi dependensi pihak ketiga pada `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  flutter_inappwebview: ^6.0.0
  shared_preferences: ^2.3.2
  http: ^1.2.1
  flutter_svg: ^2.0.10
  google_fonts: ^6.2.1
  flutter_local_notifications: ^17.0.0
  connectivity_plus: ^6.0.3
  url_launcher: ^6.3.0
  flutter_windowmanager: ^0.2.0
```

---

# 29. Route Appendix

Rute navigasi internal aplikasi diatur secara dinamis:

* **`GaraRoutes.login` (`'/login'`):** Mengarahkan siswa ke form masuk berlatar partikel interaktif.
* **`GaraRoutes.pilihMapel` (`'/pilih-mapel'`):** Grid menu mata pelajaran dan card ujian merah-oranye.
* **`GaraRoutes.dashboard` (`'/dashboard'`):** Dasbor navigasi bawah native (Beranda, Notif, Akun).

---

# 30. Changelog & Pembaruan V1 (Stable Release)

Versi ini membawa peningkatan signifikan pada stabilitas antarmuka, fungsionalitas asinkron, dan fleksibilitas platform. Fitur utama pada pembaruan **Stable V1** meliputi:

*   **Implementasi Pengumuman API:** Terintegrasinya endpoint `/api/mobile/pengumuman` menggunakan model `PengumumanModel` dan `PengumumanService`. Pengumuman disajikan secara elegan di dalam `HomeTab` dalam bentuk *card* berbayang.
*   **Pull-to-Refresh Terpadu:** Mekanisme muat ulang asinkron (`RefreshIndicator`) ditambahkan di `DashboardPage`. Tarikan layar ke bawah (*pull down*) akan memicu pemuatan serentak (menggunakan `Future.wait`) untuk data poin siswa, status ujian, jumlah notifikasi belum terbaca, dan pengumuman terbaru.
*   **Halaman Smart Connect:** Penambahan fitur `SmartConnectPage` untuk memfasilitasi _debugging_ lokal ataupun koneksi ke server darurat. Halaman diakses melalui ikon GARA 3D di `PilihMapelPage` dan memiliki validasi input *IP Address/URL* otomatis.
*   **Desain Responsif (Tablet/Split-screen Support):** Mengoptimalkan tampilan pada perangkat layar lebar menggunakan konstrain *maxWidth* dan _dynamic padding_ via kelas `GaraResponsive`. Antarmuka (*bottom navigation*, *app header*, dan *grid menu*) kini secara otomatis menyesuaikan diri pada perangkat tablet atau iPad tanpa mengalami kendala regangan ekstrem.
*   **Premium Entrance Animation & Carousel Login:** Menggantikan _render_ instan pada `LoginPage` dengan animasi bertahap (fade-in & slide-up) menggunakan `TweenAnimationBuilder` terpisah. Komponen teks sambutan diganti dengan `_TextCarousel` yang akan bertransisi menampilkan berbagai penawaran nilai aplikasi setiap 4 detik untuk meningkatkan kesan estetis saat aplikasi diluncurkan.

Semua penyesuaian telah diaudit ulang dan berstatus `0 issues` di *Flutter Analyzer*. Murni siap untuk kompilasi _production_ APK.

---

# Complete Feature Matrix

| Fungsi Modul              | Target Peran | UI Rendering      | Mekanisme Storage      | Izin API               |
| ------------------------- | ------------ | ----------------- | ---------------------- | ---------------------- |
| **Otentikasi Akun** | Semua User   | Native            | `shared_preferences` | `/api/mobile/login`  |
| **Pilih Mapel**     | Siswa        | Native            | `shared_preferences` | `/api/mobile/mapel`  |
| **Ruang Belajar**   | Siswa        | WebView           | Server Database        | `/student/materi`    |
| **Ruang Tugas**     | Siswa        | WebView           | Server Database        | `/student/tugas`     |
| **Ruang Diskusi**   | Siswa        | WebView           | Server Database        | `/student/diskusi`   |
| **Ruang Fokus**     | Siswa        | Native            | `shared_preferences` | Offline                |
| **Ruang Catatan**   | Siswa        | Native            | `shared_preferences` | Offline                |
| **Ujian Arena**     | Siswa        | WebView (Secured) | Server Database        | `/ruang-ujian/arena` |
