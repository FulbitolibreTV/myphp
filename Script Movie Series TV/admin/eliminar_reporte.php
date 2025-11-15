<?php
require_once 'config.php';
if (!check_session() || !is_super_admin()) {
    header('Location: login.php');
    exit;
}

$index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
$file = 'data/reportes.json';

if ($index >= 0 && file_exists($file)) {
    $reportes = json_decode(file_get_contents($file), true);
    array_splice($reportes, $index, 1);
    file_put_contents($file, json_encode($reportes, JSON_PRETTY_PRINT));
}

header('Location: ver_reportes.php');
exit;
