<?php
$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

// Cargar datos
$movies_file = 'data/movies.json';
$movies = file_exists($movies_file) ? json_decode(file_get_contents($movies_file), true) : [];

// Recopilar todos los géneros únicos (en minúsculas)
$allGenres = [];

// Función para extraer géneros (array o string separados por coma, según tu JSON)
function getGenres($item) {
    if (isset($item['genres']) && is_array($item['genres'])) {
        return array_map('strtolower', $item['genres']);
    }
    return [];
}

// Recopilar géneros de películas
foreach ($movies as $movie) {
    $allGenres = array_merge($allGenres, getGenres($movie));
}

// Recopilar géneros de series
foreach (glob('data/series/*.json') as $serie_file) {
    $serie_data = json_decode(file_get_contents($serie_file), true);
    if (!$serie_data) continue;
    $allGenres = array_merge($allGenres, getGenres($serie_data));
}
$allGenres = array_unique($allGenres);

$resultados = [];

// Buscar por título o por género si coincide con alguno
if (!empty($query)) {
    // Buscar por título igual que antes
    foreach ($movies as $item) {
        if (strpos(strtolower($item['title']), $query) !== false) {
            $item['type'] = 'película';
            $item['link'] = "peliculas/{$item['id']}.php";
            $resultados[] = $item;
        }
    }

    foreach (glob('data/series/*.json') as $serie_file) {
        $serie_data = json_decode(file_get_contents($serie_file), true);
        if (!$serie_data) continue;

        if (strpos(strtolower($serie_data['title']), $query) !== false) {
            $serie_data['type'] = 'serie';
            $id = basename($serie_file, '.json');
            $serie_data['id'] = $id;
            $serie_data['link'] = "series/$id.php";
            $resultados[] = $serie_data;
        }
    }

    // Además, si $query coincide con algún género, buscar por género
    if (in_array($query, $allGenres)) {
        // Buscar películas por género
        foreach ($movies as $item) {
            $genres = getGenres($item);
            if (in_array($query, $genres)) {
                // Verificar no duplicar (por ID)
                $existe = false;
                foreach ($resultados as $r) {
                    if ($r['id'] == $item['id'] && $r['type'] == 'película') {
                        $existe = true;
                        break;
                    }
                }
                if (!$existe) {
                    $item['type'] = 'película';
                    $item['link'] = "peliculas/{$item['id']}.php";
                    $resultados[] = $item;
                }
            }
        }
        // Buscar series por género
        foreach (glob('data/series/*.json') as $serie_file) {
            $serie_data = json_decode(file_get_contents($serie_file), true);
            if (!$serie_data) continue;

            $genres = getGenres($serie_data);
            if (in_array($query, $genres)) {
                $id = basename($serie_file, '.json');
                // Verificar no duplicar
                $existe = false;
                foreach ($resultados as $r) {
                    if ($r['id'] == $id && $r['type'] == 'serie') {
                        $existe = true;
                        break;
                    }
                }
                if (!$existe) {
                    $serie_data['type'] = 'serie';
                    $serie_data['id'] = $id;
                    $serie_data['link'] = "series/$id.php";
                    $resultados[] = $serie_data;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buscar - <?= htmlspecialchars($query ?: 'Películas y Series') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    body {
      background: radial-gradient(circle at top left, #0f0f1a, #000);
      color: #eee;
      font-family: 'Orbitron', sans-serif;
      margin: 0;
    }
    header {
      background: #000;
      border-bottom: 2px solid #0ff;
      box-shadow: 0 0 10px #0ff;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logo {
      font-size: 1.3rem;
      font-weight: 800;
      color: #0ff;
      text-shadow: 0 0 8px #0ff;
    }
    .back-btn {
      color: #0ff;
      text-decoration: none;
      font-size: 1.2rem;
      text-shadow: 0 0 5px #0ff;
    }
    .back-btn:hover {
      color: #ff0;
      text-shadow: 0 0 10px #ff0;
    }
    .search-box {
      text-align: center;
      margin: 30px 20px;
    }
    .search-box form {
      display: flex;
      justify-content: center;
      gap: 10px;
    }
    .search-box input[type="text"] {
      padding: 12px;
      width: 250px;
      border: 2px solid #0ff;
      border-radius: 10px;
      background: #000;
      color: #0ff;
      font-size: 1rem;
      box-shadow: 0 0 10px #0ff;
    }
    .search-box input[type="text"]:focus {
      outline: none;
      border-color: #ff0;
      box-shadow: 0 0 15px #ff0;
    }
    .search-box button {
      padding: 12px 20px;
      border: none;
      background: linear-gradient(135deg, #ff0044, #ff8800);
      color: #fff;
      font-size: 1rem;
      border-radius: 10px;
      cursor: pointer;
      box-shadow: 0 0 10px #ff0044;
      transition: all 0.4s;
    }
    .search-box button:hover {
      transform: scale(1.1);
      background: linear-gradient(135deg, #ff8800, #ff0044);
      box-shadow: 0 0 20px #ff8800, 0 0 40px #ff8800;
    }
    .resultados {
      max-width: 800px;
      margin: auto;
      padding: 20px;
    }
    .resultados h2 {
      color: #0ff;
      font-size: 1.5rem;
      text-shadow: 0 0 8px #0ff;
      margin-bottom: 20px;
      text-align: center;
    }
    .pelicula {
      display: flex;
      align-items: center;
      background: #000;
      padding: 12px;
      border: 2px solid #0ff60;
      border-radius: 15px;
      margin-bottom: 20px;
      gap: 20px;
      box-shadow: 0 0 10px #0ff;
      transition: all 0.3s;
    }
    .pelicula:hover {
      transform: scale(1.02);
      border-color: #ff0;
      box-shadow: 0 0 20px #ff0;
    }
    .pelicula img {
      width: 90px;
      height: 135px;
      object-fit: cover;
      border-radius: 10px;
      background: #111;
    }
    .pelicula-info {
      flex: 1;
    }
    .pelicula-info h3 {
      margin: 0;
      font-size: 1.2rem;
      color: #0ff;
      text-shadow: 0 0 6px #0ff;
    }
    .pelicula-info p {
      margin: 8px 0;
      font-size: 0.9rem;
      color: #aaa;
    }
    .pelicula-info .tipo {
      font-size: 0.85rem;
      color: #ff8800;
      font-weight: bold;
    }
    .pelicula a {
      display: inline-block;
      background: linear-gradient(135deg, #ff0044, #ff8800);
      color: white;
      padding: 8px 15px;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.9rem;
      box-shadow: 0 0 10px #ff0044;
      transition: all 0.3s;
    }
    .pelicula a:hover {
      transform: scale(1.1);
      box-shadow: 0 0 20px #ff8800;
    }
    .no-result {
      text-align: center;
      margin-top: 40px;
      font-size: 1.1rem;
      color: #888;
    }
  </style>
</head>
<body>

<div class="search-box">
  <form method="GET">
    <input type="text" name="q" placeholder="Buscar película o serie..." value="<?= htmlspecialchars($query) ?>" required>
    <button type="submit">🔍</button>
  </form>
</div>

<div class="resultados">
  <h2>Resultados para: "<?= htmlspecialchars($query) ?>"</h2>

  <?php if (empty($resultados)): ?>
    <p class="no-result">No se encontraron películas ni series con ese título.</p>
  <?php else: ?>
    <?php foreach ($resultados as $m): ?>
      <div class="pelicula">
        <img src="<?= $m['poster_path'] ? 'https://image.tmdb.org/t/p/w200' . $m['poster_path'] : 'assets/no-image.png' ?>" alt="<?= htmlspecialchars($m['title']) ?>">
        <div class="pelicula-info">
          <h3><?= htmlspecialchars($m['title']) ?></h3>
          <p class="tipo"><?= strtoupper($m['type']) ?></p>
          <p><?= htmlspecialchars(mb_substr($m['overview'], 0, 100)) ?>...</p>
          <a href="<?= $m['link'] ?>">Ver ahora</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

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
