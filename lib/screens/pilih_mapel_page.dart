import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';
import '../utils/performance_config.dart';
import '../utils/route_builders.dart';
import '../utils/responsive_utils.dart';
import '../models/mapel_model.dart';
import '../services/auth_service.dart';
import '../widgets/hybrid_wrapper.dart';
import '../widgets/dashboard/ds_glass_card.dart';
import 'dashboard_page.dart';
import 'smart_connect_page.dart';


class PilihMapelPage extends StatefulWidget {
  const PilihMapelPage({super.key});

  @override
  State<PilihMapelPage> createState() => _PilihMapelPageState();
}

class _PilihMapelPageState extends State<PilihMapelPage> with TickerProviderStateMixin {
  bool _isLoading = true;
  String _namaSiswa = '';
  String _kelasSiswa = '';
  String _sekolahNama = 'Garuda Akademi';
  bool _gateUjianOpen = false;
  List<MapelModel> _mapelList = [];
  String? _errorMessage;
  bool _isFromCache = false; // true jika data dari cache (offline)

  late List<AnimationController> _cardControllers = [];
  late List<Animation<double>> _cardFadeAnims = [];
  late List<Animation<Offset>> _cardSlideAnims = [];

  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

  String get _firstName => _namaSiswa.split(' ').first;

  @override
  void initState() {
    super.initState();
    _blobCtrl1 = AnimationController(vsync: this, duration: const Duration(milliseconds: 25000))..repeat(reverse: true);
    _blobCtrl2 = AnimationController(vsync: this, duration: const Duration(milliseconds: 22000))..repeat(reverse: true);
    _blobCtrl3 = AnimationController(vsync: this, duration: const Duration(milliseconds: 28000))..repeat(reverse: true);
    _blobCtrl2.value = 0.2;
    _blobCtrl3.value = 0.4;
    _fetchMapelList();
  }

  Future<void> _fetchMapelList() async {
    setState(() { _isLoading = true; _errorMessage = null; });
    final result = await AuthService.getMapelList();
    if (!mounted) return;
    if (!result.success) {
      final msg = result.errorMessage?.toLowerCase() ?? '';
      if (msg.contains('sesi') || msg.contains('login') || msg.contains('unauth') || msg.contains('expired')) {
        // Sebelum logout karena sesi, cek apakah ada cache untuk mode offline
        final cached = await AuthService.getCachedMapelList();
        if (cached != null && mounted) {
          setState(() {
            _isLoading = false;
            _namaSiswa = cached.namaSiswa;
            _kelasSiswa = cached.kelasSiswa;
            _sekolahNama = cached.sekolahNama;
            _gateUjianOpen = cached.gateUjianOpen;
            _mapelList = cached.mapelList;
            _isFromCache = true;
          });
          _initAnimations();
          return;
        }
        await AuthService.clearSession();
        if (mounted) Navigator.pushNamedAndRemoveUntil(context, GaraRoutes.login, (route) => false);
        return;
      }
      setState(() { _isLoading = false; _errorMessage = result.errorMessage; _isFromCache = false; });
      return;
    }
    setState(() {
      _isLoading = false;
      _namaSiswa = result.namaSiswa;
      _kelasSiswa = result.kelasSiswa;
      _sekolahNama = result.sekolahNama;
      _gateUjianOpen = result.gateUjianOpen;
      _mapelList = result.mapelList;
      _isFromCache = result.fromCache;
    });
    _initAnimations();
  }

  void _initAnimations() {
    final int cardCount = (_gateUjianOpen ? 1 : 0) + _mapelList.length;
    _cardControllers = List.generate(cardCount, (i) => AnimationController(vsync: this, duration: PerformanceConfig.cardSlideDuration));
    _cardFadeAnims = _cardControllers.map((ctrl) => Tween<double>(begin: 0.0, end: 1.0).animate(CurvedAnimation(parent: ctrl, curve: Curves.easeOut))).toList();
    _cardSlideAnims = _cardControllers.map((ctrl) => Tween<Offset>(begin: const Offset(0, 0.3), end: Offset.zero).animate(CurvedAnimation(parent: ctrl, curve: Curves.easeOut))).toList();
    for (int i = 0; i < _cardControllers.length; i++) {
      Future.delayed(Duration(milliseconds: 80 + (i * 80)), () { if (mounted) _cardControllers[i].forward(); });
    }
  }

  @override
  void dispose() {
    for (final c in _cardControllers) c.dispose();
    _blobCtrl1.dispose(); _blobCtrl2.dispose(); _blobCtrl3.dispose();
    super.dispose();
  }

  Future<void> _navigateToDashboard(MapelModel mapel) async {
    HapticFeedback.lightImpact();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(GaraPrefKeys.selectedMapel, mapel.namaMapel);
    await prefs.setString(GaraPrefKeys.selectedMapelId, mapel.id.toString());
    if (mounted) Navigator.push(context, garaSlideRightRoute(DashboardPage(selectedMapel: mapel.namaMapel)));
  }

  void _handleLogout() {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Keluar Akun', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700, color: GaraColors.dsSlate800)),
        content: Text('Yakin ingin keluar dari akun?', style: GoogleFonts.plusJakartaSans(color: GaraColors.dsSlate500)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text('Batal', style: GoogleFonts.plusJakartaSans(color: GaraColors.dsSlate500, fontWeight: FontWeight.w600))),
          TextButton(
            onPressed: () async {
              Navigator.pop(context);
              await AuthService.logout();
              if (mounted) Navigator.pushNamedAndRemoveUntil(context, GaraRoutes.login, (route) => false);
            },
            child: Text('Keluar', style: GoogleFonts.plusJakartaSans(color: GaraColors.dsRose500, fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.dsBgBody,
      body: Stack(
        children: [
          Positioned.fill(
            child: AnimatedBuilder(
              animation: Listenable.merge([_blobCtrl1, _blobCtrl2, _blobCtrl3]),
              builder: (context, _) => CustomPaint(painter: _PilihMapelMeshPainter(t1: _blobCtrl1.value, t2: _blobCtrl2.value, t3: _blobCtrl3.value)),
            ),
          ),
          SafeArea(
            child: Center(
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  maxWidth: GaraResponsive.pageMaxWidth(context),
                ),
                child: _buildBody(),
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: _buildSmartConnectButton(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) return const Center(child: CircularProgressIndicator(color: GaraColors.dsPrimaryDeep));
    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(30),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.wifi_off_rounded, size: 60, color: GaraColors.dsSlate400),
              const SizedBox(height: 16),
              Text(_errorMessage!, textAlign: TextAlign.center, style: GoogleFonts.plusJakartaSans(fontSize: 16, color: GaraColors.dsSlate500)),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _fetchMapelList,
                style: ElevatedButton.styleFrom(backgroundColor: GaraColors.dsPrimaryDeep, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                child: Text('Coba Lagi', style: GoogleFonts.plusJakartaSans(color: Colors.white, fontWeight: FontWeight.w600)),
              ),
            ],
          ),
        ),
      );
    }
    return Column(
      children: [
        // Banner offline jika data dari cache
        if (_isFromCache)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            color: const Color(0xFFFEF3C7),
            child: Row(
              children: [
                const Icon(Icons.wifi_off_rounded, size: 16, color: Color(0xFFD97706)),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Mode offline — menampilkan data terakhir yang tersimpan',
                    style: GoogleFonts.plusJakartaSans(fontSize: 12, color: const Color(0xFFD97706), fontWeight: FontWeight.w600),
                  ),
                ),
                GestureDetector(
                  onTap: _fetchMapelList,
                  child: Text('Perbarui', style: GoogleFonts.plusJakartaSans(fontSize: 12, color: const Color(0xFFD97706), fontWeight: FontWeight.w700, decoration: TextDecoration.underline)),
                ),
              ],
            ),
          ),
        Expanded(
          child: CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              SliverToBoxAdapter(child: _buildHeader()),
              SliverPadding(
                padding: EdgeInsets.symmetric(horizontal: GaraResponsive.hPad(context)),
                sliver: SliverList(delegate: SliverChildListDelegate(_buildCardList())),
              ),
              SliverToBoxAdapter(child: _buildLogoutArea()),
              const SliverToBoxAdapter(child: SizedBox(height: 100)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildHeader() {
    final isTablet = GaraResponsive.isTablet(context);
    final hPad = GaraResponsive.hPad(context);
    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, isTablet ? 48 : 40, hPad, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Halo, $_firstName! 👋', style: GoogleFonts.plusJakartaSans(fontSize: isTablet ? 30 : 26, fontWeight: FontWeight.w800, color: GaraColors.dsSlate800)),
          const SizedBox(height: 8),
          Text('Kamu berada di Kelas $_kelasSiswa.\nPilih mata pelajaran untuk mulai belajar.', style: GoogleFonts.plusJakartaSans(fontSize: isTablet ? 15 : 14, fontWeight: FontWeight.w500, color: GaraColors.dsSlate500, height: 1.4)),
        ],
      ),
    );
  }

  List<Widget> _buildCardList() {
    final List<Widget> cards = [];
    int animIndex = 0;
    if (_gateUjianOpen) { cards.add(_buildUjianCard(animIndex)); cards.add(const SizedBox(height: 15)); animIndex++; }
    for (int i = 0; i < _mapelList.length; i++) {
      cards.add(_buildMapelCard(_mapelList[i], animIndex));
      if (i < _mapelList.length - 1) cards.add(const SizedBox(height: 15));
      animIndex++;
    }
    return cards;
  }

  Widget _buildUjianCard(int animIndex) {
    return RepaintBoundary(
      child: FadeTransition(
        opacity: _cardFadeAnims[animIndex],
        child: SlideTransition(
          position: _cardSlideAnims[animIndex],
          child: GestureDetector(
            onTap: () async {
              HapticFeedback.lightImpact();
              final prefs = await SharedPreferences.getInstance();
              final token = prefs.getString(GaraPrefKeys.authToken) ?? '';
              final mapelId = prefs.getString(GaraPrefKeys.selectedMapelId) ?? '';
              if (!context.mounted) return;
              Navigator.push(context, MaterialPageRoute(builder: (_) => HybridWrapper(url: AppConfig.getHandoffUrl(token: token, targetPath: AppConfig.ruangUjianEntryPath, mapelId: mapelId.isNotEmpty ? mapelId : null), pageTitle: 'Ruang Ujian', enableExamMode: false)));
            },
            child: Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [GaraColors.dsRose500, const Color(0xFFF97316)]),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [BoxShadow(color: GaraColors.dsRose500.withOpacity(0.35), blurRadius: 20, offset: const Offset(0, 8))],
              ),
              child: Row(
                children: [
                  Container(width: 50, height: 50, decoration: BoxDecoration(color: Colors.white.withOpacity(0.25), borderRadius: BorderRadius.circular(14)), child: const Icon(Icons.laptop_mac_rounded, color: Colors.white, size: 24)),
                  const SizedBox(width: 15),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('RUANG ASESMEN', style: GoogleFonts.plusJakartaSans(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18, letterSpacing: 0.5)),
                        const SizedBox(height: 4),
                        Row(children: [Container(width: 8, height: 8, decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle)), const SizedBox(width: 6), Text('Ujian Telah Dibuka!', style: GoogleFonts.plusJakartaSans(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600))]),
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right_rounded, color: Colors.white, size: 28),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildMapelCard(MapelModel mapel, int animIndex) {
    return RepaintBoundary(
      child: FadeTransition(
        opacity: _cardFadeAnims[animIndex],
        child: SlideTransition(
          position: _cardSlideAnims[animIndex],
          child: _MapelCardTile(mapel: mapel, onTap: () => _navigateToDashboard(mapel)),
        ),
      ),
    );
  }

  Widget _buildLogoutArea() {
    final hPad = GaraResponsive.hPad(context);
    return Container(
      padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 24),
      child: Center(
        child: GestureDetector(
          onTap: _handleLogout,
          child: GaraGlassCardStrong(
            borderRadius: BorderRadius.circular(999),
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.logout_rounded, color: GaraColors.dsRose500, size: 18),
                const SizedBox(width: 8),
                Text('Keluar Akun', style: GoogleFonts.plusJakartaSans(color: GaraColors.dsRose500, fontWeight: FontWeight.w700, fontSize: 14)),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSmartConnectButton() {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const SmartConnectPage()),
        );
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 20),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: GaraColors.dsSlate800.withOpacity(0.1),
              blurRadius: 15,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Image.asset(
          'assets/images/3dlogo.png',
          width: 40,
          height: 40,
        ),
      ),
    );
  }
}

class _MapelCardTile extends StatefulWidget {
  final MapelModel mapel;
  final VoidCallback onTap;
  const _MapelCardTile({required this.mapel, required this.onTap});
  @override
  State<_MapelCardTile> createState() => _MapelCardTileState();
}

class _MapelCardTileState extends State<_MapelCardTile> {
  bool _isPressed = false;

  String _getHariIniString() {
    final int weekday = DateTime.now().weekday;
    switch (weekday) {
      case 1: return 'Senin';
      case 2: return 'Selasa';
      case 3: return 'Rabu';
      case 4: return 'Kamis';
      case 5: return 'Jumat';
      case 6: return 'Sabtu';
      case 7: return 'Minggu';
      default: return '';
    }
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => setState(() => _isPressed = true),
      onTapUp: (_) { setState(() => _isPressed = false); widget.onTap(); },
      onTapCancel: () => setState(() => _isPressed = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        transform: Matrix4.identity()..scale(_isPressed ? 0.96 : 1.0),
        transformAlignment: Alignment.center,
        child: GaraGlassCard(
          borderRadius: BorderRadius.circular(20),
          padding: const EdgeInsets.all(20),
          child: Row(
            children: [
              Container(width: 48, height: 48, decoration: BoxDecoration(color: GaraColors.dsPrimaryBright.withOpacity(0.15), borderRadius: BorderRadius.circular(14)), child: const Icon(Icons.menu_book_rounded, color: GaraColors.dsPrimaryDeep, size: 24)),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(widget.mapel.namaMapel, style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700, fontSize: 16, color: GaraColors.dsSlate800)),
                    if (widget.mapel.jadwal.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        children: widget.mapel.jadwal.map((jadwal) {
                          final bool isHariIni = jadwal.hari == _getHariIniString();
                          return Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              gradient: isHariIni 
                                ? const LinearGradient(colors: [GaraColors.dsPrimaryDeep, GaraColors.dsPrimaryBright])
                                : null,
                              color: isHariIni ? null : GaraColors.dsSlate200.withOpacity(0.5),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.schedule_rounded, size: 12, color: isHariIni ? Colors.white : GaraColors.dsSlate500),
                                const SizedBox(width: 4),
                                Text(
                                  '${jadwal.hari}, ${jadwal.jamMulai} - ${jadwal.jamSelesai}',
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 11,
                                    fontWeight: isHariIni ? FontWeight.w700 : FontWeight.w600,
                                    color: isHariIni ? Colors.white : GaraColors.dsSlate500,
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    ],
                  ],
                ),
              ),
              const Icon(Icons.chevron_right_rounded, color: GaraColors.dsSlate400, size: 24),
            ],
          ),
        ),
      ),
    );
  }
}

class _PilihMapelMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _PilihMapelMeshPainter({required this.t1, required this.t2, required this.t3});
  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(Rect.fromLTWH(0, 0, size.width, size.height), Paint()..color = GaraColors.dsMeshBase);
    _blob(canvas, size, color: GaraColors.dsBlob1.withOpacity(0.75), radius: size.width * 0.38, baseX: size.width * 0.0, baseY: size.height * 0.0, t: t1, dx: 25, dy: -40, blur: 55);
    _blob(canvas, size, color: GaraColors.dsBlob2.withOpacity(0.70), radius: size.width * 0.42, baseX: size.width * 0.85, baseY: size.height * 0.35, t: t2, dx: -20, dy: 25, blur: 65);
    _blob(canvas, size, color: GaraColors.dsBlob3.withOpacity(0.65), radius: size.width * 0.32, baseX: size.width * 0.15, baseY: size.height * 0.88, t: t3, dx: 18, dy: -25, blur: 50);
  }
  void _blob(Canvas canvas, Size size, {required Color color, required double radius, required double baseX, required double baseY, required double t, required double dx, required double dy, required double blur}) {
    final progress = math.sin(t * math.pi);
    canvas.drawCircle(Offset(baseX + dx * progress, baseY + dy * progress), radius * (1.0 + 0.08 * progress), Paint()..color = color..maskFilter = MaskFilter.blur(BlurStyle.normal, blur));
  }
  @override
  bool shouldRepaint(_PilihMapelMeshPainter old) => old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
