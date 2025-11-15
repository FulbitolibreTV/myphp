<?php
require_once '../config.php';

$error = '';
$users_file = '../data/usuarios.json';

// Leer usuarios desde JSON
$admin_users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Por favor ingrese usuario y contraseña';
    } elseif (!isset($admin_users[$username])) {
        $error = 'Usuario no encontrado';
    } else {
        // Validar contraseña hasheada
        $user_data = $admin_users[$username];
        if (verify_password($password, $user_data['password'])) {
            // ✅ Aquí pasamos los datos para la sesión
            login_user($username, $user_data['name'] ?? $username);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Contraseña incorrecta';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Administrativo - CorpSRTony</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #1a237e 0%, #3f51b5 100%);
        display: flex; align-items: center; justify-content: center;
        min-height: 100vh; margin: 0;
    }

    .login-box {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 420px;
        padding: 2.5rem 2rem;
        text-align: center;
    }

    .login-box img {
        width: 90px; height: 90px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 1rem;
        border: 4px solid #1a237e;
    }

    h2 { color: #1a237e; margin-bottom: 1rem; }

    .form-group {
        text-align: left;
        margin-bottom: 1.2rem;
    }

    label { font-weight: bold; color: #333; margin-bottom: 0.3rem; display: block; }

    input {
        width: 100%;
        padding: 0.75rem;
        border-radius: 8px;
        border: 1.5px solid #ccc;
        font-size: 1rem;
    }

    input:focus {
        border-color: #1a237e;
        outline: none;
    }

    .btn {
        background-color: #1a237e;
        color: white;
        border: none;
        padding: 0.8rem;
        width: 100%;
        font-weight: bold;
        font-size: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        cursor: pointer;
    }

    .btn:hover {
        background-color: #3f51b5;
    }

    .error {
        background: #ffebee;
        color: #c62828;
        padding: 0.8rem;
        border-radius: 6px;
        margin-bottom: 1rem;
        border-left: 4px solid #c62828;
    }

    .back-link {
        margin-top: 1rem;
        display: inline-block;
        font-size: 0.9rem;
        color: #555;
        text-decoration: none;
    }

    .back-link:hover {
        color: #1a237e;
    }
  </style>
</head>
<body>

<div class="login-box">
    <img src="../assets/perfil.png" alt="Perfil Default">
    <h2>Acceso Administrativo</h2>

    <?php if ($error): ?>
        <div class="error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Usuario:</label>
            <input type="text" name="username" id="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit" class="btn"><i class="fas fa-lock"></i> Iniciar Sesión</button>
		
    </form>

    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver al sitio</a>
</div>
<script src="../js/protect.js"></script>
</body>
</html>
