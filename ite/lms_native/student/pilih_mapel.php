<?php
// GARA - Garuda Akademi
// File: student/pilih_mapel.php
// Tujuan: Halaman pemilihan mata pelajaran setelah login siswa.

// PENTING: Definisi konstanta untuk melewati pengecekan mapel di auth_helper.php
define('SKIP_MAPEL_CHECK', true);

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

// Cek Login Siswa (Tanpa redirect loop karena SKIP_MAPEL_CHECK)
requireSiswa();

$nama_siswa = $_SESSION['nama'];
$kelas_siswa = $_SESSION['nama_kelas'];

// Ambil Daftar Mata Pelajaran Aktif
try {
    // Kita ambil semua mapel.
    // Jika nanti ada kebutuhan filter mapel per kelas, query bisa disesuaikan.
    $stmt = $pdo->query("SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC");
    $mapel_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mapel_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pilih Mata Pelajaran | GARA</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Base CSS (Ambil dari login.css untuk konsistensi atau inline) -->
    <style>
        :root {
            --primary: #0056b3;
            --secondary: #6c757d;
            --bg-light: #f4f6f9;
            --card-bg: #ffffff;
            --text-dark: #333333;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }

        .container-pilih {
            max-width: 600px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .header-section {
            margin-top: 40px;
            margin-bottom: 30px;
        }
        
        .welcome-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .sub-text {
            color: var(--secondary);
            font-size: 0.95rem;
        }

        .mapel-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        /* Card Style */
        .mapel-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            color: inherit;
        }

        .mapel-card:active {
            transform: scale(0.98);
        }

        .mapel-card:hover {
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            border-color: rgba(0,86,179,0.2);
        }

        .mapel-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .mapel-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(0,86,179,0.1);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .mapel-name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .mapel-arrow {
            color: #ced4da;
        }

        /* Logout Button */
        .logout-area {
            margin-top: auto;
            padding-top: 40px;
            text-align: center;
        }
        
        .btn-logout {
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 50px;
            background: rgba(220, 53, 69, 0.1);
        }
    </style>
</head>
<body>

<div class="container-pilih">
    <div class="header-section">
        <div class="welcome-text">Halo, <?= htmlspecialchars(explode(' ', $nama_siswa)[0]) ?>! 👋</div>
        <div class="sub-text">Kamu berada di Kelas <b><?= htmlspecialchars($kelas_siswa) ?></b>.<br>Pilih pelajaran untuk memulai.</div>
    </div>

    <div class="mapel-grid">
        <?php if (!empty($mapel_list)): ?>
            <?php foreach ($mapel_list as $mapel): ?>
                <!-- Form tersembunyi untuk kirim POST request -->
                <form action="../actions/set_mapel_siswa.php" method="POST" class="mapel-form">
                    <input type="hidden" name="mapel_id" value="<?= $mapel['id'] ?>">
                    
                    <div class="mapel-card" onclick="this.parentNode.submit()">
                        <div class="mapel-info">
                            <div class="mapel-icon">
                                <!-- Ikon Statis, nanti bisa didinamiskan dari DB jika ada kolom icon -->
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="mapel-name">
                                <?= htmlspecialchars($mapel['nama_mapel']) ?>
                            </div>
                        </div>
                        <div class="mapel-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center" style="color: #999; padding: 20px;">
                Belum ada mata pelajaran tersedia.
            </div>
        <?php endif; ?>
    </div>

    <div class="logout-area">
        <a href="../actions/logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Keluar Akun
        </a>
    </div>
</div>

</body>
</html>