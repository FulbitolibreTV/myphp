<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// Manejo de preflight requests (CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Directorios
$base_dir = __DIR__ . '/../../data/';
$tv_file = $base_dir . 'tv_channels.json';
$site_config_file = $base_dir . 'site_config.json';
$site_info_file = $base_dir . 'site_info.json';

// Verificar si existe el archivo de canales de TV
if (!file_exists($tv_file)) {
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'message' => 'Archivo de canales de TV no encontrado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Leer datos de los canales
$tv_channels = json_decode(file_get_contents($tv_file), true);
if (!$tv_channels) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al leer datos de los canales de TV'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Configuración del sitio
$site_config = file_exists($site_config_file) ? json_decode(file_get_contents($site_config_file), true) : [];
$site_info = file_exists($site_info_file) ? json_decode(file_get_contents($site_info_file), true) : [];

// Obtener categorías únicas
$categories = [];
foreach ($tv_channels as $channel) {
    if (!empty($channel['category']) && !in_array($channel['category'], $categories)) {
        $categories[] = $channel['category'];
    }
}

// Estadísticas de canales
$total_channels = count($tv_channels);
$channels_by_category = [];
foreach ($categories as $cat) {
    $channels_by_category[$cat] = count(array_filter($tv_channels, function($ch) use ($cat) {
        return $ch['category'] === $cat;
    }));
}

// Preparar respuesta JSON
$response = [
    'error' => false,
    'data' => [
        'channels' => $tv_channels,
        'categories' => $categories,
        'stats' => [
            'total_channels' => $total_channels,
            'channels_by_category' => $channels_by_category
        ],
        'site_config' => [
            'maintenance' => !empty($site_config['maintenance']),
            'site_name' => $site_info['title'] ?? 'CorpSRTony TV',
            'favicon' => $site_info['favicon'] ?? 'assets/favicon.png',
            'footer' => $site_info['footer'] ?? '© 2025 CorpSRTony TV. Todos los derechos reservados.',
            'main_color' => $site_info['main_color'] ?? '#0e0e0e',
            'header_color' => $site_info['header_color'] ?? '#1a1a1a'
        ]
    ]
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>