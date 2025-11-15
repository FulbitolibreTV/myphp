<?php
http_response_code(429);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Error 429 - Demasiadas solicitudes</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { /* mismo estilo */ }
h1 { color: #4caf50; }
a { background: #4caf50; }
a:hover { background: #43a047; }
</style>
</head>
<body>
  <h1>🚀 429</h1>
  <p>Demasiadas solicitudes. Ha superado el límite permitido. Por favor, espere un momento y vuelva a intentarlo.</p>
  <a href="inde.php">Volver al inicio</a>
</body>
</html>
