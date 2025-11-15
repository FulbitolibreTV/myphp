<?php
$html_generado = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serverUrl'])) {
    $url = rtrim(trim($_POST['serverUrl']), '/');
    if ($url) {
        $moviesApi = $url . "/admin/api/moviespe.php";
        $seriesApi = $url . "/admin/api/seriesConnect.php";
        $html_generado = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Favoritos - Cine SRTony</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
body {
  margin: 0;
  font-family: 'Orbitron', sans-serif;
  background-color: #0e0e0e;
  color: #fff;
  padding: 20px;
  font-size: 14px;
}
h1 {
  text-align: center;
  margin-bottom: 20px;
  color: #00ffe0;
  text-shadow: 0 0 5px #00ffe0;
}
.secciones {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 30px;
}
.seccion-btn {
  padding: 10px 20px;
  background-color: #111;
  color: #ccc;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-family: 'Orbitron', sans-serif;
  font-weight: 600;
  transition: all 0.3s;
  box-shadow: 0 0 10px rgba(0,255,255,0.1);
}
.seccion-btn:hover {
  background-color: #191919;
  transform: translateY(-2px);
}
.seccion-btn.active {
  background-color: #00ffe0;
  color: #0e0e0e;
  box-shadow: 0 0 15px rgba(0,255,224,0.3);
}
.favoritos {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 600px;
  margin: 0 auto;
}
.item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #111;
  padding: 10px;
  border-radius: 12px;
  transition: background 0.3s, transform 0.3s;
  box-shadow: 0 0 15px rgba(0,255,255,0.15);
}
.item:hover {
  background-color: #191919;
  transform: translateY(-2px);
}
.item-info {
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
}
.item img {
  width: 70px;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,255,255,0.2);
}
.item-content {
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.item-title {
  font-size: 1rem;
  font-weight: 600;
  color: #ccc;
}
.item-type {
  font-size: 0.8rem;
  color: #888;
  font-style: italic;
}
.item i {
  color: #ff0040;
  font-size: 1.2rem;
  cursor: pointer;
  transition: transform 0.3s;
}
.item i:hover {
  transform: scale(1.2);
}
.no-favoritos {
  text-align: center;
  font-size: 1.1rem;
  margin-top: 40px;
  color: #888;
}
.loading {
  text-align: center;
  color: #00ffe0;
  margin-top: 20px;
}
.back-btn {
  position: fixed;
  top: 20px;
  left: 20px;
  background: #00ffe0;
  color: #0e0e0e;
  padding: 10px 15px;
  border-radius: 50px;
  text-decoration: none;
  font-weight: 600;
  box-shadow: 0 0 15px rgba(0,255,224,0.3);
  transition: all 0.3s;
}
.back-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 0 20px rgba(0,255,224,0.5);
}
</style>
</head>
<body>

<a href="go:home" class="back-btn">
  <i class="fas fa-arrow-left"></i> Inicio
</a>

<h1>💝 Mis Favoritos</h1>

<div class="secciones">
  <button class="seccion-btn active" onclick="mostrarSeccion('todo')" id="btn-todo">
    <i class="fas fa-heart"></i> Todo
  </button>
  <button class="seccion-btn" onclick="mostrarSeccion('peliculas')" id="btn-peliculas">
    <i class="fas fa-film"></i> Películas
  </button>
  <button class="seccion-btn" onclick="mostrarSeccion('series')" id="btn-series">
    <i class="fas fa-tv"></i> Series
  </button>
</div>

<div class="loading" id="loading" style="display: none;">
  <i class="fas fa-spinner fa-spin"></i> Cargando favoritos...
</div>

<div class="favoritos" id="contenedor"></div>
<div class="no-favoritos" id="mensajeVacio">
  <i class="fas fa-heart-broken"></i><br>
  No tienes favoritos aún.<br>
  <small>¡Agrega contenido a tus favoritos para verlo aquí!</small>
</div>

<script>
const contenedor = document.getElementById('contenedor');
const mensajeVacio = document.getElementById('mensajeVacio');
const loading = document.getElementById('loading');

// URLs de las APIs
const MOVIES_API = "$moviesApi";
const SERIES_API = "$seriesApi";

// Obtener favoritos del localStorage (sistema unificado)
const todosFavoritos = JSON.parse(localStorage.getItem('favoritos') || '[]');

let todosLosFavoritos = [];
let seccionActual = 'todo';

function mostrarSeccion(seccion) {
  // Actualizar botones
  document.querySelectorAll('.seccion-btn').forEach(btn => btn.classList.remove('active'));
  document.getElementById(\`btn-\${seccion}\`).classList.add('active');
  
  seccionActual = seccion;
  
  // Filtrar y mostrar contenido
  const contenidoFiltrado = filtrarPorSeccion(todosLosFavoritos, seccion);
  mostrarFavoritos(contenidoFiltrado);
}

function filtrarPorSeccion(favoritos, seccion) {
  if (seccion === 'todo') return favoritos;
  return favoritos.filter(item => item.tipo === seccion);
}

function eliminarFavorito(id, tipo, elemento) {
  // Mostrar confirmación
  if (!confirm('¿Estás seguro de que quieres eliminar este favorito?')) {
    return;
  }
  
  // Eliminar del array unificado
  let nuevosFavoritos = todosFavoritos.filter(fav => fav != id);
  localStorage.setItem('favoritos', JSON.stringify(nuevosFavoritos));
  todosFavoritos.length = 0;
  todosFavoritos.push(...nuevosFavoritos);
  
  // Actualizar array principal
  todosLosFavoritos = todosLosFavoritos.filter(item => !(item.id == id && item.tipo === tipo));
  
  // Quitar del DOM con animación
  elemento.style.transform = 'translateX(-100%)';
  elemento.style.opacity = '0';
  setTimeout(() => {
    elemento.remove();
    
    // Verificar si mostrar mensaje vacío
    const contenidoFiltrado = filtrarPorSeccion(todosLosFavoritos, seccionActual);
    if (contenidoFiltrado.length === 0) {
      mensajeVacio.style.display = 'block';
    }
  }, 300);
}

function mostrarFavoritos(favoritos) {
  contenedor.innerHTML = '';
  
  if (favoritos.length === 0) {
    mensajeVacio.style.display = 'block';
    return;
  }
  
  mensajeVacio.style.display = 'none';
  
  favoritos.forEach(item => {
    const div = document.createElement('div');
    div.className = 'item';
    
    const info = document.createElement('div');
    info.className = 'item-info';
    info.onclick = () => window.location.href = \`go:\${item.id}\`;
    
    const img = document.createElement('img');
    img.src = \`https://image.tmdb.org/t/p/w200\${item.poster_path}\`;
    img.alt = item.title;
    img.onerror = () => {
      img.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="70" height="105" fill="%23333"><rect width="100%" height="100%" fill="%23111"/><text x="50%" y="50%" text-anchor="middle" fill="%23666" font-size="8">Sin imagen</text></svg>';
    };
    
    const content = document.createElement('div');
    content.className = 'item-content';
    
    const nombre = document.createElement('div');
    nombre.className = 'item-title';
    nombre.textContent = item.title;
    
    const tipoLabel = document.createElement('div');
    tipoLabel.className = 'item-type';
    tipoLabel.textContent = item.tipo === 'peliculas' ? '🎬 Película' : '📺 Serie';
    
    const trash = document.createElement('i');
    trash.className = 'fas fa-trash';
    trash.title = 'Eliminar favorito';
    trash.onclick = (e) => {
      e.stopPropagation();
      eliminarFavorito(item.id, item.tipo, div);
    };
    
    content.appendChild(nombre);
    content.appendChild(tipoLabel);
    info.appendChild(img);
    info.appendChild(content);
    div.appendChild(info);
    div.appendChild(trash);
    contenedor.appendChild(div);
  });
}

async function cargarFavoritos() {
  if (todosFavoritos.length === 0) {
    mensajeVacio.style.display = 'block';
    return;
  }
  
  loading.style.display = 'block';
  todosLosFavoritos = [];
  
  // Cargar cada ID intentando primero como película y luego como serie
  const promesas = todosFavoritos.slice(0, 20).map(async (id) => {
    // Primero intentar como película
    try {
      const response = await fetch(\`\${MOVIES_API}?id=\${id}\`);
      const data = await response.json();
      if (data.status !== "error" && data.title) {
        return {
          id: id,
          title: data.title,
          poster_path: data.poster_path,
          tipo: 'peliculas'
        };
      }
    } catch (error) {
      console.log(\`No se pudo cargar como película: \${id}\`);
    }
    
    // Si no es película, intentar como serie
    try {
      const response = await fetch(\`\${SERIES_API}?id=\${id}\`);
      const data = await response.json();
      
      // Verificar diferentes formatos de respuesta de series
      let serieData = data;
      if (data.data && data.data.serie) {
        serieData = data.data.serie;
      }
      
      if (!data.error && serieData && (serieData.title || serieData.name)) {
        return {
          id: id,
          title: serieData.title || serieData.name || \`Serie \${id}\`,
          poster_path: serieData.poster_path,
          tipo: 'series'
        };
      }
    } catch (error) {
      console.log(\`No se pudo cargar como serie: \${id}\`);
    }
    
    // Si no se pudo cargar, devolver un objeto con datos por defecto
    return {
      id: id,
      title: \`Contenido \${id}\`,
      poster_path: '',
      tipo: 'peliculas'
    };
  });
  
  // Esperar a que se carguen todas las promesas
  const resultados = await Promise.all(promesas);
  
  // Filtrar resultados válidos
  todosLosFavoritos = resultados.filter(item => item !== null);
  
  loading.style.display = 'none';
  
  // Mostrar favoritos según la sección actual
  const contenidoFiltrado = filtrarPorSeccion(todosLosFavoritos, seccionActual);
  mostrarFavoritos(contenidoFiltrado);
}

// Cargar favoritos al iniciar la página
document.addEventListener('DOMContentLoaded', () => {
  cargarFavoritos();
});
</script>
</body>
</html>
HTML;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Generador Favoritos PHP - CorpSRTony Cine</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;background:#f4f6fc;}
.sidebar{width:250px;background:#1a237e;color:white;height:100vh;position:fixed;left:0;top:0;overflow-y:auto;padding:1.5rem 1rem;transition:left 0.3s;}
.sidebar h1{text-align:center;font-size:1.4rem;margin-bottom:1.2rem;}
.sidebar .section-title{font-size:0.8rem;text-transform:uppercase;opacity:0.7;margin:1rem 0 0.5rem 0;padding-left:1rem;}
.sidebar a{display:flex;align-items:center;gap:10px;color:white;text-decoration:none;padding:0.5rem 1rem;border-radius:6px;margin-bottom:0.3rem;font-size:0.95rem;}
.sidebar a:hover{background:rgba(255,255,255,0.2);}
.menu-toggle{display:none;position:fixed;top:15px;left:15px;background:#1a237e;color:white;padding:0.5rem 0.7rem;border-radius:5px;z-index:1100;cursor:pointer;}
.main-content{margin-left:250px;padding:2rem;transition:margin-left 0.3s;}
.content-wrapper{max-width:700px;margin:0 auto;display:flex;flex-direction:column;align-items:center;width:100%;}
form, textarea{width:100%;max-width:600px;margin-top:1.2rem;}
textarea,input[type="text"]{width:100%;padding:0.8rem;margin:0.8rem 0;border:1px solid #ccc;border-radius:6px;background:#1a1a1a;color:#0f0;font-family:monospace;}
button{background:#1a237e;color:white;padding:0.6rem 1.2rem;border:none;border-radius:6px;font-weight:bold;cursor:pointer;margin-top:1rem;}
button:hover{background:#151d6e;}
.info-box{background:#e3f2fd;border:1px solid #2196f3;border-radius:8px;padding:1rem;margin:1rem 0;color:#1976d2;}
.info-box h3{margin:0 0 0.5rem 0;color:#1565c0;}
.info-box ul{margin:0.5rem 0;padding-left:1.5rem;}
@media(max-width:768px){
  .sidebar{left:-250px;}
  .sidebar.open{left:0;}
  .main-content{margin-left:0;padding-top:4rem;}
  .menu-toggle{display:block;}
}
</style>
</head>
<body>
<div class="menu-toggle"><i class="fas fa-bars"></i></div>
<div class="sidebar">
  <h1>Admin Panel</h1>
  <div class="section-title">Menú Principal</div>
  <a href="../index.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="../manage-series.php"><i class="fas fa-tv"></i> Series</a>
  <a href="../create-movie.php"><i class="fas fa-video"></i> Crear Película</a>
  <a href="../manage-categories.php"><i class="fas fa-layer-group"></i> Categorías</a>
        <a href="../detv.php"><i class="fas fa-play-circle"></i> TV</a>
  <div class="section-title">⚙️ Configuración</div>
  <a href="../config_home.php"><i class="fas fa-home"></i> Config Home</a>
  <a href="../config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>
  <div class="section-title">🔧 Admin Tools</div>
  <a href="../soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
  <a href="../configure-api.php"><i class="fas fa-key"></i> TMDB API</a>
  <a href="../api/generadores.php"><i class="fas fa-cogs"></i> App Creator 24 📲</a>
  <a href="../monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
  <a href="../telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <div class="section-title">Usuario</div>
  <a href="../perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>
<div class="main-content">
  <div class="content-wrapper">
    <h1>💝 Generador Página de Favoritos</h1>
    
    <div class="info-box">
      <h3><i class="fas fa-info-circle"></i> Información del Generador</h3>
      <p>Este generador crea una página de favoritos que se conecta automáticamente a tus APIs.</p>
      <ul>
        <li><strong>API de Películas:</strong> /admin/api/moviespe.php</li>
        <li><strong>API de Series:</strong> /admin/api/seriesConnect.php</li>
        <li><strong>Sistema:</strong> Utiliza localStorage para almacenar favoritos</li>
        <li><strong>Funciones:</strong> Ver, filtrar y eliminar favoritos</li>
      </ul>
    </div>
    
    <form method="post">
      <p>👉 Ingresa la URL base de tu servidor:</p>
      <input type="text" name="serverUrl" placeholder="https://tuservidor.com" required>
      <button type="submit"><i class="fas fa-heart"></i> Generar Página de Favoritos</button>
    </form>
    
    <?php if ($html_generado): ?>
      <h2>✅ HTML de Favoritos Generado (guarda como favoritos.html):</h2>
      <div class="info-box">
        <h3><i class="fas fa-download"></i> Instrucciones de Uso</h3>
        <ul>
          <li>Guarda el código como <strong>favoritos</strong></li>
          <li>Súbelo en seccion html</li>
          <li>La página se conectará automáticamente a: <strong><?php echo htmlspecialchars($_POST['serverUrl']); ?></strong></li>
          <li>Los usuarios podrán ver sus favoritos organizados por películas y series</li>
		  <li>recuerda colocar en referencia favoritos</li>
        </ul>
      </div>
      <textarea readonly rows="20"><?php echo htmlspecialchars($html_generado); ?></textarea>
    <?php endif; ?>
  </div>
</div>
<script>
document.querySelector('.menu-toggle').addEventListener('click', () => {
  document.querySelector('.sidebar').classList.toggle('open');
});
</script>
</body>
</html>