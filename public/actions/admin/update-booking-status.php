<?php
declare(strict_types=1);
// Turn OFF display (never show users errors)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Turn ON logging
ini_set('log_errors', '1');

// Set log file path
ini_set('error_log', __DIR__ . '/../../storage/logs/app.log');

// Report everything
error_reporting(E_ALL);
require_once __DIR__ . '/../../../app/Core/helpers.php';
require_once __DIR__ . '/../../../app/Core/auth.php';
require_once __DIR__ . '/../../../app/Core/csrf.php';
require_once __DIR__ . '/../../../app/Core/database.php';

require_admin();
verify_csrf();

$bookingId = (int)($_POST['booking_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$note = trim($_POST['note'] ?? '');

$allowedStatuses = [
    'Requested',
    'Confirmed',
    'Vehicle Received',
    'Inspection',
    'In Progress',
    'Awaiting Parts',
    'Ready for Collection',
    'Completed',
    'Cancelled'
];

if ($bookingId <= 0 || !in_array($status, $allowedStatuses, true)) {
    header('Location: ' . app_url('admin/bookings.php'));
    exit;
}

// Update booking status
$update = $pdo->prepare("
    UPDATE bookings 
    SET status = ?, updated_at = NOW()
    WHERE id = ?
    LIMIT 1
");
$update->execute([$status, $bookingId]);

// Add timeline update (visible to customer)
if ($note !== '') {
    $log = $pdo->prepare("
        INSERT INTO booking_updates (booking_id, update_text, visible_to_customer)
        VALUES (?, ?, 1)
    ");
    $log->execute([$bookingId, $note]);
}

// Always add automatic system update
$systemLog = $pdo->prepare("
    INSERT INTO booking_updates (booking_id, update_text, visible_to_customer)
    VALUES (?, ?, 1)
");
$systemLog->execute([
    $bookingId,
    'Status updated to: ' . $status
]);

header('Location: ' . app_url('admin/booking-view.php?id=' . $bookingId));
exit;
?>