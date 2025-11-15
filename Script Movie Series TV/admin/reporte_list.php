<?php
require_once '../config.php';
if (!check_session()) { header('Location: login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$is_admin = in_array($current_data['role'], ['admin', 'super_admin']);
$is_super_admin = $current_data['role'] === 'super_admin';

$reportes_file = '../data/reportes.json';
$historial_file = '../data/historial_reportes.json';

$reportes = file_exists($reportes_file) ? json_decode(file_get_contents($reportes_file), true) : [];
$historial = file_exists($historial_file) ? json_decode(file_get_contents($historial_file), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clear_history'])) {
        file_put_contents($historial_file, json_encode([], JSON_PRETTY_PRINT));
        header("Location: reporte_list.php");
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    foreach ($reportes as $index => $r) {
        if (($r['id'] ?? null) == $id) {
            $registro = $r;
            $registro['accion'] = ($action === 'resolve') ? 'resuelto' : 'eliminado';
            $registro['fecha_accion'] = date('Y-m-d H:i:s');

            $historial[] = $registro;
            array_splice($reportes, $index, 1);

            file_put_contents($historial_file, json_encode($historial, JSON_PRETTY_PRINT));
            file_put_contents($reportes_file, json_encode($reportes, JSON_PRETTY_PRINT));
            break;
        }
    }
    header("Location: reporte_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Reportes - Admin Panel</title>
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
  flex:1; margin-left: 250px; padding: 2rem; display:flex; flex-direction:column;
}
.report-container {
  display: flex; flex-direction: column; gap: 1rem; max-width:900px; margin: 0 auto;
}
.report-card {
  background: white; padding:1.2rem 1.5rem; border-radius:10px;
  box-shadow:0 4px 12px rgba(0,0,0,0.08);
  transition: transform 0.2s;
}
.report-card:hover { transform: translateY(-3px); }
.report-card h3 { color:#1a237e; margin-bottom:0.5rem; }
.report-card p { margin:0.3rem 0; }
.action-buttons form {
  display:inline-block; margin-right:0.5rem;
}
.action-buttons button {
  background:#1a237e; color:white; border:none; padding:0.4rem 0.8rem;
  border-radius:6px; cursor:pointer; font-size:0.85rem; font-weight:bold;
  transition: background 0.3s;
}
.action-buttons button:hover { background:#3949ab; }
.clear-history {
  margin: 2rem auto 0 auto; text-align: center;
}
.clear-history button {
  background:#c62828; color:white; padding:0.6rem 1.2rem; border:none;
  border-radius:6px; font-size:0.9rem; cursor:pointer; font-weight:bold;
}
.clear-history button:hover { background:#e53935; }
@media(max-width:768px){
  .hamburger { display:block; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  .main-content { margin-left:0; padding-top:4rem; }
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
  <h2 style="text-align:center;color:#1a237e;margin-bottom:2rem;">
    <i class="fas fa-bell"></i> Lista de reportes
  </h2>

  <div class="report-container">
  <?php if (!empty($reportes)): ?>
    <?php foreach($reportes as $r): ?>
      <div class="report-card">
        <h3><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($r['tipo'] ?? 'Tipo desconocido') ?></h3>
        <?php if (!empty($r['pelicula'])): ?>
          <p><strong>Película:</strong> <?= htmlspecialchars($r['pelicula']) ?></p>
        <?php endif; ?>
        <?php if (!empty($r['nombrePublicidad']) || !empty($r['contactoPublicidad'])): ?>
          <p><strong>Nombre:</strong> <?= htmlspecialchars($r['nombrePublicidad']) ?></p>
          <p><strong>Contacto:</strong> <?= htmlspecialchars($r['contactoPublicidad']) ?></p>
        <?php endif; ?>
        <p><strong>Mensaje:</strong> <?= htmlspecialchars($r['mensaje'] ?? '') ?></p>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($r['fecha'] ?? '') ?></p>
        <div class="action-buttons">
          <form method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($r['id'] ?? '') ?>">
            <input type="hidden" name="action" value="resolve">
            <button type="submit"><i class="fas fa-check"></i> Marcar resuelto</button>
          </form>
          <form method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($r['id'] ?? '') ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" style="background:#c62828;"><i class="fas fa-trash"></i> Eliminar</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="text-align:center;">No hay reportes registrados.</p>
  <?php endif; ?>
  </div>

  <?php if (!empty($historial)): ?>
  <div style="margin-top:3rem;">
    <h2 style="text-align:center;color:#1a237e;"><i class="fas fa-archive"></i> Historial de reportes</h2>
    <div class="report-container">
      <?php foreach($historial as $h): ?>
        <div class="report-card">
          <h3><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($h['tipo'] ?? 'Tipo desconocido') ?></h3>
          <?php if (!empty($h['pelicula'])): ?>
            <p><strong>Película:</strong> <?= htmlspecialchars($h['pelicula']) ?></p>
          <?php endif; ?>
          <?php if (!empty($h['nombrePublicidad']) || !empty($h['contactoPublicidad'])): ?>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($h['nombrePublicidad']) ?></p>
            <p><strong>Contacto:</strong> <?= htmlspecialchars($h['contactoPublicidad']) ?></p>
          <?php endif; ?>
          <p><strong>Mensaje:</strong> <?= htmlspecialchars($h['mensaje'] ?? '') ?></p>
          <p><strong>Fecha reporte:</strong> <?= htmlspecialchars($h['fecha'] ?? '') ?></p>
          <p><strong>Acción:</strong> <?= htmlspecialchars($h['accion'] ?? '') ?> en <?= htmlspecialchars($h['fecha_accion'] ?? '') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="clear-history">
      <form method="POST">
        <input type="hidden" name="clear_history" value="1">
        <button type="submit"><i class="fas fa-trash"></i> Borrar historial completo</button>
      </form>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
}
</script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>
</body>
</html>
