<?php
require_once '../config.php';
require_once '../functions.php';

if (!check_session()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Cargar configuración actual
$site_info = load_json_data('../data/site_info.json');
if (empty($site_info)) {
    $site_info = $site_config;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = sanitize_input($_POST['site_name'] ?? '');
    $site_description = sanitize_input($_POST['site_description'] ?? '');
    $logo_url = sanitize_input($_POST['logo_url'] ?? '');
    $contact_whatsapp = sanitize_input($_POST['contact_whatsapp'] ?? '');
    $facebook_url = sanitize_input($_POST['facebook_url'] ?? '');
    $youtube_url = sanitize_input($_POST['youtube_url'] ?? '');

    if (empty($site_name) || empty($site_description)) {
        $error = 'El nombre y descripción del sitio son obligatorios';
    } else {
        $new_config = [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'logo_url' => $logo_url,
            'contact_whatsapp' => $contact_whatsapp,
            'facebook_url' => $facebook_url,
            'youtube_url' => $youtube_url
        ];

        if (!is_dir('../data')) {
            mkdir('../data', 0755, true);
        }

        if (save_json_data('../data/site_info.json', $new_config)) {
            $site_info = $new_config;
            $message = 'Configuración guardada exitosamente';
        } else {
            $error = 'Error al guardar la configuración';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sitio - Panel Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="css/panel.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-cog"></i> Configuración del Sitio</h1>
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    </div>

    <div class="container">
        <div class="card">
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="site_name">Nombre del Sitio *</label>
                    <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_info['site_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="site_description">Descripción del Sitio *</label>
                    <textarea id="site_description" name="site_description" required><?php echo htmlspecialchars($site_info['site_description']); ?></textarea>
                    <div class="help-text">Esta descripción aparece en el encabezado principal del sitio</div>
                </div>

                <div class="form-group">
                    <label for="logo_url">URL del Logo</label>
                    <input type="url" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($site_info['logo_url']); ?>">
                    <div class="help-text">URL completa de la imagen del logo</div>
                </div>

                <div class="form-group">
                    <label for="contact_whatsapp">Número de WhatsApp</label>
                    <input type="text" id="contact_whatsapp" name="contact_whatsapp" value="<?php echo htmlspecialchars($site_info['contact_whatsapp']); ?>" placeholder="573205680134">
                    <div class="help-text">Número con código de país (sin +)</div>
                </div>

                <div class="form-group">
                    <label for="facebook_url">URL de Facebook</label>
                    <input type="url" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($site_info['facebook_url']); ?>">
                </div>

                <div class="form-group">
                    <label for="youtube_url">URL de YouTube</label>
                    <input type="url" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($site_info['youtube_url']); ?>">
                </div>

                <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar Configuración</button>
            </form>
        </div>
    </div>
</body>
</html>
