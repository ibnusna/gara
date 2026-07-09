<?php




$today = time();
$current_year = date('Y', $today);







$recap_active = false;
$recap_url = "recap/";

$schedule = [
    'Q1' => ['start' => "$current_year-03-28", 'end' => "$current_year-04-10"],
    'Q2' => ['start' => "$current_year-06-28", 'end' => "$current_year-07-10"],
    'Q3' => ['start' => "$current_year-09-28", 'end' => "$current_year-10-10"],
    'Q4' => ['start' => "$current_year-12-28", 'end' => ((int)$current_year+1)."-01-10"]
];

foreach ($schedule as $q => $data) {
    $s = strtotime($data['start']);
    $e = strtotime($data['end']);
    if ($today >= $s && $today <= $e) {
        $recap_active = true;
        break;
    }
}

























if ($recap_active):
?>
<div class="animate-up module-item" style="margin-bottom: 24px; animation-delay: 0.1s;">
    <div style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border-radius: 16px; padding: 20px; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.5);">
        
        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -40px; left: 10%; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        
        <div style="position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.25rem; font-weight: 800;">GARA RECAP IS HERE! ⚡</h3>
                <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Lihat perjalanan epik belajarmu triwulan ini.</p>
            </div>
            <a href="recap/" class="btn-shine" style="background: white; color: #6366f1; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.9rem; transition: transform 0.2s; white-space: nowrap;">
                Lihat Sekarang <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <style>
        .btn-shine:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</div>
<?php endif; ?>
