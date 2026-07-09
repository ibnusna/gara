# 📱 Dokumentasi Teknis Resmi
## Proyek GARA — Aplikasi Android Native (Kotlin)
### Laporan Progres Tahap 1: Integrasi WebView & Sesi Persistensi

---

> **Status Proyek:** ✅ Tahap 1 Selesai  
> **Versi Aplikasi:** `3.1` (versionCode: 3)  
> **Tanggal Laporan:** 23 April 2026  
> **Platform:** Android (Native Kotlin)  
> **Package ID:** `com.lms.gara`  
> **Penyusun Dokumentasi:** Tim Pengembang GARA

---

## Daftar Isi

1. [Gambaran Umum Proyek](#1-gambaran-umum-proyek)
2. [Arsitektur Sistem & Ekosistem GARA](#2-arsitektur-sistem--ekosistem-gara)
3. [Lingkungan Pengembangan & Konfigurasi Build](#3-lingkungan-pengembangan--konfigurasi-build)
4. [Struktur Direktori Proyek](#4-struktur-direktori-proyek)
5. [Komponen Inti: Activity & Layar](#5-komponen-inti-activity--layar)
6. [Konfigurasi WebView & Integrasi Backend](#6-konfigurasi-webview--integrasi-backend)
7. [JavaScript Bridge: WebAppInterface](#7-javascript-bridge-webappinterface)
8. [Sistem Manajemen Sesi: SharedPreferences & LocalStorage](#8-sistem-manajemen-sesi-sharedpreferences--localstorage)
9. [Modul Mode Ujian (Exam Mode)](#9-modul-mode-ujian-exam-mode)
10. [Assistive FAB: Antarmuka Kontrol Dinamis](#10-assistive-fab-antarmuka-kontrol-dinamis)
11. [Manajemen Navigasi & Interceptor URL](#11-manajemen-navigasi--interceptor-url)
12. [Download Manager & Penanganan File](#12-download-manager--penanganan-file)
13. [Halaman Error Interaktif (eror.html)](#13-halaman-error-interaktif-erorhtml)
14. [Konfigurasi Izin & Manifes Android](#14-konfigurasi-izin--manifes-android)
15. [Theming & Sistem Desain](#15-theming--sistem-desain)
16. [Alur Pengembangan Iteratif (Riwayat Versi)](#16-alur-pengembangan-iteratif-riwayat-versi)
17. [Ringkasan Capaian & Rencana Tahap 2](#17-ringkasan-capaian--rencana-tahap-2)

---

## 1. Gambaran Umum Proyek

**GARA** *(Garuda Akademi)* adalah platform Learning Management System (LMS) yang dirancang untuk ekosistem pendidikan formal di Indonesia. Platform ini terdiri dari dua lapisan utama:

- **Backend Laravel**: Sistem web yang mengelola data akademik, ujian, materi pembelajaran, dan manajemen pengguna di berbagai peran (Siswa, Guru, Operator, Kepsek, Super Admin). Domain utama: `garudakademi.ct.ws`.
- **Frontend Android (Kotlin)**: Aplikasi native Android yang berfungsi sebagai *shell* terpadu untuk mengakses platform melalui WebView berfitur penuh, dengan nilai tambah berupa kemampuan native yang tidak tersedia di browser biasa.

### Filosofi Arsitektur: Hybrid Native–Web

Pendekatan **Hybrid WebView** dipilih karena beberapa alasan strategis:

| Keputusan | Rationale |
|---|---|
| Gunakan WebView sebagai inti | Backend Laravel sudah stabil & kaya fitur; tidak perlu membangun ulang semua UI di Android. |
| Tambahkan lapisan native | Untuk fitur yang mustahil di browser: kunci layar ujian, pengamatan volume, download blob, FAB draggable. |
| Interceptor URL cerdas | Untuk menjaga pengalaman pengguna konsisten (link eksternal → Custom Tab, bukan meninggalkan app). |
| Persistensi sesi native | SharedPreferences digunakan untuk mengingat URL terakhir & status ujian, agar sesi tidak hilang setelah app di-restart. |

---

## 2. Arsitektur Sistem & Ekosistem GARA

```
┌─────────────────────────────────────────────────────────────────┐
│             EKOSISTEM PLATFORM GARA                             │
├────────────────────┬────────────────────────────────────────────┤
│  ANDROID APP       │  BACKEND LARAVEL                           │
│  (com.lms.gara)    │  (garudakademi.ct.ws)                      │
│                    │                                            │
│  SplashActivity    │  ┌─────────────────────────────────────┐  │
│       │            │  │  Multi-Role Web Application         │  │
│       ▼            │  │  - Siswa    /siswa/...              │  │
│  MainActivity ─────┼──┤  - Guru     /guru/...               │  │
│  ┌─────────────┐   │  │  - Operator /operator/...           │  │
│  │  WebView    │◄──┼──┤  - Kepsek   /kepsek/...             │  │
│  │  Engine     │───┼──►  - SuperAdmin/superadmin/...         │  │
│  └─────────────┘   │  └─────────────────────────────────────┘  │
│  ┌─────────────┐   │                                            │
│  │ JS Bridge   │   │  Exam System (garudakademi.netlify.app)    │
│  │ (Interface) │   │  - Static Netlify Host untuk ujian online  │
│  └─────────────┘   │                                            │
│  ┌─────────────┐   │  Local Exam Server (IP Dinamis)            │
│  │SharedPrefs  │   │  - Diakses via URL input (jaringan lokal)  │
│  │ + Cookies   │   │                                            │
│  └─────────────┘   │                                            │
└────────────────────┴────────────────────────────────────────────┘
```

### Alur Data Utama

```
Pengguna buka app
       │
       ▼
SplashActivity (1.5 detik)
       │
       ▼
MainActivity.onCreate()
       │
       ├─► Baca SharedPreferences (status ujian + URL terakhir)
       │
       ├─► setupWebView() ─────────────────────────────────────────►
       │         └── WebSettings (JS enabled, DOM Storage, dll)    │
       │         └── CookieManager (accept + 3rd party cookies)    │
       │         └── WebChromeClient (dialog JS, file chooser)     │
       │         └── WebViewClient (URL interceptor, error handler)│
       │                                                           │
       ├─► setupAssistiveFab() (FAB Ujian)                         │
       │                                                           │
       └─► webView.loadUrl(mainHomeUrl / lastUrl) ◄────────────────┘
```

---

## 3. Lingkungan Pengembangan & Konfigurasi Build

### Spesifikasi Teknis

| Parameter | Nilai |
|---|---|
| **Bahasa Pemrograman** | Kotlin (JVM Target: 11) |
| **IDE** | Android Studio |
| **Build System** | Gradle (Kotlin DSL - `.gradle.kts`) |
| **Compile SDK** | API 36 (Android 16) |
| **Target SDK** | API 35 (Android 15) |
| **Min SDK** | API 24 (Android 7.0 Nougat) |
| **Java Compatibility** | `JavaVersion.VERSION_11` |

### File: `app/build.gradle.kts`

```kotlin
android {
    namespace = "com.lms.gara"
    compileSdk = 36

    defaultConfig {
        applicationId = "com.lms.gara"
        minSdk = 24
        targetSdk = 35
        versionCode = 3
        versionName = "3.1"
    }
    signingConfigs {
        create("release") {
            storeFile = file("../gara-keystore.jks")
            
        }
    }
    buildTypes {
        release {
            isMinifyEnabled = true      
            isShrinkResources = true    
        }
    }
    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_11
        targetCompatibility = JavaVersion.VERSION_11
    }
    kotlinOptions {
        jvmTarget = "11"
    }
}
```

### Dependensi Library

```kotlin
dependencies {
    
    implementation(libs.androidx.core.ktx)
    implementation(libs.androidx.appcompat)

    
    implementation(libs.material)

    
    implementation(libs.androidx.activity)
    implementation(libs.androidx.constraintlayout)

    
    implementation("androidx.swiperefreshlayout:swiperefreshlayout:1.2.0")

    
    implementation("androidx.browser:browser:1.8.0")
}
```

**Catatan Library:**
- `swiperefreshlayout`: Mendukung fitur *pull-to-refresh* pada WebView.
- `browser:1.8.0`: Memungkinkan pembukaan URL eksternal dalam Chrome Custom Tab (tampilan in-app yang elegan, bukan meninggalkan aplikasi sepenuhnya).

---

## 4. Struktur Direktori Proyek

```
Gara/
├── app/
│   ├── build.gradle.kts                 ← Konfigurasi build & dependensi
│   ├── proguard-rules.pro               ← Aturan R8/ProGuard untuk release
│   └── src/
│       └── main/
│           ├── AndroidManifest.xml      ← Deklarasi komponen & izin
│           ├── ic_launcher-playstore.png
│           ├── assets/
│           │   └── helpers/
│           │       ├── eror.html        ← Halaman error koneksi (interaktif)
│           │       └── exam.png         ← Ikon kustom FAB mode ujian
│           ├── java/
│           │   └── com/lms/gara/
│           │       ├── MainActivity.kt  ← Aktivitas utama (WebView + semua logika)
│           │       └── SplashActivity.kt ← Layar pembuka 1.5 detik
│           └── res/
│               ├── drawable/            ← Aset drawable (cover, ikon, dll)
│               ├── layout/
│               │   ├── activity_main.xml    ← Layout utama (WebView + FAB + Blackout)
│               │   └── activity_splash.xml  ← Layout splash screen
│               ├── mipmap-*/            ← Ikon aplikasi berbagai densitas
│               ├── values/
│               │   ├── colors.xml       ← Palet warna
│               │   ├── strings.xml      ← String resource (internalisasi teks)
│               │   └── themes.xml       ← Definisi tema Material3
│               ├── values-night/        ← Overrride tema malam
│               └── xml/                 ← Konfigurasi tambahan (backup rules, dll)
├── build.gradle.kts                     ← Konfigurasi Gradle root
├── settings.gradle.kts                  ← Konfigurasi modul & repositori
├── gradle.properties                    ← Properti JVM & flag AndroidX
├── gradlew / gradlew.bat                ← Gradle wrapper script
├── gara-keystore.jks                    ← (Bukan di VCS) Keystore untuk release
└── dokumentasi.md                       ← ← File ini
```

---

## 5. Komponen Inti: Activity & Layar

Aplikasi GARA terdiri dari **dua Activity**:

### 5.1 `SplashActivity.kt`

**Versi:** `1.1 (Package Fix)`  
**Path:** `app/src/main/java/com/lms/gara/SplashActivity.kt`

**Peran:** Titik masuk aplikasi — menampilkan branding visual selama 1.500 milidetik sebelum meluncurkan `MainActivity`.

```kotlin
@SuppressLint("CustomSplashScreen")
class SplashActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash)

        Handler(Looper.getMainLooper()).postDelayed({
            startActivity(Intent(this@SplashActivity, MainActivity::class.java))
            finish()    
        }, 1500)        
    }
}
```

**Poin desain:**
- Anotasi `@SuppressLint("CustomSplashScreen")` digunakan karena implementasi dilakukan secara manual (kustom), bukan menggunakan `SplashScreen API` bawaan Android 12+.
- `finish()` dipanggil setelah `startActivity()` untuk memastikan SplashActivity tidak ada di back stack, sehingga tombol *Back* dari `MainActivity` langsung keluar dari aplikasi.
- Layout menggunakan `FrameLayout` dengan `android:fitsSystemWindows="true"` agar konten tidak bertabrakan dengan status bar sistem.

**Layout `activity_splash.xml`:**
```xml
<FrameLayout
    android:fitsSystemWindows="true"
    android:background="#FFFFFF">
    <ImageView
        android:id="@+id/img_cover"
        android:scaleType="centerCrop"
        android:src="@drawable/cover" />
</FrameLayout>
```

---

### 5.2 `MainActivity.kt`

**Versi:** `1.6.7 (Liquid Glass FAB & Exam Icon)`  
**Path:** `app/src/main/java/com/lms/gara/MainActivity.kt`

Activity utama. Seluruh logika aplikasi dikonsolidasikan di sini, meliputi:
- Konfigurasi & rendering WebView
- Intercept & routing URL
- Manajemen Mode Ujian
- Assistive FAB (draggable, expandable)
- Download manager
- Navigasi kembali (back press)
- Sinkronisasi scroll dengan swipe-refresh

**Properti State Utama:**

```kotlin
class MainActivity : AppCompatActivity() {
    
    private lateinit var webView: WebView
    private lateinit var swipeRefreshLayout: SwipeRefreshLayout
    private lateinit var fullscreenContainer: FrameLayout
    private lateinit var blackoutView: View

    
    private lateinit var fabContainer: View
    private lateinit var fabMain: FloatingActionButton
    private lateinit var fabSubExit: FloatingActionButton
    private lateinit var fabSubRefresh: FloatingActionButton
    private var isFabMenuOpen = false

    
    private var isExamMode = false
    private var volumeObserver: VolumeObserver? = null
    private var localExamIp: String? = null

    
    private val PREF_NAME = "com.lms.gara.prefs"
    private val KEY_IS_EXAM_ACTIVE = "exam_status"
    private val KEY_LAST_URL = "saved_exam_url"

    
    private val homeKeyword = "garudakademi.ct.ws"
    private val examKeyword = "garudakademi.netlify.app"
    private val mainHomeUrl = "http://garudakademi.ct.ws/"
    private val thirdPartyKeywords = arrayOf("forms.gle", "docs.google.com/forms", "tally.so")
    private val googleLoginKeywords = arrayOf("accounts.google.com", "accounts.youtube.com")
}
```

---

## 6. Konfigurasi WebView & Integrasi Backend

### 6.1 Pengaturan WebSettings

Fungsi `setupWebView()` bertanggung jawab mengonfigurasi engine WebView sebelum halaman pertama dimuat.

```kotlin
@SuppressLint("SetJavaScriptEnabled", "ClickableViewAccessibility")
private fun setupWebView() {
    val settings = webView.settings

    
    
    settings.userAgentString += " GARA_OFFICIAL_APP"

    
    settings.javaScriptEnabled = true        
    settings.domStorageEnabled = true        
    settings.allowFileAccess = true          
    settings.allowContentAccess = true       
    settings.databaseEnabled = true          

    
    settings.setSupportZoom(false)
    settings.builtInZoomControls = false
    settings.displayZoomControls = false

    
    settings.cacheMode = WebSettings.LOAD_DEFAULT

    
    settings.mediaPlaybackRequiresUserGesture = false

    
    webView.setLayerType(View.LAYER_TYPE_HARDWARE, null)
}
```

**Penjelasan `domStorageEnabled = true`:**
> Ini adalah pengaturan kunci untuk tujuan utama Tahap 1. Dengan `domStorageEnabled = true`, WebView Android mengaktifkan akses ke API `window.localStorage` dan `window.sessionStorage` di dalam halaman web yang dimuat. Semua data yang tersimpan oleh kode JavaScript di halaman backend Laravel (misalnya: token sesi, preferensi UI, status pengerjaan soal) akan **dipersistensikan secara transparan** oleh sistem Android, sama seperti perilaku di browser desktop. Tanpa ini, `localStorage.setItem()` akan gagal secara diam-diam, menyebabkan logout otomatis setiap kali halaman di-refresh.

**Custom User-Agent:**
Penambahan ` GARA_OFFICIAL_APP` pada User-Agent adalah mekanisme "握手" (handshake) sederhana antara app Android dan backend. Backend Laravel dapat mendeteksi nilai ini dan secara kondisional:
- Menyembunyikan elemen UI yang tidak relevan di mobile (mis. sidebar navigasi desktop)
- Mengizinkan/memblokir fitur tertentu
- Mencatat log akses berbasis platform

### 6.2 CookieManager

```kotlin
val cookieManager = CookieManager.getInstance()
cookieManager.setAcceptCookie(true)                         
cookieManager.setAcceptThirdPartyCookies(webView, true)     
```

Cookie third-party diperlukan untuk alur ujian yang melibatkan Google Forms (`forms.gle`) dan Tally (`tally.so`). Cookie di-flush ke disk pada setiap `onPause()` dan setelah setiap halaman selesai dimuat (`onPageFinished`).

### 6.3 Long-Press & User-Select

Untuk mencegah aksi copy-paste yang tidak diinginkan (terutama saat ujian), dua mekanisme diimplementasikan:

```kotlin

webView.setOnLongClickListener {
    val hr = webView.hitTestResult
    hr.type != WebView.HitTestResult.EDIT_TEXT_TYPE
}


view?.evaluateJavascript("""
    (function() {
        var style = document.createElement('style');
        style.innerHTML = 'body { -webkit-user-select: none; -webkit-touch-callout: none; }
                           input, textarea, [contenteditable] { -webkit-user-select: text; }';
        document.head.appendChild(style);
    })();
""".trimIndent(), null)
```

CSS diinjeksikan via `evaluateJavascript()` setiap kali `onPageFinished` terpanggil, memastikan proteksi berlaku di setiap halaman secara otomatis.

---

## 7. JavaScript Bridge: WebAppInterface

`WebAppInterface` adalah kelas inner yang didekorasi dengan `@JavascriptInterface`, memungkinkan kode JavaScript di halaman web memanggil fungsi Kotlin secara langsung.

```kotlin
webView.addJavascriptInterface(WebAppInterface(), "AndroidInterface")
```

Setelah baris ini dieksekusi, objek `window.AndroidInterface` tersedia di seluruh halaman web yang dimuat oleh WebView.

### Metode yang Tersedia

| Method | Dipanggil dari JS | Fungsi |
|---|---|---|
| `retryLastUrl()` | `window.AndroidInterface.retryLastUrl()` | Memuat ulang URL terakhir yang tersimpan di SharedPreferences (digunakan oleh `eror.html`). |
| `saveNewBaseUrl(newUrl)` | `window.AndroidInterface.saveNewBaseUrl(url)` | Menyimpan URL server lokal ke SharedPreferences dan langsung navigasi ke URL tersebut. |
| `saveBase64File(base64Data, fileName, mimeType)` | `window.AndroidInterface.saveBase64File(...)` | Mendekodekan string Base64 dan menyimpannya sebagai file PDF ke folder Downloads. |

### Implementasi Lengkap

```kotlin
inner class WebAppInterface {

    @JavascriptInterface
    fun retryLastUrl() {
        runOnUiThread {
            webView.stopLoading()
            val lastUrl = getSharedPreferences(PREF_NAME, MODE_PRIVATE)
                .getString(KEY_LAST_URL, mainHomeUrl)!!
            webView.loadUrl(lastUrl)
        }
    }

    @JavascriptInterface
    fun saveNewBaseUrl(newUrl: String) {
        runOnUiThread {
            localExamIp = newUrl
            getSharedPreferences(PREF_NAME, MODE_PRIVATE)
                .edit().putString(KEY_LAST_URL, newUrl).apply()
            webView.stopLoading()
            webView.loadUrl(newUrl)
        }
    }

    @JavascriptInterface
    fun saveBase64File(base64Data: String, fileName: String, mimeType: String) {
        saveBase64ToFile(base64Data, fileName)
    }
}
```

> **Keamanan:** Semua operasi UI yang dipanggil melalui `@JavascriptInterface` **wajib** dibungkus `runOnUiThread { }`. Ini karena callback `@JavascriptInterface` dipanggil dari thread JavaScript (bukan Main Thread Android), dan semua modifikasi UI hanya boleh dilakukan dari Main Thread.

---

## 8. Sistem Manajemen Sesi: SharedPreferences & LocalStorage

Tahap 1 berfokus pada dua lapisan persistensi sesi yang saling melengkapi:

### 8.1 LocalStorage WebView (DOM Storage)
**Dikelola oleh:** Browser engine Android (WebKit/Chromium)  
**Diakses oleh:** Kode JavaScript di halaman web backend  
**Diaktifkan via:** `settings.domStorageEnabled = true`  

Data yang disimpan di `localStorage` oleh JavaScript di `garudakademi.ct.ws` akan tetap ada meskipun WebView di-refresh. Ini memastikan:
- Status login pengguna tetap terjaga
- Preferensi tampilan (tema, bahasa) tersimpan
- Draft pekerjaan siswa tidak hilang saat halaman di-reload

### 8.2 SharedPreferences Native (Kotlin)
**Dikelola oleh:** Kode native Kotlin di `MainActivity`  
**Scope:** Lintas sesi aplikasi (bertahan setelah app di-restart)

```kotlin
private val PREF_NAME = "com.lms.gara.prefs"
```

**Data yang Dipersistensikan:**

| Key | Tipe | Nilai Default | Fungsi |
|---|---|---|---|
| `exam_status` | `Boolean` | `false` | Apakah sesi ujian sedang aktif |
| `saved_exam_url` | `String` | `"http://garudakademi.ct.ws/"` | URL terakhir yang dikunjungi saat mode ujian |

**Alur Pemulihan Sesi:**

```
onCreate() dipanggil
       │
       ▼
Baca SharedPreferences
       │
       ├─── isExamMode = true? ──► loadUrl(lastUrl)
       │                                │
       │                                ▼
       │                          activateExamMode() ─► FLAG_SECURE, Lock Volume, startLockTask()
       │
       └─── isExamMode = false? ──► loadUrl(mainHomeUrl)
```

Mekanisme ini critical untuk **pemulihan sesi ujian**. Jika siswa tidak sengaja menekan tombol Home atau aplikasi crash karena low memory, saat membuka kembali aplikasi, state ujian akan dipulihkan secara otomatis berdasarkan nilai yang tersimpan di SharedPreferences.

### 8.3 Cookie Persistence

Cookie sesi dari browser (Laravel session cookie) di-flush secara eksplisit ke penyimpanan persisten:

```kotlin

override fun onPageFinished(view: WebView?, url: String?) {
    CookieManager.getInstance().flush()
    
    if (url != null && !url.startsWith("file:/
        getSharedPreferences(PREF_NAME, MODE_PRIVATE)
            .edit().putString(KEY_LAST_URL, url).apply()
    }
}


override fun onPause() {
    super.onPause()
    CookieManager.getInstance().flush()
}
```

---

## 9. Modul Mode Ujian (Exam Mode)

Mode Ujian adalah fitur differensiator utama GARA Android dibanding akses web biasa. Ini adalah implementasi *proctoring* sederhana on-device yang memastikan integritas akademik.

### 9.1 Trigger Aktivasi

Mode ujian diaktifkan secara otomatis berdasarkan URL halaman yang sedang dikunjungi:

```kotlin
override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
    val urlStr = url ?: ""
    val isExternalNetlify = urlStr.contains(examKeyword) || urlStr.contains("netlify.app")
    val isInternalLocal = urlStr.contains("ujian.php") || urlStr.contains("/ujian/")
    val isThirdParty = thirdPartyKeywords.any { urlStr.contains(it) }
    val isHomeMainDomain = urlStr.contains(homeKeyword)

    when {
        urlStr.contains("hasil.php") -> deactivateExamMode()
        isExternalNetlify || isInternalLocal -> activateExamMode(showFAB = false)
        isThirdParty -> activateExamMode(showFAB = true)
        isHomeMainDomain && !isInternalLocal -> deactivateExamMode()
    }
}
```

**URL yang Memicu Exam Mode:**

| URL Pattern | Tipe | `showFAB` |
|---|---|---|
| `garudakademi.netlify.app` | Exam host eksternal | `false` |
| `netlify.app` | Netlify hosting | `false` |
| `ujian.php` | Halaman ujian internal | `false` |
| `/ujian/` | Route ujian internal | `false` |
| `forms.gle` | Google Forms | `true` |
| `docs.google.com/forms` | Google Forms | `true` |
| `tally.so` | Tally forms | `true` |

### 9.2 Fungsi `activateExamMode()`

```kotlin
private fun activateExamMode(showFAB: Boolean = false) {
    if (!isExamMode) {
        isExamMode = true
        
        getSharedPreferences(PREF_NAME, MODE_PRIVATE)
            .edit().putBoolean(KEY_IS_EXAM_ACTIVE, true).apply()

        
        window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)

        
        val audioManager = getSystemService(AUDIO_SERVICE) as AudioManager
        audioManager.setStreamVolume(
            AudioManager.STREAM_MUSIC,
            audioManager.getStreamMaxVolume(AudioManager.STREAM_MUSIC), 0
        )
        
        volumeObserver = VolumeObserver(Handler(Looper.getMainLooper()))
        contentResolver.registerContentObserver(
            Settings.System.CONTENT_URI, true, volumeObserver!!
        )

        
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
    }

    
    fabContainer.visibility = if (showFAB) View.VISIBLE else View.GONE

    
    try { startLockTask() } catch (_: Exception) {}

    
    WindowInsetsControllerCompat(window, window.decorView).apply {
        hide(WindowInsetsCompat.Type.systemBars())
        systemBarsBehavior =
            WindowInsetsControllerCompat.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE
    }
}
```

### 9.3 Fungsi `deactivateExamMode()`

```kotlin
private fun deactivateExamMode() {
    if (isExamMode) {
        isExamMode = false
        getSharedPreferences(PREF_NAME, MODE_PRIVATE)
            .edit().putBoolean(KEY_IS_EXAM_ACTIVE, false).apply()

        window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
        fabContainer.visibility = View.GONE
        blackoutView.visibility = View.GONE

        
        volumeObserver?.let {
            contentResolver.unregisterContentObserver(it)
            volumeObserver = null
        }

        try { stopLockTask() } catch (_: Exception) {}
        WindowInsetsControllerCompat(window, window.decorView)
            .show(WindowInsetsCompat.Type.systemBars())
        window.clearFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
    }
}
```

### 9.4 Blackout Overlay: Anti-Cheat Focus Detector

```kotlin
override fun onWindowFocusChanged(hasFocus: Boolean) {
    super.onWindowFocusChanged(hasFocus)
    if (isExamMode) {
        if (hasFocus) {
            WindowInsetsControllerCompat(window, window.decorView)
                .hide(WindowInsetsCompat.Type.navigationBars())
            blackoutView.visibility = View.GONE
        } else {
            
            
            blackoutView.visibility = View.VISIBLE
        }
    }
}
```

**Layout Blackout (`activity_main.xml`):**
```xml
<androidx.constraintlayout.widget.ConstraintLayout
    android:id="@+id/blackoutView"
    android:background="@android:color/black"
    android:visibility="gone">
    <TextView
        android:text="SEDANG MELAKSANAKAN UJIAN"
        android:textColor="@android:color/white"
        android:textSize="18sp" />
    <TextView
        android:text="Jangan curang ya!"
        android:textColor="#BBBBBB" />
</androidx.constraintlayout.widget.ConstraintLayout>
```

### 9.5 VolumeObserver

```kotlin
inner class VolumeObserver(handler: Handler) : ContentObserver(handler) {
    override fun onChange(selfChange: Boolean) {
        super.onChange(selfChange)
        val audioManager = getSystemService(AUDIO_SERVICE) as AudioManager
        
        audioManager.setStreamVolume(
            AudioManager.STREAM_MUSIC,
            audioManager.getStreamMaxVolume(AudioManager.STREAM_MUSIC), 0
        )
    }
}
```

### 9.6 Ringkasan Fitur Exam Mode

| Fitur | Implementasi | Tujuan |
|---|---|---|
| Screenshot Protection | `FLAG_SECURE` | Cegah tangkap layar & screen record |
| Volume Lock | `VolumeObserver` + `ContentObserver` | Pastikan audio ujian selalu terdengar |
| Screen Keep-On | `FLAG_KEEP_SCREEN_ON` | Layar tidak mati saat mengerjakan soal |
| Fullscreen Lock | `WindowInsetsController.hide()` | Sembunyikan status & navigation bar |
| App Lock | `startLockTask()` | Cegah keluar ke home/recent apps (Kiosk Mode) |
| Focus Detector | `onWindowFocusChanged()` | Deteksi buka notifikasi/overlay → blackout |
| Obscure Touch Guard | `MotionEvent.FLAG_WINDOW_IS_OBSCURED` | Blokir tap melalui overlay aplikasi lain |
| Back Navigation Block | `OnBackPressedCallback` | Nonaktifkan tombol back saat ujian |
| Session Recovery | `SharedPreferences` | Pulihkan state ujian setelah app restart |

---

## 10. Assistive FAB: Antarmuka Kontrol Dinamis

FAB (Floating Action Button) yang diimplementasikan terinspirasi dari fitur **AssistiveTouch Apple iOS**, dengan gaya visual **Liquid Glass** — transparan, melayang, dan bisa dipindahkan.

### 10.1 Struktur Layout FAB

```xml
<!-- activity_main.xml -->
<androidx.constraintlayout.widget.ConstraintLayout
    android:id="@+id/fabContainer"
    android:visibility="gone">      <!-- Default tersembunyi, hanya muncul saat ujian -->

    <!-- Sub-Button: Keluar Ujian -->
    <FloatingActionButton
        android:id="@+id/fabSubExit"
        app:backgroundTint="#80FFFFFF"    <!-- Semi-transparan putih (Liquid Glass) -->
        app:fabSize="mini"
        android:visibility="gone" />      <!-- Hanya terlihat saat menu dibuka -->

    <!-- Sub-Button: Refresh Halaman -->
    <FloatingActionButton
        android:id="@+id/fabSubRefresh"
        app:backgroundTint="#80FFFFFF"
        app:fabSize="mini"
        android:visibility="gone" />

    <!-- Main FAB: Draggable + Expandable -->
    <FloatingActionButton
        android:id="@+id/fabMain"
        app:backgroundTint="#80FFFFFF"
        app:elevation="6dp" />
</androidx.constraintlayout.widget.ConstraintLayout>
```

### 10.2 Ikon Kustom dari Assets

Ikon FAB tidak menggunakan drawable resource biasa, melainkan di-load secara programatik dari folder `assets/helpers/exam.png`:

```kotlin
try {
    val inputStream = assets.open("helpers/exam.png")
    val bitmap = BitmapFactory.decodeStream(inputStream)
    fabMain.setImageBitmap(bitmap)
    inputStream.close()
} catch (e: Exception) {
    e.printStackTrace()    
}
```

### 10.3 Logika Drag & Drop

Drag diwujudkan melalui `OnTouchListener` yang menghitung offset posisi:

```kotlin
var dX = 0f; var dY = 0f
var startX = 0f; var startY = 0f
val clickThreshold = 10          

fabMain.setOnTouchListener { _, event ->
    when (event.action) {
        MotionEvent.ACTION_DOWN -> {
            dX = fabContainer.x - event.rawX
            dY = fabContainer.y - event.rawY
            startX = event.rawX; startY = event.rawY
            true
        }
        MotionEvent.ACTION_MOVE -> {
            
            fabContainer.animate()
                .x(event.rawX + dX)
                .y(event.rawY + dY)
                .setDuration(0).start()
            true
        }
        MotionEvent.ACTION_UP -> {
            
            if (abs(endX - startX) < clickThreshold && abs(endY - startY) < clickThreshold) {
                toggleFabMenu()    
            }
            true
        }
    }
}
```

### 10.4 Logika Toggle Menu

```kotlin
private fun toggleFabMenu() {
    isFabMenuOpen = !isFabMenuOpen
    if (isFabMenuOpen) {
        fabSubExit.visibility = View.VISIBLE
        fabSubRefresh.visibility = View.VISIBLE
        fabMain.alpha = 1.0f                
    } else {
        fabSubExit.visibility = View.GONE
        fabSubRefresh.visibility = View.GONE
        fabMain.alpha = 0.5f               
    }
}
```

### 10.5 Aksi Sub-Button

| Sub-Button | Ikon | Aksi |
|---|---|---|
| `fabSubExit` | `ic_menu_close_clear_cancel` | Tampilkan dialog konfirmasi → `exitExamAndGoToDashboard()` |
| `fabSubRefresh` | `ic_menu_rotate` | Reload halaman saat ini + toast notifikasi |

**Fungsi Exit Ujian:**
```kotlin
private fun exitExamAndGoToDashboard() {
    webView.stopLoading()
    webView.loadUrl("about:blank")
    webView.clearHistory()          
    webView.clearCache(true)
    deactivateExamMode()
    webView.loadUrl(mainHomeUrl)    
}
```

---

## 11. Manajemen Navigasi & Interceptor URL

### 11.1 `shouldOverrideUrlLoading()` — URL Router Utama

Setiap URL yang hendak dimuat WebView melewati fungsi ini. Ini adalah "penjaga gerbang" utama.

```kotlin
override fun shouldOverrideUrlLoading(
    view: WebView?, request: WebResourceRequest?
): Boolean {
    val url = request?.url.toString()

    
    if ((url.contains("youtube.com") || url.contains("youtu.be"))
        && !url.contains("accounts.")) {
        Toast.makeText(context, "Akses ke YouTube diblokir.", Toast.LENGTH_SHORT).show()
        return true    
    }

    
    val isLocalAllowed = localExamIp != null && url.contains(localExamIp!!)
    val isThirdParty = thirdPartyKeywords.any { url.contains(it) }
    val isGoogleLogin = googleLoginKeywords.any { url.contains(it) }

    val isAllowedInternal = url.contains(homeKeyword)
        || url.contains(examKeyword)
        || url.startsWith("file:/
        || isLocalAllowed || isThirdParty || isGoogleLogin

    if (isAllowedInternal) return false    

    
    try {
        CustomTabsIntent.Builder()
            .setShowTitle(true)
            .build()
            .launchUrl(this@MainActivity, Uri.parse(url))
    } catch (_: Exception) {
        startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
    }
    return true
}
```

**Matriks Routing URL:**

| URL Pattern | Dimuat di | Alasan |
|---|---|---|
| `garudakademi.ct.ws` | WebView internal | Domain utama aplikasi |
| `garudakademi.netlify.app` | WebView internal | Host ujian |
| `file:/
| IP lokal (ujian) | WebView internal | Server ujian jaringan lokal |
| `forms.gle`, `tally.so` | WebView internal | Form third-party (diizinkan) |
| `accounts.google.com` | WebView internal | Login Google |
| `youtube.com`, `youtu.be` | ❌ Diblokir | Gangguan saat ujian |
| URL eksternal lainnya | Chrome Custom Tab | Menjaga pengguna di dalam app |

### 11.2 Back Navigation Handler

```kotlin
onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
    override fun handleOnBackPressed() {
        
        if (customView != null) {
            mWebChromeClient.onHideCustomView()
            return
        }
        
        if (blackoutView.visibility == View.VISIBLE) return

        val currentUrl = webView.url ?: ""
        val isOnThirdParty = thirdPartyKeywords.any { currentUrl.contains(it) }
        val isOnGoogleLogin = googleLoginKeywords.any { currentUrl.contains(it) }

        
        if (isExamMode || isOnThirdParty || isOnGoogleLogin) {
            Toast.makeText(context, "Navigasi Kembali Terkunci Selama Ujian!", Toast.LENGTH_SHORT).show()
            return
        }

        
        if (webView.canGoBack()) {
            webView.goBack()
        } else {
            isEnabled = false
            onBackPressedDispatcher.onBackPressed()    
        }
    }
})
```

### 11.3 Scroll Sync & SwipeRefresh

SwipeRefresh hanya diaktifkan saat pengguna berada di posisi scroll paling atas, dan dinonaktifkan saat exam mode aktif:

```kotlin
private fun setupScrollSync() {
    webView.viewTreeObserver.addOnScrollChangedListener {
        swipeRefreshLayout.isEnabled =
            if (isExamMode) false       
            else webView.scrollY == 0   
    }
}
```

---

## 12. Download Manager & Penanganan File

Aplikasi mendukung tiga jenis URL download yang umum dihasilkan oleh backend:

### 12.1 Blob URL (dari JavaScript)

URL `blob:` tidak dapat diunduh langsung oleh sistem. Triknya adalah mengonversinya terlebih dahulu menjadi Base64 via JavaScript:

```kotlin
if (url.startsWith("blob:")) {
    webView.evaluateJavascript("""
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '$url', true);
        xhr.responseType = 'blob';
        xhr.onload = function(e) {
            if (this.status == 200) {
                var reader = new FileReader();
                reader.readAsDataURL(this.response);
                reader.onloadend = function() {
                    
                    AndroidInterface.saveBase64File(
                        reader.result, 'Hasil_Ujian.pdf', 'application/pdf'
                    );
                }
            }
        };
        xhr.send();
    """.trimIndent(), null)
    return@setDownloadListener
}
```

### 12.2 Data URL (Base64 langsung)

```kotlin
if (url.startsWith("data:")) {
    saveBase64ToFile(url, "Hasil_Ujian.pdf")
    return@setDownloadListener
}
```

### 12.3 URL HTTP Biasa

```kotlin
val request = DownloadManager.Request(Uri.parse(url))
val fileName = URLUtil.guessFileName(url, contentDisposition, mimetype)
request.setMimeType(mimetype)
request.addRequestHeader("User-Agent", userAgent)
request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, fileName)
(getSystemService(DOWNLOAD_SERVICE) as DownloadManager).enqueue(request)
Toast.makeText(this, "Unduhan dimulai...", Toast.LENGTH_SHORT).show()
```

### 12.4 `saveBase64ToFile()`

```kotlin
private fun saveBase64ToFile(base64String: String, fileName: String) {
    val base64Data = if (base64String.contains(","))
        base64String.split(",")[1] else base64String   

    val pdfAsBytes = Base64.decode(base64Data, Base64.DEFAULT)
    val file = File(
        Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS),
        fileName
    )
    FileOutputStream(file).use { it.write(pdfAsBytes) }

    
    sendBroadcast(Intent(Intent.ACTION_MEDIA_SCANNER_SCAN_FILE).apply {
        data = Uri.fromFile(file)
    })
    Toast.makeText(this, "PDF Berhasil disimpan", Toast.LENGTH_LONG).show()
}
```

---

## 13. Halaman Error Interaktif (`eror.html`)

**Path:** `app/src/main/assets/helpers/eror.html`  
**Dimuat Saat:** `onReceivedError()` dipanggil untuk main frame request.

Ini bukan halaman error statis biasa. Halaman ini adalah **SPA mini** yang terintegrasi dengan `WebAppInterface` dan menawarkan dua jalur pemulihan:

### 13.1 Trigger Pemuatan

```kotlin
override fun onReceivedError(
    view: WebView?, request: WebResourceRequest?, error: WebResourceError?
) {
    if (request?.isForMainFrame == true
        && !request.url.toString().startsWith("file:/
        view?.loadUrl("file:/
    }
}
```

Hanya error pada main frame (bukan sub-resource) yang memicu halaman error, menghindari false positive dari resource opsional.

### 13.2 Jalur Pemulihan 1: Smart Retry

```javascript
function handleRetry() {
    showFeedback("Mencoba memuat ulang halaman...");
    setLoading(btnRetry, true);

    if (window.AndroidInterface) {
        
        window.AndroidInterface.retryLastUrl();
    } else {
        
        showFeedback("Mode Browser: AndroidInterface tidak ditemukan.", true);
    }
}
```

### 13.3 Jalur Pemulihan 2: Koneksi Server Lokal

Untuk skenario ujian dengan server lokal di jaringan internal sekolah:

```javascript
function submitLocalUrl() {
    const urlInput = document.getElementById('serverUrl').value.trim();

    
    if (!urlInput || urlInput.length < 8 || !urlInput.startsWith('http')) {
        showFeedback("Format URL salah! Wajib diawali http:// atau https://", true);
        return;
    }

    if (window.AndroidInterface) {
        
        window.AndroidInterface.saveNewBaseUrl(urlInput);
    }
}
```

### 13.4 Fitur UX Halaman Error

| Fitur | Detail |
|---|---|
| Loading Spinner | Animasi CSS pada tombol saat proses berlangsung |
| State Management | Tombol di-disable saat loading untuk mencegah double-submit |
| Feedback Visual | Panel feedback dengan warna berbeda (biru=info, merah=error) |
| Input Validasi | Cek prefix `http://` atau `https://` sebelum submit |
| Graceful Degradation | Deteksi `window.AndroidInterface` untuk fallback di browser |

---

## 14. Konfigurasi Izin & Manifes Android

**Path:** `app/src/main/AndroidManifest.xml` — Versi `1.6`

### 14.1 Izin Sistem

```xml
<!-- Akses internet wajib untuk WebView -->
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />

<!-- Penyimpanan (untuk download file) -->
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE"
    android:maxSdkVersion="28" />        <!-- Tidak diperlukan di Android 9+ -->
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" />

<!-- Media (Android 13+ — API 33+) -->
<uses-permission android:name="android.permission.READ_MEDIA_IMAGES" />
<uses-permission android:name="android.permission.READ_MEDIA_VIDEO" />
<uses-permission android:name="android.permission.MODIFY_AUDIO_SETTINGS" />
```

### 14.2 Queries Block (Visibility Intent)

Diperlukan sejak Android 11 (API 30) untuk dapat "melihat" aplikasi lain:

```xml
<queries>
    <!-- Izinkan melihat browser untuk Custom Tabs -->
    <intent>
        <action android:name="android.support.customtabs.action.CustomTabsService" />
    </intent>
    <!-- Izinkan melihat app browser untuk membuka HTTPS links -->
    <intent>
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.BROWSABLE" />
        <data android:scheme="https" />
    </intent>
</queries>
```

### 14.3 Konfigurasi Application

```xml
<application
    android:allowBackup="true"
    android:usesCleartextTraffic="true"      <!-- Izinkan HTTP (lokal server ujian) -->
    android:resizeableActivity="false"        <!-- Nonaktifkan split-screen / freeform -->
    android:requestLegacyExternalStorage="true"  <!-- Kompatibilitas storage lama API 28 -->
    android:theme="@style/Theme.Gara">
```

> **Catatan `usesCleartextTraffic`:** Izin HTTP (bukan HTTPS) diperlukan karena server ujian lokal kemungkinan besar menggunakan `http://192.168.x.x` tanpa sertifikat SSL.

### 14.4 Deklarasi Activity

| Activity | `exported` | Orientation | Theme | Entry Point |
|---|---|---|---|---|
| `SplashActivity` | `true` | Portrait | `Theme.Gara.Launcher` | ✅ MAIN + LAUNCHER |
| `MainActivity` | `false` | Portrait | `Theme.Gara` | Dari SplashActivity |

```xml
<activity
    android:name=".MainActivity"
    android:configChanges="orientation|screenSize|keyboardHidden"
    android:screenOrientation="portrait"
    android:theme="@style/Theme.Gara" />
```

`android:configChanges` mencegah Activity di-recreate saat orientasi berubah (terutama saat video fullscreen di-toggle).

---

## 15. Theming & Sistem Desain

### 15.1 Material Design 3 (Material You)

```xml
<!-- res/values/themes.xml -->
<resources>
    <style name="Base.Theme.Gara" parent="Theme.Material3.Light.NoActionBar">
    </style>
    <style name="Theme.Gara" parent="Base.Theme.Gara" />

    <!-- Tema khusus Splash — konfigurasi SplashScreen API -->
    <style name="Theme.Gara.Launcher" parent="Theme.Material3.Light.NoActionBar">
        <item name="android:windowSplashScreenAnimatedIcon">@android:color/transparent</item>
        <item name="android:windowSplashScreenBackground">@android:color/white</item>
    </style>
</resources>
```

Dipilih `NoActionBar` karena seluruh navigasi dikelola oleh WebView dan custom layout, sehingga ActionBar standar tidak diperlukan.

### 15.2 FAB Liquid Glass Effect

Efek "Liquid Glass" pada FAB dicapai melalui kombinasi:
- `backgroundTint="#80FFFFFF"`: Putih dengan 50% opacity (0x80 = 128 dari 255)
- `elevation="6dp"`: Shadow halus untuk kesan melayang
- `borderWidth="1dp"`: Garis tipis untuk kesan kaca
- `fabMain.alpha = 0.5f`: Lebih transparan saat menu tertutup

---

## 16. Alur Pengembangan Iteratif (Riwayat Versi)

Versi file dalam komentar kode mencerminkan proses iterasi yang sistematis:

| Versi | File | Perubahan Utama |
|---|---|---|
| `1.1` | `SplashActivity.kt` | Package fix — perbaikan namespace |
| `1.6` | `AndroidManifest.xml` | Update tema ke Material Components |
| `2.3` | `activity_splash.xml` | Final UI fix — `fitsSystemWindows` agar tidak tabrak status bar |
| `1.6.7` | `MainActivity.kt` | Liquid Glass FAB style + ikon kustom dari `exam.png` |
| `3.1` | `app/build.gradle.kts` | versionName resmi ke `3.1` |

**Catatan Pengembangan MainActivity secara khusus:**

```
v1.x → Implementasi dasar WebView
v1.2 → Penambahan Download Manager
v1.3 → Exam Mode dasar (FLAG_SECURE)
v1.4 → Volume Observer + Lock Task
v1.5 → Assistive FAB (draggable dasar)
v1.6 → Blackout overlay + focus detector
v1.6.4 → Blackout UI dirapikan
v1.6.7 → Liquid Glass FAB + exam.png icon
```

---

## 17. Ringkasan Capaian & Rencana Tahap 2

### ✅ Capaian Tahap 1

| No | Capaian | Detail |
|---|---|---|
| 1 | **WebView Berfitur Penuh** | JavaScript, DOM Storage, Cookie, file chooser, video fullscreen, hardware acceleration |
| 2 | **Persistensi Sesi (LocalStorage)** | `domStorageEnabled = true` memastikan data web tersimpan lintas refresh |
| 3 | **Persistensi Sesi (Native)** | SharedPreferences menyimpan status ujian & URL terakhir lintas restart app |
| 4 | **Exam Mode Komprehensif** | FLAG_SECURE, Volume Lock, Screen Keep-On, Lock Task, Blackout, Touch Guard |
| 5 | **JavaScript Bridge** | `WebAppInterface` dengan 3 metode: retry, saveUrl, saveFile |
| 6 | **URL Routing Cerdas** | Interceptor dengan matriks allow/block/custom-tab |
| 7 | **Download Multi-Format** | Dukungan blob:, data:, dan HTTP URL dengan notifikasi |
| 8 | **Halaman Error Interaktif** | Smart retry + koneksi server lokal via `eror.html` |
| 9 | **FAB AssistiveTouch** | Liquid Glass, draggable, expandable, ikon kustom |
| 10 | **Custom User-Agent** | Identifikasi request dari app native ke backend |
| 11 | **Release Configuration** | ProGuard aktif, keystore tersedia, resource shrinking |

### 🗺️ Rencana Tahap 2

Berdasarkan fondasi yang telah dibangun pada Tahap 1, berikut arah pengembangan yang bisa dilanjutkan:

| No | Fitur | Justifikasi |
|---|---|---|
| 1 | **Push Notification (FCM)** | Notifikasi native untuk pengumuman, jadwal ujian, nilai keluar |
| 2 | **Offline Cache Strategy** | Service Worker atau WebView cache routing untuk konten materi |
| 3 | **Biometric Authentication** | Fingerprint/Face ID sebagai lapisan keamanan login tambahan |
| 4 | **Deep Link Support** | Buka halaman spesifik di app langsung dari link/QR code |
| 5 | **In-App Update** | Tampilkan dialog pembaruan versi menggunakan Google Play In-App Update |
| 6 | **Network State Monitor** | Deteksi koneksi terputus secara real-time, tampilkan banner offline |
| 7 | **ProGuard Rules Refinement** | Tambahkan keep-rules spesifik untuk @JavascriptInterface agar tidak di-obfuscate |
| 8 | **Screen Orientation Handling** | Dukungan landscape untuk halaman materi video yang lebih kaya |

---

## Lampiran: Glosarium Teknis

| Istilah | Penjelasan |
|---|---|
| **WebView** | Komponen Android yang merender halaman web menggunakan engine Chromium (Blink) |
| **DOM Storage / LocalStorage** | API penyimpanan key-value di browser, diaktifkan via `domStorageEnabled` |
| **SharedPreferences** | Mekanisme penyimpanan key-value native Android untuk data sederhana |
| **@JavascriptInterface** | Anotasi Kotlin/Java yang mengekspos fungsi native ke konteks JavaScript |
| **FLAG_SECURE** | Flag window yang mencegah screenshot, screen recording, dan tampilan di recent apps |
| **Lock Task Mode** | Mode Android yang "mengunci" app sebagai satu-satunya app yang aktif (Kiosk Mode) |
| **Content Observer** | Mekanisme Android untuk memantau perubahan di ContentProvider (termasuk setting sistem) |
| **Custom Tab** | Komponen browser in-app yang memungkinkan tampilan halaman web dalam konteks app |
| **ProGuard / R8** | Tool untuk minifikasi, obfuscation, dan optimasi bytecode Android |
| **Blob URL** | URL sementara yang merujuk pada data biner di memori browser |
| **Hardware Acceleration** | Rendering menggunakan GPU (lebih cepat dari software rendering CPU) |
| **versionCode** | Integer internal untuk manajemen update (harus selalu naik di setiap rilis) |
| **versionName** | String versi yang ditampilkan ke pengguna (`"3.1"`) |

---

*Dokumen ini disusun berdasarkan analisis mendalam terhadap source code proyek GARA Android pada tanggal 23 April 2026. Dokumentasi ini bersifat hidup dan akan diperbarui seiring perkembangan proyek.*

---
**GARA — Garuda Akademi** | Platform LMS untuk Ekosistem Pendidikan Indonesia
