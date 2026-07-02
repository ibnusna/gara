// ============================================================
//  GARA Flutter — Akun Tab (Redesign: Setara Web Tentang Saya)
//  Masalah 3: Tambah Ganti Password + Info Sekolah
//
//  Fitur:
//   1. Profil siswa (nama, NIS, kelas, status akun)
//   2. Tombol Ganti Password → WebView /student/ganti-password
//   3. Badge peringatan jika masih menggunakan password bawaan
//   4. Informasi Sekolah (nama sekolah, tahun ajaran, semester)
//   5. Tombol Ganti Mapel
//   6. Tombol Logout
// ============================================================

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../utils/app_constants.dart';
import '../../utils/app_config.dart';
import '../../utils/responsive_utils.dart';
import '../../services/auth_service.dart';
import '../../widgets/hybrid_wrapper.dart';

class AkunTab extends StatefulWidget {
  final String namaSiswa;
  final String kelasSiswa;
  final String sekolahNama;
  final String selectedMapel;

  const AkunTab({
    super.key,
    required this.namaSiswa,
    required this.kelasSiswa,
    required this.sekolahNama,
    required this.selectedMapel,
  });

  @override
  State<AkunTab> createState() => _AkunTabState();
}

class _AkunTabState extends State<AkunTab> {
  bool _isDefaultPassword = false;
  bool _isLoadingProfile = true;
  String _nisSiswa = '';
  String _tahunAjaran = '';
  String _semester = '';
  String _namaSekolah = '';

  @override
  void initState() {
    super.initState();
    _loadProfileData();
  }

  Future<void> _loadProfileData() async {
    // Load dari SharedPreferences dulu (cepat, sudah ada)
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _nisSiswa = prefs.getString('nis_siswa') ?? '';
        _isDefaultPassword = prefs.getBool('is_default_password') ?? false;
        _tahunAjaran = prefs.getString('tahun_ajaran') ?? '';
        _semester = prefs.getString('semester') ?? '';
        _namaSekolah = prefs.getString('nama_sekolah') ?? widget.sekolahNama;
        _isLoadingProfile = false;
      });
    }

    // Fetch data terbaru dari API di background
    final detail = await AuthService.getStudentAccountDetail();
    if (detail != null && mounted) {
      setState(() {
        _nisSiswa = detail['nis'] ?? _nisSiswa;
        _isDefaultPassword = detail['is_default_password'] ?? _isDefaultPassword;
        _tahunAjaran = detail['tahun_ajaran'] ?? _tahunAjaran;
        _semester = detail['semester'] ?? _semester;
        _namaSekolah = detail['nama_sekolah'] ?? _namaSekolah;
      });

      // Cache ke SharedPreferences
      await prefs.setString('nis_siswa', _nisSiswa);
      await prefs.setBool('is_default_password', _isDefaultPassword);
      await prefs.setString('tahun_ajaran', _tahunAjaran);
      await prefs.setString('semester', _semester);
      await prefs.setString('nama_sekolah', _namaSekolah);
    }
  }

  // Buka halaman ganti password via HybridWrapper (WebView + Sanctum handoff)
  // Sama seperti cara kerja Ruang Belajar, Ruang Tugas, dll.
  Future<void> _openGantiPassword() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(GaraPrefKeys.authToken) ?? '';
    if (!mounted) return;

    final handoffUrl = AppConfig.getHandoffUrl(
      token: token,
      targetPath: AppConfig.studentPasswordPath,
    );

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => HybridWrapper(
          url: handoffUrl,
          pageTitle: 'Ganti Password',
        ),
      ),
    ).then((_) {
      // Refresh data profil setelah kembali (password mungkin sudah diubah)
      _loadProfileData();
    });
  }

  @override
  Widget build(BuildContext context) {
    final hPad = GaraResponsive.hPad(context);
    return Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(
          maxWidth: GaraResponsive.contentMaxWidth(context),
        ),
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: EdgeInsets.symmetric(horizontal: hPad, vertical: 8),
          child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(height: 8),

          // ── Header Profil ──────────────────────────────
          _buildProfileHeader(),
          const SizedBox(height: 20),

          // ── Informasi Siswa ───────────────────────────
          _sectionLabel('INFORMASI SISWA'),
          const SizedBox(height: 8),
          _buildInfoCard([
            if (_isLoadingProfile)
              _infoRow('NIS', '...')
            else
              _infoRow('NIS', _nisSiswa.isNotEmpty ? _nisSiswa : '—'),
            _infoRow('Kelas', widget.kelasSiswa.isNotEmpty ? widget.kelasSiswa : '—'),
            _infoRow('Mapel Aktif', widget.selectedMapel.isNotEmpty ? widget.selectedMapel : '—'),
            _infoRowColored('Status Akun', 'Aktif', const Color(0xFF16A34A)),
          ]),
          const SizedBox(height: 20),

          // ── Keamanan Akun ─────────────────────────────
          _sectionLabel('KEAMANAN AKUN'),
          const SizedBox(height: 8),

          // Badge peringatan password bawaan
          if (_isDefaultPassword) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              margin: const EdgeInsets.only(bottom: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF8E1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFFFB300).withOpacity(0.4)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.warning_amber_rounded, color: Color(0xFFFFB300), size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Password bawaan terdeteksi. Segera ubah untuk keamanan akun.',
                      style: GoogleFonts.poppins(
                        fontSize: 12,
                        color: const Color(0xFF92400E),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          // Tombol ganti password
          GestureDetector(
            onTap: _openGantiPassword,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: GaraColors.studentSurface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: GaraColors.studentBorder),
              ),
              child: Row(
                children: [
                  Container(
                    width: 38, height: 38,
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.key_rounded, color: Color(0xFF0B57D0), size: 18),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Ubah Password',
                            style: GoogleFonts.poppins(
                                fontSize: 14, fontWeight: FontWeight.w600,
                                color: GaraColors.studentTextMain)),
                        Text(
                          _isDefaultPassword
                              ? 'Menggunakan password bawaan sistem'
                              : 'Terakhir diubah secara manual',
                          style: GoogleFonts.poppins(
                              fontSize: 11.5, color: GaraColors.studentTextMuted),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: GaraColors.studentPrimary,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text('Ubah',
                        style: GoogleFonts.poppins(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.w600)),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),

          // ── Info Sekolah ──────────────────────────────
          _sectionLabel('INFORMASI SEKOLAH'),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: GaraColors.studentSurface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: GaraColors.studentBorder),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ── Nama Sekolah + Centang Biru ──────────────
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          Flexible(
                            child: Text(
                              _namaSekolah.isNotEmpty ? _namaSekolah : widget.sekolahNama,
                              style: GoogleFonts.poppins(
                                fontWeight: FontWeight.w700,
                                fontSize: 14,
                                color: GaraColors.studentTextMain,
                              ),
                            ),
                          ),
                          const SizedBox(width: 4),
                          const Icon(
                            Icons.verified_rounded,
                            color: Color(0xFF0095F6),
                            size: 15,
                          ),
                        ],
                      ),
                      if (_tahunAjaran.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          'Tahun Ajaran: $_tahunAjaran',
                          style: GoogleFonts.poppins(
                              fontSize: 12, color: GaraColors.studentTextMuted),
                        ),
                      ],
                      if (_semester.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(
                          'Semester: $_semester',
                          style: GoogleFonts.poppins(
                              fontSize: 12, color: GaraColors.studentTextMuted),
                        ),
                      ],
                    ],
                  ),
                ),
                Container(
                  width: 50, height: 50,
                  decoration: BoxDecoration(
                    color: const Color(0xFFE0F2FE),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.school_rounded,
                      color: Color(0xFF0284C7), size: 24),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // ── Tombol Aksi ────────────────────────────────
          _buildGantiMapelButton(context),
          const SizedBox(height: 12),
          _buildLogoutButton(context),
          const SizedBox(height: 24),
        ],
        ),
        ),
      ),
    );
  }

  Widget _buildProfileHeader() {
    final initial = widget.namaSiswa.isNotEmpty ? widget.namaSiswa[0].toUpperCase() : 'S';
    return Center(
      child: Column(
        children: [
          Container(
            width: 84,
            height: 84,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0B57D0), Color(0xFF063A89)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0B57D0).withOpacity(0.3),
                  blurRadius: 16,
                  spreadRadius: 2,
                )
              ],
            ),
            child: Center(
              child: Text(initial,
                  style: GoogleFonts.poppins(
                      color: Colors.white, fontSize: 32, fontWeight: FontWeight.w700)),
            ),
          ),
          const SizedBox(height: 12),
          Text(widget.namaSiswa,
              style: GoogleFonts.poppins(
                  fontSize: 18, fontWeight: FontWeight.w700,
                  color: GaraColors.studentTextMain)),
          const SizedBox(height: 2),
          Text(widget.sekolahNama,
              style: GoogleFonts.poppins(
                  fontSize: 12, color: GaraColors.studentTextMuted)),
        ],
      ),
    );
  }

  Widget _sectionLabel(String text) => Text(
        text,
        style: GoogleFonts.poppins(
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
          color: GaraColors.studentTextMuted,
          letterSpacing: 0.8,
        ),
      );

  Widget _buildInfoCard(List<Widget> rows) {
    return Container(
      decoration: BoxDecoration(
        color: GaraColors.studentSurface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: GaraColors.studentBorder),
      ),
      child: Column(children: rows),
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: GoogleFonts.poppins(
              fontSize: 13, color: GaraColors.studentTextMuted)),
          Text(value, style: GoogleFonts.poppins(
              fontSize: 13, fontWeight: FontWeight.w600,
              color: GaraColors.studentTextMain)),
        ],
      ),
    );
  }

  Widget _infoRowColored(String label, String value, Color valueColor) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: GoogleFonts.poppins(
              fontSize: 13, color: GaraColors.studentTextMuted)),
          Row(
            children: [
              Icon(Icons.check_circle_rounded, color: valueColor, size: 14),
              const SizedBox(width: 4),
              Text(value, style: GoogleFonts.poppins(
                  fontSize: 13, fontWeight: FontWeight.w600, color: valueColor)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildGantiMapelButton(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton.icon(
        onPressed: () {
          Navigator.pushNamedAndRemoveUntil(
            context,
            GaraRoutes.pilihMapel,
            (r) => false,
          );
        },
        icon: const Icon(Icons.swap_horiz_rounded, size: 18),
        label: Text('Ganti Mata Pelajaran',
            style: GoogleFonts.poppins(fontWeight: FontWeight.w600)),
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFFDC2626),
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          elevation: 0,
        ),
      ),
    );
  }

  Widget _buildLogoutButton(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton.icon(
        onPressed: () => _confirmLogout(context),
        icon: const Icon(Icons.logout_rounded, size: 18),
        label: Text('Keluar Akun',
            style: GoogleFonts.poppins(fontWeight: FontWeight.w600)),
        style: OutlinedButton.styleFrom(
          foregroundColor: GaraColors.danger,
          side: const BorderSide(color: GaraColors.danger),
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),
    );
  }

  void _confirmLogout(BuildContext context) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Keluar Akun',
            style: GoogleFonts.poppins(fontWeight: FontWeight.w700)),
        content: Text('Yakin ingin keluar dari GARA?',
            style: GoogleFonts.poppins()),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Batal', style: GoogleFonts.poppins()),
          ),
          TextButton(
            onPressed: () async {
              Navigator.pop(context);
              await AuthService.logout();
              if (context.mounted) {
                Navigator.pushNamedAndRemoveUntil(
                    context, GaraRoutes.login, (_) => false);
              }
            },
            child: Text('Keluar',
                style: GoogleFonts.poppins(color: GaraColors.danger)),
          ),
        ],
      ),
    );
  }
}
