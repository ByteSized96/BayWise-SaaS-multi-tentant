<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/database.php';

require_admin();

$pageTitle = 'Admin Dashboard';

$garageId = garage_id();

$countCustomers = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE garage_id = ?");
$countCustomers->execute([$garageId]);
$totalCustomers = (int)$countCustomers->fetchColumn();

$countVehicles = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE garage_id = ?");
$countVehicles->execute([$garageId]);
$totalVehicles = (int)$countVehicles->fetchColumn();

$countBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE garage_id = ?");
$countBookings->execute([$garageId]);
$totalBookings = (int)$countBookings->fetchColumn();

$countOpenBookings = $pdo->prepare("
    SELECT COUNT(*) 
    FROM bookings 
    WHERE garage_id = ?
    AND status NOT IN ('Completed','Cancelled')
");
$countOpenBookings->execute([$garageId]);
$openBookings = (int)$countOpenBookings->fetchColumn();

$countTodayBookings = $pdo->prepare("
    SELECT COUNT(*)
    FROM bookings
    JOIN calendar_slots ON calendar_slots.id = bookings.slot_id
    WHERE bookings.garage_id = ?
    AND calendar_slots.slot_date = CURDATE()
");
$countTodayBookings->execute([$garageId]);
$todayBookings = (int)$countTodayBookings->fetchColumn();

$recentStmt = $pdo->prepare("
    SELECT 
        bookings.*,
        customers.name AS customer_name,
        services.name AS service_name,
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
    ORDER BY bookings.created_at DESC
    LIMIT 5
");
$recentStmt->execute([$garageId]);
$recentBookings = $recentStmt->fetchAll();

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>
    <div class="absolute -bottom-20 left-8 h-56 w-56 rounded-full bg-bay-blue/30 blur-3xl"></div>

    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold text-white/80">Admin mode</p>
            <h2 class="mt-2 text-4xl font-black tracking-tight">Garage command centre</h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
                Manage online bookings, calendar slots, services and customer-visible repair updates from one clean workspace.
            </p>
        </div>

        <div class="grid gap-3 sm:flex">
            <a href="<?= app_url('admin/bookings.php') ?>" class="rounded-2xl bg-white px-5 py-3 text-center text-sm font-black text-bay-ink shadow-soft">
                View bookings
            </a>

            <a href="<?= app_url('admin/slots.php') ?>" class="rounded-2xl bg-white/20 px-5 py-3 text-center text-sm font-black text-white backdrop-blur">
                Add slot
            </a>
        </div>
    </div>
</section>

<section class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-blueSoft text-xl">👥</div>
        <p class="text-sm font-bold text-bay-muted">Customers</p>
        <p class="mt-2 text-4xl font-black"><?= $totalCustomers ?></p>
    </div>

    <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-greenSoft text-xl">🚗</div>
        <p class="text-sm font-bold text-bay-muted">Vehicles</p>
        <p class="mt-2 text-4xl font-black"><?= $totalVehicles ?></p>
    </div>

    <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-purpleSoft text-xl">📅</div>
        <p class="text-sm font-bold text-bay-muted">Bookings</p>
        <p class="mt-2 text-4xl font-black"><?= $totalBookings ?></p>
    </div>

    <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-amberSoft text-xl">🔧</div>
        <p class="text-sm font-bold text-bay-muted">Open jobs</p>
        <p class="mt-2 text-4xl font-black"><?= $openBookings ?></p>
    </div>

    <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-pinkSoft text-xl">⭐</div>
        <p class="text-sm font-bold text-bay-muted">Today</p>
        <p class="mt-2 text-4xl font-black"><?= $todayBookings ?></p>
    </div>
</section>

<section class="mt-5 grid gap-4 lg:grid-cols-[0.85fr_1.15fr]">
    <div class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Admin shortcuts</p>
        <h3 class="mt-2 text-2xl font-black">Manage the portal</h3>

        <div class="mt-5 grid gap-3">
            <a href="<?= app_url('admin/bookings.php') ?>" class="rounded-2xl bg-bay-primarySoft p-4">
                <p class="font-black text-bay-primary">Review bookings →</p>
                <p class="mt-1 text-sm text-bay-muted">Confirm requests and update repair progress.</p>
            </a>

            <a href="<?= app_url('admin/slots.php') ?>" class="rounded-2xl bg-bay-greenSoft p-4">
                <p class="font-black text-green-600">Manage calendar slots →</p>
                <p class="mt-1 text-sm text-bay-muted">Create appointment windows customers can book.</p>
            </a>

            <a href="<?= app_url('admin/services.php') ?>" class="rounded-2xl bg-bay-pinkSoft p-4">
                <p class="font-black text-pink-500">Manage services →</p>
                <p class="mt-1 text-sm text-bay-muted">Control MOTs, services, diagnostics and pricing.</p>
            </a>

            <a href="<?= app_url('admin/settings.php') ?>" class="rounded-2xl bg-bay-purpleSoft p-4">
                <p class="font-black text-purple-600">Brand settings →</p>
                <p class="mt-1 text-sm text-bay-muted">Update homepage copy, CTA and hero image.</p>
            </a>
        </div>
    </div>

    <div class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Live queue</p>
                <h3 class="mt-2 text-2xl font-black">Recent bookings</h3>
            </div>

            <a href="<?= app_url('admin/bookings.php') ?>" class="text-sm font-black text-bay-primary">View all</a>
        </div>

        <div class="space-y-3">
            <?php foreach ($recentBookings as $booking): ?>
                <a href="<?= app_url('admin/booking-view.php?id=' . (int)$booking['id']) ?>" class="block rounded-2xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-black"><?= h($booking['service_name']) ?></p>
                            <p class="mt-1 text-sm text-bay-muted">
                                <?= h($booking['customer_name']) ?> ·
                                <?= h($booking['make']) ?> <?= h($booking['model']) ?>
                                <?= $booking['registration'] ? ' · ' . h($booking['registration']) : '' ?>
                            </p>
                            <p class="mt-1 text-xs font-bold text-slate-400">
                                <?= h(date('D d M', strtotime($booking['slot_date']))) ?>
                                at <?= h(substr($booking['start_time'], 0, 5)) ?>
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-black <?= status_badge($booking['status']) ?>">
                            <?= h($booking['status']) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php if (!$recentBookings): ?>
                <div class="rounded-2xl bg-bay-blueSoft p-5 text-center">
                    <p class="font-black">No bookings yet</p>
                    <p class="mt-1 text-sm text-bay-muted">Customer bookings will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>