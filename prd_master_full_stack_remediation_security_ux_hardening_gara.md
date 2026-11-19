# MASTER PRODUCT REQUIREMENTS DOCUMENT (PRD)

## GARA Full-Stack Critical Remediation, Security Hardening, In-App Flows & UI/UX Polish

| Atribut Dokumen | Spesifikasi | 
| ----- | ----- | 
| **Project Name** | GARA Mobile & LMS Web Backend (`gara_flutter` + `ite/gara` Laravel) | 
| **Target Release** | `v2.0.0-hardened` (Unified Security, Architecture & UX Release) | 
| **Baseline Commit** | `bf4143c4` | 
| **Primary Consumer** | Autonomous AI Coding Agent / Multi-Agent Engineering Orchestrator | 
| **Scope Boundary** | **Aplikasi Sisi Klien (Flutter) & Backend (Laravel) Saja.** Konfigurasi eksternal Google Cloud Console (GCP) berada **di luar cakupan** PRD ini. | 
| **Status** | `APPROVED FOR EXECUTION` | 

## 1. Executive Context & Objective

### 1.1 Latar Belakang Masalah

Berdasarkan audit menyeluruh terhadap *code knowledge graph* (`GRAPH_REPORT_FLUTTER.md`), dokumentasi teknis (`LAPORAN DOKUMENTASI.md`), serta hasil pengujian langsung pada antarmuka siswa dan guru, ditemukan 10 kelompok masalah kritis lintas lapisan (*full-stack*):

1. **Kerentanan Autentikasi & Token:** Token Laravel Sanctum disimpan *plaintext* di `SharedPreferences` dan dikirim via parameter URL `GET` (`/auth/webview-handoff?token=...`).

2. **Celah Keamanan WebView & Kiosk Ujian:** Konfigurasi `InAppWebViewSettings` mengizinkan *mixed content*, akses file lokal, dan *context menu* saat ujian; `SmartConnectPage` terbuka di rilis produksi; serta proteksi anti-cheat belum memiliki paritas di iOS.

3. **Ketergantungan Hosting Gratis & Polling Boros:** URL produksi terikat pada `garaedu.gt.tc` dengan *cookie scraper* (`InfinityBypassEngine`), serta *polling* notifikasi 60 detik berjalan terus tanpa memantau *lifecycle* aplikasi.

4. **Bottleneck Penyimpanan Lokal:** Seluruh catatan (`NotesService`) dan histori Pomodoro (`FocusService`) diserialisasi ke satu string JSON monolitik di `SharedPreferences`.

5. **Duplikasi Kode & Graf/Dokumen Usang:** Terdapat duplikasi modul inti (`AppConfig`, `AuthService`, `GaraColors`, `NoteModel`, `FocusService`), 2.261 *isolated nodes*, polusi graf oleh bundel vendor JS, serta dokumen yang tidak sinkron.

6. **Alur Integrasi Google Workspace Terlempar ke Browser Luar:** Menekan tombol *"Hubungkan Google Drive & Workspace"* pada akun guru melempar pengguna keluar dari GARA ke aplikasi Google Chrome eksternal.

7. **Unduhan File Guru Terlempar & Diminta Login Ulang:** Menekan tombol *"Unduh"* pada modal *"Format Template Soal"* (`.xlsx`, `.docx`, `.txt`, `.csv`, `.md`) membelokkan request ke browser eksternal tanpa membawa *session cookie* WebView sehingga pengguna diminta login ulang dan file gagal tersimpan ke memori perangkat.

8. **Kerusakan Layout Responsif (Overflow & Overlap):**

   * Pada Dashboard Siswa (`HeroCard` — *Aktivitas Terakhir*), teks nama mata pelajaran yang panjang (*"Pendidikan Jasmani, Olahraga, dan Kesehatan"*) menembus tombol *play* putih dan keluar dari batas kanan kartu biru (`"...nik da"`).

   * Pada Editor Soal Guru (*Soal 1 dari 10*), kotak putih *"NAVIGASI SOAL"* bertumpuk (*overlapping*) menutupi kolom input *"Ketik soal di sini..."*.

9. **Kebocoran Pesan Exception & Raw HTML ke Layar Pengguna:** Layar gagal koneksi menampilkan pesan mentah `InfinityFreeBypassException: Warm-up timeout... --- RAW HTML SNIPPET (maks 500 chars) --- TIMEOUT_EXPIRED` langsung kepada siswa dan guru.

### 1.2 Tujuan Utama (Success Outcomes)

AI Agent wajib mengeksekusi seluruh perbaikan dalam **10 Epic terstruktur (`EP-01` hingga `EP-10`)** untuk mencapai:

1. **Zero Plaintext Token Exposure:** Token terenkripsi di `FlutterSecureStorage` dan *handoff* menggunakan tiket sekali pakai berumur 15 detik.

2. **Hardened Exam Sandbox:** WebView terkunci ketat pada mode ujian di Android dan iOS tanpa *debug backdoor* produksi.

3. **Seamless In-App Google Workspace Auth:** OAuth Google berjalan di dalam alur aplikasi menggunakan Chrome Custom Tabs / `ASWebAuthenticationSession` dengan kembalian otomatis ke GARA WebView (tanpa menyentuh pengaturan GCP Console).

4. **Direct Authenticated File Downloads:** Semua unduhan dokumen/template di WebView diunduh langsung ke folder *Download* perangkat menggunakan *cookie* sesi aktif + notifikasi lokal tanpa keluar ke browser eksternal.

5. **Zero UI Overflow & Overlap:** Kartu dashboard siswa dan editor soal guru tampil rapi di seluruh ukuran layar ponsel maupun tablet.

6. **Sanitized User-Friendly Error UI:** Tidak ada nama kelas *exception*, *stack trace*, atau potongan HTML mentah yang tampil di layar pengguna; seluruh gangguan dipetakan ke kode error standar (`ERR-xxx`).

7. **Clean Architecture & 100% Automated Tests:** Migrasi SQLite tuntas, modul duplikat terkonsolidasi, graf/dokumen sinkron, dan seluruh pengujian lulus 100%.

## 2. AI Agent Execution Protocol & Guardrails

AI Agent **WAJIB** mematuhi aturan operasional berikut:

1. **Eksekusi Berurutan per Epic:** Kerjakan `EP-01` $\rightarrow$ `EP-02` $\rightarrow$ `EP-03` $\rightarrow$ `EP-04` $\rightarrow$ `EP-05` $\rightarrow$ `EP-06` $\rightarrow$ `EP-07` $\rightarrow$ `EP-08` $\rightarrow$ `EP-09` $\rightarrow$ `EP-10`. Pastikan setiap tahap bersih dari *compiler/analyzer error* sebelum berlanjut.

2. **Larangan Konfigurasi GCP Console:** Pada `EP-07`, AI Agent **DILARANG** membuat instruksi, skrip, atau perubahan yang mengasumsikan modifikasi Google Cloud Console / OAuth Client ID. Perbaikan wajib berfokus 100% pada mekanisme *in-app browser tab*, *deep link/callback routing*, dan sinkronisasi sesi antara Flutter dan Laravel (`GoogleAuthController`).

3. **Offline-First Preservation:** Fitur `RuangCatatanPage` dan `RuangFokusPage` harus tetap berfungsi penuh tanpa koneksi internet.

4. **Backward-Compatible Data Migration:** Token sesi lama serta data catatan/Pomodoro di `SharedPreferences` wajib dimigrasikan secara otomatis dan idempoten tanpa kehilangan data pengguna.

5. **Zero Analyzer Issues:** `flutter analyze` wajib menghasilkan `No issues found!`.

## 3. Repository & Architecture Baseline

```
workspace_root/
├── ite/gara/                                      # Backend Laravel + Laravel Sanctum
│   ├── app/Http/Controllers/
│   │   ├── Api/
│   │   │   ├── ExamStatusController.php
│   │   │   ├── MobileAuthController.php
│   │   │   ├── MobileDataController.php
│   │   │   ├── MobileHandoffController.php
│   │   │   └── NotificationApiController.php
│   │   └── GoogleAuthController.php
│   ├── resources/views/                           # Blade Templates (Guru Bank Soal / Ujian, Siswa)
│   ├── routes/api.php
│   ├── routes/web.php
│   └── tests/Feature/
└── gara_flutter/                                  # Frontend Mobile Flutter (^3.5.0)
    ├── android/app/
    │   ├── src/main/AndroidManifest.xml
    │   └── src/main/kotlin/com/example/gara_flutter/MainActivity.kt
    ├── ios/Runner/
    │   ├── Info.plist
    │   └── AppDelegate.swift
    ├── lib/
    │   ├── main.dart
    │   ├── models/                                # note_model, focus_model, mapel_model, pengumuman_model, trivia_model
    │   ├── screens/                               # login, pilih_mapel, dashboard, ruang_fokus, ruang_catatan, note_editor, smart_connect, activation
    │   ├── services/                              # auth, notes, focus, connectivity, notification, pengumuman, diskusi, infinity_*
    │   ├── utils/                                 # app_config.dart, app_constants.dart
    │   └── widgets/                               # hybrid_wrapper.dart, dashboard/*
    ├── test/
    └── pubspec.yaml

```

## 4. Detailed Functional & Technical Requirements

### EPIC 01 \[P0\]: Authentication & Handoff Bridge Security Hardening

#### 4.1.1 Enkripsi Penyimpanan Token Klien (`FlutterSecureStorage`)

* **Target Files:** `gara_flutter/pubspec.yaml`, `gara_flutter/lib/services/auth_service.dart`, `gara_flutter/lib/utils/app_constants.dart`, `gara_flutter/lib/main.dart`

* **Current Flaw:** Token Sanctum disimpan sebagai *plaintext string* di `SharedPreferences` (`GaraPrefKeys.authToken`).

* **Technical Specification:**

  1. Tambahkan `flutter_secure_storage: ^9.2.2` ke `pubspec.yaml`.

  2. Inisialisasi `FlutterSecureStorage` pada `AuthService` dengan `AndroidOptions(encryptedSharedPreferences: true)` dan `IOSOptions(accessibility: KeychainAccessibility.first_unlock_this_device)`.

  3. Implementasikan `Future<void> migrateLegacyTokenIfNeeded()` yang dipanggil pada saat inisialisasi aplikasi di `main.dart`: jika `SharedPreferences` masih memiliki nilai pada `GaraPrefKeys.authToken`, salin ke `FlutterSecureStorage` lalu jalankan `await prefs.remove(GaraPrefKeys.authToken)`.

  4. Arahkan seluruh operasi `saveToken`, `getToken`, dan `clearSession` ke `FlutterSecureStorage`.

#### 4.1.2 Penggantian URL Query Token dengan One-Time Short-Lived Handoff Ticket

* **Target Files:** `ite/gara/app/Http/Controllers/Api/MobileHandoffController.php`, `ite/gara/routes/api.php`, `ite/gara/routes/web.php`, `gara_flutter/lib/services/auth_service.dart`, `gara_flutter/lib/utils/app_config.dart`, `gara_flutter/lib/widgets/hybrid_wrapper.dart`

* **Current Flaw:** URL handoff `${baseUrl}/auth/webview-handoff?token=${token}&target=${targetPath}&mapel_id=${mapelId}` mengekspos token Sanctum di URL query dan tidak memvalidasi hak akses rute berdasarkan *role*.

* **Technical Specification (Backend Laravel):**

  1. Buat endpoint `POST /api/mobile/handoff-ticket` (`middleware('auth:sanctum')`) di `MobileHandoffController@issueTicket`.

  2. Validasi payload `target` terhadap daftar putih (*allowlist*) prefix berdasarkan `auth()->user()->role`:

     * `siswa`: `['/student/', '/ruang-ujian/']`

     * `guru`: `['/guru/', '/teacher/']`

     * `operator`: `['/operator/']`

     * `kepsek`: `['/kepsek/', '/principal/']`

     * `admin`: `['/admin/', '/super-admin/']`

     * Tolak dengan HTTP `403 Forbidden` jika `target` berisi URL eksternal (`http://`, `https://`, `//`), *path traversal* (`..`), atau di luar prefix peran pengguna.

  3. Hasilkan tiket acak kriptografis 64 karakter (`bin2hex(random_bytes(32))`) dan simpan ke Laravel Cache dengan TTL ketat **15 detik**:

     ```
     Cache::put("handoff_ticket:{$ticket}", [
         'user_id'  => $user->id,
         'role'     => $user->role,
         'target'   => $validatedTarget,
         'mapel_id' => $validatedMapelId,
     ], now()->addSeconds(15));
     
     ```

  4. Refactor `GET /auth/webview-handoff` (`MobileHandoffController@consumeTicket`):

     * Tolak dengan HTTP `400 Bad Request` jika query mengandung parameter `token` lama.

     * Ambil dan hapus tiket secara atomik via `$payload = Cache::pull("handoff_ticket:{$ticket}");`. Jika kosong/kedaluwarsa, kembalikan HTTP `401 Unauthorized`.

     * Jalankan `Auth::loginUsingId($payload['user_id'])`, `$request->session()->regenerate()`, simpan `active_mapel_id` ke sesi, dan redirect ke `$payload['target']`.

* **Technical Specification (Frontend Flutter):**

  1. Tambahkan `AuthService.requestHandoffTicket({required String targetPath, String? mapelId})` yang memanggil `POST /api/mobile/handoff-ticket`.

  2. Perbarui `HybridWrapper` agar memuat `${AppConfig.baseUrl}/auth/webview-handoff?ticket=$ticket` dan hapus penyuntikan cookie `Authorization: Bearer $token` mentah.

* **Acceptance Criteria (`EP-01`):**

  * \[ \] `SharedPreferences` tidak menyimpan `auth_token`.

  * \[ \] URL WebView tidak mengandung `token=`.

  * \[ \] Tiket handoff gagal (HTTP 401) pada pemakaian kedua atau setelah $> 15$ detik, dan HTTP 403 untuk rute lintas peran.

### EPIC 02 \[P0\]: WebView Sandbox & Cross-Platform Exam Kiosk Hardening

#### 4.2.1 Pengetatan Konfigurasi `InAppWebViewSettings` & Domain Whitelisting

* **Target Files:** `gara_flutter/lib/widgets/hybrid_wrapper.dart`, `gara_flutter/lib/utils/app_config.dart`

* **Technical Specification:**

  1. Terapkan `InAppWebViewSettings` terisolasi pada `HybridWrapper`:

     ```
     final bool isExam = _isExamRoute(widget.targetPath);
     InAppWebViewSettings webSettings = InAppWebViewSettings(
       useHybridComposition: true,
       domStorageEnabled: true,
       databaseEnabled: false,
       javaScriptEnabled: true,
       safeBrowsingEnabled: true,
       mixedContentMode: MixedContentMode.MIXED_CONTENT_NEVER_ALLOW,
       cacheEnabled: !isExam,
       cacheMode: isExam ? CacheMode.LOAD_NO_CACHE : CacheMode.LOAD_DEFAULT,
       useShouldOverrideUrlLoading: true,
       useOnDownloadStart: true,
       allowFileAccessFromFileURLs: false,
       allowUniversalAccessFromFileURLs: false,
       allowFileAccess: false,
       allowContentAccess: false,
       mediaPlaybackRequiresUserGesture: false,
       allowsInlineMediaPlayback: true,
       javaScriptCanOpenWindowsAutomatically: false,
       transparentBackground: false,
       supportZoom: !isExam,
       thirdPartyCookiesEnabled: true, // Dibutuhkan untuk sesi sub-resource web
       disableContextMenu: isExam,
       applicationNameForUserAgent: 'GARA_OFFICIAL_APP',
     );
     
     ```

  2. Pada mode ujian (`isExam == true`), blokir seluruh navigasi keluar dari `AppConfig.allowedDomains` dan blokir tautan eksternal (`whatsapp://`, `https://wa.me/`).

#### 4.2.2 Penutupan Pintu Belakang `SmartConnectPage` & Pembersihan Paket Usang

* **Target Files:** `gara_flutter/lib/screens/pilih_mapel_page.dart`, `gara_flutter/lib/screens/smart_connect_page.dart`, `gara_flutter/pubspec.yaml`

* **Technical Specification:**

  1. Hapus `flutter_windowmanager: ^0.2.0` dari `pubspec.yaml` dan bersihkan seluruh *import*-nya.

  2. Pada `PilihMapelPage`, batasi akses menuju `SmartConnectPage` hanya saat `kDebugMode == true`, dan tambahkan guard `if (kReleaseMode) { Navigator.pop(context); }` di `SmartConnectPage`.

#### 4.2.3 Penguatan Anti-Cheat Native Android (`MainActivity.kt`) & Paritas iOS (`AppDelegate.swift`)

* **Target Files:** `gara_flutter/android/app/src/main/kotlin/com/example/gara_flutter/MainActivity.kt`, `gara_flutter/ios/Runner/AppDelegate.swift`, `gara_flutter/lib/widgets/hybrid_wrapper.dart`

* **Technical Specification:**

  1. Tambahkan method `checkDeviceIntegrity` pada channel `com.lms.gara/security` di `MainActivity.kt` Android yang memeriksa `isRooted` (binary `su` / tag `test-keys`), `isAdbEnabled`, dan `canDrawOverlays` (`Settings.canDrawOverlays`).

  2. Daftarkan channel `com.lms.gara/security` pada `AppDelegate.swift` iOS yang memantau `UIScreen.capturedDidChangeNotification` (*screen recording*/AirPlay) dan `UIApplication.willResignActiveNotification`, lalu mengirim event `onSecurityViolation` ke Flutter.

  3. Di `HybridWrapper`, jalankan `checkDeviceIntegrity` sebelum memuat `/ruang-ujian/arena` dan blokir akses jika perangkat terdeteksi tidak aman.

* **Acceptance Criteria (`EP-02`):**

  * \[ \] `flutter_windowmanager` dihapus; `SmartConnectPage` tidak dapat diakses di build rilis.

  * \[ \] `MIXED_CONTENT_NEVER_ALLOW`, `allowFileAccessFromFileURLs: false`, dan `disableContextMenu: isExam` aktif.

  * \[ \] Kanal `com.lms.gara/security` merespons tanpa error di Android dan iOS.

### EPIC 03 \[P1\]: Infrastructure Decoupling & Network Efficiency

#### 4.3.1 Dekoupling dari Free Hosting (`garaedu.gt.tc`) & Isolasi `InfinityBypassEngine`

* **Target Files:** `gara_flutter/lib/utils/app_config.dart`, `gara_flutter/lib/services/infinity_api_client.dart`, `gara_flutter/lib/services/infinity_bypass_engine.dart`, `gara_flutter/lib/widgets/infinity_bypass_dialog.dart`

* **Technical Specification:**

  1. Gunakan `String.fromEnvironment('GARA_BASE_URL', defaultValue: 'https://garaedu.gt.tc')` dan `bool.fromEnvironment('ENABLE_INFINITY_BYPASS', defaultValue: false)` pada `AppConfig`.

  2. Gunakan `http.Client` standar sebagai jalur utama komunikasi API JSON, dan jalankan `InfinityBypassEngine` hanya jika `enableLegacyInfinityBypass == true` atau terdeteksi pola tantangan JS spesifik.

#### 4.3.2 Optimasi Polling Notifikasi & Timeout `SholatWidget`

* **Target Files:** `gara_flutter/lib/screens/dashboard_page.dart`, `gara_flutter/lib/widgets/dashboard/sholat_widget.dart`

* **Technical Specification:**

  1. Gunakan `WidgetsBindingObserver` di `DashboardPage` untuk menghentikan timer *polling* saat aplikasi `paused`/`inactive`, dan atur interval adaptif $\ge 180$ detik saat aktif.

  2. Tambahkan `.timeout(const Duration(seconds: 4))` pada panggilan Aladhan API di `SholatWidget` dengan fallback instan ke cache `SharedPreferences`.

* **Acceptance Criteria (`EP-03`):**

  * \[ \] Build dengan `--dart-define=GARA_BASE_URL=...` berjalan bersih tanpa memicu modul bypass.

  * \[ \] Polling berhenti saat aplikasi di *background*, dan `SholatWidget` selesai merender $< 4$ detik saat offline.

### EPIC 04 \[P2\]: Local Storage Migration (`SharedPreferences` $\rightarrow$ SQLite)

#### 4.4.1 Skema Database Relasional Lokal (`GaraLocalDatabase`)

* **Target Files:** `gara_flutter/pubspec.yaml`, `gara_flutter/lib/services/local_database_service.dart` (baru), `gara_flutter/lib/services/notes_service.dart`, `gara_flutter/lib/services/focus_service.dart`

* **Technical Specification:**

  1. Tambahkan `sqflite: ^2.3.3` dan `path: ^1.9.0` pada `pubspec.yaml`.

  2. Buat singleton `LocalDatabaseService` (`gara_offline.db`, versi `1`) dengan tabel terindeks: `notes`, `note_blocks` (`ON DELETE CASCADE`), `focus_meta`, dan `focus_history`.

  3. Jalankan migrasi idempoten satu kali dari key JSON `'garuda_akademi_ruang_catatan'` dan `'garuda_akademi_ruang_fokus'` ke SQLite saat inisialisasi pertama, lalu hapus key lama setelah transaksi SQLite berhasil `COMMIT`.

  4. Ubah `NotesService.saveNote` agar hanya menulis 1 catatan yang diubah (`INSERT OR REPLACE`) tanpa serialisasi ulang seluruh koleksi.

* **Acceptance Criteria (`EP-04`):**

  * \[ \] Data catatan dan Pomodoro lama termigrasi 100% ke SQLite tanpa duplikasi saat *restart*.

### EPIC 05 \[P2\]: Codebase Deduplication, Dead Code Pruning & Graph/Docs Sync

#### 4.5.1 Konsolidasi Modul Duplikat, `.graphifyignore` & Sinkronisasi Dokumentasi

* **Target Files:** `gara_flutter/lib/**`, `.graphifyignore` (baru), `ARCHITECTURE.md` (menggantikan duplikat `LAPORAN DOKUMENTASI.md` & `dokumflut.md`)

* **Technical Specification:**

  1. Konsolidasikan definisi kelas ganda (`AppConfig`, `GaraColors`, `AuthService`, `NoteModel`, `FocusService`, `PengumumanModel`, `SholatWidget`) ke berkas kanonik tunggal dan perbarui seluruh *import*.

  2. Hapus simbol yatim (*dead code*) yang tidak direferensikan di `gara_flutter/lib/`.

  3. Buat `.graphifyignore` untuk mengecualikan `public/vendor/**`, `public/build/**`, `node_modules/**`, `vendor/**`, `gara_flutter/build/**`, `gara_flutter/.dart_tool/**`, `gara_flutter/windows/**`, dan `gara_flutter/linux/**`.

  4. Satukan dokumentasi teknis ke `ARCHITECTURE.md` dengan pohon direktori akurat, tautan relatif valid, dan dokumentasi alur baru (`EP-01` s.d. `EP-10`), lalu jalankan `graphify update .`.

* **Acceptance Criteria (`EP-05`):**

  * \[ \] Tidak ada definisi kelas ganda di `gara_flutter/lib/` dan graf kode bersih dari polusi aset vendor JS.

### EPIC 06 \[P1/P2\]: Automated Security, Contract & Migration Test Suite

#### 4.6.1 Laravel Feature Tests & Flutter Unit/Widget Tests

* **Target Files:** `ite/gara/tests/Feature/MobileHandoffSecurityTest.php`, `gara_flutter/test/services/auth_migration_test.dart`, `gara_flutter/test/services/sqlite_migration_test.dart`, `gara_flutter/test/widgets/hero_card_overflow_test.dart`, `gara_flutter/test/utils/gara_error_mapper_test.dart`

* **Technical Specification:**

  1. Uji siklus tiket handoff (1x pakai, kadaluarsa 15 detik, penolakan *role* tidak sah, dan penolakan query `?token=`).

  2. Uji migrasi token `SharedPreferences` $\rightarrow$ `FlutterSecureStorage` dan migrasi JSON $\rightarrow$ SQLite.

  3. Uji widget `HeroCard` dengan teks panjang pada viewport `320px` (memastikan 0 `FlutterError` overflow).

  4. Uji `GaraErrorMapper` memastikan seluruh pesan exception mentah (`InfinityFreeBypassException`, `RAW HTML SNIPPET`, `SocketException`) dikonversi menjadi kode `ERR-xxx` yang aman.

* **Acceptance Criteria (`EP-06`):**

  * \[ \] `php artisan test` dan `flutter test` lulus 100%.

### EPIC 07 \[P0/P1\]: In-App Google Workspace OAuth Flow (App-Side Only — Exclude GCP Console)

#### 4.7.1 Masalah & Batasan Ruang Lingkup

* **Gejala Aktual (Screenshot 1 & 2):** Pada halaman profil guru (*"INTEGRASI GOOGLE WORKSPACE"*), menekan tombol **"Hubungkan Google Drive & Workspace"** melempar pengguna keluar dari GARA Mobile ke aplikasi Google Chrome eksternal (`accounts.google.com/...`).

* **Batasan Teknis (WAJIB DIPATUHI):**

  * **Jangan menyentuh atau membuat instruksi konfigurasi Google Cloud Console.**

  * Jangan memaksa memuat `accounts.google.com` di dalam `InAppWebView` standar karena kebijakan keamanan Google memblokir *embedded WebView* dengan `Error 403: disallowed_useragent`.

  * Gunakan **Chrome Custom Tabs (Android) / `ASWebAuthenticationSession` (iOS)** melalui paket `flutter_custom_tabs`, yang tampil menyatu sebagai *in-app modal sheet* di atas aplikasi GARA tanpa berpindah ke aplikasi browser terpisah, lalu otomatis menutup dan me-reload WebView GARA setelah proses selesai.

#### 4.7.2 Spesifikasi Implementasi (Flutter + Laravel)

* **Target Files:**

  * `gara_flutter/pubspec.yaml`

  * `gara_flutter/lib/services/in_app_oauth_service.dart` (baru)

  * `gara_flutter/lib/widgets/hybrid_wrapper.dart`

  * `gara_flutter/android/app/src/main/AndroidManifest.xml`

  * `gara_flutter/ios/Runner/Info.plist`

  * `ite/gara/app/Http/Controllers/GoogleAuthController.php`

* **Langkah Teknis:**

  1. Tambahkan `flutter_custom_tabs: ^2.1.0` ke `gara_flutter/pubspec.yaml`.

  2. Buat `InAppOAuthService.launchGoogleWorkspaceAuth({required String authUrl, required BuildContext context})`:

     * Membuka `authUrl` menggunakan `launchUrl(Uri.parse(authUrl), customTabsOptions: CustomTabsOptions(...), safariVCOptions: SafariViewControllerOptions(...))` dengan warna toolbar `GaraColors.primary`, `showTitle: true`, dan `urlBarHidingEnabled: true` sehingga tampil menyatu di dalam aplikasi GARA.

  3. Pada `HybridWrapper.shouldOverrideUrlLoading`:

     * Saat `isExam == false`, deteksi URL navigasi yang menuju endpoint OAuth Google aplikasi (`${AppConfig.baseUrl}/auth/google` atau `${AppConfig.baseUrl}/google/redirect` atau host `accounts.google.com`).

     * Alih-alih memanggil `LaunchMode.externalApplication` (yang membuka Chrome terpisah), panggil `InAppOAuthService.launchGoogleWorkspaceAuth(...)` dengan menyertakan parameter `?source=mobile_app` pada URL awal endpoint Laravel, lalu kembalikan `NavigationActionPolicy.CANCEL`.

  4. **Sinkronisasi Kembalian ke Aplikasi (Deep Link Return):**

     * Daftarkan *custom URL scheme* `garamobile` (`host: "oauth"`, `pathPrefix: "/google-done"`) di `AndroidManifest.xml` (pada `<activity android:name=".MainActivity">`) dan `CFBundleURLTypes` di `ios/Runner/Info.plist`.

     * Pada `GoogleAuthController.php` di Laravel: saat proses callback selesai (baik berhasil maupun gagal/batal), periksa apakah sesi awal memiliki penanda `session('oauth_source') === 'mobile_app'`. Jika ya, selain menyimpan token Google ke database pengguna seperti biasa, arahkan respons akhir ke halaman jembatan HTML ringan yang memicu `window.location.href = 'garamobile://oauth/google-done?status=' + status` (dengan tombol fallback manual *"Kembali ke Aplikasi GARA"*).

     * Pada `HybridWrapper`: dengarkan *app lifecycle* (`AppLifecycleState.resumed`) dan/atau *deep link stream* setelah sesi Custom Tab ditutup, lalu otomatis jalankan `await _webViewController?.reload()` agar kartu *"INTEGRASI GOOGLE WORKSPACE"* pada WebView guru langsung memperbarui statusnya dari *"Belum Terhubung"* menjadi *"Terhubung"* tanpa pengguna harus keluar atau memuat ulang halaman secara manual.

* **Acceptance Criteria (`EP-07`):**

  * \[ \] Menekan *"Hubungkan Google Drive & Workspace"* membuka layar autentikasi di dalam *in-app Custom Tab* (tidak membuka jendela aplikasi Chrome terpisah).

  * \[ \] Tidak ada kode/dokumen yang meminta atau mengubah konfigurasi Google Cloud Console.

  * \[ \] Setelah layar Custom Tab ditutup/selesai, `HybridWrapper` otomatis me-reload halaman profil guru di dalam aplikasi.

### EPIC 08 \[P0/P1\]: Authenticated In-App File Download Manager

#### 4.8.1 Masalah & Akar Penyebab

* **Gejala Aktual (Screenshot 3):** Pada modal **"Format Template Soal"** guru (`.xlsx`, `.docx`, `.txt`, `.csv`, `.md`), menekan tombol **"Unduh"** melempar pengguna keluar ke browser eksternal dan menampilkan halaman login, sehingga file gagal terunduh ke penyimpanan HP.

* **Akar Penyebab:**

  1. `HybridWrapper` tidak mengimplementasikan callback `onDownloadStartRequest` milik `InAppWebView`.

  2. Browser eksternal tidak memiliki *PHP session cookie* (dan cookie `__test` jika hosting lama aktif) yang tersimpan di dalam `CookieManager` WebView aplikasi.

#### 4.8.2 Spesifikasi Implementasi (Flutter Native Download Engine)

* **Target Files:**

  * `gara_flutter/pubspec.yaml`

  * `gara_flutter/lib/services/authenticated_download_service.dart` (baru)

  * `gara_flutter/lib/widgets/hybrid_wrapper.dart`

  * `gara_flutter/android/app/src/main/AndroidManifest.xml`

* **Langkah Teknis:**

  1. Tambahkan `path_provider: ^2.1.4` dan `open_filex: ^4.5.0` pada `gara_flutter/pubspec.yaml`. Pastikan izin penyimpanan kompatibel di `AndroidManifest.xml` (`android:maxSdkVersion="32"` untuk `WRITE_EXTERNAL_STORAGE`, dan penyimpanan langsung ke direktori publik `Download` / `getExternalStorageDirectories(type: StorageDirectory.downloads)` pada Android 10+).

  2. Pastikan `HybridWrapper.shouldOverrideUrlLoading` **tidak** membelokkan rute unduhan internal domain (`AppConfig.allowedDomains`) ke browser eksternal—biarkan URL tersebut diproses oleh WebView agar memicu `onDownloadStartRequest`.

  3. Implementasikan `onDownloadStartRequest(InAppWebViewController controller, DownloadStartRequest request)` di `HybridWrapper` (blokir jika `isExam == true`; jalankan `AuthenticatedDownloadService.downloadFromWebView(...)` jika `isExam == false`):

     * **Ekstraksi Cookie Sesi Aktif:** Ambil seluruh cookie untuk `request.url` dan `WebUri(AppConfig.baseUrl)` melalui `CookieManager.instance().getCookies(...)`, lalu gabungkan menjadi header `Cookie: key1=val1; key2=val2`.

     * **Authenticated HTTP Request:** Lakukan request `GET` menggunakan `http.Client` dengan membawa header `Cookie`, `User-Agent: request.userAgent ?? 'GARA_OFFICIAL_APP'`, dan `Referer: AppConfig.baseUrl`. Ikuti HTTP redirect (`301`/`302`) secara manual hingga maksimal 5 kali sambil tetap menyertakan header `Cookie` agar endpoint Laravel yang melakukan redirect sebelum streaming file tetap terautentikasi.

     * **Deteksi Tipe & Nama File:** Validasi bahwa respons bukan halaman HTML login (`!contentType.contains('text/html')` kecuali ekstensi target memang `.html`). Ekstrak nama file dari header `Content-Disposition` (`filename*=UTF-8''...` atau `filename="..."`), atau fallback dari segmen akhir URL + ekstensi MIME (`.xlsx`, `.docx`, `.txt`, `.csv`, `.md`, `.pdf`). Jika terdapat nama file duplikat di folder tujuan, tambahkan sufiks `(1)`, `(2)`, dst.

     * **Penyimpanan & Umpan Balik UI:** Tampilkan *loading snackbar* *"Mengunduh file..."* saat proses berjalan; setelah selesai simpan ke folder `Download` perangkat, tampilkan notifikasi sistem lokal (`flutter_local_notifications`) serta *floating snackbar* sukses dengan tombol aksi **"Buka"** yang memanggil `OpenFilex.open(savedFilePath)`.

     * Jika respons server ternyata mengembalikan `401`/`403` atau halaman login HTML, tampilkan pesan ramah via `GaraErrorMapper` (`ERR-DL-401`) dan tawarkan muat ulang sesi tanpa membuka browser eksternal.

* **Acceptance Criteria (`EP-08`):**

  * \[ \] Menekan tombol *"Unduh"* pada seluruh 5 format template soal (`.xlsx`, `.docx`, `.txt`, `.csv`, `.md`) mengunduh file langsung ke memori internal perangkat tanpa membuka browser eksternal dan tanpa meminta login ulang.

  * \[ \] File yang terunduh berukuran $> 0$ bytes, dapat dibuka dengan aplikasi pembaca dokumen melalui tombol *"Buka"*, dan tercatat di notifikasi sistem.

### EPIC 09 \[P1\]: Native & WebView Responsive Layout Remediation

#### 4.9.1 Perbaikan Overflow Kartu Dashboard Siswa (`HeroCard`)

* **Target Files:** `gara_flutter/lib/widgets/dashboard/hero_card.dart`

* **Gejala Aktual (Screenshot 5):** Pada kartu biru *"Aktivitas Terakhir / Lanjutkan Belajar"*, badge nama mata pelajaran (`"Pendidikan Jasmani, Olahraga, dan Kesehatan"`) memanjang ke kanan menembus di belakang tombol *play* putih dan terpotong keluar batas bingkai kartu (`"...nik da"`).

* **Technical Specification:**

  1. Audit struktur `Row` dan `Stack` pada `HeroCard` (`lib/widgets/dashboard/hero_card.dart`).

  2. Batasi lebar area konten teks kiri agar tidak pernah bertabrakan dengan tombol *play* putih di kanan:

     * Bungkus `Column` konten kiri dengan `Expanded` dan beri jarak kanan eksplisit (`Padding(padding: EdgeInsets.only(right: 12))`) sebelum widget tombol *play*.

     * Pada container badge/pill mata pelajaran pertama di atas judul *"Lanjutkan Belajar"*, ganti ukuran lebar tak terbatas menjadi `Flexible` (di dalam `Row` dengan `mainAxisSize: MainAxisSize.min`) dan setel properti `Text`:

       ```
       Flexible(
         child: Text(
           namaMapel,
           maxLines: 1,
           overflow: TextOverflow.ellipsis,
           softWrap: false,
           style: ...,
         ),
       )
       
       ```

     * Pastikan badge kedua di bawah judul (*"Pendidikan Jasmani, Olahraga, dan ..."*) menggunakan tata letak responsif yang konsisten dan tidak pernah memicu `RenderFlex overflow` pada layar selebar `320dp` maupun tablet.

#### 4.9.2 Perbaikan Overlap *"NAVIGASI SOAL"* pada Editor Soal Guru (WebView / Laravel Blade)

* **Target Files:** `ite/gara/resources/views/` (berkas Blade editor soal Bank Soal / Ujian Guru, misal `bank-soal/soal.blade.php` atau komponen modal/form soal guru) dan CSS terkait di `ite/gara/public/` atau `ite/gara/resources/css/`.

* **Gejala Aktual (Screenshot 4):** Pada layar *"Soal 1 dari 10 (PG)"*, kartu putih **"NAVIGASI SOAL"** (kotak nomor 1–10 dan legenda *"Sudah diisi / Aktif"*) bertumpuk (*overlapping*) menutupi kolom input teks soal (*"Ketik soal di sini... (Mendukung paragraf, bold, italic, list)"*).

* **Technical Specification:**

  1. Temukan komponen *"NAVIGASI SOAL"* dan kontainer *"Ketik soal di sini..."* pada template Blade guru di `ite/gara/resources/views/`.

  2. Hilangkan properti CSS penyebab tumpang tindih pada tampilan mobile (`position: sticky` / `position: fixed` tanpa *offset* yang benar, *negative margin*, atau ketinggian *container* tetap `height: ...px` dengan `overflow: visible`).

  3. Terapkan aturan CSS responsif eksplisit pada halaman editor soal tersebut:

     ```
     @media (max-width: 768px) {
       .navigasi-soal-card,
       [data-role="navigasi-soal"] {
         position: relative !important;
         top: auto !important;
         z-index: 1 !important;
         margin-bottom: 16px !important;
         height: auto !important;
         clear: both;
       }
       .editor-soal-container,
       .soal-input-wrapper {
         position: relative !important;
         margin-top: 0 !important;
         z-index: 1 !important;
       }
     }
     
     ```

     *(Sesuaikan selektor kelas/ID dengan nama aktual di file Blade proyek).*

* **Acceptance Criteria (`EP-09`):**

  * \[ \] `HeroCard` menampilkan nama mata pelajaran sepanjang 80 karakter pada layar `320px` tanpa keluar batas kartu dan tanpa menabrak tombol *play*.

  * \[ \] Pada layar guru *"Soal 1 dari 10"*, kartu *"NAVIGASI SOAL"* berada sepenuhnya di atas area *"Ketik soal di sini..."* dengan jarak bersih minimal `16px` dan nol tumpang tindih.

### EPIC 10 \[P1\]: Production Error Masking & Standardized Error Code System

#### 4.10.1 Penghapusan Stack Trace & Raw HTML dari Antarmuka Pengguna

* **Target Files:**

  * `gara_flutter/lib/utils/gara_error_mapper.dart` (baru)

  * `gara_flutter/lib/widgets/gara_error_view.dart` (baru)

  * `gara_flutter/lib/services/infinity_bypass_exception.dart` (atau file definisi `InfinityFreeBypassException`)

  * `gara_flutter/lib/widgets/hybrid_wrapper.dart`

  * `gara_flutter/lib/screens/pilih_mapel_page.dart`

  * `gara_flutter/lib/screens/dashboard_page.dart`

  * `gara_flutter/lib/screens/login_page.dart`

* **Gejala Aktual (Screenshot 6):** Layar kesalahan menampilkan teks teknis mentah:
  `Terjadi kesalahan koneksi: InfinityFreeBypassException: Warm-up timeout. WebView gagal menyelesaikan JS Challenge. --- RAW HTML SNIPPET (maks 500 chars) --- TIMEOUT_EXPIRED`.

* **Technical Specification:**

  1. Buat model `GaraUserError` dan utilitas `GaraErrorMapper.fromException(Object error, [StackTrace? stackTrace])` di `lib/utils/gara_error_mapper.dart`:

     * Selalu catat detail teknis lengkap (`error.toString()`, `rawHtmlSnippet`, `stackTrace`) **hanya** ke `debugPrint(...)` (yang otomatis nonaktif pada rilis produksi).

     * Kembalikan objek `GaraUserError` berisi `title`, `userMessage` (bahasa Indonesia sopan, non-teknis), `errorCode`, dan `icon`:

       | Kondisi / Jenis Exception | Kode Error | Judul UI | Pesan Ramah Pengguna | 
       | ----- | ----- | ----- | ----- | 
       | `SocketException`, `ERR_INTERNET_DISCONNECTED`, `ERR_NAME_NOT_RESOLVED` | `ERR-NET-001` | Koneksi Terputus | Periksa jaringan Wi-Fi atau data seluler Anda, lalu coba kembali. | 
       | `TimeoutException`, `InfinityFreeBypassException`, `TIMEOUT_EXPIRED` | `ERR-CONN-504` | Server Sedang Sibuk | Waktu tunggu koneksi ke server habis. Silakan tekan Coba Lagi beberapa saat lagi. | 
       | HTTP `500`–`503` / `ERR_CONNECTION_REFUSED` | `ERR-SRV-500` | Layanan Sedang Pemeliharaan | Server sekolah sedang dalam penyesuaian teknis. Mohon tunggu sebentar dan coba kembali. | 
       | HTTP `401` / `403` / Sesi kedaluwarsa | `ERR-AUTH-401` | Sesi Berakhir | Sesi masuk Anda telah berakhir demi keamanan. Silakan masuk kembali. | 
       | Kegagalan unduhan / penyimpanan file | `ERR-DL-002` | Gagal Mengunduh Berkas | Berkas belum dapat disimpan ke perangkat. Pastikan ruang penyimpanan cukup dan coba lagi. | 
       | Exception tidak dikenal lainnya | `ERR-SYS-999` | Gangguan Sementara | Terjadi kendala saat memuat halaman ini. Silakan tekan Coba Lagi. | 

  2. Buat widget terstandar `GaraErrorView` (`lib/widgets/gara_error_view.dart`) yang menampilkan:

     * Ikon status jaringan/gangguan yang bersih (ukuran `56dp`, warna `GaraColors.primary` / abu-abu lembut).

     * Judul & `userMessage` maksimal 2 baris rata tengah.

     * Tombol utama **"Coba Lagi"** (`GaraPrimaryButton`).

     * Badge kecil di bagian bawah tombol bertuliskan **`Kode: ERR-CONN-504`** (dengan fitur *tap-to-copy* kode error untuk memudahkan pelaporan ke admin sekolah tanpa membocorkan detail teknis).

  3. Ganti seluruh tampilan error mentah di `HybridWrapper` (`_OfflineOverlay` & `_onReceivedError`), `PilihMapelPage`, `DashboardPage`, dan `LoginPage` agar menggunakan `GaraErrorMapper` + `GaraErrorView`. Pastikan `InfinityFreeBypassException.toString()` tidak lagi menyisipkan `--- RAW HTML SNIPPET ---` ke dalam properti pesan yang dirender UI.

* **Acceptance Criteria (`EP-10`):**

  * \[ \] Tidak ada kata `Exception`, `RAW HTML SNIPPET`, `JS Challenge`, `InfinityFree`, atau `StackTrace` yang pernah tampil di layar pengguna pada kondisi timeout maupun offline.

  * \[ \] Setiap layar gangguan menampilkan ikon bersih, pesan ramah pengguna, tombol *"Coba Lagi"*, dan kode error standar (`ERR-xxx`).

## 5. Master Verification Checklist (Run Before Completion)

AI Agent wajib menjalankan seluruh perintah verifikasi berikut secara berurutan dan memastikan **0 errors / 0 failures**:

```
# 1. Backend Laravel Verification
cd ite/gara
php artisan config:clear
php artisan test --filter=Mobile

# 2. Frontend Flutter Static Analysis & Unit/Widget Tests
cd ../../gara_flutter
flutter pub get
flutter analyze
flutter test

# 3. Knowledge Graph Regeneration
cd ..
graphify update .

```

### Final Sign-Off Criteria

* \[ \] Seluruh item pada `EP-01` s.d. `EP-10` terimplementasi tuntas tanpa *TODO* atau *stub* kosong.

* \[ \] Alur Google Workspace berjalan di dalam aplikasi via Custom Tab tanpa menyentuh konfigurasi GCP Console (`EP-07`).

* \[ \] Unduhan template soal guru tersimpan langsung ke folder *Download* perangkat tanpa redirect login (`EP-08`).

* \[ \] Overflow `HeroCard` siswa dan overlap *"NAVIGASI SOAL"* guru teratasi sepenuhnya (`EP-09`).

* \[ \] Layar error hanya menampilkan ikon, pesan ramah, tombol *"Coba Lagi"*, dan kode `ERR-xxx` (`EP-10`).

* \[ \] `php artisan test`, `flutter analyze`, dan `flutter test` lulus 100%, serta `GRAPH_REPORT_FLUTTER.md` dan `ARCHITECTURE.md` telah diperbarui.