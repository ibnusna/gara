
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

/// Logo GARA (putih) — untuk latar gelap (login, hero)
class GaraLogoWhite extends StatelessWidget {
  final double height;
  const GaraLogoWhite({super.key, this.height = 36});

  @override
  Widget build(BuildContext context) {
    return SvgPicture.asset(
      'assets/images/GARA_WHITE.svg',
      height: height,
      fit: BoxFit.contain,
    );
  }
}

/// Logo FARA (hitam) — untuk latar terang (app header)
/// setara: <img src="{{ asset('assets/img/FARA_BLACK.svg') }}" style="height:35px">
class GaraLogoBlack extends StatelessWidget {
  final double height;
  const GaraLogoBlack({super.key, this.height = 35});

  @override
  Widget build(BuildContext context) {
    return SvgPicture.asset(
      'assets/images/FARA_BLACK.svg',
      height: height,
      fit: BoxFit.contain,
    );
  }
}

/// Logo 3D (colored) — untuk splash premium
class GaraLogo3d extends StatelessWidget {
  final double height;
  const GaraLogo3d({super.key, this.height = 80});

  @override
  Widget build(BuildContext context) {
    return SvgPicture.asset(
      'assets/images/3dlogo.svg',
      height: height,
      fit: BoxFit.contain,
    );
  }
}
