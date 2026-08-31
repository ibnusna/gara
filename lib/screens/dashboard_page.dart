
















import 'dart:async';
import 'dart:convert';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';
import '../utils/responsive_utils.dart';
import '../services/auth_service.dart';
import '../services/notification_service.dart';
import '../widgets/hybrid_wrapper.dart';
import '../widgets/dashboard/ds_glass_card.dart';
import '../models/pengumuman_model.dart';
import '../services/pengumuman_service.dart';
import 'tabs/home_tab.dart';
import 'tabs/notifikasi_tab.dart';
import 'tabs/akun_tab.dart';
import 'session_expired_page.dart';

class DashboardPage extends StatefulWidget {
  final String selectedMapel;
  const DashboardPage({super.key, required this.selectedMapel});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage>
    with TickerProviderStateMixin {
  int _navIndex = 0;

  
  String _nama    = '';
  String _kelas   = '';
  String _sekolah = 'Garuda Akademi';
  int _poin       = 0;

  
  bool? _isExamActive;

  
  List<PengumumanModel> _pengumumanList = [];
  bool _isFetchingPengumuman = false; // false: tidak tampilkan spinner di awal

  
  Timer? _pollTimer;
  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();
  int _prevUnreadCount = 0;
  // Mencegah SessionExpiredPage muncul lebih dari sekali
  bool _sessionExpiredHandled = false;

  
  late final AnimationController _fadeCtrl;
  late final Animation<double> _fadeAnim;

  
  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

  @override
  void initState() {
    super.initState();

    
    _fadeCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    )..forward();
    _fadeAnim = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOut);

    
    _blobCtrl1 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 25000),
    )..repeat(reverse: true);
    _blobCtrl2 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 22000),
    )..repeat(reverse: true);
    _blobCtrl3 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 28000),
    )..repeat(reverse: true);
    
    _blobCtrl2.value = 0.2;
    _blobCtrl3.value = 0.4;

    _loadUserData();
    _refreshProfile(); 
    _loadExamStatus(); 
    _fetchPengumuman(); 
    _initNotifications();
    _checkUnreadCount(isInitial: true);
  }

  
  Future<void> _handleRefresh() async {
    HapticFeedback.lightImpact();
    await Future.wait([
      _refreshProfile(),
      _loadExamStatus(),
      _fetchPengumuman(),
      _checkUnreadCount(),
    ]);
  }

  
  String get _sapaan {
    final h = DateTime.now().hour;
    if (h < 12) return 'Selamat Pagi';
    if (h < 15) return 'Selamat Siang';
    if (h < 18) return 'Selamat Sore';
    return 'Selamat Malam';
  }

  Future<void> _initNotifications() async {
    const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
    const initSettings = InitializationSettings(android: androidInit);
    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (response) async {
        
        
        final rawPayload = response.payload;
        if (rawPayload != null && rawPayload.isNotEmpty) {
          try {
            final Map<String, dynamic> payload = jsonDecode(rawPayload);
            final String type       = payload['type']        ?? 'notif';
            final String targetPath = payload['target_path'] ?? '';

            if ((type == 'exam' || type == 'web') && targetPath.isNotEmpty) {
              final prefs = await SharedPreferences.getInstance();
              final token   = prefs.getString(GaraPrefKeys.authToken) ?? '';
              final mapelId = prefs.getString(GaraPrefKeys.selectedMapelId) ?? '';

              final handoffUrl = AppConfig.getHandoffUrl(
                token:      token,
                targetPath: targetPath,
                mapelId:    (mapelId.isNotEmpty && type == 'web') ? mapelId : null,
              );

              if (mounted) {
                Navigator.push(
                  context,
                  PageRouteBuilder(
                    pageBuilder: (_, animation, __) => HybridWrapper(
                      url:       handoffUrl,
                      pageTitle: payload['title'] ?? 'GARA',
                    ),
                    transitionsBuilder: (_, animation, __, child) => FadeTransition(
                      opacity: CurvedAnimation(parent: animation, curve: Curves.easeInOutCubic),
                      child: child,
                    ),
                    transitionDuration: const Duration(milliseconds: 300),
                  ),
                );
              }
              return;
            }
          } catch (_) {}
        }
        if (mounted) setState(() => _navIndex = 1);
      },
    );
  }



  Future<void> _checkUnreadCount({bool isInitial = false}) async {
    final count = await NotificationService.getUnreadCount();
    if (isInitial) { _prevUnreadCount = count; return; }
    if (count > _prevUnreadCount) _showLocalNotification(count);
    _prevUnreadCount = count;
  }

  Future<void> _showLocalNotification(int count) async {
    const androidDetails = AndroidNotificationDetails(
      'gara_notif_channel', 'GARA Notifications',
      channelDescription: 'Notifikasi sistem GARA',
      importance: Importance.max, priority: Priority.high,
      sound: RawResourceAndroidNotificationSound('gara_sound'),
      icon: '@mipmap/ic_launcher',
      color: Color(0xFF3B82F6),
    );
    const details = NotificationDetails(android: androidDetails);
    await _localNotifications.show(0, 'Pemberitahuan Baru',
        'Anda memiliki $count notifikasi yang belum dibaca.', details);
  }

  Future<void> _loadUserData() async {
    final prefs = await SharedPreferences.getInstance();
    if (!mounted) return;
    setState(() {
      _nama    = prefs.getString(GaraPrefKeys.namaSiswa)  ?? 'Siswa GARA';
      _kelas   = prefs.getString(GaraPrefKeys.kelasSiswa) ?? '';
      _sekolah = 'Garuda Akademi';
      _poin    = prefs.getInt('student_points')           ?? 0;
    });
  }

  Future<void> _refreshProfile() async {
    final result = await AuthService.getProfileWithStatus();
    if (result == AuthProfileStatus.sessionExpired) {
      _handleSessionExpired();
      return;
    }
    if (result == AuthProfileStatus.success && mounted) _loadUserData();
  }

  /// Tampilkan halaman peringatan sesi berakhir.
  /// Dipanggil saat token invalid terdeteksi dari polling atau API.
  void _handleSessionExpired() {
    if (_sessionExpiredHandled) return; // Hanya sekali
    _sessionExpiredHandled = true;
    _pollTimer?.cancel(); // Stop polling
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      PageRouteBuilder(
        pageBuilder: (_, __, ___) => const SessionExpiredPage(
          reason:
              'Akun Anda telah masuk dari perangkat lain. '
              'Sesi di perangkat ini telah diakhiri untuk '
              'menjaga keamanan akun Anda.',
        ),
        transitionsBuilder: (_, anim, __, child) =>
            FadeTransition(opacity: anim, child: child),
        transitionDuration: const Duration(milliseconds: 400),
      ),
      (route) => false,
    );
  }

  Future<void> _loadExamStatus() async {
    final status = await AuthService.getExamStatus();
    if (mounted) setState(() => _isExamActive = status ?? false);
  }

  Future<void> _fetchPengumuman() async {
    if (!mounted) return;
    // Tidak set _isFetchingPengumuman = true agar tidak ada rebuild / loading state.
    // Pengumuman berjalan sepenuhnya di background — UI hanya diupdate saat data siap.
    final result = await PengumumanService.getPengumumanList();
    if (mounted) {
      setState(() {
        _isFetchingPengumuman = false;
        if (result.success) {
          _pengumumanList = result.data;
        }
      });
    }
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _fadeCtrl.dispose();
    _blobCtrl1.dispose();
    _blobCtrl2.dispose();
    _blobCtrl3.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      extendBody: true,
      backgroundColor: GaraColors.dsBgBody,

      body: Stack(
        children: [
          Positioned.fill(
            child: AnimatedBuilder(
              animation: Listenable.merge([_blobCtrl1, _blobCtrl2, _blobCtrl3]),
              builder: (context, _) => CustomPaint(
                painter: _DashboardMeshPainter(
                  t1: _blobCtrl1.value,
                  t2: _blobCtrl2.value,
                  t3: _blobCtrl3.value,
                ),
              ),
            ),
          ),

          SafeArea(
            child: Column(
              children: [
                _AppHeader(
                      namaSiswa: _nama,
                      sapaan: _sapaan,
                      onBellTap: () => setState(() => _navIndex = 1),
                    ),
                    Expanded(
                      child: ShaderMask(
                        shaderCallback: (Rect bounds) {
                          return const LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Colors.transparent,
                              Colors.white,
                              Colors.white,
                              Colors.transparent,
                            ],
                            stops: [0.0, 0.04, 0.92, 1.0],
                          ).createShader(bounds);
                        },
                        blendMode: BlendMode.dstIn,
                        child: IndexedStack(
                          index: _navIndex,
                          children: [
                            HomeTab(
                              namaSiswa: _nama,
                              kelasSiswa: _kelas,
                              selectedMapel: widget.selectedMapel,
                              poinSiswa: _poin,
                              fadeAnimation: _fadeAnim,
                              isExamActive: _isExamActive,
                              pengumumanList: _pengumumanList,
                              isFetchingPengumuman: _isFetchingPengumuman,
                              onRefresh: _handleRefresh,
                            ),
                            const NotifikasiTab(),
                            AkunTab(
                              namaSiswa: _nama,
                              kelasSiswa: _kelas,
                              sekolahNama: _sekolah,
                              selectedMapel: widget.selectedMapel,
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
          ),
        ],
      ),

      bottomNavigationBar: _BottomNav(
        currentIndex: _navIndex,
        onTap: (i) {
          HapticFeedback.selectionClick();
          setState(() => _navIndex = i);
        },
      ),
    );
  }
}




class _DashboardMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _DashboardMeshPainter({required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );

    
    _blob(canvas, size,
      color: GaraColors.dsBlob1.withOpacity(0.75),
      radius: size.width * 0.38,
      baseX: size.width * 0.0,
      baseY: size.height * 0.0,
      t: t1, dx: 25, dy: -40, blur: 55,
    );

    
    _blob(canvas, size,
      color: GaraColors.dsBlob2.withOpacity(0.70),
      radius: size.width * 0.42,
      baseX: size.width * 0.85,
      baseY: size.height * 0.35,
      t: t2, dx: -20, dy: 25, blur: 65,
    );

    
    _blob(canvas, size,
      color: GaraColors.dsBlob3.withOpacity(0.65),
      radius: size.width * 0.32,
      baseX: size.width * 0.15,
      baseY: size.height * 0.88,
      t: t3, dx: 18, dy: -25, blur: 50,
    );
  }

  void _blob(Canvas canvas, Size size, {
    required Color color,
    required double radius,
    required double baseX,
    required double baseY,
    required double t,
    required double dx,
    required double dy,
    required double blur,
  }) {
    final progress = math.sin(t * math.pi);
    final x = baseX + dx * progress;
    final y = baseY + dy * progress;
    final scale = 1.0 + 0.08 * progress;

    canvas.drawCircle(
      Offset(x, y),
      radius * scale,
      Paint()
        ..color = color
        ..maskFilter = MaskFilter.blur(BlurStyle.normal, blur),
    );
  }

  @override
  bool shouldRepaint(_DashboardMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}








class _AppHeader extends StatelessWidget {
  final String namaSiswa;
  final String sapaan;
  final VoidCallback onBellTap;

  const _AppHeader({
    required this.namaSiswa,
    required this.sapaan,
    required this.onBellTap,
  });

  @override
  Widget build(BuildContext context) {
    final isTablet = GaraResponsive.isTablet(context);
    
    final initial = namaSiswa.isNotEmpty
        ? namaSiswa[0].toUpperCase()
        : 'S';

    return Padding(
      padding: EdgeInsets.fromLTRB(
        isTablet ? 24 : 20,
        isTablet ? 16 : 14,
        isTablet ? 24 : 20,
        10,
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          
          SizedBox(
            width: isTablet ? 58 : 52,
            height: isTablet ? 58 : 52,
            child: Stack(
              alignment: Alignment.center,
              children: [
                
                Container(
                  width: isTablet ? 58 : 52,
                  height: isTablet ? 58 : 52,
                  decoration: BoxDecoration(
                    color: GaraColors.dsPrimaryBright.withOpacity(0.35),
                    shape: BoxShape.circle,
                  ),
                ),
                
                Container(
                  width: isTablet ? 50 : 44,
                  height: isTablet ? 50 : 44,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [GaraColors.dsPrimaryBright, GaraColors.dsPrimaryDeep],
                    ),
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 2),
                    boxShadow: [
                      BoxShadow(
                        color: GaraColors.dsPrimaryDeep.withOpacity(0.30),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Center(
                    child: Text(
                      initial,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: isTablet ? 20 : 18,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(width: 12),

          
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  '${sapaan.toUpperCase()},',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: isTablet ? 11 : 10,
                    fontWeight: FontWeight.w600,
                    color: GaraColors.dsSlate500,
                    letterSpacing: 0.8,
                  ),
                ),
                const SizedBox(height: 1),
                Text(
                  namaSiswa.isEmpty ? 'Siswa GARA' : namaSiswa,
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: isTablet ? 19 : 17,
                    fontWeight: FontWeight.w700,
                    color: GaraColors.dsSlate800,
                    height: 1.2,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),

          const SizedBox(width: 10),

          // Ikon bel notifikasi — satu-satunya aksi di kanan header
          GestureDetector(
            onTap: onBellTap,
            child: Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: GaraColors.dsGlassBgStrong,
                shape: BoxShape.circle,
                border: Border.all(
                  color: GaraColors.dsGlassBorderStrong,
                ),
              ),
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  const Center(
                    child: Icon(
                      Icons.notifications_rounded,
                      size: 22,
                      color: GaraColors.dsSlate500,
                    ),
                  ),
                  Positioned(
                    top: 8,
                    right: 8,
                    child: _PulsingDot(),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}




class _PulsingDot extends StatefulWidget {
  @override
  State<_PulsingDot> createState() => _PulsingDotState();
}

class _PulsingDotState extends State<_PulsingDot>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1000),
  )..repeat(reverse: true);
  late final Animation<double> _anim =
      Tween<double>(begin: 0.4, end: 1.0).animate(
        CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut));

  @override
  void dispose() { _ctrl.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _anim,
      builder: (_, __) => Container(
        width: 8, height: 8,
        decoration: BoxDecoration(
          color: GaraColors.dsRose500.withOpacity(_anim.value),
          shape: BoxShape.circle,
          border: Border.all(color: Colors.white, width: 1.5),
        ),
      ),
    );
  }
}




class _BottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;
  const _BottomNav({required this.currentIndex, required this.onTap});

  static const _items = [
    (icon: Icons.home_rounded,          outline: Icons.home_outlined,          label: 'Beranda'),
    (icon: Icons.notifications_rounded, outline: Icons.notifications_outlined, label: 'Notifikasi'),
    (icon: Icons.person_rounded,        outline: Icons.person_outlined,        label: 'Akun Saya'),
  ];

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 16),
        child: Align(
          alignment: Alignment.bottomCenter,
          heightFactor: 1.0,
          child: ConstrainedBox(
            constraints: BoxConstraints(
              maxWidth: GaraResponsive.navMaxWidth(context),
            ),
            child: GaraGlassCardStrong(
              borderRadius: BorderRadius.circular(999),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 6),
                child: Stack(
                  children: [
                    
                    Positioned.fill(
                      child: AnimatedAlign(
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeOut,
                        alignment: currentIndex == 0
                            ? Alignment.centerLeft
                            : currentIndex == 1
                                ? Alignment.center
                                : Alignment.centerRight,
                        child: FractionallySizedBox(
                          widthFactor: 1 / _items.length,
                          child: Container(
                            height: 44,
                            decoration: BoxDecoration(
                              color: GaraColors.dsGaraLight.withOpacity(0.15),
                              borderRadius: BorderRadius.circular(999),
                            ),
                          ),
                        ),
                      ),
                    ),
                    
                    Row(
                      children: List.generate(_items.length, (i) {
                        final active = currentIndex == i;
                        final item = _items[i];
                        return Expanded(
                          child: GestureDetector(
                            onTap: () => onTap(i),
                            behavior: HitTestBehavior.opaque,
                            child: SizedBox(
                              height: 44,
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  AnimatedSwitcher(
                                    duration: const Duration(milliseconds: 200),
                                    child: Icon(
                                      active ? item.icon : item.outline,
                                      key: ValueKey('icon_${i}_$active'),
                                      color: active
                                          ? GaraColors.dsGaraBlue
                                          : GaraColors.dsSlate400,
                                      size: active ? 24 : 22,
                                    ),
                                  ),
                                  AnimatedSize(
                                    duration: const Duration(milliseconds: 250),
                                    curve: Curves.easeOut,
                                    child: active
                                        ? Row(children: [
                                            const SizedBox(width: 6),
                                            Text(
                                              item.label,
                                              style: GoogleFonts.plusJakartaSans(
                                                color: GaraColors.dsGaraBlue,
                                                fontWeight: FontWeight.w700,
                                                fontSize: 13,
                                              ),
                                            ),
                                          ])
                                        : const SizedBox.shrink(),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      }),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}