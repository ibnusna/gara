// ============================================================
//  GARA Flutter — Screen: Notifikasi (Legacy)
//  Note: Dashboard menggunakan NotifikasiTab dengan API real.
//  File ini adalah fallback standalone page.
// ============================================================

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../utils/app_constants.dart';
import '../widgets/gara_logo.dart';

class NotifikasiPage extends StatefulWidget {
  const NotifikasiPage({super.key});

  @override
  State<NotifikasiPage> createState() => _NotifikasiPageState();
}

class _NotifikasiPageState extends State<NotifikasiPage>
    with SingleTickerProviderStateMixin {
  late AnimationController _ctrl;
  late Animation<double> _fade;

  // Dummy notification data — FASE 2: ganti dengan data dari API
  final List<_NotifItem> _dummyNotifs = [
    _NotifItem(
      icon: Icons.assignment_rounded,
      iconColor: const Color(0xFFEA580C),
      iconBg: const Color(0xFFFFEDD5),
      title: 'Tugas Baru: Matematika',
      body: 'Guru telah menambahkan tugas baru "Latihan Integral". Batas waktu: Besok pukul 23:59.',
      waktu: '2 menit lalu',
      isRead: false,
    ),
    _NotifItem(
      icon: Icons.forum_rounded,
      iconColor: const Color(0xFF16A34A),
      iconBg: const Color(0xFFDCFCE7),
      title: 'Reply di Diskusi',
      body: 'Guru membalas pertanyaanmu di thread "Cara menghitung limit fungsi".',
      waktu: '1 jam lalu',
      isRead: false,
    ),
    _NotifItem(
      icon: Icons.menu_book_rounded,
      iconColor: const Color(0xFF0284C7),
      iconBg: const Color(0xFFE0F2FE),
      title: 'Materi Baru Tersedia',
      body: 'Materi Bab 5 "Trigonometri Dasar" telah diunggah oleh guru.',
      waktu: '3 jam lalu',
      isRead: true,
    ),
    _NotifItem(
      icon: Icons.laptop_mac_rounded,
      iconColor: const Color(0xFFDC2626),
      iconBg: const Color(0xFFFEE2E2),
      title: 'Ujian Akan Dimulai',
      body: 'Ruang Asesmen akan dibuka besok pukul 08:00. Pastikan kamu siap!',
      waktu: 'Kemarin',
      isRead: true,
    ),
  ];

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 400));
    _fade = Tween<double>(begin: 0, end: 1)
        .animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOut));
    _ctrl.forward();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.studentBgBody,
      appBar: AppBar(
        backgroundColor: GaraColors.studentSurface,
        foregroundColor: GaraColors.studentTextMain,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        automaticallyImplyLeading: false,
        title: Row(
          children: [
            const GaraLogoBlack(height: 28),
            const SizedBox(width: 10),
            Text(
              'Notifikasi',
              style: GoogleFonts.poppins(
                fontWeight: FontWeight.w700,
                fontSize: 18,
                color: GaraColors.studentTextMain,
              ),
            ),
          ],
        ),
        actions: [
          // Tandai semua dibaca
          TextButton.icon(
            onPressed: () => setState(() {
              for (final n in _dummyNotifs) {
                n.isRead = true;
              }
            }),
            icon: const Icon(Icons.done_all_rounded, size: 16),
            label: Text(
              'Baca Semua',
              style: GoogleFonts.poppins(fontSize: 12, fontWeight: FontWeight.w600),
            ),
            style: TextButton.styleFrom(
                foregroundColor: GaraColors.studentPrimary),
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: GaraColors.studentBorder, height: 1),
        ),
      ),
      body: FadeTransition(
        opacity: _fade,
        child: ListView.separated(
          padding: const EdgeInsets.symmetric(vertical: 12),
          itemCount: _dummyNotifs.length,
          separatorBuilder: (_, __) => const Divider(
              height: 1, indent: 72, endIndent: 16,
              color: GaraColors.studentBorder),
          itemBuilder: (context, i) {
            final n = _dummyNotifs[i];
            return _NotifTile(item: n, onTap: () {
              setState(() => n.isRead = true);
            });
          },
        ),
      ),
    );
  }
}

// ── Notification Item Model ──────────────────────────────────
class _NotifItem {
  final IconData icon;
  final Color iconColor;
  final Color iconBg;
  final String title;
  final String body;
  final String waktu;
  bool isRead;

  _NotifItem({
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    required this.title,
    required this.body,
    required this.waktu,
    this.isRead = false,
  });
}

// ── Notification Tile Widget ─────────────────────────────────
class _NotifTile extends StatelessWidget {
  final _NotifItem item;
  final VoidCallback onTap;

  const _NotifTile({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        color: item.isRead ? Colors.transparent : const Color(0xFFEFF6FF),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Icon
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: item.iconBg,
                shape: BoxShape.circle,
              ),
              child: Icon(item.icon, color: item.iconColor, size: 20),
            ),
            const SizedBox(width: 12),
            // Content
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          item.title,
                          style: GoogleFonts.poppins(
                            fontWeight: item.isRead
                                ? FontWeight.w500
                                : FontWeight.w700,
                            fontSize: 13.5,
                            color: GaraColors.studentTextMain,
                          ),
                        ),
                      ),
                      if (!item.isRead)
                        Container(
                          width: 8,
                          height: 8,
                          decoration: const BoxDecoration(
                            color: GaraColors.studentPrimary,
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Text(
                    item.body,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.poppins(
                      fontSize: 12.5,
                      color: GaraColors.studentTextMuted,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    item.waktu,
                    style: GoogleFonts.poppins(
                      fontSize: 11,
                      color: GaraColors.studentPrimary,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
