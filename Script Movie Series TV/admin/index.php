<?php
require_once '../config.php';

// Si ya está logueado, redirigir al dashboard
if (check_session()) {
    header('Location: dashboard.php');
    exit;
}

// Si no está logueado, redirigir al login
header('Location: login.php');
exit;
?>
