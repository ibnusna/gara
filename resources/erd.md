# 🗄️ GARA — Entity Relationship Diagram (ERD)

Dokumen ini berisi diagram ERD lengkap untuk seluruh database pada platform GARA, dibagi ke dalam 3 database terpisah sesuai arsitektur multi-koneksi Eloquent.

> **Cara pakai:** Copy blok ` ```mermaid ``` ` ke [https://mermaid.live](https://mermaid.live) untuk preview.

---

## Arsitektur Database

```
┌─────────────────┐   ┌──────────────────┐   ┌─────────────────────┐
│   mysql_auth    │   │   mysql_apps     │   │   mysql_asesmen     │
│  ─────────────  │   │  ──────────────  │   │  ─────────────────  │
│  users          │   │  guru            │   │  bank_soal          │
│  roles          │   │  siswa           │   │  jadwal_ujian       │
│  permissions    │   │  kelas           │   │  asesmen_config     │
│  role_perms     │   │  mata_pelajaran  │   │  hasil_ujian        │
│                 │   │  class_subjects  │   │                     │
│                 │   │  teacher_subj.   │   │                     │
│                 │   │  teach_assign.   │   │                     │
│                 │   │  absensi         │   │                     │
│                 │   │  absensi_detail  │   │                     │
│                 │   │  agenda_harian   │   │                     │
│                 │   │  ruang_kompetens │   │                     │
│                 │   │  nilai_tugas     │   │                     │
│                 │   │  tugas_pengump.  │   │                     │
│                 │   │  app_settings    │   │                     │
│                 │   │  audit_logs      │   │                     │
│                 │   │  ip_blocks       │   │                     │
└─────────────────┘   └──────────────────┘   └─────────────────────┘
```

---

## ERD 1 — `mysql_auth` (Autentikasi & RBAC)

```mermaid
erDiagram
    USERS {
        int id PK
        string username UK "Login identifier"
        string nama_lengkap
        string password_hash "Hashed via Laravel default"
        int role_id FK
        tinyint status_aktif "0=nonaktif, 1=aktif"
    }

    ROLES {
        int id PK
        string role_name "super_admin|operator|kepsek|guru|siswa"
    }

    PERMISSIONS {
        int id PK
        string name UK "permission identifier"
        string description
    }

    ROLE_PERMISSIONS {
        int role_id FK
        int permission_id FK
    }

    USERS }o--|| ROLES : "memiliki role"
    ROLES ||--o{ ROLE_PERMISSIONS : "ditetapkan"
    PERMISSIONS ||--o{ ROLE_PERMISSIONS : "termasuk dalam"
```

---

## ERD 2 — `mysql_apps` (Operasional Akademik)

### 2a — Master Data & Pengguna

```mermaid
erDiagram
    GURU {
        int id PK
        int user_id FK "→ auth_gara.users"
        string nip UK
        string bidang_studi
    }

    SISWA {
        int id PK
        int user_id FK "→ auth_gara.users"
        string nis UK
        int kelas_id FK
    }

    KELAS {
        int id PK
        string nama_kelas "X-MIPA-1, XI-IPS-2, dst"
        string tingkat "X | XI | XII"
    }

    MATA_PELAJARAN {
        int id PK
        string kode_mapel UK
        string nama_mapel
    }

    CLASS_SUBJECTS {
        int id PK
        int kelas_id FK
        int mapel_id FK
    }

    TEACHER_SUBJECTS {
        int id PK
        int guru_id FK
        int mapel_id FK
    }

    TEACHING_ASSIGNMENTS {
        int id PK
        int guru_id FK
        int kelas_id FK
        int mapel_id FK
    }

    SISWA }o--|| KELAS : "terdaftar di"
    KELAS ||--o{ CLASS_SUBJECTS : "memiliki mapel"
    MATA_PELAJARAN ||--o{ CLASS_SUBJECTS : "diambil oleh kelas"
    GURU ||--o{ TEACHER_SUBJECTS : "mengajar"
    MATA_PELAJARAN ||--o{ TEACHER_SUBJECTS : "diajar oleh guru"
    GURU ||--o{ TEACHING_ASSIGNMENTS : "ditugaskan"
    KELAS ||--o{ TEACHING_ASSIGNMENTS : "sasaran kelas"
    MATA_PELAJARAN ||--o{ TEACHING_ASSIGNMENTS : "sasaran mapel"
```

### 2b — Absensi & Agenda

```mermaid
erDiagram
    ABSENSI {
        int id PK
        int mapel_id FK "→ mata_pelajaran"
        int kelas_id FK "→ kelas"
        date tanggal
        time jam_mulai
        time jam_selesai
        int pertemuan_ke
        string pokok_bahasan
        string rangkuman
        string materi
        string metode "ceramah | diskusi | praktik | dll"
    }

    ABSENSI_DETAIL {
        int id PK
        int absensi_id FK
        int siswa_id FK "→ siswa"
        string status_hadir "H | A | I | S"
    }

    AGENDA_HARIAN {
        int id PK
        int absensi_id FK
        string catatan "Jurnal mengajar guru"
        timestamp created_at
    }

    KELAS {
        int id PK
        string nama_kelas
        string tingkat
    }

    MATA_PELAJARAN {
        int id PK
        string kode_mapel
        string nama_mapel
    }

    SISWA {
        int id PK
        int user_id FK
        string nis
        int kelas_id FK
    }

    ABSENSI }o--|| KELAS : "untuk kelas"
    ABSENSI }o--|| MATA_PELAJARAN : "untuk mapel"
    ABSENSI ||--o{ ABSENSI_DETAIL : "memiliki detail"
    ABSENSI ||--o| AGENDA_HARIAN : "memiliki agenda"
    ABSENSI_DETAIL }o--|| SISWA : "mencatat kehadiran"
```

### 2c — Konten & Penilaian

```mermaid
erDiagram
    MATERI {
        int id PK
        int guru_id FK "→ guru"
        int mapel_id FK
        int kelas_id FK
        string judul
        string bab "Bab 1, Bab 2, dst"
        text deskripsi
        string tipe "file | link | teks"
        string path_file
        int urutan
        timestamp created_at
    }

    RUANG_KOMPETENSIS {
        int id PK
        int guru_id FK "→ guru"
        int mapel_id FK
        int kelas_id FK
        string judul
        string link
        string deskripsi
        tinyint is_archived "0=aktif, 1=arsip"
        timestamp created_at
    }

    TUGAS_PENGUMPULAN {
        int id PK
        int guru_id FK
        int mapel_id FK
        int kelas_id FK
        string judul
        text instruksi
        datetime deadline
        tinyint is_open "0=tutup, 1=buka"
        timestamp created_at
    }

    NILAI_TUGAS {
        int id PK
        int siswa_id FK "→ siswa"
        int guru_id FK "→ guru"
        int tugas_id FK "→ tugas_pengumpulan"
        string path_file_submission
        float nilai
        string keterangan
        timestamp submitted_at
    }

    GURU {
        int id PK
        int user_id FK
        string nip
    }

    SISWA {
        int id PK
        int user_id FK
        string nis
    }

    GURU ||--o{ MATERI : "membuat"
    GURU ||--o{ RUANG_KOMPETENSIS : "mengelola"
    GURU ||--o{ TUGAS_PENGUMPULAN : "membuat"
    TUGAS_PENGUMPULAN ||--o{ NILAI_TUGAS : "dikumpulkan via"
    SISWA ||--o{ NILAI_TUGAS : "menerima/mengumpulkan"
    GURU ||--o{ NILAI_TUGAS : "memberi nilai"
```

### 2d — Keamanan & Sistem

```mermaid
erDiagram
    APP_SETTINGS {
        int id PK
        string setting_key UK "maintenance_mode | sekolah_nama | dll"
        string setting_value
    }

    AUDIT_LOGS {
        int id PK
        int user_id FK "→ auth_gara.users"
        string action "LOGIN | CREATE | UPDATE | DELETE | dll"
        string description
        string ip_address
        timestamp created_at
    }

    IP_BLOCKS {
        int id PK
        string ip_address UK
        string alasan
        int blocked_by FK "→ auth_gara.users (super_admin)"
        timestamp created_at
    }

    USERS_REF {
        int id PK
        string username
        string role_name "super_admin | operator | dll"
    }

    USERS_REF ||--o{ AUDIT_LOGS : "mencatat aktivitas"
    USERS_REF ||--o{ IP_BLOCKS : "memblokir IP"
```

---

## ERD 3 — `mysql_asesmen` (Sistem Ujian)

```mermaid
erDiagram
    BANK_SOAL {
        int id_soal PK
        int id_guru FK "→ mysql_apps.guru"
        string mapel "Nama mapel (string)"
        string kelas "Nama kelas (string)"
        string tipe_soal "pilihan_ganda | essay"
        text konten_soal
        string opsi_a
        string opsi_b
        string opsi_c
        string opsi_d
        string opsi_e
        string kunci_jawaban "A | B | C | D | E"
        int bobot "Bobot nilai soal"
        string status_soal "DRAFT | APPROVED | REJECTED"
        timestamp created_at
        timestamp updated_at
    }

    JADWAL_UJIAN {
        int id_jadwal PK
        string mapel
        string kelas
        date tanggal_ujian
        string hari
        time jam_mulai
        time jam_selesai
        int durasi "Dalam menit"
        string jenis_asesmen "PH | PTS | PAS | PAT"
        tinyint pengulangan "0=tidak, 1=boleh"
        tinyint tampilkan_jawaban "0=tidak, 1=ya"
        tinyint tampilkan_nilai "0=tidak, 1=ya"
        string mode_submit "auto | manual"
        string token UK "Kode akses unik ujian"
        int created_by FK "→ auth_gara.users (operator)"
        timestamp created_at
        timestamp updated_at
    }

    ASESMEN_CONFIG {
        int id_config PK
        string jenis_asesmen "PH | PTS | PAS"
        string status_pintu "OPEN | CLOSED"
        string status_pintu_siswa "OPEN | CLOSED"
        int opened_by FK "→ auth_gara.users (operator)"
        timestamp updated_at
    }

    HASIL_UJIAN {
        int id PK
        int jadwal_id FK "→ jadwal_ujian"
        int siswa_id FK "→ mysql_apps.siswa"
        float nilai "Skor akhir"
        int benar "Jumlah jawaban benar"
        int salah "Jumlah jawaban salah"
        text jawaban_json "Snapshot jawaban siswa"
        timestamp submitted_at
    }

    JADWAL_UJIAN ||--o{ HASIL_UJIAN : "menghasilkan nilai"
    JADWAL_UJIAN }o--o{ BANK_SOAL : "memuat soal-soal"
    ASESMEN_CONFIG ||--o{ JADWAL_UJIAN : "mengontrol akses"
```

---

## ERD Lengkap — Relasi Lintas Database

> Catatan: ERD lintas database divisualisasikan secara konseptual karena MySQL tidak mendukung _foreign key_ lintas database. Relasi ini dijaga di level **aplikasi (Eloquent)**.

```mermaid
erDiagram
    %% ── mysql_auth ──
    USERS {
        int id PK
        string username
        int role_id FK
        tinyint status_aktif
    }
    ROLES {
        int id PK
        string role_name
    }

    %% ── mysql_apps ──
    GURU {
        int id PK
        int user_id FK "→ users.id"
        string nip
    }
    SISWA {
        int id PK
        int user_id FK "→ users.id"
        string nis
        int kelas_id FK
    }
    KELAS {
        int id PK
        string nama_kelas
    }
    MATA_PELAJARAN {
        int id PK
        string nama_mapel
    }
    TEACHING_ASSIGNMENTS {
        int id PK
        int guru_id FK
        int kelas_id FK
        int mapel_id FK
    }
    ABSENSI {
        int id PK
        int kelas_id FK
        int mapel_id FK
        date tanggal
    }
    ABSENSI_DETAIL {
        int id PK
        int absensi_id FK
        int siswa_id FK
        string status_hadir
    }
    NILAI_TUGAS {
        int id PK
        int siswa_id FK
        int guru_id FK
        float nilai
    }
    APP_SETTINGS {
        int id PK
        string setting_key
        string setting_value
    }
    AUDIT_LOGS {
        int id PK
        int user_id FK "→ users.id"
        string action
    }

    %% ── mysql_asesmen ──
    BANK_SOAL {
        int id_soal PK
        int id_guru FK "→ guru.id"
        string status_soal
        string kunci_jawaban
    }
    JADWAL_UJIAN {
        int id_jadwal PK
        string token UK
        int created_by FK "→ users.id"
        string jenis_asesmen
    }
    ASESMEN_CONFIG {
        int id_config PK
        string status_pintu
        int opened_by FK "→ users.id"
    }
    HASIL_UJIAN {
        int id PK
        int jadwal_id FK
        int siswa_id FK "→ siswa.id"
        float nilai
    }

    %% ── Relasi ──
    USERS }o--|| ROLES : "role"
    USERS ||--o| GURU : "profil guru"
    USERS ||--o| SISWA : "profil siswa"
    SISWA }o--|| KELAS : "terdaftar"
    GURU ||--o{ TEACHING_ASSIGNMENTS : "ditugaskan"
    KELAS ||--o{ TEACHING_ASSIGNMENTS : "target kelas"
    MATA_PELAJARAN ||--o{ TEACHING_ASSIGNMENTS : "target mapel"
    ABSENSI }o--|| KELAS : "untuk"
    ABSENSI }o--|| MATA_PELAJARAN : "untuk"
    ABSENSI ||--o{ ABSENSI_DETAIL : "rincian hadir"
    ABSENSI_DETAIL }o--|| SISWA : "siswa"
    NILAI_TUGAS }o--|| SISWA : "penerima"
    NILAI_TUGAS }o--|| GURU : "pemberi"
    USERS ||--o{ AUDIT_LOGS : "aktivitas"
    GURU ||--o{ BANK_SOAL : "membuat soal"
    JADWAL_UJIAN ||--o{ HASIL_UJIAN : "hasil ujian"
    SISWA ||--o{ HASIL_UJIAN : "nilai siswa"
```

---

_Dibuat: April 2026 — GARA Platform ERD Documentation_
