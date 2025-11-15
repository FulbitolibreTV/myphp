<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight de CORS (para evitar error en fetch desde apps externas)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$archivo = '../../data/reportes.json';

// Recibir datos del POST en formato JSON
$input = json_decode(file_get_contents('php://input'), true);

// Mapear datos correctamente según JS
$tipo = trim($input['tipo'] ?? '');
$pelicula = trim($input['linkCaido'] ?? ''); // Para Link caído
$nombrePublicidad = trim($input['nombrePub'] ?? ''); // Para Publicidad
$contactoPublicidad = trim($input['contactoPub'] ?? '');
$mensaje = trim($input['mensaje'] ?? '');

// Validación básica
if (empty($tipo) || strlen($mensaje) < 10) {
    echo json_encode([
        'success' => false,
        'error' => 'Debes indicar el tipo y un mensaje válido (mínimo 10 caracteres).'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar campos específicos según tipo
if($tipo === 'Link caído' && strlen($pelicula) < 2){
    echo json_encode([
        'success' => false,
        'error' => 'Debes seleccionar una película o serie válida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if($tipo === 'Publicidad' && (empty($nombrePublicidad) || empty($contactoPublicidad))){
    echo json_encode([
        'success' => false,
        'error' => 'Debes ingresar tu nombre y contacto para publicidad.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Cargar reportes previos
$reportes = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];
if (!is_array($reportes)) {
    $reportes = [];
}

// Crear nuevo reporte
$nuevo = [
    'id' => count($reportes) + 1,
    'fecha' => date('Y-m-d H:i:s'),
    'tipo' => $tipo,
    'pelicula' => $pelicula,
    'mensaje' => $mensaje,
    'nombrePublicidad' => $nombrePublicidad,
    'contactoPublicidad' => $contactoPublicidad,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
];

// Agregar y guardar
$reportes[] = $nuevo;
file_put_contents($archivo, json_encode($reportes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Respuesta de éxito
echo json_encode([
    'success' => true,
    'mensaje' => '✅ Reporte guardado con éxito.',
    'data' => $nuevo
], JSON_UNESCAPED_UNICODE);
exit;
