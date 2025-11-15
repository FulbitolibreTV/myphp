<?php
http_response_code(403); // fuerza el código HTTP 403
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Error 403 - Acceso prohibido</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(-45deg, #0e0e0e, #1c1c1c, #2e2e2e, #0e0e0e);
      background-size: 400% 400%;
      animation: gradientBG 10s ease infinite;
      color: #fff;
      font-family: 'Inter', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      text-align: center;
    }
    @keyframes gradientBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }
    h1 {
      font-size: 5rem;
      color: #e91e63;
      margin-bottom: 15px;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    p {
      font-size: 1.2rem;
      color: #ccc;
      max-width: 500px;
    }
    a {
      display: inline-block;
      margin-top: 25px;
      padding: 14px 30px;
      background: #ff5722;
      color: #fff;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      position: relative;
      overflow: hidden;
      transition: background 0.4s ease;
    }
    a::before {
      content: "";
      position: absolute;
      top: 0;
      left: -75%;
      width: 50%;
      height: 100%;
      background: rgba(255,255,255,0.2);
      transform: skewX(-25deg);
      transition: left 0.5s;
    }
    a:hover {
      background: #ff7043;
    }
    a:hover::before {
      left: 130%;
    }
  </style>
</head>
<body>
  <h1>🚫 403</h1>
  <p>¡Acceso prohibido!<br>
  Usted no tiene permiso para acceder al recurso solicitado.  
  El objeto está protegido contra lectura o el servidor no puede leerlo.<br><br>
  Si cree que esto es un error del servidor, por favor comuníqueselo al administrador del portal.</p>
  <a href="inde.php">Volver al inicio</a>
<script src="js/protect.js"></script>
</body>
</html>
