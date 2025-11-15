<?php
require_once '../../config.php';
if (!check_session()) { header('Location: ../login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$current_name = $_SESSION['name'] ?? $current_user;
$current_image = $current_data['profile_image'];
$is_admin = in_array($current_data['role'], ['admin', 'super_admin']);
$is_super_admin = $current_data['role'] === 'super_admin';

$html_generado = "";
$movie_id_selected = "";

if (isset($_GET['serverUrl'], $_GET['movieId'])) {
    $url = rtrim(trim($_GET['serverUrl']), '/');
    $movieId = trim($_GET['movieId']);
    $movie_id_selected = $movieId;
    if ($url && $movieId) {
        $moviesApi = $url . "/admin/api/moviespe.php?id=" . $movieId;

$html_template = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Detalle Película</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body{font-family:'Inter',sans-serif;background:#0e0e0e;color:#fff;margin:0;padding:0}
    .movie-detail{padding:2rem;max-width:800px;margin:auto}
    .movie-detail h1{text-align:center;font-size:2rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:center;gap:10px}
    .movie-detail img{display:block;margin:0 auto 1rem;width:100%;max-width:250px;border-radius:12px}
    .movie-detail p{text-align:center;line-height:1.5}
    .movie-detail iframe{display:block;margin:1.5rem auto 0;width:100%;max-width:600px;height:315px;border-radius:8px}
    #watchBtnContainer{text-align:center;margin-top:2rem}
    .btn-ver{padding:0.75rem 1.5rem;font-size:1.1rem;border:none;border-radius:8px;cursor:pointer;background:#f44336;color:#fff;font-weight:bold;transition:background 0.3s;display:inline-block}
    .btn-ver:hover{background:#d32f2f}
    .modal{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.95);justify-content:center;align-items:center;z-index:9999}
    .modal-content{background:#000;padding:1rem;border-radius:10px;width:95%;max-width:700px;text-align:center;position:relative}
    .close-btn{position:absolute;top:10px;right:15px;font-size:1.5rem;cursor:pointer;color:#f44336}
    .video-options button{margin:5px;padding:0.5rem 1rem;background:#3949ab;color:#fff;border:none;border-radius:5px;cursor:pointer}
    video{width:100%;height:auto;max-height:360px;border-radius:8px}
    .fav-icon{font-size:1.5rem;cursor:pointer;color:#555;transition:color 0.3s}
    @media(max-width:768px){.movie-detail iframe{height:240px}video{max-height:240px}}
  </style>
</head>
<body>
<div class="movie-detail">
<h1>

  <span id="movie-title">Cargando...</span> 
  <span id="favIcon" class="fav-icon">❤</span>
</h1>

<div style="text-align: center; margin: 20px 0;">
  <a href="go:Peliculas" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; color: white; background: #1a237e; padding: 10px 20px; border-radius: 8px;">
    <i class="fas fa-home"></i> Inicio
  </a>
</div>
  <img id="movie-poster" src="" alt="Poster">
  <p><strong>Fecha de estreno:</strong> <span id="release-date"></span></p>
  <p><strong>Descripción:</strong> <span id="overview"></span></p>
  <p><strong>Categoría:</strong> <span id="category"></span></p>
  <p><strong>Géneros:</strong> <span id="genres"></span></p>

  <iframe id="trailerFrame" src="" frameborder="0" allowfullscreen style="display:none;"></iframe>
  <div id="watchBtnContainer" style="display:none;">
    <button class="btn-ver" onclick="openModal()">Ver Ahora</button>
  </div>
</div>

<div id="playerModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <div id="modal-buttons" class="video-options"></div>
    <video id="modalPlayer" controls>
      <source src="" type="">
      Tu navegador no soporta videos HTML5.
    </video>
  </div>
</div>

<script>
fetch("__API_URL__")
  .then(res => res.json())
  .then(movie => {
    if (movie.status === "error") {
      document.body.innerHTML = "<h2 style='text-align:center; color:#f44336;'>🚫 " + movie.message + "</h2>";
      return;
    }

    // 📝 HISTORIAL - CORREGIDO PARA USAR 'historial_cine'
    let historial = JSON.parse(localStorage.getItem('historial_cine') || '[]');
    historial = historial.filter(item => item !== movie.id);
    historial.unshift(movie.id);
    if (historial.length > 15) historial = historial.slice(0,15);
    localStorage.setItem('historial_cine', JSON.stringify(historial));

    // ❤️ FAVORITOS
    const favIcon = document.getElementById('favIcon');
    let favoritos = JSON.parse(localStorage.getItem('favoritos') || '[]');
    actualizarCorazon();
    favIcon.addEventListener('click', () => {
      favoritos = JSON.parse(localStorage.getItem('favoritos') || '[]');
      if (favoritos.includes(movie.id)) {
        favoritos = favoritos.filter(f => f !== movie.id);
      } else {
        favoritos.unshift(movie.id);
        if (favoritos.length > 15) favoritos = favoritos.slice(0,15);
      }
      localStorage.setItem('favoritos', JSON.stringify(favoritos));
      actualizarCorazon();
    });
    function actualizarCorazon() {
      favIcon.style.color = favoritos.includes(movie.id) ? '#ff4444' : '#555';
    }
    document.title = movie.title + " | CorpSRTony Cine";
    document.getElementById("movie-title").textContent = movie.title;
    document.getElementById("movie-poster").src = "https://image.tmdb.org/t/p/w500" + movie.poster_path;
    document.getElementById("release-date").textContent = movie.release_date;
    document.getElementById("overview").textContent = movie.overview;
    document.getElementById("category").textContent = movie.category;
	document.getElementById("genres").textContent = movie.genres ? movie.genres.join(', ') : 'No disponible';

    if (movie.trailer) { document.getElementById("trailerFrame").src = "https://www.youtube.com/embed/" + movie.trailer; document.getElementById("trailerFrame").style.display = "block"; }
    if (movie.players && movie.players.length > 0) {
      document.getElementById("watchBtnContainer").style.display = "block";
      const btnsDiv = document.getElementById("modal-buttons");
      movie.players.forEach(link => { const btn = document.createElement("button"); btn.textContent = link.label; btn.onclick = () => setModalPlayer(link.url); btnsDiv.appendChild(btn); });
    }
  })
  .catch(err => { console.error(err); document.body.innerHTML = "<h2 style='text-align:center; color:#f44336;'>🚫 Error cargando los datos.</h2>"; });
function openModal(){document.getElementById("playerModal").style.display="flex";}
function closeModal(){document.getElementById("playerModal").style.display="none";document.getElementById("modalPlayer").pause();}
function setModalPlayer(url){const v=document.getElementById("modalPlayer");const s=v.querySelector("source");s.src=url;let e=url.split('.').pop().toLowerCase();let t='video/mp4';if(e==='m3u8')t='application/x-mpegURL';else if(e==='webm')t='video/webm';else if(e==='avi')t='video/x-msvideo';else if(e==='mkv')t='video/x-matroska';s.type=t;v.load();v.play();}
</script>
</body>
</html>
HTML;
        $html_generado = str_replace("__API_URL__", $moviesApi, $html_template);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Generador Render Movie - CorpSRTony Cine</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',sans-serif;background:#f4f6fc;display:flex;min-height:100vh}
  .sidebar{width:250px;background:#1a237e;color:white;height:100vh;position:fixed;left:0;top:0;overflow-y:auto;padding:1.5rem 1rem;transition:transform 0.3s ease;}
  .sidebar h1{text-align:center;font-size:1.4rem;margin-bottom:1.2rem}
  .sidebar .section-title{font-size:0.8rem;text-transform:uppercase;opacity:0.7;margin:1rem 0 0.5rem 0;padding-left:1rem}
  .sidebar a{display:flex;align-items:center;gap:10px;color:white;text-decoration:none;padding:0.5rem 1rem;border-radius:6px;margin-bottom:0.3rem;font-size:0.95rem}
  .sidebar a:hover{background:rgba(255,255,255,0.2)}
  .hamburger{position:fixed;top:1rem;left:1rem;font-size:1.5rem;background:#1a237e;color:white;border:none;padding:0.6rem;border-radius:6px;z-index:1100;cursor:pointer;display:none}
  .main-content{flex:1;margin-left:250px;padding:2rem;transition:margin-left 0.3s ease;}
  .content-wrapper{
    max-width:700px;
    margin:0 auto;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:flex-start;
    width:100%;
  }
  form, textarea{
    width:100%;
    max-width:600px;
    margin-top:1.2rem;
  }
  textarea,input[type="text"]{
    width:100%;
    padding:0.8rem;
    margin:0.8rem 0;
    border:1px solid #ccc;
    border-radius:6px;
    background:#1a1a1a;
    color:#0f0;
    font-family:monospace;
  }
  button{
    background:#1a237e;
    color:white;
    padding:0.6rem 1.2rem;
    border:none;
    border-radius:6px;
    font-weight:bold;
    cursor:pointer;
    margin-top:1rem;
  }
  button:hover{background:#151d6e}
  #resultados button{
    background:#3949ab;
    margin:5px;
    padding:0.6rem 1rem;
    border-radius:5px;
  }
  .copy-btn{background:#4caf50;margin-top:1rem;}
  .tutorial{
    background:#fff;
    color:#333;
    padding:1.2rem;
    border-radius:8px;
    margin-bottom:2rem;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    width:100%;
  }
  .tutorial h2{color:#1a237e;margin-bottom:1rem;font-size:1.4rem}
  .tutorial ul{list-style:square;padding-left:1.2rem}
  .tutorial li{margin-bottom:0.6rem;line-height:1.5;font-size:0.95rem}
  @media(max-width:768px){
    .hamburger{display:block;}
    .sidebar{transform:translateX(-100%);}
    .sidebar.active{transform:translateX(0);}
    .main-content{margin-left:0;padding-top:4rem;}
  }
</style>

</head>

<body>

<button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

<div class="sidebar" id="sidebar">
  <h1>Admin panel</h1>
  <div class="section-title">Menú Principal</div>
  <a href="../index.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="../manage-series.php"><i class="fas fa-tv"></i> Series</a>
  <a href="../create-movie.php"><i class="fas fa-video"></i> Crear Película</a>
  <a href="../manage-categories.php"><i class="fas fa-layer-group"></i> Categorías</a>
        <a href="../detv.php"><i class="fas fa-play-circle"></i> TV</a>

  <?php if ($is_admin): ?>
    <div class="section-title">⚙️ Configuración</div>
    <a href="../config_home.php"><i class="fas fa-home"></i> Config Home</a>
    <a href="../config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>
  <?php endif; ?>

  <?php if ($is_super_admin): ?>
    <div class="section-title">🔧 Admin Tools</div>
    <a href="../soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
    <a href="../configure-api.php"><i class="fas fa-key"></i> TMDB API</a>
    <a href="generadores.php"><i class="fas fa-cogs"></i> App Creator 24 📲</a>
    <a href="../monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
    <a href="../telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <?php endif; ?>

  <div class="section-title">Usuario</div>
  <a href="../perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
  <div class="content-wrapper">

    <div class="tutorial">
      <h2>📚 Tutorial rápido para App Creator 24</h2>
      <ul>
        <li>✅ Ve a App Creator 24 ➔ <strong>Añadir sección ➔ HTML</strong></li>
        <li>🔧 Selecciona: <strong>Introducir directamente el código HTML (Avanzado)</strong></li>
        <li>📝 En <strong>Título sección:</strong> coloca el nombre de la película</li>
        <li>💻 En <strong>HTML:</strong> pega el código generado abajo</li>
        <li>🔗 En <strong>Referencia:</strong> coloca la <em>ID TMDB</em></li>
        <li>🚫 En <strong>Incluir en menú:</strong> pon <strong>No</strong></li>
        <li>🎥 En <strong>Ver vídeo para acceder:</strong> <strong>Sí</strong> o <strong>No</strong></li>
      </ul>
    </div>

    <form method="get" id="formGenerator">
      <input type="text" name="serverUrl" placeholder="https://tuservidor.com" required value="<?= htmlspecialchars($_GET['serverUrl'] ?? '') ?>">
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" id="buscador" placeholder="🔍 Escribe para buscar..." style="flex:1;">
        <button type="button" onclick="buscarPeliculas()">Buscar</button>
      </div>
      <div id="resultados" style="margin-top:1rem;"></div>
      <input type="hidden" name="movieId" id="movieId" value="<?= htmlspecialchars($_GET['movieId'] ?? '') ?>">
    </form>

    <?php if ($html_generado): ?>
      <textarea readonly rows="20"><?= htmlspecialchars($html_generado) ?></textarea>
      <br><button class="copy-btn" onclick="copiarTexto('<?= $movie_id_selected ?>')">📋 Copiar ID TMDB</button>
    <?php endif; ?>

  </div>
</div>


<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('active');}
function buscarPeliculas(){
  const term=document.getElementById("buscador").value.toLowerCase();
  const url=document.querySelector("input[name='serverUrl']").value.trim();
  if(!url)return alert("Primero ingresa la URL base.");
  fetch(url+"/admin/api/movies.php").then(res=>res.json()).then(data=>{
    let html="";
    Object.values(data).forEach(movie=>{
      if(movie.title.toLowerCase().includes(term)){
        html+=`<button onclick="selectMovie('${movie.id}')">${movie.title} 📋</button>`;
      }
    });
    document.getElementById("resultados").innerHTML=html||"<p>Sin resultados</p>";
  }).catch(err=>console.error(err));
}
function selectMovie(id){
  document.getElementById("movieId").value=id;
  document.getElementById("formGenerator").submit();
}
function copiarTexto(text){
  navigator.clipboard.writeText(text).then(()=>alert("ID copiada: "+text));
}
</script>
<script>
function copiarTexto(text){
    // Crear un input temporal oculto
    const tempInput = document.createElement("input");
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // Para móviles

    try {
        let exitoso = document.execCommand('copy');
        if(exitoso) {
            alert("ID copiada: " + text);
        } else {
            alert("No se pudo copiar automáticamente. Copia manual: " + text);
        }
    } catch (err) {
        alert("Error al copiar. Copia manual: " + text);
    }
    document.body.removeChild(tempInput);
}
</script>


</body>
</html>
