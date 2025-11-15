<?php
require_once '../../config.php';

if (!check_session() || !is_super_admin()) {
    header('Location: ../login.php');
    exit;
}

// Archivos a incluir
$archivos = [
    '../../data/movies.json',
    '../../data/categories.json',
    '../../data/usuarios.json'
];

// Crear nombre único de backup
$fecha = date('Y-m-d_H-i-s');
$zip_name = "backup_$fecha.zip";
$zip_path = "../../data/$zip_name";

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
    foreach ($archivos as $archivo) {
        if (file_exists($archivo)) {
            $zip->addFile($archivo, basename($archivo));
        }
    }
    $zip->close();

    // Descargar directamente
    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename=$zip_name");
    header('Content-Length: ' . filesize($zip_path));
    readfile($zip_path);
    unlink($zip_path); // borrar luego de descargar
    exit;
} else {
    die('Error al crear el archivo ZIP.');
}
