package com.example.gara_flutter

import android.app.ActivityManager
import android.database.ContentObserver
import android.media.AudioManager
import android.os.Build
import android.os.Handler
import android.os.Looper
import android.provider.Settings
import android.view.WindowManager
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

/**
 * GARA MainActivity v5.2.0 — Kotlin Native Host
 *
 * Mengekspos MethodChannel "com.lms.gara/security" ke Flutter.
 * Semua logika keamanan ujian yang membutuhkan native Android API
 * ada di sini, dicontoh dari Gara/app/src/main/java/com/lms/gara/MainActivity.kt.
 *
 * Channel Methods:
 *  ├── addFlagSecure       — blokir screenshot & screen recording
 *  ├── clearFlagSecure     — izinkan screenshot kembali
 *  ├── startLockTask       — kiosk/pin mode (cegah keluar dari app)
 *  ├── stopLockTask        — keluar dari kiosk mode
 *  ├── enforceMaxVolume    — paksa volume STREAM_MUSIC ke maksimum (one-shot)
 *  ├── startVolumeWatch    — mulai VolumeObserver (persistent enforcement)
 *  ├── stopVolumeWatch     — hentikan VolumeObserver
 *  ├── keepScreenOn        — cegah layar mati selama ujian
 *  ├── clearKeepScreenOn   — izinkan layar mati kembali
 *  ├── hideSystemBars      — immersive fullscreen (sembunyikan status & nav bar)
 *  └── showSystemBars      — tampilkan kembali system bars
 */
class MainActivity : FlutterActivity() {

    private val SECURITY_CHANNEL = "com.lms.gara/security"

    // VolumeObserver — setara dengan inner class VolumeObserver di contoh Kotlin
    private var volumeObserver: ContentObserver? = null

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)

        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, SECURITY_CHANNEL)
            .setMethodCallHandler { call, result ->
                when (call.method) {

                    // ── Screenshot Protection ─────────────────────────────
                    "addFlagSecure" -> {
                        runOnUiThread {
                            window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)
                        }
                        result.success(null)
                    }
                    "clearFlagSecure" -> {
                        runOnUiThread {
                            window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
                        }
                        result.success(null)
                    }

                    // ── Kiosk / Lock Task Mode ────────────────────────────
                    // startLockTask() efektif hanya jika app terdaftar sebagai
                    // Device Owner atau diizinkan via Device Policy Manager.
                    // Jika tidak, Android tetap memasukkannya ke "pinning mode ringan".
                    "startLockTask" -> {
                        try {
                            val am = getSystemService(ACTIVITY_SERVICE) as ActivityManager
                            val isAlreadyLocked = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                                am.lockTaskModeState != ActivityManager.LOCK_TASK_MODE_NONE
                            } else {
                                @Suppress("DEPRECATION")
                                am.isInLockTaskMode
                            }
                            if (!isAlreadyLocked) {
                                startLockTask()
                            }
                            result.success(null)
                        } catch (e: Exception) {
                            // Tidak crash — Flutter tetap bisa melanjutkan
                            result.success("not_device_owner")
                        }
                    }
                    "stopLockTask" -> {
                        try {
                            val am = getSystemService(ACTIVITY_SERVICE) as ActivityManager
                            val isLocked = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                                am.lockTaskModeState != ActivityManager.LOCK_TASK_MODE_NONE
                            } else {
                                @Suppress("DEPRECATION")
                                am.isInLockTaskMode
                            }
                            if (isLocked) {
                                stopLockTask()
                            }
                            result.success(null)
                        } catch (e: Exception) {
                            result.success(null)
                        }
                    }

                    // ── Volume Lock (One-Shot) ─────────────────────────────
                    // Paksa STREAM_MUSIC ke volume maksimum sekarang.
                    "enforceMaxVolume" -> {
                        try {
                            val audioManager = getSystemService(AUDIO_SERVICE) as AudioManager
                            val maxVol = audioManager.getStreamMaxVolume(AudioManager.STREAM_MUSIC)
                            audioManager.setStreamVolume(AudioManager.STREAM_MUSIC, maxVol, 0)
                            result.success(null)
                        } catch (e: Exception) {
                            result.success(null)
                        }
                    }

                    // ── Volume Observer (Persistent) ──────────────────────
                    // Daftarkan ContentObserver yang akan re-enforce volume
                    // setiap kali user mencoba menurunkan volume.
                    // Setara dengan VolumeObserver inner class di contoh Kotlin.
                    "startVolumeWatch" -> {
                        try {
                            if (volumeObserver == null) {
                                val handler = Handler(Looper.getMainLooper())
                                volumeObserver = object : ContentObserver(handler) {
                                    override fun onChange(selfChange: Boolean) {
                                        super.onChange(selfChange)
                                        val am = getSystemService(AUDIO_SERVICE) as AudioManager
                                        val maxVol = am.getStreamMaxVolume(AudioManager.STREAM_MUSIC)
                                        val curVol = am.getStreamVolume(AudioManager.STREAM_MUSIC)
                                        if (curVol < maxVol) {
                                            am.setStreamVolume(AudioManager.STREAM_MUSIC, maxVol, 0)
                                        }
                                    }
                                }
                                contentResolver.registerContentObserver(
                                    Settings.System.CONTENT_URI,
                                    true,
                                    volumeObserver!!
                                )
                                // Enforce langsung saat mulai watch
                                val am = getSystemService(AUDIO_SERVICE) as AudioManager
                                am.setStreamVolume(
                                    AudioManager.STREAM_MUSIC,
                                    am.getStreamMaxVolume(AudioManager.STREAM_MUSIC), 0
                                )
                            }
                            result.success(null)
                        } catch (e: Exception) {
                            result.success(null)
                        }
                    }
                    "stopVolumeWatch" -> {
                        try {
                            volumeObserver?.let { observer ->
                                contentResolver.unregisterContentObserver(observer)
                                volumeObserver = null
                            }
                            result.success(null)
                        } catch (e: Exception) {
                            result.success(null)
                        }
                    }

                    // ── Screen Keep-On ────────────────────────────────────
                    "keepScreenOn" -> {
                        runOnUiThread {
                            window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
                        }
                        result.success(null)
                    }
                    "clearKeepScreenOn" -> {
                        runOnUiThread {
                            window.clearFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
                        }
                        result.success(null)
                    }

                    // ── Immersive Fullscreen ──────────────────────────────
                    // Sembunyikan status bar + navigation bar (immersive sticky).
                    "hideSystemBars" -> {
                        runOnUiThread {
                            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
                                window.insetsController?.let { ctrl ->
                                    ctrl.hide(android.view.WindowInsets.Type.systemBars())
                                    ctrl.systemBarsBehavior =
                                        android.view.WindowInsetsController.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE
                                }
                            } else {
                                @Suppress("DEPRECATION")
                                window.decorView.systemUiVisibility = (
                                    android.view.View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY
                                    or android.view.View.SYSTEM_UI_FLAG_FULLSCREEN
                                    or android.view.View.SYSTEM_UI_FLAG_HIDE_NAVIGATION
                                    or android.view.View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                                    or android.view.View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                                    or android.view.View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
                                )
                            }
                        }
                        result.success(null)
                    }
                    "showSystemBars" -> {
                        runOnUiThread {
                            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
                                window.insetsController?.show(
                                    android.view.WindowInsets.Type.systemBars()
                                )
                            } else {
                                @Suppress("DEPRECATION")
                                window.decorView.systemUiVisibility =
                                    android.view.View.SYSTEM_UI_FLAG_VISIBLE
                            }
                        }
                        result.success(null)
                    }

                    else -> result.notImplemented()
                }
            }
    }

    override fun onDestroy() {
        // Pastikan VolumeObserver selalu di-unregister
        try {
            volumeObserver?.let { observer ->
                contentResolver.unregisterContentObserver(observer)
                volumeObserver = null
            }
        } catch (_: Exception) {}
        super.onDestroy()
    }
}
