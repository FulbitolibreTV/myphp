<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$site_file = '../../data/site_info.json';

if (!file_exists($site_file)) {
    echo json_encode([
        "title" => "CorpSRTony Cine",
        "footer" => "© 2025 CorpSRTony Cine. Todos los derechos reservados."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo file_get_contents($site_file);
?>
