<?php
require_once '../../config.php';

if (!check_session() || !is_super_admin()) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_zip'])) {
    $zip = $_FILES['backup_zip'];
    
    if ($zip['error'] !== 0 || pathinfo($zip['name'], PATHINFO_EXTENSION) !== 'zip') {
        die('❌ Error al subir el archivo. Asegúrese de que sea un archivo .zip válido.');
    }

    $tempZip = $zip['tmp_name'];
    $extractPath = '../../data/temp_restore';

    // Crear carpeta temporal si no existe
    if (!is_dir($extractPath)) {
        mkdir($extractPath, 0755, true);
    }

    $zipArchive = new ZipArchive;
    if ($zipArchive->open($tempZip) === TRUE) {
        $zipArchive->extractTo($extractPath);
        $zipArchive->close();

        // Restaurar archivos si existen
        $restaurados = 0;
        foreach (['movies.json', 'categories.json', 'usuarios.json'] as $file) {
            $tempFile = $extractPath . '/' . $file;
            if (file_exists($tempFile)) {
                copy($tempFile, '../../data/' . $file);
                $restaurados++;
            }
        }

        // Limpiar
        array_map('unlink', glob($extractPath . '/*.json'));
        rmdir($extractPath);

        echo "<script>alert('✅ Backup restaurado con éxito. Archivos restaurados: $restaurados'); window.location.href='../reset.php';</script>";
    } else {
        die('❌ No se pudo abrir el archivo ZIP.');
    }
}
?>
