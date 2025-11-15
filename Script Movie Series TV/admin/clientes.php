<?php
require_once '../config.php';
if (!check_session()) { header('Location: login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../data/accesclient.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$current_name = $_SESSION['name'] ?? $current_user;
$current_image = $current_data['profile_image'];

// Archivo para guardar clientes
$clients_file = '../data/accesclient.json';

// Función para cargar clientes
function loadClients() {
    global $clients_file;
    if (file_exists($clients_file)) {
        $content = file_get_contents($clients_file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

// Función para guardar clientes
function saveClients($clients) {
    global $clients_file;
    $dir = dirname($clients_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents($clients_file, json_encode($clients, JSON_PRETTY_PRINT));
}

// Función para verificar si un PIN ya existe
function pinExists($pin, $clients, $excludeId = null) {
    foreach ($clients as $id => $client) {
        if ($client['pin'] === $pin && $id !== $excludeId) {
            return true;
        }
    }
    return false;
}

// Función para verificar vencimiento
function checkExpiration($client) {
    if ($client['dias'] > 0) {
        $created = new DateTime($client['creacion']);
        $now = new DateTime();
        $diff = $now->diff($created)->days;
        
        if ($diff >= $client['dias']) {
            return 'vencido';
        }
    }
    return $client['estado'];
}

// Procesar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $clients = loadClients();
    
    switch ($_POST['action']) {
        case 'create':
            $nombre = trim($_POST['nombre']);
            $pin = trim($_POST['pin']);
            $estado = $_POST['estado'];
            $dias = intval($_POST['dias']);
            
            if (empty($nombre) || empty($pin)) {
                echo json_encode(['success' => false, 'message' => 'Nombre y PIN son obligatorios']);
                exit;
            }
            
            if (pinExists($pin, $clients)) {
                echo json_encode(['success' => false, 'message' => 'El PIN ya existe']);
                exit;
            }
            
            $id = uniqid();
            $clients[$id] = [
                'nombre' => $nombre,
                'pin' => $pin,
                'estado' => $estado,
                'dias' => $dias,
                'creacion' => date('Y-m-d H:i:s')
            ];
            
            if (saveClients($clients)) {
                echo json_encode(['success' => true, 'message' => 'Cliente creado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar el cliente']);
            }
            exit;
            
        case 'update':
            $id = $_POST['id'];
            $pin = trim($_POST['pin']);
            $estado = $_POST['estado'];
            $dias = intval($_POST['dias']);
            
            if (!isset($clients[$id])) {
                echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
                exit;
            }
            
            if (pinExists($pin, $clients, $id)) {
                echo json_encode(['success' => false, 'message' => 'El PIN ya existe']);
                exit;
            }
            
            $clients[$id]['pin'] = $pin;
            $clients[$id]['estado'] = $estado;
            $clients[$id]['dias'] = $dias;
            
            if (saveClients($clients)) {
                echo json_encode(['success' => true, 'message' => 'Cliente actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el cliente']);
            }
            exit;
            
        case 'delete':
            $id = $_POST['id'];
            
            if (isset($clients[$id])) {
                unset($clients[$id]);
                if (saveClients($clients)) {
                    echo json_encode(['success' => true, 'message' => 'Cliente eliminado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el cliente']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
            }
            exit;
            
        case 'get':
            $id = $_POST['id'];
            if (isset($clients[$id])) {
                echo json_encode(['success' => true, 'client' => $clients[$id]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
            }
            exit;
    }
}

$clients = loadClients();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Gestión de Clientes - Admin Panel</title>
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
  flex:1; margin-left: 250px; padding: 2rem;
}

.header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;
}

.header h1 {
  font-size: 2rem; color: #1a237e; margin: 0;
}

.search-container {
  display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;
}

.search-box {
  position: relative;
}

.search-box input {
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 2px solid #e0e0e0; border-radius: 8px;
  font-size: 1rem; width: 250px;
}

.search-box i {
  position: absolute; left: 0.75rem; top: 50%;
  transform: translateY(-50%); color: #666;
}

.btn {
  padding: 0.75rem 1.5rem; border: none; border-radius: 8px;
  font-size: 1rem; cursor: pointer; transition: all 0.3s;
  display: inline-flex; align-items: center; gap: 0.5rem;
}

.btn-primary {
  background: #1a237e; color: white;
}

.btn-primary:hover {
  background: #0d47a1;
}

.btn-success {
  background: #4caf50; color: white;
}

.btn-success:hover {
  background: #45a049;
}

.btn-warning {
  background: #ff9800; color: white;
}

.btn-warning:hover {
  background: #f57c00;
}

.btn-danger {
  background: #f44336; color: white;
}

.btn-danger:hover {
  background: #d32f2f;
}

.btn-sm {
  padding: 0.5rem 1rem; font-size: 0.875rem;
}

.table-container {
  background: white; border-radius: 12px; 
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  overflow: hidden;
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.table {
  width: 100%; border-collapse: collapse; 
  min-width: 700px;
}

.table th,
.table td {
  padding: 0.75rem; text-align: left; 
  border-bottom: 1px solid #e0e0e0;
  white-space: nowrap;
}

.table th {
  background: #f8f9fa; font-weight: 600; color: #333;
  position: sticky; top: 0; z-index: 10;
  font-size: 0.9rem;
}

.table tbody tr:hover {
  background: #f8f9fa;
}

/* Ancho específico para columnas en móvil */
.table th:nth-child(1), .table td:nth-child(1) { min-width: 120px; } /* Nombre */
.table th:nth-child(2), .table td:nth-child(2) { min-width: 80px; }  /* PIN */
.table th:nth-child(3), .table td:nth-child(3) { min-width: 100px; } /* Estado */
.table th:nth-child(4), .table td:nth-child(4) { min-width: 80px; }  /* Días */
.table th:nth-child(5), .table td:nth-child(5) { min-width: 140px; } /* Creación */
.table th:nth-child(6), .table td:nth-child(6) { min-width: 100px; } /* Acciones */

.status-badge {
  padding: 0.2rem 0.6rem; border-radius: 12px;
  font-size: 0.8rem; font-weight: 500;
  display: inline-block;
}

.status-activado {
  background: #e8f5e8; color: #2e7d32;
}

.status-desactivado {
  background: #fff3e0; color: #f57c00;
}

.status-bloqueado {
  background: #ffebee; color: #d32f2f;
}

.status-vencido {
  background: #f3e5f5; color: #7b1fa2;
}

.actions {
  display: flex; gap: 0.25rem; justify-content: center;
}

.mobile-scroll-hint {
  display: none; background: #e3f2fd; color: #1976d2;
  padding: 0.5rem; text-align: center; font-size: 0.8rem;
  border-bottom: 1px solid #bbdefb;
}

.modal {
  display: none; position: fixed; z-index: 2000;
  left: 0; top: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  overflow-y: auto; padding: 1rem;
}

.modal-content {
  background: white; margin: 0 auto; padding: 0;
  border-radius: 12px; width: 100%; max-width: 500px;
  animation: modalSlide 0.3s ease;
  max-height: calc(100vh - 2rem);
  display: flex; flex-direction: column;
  position: relative; top: 1rem;
}

@keyframes modalSlide {
  from { opacity: 0; transform: translateY(-50px); }
  to { opacity: 1; transform: translateY(0); }
}

.modal-header {
  padding: 1rem 1.5rem; border-bottom: 1px solid #e0e0e0;
  display: flex; justify-content: space-between; align-items: center;
  flex-shrink: 0;
}

.modal-header h2 {
  margin: 0; color: #1a237e; font-size: 1.25rem;
}

.close {
  background: none; border: none; font-size: 1.5rem;
  cursor: pointer; color: #666; padding: 0.25rem;
  border-radius: 4px;
}

.close:hover {
  color: #333; background: #f5f5f5;
}

.modal-body {
  padding: 1rem 1.5rem; overflow-y: auto; flex-grow: 1;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block; margin-bottom: 0.5rem;
  font-weight: 600; color: #333; font-size: 0.9rem;
}

.form-group input,
.form-group select {
  width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0;
  border-radius: 8px; font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none; border-color: #1a237e;
}

.modal-footer {
  padding: 1rem 1.5rem; border-top: 1px solid #e0e0e0;
  display: flex; justify-content: flex-end; gap: 1rem;
  flex-shrink: 0;
}

.alert {
  padding: 1rem; border-radius: 8px; margin-bottom: 1rem;
  display: none;
}

.alert-success {
  background: #e8f5e8; color: #2e7d32; border: 1px solid #c8e6c9;
}

.alert-error {
  background: #ffebee; color: #d32f2f; border: 1px solid #ffcdd2;
}

.empty-state {
  text-align: center; padding: 3rem; color: #666;
}

.empty-state i {
  font-size: 3rem; margin-bottom: 1rem; color: #ccc;
}

@media(max-width: 768px) {
  .hamburger { display: block; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  .main-content { margin-left: 0; padding: 0.75rem; padding-top: 4rem; }
  
  .header {
    flex-direction: column; align-items: stretch; margin-bottom: 1rem;
  }
  
  .header h1 {
    font-size: 1.4rem; text-align: center; margin-bottom: 0.75rem;
  }
  
  .search-container {
    justify-content: center; flex-direction: column; gap: 0.5rem;
  }
  
  .search-box {
    width: 100%;
  }
  
  .search-box input {
    width: 100%; padding: 0.6rem 1rem 0.6rem 2.2rem;
    font-size: 0.9rem;
  }
  
  .btn {
    width: 100%; justify-content: center; padding: 0.6rem 1rem;
    font-size: 0.9rem;
  }
  
  /* Mostrar hint de scroll */
  .mobile-scroll-hint {
    display: block;
  }
  
  /* Tabla más compacta en móvil */
  .table-container {
    margin: 0 -0.75rem; border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  
  .table {
    min-width: 650px; font-size: 0.85rem;
  }
  
  .table th,
  .table td {
    padding: 0.5rem 0.4rem;
  }
  
  .table th {
    font-size: 0.8rem; padding: 0.6rem 0.4rem;
  }
  
  /* Ajustar anchos mínimos para móvil */
  .table th:nth-child(1), .table td:nth-child(1) { min-width: 100px; }
  .table th:nth-child(2), .table td:nth-child(2) { min-width: 70px; }
  .table th:nth-child(3), .table td:nth-child(3) { min-width: 85px; }
  .table th:nth-child(4), .table td:nth-child(4) { min-width: 70px; }
  .table th:nth-child(5), .table td:nth-child(5) { min-width: 120px; }
  .table th:nth-child(6), .table td:nth-child(6) { min-width: 85px; }
  
  .actions {
    flex-direction: column; gap: 0.2rem;
  }
  
  .btn-sm {
    font-size: 0.75rem; padding: 0.35rem 0.6rem;
  }
  
  .status-badge {
    font-size: 0.7rem; padding: 0.15rem 0.4rem;
  }
  
  /* Modal responsive */
  .modal {
    padding: 0.5rem;
  }
  
  .modal-content {
    top: 0.5rem; max-height: calc(100vh - 1rem);
  }
  
  .modal-header {
    padding: 0.75rem 1rem;
  }
  
  .modal-header h2 {
    font-size: 1.1rem;
  }
  
  .modal-body {
    padding: 0.75rem 1rem;
  }
  
  .modal-footer {
    padding: 0.75rem 1rem; flex-direction: column;
  }
  
  .modal-footer .btn {
    width: 100%; margin: 0;
  }
}

@media(max-width: 480px) {
  .main-content {
    padding: 0.5rem; padding-top: 3.5rem;
  }
  
  .header h1 {
    font-size: 1.2rem;
  }
  
  .search-box input {
    padding: 0.5rem 0.8rem 0.5rem 2rem; font-size: 0.85rem;
  }
  
  .btn {
    padding: 0.5rem 0.8rem; font-size: 0.85rem;
  }
  
  /* Tabla ultra compacta */
  .table-container {
    margin: 0 -0.5rem;
  }
  
  .table {
    min-width: 600px; font-size: 0.8rem;
  }
  
  .table th,
  .table td {
    padding: 0.4rem 0.3rem;
  }
  
  .table th {
    font-size: 0.75rem; padding: 0.5rem 0.3rem;
  }
  
  /* Anchos mínimos reducidos */
  .table th:nth-child(1), .table td:nth-child(1) { min-width: 90px; }
  .table th:nth-child(2), .table td:nth-child(2) { min-width: 60px; }
  .table th:nth-child(3), .table td:nth-child(3) { min-width: 75px; }
  .table th:nth-child(4), .table td:nth-child(4) { min-width: 60px; }
  .table th:nth-child(5), .table td:nth-child(5) { min-width: 110px; }
  .table th:nth-child(6), .table td:nth-child(6) { min-width: 80px; }
  
  .btn-sm {
    font-size: 0.7rem; padding: 0.3rem 0.5rem;
  }
  
  .status-badge {
    font-size: 0.65rem; padding: 0.1rem 0.3rem;
  }
  
  /* Modal pequeño */
  .modal {
    padding: 0.25rem;
  }
  
  .modal-content {
    top: 0.25rem; max-height: calc(100vh - 0.5rem);
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 0.6rem 0.8rem;
  }
  
  .form-group {
    margin-bottom: 0.6rem;
  }
  
  .form-group input,
  .form-group select {
    padding: 0.5rem; font-size: 0.85rem;
  }
}

@media(max-width: 360px) {
  .table {
    min-width: 550px;
  }
  
  .table th:nth-child(1), .table td:nth-child(1) { min-width: 80px; }
  .table th:nth-child(2), .table td:nth-child(2) { min-width: 55px; }
  .table th:nth-child(3), .table td:nth-child(3) { min-width: 70px; }
  .table th:nth-child(4), .table td:nth-child(4) { min-width: 55px; }
  .table th:nth-child(5), .table td:nth-child(5) { min-width: 100px; }
  .table th:nth-child(6), .table td:nth-child(6) { min-width: 75px; }
  
  .modal-header h2 {
    font-size: 1rem;
  }
  
  .btn {
    font-size: 0.8rem; padding: 0.45rem 0.7rem;
  }
}

/* Mejora del scroll en móvil */
@media(max-width: 768px) {
  .table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #ccc #f1f1f1;
  }
  
  .table-responsive::-webkit-scrollbar {
    height: 6px;
  }
  
  .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }
  
  .table-responsive::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
  }
  
  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #999;
  }
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

  <div class="section-title">⚙️ Configuración</div>
  <a href="config_home.php"><i class="fas fa-home"></i> Config Home</a>
  <a href="config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>

  <div class="section-title">🔧 Admin Tools</div>
  <a href="soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
  <a href="configure-api.php"><i class="fas fa-key"></i> TMDB API</a>
  <a href="api/generadores.php"><i class="fas fa-cogs"></i> App Creator 24 📲</a>
  <a href="monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
  <a href="telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>

  <div class="section-title">Usuario</div>
  <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
  <div class="header">
    <h1><i class="fas fa-users"></i> Gestión de Clientes</h1>
    <div class="search-container">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar clientes...">
      </div>
      <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="fas fa-plus"></i> Nuevo Cliente
      </button>
    </div>
  </div>

  <div id="alertContainer"></div>

  <div class="table-container">
    <div class="mobile-scroll-hint">
    </div>
    <div class="table-responsive">
      <table class="table" id="clientsTable">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>PIN</th>
            <th>Estado</th>
            <th>Días</th>
            <th>Creación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <!-- Contenido cargado por JavaScript -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Crear/Editar Cliente -->
<div id="clientModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitle">Nuevo Cliente</h2>
      <button class="close" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body">
      <form id="clientForm">
        <input type="hidden" id="clientId" name="id">
        
        <div class="form-group">
          <label for="nombre">Nombre del Cliente *</label>
          <input type="text" id="nombre" name="nombre" required>
        </div>
        
        <div class="form-group">
          <label for="pin">PIN de Acceso *</label>
          <input type="text" id="pin" name="pin" required maxlength="10">
        </div>
        
        <div class="form-group">
          <label for="estado">Estado</label>
          <select id="estado" name="estado">
            <option value="activado">Activado</option>
            <option value="desactivado">Desactivado</option>
            <option value="bloqueado">Bloqueado</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="dias">Días de Acceso (0 = Ilimitado)</label>
          <input type="number" id="dias" name="dias" min="0" value="0">
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
      <button type="button" class="btn btn-primary" onclick="saveClient()">
        <i class="fas fa-save"></i> Guardar
      </button>
    </div>
  </div>
</div>

<script>
let clients = <?php echo json_encode($clients); ?>;
let isEditing = false;
let currentClientId = null;

// Cargar datos iniciales
document.addEventListener('DOMContentLoaded', function() {
    loadClients();
    
    // Configurar búsqueda
    document.getElementById('searchInput').addEventListener('input', function() {
        filterClients(this.value);
    });
});

function loadClients() {
    const tbody = document.getElementById('tableBody');
    
    if (Object.keys(clients).length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="fas fa-users"></i>
                    <div>No hay clientes registrados</div>
                    <small>Haz clic en "Nuevo Cliente" para agregar el primero</small>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    for (const [id, client] of Object.entries(clients)) {
        const estado = checkClientExpiration(client);
        const statusClass = `status-${estado}`;
        const statusText = estado.charAt(0).toUpperCase() + estado.slice(1);
        
        const creacion = new Date(client.creacion).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        html += `
            <tr>
                <td><strong>${escapeHtml(client.nombre)}</strong></td>
                <td><code>${escapeHtml(client.pin)}</code></td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                <td>${client.dias === 0 ? 'Ilimitado' : client.dias + ' días'}</td>
                <td>${creacion}</td>
                <td>
                    <div class="actions">
                        <button class="btn btn-warning btn-sm" onclick="editClient('${id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteClient('${id}', '${escapeHtml(client.nombre)}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
    
    tbody.innerHTML = html;
}

function checkClientExpiration(client) {
    if (client.dias > 0) {
        const created = new Date(client.creacion);
        const now = new Date();
        const diffTime = Math.abs(now - created);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > client.dias) {
            return 'vencido';
        }
    }
    return client.estado;
}

function filterClients(searchTerm) {
    const rows = document.querySelectorAll('#tableBody tr');
    const search = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        if (row.querySelector('.empty-state')) return;
        
        const text = row.textContent.toLowerCase();
        if (text.includes(search)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openCreateModal() {
    isEditing = false;
    currentClientId = null;
    document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
    document.getElementById('clientForm').reset();
    document.getElementById('clientId').value = '';
    document.getElementById('clientModal').style.display = 'block';
}

function editClient(id) {
    isEditing = true;
    currentClientId = id;
    const client = clients[id];
    
    document.getElementById('modalTitle').textContent = 'Editar Cliente';
    document.getElementById('clientId').value = id;
    document.getElementById('nombre').value = client.nombre;
    document.getElementById('pin').value = client.pin;
    document.getElementById('estado').value = client.estado;
    document.getElementById('dias').value = client.dias;
    
    // Deshabilitar campo nombre en edición
    document.getElementById('nombre').readOnly = true;
    document.getElementById('nombre').style.backgroundColor = '#f5f5f5';
    
    document.getElementById('clientModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('clientModal').style.display = 'none';
    document.getElementById('nombre').readOnly = false;
    document.getElementById('nombre').style.backgroundColor = '';
}

function saveClient() {
    const form = document.getElementById('clientForm');
    const formData = new FormData(form);
    
    const action = isEditing ? 'update' : 'create';
    formData.append('action', action);
    
    if (isEditing) {
        formData.append('id', currentClientId);
    }
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal();
            // Recargar página para actualizar datos
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Error de conexión', 'error');
        console.error('Error:', error);
    });
}

function deleteClient(id, name) {
    if (!confirm(`¿Estás seguro de que quieres eliminar al cliente "${name}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Recargar página para actualizar datos
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Error de conexión', 'error');
        console.error('Error:', error);
    });
}

function showAlert(message, type) {
    const container = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    
    container.innerHTML = `
        <div class="alert ${alertClass}" style="display: block;">
            ${escapeHtml(message)}
        </div>
    `;
    
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('clientModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

</body>
</html>