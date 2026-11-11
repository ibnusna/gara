import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../utils/app_constants.dart';
import '../gara_logo.dart';

class BrowserAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final bool canGoBack;
  final VoidCallback onBack;
  final Future<String?> Function() onGetCurrentUrl;
  final String fallbackUrl;

  const BrowserAppBar({
    super.key,
    required this.title,
    required this.canGoBack,
    required this.onBack,
    required this.onGetCurrentUrl,
    required this.fallbackUrl,
  });

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: GaraColors.studentSurface,
      elevation: 0,
      centerTitle: true,
      leading: IconButton(
        icon: const Icon(Icons.close_rounded, color: GaraColors.studentTextMain),
        onPressed: () => Navigator.pop(context),
      ),
      title: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const GaraLogoBlack(height: 24),
          const SizedBox(width: 8),
          Text(
            title.isNotEmpty ? title : 'Garuda Akademi',
            style: GoogleFonts.poppins(
              fontWeight: FontWeight.w600,
              fontSize: 15,
              color: GaraColors.studentTextMain,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
      actions: [
        if (canGoBack)
          IconButton(
            icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18, color: GaraColors.studentTextMain),
            onPressed: onBack,
            tooltip: 'Kembali',
          ),
        PopupMenuButton<String>(
          icon: const Icon(Icons.more_vert_rounded, color: GaraColors.studentTextMuted),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          onSelected: (val) async {
            if (val == 'open_browser') {
              final cur = await onGetCurrentUrl() ?? fallbackUrl;
              final uri = Uri.tryParse(cur);
              if (uri != null) await launchUrl(uri, mode: LaunchMode.externalApplication);
            }
          },
          itemBuilder: (_) => [
            PopupMenuItem(
              value: 'open_browser',
              child: Row(
                children: [
                  const Icon(Icons.open_in_browser_rounded, size: 18, color: GaraColors.studentTextMain),
                  const SizedBox(width: 10),
                  Text('Buka di Browser', style: GoogleFonts.poppins(fontSize: 13)),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(width: 4),
      ],
    );
  }
}
