<?php






?>

<div class="widget-card h-100">
    
    <div class="widget-header">
        <div class="widget-title">
            <i class="fas fa-mosque text-primary"></i> Jadwal Sholat
        </div>
        <div class="widget-action">
            <small class="text-muted" id="sholat-date"><?= date('d M Y') ?></small>
        </div>
    </div>

    
    <div class="sholat-container">
        
        <div id="sholat-location" class="sholat-location">
            <i class="fas fa-map-marker-alt me-1"></i> Mendeteksi Lokasi...
        </div>

        
        <div class="sholat-times-grid" id="sholat-times-grid">
            
            <div class="time-box">
                <span class="time-name">Subuh</span>
                <span class="time-value" id="time-subuh">--:--</span>
            </div>
            
            <div class="time-box">
                <span class="time-name">Dzuhur</span>
                <span class="time-value" id="time-dzuhur">--:--</span>
            </div>
            
            <div class="time-box">
                <span class="time-name">Ashar</span>
                <span class="time-value" id="time-ashar">--:--</span>
            </div>
            
            <div class="time-box">
                <span class="time-name">Maghrib</span>
                <span class="time-value" id="time-maghrib">--:--</span>
            </div>
            
            <div class="time-box">
                <span class="time-name">Isya</span>
                <span class="time-value" id="time-isya">--:--</span>
            </div>
        </div>
    </div>
</div>