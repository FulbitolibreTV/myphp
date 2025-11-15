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

$msg = "";

// Solo super admin
if (!$is_super_admin) {
    die('<h2 style="color:red;text-align:center; margin-top:2rem;">⛔ Solo el Super Admin puede acceder a esta sección.</h2>');
}

// Procesos
if (!empty($_POST['new_user']) && !empty($_POST['new_hash']) && isset($_POST['role']) && $_POST['role'] !== '') {
    $user = trim($_POST['new_user']);
    $hash = trim($_POST['new_hash']);
    $role = $_POST['role'];
    if (!isset($users[$user]) && $user !== 'corpsrtony') {
        $users[$user] = ['password' => $hash, 'role' => $role, 'profile_image' => 'perfil.png'];
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        $msg = "✅ Usuario $user creado exitosamente.";
    } else {
        $msg = "⚠️ El usuario ya existe o no permitido.";
    }
}

if (!empty($_POST['edit_user']) && !empty($_POST['new_password'])) {
    $user = $_POST['edit_user'];
    if ($user !== 'corpsrtony' && isset($users[$user])) {
        $users[$user]['password'] = $_POST['new_password'];
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        $msg = "✅ Contraseña de $user actualizada.";
    }
}

if (isset($_GET['delete']) && $_GET['delete'] !== 'corpsrtony') {
    $delete = $_GET['delete'];
    if (isset($users[$delete])) {
        unset($users[$delete]);
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        $msg = "❌ Usuario $delete eliminado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Gestionar Usuarios - CorpSRTony</title>
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
@media(max-width:768px){
  .hamburger { display:block; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  .main-content { margin-left:0; padding-top:4rem; }
}

/* tabla y formularios */
table { width:100%; border-collapse: collapse; margin-bottom:2rem; }
th, td { border:1px solid #ddd; padding:0.7rem; text-align:left; }
th { background:#1a237e; color:white; }
input[type="text"], select { width:100%; padding:0.5rem; border:1px solid #ccc; border-radius:6px; margin-bottom:1rem; }
button { background:#1a237e; color:white; border:none; padding:0.5rem 1rem; border-radius:6px; cursor:pointer; }
button:hover { background:#3949ab; }
.alert { padding:0.7rem; background:#e3f2fd; color:#1a237e; font-weight:bold; margin-bottom:1rem; border-radius:6px; }
.danger { color:red; font-size:1.2rem; }
@media(max-width:480px){
  .fas.fa-trash, .fas.fa-key, .fas.fa-plus { font-size:1rem; }
}
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
  <h2><i class="fas fa-users-cog"></i> Gestionar Usuarios</h2>

  <?php if (!empty($msg)): ?>
    <div class="alert"><?= $msg ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr><th>Usuario</th><th>Rol</th><th><i class="fas fa-key"></i></th><th><i class="fas fa-trash"></i></th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user => $info): ?>
        <?php if ($user === 'corpsrtony') continue; ?>
        <tr>
          <td><?= htmlspecialchars($user) ?></td>
          <td><?= htmlspecialchars($info['role'] ?? 'Desconocido') ?></td>
          <td>
            <form method="POST" style="display:flex; gap:0.5rem;">
              <input type="hidden" name="edit_user" value="<?= htmlspecialchars($user) ?>">
              <input type="text" name="new_password" placeholder="Nuevo hash bcrypt" required>
              <button type="submit"><i class="fas fa-key"></i></button>
            </form>
          </td>
          <td>
            <a href="?delete=<?= urlencode($user) ?>" class="danger" onclick="return confirm('¿Eliminar este usuario?')">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h3>Agregar Nuevo Usuario</h3>
  <p>🛡️ Usa <a href="https://bcrypt.online/" target="_blank">https://bcrypt.online</a> para generar el hash bcrypt.</p>
  <p>🛡️ Opcional <a href="https://seo.portalapps.es/tool/bcrypt-generator" target="_blank">SeoPortal</a> para generar el hash bcrypt.</p>
  <form method="POST">
    <input type="text" name="new_user" placeholder="Nombre de usuario" required>
    <input type="text" name="new_hash" placeholder="Hash bcrypt de la contraseña" required>
    <select name="role" required>
      <option value="">Selecciona rol</option>
      <option value="admin">Administrador</option>
      <option value="editor">Editor</option>
    </select>
    <button type="submit"><i class="fas fa-plus"></i> Crear Usuario</button>
  </form>
</div>

</div>

<script>
function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('active'); }
</script>
<script src="../js/protect.js"></script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>
</body>
</html>
