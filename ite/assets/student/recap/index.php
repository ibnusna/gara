<?php

session_start();
require_once '../../config/database.php';
require_once '../../actions/auth_helper.php';


if (!isset($_SESSION['nis'])) {
    header("Location: ../../login.php");
    exit;
}


$today = time();
$current_year = date('Y', $today);


$schedule = [
    'Q1' => ['start' => "$current_year-03-28", 'end' => "$current_year-04-10", 'template' => 'template1.html'],
    'Q2' => ['start' => "$current_year-06-28", 'end' => "$current_year-07-10", 'template' => 'template2.html'],
    'Q3' => ['start' => "$current_year-09-28", 'end' => "$current_year-10-10", 'template' => 'template3.html'],
    'Q4' => ['start' => "$current_year-12-28", 'end' => ((int)$current_year+1)."-01-10", 'template' => 'template4.html']
];

$active_template = null;
if (isset($_GET['force_quarter'])) {
    $q = $_GET['force_quarter'];
    if (isset($schedule[$q])) {
        $active_template = $schedule[$q]['template'];
    }
} else {
    foreach ($schedule as $q => $data) {
        $s = strtotime($data['start']);
        $e = strtotime($data['end']);
        if ($today >= $s && $today <= $e) {
            $active_template = $data['template'];
            break;
        }
    }
}


if (!$active_template) {
    
    
    
    
    
    
    $active_template = 'template1.html'; 
    
    
    
    
}




readfile($active_template);
?>
