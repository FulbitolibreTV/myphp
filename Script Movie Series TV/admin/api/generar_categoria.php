<?php
$html_generado = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serverUrl'], $_POST['categoria'])) {
    $url = rtrim(trim($_POST['serverUrl']), '/');
    $categoria = trim($_POST['categoria']); // <== ✅ Aquí capturas la categoría
    if ($url && $categoria) {
        $moviesApi = $url . "/admin/api/movies.php";
        $siteInfoApi = $url . "/admin/api/seriesApi.php";
        $html_generado = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Categoría - Cine SRTony</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Orbitron', sans-serif;
      background: radial-gradient(circle at top left, #0f0f1a, #000);
      color: #0ff;
      margin: 0;
      padding-bottom: 100px;
    }

    header {
      background: #000;
      padding: 20px;
      display: flex;
      align-items: center;
      border-bottom: 2px solid #0ff;
      box-shadow: 0 0 10px #0ff80;
    }

    header i {
      color: #0ff;
      font-size: 1.5rem;
      margin-right: 15px;
      cursor: pointer;
      text-shadow: 0 0 8px #0ff;
    }

    header h1 {
      font-size: 1.5rem;
      color: #0ff;
      text-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    }

    h2 {
      margin: 20px;
      font-size: 1.4rem;
      color: #0ff;
      text-shadow: 0 0 8px #0ff;
    }

    .movie-list, .series-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 20px;
      padding: 0 20px;
    }

    .movie, .serie {
      background: #111;
      border: 2px solid #0ff80;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 0 12px #0ff60;
      transition: transform 0.3s, box-shadow 0.3s;
      cursor: pointer;
    }

    .movie:hover, .serie:hover {
      transform: scale(1.05);
      box-shadow: 0 0 20px #ff0, 0 0 40px #ff0;
    }

    .movie img, .serie img {
      width: 100%;
      display: block;
      border-bottom: 2px solid #0ff40;
    }

    footer {
      text-align: center;
      padding: 10px;
      background: #000;
      color: #0ff;
      font-size: 14px;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      border-top: 2px solid #0ff40;
      box-shadow: 0 -2px 10px #0ff60;
    }
  </style>
</head>
<body>

  <header>
    <i class="fas fa-arrow-left" onclick="window.location.href='go:home'"></i>
    <h1 id="categoria-titulo">Películas y Series</h1>
  </header>

  <h2 id="categoria-subtitulo">Filtrando por categoría...</h2>

  <h2>🎬 Películas</h2>
  <div class="movie-list" id="movie-list"></div>

  <h2>📺 Series</h2>
  <div class="series-list" id="series-list"></div>

  <script>
    const categoriaActual = '$categoria';
    document.getElementById('categoria-titulo').textContent = "🎬 Categoría: " + categoriaActual;
    document.getElementById('categoria-subtitulo').textContent = "Contenido de la categoría \"" + categoriaActual + "\"";

    async function cargarPeliculas() {
      try {
        const res = await fetch("$moviesApi");
        const data = await res.json();

        const listaPeliculas = document.getElementById('movie-list');
        listaPeliculas.innerHTML = '';

        for (const id in data) {
          const pelicula = data[id];
          if (pelicula.category.toLowerCase() === categoriaActual.toLowerCase()) {
            const div = document.createElement('div');
            div.className = 'movie';
            div.onclick = function () {
              window.location.href = "go:" + id;
            };

            const img = document.createElement('img');
            img.src = "https://image.tmdb.org/t/p/w500" + pelicula.poster_path;
            img.alt = pelicula.title;

            div.appendChild(img);
            listaPeliculas.appendChild(div);
          }
        }
      } catch (error) {
        console.error('❌ Error cargando películas:', error);
      }
    }

    async function cargarSeries() {
      try {
        const res = await fetch("$siteInfoApi");
        const series = await res.json();

        const listaSeries = document.getElementById('series-list');
        listaSeries.innerHTML = '';

        series.forEach(function(serie) {
          if (serie.category.toLowerCase() === categoriaActual.toLowerCase()) {
            const div = document.createElement('div');
            div.className = 'serie';
            div.onclick = function () {
              window.location.href = "go:" + serie.id;
            };

            const img = document.createElement('img');
            img.src = "https://image.tmdb.org/t/p/w500" + serie.poster_path;
            img.alt = serie.title;

            div.appendChild(img);
            listaSeries.appendChild(div);
          }
        });
      } catch (error) {
        console.error('❌ Error cargando series:', error);
      }
    }

    cargarPeliculas();
    cargarSeries();
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
    <h1>🚀 Generador Categorias</h1>
<form method="post">
  <p>👉 Ingresa tu URL del servidor:</p>
  <input type="text" name="serverUrl" placeholder="https://tuservidor.com" required>

  <p>🎯 Selecciona la categoría a filtrar:</p>
  <select name="categoria" id="categoriaSelect" required>
    <option value="">Cargando categorías...</option>
  </select>

  <button type="submit">Generar HTML</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
  fetch('categories.php')
    .then(response => response.json())
    .then(data => {
      const select = document.getElementById('categoriaSelect');
      select.innerHTML = '<option value="">Selecciona una categoría</option>';

      if (Array.isArray(data)) {
        data.forEach(cat => {
          const option = document.createElement('option');
          option.value = cat.name;          // Correcto: viene como { name: "Accion" }
          option.textContent = cat.name;
          select.appendChild(option);
        });
      } else {
        select.innerHTML = '<option value="">Error al cargar categorías</option>';
      }
    })
    .catch(error => {
      console.error('Error al cargar categorías:', error);
      document.getElementById('categoriaSelect').innerHTML = '<option value="">Error al cargar</option>';
    });
});
</script>


    <?php if ($html_generado): ?>
      <h2>✅ HTML generado (recuerda cambiar en la linea'Terror' por tu categoria):</h2>
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
