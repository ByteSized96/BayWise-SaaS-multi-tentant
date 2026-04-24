<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/database.php';
require_once __DIR__ . '/../../app/Core/csrf.php';

require_admin();

$pageTitle = 'Booking Details';

$garageId = garage_id();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT 
        bookings.*,
        customers.name AS customer_name,
        customers.phone,
        customers.email,
        services.name AS service_name,
        services.description AS service_description,
        services.base_price,
        vehicles.make,
        vehicles.model,
        vehicles.year,
        vehicles.registration,
        vehicles.mileage,
        calendar_slots.slot_date,
        calendar_slots.start_time,
        calendar_slots.end_time
    FROM bookings
    JOIN customers ON customers.id = bookings.customer_id
    JOIN services ON services.id = bookings.service_id
    JOIN vehicles ON vehicles.id = bookings.vehicle_id
    JOIN calendar_slots ON calendar_slots.id = bookings.slot_id
    WHERE bookings.id = ?
    AND bookings.garage_id = ?
    LIMIT 1
");
$stmt->execute([$id, $garageId]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: ' . app_url('admin/bookings.php'));
    exit;
}

$updatesStmt = $pdo->prepare("
    SELECT booking_updates.* 
    FROM booking_updates
    JOIN bookings ON bookings.id = booking_updates.booking_id
    WHERE booking_updates.booking_id = ?
    AND bookings.garage_id = ?
    ORDER BY booking_updates.created_at DESC
");
$updatesStmt->execute([$id, $garageId]);
$updates = $updatesStmt->fetchAll();

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<div class="mb-4">
    <a href="<?= app_url('admin/bookings.php') ?>" class="text-sm font-black text-slate-600">← Back to bookings</a>
</div>

<section class="rounded-[2rem] bg-white p-5 shadow-soft">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-bay-muted">
                <?= h($booking['registration'] ?: 'No registration') ?>
            </p>
            <h2 class="mt-2 text-2xl font-black"><?= h($booking['service_name']) ?></h2>
            <p class="mt-1 text-sm text-bay-muted">
                <?= h($booking['customer_name']) ?> · <?= h($booking['make']) ?> <?= h($booking['model']) ?>
            </p>
        </div>

        <span class="w-fit rounded-full px-4 py-2 text-sm font-black <?= status_badge($booking['status']) ?>">
            <?= h($booking['status']) ?>
        </span>
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-xs font-bold text-bay-muted">Date</p>
            <p class="mt-1 font-black"><?= h(date('D d M Y', strtotime($booking['slot_date']))) ?></p>
        </div>

        <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-xs font-bold text-bay-muted">Time</p>
            <p class="mt-1 font-black"><?= h(substr($booking['start_time'], 0, 5)) ?></p>
        </div>

        <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-xs font-bold text-bay-muted">Estimate</p>
            <p class="mt-1 font-black"><?= money_gbp($booking['base_price']) ?></p>
        </div>

        <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-xs font-bold text-bay-muted">Mileage</p>
            <p class="mt-1 font-black"><?= $booking['mileage'] ? number_format((int)$booking['mileage']) . ' mi' : 'Unknown' ?></p>
        </div>
    </div>

    <?php if ($booking['notes']): ?>
        <div class="mt-5 rounded-2xl bg-slate-50 p-4">
            <p class="mb-1 text-sm font-black">Customer notes</p>
            <p class="text-sm leading-6 text-slate-600"><?= nl2br(h($booking['notes'])) ?></p>
        </div>
    <?php endif; ?>
</section>

<section class="mt-5 rounded-[2rem] bg-white p-5 shadow-soft">
    <h3 class="mb-4 text-lg font-black">Update booking status</h3>

    <form action="<?= app_url('actions/admin/update-booking-status.php') ?>" method="post" class="grid gap-3 sm:grid-cols-[1fr_auto]">
        <?= csrf_field() ?>
        <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">

        <select name="status" class="w-full rounded-2xl border border-bay-line px-4 py-3 font-bold outline-none focus:border-slate-900">
            <?php foreach (['Requested','Confirmed','Vehicle Received','Inspection','In Progress','Awaiting Parts','Ready for Collection','Completed','Cancelled'] as $status): ?>
                <option <?= $booking['status'] === $status ? 'selected' : '' ?>>
                    <?= h($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="rounded-2xl bg-slate-900 px-5 py-3 font-black text-white">
            Update
        </button>
    </form>
</section>

<section class="mt-5 rounded-[2rem] bg-white p-5 shadow-soft">
    <h3 class="mb-4 text-lg font-black">Add repair update</h3>

    <form action="<?= app_url('actions/admin/add-booking-update.php') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">

        <textarea name="update_text" required rows="4"
                  placeholder="Example: Vehicle inspection has started. We are checking the front brakes and suspension."
                  class="w-full rounded-2xl border border-bay-line px-4 py-3 outline-none focus:border-slate-900"></textarea>

        <label class="mt-3 flex items-center gap-2 text-sm font-bold text-slate-600">
            <input type="checkbox" name="visible_to_customer" value="1" checked>
            Visible to customer
        </label>

        <button class="mt-4 w-full rounded-2xl bg-slate-900 px-5 py-3 font-black text-white sm:w-auto">
            Save update
        </button>
    </form>
</section>

<section class="mt-5">
    <h3 class="mb-3 text-lg font-black">Timeline</h3>

    <div class="space-y-3">
        <?php foreach ($updates as $update): ?>
            <div class="rounded-[1.5rem] bg-white p-5 shadow-soft">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <span class="rounded-full px-3 py-1 text-xs font-black <?= $update['visible_to_customer'] ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-700' ?>">
                        <?= $update['visible_to_customer'] ? 'Customer visible' : 'Internal only' ?>
                    </span>
                    <span class="text-xs font-bold text-slate-400">
                        <?= h(date('d M Y, H:i', strtotime($update['created_at']))) ?>
                    </span>
                </div>

                <p class="text-sm leading-6 text-slate-700"><?= nl2br(h($update['update_text'])) ?></p>
            </div>
        <?php endforeach; ?>

        <?php if (!$updates): ?>
            <div class="rounded-[1.5rem] bg-white p-6 text-center shadow-soft">
                <p class="font-bold">No updates yet</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>