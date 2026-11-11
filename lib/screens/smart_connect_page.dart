import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../utils/app_constants.dart';
import '../widgets/hybrid_wrapper.dart';

class SmartConnectPage extends StatefulWidget {
  const SmartConnectPage({super.key});

  @override
  State<SmartConnectPage> createState() => _SmartConnectPageState();
}

class _SmartConnectPageState extends State<SmartConnectPage>
    with TickerProviderStateMixin {
  final _ipController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  // ── Mesh background blob animators (identik dengan halaman lain) ──
  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

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
    _blobCtrl2.value = 0.2;
    _blobCtrl3.value = 0.4;
  }

  @override
  void dispose() {
    _ipController.dispose();
    _blobCtrl1.dispose();
    _blobCtrl2.dispose();
    _blobCtrl3.dispose();
    super.dispose();
  }

  void _connect() {
    if (_formKey.currentState!.validate()) {
      HapticFeedback.mediumImpact();
      String url = _ipController.text.trim();
      if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'http://$url';
      }

      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (context) => HybridWrapper(
            url: url,
            pageTitle: 'Smart Connect',
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.dsBgBody,
      body: Stack(
        children: [
          // ── Animated Gradient Mesh Background ──────────────────────────────
          Positioned.fill(
            child: AnimatedBuilder(
              animation:
                  Listenable.merge([_blobCtrl1, _blobCtrl2, _blobCtrl3]),
              builder: (context, _) => CustomPaint(
                painter: _SmartConnectMeshPainter(
                  t1: _blobCtrl1.value,
                  t2: _blobCtrl2.value,
                  t3: _blobCtrl3.value,
                ),
              ),
            ),
          ),
          // ── Content ────────────────────────────────────────────────────────
          SafeArea(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Back button
                Padding(
                  padding: const EdgeInsets.only(left: 8, top: 4),
                  child: IconButton(
                    icon: const Icon(Icons.arrow_back_rounded,
                        color: GaraColors.dsSlate800),
                    onPressed: () => Navigator.pop(context),
                  ),
                ),
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 24, vertical: 8),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const SizedBox(height: 12),

                          // Icon
                          Container(
                            width: 56,
                            height: 56,
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [
                                  GaraColors.dsPrimaryDeep,
                                  GaraColors.dsPrimaryBright
                                ],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(18),
                              boxShadow: [
                                BoxShadow(
                                  color:
                                      GaraColors.dsPrimaryDeep.withOpacity(0.3),
                                  blurRadius: 16,
                                  offset: const Offset(0, 6),
                                ),
                              ],
                            ),
                            child: const Icon(Icons.wifi_tethering_rounded,
                                color: Colors.white, size: 28),
                          ),
                          const SizedBox(height: 20),

                          Text(
                            'SmartConnect',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 28,
                              fontWeight: FontWeight.w800,
                              color: GaraColors.dsSlate800,
                            ),
                          ),
                          const SizedBox(height: 10),
                          Text(
                            'Masukkan alamat IP server lokal (Local Server) untuk memanggil fitur webview seperti ruang belajar secara mandiri.',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 14,
                              color: GaraColors.dsSlate500,
                              height: 1.55,
                            ),
                          ),
                          const SizedBox(height: 36),

                          // Input field
                          Container(
                            decoration: BoxDecoration(
                              color: GaraColors.dsGlassBgStrong,
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(
                                  color: GaraColors.dsGlassBorderStrong),
                              boxShadow: [
                                BoxShadow(
                                  color:
                                      GaraColors.dsSlate800.withOpacity(0.05),
                                  blurRadius: 12,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: TextFormField(
                              controller: _ipController,
                              style: GoogleFonts.plusJakartaSans(
                                  color: GaraColors.dsSlate800,
                                  fontSize: 14),
                              decoration: InputDecoration(
                                hintText:
                                    'Contoh: 192.168.1.100 atau http://domain.com',
                                hintStyle: GoogleFonts.plusJakartaSans(
                                    color: GaraColors.dsSlate400,
                                    fontSize: 13),
                                contentPadding:
                                    const EdgeInsets.symmetric(
                                        horizontal: 20, vertical: 18),
                                border: InputBorder.none,
                                prefixIcon: const Icon(
                                    Icons.language_rounded,
                                    color: GaraColors.dsSlate400),
                              ),
                              validator: (val) {
                                if (val == null || val.trim().isEmpty) {
                                  return 'Alamat IP / URL tidak boleh kosong';
                                }
                                return null;
                              },
                            ),
                          ),
                          const SizedBox(height: 28),

                          // Connect button
                          SizedBox(
                            width: double.infinity,
                            height: 56,
                            child: ElevatedButton.icon(
                              onPressed: _connect,
                              icon: const Icon(Icons.send_rounded,
                                  size: 18, color: Colors.white),
                              label: Text(
                                'Masuk Webview',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w700,
                                  color: Colors.white,
                                ),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: GaraColors.dsPrimaryDeep,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                elevation: 0,
                              ),
                            ),
                          ),

                          const SizedBox(height: 24),

                          // Info card
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: GaraColors.dsGlassBgStrong,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                  color: GaraColors.dsGlassBorder),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(Icons.info_outline_rounded,
                                    size: 18,
                                    color: GaraColors.dsPrimaryBright),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    'Fitur ini hanya untuk pengembang dan administrator. Pastikan server lokal sudah berjalan sebelum terhubung.',
                                    style: GoogleFonts.plusJakartaSans(
                                      fontSize: 12.5,
                                      color: GaraColors.dsSlate500,
                                      height: 1.5,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Mesh Painter — identik dengan halaman lain (SplashPage, DashboardPage, dll)
// ─────────────────────────────────────────────────────────────────────────────
class _SmartConnectMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _SmartConnectMeshPainter(
      {required this.t1, required this.t2, required this.t3});

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
        t: t1,
        dx: 25,
        dy: -40,
        blur: 55);
    _blob(canvas, size,
        color: GaraColors.dsBlob2.withOpacity(0.70),
        radius: size.width * 0.42,
        baseX: size.width * 0.85,
        baseY: size.height * 0.40,
        t: t2,
        dx: -20,
        dy: 25,
        blur: 65);
    _blob(canvas, size,
        color: GaraColors.dsBlob3.withOpacity(0.65),
        radius: size.width * 0.32,
        baseX: size.width * 0.15,
        baseY: size.height * 0.85,
        t: t3,
        dx: 18,
        dy: -25,
        blur: 50);
  }

  void _blob(Canvas canvas, Size size,
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
  bool shouldRepaint(_SmartConnectMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
