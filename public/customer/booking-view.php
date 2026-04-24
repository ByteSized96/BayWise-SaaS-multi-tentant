<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/database.php';

require_customer();

$pageTitle = 'Booking Progress';

$garageId = garage_id();

$id = (int)($_GET['id'] ?? 0);

$customerStmt = $pdo->prepare("
    SELECT id 
    FROM customers 
    WHERE user_id = ?
    AND garage_id = ?
    LIMIT 1
");
$customerStmt->execute([user()['id'], $garageId]);
$customer = $customerStmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        bookings.*,
        services.name AS service_name,
        services.description AS service_description,
        services.base_price,
        vehicles.make,
        vehicles.model,
        vehicles.registration,
        vehicles.mileage,
        calendar_slots.slot_date,
        calendar_slots.start_time,
        calendar_slots.end_time
    FROM bookings
    JOIN services ON services.id = bookings.service_id
    JOIN vehicles ON vehicles.id = bookings.vehicle_id
    JOIN calendar_slots ON calendar_slots.id = bookings.slot_id
    WHERE bookings.id = ?
    AND bookings.customer_id = ?
    AND bookings.garage_id = ?
    LIMIT 1
");
$stmt->execute([$id, $customer['id'] ?? 0, $garageId]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: ' . app_url('customer/bookings.php'));
    exit;
}

$updatesStmt = $pdo->prepare("
    SELECT booking_updates.* 
    FROM booking_updates
    JOIN bookings ON bookings.id = booking_updates.booking_id
    WHERE booking_updates.booking_id = ?
    AND bookings.garage_id = ?
    AND booking_updates.visible_to_customer = 1
    ORDER BY booking_updates.created_at DESC
");
$updatesStmt->execute([$id, $garageId]);
$updates = $updatesStmt->fetchAll();

$steps = [
    'Requested',
    'Confirmed',
    'Vehicle Received',
    'Inspection',
    'In Progress',
    'Awaiting Parts',
    'Ready for Collection',
    'Completed',
];

$currentIndex = array_search($booking['status'], $steps, true);
$currentIndex = $currentIndex === false ? -1 : $currentIndex;

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<div class="mb-4">
    <a href="<?= app_url('customer/bookings.php') ?>" class="text-sm font-black text-bay-primary">← Back to bookings</a>
</div>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>

    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-white/70">
                <?= h($booking['registration'] ?: 'No registration') ?>
            </p>

            <h2 class="mt-2 text-4xl font-black tracking-tight"><?= h($booking['service_name']) ?></h2>

            <p class="mt-2 text-sm text-white/80">
                <?= h($booking['make']) ?> <?= h($booking['model']) ?>
            </p>
        </div>

        <span class="w-fit rounded-full bg-white px-4 py-2 text-sm font-black text-bay-primary">
            <?= h($booking['status']) ?>
        </span>
    </div>
</section>

<section class="mt-5 grid gap-3 sm:grid-cols-3">
    <div class="rounded-2xl bg-white/80 p-4 shadow-soft backdrop-blur">
        <p class="text-xs font-bold text-bay-muted">Date</p>
        <p class="mt-1 font-black"><?= h(date('D d M Y', strtotime($booking['slot_date']))) ?></p>
    </div>

    <div class="rounded-2xl bg-white/80 p-4 shadow-soft backdrop-blur">
        <p class="text-xs font-bold text-bay-muted">Time</p>
        <p class="mt-1 font-black"><?= h(substr($booking['start_time'], 0, 5)) ?> - <?= h(substr($booking['end_time'], 0, 5)) ?></p>
    </div>

    <div class="rounded-2xl bg-white/80 p-4 shadow-soft backdrop-blur">
        <p class="text-xs font-bold text-bay-muted">Estimate from</p>
        <p class="mt-1 font-black"><?= money_gbp($booking['base_price']) ?></p>
    </div>
</section>

<?php if ($booking['notes']): ?>
    <section class="mt-5 rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <p class="mb-1 text-sm font-black">Your notes</p>
        <p class="text-sm leading-6 text-bay-muted"><?= nl2br(h($booking['notes'])) ?></p>
    </section>
<?php endif; ?>

<section class="mt-5 rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
    <h3 class="mb-4 text-xl font-black">Repair progress</h3>

    <div class="space-y-3">
        <?php foreach ($steps as $index => $step): ?>
            <?php
            $done = $index <= $currentIndex;
            $current = $index === $currentIndex;
            ?>
            <div class="flex items-center gap-3 rounded-2xl <?= $done ? 'bg-bay-primarySoft text-bay-ink' : 'bg-white text-slate-400' ?> p-4 shadow-sm">
                <div class="flex h-9 w-9 items-center justify-center rounded-full <?= $done ? 'bg-bay-primary text-white' : 'bg-slate-100 text-slate-400' ?> text-sm font-black">
                    <?= $done ? '✓' : $index + 1 ?>
                </div>

                <div>
                    <p class="font-black"><?= h($step) ?></p>
                    <?php if ($current): ?>
                        <p class="text-xs font-bold text-bay-primary">Current stage</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="mt-5">
    <h3 class="mb-3 text-xl font-black">Garage updates</h3>

    <div class="space-y-3">
        <?php foreach ($updates as $update): ?>
            <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
                <p class="text-sm leading-6 text-bay-muted"><?= nl2br(h($update['update_text'])) ?></p>

                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-400">
                    <?= h(date('d M Y, H:i', strtotime($update['created_at']))) ?>
                </p>
            </div>
        <?php endforeach; ?>

        <?php if (!$updates): ?>
            <div class="rounded-[1.5rem] bg-bay-blueSoft p-6 text-center shadow-soft">
                <p class="font-bold">No updates yet</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>