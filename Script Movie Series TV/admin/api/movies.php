<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$generadores_file = '../../data/generadores_config.json';
$generadores_config = file_exists($generadores_file) 
    ? json_decode(file_get_contents($generadores_file), true)
    : ['enabled' => true];

$generadores_enabled = $generadores_config['enabled'] ?? true;

if (!$generadores_enabled) {
    echo json_encode([
        "status" => "error",
        "message" => "🚫 Código desactivado. Comunícate con soporte."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$movies_file = '../../data/movies.json';

if (!file_exists($movies_file)) {
    echo json_encode([]);
    exit;
}

echo file_get_contents($movies_file);
?>
