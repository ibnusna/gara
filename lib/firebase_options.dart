import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

/// Default [FirebaseOptions] for use with your Firebase apps.
/// Extracted from lms-gara-integration web config.
class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) {
      return web;
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      case TargetPlatform.iOS:
        return ios;
      case TargetPlatform.macOS:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for macos - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      case TargetPlatform.windows:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for windows - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      case TargetPlatform.linux:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for linux - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      default:
        throw UnsupportedError(
          'DefaultFirebaseOptions are not supported for this platform.',
        );
    }
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'AIzaSyAAUiz0SYaapsFOvy4U1eKeBTxSdmmzmtc',
    appId: '1:627967626685:web:c81345741406d816c796d4',
    messagingSenderId: '627967626685',
    projectId: 'lms-gara-integration',
    authDomain: 'lms-gara-integration.firebaseapp.com',
    storageBucket: 'lms-gara-integration.firebasestorage.app',
    measurementId: 'G-7FZ33D4DVZ',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'AIzaSyAAUiz0SYaapsFOvy4U1eKeBTxSdmmzmtc',
    appId: '1:627967626685:android:e61a4f02f924194d9326d9',
    messagingSenderId: '627967626685',
    projectId: 'lms-gara-integration',
    storageBucket: 'lms-gara-integration.firebasestorage.app',
  );

  static const FirebaseOptions ios = FirebaseOptions(
    apiKey: 'AIzaSyAAUiz0SYaapsFOvy4U1eKeBTxSdmmzmtc',
    appId: '1:627967626685:ios:e61a4f02f924194d9326d9',
    messagingSenderId: '627967626685',
    projectId: 'lms-gara-integration',
    storageBucket: 'lms-gara-integration.firebasestorage.app',
    iosBundleId: 'com.example.gara_flutter',
  );
}
