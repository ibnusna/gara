










class GuruInfoModel {
  final String namaLengkap;
  final String avatarUrl;

  const GuruInfoModel({required this.namaLengkap, required this.avatarUrl});

  factory GuruInfoModel.fromJson(Map<String, dynamic> json) => GuruInfoModel(
        namaLengkap: json['nama_lengkap'] as String? ?? 'Guru Pengajar',
        avatarUrl: json['avatar_url'] as String? ?? '',
      );
}


class BabModel {
  final int bab;
  final int semester;
  final int totalTopik;
  final String namaBab;

  const BabModel({
    required this.bab,
    required this.semester,
    required this.totalTopik,
    required this.namaBab,
  });

  factory BabModel.fromJson(Map<String, dynamic> json) => BabModel(
        bab: json['bab'] as int? ?? 0,
        semester: json['semester'] as int? ?? 1,
        totalTopik: json['total_topik'] as int? ?? 0,
        namaBab: json['nama_bab_otomatis'] as String? ?? 'Bab ${json['bab']}',
      );

  
  String get babFormatted => bab.toString().padLeft(2, '0');
}


class BabListResponse {
  final bool success;
  final String? errorMessage;
  final int mapelId;
  final int kelasId;
  final GuruInfoModel? guru;
  final List<BabModel> data;

  const BabListResponse({
    required this.success,
    this.errorMessage,
    this.mapelId = 0,
    this.kelasId = 0,
    this.guru,
    this.data = const [],
  });

  factory BabListResponse.error(String message) =>
      BabListResponse(success: false, errorMessage: message);
}


class TopicModel {
  final int id;
  final String idMateri;
  final int bab;
  final int bagian;
  final String judulMateri;
  final int semester;
  
  final bool hasYoutube;
  final bool hasPpt;
  final bool hasModul;
  final bool hasTugas;
  final bool hasNotebook;

  const TopicModel({
    required this.id,
    required this.idMateri,
    required this.bab,
    required this.bagian,
    required this.judulMateri,
    required this.semester,
    this.hasYoutube = false,
    this.hasPpt = false,
    this.hasModul = false,
    this.hasTugas = false,
    this.hasNotebook = false,
  });

  factory TopicModel.fromJson(Map<String, dynamic> json) => TopicModel(
        id: json['id'] as int? ?? 0,
        idMateri: json['id_materi'] as String? ?? '',
        bab: json['bab'] as int? ?? 0,
        bagian: json['bagian'] as int? ?? 0,
        judulMateri: json['judul_materi'] as String? ?? '',
        semester: json['semester'] as int? ?? 1,
        hasYoutube: json['has_youtube'] as bool? ?? false,
        hasPpt: json['has_ppt'] as bool? ?? false,
        hasModul: json['has_modul'] as bool? ?? false,
        hasTugas: json['has_tugas'] as bool? ?? false,
        hasNotebook: json['has_notebook'] as bool? ?? false,
      );
}


class TopicListResponse {
  final bool success;
  final String? errorMessage;
  final int bab;
  final String judulBab;
  final List<TopicModel> data;

  const TopicListResponse({
    required this.success,
    this.errorMessage,
    this.bab = 0,
    this.judulBab = '',
    this.data = const [],
  });

  factory TopicListResponse.error(String message) =>
      TopicListResponse(success: false, errorMessage: message);
}


class MateriDetailModel {
  final int id;
  final String idMateri;
  final int bab;
  final int bagian;
  final String judulMateri;
  final int semester;
  
  final bool hasVideo;
  final String? youtubeId;
  final String? youtubeEmbedUrl;
  final String? youtubeThumb;
  
  final String? linkPpt;
  final String? linkModul;
  final String? linkTugas;
  final String? linkNotebook;
  
  final bool pptIsGdrive;
  final bool modulIsGdrive;
  final bool tugasIsGdrive;
  
  final GuruInfoModel? guru;

  const MateriDetailModel({
    required this.id,
    required this.idMateri,
    required this.bab,
    required this.bagian,
    required this.judulMateri,
    required this.semester,
    this.hasVideo = false,
    this.youtubeId,
    this.youtubeEmbedUrl,
    this.youtubeThumb,
    this.linkPpt,
    this.linkModul,
    this.linkTugas,
    this.linkNotebook,
    this.pptIsGdrive = false,
    this.modulIsGdrive = false,
    this.tugasIsGdrive = false,
    this.guru,
  });

  factory MateriDetailModel.fromJson(Map<String, dynamic> json) {
    final g = json['guru'] as Map<String, dynamic>?;
    return MateriDetailModel(
      id: json['id'] as int? ?? 0,
      idMateri: json['id_materi'] as String? ?? '',
      bab: json['bab'] as int? ?? 0,
      bagian: json['bagian'] as int? ?? 0,
      judulMateri: json['judul_materi'] as String? ?? '',
      semester: json['semester'] as int? ?? 1,
      hasVideo: json['has_video'] as bool? ?? false,
      youtubeId: json['youtube_id'] as String?,
      youtubeEmbedUrl: json['youtube_embed_url'] as String?,
      youtubeThumb: json['youtube_thumb'] as String?,
      linkPpt: json['link_ppt'] as String?,
      linkModul: json['link_modul'] as String?,
      linkTugas: json['link_tugas'] as String?,
      linkNotebook: json['link_notebook'] as String?,
      pptIsGdrive: json['ppt_is_gdrive'] as bool? ?? false,
      modulIsGdrive: json['modul_is_gdrive'] as bool? ?? false,
      tugasIsGdrive: json['tugas_is_gdrive'] as bool? ?? false,
      guru: g != null ? GuruInfoModel.fromJson(g) : null,
    );
  }

  
  String get babBagianLabel =>
      'BAB ${bab.toString().padLeft(2, '0')} - Bagian $bagian';
}


class MateriDetailResponse {
  final bool success;
  final String? errorMessage;
  final MateriDetailModel? data;

  const MateriDetailResponse({
    required this.success,
    this.errorMessage,
    this.data,
  });

  factory MateriDetailResponse.error(String message) =>
      MateriDetailResponse(success: false, errorMessage: message);
}
