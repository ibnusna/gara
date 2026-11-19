/// Model hasil autentikasi dan status profil pengguna.
class AuthResult {
  final bool success;
  final String? token;
  final String? role;
  final String? nama;
  final String? username;
  final String? profilePhotoUrl;
  final String? errorMessage;
  final bool isMaintenance;

  const AuthResult({
    required this.success,
    this.token,
    this.role,
    this.nama,
    this.username,
    this.profilePhotoUrl,
    this.errorMessage,
    this.isMaintenance = false,
  });
}

/// Status pengembalian untuk cek profil:
/// - [success]: profil berhasil diambil
/// - [sessionExpired]: token 401/Unauthenticated (login di perangkat lain)
/// - [failure]: error jaringan atau server lainnya
enum AuthProfileStatus {
  success,
  sessionExpired,
  failure,
}
