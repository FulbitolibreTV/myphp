<?php
header('Content-Type: application/json');

// Ruta donde se almacenarán los reportes
$archivo = 'data/reportes.json';

// Leer cuerpo JSON
$input = json_decode(file_get_contents('php://input'), true);

$tipo = trim($input['tipo'] ?? '');
$pelicula = trim($input['pelicula'] ?? '');
$mensaje = trim($input['mensaje'] ?? '');
$nombrePublicidad = trim($input['nombrePublicidad'] ?? '');
$contactoPublicidad = trim($input['contactoPublicidad'] ?? '');

// Validaciones básicas
if (strlen($mensaje) < 10 || $tipo == '') {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Cargar reportes existentes
$reportes = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];

// Crear nuevo reporte
$id = count($reportes) + 1;

$reportes[] = [
    'id' => $id,
    'fecha' => date('Y-m-d H:i:s'),
    'tipo' => $tipo,
    'pelicula' => $pelicula,
    'mensaje' => $mensaje,
    'nombrePublicidad' => $nombrePublicidad,
    'contactoPublicidad' => $contactoPublicidad
];

// Guardar archivo
file_put_contents($archivo, json_encode($reportes, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
exit;
?>
