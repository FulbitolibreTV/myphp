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
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Generadores - CorpSRTony</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'Inter', sans-serif; background: #f4f6fc; display:flex; min-height:100vh; }
.sidebar { width: 250px; background: #1a237e; color: white; height: 100vh; position: fixed; left:0; top:0; overflow-y:auto; padding: 1.5rem 1rem; }
.sidebar h1 { font-size:1.4rem; margin-bottom:1.2rem; text-align:center; }
.sidebar .section-title { font-size:0.8rem; text-transform:uppercase; opacity:0.7; margin:1rem 0 0.5rem 0; padding-left:1rem; }
.sidebar a { display:flex; align-items:center; gap:10px; color:white; text-decoration:none; padding:0.5rem 1rem; border-radius:6px; margin-bottom:0.3rem; font-size:0.95rem; }
.sidebar a:hover { background: rgba(255,255,255,0.2); }
.hamburger { position: fixed; top: 1rem; left: 1rem; font-size: 1.5rem; background: #1a237e; color: white; border: none; padding: 0.6rem; border-radius: 6px; z-index: 1100; cursor: pointer; display: none; }
.main-content { flex:1; margin-left: 250px; padding: 2rem; display:flex; flex-direction:column; }
.card-container { display:flex; flex-wrap:wrap; gap:2rem; justify-content:center; }
.gen-card { background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center; width:220px; }
.gen-card h3 { color:#1a237e; margin-bottom:0.8rem; }
.gen-card p { font-size:0.9rem; color:#555; margin-bottom:1rem; }
.gen-card a { display:inline-block; padding:0.5rem 1rem; background:#1a237e; color:white; border-radius:8px; text-decoration:none; font-weight:bold; }
@media(max-width:768px){ .hamburger { display:block; } .sidebar { transform: translateX(-100%); position:fixed; } .sidebar.active { transform: translateX(0); } .main-content { margin-left:0; padding-top:4rem; } }
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
    <a href="generadores.php"><i class="fas fa-cogs"></i> API AppCreator24</a>
    <a href="../monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
    <a href="../telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <?php endif; ?>

  <div class="section-title">Usuario</div>
  <a href="../perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>


<div class="main-content">

  <div style="text-align:center; max-width:700px; margin:auto;">
    <p style="margin-bottom:1rem;">
      App Creator 24 es una plataforma online para crear aplicaciones Android 
      <b>sin necesidad de saber programar</b>.
    </p>
    <img src="../../imagenes/appcreator24.png" alt="App Creator 24" style="width:100%; max-width:300px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.15); margin-bottom:1rem;">
    <p>
      ¿Aún no tienes cuenta? 
      <a href="https://www.appcreator24.com/afi/1166831" target="_blank" style="color:#1a237e;font-weight:bold;text-decoration:underline;">
        Regístrate gratis aquí
      </a>.
    </p>
  </div>

  <p style="max-width:600px;margin:auto;text-align:center;margin-top:2rem;margin-bottom:2rem;">
    Desde este panel puedes generar archivos HTML o PHP listos para pegar en App Creator 24 
    y así crear listas de películas, sliders dinámicos y mucho más.
  </p>
<!-- 🔥 Botón Sistema de Usuario -->
<div style="text-align:center; margin-bottom:2rem;">
  <a href="datausuarios.php" 
     style="display:inline-block; padding:0.8rem 1.5rem; background:#1a237e; color:white; 
            border-radius:10px; font-weight:bold; text-decoration:none; 
            box-shadow:0 4px 10px rgba(0,0,0,0.2); transition:0.3s;">
    👥 Ir al Sistema de Usuario
  </a>
</div>

  <div class="card-container">
    <div class="gen-card">
      <h3>Generar Home Principal de Peliculas</h3>
      <p>Crea el Home HTML listo para pegar recuerda colocar Peliculas en Referencia.</p>
      <a href="generar_home.php">Ir</a>
    </div>
	    <div class="gen-card">
      <h3>Generar Home Principal de Series</h3>
      <p>Crea el Home Series en HTML listo  para pegar recuerda colocar Series en Referencia.</p>
      <a href="generar_home.series.php">Ir</a>
    </div>
    <div class="gen-card">
      <h3>Generador Película</h3>
      <p>Crea un archivo HTML para película  para pegar recuerda colocar id de TMDB en Referencia.</p>
      <a href="generar_pelicula.php">Ir</a>
    </div>
	    <div class="gen-card">
      <h3>Generador Series</h3>
      <p>Crea un archivo HTML para Series  para pegar recuerda colocar id de TMDB en Referencia.</p>
      <a href="generar_serie.php">Ir</a>
    </div>
	    <div class="gen-card">
      <h3>Generador Categorias</h3>
      <p>Crea un archivo HTML esto deves colocar segun categorias que tengas Ej:terror esto va en referencias.</p>
      <a href="generar_categoria.php">Ir</a>
    </div>
		    <div class="gen-card">
      <h3>Generador Favoritos</h3>
      <p>Crea un archivo HTML esto deves colocar en una seccion HTML y recuerda colocar favoritos en referencias.</p>
      <a href="generar_favorito.php">Ir</a>
    </div>
		    <div class="gen-card">
      <h3>Generador Historial</h3>
      <p>Crea un archivo HTML esto deves colocar en una seccion HTML y recuerda colocar historial en referencias.</p>
      <a href="generar_historial.php">Ir</a>
    </div>
			    <div class="gen-card">
      <h3>Generador Buscador</h3>
      <p>Crea un archivo HTML esto deves colocar en una seccion HTML y recuerda colocar buscador en referencias.</p>
      <a href="generar_buscador.php">Ir</a>
    </div>
			    <div class="gen-card">
      <h3>Generador Soporte</h3>
      <p>Crea un archivo HTML esto deves colocar en una seccion HTML y recuerda colocar soporte en referencias.</p>
      <a href="generar_soporte.php">Ir</a>
    </div>
			    <div class="gen-card">
      <h3>Generador TV</h3>
      <p>Crea un archivo HTML esto deves colocar en una seccion HTML y recuerda colocar tv en referencias.</p>
      <a href="generadortv.php">Ir</a>
    </div>
				    <div class="gen-card">
      <h3>Gestion de Clientes</h3>
      <p>Crea un archivo HTML esto deves colocar en una seccion HTML.</p>
      <a href="gestionclientes.php">Ir</a>
    </div>
  </div>
</div>

<script>
function toggleSidebar(){ 
  document.getElementById('sidebar').classList.toggle('active'); 
}
</script>
<script src="../../js/protect.js"></script>


</body>
</html>
