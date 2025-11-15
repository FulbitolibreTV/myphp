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

    // Capturar géneros enviados
$genres_json = $_POST['genres'] ?? '[]';
$genres = json_decode($genres_json, true);
if (!is_array($genres)) {
    $genres = [];
}


    if (!empty($tmdb_id) && !empty($tmdb_api_key)) {
        $tmdb_url = "https://api.themoviedb.org/3/tv/{$tmdb_id}?api_key={$tmdb_api_key}&language=es";
        $response = @file_get_contents($tmdb_url);

        if ($response) {
            $data = json_decode($response, true);
            $title = $data['title'] ?? $data['name'] ?? '';

            if (!empty($title)) {
                $series_file = '../data/series.json';
                $series = file_exists($series_file) ? json_decode(file_get_contents($series_file), true) : [];

                $serie_data = [
                    'id' => $tmdb_id,
                    'title' => $title,
                    'overview' => $data['overview'] ?? '',
                    'poster_path' => $data['poster_path'] ?? '',
                    'backdrop_path' => $data['backdrop_path'] ?? '',
                    'release_date' => $data['first_air_date'] ?? '',
                    'category' => $category,
                    'trailer' => $trailer_key,
                    'players' => $player_options,
                    'seasons' => $_POST['serie'] ?? [],
                    'genres' => $genres
                ];

                $series[$tmdb_id] = $serie_data;
                file_put_contents($series_file, json_encode($series, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                // Guardar el archivo JSON individual por serie
                $json_series_path = "../data/series/{$tmdb_id}.json";
                if (!is_dir('../data/series')) {
                    mkdir('../data/series', 0777, true);
                }
                file_put_contents($json_series_path, json_encode($serie_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                // Crear el archivo PHP fuera de /data
                $php_series_path = "../series/{$tmdb_id}.php";
                if (!is_dir('../series')) {
                    mkdir('../series', 0777, true);
                }
                $serie_php = "<?php\n\$serie_id = '{$tmdb_id}';\ninclude '../components/render-series.php';\n?>";
                file_put_contents($php_series_path, $serie_php);

                $message = '✅ Serie guardada exitosamente.';
            } else {
                $message = '❌ No se encontró el contenido con ese ID en TMDB.';
            }
        } else {
            $message = '⚠️ Error al conectar con TMDB. Revisa tu API key o conexión.';
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
  <title>Crear Serie Manualmente</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * { box-sizing: border-box; margin:0; padding:0; }
    body { font-family: 'Inter', sans-serif; background: #f4f6fc; display:flex; min-height:100vh; }
    .sidebar {
      width: 250px; background: #1a237e; color: white; height: 100vh;
      position: fixed; left:0; top:0; overflow-y:auto;
      transition: transform 0.3s ease; z-index: 1000; padding: 1.5rem 1rem;
    }
    .sidebar h1 {
      font-size:1.4rem; margin-bottom:1.2rem; text-align:center;
    }
    .sidebar .section-title {
      font-size:0.8rem; text-transform:uppercase;
      opacity:0.7; margin:1rem 0 0.5rem 0; padding-left:1rem;
    }
    .sidebar a {
      display:flex; align-items:center; gap:10px;
      color:white; text-decoration:none;
      padding:0.5rem 1rem; border-radius:6px; margin-bottom:0.3rem; font-size:0.95rem;
    }
    .sidebar a:hover { background: rgba(255,255,255,0.2); }
    .hamburger {
      position: fixed; top: 1rem; left: 1rem; font-size: 1.5rem;
      background: #1a237e; color: white; border: none; padding: 0.6rem;
      border-radius: 6px; z-index: 1100; cursor: pointer; display: none;
    }
    .main-content {
      flex:1; margin-left: 250px; padding: 2rem; display:flex; flex-direction:column;
    }
    .card{background:white;padding:2rem;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.1);max-width:800px;margin:auto;}
    .input, select {width:100%;padding:0.8rem;margin:0.5rem 0;border:1px solid #ccc;border-radius:6px;}
.btn {
  padding: 0.8rem 1.4rem;
  background: #1a237e;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 0.8rem;
  font-size: 1rem;
}
    .season-block {border:1px solid #ccc;padding:1rem;margin-bottom:1rem;border-radius:6px;}
    @media(max-width:768px){
      .hamburger { display:block; }
      .sidebar { transform: translateX(-100%); }
      .sidebar.active { transform: translateX(0); }
      .main-content { margin-left:0; padding-top:4rem; }
    }
  </style>
</head>
<body>

<button class="hamburger" onclick="toggleSidebar()">
  <i class="fas fa-bars"></i>
</button>

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
  <h2><i class="fas fa-tv"></i> Crear Serie Manualmente</h2>
  <div class="card">
    <?php if ($message): ?><p><?= $message ?></p><?php endif; ?>
<form method="POST">
  <input type="text" name="tmdb_id" placeholder="ID de TMDB" required class="input">

<button type="button" id="openTmdbSearchBtn" class="btn" style="margin-bottom:1rem;">
  <i class="fas fa-search"></i> Ayuda para buscar serie
</button>

  <select name="category" class="input">
    <option value="">Seleccionar Categoría</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <input type="text" name="trailer_key" placeholder="ID del tráiler de YouTube" class="input">

  <button type="button" class="btn" onclick="buscarGeneros()">🎬 Buscar géneros TMDB</button>

  <div id="genresContainer" style="margin: 1rem 0; padding: 1rem; background: #e0e7ff; border-radius: 8px; min-height: 50px;">
    <!-- Aquí se mostrarán los géneros seleccionados -->
  </div>

  <!-- Campo oculto para enviar géneros como JSON -->
  <input type="hidden" name="genres" id="genresInput" value="[]">

  <div style="background:#e3f2fd; padding:1rem; border-left:5px solid #2196f3; margin-bottom:1rem; border-radius:8px;">
    <strong>📢 Recomendación:</strong><br>
    Puedes subir tu contenido a <a href="https://filemoon.to/reg89462" target="_blank" style="color:#0d47a1;text-decoration:underline;">Filemoon</a> o <a href="https://pixeldrain.com/" target="_blank" style="color:#0d47a1;text-decoration:underline;">Pixeldrain</a>.<br>
    Funcionan al 100% con el sistema. ¡Solo copia y pega el enlace del video en el capítulo!
  </div>

  <div id="seasonsContainer"></div>
  <button type="button" class="btn" onclick="agregarTemporada()">➕ Añadir Temporada</button>

  <br><br>
  <button type="submit" class="btn">💾 Guardar Serie</button>
</form>

<form action="edit_series.php" method="POST">
  <!-- otros campos si los hay -->
  <button type="submit" class="btn" style="margin-top: 1rem;">Editar Series</button>
</form>

  </div>
</div>
<script>
let temporadaCount = 0;
function agregarTemporada() {
  temporadaCount++;
  const cont = document.getElementById('seasonsContainer');
  const div = document.createElement('div');
  div.className = 'season-block';
  div.innerHTML = `
    <h3>Temporada ${temporadaCount}</h3>
    <div id="epsT${temporadaCount}"></div>
    <button type="button" class="btn" onclick="agregarCapitulo(${temporadaCount})">➕ Añadir Capítulo</button>
  `;
  cont.appendChild(div);
}
function agregarCapitulo(tempNum) {
  const cont = document.getElementById(`epsT${tempNum}`);
  const epNum = cont.children.length + 1;
  const div = document.createElement('div');
  div.innerHTML = `<label>Capítulo ${epNum}</label><input type="url" class="input" name="serie[${tempNum}][${epNum}][url]" placeholder="URL del capítulo">`;
  cont.appendChild(div);
}
</script>
<script>
  function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    sidebar.classList.toggle("active");
  }
</script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>
<script>
async function buscarGeneros() {
  const apiKey = '<?= $tmdb_api_key ?>'; // Tu API Key de TMDB desde PHP
  const tmdbId = document.querySelector('input[name="tmdb_id"]').value.trim();

  if (!tmdbId) {
    alert('Por favor ingresa un ID válido de TMDB');
    return;
  }

  try {
    const response = await fetch(`https://api.themoviedb.org/3/tv/${tmdbId}?api_key=${apiKey}&language=es`);
    if (!response.ok) throw new Error('No se encontró la serie con ese ID');

    const data = await response.json();

    const genres = data.genres || [];
    const container = document.getElementById('genresContainer');
    container.innerHTML = '';

    if (genres.length === 0) {
      container.textContent = 'No se encontraron géneros para esta serie.';
      updateGenresInput([]);
      return;
    }

    // Crear checkboxes para cada género
    genres.forEach(genre => {
      const label = document.createElement('label');
      label.style.marginRight = '10px';
      label.style.cursor = 'pointer';

      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.value = genre.name;
      checkbox.checked = true; // Por defecto checked
      checkbox.style.marginRight = '5px';

      checkbox.addEventListener('change', () => {
        actualizarGenerosSeleccionados();
      });

      label.appendChild(checkbox);
      label.appendChild(document.createTextNode(genre.name));

      container.appendChild(label);
    });

    // Actualizar input oculto con los géneros seleccionados
    actualizarGenerosSeleccionados();

  } catch (error) {
    alert('Error al buscar géneros: ' + error.message);
  }
}

function actualizarGenerosSeleccionados() {
  const container = document.getElementById('genresContainer');
  const checkboxes = container.querySelectorAll('input[type="checkbox"]');
  const seleccionados = [];

  checkboxes.forEach(cb => {
    if (cb.checked) {
      seleccionados.push(cb.value);
    }
  });

  updateGenresInput(seleccionados);
}

function updateGenresInput(genresArray) {
  const input = document.getElementById('genresInput');
  input.value = JSON.stringify(genresArray);
}
</script>
<script src="../js/tmdb_search_modalserie.js"></script>
<script>
  initTmdbSearchModal({
    apiKey: '<?= $tmdb_api_key ?>',
    openButtonId: 'openTmdbSearchBtn',
    tmdbInputId: 'tmdb_id'
  });
</script>


</body>
</html>
