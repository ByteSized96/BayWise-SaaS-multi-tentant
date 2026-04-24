<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/database.php';

require_admin();

$pageTitle = 'Bookings';

$garageId = garage_id();

$status = $_GET['status'] ?? '';
$params = [$garageId];

$sql = "
    SELECT 
        bookings.*,
        customers.name AS customer_name,
        customers.phone,
        services.name AS service_name,
        services.base_price,
        vehicles.make,
        vehicles.model,
        vehicles.registration,
        calendar_slots.slot_date,
        calendar_slots.start_time
    FROM bookings
    JOIN customers ON customers.id = bookings.customer_id
    JOIN services ON services.id = bookings.service_id
    JOIN vehicles ON vehicles.id = bookings.vehicle_id
    JOIN calendar_slots ON calendar_slots.id = bookings.slot_id
    WHERE bookings.garage_id = ?
";

if ($status !== '') {
    $sql .= " AND bookings.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY calendar_slots.slot_date ASC, calendar_slots.start_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>

    <div class="relative">
        <p class="text-sm font-bold text-white/80">Garage admin</p>
        <h2 class="mt-2 text-4xl font-black tracking-tight">Bookings</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
            Review customer bookings, filter by status and open each booking to update repair progress.
        </p>
    </div>
</section>

<div class="mt-5 flex gap-2 overflow-x-auto pb-1">
    <?php
    $filters = ['', 'Requested', 'Confirmed', 'Vehicle Received', 'Inspection', 'In Progress', 'Awaiting Parts', 'Ready for Collection', 'Completed', 'Cancelled'];
    foreach ($filters as $filter):
        $active = $status === $filter;
        $label = $filter ?: 'All';
    ?>
        <a href="<?= app_url('admin/bookings.php' . ($filter ? '?status=' . urlencode($filter) : '')) ?>"
           class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-black <?= $active ? 'bg-bay-primary text-white' : 'bg-white/80 text-bay-muted shadow-sm backdrop-blur' ?>">
            <?= h($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<section class="mt-4 space-y-3">
    <?php foreach ($bookings as $booking): ?>
        <a href="<?= app_url('admin/booking-view.php?id=' . (int)$booking['id']) ?>"
           class="block rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur transition hover:-translate-y-0.5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-black"><?= h($booking['service_name']) ?></h3>
                    <p class="mt-1 text-sm text-bay-muted">
                        <?= h($booking['customer_name']) ?> · <?= h($booking['make']) ?> <?= h($booking['model']) ?>
                    </p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">
                        <?= h($booking['registration'] ?: 'No reg') ?>
                    </p>
                </div>

                <span class="rounded-full px-3 py-1 text-xs font-black <?= status_badge($booking['status']) ?>">
                    <?= h($booking['status']) ?>
                </span>
            </div>

            <div class="mt-4 flex items-center justify-between rounded-2xl bg-bay-blueSoft p-3">
                <span class="text-sm font-bold text-bay-muted">
                    <?= h(date('D d M', strtotime($booking['slot_date']))) ?> · <?= h(substr($booking['start_time'], 0, 5)) ?>
                </span>
                <span class="text-sm font-black"><?= money_gbp($booking['base_price']) ?></span>
            </div>
        </a>
    <?php endforeach; ?>

    <?php if (!$bookings): ?>
        <div class="rounded-[2rem] bg-white/80 p-6 text-center shadow-soft backdrop-blur">
            <p class="font-black">No bookings found</p>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>