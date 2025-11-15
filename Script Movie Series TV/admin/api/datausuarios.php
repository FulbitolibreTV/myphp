<?php
require_once '../../config.php';
if (!check_session()) { header('Location: ../login.php'); exit; }

// ----------------- USUARIOS DEL SISTEMA -----------------
$current_user = $_SESSION['username'];
$users_file = '../../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$current_name = $_SESSION['name'] ?? $current_user;
$current_image = $current_data['profile_image'];
$is_admin = in_array($current_data['role'], ['admin', 'super_admin']);
$is_super_admin = $current_data['role'] === 'super_admin';

// --- Validar vencimientos automáticamente usuarios del sistema ---
$today = date("Y-m-d");
foreach($users as &$u){
    if(!empty($u['vencimiento']) && strtotime($u['vencimiento']) < strtotime($today)){
        if($u['estado'] === 'activo') $u['estado'] = 'desactivado';
    }
}
unset($u);
file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));

// ----------------- CLIENTES -----------------
$clients_file = '../../data/clientedata.json';
$clients = file_exists($clients_file) ? json_decode(file_get_contents($clients_file), true) : [];

// --- POST actions para clientes ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch($action) {
        case 'add':
            $usuario = $_POST['usuario'] ?? '';
            $pin = $_POST['pin'] ?? '';
            $vencimiento = $_POST['vencimiento'] ?? date('Y-m-d', strtotime("+30 days"));

            if(!empty($usuario)){
                // Crear un ID único
                $id = uniqid();
                $clients[$id] = [
                    'id' => $id,
                    'usuario' => $usuario,
                    'pin' => $pin,
                    'estado' => 'activo',
                    'vencimiento' => $vencimiento
                ];
            }
            break;

        case 'edit':
            $id = $_POST['id'] ?? '';
            if(isset($clients[$id])){
                $clients[$id]['usuario'] = $_POST['usuario'] ?? $clients[$id]['usuario'];
                $clients[$id]['pin'] = $_POST['pin'] ?? $clients[$id]['pin'];
                $clients[$id]['vencimiento'] = $_POST['vencimiento'] ?? $clients[$id]['vencimiento'];
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? '';
            if(isset($clients[$id])){
                unset($clients[$id]);
            }
            break;

        case 'toggle':
            $id = $_POST['id'] ?? '';
            if(isset($clients[$id])){
                $estado = $clients[$id]['estado'];
                $clients[$id]['estado'] = $estado === 'activo' ? 'desactivado' : ($estado === 'desactivado' ? 'bloqueado' : 'activo');
            }
            break;
    }

    // Guardar cambios en clientedata.json
    file_put_contents($clients_file, json_encode($clients, JSON_PRETTY_PRINT));
    exit(json_encode(['status'=>'ok']));
}

// --- Validar vencimientos automáticamente clientes ---
foreach($clients as &$c){
    if(!empty($c['vencimiento']) && strtotime($c['vencimiento']) < strtotime($today)){
        if($c['estado'] === 'activo') $c['estado'] = 'desactivado';
    }
}
unset($c);
file_put_contents($clients_file, json_encode($clients, JSON_PRETTY_PRINT));
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Generadores - CorpSRTony</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'Inter', sans-serif; background: #f4f6fc; display:flex; min-height:100vh; }

/* Sidebar */
.sidebar { 
    width: 250px; 
    background: #1a237e; 
    color: white; 
    height: 100vh; 
    position: fixed; 
    left:0; 
    top:0; 
    overflow-y:auto; 
    overflow-x:hidden; 
    padding: 1.5rem 1rem; 
    transition: transform 0.3s ease-in-out;
    z-index: 1000;
}

.sidebar h1 { font-size:1.4rem; margin-bottom:1.2rem; text-align:center; }
.sidebar .section-title { font-size:0.8rem; text-transform:uppercase; opacity:0.7; margin:1rem 0 0.5rem 0; padding-left:1rem; }
.sidebar a { display:flex; align-items:center; gap:10px; color:white; text-decoration:none; padding:0.5rem 1rem; border-radius:6px; margin-bottom:0.3rem; font-size:0.95rem; white-space: nowrap; }
.sidebar a:hover { background: rgba(255,255,255,0.2); }

/* Hamburger Button */
.hamburger { 
    position: fixed; 
    top: 1rem; 
    left: 1rem; 
    font-size: 1.5rem; 
    background: #1a237e; 
    color: white; 
    border: none; 
    padding: 0.6rem; 
    border-radius: 6px; 
    z-index: 1100; 
    cursor: pointer; 
    display: none; 
}

/* Main Content */
.main-content { 
    flex:1; 
    margin-left: 250px; 
    padding: 2rem; 
    display:flex; 
    flex-direction:column; 
}

/* Cards */
.card-container { display:flex; flex-wrap:wrap; gap:2rem; justify-content:center; }
.gen-card { background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center; width:220px; }
.gen-card h3 { color:#1a237e; margin-bottom:0.8rem; }
.gen-card p { font-size:0.9rem; color:#555; margin-bottom:1rem; }
.gen-card a { display:inline-block; padding:0.5rem 1rem; background:#1a237e; color:white; border-radius:8px; text-decoration:none; font-weight:bold; }

/* Action Buttons */
.action-btn {
    background: none;
    border: none;
    font-size: 1.1rem;
    cursor: pointer;
    margin: 0 3px;
    padding: 5px;
    border-radius: 4px;
    transition: background 0.2s;
}

.action-btn:hover {
    background: rgba(0,0,0,0.1);
}

.action-btn.toggle { color: #2196F3; }
.action-btn.edit { color: #FF9800; }
.action-btn.delete { color: #F44336; }

/* Overlay for mobile */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 999;
}

/* Mobile Styles */
@media(max-width:768px){ 
    .hamburger { display:block; } 
    
    .sidebar { 
        transform: translateX(-100%); 
        position:fixed; 
    } 
    
    .sidebar.active { 
        transform: translateX(0); 
    }
    
    .sidebar-overlay.active {
        display: block;
    }
    
    .main-content { 
        margin-left:0; 
        padding: 1rem; 
        padding-top:4rem; 
    }
    
    /* Hacer la tabla scrolleable horizontalmente en móviles */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-container table {
        min-width: 600px;
    }
}
</style>
</head>
<body>

<button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
  <h1>Admin panel</h1>

  <div class="section-title">Menú Principal</div>
  <a href="../index.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="../manage-series.php"><i class="fas fa-tv"></i> Series</a>
  <a href="../create-movie.php"><i class="fas fa-video"></i> Crear Película</a>
  <a href="../manage-categories.php"><i class="fas fa-layer-group"></i> Categorías</a>
  <a href="../detv.php"><i class="fas fa-play-circle"></i> TV</a>

  <?php if ($is_admin): ?>
    <div class="section-title">⚙️ Configuración</div>
    <a href="../config_home.php"><i class="fas fa-home"></i> Config Home</a>
    <a href="../config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>
  <?php endif; ?>

  <?php if ($is_super_admin): ?>
    <div class="section-title">🔧 Admin Tools</div>
    <a href="../soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
    <a href="../configure-api.php"><i class="fas fa-key"></i> TMDB API</a>
    <a href="generadores.php"><i class="fas fa-cogs"></i> API AppCreator24</a>
    <a href="../monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
    <a href="../telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <?php endif; ?>

  <div class="section-title">Usuario</div>
  <a href="../perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">

  <h2 style="color:#1a237e; margin-bottom:1.5rem; text-align:center;">👥 Gestión de Clientes</h2>

  <!-- Barra de herramientas -->
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:1rem;">
    <input type="text" id="searchInput" placeholder="Buscar cliente..." 
           style="padding:0.6rem 1rem; border-radius:8px; border:1px solid #ccc; flex:1; max-width:250px;">
    <button onclick="openAddModal()" 
            style="padding:0.6rem 1.2rem; background:#1a237e; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">
      ➕ Agregar Cliente
    </button>
  </div>

  <!-- Tabla de clientes -->
  <div class="table-container" style="background:white; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
    <table style="width:100%; border-collapse:collapse; text-align:left;">
      <thead style="background:#1a237e; color:white;">
        <tr>
          <th style="padding:0.8rem;">Usuario</th>
          <th style="padding:0.8rem;">PIN</th>
          <th style="padding:0.8rem;">Estado</th>
          <th style="padding:0.8rem;">Vencimiento</th>
          <th style="padding:0.8rem; text-align:center;">Acciones</th>
        </tr>
      </thead>
      <tbody id="clientTableBody">
        <?php if (!empty($clients)): ?>
          <?php foreach ($clients as $c): ?>
          <tr data-id="<?= $c['id'] ?>">
            <td style="padding:0.8rem;"><?= htmlspecialchars($c['usuario']) ?></td>
            <td style="padding:0.8rem;"><?= htmlspecialchars($c['pin']) ?></td>
            <td style="padding:0.8rem; color:<?= $c['estado']==='activo' ? 'green':'red' ?>; font-weight:bold;">
              <?= $c['estado'] ?>
            </td>
            <td style="padding:0.8rem;"><?= htmlspecialchars($c['vencimiento']) ?></td>
            <td style="padding:0.8rem; text-align:center;">
              <button class="action-btn toggle" onclick="toggleClient('<?= $c['id'] ?>')" title="Activar/Desactivar">
                <i class="fas fa-sync-alt"></i>
              </button>
              <button class="action-btn edit" onclick="openEditModal('<?= $c['id'] ?>')" title="Editar">
                <i class="fas fa-edit"></i>
              </button>
              <button class="action-btn delete" onclick="deleteClient('<?= $c['id'] ?>')" title="Eliminar">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" style="padding:1rem; text-align:center; color:#888;">No hay clientes registrados</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal Agregar -->
  <div id="addModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:2000;">
    <div style="background:white; padding:2rem; border-radius:12px; width:100%; max-width:400px;">
      <h3 style="margin-bottom:1rem; color:#1a237e;">➕ Nuevo Cliente</h3>
      <form id="addForm">
        <input type="text" name="usuario" placeholder="Usuario" required style="width:100%; padding:0.6rem; margin-bottom:1rem; border-radius:8px; border:1px solid #ccc;">
        <input type="text" name="pin" placeholder="PIN" required style="width:100%; padding:0.6rem; margin-bottom:1rem; border-radius:8px; border:1px solid #ccc;">
        <input type="date" name="vencimiento" required style="width:100%; padding:0.6rem; margin-bottom:1rem; border-radius:8px; border:1px solid #ccc;">
        <button type="submit" style="padding:0.6rem 1.2rem; background:#1a237e; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">Guardar</button>
        <button type="button" onclick="closeModal('addModal')" style="margin-left:10px; padding:0.6rem 1.2rem; background:#ccc; border:none; border-radius:8px; cursor:pointer;">Cancelar</button>
      </form>
    </div>
  </div>

  <!-- Modal Editar -->
  <div id="editModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:2000;">
    <div style="background:white; padding:2rem; border-radius:12px; width:100%; max-width:400px;">
      <h3 style="margin-bottom:1rem; color:#1a237e;">✏️ Editar Cliente</h3>
      <form id="editForm">
        <input type="hidden" name="id">
        <input type="text" name="usuario" required style="width:100%; padding:0.6rem; margin-bottom:1rem; border-radius:8px; border:1px solid #ccc;">
        <input type="text" name="pin" required style="width:100%; padding:0.6rem; margin-bottom:1rem; border-radius:8px; border:1px solid #ccc;">
        <input type="date" name="vencimiento" required style="width:100%; padding:0.6rem; margin-bottom:1rem; border-radius:8px; border:1px solid #ccc;">
        <button type="submit" style="padding:0.6rem 1.2rem; background:#1a237e; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">Actualizar</button>
        <button type="button" onclick="closeModal('editModal')" style="margin-left:10px; padding:0.6rem 1.2rem; background:#ccc; border:none; border-radius:8px; cursor:pointer;">Cancelar</button>
      </form>
    </div>
  </div>
</div>

<script>
// --- Sidebar Toggle Functions ---
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
}

// --- Buscador ---
document.getElementById("searchInput").addEventListener("keyup", function() {
  let value = this.value.toLowerCase();
  document.querySelectorAll("#clientTableBody tr").forEach(tr => {
    tr.style.display = tr.innerText.toLowerCase().includes(value) ? "" : "none";
  });
});

// --- Modales ---
function openAddModal(){ 
  document.getElementById("addModal").style.display = "flex"; 
}

function openEditModal(id){
  const row = document.querySelector(`tr[data-id="${id}"]`);
  const form = document.getElementById("editForm");
  form.id.value = id; // campo oculto para enviar el id del cliente
  form.usuario.value = row.children[0].innerText;
  form.pin.value = row.children[1].innerText;
  form.vencimiento.value = row.children[3].innerText;
  document.getElementById("editModal").style.display = "flex";
}

function closeModal(id){ 
  document.getElementById(id).style.display = "none"; 
}

// --- CRUD con fetch ---
async function saveForm(form, action){
  const formData = new FormData(form);
  formData.append("action", action);
  await fetch("datausuarios.php", { method:"POST", body:formData });
  location.reload();
}

document.getElementById("addForm").onsubmit = e => { 
  e.preventDefault(); 
  saveForm(e.target, "add"); 
}

document.getElementById("editForm").onsubmit = e => { 
  e.preventDefault(); 
  saveForm(e.target, "edit"); 
}

// --- Eliminar cliente ---
async function deleteClient(id){
  if(confirm("¿Eliminar cliente?")){
    let fd = new FormData();
    fd.append("id", id);
    fd.append("action", "delete");
    await fetch("datausuarios.php", { method:"POST", body:fd });
    location.reload();
  }
}

// --- Activar/Desactivar cliente ---
async function toggleClient(id){
  let fd = new FormData();
  fd.append("id", id);
  fd.append("action", "toggle");
  await fetch("datausuarios.php", { method:"POST", body:fd });
  location.reload();
}

// Cerrar sidebar cuando se hace clic fuera de él en móvil
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.querySelector('.hamburger');
    
    if (window.innerWidth <= 768 && 
        sidebar.classList.contains('active') && 
        !sidebar.contains(e.target) && 
        !hamburger.contains(e.target)) {
        closeSidebar();
    }
});

</script>

<script src="../../js/protect.js"></script>

</body>
</html>