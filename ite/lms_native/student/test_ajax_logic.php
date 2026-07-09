<?php
// Script to test partial rendering response
// Run this via CLI: php test_partial.php
// Or place in root and access via browser

// Helper function mock (since we might no be able to include the real one easily from CLI if paths depend on exact location)
function is_ajax_check() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
           (isset($_SERVER['HTTP_X_PARTIAL_RENDER']) && $_SERVER['HTTP_X_PARTIAL_RENDER'] == 'true');
}

echo "Testing Normal Request:\n";
$_SERVER['HTTP_X_PARTIAL_RENDER'] = 'false';
if (is_ajax_check()) {
    echo "FAIL: Detected as AJAX (False Positive)\n";
} else {
    echo "PASS: Detected as Normal\n";
}

echo "\nTesting AJAX Request (Header Mock):\n";
$_SERVER['HTTP_X_PARTIAL_RENDER'] = 'true';
if (is_ajax_check()) {
    echo "PASS: Detected as AJAX\n";
} else {
    echo "FAIL: Detected as Normal (False Negative)\n";
}
?>
