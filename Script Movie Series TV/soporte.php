<?php
// Cargar películas
$movies = $series = [];
if (file_exists('data/movies.json')) {
    $movies = json_decode(file_get_contents('data/movies.json'), true);
}
if (file_exists('data/series.json')) {
    $series = json_decode(file_get_contents('data/series.json'), true);
}

// Añadir propiedad "tipo" para diferenciarlos
foreach ($movies as &$m) $m['tipo'] = 'Pelicula';
foreach ($series as &$s) $s['tipo'] = 'Serie';

$contenido = array_merge(array_values($movies), array_values($series));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Soporte - CorpSRTony</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet" />
  <style>
    body {
      margin:0;
      padding:20px;
      font-family:'Orbitron', sans-serif;
      background: radial-gradient(circle at top left, #0f0f1a, #000);
      color: #eee;
    }
    h1 {
      color: #0ff;
      text-align: center;
      margin-bottom: 15px;
      text-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    }
    p {
      text-align: center;
      max-width: 600px;
      margin: 0 auto 30px;
      line-height: 1.6;
      color: #aaa;
    }
    .formulario {
      max-width: 600px;
      margin: auto;
      background: #111;
      padding: 25px;
      border-radius: 15px;
      border: 2px solid #0ff40;
      box-shadow: 0 0 15px #0ff60;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: #0ff;
      text-shadow: 0 0 5px #0ff;
    }
    select, textarea, input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-top: 8px;
      border: 2px solid #0ff40;
      border-radius: 10px;
      background: #000;
      color: #0ff;
      font-size: 1rem;
      box-shadow: 0 0 8px #0ff;
    }
    select:focus, textarea:focus, input[type="text"]:focus {
      outline: none;
      box-shadow: 0 0 15px #ff0, 0 0 30px #ff0;
      border-color: #ff0;
    }
    textarea::placeholder, input[type="text"]::placeholder { color: #555; }
    .btn-enviar {
      display: block;
      margin: 25px auto 0;
      padding: 14px 28px;
      background: linear-gradient(135deg, #ff0044, #ff8800);
      border: none;
      border-radius: 30px;
      color: white;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      opacity: 0.5;
      pointer-events: none;
      transition: all 0.4s;
      box-shadow: 0 0 15px #ff0044;
    }
    .btn-enviar.activo {
      opacity: 1;
      pointer-events: auto;
    }
    .btn-enviar:hover {
      transform: scale(1.05);
      background: linear-gradient(135deg, #ff8800, #ff0044);
      box-shadow: 0 0 20px #ff8800, 0 0 40px #ff8800;
    }
    #pelicula-select, #publicidad-select {
      display: none;
    }
    #resultadosBusqueda div:hover {
      background: #222;
      color: #ff0;
    }
  </style>
</head>
<body>

<h1>🚀 Soporte</h1>
<p>Reporta errores, enlaces caídos, bugs, problemas visuales o solicita publicidad. El equipo de CorpSRTony te responderá pronto.</p>

<div class="formulario">
  <label for="tipo">¿Qué deseas reportar?</label>
  <select id="tipo">
    <option value="">Selecciona una opción</option>
    <option value="Link caído">Link caído</option>
    <option value="Bug o error de carga">Bug o error de carga</option>
    <option value="Problema visual o diseño">Problema visual o diseño</option>
    <option value="Publicidad">Publicidad</option>
    <option value="Otro">Otro</option>
  </select>

  <div id="pelicula-select">
    <label for="buscarPelicula">Busca la película o serie afectada</label>
    <input type="text" id="buscarPelicula" placeholder="Empieza a escribir..." autocomplete="off">
    <div id="resultadosBusqueda" style="background:#000; border-radius:10px; margin-top:5px; max-height:200px; overflow-y:auto; border:1px solid #0ff; box-shadow: 0 0 10px #0ff;"></div>
    <input type="hidden" id="peliculaElegida" value="">
  </div>

  <div id="publicidad-select">
    <label for="nombrePublicidad">Tu nombre</label>
    <input type="text" id="nombrePublicidad" placeholder="Ej: Juan Perez">

    <label for="contactoPublicidad">Contacto (Telegram o WhatsApp)</label>
    <input type="text" id="contactoPublicidad" placeholder="Ej: @usuario o +573001234567">
  </div>

  <label for="mensaje">Describe el problema o consulta (mínimo 10 caracteres)</label>
  <textarea id="mensaje" rows="6" placeholder="Ejemplo: Quisiera pautar en su sitio..."></textarea>

  <button class="btn-enviar" id="btnEnviar">Enviar Reporte</button>
</div>

<script>
const tipo = document.getElementById('tipo');
const peliculaDiv = document.getElementById('pelicula-select');
const buscarPelicula = document.getElementById('buscarPelicula');
const resultados = document.getElementById('resultadosBusqueda');
const peliculaElegidaInput = document.getElementById('peliculaElegida');
const publicidadDiv = document.getElementById('publicidad-select');
const nombrePublicidad = document.getElementById('nombrePublicidad');
const contactoPublicidad = document.getElementById('contactoPublicidad');
const mensaje = document.getElementById('mensaje');
const btn = document.getElementById('btnEnviar');

let peliculas = <?php echo json_encode($contenido); ?>;

function filtrarPeliculas() {
  const q = buscarPelicula.value.toLowerCase().trim();
  resultados.innerHTML = '';
  if (q.length < 2) return;

  const filtradas = peliculas.filter(p => p.title.toLowerCase().includes(q));
  filtradas.forEach(p => {
    const div = document.createElement('div');
	div.textContent = `${p.title} (${p.tipo})`;
    div.style.padding = '8px';
    div.style.cursor = 'pointer';
    div.style.borderBottom = '1px solid #0ff';
    div.addEventListener('click', () => {
      buscarPelicula.value = p.title;
	peliculaElegidaInput.value = `${p.tipo}: ${p.title}`;
      resultados.innerHTML = '';
      validar();
    });
    resultados.appendChild(div);
  });
}

buscarPelicula.addEventListener('input', () => {
  filtrarPeliculas();
  validar();
});

function validar() {
  let minimoCaracteres = mensaje.value.trim().length >= 10;
  let tipoElegido = tipo.value !== "";
  let peliculaElegida = true;
  let publicidadValida = true;

  if (tipo.value === "Link caído") {
    peliculaDiv.style.display = "block";
    publicidadDiv.style.display = "none";
    peliculaElegida = peliculaElegidaInput.value !== "";
  } else if (tipo.value === "Publicidad") {
    peliculaDiv.style.display = "none";
    publicidadDiv.style.display = "block";
    publicidadValida = nombrePublicidad.value.trim() !== "" && contactoPublicidad.value.trim() !== "";
  } else {
    peliculaDiv.style.display = "none";
    publicidadDiv.style.display = "none";
  }

  if (minimoCaracteres && tipoElegido && peliculaElegida && publicidadValida) {
    btn.classList.add('activo');
  } else {
    btn.classList.remove('activo');
  }
}

tipo.addEventListener('change', validar);
mensaje.addEventListener('input', validar);
buscarPelicula.addEventListener('input', validar);
nombrePublicidad.addEventListener('input', validar);
contactoPublicidad.addEventListener('input', validar);

function checkBloqueo() {
  const ultimoEnvio = localStorage.getItem('ultimoReporte');
  if (ultimoEnvio && Date.now() - parseInt(ultimoEnvio) < 36) {
    btn.textContent = "⏳ Ya enviaste un reporte, espera 1 hora";
    btn.classList.remove('activo');
    btn.style.opacity = 0.6;
    btn.style.pointerEvents = 'none';
  }
}
checkBloqueo();

btn.addEventListener('click', () => {
  if (!btn.classList.contains('activo')) return;

  const data = {
    tipo: tipo.value,
    pelicula: tipo.value === "Link caído" ? peliculaElegidaInput.value : "",
    nombrePublicidad: tipo.value === "Publicidad" ? nombrePublicidad.value.trim() : "",
    contactoPublicidad: tipo.value === "Publicidad" ? contactoPublicidad.value.trim() : "",
    mensaje: mensaje.value.trim()
  };

  fetch('guardar_reporte.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      alert("✅ Tu reporte fue guardado correctamente");
      localStorage.setItem('ultimoReporte', Date.now());
      location.reload();
    } else {
      alert("❌ Error al guardar el reporte");
    }
  })
  .catch(err => {
    alert("❌ Error de conexión");
    console.error(err);
  });
});
</script>

</body>
</html>
