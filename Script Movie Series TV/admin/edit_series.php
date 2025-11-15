<?php
require_once '../config.php';

if (!check_session()) {
    header('Location: login.php');
    exit;
}

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

$current_data = $users[$current_user] ?? [
    'name' => $current_user,
    'profile_image' => 'perfil.png',
    'role' => 'editor'
];

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = $current_data['role'];
}
$current_role = $_SESSION['role'];

$is_admin = in_array($current_role, ['admin', 'super_admin']);
$is_super_admin = $current_role === 'super_admin';

if (!in_array($current_role, ['editor', 'admin', 'super_admin'])) {
    die('<h2 style="color:red;text-align:center;">⛔️ Acceso restringido.</h2>');
}

$series_folder = '../data/series';
if (!is_dir($series_folder)) mkdir($series_folder, 0777, true);

$series = [];
foreach (glob($series_folder . '/*.json') as $file) {
    $id = basename($file, '.json');
    $data = json_decode(file_get_contents($file), true);
    if ($data) $series[$id] = $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $file_path = "$series_folder/$edit_id.json";

    if (file_exists($file_path)) {
        $data = json_decode(file_get_contents($file_path), true);

        $data['title'] = htmlspecialchars($_POST['title'] ?? $data['title']);
        $data['overview'] = htmlspecialchars($_POST['overview'] ?? $data['overview']);
        $data['release_date'] = htmlspecialchars($_POST['release_date'] ?? $data['release_date']);
        $data['category'] = htmlspecialchars($_POST['category'] ?? $data['category']);
        $data['trailer'] = htmlspecialchars($_POST['trailer'] ?? $data['trailer']);

        $seasons = [];
        if (!empty($_POST['seasons'])) {
            foreach ($_POST['seasons'] as $season_num => $episodes) {
                foreach ($episodes as $episode_num => $episode_data) {
                    $seasons[$season_num][$episode_num] = [
                        'url' => trim($episode_data['url'] ?? '')
                    ];
                }
            }
        }
        $data['seasons'] = $seasons;

        file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_series.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Editar Series - CorpSRTony</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'Inter', sans-serif; background: #f4f6fc; display:flex; min-height:100vh; }
.sidebar {
  width: 250px; background: #1a237e; color: white; height: 100vh;
  position: fixed; left:0; top:0; overflow-y:auto; transition: transform 0.3s ease;
  z-index: 1000; padding: 1.5rem 1rem;
}
.sidebar h1 { font-size:1.4rem; margin-bottom:1.2rem; text-align:center; }
.sidebar .section-title {
  font-size:0.8rem; text-transform:uppercase; opacity:0.7;
  margin:1rem 0 0.5rem 0; padding-left:1rem;
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
.main-content { flex:1; margin-left: 250px; padding: 2rem; }
@media(max-width:768px){
  .hamburger { display:block; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  .main-content { margin-left:0; padding-top:4rem; }
}
.search-box { margin: 1rem 0; text-align: right; }
.search-box input {
  padding: 0.5rem; width: 100%; max-width:300px;
  border-radius: 6px; border: 1px solid #ccc;
}
.table-container { overflow-x:auto; }
.table-container table { width:100%; border-collapse:collapse; margin-top:1rem; }
.table-container th, .table-container td {
  padding:0.8rem; border-bottom:1px solid #ddd; text-align:left;
}
.table-container th { background:#1a237e; color:white; }
.btn-sm {
  background:#1a237e; color:white; padding:0.4rem 0.6rem;
  border-radius:5px; font-size:0.85rem; text-decoration:none; display:inline-block; margin:0.2rem 0;
}
.btn-danger { background:#e53935; }
.btn-edit { background:#3949ab; color:white; }
.edit-section .card {
  background:white; padding:2rem; border-radius:10px;
  box-shadow:0 0 15px rgba(0,0,0,0.1); margin-top:2rem;
}
.edit-section input, .edit-section textarea {
  width:100%; padding:0.6rem; margin:0.5rem 0 1rem;
  border:1px solid #ccc; border-radius:6px;
}
.btn-secondary {
  background:#f0f0f0; border:none; padding:8px 12px;
  border-radius:5px; cursor:pointer; font-weight:bold; margin-bottom:1rem;
}
.players-list .player-input-group { display:flex; gap:0.5rem; margin-bottom:0.5rem; flex-wrap:wrap; }
.players-list input { flex:1; }
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
<?php if (isset($_GET['updated'])): ?>
  <div style="background: #e0f7fa; border-left: 5px solid #00acc1; padding: 1rem; margin-bottom: 1rem; border-radius: 6px; display: flex; align-items: center;">
    <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" width="30" height="30" style="margin-right: 10px;">
    <span style="color: #007c91; font-weight: bold;">✅ La serie fue actualizada correctamente.</span>
  </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
  <p style="color: green; font-weight: bold;">🗑️ Serie eliminada correctamente</p>
<?php endif; ?>

<?php if (empty($series)): ?>
  <p>No hay series registradas.</p>
<?php else: ?>
  <div class="search-box">
    <input type="text" id="searchInput" onkeyup="filterSeries()" placeholder="Buscar por título o ID...">
  </div>
  <div class="table-container">
    <table id="seriesTable">
      <thead>
        <tr><th>Poster</th><th>Título</th><th>Fecha</th><th>Categoría</th><th>Acciones</th></tr>
      </thead>
      <tbody>
      <?php foreach ($series as $id => $s): ?>
        <tr>
          <td><img src="https://image.tmdb.org/t/p/w200<?= $s['poster_path'] ?>" style="height:100px;border-radius:6px;"></td>
          <td><?= htmlspecialchars($s['title']) ?></td>
          <td><?= htmlspecialchars($s['release_date']) ?></td>
          <td><?= htmlspecialchars($s['category']) ?></td>
          <td>
  <button onclick="editSerie('<?= $id ?>')" class="btn-sm btn-edit"><i class="fas fa-pen"></i></button>
  <button onclick="confirmDelete('<?= $id ?>')" class="btn-sm btn-danger"><i class="fas fa-trash"></i></button></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div id="editForm" class="edit-section" style="display:none;">
    <form method="POST">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="card">
        <h3>✏️ Editar Serie</h3>
        <label>Título:</label><input type="text" name="title" id="edit_title" required>
        <label>Fecha de estreno:</label><input type="text" name="release_date" id="edit_date">
        <label>Categoría:</label><input type="text" name="category" id="edit_category">
        <label>Descripción:</label><textarea name="overview" id="edit_overview" rows="4"></textarea>
        <label>Código tráiler (YouTube):</label><input type="text" name="trailer" id="edit_trailer">
        <label>Temporadas y Episodios:</label>
        <div id="seasonsContainer"></div>
        <button type="button" class="btn-secondary" onclick="addSeason()">➕ Añadir Temporada</button>
        <button type="submit" class="btn-sm">💾 Guardar cambios</button>
      </div>
    </form>
  </div>
<?php endif; ?>
</div>

<script>
function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('active'); }
const series = <?= json_encode($series) ?>;
let seasonCount = 0;
function editSerie(id) {
  const s = series[id];
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_title').value = s.title;
  document.getElementById('edit_date').value = s.release_date;
  document.getElementById('edit_category').value = s.category;
  document.getElementById('edit_overview').value = s.overview;
  document.getElementById('edit_trailer').value = s.trailer;
  const container = document.getElementById('seasonsContainer');
  container.innerHTML=''; seasonCount = 0;
  const seasons = s.seasons || {};
  Object.entries(seasons).forEach(([season, episodes]) => {
    const seasonDiv = document.createElement('div');
    seasonDiv.innerHTML = `<h4>Temporada ${season}</h4>`;
Object.entries(episodes).forEach(([ep, data]) => {
  seasonDiv.innerHTML += `
    <div class="player-input-group">
      <label>Episodio ${ep}:</label>
      <input name="seasons[${season}][${ep}][url]" value="${data.url}" />
      <button type="button" class="btn-danger" onclick="this.parentElement.remove()" title="Eliminar episodio">
        <i class="fas fa-trash"></i>
      </button>
    </div>`;
});
    seasonDiv.innerHTML += `<button type="button" class="btn-secondary" onclick="addEpisode(${season})">➕ Añadir Episodio</button>`;
    container.appendChild(seasonDiv);
    seasonCount++;
  });
  document.getElementById('editForm').style.display = 'block';
  window.scrollTo({ top: document.getElementById('editForm').offsetTop, behavior:'smooth'});
}
function addSeason(){
  seasonCount++;
  const container = document.getElementById('seasonsContainer');
  const seasonDiv = document.createElement('div');
  seasonDiv.innerHTML = `
  <h4>Temporada ${seasonCount}</h4>
  <div class="player-input-group">
    <label>Episodio 1:</label>
    <input name="seasons[${seasonCount}][1][url]" />
    <button type="button" class="btn-danger" onclick="this.parentElement.remove()" title="Eliminar episodio">
      <i class="fas fa-trash"></i>
    </button>
  </div>`;
  seasonDiv.innerHTML += `<button type="button" class="btn-secondary" onclick="addEpisode(${seasonCount})">➕ Añadir Episodio</button>`;
  container.appendChild(seasonDiv);
}
function addEpisode(seasonNum){
  const seasonDivs = document.querySelectorAll('#seasonsContainer > div');
  const target = seasonDivs[seasonNum - 1];
  const epCount = target.querySelectorAll('input').length + 1;
  const newInput = document.createElement('div');
  newInput.innerHTML = `<label>Episodio ${epCount}:</label><input name="seasons[${seasonNum}][${epCount}][url]" />`;
  target.insertBefore(newInput, target.lastElementChild);
}
function filterSeries(){
  const input = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#seriesTable tbody tr').forEach(row => {
    const title = row.cells[1].textContent.toLowerCase();
    const id = row.querySelector('button.btn-edit').getAttribute('onclick').match(/'(.+)'/)[1].toLowerCase();
    row.style.display = (title.includes(input) || id.includes(input)) ? '' : 'none';
  });
}
</script>
<script>
function confirmDelete(id) {
  if (confirm("¿Estás seguro de que deseas eliminar esta serie?")) {
    window.location.href = "delete_series.php?id=" + id;
  }
}
</script>

<script src="../js/protect.js"></script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>

</body>
</html>
