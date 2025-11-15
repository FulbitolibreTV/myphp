<?php
require_once '../config.php';

if (!check_session()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $file = "../data/series/$id.json";
    if (file_exists($file)) {
        unlink($file);
        header("Location: manage-series.php?deleted=1");
        exit;
    } else {
        echo "❌ Archivo no encontrado.";
    }
} else {
    echo "❌ ID inválido.";
}
?>
