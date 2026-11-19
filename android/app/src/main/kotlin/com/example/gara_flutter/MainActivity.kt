package com.example.gara_flutter

import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    private val securityChannel = "com.lms.gara/security"
    private var examManager: ExamSecurityManager? = null

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        val manager = ExamSecurityManager(this)
        examManager = manager

        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, securityChannel)
            .setMethodCallHandler { call, result ->
                when (call.method) {
                    "enableExamMode" -> {
                        manager.enableExamMode()
                        result.success(null)
                    }
                    "disableExamMode" -> {
                        manager.disableExamMode()
                        result.success(null)
                    }
                    "checkDeviceIntegrity" -> {
                        val report = DeviceIntegrityChecker.checkIntegrity(this)
                        result.success(report)
                    }
                    "addFlagSecure" -> {
                        runOnUiThread {
                            window.addFlags(android.view.WindowManager.LayoutParams.FLAG_SECURE)
                        }
                        result.success(null)
                    }
                    "clearFlagSecure" -> {
                        runOnUiThread {
                            window.clearFlags(android.view.WindowManager.LayoutParams.FLAG_SECURE)
                        }
                        result.success(null)
                    }
                    "startLockTask" -> {
                        manager.startLockTask()
                        result.success(null)
                    }
                    "stopLockTask" -> {
                        manager.stopLockTask()
                        result.success(null)
                    }
                    "enforceMaxVolume" -> {
                        manager.enforceMaxVolume()
                        result.success(null)
                    }
                    "startVolumeWatch" -> {
                        manager.startVolumeWatch()
                        result.success(null)
                    }
                    "stopVolumeWatch" -> {
                        manager.stopVolumeWatch()
                        result.success(null)
                    }
                    "keepScreenOn" -> {
                        runOnUiThread {
                            window.addFlags(android.view.WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
                        }
                        result.success(null)
                    }
                    "clearKeepScreenOn" -> {
                        runOnUiThread {
                            window.clearFlags(android.view.WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
                        }
                        result.success(null)
                    }
                    "hideSystemBars" -> {
                        runOnUiThread { manager.hideSystemBars() }
                        result.success(null)
                    }
                    "showSystemBars" -> {
                        runOnUiThread { manager.showSystemBars() }
                        result.success(null)
                    }
                    else -> result.notImplemented()
                }
            }
    }

    override fun onDestroy() {
        examManager?.stopVolumeWatch()
        super.onDestroy()
    }
}
