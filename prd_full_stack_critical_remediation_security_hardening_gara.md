# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## GARA Full-Stack Critical Remediation, Security Hardening & Architecture Consolidation

| Atribut Dokumen | Spesifikasi |
| :--- | :--- |
| **Project Name** | GARA Mobile & LMS Web Backend (`gara_flutter` + `ite/gara` Laravel) |
| **Target Release** | `v2.0.0-hardened` (Security & Architecture Remediation) |
| **Baseline Commit** | `bf4143c4` |
| **Primary Consumer** | Autonomous AI Coding Agent / Multi-Agent Engineering Orchestrator |
| **Status** | `APPROVED FOR EXECUTION` |

---

## 1. Executive Context & Objective

### 1.1 Latar Belakang Masalah
Berdasarkan audit menyeluruh terhadap *code knowledge graph* (`GRAPH_REPORT_FLUTTER.md`) dan dokumentasi teknis (`LAPORAN DOKUMENTASI.md`), sistem **GARA (Garuda Akademi)** memiliki celah keamanan kritis pada alur autentikasi *handoff*, penyimpanan kredensial lokal, konfigurasi *sandbox* WebView ujian, serta ketergantungan infrastruktur pada *free hosting* (`garaedu.gt.tc`) yang memaksa penggunaan mekanisme *scraping bypass* (`InfinityBypassEngine`). Selain itu, ditemukan duplikasi modul internal Flutter, 2.261 *isolated/dead nodes*, dan ketidaksinkronan antara kode sumber aktual dengan dokumentasi.

### 1.2 Tujuan Utama (Success Outcomes)
AI Agent bertugas mengeksekusi seluruh perbaikan dalam **6 Epic berurutan** (`EP-01` hingga `EP-06`) untuk mencapai kondisi berikut:
1. **Zero Plaintext Token Exposure:** Tidak ada token Laravel Sanctum yang disimpan di `SharedPreferences` maupun dikirim melalui parameter URL `GET`.
2. **Hardened Exam Sandbox (Android & iOS):** Penguncian layar ujian berjalan konsisten di Android dan iOS tanpa celah *mixed content*, akses file lokal, *context menu* salin-tempel, maupun *debug backdoor* di rilis produksi.
3. **Clean Infrastructure Decoupling:** Aplikasi produksi tidak bergantung pada domain *free hosting* ataupun *cookie-challenge scraper*, serta menggunakan siklus *polling* hemat daya.
4. **Reliable Offline Storage:** Seluruh catatan belajar (`NotesService`) dan sesi Pomodoro (`FocusService`) bermigrasi dari JSON monolitik `SharedPreferences` ke `SQLite` (`sqflite`) secara atomik tanpa kehilangan data pengguna.
5. **Single Source of Truth Architecture:** Kelas-kelas duplikat di Flutter terkonsolidasi, aset vendor tereksklusi dari *knowledge graph*, dan dokumentasi teknis 100% akurat.
6. **100% Automated Verification:** Seluruh perubahan lulus uji otomatis (`php artisan test`, `flutter analyze`, dan `flutter test`).

---

## 2. AI Agent Execution Protocol & Guardrails

AI Agent **WAJIB** mematuhi aturan operasional berikut selama mengeksekusi PRD ini:

1. **Eksekusi Berurutan per Epic:** Kerjakan `EP-01` $\rightarrow$ `EP-02` $\rightarrow$ `EP-03` $\rightarrow$ `EP-04` $\rightarrow$ `EP-05` $\rightarrow$ `EP-06`. Jangan melompat ke Epic berikutnya sebelum seluruh *Verification Commands* pada Epic aktif lulus tanpa error.
2. **Jangan Merusak Fitur Offline-First:** Siswa harus tetap dapat membuka dan mengedit catatan (`RuangCatatanPage`) serta menjalankan timer (`RuangFokusPage`) tanpa koneksi internet.
3. **Backward Compatibility & Graceful Migration:** Pengguna lama yang sudah memiliki data catatan/fokus di `SharedPreferences` tidak boleh kehilangan data saat aplikasi diperbarui. Sesi autentikasi lama di `SharedPreferences` harus dimigrasikan secara otomatis ke `FlutterSecureStorage` pada *cold start* pertama.
4. **Strict Null-Safety & Zero Analyzer Warnings:** Setiap perubahan kode Dart harus memenuhi standar Dart `^3.5.0` dan menghasilkan `No issues found!` pada perintah `flutter analyze`.
5. **No Hardcoded Secrets:** Jangan pernah menuliskan IP pengembangan lokal, *secret key*, atau *bypass token* secara permanen sebagai *default* konfigurasi produksi.

---

## 3. Repository & Architecture Baseline

```text
workspace_root/
├── ite/gara/                                      # Backend Laravel + Laravel Sanctum
│   ├── app/Http/Controllers/Api/
│   │   ├── ExamStatusController.php
│   │   ├── MobileAuthController.php
│   │   ├── MobileDataController.php
│   │   ├── MobileHandoffController.php
│   │   └── NotificationApiController.php
│   ├── routes/api.php
│   ├── routes/web.php
│   └── tests/Feature/
└── gara_flutter/                                  # Frontend Mobile Flutter (^3.5.0)
    ├── android/app/src/main/kotlin/com/example/gara_flutter/MainActivity.kt
    ├── ios/Runner/AppDelegate.swift
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

---

## 4. Detailed Functional & Technical Requirements

---

### EPIC 01 [P0]: Authentication & Handoff Bridge Security Hardening

#### 4.1.1 Enkripsi Penyimpanan Token Klien (`FlutterSecureStorage`)
* **Target Files:**
  * `gara_flutter/pubspec.yaml`
  * `gara_flutter/lib/services/auth_service.dart`
  * `gara_flutter/lib/utils/app_constants.dart`
  * `gara_flutter/lib/main.dart`
* **Current Flaw:** Token Sanctum disimpan sebagai *plaintext string* di `SharedPreferences` (`GaraPrefKeys.authToken`), yang dapat diekstrak melalui ADB backup atau perangkat *rooted*.
* **Technical Specification:**
  1. Tambahkan dependensi `flutter_secure_storage: ^9.2.2` pada `pubspec.yaml`.
  2. Buat wrapper/helper penyimpanan aman pada `AuthService` menggunakan `AndroidOptions(encryptedSharedPreferences: true)` dan `IOSOptions(accessibility: KeychainAccessibility.first_unlock_this_device)`.
  3. Implementasikan metode `migrateLegacyTokenIfNeeded()` yang dipanggil pada saat bootstrap di `main.dart`:
     * Cek apakah `SharedPreferences` masih memiliki nilai pada key `GaraPrefKeys.authToken`.
     * Jika ada, salin nilai tersebut ke `FlutterSecureStorage`, lalu segera hapus key tersebut dari `SharedPreferences` (`prefs.remove(GaraPrefKeys.authToken)`).
  4. Seluruh operasi baca/tulis/hapus token (`saveToken`, `getToken`, `clearSession`) wajib melalui `FlutterSecureStorage`.

#### 4.1.2 Penggantian URL Query Token dengan One-Time Short-Lived Handoff Ticket
* **Target Files:**
  * `ite/gara/app/Http/Controllers/Api/MobileHandoffController.php`
  * `ite/gara/routes/api.php`
  * `ite/gara/routes/web.php`
  * `gara_flutter/lib/services/auth_service.dart`
  * `gara_flutter/lib/utils/app_config.dart`
  * `gara_flutter/lib/widgets/hybrid_wrapper.dart`
* **Current Flaw:** Flutter membuka WebView ke `${baseUrl}/auth/webview-handoff?token=${token}&target=${targetPath}&mapel_id=${mapelId}`, mengekspos *plaintext* Sanctum token di URL query, *access logs*, dan header `Referer`. Selain itu, parameter `target` tidak divalidasi berbasis *role*.
* **Technical Specification (Backend Laravel):**
  1. Buat endpoint baru terautentikasi Sanctum: `POST /api/mobile/handoff-ticket` (diarahkan ke `MobileHandoffController@issueTicket`).
  2. **Request Payload (`application/json`):**
     ```json
     {
       "target": "/student/materi",
       "mapel_id": 12
     }
     ```
  3. **Validasi Role-Based Route Allowlist (WAJIB):**
     `MobileHandoffController` wajib memvalidasi bahwa nilai `target` diawali oleh prefix yang diizinkan untuk `auth()->user()->role`:
     * Role `siswa`: `['/student/', '/ruang-ujian/']`
     * Role `guru`: `['/guru/', '/teacher/']`
     * Role `operator`: `['/operator/']`
     * Role `kepsek`: `['/kepsek/', '/principal/']`
     * Role `admin`: `['/admin/', '/super-admin/']`
     * Tolak dengan HTTP `403 Forbidden` jika `target` mengandung skema eksternal (`http://`, `https://`, `//`), path traversal (`..`), atau berada di luar daftar putih peran pengguna.
  4. **Generasi Tiket Sekali Pakai:**
     * Hasilkan string acak kriptografis 64 karakter: `$ticket = bin2hex(random_bytes(32));`.
     * Simpan metadata ke Laravel Cache dengan TTL ketat **15 detik**:
       ```php
       Cache::put("handoff_ticket:{$ticket}", [
           'user_id' => $user->id,
           'role' => $user->role,
           'target' => $validatedTarget,
           'mapel_id' => $validatedMapelId,
           'ip' => $request->ip(),
       ], now()->addSeconds(15));
       ```
     * Kembalikan HTTP `200 OK`:
       ```json
       {
         "status": "success",
         "ticket": "<64_hex_chars>",
         "expires_in": 15
       }
       ```
  5. **Refactor Endpoint `GET /auth/webview-handoff` (`MobileHandoffController@consumeTicket`):**
     * Terima parameter `ticket` (bukan `token`).
     * Ambil dan **langsung hapus** tiket secara atomik menggunakan `$payload = Cache::pull("handoff_ticket:{$ticket}");`.
     * Jika `$payload` kosong/kedaluwarsa, kembalikan HTTP `401 Unauthorized` (atau redirect ke halaman sesi habis tanpa membuka akses).
     * Lakukan `Auth::loginUsingId($payload['user_id'])`, regenerasi session ID (`$request->session()->regenerate()`), simpan `active_mapel_id` di session jika ada, dan redirect ke `$payload['target']`.
     * Tambahkan peringatan log keamanan apabila endpoint masih menerima parameter `token` lama, dan tolak seluruh request yang membawa `token` di query string dengan HTTP `400 Bad Request`.
* **Technical Specification (Frontend Flutter):**
  1. Tambahkan method `Future<String?> requestHandoffTicket({required String targetPath, String? mapelId})` di `AuthService` yang memanggil `POST /api/mobile/handoff-ticket` dengan header `Authorization: Bearer <secure_token>`.
  2. Perbarui `HybridWrapper` agar sebelum memuat WebView, widget memanggil `requestHandoffTicket(...)`, lalu memuat URL:
     `${AppConfig.baseUrl}/auth/webview-handoff?ticket=$ticket`
  3. Hapus logika penyuntikan cookie `Authorization: Bearer $token` mentah dari `HybridWrapper` karena autentikasi sesi web kini sepenuhnya ditangani oleh PHP session cookie berbendera `HttpOnly` dan `Secure` hasil penukaran tiket.

* **Acceptance Criteria (`EP-01`):**
  * [ ] `SharedPreferences` tidak pernah menyimpan key `auth_token` setelah login maupun migrasi.
  * [ ] URL yang dimuat oleh `InAppWebView` tidak mengandung substring `token=`.
  * [ ] Tiket handoff gagal (HTTP 401) jika digunakan kedua kali (*replay attack*) atau setelah $> 15$ detik.
  * [ ] Siswa yang meminta tiket dengan `target: "/admin/users"` mendapat HTTP 403.

---

### EPIC 02 [P0]: WebView Sandbox & Cross-Platform Exam Kiosk Hardening

#### 4.2.1 Pengetatan Konfigurasi `InAppWebViewSettings` & Domain Whitelisting
* **Target Files:**
  * `gara_flutter/lib/widgets/hybrid_wrapper.dart`
  * `gara_flutter/lib/utils/app_config.dart`
* **Current Flaw:** `HybridWrapper` mengizinkan *mixed content* HTTP, akses file lokal (`allowFileAccessFromFileURLs: true`, `allowUniversalAccessFromFileURLs: true`), *pop-up window*, dan *context menu* salin-tempel selama ujian.
* **Technical Specification:**
  1. Ubah konfigurasi `InAppWebViewSettings` di `HybridWrapper` menjadi:
     ```dart
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
       allowFileAccessFromFileURLs: false,
       allowUniversalAccessFromFileURLs: false,
       allowFileAccess: false,
       allowContentAccess: false,
       mediaPlaybackRequiresUserGesture: false,
       allowsInlineMediaPlayback: true,
       javaScriptCanOpenWindowsAutomatically: false,
       transparentBackground: false,
       supportZoom: !isExam,
       thirdPartyCookiesEnabled: false,
       disableContextMenu: isExam,
       applicationNameForUserAgent: 'GARA_OFFICIAL_APP',
     );
     ```
  2. Pada callback `shouldOverrideUrlLoading`:
     * Izinkan skema `whatsapp://` dan `https://wa.me/` dialihkan ke aplikasi eksternal **HANYA JIKA** `isExam == false`. Saat `isExam == true`, blokir seluruh navigasi keluar aplikasi (`NavigationActionPolicy.CANCEL`).
     * Validasi host dari setiap URL HTTP/HTTPS terhadap `AppConfig.allowedDomains`. Blokir seluruh navigasi ke domain luar saat mode ujian aktif.

#### 4.2.2 Penutupan Pintu Belakang `SmartConnectPage` & Pembersihan Paket Usang
* **Target Files:**
  * `gara_flutter/lib/screens/pilih_mapel_page.dart`
  * `gara_flutter/lib/screens/smart_connect_page.dart`
  * `gara_flutter/pubspec.yaml`
* **Current Flaw:** Pengguna rilis produksi dapat mengetuk logo GARA 3D di `PilihMapelPage` untuk membuka `SmartConnectPage` dan mengganti server URL ke server penyerang. Selain itu, `pubspec.yaml` masih memuat `flutter_windowmanager: ^0.2.0` yang usang dan bentrok dengan `MainActivity.kt`.
* **Technical Specification:**
  1. Hapus `flutter_windowmanager: ^0.2.0` dari `gara_flutter/pubspec.yaml` dan pastikan tidak ada sisa *import* `package:flutter_windowmanager/...` di seluruh folder `lib/`.
  2. Di `PilihMapelPage`, bungkus gesture handler menuju `SmartConnectPage` dengan konstanta kompilasi Dart `kDebugMode`:
     ```dart
     onLongPress: kDebugMode
         ? () => Navigator.push(
               context,
               MaterialPageRoute(builder: (_) => const SmartConnectPage()),
             )
         : null,
     ```
  3. Tambahkan assertion di `SmartConnectPage.build()`: jika `kReleaseMode` bernilai `true`, segera tampilkan halaman kosong atau `Navigator.pop(context)`.

#### 4.2.3 Penguatan Anti-Cheat Native Android (`MainActivity.kt`) & Paritas iOS (`AppDelegate.swift`)
* **Target Files:**
  * `gara_flutter/android/app/src/main/kotlin/com/example/gara_flutter/MainActivity.kt`
  * `gara_flutter/ios/Runner/AppDelegate.swift`
  * `gara_flutter/lib/widgets/hybrid_wrapper.dart`
* **Current Flaw:** Kanal keamanan `com.lms.gara/security` hanya diimplementasikan di Android dan belum memeriksa kondisi *root*, *developer options*, maupun aplikasi *floating overlay* sebelum ujian dimulai. iOS tidak memiliki perlindungan perekaman layar atau deteksi keluar aplikasi.
* **Technical Specification (Android `MainActivity.kt`):**
  1. Pertahankan method `enableExamMode` (`WindowManager.LayoutParams.FLAG_SECURE`, immersive sticky mode, `startLockTask()`) dan `disableExamMode` (`stopLockTask()`, hapus `FLAG_SECURE`).
  2. Tambahkan method baru pada channel `com.lms.gara/security`: `checkDeviceIntegrity` yang mengembalikan `Map<String, Any>`:
     * `isRooted`: cek keberadaan binary `su` pada path standar (`/system/bin/su`, `/system/xbin/su`, `/sbin/su`) atau tag build `test-keys`.
     * `isAdbEnabled`: baca `Settings.Global.getInt(contentResolver, Settings.Global.ADB_ENABLED, 0) == 1`.
     * `canDrawOverlays`: pada API >= 23, periksa `Settings.canDrawOverlays(this)` untuk mendeteksi aplikasi *overlay* mengambang yang berpotensi dipakai menyontek.
* **Technical Specification (iOS `AppDelegate.swift`):**
  1. Daftarkan `FlutterMethodChannel(name: "com.lms.gara/security", binaryMessenger: controller.binaryMessenger)` pada `AppDelegate.swift`.
  2. Implementasikan handler untuk `enableExamMode`, `disableExamMode`, dan `checkDeviceIntegrity`:
     * Saat `enableExamMode` aktif, pantau `UIScreen.capturedDidChangeNotification` (deteksi *screen recording* / AirPlay mirroring) dan `UIApplication.willResignActiveNotification` (deteksi perpindahan aplikasi / *split view*).
     * Kirim event balik ke Flutter melalui `channel.invokeMethod("onSecurityViolation", ["reason": ...])` agar `HybridWrapper` menyembunyikan konten ujian dan mencatat insiden pelanggaran ke server.
* **Technical Specification (`HybridWrapper` Pre-Exam Gate):**
  * Sebelum memanggil `requestHandoffTicket` untuk rute ujian (`/ruang-ujian/arena`), panggil `checkDeviceIntegrity` via platform channel. Jika `isRooted == true` (pada build rilis) atau terdeteksi *screen capture* aktif di iOS, tampilkan dialog peringatan keamanan dan batalkan pemuatan halaman ujian.

* **Acceptance Criteria (`EP-02`):**
  * [ ] `flutter_windowmanager` tidak ada lagi di `pubspec.yaml` maupun `pubspec.lock`.
  * [ ] Build rilis (`flutter build apk --release`) tidak dapat mengakses `SmartConnectPage`.
  * [ ] Saat `isExam == true`, `disableContextMenu` bernilai `true`, `mixedContentMode` bernilai `NEVER_ALLOW`, dan `allowFileAccessFromFileURLs` bernilai `false`.
  * [ ] Kanal `com.lms.gara/security` terpasang dan merespons di Android maupun iOS tanpa `MissingPluginException`.

---

### EPIC 03 [P1]: Infrastructure Decoupling & Network Efficiency

#### 4.3.1 Dekoupling dari Free Hosting (`garaedu.gt.tc`) & Isolasi `InfinityBypassEngine`
* **Target Files:**
  * `gara_flutter/lib/utils/app_config.dart`
  * `gara_flutter/lib/services/infinity_api_client.dart`
  * `gara_flutter/lib/services/infinity_bypass_engine.dart`
  * `gara_flutter/lib/widgets/infinity_bypass_dialog.dart`
* **Current Flaw:** URL produksi di-*hardcode* ke `https://garaedu.gt.tc` dan seluruh trafik API bergantung pada modul *scraping cookie challenge* (`InfinityBypassEngine`), yang menyebabkan kegagalan massal saat ujian berlangsung bersamaan (*concurrency limit*) dan tidak diperlukan pada server VPS/Cloud standar.
* **Technical Specification:**
  1. Ubah `AppConfig` agar mendukung injeksi konfigurasi saat *build-time* melalui `String.fromEnvironment`:
     ```dart
     static const String _compiledBaseUrl = String.fromEnvironment(
       'GARA_BASE_URL',
       defaultValue: 'https://garaedu.gt.tc',
     );
     static const bool enableLegacyInfinityBypass = bool.fromEnvironment(
       'ENABLE_INFINITY_BYPASS',
       defaultValue: false,
     );
     ```
  2. Refactor klien HTTP utama agar secara *default* menggunakan klien REST JSON standar (`http.Client` dengan header `Accept: application/json`, `X-App: GARA_MOBILE`, dan *timeout* 15 detik).
  3. Jalankan `InfinityBypassEngine` **hanya jika** `AppConfig.enableLegacyInfinityBypass == true` ATAU respons HTTP awal mengembalikan pola khas tantangan JS InfinityFree (`aes.js` / `__test` cookie) saat mode kompatibilitas diaktifkan. Jangan pernah menampilkan `InfinityBypassDialog` pada infrastruktur server produksi bersih.

#### 4.3.2 Optimasi Polling Notifikasi & Timeout API Eksternal (`SholatWidget`)
* **Target Files:**
  * `gara_flutter/lib/screens/dashboard_page.dart`
  * `gara_flutter/lib/services/notification_service.dart`
  * `gara_flutter/lib/widgets/dashboard/sholat_widget.dart`
* **Current Flaw:** `DashboardPage` melakukan HTTP *polling* setiap 60 detik tanpa memperhatikan siklus hidup aplikasi (`AppLifecycleState`), menguras baterai dan membebani server. `SholatWidget` memanggil Aladhan API tanpa batas *timeout* ketat.
* **Technical Specification:**
  1. Pada `DashboardPage`, gunakan `WidgetsBindingObserver`:
     * Hentikan timer *polling* saat `AppLifecycleState.paused` atau `inactive`.
     * Picu pemanggilan `getUnreadCount()` segera saat aplikasi kembali ke `AppLifecycleState.resumed`.
     * Terapkan interval adaptif (minimal 180 detik saat aktif di latar depan, dan *exponential backoff* hingga 600 detik jika terjadi kegagalan jaringan berturut-turut).
  2. Pada `SholatWidget`, tambahkan `.timeout(const Duration(seconds: 4))` pada panggilan Aladhan API. Jika gagal atau *timeout*, langsung muat jadwal sholat terakhir dari cache `SharedPreferences` tanpa menampilkan *loading spinner* tak terbatas.

* **Acceptance Criteria (`EP-03`):**
  * [ ] Kompilasi dengan `--dart-define=GARA_BASE_URL=https://api.sekolah.sch.id` mengarahkan seluruh endpoint API dan WebView tanpa memicu `InfinityBypassEngine`.
  * [ ] Timer notifikasi berhenti otomatis saat aplikasi berada di *background*.
  * [ ] `SholatWidget` merender data cache dalam waktu $< 4$ detik saat koneksi ke `api.aladhan.com` diblokir.

---

### EPIC 04 [P2]: Local Storage Migration (`SharedPreferences` $\rightarrow$ SQLite)

#### 4.4.1 Skema Database Relasional Lokal (`GaraLocalDatabase`)
* **Target Files:**
  * `gara_flutter/pubspec.yaml`
  * `gara_flutter/lib/services/local_database_service.dart` (baru)
  * `gara_flutter/lib/services/notes_service.dart`
  * `gara_flutter/lib/services/focus_service.dart`
  * `gara_flutter/lib/models/note_model.dart`
  * `gara_flutter/lib/models/focus_model.dart`
* **Current Flaw:** Seluruh catatan disimpan sebagai satu string JSON besar pada key `'garuda_akademi_ruang_catatan'` dan statistik fokus pada `'garuda_akademi_ruang_fokus'`. Penyimpanan otomatis (*debounced* 800ms) di `NoteEditorPage` memicu serialisasi ulang seluruh catatan dan berisiko merusak JSON jika proses terhenti.
* **Technical Specification:**
  1. Tambahkan `sqflite: ^2.3.3` dan `path: ^1.9.0` pada `pubspec.yaml`.
  2. Buat singleton `LocalDatabaseService` yang mengelola database `gara_offline.db` (versi `1`) dengan skema berikut:
     ```sql
     CREATE TABLE notes (
       id TEXT PRIMARY KEY,
       title TEXT NOT NULL,
       category TEXT NOT NULL,
       created_at TEXT NOT NULL,
       updated_at TEXT NOT NULL
     );

     CREATE TABLE note_blocks (
       id INTEGER PRIMARY KEY AUTOINCREMENT,
       note_id TEXT NOT NULL,
       sort_order INTEGER NOT NULL,
       type TEXT NOT NULL,
       content TEXT NOT NULL,
       checked INTEGER NOT NULL DEFAULT 0,
       FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
     );

     CREATE INDEX idx_notes_category_updated ON notes(category, updated_at DESC);
     CREATE INDEX idx_note_blocks_note_id ON note_blocks(note_id, sort_order ASC);

     CREATE TABLE focus_meta (
       id INTEGER PRIMARY KEY CHECK (id = 1),
       focus_duration INTEGER NOT NULL DEFAULT 25,
       short_break INTEGER NOT NULL DEFAULT 5,
       long_break INTEGER NOT NULL DEFAULT 15,
       total_sessions INTEGER NOT NULL DEFAULT 0,
       total_focus_minutes INTEGER NOT NULL DEFAULT 0,
       current_streak INTEGER NOT NULL DEFAULT 0,
       longest_streak INTEGER NOT NULL DEFAULT 0,
       last_focus_date TEXT,
       plant_water_points INTEGER NOT NULL DEFAULT 0,
       plant_level INTEGER NOT NULL DEFAULT 1
     );

     CREATE TABLE focus_history (
       focus_date TEXT PRIMARY KEY,
       completed_sessions INTEGER NOT NULL DEFAULT 0
     );
     ```
  3. **Idempotent One-Time Migration:**
     * Saat `LocalDatabaseService.database` pertama kali diinisialisasi, jalankan transaksi SQLite untuk membaca string JSON lama dari `SharedPreferences` (`'garuda_akademi_ruang_catatan'` dan `'garuda_akademi_ruang_fokus'`).
     * Jika ada, masukkan seluruh baris ke tabel SQLite di atas. Setelah transaksi SQLite berhasil `COMMIT`, hapus kedua key lama dari `SharedPreferences` dan simpan penanda `sqlite_migration_v1_done = true`.
  4. Perbarui `NotesService` agar operasi `saveNote(NoteModel note)` hanya melakukan `INSERT OR REPLACE` pada baris `notes` dan `note_blocks` milik `note.id` tersebut (bukan menulis ulang seluruh daftar catatan), serta sediakan query pencarian SQL (`WHERE title LIKE ? OR id IN (SELECT note_id FROM note_blocks WHERE content LIKE ?)`).

* **Acceptance Criteria (`EP-04`):**
  * [ ] Data lama dari `SharedPreferences` termigrasi 100% ke tabel SQLite pada *startup* pertama tanpa duplikasi jika aplikasi di-restart.
  * [ ] Mengedit 1 catatan pada koleksi berisi 500 catatan hanya mengeksekusi transaksi SQL pada 1 catatan tersebut dalam waktu $< 20\text{ ms}$.

---

### EPIC 05 [P2]: Codebase Deduplication, Dead Code Pruning & Graph/Docs Sync

#### 4.5.1 Konsolidasi Modul Duplikat Flutter
* **Target Files:** Seluruh berkas duplikat yang terdeteksi di komunitas graf (`AppConfig`, `GaraColors`, `AuthService`, `NoteModel`, `FocusService`, `SholatWidget`/`PrayerTimesWidget`, `PengumumanModel`).
* **Current Flaw:** Graf kode mendeteksi pasangan komunitas terpisah untuk abstraksi inti yang sama (misalnya `AppConfig` di komunitas 22 & 24, `AuthService` di komunitas 118 & 121), yang berisiko menyebabkan *bug* perilaku tidak konsisten antar-halaman.
* **Technical Specification:**
  1. Lakukan pencarian berkas duplikat atau kelas kembar pada `gara_flutter/lib/`.
  2. Tetapkan satu berkas kanonik per domain:
     * `lib/utils/app_config.dart` (`AppConfig`)
     * `lib/utils/app_constants.dart` (`GaraColors`, `GaraRoutes`, `GaraPrefKeys`)
     * `lib/services/auth_service.dart` (`AuthService`)
     * `lib/services/notes_service.dart` (`NotesService`)
     * `lib/services/focus_service.dart` (`FocusService`)
     * `lib/models/note_model.dart` (`NoteModel`)
     * `lib/models/pengumuman_model.dart` (`PengumumanModel`)
     * `lib/widgets/dashboard/sholat_widget.dart` (`SholatWidget`)
  3. Perbarui seluruh pernyataan `import` di aplikasi agar menunjuk ke berkas kanonik dan hapus berkas salinan/usang yang tidak terpakai.

#### 4.5.2 Pembersihan Isolated/Dead Nodes & Konfigurasi `.graphifyignore`
* **Target Files:**
  * `.graphifyignore` (baru di root repositori)
  * `gara_flutter/lib/**` (berkas dengan *dead code* terkonfirmasi)
  * `ARCHITECTURE.md` (menggantikan duplikat `LAPORAN DOKUMENTASI.md` dan `dokumflut.md`)
* **Technical Specification:**
  1. Buat berkas `.graphifyignore` pada root proyek untuk mengecualikan bundel aset pihak ketiga yang mendominasi graf (`public/vendor/**`, `public/build/**`, `node_modules/**`, `vendor/**`, `gara_flutter/build/**`, `gara_flutter/.dart_tool/**`, `gara_flutter/windows/**`, `gara_flutter/linux/**`).
  2. Audit simbol yang tidak direferensikan di dalam `gara_flutter/lib/` menggunakan `dart analyze` dan hapus fungsi/variabel yatim (*dead code*) yang tidak lagi digunakan.
  3. Hapus duplikasi antara `LAPORAN DOKUMENTASI.md` dan `dokumflut.md` dengan menyatukannya ke dalam `ARCHITECTURE.md` yang diperbarui:
     * Perbarui pohon direktori agar mencakup `lib/screens/`, `SmartConnectPage`, `ActivationPage`, `PengumumanService`, `DiskusiService`, dan `LocalDatabaseService`.
     * Perbaiki seluruh tautan berkas yang rusak (`[main.dart](file:/`) menjadi tautan relatif valid (`[main.dart](gara_flutter/lib/main.dart)`).
     * Dokumentasikan alur *One-Time Handoff Ticket*, penyimpanan `FlutterSecureStorage`, dan skema `SQLite`.
  4. Jalankan ulang `graphify update .` untuk meregenerasi `GRAPH_REPORT_FLUTTER.md` yang bersih.

* **Acceptance Criteria (`EP-05`):**
  * [ ] Tidak ada definisi kelas ganda untuk `AppConfig`, `GaraColors`, `AuthService`, `NoteModel`, dan `FocusService`.
  * [ ] `GRAPH_REPORT_FLUTTER.md` yang baru tidak lagi menampilkan fungsi *minified* vendor (`tn`, `n()`, `s()`) sebagai *God Nodes* proyek.
  * [ ] Seluruh tautan berkas di dalam dokumentasi arsitektur valid dan mengarah ke berkas yang eksis.

---

### EPIC 06 [P1/P2]: Automated Security, Contract & Migration Test Suite

#### 4.6.1 Laravel Feature & Security Contract Tests
* **Target Files:**
  * `ite/gara/tests/Feature/MobileHandoffSecurityTest.php` (baru)
  * `ite/gara/tests/Feature/MobileAuthContractTest.php` (baru)
* **Technical Specification:**
  Implementasikan pengujian PHPUnit/Pest yang memverifikasi:
  1. `test_handoff_ticket_can_be_issued_and_consumed_once`: Memastikan `POST /api/mobile/handoff-ticket` mengembalikan tiket 64-karakter, konsumsi pertama berhasil login dan redirect ke target, dan konsumsi kedua dengan tiket yang sama gagal (HTTP 401).
  2. `test_handoff_ticket_expires_after_15_seconds`: Menggunakan `Carbon::setTestNow()` untuk memajukan waktu 16 detik dan memastikan tiket ditolak.
  3. `test_handoff_rejects_unauthorized_role_target`: Memastikan pengguna ber-role `siswa` menerima HTTP 403 saat meminta tiket untuk `/guru/dashboard` atau `/admin/settings`, serta menolak URL eksternal `https://evil.com`.
  4. `test_legacy_query_token_handoff_is_rejected`: Memastikan `GET /auth/webview-handoff?token=plaintext` ditolak dengan HTTP 400.

#### 4.6.2 Flutter Unit & Migration Tests
* **Target Files:**
  * `gara_flutter/test/services/auth_migration_test.dart` (baru)
  * `gara_flutter/test/services/sqlite_migration_test.dart` (baru)
  * `gara_flutter/test/utils/app_config_security_test.dart` (baru)
* **Technical Specification:**
  Implementasikan pengujian `flutter_test` yang memverifikasi:
  1. Migrasi token dari `SharedPreferences` ke mock `FlutterSecureStorage` memindahkan nilai secara akurat dan menghapus key dari `SharedPreferences`.
  2. Migrasi JSON `garuda_akademi_ruang_catatan` dan `garuda_akademi_ruang_fokus` ke database SQLite menghasilkan jumlah baris, blok catatan, dan angka *streak* yang identik, serta bersifat idempoten.
  3. Konfigurasi default `AppConfig.enableLegacyInfinityBypass` bernilai `false` dan validator domain menolak domain di luar `allowedDomains`.

---

## 5. Master Verification Checklist (Run Before Completion)

AI Agent wajib menjalankan seluruh perintah berikut secara berurutan dan memastikan **0 errors / 0 failures**:

```bash
# 1. Backend Laravel Verification
cd ite/gara
php artisan config:clear
php artisan test --filter=Mobile

# 2. Frontend Flutter Static Analysis & Unit Tests
cd ../../gara_flutter
flutter pub get
flutter analyze
flutter test

# 3. Knowledge Graph Regeneration
cd ..
graphify update .
```

### Final Sign-Off Criteria
* [ ] Seluruh item pada `EP-01` s.d. `EP-06` terimplementasi lengkap tanpa *TODO* atau *stub* kosong.
* [ ] `php artisan test` lulus 100%.
* [ ] `flutter analyze` menghasilkan `No issues found!`.
* [ ] `flutter test` lulus 100%.
* [ ] `GRAPH_REPORT_FLUTTER.md` dan `ARCHITECTURE.md` telah diperbarui dan sinkron dengan *commit* terbaru.