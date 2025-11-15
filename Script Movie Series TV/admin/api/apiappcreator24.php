<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_url'])) {
    $site_url = trim($_POST['site_url']);

    if (empty($site_url)) {
        exit("❌ Debes proporcionar la URL del sitio.");
    }

    $template_file = __DIR__ . '/home_template.html';

    if (!file_exists($template_file)) {
        exit("❌ No se encontró el archivo home_template.html");
    }

    $html_template = file_get_contents($template_file);

    // Reemplazar los placeholders
    $html_ready = str_replace('{{site_url}}', htmlspecialchars($site_url), $html_template);

    header("Content-Type: text/html; charset=utf-8");
    echo $html_ready;
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Generar Home para App Creator 24</title>
  <style>
    body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
    .container { background: #fff; padding: 20px; max-width: 600px; margin: auto; border-radius: 8px; }
    input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; }
    button { background: #007bff; color: #fff; padding: 12px; border: none; width: 100%; border-radius: 5px; cursor: pointer; }
    button:hover { background: #0056b3; }
    textarea { width: 100%; height: 400px; margin-top: 15px; }
  </style>
</head>
<body>
<div class="container">
  <h2>Generador de Home para App Creator 24</h2>
  <form method="post">
    <label>URL de tu sitio web:</label>
    <input type="text" name="site_url" placeholder="https://www.tusitio.com" required />
    <button type="submit">Generar Home</button>
  </form>
</div>
</body>
</html>
