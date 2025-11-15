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

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = $current_data['role'];
}
$current_role = $_SESSION['role'];

$is_admin = in_array($current_role, ['admin', 'super_admin']);
$is_super_admin = $current_role === 'super_admin';

if (!in_array($current_role, ['editor', 'admin', 'super_admin'])) {
    die('<h2 style="color:red;text-align:center;">⛔ Acceso restringido.</h2>');
}

$api_config = json_decode(file_get_contents('../data/config_api.json') ?: '{}', true);
$tmdb_api_key = $api_config['tmdb_api_key'] ?? '';
$movie_data = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tmdb_id = trim($_POST['tmdb_id']);
    $category = trim($_POST['category'] ?? 'Sin categoría');
    $trailer_key = trim($_POST['trailer_key'] ?? '');
    $player_options = [];

    if (!empty($_POST['players'])) {
        foreach ($_POST['players'] as $player) {
            $label = trim($player['label'] ?? '');
            $url = trim($player['url'] ?? '');
            if ($label && $url) {
                $player_options[] = ['label' => $label, 'url' => $url];
            }
        }
    }
    if (count($player_options) < 1) {
        $player_options[] = ['label' => 'Default', 'url' => '#'];
    }

    // Capturar géneros desde el formulario (array)
    $genres = $_POST['genres'] ?? [];
    if (!is_array($genres)) {
        $genres = [];
    }

    if (!empty($tmdb_id) && !empty($tmdb_api_key)) {
        $tmdb_url = "https://api.themoviedb.org/3/movie/{$tmdb_id}?api_key={$tmdb_api_key}&language=es";
        $response = file_get_contents($tmdb_url);
        $data = json_decode($response, true);

        if (isset($data['title'])) {
            $movies_file = '../data/movies.json';
            $movies = file_exists($movies_file) ? json_decode(file_get_contents($movies_file), true) : [];

            $movie_data = [
                'id' => $tmdb_id,
                'title' => $data['title'],
                'overview' => $data['overview'],
                'poster_path' => $data['poster_path'],
                'backdrop_path' => $data['backdrop_path'],
                'release_date' => $data['release_date'],
                'category' => $category,
                'genres' => $genres,  // Guardamos el array de géneros aquí
                'trailer' => $trailer_key,
                'players' => $player_options
            ];

            $movies[$tmdb_id] = $movie_data;
            file_put_contents($movies_file, json_encode($movies, JSON_PRETTY_PRINT));

            $movie_php = "<?php\n\$movie_id = '{$tmdb_id}';\ninclude '../components/render-movie.php';\n?>";
            file_put_contents("../peliculas/{$tmdb_id}.php", $movie_php);

            $message = '✅ Película guardada exitosamente.';
        } else {
            $message = '❌ No se encontró la película con ese ID en TMDB.';
        }
    } else {
        $message = '❌ Debes ingresar un ID válido y tener configurada la API key.';
    }
}

$categories = json_decode(file_get_contents('../data/categories.json') ?: '{}', true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Crear Película</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;background:#f4f6fc;display:flex;min-height:100vh;}
.sidebar{
  width:250px;background:#1a237e;color:white;height:100vh;
  position:fixed;left:0;top:0;overflow-y:auto;transition:transform 0.3s ease;
  z-index:1000;padding:1.5rem 1rem;
}
.sidebar h1{font-size:1.4rem;margin-bottom:1.2rem;text-align:center;}
.sidebar .section-title{
  font-size:0.8rem;text-transform:uppercase;opacity:0.7;
  margin:1rem 0 0.5rem 0;padding-left:1rem;
}
.sidebar a{
  display:flex;align-items:center;gap:10px;
  color:white;text-decoration:none;padding:0.5rem 1rem;
  border-radius:6px;margin-bottom:0.3rem;font-size:0.95rem;
}
.sidebar a:hover{background:rgba(255,255,255,0.2);}
.hamburger{
  position:fixed;top:1rem;left:1rem;font-size:1.5rem;
  background:#1a237e;color:white;border:none;padding:0.6rem;
  border-radius:6px;z-index:1100;cursor:pointer;display:none;
}
.main-content{flex:1;margin-left:250px;padding:2rem;}
@media(max-width:768px){
  .hamburger{display:block;}
  .sidebar{transform:translateX(-100%);}
  .sidebar.active{transform:translateX(0);}
  .main-content{margin-left:0;padding-top:4rem;}
}
.card{
  background:white;padding:2rem;border-radius:10px;
  box-shadow:0 0 15px rgba(0,0,0,0.1);max-width:600px;margin:auto;
}
.input,select{
  width:100%;padding:0.8rem;margin:0.8rem 0;
  border:1px solid #ccc;border-radius:6px;
}
.btn,.btn-add,.btn-delete{
  display:inline-block;padding:0.6rem 1rem;border:none;
  border-radius:6px;cursor:pointer;font-weight:bold;
}
.btn{background:#1a237e;color:white;}
.btn-add{background:#f0f0f0;}
.btn-delete{background:#ff4444;color:white;margin-left:0.5rem;}
.player-item{display:flex;gap:0.5rem;margin:0.5rem 0;}
.alert{
  padding:0.8rem;background:#e3f2fd;
  border-left:5px solid #1a237e;margin-bottom:1rem;
}
.note{font-size:0.9rem;color:#555;margin-bottom:1rem;}
.info-box {
  background: #e3f2fd;
  padding: 1rem;
  border-left: 5px solid #1a237e;
  margin-bottom: 1rem;
  border-radius: 8px;
  font-size: 0.95rem;
  line-height: 1.5;
}
.info-box a {
  color: #0d47a1;
  text-decoration: underline;
  font-weight: bold;
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
  <h2><i class="fas fa-video"></i> Crear Película</h2>
  <div class="card">
    <?php if ($message): ?><p class="alert"><?= $message ?></p><?php endif; ?>
    
    <!-- Botón para abrir modal de búsqueda -->
    <p style="margin-bottom:1rem;">
      <button type="button" id="openTmdbSearchBtn" class="btn">
        <i class="fas fa-search"></i> Ayuda para buscar película
      </button>
    </p>
<form method="POST" id="movieForm">
  <input type="text" name="tmdb_id" id="tmdb_id" placeholder="ID de TMDB" required class="input" />
  <button type="button" onclick="fetchGenres()" class="btn" style="margin-left:10px;">Generar Géneros</button>

  <select name="category" class="input" style="margin-top:1rem;">
    <option value="">Seleccionar Categoría</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <div id="genresContainer" style="margin-top:1rem;">
    <!-- Aquí se llenarán los inputs para géneros -->
  </div>

  <input type="text" name="trailer_key" placeholder="ID del tráiler de YouTube" class="input" style="margin-top:1rem;" />

  <div class="player-section" style="margin-top:1rem;">
    <div class="info-box">
      📢 <strong>Recomendación:</strong><br />
      Para evitar problemas de reproducción, se recomienda usar enlaces de <a href="https://filemoon.to/reg89462" target="_blank">Filemoon</a> o <a href="https://pixeldrain.com" target="_blank">Pixeldrain</a>.  
      Estos servicios funcionan al 100% con el sistema.
    </div>

    <h4>Enlaces de Reproducción</h4>

    <div id="playerInputs">
      <div class="player-item">
        <input type="text" name="players[0][label]" placeholder="Nombre del botón" class="input" required />
        <input type="url" name="players[0][url]" placeholder="URL del reproductor" class="input" required />
      </div>
    </div>
    <button type="button" class="btn-add" onclick="agregarPlayer()">+ Añadir Enlace</button>
  </div>

  <button type="submit" class="btn" style="margin-top:1rem;"><i class="fas fa-save"></i> Guardar Película</button>
</form>

<script>
  async function fetchGenres() {
    const apiKey = '<?= $tmdb_api_key ?>';
    const tmdbId = document.getElementById('tmdb_id').value.trim();
    if (!tmdbId) {
      alert('Por favor ingresa un ID válido de TMDB');
      return;
    }

    try {
      const response = await fetch(`https://api.themoviedb.org/3/movie/${tmdbId}?api_key=${apiKey}&language=es`);
      if (!response.ok) throw new Error('Película no encontrada');
      const data = await response.json();

      const genresContainer = document.getElementById('genresContainer');
      genresContainer.innerHTML = '';

      if (data.genres && data.genres.length > 0) {
        data.genres.forEach(genre => {
          // Crear input oculto para enviar el género en el POST
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'genres[]';
          input.value = genre.name;
          genresContainer.appendChild(input);

          // Mostrar el género visible al editor
          const label = document.createElement('label');
          label.textContent = genre.name;
          label.style.marginRight = '10px';
          genresContainer.appendChild(label);
        });
      } else {
        genresContainer.textContent = 'No se encontraron géneros para esta película.';
      }
    } catch (error) {
      alert('Error al buscar la película o sus géneros: ' + error.message);
    }
  }
</script>

    <div style="text-align:center;margin-top:1.5rem;">
      <a href="manage-movies.php" class="btn" style="text-decoration:none;"><i class="fas fa-edit"></i> Editar Películas</a>
    </div>
  </div>
</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
}
let playerCount=1;
function agregarPlayer(){
  if(playerCount>=4) return;
  const container=document.getElementById('playerInputs');
  const div=document.createElement('div');
  div.className='player-item';
  div.innerHTML=`
    <input type="text" name="players[${playerCount}][label]" placeholder="Nombre del botón" class="input" required>
    <input type="url" name="players[${playerCount}][url]" placeholder="URL del reproductor" class="input" required>
    <button type="button" class="btn-delete" onclick="removePlayer(this)"><i class="fas fa-trash-alt"></i></button>
  `;
  container.appendChild(div);
  playerCount++;
}
function removePlayer(btn){
  btn.parentNode.remove();
  playerCount--;
  if(playerCount<1){
    agregarPlayer();
  }
}
</script>
<script src="../js/protect.js"></script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>
<script src="../js/tmdb_search_modal.js"></script>
<script>
  initTmdbSearchModal({
    apiKey: '<?= $tmdb_api_key ?>',
    openButtonId: 'openTmdbSearchBtn',
    tmdbInputId: 'tmdb_id'
  });
</script>

</body>
</html>
