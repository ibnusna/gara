import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../utils/app_constants.dart';
import '../services/auth_service.dart';

/// Halaman yang ditampilkan ketika sesi user berakhir secara paksa,
/// misalnya karena login di perangkat lain atau token diinvalidasi server.
/// Menggantikan silent logout yang langsung redirect ke login page.
class SessionExpiredPage extends StatefulWidget {
  /// Pesan penyebab sesi berakhir (opsional, untuk konteks lebih detail)
  final String? reason;

  const SessionExpiredPage({super.key, this.reason});

  @override
  State<SessionExpiredPage> createState() => _SessionExpiredPageState();
}

class _SessionExpiredPageState extends State<SessionExpiredPage>
    with TickerProviderStateMixin {
  bool _isLoggingOut = false;

  // ── Mesh background animators ──
  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

  // ── Entry animation ──
  late final AnimationController _entryCtrl;
  late final Animation<double> _fadeAnim;
  late final Animation<double> _scaleAnim;

  @override
  void initState() {
    super.initState();

    _blobCtrl1 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 25000))
      ..repeat(reverse: true);
    _blobCtrl2 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 22000))
      ..repeat(reverse: true);
    _blobCtrl3 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 28000))
      ..repeat(reverse: true);
    _blobCtrl2.value = 0.3;
    _blobCtrl3.value = 0.6;

    _entryCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 700))
      ..forward();
    _fadeAnim =
        CurvedAnimation(parent: _entryCtrl, curve: Curves.easeOut);
    _scaleAnim = Tween<double>(begin: 0.88, end: 1.0).animate(
        CurvedAnimation(parent: _entryCtrl, curve: Curves.elasticOut));
  }

  @override
  void dispose() {
    _blobCtrl1.dispose();
    _blobCtrl2.dispose();
    _blobCtrl3.dispose();
    _entryCtrl.dispose();
    super.dispose();
  }

  Future<void> _handleLoginUlang() async {
    if (_isLoggingOut) return;
    setState(() => _isLoggingOut = true);
    // Bersihkan sesi lokal (token sudah tidak valid di server)
    await AuthService.clearSession();
    if (mounted) {
      Navigator.pushNamedAndRemoveUntil(
          context, GaraRoutes.login, (r) => false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      // Tidak bisa di-back — paksa user untuk tekan "Login Lagi"
      canPop: false,
      child: Scaffold(
        backgroundColor: GaraColors.dsBgBody,
        body: Stack(
          children: [
            // ── Animated Mesh Background ──────────────────────────────────
            Positioned.fill(
              child: AnimatedBuilder(
                animation: Listenable.merge(
                    [_blobCtrl1, _blobCtrl2, _blobCtrl3]),
                builder: (context, _) => CustomPaint(
                  painter: _SessionExpiredMeshPainter(
                    t1: _blobCtrl1.value,
                    t2: _blobCtrl2.value,
                    t3: _blobCtrl3.value,
                  ),
                ),
              ),
            ),

            // ── Content ──────────────────────────────────────────────────
            SafeArea(
              child: Center(
                child: FadeTransition(
                  opacity: _fadeAnim,
                  child: AnimatedBuilder(
                    animation: _scaleAnim,
                    builder: (ctx, child) =>
                        Transform.scale(scale: _scaleAnim.value, child: child),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 28),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          // ── Warning Icon ────────────────────────────
                          Container(
                            width: 96,
                            height: 96,
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFFFF6B6B), Color(0xFFFF8E53)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFFFF6B6B)
                                      .withOpacity(0.35),
                                  blurRadius: 28,
                                  spreadRadius: 2,
                                  offset: const Offset(0, 10),
                                ),
                              ],
                            ),
                            child: const Icon(
                              Icons.devices_other_rounded,
                              color: Colors.white,
                              size: 46,
                            ),
                          ),

                          const SizedBox(height: 28),

                          // ── Title ─────────────────────────────────
                          Text(
                            'Sesi Berakhir',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 26,
                              fontWeight: FontWeight.w800,
                              color: GaraColors.dsSlate800,
                            ),
                            textAlign: TextAlign.center,
                          ),

                          const SizedBox(height: 12),

                          // ── Reason / Description ───────────────────
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 20, vertical: 16),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFFE4E6),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                  color: const Color(0xFFFBD0D5)),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(Icons.warning_amber_rounded,
                                    color: Color(0xFFDC2626), size: 20),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    widget.reason ??
                                        'Akun Anda telah masuk dari perangkat lain. '
                                            'Sesi di perangkat ini telah diakhiri untuk '
                                            'menjaga keamanan akun Anda.',
                                    style: GoogleFonts.plusJakartaSans(
                                      fontSize: 13.5,
                                      color: const Color(0xFF991B1B),
                                      height: 1.55,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),

                          const SizedBox(height: 32),

                          // ── Login Lagi Button ──────────────────────
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: _isLoggingOut ? null : _handleLoginUlang,
                              icon: _isLoggingOut
                                  ? const SizedBox(
                                      width: 18,
                                      height: 18,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        valueColor:
                                            AlwaysStoppedAnimation<Color>(
                                                Colors.white),
                                      ),
                                    )
                                  : const Icon(Icons.login_rounded,
                                      size: 20, color: Colors.white),
                              label: Text(
                                _isLoggingOut ? 'Mengarahkan...' : 'Login Lagi',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w700,
                                  color: Colors.white,
                                ),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: GaraColors.dsPrimaryDeep,
                                disabledBackgroundColor:
                                    GaraColors.dsPrimaryDeep.withOpacity(0.7),
                                disabledForegroundColor: Colors.white,
                                padding:
                                    const EdgeInsets.symmetric(vertical: 16),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(16)),
                                elevation: 0,
                              ),
                            ),
                          ),

                          const SizedBox(height: 24),

                          Text(
                            'Jika Anda tidak merasa login dari perangkat lain,\nsegera ganti password Anda.',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 12,
                              color: GaraColors.dsSlate400,
                              height: 1.5,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Mesh Painter
// ─────────────────────────────────────────────────────────────────────────────
class _SessionExpiredMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _SessionExpiredMeshPainter(
      {required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );
    _blob(canvas,
        color: const Color(0xFFFFB3B3).withOpacity(0.70),
        radius: size.width * 0.40,
        baseX: size.width * 0.05,
        baseY: size.height * 0.05,
        t: t1, dx: 20, dy: -30, blur: 55);
    _blob(canvas,
        color: GaraColors.dsBlob2.withOpacity(0.55),
        radius: size.width * 0.38,
        baseX: size.width * 0.85,
        baseY: size.height * 0.45,
        t: t2, dx: -18, dy: 22, blur: 60);
    _blob(canvas,
        color: GaraColors.dsBlob3.withOpacity(0.50),
        radius: size.width * 0.30,
        baseX: size.width * 0.15,
        baseY: size.height * 0.88,
        t: t3, dx: 15, dy: -20, blur: 48);
  }

  void _blob(Canvas canvas,
      {required Color color,
      required double radius,
      required double baseX,
      required double baseY,
      required double t,
      required double dx,
      required double dy,
      required double blur}) {
    final progress = math.sin(t * math.pi);
    canvas.drawCircle(
      Offset(baseX + dx * progress, baseY + dy * progress),
      radius * (1.0 + 0.08 * progress),
      Paint()
        ..color = color
        ..maskFilter = MaskFilter.blur(BlurStyle.normal, blur),
    );
  }

  @override
  bool shouldRepaint(_SessionExpiredMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
