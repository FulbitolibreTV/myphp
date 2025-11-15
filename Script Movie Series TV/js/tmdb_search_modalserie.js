// tmdb_search_modalserie.js

function initTmdbSearchModal({ apiKey, openButtonId, tmdbInputId }) {
  if (document.getElementById('tmdbSearchModal')) return;

  const modal = document.createElement('div');
  modal.id = 'tmdbSearchModal';
  modal.style.cssText = `
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.7); display: flex; justify-content: center; align-items: center;
    z-index: 9999; visibility: hidden; opacity: 0; transition: opacity 0.3s ease;
  `;

  modal.innerHTML = `
    <div style="background:#fff; padding: 1.5rem; border-radius: 10px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; position: relative;">
      <button id="closeTmdbModal" style="position:absolute; top:10px; right:10px; font-size: 1.2rem; background:none; border:none; cursor:pointer;">✖</button>
      <h2>Buscar Serie en TMDB</h2>
      <input type="search" id="tmdbSerieSearchInput" placeholder="Escribe el nombre de la serie" style="width: 100%; padding: 0.8rem; margin: 1rem 0; border: 1px solid #ccc; border-radius: 6px;" autofocus />
      <div id="tmdbSerieResults"></div>
    </div>
  `;

  document.body.appendChild(modal);

  const openBtn = document.getElementById(openButtonId);
  const tmdbInput = document.getElementById(tmdbInputId);
  const closeBtn = modal.querySelector('#closeTmdbModal');
  const searchInput = modal.querySelector('#tmdbSerieSearchInput');
  const resultsDiv = modal.querySelector('#tmdbSerieResults');

  function openModal() {
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
    searchInput.value = '';
    resultsDiv.innerHTML = '';
    searchInput.focus();
  }

  function closeModal() {
    modal.style.opacity = '0';
    setTimeout(() => {
      modal.style.visibility = 'hidden';
    }, 300);
  }

  openBtn.addEventListener('click', openModal);
  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  let timeout = null;
  searchInput.addEventListener('input', () => {
    clearTimeout(timeout);
    const query = searchInput.value.trim();
    if (!query) {
      resultsDiv.innerHTML = '';
      return;
    }
    timeout = setTimeout(() => {
      fetch(`https://api.themoviedb.org/3/search/tv?api_key=${apiKey}&language=es&query=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
          if (!data.results || data.results.length === 0) {
            resultsDiv.innerHTML = '<p>No se encontraron resultados.</p>';
            return;
          }
          resultsDiv.innerHTML = '';
          data.results.forEach(serie => {
            const div = document.createElement('div');
            div.style.cssText = 'display:flex; align-items:center; margin-bottom: 10px; cursor:pointer; padding: 5px; border-bottom: 1px solid #ddd; justify-content: space-between;';

            div.innerHTML = `
              <div style="display:flex; align-items:center; gap:10px; flex:1; cursor: pointer;">
                <img src="https://image.tmdb.org/t/p/w45${serie.poster_path || ''}" alt="Poster" style="width:45px; height:67px; object-fit:cover; border-radius:4px;">
                <div>
                  <strong>${serie.name}</strong><br>
                  <small>${serie.first_air_date || 'Fecha no disponible'}</small>
                </div>
              </div>
              <button style="background:#1a237e; color:#fff; border:none; padding:5px 8px; border-radius:4px; cursor:pointer; flex-shrink:0;">
                Copiar ID
              </button>
            `;

            // Al hacer clic en la zona del poster + texto: pega el ID y cierra el modal
            div.querySelector('div').addEventListener('click', () => {
              tmdbInput.value = serie.id;
              closeModal();
            });

            // Al hacer clic en botón Copiar ID: copia el ID, pega en input pero NO cierra el modal
            div.querySelector('button').addEventListener('click', (e) => {
              e.stopPropagation(); // No dispara el click del padre
              navigator.clipboard.writeText(serie.id)
                .then(() => {
                  tmdbInput.value = serie.id;
                  alert(`ID ${serie.id} copiado y pegado en el campo.`);
                })
                .catch(() => {
                  alert('Error al copiar el ID');
                });
            });

            resultsDiv.appendChild(div);
          });
        })
        .catch(() => {
          resultsDiv.innerHTML = '<p>Error al buscar series. Intenta nuevamente.</p>';
        });
    }, 400);
  });
}
