<?php
require_once '../config.php';

if (!check_session()) {
    header('Location: login.php');
    exit;
}

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

$current_data = $users[$current_user] ?? [
    'name' => $current_user,
    'profile_image' => 'perfil.png',
    'role' => 'editor'
];

$current_name = $_SESSION['name'] ?? $current_user;
$current_image = $current_data['profile_image'];

$is_admin = in_array($current_data['role'], ['admin', 'super_admin']);
$is_super_admin = $current_data['role'] === 'super_admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin - CorpSRTony</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      background: #f4f6fc;
      display: flex;
    }
    .sidebar {
      position: fixed; top: 0; left: 0;
      width: 250px; height: 100vh;
      background: #1a237e; color: white;
      overflow-y: auto;
      transition: transform 0.3s ease;
      z-index: 1000;
    }
    .sidebar.hide { transform: translateX(-100%); }
    .sidebar h2 {
      text-align: center; padding: 1rem;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      margin: 0;
    }
    .sidebar a {
      display: block; padding: 1rem 1.5rem;
      color: white; text-decoration: none;
      transition: background 0.3s;
    }
    .sidebar a:hover { background: rgba(255,255,255,0.1); }
    .main {
      flex: 1; margin-left: 250px;
      transition: margin-left 0.3s ease;
      padding: 1rem 2rem;
    }
    .main.full { margin-left: 0; }
    .toggle-btn {
      position: fixed; top: 15px; left: 15px;
      font-size: 1.5rem; color: #1a237e; cursor: pointer;
      z-index: 1100;
    }
    .header {
      background-color: #1a237e; color: white;
      padding: 1rem 2rem;
      display: flex; justify-content: space-between; align-items: center;
      border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      min-height: 60px;
    }
    .header a {
      display: flex; align-items: center; gap: 10px;
      color: white; text-decoration: none;
    }
    .profile-icon {
      width: 40px; height: 40px; border-radius: 50%;
      background: url('../assets/<?= htmlspecialchars($current_image) ?>') center/cover no-repeat;
      border: 2px solid #fff;
    }
  </style>
</head>
<body>

<div class="toggle-btn" onclick="toggleSidebar()">
  <i class="fas fa-bars"></i>
</div>

<div class="sidebar" id="sidebar">
  <h2>CorpSRTony</h2>
  <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage-categories.php"><i class="fas fa-layer-group"></i> Categorías</a>
  <a href="manage-movies.php"><i class="fas fa-film"></i> Películas</a>
  <a href="create-movie.php"><i class="fas fa-video"></i> Crear Película</a>
  <?php if ($is_admin): ?>
    <a href="config_home.php"><i class="fas fa-home"></i> Config Home</a>
    <a href="config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>
  <?php endif; ?>
  <?php if ($is_super_admin): ?>
    <a href="soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
    <a href="configure-api.php"><i class="fas fa-key"></i> API TMDB</a>
    <a href="monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
    <a href="telegram_publicar.php"><i class="fas fa-paper-plane"></i> Publicar</a>
    <hr style="border:0;border-top:1px solid rgba(255,255,255,0.2);">
    <a href="#"><i class="fas fa-code-branch"></i> Versiones</a>
    <a href="#"><i class="fas fa-user-cog"></i> Soporte Dev</a>
    <a href="#"><i class="fas fa-paint-brush"></i> Temas</a>
  <?php endif; ?>
  <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main" id="main">
  <div class="header">
    <h1><i class="fas fa-film"></i> Administrador</h1>
    <a href="perfil.php">
      <span><?= htmlspecialchars($current_name); ?></span>
      <div class="profile-icon"></div>
    </a>
  </div>

<script>
function toggleSidebar(){
  var sidebar = document.getElementById('sidebar');
  var main = document.getElementById('main');
  sidebar.classList.toggle('hide');
  main.classList.toggle('full');
}
</script>