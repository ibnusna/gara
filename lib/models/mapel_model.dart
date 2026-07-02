// ============================================================
//  GARA Flutter — Model: Mata Pelajaran (Mapel)
//  Fase 2: Real data mapping dari Laravel API
// ============================================================

class MapelModel {
  final int id;
  final String namaMapel;
  final String? kodeMapel;

  const MapelModel({
    required this.id,
    required this.namaMapel,
    this.kodeMapel,
  });

  /// Factory untuk parsing data mapel dari JSON API Laravel
  factory MapelModel.fromJson(Map<String, dynamic> json) {
    return MapelModel(
      id:        json['id'] as int,
      namaMapel: json['nama_mapel'] as String,
      kodeMapel: json['kode_mapel'] as String?,
    );
  }
}

/// Model untuk membungkus seluruh respons dari /api/mobile/mapel
class MapelResponse {
  final bool success;
  final String namaSiswa;
  final String kelasSiswa;
  final String sekolahNama;
  final bool gateUjianOpen;
  final List<MapelModel> mapelList;
  final String? errorMessage;

  const MapelResponse({
    required this.success,
    this.namaSiswa = '',
    this.kelasSiswa = '',
    this.sekolahNama = '',
    this.gateUjianOpen = false,
    this.mapelList = const [],
    this.errorMessage,
  });

  factory MapelResponse.error(String message) {
    return MapelResponse(success: false, errorMessage: message);
  }
}

/// Data dummy untuk FASE 1 (Frontend only)
/// FASE 2: Hapus dummy ini, ganti dengan data dari API /student/pilih-mapel
class DummyMapelData {
  static const List<MapelModel> mapelList = [
    MapelModel(id: 1, namaMapel: 'Matematika', kodeMapel: 'MTK'),
    MapelModel(id: 2, namaMapel: 'Bahasa Indonesia', kodeMapel: 'BIND'),
    MapelModel(id: 3, namaMapel: 'Bahasa Inggris', kodeMapel: 'BING'),
    MapelModel(id: 4, namaMapel: 'Fisika', kodeMapel: 'FIS'),
    MapelModel(id: 5, namaMapel: 'Kimia', kodeMapel: 'KIM'),
    MapelModel(id: 6, namaMapel: 'Biologi', kodeMapel: 'BIO'),
  ];

  static const String namaSiswa = 'Budi Santoso';
  static const String kelasSiswa = 'XI IPA 1';
  static const String sekolahNama = 'Garuda Akademi';
  static const bool gateUjianOpen = true;
}
