<?php
require_once 'config.php';

// Verifica modo mantenimiento
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

// Cargar todas las películas
// Cargar películas
$moviesFile = 'data/movies.json';
$moviesData = file_exists($moviesFile) ? json_decode(file_get_contents($moviesFile), true) : [];

// Cargar series desde archivos individuales
$seriesDir = 'data/series/';
$seriesData = [];

foreach (glob($seriesDir . '*.json') as $file) {
    $id = basename($file, '.json');
    $data = json_decode(file_get_contents($file), true);
    if ($data && isset($data['title'])) {
        $seriesData[$id] = $data;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Favoritos - <?= htmlspecialchars($siteData['title']) ?></title>
  <link rel="icon" href="<?= htmlspecialchars($siteData['favicon']) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    body {
      margin: 0;
      padding: 20px;
      font-family: 'Orbitron', sans-serif;
      background: radial-gradient(circle at top left, #0f0f1a, #000);
      color: #eee;
    }
    header {
      display: flex;
      align-items: center;
      padding-bottom: 10px;
    }
    header i {
      font-size: 1.8rem;
      color: #0ff;
      cursor: pointer;
      transition: 0.4s;
      text-shadow: 0 0 8px #0ff, 0 0 15px #0ff;
    }
    header i:hover {
      color: #ff0;
      transform: scale(1.2) rotate(-5deg);
      text-shadow: 0 0 12px #ff0;
    }
    h1 {
      text-align: center;
      color: #0ff;
      margin-top: 15px;
      text-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    }
    .favorito {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #111;
      border: 2px solid #0ff40;
      border-radius: 15px;
      margin: 12px 0;
      padding: 12px;
      box-shadow: 0 0 15px #0ff60;
      transition: 0.4s;
    }
    .favorito:hover {
      transform: scale(1.02);
      background: #000;
      box-shadow: 0 0 20px #ff0, 0 0 40px #ff0;
    }
    .info {
      display: flex;
      align-items: center;
      cursor: pointer;
    }
    .favorito img {
      width: 65px;
      border-radius: 12px;
      margin-right: 15px;
      box-shadow: 0 0 10px #0ff;
    }
    .favorito h3 {
      font-size: 1rem;
      margin: 0;
      color: #0ff;
      text-shadow: 0 0 8px #0ff;
    }
    .borrar {
      font-size: 1.2rem;
      color: #ff4444;
      cursor: pointer;
      transition: 0.3s;
    }
    .borrar:hover {
      color: #ff0;
      transform: rotate(10deg) scale(1.2);
      text-shadow: 0 0 10px #ff0;
    }
    .vacio {
      text-align: center;
      margin-top: 40px;
      color: #888;
      font-size: 1.1rem;
    }
  </style>
</head>
<body>
  <h1>Mis Favoritos</h1>
  <div id="listaFavoritos"></div>

  <script>
    const peliculas = <?= json_encode($moviesData) ?>;
	const series = <?= json_encode($seriesData) ?>;
    const contenedor = document.getElementById('listaFavoritos');

function cargarFavoritos() {
  contenedor.innerHTML = "";
  let favoritos = JSON.parse(localStorage.getItem('favoritos_cine') || '[]').slice(0, 30);

  if (favoritos.length === 0) {
    contenedor.innerHTML = "<p class='vacio'>No tienes favoritos aún.</p>";
    return;
  }

  favoritos.forEach(id => {
    let item = peliculas[id] || series[id];
    if (!item) return;

    const esSerie = !!series[id];
    const div = document.createElement('div');
    div.className = 'favorito';

    div.innerHTML = `
      <div class="info" onclick="window.location.href='${esSerie ? 'series' : 'peliculas'}/${id}.php'">
        <img src="https://image.tmdb.org/t/p/w92${item.poster_path}" alt="${item.title}" />
        <h3>${item.title}</h3>
      </div>
      <i class="fas fa-trash borrar" title="Eliminar favorito" onclick="eliminarFavorito('${id}', event)"></i>
    `;

    contenedor.appendChild(div);
  });
}


    function eliminarFavorito(id, e) {
      e.stopPropagation();
      let favoritos = JSON.parse(localStorage.getItem('favoritos_cine') || '[]');
      favoritos = favoritos.filter(fav => fav !== id);
      localStorage.setItem('favoritos_cine', JSON.stringify(favoritos));
      cargarFavoritos();
    }

    cargarFavoritos();
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
      if (e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) || e.keyCode == 'J'.charCodeAt(0))) return false;
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
