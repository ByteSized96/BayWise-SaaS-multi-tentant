<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/helpers.php';
require_once __DIR__ . '/../../../app/Core/auth.php';
require_once __DIR__ . '/../../../app/Core/csrf.php';
require_once __DIR__ . '/../../../app/Core/database.php';

require_admin();
verify_csrf();

$slotDate = $_POST['slot_date'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$capacity = (int)($_POST['capacity'] ?? 1);

$garageId = garage_id();

if ($slotDate !== '' && $startTime !== '' && $endTime !== '') {
    $stmt = $pdo->prepare("
        INSERT INTO calendar_slots (garage_id, slot_date, start_time, end_time, capacity)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $garageId,
        $slotDate,
        $startTime,
        $endTime,
        max(1, $capacity)
    ]);
}

header('Location: ' . app_url('admin/slots.php'));
exit;
?>