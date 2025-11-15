<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para leer el archivo JSON
function leerUsuarios() {
    $users_file = 'data/accesclient.json';
    if (file_exists($users_file)) {
        $content = file_get_contents($users_file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

// Función para guardar el archivo JSON
function guardarUsuarios($users) {
    $users_file = 'data/accesclient.json';
    
    // Crear directorio si no existe
    if (!file_exists('data')) {
        mkdir('data', 0755, true);
    }
    
    return file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Función para calcular días restantes
function calcularDiasRestantes($fechaCreacion, $diasTotales) {
    $fechaCreacion = new DateTime($fechaCreacion);
    $fechaActual = new DateTime();
    $diasTranscurridos = $fechaActual->diff($fechaCreacion)->days;
    $diasRestantes = $diasTotales - $diasTranscurridos;
    
    return max(0, $diasRestantes); // No puede ser negativo
}

// Función para registrar intentos fallidos
function registrarIntentoFallido($usuario) {
    if (!isset($_SESSION['intentos_fallidos'])) {
        $_SESSION['intentos_fallidos'] = [];
    }
    
    if (!isset($_SESSION['intentos_fallidos'][$usuario])) {
        $_SESSION['intentos_fallidos'][$usuario] = 0;
    }
    
    $_SESSION['intentos_fallidos'][$usuario]++;
    
    // Si supera 5 intentos, bloquear usuario
    if ($_SESSION['intentos_fallidos'][$usuario] >= 5) {
        $users = leerUsuarios();
        if (isset($users[$usuario])) {
            $users[$usuario]['estado'] = 'bloqueado';
            guardarUsuarios($users);
        }
    }
    
    return $_SESSION['intentos_fallidos'][$usuario];
}

// Función para obtener intentos fallidos
function obtenerIntentos($usuario) {
    return $_SESSION['intentos_fallidos'][$usuario] ?? 0;
}

// Verificar si ya está logueado
$ya_logueado = isset($_SESSION['cliente']) && !empty($_SESSION['cliente']);

if (!$ya_logueado) {
    $users = leerUsuarios();
    $mensaje = "";
    $logueado = false;
    $nombre_usuario = "";
    $dias_restantes = 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['pin'])) {
        $nombre_ingresado = trim($_POST['usuario']);
        $pin = trim($_POST['pin']);
        
        // Buscar usuario por nombre (insensible a mayús/minús)
        $usuario_encontrado = null;
        $datos_usuario = null;
        
        foreach ($users as $id => $data) {
            if (strtolower($data['nombre']) === strtolower($nombre_ingresado)) {
                $usuario_encontrado = $id;
                $datos_usuario = $data;
                break;
            }
        }

        if ($usuario_encontrado && $datos_usuario) {
            // Si el usuario está activo pero tenía intentos previos, limpiarlos
            if ($datos_usuario['estado'] === 'activado' && isset($_SESSION['intentos_fallidos'][$usuario_encontrado])) {
                unset($_SESSION['intentos_fallidos'][$usuario_encontrado]);
            }
            
            $intentos_actuales = obtenerIntentos($usuario_encontrado);
            
            // Verificar si está bloqueado
            if ($datos_usuario['estado'] === 'bloqueado') {
                $mensaje = 'Usuario bloqueado por seguridad. Contacte a <a href="soporte.php" style="color: #ff6b9d; text-decoration: underline; font-weight: bold;">soporte</a> para desbloquearlo.';
            } elseif ($intentos_actuales >= 5) {
                $mensaje = 'Usuario bloqueado por exceso de intentos fallidos. Contacte a <a href="soporte.php" style="color: #ff6b9d; text-decoration: underline; font-weight: bold;">soporte</a> para desbloquearlo.';
            } elseif ($datos_usuario['pin'] === $pin && $datos_usuario['estado'] === 'activado') {
                // Calcular días restantes
                $dias_restantes = calcularDiasRestantes($datos_usuario['creacion'], $datos_usuario['dias']);
                
                // Verificar si la cuenta ha expirado
                if ($dias_restantes <= 0) {
                    $mensaje = 'Su cuenta ha expirado. Contacte a <a href="soporte.php" style="color: #ff6b9d; text-decoration: underline; font-weight: bold;">soporte</a> para renovarla.';
                } else {
                    // Login exitoso  
                    $_SESSION['cliente'] = [
                        'usuario' => $usuario_encontrado,
                        'nombre' => $datos_usuario['nombre'],
                        'dias' => $datos_usuario['dias'],
                        'creacion' => $datos_usuario['creacion'],
                        'dias_restantes' => $dias_restantes
                    ];
                    
                    // Limpiar intentos fallidos
                    if (isset($_SESSION['intentos_fallidos'][$usuario_encontrado])) {
                        unset($_SESSION['intentos_fallidos'][$usuario_encontrado]);
                    }
                    
                    $logueado = true;
                    $nombre_usuario = $datos_usuario['nombre'];
                }
            } else {
                // PIN incorrecto
                $intentos = registrarIntentoFallido($usuario_encontrado);
                $restantes = 5 - $intentos;
                
                if ($restantes > 0) {
                    $mensaje = "PIN incorrecto. Te quedan $restantes intentos.";
                } else {
                    $mensaje = 'Usuario bloqueado por exceso de intentos fallidos. Contacte a <a href="soporte.php" style="color: #ff6b9d; text-decoration: underline; font-weight: bold;">soporte</a> para desbloquearlo.';
                }
            }
        } else {
            // Crear lista de nombres disponibles para el mensaje de error
            $nombres_disponibles = [];
            foreach ($users as $data) {
                $nombres_disponibles[] = $data['nombre'];
            }
            $mensaje = "Usuario no encontrado. Usuarios disponibles: " . implode(', ', $nombres_disponibles);
        }
    }

    // Solo mostrar el formulario si no está logueado
    if (!$logueado) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Verificación de Acceso</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * {
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Orbitron', sans-serif;
                    background: radial-gradient(circle at top left, #0f0f1a, #000);
                    color: white;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                    padding: 20px;
                    overflow: hidden;
                }
                
                .login-container {
                    background: linear-gradient(135deg, #1a1a3f, #000);
                    border: 2px solid #0ff;
                    box-shadow: 0 0 30px #0ff;
                    padding: 2.5rem;
                    border-radius: 20px;
                    max-width: 450px;
                    width: 100%;
                    animation: slideIn 0.8s ease-out;
                }
                
                @keyframes slideIn {
                    from {
                        opacity: 0;
                        transform: translateY(-30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .logo {
                    text-align: center;
                    margin-bottom: 2rem;
                }
                
                .logo h1 {
                    margin: 0;
                    font-size: 2.2rem;
                    color: #0ff;
                    text-shadow: 0 0 15px #0ff, 0 0 25px #0ff;
                    font-weight: 800;
                }
                
                .form-group {
                    margin-bottom: 1.5rem;
                }
                
                .form-group label {
                    display: block;
                    margin-bottom: 0.5rem;
                    font-weight: 600;
                    color: #0ff;
                    text-shadow: 0 0 5px #0ff;
                }
                
                .form-group input[type="text"],
                .form-group input[type="password"] {
                    width: 100%;
                    padding: 15px;
                    border: 2px solid #0ff40;
                    border-radius: 10px;
                    background: #111;
                    color: #0ff;
                    font-size: 1rem;
                    box-sizing: border-box;
                    transition: all 0.3s ease;
                    font-family: 'Orbitron', sans-serif;
                }
                
                .form-group input[type="text"]:focus,
                .form-group input[type="password"]:focus {
                    outline: none;
                    border-color: #0ff;
                    box-shadow: 0 0 15px #0ff;
                    transform: translateY(-2px);
                }
                
                .form-group input::placeholder {
                    color: rgba(0, 255, 255, 0.6);
                }
                
                .checkbox-group {
                    display: flex;
                    align-items: center;
                    margin-bottom: 1.5rem;
                    color: #0ff;
                }
                
                .checkbox-group input[type="checkbox"] {
                    margin-right: 10px;
                    transform: scale(1.2);
                }
                
                .btn-submit {
                    width: 100%;
                    padding: 15px;
                    border: none;
                    border-radius: 10px;
                    background: linear-gradient(135deg, #0ff, #00f);
                    color: white;
                    font-size: 1.1rem;
                    font-weight: bold;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    font-family: 'Orbitron', sans-serif;
                }
                
                .btn-submit:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 25px rgba(0, 255, 255, 0.4);
                    background: linear-gradient(135deg, #ff0, #f0f);
                }
                
                .btn-submit:active {
                    transform: translateY(-1px);
                }
                
                .mensaje {
                    background: rgba(255, 0, 100, 0.2);
                    color: #ff6b9d;
                    padding: 12px;
                    border-radius: 8px;
                    margin-top: 15px;
                    text-align: center;
                    border: 1px solid rgba(255, 0, 100, 0.3);
                    animation: shake 0.5s ease-in-out;
                    font-weight: 600;
                }
                
                .mensaje a {
                    color: #ff6b9d;
                    text-decoration: underline;
                    font-weight: bold;
                }
                
                .mensaje a:hover {
                    color: #fff;
                    text-shadow: 0 0 5px #ff6b9d;
                }
                
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }
                
                .success {
                    background: rgba(76, 175, 80, 0.2);
                    color: #c8e6c9;
                    padding: 15px;
                    border-radius: 8px;
                    margin-top: 15px;
                    text-align: center;
                    border: 1px solid rgba(76, 175, 80, 0.3);
                    animation: fadeIn 0.5s ease-in;
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                .btn-continue {
                    width: 100%;
                    padding: 15px;
                    border: none;
                    border-radius: 10px;
                    background: linear-gradient(45deg, #2196F3, #1976D2);
                    color: white;
                    font-size: 1.1rem;
                    font-weight: bold;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    margin-top: 15px;
                }
                
                .btn-continue:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="logo">
                    <h1>🔐 Acceso Seguro</h1>
                </div>
                
                <form method="post" onsubmit="guardarUsuario()">
                    <div class="form-group">
                        <label for="usuario">Nombre de Usuario</label>
                        <input type="text" name="usuario" id="usuario" placeholder="Ej: Pepe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pin">PIN de Acceso</label>
                        <input type="password" name="pin" id="pin" placeholder="Ingrese su PIN" required>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="recordarme">
                        <label for="recordarme">Recordar usuario</label>
                    </div>
                    
                    <button type="submit" class="btn-submit">Verificar Acceso</button>
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="mensaje"><?= $mensaje ?></div>
                    <?php endif; ?>
                </form>
            </div>
            
            <script>
                const usuarioInput = document.getElementById("usuario");
                const recordarme = document.getElementById("recordarme");
                
                // Cargar usuario guardado
                window.addEventListener("DOMContentLoaded", () => {
                    const guardado = localStorage.getItem("usuario_guardado");
                    if (guardado) {
                        usuarioInput.value = guardado;
                        recordarme.checked = true;
                    }
                });
                
                // Guardar usuario si está marcado
                function guardarUsuario() {
                    const user = usuarioInput.value.trim();
                    if (recordarme.checked && user) {
                        localStorage.setItem("usuario_guardado", user);
                    } else {
                        localStorage.removeItem("usuario_guardado");
                    }
                }
                
                // Efecto de focus en inputs
                document.querySelectorAll('input[type="text"], input[type="password"]').forEach(input => {
                    input.addEventListener('focus', function() {
                        this.parentElement.style.transform = 'scale(1.02)';
                    });
                    
                    input.addEventListener('blur', function() {
                        this.parentElement.style.transform = 'scale(1)';
                    });
                });
            </script>
        </body>
        </html>
        <?php
        exit; // Importante: detener la ejecución aquí si no está logueado
    } else {
        // Si el login fue exitoso, mostrar mensaje de bienvenida temporal con días restantes
        ?>
        <div id="welcome-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); display: flex; align-items: center; justify-content: center; z-index: 99999; font-family: 'Orbitron', sans-serif;">
            <div style="background: linear-gradient(135deg, #1a1a3f, #000); padding: 3rem; border-radius: 20px; text-align: center; max-width: 500px; border: 2px solid #0ff; box-shadow: 0 0 30px #0ff;">
                <h2 style="color: #0ff; margin-bottom: 1rem; font-size: 2rem; text-shadow: 0 0 10px #0ff;">✅ Acceso Concedido</h2>
                <p style="color: #eee; margin-bottom: 1.5rem; font-size: 1.2rem;">Bienvenido/a, <?= htmlspecialchars($nombre_usuario) ?></p>
                
                <div style="background: rgba(0,255,255,0.1); padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                    <p style="color: #0ff; margin: 0; font-size: 0.9rem;">🎬 Disfruta de todo nuestro contenido</p>
                </div>
                
                <div style="background: rgba(255,193,7,0.2); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid rgba(255,193,7,0.3);">
                    <p style="color: #ffc107; margin: 0; font-size: 1rem; font-weight: bold;">
                        ⏰ Te quedan <?= $dias_restantes ?> días de acceso
                    </p>
                    <?php if ($dias_restantes <= 7): ?>
                        <p style="color: #ff6b9d; margin: 0.5rem 0 0 0; font-size: 0.85rem;">
                            ⚠️ Tu cuenta expira pronto. Contacta a soporte para renovar.
                        </p>
                    <?php endif; ?>
                </div>
                
                <button onclick="document.getElementById('welcome-overlay').style.display='none'" style="background: linear-gradient(135deg, #0ff, #00f); color: white; padding: 12px 24px; border: none; border-radius: 10px; cursor: pointer; font-size: 1.1rem; font-weight: bold; transition: all 0.3s ease; text-transform: uppercase;">
                    Continuar al Sitio
                </button>
            </div>
        </div>
        <script>
            // Auto-ocultar después de 6 segundos (más tiempo para leer los días)
            setTimeout(() => {
                const overlay = document.getElementById('welcome-overlay');
                if (overlay) {
                    overlay.style.opacity = '0';
                    overlay.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => overlay.style.display = 'none', 500);
                }
            }, 6000);
        </script>
        <?php
    }
}
?>