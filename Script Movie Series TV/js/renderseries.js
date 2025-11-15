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

  // Marcar como visto autom芍ticamente si se especifica temporada y episodio
  if (season !== null && episode !== null && window.watchedManager) {
    window.watchedManager.markAsWatched(season, episode);
  }

  // Entrar en pantalla completa autom芍ticamente en TV/Desktop
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
          
          // Configurar tama?o completo
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
              <p>Intentando m谷todo alternativo...</p>
            </div>
          `;
          
          // M谷todo alternativo con iframe
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

    // Ignorar si est芍s escribiendo en un input o textarea
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
  
  // Salir de pantalla completa si est芍 activa
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

  // Restaurar navegaci車n normal
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
      console.log('Sali車 de pantalla completa, pero manteniendo modal');
    }
  }
}

// Manejo de orientaci車n en m車viles
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

  // Actualizar navegaci車n despu谷s del resize
  if (window.navigationManager) {
    setTimeout(() => {
      window.navigationManager.updateFocusableElements();
    }, 300);
  }
});

// Prevenir scroll del body cuando el modal est芍 abierto
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
    console.log('Error relacionado con video, intentando soluci車n alternativa');
  }
});

// Prevenir zoom en dispositivos m車viles
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

// Funci車n para scroll suave con teclado
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

// Configuraci車n adicional para Smart TV
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