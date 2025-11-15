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

$categories_file = '../data/categories.json';
$categories = file_exists($categories_file) ? json_decode(file_get_contents($categories_file), true) : [];

// Agregar categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $categories[] = ['name' => $name];
        file_put_contents($categories_file, json_encode($categories, JSON_PRETTY_PRINT));
        header('Location: manage-categories.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categorías - CorpSRTony</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;background:#f4f6fc;display:flex;min-height:100vh;}
.sidebar{
    width:250px;background:#1a237e;color:white;height:100vh;
    position:fixed;left:0;top:0;overflow-y:auto;padding:1.5rem 1rem;
    transition: transform 0.3s ease; z-index:1000;
}
.sidebar h1{text-align:center;font-size:1.4rem;margin-bottom:1.2rem;}
.sidebar .section-title{font-size:0.8rem;text-transform:uppercase;opacity:0.7;margin:1rem 0 0.5rem;padding-left:1rem;}
.sidebar a{display:flex;align-items:center;gap:10px;color:white;text-decoration:none;padding:0.5rem 1rem;border-radius:6px;margin-bottom:0.3rem;}
.sidebar a:hover{background:rgba(255,255,255,0.2);}
.main-content{flex:1;margin-left:250px;padding:2rem;}
.card{background:white;padding:2rem;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.1);max-width:700px;margin:1rem auto;}
.input{width:100%;padding:0.8rem;margin:0.8rem 0;border:1px solid #ccc;border-radius:6px;}
.btn{background:#1a237e;color:white;border:none;padding:0.6rem 1rem;border-radius:6px;font-weight:bold;cursor:pointer;}
ul{list-style:none;padding:0;}
li{display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid #eee;}
li.dragging{opacity:0.5;}
.drag-handle{cursor:grab;}
.save-order-btn{margin-top:1rem;display:block;width:100%;background:#4caf50;color:white;border:none;padding:0.8rem;border-radius:6px;font-size:1rem;cursor:pointer;}
.save-order-btn:hover{background:#45a049;}
/* Responsive */
@media (max-width: 768px){
    .sidebar{
        transform: translateX(-100%);
    }
    .sidebar.active{
        transform: translateX(0);
    }
    .main-content{
        margin-left:0;
        padding:1rem;
    }
    .card{
        padding:1rem;
        margin:1rem;
    }
    li{
        flex-direction:column;
        align-items:flex-start;
    }
    li div{
        margin-top:0.5rem;
    }
    .btn, .save-order-btn{
        width:100%;
    }
}
/* Hamburguesa */
.hamburger {
    position: fixed; top: 1rem; left: 1rem; font-size: 1.5rem;
    background: #1a237e; color: white; border: none; padding: 0.6rem;
    border-radius: 6px; z-index: 1100; cursor: pointer; display: none;
}
@media(max-width:768px){
    .hamburger { display:block; }
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
    <h2><i class="fas fa-layer-group"></i> Categorías de Películas</h2>
    <div class="card">
        <h3><i class="fas fa-lightbulb"></i> Sugerencia</h3>
        <p style="margin-bottom:1rem;">Algunas categorías populares: <strong>Acción, Comedia, Drama, Terror, Ciencia Ficción, Romántica, Documental, Familiar, Animación</strong>. ¡Adáptalas según tu catálogo!</p>
        <h3><i class="fas fa-plus"></i> Agregar Nueva Categoría</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Nombre de la categoría" required class="input">
            <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar</button>
        </form>
    </div>

    <div class="card">
        <h3><i class="fas fa-arrows-alt"></i> Ordenar / Editar / Eliminar Categorías</h3>
        <ul id="category-list">
            <?php foreach ($categories as $index => $cat): ?>
                <li draggable="true">
                    <span class="drag-handle"><i class="fas fa-grip-lines"></i> <?= htmlspecialchars($cat['name']) ?></span>
                    <div>
                        <button onclick="editCategory(<?= $index ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>')" style="background:#ff9800;border:none;color:white;padding:4px 8px;border-radius:4px;margin-left:5px;cursor:pointer;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteCategory(<?= $index ?>)" style="background:#f44336;border:none;color:white;padding:4px 8px;border-radius:4px;margin-left:5px;cursor:pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <button class="save-order-btn" onclick="saveOrder()">Guardar Orden</button>
    </div>
</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
}
const list = document.getElementById('category-list');
let draggedItem = null;
list.addEventListener('dragstart', e => {
    draggedItem = e.target;
    e.target.classList.add('dragging');
});
list.addEventListener('dragend', e => {
    e.target.classList.remove('dragging');
});
list.addEventListener('dragover', e => {
    e.preventDefault();
    const afterElement = getDragAfterElement(list, e.clientY);
    if (afterElement == null) {
        list.appendChild(draggedItem);
    } else {
        list.insertBefore(draggedItem, afterElement);
    }
});
function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('li:not(.dragging)')];
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child }
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}
function saveOrder(){
    const names = [...list.querySelectorAll('li span')].map(span => span.textContent.trim());
    fetch('save_categories_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({order: names})
    }).then(res => res.text()).then(msg => {
        alert(msg);
        location.reload();
    });
}
function editCategory(index, oldName) {
    const newName = prompt("Editar nombre de categoría:", oldName);
    if (newName && newName.trim() !== "" && newName !== oldName) {
        fetch('edit_category.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ index: index, newName: newName })
        })
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            location.reload();
        });
    }
}
function deleteCategory(index) {
    if (confirm("¿Seguro que quieres eliminar esta categoría?")) {
        fetch('delete_category.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ index: index })
        })
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            location.reload();
        });
    }
}
</script>

<?php include '../components/notificaciones_bell.php'; ?>
<?php include '../components/version_check.php'; ?>
</body>
</html>
