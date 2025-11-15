<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $archivo = '../data/accessclient.json';
  $usuarios = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];

  $nuevoUsuario = [
    'nombre' => $_POST['nombre'],
    'pin' => $_POST['pin'],
    'estado' => 'activo',
    'dias' => 30,
    'creado' => date('Y-m-d')
  ];

  $usuarios[] = $nuevoUsuario;

  file_put_contents($archivo, json_encode($usuarios, JSON_PRETTY_PRINT));
  http_response_code(200);
}
?>
