<?php
/**
 * GARA MODULE: WIDGET TRIVIA (ASAH OTAK)
 * --------------------------------------
 * Container HTML untuk menampilkan kuis sederhana.
 * Data soal & logika jawaban dihandle oleh JS (dashboard_v2.js).
 */
?>

<div class="widget-card h-100">
    <!-- Header Widget -->
    <div class="widget-header">
        <div class="widget-title">
            <i class="fas fa-brain text-warning"></i> Brain Warmup
        </div>
        <div class="widget-action">
            <span class="badge bg-light text-dark border">Science & Comp</span>
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="trivia-container">
        <!-- Area Soal -->
        <div id="trivia-question" class="trivia-question">
            <!-- Spinner Loading Default -->
<!-- Skeleton State DEFAULT -->
            <div class="skeleton sk-text sk-text-lg mb-2"></div>
            <div class="skeleton sk-text sk-text-sm mb-4" style="width: 60%"></div>
            
            <!-- Skeleton Options -->
            <div class="d-grid gap-2">
                <div class="skeleton sk-btn"></div>
                <div class="skeleton sk-btn"></div>
                <div class="skeleton sk-btn"></div>
                <div class="skeleton sk-btn"></div>
            </div>
        </div>

        <!-- Area Tombol Jawaban -->
        <div id="trivia-options" class="trivia-options">
            <!-- Tombol akan di-generate oleh Javascript -->
        </div>
    </div>
</div>