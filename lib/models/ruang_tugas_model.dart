// ============================================================
//  GARA Flutter — RuangTugasModel
//  Data models for the native Ruang Tugas screens.
//
//  Matches JSON response from RuangTugasApiController:
//   GET  /api/mobile/ruang-tugas/list?mapel_id=X
//   GET  /api/mobile/ruang-tugas/detail/{id}?mapel_id=X
//   POST /api/mobile/ruang-tugas/submit
//   POST /api/mobile/ruang-tugas/delete
// ============================================================

// ── Enums (verified from tugas.status, tugas_pengumpulan.metode) ─────────

/// Computed submission status — categorized by TugasController::index() logic.
enum TugasStatus { aktif, selesai, terlewat }

/// Submission method — enum('link','file') in DB + manual for mark-as-read
enum MetodePengumpulan { link, file, manual }

extension MetodePengumpulanExt on MetodePengumpulan {
  String get apiValue {
    switch (this) {
      case MetodePengumpulan.link:
        return 'link';
      case MetodePengumpulan.file:
        return 'file';
      case MetodePengumpulan.manual:
        return 'manual';
    }
  }

  static MetodePengumpulan fromString(String? s) {
    switch (s) {
      case 'file':
        return MetodePengumpulan.file;
      case 'manual':
        return MetodePengumpulan.manual;
      default:
        return MetodePengumpulan.link;
    }
  }
}

// ── TugasModel — Full assignment + submission row ────────────────────────
class TugasModel {
  // Core tugas fields (from `tugas` table)
  final int id;
  final String judul;
  final String? deskripsi;
  final String? deskripsiPreview;   // Str::limit(100) — from server
  final String? linkLampiran;        // Attachment URL (may be null)
  final DateTime? batasWaktu;
  final String batasWaktuLabel;      // Formatted: "dd MMM, HH:mm" or "Tanpa Batas"
  final String? batasWaktuIso;       // ISO format for countdown timer
  final bool isAutoClose;
  final bool allowUpload;
  final String status;               // 'aktif' | 'draft'
  final String? kategoriAsesmen;     // Formatif | Sumatif | P5 | Remedial
  final String? teknikPenilaian;
  final String? pokokBahasan;
  final String? tipeTugas;           // Individual | Kelompok

  // Computed flags (from server)
  final TugasStatus tugasStatus;
  final bool isUrgent;               // deadline < 24h from now AND aktif

  // Submission fields (from LEFT JOIN tugas_pengumpulan — may be null if not submitted)
  final int? submissionId;
  final String? linkPengumpulan;     // URL or filename
  final String? submissionFileUrl;   // Resolved public URL (for file metode)
  final double? nilai;               // Grade (null = not graded yet)
  final String? catatanSiswa;
  final DateTime? dikumpulkanPada;
  final String? feedbackGuru;
  final MetodePengumpulan? metode;

  const TugasModel({
    required this.id,
    required this.judul,
    required this.batasWaktuLabel,
    required this.isAutoClose,
    required this.allowUpload,
    required this.status,
    required this.tugasStatus,
    required this.isUrgent,
    this.deskripsi,
    this.deskripsiPreview,
    this.linkLampiran,
    this.batasWaktu,
    this.batasWaktuIso,
    this.kategoriAsesmen,
    this.teknikPenilaian,
    this.pokokBahasan,
    this.tipeTugas,
    this.submissionId,
    this.linkPengumpulan,
    this.submissionFileUrl,
    this.nilai,
    this.catatanSiswa,
    this.dikumpulkanPada,
    this.feedbackGuru,
    this.metode,
  });

  bool get isSubmitted => submissionId != null;
  bool get isGraded    => nilai != null;

  factory TugasModel.fromJson(Map<String, dynamic> json) {
    final statusStr = json['tugas_status'] as String? ?? 'aktif';
    final TugasStatus status;
    switch (statusStr) {
      case 'selesai':
        status = TugasStatus.selesai;
        break;
      case 'terlewat':
        status = TugasStatus.terlewat;
        break;
      default:
        status = TugasStatus.aktif;
    }

    DateTime? batasWaktu;
    if (json['batas_waktu'] != null) {
      try {
        batasWaktu = DateTime.parse(json['batas_waktu'] as String);
      } catch (_) {}
    }

    DateTime? dikumpulkanPada;
    if (json['dikumpulkan_pada'] != null) {
      try {
        dikumpulkanPada = DateTime.parse(json['dikumpulkan_pada'] as String);
      } catch (_) {}
    }

    return TugasModel(
      id: json['id'] as int? ?? 0,
      judul: json['judul'] as String? ?? '',
      deskripsi: json['deskripsi'] as String?,
      deskripsiPreview: json['deskripsi_preview'] as String?,
      linkLampiran: json['link_lampiran'] as String?,
      batasWaktu: batasWaktu,
      batasWaktuLabel: json['batas_waktu_label'] as String? ?? 'Tanpa Batas',
      batasWaktuIso: json['batas_waktu_iso'] as String?,
      isAutoClose: json['is_auto_close'] as bool? ?? false,
      allowUpload: json['allow_upload'] as bool? ?? true,
      status: json['status'] as String? ?? 'aktif',
      kategoriAsesmen: json['kategori_asesmen'] as String?,
      teknikPenilaian: json['teknik_penilaian'] as String?,
      pokokBahasan: json['pokok_bahasan'] as String?,
      tipeTugas: json['tipe_tugas'] as String?,
      tugasStatus: status,
      isUrgent: json['is_urgent'] as bool? ?? false,
      submissionId: json['submission_id'] as int?,
      linkPengumpulan: json['link_pengumpulan'] as String?,
      submissionFileUrl: json['submission_file_url'] as String?,
      nilai: json['nilai'] != null ? (json['nilai'] as num).toDouble() : null,
      catatanSiswa: json['catatan_siswa'] as String?,
      dikumpulkanPada: dikumpulkanPada,
      feedbackGuru: json['feedback_guru'] as String?,
      metode: MetodePengumpulanExt.fromString(json['metode'] as String?),
    );
  }

  /// Creates a copy with updated submission fields — used after submit/delete.
  TugasModel copyWithSubmission({
    int? submissionId,
    String? linkPengumpulan,
    String? submissionFileUrl,
    double? nilai,
    String? catatanSiswa,
    DateTime? dikumpulkanPada,
    String? feedbackGuru,
    MetodePengumpulan? metode,
    TugasStatus? tugasStatus,
    bool clearSubmission = false,
  }) {
    if (clearSubmission) {
      return TugasModel(
        id: id, judul: judul, deskripsi: deskripsi, deskripsiPreview: deskripsiPreview,
        linkLampiran: linkLampiran, batasWaktu: batasWaktu, batasWaktuLabel: batasWaktuLabel,
        batasWaktuIso: batasWaktuIso, isAutoClose: isAutoClose, allowUpload: allowUpload,
        status: status, kategoriAsesmen: kategoriAsesmen, teknikPenilaian: teknikPenilaian,
        pokokBahasan: pokokBahasan, tipeTugas: tipeTugas,
        tugasStatus: TugasStatus.aktif, isUrgent: isUrgent,
      );
    }
    return TugasModel(
      id: id, judul: judul, deskripsi: deskripsi, deskripsiPreview: deskripsiPreview,
      linkLampiran: linkLampiran, batasWaktu: batasWaktu, batasWaktuLabel: batasWaktuLabel,
      batasWaktuIso: batasWaktuIso, isAutoClose: isAutoClose, allowUpload: allowUpload,
      status: status, kategoriAsesmen: kategoriAsesmen, teknikPenilaian: teknikPenilaian,
      pokokBahasan: pokokBahasan, tipeTugas: tipeTugas,
      tugasStatus: tugasStatus ?? this.tugasStatus, isUrgent: isUrgent,
      submissionId: submissionId ?? this.submissionId,
      linkPengumpulan: linkPengumpulan ?? this.linkPengumpulan,
      submissionFileUrl: submissionFileUrl ?? this.submissionFileUrl,
      nilai: nilai ?? this.nilai,
      catatanSiswa: catatanSiswa ?? this.catatanSiswa,
      dikumpulkanPada: dikumpulkanPada ?? this.dikumpulkanPada,
      feedbackGuru: feedbackGuru ?? this.feedbackGuru,
      metode: metode ?? this.metode,
    );
  }
}

// ── Response Wrappers ────────────────────────────────────────────────────

class TugasListResponse {
  final bool success;
  final String? errorMessage;
  final List<TugasModel> aktif;
  final List<TugasModel> selesai;
  final List<TugasModel> terlewat;

  const TugasListResponse({
    required this.success,
    this.errorMessage,
    this.aktif    = const [],
    this.selesai  = const [],
    this.terlewat = const [],
  });

  factory TugasListResponse.error(String message) =>
      TugasListResponse(success: false, errorMessage: message);

  int get totalAktif    => aktif.length;
  int get totalSelesai  => selesai.length;
  int get totalTerlewat => terlewat.length;
}

class TugasApiResponse {
  final bool success;
  final String message;
  final Map<String, dynamic>? data;

  const TugasApiResponse({
    required this.success,
    required this.message,
    this.data,
  });
}
