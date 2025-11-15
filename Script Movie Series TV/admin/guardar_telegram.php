<?php
header('Content-Type: application/json');

$data_file = '../data/telegram_notifi.json';
$input = json_decode(file_get_contents('php://input'), true);

$botToken = trim($input['bot_token'] ?? '');
$chatId   = trim($input['chat_id'] ?? '');

if(!$botToken || !$chatId){
    echo json_encode(['success'=>false, 'error'=>'Campos vacíos']);
    exit;
}

file_put_contents($data_file, json_encode([
    'bot_token' => $botToken,
    'chat_id' => $chatId
], JSON_PRETTY_PRINT));

echo json_encode(['success'=>true]);
