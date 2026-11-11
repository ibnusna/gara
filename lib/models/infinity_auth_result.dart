class InfinityAuthResult {
  final bool success;
  final String? token;
  final String? role;
  final String? nama;
  final String? username;
  final String? profilePhotoUrl;
  final String? errorMessage;
  final bool isMaintenance;
  final bool mustChangePassword;
  final bool isForcedChange;

  const InfinityAuthResult({
    required this.success,
    this.token,
    this.role,
    this.nama,
    this.username,
    this.profilePhotoUrl,
    this.errorMessage,
    this.isMaintenance = false,
    this.mustChangePassword = false,
    this.isForcedChange = false,
  });

  factory InfinityAuthResult.error(String msg, {bool maintenance = false}) =>
      InfinityAuthResult(
        success: false,
        errorMessage: msg,
        isMaintenance: maintenance,
      );
}
