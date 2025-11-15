<?php
http_response_code(401);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Error 401 - No autorizado</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* igual que antes, solo cambia el color principal */
body { /*...*/ }
h1 { color: #ff9800; }
a { background: #ff9800; }
a:hover { background: #fb8c00; }
</style>
</head>
<body>
  <h1>🔒 401</h1>
  <p>No autorizado. Este recurso requiere autenticación. Por favor, inicie sesión para continuar.</p>
  <a href="inde.php">Volver al inicio</a>
</body>
</html>
