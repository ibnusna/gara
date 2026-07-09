// Widget: statistik card — identik dengan Stats Summary di blade
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/focus_model.dart';

class FocusStatsCard extends StatelessWidget {
  final FocusData data;

  const FocusStatsCard({super.key, required this.data});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 10)],
      ),
      padding: const EdgeInsets.all(18),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Statistik',
            style: GoogleFonts.poppins(
                fontSize: 15, fontWeight: FontWeight.w700, color: const Color(0xFF1E3A8A))),
        const SizedBox(height: 12),
        _row('Total Sesi', '${data.stats.totalSessions}'),
        _row('Total Menit Fokus', '${data.stats.totalFocusMinutes}'),
        _row('Streak Terpanjang', '${data.streak.longest} hari'),
        _row('Poin Air', '${data.plantWaterPoints}'),
        _row('Sesi Hari Ini', '${data.todaySessions}/4'),
      ]),
    );
  }

  Widget _row(String label, String value) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Row(children: [
          Text(label, style: GoogleFonts.poppins(fontSize: 13, color: const Color(0xFF3B82F6))),
          const Spacer(),
          Text(value,
              style: GoogleFonts.poppins(
                  fontSize: 13, fontWeight: FontWeight.w700, color: const Color(0xFF1E3A8A))),
        ]),
      );
}
