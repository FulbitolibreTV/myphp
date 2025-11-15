<?php
require_once '../config.php';
if (!check_session()) { http_response_code(403); exit; }

$data = json_decode(file_get_contents("php://input"), true);
$index = intval($data['index'] ?? -1);
$newName = trim($data['newName'] ?? "");

$categories_file = '../data/categories.json';
$categories = file_exists($categories_file) ? json_decode(file_get_contents($categories_file), true) : [];

if ($index >= 0 && isset($categories[$index]) && $newName !== "") {
    $categories[$index]['name'] = $newName;
    file_put_contents($categories_file, json_encode($categories, JSON_PRETTY_PRINT));
    echo "Categoría editada exitosamente.";
} else {
    echo "Error al editar categoría.";
}
?>
