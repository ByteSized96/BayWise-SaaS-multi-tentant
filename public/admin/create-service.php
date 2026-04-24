<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/helpers.php';
require_once __DIR__ . '/../../../app/Core/auth.php';
require_once __DIR__ . '/../../../app/Core/csrf.php';
require_once __DIR__ . '/../../../app/Core/database.php';

require_admin();
verify_csrf();

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration = (int)($_POST['duration_minutes'] ?? 60);
$price = (float)($_POST['base_price'] ?? 0);

$garageId = garage_id();

if ($name !== '') {
    $stmt = $pdo->prepare("
        INSERT INTO services (garage_id, name, description, duration_minutes, base_price)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $garageId,
        $name,
        $description,
        $duration ?: 60,
        $price
    ]);
}

header('Location: ' . app_url('admin/services.php'));
exit;
?>