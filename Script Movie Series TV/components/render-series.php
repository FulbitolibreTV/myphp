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

$id = $series_id ?? basename($_SERVER['PHP_SELF'], '.php');
$data_path = "../data/series/{$id}.json";
$series = file_exists($data_path) ? json_decode(file_get_contents($data_path), true) : null;

if (!$series) {
    header('Location: ../404.php');
    exit;
}

// Incrementar views
$views_file = '../data/views.json';
$views = file_exists($views_file) ? json_decode(file_get_contents($views_file), true) : [];
$views[$series['id']] = ($views[$series['id']] ?? 0) + 1;
file_put_contents($views_file, json_encode($views, JSON_PRETTY_PRINT));

$siteData = json_decode(file_get_contents('../data/site_info.json'), true);
$trailer_url = !empty($series['trailer']) ? "https://www.youtube.com/embed/{$series['trailer']}" : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
  <title><?= htmlspecialchars($series['title']) ?> | CorpSRTony Cine</title>
  <link rel="icon" href="../<?= htmlspecialchars($siteData['favicon']) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
  <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      width: 100%;
      height: 100%;
      overflow-x: hidden;
    }

    body {
      font-family: 'Orbitron', sans-serif;
      background: #0e0e0e;
      color: #fff;
      line-height: 1.6;
    }

    /* Contenedor principal */
    .serie-detail {
      max-width: 1200px;
      margin: 0 auto;
      padding: 1rem;
    }

    /* Títulos */
    h1, h3, h4 {
      color: #00ffe0;
      text-align: center;
      margin: 20px 0;
      font-weight: 600;
    }

    h1 {
      font-size: clamp(1.8rem, 4vw, 3rem);
    }

    h3 {
      font-size: clamp(1.4rem, 3vw, 2rem);
    }

    h4 {
      font-size: clamp(1.2rem, 2.5vw, 1.6rem);
      color: #fff;
      text-align: left;
      margin: 15px 0 10px 0;
    }

    /* Imagen del poster */
    .poster-container {
      text-align: center;
      margin: 2rem 0;
    }

    .poster-container img {
      max-width: 100%;
      width: auto;
      height: auto;
      max-height: 400px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 255, 224, 0.3);
      transition: transform 0.3s ease;
    }

    .poster-container img:hover {
      transform: scale(1.05);
    }

    /* Información de la serie */
    .serie-info {
      background: rgba(26, 26, 26, 0.8);
      padding: 1.5rem;
      border-radius: 15px;
      margin: 2rem 0;
      backdrop-filter: blur(10px);
    }

    .serie-info p {
      margin: 10px 0;
      font-size: clamp(0.9rem, 2vw, 1.1rem);
    }

    .serie-info strong {
      color: #00ffe0;
    }

    /* Botones */
    .btn {
      background: linear-gradient(45deg, #ff0040, #ff4080);
      color: white;
      padding: 12px 20px;
      text-align: center;
      display: inline-block;
      border-radius: 25px;
      cursor: pointer;
      border: none;
      font-family: 'Orbitron', sans-serif;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(255, 0, 64, 0.4);
      font-size: clamp(0.9rem, 2vw, 1rem);
      min-width: 150px;
    }

    .btn:hover, .btn:focus {
      background: linear-gradient(45deg, #ff4080, #ff0040);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(255, 0, 64, 0.6);
      outline: 2px solid #00ffe0;
    }

    .btn:active {
      transform: translateY(0);
    }

    #btnFavorito {
      margin: 20px auto;
      display: block;
    }

    /* Contenedor de temporadas */
    .season-container {
      background: rgba(26, 26, 26, 0.6);
      margin: 2rem 0;
      padding: 1.5rem;
      border-radius: 15px;
      backdrop-filter: blur(10px);
    }

    /* Episodes */
    .episodes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }

    .episode {
      background: rgba(42, 42, 42, 0.8);
      padding: 1rem;
      border-radius: 12px;
      border: 2px solid transparent;
      transition: all 0.3s ease;
      backdrop-filter: blur(5px);
      position: relative;
    }

    .episode:hover, .episode:focus {
      border-color: #00ffe0;
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 255, 224, 0.2);
    }

    .episode strong {
      color: #00ffe0;
      font-size: 1.1rem;
      display: block;
      margin-bottom: 10px;
    }

    /* Sistema de episodios vistos */
    .episode-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .watched-toggle {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
      user-select: none;
    }

    .watched-toggle:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .watched-checkbox {
      appearance: none;
      width: 20px;
      height: 20px;
      border: 2px solid #666;
      border-radius: 4px;
      position: relative;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .watched-checkbox:checked {
      background: #00ffe0;
      border-color: #00ffe0;
    }

    .watched-checkbox:checked::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: #000;
      font-weight: bold;
      font-size: 14px;
    }

    .watched-label {
      font-size: 0.9rem;
      color: #ccc;
      cursor: pointer;
    }

    .episode.watched {
      background: rgba(0, 255, 224, 0.1);
      border-color: rgba(0, 255, 224, 0.3);
    }

    .episode.watched strong {
      color: #00ccb3;
    }

    /* Navegación con teclado/control remoto */
    .focusable {
      outline: none;
      transition: all 0.3s ease;
    }

    .focusable:focus,
    .focusable.focused {
      outline: 3px solid #00ffe0 !important;
      outline-offset: 3px;
      box-shadow: 0 0 20px rgba(0, 255, 224, 0.8);
      transform: scale(1.02);
      z-index: 10;
    }

    .btn.focused,
    .btn:focus {
      outline: 3px solid #00ffe0 !important;
      outline-offset: 3px;
      box-shadow: 0 0 20px rgba(0, 255, 224, 0.8);
      transform: translateY(-2px) scale(1.05);
    }

    /* Indicador de navegación */
    .navigation-hint {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: rgba(0, 0, 0, 0.8);
      color: #00ffe0;
      padding: 10px 15px;
      border-radius: 10px;
      font-size: 0.8rem;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(0, 255, 224, 0.3);
      z-index: 1000;
      display: none;
    }

    .navigation-hint.show {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Progreso de temporada */
    .season-progress {
      margin-top: 15px;
      padding: 10px;
      background: rgba(0, 0, 0, 0.3);
      border-radius: 8px;
      text-align: center;
    }

    .progress-bar {
      width: 100%;
      height: 8px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 4px;
      overflow: hidden;
      margin: 10px 0;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #00ffe0, #00ccb3);
      transition: width 0.5s ease;
      width: 0%;
    }

    .progress-text {
      font-size: 0.9rem;
      color: #00ffe0;
    }

    /* Trailer */
    .trailer-container {
      margin: 2rem 0;
      text-align: center;
    }

    .trailer-container iframe {
      width: 100%;
      max-width: 800px;
      height: 315px;
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    /* Modal del reproductor - PANTALLA COMPLETA */
    .video-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      width: 100vw;
      height: 100vh;
      background: #000;
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    .video-content {
      position: relative;
      width: 100vw;
      height: 100vh;
      background: #000;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .close-btn {
      position: fixed;
      top: 20px;
      right: 20px;
      background: rgba(255, 0, 64, 0.9);
      color: #fff;
      border: none;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
      z-index: 10001;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(10px);
    }

    .close-btn:hover, .close-btn:focus {
      background: rgba(255, 0, 64, 1);
      transform: scale(1.1);
      outline: 2px solid #00ffe0;
    }

    /* Contenedor del reproductor - PANTALLA COMPLETA */
    #playerContainer {
      width: 100vw;
      height: 100vh;
      position: relative;
      background: #000;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* Video.js PANTALLA COMPLETA */
    .video-js {
      width: 100vw !important;
      height: 100vh !important;
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #000 !important;
    }

    .video-js .vjs-tech {
      width: 100vw !important;
      height: 100vh !important;
      position: absolute;
      top: 0;
      left: 0;
      object-fit: contain !important;
      background: #000;
    }

    .video-js.vjs-fullscreen {
      width: 100vw !important;
      height: 100vh !important;
    }

    .video-js.vjs-fullscreen .vjs-tech {
      width: 100vw !important;
      height: 100vh !important;
    }

    /* Iframe PANTALLA COMPLETA */
    .iframe-container {
      width: 100vw;
      height: 100vh;
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: #000;
    }

    .iframe-container iframe {
      width: 100vw !important;
      height: 100vh !important;
      border: none;
      position: absolute;
      top: 0;
      left: 0;
      background: #000;
    }

    /* Responsive Design */
    
    /* Móviles pequeños */
    @media (max-width: 480px) {
      .serie-detail {
        padding: 0.5rem;
      }

      .serie-info {
        padding: 1rem;
      }

      .season-container {
        padding: 1rem;
      }

      .episodes-grid {
        grid-template-columns: 1fr;
        gap: 0.8rem;
      }

      .btn {
        padding: 10px 16px;
        min-width: 120px;
      }

      .trailer-container iframe {
        height: 200px;
      }

      .close-btn {
        width: 40px;
        height: 40px;
        font-size: 18px;
        top: 15px;
        right: 15px;
      }

      .episode-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .watched-toggle {
        font-size: 0.8rem;
      }

      .navigation-hint {
        bottom: 10px;
        right: 10px;
        font-size: 0.7rem;
        padding: 8px 12px;
      }

      /* Video en móviles - pantalla completa */
      .video-js,
      .video-js .vjs-tech,
      .iframe-container,
      .iframe-container iframe {
        width: 100vw !important;
        height: 100vh !important;
      }
    }

    /* Tablets */
    @media (min-width: 481px) and (max-width: 768px) {
      .episodes-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      }

      .trailer-container iframe {
        height: 280px;
      }

      .close-btn {
        width: 45px;
        height: 45px;
        font-size: 20px;
      }
    }

    /* Tablets grandes y laptops */
    @media (min-width: 769px) and (max-width: 1024px) {
      .episodes-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      }

      .trailer-container iframe {
        height: 350px;
      }
    }

    /* Desktop */
    @media (min-width: 1025px) and (max-width: 1399px) {
      .episodes-grid {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      }

      .trailer-container iframe {
        height: 450px;
      }
    }

    /* Smart TV y pantallas muy grandes */
    @media (min-width: 1400px) {
      body {
        font-size: 1.2rem;
      }

      .serie-detail {
        max-width: 1400px;
      }

      .btn {
        padding: 15px 25px;
        font-size: 1.1rem;
      }

      .close-btn {
        width: 60px;
        height: 60px;
        font-size: 28px;
        top: 30px;
        right: 30px;
      }

      .episodes-grid {
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      }

      .navigation-hint {
        font-size: 1rem;
        padding: 15px 20px;
      }
    }

    /* Orientación landscape en móviles */
    @media (max-height: 500px) and (orientation: landscape) {
      h1 {
        font-size: 1.5rem;
        margin: 10px 0;
      }

      .serie-info {
        padding: 1rem;
      }

      .season-container {
        padding: 1rem;
      }

      .close-btn {
        top: 10px;
        right: 10px;
        width: 35px;
        height: 35px;
        font-size: 16px;
      }
    }

    /* TV Box y Smart TV específico */
    @media (min-width: 1920px) {
      .close-btn {
        width: 70px;
        height: 70px;
        font-size: 32px;
        top: 40px;
        right: 40px;
      }

      .btn {
        padding: 18px 30px;
        font-size: 1.3rem;
        min-width: 200px;
      }

      h1 {
        font-size: 4rem;
      }

      h3 {
        font-size: 2.5rem;
      }

      .serie-info p {
        font-size: 1.4rem;
      }
    }

    /* Focus para navegación con control remoto */
    .btn:focus,
    .episode:focus,
    .close-btn:focus,
    .watched-toggle:focus {
      outline: 3px solid #00ffe0 !important;
      outline-offset: 3px;
      box-shadow: 0 0 20px rgba(0, 255, 224, 0.8);
    }

    /* Loading spinner */
    .loading {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100vw;
      height: 100vh;
      background: #000;
    }

    .loading::after {
      content: '';
      width: 60px;
      height: 60px;
      border: 6px solid rgba(0, 255, 224, 0.3);
      border-radius: 50%;
      border-top-color: #00ffe0;
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Ocultar scrollbar pero mantener funcionalidad */
    body::-webkit-scrollbar {
      width: 8px;
    }

    body::-webkit-scrollbar-track {
      background: #0e0e0e;
    }

    body::-webkit-scrollbar-thumb {
      background: #00ffe0;
      border-radius: 4px;
    }

    body::-webkit-scrollbar-thumb:hover {
      background: #00ccb3;
    }

    /* Ocultar scroll cuando modal está abierto */
    body.modal-open {
      overflow: hidden !important;
    }

    /* Controles de Video.js personalizados para pantalla completa */
    .vjs-control-bar {
      background: linear-gradient(transparent, rgba(0,0,0,0.8)) !important;
    }

    .vjs-big-play-button {
      background: rgba(0, 255, 224, 0.8) !important;
      border: none !important;
      border-radius: 50% !important;
    }

    .vjs-big-play-button:hover {
      background: rgba(0, 255, 224, 1) !important;
    }

    /* Asegurar que no haya márgenes ni padding en modal */
    .video-modal * {
      margin: 0;
      padding: 0;
    }

    /* Estilos específicos para Smart TV */
    .smart-tv .episode {
      padding: 1.5rem;
    }

    .smart-tv .btn {
      font-size: 1.2rem;
      padding: 15px 25px;
    }

    .smart-tv .watched-toggle {
      padding: 8px 15px;
    }

    .smart-tv .watched-checkbox {
      width: 24px;
      height: 24px;
    }
  </style>
</head>
<body>

<div class="serie-detail">
  <h1><?= htmlspecialchars($series['title']) ?></h1>
  <div style="text-align: center; margin: 20px 0;">
    <a href="../series.php" class="focusable" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; color: white; background: #1a237e; padding: 10px 20px; border-radius: 8px;" tabindex="0">
      <i class="fas fa-home"></i> Inicio
    </a>
  </div>

  <button id="btnFavorito" class="btn focusable" tabindex="0"></button>
  
  <div class="poster-container">
    <img src="https://image.tmdb.org/t/p/w500<?= $series['poster_path'] ?>" alt="Poster" loading="lazy">
  </div>
<div class="serie-info">
  <p><strong>Fecha de estreno:</strong> <?= htmlspecialchars($series['release_date']) ?></p>
  <p><strong>Categoría:</strong> <?= htmlspecialchars($series['category']) ?></p>
  <p><strong>Descripción:</strong> <?= htmlspecialchars($series['overview']) ?></p>
  <?php if (!empty($series['genres']) && is_array($series['genres'])): ?>
    <p><strong>Géneros:</strong>
      <?= htmlspecialchars(implode(', ', $series['genres'])) ?>
    </p>
  <?php else: ?>
    <p><strong>Géneros:</strong> No disponible</p>
  <?php endif; ?>
</div>


  <?php if ($trailer_url): ?>
    <h3>Tráiler</h3>
    <div class="trailer-container">
      <iframe src="<?= $trailer_url ?>" allowfullscreen loading="lazy" class="focusable" tabindex="0"></iframe>
    </div>
  <?php endif; ?>

  <?php if (!empty($series['seasons'])): ?>
    <h3>Temporadas</h3>
    <?php foreach ($series['seasons'] as $season_num => $episodios): ?>
      <div class="season-container">
        <h4>Temporada <?= htmlspecialchars($season_num) ?></h4>
        <div class="season-progress">
          <div class="progress-text">Progreso: <span id="progress-text-<?= $season_num ?>">0 de <?= count($episodios) ?> episodios vistos</span></div>
          <div class="progress-bar">
            <div class="progress-fill" id="progress-bar-<?= $season_num ?>"></div>
          </div>
        </div>
        <div class="episodes-grid">
          <?php foreach ($episodios as $ep_num => $ep): ?>
            <div class="episode focusable" tabindex="0" data-season="<?= $season_num ?>" data-episode="<?= $ep_num ?>">
              <div class="episode-header">
                <strong>Episodio <?= htmlspecialchars($ep_num) ?></strong>
                <div class="watched-toggle focusable" tabindex="0">
                  <input type="checkbox" class="watched-checkbox" id="watched-<?= $season_num ?>-<?= $ep_num ?>" data-season="<?= $season_num ?>" data-episode="<?= $ep_num ?>">
                  <label for="watched-<?= $season_num ?>-<?= $ep_num ?>" class="watched-label">Visto</label>
                </div>
              </div>
              <button class="btn focusable" onclick="openPlayer('<?= htmlspecialchars($ep['url']) ?>', <?= $season_num ?>, <?= $ep_num ?>)" tabindex="0">Ver episodio</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="serie-info">
      <p>No hay temporadas disponibles.</p>
    </div>
  <?php endif; ?>
</div>

<!-- Modal del reproductor -->
<div id="videoModal" class="video-modal">
  <div class="video-content">
    <button class="close-btn focusable" onclick="closePlayer()" aria-label="Cerrar reproductor" tabindex="0">✕</button>
    <div id="playerContainer"></div>
  </div>
</div>

<!-- Indicador de navegación -->
<div id="navigationHint" class="navigation-hint">
  <div>🎮 Usa las flechas para navegar</div>
  <div>▶️ Enter para seleccionar</div>
  <div>⬅️ Escape para salir</div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
// Variables globales
let currentPlayer = null;
let isModalOpen = false;
let focusableElements = [];
let currentFocusIndex = 0;
const seriesId = "<?= $series['id'] ?>";

// Sistema de navegación con teclado/control remoto
class NavigationManager {
  constructor() {
    this.focusableElements = [];
    this.currentIndex = 0;
    this.isNavigating = false;
    this.init();
  }

  init() {
    this.updateFocusableElements();
    this.setupEventListeners();
    this.showNavigationHint();
  }

  updateFocusableElements() {
    this.focusableElements = Array.from(document.querySelectorAll('.focusable:not([disabled])'));
    this.focusableElements.forEach((el, index) => {
      el.setAttribute('data-nav-index', index);
    });
  }

  showNavigationHint() {
    const hint = document.getElementById('navigationHint');
    if (this.isSmartTV() || this.isTVBox()) {
      hint.classList.add('show');
      setTimeout(() => {
        hint.classList.remove('show');
      }, 5000);
    }
  }

  isSmartTV() {
    const userAgent = navigator.userAgent.toLowerCase();
    const tvKeywords = ['tizen', 'webos', 'smart-tv', 'smarttv', 'googletv', 'android tv', 'roku'];
    return tvKeywords.some(keyword => userAgent.includes(keyword)) || window.innerWidth >= 1920;
  }

  isTVBox() {
    return window.innerWidth >= 1200 && window.screen.width >= 1200;
  }

  focusElement(index) {
    if (this.focusableElements[index]) {
      // Remover focus anterior
      this.focusableElements.forEach(el => {
        el.classList.remove('focused');
        el.blur();
      });

      // Aplicar nuevo focus
      const element = this.focusableElements[index];
      element.classList.add('focused');
      element.focus();
      
      // Scroll suave al elemento
      element.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'center'
      });

      this.currentIndex = index;
    }
  }

  navigate(direction) {
    this.isNavigating = true;
    let newIndex = this.currentIndex;

    switch (direction) {
      case 'up':
        newIndex = Math.max(0, this.currentIndex - 1);
        break;
      case 'down':
        newIndex = Math.min(this.focusableElements.length - 1, this.currentIndex + 1);
        break;
      case 'left':
        // Buscar elemento en la misma fila hacia la izquierda
        newIndex = this.findElementInDirection('left');
        break;
      case 'right':
        // Buscar elemento en la misma fila hacia la derecha
        newIndex = this.findElementInDirection('right');
        break;
    }

    this.focusElement(newIndex);
    setTimeout(() => {
      this.isNavigating = false;
    }, 100);
  }

  findElementInDirection(direction) {
    const currentEl = this.focusableElements[this.currentIndex];
    if (!currentEl) return this.currentIndex;

    const currentRect = currentEl.getBoundingClientRect();
    const currentRow = Math.floor(currentRect.top);
    let candidates = [];

    this.focusableElements.forEach((el, index) => {
      const rect = el.getBoundingClientRect();
      const elementRow = Math.floor(rect.top);

      // Elementos en la misma fila (con tolerancia de 50px)
      if (Math.abs(elementRow - currentRow) <= 50) {
        if (direction === 'left' && rect.left < currentRect.left) {
          candidates.push({ index, distance: currentRect.left - rect.left });
        } else if (direction === 'right' && rect.left > currentRect.left) {
          candidates.push({ index, distance: rect.left - currentRect.left });
        }
      }
    });

    if (candidates.length > 0) {
      // Ordenar por distancia y retornar el más cercano
      candidates.sort((a, b) => a.distance - b.distance);
      return candidates[0].index;
    }

    return this.currentIndex;
  }

  setupEventListeners() {
    document.addEventListener('keydown', (e) => {
      if (this.isNavigating) return;

      // Solo procesar navegación si no estamos en el modal o en un input
      if (isModalOpen || document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') {
        return;
      }

      switch (e.key) {
        case 'ArrowUp':
          e.preventDefault();
          this.navigate('up');
          break;
        case 'ArrowDown':
          e.preventDefault();
          this.navigate('down');
          break;
        case 'ArrowLeft':
          e.preventDefault();
          this.navigate('left');
          break;
        case 'ArrowRight':
          e.preventDefault();
          this.navigate('right');
          break;
        case 'Enter':
        case ' ':
          e.preventDefault();
          this.activateCurrentElement();
          break;
        case 'Escape':
          if (isModalOpen) {
            e.preventDefault();
            closePlayer();
          }
          break;
      }
    });

    // Actualizar elementos focusables cuando el DOM cambie
    const observer = new MutationObserver(() => {
      this.updateFocusableElements();
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }

  activateCurrentElement() {
    const element = this.focusableElements[this.currentIndex];
    if (element) {
      if (element.tagName === 'BUTTON' || element.tagName === 'A') {
        element.click();
      } else if (element.classList.contains('watched-toggle')) {
        const checkbox = element.querySelector('.watched-checkbox');
        if (checkbox) {
          checkbox.click();
        }
      } else if (element.classList.contains('episode')) {
        const button = element.querySelector('button');
        if (button) {
          button.click();
        }
      }
    }
  }
}

// Sistema de episodios vistos
class WatchedEpisodesManager {
  constructor(seriesId) {
    this.seriesId = seriesId;
    this.storageKey = `watched_episodes_${seriesId}`;
    this.watchedEpisodes = this.loadWatchedEpisodes();
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.updateAllEpisodes();
    this.updateAllSeasonProgress();
  }

  loadWatchedEpisodes() {
    try {
      const stored = localStorage.getItem(this.storageKey);
      return stored ? JSON.parse(stored) : {};
    } catch (e) {
      console.log('Error al cargar episodios vistos:', e);
      return {};
    }
  }

  saveWatchedEpisodes() {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(this.watchedEpisodes));
    } catch (e) {
      console.log('Error al guardar episodios vistos:', e);
    }
  }

  setupEventListeners() {
    document.addEventListener('change', (e) => {
      if (e.target.classList.contains('watched-checkbox')) {
        const season = e.target.dataset.season;
        const episode = e.target.dataset.episode;
        this.toggleEpisode(season, episode, e.target.checked);
      }
    });

    // También permitir clic en el toggle completo
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('watched-toggle') || e.target.classList.contains('watched-label')) {
        const toggle = e.target.classList.contains('watched-toggle') ? e.target : e.target.parentElement;
        const checkbox = toggle.querySelector('.watched-checkbox');
        if (checkbox) {
          checkbox.checked = !checkbox.checked;
          const season = checkbox.dataset.season;
          const episode = checkbox.dataset.episode;
          this.toggleEpisode(season, episode, checkbox.checked);
        }
      }
    });
  }

  toggleEpisode(season, episode, watched) {
    const key = `${season}-${episode}`;
    
    if (watched) {
      this.watchedEpisodes[key] = {
        season: season,
        episode: episode,
        watchedAt: new Date().toISOString()
      };
    } else {
      delete this.watchedEpisodes[key];
    }

    this.saveWatchedEpisodes();
    this.updateEpisodeDisplay(season, episode, watched);
    this.updateSeasonProgress(season);
  }

  updateEpisodeDisplay(season, episode, watched) {
    const episodeElement = document.querySelector(`[data-season="${season}"][data-episode="${episode}"]`);
    const checkbox = document.getElementById(`watched-${season}-${episode}`);
    
    if (episodeElement && checkbox) {
      checkbox.checked = watched;
      
      if (watched) {
        episodeElement.classList.add('watched');
      } else {
        episodeElement.classList.remove('watched');
      }
    }
  }

  updateAllEpisodes() {
    Object.keys(this.watchedEpisodes).forEach(key => {
      const episode = this.watchedEpisodes[key];
      this.updateEpisodeDisplay(episode.season, episode.episode, true);
    });
  }

  updateSeasonProgress(season) {
    const episodesInSeason = document.querySelectorAll(`[data-season="${season}"]`);
    const watchedInSeason = Array.from(episodesInSeason).filter(ep => ep.classList.contains('watched'));
    
    const total = episodesInSeason.length;
    const watched = watchedInSeason.length;
    const percentage = total > 0 ? (watched / total) * 100 : 0;

    const progressBar = document.getElementById(`progress-bar-${season}`);
    const progressText = document.getElementById(`progress-text-${season}`);

    if (progressBar) {
      progressBar.style.width = `${percentage}%`;
    }

    if (progressText) {
      progressText.textContent = `${watched} de ${total} episodios vistos`;
    }
  }

  updateAllSeasonProgress() {
    const seasons = [...new Set(Array.from(document.querySelectorAll('[data-season]')).map(el => el.dataset.season))];
    seasons.forEach(season => {
      this.updateSeasonProgress(season);
    });
  }

  markAsWatched(season, episode) {
    this.toggleEpisode(season, episode, true);
  }
}

// Detectar tipo de dispositivo
function detectDevice() {
  const width = window.innerWidth;
  const height = window.innerHeight;
  const userAgent = navigator.userAgent.toLowerCase();
  
  if (width >= 1920 || height >= 1080) {
    return 'tv'; // Smart TV / TV Box
  } else if (width >= 1024) {
    return 'desktop';
  } else if (width >= 768) {
    return 'tablet';
  } else {
    return 'mobile';
  }
}

// Guardar en historial
function guardarEnHistorial() {
  try {
    const historial = JSON.parse(localStorage.getItem('historial_cine') || '[]');
    const id = seriesId;

    const index = historial.indexOf(id);
    if (index > -1) historial.splice(index, 1);
    historial.unshift(id);

    localStorage.setItem('historial_cine', JSON.stringify(historial.slice(0, 10)));
  } catch (e) {
    console.log('Error al guardar historial:', e);
  }
}

// Manejo de favoritos
function manejarFavoritos() {
  const id = seriesId;
  const btn = document.getElementById('btnFavorito');
  
  try {
    let favoritos = JSON.parse(localStorage.getItem('favoritos_cine') || '[]');

    function actualizarTexto() {
      if (favoritos.includes(id)) {
        btn.textContent = "💔 Quitar de Favoritos";
        btn.setAttribute('aria-label', 'Quitar de favoritos');
      } else {
        btn.textContent = "❤️ Agregar a Favoritos";
        btn.setAttribute('aria-label', 'Agregar a favoritos');
      }
    }

    actualizarTexto();

    btn.addEventListener('click', () => {
      if (favoritos.includes(id)) {
        favoritos = favoritos.filter(fav => fav !== id);
      } else {
        favoritos.unshift(id);
      }

      localStorage.setItem('favoritos_cine', JSON.stringify(favoritos));
      actualizarTexto();
    });
  } catch (e) {
    console.log('Error al manejar favoritos:', e);
    btn.textContent = "❤️ Agregar a Favoritos";
  }
}

// Pantalla completa real
function enterFullscreen(element) {
  if (element.requestFullscreen) {
    element.requestFullscreen();
  } else if (element.webkitRequestFullscreen) {
    element.webkitRequestFullscreen();
  } else if (element.mozRequestFullScreen) {
    element.mozRequestFullScreen();
  } else if (element.msRequestFullscreen) {
    element.msRequestFullscreen();
  }
}

// Salir de pantalla completa
function exitFullscreen() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.webkitExitFullscreen) {
    document.webkitExitFullscreen();
  } else if (document.mozCancelFullScreen) {
    document.mozCancelFullScreen();
  } else if (document.msExitFullscreen) {
    document.msExitFullscreen();
  }
}

// Abrir reproductor
function openPlayer(url, season = null, episode = null) {
  const container = document.getElementById('playerContainer');
  const modal = document.getElementById('videoModal');
  const deviceType = detectDevice();
  
  // Limpiar contenido previo
  container.innerHTML = '<div class="loading"></div>';
  
  // Mostrar modal y bloquear scroll
  modal.style.display = 'flex';
  document.body.classList.add('modal-open');
  isModalOpen = true;

  // Marcar como visto automáticamente si se especifica temporada y episodio
  if (season !== null && episode !== null && window.watchedManager) {
    window.watchedManager.markAsWatched(season, episode);
  }

  // Entrar en pantalla completa automáticamente en TV/Desktop
  if (deviceType === 'tv' || deviceType === 'desktop') {
    setTimeout(() => {
      enterFullscreen(modal);
    }, 500);
  }

  // Detectar tipo de archivo
  const ext = url.split('.').pop().toLowerCase().split('?')[0];
  
  // Limpiar reproductor anterior
  if (currentPlayer) {
    try {
      if (typeof currentPlayer.dispose === 'function') {
        currentPlayer.dispose();
      }
    } catch (e) {
      console.log('Error al limpiar reproductor:', e);
    }
    currentPlayer = null;
  }

  setTimeout(() => {
    // Video directo (MP4, M3U8)
    if (ext === 'mp4' || ext === 'm3u8' || url.includes('.m3u8')) {
      const videoId = `my-video-${Date.now()}`;
      
      container.innerHTML = `
        <video
          id="${videoId}"
          class="video-js vjs-default-skin"
          controls
          preload="auto"
          crossorigin="anonymous"
          data-setup='{"fluid": false, "responsive": false, "aspectRatio": "16:9", "playbackRates": [0.5, 1, 1.25, 1.5, 2]}'
        >
          <source src="${url}" type="${ext === 'm3u8' || url.includes('.m3u8') ? 'application/x-mpegURL' : 'video/mp4'}">
          <p class="vjs-no-js">
            Para ver este video necesitas activar JavaScript y considerar actualizar a un
            <a href="https://videojs.com/html5-video-support/" target="_blank">
              navegador que soporte HTML5 video
            </a>.
          </p>
        </video>
      `;

      const videoElement = document.getElementById(videoId);
      
      if (videoElement) {
        currentPlayer = videojs(videoId, {
          fluid: false,
          responsive: false,
          fill: true,
          playbackRates: [0.5, 1, 1.25, 1.5, 2],
          controls: true,
          preload: 'auto',
          html5: {
            vhs: {
              enableLowInitialPlaylist: true,
              smoothQualityChange: true,
              overrideNative: true
            },
            nativeVideoTracks: false,
            nativeAudioTracks: false,
            nativeTextTracks: false
          }
        });

        currentPlayer.ready(() => {
          console.log('Reproductor listo');
          
          // Configurar tamaño completo
          currentPlayer.dimensions('100vw', '100vh');
          
          // Auto-play si es posible
          currentPlayer.play().catch(e => {
            console.log('Autoplay no permitido:', e);
          });

          // Evento para pantalla completa del reproductor
          currentPlayer.on('fullscreenchange', () => {
            if (currentPlayer.isFullscreen()) {
              currentPlayer.dimensions('100vw', '100vh');
            }
          });

          // Controles adicionales para Smart TV
          setupVideoPlayerControls(currentPlayer);
        });

        currentPlayer.on('error', (e) => {
          console.error('Error en reproductor:', e);
          container.innerHTML = `
            <div style="color: #ff0040; text-align: center; padding: 2rem; width: 100vw; height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; background: #000;">
              <p style="font-size: 1.5rem; margin-bottom: 1rem;">Error al cargar el video</p>
              <p>Intentando método alternativo...</p>
            </div>
          `;
          
          // Método alternativo con iframe
          setTimeout(() => {
            container.innerHTML = `
              <div class="iframe-container">
                <iframe src="${url}" allowfullscreen></iframe>
              </div>
            `;
          }, 2000);
        });
      }
    } 
    // Enlaces externos (Filemoon, Pixeldrain, etc.)
    else {
      container.innerHTML = `
        <div class="iframe-container">
          <iframe 
            src="${url}" 
            allowfullscreen 
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
            loading="lazy">
          </iframe>
        </div>
      `;
    }
  }, 300);
}

// Configurar controles del reproductor para Smart TV
function setupVideoPlayerControls(player) {
  if (!player) return;

  document.addEventListener('keydown', (e) => {
    if (!isModalOpen || !player) return;

    // Ignorar si estás escribiendo en un input o textarea
    if (document.activeElement.tagName === "INPUT" || document.activeElement.tagName === "TEXTAREA") {
      return;
    }

    switch (e.key) {
      case "ArrowUp":
        e.preventDefault();
        player.volume(Math.min(1, player.volume() + 0.1));
        break;

      case "ArrowDown":
        e.preventDefault();
        player.volume(Math.max(0, player.volume() - 0.1));
        break;

      case "ArrowRight":
        e.preventDefault();
        player.currentTime(player.currentTime() + 10);
        break;

      case "ArrowLeft":
        e.preventDefault();
        player.currentTime(player.currentTime() - 10);
        break;

      case " ":
        e.preventDefault();
        if (player.paused()) {
          player.play();
        } else {
          player.pause();
        }
        break;

      case "m":
      case "M":
        e.preventDefault();
        player.muted(!player.muted());
        break;

      case "f":
      case "F":
        e.preventDefault();
        if (player.isFullscreen()) {
          player.exitFullscreen();
        } else {
          player.requestFullscreen();
        }
        break;

      case "Escape":
        e.preventDefault();
        closePlayer();
        break;
    }
  });
}

// Cerrar reproductor
function closePlayer() {
  const modal = document.getElementById('videoModal');
  const container = document.getElementById('playerContainer');
  
  // Salir de pantalla completa si está activa
  if (document.fullscreenElement || document.webkitFullscreenElement || 
      document.mozFullScreenElement || document.msFullscreenElement) {
    exitFullscreen();
  }
  
  // Limpiar reproductor
  if (currentPlayer) {
    try {
      if (typeof currentPlayer.dispose === 'function') {
        currentPlayer.dispose();
      }
    } catch (e) {
      console.log('Error al cerrar reproductor:', e);
    }
    currentPlayer = null;
  }
  
  // Limpiar container
  container.innerHTML = '';
  
  // Ocultar modal y restaurar scroll
  modal.style.display = 'none';
  document.body.classList.remove('modal-open');
  isModalOpen = false;

  // Restaurar navegación normal
  if (window.navigationManager) {
    window.navigationManager.updateFocusableElements();
  }
}

// Event listeners principales
document.addEventListener('DOMContentLoaded', () => {
  // Inicializar sistemas
  window.navigationManager = new NavigationManager();
  window.watchedManager = new WatchedEpisodesManager(seriesId);
  
  guardarEnHistorial();
  manejarFavoritos();
  
  // Cerrar modal con Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isModalOpen) {
      closePlayer();
    }
  });
  
  // Cerrar modal haciendo clic fuera (solo en el fondo negro)
  document.getElementById('videoModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
      closePlayer();
    }
  });

  // Manejo de cambios de pantalla completa
  document.addEventListener('fullscreenchange', handleFullscreenChange);
  document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
  document.addEventListener('mozfullscreenchange', handleFullscreenChange);
  document.addEventListener('MSFullscreenChange', handleFullscreenChange);

  // Detectar tipo de dispositivo y agregar clase CSS
  const deviceType = detectDevice();
  document.body.classList.add(`device-${deviceType}`);
  
  if (window.navigationManager.isSmartTV()) {
    document.body.classList.add('smart-tv');
  }

  // Inicializar focus en el primer elemento
  setTimeout(() => {
    window.navigationManager.focusElement(0);
  }, 500);
});

// Manejar cambios de pantalla completa
function handleFullscreenChange() {
  const isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement || 
                          document.mozFullScreenElement || document.msFullscreenElement);
  
  if (!isFullscreen && isModalOpen) {
    // Si salimos de pantalla completa pero el modal sigue abierto, mantener el reproductor
    const modal = document.getElementById('videoModal');
    if (modal.style.display === 'flex') {
      console.log('Salió de pantalla completa, pero manteniendo modal');
    }
  }
}

// Manejo de orientación en móviles
window.addEventListener('orientationchange', () => {
  setTimeout(() => {
    if (currentPlayer && typeof currentPlayer.trigger === 'function') {
      currentPlayer.trigger('resize');
      currentPlayer.dimensions('100vw', '100vh');
    }
  }, 500);
});

// Manejo de redimensionamiento
window.addEventListener('resize', () => {
  if (currentPlayer && typeof currentPlayer.trigger === 'function') {
    currentPlayer.trigger('resize');
    if (isModalOpen) {
      currentPlayer.dimensions('100vw', '100vh');
    }
  }

  // Actualizar navegación después del resize
  if (window.navigationManager) {
    setTimeout(() => {
      window.navigationManager.updateFocusableElements();
    }, 300);
  }
});

// Prevenir scroll del body cuando el modal está abierto
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('videoModal');
  
  modal.addEventListener('wheel', (e) => {
    e.preventDefault();
  }, { passive: false });

  modal.addEventListener('touchmove', (e) => {
    e.preventDefault();
  }, { passive: false });
});

// Manejo de errores global
window.addEventListener('error', (e) => {
  console.error('Error global:', e.error);
  
  if (e.error && e.error.message && e.error.message.includes('video')) {
    console.log('Error relacionado con video, intentando solución alternativa');
  }
});

// Prevenir zoom en dispositivos móviles
document.addEventListener('touchstart', (e) => {
  if (e.touches.length > 1) {
    e.preventDefault();
  }
}, { passive: false });

let lastTouchEnd = 0;
document.addEventListener('touchend', (e) => {
  const now = (new Date()).getTime();
  if (now - lastTouchEnd <= 300) {
    e.preventDefault();
  }
  lastTouchEnd = now;
}, { passive: false });

// Función para scroll suave con teclado
function smoothScrollPage(direction) {
  const scrollAmount = window.innerHeight * 0.8;
  const currentScroll = window.pageYOffset;
  
  if (direction === 'down') {
    window.scrollTo({
      top: currentScroll + scrollAmount,
      behavior: 'smooth'
    });
  } else if (direction === 'up') {
    window.scrollTo({
      top: Math.max(0, currentScroll - scrollAmount),
      behavior: 'smooth'
    });
  }
}

// Configuración adicional para Smart TV
document.addEventListener('DOMContentLoaded', () => {
  // Configurar scroll suave para TV Box
  if (detectDevice() === 'tv') {
    document.addEventListener('keydown', (e) => {
      if (isModalOpen) return;
      
      // PageUp/PageDown para Smart TV
      if (e.key === 'PageDown' || (e.key === 'ArrowDown' && e.ctrlKey)) {
        e.preventDefault();
        smoothScrollPage('down');
      } else if (e.key === 'PageUp' || (e.key === 'ArrowUp' && e.ctrlKey)) {
        e.preventDefault();
        smoothScrollPage('up');
      }
    });
  }
});

</script>

<!-- Script de adblock -->
<script src="js/adblock.js"></script>

</body>
</html>