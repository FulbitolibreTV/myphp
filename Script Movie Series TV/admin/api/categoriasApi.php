<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$archivo = __DIR__ . '/../../data/categorias.json';

if (file_exists($archivo)) {
    $categorias = json_decode(file_get_contents($archivo), true);
    echo json_encode($categorias, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['error' => 'Archivo de categorías no encontrado']);
}
