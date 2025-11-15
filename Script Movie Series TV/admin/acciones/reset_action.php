<?php
require_once '../../config.php';

if (!check_session() || !is_super_admin()) {
    header('Location: ../login.php');
    exit;
}

$password = trim($_POST['password'] ?? '');
$users = load_users();
$current_user = $_SESSION['username'];
$user_data = $users[$current_user] ?? null;

// Validación
if (!$user_data || !verify_password($password, $user_data['password'])) {
    header('Location: ../reset.php?error=Contraseña incorrecta');
    exit;
}

// Archivos a borrar
$files = [
    '../../data/movies.json',
    '../../data/categories.json',
    '../../data/usuarios.json'
];

// Limpiar archivos
foreach ($files as $file) {
    if (file_exists($file)) {
        file_put_contents($file, '[]'); // Vaciar con un array vacío
    }
}

header('Location: ../reset.php?success=Se ha eliminado todo correctamente');
exit;
