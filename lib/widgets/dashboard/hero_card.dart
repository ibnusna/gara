























import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../utils/app_constants.dart';
import '../../utils/app_config.dart';
import '../../utils/responsive_utils.dart';
import '../../screens/pilih_mapel_page.dart';
import '../hybrid_wrapper.dart';

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

  
  Future<void> _openRuangBelajar() async {
    HapticFeedback.lightImpact();
    final prefs   = await SharedPreferences.getInstance();
    final token   = prefs.getString(GaraPrefKeys.authToken)       ?? '';
    final mapelId = prefs.getString(GaraPrefKeys.selectedMapelId) ?? '';
    if (!mounted) return;

    final handoffUrl = AppConfig.getHandoffUrl(
      token:      token,
      targetPath: AppConfig.ruangBelajarPath,
      mapelId:    mapelId.isNotEmpty ? mapelId : null,
    );

    Navigator.push(
      context,
      PageRouteBuilder(
        pageBuilder: (_, animation, __) =>
            HybridWrapper(url: handoffUrl, pageTitle: 'Ruang Belajar'),
        transitionsBuilder: (_, animation, __, child) =>
            FadeTransition(opacity: animation, child: child),
        transitionDuration: const Duration(milliseconds: 300),
      ),
    );
  }

  
  void _gantiMapel() {
    Navigator.pushReplacement(
      context,
      PageRouteBuilder(
        pageBuilder: (_, a, __) => const PilihMapelPage(),
        transitionsBuilder: (_, a, __, child) =>
            FadeTransition(opacity: a, child: child),
        transitionDuration: const Duration(milliseconds: 300),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 12),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Aktivitas Terakhir',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: GaraColors.dsSlate800,
                  letterSpacing: 0.2,
                ),
              ),
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
        ),

        
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
                  color: GaraColors.dsPrimaryDeep
                      .withOpacity(_pressed ? 0.3 : 0.50),
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

  
  Widget _buildInnerCard() {
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
                    
                    
                    GestureDetector(
                      onTap: _gantiMapel,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.20),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.menu_book_rounded,
                                size: 10, color: Color(0xFFBFDBFE)),
                            const SizedBox(width: 4),
                            Text(
                              
                              '${widget.selectedMapel} (${widget.kelasSiswa})',
                              style: GoogleFonts.plusJakartaSans(
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
                                color: const Color(0xFFBFDBFE), 
                                letterSpacing: 0.5,
                              ),
                            ),
                            const SizedBox(width: 5),
                            const Icon(Icons.swap_horiz_rounded,
                                size: 10, color: Color(0xFFBFDBFE)),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 10),

                    
                    Text(
                      'Lanjutkan\nBelajar',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: GaraResponsive.isTablet(context) ? 26 : 22,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                        height: 1.15,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text.rich(
                        TextSpan(
                          children: [
                            WidgetSpan(
                              alignment: PlaceholderAlignment.middle,
                              child: Padding(
                                padding: const EdgeInsets.only(right: 6),
                                child: Icon(Icons.play_circle_fill_rounded, size: 12, color: Colors.white.withOpacity(0.9)),
                              ),
                            ),
                            TextSpan(
                              text: widget.selectedMapel.isNotEmpty ? widget.selectedMapel : 'Bab 3: Sistem Tata Surya',
                              style: GoogleFonts.plusJakartaSans(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: Colors.white.withOpacity(0.9),
                              ),
                            ),
                          ],
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(width: 16),

              
              
              Transform.rotate(
                angle: 3 * math.pi / 180,
                child: Builder(
                  builder: (context) {
                    final fabSize = GaraResponsive.isTablet(context) ? 58.0 : 50.0;
                    return Container(
                      width: fabSize,
                      height: fabSize,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.20),
                            blurRadius: 16,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: const Icon(
                        Icons.play_arrow_rounded,
                        color: GaraColors.dsPrimaryDeep,
                        size: 28,
                      ),
                    );
                  },
                ),
              ),
            ],
          ),

          const SizedBox(height: 22),

          
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Progres Modul',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFFEFF6FF), 
                    ),
                  ),
                  Text(
                    '65%',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFFEFF6FF),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              
              Container(
                height: 8,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.20),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: FractionallySizedBox(
                  alignment: Alignment.centerLeft,
                  widthFactor: 0.65,
                  child: Container(
                    decoration: BoxDecoration(
                      
                      gradient: LinearGradient(
                        colors: [
                          Colors.white.withOpacity(0.90),
                          Colors.white,
                        ],
                      ),
                      borderRadius: BorderRadius.circular(999),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.white.withOpacity(0.50),
                          blurRadius: 6,
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
