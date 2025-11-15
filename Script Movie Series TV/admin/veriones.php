<?php
session_start();
require_once '../config.php'; // Ajusta la ruta a tu config.php si es necesario

// Solo admin logueados
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// Obtener actualizaciones
$stmt = $pdo->query("SELECT a.id, p.nombre AS producto, a.version, a.archivo_zip, a.fecha 
                     FROM actualizaciones a
                     LEFT JOIN productos p ON a.producto_id = p.id
                     ORDER BY a.fecha DESC");
$actualizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Veriones - Admin Panel</title>
    <link rel="stylesheet" href="estilos-admin.css"> <!-- O el css que uses -->
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Tu sidebar -->
    <div class="content">
        <h1>📦 Veriones Disponibles</h1>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Versión</th>
                    <th>Archivo ZIP</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($actualizaciones as $act): ?>
                <tr>
                    <td><?= htmlspecialchars($act['id']) ?></td>
                    <td><?= htmlspecialchars($act['producto']) ?></td>
                    <td><?= htmlspecialchars($act['version']) ?></td>
                    <td>
                        <?php if($act['archivo_zip']): ?>
                            <a href="../uploads/<?= htmlspecialchars($act['archivo_zip']) ?>" target="_blank">Descargar</a>
                        <?php else: ?>
                            No disponible
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($act['fecha']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($actualizaciones) == 0): ?>
                <tr><td colspan="5">No hay actualizaciones registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
