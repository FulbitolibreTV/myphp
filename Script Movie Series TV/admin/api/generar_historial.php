<?php
require_once '../../config.php';
if (!check_session()) { header('Location: ../login.php'); exit; }

$current_user = $_SESSION['username'];
$users_file = '../../data/usuarios.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$current_data = $users[$current_user] ?? ['name' => $current_user, 'profile_image' => 'perfil.png', 'role' => 'editor'];
$current_name = $_SESSION['name'] ?? $current_user;
$current_image = $current_data['profile_image'];
$is_admin = in_array($current_data['role'], ['admin', 'super_admin']);
$is_super_admin = $current_data['role'] === 'super_admin';
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
.main-content {
  flex:1; margin-left: 250px; padding: 2rem;
  display:flex; flex-direction:column; justify-content:center; align-items:center;
  text-align:center;
}
.main-content h1 {
  font-size:2rem; color:#1a237e; margin-bottom:1rem;
}
.main-content p {
  font-size:1.2rem; color:#333;
}
@media(max-width:768px){
  .hamburger { display:block; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  .main-content { margin-left:0; padding-top:4rem; }
}

<!-- ✅ CSS adicional para el bloque de copiar HTML -->
.contenido-principal {
  margin-left: 600px; /* 🔧 más separación */
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 2rem;
  min-height: 100vh;
  box-sizing: border-box;
  transition: margin-left 0.3s ease;
}

/* Contenedor del contenido */
.recurso-html {
  width: 100%;
  max-width: 800px;
  background: white;
  padding: 30px;
  border-radius: 40px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  text-align: center;
  margin: 0 auto;
}

.recurso-html p {
  font-weight: bold;
  margin-bottom: 10px;
  color: #1a237e;
}

.recurso-html textarea {
  width: 100%;
  height: 200px;
  resize: none;
  border: 1px solid #ccc;
  padding: 10px;
  font-family: monospace;
  border-radius: 8px;
  overflow: auto;
  background: #f9f9f9;
}

.recurso-html button {
  margin-top: 25px;
  background: #1a237e;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
}

.recurso-html button:hover {
  background: #283593;
}

/* Responsive */
@media(max-width: 768px){
  .contenido-principal {
    margin-left: 0; /* ✅ Elimina espacio en móvil */
    padding: 1rem;
    flex-direction: column;
    align-items: stretch;
  }

  .recurso-html {
    margin: 0 10px;
    padding: 15px;
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
    <a href="generadores.php"><i class="fas fa-cogs"></i> App Creator 24 📲</a>
    <a href="../monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
    <a href="../telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <?php endif; ?>

  <div class="section-title">Usuario</div>
  <a href="../perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<!-- ✅ BLOQUE HTML PARA MOSTRAR Y COPIAR -->
<div class="recurso-html">
<h2 style="font-size: 2rem; color: #1a237e; margin-bottom: 20px;">📌 HTML para Historial</h2>

<p style="font-size: 1.1rem; margin-bottom: 10px;">
  Este código está diseñado para ayudarte fácilmente a colocar en seccion HTML en App Creator 24.
</p>

<p style="color: #d32f2f; font-weight: bold;">
  Este código deves de pegarlo en seccion html y colocar como historial en referencia.</p>
</p>

<p style="font-size: 0.95rem; margin-top: 15px;">
  Puedes copiar el contenido del cuadro inferior y usarlo en tu aplicación.
</p>


  <?php
  $ruta_archivo = 'comple/historial.html';
  $codigo_html = file_exists($ruta_archivo) ? file_get_contents($ruta_archivo) : 'Archivo no encontrado.';
  ?>

  <textarea id="codigoHtml" readonly onclick="this.select();">
<?= htmlspecialchars($codigo_html) ?>
  </textarea>

  <button onclick="copiarHtml()">📋 Copiar HTML</button>
</div>

<script>
function copiarHtml() {
  const textarea = document.getElementById("codigoHtml");
  textarea.select();
  textarea.setSelectionRange(0, 99999);
  document.execCommand("copy");
  alert("¡Código copiado al portapapeles!");
}
</script>


<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
}
</script>

</body>
</html>
