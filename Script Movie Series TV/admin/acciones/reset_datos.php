<?php
require_once '../../config.php';

if (!check_session() || !is_super_admin()) {
    header('Location: ../login.php');
    exit;
}

$password_input = $_POST['confirm_password'] ?? '';
$current_user = $_SESSION['username'];

// Verificar que el usuario existe y cargar su contraseña
$users = load_users();

if (!isset($users[$current_user])) {
    header('Location: ../reset.php?error=Usuario inválido');
    exit;
}

$hashed_password = $users[$current_user]['password'] ?? '';

if (!verify_password($password_input, $hashed_password)) {
    header('Location: ../reset.php?error=Contraseña incorrecta');
    exit;
}

// Archivos que se eliminarán (solo películas y categorías)
$archivos = [
    '../../data/movies.json',
    '../../data/categories.json'
];

$errores = [];
foreach ($archivos as $file) {
    if (file_exists($file)) {
        if (!file_put_contents($file, json_encode([]))) {
            $errores[] = basename($file);
        }
    }
}

if (empty($errores)) {
    header('Location: ../reset.php?success=Películas y categorías eliminadas correctamente');
} else {
    $msg = 'Error al limpiar: ' . implode(', ', $errores);
    header('Location: ../reset.php?error=' . urlencode($msg));
}
exit;
