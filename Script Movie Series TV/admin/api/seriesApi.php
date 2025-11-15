<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$series_dir = __DIR__ . '/../../data/series/';
$series = [];

foreach (glob($series_dir . '*.json') as $file) {
    $data = json_decode(file_get_contents($file), true);
    if ($data && isset($data['title'])) {
        $series[] = [
            'id' => basename($file, '.json'),
            'title' => $data['title'],
            'poster_path' => $data['poster_path'] ?? '',
            'category' => $data['category'] ?? 'Sin categoría'
        ];
    }
}

echo json_encode($series, JSON_UNESCAPED_UNICODE);
