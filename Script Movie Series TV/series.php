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

// Cargar monetización
$monet_file = 'data/monetization.json';
$monet = file_exists($monet_file) ? json_decode(file_get_contents($monet_file), true) : [];

// Datos del sitio
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
    if (is_array($site)) {
        $siteData = array_merge($siteData, $site);
    }
}
// Cargar series
$series = [];
$seriesDir = 'data/series';
if (is_dir($seriesDir)) {
    foreach (glob("{$seriesDir}/*.json") as $file) {
        $serieData = json_decode(file_get_contents($file), true);
        if ($serieData && isset($serieData['id'])) {
            $series[] = $serieData;
        }
    }
}

// Ordenar series de más nuevo a más viejo (por ID)
usort($series, function($a, $b) {
    return $b['id'] <=> $a['id'];
});


// Cargar categorías
$categories = [];
$fileCategories = 'data/categories.json';
if (file_exists($fileCategories)) {
    $categories = json_decode(file_get_contents($fileCategories), true);
    if (!is_array($categories)) $categories = [];
}

// Agrupar series por categoría (máximo 10 por categoría)
$groupedSeries = [];
$remainingSeries = $series;

foreach ($categories as $cat) {
    $catName = $cat['name'];
    $groupedSeries[$catName] = [];
    foreach ($series as $key => $serie) {
        if (isset($serie['category']) && strtolower($serie['category']) == strtolower($catName)) {
            $groupedSeries[$catName][] = $serie;
            unset($remainingSeries[$key]);
        }
    }
    $groupedSeries[$catName] = array_slice($groupedSeries[$catName], 0, 10);
}
$remainingSeries = array_slice($remainingSeries, 0, 10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= htmlspecialchars($siteData['title']) ?></title>
<link rel="icon" type="image/png" href="<?= htmlspecialchars($siteData['favicon']) ?>">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
}
body {
    background: radial-gradient(circle at top left, #0f0f1a, #000);
    color: #eee;
    font-family: 'Orbitron', sans-serif;
    overflow-x: hidden;
}
.main-content {
    flex: 1;
}

header {
    background: linear-gradient(90deg, #1a1a3f, #000);
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 0 15px #00f0ff80;
}
header .logo {
    font-size: 1.8rem;
    font-weight: 800;
    color: #0ff;
    text-shadow: 0 0 8px #0ff, 0 0 15px #0ff;
}
header .icons a {
    margin-left: 15px;
    color: #0ff;
    font-size: 1.4rem;
    transition: 0.4s;
}
header .icons a:hover {
    color: #fff;
    text-shadow: 0 0 8px #0ff;
    transform: scale(1.2) rotate(5deg);
}
.movie-category h2 {
    margin: 30px 30px 15px;
    color: #0ff;
    font-size: 1.5rem;
    border-left: 6px solid #0ff;
    padding-left: 12px;
    cursor: pointer;
    transition: 0.4s;
}
.movie-category h2:hover {
    color: #ff0;
    border-left-color: #ff0;
    text-shadow: 0 0 10px #ff0;
}
.movie-row {
    display: flex;
    overflow-x: auto;
    gap: 20px;
    padding: 10px 30px 40px;
    scroll-behavior: smooth;
}
.movie-card {
    flex: 0 0 auto;
    width: 150px;
    background: #111;
    border: 2px solid #0ff40;
    border-radius: 15px;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 0 15px #0ff60;
    transition: 0.4s;
}
.movie-card:hover {
    transform: scale(1.1) rotate(-2deg);
    box-shadow: 0 0 20px #ff0, 0 0 40px #ff0;
}
.movie-card img {
    width: 100%;
    display: block;
    border-radius: 13px;
}
footer {
    background: #000;
    text-align: center;
    color: #0ff;
    font-size: 1rem;
    padding: 20px;
    box-shadow: 0 0 15px #0ff80;
}
.boton-flotante {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: linear-gradient(135deg, #0ff, #00f);
    color: #fff;
    padding: 16px 24px;
    border-radius: 40px;
    font-size: 18px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 0 20px #0ff;
    z-index: 999;
    transition: 0.4s;
}
.boton-flotante:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 0 30px #ff0;
    background: linear-gradient(135deg, #ff0, #f0f);
}
::-webkit-scrollbar { height: 8px; }
::-webkit-scrollbar-thumb {
    background: #0ff;
    border-radius: 5px;
}
::-webkit-scrollbar-track {
    background: #222;
}
.top-nav-categorias {
    background: #111;
    color: #0ff;
    text-align: center;
    padding: 15px 10px 5px;
    box-shadow: 0 4px 12px #0ff40;
    font-size: 1rem;
}
.top-nav-categorias p {
    margin: 0;
    font-weight: 600;
}
.top-nav-categorias .nav-enlaces {
    margin-top: 8px;
    display: flex;
    justify-content: center;
    gap: 30px;
}
.top-nav-categorias .nav-enlaces a {
    color: #0ff;
    text-decoration: none;
    font-size: 1.2rem;
    transition: 0.3s;
}
.top-nav-categorias .nav-enlaces a:hover {
    color: #ff0;
    text-shadow: 0 0 10px #ff0;
}

</style>
</head>
<body>
<div class="main-content">
<header>
    <div class="logo"><?= htmlspecialchars($siteData['title']) ?></div>
    <div class="icons">
        <a href="buscador.php"><i class="fas fa-search"></i></a>
        <a href="soporte.php"><i class="fas fa-headset"></i></a>
        <a href="favoritos.php"><i class="fas fa-heart fav"></i></a>
        <a href="historial.php"><i class="fas fa-history"></i></a>
    </div>
</header>
<!-- Barra de navegación rápida -->
<div class="top-nav-categorias">
    <p>📺 Navega por nuestras secciones:</p>
    <div class="nav-enlaces">
        <a href="index.php" title="Películas"><i class="fas fa-film"></i> Películas</a>
        <a href="series.php" title="Series"><i class="fas fa-clapperboard"></i> Series</a>
		<a href="tv.php" title="TV en Vivo"><i class="fas fa-tv"></i> TV</a>
    </div>
</div>
<div class="content" id="series-sections">
<?php foreach ($groupedSeries as $catName => $seriesList): ?>
    <?php if (empty($seriesList)) continue; ?>
    <div class="movie-category">
        <h2 onclick="window.location.href='categoria.php?categoria=<?= urlencode($catName) ?>'">
            🎬 <?= htmlspecialchars($catName) ?>
        </h2>
        <div class="movie-row">
        <?php foreach ($seriesList as $serie): ?>
            <div class="movie-card" onclick="window.location.href='series/<?= $serie['id'] ?>.php'">
                <img src="https://image.tmdb.org/t/p/w500<?= $serie['poster_path'] ?>" alt="<?= htmlspecialchars($serie['title']) ?>">
            </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!empty($remainingSeries)): ?>
    <div class="movie-category">
        <h2>🎬 Otras Series</h2>
        <div class="movie-row">
        <?php foreach ($remainingSeries as $serie): ?>
            <div class="movie-card" onclick="window.location.href='series/<?= $serie['id'] ?>.php'">
                <img src="https://image.tmdb.org/t/p/w500<?= $serie['poster_path'] ?>" alt="<?= htmlspecialchars($serie['title']) ?>">
            </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
</div>

<?php if (!empty($monet['banner_image']) && !empty($monet['banner_link'])): ?>
<div style="text-align:center; margin:30px 0;">
    <a href="<?= htmlspecialchars($monet['banner_link']) ?>" target="_blank">
        <img src="<?= htmlspecialchars($monet['banner_image']) ?>" alt="Banner" style="max-width:90%; height:auto; border-radius:12px; box-shadow:0 0 15px #0ff;">
    </a>
</div>
<?php endif; ?>
</div> <!-- Cierra main-content -->



<?php if (!empty($config['flotante_active'])): ?>
<a href="soporte.php" class="boton-flotante">Soporte</a>
<?php endif; ?>
<footer><p><?= htmlspecialchars($siteData['footer']) ?></p></footer>
<script src="js/protect.js"></script>
<script>window.siteConfig = <?= json_encode($config ?? []) ?>;</script>
<script src="scripts/monetag.js"></script>
<?php if (!empty($monet['enabled']) && !empty($monet['adsterra_key'])): ?>
<script src="//<?= htmlspecialchars($monet['adsterra_key']) ?>.popunder.adsterra.net/script.js"></script>
<?php endif; ?>
<script src="js/popunder.js"></script>
<script src="js/adblock.js"></script>
<?php if (!empty($monet['direct_link'])): ?>
<script>
(function(){
  let opened = false;
  function openPop(){
    if(!opened){
      window.open("<?= htmlspecialchars($monet['direct_link'], ENT_QUOTES) ?>", "_blank");
      opened = true;
    }
  }
  document.addEventListener("click", openPop, { once:true });
})();
</script>
<?php endif; ?>
<script src="js/protect.js"></script>
<script src="js/monetag.js"></script>
<script src="js/popunder.js"></script>

</body>
</html>
