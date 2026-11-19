import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../utils/app_constants.dart';
import '../../utils/app_config.dart';
import '../../utils/responsive_utils.dart';
import '../../screens/pilih_mapel_page.dart';
import '../../services/auth_service.dart';
import '../hybrid_wrapper.dart';
import 'hero_play_button.dart';
import 'hero_progress_bar.dart';
import 'hero_subject_badge.dart';

/// Overflow-resilient HeroCard for Student Dashboard (< 200 lines).
class HeroCard extends StatefulWidget {
  final String selectedMapel;
  final String kelasSiswa;
  final String namaSiswa;
  final String sapaan;
  final String quoteHarian;
  final int poinSiswa;

  const HeroCard({
    super.key,
    required this.selectedMapel,
    required this.kelasSiswa,
    this.namaSiswa = '',
    this.sapaan = '',
    this.quoteHarian = '',
    this.poinSiswa = 0,
  });

  @override
  State<HeroCard> createState() => _HeroCardState();
}

class _HeroCardState extends State<HeroCard> {
  bool _pressed = false;

  PageRouteBuilder<T> _fadeRoute<T>(Widget page) => PageRouteBuilder<T>(
    pageBuilder: (_, a, __) => page,
    transitionsBuilder: (_, a, __, c) => FadeTransition(opacity: a, child: c),
    transitionDuration: const Duration(milliseconds: 300),
  );

  Future<void> _openRuangBelajar() async {
    HapticFeedback.lightImpact();
    final prefs = await SharedPreferences.getInstance();
    final token = await AuthService.getToken() ?? prefs.getString(GaraPrefKeys.authToken) ?? '';
    final mapelId = prefs.getString(GaraPrefKeys.selectedMapelId) ?? '';
    if (!mounted) return;

    final url = AppConfig.getHandoffUrl(
      token: token,
      targetPath: AppConfig.ruangBelajarPath,
      mapelId: mapelId.isNotEmpty ? mapelId : null,
    );

    Navigator.push(context, _fadeRoute(HybridWrapper(url: url, pageTitle: 'Ruang Belajar')));
  }

  void _gantiMapel() {
    Navigator.pushReplacement(context, _fadeRoute(const PilihMapelPage()));
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildHeaderRow(),
        GestureDetector(
          onTapDown: (_) => setState(() => _pressed = true),
          onTapUp: (_) {
            setState(() => _pressed = false);
            _openRuangBelajar();
          },
          onTapCancel: () => setState(() => _pressed = false),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 150),
            curve: Curves.easeOut,
            transform: Matrix4.identity()..scale(_pressed ? 0.98 : 1.0),
            transformAlignment: Alignment.center,
            width: double.infinity,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [GaraColors.dsPrimaryBright, GaraColors.dsPrimaryDeep],
              ),
              borderRadius: BorderRadius.circular(32),
              boxShadow: [
                BoxShadow(
                  color: GaraColors.dsPrimaryDeep.withOpacity(_pressed ? 0.3 : 0.50),
                  blurRadius: 32,
                  spreadRadius: -8,
                  offset: const Offset(0, 12),
                ),
              ],
            ),
            padding: const EdgeInsets.all(6),
            child: _buildInnerCard(),
          ),
        ),
      ],
    );
  }

  Widget _buildHeaderRow() {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              'Aktivitas Terakhir',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: GaraColors.dsSlate800,
                letterSpacing: 0.2,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          const SizedBox(width: 8),
          GestureDetector(
            onTap: _gantiMapel,
            child: Text(
              'Lihat Semua',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: GaraColors.dsGaraLight,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInnerCard() {
    final isTablet = GaraResponsive.isTablet(context);
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.10),
        borderRadius: BorderRadius.circular(26),
        border: Border.all(color: Colors.white.withOpacity(0.25)),
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    HeroSubjectBadge(
                      selectedMapel: widget.selectedMapel,
                      kelasSiswa: widget.kelasSiswa,
                      onTap: _gantiMapel,
                    ),
                    const SizedBox(height: 10),
                    Text(
                      'Lanjutkan\nBelajar',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: isTablet ? 26 : 22,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                        height: 1.15,
                      ),
                    ),
                    const SizedBox(height: 8),
                    HeroActivityPill(
                      title: widget.selectedMapel.isNotEmpty
                          ? widget.selectedMapel
                          : 'Bab 3: Sistem Tata Surya',
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 16),
              const HeroPlayButton(),
            ],
          ),
          const SizedBox(height: 22),
          const HeroProgressBar(progress: 0.65),
        ],
      ),
    );
  }
}
