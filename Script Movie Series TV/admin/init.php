<?php
require_once '../config.php';
require_once '../functions.php';

// Crear carpetas necesarias
$directories = [
    '../data',
    '../uploads',
    '../uploads/images',
    '../uploads/videos'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Carpeta creada: $dir<br>";
    }
}

// Crear archivos JSON iniciales si no existen
$json_files = [
    '../data/site_info.json' => $site_config,
    '../data/services.json' => [
        [
            "id" => "service_1",
            "title" => "Diseño Gráfico y Branding",
            "icon" => "fas fa-palette",
            "features" => [
                "Desarrollo de Identidad Corporativa (Logos, Manuales de Marca)",
                "Producción de Material Audiovisual de Alta Calidad"
            ],
            "link" => "catalogo-diseno.php",
            "link_text" => "Explorar Diseño"
        ],
        [
            "id" => "service_2",
            "title" => "Desarrollo de Aplicaciones Móviles",
            "icon" => "fas fa-mobile-alt",
            "features" => [
                "Creación de Aplicaciones Nativas y Híbridas a Medida",
                "Estrategias de Publicación y Optimización en Tiendas Digitales"
            ],
            "link" => "catalogo-apps.php",
            "link_text" => "Ver Proyectos Apps"
        ]
    ],
    '../data/projects.json' => [
        [
            "id" => "project_1",
            "title" => "Portal de Aplicaciones Corporativas",
            "icon" => "fas fa-sitemap",
            "url" => "https://corpsrtonyoficial.blogspot.com"
        ],
        [
            "id" => "project_2",
            "title" => "Plataforma Gastronómica \"Bambi Pollo\"",
            "icon" => "fas fa-drumstick-bite",
            "url" => "https://bambipollo.blogspot.com"
        ]
    ],
    '../data/design_catalog.json' => [],
    '../data/apps_catalog.json' => []
];

foreach ($json_files as $file => $data) {
    if (!file_exists($file)) {
        save_json_data($file, $data);
        echo "Archivo creado: $file<br>";
    }
}

echo "<h3>Inicialización completada!</h3>";
echo "<p><a href='login.php'>Ir al login administrativo</a></p>";
?>
