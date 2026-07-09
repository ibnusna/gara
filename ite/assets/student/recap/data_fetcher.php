<?php

session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../actions/auth_helper.php'; 


if (!isset($_SESSION['nis'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$nis = $_SESSION['nis'];
$student_id = $_SESSION['siswa_id'] ?? 0;

if (!$student_id) {
    $stmt = $pdoAuth->prepare("SELECT id, nama_lengkap FROM siswa WHERE nis = ?");
    
    
    
    
    
    
    
    
    
    
    $stmt = $pdoAuth->prepare("SELECT id, nama FROM siswa WHERE nis = ?");
    $stmt->execute([$nis]);
    $user = $stmt->fetch();
    if ($user) {
        $student_id = $user['id'];
        $student_name = $user['nama_lengkap'];
    } else {
        echo json_encode(['error' => 'User not found']);
        exit;
    }
} else {
    $student_name = $_SESSION['nama'] ?? 'Siswa'; 
}


$today = time(); 



$current_year = date('Y', $today);
$quarters = [
    'Q1' => ['start' => "$current_year-01-01", 'end' => "$current_year-03-31", 'show_start' => "$current_year-03-28", 'show_end' => "$current_year-04-10"],
    'Q2' => ['start' => "$current_year-04-01", 'end' => "$current_year-06-30", 'show_start' => "$current_year-06-28", 'show_end' => "$current_year-07-10"],
    'Q3' => ['start' => "$current_year-07-01", 'end' => "$current_year-09-30", 'show_start' => "$current_year-09-28", 'show_end' => "$current_year-10-10"],
    'Q4' => ['start' => "$current_year-10-01", 'end' => "$current_year-12-31", 'show_start' => "$current_year-12-28", 'show_end' => "$current_year-01-10"] 
];


$active_quarter = null;
$period_label = "";
$q_start = "";
$q_end = "";


foreach ($quarters as $q => $dates) {
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    if (isset($_GET['force_quarter'])) {
        $active_quarter = $_GET['force_quarter'];
        $q_start = $quarters[$active_quarter]['start'];
        $q_end = $quarters[$active_quarter]['end'];
        $period_label = "Triwulan " . substr($active_quarter, 1) . " $current_year";
        break;
    }
}


if (!$active_quarter) {
    
    
    
    
    $active_quarter = 'Q1';
    $q_start = $quarters['Q1']['start'];
    $q_end = $quarters['Q1']['end'];
    $period_label = "Triwulan I $current_year";
}




$stmt = $pdo->prepare("SELECT tanggal FROM rekap_harian WHERE siswa_id = ? AND tanggal BETWEEN ? AND ? ORDER BY tanggal ASC");
$stmt->execute([$student_id, $q_start, $q_end]);
$dates_active = $stmt->fetchAll(PDO::FETCH_COLUMN);

$total_days = count($dates_active);


$max_streak = 0;
$current_streak = 0;
$last_date = null;

foreach ($dates_active as $date_str) {
    if (!$last_date) {
        $current_streak = 1;
    } else {
        $diff = (strtotime($date_str) - strtotime($last_date)) / (60 * 60 * 24);
        if ($diff == 1) {
            $current_streak++;
        } else {
            if ($current_streak > $max_streak) $max_streak = $current_streak;
            $current_streak = 1;
        }
    }
    $last_date = $date_str;
}
if ($current_streak > $max_streak) $max_streak = $current_streak;


$stmt = $pdo->prepare("SELECT SUM(jumlah_aksi) FROM rekap_harian WHERE siswa_id = ? AND tanggal BETWEEN ? AND ?");
$stmt->execute([$student_id, $q_start, $q_end]);
$total_aksi = $stmt->fetchColumn() ?: 0;
$total_minutes_calc = $total_aksi * 8;
$hours = floor($total_minutes_calc / 60);
$minutes = $total_minutes_calc % 60;





$tugas_submit = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tugas_pengumpulan WHERE siswa_id = ? AND created_at BETWEEN ? AND ?"); 
    
    
    $stmt->execute([$student_id, "$q_start 00:00:00", "$q_end 23:59:59"]);
    $tugas_submit = $stmt->fetchColumn();
} catch (Exception $e) {
    $tugas_submit = 0; 
}



$csv_url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vRJHk7Z_Xn9ZZQsU3SwQgH8GqfkH08VdvTUuwFZnUvPJ9u_BVFZRxb_4z6CmTpqGHsqDQWPWAC8dBDY/pub?gid=115463958&single=true&output=csv";

function fetch_csv_data($url) {
    $ctx = stream_context_create(array('http'=> array('timeout' => 10)));
    $data = @file($url, false, $ctx);
    if (!$data) return [];
    return array_map('str_getcsv', $data);
}

$sheet_data = fetch_csv_data($csv_url);





$my_rows = [];
$class_scores = []; 
$my_class = $_SESSION['nama_kelas'] ?? ''; 


$exam_count = 0;
$mapel_counts = [];
$my_scores = [];

foreach ($sheet_data as $index => $row) {
    if ($index == 0) continue; 
    if (count($row) < 8) continue;
    
    $row_nis = trim($row[3]);
    $row_timestamp = $row[0]; 
    $row_class = trim($row[2]);
    $score = floatval($row[7]);
    
    
    $ts = strtotime($row_timestamp);
    if ($ts < strtotime($q_start) || $ts > strtotime($q_end . ' 23:59:59')) {
        
        continue;
    }
    
    
    if ($row_class == $my_class) {
        $class_scores[] = $score;
    }
    
    
    if ($row_nis == $nis) {
        $exam_count++;
        $my_scores[] = $score;
        
        
        
        
        $sheet_name = $row[4];
        $parts = explode('_', $sheet_name);
        $mapel = $parts[0]; 
        if (isset($mapel_counts[$mapel])) {
            $mapel_counts[$mapel]++;
        } else {
            $mapel_counts[$mapel] = 1;
        }
    }
}



$top_feature = "Tidak Ada";
$feature_percentage = 0;
if (!empty($mapel_counts)) {
    arsort($mapel_counts);
    $top_feature = array_key_first($mapel_counts);
    $total_taken = array_sum($mapel_counts);
    $feature_percentage = round(($mapel_counts[$top_feature] / $total_taken) * 100);
}


$class_avg = !empty($class_scores) ? array_sum($class_scores) / count($class_scores) : 0;
$my_avg = !empty($my_scores) ? array_sum($my_scores) / count($my_scores) : 0;

$persona_type = "The Curious Mind";
$persona_desc = "Rasa ingin tahumu adalah kekuatanmu.";
$persona_icon = "fa-brain";

if ($my_avg > ($class_avg + 15)) {
    $persona_type = "The Class Carry";
    $persona_desc = "Nilaimu di atas rata-rata! Kamu menggendong performa kelas.";
    $persona_icon = "fa-crown";
} elseif ($my_avg > $class_avg && $total_aksi < 50) { 
    $persona_type = "The Ghost Genius";
    $persona_desc = "Jarang terlihat online, tapi nilainya selalu bagus. Magic!";
    $persona_icon = "fa-ghost";
} elseif ($hours > 20) { 
    $persona_type = "The High Flyer";
    $persona_desc = "Jam terbangmu tinggi. Dedikasi tanpa batas.";
    $persona_icon = "fa-plane-departure";
}




$best_day_date = "-";
$best_day_count = 0;
$stmt = $pdo->prepare("SELECT tanggal, jumlah_aksi FROM rekap_harian WHERE siswa_id = ? AND tanggal BETWEEN ? AND ? ORDER BY jumlah_aksi DESC LIMIT 1");
$stmt->execute([$student_id, $q_start, $q_end]);
$best = $stmt->fetch();
if ($best) {
    
    $best_day_date = date("d F", strtotime($best['tanggal']));
    $best_day_count = $best['jumlah_aksi'];
}



$trend = "stable";
if (count($my_scores) >= 2) {
    $first = $my_scores[0];
    $last = end($my_scores);
    if ($last > $first) $trend = "up";
    elseif ($last < $first) $trend = "down";
}


$data = [
    'student_name' => explode(' ', trim($student_name))[0], 
    'period' => $period_label,
    'stats' => [
        'total_days' => $total_days,
        'streak' => $max_streak,
        'total_hours' => $hours,
        'total_minutes' => $minutes,
        'top_feature' => $top_feature,
        'feature_percentage' => $feature_percentage,
        'exams_count' => $exam_count,
        'assignments_count' => $tugas_submit,
        'avg_score_trend' => $trend,
        'best_day_date' => $best_day_date,
        'best_day_count' => $best_day_count
    ],
    'persona' => [
        'type' => $persona_type,
        'desc' => $persona_desc,
        'icon' => $persona_icon
    ]
];

echo json_encode($data);
?>
