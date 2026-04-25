<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/helpers.php';
require_once __DIR__ . '/../../../app/Core/auth.php';
require_once __DIR__ . '/../../../app/Core/csrf.php';
require_once __DIR__ . '/../../../app/Core/database.php';

require_customer();
verify_csrf();

$garageId = garage_id();

$vehicleId = (int)($_POST['vehicle_id'] ?? 0);
$serviceId = (int)($_POST['service_id'] ?? 0);
$slotId = (int)($_POST['slot_id'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

$stmt = $pdo->prepare("
    SELECT id 
    FROM customers 
    WHERE user_id = ? 
    AND garage_id = ?
    LIMIT 1
");
$stmt->execute([user()['id'], $garageId]);
$customer = $stmt->fetch();

if (!$customer || $vehicleId <= 0 || $serviceId <= 0 || $slotId <= 0) {
    flash('error', 'Please choose a vehicle, service and available slot.');
    header('Location: ' . app_url('customer/book-service.php'));
    exit;
}

$checkVehicle = $pdo->prepare("
    SELECT id 
    FROM vehicles
    WHERE id = ? 
    AND customer_id = ?
    AND garage_id = ?
    LIMIT 1
");
$checkVehicle->execute([$vehicleId, $customer['id'], $garageId]);

if (!$checkVehicle->fetch()) {
    flash('error', 'That vehicle could not be found on your account.');
    header('Location: ' . app_url('customer/book-service.php'));
    exit;
}

$checkService = $pdo->prepare("
    SELECT id 
    FROM services
    WHERE id = ?
    AND garage_id = ?
    AND is_active = 1
    LIMIT 1
");
$checkService->execute([$serviceId, $garageId]);

if (!$checkService->fetch()) {
    flash('error', 'That service is no longer available.');
    header('Location: ' . app_url('customer/book-service.php'));
    exit;
}

$slotCheck = $pdo->prepare("
    SELECT capacity 
    FROM calendar_slots 
    WHERE id = ? 
    AND garage_id = ?
    AND is_active = 1
    LIMIT 1
");
$slotCheck->execute([$slotId, $garageId]);
$slot = $slotCheck->fetch();

if (!$slot) {
    flash('error', 'That slot is no longer available.');
    header('Location: ' . app_url('customer/book-service.php'));
    exit;
}

$bookingCount = $pdo->prepare("
    SELECT COUNT(*) 
    FROM bookings 
    WHERE slot_id = ? 
    AND garage_id = ?
    AND status != 'Cancelled'
");
$bookingCount->execute([$slotId, $garageId]);
$currentBookings = (int)$bookingCount->fetchColumn();

if ($currentBookings >= (int)$slot['capacity']) {
    flash('error', 'That slot is fully booked. Please choose another time.');
    header('Location: ' . app_url('customer/book-service.php'));
    exit;
}

$insert = $pdo->prepare("
    INSERT INTO bookings (garage_id, customer_id, vehicle_id, service_id, slot_id, notes)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insert->execute([
    $garageId,
    $customer['id'],
    $vehicleId,
    $serviceId,
    $slotId,
    $notes
]);

$bookingId = (int)$pdo->lastInsertId();

$update = $pdo->prepare("
    INSERT INTO booking_updates (booking_id, update_text, visible_to_customer)
    VALUES (?, ?, 1)
");
$update->execute([
    $bookingId,
    'Booking request received. The garage will review and confirm your appointment.'
]);

flash('success', 'Booking request created. You can now track progress here.');

header('Location: ' . app_url('customer/booking-view.php?id=' . $bookingId));
exit;
