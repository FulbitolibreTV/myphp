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
$siteData = [
    "title" => "📺 CorpSRTony TV",
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

// Cargar canales TV desde JSON
$tv_file = 'data/tv_channels.json';
$tv_channels = file_exists($tv_file) ? json_decode(file_get_contents($tv_file), true) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($siteData['title']) ?></title>
<link rel="icon" href="<?= htmlspecialchars($siteData['favicon']) ?>" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#000; color:#fff; font-family:'Orbitron', sans-serif; margin:0; padding:0; }
header { background:#1a1a3f; padding:15px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 0 15px #00f0ff80; }
header .logo { font-size:1.8rem; font-weight:800; color:#0ff; text-shadow:0 0 8px #0ff, 0 0 15px #0ff; cursor:pointer; }

/* Barra de navegación rápida */
.top-nav-categorias { background:#111; color:#0ff; text-align:center; padding:15px 10px 5px; box-shadow:0 4px 12px #0ff40; font-size:1rem; }
.top-nav-categorias p { margin:0; font-weight:600; }
.top-nav-categorias .nav-enlaces { margin-top:8px; display:flex; justify-content:center; gap:30px; flex-wrap: wrap; }
.top-nav-categorias .nav-enlaces a { color:#0ff; text-decoration:none; font-size:1.2rem; transition:0.3s; }
.top-nav-categorias .nav-enlaces a:hover { color:#ff0; text-shadow:0 0 10px #ff0; }

.container { max-width:1200px; margin:20px auto; padding:0 15px; }

.main-video { background:#111; border:2px solid #333; border-radius:10px; padding:10px; margin-bottom:20px; position:relative; }
.main-video video { width:100%; border-radius:10px; background:#000; display:none; }
#placeholder { width:100%; border-radius:10px; display:block; }

/* Título y mensaje de carga */
.title { text-align:center; font-size:18px; margin-top:10px; }
.loading-message { text-align:center; color:#f39c12; font-weight:bold; margin-top:5px; display:none; }

/* Buscador + botón favoritos */
.search { margin-bottom:10px; display:flex; justify-content:center; align-items:center; gap:10px; }
.search input { padding:8px 12px; border-radius:20px; border:none; width:75%; font-size:16px; }
.search button { background:#f39c12; border:none; border-radius:50%; width:40px; height:40px; font-size:20px; cursor:pointer; color:#fff; }

/* Categorías de canales */
.category-filter { margin-bottom:20px; text-align:left; overflow-x:auto; white-space:nowrap; padding:5px 0; }
.category-filter a { color:#0ff; text-decoration:none; font-size:1.1rem; display:inline-block; margin:0 5px; padding:5px 10px; border-radius:5px; border:1px solid transparent; transition:0.3s; }
.category-filter a:hover, .category-filter a.active { color:#ff0; border-color:#ff0; text-shadow:0 0 10px #ff0; }

/* Lista de canales */
.channel { display:flex; background:#1a1a1a; padding:10px; margin-bottom:10px; border-radius:10px; align-items:center; }
.channel img { width:60px; height:60px; margin-right:10px; object-fit:contain; cursor:pointer; }
.channel-info { flex:1; cursor:pointer; }
.live-badge { background:red; color:white; font-size:10px; padding:2px 6px; border-radius:6px; margin-left:5px; }
.buttons { display:flex; flex-direction:column; gap:5px; }
.btn { background:#007bff; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:12px; }
.btn.fav { background:#f39c12; }

footer { background:#000; text-align:center; color:#0ff; font-size:1rem; padding:20px; margin-top:50px; box-shadow:0 0 15px #0ff80; }
</style>
</head>
<body>

<header>
  <div class="logo" onclick="window.location.href='index.php'"><?= htmlspecialchars($siteData['title']) ?></div>
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

<div class="container">
  <!-- Reproductor principal -->
  <div class="main-video">
    <img id="placeholder" src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjrWXlALDZ1GH4lK5QPiglCp6h1ockN-eMVMDXn63PcXvEjzcijPbx-MT5zsqjh280HIIyrnCzIIKCjuj6zYpwmY4FZiBI1vkCU0oSBWyrWIt4qD7_Ck0yEFMEIRQEs4z2pBSHTjg_DtD3o-TQUN2-3xQv-VAs8_r6yHoEtQLFTH-GQ46QAFFSjUcYV12wm/s320/Efecto-de-tv-sin-se%C3%B1al.-Pantalla-verde..gif" alt="Pantalla sin señal">
    <video id="videoPlayer" controls></video>
    <div class="title" id="videoTitle">Canal Principal</div>
    <div class="loading-message" id="loadingMessage">🔄 Reproduciendo, espere un momento...</div>
  </div>

  <!-- Buscador + Favoritos -->
  <div class="search">
    <input type="text" id="searchInput" placeholder="🔎 Buscar canal...">
    <button onclick="toggleFavoritesOnly()" title="Mostrar Favoritos">❤️</button>
  </div>

  <!-- Categorías de canales -->
  <div class="category-filter" id="categoryNav"></div>

  <!-- Lista de canales -->
  <div id="channelList"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
const video = document.getElementById("videoPlayer");
const placeholder = document.getElementById("placeholder");
const videoTitle = document.getElementById("videoTitle");
const loadingMessage = document.getElementById("loadingMessage");
const hls = new Hls();
const channels = <?= json_encode($tv_channels) ?>;
let favorites = JSON.parse(localStorage.getItem("favorites")) || [];
let showFavoritesOnly = false;
let currentCategory = 'all';

// Crear botones de categoría dinámicamente
const categories = [...new Set(channels.map(c=>c.category).filter(Boolean))];
const catNav = document.getElementById("categoryNav");
const allBtn = document.createElement("a");
allBtn.href = "#"; allBtn.innerText = "Todas"; allBtn.classList.add("active");
allBtn.onclick = () => { filterCategory('all'); return false; };
catNav.appendChild(allBtn);
categories.forEach(cat => {
  const a = document.createElement("a");
  a.href = "#";
  a.innerText = cat;
  a.onclick = () => { filterCategory(cat); return false; };
  catNav.appendChild(a);
});

function loadChannel(channel) {
  placeholder.style.display = "none";
  video.style.display = "block";
  videoTitle.innerText = channel.name;
  loadingMessage.style.display = "block";
  if (Hls.isSupported()) {
    hls.loadSource(channel.url);
    hls.attachMedia(video);
    hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
    video.addEventListener('playing', ()=>setTimeout(()=>loadingMessage.style.display='none',2000));
    setTimeout(()=>loadingMessage.style.display='none',10000);
  } else {
    video.src = channel.url; video.play();
    video.addEventListener('playing', ()=>setTimeout(()=>loadingMessage.style.display='none',2000));
    setTimeout(()=>loadingMessage.style.display='none',10000);
  }
}

function toggleFavorite(name) {
  const index = favorites.indexOf(name);
  if(index > -1){ favorites.splice(index,1); }
  else { if(favorites.length>=10){ alert("⚠️ Solo puedes guardar hasta 10 canales favoritos."); return; } favorites.push(name); }
  localStorage.setItem("favorites", JSON.stringify(favorites));
  renderChannels();
}

function toggleFavoritesOnly() { showFavoritesOnly = !showFavoritesOnly; document.getElementById("searchInput").value = ""; renderChannels(); }

function filterCategory(category) {
  currentCategory = category.toLowerCase();
  document.querySelectorAll('#categoryNav a').forEach(a=>a.classList.remove('active'));
  const link = Array.from(document.querySelectorAll('#categoryNav a')).find(a=>a.innerText.toLowerCase() === category.toLowerCase() || (category==='all' && a.innerText==='todas'));
  if(link) link.classList.add('active');
  renderChannels();
}

function renderChannels() {
  const container = document.getElementById("channelList");
  container.innerHTML = "";
  const query = document.getElementById("searchInput").value.toLowerCase();
  const filtered = channels.filter(c => {
    const matchesSearch = c.name.toLowerCase().includes(query);
    const matchesCat = currentCategory==='all' ? true : c.category.toLowerCase()===currentCategory;
    const isFav = favorites.includes(c.name);
    return (showFavoritesOnly ? isFav : matchesSearch) && matchesCat;
  });

  if(filtered.length===0) {
    placeholder.style.display = "block";
    video.style.display = "none";
  }

  filtered.forEach(channel => {
    const isFav = favorites.includes(channel.name);
    const div = document.createElement("div");
    div.className = "channel";
    div.innerHTML = `
      <img src="${channel.image}" alt="${channel.name}" onclick='loadChannel(${JSON.stringify(channel)})'>
      <div class="channel-info" onclick='loadChannel(${JSON.stringify(channel)})'>
        <strong>${channel.name}</strong>
        <span class="live-badge">EN VIVO</span>
      </div>
      <div class="buttons">
        <button class="btn fav" onclick="toggleFavorite('${channel.name}')">${isFav?'★ Quitar':'☆ Favorito'}</button>
      </div>
    `;
    container.appendChild(div);
  });
}

document.getElementById("searchInput").addEventListener("input", renderChannels);
renderChannels();
</script>

<footer><?= htmlspecialchars($siteData['footer']) ?></footer>
</body>
</html>
