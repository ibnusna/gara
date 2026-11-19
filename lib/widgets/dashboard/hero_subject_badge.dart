import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Overflow-safe subject badge and active activity pill for HeroCard.
/// Conforms to <= 200 lines per file policy.
class HeroSubjectBadge extends StatelessWidget {
  final String selectedMapel;
  final String kelasSiswa;
  final VoidCallback onTap;

  const HeroSubjectBadge({
    super.key,
    required this.selectedMapel,
    required this.kelasSiswa,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final displayText = selectedMapel.isNotEmpty
        ? '$selectedMapel ($kelasSiswa)'
        : 'Pilih Pelajaran ($kelasSiswa)';

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.20),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.menu_book_rounded,
              size: 10,
              color: Color(0xFFBFDBFE),
            ),
            const SizedBox(width: 4),
            Flexible(
              child: Text(
                displayText,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                softWrap: false,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 10,
                  fontWeight: FontWeight.w800,
                  color: const Color(0xFFBFDBFE),
                  letterSpacing: 0.5,
                ),
              ),
            ),
            const SizedBox(width: 5),
            const Icon(
              Icons.swap_horiz_rounded,
              size: 10,
              color: Color(0xFFBFDBFE),
            ),
          ],
        ),
      ),
    );
  }
}

class HeroActivityPill extends StatelessWidget {
  final String title;

  const HeroActivityPill({super.key, required this.title});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.15),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.play_circle_fill_rounded,
            size: 12,
            color: Colors.white.withOpacity(0.9),
          ),
          const SizedBox(width: 6),
          Flexible(
            child: Text(
              title.isNotEmpty ? title : 'Bab 3: Sistem Pembelajaran',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              softWrap: false,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: Colors.white.withOpacity(0.9),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
