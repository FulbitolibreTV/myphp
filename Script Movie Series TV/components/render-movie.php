<?php
require_once '../config.php';

// Verificar mantenimiento
$config_file = '../data/site_config.json';
$config = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [];
$modo_mantenimiento = !empty($config['maintenance']);
$usuario_es_admin = check_session();

if ($modo_mantenimiento && !$usuario_es_admin) {
    header('Location: ../mantenimiento.php');
    exit;
}

$id = $movie_id ?? basename($_SERVER['PHP_SELF'], '.php');

$data = json_decode(file_get_contents('../data/movies.json'), true);
$movie = $data[$id] ?? null;

if (!$movie) {
    header('Location: ../404.php');
    exit;
}

// Incrementar views
$views_file = '../data/views.json';
$views = file_exists($views_file) ? json_decode(file_get_contents($views_file), true) : [];
$views[$movie['id']] = ($views[$movie['id']] ?? 0) + 1;
file_put_contents($views_file, json_encode($views, JSON_PRETTY_PRINT));

$siteData = json_decode(file_get_contents('../data/site_info.json'), true);
$trailer_url = !empty($movie['trailer']) ? "https://www.youtube.com/embed/{$movie['trailer']}" : null;

// ✅ Players
$player_links = $movie['players'] ?? [];

// ✅ Monetipelis
$moneti_file = '../data/monetipelis.json';
$moneti = file_exists($moneti_file) ? json_decode(file_get_contents($moneti_file), true) : [];
$moneti_enabled = !empty($moneti['enabled']) && !empty($moneti['video_url']);
$moneti_text = $moneti['text'] ?? '';
$moneti_time = intval($moneti['time_seconds'] ?? 15);
$moneti_video = $moneti['video_url'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($movie['title']) ?> | CorpSRTony Cine</title>
  <link rel="icon" href="../<?= htmlspecialchars($siteData['favicon']) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body {
    font-family: 'Orbitron', sans-serif;
    margin: 0;
    padding: 0;
    background: #0e0e0e;
    color: #fff;
    font-size: 14px;
  }

  h1, h3 {
    font-size: 1.6rem;
    letter-spacing: 1px;
    color: #00ffe0;
    text-shadow: 0 0 5px #00ffe0;
  }

  p {
    font-size: 0.9rem;
    color: #ccc;
  }

  .movie-detail {
    text-align: center;
    padding: 1.5rem;
    max-width: 800px;
    margin: auto;
  }

  .movie-detail img {
    max-width: 220px;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0,255,255,0.3);
    margin-bottom: 1rem;
  }

  .movie-detail iframe {
    width: 100%;
    max-width: 600px;
    height: 280px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,255,255,0.2);
  }

  .btn-ver, .btn-fav {
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    margin: 12px auto;
    display: block;
    font-weight: bold;
    transition: all 0.3s ease;
  }

  .btn-ver {
    background: #ff0040;
    color: #fff;
    box-shadow: 0 0 10px #ff0040;
  }

  .btn-fav {
    background: #0040ff;
    color: #fff;
    box-shadow: 0 0 10px #0040ff;
  }

  .btn-fav.fav-active {
    background: #ff0040;
    box-shadow: 0 0 10px #ff0040;
  }

  .btn-ver:hover, .btn-fav:hover {
    transform: translateY(-2px);
  }

  .modal {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    justify-content: center;
    align-items: center;
    z-index: 999;
  }

  .modal-content {
    background: #111;
    padding: 1rem;
    border-radius: 12px;
    width: 95%;
    max-width: 700px;
    text-align: center;
    position: relative;
    box-shadow: 0 0 20px rgba(0,255,255,0.2);
  }

.modal-content .close {
  position: absolute;
  top: 10px;
  right: 12px;
  font-size: 24px;
  color: #00fff2;
  cursor: pointer;
  z-index: 9999;
  background-color: rgba(0, 0, 0, 0.6);
  padding: 4px 10px;
  border-radius: 50%;
  transition: 0.3s ease;
  border: 1px solid #00fff2;
}

.modal-content .close:hover {
  background-color: #00fff2;
  color: #000;
}

  .video-options button {
    margin: 6px 4px;
    padding: 0.4rem 0.9rem;
    background-color: #0040ff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
    box-shadow: 0 0 8px #0040ff;
    transition: transform 0.3s;
  }

  .video-options button:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 12px #0040ff;
  }

  .video-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    height: 100vh;
    background: #000;
  }

  .video-wrapper {
    position: relative;
    width: 100%;
    max-width: 800px;
    aspect-ratio: 16 / 9;
    background: #000;
    box-shadow: 0 0 20px rgba(0,255,255,0.3);
    border: 1px solid #00ffe0;
    border-radius: 10px;
    overflow: hidden;
  }

  .video-wrapper iframe,
  .video-wrapper video {
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    border: none;
    display: block;
  }

  @media (max-width: 768px) {
    .video-wrapper {
      max-width: 100%;
      border-radius: 0;
      box-shadow: none;
    }
  }

</style>
</head>
<body>

<?php if ($moneti_enabled): ?>
<div id="adModal" class="modal" style="display:flex;">
  <div class="modal-content">
    <h3><?= htmlspecialchars($moneti_text) ?></h3>
    <?php 
      if (strpos($moneti_video, 'youtube.com') !== false || strpos($moneti_video, 'youtu.be') !== false) {
          $video_id = preg_match('/(youtu\.be\/|v=)([^\&\?\/]+)/', $moneti_video, $matches) ? $matches[2] : '';
    ?>
<iframe id="ytVideo" src="https://www.youtube.com/embed/<?= $video_id ?>?enablejsapi=1&autoplay=1&mute=1&controls=0"
 frameborder="0" allow="autoplay; encrypted-media" allowfullscreen style="width:100%;height:300px;border-radius:8px;">
</iframe>
    <?php } else { ?>
      <video id="adVideo" controls autoplay style="width:100%;border-radius:8px;">
        <source src="<?= htmlspecialchars($moneti_video) ?>" type="video/mp4">
      </video>
    <?php } ?>
    <button id="skipBtn" disabled style="margin-top:1rem;background:#f44336;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:8px;font-weight:bold;cursor:not-allowed;">
      Saltar en <?= $moneti_time ?>s
    </button>
  </div>
</div>
<?php endif; ?>

<div class="movie-detail">
  <h1><?= htmlspecialchars($movie['title']) ?></h1>
  
<div style="text-align: center; margin: 20px 0;">
  <a href="../index.php" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; color: white; background: #1a237e; padding: 10px 20px; border-radius: 8px;">
    <i class="fas fa-home"></i> Inicio
  </a>
</div>

  <button id="favBtn" class="btn-fav" onclick="toggleFavorito()">
    <i class="fas fa-heart"></i> <span>Agregar a favoritos</span>
  </button>

  <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" alt="<?= $movie['title'] ?>">
  <p><strong>Fecha de estreno:</strong> <?= $movie['release_date'] ?></p>
  <p><strong>Descripción:</strong> <?= htmlspecialchars($movie['overview']) ?></p>
  <p><strong>Categoría:</strong> <?= htmlspecialchars($movie['category']) ?></p>
<?php if (!empty($movie['genres']) && is_array($movie['genres'])): ?>
  <p><strong>Géneros:</strong> <?= htmlspecialchars(implode(', ', $movie['genres'])) ?></p>
<?php endif; ?>


  <?php if ($trailer_url): ?>
    <h3>Tráiler</h3>
    <iframe src="<?= $trailer_url ?>" frameborder="0" allowfullscreen></iframe>
  <?php endif; ?>

  <div class="video-options">
    <h3>Selecciona un servidor</h3>
    <?php if (!empty($player_links)): ?>
      <?php foreach ($player_links as $link): ?>
        <button onclick="abrirModalConVideo('<?= $link['url'] ?>')">
          <?= htmlspecialchars($link['label']) ?>
        </button>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay enlaces disponibles.</p>
    <?php endif; ?>
  </div>
</div>

<!-- 🎬 MODAL DEL REPRODUCTOR con barra personalizada -->
<div id="videoModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="cerrarModal()">&times;</span>
    <div class="video-wrapper">
      <video id="videoPlayer" controls autoplay playsinline style="--bar-color:#00ffe0;">
        <source src="" type="">
        Tu navegador no soporta video HTML5.
      </video>
    </div>
  </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script src="https://www.youtube.com/iframe_api"></script>
<script>
const movieID = '<?= $movie['id'] ?>';
const historialKey = 'historial_cine';
const favKey = 'favoritos_cine';
let hls = null;

function abrirModal() {
  const video = document.getElementById('playerFrame');
  video.muted = true; // Algunos navegadores exigen muteado para autoplay
  document.getElementById('playerModal').style.display = 'flex';

  if (/Android|iPhone|iPad/i.test(navigator.userAgent)) {
    alert('Para mejor experiencia, gira tu teléfono horizontal y activa pantalla completa.');
    if (video.requestFullscreen) video.requestFullscreen();
  }
}


function cerrarModal() {
  document.getElementById('playerModal').style.display = 'none';
  const video = document.getElementById('playerFrame');
  video.pause();
  if (hls) { hls.destroy(); hls = null; }
}

function setPlayer(url) {
  const video = document.getElementById('playerFrame');
  const source = video.querySelector('source');
  const ext = url.split('?')[0].split('.').pop().toLowerCase();

  if (hls) {
    hls.destroy();
    hls = null;
  }

  video.pause();

  const playerContainer = document.getElementById('playerModal').querySelector('.modal-content');
  video.style.display = 'block';
  const oldIframe = document.getElementById('externalIframe');
  if (oldIframe) oldIframe.remove();

  if (url.includes('embed') || url.includes('iframe')) {
    video.style.display = 'none';
    
    const iframe = document.createElement('iframe');
    iframe.id = 'externalIframe';
    iframe.src = url.includes('autoplay') ? url : url + (url.includes('?') ? '&autoplay=1' : '?autoplay=1');
    iframe.width = '100%';
    iframe.height = '320';
    iframe.allow = 'autoplay; fullscreen';
    iframe.allowFullscreen = true;
    iframe.style.border = 'none';
    iframe.style.borderRadius = '10px';
    iframe.style.boxShadow = '0 0 15px rgba(0,255,255,0.3)';
    playerContainer.appendChild(iframe);
    return;
  }

  if (ext === 'm3u8') {
    source.src = '';
    source.type = '';

    if (Hls.isSupported()) {
      hls = new Hls();
      hls.loadSource(url);
      hls.attachMedia(video);
      hls.on(Hls.Events.MANIFEST_PARSED, function () {
        video.removeAttribute('poster');
        video.play();
      });
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = url;
      video.removeAttribute('poster');
      video.load();
      video.play();
    } else {
      alert('Tu navegador no soporta reproducción HLS');
    }

  } else {
    source.src = url;

    const mimeMap = {
      'mp4': 'video/mp4',
      'webm': 'video/webm',
      'avi': 'video/x-msvideo',
      'mkv': 'video/x-matroska',
      'mov': 'video/quicktime'
    };
    source.type = mimeMap[ext] || 'video/mp4';

    video.removeAttribute('poster');  // quitar imagen previa
    video.load();
    video.play().catch(err => {
      console.error('Error al reproducir:', err);
      alert('No se pudo reproducir el video. Intenta con otro servidor.');
    });
  }
}

function guardarHistorial() {
  let historial = JSON.parse(localStorage.getItem(historialKey) || '[]');
  historial = historial.filter(mid => mid !== movieID);
  historial.unshift(movieID);
  if (historial.length > 15) historial = historial.slice(0, 15);
  localStorage.setItem(historialKey, JSON.stringify(historial));
}

function toggleFavorito() {
  let favoritos = JSON.parse(localStorage.getItem(favKey) || '[]');
  const idx = favoritos.indexOf(movieID);
  if (idx > -1) {
    favoritos.splice(idx, 1);
    document.getElementById('favBtn').classList.remove('fav-active');
    document.getElementById('favBtn').querySelector('span').innerText = 'Agregar a favoritos';
  } else {
    favoritos.push(movieID);
    document.getElementById('favBtn').classList.add('fav-active');
    document.getElementById('favBtn').querySelector('span').innerText = 'Quitar de favoritos';
  }
  localStorage.setItem(favKey, JSON.stringify(favoritos));
}

document.addEventListener('DOMContentLoaded', () => {
  guardarHistorial();
  const favoritos = JSON.parse(localStorage.getItem(favKey) || '[]');
  if (favoritos.includes(movieID)) {
    document.getElementById('favBtn').classList.add('fav-active');
    document.getElementById('favBtn').querySelector('span').innerText = 'Quitar de favoritos';
  }

  // 🚀 Control para mostrar anuncio cada 10 min
  const lastTime = localStorage.getItem('last_ad_time');
  const now = Date.now();
  if (lastTime && (now - parseInt(lastTime)) < (10 * 60 * 1000)) {
    document.getElementById('adModal').style.display = "none";
  }
});

let ytPlayer;
let countdown = <?= $moneti_time ?>;
let interval = null;
let playing = false;
const skipBtn = document.getElementById('skipBtn');

function actualizarBoton() {
  skipBtn.innerText = `Saltar en ${countdown}s`;
  if (countdown <= 0) {
    clearInterval(interval);
    skipBtn.innerText = "Saltar anuncio";
    skipBtn.disabled = false;
    skipBtn.style.cursor = "pointer";
  }
}

function startCountdown() {
  if (!interval) {
    interval = setInterval(() => {
      if (playing) {
        countdown--;
        actualizarBoton();
      }
      if (countdown <= 0) {
        clearInterval(interval);
      }
    }, 1000);
  }
}

function onYouTubeIframeAPIReady() {
  ytPlayer = new YT.Player('ytVideo', {
    events: {
      'onStateChange': function(event) {
        if (event.data === YT.PlayerState.PLAYING) {
          playing = true;
          startCountdown();
        } else if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
          playing = false;
        }
      }
    }
  });
}

skipBtn.addEventListener('click', () => {
  if (countdown <= 0) {
    localStorage.setItem('last_ad_time', Date.now());
    document.getElementById('adModal').style.display = "none";
    if (ytPlayer) ytPlayer.stopVideo();
  }
});
</script>
<script>
function abrirModalConVideo(videoUrl) {
  const modal = document.getElementById("videoModal");
  const wrapper = modal.querySelector(".video-wrapper");
  const player = document.getElementById("videoPlayer");

  // Limpiar reproducción previa
  if (hls) { hls.destroy(); hls = null; }
  player.pause();
  player.src = "";
  player.load();

  // Eliminar iframe anterior si existe
  const oldIframe = document.getElementById("embedIframe");
  if (oldIframe) oldIframe.remove();

  const ext = videoUrl.split('?')[0].split('.').pop().toLowerCase();

  // ⛔️ Si es embebido tipo iframe (Filemoon, Pixeldrain, etc.)
  if (
    videoUrl.includes("embed") || 
    videoUrl.includes("filemoon.to") || 
    videoUrl.includes("pixeldrain.com") || 
    videoUrl.includes("iframe")
  ) {
    player.style.display = "none"; // Oculta el reproductor nativo

    const iframe = document.createElement("iframe");
    iframe.id = "embedIframe";
    iframe.src = videoUrl.includes("autoplay") ? videoUrl : videoUrl + (videoUrl.includes("?") ? "&autoplay=1" : "?autoplay=1");
    iframe.allow = "autoplay; fullscreen";
    iframe.allowFullscreen = true;
    iframe.frameBorder = "0";
    iframe.style = "width:100%;height:100%;border:none;border-radius:10px;";
    
    wrapper.appendChild(iframe);
  }
  // ✅ HLS
  else if (ext === "m3u8") {
    player.style.display = "block";
    if (Hls.isSupported()) {
      hls = new Hls();
      hls.loadSource(videoUrl);
      hls.attachMedia(player);
      hls.on(Hls.Events.MANIFEST_PARSED, () => player.play());
    } else if (player.canPlayType("application/vnd.apple.mpegurl")) {
      player.src = videoUrl;
      player.play();
    } else {
      alert("Este navegador no soporta HLS (.m3u8).");
      return;
    }
  }
  // ✅ MP4, WEBM, etc.
  else if (["mp4", "webm", "mov"].includes(ext)) {
    player.style.display = "block";
    player.src = videoUrl;
    player.play().catch(err => alert("Error de reproducción: " + err.message));
  }
  // ⛔️ No soportado
  else {
    alert("Formato no soportado. Convierte el archivo a MP4 o usa un servidor compatible.");
    return;
  }

  modal.style.display = "flex";
}


function cerrarModal() {
  const modal = document.getElementById("videoModal");
  const player = document.getElementById("videoPlayer");
  modal.style.display = "none";
  player.pause();
  player.src = "";
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const video = document.getElementById('videoPlayer');

  // Al hacer play, intenta poner en pantalla completa si está disponible
  video.addEventListener('play', function () {
    if (video.requestFullscreen) {
      video.requestFullscreen();
    } else if (video.webkitEnterFullscreen) {
      video.webkitEnterFullscreen(); // iOS
    } else if (video.mozRequestFullScreen) {
      video.mozRequestFullScreen();
    } else if (video.msRequestFullscreen) {
      video.msRequestFullscreen();
    }
  });
});
</script>

<script src="../js/adblock.js"></script>
</body>
</html>
