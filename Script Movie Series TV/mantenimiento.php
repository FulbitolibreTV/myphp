<?php
require_once 'config.php';

$config_file = 'data/site_config.json';
$config = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [];

$modo_mantenimiento = !empty($config['maintenance']);
$mensaje = $config['maintenance_message'] ?? 'Estamos en mantenimiento. Vuelve pronto.';
$imagen = $config['maintenance_image'] ?? 'imagenes/mantenimiento.gif';
$es_admin = check_session(); // detecta si es admin logueado

// Si el modo mantenimiento ya está desactivado y el usuario no es admin, redirige al index
if (!$modo_mantenimiento && !$es_admin) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Estamos en mantenimiento</title>
  <link rel="stylesheet" href="admin/css/index.css">
  <style>
    body {
      background-color: #0e0e0e;
      color: white;
      font-family: 'Inter', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 2rem;
      min-height: 100vh;
      flex-direction: column;
    }

    img {
      max-width: 320px;
      width: 100%;
      margin-bottom: 1rem;
      border-radius: 8px;
      box-shadow: 0 0 12px rgba(0,0,0,0.4);
    }

    .mensaje {
      font-size: 1.3rem;
      margin-bottom: 2rem;
      color: #ff6347;
    }

    .admin-alert {
      background: #ffcc00;
      color: black;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-weight: bold;
      margin-top: 2rem;
    }

    a.panel-link {
      color: #ffffff;
      background: #1a237e;
      padding: 0.7rem 1.2rem;
      text-decoration: none;
      font-weight: bold;
      border-radius: 6px;
      margin-top: 1.5rem;
      display: inline-block;
      transition: 0.3s;
    }

    a.panel-link:hover {
      background: #3949ab;
    }
  </style>
</head>
<body>

  <img src="<?= htmlspecialchars($imagen) ?>" alt="Mantenimiento">
  <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>

  <?php if ($es_admin): ?>
    <div class="admin-alert">🔒 Estás viendo esto como administrador.</div>
    <a href="admin/index.php" class="panel-link">Ir al panel de administración</a>
  <?php endif; ?>
<script src="js/protect.js"></script>
</body>
</html>
