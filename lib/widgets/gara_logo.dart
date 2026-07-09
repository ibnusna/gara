
import 'package:flutter/material.dart';

/// Logo GARA putih — untuk latar belakang gelap
class GaraLogoWhite extends StatelessWidget {
  final double height;
  const GaraLogoWhite({super.key, this.height = 36});

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/GARA_WHITE.png',
      height: height,
      fit: BoxFit.contain,
      // Fallback jika asset gagal load
      errorBuilder: (_, __, ___) => Text(
        'GARA',
        style: TextStyle(
          fontSize: height * 0.7,
          fontWeight: FontWeight.w900,
          color: Colors.white,
          letterSpacing: 2,
        ),
      ),
    );
  }
}

/// Logo GARA hitam / gelap — untuk latar belakang terang
class GaraLogoBlack extends StatelessWidget {
  final double height;
  const GaraLogoBlack({super.key, this.height = 35});

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/FARA_BLACK.png',
      height: height,
      fit: BoxFit.contain,
      errorBuilder: (_, __, ___) => Text(
        'GARA',
        style: TextStyle(
          fontSize: height * 0.7,
          fontWeight: FontWeight.w900,
          color: Colors.black87,
          letterSpacing: 2,
        ),
      ),
    );
  }
}

/// Logo 3D GARA — untuk FAB dan aksen visual
class GaraLogo3d extends StatelessWidget {
  final double height;
  const GaraLogo3d({super.key, this.height = 80});

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/3dlogo.png',
      height: height,
      fit: BoxFit.contain,
      errorBuilder: (_, __, ___) => Container(
        height: height,
        width: height,
        decoration: BoxDecoration(
          color: const Color(0xFF1D4ED8),
          borderRadius: BorderRadius.circular(height * 0.2),
        ),
        child: Center(
          child: Text(
            'G',
            style: TextStyle(
              fontSize: height * 0.5,
              fontWeight: FontWeight.w900,
              color: Colors.white,
            ),
          ),
        ),
      ),
    );
  }
}
