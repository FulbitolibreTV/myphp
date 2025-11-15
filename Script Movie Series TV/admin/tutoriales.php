<?php
require_once '../config.php';
if (!check_session()) { header('Location: login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
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
<title>Admin Panel - En Planificación</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'Inter', sans-serif; background: #f4f6fc; display:flex; min-height:100vh; }
.sidebar {
  width: 250px; background: #1a237e; color: white; height: 100vh;
  position: fixed; left:0; top:0; overflow-y:auto;
  transition: transform 0.3s ease; z-index: 1000; padding: 1.5rem 1rem;
}
.sidebar h1 { font-size:1.4rem; margin-bottom:1.2rem; text-align:center; }
.sidebar .section-title {
  font-size:0.8rem; text-transform:uppercase;
  opacity:0.7; margin:1rem 0 0.5rem 0; padding-left:1rem;
}
.sidebar a {
  display:flex; align-items:center; gap:10px;
  color:white; text-decoration:none;
  padding:0.5rem 1rem; border-radius:6px; margin-bottom:0.3rem; font-size:0.95rem;
}
.sidebar a:hover { background: rgba(255,255,255,0.2); }
.hamburger {
  position: fixed; top: 1rem; left: 1rem; font-size: 1.5rem;
  background: #1a237e; color: white; border: none; padding: 0.6rem;
  border-radius: 6px; z-index: 1100; cursor: pointer; display: none;
}
.main-content {
  flex:1; margin-left: 250px; padding: 2rem;
  display:flex; flex-direction:column; justify-content:center; align-items:center;
  text-align:center;
}
.main-content h1 {
  font-size:2rem; color:#1a237e; margin-bottom:1rem;
}
.main-content p {
  font-size:1.2rem; color:#333;
}
@media(max-width:768px){
  .hamburger { display:block; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  .main-content { margin-left:0; padding-top:4rem; }
}
.video-container {
  position: relative;
  width: 100%;
  max-width: 720px;
  aspect-ratio: 16 / 9;
  margin: 2rem auto;
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
  border-radius: 12px;
  overflow: hidden;
}

.video-container iframe {
  width: 100%;
  height: 100%;
  border: 0;
}

</style>
</head>
<body>

<button class="hamburger" onclick="toggleSidebar()">
  <i class="fas fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
  <h1>Admin panel</h1>
  <div class="section-title">Menú Principal</div>
  <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage-series.php"><i class="fas fa-tv"></i> Series</a>
  <a href="create-movie.php"><i class="fas fa-video"></i> Crear Película</a>
  <a href="manage-categories.php"><i class="fas fa-layer-group"></i> Categorías</a>
          <a href="detv.php"><i class="fas fa-play-circle"></i> TV</a>

  <?php if ($is_admin): ?>
    <div class="section-title">⚙️ Configuración</div>
    <a href="config_home.php"><i class="fas fa-home"></i> Config Home</a>
    <a href="config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>
  <?php endif; ?>

  <?php if ($is_super_admin): ?>
    <div class="section-title">🔧 Admin Tools</div>
    <a href="soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
    <a href="configure-api.php"><i class="fas fa-key"></i> TMDB API</a>
    <a href="api/generadores.php"><i class="fas fa-cogs"></i> App Creator 24 📲</a>
    <a href="monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
    <a href="telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <?php endif; ?>

  <div class="section-title">Usuario</div>
  <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
  <h1>📺 Tutoriales</h1>
  <p>Revisa nuestros tutoriales para aprender a usar el sistema:</p>

<div class="videos">


  <div class="video-container">
    <iframe 
      src="https://www.youtube.com/embed/yAmyA80euIQ"
      title="Tutorial YouTube"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen>
    </iframe>
  </div>


  <div class="video-container">
    <iframe 
      src="https://www.youtube.com/embed/PpSjUvzjQ0A"
      title="Tutorial YouTube"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen>
    </iframe>
  </div>


  <div class="video-container">
    <iframe 
      src="https://www.youtube.com/embed/fnn6SqVjlnE"
      title="Tutorial YouTube"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen>
    </iframe>
  </div>

<style>
  .video-section {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background-color: #1e1e1e;
    border-radius: 15px;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
    color: white;
  }

  .video-title {
    font-size: 1.5rem;
    text-align: center;
    margin-bottom: 15px;
    font-weight: bold;
    color: #00ffcc;
  }

  .video-container video {
    width: 100%;
    height: auto;
    border-radius: 12px;
    outline: none;
  }

  @media (max-width: 600px) {
    .video-title {
      font-size: 1.2rem;
    }
  }
</style>

<div class="video-section">
  <div class="video-title">Sacar links de páginas</div>
  <div class="video-container">
    <video controls>
      <source src="http://files.corpsrtony.com/uploads/archivos/sacar-links-en-paginas.mp4" type="video/mp4">
      Tu navegador no soporta el video.
    </video>
  </div>
</div>


</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
}
</script>
<?php include '../components/version_check.php'; ?>
<?php include '../components/notificaciones_bell.php'; ?>
</body>
</html>
