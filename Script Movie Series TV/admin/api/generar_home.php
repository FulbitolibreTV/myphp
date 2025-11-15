<?php
$html_generado = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serverUrl'])) {
    $url = rtrim(trim($_POST['serverUrl']), '/');
    if ($url) {
        $moviesApi = $url . "/admin/api/movies.php";
        $siteInfoApi = $url . "/admin/api/site_info.php";
        $html_generado = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cargando...</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: #0e0e0e;
      color: #fff;
      font-family: 'Orbitron', sans-serif;
      margin: 0;
      padding-bottom: 80px;
      font-size: 14px;
    }
    header {
      background: #111;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 0 15px rgba(0,255,255,0.3);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    header .logo {
      font-size: 1.5rem;
      color: #00ffe0;
      font-weight: 700;
      text-shadow: 0 0 5px #00ffe0;
    }
    header .icons a {
      margin-left: 12px;
      color: #00ffe0;
      font-size: 1.3rem;
      transition: 0.3s;
    }
    header .icons a:hover {
      color: #fff;
      transform: scale(1.1);
      text-shadow: 0 0 8px #00ffe0;
    }
    h2 {
      margin: 25px 20px 10px;
      color: #00ffe0;
      font-size: 1.4rem;
      border-left: 5px solid #00ffe0;
      padding-left: 10px;
      cursor: pointer;
      text-shadow: 0 0 5px #00ffe0;
    }
    .movie-row {
      display: flex;
      overflow-x: auto;
      gap: 15px;
      padding: 0 20px 30px;
      scroll-behavior: smooth;
    }
    .movie {
      flex: 0 0 auto;
      width: 140px;
      border-radius: 10px;
      overflow: hidden;
      background: #111;
      box-shadow: 0 0 12px rgba(0,255,255,0.1);
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    .movie:hover {
      transform: scale(1.05);
      box-shadow: 0 0 20px rgba(0,255,255,0.4);
    }
    .movie img {
      width: 100%;
      display: block;
    }
    footer {
      background: #111;
      text-align: center;
      color: #aaa;
      font-size: 0.9rem;
      padding: 15px;
      position: fixed;
      bottom: 0;
      width: 100%;
      box-shadow: 0 -4px 10px rgba(0,255,255,0.2);
    }
    .no-connection {
      text-align: center;
      margin-top: 50px;
      font-size: 1.2rem;
      color: #00ffe0;
      text-shadow: 0 0 5px #00ffe0;
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
  <header>
    <div class="logo" id="site-title">Cargando...</div>
    <div class="icons">
      <a href="go:buscador"><i class="fas fa-search"></i></a>
      <a href="go:soporte"><i class="fas fa-headset"></i></a>
      <a href="go:favoritos"><i class="fas fa-heart fav"></i></a>
      <a href="go:historial"><i class="fas fa-history"></i></a>
    </div>
  </header>
<!-- Barra de navegación rápida -->
<div class="top-nav-categorias">
    <p>📺 Navega por nuestras secciones:</p>
    <div class="nav-enlaces">
        <a href="go:peliculas" title="Películas"><i class="fas fa-film"></i> Películas</a>
        <a href="go:series" title="Series"><i class="fas fa-clapperboard"></i> Series</a>
		<a href="go:tv" title="TV en Vivo"><i class="fas fa-tv"></i> TV</a>
    </div>
</div>

  <div id="content"></div>
  <div id="no-connection" class="no-connection" style="display:none;">
    ❌ No hay conexión o los datos no están disponibles.
  </div>

<script>
fetch("$siteInfoApi")
  .then(res => res.json())
  .then(site => {
    document.title = site.title || "CorpSRTony Cine";
    document.getElementById("site-title").textContent = site.title || "CorpSRTony Cine";
    document.getElementById("site-footer").textContent = site.footer || "© 2025 CorpSRTony Cine. Todos los derechos reservados.";
    if(site.favicon) {
      const link = document.createElement('link');
      link.rel = 'icon';
      link.href = site.favicon;
      document.head.appendChild(link);
    }
  })
  .catch(() => console.error("No se pudo cargar configuración del sitio"));

fetch("$moviesApi")
  .then(response => {
    if (!response.ok) throw new Error("No se pudo cargar");
    return response.json();
  })
  .then(data => {
    if (data.status === "error") {
      document.getElementById("no-connection").innerHTML = "🚫 " + data.message;
      document.getElementById("no-connection").style.display = "block";
      return;
    }

    const grouped = {};
    Object.values(data).forEach(movie => {
      const cat = movie.category || "Sin categoría";
      if (!grouped[cat]) grouped[cat] = [];
      grouped[cat].push(movie);
    });

    const content = document.getElementById("content");
    Object.entries(grouped).forEach(([cat, movies]) => {
      movies = movies.slice(0, 10);

      const section = document.createElement("div");
      const title = document.createElement("h2");
      title.textContent = cat;
      title.onclick = () => location.href = 'go:' + cat.toLowerCase();
      section.appendChild(title);

      const row = document.createElement("div");
      row.className = "movie-row";

      movies.forEach(movie => {
        const id = movie.id || movie.tmdb_id || "0";
        const poster = movie.poster_path || movie.imagen || "";
        const title = movie.title || movie.titulo || "Sin título";

        const card = document.createElement("div");
        card.className = "movie";
        const img = document.createElement("img");
        img.src = "https://image.tmdb.org/t/p/w500" + poster;
        img.alt = title;
        card.appendChild(img);
        card.onclick = () => location.href = 'go:' + id;
        row.appendChild(card);
      });

      section.appendChild(row);
      content.appendChild(section);
    });
  })
  .catch(() => {
    document.getElementById("no-connection").style.display = "block";
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
<title>Generador Home PHP - CorpSRTony Cine</title>
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
  <h1>Admin panel</h1>
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
    <h1>🚀 Generador Home Peliculas</h1>
    <form method="post">
      <p>👉 Ingresa tu URL del servidor:</p>
      <input type="text" name="serverUrl" placeholder="https://tuservidor.com" required>
      <button type="submit">Generar Home HTML</button>
    </form>
    <?php if ($html_generado): ?>
      <h2>✅ HTML generado (copia o guarda como home.html):</h2>
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
