<?php
http_response_code(400);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Error 400 - Solicitud incorrecta</title>
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
    color: #f44336;
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
    background: #f44336;
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
    background: #e53935;
  }
  a:hover::before {
    left: 130%;
  }
</style>
</head>
<body>
  <h1>❌ 400</h1>
  <p>Solicitud incorrecta. El servidor no pudo entender la petición por sintaxis inválida.</p>
  <a href="inde.php">Volver al inicio</a>
</body>
</html>
