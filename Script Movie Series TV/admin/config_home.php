<?php
require_once '../config.php';

if (!check_session()) {
    header('Location: login.php');
    exit;
}

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = $current_data['role'];
}
$current_role = $_SESSION['role'];

$is_admin = in_array($current_role, ['admin', 'super_admin']);
$is_super_admin = $current_role === 'super_admin';

if (!$is_admin) {
    die('<h2 style="color:red;text-align:center;">⛔ Acceso restringido solo para administradores.</h2>');
}

$siteFile = '../data/site_info.json';
$data = [
    "title" => "🎥 CorpSRTony Cine",
    "favicon" => "assets/favicon.ico",
    "footer" => "© 2026 CorpSRTony Cine. Todos los derechos reservados."
];

if (file_exists($siteFile)) {
    $json = file_get_contents($siteFile);
    $data = json_decode($json, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title'] = $_POST['title'];
    $data['footer'] = $_POST['footer'];

    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        if (!empty($data['favicon']) && file_exists('../' . $data['favicon'])) {
            unlink('../' . $data['favicon']);
        }
        $ext = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
        $path = 'assets/favicon.' . $ext;
        move_uploaded_file($_FILES['favicon']['tmp_name'], '../' . $path);
        $data['favicon'] = $path;
    }

    file_put_contents($siteFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: config_home.php?ok=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Configurar Página - CorpSRTony</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
    <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',sans-serif;background:#f4f6fc;display:flex;min-height:100vh;}
    .sidebar{
      width:250px;background:#1a237e;color:white;height:100vh;
      position:fixed;left:0;top:0;overflow-y:auto;transition:transform 0.3s ease;
      z-index:1000;padding:1.5rem 1rem;
    }
    .sidebar h1{font-size:1.4rem;margin-bottom:1.2rem;text-align:center;}
    .sidebar .section-title{
      font-size:0.8rem;text-transform:uppercase;opacity:0.7;
      margin:1rem 0 0.5rem 0;padding-left:1rem;
    }
    .sidebar a{
      display:flex;align-items:center;gap:10px;
      color:white;text-decoration:none;padding:0.5rem 1rem;
      border-radius:6px;margin-bottom:0.3rem;font-size:0.95rem;
    }
    .sidebar a:hover{background:rgba(255,255,255,0.2);}
    .hamburger{
      position:fixed;top:1rem;left:1rem;font-size:1.5rem;
      background:#1a237e;color:white;border:none;padding:0.6rem;
      border-radius:6px;z-index:1100;cursor:pointer;display:none;
    }
    .main-content{flex:1;margin-left:250px;padding:2rem;}
    @media(max-width:768px){
      .hamburger{display:block;}
      .sidebar{transform:translateX(-100%);}
      .sidebar.active{transform:translateX(0);}
      .main-content{margin-left:0;padding-top:4rem;}
    }
    .card{
      background:white;padding:2rem;border-radius:10px;
      box-shadow:0 0 15px rgba(0,0,0,0.1);max-width:600px;margin:1rem auto;
    }
    .input, select{
      width:100%;padding:0.8rem;margin:0.8rem 0;
      border:1px solid #ccc;border-radius:6px;
    }
    .btn{display:inline-block;padding:0.6rem 1rem;border:none;
      border-radius:6px;cursor:pointer;font-weight:bold;
      background:#1a237e;color:white;}
    .alert{padding:0.8rem;background:#e3f2fd;border-left:5px solid #1a237e;margin-bottom:1rem;}
    .favicon-preview img{height:60px;}
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
    <a href="monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
    <a href="telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <?php endif; ?>
  <div class="section-title">Usuario</div>
  <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>


<div class="main-content">
    <h2><i class="fas fa-paint-roller"></i> Configuración del sitio</h2>
    <?php if (isset($_GET['ok'])): ?>
        <p class="alert">✅ Configuración guardada correctamente.</p>
    <?php endif; ?>
    <div class="card">
        <form method="POST" enctype="multipart/form-data">
            <label><i class="fas fa-heading"></i> Título del sitio:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" required class="input">

            <label><i class="fas fa-paragraph"></i> Pie de página:</label>
            <input type="text" name="footer" value="<?= htmlspecialchars($data['footer']) ?>" required class="input">

            <label><i class="fas fa-image"></i> Favicon actual:</label>
            <?php if (!empty($data['favicon']) && file_exists('../' . $data['favicon'])): ?>
                <div class="favicon-preview">
                    <img src="../<?= $data['favicon'] ?>" alt="Favicon actual">
                </div>
            <?php else: ?>
                <p style="color:#999;">No hay favicon cargado actualmente.</p>
            <?php endif; ?>

            <input type="file" name="favicon" accept=".png,.ico,.jpg,.jpeg,.svg" class="input">
            <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar configuración</button>
        </form>
    </div>
</div>

<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('active');}
</script>
<script src="../js/protect.js"></script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>

</body>
</html>
