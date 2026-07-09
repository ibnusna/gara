<?php
$host = 'sql311.infinityfree.com';
$dbname = 'if0_40676339_student'; // Sesuai nama database di siswa.sql
$user = 'if0_40676339';
$pass = '0RNvDPxhA8q'; // Default XAMPP/Laragon biasanya kosong
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Opsi PDO untuk keamanan dan kemudahan debugging
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Error akan melempar Exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Hasil query otomatis jadi array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                // Menggunakan native prepared statements (lebih aman dari SQL Injection)
];

try {
    // Membuat koneksi
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {

    $pdo->exec("SET SESSION sql_mode = ''");
    // Jika koneksi gagal, hentikan proses dan tampilkan pesan error
    // Dalam production, sebaiknya error ini dicatat di log, bukan ditampilkan ke user
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>