<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/helpers.php';
require_once __DIR__ . '/../../../app/Core/auth.php';
require_once __DIR__ . '/../../../app/Core/csrf.php';
require_once __DIR__ . '/../../../app/Core/database.php';

require_admin();
verify_csrf();

$garageId = garage_id();

$bookingId = (int)($_POST['booking_id'] ?? 0);
$status = $_POST['status'] ?? 'Requested';

$allowed = [
    'Requested',
    'Confirmed',
    'Vehicle Received',
    'Inspection',
    'In Progress',
    'Awaiting Parts',
    'Ready for Collection',
    'Completed',
    'Cancelled',
];

if ($bookingId > 0 && in_array($status, $allowed, true)) {
    $check = $pdo->prepare("
        SELECT id
        FROM bookings
        WHERE id = ?
        AND garage_id = ?
        LIMIT 1
    ");
    $check->execute([$bookingId, $garageId]);

    if ($check->fetch()) {
        $stmt = $pdo->prepare("
            UPDATE bookings
            SET status = ?
            WHERE id = ?
            AND garage_id = ?
        ");
        $stmt->execute([$status, $bookingId, $garageId]);

        $message = "Booking status updated to: {$status}.";

        $update = $pdo->prepare("
            INSERT INTO booking_updates (booking_id, update_text, visible_to_customer)
            VALUES (?, ?, 1)
        ");
        $update->execute([$bookingId, $message]);

        flash('success', 'Booking status updated.');
    } else {
        flash('error', 'Booking could not be found for this garage.');
    }
}

header('Location: ' . app_url('admin/booking-view.php?id=' . $bookingId));
exit;
?>