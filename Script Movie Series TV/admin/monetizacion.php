<?php
require_once '../config.php';
if (!check_session()) { header('Location: login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$is_admin = in_array($current_data['role'], ['admin', 'super_admin']);
$is_super_admin = $current_data['role'] === 'super_admin';

$monet_file = '../data/monetization.json';
$monet = file_exists($monet_file) ? json_decode(file_get_contents($monet_file), true) : [
    "enabled" => false,
    "direct_link" => "",
    "banner_image" => "",
    "banner_link" => ""
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $direct_link = trim($_POST['direct_link'] ?? '');
    $banner_image = trim($_POST['banner_image'] ?? '');
    $banner_link = trim($_POST['banner_link'] ?? '');

    $monet = [
        "enabled" => ($direct_link || ($banner_image && $banner_link)) ? true : false,
        "direct_link" => $direct_link,
        "banner_image" => $banner_image,
        "banner_link" => $banner_link
    ];

    file_put_contents($monet_file, json_encode($monet, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: monetizacion.php?ok=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Monetización - CorpSRTony</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'Inter', sans-serif; background: #f4f6fc; display:flex; min-height:100vh; }
.sidebar { width: 250px; background: #1a237e; color: white; height: 100vh; position: fixed; left:0; top:0; overflow-y:auto; transition: transform 0.3s ease; z-index: 1000; padding: 1.5rem 1rem; }
.sidebar h1 { font-size:1.4rem; margin-bottom:1.2rem; text-align:center; }
.sidebar .section-title { font-size:0.8rem; text-transform:uppercase; opacity:0.7; margin:1rem 0 0.5rem 0; padding-left:1rem; }
.sidebar a { display:flex; align-items:center; gap:10px; color:white; text-decoration:none; padding:0.5rem 1rem; border-radius:6px; margin-bottom:0.3rem; font-size:0.95rem; }
.sidebar a:hover { background: rgba(255,255,255,0.2); }
.hamburger { position: fixed; top: 1rem; left: 1rem; font-size: 1.5rem; background: #1a237e; color: white; border: none; padding: 0.6rem; border-radius: 6px; z-index: 1100; cursor: pointer; display: none; }
.main-content { flex:1; margin-left: 250px; padding: 2rem; display:flex; flex-direction:column; }
.alert { background:#e8f5e9; color:#2e7d32; padding:0.8rem; border-left:5px solid #4caf50; border-radius:6px; margin-bottom:1.5rem; text-align:center; font-weight:bold; }
.form-group { margin-bottom:1.5rem; }
label { display:block; margin-bottom:0.5rem; font-weight:600; }
input[type="text"] { width:100%; padding:0.8rem; border:1px solid #ccc; border-radius:6px; }
button { background:#1a237e; color:white; padding:0.8rem 1.5rem; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
button:hover { background:#3949ab; }
.tutorial { background:#f9f9f9; padding:1rem; border-radius:8px; margin-top:1.5rem; }
.tutorial h3 { margin-top:0; color:#1a237e; }
.tutorial ul li { margin-bottom:0.8rem; }
@media(max-width:768px){ .hamburger { display:block; } .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left:0; padding-top:4rem; } }
</style>
</head>
<body>
<button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
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
  <a href="monetizacion.php" style="background: rgba(255,255,255,0.2);"><i class="fas fa-dollar-sign"></i> Monetización</a>
  <a href="telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <?php endif; ?>
  <div class="section-title">Usuario</div>
  <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
  <?php if(isset($_GET['ok'])): ?>
  <div class="alert"><i class="fas fa-check"></i> Configuración guardada correctamente.</div>
  <?php endif; ?>

  <h2><i class="fas fa-dollar-sign"></i> Configuración de Monetización</h2>
  <form method="POST">
    <div class="form-group">
      <label>Direct Link para PopUnder (Monetag / Adsterra)</label>
      <input type="text" name="direct_link" value="<?= htmlspecialchars($monet['direct_link'] ?? '') ?>" placeholder="Ej: https://directlink.monetag.com/abc123">
    </div>
    <div class="form-group">
      <label>URL de Imagen del Banner (728x90 recomendado)</label>
      <input type="text" name="banner_image" value="<?= htmlspecialchars($monet['banner_image'] ?? '') ?>" placeholder="Ej: https://misitio.com/banner.png">
    </div>
    <div class="form-group">
      <label>URL donde redirige el Banner al hacer click</label>
      <input type="text" name="banner_link" value="<?= htmlspecialchars($monet['banner_link'] ?? '') ?>" placeholder="Ej: https://midestino.com">
    </div>
    <button type="submit"><i class="fas fa-save"></i> Guardar Configuración</button>
  </form>

  <div style="margin-top:1rem; padding:1rem; background:#fff3cd; color:#856404; border-left:5px solid #ffeeba; border-radius:6px;">
    ⚠️ Si completas el Direct Link se activará el popunder al hacer clic en el sitio.<br>
    Si colocas imagen + link del banner, se mostrará automáticamente.
  </div>
<div style="margin-top: 1.5rem; text-align: center;">
  <button onclick="window.location.href='monetipelis.php'">
    💰 Monetizar sección películas
  </button>
</div>

  <div class="tutorial">
    <h3>🚀 ¿No tienes cuenta?</h3>
    <ul>
      <li><a href="https://monetag.com/?ref_id=uHHQ" target="_blank" style="color:#1a237e;font-weight:bold;">Monetag.com</a> (Direct Links / PopUnder)</li>
      <li><a href="https://beta.publishers.adsterra.com/referral/FmyuntKiTU" target="_blank" style="color:#1a237e;font-weight:bold;">Adsterra.com</a> (Direct Links / PopUnder)</li>
  </div>
</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
}
</script>
<script src="../js/protect.js"></script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>

</body>
</html>
