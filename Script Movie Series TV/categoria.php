<?php
require_once 'config.php';

$categoria = $_GET['categoria'] ?? '';
if ($categoria === '') {
  die('Categoría no especificada.');
}

$movies = $series = [];

if (file_exists('data/movies.json')) {
    $movies = json_decode(file_get_contents('data/movies.json'), true);
    foreach ($movies as &$m) $m['tipo'] = 'pelicula';
}
if (file_exists('data/series.json')) {
    $series = json_decode(file_get_contents('data/series.json'), true);
    foreach ($series as &$s) $s['tipo'] = 'serie';
}

$todo = array_merge($movies, $series);

$siteData = [
  "title" => "🎥 CorpSRTony Cine",
  "favicon" => "assets/favicon.png",
  "footer" => "© 2025 CorpSRTony Cine. Todos los derechos reservados.",
  "main_color" => "#0e0e0e",
  "header_color" => "#1a1a1a"
];

$siteFile = 'data/site_info.json';
if (file_exists($siteFile)) {
  $json = file_get_contents($siteFile);
  $site = json_decode($json, true);
  $siteData = array_merge($siteData, $site);
}

$filtradas = array_filter($todo, function ($m) use ($categoria) {
  return strtolower($m['category']) === strtolower($categoria);
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($categoria) ?> - <?= htmlspecialchars($siteData['title']) ?></title>
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($siteData['favicon']) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 20px;
      background: radial-gradient(circle at top left, #0f0f1a, #000);
      color: #eee;
      font-family: 'Orbitron', sans-serif;
    }

    header {
      background: #111;
      padding: 10px 20px;
      border-bottom: 2px solid #0ff40;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 0 10px #0ff;
    }

    .logo {
      font-size: 1.5rem;
      color: #0ff;
      text-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    }

    .icons a {
      color: #0ff;
      font-size: 1.2rem;
      text-decoration: none;
      padding: 8px;
      transition: 0.3s;
    }

    .icons a:hover {
      color: #ff0;
    }

    .search-box {
      max-width: 500px;
      margin: 30px auto 20px;
      display: flex;
      border: 2px solid #0ff40;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 10px #0ff;
    }

    .search-box input {
      flex: 1;
      padding: 12px;
      border: none;
      background: #000;
      color: #0ff;
      font-size: 1rem;
    }

    .search-box button {
      padding: 12px 20px;
      background: linear-gradient(135deg, #ff0044, #ff8800);
      color: #fff;
      border: none;
      cursor: pointer;
      font-weight: bold;
      transition: 0.4s;
    }

    .search-box button:hover {
      background: linear-gradient(135deg, #ff8800, #ff0044);
      box-shadow: 0 0 20px #ff8800;
    }

    h2 {
      text-align: center;
      color: #ff6347;
      margin-top: 10px;
      text-shadow: 0 0 10px #ff6347;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 15px;
      padding: 20px;
    }

    .movie-card {
      background: #111;
      border: 2px solid #0ff40;
      border-radius: 12px;
      overflow: hidden;
      transition: transform 0.3s, box-shadow 0.3s;
      cursor: pointer;
      box-shadow: 0 0 10px #0ff40;
    }

    .movie-card:hover {
      transform: scale(1.05);
      box-shadow: 0 0 25px #ff0;
    }

    .movie-card img {
      width: 100%;
      display: block;
      border-radius: 10px;
    }

    @media (max-width: 500px) {
      .search-box {
        flex-direction: column;
      }

      .search-box input,
      .search-box button {
        width: 100%;
        border-radius: 0;
      }

      .search-box button {
        border-top: 1px solid #0ff;
      }
    }
  </style>
</head>
<body>

  <header>
    <div class="logo"><?= htmlspecialchars($siteData['title']) ?></div>
    <div class="icons">
    </div>
  </header>

  <div class="search-box">
    <input type="text" id="searchInput" placeholder="Buscar película o Series..">
    <button onclick="buscar()">Buscar</button>
  </div>

  <h2>Películas en: <?= htmlspecialchars($categoria) ?></h2>
  <div class="grid" id="movieGrid">
    <?php foreach ($filtradas as $movie): ?>
      <?php $ruta = $movie['tipo'] === 'serie' ? 'series' : 'peliculas'; ?>
      <div class="movie-card" onclick="window.location.href='<?= $ruta ?>/<?= $movie['id'] ?>.php'">
        <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
      </div>
    <?php endforeach; ?>
  </div>

  <script>
    function buscar() {
      const input = document.getElementById('searchInput').value.toLowerCase();
      document.querySelectorAll('#movieGrid .movie-card').forEach(card => {
        const alt = card.querySelector('img').alt.toLowerCase();
        card.style.display = alt.includes(input) ? 'block' : 'none';
      });
    }

    // Protección anti-inspección
    document.addEventListener('contextmenu', e => {
      e.preventDefault();
      alert("🚫 No está permitido copiar ni inspeccionar este sitio.");
    });
    document.addEventListener('selectstart', e => e.preventDefault());
    document.addEventListener('dragstart', e => e.preventDefault());
    document.onkeydown = function(e) {
      if (e.keyCode == 123) return false;
      if (e.ctrlKey && e.shiftKey && ['I', 'J'].includes(String.fromCharCode(e.keyCode))) return false;
      if (e.ctrlKey && ['U', 'S'].includes(String.fromCharCode(e.keyCode))) {
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
