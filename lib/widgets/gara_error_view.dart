import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../utils/app_constants.dart';
import '../utils/gara_error_mapper.dart';

class GaraErrorView extends StatelessWidget {
  final GaraUserError error;
  final VoidCallback onRetry;

  const GaraErrorView({
    super.key,
    required this.error,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: GaraColors.primary.withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(
                error.icon,
                size: 42,
                color: GaraColors.primary,
              ),
            ),
            const SizedBox(height: 20),
            Text(
              error.title,
              textAlign: TextAlign.center,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: GaraColors.dsSlate800,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              error.userMessage,
              textAlign: TextAlign.center,
              maxLines: 2,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: GaraColors.dsSlate500,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: 160,
              height: 44,
              child: ElevatedButton.icon(
                onPressed: onRetry,
                style: ElevatedButton.styleFrom(
                  backgroundColor: GaraColors.primary,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: Text(
                  'Coba Lagi',
                  style: GoogleFonts.plusJakartaSans(
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),
            InkWell(
              onTap: () {
                Clipboard.setData(ClipboardData(text: error.errorCode));
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('Kode ${error.errorCode} disalin ke papan klip'),
                    duration: const Duration(seconds: 2),
                    behavior: SnackBarBehavior.floating,
                  ),
                );
              },
              borderRadius: BorderRadius.circular(6),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.grey.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  'Kode: ${error.errorCode}',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: Colors.grey.shade600,
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
