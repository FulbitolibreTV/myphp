<?php
require_once 'config.php';
if (!check_session() || !is_super_admin()) {
    header('Location: login.php');
    exit;
}

$report_file = 'data/reportes.json';
$reportes = file_exists($report_file) ? json_decode(file_get_contents($report_file), true) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
body { font-family: 'Inter', sans-serif; background:#f4f6fc; padding:2rem; }
h1 { text-align:center; color:#1a237e; }
.reporte {
  background:white; padding:1rem; border-radius:8px; 
  box-shadow:0 2px 6px rgba(0,0,0,0.1); 
  margin:1rem auto; max-width:600px;
}
.reporte strong { color:#1a237e; }
.borrar {
  background:#c62828; color:white; border:none; padding:6px 12px;
  border-radius:6px; cursor:pointer; float:right;
}
</style>
</head>
<body>

<h1>📋 Reportes de Soporte</h1>

<?php if (empty($reportes)): ?>
  <p style="text-align:center;">No hay reportes pendientes.</p>
<?php else: ?>
  <?php foreach ($reportes as $idx => $r): ?>
    <div class="reporte">
      <strong>Tipo:</strong> <?= htmlspecialchars($r['tipo']) ?><br>
      <strong>Pelicula:</strong> <?= htmlspecialchars($r['pelicula']) ?><br>
      <strong>Mensaje:</strong> <?= nl2br(htmlspecialchars($r['mensaje'])) ?><br>
      <small><em><?= $r['fecha'] ?></em></small>
      <form method="post" action="eliminar_reporte.php" style="margin-top:10px;">
        <input type="hidden" name="index" value="<?= $idx ?>">
        <button type="submit" class="borrar"><i class="fas fa-trash"></i> Borrar</button>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
