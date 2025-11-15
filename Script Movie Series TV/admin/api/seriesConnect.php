<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// Manejo de preflight requests (CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Obtener el ID (nombre del JSON)
$series_id = $_GET['id'] ?? null;

if (!$series_id) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'ID de serie requerido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Directorios
$base_dir = __DIR__ . '/../../data/';
$series_dir = $base_dir . 'series/';
$series_file = $series_dir . $series_id . '.json';
$site_config_file = $base_dir . 'site_config.json';
$site_info_file = $base_dir . 'site_info.json';
$views_file = $base_dir . 'views.json';

// Verificar si existe el JSON
if (!file_exists($series_file)) {
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'message' => 'Serie no encontrada'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Leer datos de la serie
$series_data = json_decode(file_get_contents($series_file), true);

if (!$series_data) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al leer datos de la serie'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Configuración del sitio
$site_config = file_exists($site_config_file) ? json_decode(file_get_contents($site_config_file), true) : [];
$site_info = file_exists($site_info_file) ? json_decode(file_get_contents($site_info_file), true) : [];

// Contador de vistas
$views = file_exists($views_file) ? json_decode(file_get_contents($views_file), true) : [];
$views[$series_id] = ($views[$series_id] ?? 0) + 1;
file_put_contents($views_file, json_encode($views, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Preparar trailer de YouTube
$trailer_url = null;
if (!empty($series_data['trailer'])) {
    $trailer_url = "https://www.youtube.com/embed/" . $series_data['trailer'];
}

// Respuesta JSON
$response = [
    'error' => false,
    'data' => [
        'serie' => [
            'id' => $series_id,
            'title' => $series_data['title'] ?? 'Sin título',
            'poster_path' => $series_data['poster_path'] ?? '',
            'backdrop_path' => $series_data['backdrop_path'] ?? '',
            'release_date' => $series_data['release_date'] ?? 'No disponible',
            'category' => $series_data['category'] ?? 'Sin categoría',
            'overview' => $series_data['overview'] ?? 'Sin descripción',
            'genres' => $series_data['genres'] ?? [],
            'seasons' => $series_data['seasons'] ?? [],
            'trailer' => $series_data['trailer'] ?? null,
            'trailer_url' => $trailer_url,
            'views' => $views[$series_id] ?? 1
        ],
        'site_config' => [
            'maintenance' => !empty($site_config['maintenance']),
            'site_name' => $site_info['site_name'] ?? 'CorpSRTony Cine',
            'favicon' => $site_info['favicon'] ?? 'favicon.ico'
        ]
    ]
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
