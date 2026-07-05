CREATE TABLE IF NOT EXISTS master_jam_pelajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hari VARCHAR(20) NOT NULL,
    urutan_jam INT NULL,
    jenis_kegiatan ENUM('KBM', 'ISTIRAHAT', 'UPACARA', 'PEMBIASAAN') DEFAULT 'KBM',
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL
);

CREATE TABLE IF NOT EXISTS jadwal_pelajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jam_pelajaran_id INT NOT NULL,
    kelas_id INT NOT NULL,
    mapel_id INT NOT NULL,
    guru_id INT NOT NULL,
    FOREIGN KEY (jam_pelajaran_id) REFERENCES master_jam_pelajaran(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES users(id) ON DELETE CASCADE
);
