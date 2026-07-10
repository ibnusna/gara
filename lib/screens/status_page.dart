import 'package:flutter/material.dart';

class StatusPage extends StatelessWidget {
  final String status;
  final String schoolName;

  const StatusPage({super.key, required this.status, required this.schoolName});

  @override
  Widget build(BuildContext context) {
    String title = "Perhatian";
    String message = "Status sekolah: $status";
    IconData icon = Icons.info;
    Color color = Colors.orange;

    if (status == 'maintenance') {
      title = "Pemeliharaan Sistem";
      message = "Sistem $schoolName sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.";
      icon = Icons.build_circle;
    } else if (status == 'suspend') {
      title = "Akses Ditangguhkan";
      message = "Akses untuk $schoolName ditangguhkan. Silakan hubungi administrator.";
      icon = Icons.block;
      color = Colors.red;
    }

    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 100, color: color),
              const SizedBox(height: 24),
              Text(title, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              Text(message, textAlign: TextAlign.center, style: const TextStyle(fontSize: 16)),
            ],
          ),
        ),
      ),
    );
  }
}
