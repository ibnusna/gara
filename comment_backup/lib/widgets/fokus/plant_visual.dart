// Widget: visualisasi tanaman emoji — level 1-5 identik dari blade
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// Emoji tanaman per level — sesuai nama dari Laravel plantLevels
const _plants = [
  (emoji: '🌱', name: 'Bibit'),
  (emoji: '🌿', name: 'Tunas'),
  (emoji: '🪴', name: 'Tanaman Kecil'),
  (emoji: '🌳', name: 'Tanaman Dewasa'),
  (emoji: '🌲', name: 'Tanaman Subur'),
];

class PlantVisual extends StatelessWidget {
  final int level; // 1–5

  const PlantVisual({super.key, required this.level});

  @override
  Widget build(BuildContext context) {
    final idx = (level - 1).clamp(0, 4);
    final plant = _plants[idx];
    return Column(mainAxisSize: MainAxisSize.min, children: [
      Text(plant.emoji, style: const TextStyle(fontSize: 72)),
      const SizedBox(height: 6),
      Text('${plant.name} — Level $level',
          style: GoogleFonts.poppins(
              fontSize: 13,
              color: const Color(0xFF1D4ED8),
              fontWeight: FontWeight.w500)),
    ]);
  }
}
