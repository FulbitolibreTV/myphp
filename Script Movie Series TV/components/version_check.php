<?php
// version_check.php - flotante lado derecho con version default
$local_version_file = __DIR__ . '/../data/version.json';
$local_version_data = json_decode(@file_get_contents($local_version_file) ?: '{}', true);
$local_version = $local_version_data['version'] ?? '1.0.0.0'; // default siempre 1.0.0.0

$product_id = 1;
$update_api = "https://s.corpsrtony.com/api/check_latest.php?product_id=$product_id";
$remote_data = json_decode(@file_get_contents($update_api), true);
$latest_version = $remote_data['version'] ?? $local_version;

$update_available = version_compare($local_version, $latest_version, '<');

// Colores según estado
$color = $update_available ? '#c62828' : '#2e7d32'; // rojo o verde
$text = $update_available ? "🚀 $latest_version disponible" : "✅ $local_version";
$link = "https://s.corpsrtony.com/";
?>
<style>
.version-button {
  position: fixed;
  top: 10px;
  right: 10px;
  background: transparent;
  border: 2px solid;
  border-radius: 20px;
  padding: 6px 12px;
  font-size: 0.9rem;
  font-weight: bold;
  text-decoration: none;
  transition: 0.3s;
  z-index: 9999;
}
.version-button:hover {
  background: rgba(0,0,0,0.05);
}
</style>

<a href="<?= $link ?>" target="_blank"
   class="version-button"
   style="color: <?= $color ?>; border-color: <?= $color ?>;">
   <?= htmlspecialchars($text) ?>
</a>
