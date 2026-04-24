<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/database.php';

require_customer();

$pageTitle = 'My Bookings';

$garageId = garage_id();

$stmt = $pdo->prepare("
    SELECT id 
    FROM customers 
    WHERE user_id = ?
    AND garage_id = ?
    LIMIT 1
");
$stmt->execute([user()['id'], $garageId]);
$customer = $stmt->fetch();

$bookings = [];

if ($customer) {
    $stmt = $pdo->prepare("
        SELECT 
            bookings.*,
            services.name AS service_name,
            services.base_price,
            vehicles.make,
            vehicles.model,
            vehicles.registration,
            calendar_slots.slot_date,
            calendar_slots.start_time,
            calendar_slots.end_time
        FROM bookings
        JOIN services ON services.id = bookings.service_id
        JOIN vehicles ON vehicles.id = bookings.vehicle_id
        JOIN calendar_slots ON calendar_slots.id = bookings.slot_id
        WHERE bookings.customer_id = ?
        AND bookings.garage_id = ?
        ORDER BY bookings.created_at DESC
    ");
    $stmt->execute([$customer['id'], $garageId]);
    $bookings = $stmt->fetchAll();
}

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>

    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold text-white/80">Repair tracking</p>
            <h2 class="mt-2 text-4xl font-black tracking-tight">My bookings</h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                Track service requests, repair stages and garage updates.
            </p>
        </div>

        <a href="<?= app_url('customer/book-service.php') ?>" class="rounded-2xl bg-white px-5 py-3 text-center text-sm font-black text-bay-ink shadow-soft">
            Book service
        </a>
    </div>
</section>

<section class="mt-5 space-y-3">
    <?php foreach ($bookings as $booking): ?>
        <a href="<?= app_url('customer/booking-view.php?id=' . (int)$booking['id']) ?>"
           class="block rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur transition hover:-translate-y-0.5">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-black"><?= h($booking['service_name']) ?></h3>

                    <p class="mt-1 text-sm text-bay-muted">
                        <?= h($booking['make']) ?> <?= h($booking['model']) ?>
                        <?= $booking['registration'] ? ' · ' . h($booking['registration']) : '' ?>
                    </p>

                    <p class="mt-1 text-sm font-bold text-slate-500">
                        <?= h(date('D d M Y', strtotime($booking['slot_date']))) ?>
                        at <?= h(substr($booking['start_time'], 0, 5)) ?>
                    </p>
                </div>

                <span class="rounded-full px-3 py-1 text-xs font-black <?= status_badge($booking['status']) ?>">
                    <?= h($booking['status']) ?>
                </span>
            </div>

            <div class="mt-4 flex items-center justify-between rounded-2xl bg-bay-blueSoft p-3">
                <span class="text-sm font-bold text-bay-muted">Estimate from</span>
                <span class="text-sm font-black"><?= money_gbp($booking['base_price']) ?></span>
            </div>
        </a>
    <?php endforeach; ?>

    <?php if (!$bookings): ?>
        <div class="rounded-[2rem] bg-white/80 p-6 text-center shadow-soft backdrop-blur">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-bay-blueSoft text-2xl">📅</div>
            <h3 class="font-black">No bookings yet</h3>
            <p class="mt-2 text-sm text-bay-muted">Book your first service to start tracking progress.</p>

            <a href="<?= app_url('customer/book-service.php') ?>" class="mt-5 inline-flex rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
                Book service
            </a>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>