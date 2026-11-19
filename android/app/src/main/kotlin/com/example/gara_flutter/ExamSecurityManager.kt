package com.example.gara_flutter

import android.app.Activity
import android.app.ActivityManager
import android.content.Context
import android.database.ContentObserver
import android.media.AudioManager
import android.os.Build
import android.os.Handler
import android.os.Looper
import android.provider.Settings
import android.view.View
import android.view.WindowInsets
import android.view.WindowInsetsController
import android.view.WindowManager

class ExamSecurityManager(private val activity: Activity) {
    private var volumeObserver: ContentObserver? = null

    fun enableExamMode() {
        activity.runOnUiThread {
            activity.window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)
            activity.window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
            hideSystemBars()
            startLockTask()
        }
    }

    fun disableExamMode() {
        activity.runOnUiThread {
            activity.window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
            activity.window.clearFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
            showSystemBars()
            stopLockTask()
            stopVolumeWatch()
        }
    }

    fun hideSystemBars() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            activity.window.insetsController?.let { ctrl ->
                ctrl.hide(WindowInsets.Type.systemBars())
                ctrl.systemBarsBehavior = WindowInsetsController.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE
            }
        } else {
            @Suppress("DEPRECATION")
            activity.window.decorView.systemUiVisibility = (
                View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY
                or View.SYSTEM_UI_FLAG_FULLSCREEN
                or View.SYSTEM_UI_FLAG_HIDE_NAVIGATION
                or View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                or View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                or View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
            )
        }
    }

    fun showSystemBars() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            activity.window.insetsController?.show(WindowInsets.Type.systemBars())
        } else {
            @Suppress("DEPRECATION")
            activity.window.decorView.systemUiVisibility = View.SYSTEM_UI_FLAG_VISIBLE
        }
    }

    fun startLockTask() {
        try {
            val am = activity.getSystemService(Context.ACTIVITY_SERVICE) as ActivityManager
            val isAlreadyLocked = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                am.lockTaskModeState != ActivityManager.LOCK_TASK_MODE_NONE
            } else {
                @Suppress("DEPRECATION")
                am.isInLockTaskMode
            }
            if (!isAlreadyLocked) activity.startLockTask()
        } catch (_: Exception) {}
    }

    fun stopLockTask() {
        try {
            val am = activity.getSystemService(Context.ACTIVITY_SERVICE) as ActivityManager
            val isLocked = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                am.lockTaskModeState != ActivityManager.LOCK_TASK_MODE_NONE
            } else {
                @Suppress("DEPRECATION")
                am.isInLockTaskMode
            }
            if (isLocked) activity.stopLockTask()
        } catch (_: Exception) {}
    }

    fun enforceMaxVolume() {
        try {
            val am = activity.getSystemService(Context.AUDIO_SERVICE) as AudioManager
            val maxVol = am.getStreamMaxVolume(AudioManager.STREAM_MUSIC)
            am.setStreamVolume(AudioManager.STREAM_MUSIC, maxVol, 0)
        } catch (_: Exception) {}
    }

    fun startVolumeWatch() {
        try {
            if (volumeObserver == null) {
                val handler = Handler(Looper.getMainLooper())
                volumeObserver = object : ContentObserver(handler) {
                    override fun onChange(selfChange: Boolean) {
                        super.onChange(selfChange)
                        enforceMaxVolume()
                    }
                }
                activity.contentResolver.registerContentObserver(
                    Settings.System.CONTENT_URI,
                    true,
                    volumeObserver!!
                )
                enforceMaxVolume()
            }
        } catch (_: Exception) {}
    }

    fun stopVolumeWatch() {
        try {
            volumeObserver?.let {
                activity.contentResolver.unregisterContentObserver(it)
                volumeObserver = null
            }
        } catch (_: Exception) {}
    }
}
