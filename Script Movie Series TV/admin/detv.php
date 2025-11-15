<?php
require_once '../config.php';
if (!check_session()) { header('Location: login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$is_admin = in_array($current_data['role'], ['admin', 'super_admin']);
$is_super_admin = $current_data['role'] === 'super_admin';

// Archivo JSON de canales TV
$tv_file = '../data/tv_channels.json';
$tv_channels = file_exists($tv_file) ? json_decode(file_get_contents($tv_file), true) : [];

// Límite de 30
$tv_channels = array_slice($tv_channels, 0, 30);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Panel - En Planificación</title>
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

/* Main content */
.main-content {
  flex: 1;
  margin-left: 250px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

/* Título */
.main-content h1 {
  font-size: 2rem;
  color: #1a237e;
  margin-bottom: 1rem;
}

/* Buscador y botón agregar */
#search {
  width: 100%;
  max-width: 400px;
  padding: 0.5rem;
  margin-bottom: 1rem;
  border-radius: 6px;
  border: 1px solid #ccc;
}

.main-content button {
  background: #1a237e;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  margin-bottom: 1rem;
  cursor: pointer;
}

.main-content button:hover {
  background: #16216a;
}

/* Contenedor de tabla con scroll horizontal */
.table-container {
  width: 100%;
  overflow-x: auto;
  margin-top: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Tabla de canales */
.table {
  width: 100%;
  min-width: 800px; /* Asegura un ancho mínimo para scroll horizontal */
  border-collapse: collapse;
  background: white;
}

.table th, .table td {
  padding: 0.6rem 1rem;
  border: 1px solid #eee;
  text-align: left;
  vertical-align: middle;
  white-space: nowrap;
}

.table th {
  background: #1a237e;
  color: white;
  font-weight: 600;
  position: sticky;
  top: 0;
  z-index: 10;
}

.table td img {
  border-radius: 4px;
  max-width: 50px;
  height: auto;
}

.table td a {
  color: #1a237e;
  text-decoration: none;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
}

.table td a:hover {
  text-decoration: underline;
}

/* Botones de acciones */
.btn {
  padding: 0.4rem 0.6rem;
  border: none;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  margin: 0 0.2rem;
  font-size: 0.9rem;
  transition: background-color 0.3s ease;
}

.btn-edit {
  background: #4caf50;
}

.btn-edit:hover {
  background: #3e8e41;
}

.btn-delete {
  background: #f44336;
}

.btn-delete:hover {
  background: #d32f2f;
}

/* Modal */
.modal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.modal-content {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  width: 90%;
  max-width: 450px;
  position: relative;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h2 {
  margin-bottom: 1rem;
  color: #1a237e;
  font-size: 1.5rem;
}

.modal-content label {
  display: block;
  margin-top: 0.8rem;
  font-weight: 600;
  color: #333;
}

.modal-content input, .modal-content select {
  width: 100%;
  padding: 0.6rem;
  margin: 0.3rem 0 0.5rem 0;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.95rem;
}

.modal-content input:focus, .modal-content select:focus {
  outline: none;
  border-color: #1a237e;
  box-shadow: 0 0 0 2px rgba(26, 35, 126, 0.2);
}

.modal-content button[type="submit"] {
  background: #1a237e;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  margin-top: 1rem;
  width: 100%;
  transition: background-color 0.3s ease;
}

.modal-content button[type="submit"]:hover {
  background: #16216a;
}

/* Cerrar modal */
.close-modal {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 1.5rem;
  color: #666;
  cursor: pointer;
  transition: color 0.3s ease;
}

.close-modal:hover {
  color: #333;
}

/* Responsive */
@media(max-width:768px){
  .hamburger { display:block; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  
  .main-content {
    margin-left: 0;
    padding: 1rem;
    padding-top: 5rem;
  }
  
  .main-content h1 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
  }
  
  #search {
    max-width: 100%;
    font-size: 16px; /* Previene zoom en iOS */
  }
  
  .table-container {
    margin-top: 0.5rem;
    border-radius: 6px;
  }
  
  .table {
    min-width: 700px; /* Reduce el ancho mínimo en móvil */
  }
  
  .table th, .table td {
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
  }
  
  .table td img {
    max-width: 40px;
  }
  
  .btn {
    padding: 0.3rem 0.5rem;
    font-size: 0.8rem;
    margin: 0 0.1rem;
  }
  
  .modal-content {
    width: 95%;
    padding: 1rem;
    margin: 1rem;
  }
  
  .modal-content h2 {
    font-size: 1.3rem;
  }
  
  .modal-content input, .modal-content select {
    padding: 0.5rem;
    font-size: 16px; /* Previene zoom en iOS */
  }
}

@media(max-width:480px){
  .main-content {
    padding: 0.5rem;
    padding-top: 4.5rem;
  }
  
  .main-content h1 {
    font-size: 1.3rem;
  }
  
  .table {
    min-width: 600px; /* Aún más compacto en pantallas muy pequeñas */
  }
  
  .table th, .table td {
    padding: 0.3rem 0.4rem;
    font-size: 0.8rem;
  }
  
  .btn {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
  }
}

/* Scroll personalizado para la tabla */
.table-container::-webkit-scrollbar {
  height: 8px;
}

.table-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
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
<h1>📺 Gestión de Canales TV</h1>
<input type="text" id="search" placeholder="Buscar canal...">
<button onclick="openAddModal()">➕ Agregar Canal</button>

<div class="table-container">
<table class="table" id="tv-table">
<thead>
<tr>
<th>Nombre</th>
<th>Categoría</th>
<th>Imagen</th>
<th>URL</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($tv_channels as $index=>$tv): ?>
<tr data-index="<?= $index ?>">
<td><?= htmlspecialchars($tv['name']) ?></td>
<td><?= htmlspecialchars($tv['category']) ?></td>
<td><img src="<?= htmlspecialchars($tv['image']) ?>" width="50"></td>
<td><a href="<?= htmlspecialchars($tv['url']) ?>" target="_blank"><?= htmlspecialchars($tv['url']) ?></a></td>
<td>
<button class="btn btn-edit" onclick="editChannel(<?= $index ?>)" title="Editar">
<i class="fas fa-edit"></i>
</button>
<button class="btn btn-delete" onclick="deleteChannel(<?= $index ?>)" title="Eliminar">
<i class="fas fa-trash"></i>
</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<!-- Modal -->
<div class="modal" id="modal">
<div class="modal-content">
<span class="close-modal" onclick="closeModal()">&times;</span>
<h2 id="modal-title">Agregar Canal</h2>
<form id="channel-form">
<input type="hidden" id="channel-index">
<label>Nombre</label>
<input type="text" id="channel-name" required>
<label>Categoría</label>
<input type="text" id="channel-category" required>
<label>Imagen (URL)</label>
<input type="url" id="channel-image" required>
<label>URL de streaming</label>
<input type="url" id="channel-url" required>
<button type="submit">Guardar</button>
</form>
</div>
</div>

<script>
// Sidebar
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('active');}

// Modal
function openAddModal(){
  document.getElementById('channel-form').reset();
  document.getElementById('modal-title').innerText='Agregar Canal';
  document.getElementById('channel-index').value='';
  document.getElementById('modal').style.display='flex';
}
function openModal(){ document.getElementById('modal').style.display='flex'; }
function closeModal(){ document.getElementById('modal').style.display='none'; }

// Edit
function editChannel(index){
  const row=document.querySelector('tr[data-index="'+index+'"]');
  document.getElementById('modal-title').innerText='Editar Canal';
  document.getElementById('channel-index').value=index;
  document.getElementById('channel-name').value=row.children[0].innerText;
  document.getElementById('channel-category').value=row.children[1].innerText;
  document.getElementById('channel-image').value=row.children[2].querySelector('img').src;
  document.getElementById('channel-url').value=row.children[3].querySelector('a').href;
  openModal();
}

// Delete
function deleteChannel(index){
  if(confirm('¿Eliminar este canal?')){
    window.location.href='tv_actions.php?action=delete&index='+index;
  }
}

// Search
document.getElementById('search').addEventListener('input', function(){
  const filter=this.value.toLowerCase();
  document.querySelectorAll('#tv-table tbody tr').forEach(row=>{
    row.style.display=row.children[0].innerText.toLowerCase().includes(filter)||row.children[1].innerText.toLowerCase().includes(filter)?'':'none';
  });
});

// Form submit
document.getElementById('channel-form').addEventListener('submit', function(e){
  e.preventDefault();
  const index=document.getElementById('channel-index').value;
  const name=document.getElementById('channel-name').value;
  const category=document.getElementById('channel-category').value;
  const image=document.getElementById('channel-image').value;
  const url=document.getElementById('channel-url').value;
  const params=new URLSearchParams({index,name,category,image,url});
  if(index===''){
    window.location.href='tv_actions.php?action=add&'+params.toString();
  }else{
    window.location.href='tv_actions.php?action=edit&'+params.toString();
  }
});

// Cerrar modal al hacer clic fuera de él
document.getElementById('modal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeModal();
  }
});
</script>

</body>
</html>