/// Definisi path rute internal LMS GARA.
class AppRoutes {
  AppRoutes._();

  static const String ruangBelajarPath = '/student/materi';
  static const String ruangBelajarBabListPath = '/ruang-belajar/bab-list';
  static const String ruangBelajarTopicListPath = '/ruang-belajar/topic-list';
  static const String ruangBelajarDetailPath = '/ruang-belajar/detail';
  static const String ruangBelajarMarkSelesaiPath = '/ruang-belajar/mark-selesai';

  static const String ruangTugasPath = '/student/tugas';
  static const String ruangTugasListPath = '/ruang-tugas/list';
  static const String ruangTugasDetailPath = '/ruang-tugas/detail';
  static const String ruangTugasSubmitPath = '/ruang-tugas/submit';
  static const String ruangTugasDeletePath = '/ruang-tugas/delete';
  static const String ruangKompetensiPath = '/student/ruang-kompetensi';
  static const String ruangDiskusiPath = '/student/diskusi';
  static const String ruangUjianEntryPath = '/ruang-ujian';
  static const String ruangUjianKonfirmasiPath = '/ruang-ujian/konfirmasi';
  static const String ruangUjianPath = '/ruang-ujian/arena';
  static const String ruangUjianHasilPath = '/ruang-ujian/hasil';
  static const String examStatusPath = '/api/student/exam-status';
  static const String handoffPath = '/auth/webview-handoff';

  static const String studentDashboardPath = '/student/dashboard';
  static const String guruDashboardPath = '/guru/pilih-sesi';
  static const String operatorDashboardPath = '/operator/dashboard';
  static const String kepsekDashboardPath = '/kepsek/dashboard';
  static const String superAdminDashboardPath = '/super-admin/dashboard';

  static const String operatorUsersPath = '/operator/users';
  static const String operatorAsesmenJadwal = '/operator/asesmen/jadwal';
  static const String operatorAsesmenHasil = '/operator/asesmen/hasil';
  static const String operatorMasterPath = '/operator/master';
  static const String operatorProfilPath = '/operator/profil';
  static const String operatorPasswordPath = '/operator/ubah-password';

  static const String guruMateriPath = '/guru/materi';
  static const String guruTugasPath = '/guru/tugas';
  static const String guruPenilaianPath = '/guru/penilaian';
  static const String guruProfilPath = '/guru/profil';

  static const String studentPasswordPath = '/student/ubah-password';
  static const String studentTentangSayaPath = '/student/tentang-saya';
}
