<?php
require_once '../config.php';
if (!check_session()) { header('Location: login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$user_data = $users[$current_user] ?? ['role'=>'editor','profile_image'=>'perfil.png'];
$current_name = $_SESSION['name'] ?? $current_user;
$is_admin = in_array($user_data['role'], ['admin', 'super_admin']);
$is_super_admin = $user_data['role'] === 'super_admin';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['new_username'] ?? '';
    $current_pass = $_POST['current_password'] ?? '';
    $new_hash = $_POST['new_hash'] ?? '';
    $updated = false;

    // 🔄 CAMBIO DE USUARIO SI ES SUPER ADMIN
    if ($is_super_admin && !empty($new_username) && $new_username !== $current_user) {
        if (isset($users[$new_username])) {
            $error_msg = "❌ El nuevo nombre de usuario ya está en uso.";
        } else {
            $users[$new_username] = $user_data;
            unset($users[$current_user]);

            // Renombrar imagen si existe
            $old_image_path = '../assets/' . ($user_data['profile_image'] ?? '');
            $ext = pathinfo($old_image_path, PATHINFO_EXTENSION);
            $new_image = 'profiles/' . $new_username . '.' . $ext;
            $new_image_path = '../assets/' . $new_image;

            if (file_exists($old_image_path)) {
                rename($old_image_path, $new_image_path);
                $user_data['profile_image'] = $new_image;
            }

            $current_user = $new_username;
            $_SESSION['username'] = $new_username;
            $_SESSION['name'] = $new_username;
            $updated = true;
            $success_msg = "✅ Usuario actualizado correctamente.";
        }
    }

    // 💬 CAMBIO DE CONTRASEÑA
    if (!empty($new_hash)) {
        if (isset($user_data['password']) && verify_password($current_pass, $user_data['password'])) {
            $user_data['password'] = $new_hash;
            $updated = true;
        } else {
            $error_msg = "❌ La contraseña actual es incorrecta.";
        }
    }

    // 🖼️ CAMBIO DE IMAGEN
    if (!empty($_FILES['profile_image']['tmp_name'])) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = $current_user . '.' . strtolower($ext);
        $target = '../assets/profiles/' . $filename;
        if (!file_exists('../assets/profiles')) { mkdir('../assets/profiles', 0755, true); }
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target);
        $user_data['profile_image'] = 'profiles/' . $filename;
        $updated = true;
    }

    // ✅ GUARDAR CAMBIOS EN JSON
    if ($updated) {
        $users[$current_user] = $user_data;
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if (empty($success_msg)) {
            $success_msg = "✅ Perfil actualizado correctamente.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Mi Perfil - CorpSRTony</title>
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
.form-container { max-width:600px; background:white; padding:2rem; margin:auto; border-radius:12px; box-shadow:0 0 15px rgba(0,0,0,0.1); position: relative; }
.form-container h2 { color:#1a237e; margin-bottom:1rem; }
input[type="text"], input[type="password"], input[type="file"] {
  width:100%; padding:0.7rem; border:1px solid #ccc; border-radius:6px; margin-bottom:1rem;
}
button { background:#1a237e; color:white; border:none; padding:0.7rem 1.5rem; border-radius:6px; cursor:pointer; font-weight:bold; }
button:hover { background:#3949ab; }
.alert-success { color:green; font-weight:bold; margin-bottom:1rem; }
.alert-error { color:red; font-weight:bold; margin-bottom:1rem; }
.profile-img { width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid #1a237e; margin-bottom:1rem; }
.help-link { font-size:0.9rem; color:#1a237e; margin-bottom:0.5rem; }
.gestion-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background:#f44336;
  color:white;
  padding:0.5rem 1rem;
  border-radius:6px;
  font-weight:bold;
  text-decoration:none;
  margin-bottom: 1rem;
  float: right;
}
.gestion-btn:hover { background:#c62828; }
@media (max-width: 768px) {
  .gestion-btn {
    float: none;
    display: block;
    width: 100%;
    text-align: center;
  }
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
    <a href="gestionar_usuarios.php"><i class="fas fa-users-cog"></i> Usuarios</a>
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
  <div class="form-container">
    <h2><i class="fas fa-user-circle"></i> Mi Perfil</h2>

    <?php if ($is_super_admin): ?>
      <a href="gestionar_usuarios.php" class="gestion-btn">
        <i class="fas fa-users-cog"></i> Gestionar Usuarios
      </a>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
      <div class="alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
      <div class="alert-error"><?= $error_msg ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <center><img src="../assets/<?= htmlspecialchars($user_data['profile_image'] ?? 'perfil.png') ?>" class="profile-img"></center>

<?php if ($is_super_admin): ?>
  <label>Usuario</label>
  <input type="text" name="new_username" value="<?= htmlspecialchars($current_user) ?>" required>
<?php else: ?>
  <label>Usuario</label>
  <input type="text" value="<?= htmlspecialchars($current_user) ?>" disabled>
<?php endif; ?>


      <div class="help-link">
        Usa <a href="https://bcrypt.online/" target="_blank">bcrypt.online</a> para generar un hash seguro.
	   🤔 Opcional <a href="https://seo.portalapps.es/tool/bcrypt-generator" target="_blank">SeoPortal</a>
      </div>
      <div>
        <label>Contraseña actual</label>
        <input type="password" name="current_password" placeholder="Requerida para cambiar">
      </div>
      <div>
        <label>Nuevo hash bcrypt</label>
        <input type="text" name="new_hash" placeholder="Pega el hash generado">
      </div>

      <div>
        <label>Imagen de perfil</label>
        <input type="file" name="profile_image" accept="image/*">
      </div>

      <button type="submit"><i class="fas fa-save"></i> Guardar cambios</button>
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
