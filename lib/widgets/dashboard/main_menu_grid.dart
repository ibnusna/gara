



















import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../utils/app_constants.dart';
import '../../utils/app_config.dart';
import '../../utils/responsive_utils.dart';
import '../../screens/ruang_catatan_page.dart';
import '../../screens/ruang_fokus_page.dart';
import '../hybrid_wrapper.dart';
import 'ds_glass_card.dart';











class _MenuItem {
  final String label;
  final IconData icon;
  final Color bg;
  final Color color;
  final bool isLarge; 
  const _MenuItem({
    required this.label,
    required this.icon,
    required this.bg,
    required this.color,
    this.isLarge = false,
  });
}



const _menus = [
  
  _MenuItem(
    label: 'Ruang Belajar',
    icon: Icons.menu_book_rounded,
    bg: Color(0xFFE0F2FE),
    color: GaraColors.dsIconBlue,
    isLarge: true,
  ),
  _MenuItem(
    label: 'Ruang Diskusi',
    icon: Icons.forum_rounded,
    bg: Color(0xFFD1FAE5), 
    color: GaraColors.dsIconEmerald,
    isLarge: true,
  ),
  
  _MenuItem(
    label: 'Ruang Kompetensi',
    icon: Icons.emoji_events_rounded, 
    bg: Color(0xFFFFE4E6), 
    color: GaraColors.dsIconRose,
  ),
  _MenuItem(
    label: 'Ruang Fokus',
    icon: Icons.center_focus_strong_rounded,
    bg: Color(0xFFF3E8FF), 
    color: GaraColors.dsIconPurple,
  ),
  _MenuItem(
    label: 'Ruang Tugas',
    icon: Icons.assignment_rounded,
    bg: Color(0xFFFFEDD5), 
    color: GaraColors.dsIconOrange,
  ),
  _MenuItem(
    label: 'Ruang Catatan',
    icon: Icons.sticky_note_2_rounded,
    bg: Color(0xFFFEF3C7), 
    color: GaraColors.dsIconAmber,
  ),
];

class MainMenuGrid extends StatelessWidget {
  final bool isOffline;
  const MainMenuGrid({super.key, this.isOffline = false});

  @override
  Widget build(BuildContext context) {
    final largeMenus  = _menus.where((m) => m.isLarge).toList();
    final smallMenus  = _menus.where((m) => !m.isLarge).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 12),
          child: Text(
            'Eksplorasi',
            style: GoogleFonts.plusJakartaSans(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: GaraColors.dsSlate800,
              letterSpacing: 0.2,
            ),
          ),
        ),

        
        Row(
          children: largeMenus.map((m) {
            final isLast = m == largeMenus.last;
            return Expanded(
              child: Padding(
                padding: EdgeInsets.only(right: isLast ? 0 : 6),
                child: _LargeMenuTile(item: m, isOffline: isOffline),
              ),
            );
          }).toList(),
        ),

        const SizedBox(height: 10),

        
        Row(
          children: smallMenus.asMap().entries.map((e) {
            final i = e.key;
            final m = e.value;
            return Expanded(
              child: Padding(
                padding: EdgeInsets.only(right: i < smallMenus.length - 1 ? 8 : 0),
                child: _SmallMenuTile(item: m, isOffline: isOffline),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }
}




class _LargeMenuTile extends StatefulWidget {
  final _MenuItem item;
  final bool isOffline;
  const _LargeMenuTile({required this.item, this.isOffline = false});

  @override
  State<_LargeMenuTile> createState() => _LargeMenuTileState();
}

class _LargeMenuTileState extends State<_LargeMenuTile> {
  bool _pressed = false;

  Future<void> _navigate(BuildContext context) async {
    final label = widget.item.label;

    if (label == 'Ruang Fokus') {
      Navigator.push(
          context, MaterialPageRoute(builder: (_) => const RuangFokusPage()));
      return;
    }
    if (label == 'Ruang Catatan') {
      Navigator.push(
          context, MaterialPageRoute(builder: (_) => const RuangCatatanPage()));
      return;
    }

    final prefs   = await SharedPreferences.getInstance();
    final token   = prefs.getString(GaraPrefKeys.authToken)       ?? '';
    final mapelId = prefs.getString(GaraPrefKeys.selectedMapelId) ?? '';

    String? targetPath;
    switch (label) {
      case 'Ruang Belajar':
        targetPath = AppConfig.ruangBelajarPath;
        break;
      case 'Ruang Tugas':
        targetPath = AppConfig.ruangTugasPath;
        break;
      case 'Ruang Kompetensi':
        targetPath = AppConfig.ruangKompetensiPath;
        break;
      case 'Ruang Diskusi':
        targetPath = AppConfig.ruangDiskusiPath;
        break;
    }

    if (targetPath == null || !context.mounted) return;

    final handoffUrl = AppConfig.getHandoffUrl(
      token:      token,
      targetPath: targetPath,
      mapelId:    mapelId.isNotEmpty ? mapelId : null,
    );

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => HybridWrapper(url: handoffUrl, pageTitle: label),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isTablet = GaraResponsive.isTablet(context);
    final cardHeight = GaraResponsive.menuCardHeight(context);
    final iconContainerSize = GaraResponsive.menuIconSize(context);
    return GestureDetector(
      onTapDown: (_) => setState(() => _pressed = true),
      onTapUp: (_) {
        setState(() => _pressed = false);
        HapticFeedback.lightImpact();
        _navigate(context);
      },
      onTapCancel: () => setState(() => _pressed = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        curve: Curves.easeOut,
        transform: Matrix4.identity()
          ..translate(0.0, _pressed ? 0.0 : -2.0)
          ..scale(_pressed ? 0.96 : 1.0),
        transformAlignment: Alignment.center,
        child: GaraGlassCard(
          borderRadius: BorderRadius.circular(24),
          padding: EdgeInsets.all(isTablet ? 20 : 16),
          child: SizedBox(
            height: cardHeight,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  width: iconContainerSize,
                  height: iconContainerSize,
                  decoration: BoxDecoration(
                    color: widget.item.color,
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: widget.item.color.withOpacity(0.30),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Icon(widget.item.icon, color: Colors.white, size: isTablet ? 26 : 22),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      widget.item.label,
                      style: GoogleFonts.plusJakartaSans(
                        fontWeight: FontWeight.w700,
                        fontSize: isTablet ? 14 : 13,
                        color: GaraColors.dsSlate800,
                        height: 1.2,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      widget.item.label == 'Ruang Belajar'
                          ? 'Materi & Modul'
                          : widget.item.label == 'Ruang Diskusi'
                              ? 'Forum & Tanya Jawab'
                              : 'Ujian & Latihan',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: isTablet ? 11 : 10,
                        fontWeight: FontWeight.w500,
                        color: GaraColors.dsSlate500,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}




class _SmallMenuTile extends StatefulWidget {
  final _MenuItem item;
  final bool isOffline;
  const _SmallMenuTile({required this.item, this.isOffline = false});

  @override
  State<_SmallMenuTile> createState() => _SmallMenuTileState();
}

class _SmallMenuTileState extends State<_SmallMenuTile> {
  bool _pressed = false;

  Future<void> _navigate(BuildContext context) async {
    final label = widget.item.label;

    if (label == 'Ruang Fokus') {
      Navigator.push(
          context, MaterialPageRoute(builder: (_) => const RuangFokusPage()));
      return;
    }
    if (label == 'Ruang Catatan') {
      Navigator.push(
          context, MaterialPageRoute(builder: (_) => const RuangCatatanPage()));
      return;
    }

    final prefs   = await SharedPreferences.getInstance();
    final token   = prefs.getString(GaraPrefKeys.authToken)       ?? '';
    final mapelId = prefs.getString(GaraPrefKeys.selectedMapelId) ?? '';

    String? targetPath;
    switch (label) {
      case 'Ruang Kompetensi':
        targetPath = AppConfig.ruangKompetensiPath;
        break;
      case 'Ruang Diskusi':
        targetPath = AppConfig.ruangDiskusiPath;
        break;
      case 'Ruang Tugas':
        targetPath = AppConfig.ruangTugasPath;
        break;
    }

    if (targetPath == null || !context.mounted) return;

    final handoffUrl = AppConfig.getHandoffUrl(
      token:      token,
      targetPath: targetPath,
      mapelId:    mapelId.isNotEmpty ? mapelId : null,
    );

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => HybridWrapper(url: handoffUrl, pageTitle: label),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isTablet = GaraResponsive.isTablet(context);
    final iconSize = GaraResponsive.smallMenuIconSize(context);
    return GestureDetector(
      onTapDown: (_) => setState(() => _pressed = true),
      onTapUp: (_) {
        setState(() => _pressed = false);
        HapticFeedback.lightImpact();
        _navigate(context);
      },
      onTapCancel: () => setState(() => _pressed = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        transform: Matrix4.identity()..scale(_pressed ? 0.93 : 1.0),
        transformAlignment: Alignment.center,
        child: GaraGlassCard(
          borderRadius: BorderRadius.circular(20),
          padding: EdgeInsets.symmetric(vertical: isTablet ? 14 : 12, horizontal: 4),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: iconSize,
                height: iconSize,
                decoration: BoxDecoration(
                  color: widget.item.bg,
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  widget.item.icon,
                  color: widget.item.color,
                  size: isTablet ? 22 : 20,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                widget.item.label,
                textAlign: TextAlign.center,
                maxLines: 2,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: isTablet ? 10.5 : 9.5,
                  fontWeight: FontWeight.w700,
                  color: GaraColors.dsSlate800,
                  height: 1.2,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
