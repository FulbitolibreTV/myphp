<?php
$html_generado = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serverUrl'])) {
    $url = rtrim(trim($_POST['serverUrl']), '/');
    if ($url) {
        $reportesApi = $url . "/admin/api/reportes.php";
        $html_generado = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Soporte - CorpSRTony</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet" />
<style>
body {margin:0;padding:20px;font-family:'Orbitron',sans-serif;background-color:#0e0e0e;color:#fff;font-size:14px;}
h1{text-align:center;color:#00ffe0;text-shadow:0 0 5px #00ffe0;margin-bottom:20px;}
p{text-align:center;max-width:600px;margin:0 auto 30px;line-height:1.6;color:#ccc;}
.formulario{max-width:600px;margin:auto;background-color:#111;padding:20px;border-radius:12px;box-shadow:0 0 15px rgba(0,255,255,0.1);}
label{display:block;margin-top:15px;font-weight:bold;color:#00ffe0;text-shadow:0 0 3px #00ffe0;}
input,textarea,select{width:100%;padding:10px;margin-top:8px;border:none;border-radius:8px;background-color:#1c1c1c;color:white;font-size:1rem;box-shadow:inset 0 0 8px rgba(0,255,255,0.2);transition:box-shadow 0.3s;}
input:focus,textarea:focus,select:focus{outline:none;box-shadow:0 0 8px rgba(0,255,255,0.5);}
textarea::placeholder,input::placeholder{color:#aaa;}
.btn-enviar{display:block;margin:25px auto 0;padding:12px 25px;background:#00ffe0;color:#000;font-size:1.1rem;border:none;border-radius:8px;cursor:pointer;opacity:0.5;pointer-events:none;transition:all 0.3s ease;box-shadow:0 0 10px rgba(0,255,255,0.5);}
.btn-enviar.activo{opacity:1;pointer-events:auto;transform:translateY(-2px);}
.btn-enviar:hover.activo{background:#00e6d4;box-shadow:0 0 15px rgba(0,255,255,0.7);}
</style>
</head>
<body>

<h1>🚀 Soporte</h1>
<p>Reporta errores, enlaces caídos, bugs, problemas visuales, solicita publicidad o cualquier otra consulta. El equipo de CorpSRTony te responderá pronto.</p>

<div class="formulario">
  <label for="tipo">¿Qué deseas reportar?</label>
  <select id="tipo">
    <option value="">Selecciona una opción</option>
    <option value="Link caído">Link caído</option>
    <option value="Publicidad">Publicidad</option>
    <option value="Bug o error de carga">Bug o error de carga</option>
    <option value="Problema visual o diseño">Problema visual o diseño</option>
    <option value="Otro">Otro</option>
  </select>

  <div id="linkDiv" style="display:none;">
    <label for="linkCaido">Nombre de la película o serie (mínimo 2 caracteres)</label>
    <input type="text" id="linkCaido" placeholder="Escribe aquí...">
  </div>

  <div id="pubDiv" style="display:none;">
    <label for="nombrePub">Tu nombre</label>
    <input type="text" id="nombrePub" placeholder="Ej: Juan Perez">
    <label for="contactoPub">Contacto (Telegram o WhatsApp)</label>
    <input type="text" id="contactoPub" placeholder="Ej: @usuario o +573001234567">
  </div>

  <label for="mensaje">Describe el problema o consulta (mínimo 10 caracteres)</label>
  <textarea id="mensaje" rows="6" placeholder="Ejemplo: La película X no se carga, probé en dos dispositivos diferentes y sigue igual."></textarea>

  <button class="btn-enviar" id="btnEnviar">Enviar Reporte</button>
</div>

<script>
const tipo = document.getElementById('tipo');
const linkDiv = document.getElementById('linkDiv');
const linkCaido = document.getElementById('linkCaido');
const pubDiv = document.getElementById('pubDiv');
const nombrePub = document.getElementById('nombrePub');
const contactoPub = document.getElementById('contactoPub');
const mensaje = document.getElementById('mensaje');
const btn = document.getElementById('btnEnviar');

function validar() {
  let tipoElegido = tipo.value !== "";
  let mensajeValido = mensaje.value.trim().length >= 10;
  let linkValido = true;
  let pubValido = true;

  if(tipo.value === "Link caído"){
    linkDiv.style.display = "block";
    pubDiv.style.display = "none";
    linkValido = linkCaido.value.trim().length >= 2;
  } else if(tipo.value === "Publicidad"){
    linkDiv.style.display = "none";
    pubDiv.style.display = "block";
    pubValido = nombrePub.value.trim() !== "" && contactoPub.value.trim() !== "";
  } else {
    linkDiv.style.display = "none";
    pubDiv.style.display = "none";
  }

  if(tipoElegido && mensajeValido && linkValido && pubValido){
    btn.classList.add('activo');
  } else {
    btn.classList.remove('activo');
  }
}

tipo.addEventListener('change', validar);
mensaje.addEventListener('input', validar);
linkCaido.addEventListener('input', validar);
nombrePub.addEventListener('input', validar);
contactoPub.addEventListener('input', validar);

btn.addEventListener('click', () => {
  if(!btn.classList.contains('activo')) return;

  const data = {
    tipo: tipo.value,
    linkCaido: tipo.value==="Link caído"?linkCaido.value.trim():"",
    nombrePub: tipo.value==="Publicidad"?nombrePub.value.trim():"",
    contactoPub: tipo.value==="Publicidad"?contactoPub.value.trim():"",
    mensaje: mensaje.value.trim()
  };

  fetch('$reportesApi', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(data)
  })
  .then(res=>res.json())
  .then(res=>{
    if(res.success){
      alert("✅ Tu reporte fue guardado correctamente");
      mensaje.value = "";
      linkCaido.value = "";
      nombrePub.value = "";
      contactoPub.value = "";
      tipo.value = "";
      validar();
    } else {
      alert("❌ Error al guardar el reporte: "+(res.error||""));
    }
  })
  .catch(err=>{
    alert("❌ Error de conexión");
    console.error(err);
  });
});
</script>

</body>
</html>
HTML;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Generador Soporte PHP - CorpSRTony Cine</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;background:#f4f6fc;}
.sidebar{width:250px;background:#1a237e;color:white;height:100vh;position:fixed;left:0;top:0;overflow-y:auto;padding:1.5rem 1rem;transition:left 0.3s;}
.sidebar h1{text-align:center;font-size:1.4rem;margin-bottom:1.2rem;}
.sidebar .section-title{font-size:0.8rem;text-transform:uppercase;opacity:0.7;margin:1rem 0 0.5rem 0;padding-left:1rem;}
.sidebar a{display:flex;align-items:center;gap:10px;color:white;text-decoration:none;padding:0.5rem 1rem;border-radius:6px;margin-bottom:0.3rem;font-size:0.95rem;}
.sidebar a:hover{background:rgba(255,255,255,0.2);}
.menu-toggle{display:none;position:fixed;top:15px;left:15px;background:#1a237e;color:white;padding:0.5rem 0.7rem;border-radius:5px;z-index:1100;cursor:pointer;}
.main-content{margin-left:250px;padding:2rem;transition:margin-left 0.3s;}
.content-wrapper{max-width:700px;margin:0 auto;display:flex;flex-direction:column;align-items:center;width:100%;}
form, textarea{width:100%;max-width:600px;margin-top:1.2rem;}
textarea,input[type="text"]{width:100%;padding:0.8rem;margin:0.8rem 0;border:1px solid #ccc;border-radius:6px;background:#1a1a1a;color:#0f0;font-family:monospace;}
button{background:#1a237e;color:white;padding:0.6rem 1.2rem;border:none;border-radius:6px;font-weight:bold;cursor:pointer;margin-top:1rem;}
button:hover{background:#151d6e;}
.info-box{background:#e8f5e8;border:1px solid #4caf50;border-radius:8px;padding:1rem;margin:1rem 0;color:#2e7d32;}
.info-box h3{margin:0 0 0.5rem 0;color:#1b5e20;}
.info-box ul{margin:0.5rem 0;padding-left:1.5rem;}
@media(max-width:768px){
  .sidebar{left:-250px;}
  .sidebar.open{left:0;}
  .main-content{margin-left:0;padding-top:4rem;}
  .menu-toggle{display:block;}
}
</style>
</head>
<body>
<div class="menu-toggle"><i class="fas fa-bars"></i></div>
<div class="sidebar">
  <h1>Admin Panel</h1>
  <div class="section-title">Menú Principal</div>
  <a href="../index.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="../manage-series.php"><i class="fas fa-tv"></i> Series</a>
  <a href="../create-movie.php"><i class="fas fa-video"></i> Crear Película</a>
  <a href="../manage-categories.php"><i class="fas fa-layer-group"></i> Categorías</a>
        <a href="../detv.php"><i class="fas fa-play-circle"></i> TV</a>
  <div class="section-title">⚙️ Configuración</div>
  <a href="../config_home.php"><i class="fas fa-home"></i> Config Home</a>
  <a href="../config-mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a>
  <div class="section-title">🔧 Admin Tools</div>
  <a href="../soporte_config.php"><i class="fas fa-headset"></i> Soporte</a>
  <a href="../configure-api.php"><i class="fas fa-key"></i> TMDB API</a>
  <a href="../api/generadores.php"><i class="fas fa-cogs"></i> App Creator 24 📲</a>
  <a href="../monetizacion.php"><i class="fas fa-dollar-sign"></i> Monetización</a>
  <a href="../telegram_publicar.php"><i class="fas fa-paper-plane"></i> Telegram</a>
  <div class="section-title">Usuario</div>
  <a href="../perfil.php"><i class="fas fa-user"></i> Perfil</a>
  <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>
<div class="main-content">
  <div class="content-wrapper">
    <h1>🚀 Generador Página de Soporte</h1>
    
    <div class="info-box">
      <h3><i class="fas fa-info-circle"></i> Información del Generador</h3>
      <p>Este generador crea una página de soporte idéntica a la original pero con URL dinámica.</p>
      <ul>
        <li><strong>API de Reportes:</strong> /admin/api/reportes.php</li>
        <li><strong>Único cambio:</strong> URL del fetch reemplazada por la tuya</li>
      </ul>
    </div>
    
    <form method="post">
      <p>👉 Ingresa la URL base de tu servidor:</p>
      <input type="text" name="serverUrl" placeholder="https://tuservidor.com" required>
      <button type="submit"><i class="fas fa-headset"></i> Generar Página de Soporte</button>
    </form>
    
    <?php if ($html_generado): ?>
      <h2>✅ HTML de Soporte Generado (guarda en seccion html):</h2>
      <div class="info-box">
        <h3><i class="fas fa-download"></i> Instrucciones de Uso</h3>
        <ul>
          <li>Guarda la referencia como <strong>soporte</strong></li>
          <li>API configurada: <strong><?php echo htmlspecialchars($_POST['serverUrl'] . '/admin/api/reportes.php'); ?></strong></li>
          <li>Funcionará exactamente como el original</li>
        </ul>
      </div>
      <textarea readonly rows="20"><?php echo htmlspecialchars($html_generado); ?></textarea>
    <?php endif; ?>
  </div>
</div>
<script>
document.querySelector('.menu-toggle').addEventListener('click', () => {
  document.querySelector('.sidebar').classList.toggle('open');
});
</script>
</body>
</html>