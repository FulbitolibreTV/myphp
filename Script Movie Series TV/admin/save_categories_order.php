<?php
require_once '../config.php';
if (!check_session()) { http_response_code(403); exit; }

$data = json_decode(file_get_contents("php://input"), true);
$order = $data['order'] ?? [];

$categories_file = '../data/categories.json';
$categories = [];

foreach ($order as $name) {
    $categories[] = ['name' => $name];
}

file_put_contents($categories_file, json_encode($categories, JSON_PRETTY_PRINT));
echo "Orden de categorías actualizado correctamente.";
?>
