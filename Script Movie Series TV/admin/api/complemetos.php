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
<title>Complementos - CorpSRTony Cine</title>
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
.gen-card img { width:100%; border-radius:10px; margin-bottom:1rem; }
.toggle-container { text-align:center; margin-bottom:2rem; }
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
  <h2>🎯 Complementos para App Creator 24</h2>
  <p style="text-align:center;max-width:700px;margin:1rem auto 2rem;">
    Aquí tienes una guía rápida para crear las secciones <b>Favoritos</b>, <b>Historial</b> y <b>Soporte</b> en App Creator 24 
    usando tu propio servidor.
  </p>

<div class="card-container">
  <!-- Favoritos -->
  <div class="gen-card">
    <h3>Favoritos</h3>
    <img src="https://www.appcreator24.com/android-app-creator/tipo_html.png" alt="App Creator 24 HTML">
    <p>
      ➔ <b>Título sección:</b> Favoritos<br>
      ➔ <b>Sección:</b> HTML (Avanzado)<br>
      ➔ <b>Referencia:</b> No<br>
      ➔ <b>Incluir en menú:</b> No
    </p>
    <div style="margin-top:1rem;text-align:left;font-size:0.9rem;">
      <b>Pasos rápidos:</b><br>
      • Abre <b>favoritos.html</b> y reemplaza <code>https://tuservidor.com</code> por tu URL.<br>
      • En App Creator 24 ➔ <b>Sección ➔ HTML (Avanzado)</b><br>
      • En <b>Título sección:</b> Favoritos.<br>
      • Pega el HTML.<br>
      • En <b>Referencia:</b> No. En <b>Incluir en menú:</b> No.<br>
      • Guarda y listo.
    </div>
    <a href="comple/favoritos.html" download style="display:inline-block;margin-top:1rem;background:#1a237e;color:white;padding:0.5rem 1rem;border-radius:8px;text-decoration:none;">
      ⬇ Descargar favoritos.html
    </a>
  </div>

  <!-- Historial -->
  <div class="gen-card">
    <h3>Historial</h3>
    <img src="https://www.appcreator24.com/android-app-creator/tipo_html.png" alt="App Creator 24 HTML">
    <p>
      ➔ <b>Título sección:</b> Historial<br>
      ➔ <b>Sección:</b> HTML (Avanzado)<br>
      ➔ <b>Referencia:</b> No<br>
      ➔ <b>Incluir en menú:</b> No
    </p>
    <div style="margin-top:1rem;text-align:left;font-size:0.9rem;">
      <b>Pasos rápidos:</b><br>
      • Abre <b>historial.html</b> y reemplaza <code>https://tuservidor.com</code> por tu URL.<br>
      • En App Creator 24 ➔ <b>Sección ➔ HTML (Avanzado)</b><br>
      • En <b>Título sección:</b> Historial.<br>
      • Pega el HTML.<br>
      • En <b>Referencia:</b> No. En <b>Incluir en menú:</b> No.<br>
      • Guarda y listo.
    </div>
    <a href="comple/historial.html" download style="display:inline-block;margin-top:1rem;background:#1a237e;color:white;padding:0.5rem 1rem;border-radius:8px;text-decoration:none;">
      ⬇ Descargar historial.html
    </a>
  </div>

  <!-- Soporte -->
  <div class="gen-card">
    <h3>Soporte</h3>
    <img src="https://www.appcreator24.com/android-app-creator/tipo_html.png" alt="App Creator 24 HTML">
    <p>
      ➔ <b>Título sección:</b> Soporte<br>
      ➔ <b>Sección:</b> HTML (Avanzado)<br>
      ➔ <b>Referencia:</b> No<br>
      ➔ <b>Incluir en menú:</b> No
    </p>
    <div style="margin-top:1rem;text-align:left;font-size:0.9rem;">
      <b>Pasos rápidos:</b><br>
      • Abre <b>soporte.html</b> y reemplaza <code>AQUI TU NUMERO</code> con tu número WhatsApp, ejemplo:<br>
      <b>573245945588</b><br>
      • En App Creator 24 ➔ <b>Sección ➔ HTML (Avanzado)</b><br>
      • En <b>Título sección:</b> Soporte.<br>
      • Pega el HTML.<br>
      • En <b>Referencia:</b> No. En <b>Incluir en menú:</b> No.<br>
      • Guarda y listo.
    </div>
    <a href="comple/soporte.html" download style="display:inline-block;margin-top:1rem;background:#1a237e;color:white;padding:0.5rem 1rem;border-radius:8px;text-decoration:none;">
      ⬇ Descargar soporte.html
    </a>
  </div>
</div>

  <p style="margin-top:2rem;text-align:center;color:#c62828;font-weight:bold;">
    ⚠️ Ojo: si ya tienes una sección creada, puedes simplemente <b>duplicarla</b> y cambiar sus parámetros como te mostramos arriba.
  </p>
</div>

<script>
function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('active'); }
</script>
<script src="../../js/protect.js"></script>


</body>
</html>
