// ════════════════════════════════════════════════════════════════════════════
// lib/services/infinity_bypass_exception.dart
//
// Exception khusus untuk kegagalan bypass InfinityFree AES JS Challenge.
// File ini dipisahkan dari InfinityAuthService dan InfinityApiClient
// untuk mencegah circular import.
// ════════════════════════════════════════════════════════════════════════════

/// Exception yang dilempar ketika response dari InfinityFree hosting
/// tidak dapat di-parse sebagai JSON valid.
///
/// Ini terjadi ketika:
/// - AES JS Challenge belum selesai dieksekusi oleh WebView
/// - Server mengembalikan halaman error (403, 503, dll) dalam format HTML
/// - Session/cookie expired sehingga server redirect ke halaman login HTML
///
/// PENTING: Jangan buat fallback JSON! Throw exception ini agar developer
/// dapat melihat raw HTML snippet dan men-debug masalah sebenarnya.
class InfinityFreeBypassException implements Exception {
  /// Deskripsi error dalam bahasa yang dapat dipahami pengguna/developer
  final String message;

  /// Raw HTML/text snippet dari document.body.innerText untuk debugging
  /// (maksimal 500 karakter pertama biasanya cukup untuk identifikasi masalah)
  final String rawHtmlSnippet;

  const InfinityFreeBypassException({
    required this.message,
    required this.rawHtmlSnippet,
  });

  @override
  String toString() {
    final snippet = rawHtmlSnippet.length > 500
        ? rawHtmlSnippet.substring(0, 500)
        : rawHtmlSnippet;
    return 'InfinityFreeBypassException: $message\n'
        '--- RAW HTML SNIPPET (maks 500 chars) ---\n'
        '$snippet';
  }
}
