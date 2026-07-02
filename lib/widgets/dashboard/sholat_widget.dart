// ============================================================
//  GARA Flutter — Jadwal Sholat Widget
//  Refactored: Design System Baru (dashborad.html)
//
//  DS Mapping: Section 4 — Produktivitas (horizontal glass card)
//  Sebelum : Container putih solid + border + box-shadow
//  Sesudah : GaraGlassCard rounded-[24px] sesuai DS
//
//  Perubahan Visual:
//  - Container: glass-card rounded-24px (mengganti solid white)
//  - Icon box header: gradient slate-700→slate-900, rounded-[16px]
//    dengan aksen circle putih kecil di dalam (DS: absolute -top-2 -right-2)
//  - Trailing arrow: circle border-slate-200, bg-white/50
//  - Waktu aktif: tetap menggunakan studentPrimary untuk highlight
//  - Font: Plus Jakarta Sans
//  - Section label "Produktivitas" di atas widget
//
//  LOGIKA TIDAK DIUBAH: API Aladhan, SharedPreferences cache,
//  fallback data statis, _activeIndex() — identik dengan versi lama
// ============================================================

// Jadwal Sholat Widget — Real-time dari Aladhan API
// v9.0.0: Ganti data statis → API call berdasarkan kota tersimpan di SharedPreferences.
//         Hapus teks "FASE 2: deteksi otomatis".
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:geolocator/geolocator.dart';
import '../../utils/app_constants.dart';
import 'ds_glass_card.dart';

class _SholatTime {
  final String name;
  final String time;
  const _SholatTime({required this.name, required this.time});
}

class SholatWidget extends StatefulWidget {
  const SholatWidget({super.key});

  @override
  State<SholatWidget> createState() => _SholatWidgetState();
}

class _SholatWidgetState extends State<SholatWidget> {
  List<_SholatTime> _times = [];
  bool _isLoading = true;
  String _cityName = '';

  @override
  void initState() {
    super.initState();
    _fetchPrayerTimes();
  }

  Future<void> _fetchPrayerTimes() async {
    // ── 1. Baca kota dari SharedPreferences sebagai fallback terakhir
    final prefs  = await SharedPreferences.getInstance();
    String city  = prefs.getString('prayer_city')    ?? 'Jakarta';
    String country = prefs.getString('prayer_country') ?? 'Indonesia';
    final namaSekolahCity = prefs.getString('nama_kota_sekolah') ?? '';
    if (namaSekolahCity.isNotEmpty) city = namaSekolahCity;

    Uri? apiUrl;
    String locationName = city;

    // ── 2. Minta izin Lokasi (GPS) dari Siswa untuk akurasi tinggi
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      
      if (permission == LocationPermission.whileInUse || permission == LocationPermission.always) {
        Position position = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.low);
        apiUrl = Uri.parse(
          'https://api.aladhan.com/v1/timings'
          '?latitude=${position.latitude}&longitude=${position.longitude}'
          '&method=11'
        );
        locationName = 'Lokasi Saat Ini';
      }
    } catch (_) {}

    // ── 3. Fallback ke API IP (apabila lokasi ditolak) 
    if (apiUrl == null) {
      try {
        final ipRes = await http.get(Uri.parse('http://ip-api.com/json/')).timeout(const Duration(seconds: 4));
        if (ipRes.statusCode == 200) {
          final ipData = jsonDecode(ipRes.body);
          if (ipData['status'] == 'success' && ipData['city'] != null) {
            city = ipData['city'];
            country = ipData['country'] ?? country;
            locationName = city;
          }
        }
      } catch (_) {}

      apiUrl = Uri.parse(
        'https://api.aladhan.com/v1/timingsByCity'
        '?city=${Uri.encodeComponent(city)}'
        '&country=${Uri.encodeComponent(country)}'
        '&method=11'
      );
    }

    try {
      final response = await http.get(apiUrl).timeout(const Duration(seconds: 8));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final timings = data['data']['timings'] as Map<String, dynamic>;

        String cleanTime(String t) => t.length >= 5 ? t.substring(0, 5) : t;

        final parsed = [
          _SholatTime(name: 'Subuh',   time: cleanTime(timings['Fajr']    ?? '--:--')),
          _SholatTime(name: 'Dzuhur',  time: cleanTime(timings['Dhuhr']   ?? '--:--')),
          _SholatTime(name: 'Ashar',   time: cleanTime(timings['Asr']     ?? '--:--')),
          _SholatTime(name: 'Maghrib', time: cleanTime(timings['Maghrib'] ?? '--:--')),
          _SholatTime(name: 'Isya',    time: cleanTime(timings['Isha']    ?? '--:--')),
        ];

        final now = DateTime.now();
        final timingsJson = jsonEncode({
          'times': parsed.map((t) => {'name': t.name, 'time': t.time}).toList(),
          'city': locationName,
          'date': '${now.year}-${now.month}-${now.day}',
        });
        await prefs.setString('cached_prayer_times', timingsJson);

        if (mounted) {
          setState(() {
            _times    = parsed;
            _cityName = locationName;
            _isLoading = false;
          });
        }
        return;
      }
    } catch (_) {
      // Network error → coba cache
    }

    // ── 2. Fallback: baca dari cache SharedPreferences
    final cached = prefs.getString('cached_prayer_times');
    if (cached != null) {
      try {
        final Map<String, dynamic> c = jsonDecode(cached);
        final List times = c['times'] as List;
        if (mounted) {
          setState(() {
            _times    = times.map((t) => _SholatTime(name: t['name'], time: t['time'])).toList();
            _cityName = c['city'] ?? city;
            _isLoading = false;
          });
        }
        return;
      } catch (_) {}
    }

    // ── 3. Absolute fallback: data statis Jakarta
    if (mounted) {
      setState(() {
        _times = const [
          _SholatTime(name: 'Subuh',   time: '04:21'),
          _SholatTime(name: 'Dzuhur',  time: '11:54'),
          _SholatTime(name: 'Ashar',   time: '15:15'),
          _SholatTime(name: 'Maghrib', time: '17:48'),
          _SholatTime(name: 'Isya',    time: '19:03'),
        ];
        _cityName  = city;
        _isLoading = false;
      });
    }
  }

  int _activeIndex() {
    final nowMin = DateTime.now().hour * 60 + DateTime.now().minute;
    for (int i = 0; i < _times.length; i++) {
      final parts = _times[i].time.split(':');
      if (parts.length < 2) continue;
      final tMin = int.tryParse(parts[0])! * 60 + (int.tryParse(parts[1]) ?? 0);
      if (nowMin < tMin) return i;
    }
    return _times.isNotEmpty ? _times.length - 1 : 0;
  }

  String _formatDate() {
    const bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    final d = DateTime.now();
    return '${d.day} ${bulan[d.month]} ${d.year}';
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // ── Section label (DS: h2 "Produktivitas") ──
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 12),
          child: Text(
            'Produktivitas',
            style: GoogleFonts.plusJakartaSans(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: GaraColors.dsSlate800,
              letterSpacing: 0.2,
            ),
          ),
        ),

        // ── DS: Glass card horizontal (Zona Produktif) ──
        GaraGlassCard(
          borderRadius: BorderRadius.circular(24),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Header row ──
              Row(children: [
                // DS: icon box gradient slate-700→slate-900 + circle accent
                _SholatIconBox(),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Jadwal Sholat',
                        style: GoogleFonts.plusJakartaSans(
                          fontWeight: FontWeight.w700,
                          fontSize: 13.5,
                          color: GaraColors.dsSlate800,
                        ),
                      ),
                      Text(
                        _isLoading ? 'Memuat lokasi...' : _cityName,
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 11,
                          color: GaraColors.dsSlate500,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                // DS: tanggal kanan
                Text(
                  _formatDate(),
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 10.5,
                    color: GaraColors.dsSlate400,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ]),

              const SizedBox(height: 14),
              const Divider(color: GaraColors.dsSlate200, height: 1),
              const SizedBox(height: 14),

              // ── Grid 5 waktu atau skeleton ──
              _isLoading ? _buildSkeleton() : _buildGrid(),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildGrid() {
    final active = _activeIndex();
    return Row(
      children: List.generate(_times.length, (i) {
        final isActive = i == active;
        return Expanded(
          child: Container(
            margin: EdgeInsets.only(right: i < 4 ? 6 : 0),
            padding: const EdgeInsets.symmetric(vertical: 10),
            decoration: BoxDecoration(
              // DS: aktif = studentPrimary, tidak aktif = bg sangat transparan
              color: isActive
                  ? GaraColors.studentPrimary
                  : Colors.white.withOpacity(0.50),
              borderRadius: BorderRadius.circular(12),
              border: isActive
                  ? null
                  : Border.all(color: GaraColors.dsSlate200),
            ),
            child: Column(children: [
              Text(
                _times[i].name,
                textAlign: TextAlign.center,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 9.5,
                  fontWeight: FontWeight.w700,
                  color: isActive ? Colors.white : GaraColors.dsSlate500,
                ),
              ),
              const SizedBox(height: 3),
              Text(
                _times[i].time,
                textAlign: TextAlign.center,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: isActive ? Colors.white : GaraColors.dsSlate800,
                ),
              ),
            ]),
          ),
        );
      }),
    );
  }

  Widget _buildSkeleton() {
    return Row(
      children: List.generate(5, (i) => Expanded(
        child: Container(
          margin: EdgeInsets.only(right: i < 4 ? 6 : 0),
          height: 52,
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.40),
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      )),
    );
  }
}

/// Icon box untuk Jadwal Sholat header
/// DS: gradient slate-700→slate-900, rounded-[16px], circle accent di atas kanan
class _SholatIconBox extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: 48,
      height: 48,
      decoration: BoxDecoration(
        // DS: bg-gradient-to-br from-slate-700 to-slate-900
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF334155), Color(0xFF0F172A)],
        ),
        borderRadius: BorderRadius.circular(16),
        // DS: shadow-lg
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.25),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          // DS: absolute -top-2 -right-2 w-6 h-6 bg-white/20 rounded-full (aksen)
          Positioned(
            top: -4,
            right: -4,
            child: Container(
              width: 18,
              height: 18,
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.20),
                shape: BoxShape.circle,
              ),
            ),
          ),
          const Center(
            child: Icon(Icons.mosque_rounded, color: Colors.white, size: 22),
          ),
        ],
      ),
    );
  }
}
