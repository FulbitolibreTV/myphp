<?php
require_once '../config.php';
if (!check_session() || !is_super_admin()) { header('Location: login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$current_name = $_SESSION['name'] ?? $current_user;
$current_image = $current_data['profile_image'];

$config_path = '../data/telegram_config.json';
$mensaje = '';

$config_data = file_exists($config_path)
    ? json_decode(file_get_contents($config_path), true)
    : [];

$bot_token = $config_data['bot_token'] ?? '';
$chat_id = $config_data['chat_id'] ?? '';
$base_url = $config_data['base_url'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bot_token = trim($_POST['bot_token']);
    $chat_id = trim($_POST['chat_id']);
    $base_url = trim($_POST['base_url']);

    if ($bot_token && $chat_id && $base_url) {
        $data = [
            'bot_token' => $bot_token,
            'chat_id' => $chat_id,
            'base_url' => $base_url
        ];
        file_put_contents($config_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $mensaje = '✅ Configuración guardada correctamente.';
    } else {
        $mensaje = '❌ Todos los campos son obligatorios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Configurar Telegram | CorpSRTony</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'Inter', sans-serif; background: #f4f6fc; display:flex; min-height:100vh; }
.sidebar {
  width: 250px; background: #1a237e; color: white; height: 100vh;
  position: fixed; left:0; top:0; overflow-y:auto;
  transition: transform 0.3s ease; z-index: 1000; padding: 1.5rem 1rem;
}
.sidebar h1 { font-size:1.4rem; margin-bottom:1.2rem; text-align:center; }
.sidebar .section-title { font-size:0.8rem; text-transform:uppercase; opacity:0.7; margin:1rem 0 0.5rem 0; padding-left:1rem; }
.sidebar a {
  display:flex; align-items:center; gap:10px;
  color:white; text-decoration:none;
  padding:0.5rem 1rem; border-radius:6px; margin-bottom:0.3rem; font-size:0.95rem;
}
.sidebar a:hover { background: rgba(255,255,255,0.2); }

.hamburger {
  position: fixed;
  top: 1rem;
  left: 1rem;
  width: 44px;
  height: 44px;
  background: #1a237e;
  color: white;
  border: none;
  border-radius: 6px;
  z-index: 1100;
  cursor: pointer;
  font-size: 1.5rem;
  display: none;
  align-items: center;
  justify-content: center;
}

.overlay {
  display:none; position:fixed; top:0; left:0; right:0; bottom:0;
  background: rgba(0,0,0,0.5); z-index: 900;
}

.main-content {
  flex:1; margin-left: 250px; padding: 2rem;
}
.card {
  background:white; padding:2rem; border-radius:12px;
  box-shadow:0 4px 12px rgba(0,0,0,0.1); max-width:600px; margin:auto;
}
.card h2 { margin-bottom:1rem; color:#1a237e; text-align:center; }
input {
  width:100%; padding:0.8rem; margin:0.5rem 0 1.2rem 0;
  border:1px solid #ccc; border-radius:6px;
}
label { font-weight:600; }
button {
  background:#1a237e; color:white; padding:0.8rem 1.5rem;
  border:none; border-radius:6px; font-weight:bold; cursor:pointer;
  width:100%; margin-top:1rem;
}
button:hover { background:#3949ab; }
.message {
  padding:0.8rem; margin-bottom:1rem; border-radius:6px; text-align:center;
}
.success { background:#e8f5e9; color:#2e7d32; border-left:4px solid #2e7d32; }
.error { background:#ffebee; color:#c62828; border-left:4px solid #c62828; }
.note { font-size:0.9rem; color:#555; margin-bottom:1.5rem; }

@media(max-width:768px){
  .hamburger { display:flex; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  .overlay.active { display:block; }
  .main-content { margin-left:0; padding-top:4rem; }
}

</style>
</head>
<body>

<button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="overlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
  <h1>Admin panel</h1>
  <div class="section-title">Menú Principal</div>
  <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage-series.php"><i class="fas fa-tv"></i> Series</a>
  <a href="create-movie.php"><i class="fas fa-video"></i> Crear Película</a>
  <a href="manage-categories.php"><i class="fas fa-layer-group"></i> Categorías</a>
  <div class="section-title">⚙️ Configuración</div>
  <a href="config_home.php"><i class="fas fa-home"></i> Config Home</a>
  <a href="config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>
  <div class="section-title">🔧 Admin Tools</div>
  <a href="soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
  <a href="configure-api.php"><i class="fas fa-key"></i> TMDB API</a>
  <a href="api/generadores.php"><i class="fas fa-cogs"></i> App Creator 24 📲</a>
  <a href="monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
  <a href="telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <div class="section-title">Usuario</div>
  <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
  <div class="card">
    <h2><i class="fab fa-telegram"></i> Configurar Bot de Telegram</h2>

    <?php if ($mensaje): ?>
      <div class="message <?= strpos($mensaje, '✅') !== false ? 'success' : 'error' ?>">
        <?= $mensaje ?>
      </div>
    <?php endif; ?>

    <p class="note">Completa todos los campos para guardar la configuración del bot y la URL base de tu sitio.</p>
	<div class="message" style="background:#fff3cd; color:#856404; border-left:4px solid #ffeeba; margin-bottom:1.5rem;">
  <strong>¿No tienes un bot?</strong><br>
  Necesitas crear un bot en Telegram y obtener el token para poder configurar esta sección.<br>
  Si no sabes cómo hacerlo, puedes ver el paso a paso en nuestro <a href="tutoriales.php" style="color:#0d47a1; font-weight:bold;">📘 Tutorial aquí</a>.
</div>


    <form method="POST">
      <label for="bot_token">🔑 Bot Token:</label>
      <input type="text" id="bot_token" name="bot_token" value="<?= htmlspecialchars($bot_token) ?>" required>

      <label for="chat_id">📣 Chat ID del Canal o Grupo:</label>
      <input type="text" id="chat_id" name="chat_id" value="<?= htmlspecialchars($chat_id) ?>" required>

      <label for="base_url">🌐 URL Base de tu sitio (sin /final):</label>
      <input type="text" id="base_url" name="base_url" value="<?= htmlspecialchars($base_url) ?>" required>

      <button type="submit"><i class="fas fa-save"></i> Guardar Configuración</button>
    </form>

  </div>
</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
  document.querySelector('.overlay').classList.toggle('active');
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('active');
  document.querySelector('.overlay').classList.remove('active');
}
</script>
<script src="../js/protect.js"></script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>

</body>
</html>
