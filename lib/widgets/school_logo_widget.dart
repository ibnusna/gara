import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../utils/app_config.dart';

class SchoolLogoWidget extends StatelessWidget {
  final String? logoUrl;
  final double size;
  final double borderRadius;
  final Color backgroundColor;
  final Color fallbackIconColor;

  const SchoolLogoWidget({
    super.key,
    this.logoUrl,
    this.size = 50,
    this.borderRadius = 12,
    this.backgroundColor = const Color(0xFFE0F2FE),
    this.fallbackIconColor = const Color(0xFF0284C7),
  });

  String _resolveUrl(String raw) {
    final trimmed = raw.trim();
    if (trimmed.isEmpty) return '';
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
      return trimmed;
    }
    final base = AppConfig.baseUrl.endsWith('/')
        ? AppConfig.baseUrl.substring(0, AppConfig.baseUrl.length - 1)
        : AppConfig.baseUrl;
    final path = trimmed.startsWith('/') ? trimmed : '/$trimmed';
    return '$base$path';
  }

  Widget _buildFallback() {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(borderRadius),
      ),
      child: Icon(
        Icons.school_rounded,
        color: fallbackIconColor,
        size: size * 0.48,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final url = _resolveUrl(logoUrl ?? '');
    if (url.isEmpty) {
      return _buildFallback();
    }

    final isSvg = url.toLowerCase().contains('.svg');

    return Container(
      width: size,
      height: size,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(borderRadius),
      ),
      padding: EdgeInsets.all(size * 0.12),
      child: isSvg
          ? SvgPicture.network(
              url,
              fit: BoxFit.contain,
              placeholderBuilder: (_) => _buildFallback(),
            )
          : Image.network(
              url,
              fit: BoxFit.contain,
              errorBuilder: (_, __, ___) => _buildFallback(),
            ),
    );
  }
}
