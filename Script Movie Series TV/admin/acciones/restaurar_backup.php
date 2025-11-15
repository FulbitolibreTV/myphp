<?php
require_once '../../config.php';

if (!check_session() || !is_super_admin()) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
    $file = $_FILES['backup_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $zipPath = $file['tmp_name'];

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo('../../data'); // Extrae a la carpeta "data"
            $zip->close();
            header('Location: ../reset.php?success=Respaldo restaurado correctamente');
            exit;
        } else {
            header('Location: ../reset.php?error=No se pudo abrir el archivo ZIP');
            exit;
        }
    } else {
        header('Location: ../reset.php?error=Error al subir el archivo');
        exit;
    }
} else {
    header('Location: ../reset.php?error=No se recibió archivo válido');
    exit;
}
