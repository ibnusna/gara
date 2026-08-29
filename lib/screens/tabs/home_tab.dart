

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../utils/app_constants.dart';
import '../../utils/app_config.dart';
import '../../utils/responsive_utils.dart';
import '../../widgets/hybrid_wrapper.dart';
import '../../widgets/dashboard/hero_card.dart';
import '../../widgets/dashboard/main_menu_grid.dart';
import '../../widgets/dashboard/sholat_widget.dart';
import '../../widgets/dashboard/trivia_widget.dart';
import '../../widgets/dashboard/jelajah_ilmu_grid.dart';
import '../../models/pengumuman_model.dart';

const _quoteBank = [
  'Belajar bukan tentang nilai, tapi tentang pemahaman.',
  'Setiap hari adalah kesempatan untuk menjadi lebih pintar.',
  'Ilmu yang bermanfaat adalah cahaya di jalan kehidupan.',
  'Jadilah pelajar seumur hidup, bukan hanya saat ujian.',
  'Kesuksesan dimulai dari kebiasaan belajar yang konsisten.',
];

class HomeTab extends StatelessWidget {
  final String namaSiswa;
  final String kelasSiswa;
  final String selectedMapel;
  final int poinSiswa;
  final Animation<double> fadeAnimation;

  
  
  
  
  final bool? isExamActive;
  
  final List<PengumumanModel> pengumumanList;
  final bool isFetchingPengumuman;
  final Future<void> Function() onRefresh;

  const HomeTab({
    super.key,
    required this.namaSiswa,
    required this.kelasSiswa,
    required this.selectedMapel,
    this.poinSiswa = 0,
    required this.fadeAnimation,
    this.isExamActive,
    this.pengumumanList = const [],
    this.isFetchingPengumuman = true,
    required this.onRefresh,
  });

  String get _sapaan {
    final h = DateTime.now().hour;
    if (h < 12) return 'Selamat Pagi';
    if (h < 15) return 'Selamat Siang';
    if (h < 18) return 'Selamat Sore';
    return 'Selamat Malam';
  }

  String get _quote =>
      _quoteBank[DateTime.now().difference(DateTime(DateTime.now().year)).inDays % _quoteBank.length];

  @override
  Widget build(BuildContext context) {
    final hPad = GaraResponsive.hPad(context);
    return FadeTransition(
      opacity: fadeAnimation,
      child: RefreshIndicator(
        onRefresh: onRefresh,
        color: GaraColors.dsPrimaryDeep,
        backgroundColor: Colors.white,
        child: CustomScrollView(
          physics: const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics()),
          slivers: [
          SliverPadding(
            padding: EdgeInsets.fromLTRB(hPad, 8, hPad, 16),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                HeroCard(
                  namaSiswa: namaSiswa,
                  kelasSiswa: kelasSiswa,
                  selectedMapel: selectedMapel,
                  poinSiswa: poinSiswa,
                  sapaan: _sapaan,
                  quoteHarian: _quote,
                ),
                const SizedBox(height: 20),

                
                
                  if (isExamActive == null) ...[
                    _ExamBannerSkeleton(),
                    const SizedBox(height: 20),
                  ] else if (isExamActive == true) ...[
                    _ExamActiveBanner(),
                    const SizedBox(height: 20),
                  ],

                
                _buildPengumumanSection(),
                const SizedBox(height: 20),

                
                const MainMenuGrid(),
                const SizedBox(height: 24),
                
                const SholatWidget(),
                const SizedBox(height: 12),
                const TriviaWidget(),
                const SizedBox(height: 24),
                
                const JelajahIlmuGrid(),
                const SizedBox(height: 24),
              ]),
            ),
          ),
        ],
      ),
      ),
    );
  }

  Widget _buildPengumumanSection() {
    // Pengumuman berjalan sepenuhnya di background.
    // Jika belum ada data atau sedang fetch, tidak tampilkan apapun.
    if (pengumumanList.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 12),
          child: Row(
            children: [
              const Icon(Icons.campaign_rounded, color: GaraColors.dsRose500, size: 20),
              const SizedBox(width: 8),
              Text(
                'Pengumuman Terbaru',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: GaraColors.dsSlate800,
                ),
              ),
            ],
          ),
        ),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          padding: EdgeInsets.zero,
          itemCount: pengumumanList.length,
          separatorBuilder: (_, __) => const SizedBox(height: 12),
          itemBuilder: (context, index) {
            final p = pengumumanList[index];
            return Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: GaraColors.dsSlate200),
                boxShadow: [
                  BoxShadow(
                    color: GaraColors.dsSlate800.withOpacity(0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    p.judul,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: GaraColors.dsSlate800,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    p.isi,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: GaraColors.dsSlate500,
                      height: 1.4,
                    ),
                  ),
                  if (p.tanggal != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      p.tanggal!,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                        color: GaraColors.dsSlate400,
                      ),
                    ),
                  ],
                ],
              ),
            );
          },
        ),
      ],
    );
  }

}




class _ExamBannerSkeleton extends StatefulWidget {
  @override
  State<_ExamBannerSkeleton> createState() => _ExamBannerSkeletonState();
}

class _ExamBannerSkeletonState extends State<_ExamBannerSkeleton>
    with SingleTickerProviderStateMixin {
  late AnimationController _shimmerCtrl;
  late Animation<double> _shimmerAnim;

  @override
  void initState() {
    super.initState();
    _shimmerCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat(reverse: true);
    _shimmerAnim = Tween<double>(begin: 0.3, end: 0.7).animate(
      CurvedAnimation(parent: _shimmerCtrl, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _shimmerCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _shimmerAnim,
      builder: (_, __) => Container(
        height: 80,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(
            colors: [
              const Color(0xFFFFD0D0).withOpacity(_shimmerAnim.value * 0.8),
              const Color(0xFFFFB8A0).withOpacity(_shimmerAnim.value),
            ],
          ),
        ),
        child: Row(
          children: [
            const SizedBox(width: 16),
            Container(
              width: 48, height: 48,
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.3),
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            const SizedBox(width: 14),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 120, height: 14,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.5),
                    borderRadius: BorderRadius.circular(6),
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  width: 80, height: 10,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.3),
                    borderRadius: BorderRadius.circular(6),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}


class _ExamActiveBanner extends StatefulWidget {
  @override
  State<_ExamActiveBanner> createState() => _ExamActiveBannerState();
}

class _ExamActiveBannerState extends State<_ExamActiveBanner>
    with SingleTickerProviderStateMixin {
  bool _pressed = false;
  late AnimationController _pulseCtrl;
  late Animation<double> _pulseAnim;

  @override
  void initState() {
    super.initState();
    _pulseCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);
    _pulseAnim = Tween<double>(begin: 0.85, end: 1.0).animate(
      CurvedAnimation(parent: _pulseCtrl, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _pulseCtrl.dispose();
    super.dispose();
  }

  Future<void> _openExam(BuildContext ctx) async {
    HapticFeedback.mediumImpact();
    final prefs   = await SharedPreferences.getInstance();
    final token   = prefs.getString(GaraPrefKeys.authToken)       ?? '';
    final mapelId = prefs.getString(GaraPrefKeys.selectedMapelId) ?? '';
    if (!ctx.mounted) return;

    
    final handoffUrl = AppConfig.getHandoffUrl(
      token:      token,
      targetPath: AppConfig.ruangUjianEntryPath, 
      mapelId:    mapelId.isNotEmpty ? mapelId : null,
    );

    Navigator.push(
      ctx,
      PageRouteBuilder(
        pageBuilder: (_, animation, __) => HybridWrapper(
          url:            handoffUrl,
          pageTitle:      'Ruang Ujian',
          enableExamMode: false, 
        ),
        transitionsBuilder: (_, animation, __, child) => FadeTransition(
          opacity: animation,
          child: child,
        ),
        transitionDuration: const Duration(milliseconds: 350),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => setState(() => _pressed = true),
      onTapUp: (_) {
        setState(() => _pressed = false);
        _openExam(context);
      },
      onTapCancel: () => setState(() => _pressed = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        transform: Matrix4.identity()..scale(_pressed ? 0.97 : 1.0),
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              GaraColors.examGradientStart,
              GaraColors.examGradientEnd,
            ],
          ),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: GaraColors.examGradientStart.withOpacity(_pressed ? 0.2 : 0.4),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Row(
          children: [
            
            AnimatedBuilder(
              animation: _pulseAnim,
              builder: (_, __) => Transform.scale(
                scale: _pulseAnim.value,
                child: Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.25),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.laptop_mac_rounded,
                    color: Colors.white,
                    size: 22,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'RUANG ASESMEN',
                    style: GoogleFonts.poppins(
                      color: Colors.white,
                      fontWeight: FontWeight.w700,
                      fontSize: 16,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      
                      AnimatedBuilder(
                        animation: _pulseAnim,
                        builder: (_, __) => Container(
                          width: 7,
                          height: 7,
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(_pulseAnim.value),
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: Colors.white.withOpacity(0.5),
                                blurRadius: 4,
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 5),
                      Text(
                        'Ujian Telah Dibuka!',
                        style: GoogleFonts.poppins(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const Icon(
              Icons.chevron_right_rounded,
              color: Colors.white,
              size: 28,
            ),
          ],
        ),
      ),
    );
  }
}
