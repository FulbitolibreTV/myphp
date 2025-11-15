<?php
require_once 'config.php';
if (!check_session()) { 
    header('HTTP/1.1 403 Forbidden'); 
    exit('Acceso denegado'); 
}

// Archivos
$reportes_file = '../data/reportes.json';
$telegram_file = '../data/telegram_notifi.json';

// Leer reportes
$reportes = file_exists($reportes_file) ? json_decode(file_get_contents($reportes_file), true) : [];
if(empty($reportes)){
    exit('No hay reportes nuevos.');
}

// Leer configuración de Telegram
if(!file_exists($telegram_file)){
    exit('No hay configuración de Telegram.');
}

$telegram = json_decode(file_get_contents($telegram_file), true);
$botToken = $telegram['bot_token'] ?? '';
$chatId = $telegram['chat_name'] ?? '';

if(empty($botToken) || empty($chatId)){
    exit('Token o canal de Telegram no configurado.');
}

// Función para enviar mensaje a Telegram
function sendTelegramMessage($botToken, $chatId, $message){
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

// Enviar cada reporte al Telegram
foreach($reportes as $r){
    $msg = "<b>Nuevo reporte</b>\n";
    $msg .= !empty($r['tipo']) ? "Tipo: {$r['tipo']}\n" : '';
    $msg .= !empty($r['pelicula']) ? "Película: {$r['pelicula']}\n" : '';
    $msg .= !empty($r['nombrePublicidad']) ? "Nombre: {$r['nombrePublicidad']}\n" : '';
    $msg .= !empty($r['contactoPublicidad']) ? "Contacto: {$r['contactoPublicidad']}\n" : '';
    $msg .= !empty($r['mensaje']) ? "Mensaje: {$r['mensaje']}\n" : '';
    $msg .= !empty($r['fecha']) ? "Fecha: {$r['fecha']}" : '';

    sendTelegramMessage($botToken, $chatId, $msg);
}

// Opcional: devolver un mensaje de éxito
echo json_encode(['status'=>'Mensajes enviados a Telegram.']);
