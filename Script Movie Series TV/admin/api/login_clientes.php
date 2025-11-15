<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Para debug - puedes comentar esta línea en producción
error_reporting(E_ALL);
ini_set('display_errors', 1);

$clients_file = '../../data/clientedata.json';

// Verificar que exista el JSON
if(!file_exists($clients_file)){
    echo json_encode(["status"=>"error","message"=>"Archivo de clientes no encontrado"]);
    exit;
}

$clients = json_decode(file_get_contents($clients_file), true);

if($clients === null) {
    echo json_encode(["status"=>"error","message"=>"Error al leer archivo de clientes"]);
    exit;
}

// Verificar si es una acción de bloqueo
$action = $_POST['action'] ?? '';

if($action === 'block_user') {
    $usuario = $_POST['usuario'] ?? '';
    
    // Log para debug
    error_log("Intento de bloqueo para usuario: " . $usuario);
    
    if(!$usuario) {
        echo json_encode(["status"=>"error","message"=>"Usuario no especificado"]);
        exit;
    }
    
    // Buscar y bloquear usuario
    $user_found = false;
    for($i = 0; $i < count($clients); $i++) {
        if($clients[$i]['usuario'] === $usuario) {
            $clients[$i]['estado'] = 'bloqueado';
            $user_found = true;
            error_log("Usuario encontrado y marcado como bloqueado: " . $usuario);
            break;
        }
    }
    
    if($user_found) {
        // Crear backup antes de guardar
        $backup_file = $clients_file . '.backup.' . date('Y-m-d-H-i-s');
        copy($clients_file, $backup_file);
        
        // Guardar cambios en el archivo JSON
        $json_data = json_encode($clients, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $save_result = file_put_contents($clients_file, $json_data, LOCK_EX);
        
        if($save_result !== false) {
            error_log("Usuario bloqueado exitosamente: " . $usuario);
            echo json_encode([
                "status"=>"ok",
                "message"=>"Usuario bloqueado correctamente",
                "usuario"=>$usuario
            ]);
        } else {
            error_log("Error al guardar archivo para usuario: " . $usuario);
            echo json_encode(["status"=>"error","message"=>"Error al guardar los cambios"]);
        }
    } else {
        error_log("Usuario no encontrado para bloqueo: " . $usuario);
        echo json_encode(["status"=>"error","message"=>"Usuario no encontrado"]);
    }
    exit;
}

// Leer POST para login normal
$usuario = $_POST['usuario'] ?? '';
$pin = $_POST['pin'] ?? '';

if(!$usuario || !$pin) {
    echo json_encode(["status"=>"error","message"=>"Usuario y PIN son requeridos"]);
    exit;
}

// Verificar que exista el usuario
$user_data = null;
foreach($clients as $c){
    if($c['usuario'] === $usuario){
        $user_data = $c;
        break;
    }
}

if(!$user_data){
    echo json_encode(["status"=>"error","message"=>"Usuario no encontrado"]);
    exit;
}

// Verificar estado del usuario
if($user_data['estado'] === 'bloqueado'){
    echo json_encode([
        "status"=>"error",
        "message"=>"Tu cuenta está bloqueada. Contacta soporte para reactivarla."
    ]);
    exit;
}

if($user_data['estado'] === 'desactivado' || $user_data['estado'] === 'inactivo'){
    echo json_encode([
        "status"=>"error",
        "message"=>"Tu cuenta está desactivada. Contacta soporte para más información."
    ]);
    exit;
}

if($user_data['estado'] !== 'activo'){
    echo json_encode([
        "status"=>"error",
        "message"=>"Tu cuenta está en estado '{$user_data['estado']}'. Contacta soporte."
    ]);
    exit;
}

// Verificar PIN
if($user_data['pin'] !== $pin){
    echo json_encode(["status"=>"error","message"=>"PIN incorrecto"]);
    exit;
}

// Calcular días restantes
$today = new DateTime();
$vencimiento = new DateTime($user_data['vencimiento']);

if($today > $vencimiento) {
    // Cuenta vencida
    echo json_encode([
        "status"=>"error",
        "message"=>"Tu cuenta ha vencido. Contacta soporte para renovar."
    ]);
    exit;
}

$interval = $today->diff($vencimiento);
$days_left = $interval->days;

// Retornar OK
echo json_encode([
    "status" => "ok",
    "usuario" => $usuario,
    "days_left" => $days_left,
    "message" => "Login exitoso"
]);
?>