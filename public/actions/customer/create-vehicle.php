<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/helpers.php';
require_once __DIR__ . '/../../../app/Core/auth.php';
require_once __DIR__ . '/../../../app/Core/csrf.php';
require_once __DIR__ . '/../../../app/Core/database.php';

require_customer();
verify_csrf();

$garageId = garage_id();

$make = trim($_POST['make'] ?? '');
$model = trim($_POST['model'] ?? '');
$year = (int)($_POST['year'] ?? 0);
$registration = strtoupper(trim($_POST['registration'] ?? ''));
$mileage = (int)($_POST['mileage'] ?? 0);

if ($make === '' || $model === '') {
    flash('error', 'Please enter the vehicle make and model.');
    header('Location: ' . app_url('customer/vehicles.php'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT id 
    FROM customers 
    WHERE user_id = ?
    AND garage_id = ?
    LIMIT 1
");
$stmt->execute([user()['id'], $garageId]);
$customer = $stmt->fetch();

if (!$customer) {
    flash('error', 'Customer profile could not be found.');
    header('Location: ' . app_url('customer/dashboard.php'));
    exit;
}

$insert = $pdo->prepare("
    INSERT INTO vehicles (garage_id, customer_id, make, model, year, registration, mileage)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$insert->execute([
    $garageId,
    $customer['id'],
    $make,
    $model,
    $year ?: null,
    $registration,
    $mileage ?: null
]);

flash('success', 'Vehicle added successfully.');

header('Location: ' . app_url('customer/vehicles.php'));
exit;
?>