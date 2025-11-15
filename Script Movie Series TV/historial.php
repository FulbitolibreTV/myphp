<?php
require_once 'config.php';

// Verifica mantenimiento
$config_file = 'data/site_config.json';
$config = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [];
$modo_mantenimiento = !empty($config['maintenance']);
$usuario_es_admin = check_session();
if ($modo_mantenimiento && !$usuario_es_admin) {
    header('Location: mantenimiento.php');
    exit;
}

// Datos del sitio
$siteFile = 'data/site_info.json';
$site = file_exists($siteFile) ? json_decode(file_get_contents($siteFile), true) : [];
$siteData = array_merge([
    "title" => "🎥 CorpSRTony Cine",
    "favicon" => "assets/favicon.png"
], $site);

// Cargar películas locales
// Cargar películas
$moviesFile = 'data/movies.json';
$moviesData = file_exists($moviesFile) ? json_decode(file_get_contents($moviesFile), true) : [];

// Cargar series individuales
$seriesData = [];
foreach (glob('data/series/*.json') as $serieFile) {
    $id = basename($serieFile, '.json');
    $data = json_decode(file_get_contents($serieFile), true);
    if ($data) {
        $data['id'] = $id;
        $data['type'] = 'serie';
        $seriesData[$id] = $data;
    }
}

// Combinar películas + series
foreach ($moviesData as $id => $movie) {
    $movie['type'] = 'pelicula';
    $movie['id'] = $movie['id'] ?? $id;
    $seriesData[$movie['id']] = $movie;
}
$mediaData = $seriesData;

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Historial - <?= htmlspecialchars($siteData['title']) ?></title>
  <link rel="icon" href="<?= htmlspecialchars($siteData['favicon']) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Orbitron', sans-serif;
      background: radial-gradient(circle at top left, #0f0f1a, #000);
      color: #eee;
      padding: 20px;
    }
    h1 {
      text-align: center;
      margin-bottom: 25px;
      color: #0ff;
      text-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    }
    .acciones {
      text-align: center;
      margin-bottom: 30px;
    }
    .btn-limpiar {
      background: linear-gradient(135deg, #ff0044, #ff8800);
      border: none;
      color: #fff;
      padding: 12px 24px;
      font-size: 1rem;
      border-radius: 30px;
      cursor: pointer;
      font-weight: 700;
      box-shadow: 0 0 15px #ff0044;
      transition: 0.4s;
    }
    .btn-limpiar:hover {
      transform: scale(1.05);
      background: linear-gradient(135deg, #ff8800, #ff0044);
      box-shadow: 0 0 20px #ff8800, 0 0 30px #ff8800;
    }
    .peliculas {
      display: flex;
      flex-direction: column;
      gap: 20px;
      max-width: 600px;
      margin: 0 auto;
    }
    .item {
      display: flex;
      align-items: center;
      gap: 15px;
      background: #111;
      border: 2px solid #0ff40;
      padding: 12px;
      border-radius: 15px;
      box-shadow: 0 0 12px #0ff60;
      cursor: pointer;
      transition: 0.4s;
    }
    .item:hover {
      background: #000;
      box-shadow: 0 0 20px #ff0, 0 0 40px #ff0;
      transform: scale(1.02);
    }
    .item img {
      width: 75px;
      border-radius: 12px;
      box-shadow: 0 0 10px #0ff;
    }
    .item span {
      font-size: 1rem;
      font-weight: 700;
      color: #0ff;
      text-shadow: 0 0 8px #0ff;
    }
    .no-historial {
      text-align: center;
      font-size: 1.2rem;
      margin-top: 40px;
      color: #888;
    }
  </style>
</head>
<body>
  <h1>Historial Reciente</h1>

  <div class="acciones">
    <button class="btn-limpiar" onclick="borrarHistorial()">🗑️ Limpiar Historial</button>
  </div>

  <div class="peliculas" id="contenedor"></div>
  <div class="no-historial" id="mensajeVacio">No has visto ninguna película aún.</div>

<script>
  const media = <?= json_encode($mediaData) ?>;
  const contenedor = document.getElementById('contenedor');
  const mensajeVacio = document.getElementById('mensajeVacio');

  function cargarHistorial() {
    contenedor.innerHTML = "";
    let historial = JSON.parse(localStorage.getItem('historial_cine') || '[]').slice(0,15);

    if (historial.length === 0) {
      mensajeVacio.style.display = "block";
      return;
    } else {
      mensajeVacio.style.display = "none";
    }

historial.forEach(id => {
  const item = media[id];
  if (item) {
    const div = document.createElement('div');
    div.className = 'item';
    const tipo = item.type === 'serie' ? 'series' : 'peliculas';
    div.onclick = () => window.location.href = `${tipo}/${id}.php`;

    div.innerHTML = `
      <img src="https://image.tmdb.org/t/p/w92${item.poster_path}" alt="${item.title}" />
      <span>${item.title}</span>
    `;

    contenedor.appendChild(div);
  }
});

  }

  function borrarHistorial() {
    localStorage.removeItem('historial_cine');
    cargarHistorial();
  }

  cargarHistorial();
</script>

<script>
document.addEventListener('contextmenu', e => {
  e.preventDefault();
  alert("🚫 No está permitido copiar ni inspeccionar este sitio.");
});
document.addEventListener('selectstart', e => e.preventDefault());
document.addEventListener('dragstart', e => e.preventDefault());
document.onkeydown = function(e) {
  if (e.keyCode == 123) return false;
  if (e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) ||
                                   e.keyCode == 'J'.charCodeAt(0))) return false;
  if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false;
  if (e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
    e.preventDefault();
    return false;
  }
};
document.addEventListener("DOMContentLoaded", function(){
  document.querySelectorAll("img, video").forEach(el => {
    el.addEventListener("contextmenu", e => e.preventDefault());
    el.addEventListener("dragstart", e => e.preventDefault());
  });
});
</script>
</body>
</html>
