import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';

class ActivationService {
  static const String keySchoolKey = 'school_key';
  static const String keySchoolName = 'school_name';
  static const String keyBaseUrl = 'base_url';

  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  Future<Map<String, dynamic>?> activateSchool(String schoolKey) async {
    if (schoolKey.trim().toUpperCase() == 'HITAMPEKAT') {
      final data = {
        'school_name': 'Garuda Local Debug (ADB)',
        'url': 'http://localhost:8000',
      };
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(keySchoolKey, 'HITAMPEKAT');
      await prefs.setString(keySchoolName, data['school_name']!);
      await prefs.setString(keyBaseUrl, data['url']!);
      return data;
    }

    try {
      final doc = await _firestore.collection('schools').doc(schoolKey).get();
      if (doc.exists) {
        final data = doc.data()!;
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(keySchoolKey, schoolKey);
        await prefs.setString(keySchoolName, data['school_name'] ?? '');
        await prefs.setString(keyBaseUrl, data['url'] ?? '');
        return data;
      }
      return null; // Invalid school key
    } catch (e) {
      throw Exception('Gagal menghubungi server aktivasi: $e');
    }
  }

  Future<void> clearActivation() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(keySchoolKey);
    await prefs.remove(keySchoolName);
    await prefs.remove(keyBaseUrl);
  }

  Future<bool> isActivated() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.containsKey(keySchoolKey);
  }

  Future<String?> getBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(keyBaseUrl);
  }
}
