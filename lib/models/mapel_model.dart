




class JadwalModel {
  final String hari;
  final String jamMulai;
  final String jamSelesai;

  const JadwalModel({
    required this.hari,
    required this.jamMulai,
    required this.jamSelesai,
  });

  factory JadwalModel.fromJson(Map<String, dynamic> json) {
    String jamMulai = json['jam_mulai']?.toString() ?? '00:00';
    String jamSelesai = json['jam_selesai']?.toString() ?? '00:00';
    if (jamMulai.length > 5) jamMulai = jamMulai.substring(0, 5);
    if (jamSelesai.length > 5) jamSelesai = jamSelesai.substring(0, 5);
    return JadwalModel(
      hari: json['hari']?.toString() ?? '',
      jamMulai: jamMulai,
      jamSelesai: jamSelesai,
    );
  }
}

class MapelModel {
  final int id;
  final String namaMapel;
  final String? kodeMapel;
  final List<JadwalModel> jadwal;

  const MapelModel({
    required this.id,
    required this.namaMapel,
    this.kodeMapel,
    this.jadwal = const [],
  });

  
  factory MapelModel.fromJson(Map<String, dynamic> json) {
    var jadwalList = json['jadwal'] as List? ?? [];
    List<JadwalModel> parsedJadwal = jadwalList.map((j) => JadwalModel.fromJson(Map<String, dynamic>.from(j as Map))).toList();
    
    return MapelModel(
      id:        json['id'] is int ? json['id'] as int : int.tryParse(json['id'].toString()) ?? 0,
      namaMapel: json['nama_mapel']?.toString() ?? '',
      kodeMapel: json['kode_mapel']?.toString(),
      jadwal:    parsedJadwal,
    );
  }
}


class MapelResponse {
  final bool success;
  final String namaSiswa;
  final String kelasSiswa;
  final String sekolahNama;
  final bool gateUjianOpen;
  final List<MapelModel> mapelList;
  final String? errorMessage;
  /// true jika data diambil dari cache lokal (mode offline)
  final bool fromCache;

  const MapelResponse({
    required this.success,
    this.namaSiswa = '',
    this.kelasSiswa = '',
    this.sekolahNama = '',
    this.gateUjianOpen = false,
    this.mapelList = const [],
    this.errorMessage,
    this.fromCache = false,
  });

  factory MapelResponse.error(String message) {
    return MapelResponse(success: false, errorMessage: message);
  }

  MapelResponse copyWith({
    bool? success,
    String? namaSiswa,
    String? kelasSiswa,
    String? sekolahNama,
    bool? gateUjianOpen,
    List<MapelModel>? mapelList,
    String? errorMessage,
    bool? fromCache,
  }) {
    return MapelResponse(
      success:       success       ?? this.success,
      namaSiswa:     namaSiswa     ?? this.namaSiswa,
      kelasSiswa:    kelasSiswa    ?? this.kelasSiswa,
      sekolahNama:   sekolahNama   ?? this.sekolahNama,
      gateUjianOpen: gateUjianOpen ?? this.gateUjianOpen,
      mapelList:     mapelList     ?? this.mapelList,
      errorMessage:  errorMessage  ?? this.errorMessage,
      fromCache:     fromCache     ?? this.fromCache,
    );
  }
}



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
