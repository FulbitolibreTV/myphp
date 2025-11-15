<?php
require_once '../../config.php';

if (!check_session() || !is_super_admin()) {
    header('Location: ../login.php');
    exit;
}

// Archivo correcto de las vistas
$views_file = '../../data/views.json';

// Vaciarlo
if (file_exists($views_file)) {
    file_put_contents($views_file, json_encode([]));
}

header('Location: ../reset.php?success=Las analíticas (vistas) fueron reiniciadas correctamente');
exit;
?>
