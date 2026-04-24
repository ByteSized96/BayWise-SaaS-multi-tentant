<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/helpers.php';
require_once __DIR__ . '/../../../app/Core/auth.php';
require_once __DIR__ . '/../../../app/Core/csrf.php';
require_once __DIR__ . '/../../../app/Core/database.php';

require_admin();
verify_csrf();

$allowed = [
    'brand_name',
    'brand_tagline',
    'brand_intro',
    'hero_image',
    'primary_cta',
];

$garageId = garage_id();

$stmt = $pdo->prepare("
    INSERT INTO site_settings (garage_id, setting_key, setting_value)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
");

foreach ($allowed as $key) {
    $value = trim($_POST[$key] ?? '');
    $stmt->execute([$garageId, $key, $value]);
}

flash('success', 'Brand settings updated successfully.');

header('Location: ' . app_url('admin/settings.php'));
exit;
?>