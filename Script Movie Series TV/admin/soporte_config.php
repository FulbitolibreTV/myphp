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

// Datos de soporte
$archivo_soporte = '../data/soporte.json';
$data_soporte = [
    "whatsapp" => "573245945588",
    "facebook" => "https://facebook.com/CorpSRTony",
    "instagram" => "https://instagram.com/CorpSRTony",
    "telegram" => "https://t.me/CorpSRTony",
    "youtube" => "https://youtube.com/@CorpSRTony"
];
if (file_exists($archivo_soporte)) {
    $json = file_get_contents($archivo_soporte);
    $data_json = json_decode($json, true);
    foreach ($data_soporte as $k => $v) {
        $data_soporte[$k] = isset($data_json[$k]) ? $data_json[$k] : $v;
    }
}

// Datos del cliente
$archivo_cliente = '../data/datos_cliente.json';
$datos_cliente = [];
if (file_exists($archivo_cliente)) {
    $json_cliente = file_get_contents($archivo_cliente);
    $datos_cliente = json_decode($json_cliente, true);
}

// Guardar datos del cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardarCliente'])) {
    $datos_cliente = [
        'nombre' => trim($_POST['nombre']),
        'correo' => trim($_POST['correo']),
        'web' => trim($_POST['web']),
        'licencia' => trim($_POST['licencia'])
    ];
    file_put_contents($archivo_cliente, json_encode($datos_cliente, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: soporte_config.php?ok=1');
    exit;
}

// Subir y procesar ZIP de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subirUpdate'])) {
    if (isset($_FILES['zip_file']) && $_FILES['zip_file']['error'] === 0) {
        $zip_tmp = $_FILES['zip_file']['tmp_name'];
        $zip = new ZipArchive;
        if ($zip->open($zip_tmp) === TRUE) {
            $zip->extractTo('../'); // Extrae en el directorio raíz del proyecto
            $zip->close();
            echo "<script>alert('✅ Actualización completada correctamente'); window.location='soporte_config.php';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Error al abrir el archivo ZIP');</script>";
        }
    } else {
        echo "<script>alert('❌ Error al subir el archivo');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Soporte - CorpSRTony</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',sans-serif;background:#f4f6fc;display:flex;min-height:100vh;}
    .sidebar{width:250px;background:#1a237e;color:white;height:100vh;position:fixed;left:0;top:0;overflow-y:auto;transition:transform 0.3s ease;z-index:1000;padding:1.5rem 1rem;}
    .sidebar h1{font-size:1.4rem;margin-bottom:1.2rem;text-align:center;}
    .sidebar .section-title{font-size:0.8rem;text-transform:uppercase;opacity:0.7;margin:1rem 0 0.5rem 0;padding-left:1rem;}
    .sidebar a{display:flex;align-items:center;gap:10px;color:white;text-decoration:none;padding:0.5rem 1rem;border-radius:6px;margin-bottom:0.3rem;font-size:0.95rem;}
    .sidebar a:hover{background:rgba(255,255,255,0.2);}
    .hamburger{position:fixed;top:1rem;left:1rem;font-size:1.5rem;background:#1a237e;color:white;border:none;padding:0.6rem;border-radius:6px;z-index:1100;cursor:pointer;display:none;}
    .main-content{flex:1;margin-left:250px;padding:2rem;}
    @media(max-width:768px){.hamburger{display:block;}.sidebar{transform:translateX(-100%);}.sidebar.active{transform:translateX(0);}.main-content{margin-left:0;padding-top:4rem;}}
    .card{background:white;padding:2rem;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.1);max-width:600px;margin:1rem auto;}
    .input,select,textarea{width:100%;padding:0.8rem;margin:0.8rem 0;border:1px solid #ccc;border-radius:6px;}
    .btn{display:inline-block;padding:0.6rem 1.2rem;border:none;border-radius:6px;cursor:pointer;font-weight:bold;background:#1a237e;color:white;}
    .btn:hover{background:#3949ab;}
    .alert{padding:0.8rem;background:#e8f5e9;color:#2e7d32;border-left:5px solid #4caf50;margin-bottom:1rem;}
    .modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:2000;}
    .modal-content{background:white;padding:2rem;border-radius:10px;width:90%;max-width:500px;}
    .copy-btn{background:#f0f0f0;border:1px solid #ccc;padding:0.3rem 0.6rem;border-radius:5px;cursor:pointer;margin-left:8px;}
    .copy-btn:hover{background:#e0e0e0;}
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
  <h2><i class="fas fa-headset"></i> Información de Soporte</h2>
  <?php if (isset($_GET['ok'])): ?>
    <p class="alert"><i class="fas fa-check"></i> Datos guardados correctamente</p>
  <?php endif; ?>

  <div class="card">
    <h3>🚀 Actualizar Sistema</h3>
    <p style="margin-bottom:1rem;">
      Coloca tu archivo <strong>.zip</strong> de nuevas actualizaciones que descargaste de
      <a href="https://s.corpsrtony.com" target="_blank">https://s.corpsrtony.com</a>.
    </p>
    <button class="btn" onclick="document.getElementById('modalUpdate').style.display='flex'">
      <i class="fas fa-upload"></i> Actualizar
    </button>
  </div>

  <div class="card">
    <h3>💼 Datos del Cliente</h3>
    <?php if ($datos_cliente): ?>
      <p><i class="fas fa-user"></i> Nombre: <?= htmlspecialchars($datos_cliente['nombre']) ?>
        <button class="copy-btn" onclick="copiar('<?= htmlspecialchars($datos_cliente['nombre']) ?>')"><i class="fas fa-copy"></i></button></p>
      <p><i class="fas fa-envelope"></i> Correo: <?= htmlspecialchars($datos_cliente['correo']) ?>
        <button class="copy-btn" onclick="copiar('<?= htmlspecialchars($datos_cliente['correo']) ?>')"><i class="fas fa-copy"></i></button></p>
      <p><i class="fas fa-globe"></i> Sitio Web: <?= htmlspecialchars($datos_cliente['web']) ?>
        <button class="copy-btn" onclick="copiar('<?= htmlspecialchars($datos_cliente['web']) ?>')"><i class="fas fa-copy"></i></button></p>
      <p><i class="fas fa-key"></i> Licencia: <?= htmlspecialchars($datos_cliente['licencia']) ?>
        <button class="copy-btn" onclick="copiar('<?= htmlspecialchars($datos_cliente['licencia']) ?>')"><i class="fas fa-copy"></i></button></p>
    <?php else: ?>
      <button class="btn" onclick="document.getElementById('modalCompra').style.display='flex'">
        Guardar datos de compra
      </button>
    <?php endif; ?>
  </div>
</div>

<div id="modalCompra" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Guardar Datos de Compra</h3>
    <form method="POST">
      <input class="input" type="text" name="nombre" placeholder="Tu nombre" required>
      <input class="input" type="email" name="correo" placeholder="Tu correo" required>
      <input class="input" type="text" name="web" placeholder="Sitio Web del sistema" required>
      <input class="input" type="text" name="licencia" placeholder="Licencia" required>
      <button class="btn" type="submit" name="guardarCliente"><i class="fas fa-save"></i> Guardar</button>
    </form>
  </div>
</div>

<div id="modalUpdate" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Subir archivo de actualización (.zip)</h3>
    <form method="POST" enctype="multipart/form-data">
      <input class="input" type="file" name="zip_file" accept=".zip" required>
      <button class="btn" type="submit" name="subirUpdate"><i class="fas fa-cloud-upload-alt"></i> Subir y actualizar</button>
    </form>
  </div>
</div>

<script>
function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('active'); }
function copiar(texto){
  navigator.clipboard.writeText(texto);
  alert("📋 Copiado: " + texto);
}
</script>
<script src="../js/protect.js"></script>
<?php include '../components/version_check.php'; ?>
</body>
</html>
