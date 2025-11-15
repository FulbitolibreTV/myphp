<?php
header('Content-Type: application/json');

$file = '../data/reportes.json';
$last_file = '../data/last_check.json';

// Leer reportes actuales
$reportes = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
$total = count($reportes);

// Leer último total guardado
$last_count = 0;
if (file_exists($last_file)) {
    $last_count = json_decode(file_get_contents($last_file), true)['total'] ?? 0;
}

// Determinar si hay nuevos
$nuevos = $total > $last_count;

// Si hay nuevos, actualizar el archivo last_check
if ($nuevos) {
    file_put_contents($last_file, json_encode(['total' => $total]));
}

echo json_encode([
    "nuevos" => $nuevos,
    "total" => $total
]);
?>
