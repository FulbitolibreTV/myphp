<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if(!empty($input['bot_token']) && !empty($input['chat_name'])){
    $save = [
        'bot_token' => $input['bot_token'],
        'chat_name' => $input['chat_name']
    ];
    file_put_contents('../data/telegram_notifi.json', json_encode($save, JSON_PRETTY_PRINT));
    echo json_encode(['status'=>'Configuración guardada']);
} else {
    echo json_encode(['status'=>'Error: completa todos los campos']);
}
?>
