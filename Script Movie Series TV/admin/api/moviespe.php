<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$movies_file = '../../data/movies.json';

if (!file_exists($movies_file)) {
    echo json_encode(["status" => "error", "message" => "Archivo de películas no encontrado"]);
    exit;
}

$movies_data = json_decode(file_get_contents($movies_file), true);

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    if (isset($movies_data[$id])) {
        $movie = $movies_data[$id];
        $movie['id'] = $id;  // 🪄 añadimos el id sin tocar el archivo json
        echo json_encode($movie, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "error", "message" => "Película no encontrada"]);
    }
    exit;
}

// Si pidieron todas
$all_movies = [];
foreach ($movies_data as $key => $value) {
    $value['id'] = $key;
    $all_movies[] = $value;
}
echo json_encode($all_movies, JSON_UNESCAPED_UNICODE);
