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
$updateText = trim($_POST['update_text'] ?? '');
$visible = isset($_POST['visible_to_customer']) ? 1 : 0;

if ($bookingId > 0 && $updateText !== '') {
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
            INSERT INTO booking_updates (booking_id, update_text, visible_to_customer)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$bookingId, $updateText, $visible]);

        flash('success', 'Repair update added.');
    } else {
        flash('error', 'Booking could not be found for this garage.');
    }
}

header('Location: ' . app_url('admin/booking-view.php?id=' . $bookingId));
exit;
?>