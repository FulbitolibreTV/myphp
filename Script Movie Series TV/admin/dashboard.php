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

$movies = json_decode(file_get_contents('../data/movies.json') ?: '[]', true);
$series_dir = '../data/series/';
$series_files = glob($series_dir . '*.json');
$series = [];

foreach ($series_files as $file) {
    $content = json_decode(file_get_contents($file), true);
    if (isset($content['id'])) {
        $series[$content['id']] = $content;
    }
}
$categories = json_decode(file_get_contents('../data/categories.json') ?: '[]', true);

$views_file = '../data/views.json';
$views_data = file_exists($views_file) ? json_decode(file_get_contents($views_file), true) : [];
arsort($views_data);
$top_views = array_slice($views_data, 0, 5, true);

$labels = [];
$counts = [];

foreach ($top_views as $id => $count) {
    if (isset($movies[$id])) {
        $title = $movies[$id]['title'];
    } elseif (isset($series[$id])) {
        $title = $series[$id]['title'];
    } else {
        $title = "ID $id";
    }

    $labels[] = $title;
    $counts[] = $count;
}
$series_dir = '../data/series/';
$series_files = glob($series_dir . '*.json');
$series = is_array($series_files) ? $series_files : [];
// Cargar canales TV
$tv_file = '../data/tv_channels.json';
$tv_channels = file_exists($tv_file) ? json_decode(file_get_contents($tv_file), true) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Panel - CorpSRTony</title>
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
.sidebar h1 {
  font-size:1.4rem; margin-bottom:1.2rem; text-align:center;
}
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
.stats-container {
  display: flex; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap; justify-content: center;
}
.stat-card {
  background:white; padding:1.5rem; border-radius:12px;
  box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center;
  flex:1 1 100px; max-width:160px;
}
.stat-number { font-size:2rem; color:#1a237e; font-weight:bold; }
.footer-panel { margin-top:2rem; text-align:center; }
.footer-panel a, .footer-panel button {
  background:#1a237e; color:white; text-decoration:none;
  padding:0.5rem 0.8rem; border-radius:8px; font-size:0.85rem;
  display:inline-block; margin:0.3rem; font-weight:bold; border:none; cursor:pointer;
}
.analytics-panel {
  background: white; border-radius: 12px; 
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  padding: 1rem; max-width: 850px; margin: 2rem auto;
}
#resetModal {
  display:none; position:fixed;top:0;left:0;width:100vw;height:100vh;
  background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1200;
}
#resetModal .modal-content {
  background:white; padding:2rem; border-radius:12px; width:90%; max-width:400px; text-align:center;
}
@media(max-width:768px){
  .hamburger { display:block; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  .main-content { margin-left:0; padding-top:4rem; }
}
.footer-panel {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.5rem;
}

.footer-btn {
  background: #1a237e;
  color: white;
  text-decoration: none;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  border: none;
  cursor: pointer;
  width: 180px;
  text-align: center;
  gap: 8px;
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

  <!-- 🔢 Tarjetas de estadísticas -->
<div class="stats-container">
  <div class="stat-card">
    <div class="stat-number"><?= count($movies) ?></div>
    <div>Películas</div>
  </div>

  <div class="stat-card">
    <div class="stat-number"><?= count($categories) ?></div>
    <div>Categorías</div>
  </div>

  <div class="stat-card">
    <div class="stat-number"><?= count($series) ?></div>
    <div>Series</div>
  </div>

  <div class="stat-card">
    <div class="stat-number"><?= count($tv_channels) ?></div>
    <div>Canales TV</div>
  </div>
</div>


  <!-- 🔧 Footer / Documentación -->
<div class="footer-panel">
  <a href="../documentacion.php" class="footer-btn">
    <i class="fas fa-book"></i> Documentación
  </a>

  <?php if ($is_super_admin): ?>
    <button onclick="document.getElementById('resetModal').style.display='flex'" class="footer-btn">
      <i class="fas fa-trash-alt"></i> Resetear Todo
    </button>

    <a href="tutoriales.php" class="footer-btn">
      <i class="fas fa-graduation-cap"></i> Tutoriales
    </a>

    <a href="herramientas.php" class="footer-btn">
      <i class="fas fa-wrench"></i> Herramientas
    </a>
  <?php endif; ?>
</div>

  
  <!-- 📘 Panel Analíticas -->
  <div class="analytics-panel">
    <h3 style="text-align:center;">📈 Películas y Series más vistas</h3>
    <canvas id="viewsChart" style="max-width:800px;margin:20px auto;display:block;"></canvas>
<h4 style="text-align:center;margin-top:2rem;">Top 5 más vistos (Películas y Series)</h4>

<ul style="list-style:none;padding:0;max-width:500px;margin:1rem auto;">
<?php foreach ($top_views as $item_id => $count): 
    $title = "ID $item_id";

    // Buscar en el arreglo de películas (cargado desde movies.json)
    if (isset($movies[$item_id])) {
        $title = htmlspecialchars($movies[$item_id]['title'] ?? "Película sin título");
    
    // Si no está en movies.json, buscar como archivo de serie
    } else {
        $series_file = __DIR__ . "/../data/series/{$item_id}.json";
        if (file_exists($series_file)) {
            $series_data = json_decode(file_get_contents($series_file), true);
            $title = htmlspecialchars($series_data['title'] ?? "Serie sin título");
        }
    }
?>
    <li style="background:#fff;border-radius:8px;
               padding:0.6rem 1rem;margin:0.5rem 0;
               box-shadow:0 2px 6px rgba(0,0,0,0.1);">
      <?= $title ?> 
      <strong style="color:#1a237e;">(<?= $count ?> vistas)</strong>
    </li>
<?php endforeach; ?>
</ul>


  </div>

</div> <!-- cierre de main-content -->

  </ul>
</div>

<div id="resetModal">
  <div class="modal-content">
    <h2><i class="fas fa-exclamation-triangle" style="color:#c62828;"></i> Confirmar Reinicio</h2>
    <p>¿Seguro que deseas eliminar películas y categorías? Esta acción es irreversible.</p>
    <form method="POST" action="reset.php">
      <input type="password" name="confirm_password" placeholder="Confirma tu contraseña" required>
      <div style="margin-top:1rem;">
        <button type="submit">Sí, borrar</button>
        <button type="button" onclick="document.getElementById('resetModal').style.display='none'">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
}
const ctx = document.getElementById('viewsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Vistas',
            data: <?= json_encode($counts) ?>,
            backgroundColor: '#1a237e',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true },
          x: {
            ticks: { display: false },
            grid: { display: false }
          }
        }
    }
});
</script>

<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>

</body>
</html>
