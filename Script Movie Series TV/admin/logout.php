<?php
require_once '../config.php';

logout_user();
header('Location: login.php');
exit;
?>
