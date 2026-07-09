<?php
// GARA - Garuda Akademi
// File: components/avatar_helper.php
// Fungsi untuk membuat avatar UI yang konsisten dan modern

function getAvatar($nama, $size = 128) {
    $nama_clean = urlencode($nama);
    // Palet warna modern (Flat UI)
    $colors = ['0d6efd', '6610f2', '6f42c1', 'd63384', 'dc3545', 'fd7e14', '198754', '20c997', '0dcaf0', '343a40'];
    $bg_color = $colors[array_rand($colors)]; // Pilih satu warna acak dari palet
    
    // URL UI Avatars
    return "https://ui-avatars.com/api/?name={$nama_clean}&background={$bg_color}&color=fff&size={$size}&bold=true&font-size=0.35&rounded=true";
}
?>