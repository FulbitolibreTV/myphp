<?php
http_response_code(405);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Error 405 - Método no permitido</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { /* mismo estilo */ }
h1 { color: #9c27b0; }
a { background: #9c27b0; }
a:hover { background: #8e24aa; }
</style>
</head>
<body>
  <h1>🚫 405</h1>
  <p>Método no permitido. El método HTTP usado no está permitido para este recurso.</p>
  <a href="inde.php">Volver al inicio</a>
</body>
</html>
