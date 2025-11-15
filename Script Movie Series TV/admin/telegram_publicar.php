<?php
require_once '../config.php';
if (!check_session() || !is_super_admin()) { header('Location: login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$current_name = $_SESSION['name'] ?? $current_user;
$current_image = $current_data['profile_image'];

$movies = json_decode(file_get_contents('../data/movies.json'), true);
$series = [];
$series_dir = '../data/series/';
foreach (glob($series_dir . '*.json') as $file) {
    $id = basename($file, '.json');
    $data = json_decode(file_get_contents($file), true);
    if ($data && isset($data['title'])) {
        $series[$id] = $data;
    }
}
$telegram_config = json_decode(file_get_contents('../data/telegram_config.json'), true);
$mensaje = '';

function escapeMarkdown($text) {
    $escape = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($escape as $char) {
        $text = str_replace($char, '\\' . $char, $text);
    }
    return $text;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['movie_id'])) {
list($tipo, $id) = explode(':', $_POST['movie_id']);
if ($tipo === 'movie') {
    $movie = $movies[$id] ?? null;
    $url = rtrim($telegram_config['base_url'], '/') . "/peliculas/{$id}.php";
} else {
    $movie = $series[$id] ?? null;
    $url = rtrim($telegram_config['base_url'], '/') . "/series/{$id}.php";
}

        if ($movie && !empty($telegram_config['bot_token']) && !empty($telegram_config['chat_id']) && !empty($telegram_config['base_url'])) {
            $bot_token = $telegram_config['bot_token'];
            $chat_id = $telegram_config['chat_id'];
            $movie_name = $movie['title'];
            $url = rtrim($telegram_config['base_url'], '/') . "/peliculas/{$movie_id}.php";

            $text = "🎬 *Nueva película agregada: " . escapeMarkdown($movie_name) . "*\n[▶️ Ver Película](" . escapeMarkdown($url) . ")";

            $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
            $data = [
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => 'MarkdownV2'
            ];

            $options = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/json\r\n",
                    'content' => json_encode($data)
                ]
            ];

            $context  = stream_context_create($options);
            $result = file_get_contents($api_url, false, $context);
            $response = json_decode($result, true);

            $mensaje = !empty($response['ok']) ? "✅ Publicado correctamente en Telegram." : "❌ Error al publicar: " . ($response['description'] ?? 'Respuesta inválida');
        } else {
            $mensaje = "❌ Error: Configuración de Telegram incompleta.";
        }
    }

    if (isset($_POST['custom_message'])) {
        $custom_message = trim($_POST['custom_message']);
        if ($custom_message && !empty($telegram_config['bot_token']) && !empty($telegram_config['chat_id'])) {
            $bot_token = $telegram_config['bot_token'];
            $chat_id = $telegram_config['chat_id'];

            $text = escapeMarkdown($custom_message);
            $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
            $data = [
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => 'MarkdownV2'
            ];

            $options = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/json\r\n",
                    'content' => json_encode($data)
                ]
            ];

            $context  = stream_context_create($options);
            $result = file_get_contents($api_url, false, $context);
            $response = json_decode($result, true);

            $mensaje = !empty($response['ok']) ? "✅ Mensaje personalizado publicado en Telegram." : "❌ Error al publicar: " . ($response['description'] ?? 'Respuesta inválida');
        } else {
            $mensaje = "❌ Error: Configuración de Telegram incompleta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Telegram | CorpSRTony</title>
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
  position: fixed; top: 1rem; left: 1rem;
  width: 44px; height: 44px;
  background: #1a237e; color: white;
  border: none; border-radius: 6px;
  z-index: 1100; cursor: pointer;
  font-size: 1.5rem; display: none;
  align-items: center; justify-content: center;
}
.overlay {
  display:none; position:fixed; top:0; left:0; right:0; bottom:0;
  background: rgba(0,0,0,0.5); z-index: 900;
}
.main-content { flex:1; margin-left: 250px; padding: 2rem; }
.card {
  background:white; padding:2rem; border-radius:12px;
  box-shadow:0 4px 12px rgba(0,0,0,0.1); max-width:600px; margin:auto;
}
.card h2 { margin-bottom:1rem; color:#1a237e; text-align:center; }
select, textarea, button {
  width:100%; padding:0.9rem 1rem; margin-top:1rem;
  border: 2px solid #ddd; border-radius:8px;
  transition: all 0.3s ease; font-size:1rem;
}
select:focus, textarea:focus {
  border-color: #1a237e;
  box-shadow: 0 0 0 4px rgba(26,35,126,0.1);
}
textarea { min-height:80px; resize:vertical; }
button {
  background:#1a237e; color:white; font-weight:bold; border:none;
  cursor:pointer; margin-top:1.5rem;
  transition: background 0.3s ease, transform 0.2s;
}
button:hover { background:#3949ab; transform: translateY(-2px); }
.alert {
  margin-top:1rem; text-align:center; font-weight:bold;
  background:#e8f5e9; color:#2e7d32;
  padding:0.8rem; border-radius:6px;
}
.alert.error {
  background:#ffebee; color:#c62828;
}
.config-link {
  display:inline-block; margin-top:1.5rem; text-align:center;
  background:#666; color:white; padding:0.6rem 1.2rem;
  border-radius:6px; text-decoration:none;
}
.config-link:hover { background:#555; }
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
      <a href="detv.php"><i class="fas fa-play-circle"></i> TV</a>
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
    <h2><i class="fas fa-paper-plane"></i> Publicar Película en Telegram</h2>
    <?php if ($mensaje): ?>
      <div class="alert <?= strpos($mensaje, '✅') !== false ? '' : 'error' ?>">
        <?= htmlspecialchars($mensaje) ?>
      </div>
    <?php endif; ?>
    <form method="POST">
      <label for="movie_id"><strong>Selecciona una película:</strong></label>
      <select name="movie_id" id="movie_id" required>
        <option value="">-- Selecciona --</option>
<optgroup label="🎬 Películas">
<?php foreach ($movies as $id => $movie): ?>
  <option value="movie:<?= $id ?>"><?= htmlspecialchars($movie['title']) ?></option>
<?php endforeach; ?>
</optgroup>
<optgroup label="📺 Series">
<?php foreach ($series as $id => $serie): ?>
  <option value="serie:<?= $id ?>"><?= htmlspecialchars($serie['title']) ?></option>
<?php endforeach; ?>
</optgroup>

      </select>
      <button type="submit"><i class="fab fa-telegram-plane"></i> Publicar Película</button>
    </form>
    <form method="POST">
      <label for="custom_message"><strong>O envía un mensaje personalizado:</strong></label>
      <textarea name="custom_message" id="custom_message" placeholder="Escribe tu mensaje para Telegram..."></textarea>
      <button type="submit"><i class="fab fa-telegram-plane"></i> Publicar Mensaje Personalizado</button>
    </form>
    <div style="text-align:center;">
      <a href="config_telegram.php" class="config-link"><i class="fas fa-cog"></i> Configurar Telegram</a>
    </div>
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
