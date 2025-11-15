<?php
http_response_code(408);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Error 408 - Tiempo agotado</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { /* mismo estilo */ }
h1 { color: #03a9f4; }
a { background: #03a9f4; }
a:hover { background: #039be5; }
</style>
</head>
<body>
  <h1>⏳ 408</h1>
  <p>Tiempo de espera agotado. El servidor tardó demasiado esperando su petición.</p>
  <a href="inde.php">Volver al inicio</a>
</body>
</html>
