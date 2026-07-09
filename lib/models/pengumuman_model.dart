class PengumumanModel {
  final int id;
  final String judul;
  final String isi;
  final String? tanggal;

  PengumumanModel({
    required this.id,
    required this.judul,
    required this.isi,
    this.tanggal,
  });

  factory PengumumanModel.fromJson(Map<String, dynamic> json) {
    return PengumumanModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      judul: json['judul']?.toString() ?? 'Pengumuman',
      isi: json['isi']?.toString() ?? '',
      tanggal: json['updated_at']?.toString(), 
    );
  }
}

class PengumumanResponse {
  final bool success;
  final String? errorMessage;
  final List<PengumumanModel> data;

  const PengumumanResponse({
    required this.success,
    this.errorMessage,
    this.data = const [],
  });

  factory PengumumanResponse.error(String msg) => PengumumanResponse(
    success: false,
    errorMessage: msg,
  );
}
