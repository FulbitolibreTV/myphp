<?php
require_once 'config.php';

// Verifica sesión (opcional, si quieres protegerlo solo para admin)
if (!check_session()) {
    header('Location: 403.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentacion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0e0e0e;
            color: #eee;
            line-height: 1.7;
            margin: 0;
            padding: 0;
        }
        header {
            background: #1a1a1a;
            padding: 20px;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #ff6347;
        }
        section {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        section h2 {
            color: #00bcd4;
            margin-top: 30px;
            border-left: 5px solid #00bcd4;
            padding-left: 10px;
        }
        pre, code {
            background: #1e1e1e;
            padding: 12px;
            display: block;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 0.9rem;
        }
        ul {
            padding-left: 20px;
        }
        footer {
            text-align: center;
            padding: 20px;
            color: #aaa;
            font-size: 0.9rem;
            background: #111;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <h1>📚 Documentación</h1>
    </header>

    <section>
        <h2>🚀 Introducción</h2>
        <p>Este documento explica cómo funciona toda la plataforma <strong>CorpSRTony Cine</strong> incluyendo estructura, archivos JSON, modo mantenimiento, soporte Telegram, favoritos, historial, detección de adblock y más.</p>
    </section>

    <section>
        <h2>📁 Estructura de archivos</h2>
<pre>
/index.php          -> Página principal
/403.php, 404.php   -> Errores personalizados
/mantenimiento.php  -> Modo mantenimiento
/categoria.php      -> Películas por categoría
/peliculas/         -> Detalles de películas (ID.php)
/favoritos.php      -> Favoritos con localStorage
/historial.php      -> Historial con localStorage
/soporte.php        -> Soporte Telegram
/admin/             -> Panel admin
/data/
    site_config.json -> Config del sitio
    site_info.json   -> Info general
    movies.json      -> Películas cargadas
/assets/            -> Imágenes y favicon
/admin/css/         -> Estilos personalizados
</pre>
    </section>

    <section>
        <h2>🎬 Cómo carga películas y categorías</h2>
        <p>El archivo <code>movies.json</code> contiene las películas, cada una con:</p>
<pre>{
  "605722": {
    "id": "605722",
    "title": "Larga Distancia",
    "category": "drama",
    "poster_path": "/img.jpg"
  }
}</pre>
        <p>El <strong>index.php</strong> lee este JSON desde PHP, agrupa por categoría, y genera los bloques de películas dinámicamente. Al hacer clic en una categoría se abre <code>categoria.php</code> que muestra solo esa categoría.</p>
    </section>

    <section>
        <h2>🔧 Configuración del sitio</h2>
        <p>Los colores, el nombre del sitio, el favicon, y redes sociales están en <code>site_info.json</code>:</p>
<pre>{
  "title": "🎥 CorpSRTony Cine",
  "favicon": "assets/favicon.png",
  "main_color": "#0e0e0e",
  "header_color": "#1a1a1a",
  "telegram": "https://t.me/TuCanalTelegram"
}</pre>
    </section>

    <section>
        <h2>🛠 Modo mantenimiento</h2>
        <p>Controlado desde <code>site_config.json</code>:</p>
<pre>{
  "maintenance": true,
  "flotante_active": true
}</pre>
        <p>Si <code>maintenance</code> es <code>true</code>, el <strong>index.php</strong> redirige a <code>mantenimiento.php</code> salvo para usuarios admin.</p>
    </section>

    <section>
        <h2>💬 Soporte por Telegram</h2>
        <p>Se configura en <code>site_info.json</code> y se usa en <code>soporte.php</code> y en el botón flotante. Al hacer clic abre el chat en Telegram.</p>
<pre><a href="https://t.me/TuCanalTelegram">Chatea en Telegram</a></pre>
    </section>

    <section>
        <h2>❤️ Favoritos & Historial</h2>
        <p>Guardados en el navegador del usuario usando <code>localStorage</code>. No requiere base de datos ni PHP.</p>
        <ul>
            <li><code>favoritos.php</code> muestra lo que marcaste con el corazón.</li>
            <li><code>historial.php</code> muestra lo que has visto.</li>
        </ul>
    </section>

    <section>
        <h2>🚫 Protección & AdBlock</h2>
        <p>Los JSON están protegidos con:</p>
<pre>.htaccess
<FilesMatch "\.(json)$">
  Order allow,deny
  Deny from all
</FilesMatch>
</pre>
        <p>Además hay un script que detecta AdBlock y muestra un popup solicitando desactivarlo para apoyar el sitio.</p>
    </section>

    <section>
        <h2>🔐 Panel admin</h2>
        <p>Permite:</p>
        <ul>
            <li>Agregar / editar / eliminar películas.</li>
            <li>Configurar datos del sitio: nombre, favicon, colores, Telegram.</li>
            <li>Activar o desactivar mantenimiento.</li>
            <li>Controlar el botón flotante y AdBlock popup.</li>
        </ul>
    </section>
<section>
<section>
  <h2>Monetizacion</h2>
  <p>La seccion de monetizacion permite agregar ingresos de diferentes formas dentro de tu app o pagina web. Algunas opciones incluyen:</p>
  <ul>
    <li><strong>Direct LINK:</strong> Puedes configurar un enlace externo que se abrira al iniciar o salir del contenido (ideal para PopUnder o redireccion monetizada).</li>
    <li><strong>Adsterra:</strong> Plataforma popular para monetizar con banners, popups o links directos.</li>
    <li><strong>Monetag (ex PropellerAds):</strong> Buena opcion para monetizar trafico movil o global.</li>
    <li><strong>Videos de YouTube:</strong> Tambien puedes incrustar trailers o anuncios de tu canal, ideal si tienes canal oficial.</li>
    <li><strong>Publicidad personalizada:</strong> Mas adelante podras cargar banners o codigos desde el admin.</li>
  </ul>
</section>
<section>
  <h2>Configuracion con Telegram</h2>
  <p>Esta seccion permite conectar tu sistema con un bot de Telegram para publicar contenido (como peliculas o series) de forma automatica.</p>
  <ul>
    <li><strong>Token del bot:</strong> Necesitas crear un bot en <a href="https://t.me/BotFather" target="_blank">BotFather</a> y copiar el token que te entrega.</li>
    <li><strong>ID del canal o grupo:</strong> Para publicar en un canal, debes agregar el bot como administrador. Puedes usar el ID con <code>@tucanal</code> o el ID numerico.</li>
    <li><strong>Mensajes:</strong> El sistema permite personalizar los mensajes que se envian al publicar, incluyendo el titulo, descripcion, imagen o enlaces.</li>
    <li><strong>Publicacion automatica:</strong> Desde el panel puedes seleccionar una pelicula o serie y enviarla directamente al canal con un boton.</li>
    <li><strong>Recomendado:</strong> Usa canales privados para pruebas antes de publicar en canales oficiales.</li>
  </ul>
</section>
<section>
  <h2>Soporte para Series y Nuevos Servidores</h2>
  <p>El sistema no solo permite agregar peliculas, sino que tambien cuenta con una seccion dedicada para agregar y administrar series.</p>
  <ul>
    <li><strong>Series:</strong> Puedes agregar series por temporadas y episodios, con sus respectivas imagenes, descripciones y enlaces.</li>
    <li><strong>Servidores soportados:</strong> Ahora se han integrado nuevos servidores de video como:</li>
    <ul>
      <li><strong>Pixeldrain:</strong> Ideal para subir archivos ligeros y reproducirlos directamente.</li>
      <li><strong>Filemoon:</strong> Plataforma popular para contenido en streaming, permite incrustar facilmente.</li>
    </ul>
    <li>Proximamente se podran agregar mas servidores y reproductores personalizados.</li>
  </ul>
</section>


    <footer>
        <p>© <?= date("Y") ?> CorpSRTony - Documentación interna</p>
    </footer>
<script src="js/protect.js"></script>
</body>
</html>
