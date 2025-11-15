<?php
require_once '../config.php';

if (!check_session() || !is_super_admin()) {
    header('Location: login.php');
    exit;
}

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

$current_user = $_SESSION['username'];
$current_name = $_SESSION['name'] ?? $current_user;
$current_image = '../assets/perfil.png';

$users = load_users();
if (isset($users[$current_user]['profile_image'])) {
    $current_image = '../assets/' . $users[$current_user]['profile_image'];
}

$is_admin = is_admin();
$is_super_admin = is_super_admin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Reset Total | CorpSRTony</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f5f5f5;
      margin: 0;
      display: flex;
      min-height: 100vh;
    }
    .sidebar {
      width: 250px;
      background: #1a237e;
      color: white;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      overflow-y: auto;
      padding: 1.5rem 1rem;
      transition: transform 0.3s ease;
      z-index: 1000;
    }
    .sidebar h1 {
      font-size: 1.4rem;
      margin-bottom: 1.2rem;
      text-align: center;
    }
    .sidebar .section-title {
      font-size: 0.8rem;
      text-transform: uppercase;
      opacity: 0.7;
      margin: 1rem 0 0.5rem 0;
      padding-left: 1rem;
    }
    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      color: white;
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      margin-bottom: 0.3rem;
      font-size: 0.95rem;
    }
    .sidebar a:hover {
      background: rgba(255,255,255,0.2);
    }
    .hamburger {
      position: fixed;
      top: 1rem;
      left: 1rem;
      font-size: 1.5rem;
      background: #1a237e;
      color: white;
      border: none;
      padding: 0.6rem;
      border-radius: 6px;
      z-index: 1100;
      cursor: pointer;
      display: none;
    }
    .main-content {
      flex: 1;
      margin-left: 250px;
      padding: 2rem;
    }
    @media (max-width: 768px) {
      .hamburger {
        display: block;
      }
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.active {
        transform: translateX(0);
      }
      .main-content {
        margin-left: 0;
        padding-top: 4rem;
      }
    }
    .container {
      max-width: 700px;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      margin: auto;
    }
    h2 {
      margin-top: 0;
      color: #1a237e;
    }
    .btn {
      padding: 0.7rem 1.2rem;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }
    .btn-reset {
      background: #000;
      color: #fff;
    }
    .btn-reset:hover {
      background: #333;
    }
    .btn-backup {
      background: #1a237e;
      color: white;
    }
    .btn-backup:hover {
      background: #3949ab;
    }
    .input-group {
      margin: 1.5rem 0;
    }
    .input-group label {
      display: block;
      margin-bottom: 0.4rem;
      font-weight: 600;
    }
    .input-group input[type="password"],
    .input-group input[type="file"] {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 1rem;
    }
    .message {
      margin-bottom: 1rem;
      padding: 0.8rem;
      border-radius: 6px;
    }
    .success {
      background: #e8f5e9;
      color: #2e7d32;
      border-left: 4px solid #2e7d32;
    }
    .error {
      background: #ffebee;
      color: #c62828;
      border-left: 4px solid #c62828;
    }
    .section {
      margin-bottom: 2rem;
    }
    .actions {
      display: flex;
      gap: 1.5rem;
      flex-wrap: wrap;
      align-items: center;
    }
    .upload-group {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 1rem;
      width: 100%;
    }
    @media (max-width: 600px) {
      .actions {
        flex-direction: column;
        align-items: stretch;
      }
      .upload-group {
        flex-direction: column;
        align-items: stretch;
      }
    }
  </style>
</head>
<body>

<button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

<div class="sidebar" id="sidebar">
  <h1>Admin panel</h1>
  <div class="section-title">Menú Principal</div>
  <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage-series.php"><i class="fas fa-tv"></i> Series</a>
  <a href="create-movie.php"><i class="fas fa-video"></i> Crear Película</a>
  <a href="manage-categories.php"><i class="fas fa-layer-group"></i> Categorías</a>
          <a href="detv.php"><i class="fas fa-play-circle"></i> TV</a>

  <?php if ($is_admin): ?>
    <div class="section-title">⚙️ Configuración</div>
    <a href="config_home.php"><i class="fas fa-home"></i> Config Home</a>
    <a href="config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>
  <?php endif; ?>

  <?php if ($is_super_admin): ?>
    <div class="section-title">🔧 Admin Tools</div>
    <a href="soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
    <a href="configure-api.php"><i class="fas fa-key"></i> TMDB API</a>
    <a href="api/generadores.php"><i class="fas fa-cogs"></i> App Creator 24 📲</a>
    <a href="monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
    <a href="telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <?php endif; ?>

  <div class="section-title">Usuario</div>
  <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
  <div class="container">
    <?php if ($success): ?>
      <div class="message success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
      <div class="message error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="section">
      <h2>🔐 Confirmar Eliminación</h2>
      <form action="acciones/reset_datos.php" method="POST">
        <div class="input-group">
          <label for="password">Confirma tu contraseña:</label>
          <input type="password" name="confirm_password" id="password" required>
        </div>
        <button type="submit" class="btn btn-reset"><i class="fas fa-trash"></i> Borrar Películas y Categorías</button>
      </form>
    </div>

    <div class="section">
      <h2>📦 Respaldo</h2>
      <div class="actions">
        <form action="acciones/crear_backup.php" method="POST">
          <button type="submit" class="btn btn-backup"><i class="fas fa-download"></i> Generar Respaldo ZIP</button>
        </form>

        <form action="acciones/restaurar_backup.php" method="POST" enctype="multipart/form-data" class="upload-group">
          <input type="file" name="backup_file" accept=".zip" required>
          <button type="submit" class="btn btn-backup"><i class="fas fa-upload"></i> Subir Respaldo</button>
        </form>
      </div>
    </div>

<div class="section">
  <h2>📉 Reiniciar Vistas</h2>
  <form action="acciones/reset_views.php" method="POST" onsubmit="return confirm('¿Seguro que deseas borrar las vistas?')">
    <button type="submit" class="btn btn-reset"><i class="fas fa-chart-line"></i> Borrar Vistas</button>
  </form>
</div>

  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('active');
}
</script>
<script src="../js/protect.js"></script>

</body>
</html>
