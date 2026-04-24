<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/database.php';

require_customer();

$pageTitle = 'Customer Dashboard';

$stmt = $pdo->prepare("
    SELECT id FROM customers
    WHERE user_id = ?
    LIMIT 1
");
$stmt->execute([user()['id']]);
$customer = $stmt->fetch();

$vehicleCount = 0;
$bookingCount = 0;
$openBookingCount = 0;
$latestBookings = [];

if ($customer) {
    $countVehicles = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE customer_id = ?");
    $countVehicles->execute([$customer['id']]);
    $vehicleCount = (int)$countVehicles->fetchColumn();

    $countBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ?");
    $countBookings->execute([$customer['id']]);
    $bookingCount = (int)$countBookings->fetchColumn();

    $countOpen = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ? AND status NOT IN ('Completed','Cancelled')");
    $countOpen->execute([$customer['id']]);
    $openBookingCount = (int)$countOpen->fetchColumn();

    $latest = $pdo->prepare("
        SELECT 
            bookings.*,
            services.name AS service_name,
            vehicles.make,
            vehicles.model,
            vehicles.registration,
            calendar_slots.slot_date,
            calendar_slots.start_time
        FROM bookings
        JOIN services ON services.id = bookings.service_id
        JOIN vehicles ON vehicles.id = bookings.vehicle_id
        JOIN calendar_slots ON calendar_slots.id = bookings.slot_id
        WHERE bookings.customer_id = ?
        ORDER BY bookings.created_at DESC
        LIMIT 3
    ");
    $latest->execute([$customer['id']]);
    $latestBookings = $latest->fetchAll();
}

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>
    <div class="absolute -bottom-20 left-8 h-56 w-56 rounded-full bg-bay-blue/30 blur-3xl"></div>

    <div class="relative">
        <p class="text-sm font-bold text-white/80">Welcome, <?= h(user()['name']) ?></p>
        <h2 class="mt-2 text-4xl font-black tracking-tight">Your garage portal</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
            Book services, manage your vehicles and follow repair progress without needing to phone the garage.
        </p>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <a href="<?= app_url('customer/book-service.php') ?>" class="rounded-2xl bg-white px-5 py-3 text-center text-sm font-black text-bay-ink shadow-soft">
                Book a service
            </a>

            <a href="<?= app_url('customer/vehicles.php') ?>" class="rounded-2xl bg-white/20 px-5 py-3 text-center text-sm font-black text-white backdrop-blur">
                Add vehicle
            </a>
        </div>
    </div>
</section>

<section class="mt-5 grid gap-4 sm:grid-cols-3">
    <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-blueSoft text-xl">🚗</div>
        <p class="text-sm font-bold text-bay-muted">Vehicles</p>
        <p class="mt-2 text-4xl font-black"><?= $vehicleCount ?></p>
    </div>

    <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-purpleSoft text-xl">📅</div>
        <p class="text-sm font-bold text-bay-muted">Bookings</p>
        <p class="mt-2 text-4xl font-black"><?= $bookingCount ?></p>
    </div>

    <div class="rounded-[1.5rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-amberSoft text-xl">🔧</div>
        <p class="text-sm font-bold text-bay-muted">In Progress</p>
        <p class="mt-2 text-4xl font-black"><?= $openBookingCount ?></p>
    </div>
</section>

<section class="mt-5 grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
    <div class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Quick actions</p>
        <h3 class="mt-2 text-2xl font-black">What would you like to do?</h3>

        <div class="mt-5 grid gap-3">
            <a href="<?= app_url('customer/book-service.php') ?>" class="rounded-2xl bg-bay-primarySoft p-4">
                <p class="font-black text-bay-primary">Book a service →</p>
                <p class="mt-1 text-sm text-bay-muted">Choose a service and available calendar slot.</p>
            </a>

            <a href="<?= app_url('customer/vehicles.php') ?>" class="rounded-2xl bg-bay-pinkSoft p-4">
                <p class="font-black text-pink-500">Manage vehicles →</p>
                <p class="mt-1 text-sm text-bay-muted">Add or review your saved vehicle details.</p>
            </a>

            <a href="<?= app_url('customer/bookings.php') ?>" class="rounded-2xl bg-bay-greenSoft p-4">
                <p class="font-black text-green-600">Track repairs →</p>
                <p class="mt-1 text-sm text-bay-muted">Follow booking status and garage updates.</p>
            </a>
        </div>
    </div>

    <div class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Latest activity</p>
                <h3 class="mt-2 text-2xl font-black">Recent bookings</h3>
            </div>

            <a href="<?= app_url('customer/bookings.php') ?>" class="text-sm font-black text-bay-primary">View all</a>
        </div>

        <div class="space-y-3">
            <?php foreach ($latestBookings as $booking): ?>
                <a href="<?= app_url('customer/booking-view.php?id=' . (int)$booking['id']) ?>" class="block rounded-2xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-black"><?= h($booking['service_name']) ?></p>
                            <p class="mt-1 text-sm text-bay-muted">
                                <?= h($booking['make']) ?> <?= h($booking['model']) ?>
                                <?= $booking['registration'] ? ' · ' . h($booking['registration']) : '' ?>
                            </p>
                            <p class="mt-1 text-xs font-bold text-slate-400">
                                <?= h(date('D d M', strtotime($booking['slot_date']))) ?> at <?= h(substr($booking['start_time'], 0, 5)) ?>
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-black <?= status_badge($booking['status']) ?>">
                            <?= h($booking['status']) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php if (!$latestBookings): ?>
                <div class="rounded-2xl bg-bay-blueSoft p-5 text-center">
                    <p class="font-black">No bookings yet</p>
                    <p class="mt-1 text-sm text-bay-muted">Book your first service to start tracking progress.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>