import Flutter
import UIKit

@main
@objc class AppDelegate: FlutterAppDelegate {
  private var securityChannel: FlutterMethodChannel?
  private var isExamModeActive = false

  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    GeneratedPluginRegistrant.register(with: self)

    if let controller = window?.rootViewController as? FlutterViewController {
      let channel = FlutterMethodChannel(
        name: "com.lms.gara/security",
        binaryMessenger: controller.binaryMessenger
      )
      self.securityChannel = channel

      channel.setMethodCallHandler { [weak self] (call, result) in
        guard let self = self else { return }
        switch call.method {
        case "enableExamMode":
          self.isExamModeActive = true
          self.setupSecurityObservers()
          result(nil)
        case "disableExamMode":
          self.isExamModeActive = false
          self.removeSecurityObservers()
          result(nil)
        case "checkDeviceIntegrity":
          let isCaptured = UIScreen.main.isCaptured
          result([
            "isRooted": false,
            "isScreenCaptured": isCaptured,
            "canDrawOverlays": false,
            "isAdbEnabled": false
          ])
        case "addFlagSecure", "clearFlagSecure", "startLockTask", "stopLockTask",
             "enforceMaxVolume", "startVolumeWatch", "stopVolumeWatch",
             "keepScreenOn", "clearKeepScreenOn", "hideSystemBars", "showSystemBars":
          result(nil)
        default:
          result(FlutterMethodNotImplemented)
        }
      }
    }

    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  private func setupSecurityObservers() {
    NotificationCenter.default.removeObserver(self)

    NotificationCenter.default.addObserver(
      self,
      selector: #selector(screenCaptureChanged),
      name: UIScreen.capturedDidChangeNotification,
      object: nil
    )

    NotificationCenter.default.addObserver(
      self,
      selector: #selector(appWillResignActive),
      name: UIApplication.willResignActiveNotification,
      object: nil
    )
  }

  private func removeSecurityObservers() {
    NotificationCenter.default.removeObserver(self, name: UIScreen.capturedDidChangeNotification, object: nil)
    NotificationCenter.default.removeObserver(self, name: UIApplication.willResignActiveNotification, object: nil)
  }

  @objc private func screenCaptureChanged() {
    if isExamModeActive && UIScreen.main.isCaptured {
      securityChannel?.invokeMethod("onSecurityViolation", arguments: ["reason": "screen_recording_detected"])
    }
  }

  @objc private func appWillResignActive() {
    if isExamModeActive {
      securityChannel?.invokeMethod("onSecurityViolation", arguments: ["reason": "app_switch_detected"])
    }
  }
}
