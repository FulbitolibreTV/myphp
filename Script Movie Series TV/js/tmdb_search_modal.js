// archivo: tmdb-search-modal.js

// Función para inicializar el modal de búsqueda TMDB
function initTmdbSearchModal({ apiKey, openButtonId, tmdbInputId }) {
  // Crear el modal HTML y agregar al body si no existe
  if (!document.getElementById('tmdbSearchModal')) {
    const modalHTML = `
      <div id="tmdbSearchModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;
        background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;max-width:600px;width:90%;border-radius:8px;padding:1rem;position:relative;">
          <button id="closeTmdbModal" style="position:absolute;top:8px;right:12px;font-size:1.5rem;border:none;background:none;cursor:pointer;">&times;</button>
          <h3>Buscar Película TMDB</h3>
          <input type="text" id="tmdbSearchInput" placeholder="Escribe el nombre de la película..." style="width:100%;padding:0.5rem;margin-bottom:1rem;font-size:1rem;border:1px solid #ccc;border-radius:6px;" />
          <div id="tmdbSearchResults" style="max-height:350px;overflow-y:auto;"></div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
  }

  const modal = document.getElementById('tmdbSearchModal');
  const openBtn = document.getElementById(openButtonId);
  const closeBtn = document.getElementById('closeTmdbModal');
  const searchInput = document.getElementById('tmdbSearchInput');
  const resultsContainer = document.getElementById('tmdbSearchResults');
  const tmdbInput = document.getElementById(tmdbInputId);

  // Abrir modal al hacer click en botón
  openBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
    searchInput.value = '';
    resultsContainer.innerHTML = '';
    searchInput.focus();
  });

  // Cerrar modal
  closeBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Cerrar modal si clic fuera del contenido
  modal.addEventListener('click', e => {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });

  // Buscar películas en TMDB cuando escribe el usuario (debounce para no saturar)
  let debounceTimer;
  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const query = searchInput.value.trim();
    if (query.length < 3) {
      resultsContainer.innerHTML = '';
      return;
    }
    debounceTimer = setTimeout(() => {
      buscarPeliculasTMDB(query);
    }, 500);
  });

  async function buscarPeliculasTMDB(query) {
    resultsContainer.innerHTML = '<p>Cargando resultados...</p>';
    try {
      const url = `https://api.themoviedb.org/3/search/movie?api_key=${apiKey}&language=es&query=${encodeURIComponent(query)}&page=1&include_adult=false`;
      const res = await fetch(url);
      if (!res.ok) throw new Error('Error al buscar películas');
      const data = await res.json();
      mostrarResultados(data.results || []);
    } catch (err) {
      resultsContainer.innerHTML = `<p style="color:red;">${err.message}</p>`;
    }
  }

  function mostrarResultados(results) {
    if (results.length === 0) {
      resultsContainer.innerHTML = '<p>No se encontraron películas.</p>';
      return;
    }
    resultsContainer.innerHTML = '';
    results.forEach(movie => {
      const poster = movie.poster_path ? `https://image.tmdb.org/t/p/w92${movie.poster_path}` : 'https://via.placeholder.com/92x138?text=No+Imagen';
      const div = document.createElement('div');
      div.style = 'display:flex;align-items:center;gap:10px;margin-bottom:10px;cursor:pointer;padding:5px;border-radius:5px;';
      div.innerHTML = `
        <img src="${poster}" alt="${movie.title}" width="46" height="69" style="border-radius:4px;object-fit:cover;">
        <div style="flex:1;">
          <strong>${movie.title}</strong><br>
          <small>${movie.release_date || 'Fecha no disponible'}</small>
        </div>
        <button style="padding:0.3rem 0.6rem;border:none;background:#1a237e;color:#fff;border-radius:4px;cursor:pointer;">Copiar ID</button>
      `;

      // Cuando clic en el div o en el botón copia el ID y cierra modal
      const btn = div.querySelector('button');
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        copiarId(movie.id);
      });
      div.addEventListener('click', () => copiarId(movie.id));

      resultsContainer.appendChild(div);
    });
  }

  function copiarId(id) {
    if (!tmdbInput) {
      alert('Input TMDB no encontrado');
      return;
    }
    tmdbInput.value = id;
    modal.style.display = 'none';
    alert(`ID de película copiado: ${id}`);
  }
}
