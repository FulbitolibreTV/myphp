<?php
require_once 'config.php';

// Función para cargar datos JSON
function load_json_data($file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

// Función para guardar datos JSON
function save_json_data($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $json) !== false;
}

// Función para sanitizar entrada
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Función para validar URL
function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Función para generar ID único
function generate_id() {
    return uniqid('item_', true);
}


?>
