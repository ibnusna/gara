package com.example.gara_flutter

import android.content.Context
import android.os.Build
import android.provider.Settings
import java.io.File

object DeviceIntegrityChecker {
    fun checkIntegrity(context: Context): Map<String, Any> {
        return mapOf(
            "isRooted" to checkRoot(),
            "isAdbEnabled" to checkAdb(context),
            "canDrawOverlays" to checkOverlays(context)
        )
    }

    private fun checkRoot(): Boolean {
        val buildTags = Build.TAGS
        if (buildTags != null && buildTags.contains("test-keys")) {
            return true
        }

        val paths = arrayOf(
            "/system/app/Superuser.apk",
            "/sbin/su",
            "/system/bin/su",
            "/system/xbin/su",
            "/data/local/xbin/su",
            "/data/local/bin/su",
            "/system/sd/xbin/su",
            "/system/bin/failsafe/su",
            "/data/local/su",
            "/su/bin/su"
        )
        for (path in paths) {
            if (File(path).exists()) return true
        }
        return false
    }

    private fun checkAdb(context: Context): Boolean {
        return try {
            Settings.Global.getInt(
                context.contentResolver,
                Settings.Global.ADB_ENABLED,
                0
            ) == 1
        } catch (_: Exception) {
            false
        }
    }

    private fun checkOverlays(context: Context): Boolean {
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            Settings.canDrawOverlays(context)
        } else {
            false
        }
    }
}
