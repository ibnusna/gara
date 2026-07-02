// Shared AppBar — dipakai seragam di semua halaman Ruang
// Header: [Back] Logo FARA_BLACK | Judul | [ikon opsional]
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'gara_logo.dart';
import '../utils/app_constants.dart';

class GaraAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final IconData titleIcon;
  final Color iconColor;
  final Color iconBg;
  final List<Widget>? actions;
  final bool showBack;

  const GaraAppBar({
    super.key,
    required this.title,
    required this.titleIcon,
    this.iconColor = GaraColors.studentPrimary,
    this.iconBg = const Color(0xFFE8F0FE),
    this.actions,
    this.showBack = true,
  });

  @override
  Size get preferredSize => const Size.fromHeight(58);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: GaraColors.studentSurface,
      foregroundColor: GaraColors.studentTextMain,
      elevation: 0,
      surfaceTintColor: Colors.transparent,
      automaticallyImplyLeading: false,
      titleSpacing: 0,
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child: Container(color: GaraColors.studentBorder, height: 1),
      ),
      title: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12),
        child: Row(children: [
          // ← Back
          if (showBack)
            GestureDetector(
              onTap: () => Navigator.pop(context),
              child: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: GaraColors.studentBgBody,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.arrow_back_ios_new_rounded,
                    size: 16, color: GaraColors.studentTextMuted),
              ),
            ),
          const SizedBox(width: 10),
          // Logo FARA_BLACK
          const GaraLogoBlack(height: 26),
          const SizedBox(width: 10),
          // Divider vertikal
          Container(width: 1, height: 22, color: GaraColors.studentBorder),
          const SizedBox(width: 10),
          // Icon + Judul
          Container(
            padding: const EdgeInsets.all(5),
            decoration: BoxDecoration(color: iconBg, borderRadius: BorderRadius.circular(8)),
            child: Icon(titleIcon, color: iconColor, size: 16),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              title,
              style: GoogleFonts.poppins(
                  fontWeight: FontWeight.w700,
                  fontSize: 15,
                  color: GaraColors.studentTextMain),
              overflow: TextOverflow.ellipsis,
            ),
          ),
          // Aksi kanan (opsional)
          if (actions != null) ...actions!,
        ]),
      ),
    );
  }
}
