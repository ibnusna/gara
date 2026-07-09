
import 'package:flutter/material.dart';

class NotifModel {
  final IconData icon;
  final Color iconColor;
  final Color iconBg;
  final String title;
  final String body;
  final String waktu;
  bool isRead;

  NotifModel({
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    required this.title,
    required this.body,
    required this.waktu,
    this.isRead = false,
  });
}


List<NotifModel> dummyNotifList() => [
      NotifModel(
        icon: Icons.assignment_rounded,
        iconColor: const Color(0xFFEA580C),
        iconBg: const Color(0xFFFFEDD5),
        title: 'Tugas Baru: Matematika',
        body: 'Guru menambahkan tugas "Latihan Integral". Batas: Besok 23:59.',
        waktu: '2 menit lalu',
        isRead: false,
      ),
      NotifModel(
        icon: Icons.forum_rounded,
        iconColor: const Color(0xFF16A34A),
        iconBg: const Color(0xFFDCFCE7),
        title: 'Reply di Diskusi',
        body: 'Guru membalas pertanyaanmu di thread "Cara menghitung limit".',
        waktu: '1 jam lalu',
        isRead: false,
      ),
      NotifModel(
        icon: Icons.menu_book_rounded,
        iconColor: const Color(0xFF0284C7),
        iconBg: const Color(0xFFE0F2FE),
        title: 'Materi Baru',
        body: 'Bab 5 "Trigonometri Dasar" diunggah oleh guru.',
        waktu: '3 jam lalu',
        isRead: true,
      ),
      NotifModel(
        icon: Icons.laptop_mac_rounded,
        iconColor: const Color(0xFFDC2626),
        iconBg: const Color(0xFFFEE2E2),
        title: 'Ujian Akan Dimulai',
        body: 'Ruang Asesmen dibuka besok pukul 08:00.',
        waktu: 'Kemarin',
        isRead: true,
      ),
    ];
