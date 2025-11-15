<?php
// ====================== CONFIGURACIÓN GENERAL ======================

// Clave de seguridad del sitio
define('SITE_KEY', '5^gW[D6$jfn3aONvP>Dva8');

// Nombre único para la sesión
define('SESSION_NAME', 'sitioweb_configmaster3000');

// Ruta del archivo de usuarios JSON
define('USERS_FILE', __DIR__ . '/data/usuarios.json');

// Configuración general del sitio
$site_config = [
    'site_name' => 'CorpSRTony',
    'site_description' => 'Soluciones innovadoras para impulsar el éxito de su negocio',
    'logo_url' => 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgbM3csSVIIikdlWF-H5uFVPOM0OaBHs4juvJmJwv3VEcRsDws6CvQryCGx4NMeQOxcmPNnYsIl2FQ85QB-FcuaUoZHyxu0-ubNTn8QtYhkGcS6ioDcXv7dxCRlDNK_QX03dBDQ_Oeq3kNuk6jVmuOsaDT1V7tmJAg9NW36YPPmXqgKSgZyRfwYtJnCA-k/s16000/Corpsrtony.png',
    'contact_whatsapp' => '573205680134',
    'facebook_url' => 'https://facebook.com/corpsrtony',
    'youtube_url' => 'https://www.youtube.com/@corpsrtony'
];

// ====================== FUNCIONES DE SESIÓN Y USUARIO ======================

// Verifica si la sesión está activa
function check_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
    return isset($_SESSION['user_logged']) && $_SESSION['user_logged'] === true;
}

// Inicia sesión de usuario y guarda datos en la sesión
function login_user($username) {
    session_name(SESSION_NAME);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $users = load_users();
    $user_data = $users[$username] ?? [];

    session_regenerate_id(true);
    $_SESSION['user_logged'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $user_data['name'] ?? 'Usuario';
    $_SESSION['login_time'] = time();
}

// Cierra la sesión del usuario
function logout_user() {
    session_name(SESSION_NAME);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
}

// ====================== FUNCIONES DE USUARIOS ======================

// Carga usuarios desde archivo JSON
function load_users() {
    return file_exists(USERS_FILE)
        ? json_decode(file_get_contents(USERS_FILE), true)
        : [];
}

// Devuelve el rol del usuario actual o de uno específico
function get_user_role($username = null) {
    if ($username === null && isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    }

    $users = load_users();
    return $users[$username]['role'] ?? null;
}

// Verifica si el usuario es super_admin
function is_super_admin() {
    return get_user_role() === 'super_admin';
}

// Verifica si el usuario es admin o super_admin
function is_admin() {
    $role = get_user_role();
    return in_array($role, ['admin', 'super_admin']);
}

// ====================== FUNCIONES DE CONTRASEÑA ======================

// Verifica contraseña usando hash
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Genera un hash seguro para contraseña
function generate_password_hash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
?>
