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
    die('<h2 style="color:red;text-align:center;">⛔ Acceso restringido.</h2>');
}

$movies_file = '../data/movies.json';
$movies = file_exists($movies_file) ? json_decode(file_get_contents($movies_file), true) : [];

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (isset($movies[$id])) {
        unset($movies[$id]);
        file_put_contents($movies_file, json_encode($movies, JSON_PRETTY_PRINT));
        $archivo_php = "../peliculas/{$id}.php";
        if (file_exists($archivo_php)) unlink($archivo_php);
        header('Location: manage-movies.php?deleted=1');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    if (isset($movies[$edit_id])) {
        $movies[$edit_id]['title'] = $_POST['title'] ?? $movies[$edit_id]['title'];
        $movies[$edit_id]['overview'] = $_POST['overview'] ?? $movies[$edit_id]['overview'];
        $movies[$edit_id]['release_date'] = $_POST['release_date'] ?? $movies[$edit_id]['release_date'];
        $movies[$edit_id]['category'] = $_POST['category'] ?? $movies[$edit_id]['category'];
        $movies[$edit_id]['trailer'] = $_POST['trailer'] ?? $movies[$edit_id]['trailer'];

        $players = [];
        if (!empty($_POST['players'])) {
            foreach ($_POST['players'] as $p) {
                $label = trim($p['label'] ?? '');
                $url = trim($p['url'] ?? '');
                if ($label && $url) {
                    $players[] = ['label' => $label, 'url' => $url];
                }
            }
        }
        $movies[$edit_id]['players'] = $players;

        file_put_contents($movies_file, json_encode($movies, JSON_PRETTY_PRINT));
        header('Location: manage-movies.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Gestión de Películas - CorpSRTony</title>
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
.btn-delete {
  background:#ff4444; color:white; padding:6px 10px;
  border:none; border-radius:5px; cursor:pointer;
}
.players-list .player-input-group { display:flex; gap:0.5rem; margin-bottom:0.5rem; flex-wrap:wrap; }
.players-list input { flex:1; }
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
<?php if (isset($_GET['deleted'])): ?>
  <p style="color: green; font-weight: bold;">✅ Película eliminada correctamente</p>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
  <p style="color: blue; font-weight: bold;">✅ Película actualizada correctamente</p>
<?php endif; ?>

<?php if (empty($movies)): ?>
  <p>No hay películas registradas.</p>
<?php else: ?>
  <div class="search-box">
    <input type="text" id="searchInput" onkeyup="filterMovies()" placeholder="Buscar por título o ID...">
  </div>
  <div class="table-container">
    <table id="moviesTable">
      <thead>
        <tr><th>Poster</th><th>Título</th><th>Fecha</th><th>Categoría</th><th>Acciones</th></tr>
      </thead>
      <tbody>
      <?php foreach ($movies as $id => $m): ?>
        <tr>
          <td><img src="https://image.tmdb.org/t/p/w200<?= $m['poster_path'] ?>" style="height:100px;border-radius:6px;"></td>
          <td><?= htmlspecialchars($m['title']) ?></td>
          <td><?= $m['release_date'] ?></td>
          <td><?= htmlspecialchars($m['category']) ?></td>
          <td>
            <a href="../peliculas/<?= $id ?>.php" target="_blank" class="btn-sm"><i class="fas fa-eye"></i></a>
            <a href="?delete=<?= $id ?>" onclick="return confirm('¿Seguro que deseas eliminar esta película?')" class="btn-sm btn-danger"><i class="fas fa-trash"></i></a>
            <button onclick="editMovie('<?= $id ?>')" class="btn-sm btn-edit"><i class="fas fa-pen"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div id="editForm" class="edit-section" style="display:none;">
    <form method="POST">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="card">
        <h3>✏️ Editar Película</h3>
        <label>Título:</label><input type="text" name="title" id="edit_title" required>
        <label>Fecha de estreno:</label><input type="text" name="release_date" id="edit_date">
        <label>Categoría:</label><input type="text" name="category" id="edit_category">
        <label>Descripción:</label><textarea name="overview" id="edit_overview" rows="4"></textarea>
        <label>Código tráiler (YouTube):</label><input type="text" name="trailer" id="edit_trailer">
        <label>Enlaces de reproducción:</label>
        <div id="editPlayerLinksContainer" class="players-list"></div>
        <button type="button" class="btn-secondary" onclick="addEditPlayer()">➕ Añadir enlace</button>
        <button type="submit" class="btn-sm">💾 Guardar cambios</button>
      </div>
    </form>
  </div>
<?php endif; ?>
</div>

<script>
function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('active'); }
const movies = <?= json_encode($movies) ?>;
let editPlayerCount = 0;
function editMovie(id) {
  const m = movies[id];
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_title').value = m.title;
  document.getElementById('edit_date').value = m.release_date;
  document.getElementById('edit_category').value = m.category;
  document.getElementById('edit_overview').value = m.overview;
  document.getElementById('edit_trailer').value = m.trailer;
  document.getElementById('editForm').style.display = 'block';
  const container = document.getElementById('editPlayerLinksContainer');
  container.innerHTML=''; editPlayerCount=0;
  (m.players||[]).forEach(p=>{ addEditPlayer(p.label || '', p.url || ''); });
  window.scrollTo({ top: document.getElementById('editForm').offsetTop, behavior:'smooth'});
}
function addEditPlayer(label='',url=''){
  if(editPlayerCount>=4) return;
  const group=document.createElement('div');
  group.className='player-input-group';
  group.innerHTML=`
    <input type="text" name="players[${editPlayerCount}][label]" placeholder="Nombre" value="${label}">
    <input type="text" name="players[${editPlayerCount}][url]" placeholder="URL" value="${url}">
    <button type="button" class="btn-delete" onclick="this.parentElement.remove();editPlayerCount--;">✖</button>
  `;
  document.getElementById('editPlayerLinksContainer').appendChild(group);
  editPlayerCount++;
}
function filterMovies(){
  const input=document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#moviesTable tbody tr').forEach(row=>{
    const title=row.cells[1].textContent.toLowerCase();
    const id=row.cells[4].querySelector('button').getAttribute('onclick').match(/'(.+)'/)[1];
    row.style.display=(title.includes(input)||id.includes(input))?'':'none';
  });
}
</script>
<script src="js/protect.js"></script>
<script src="../js/protect.js"></script>
<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>
</body>
</html>
