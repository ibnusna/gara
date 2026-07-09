
class DiskusiThread {
  final int id;
  final int mapelId;
  final int kelasId;
  final String judul;
  final String isiKonten;
  final String rolePembuat; 
  final int? siswaId;
  final String? mediaPath;
  final String createdAt;
  final bool isPinned;
  final String status;
  
  
  final String namaPenulis;
  final String nisSiswa;
  final String avatar;
  final String waktuRelatif;
  final String? waktuLengkap;
  final bool isGuru;
  final bool isMe;
  final int jumlahBalasan;

  DiskusiThread({
    required this.id,
    required this.mapelId,
    required this.kelasId,
    required this.judul,
    required this.isiKonten,
    required this.rolePembuat,
    this.siswaId,
    this.mediaPath,
    required this.createdAt,
    required this.isPinned,
    required this.status,
    required this.namaPenulis,
    required this.nisSiswa,
    required this.avatar,
    required this.waktuRelatif,
    this.waktuLengkap,
    required this.isGuru,
    required this.isMe,
    required this.jumlahBalasan,
  });

  factory DiskusiThread.fromJson(Map<String, dynamic> json) {
    return DiskusiThread(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      mapelId: json['mapel_id'] is int ? json['mapel_id'] : int.tryParse(json['mapel_id']?.toString() ?? '0') ?? 0,
      kelasId: json['kelas_id'] is int ? json['kelas_id'] : int.tryParse(json['kelas_id']?.toString() ?? '0') ?? 0,
      judul: json['judul'] ?? '',
      isiKonten: json['isi_konten'] ?? '',
      rolePembuat: json['role_pembuat'] ?? 'siswa',
      siswaId: json['siswa_id'] != null ? int.tryParse(json['siswa_id'].toString()) : null,
      mediaPath: json['media_path'],
      createdAt: json['created_at'] ?? '',
      isPinned: json['is_pinned'] == 1 || json['is_pinned'] == true || json['is_pinned'] == '1',
      status: json['status'] ?? 'aktif',
      namaPenulis: json['nama_penulis'] ?? 'Siswa',
      nisSiswa: json['nis_siswa'] ?? '-',
      avatar: json['avatar'] ?? '',
      waktuRelatif: json['waktu_relatif'] ?? '',
      waktuLengkap: json['waktu_lengkap'],
      isGuru: json['is_guru'] == true || json['is_guru'] == 1 || json['is_guru'] == '1',
      isMe: json['is_me'] == true || json['is_me'] == 1 || json['is_me'] == '1',
      jumlahBalasan: json['jumlah_balasan'] is int ? json['jumlah_balasan'] : int.tryParse(json['jumlah_balasan']?.toString() ?? '0') ?? 0,
    );
  }
}

class DiskusiReply {
  final int id;
  final int threadId;
  final String isiBalasan;
  final String? mediaPath;
  final String rolePembuat;
  final int? siswaId;
  final String createdAt;
  
  
  final String namaPenulis;
  final String nisSiswa;
  final String avatar;
  final String waktuRelatif;
  final bool isGuru;
  final bool isMe;

  DiskusiReply({
    required this.id,
    required this.threadId,
    required this.isiBalasan,
    this.mediaPath,
    required this.rolePembuat,
    this.siswaId,
    required this.createdAt,
    required this.namaPenulis,
    required this.nisSiswa,
    required this.avatar,
    required this.waktuRelatif,
    required this.isGuru,
    required this.isMe,
  });

  factory DiskusiReply.fromJson(Map<String, dynamic> json) {
    return DiskusiReply(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      threadId: json['thread_id'] is int ? json['thread_id'] : int.tryParse(json['thread_id']?.toString() ?? '0') ?? 0,
      isiBalasan: json['isi_balasan'] ?? '',
      mediaPath: json['media_path'],
      rolePembuat: json['role_pembuat'] ?? 'siswa',
      siswaId: json['siswa_id'] != null ? int.tryParse(json['siswa_id'].toString()) : null,
      createdAt: json['created_at'] ?? '',
      namaPenulis: json['nama_penulis'] ?? 'Siswa',
      nisSiswa: json['nis_siswa'] ?? '-',
      avatar: json['avatar'] ?? '',
      waktuRelatif: json['waktu_relatif'] ?? '',
      isGuru: json['is_guru'] == true || json['is_guru'] == 1 || json['is_guru'] == '1',
      isMe: json['is_me'] == true || json['is_me'] == 1 || json['is_me'] == '1',
    );
  }
}
