<?php
header('Content-Type: application/json');
$categories_file = '../../data/categories.json';
$categories = file_exists($categories_file) ? json_decode(file_get_contents($categories_file), true) : [];
echo json_encode($categories);
