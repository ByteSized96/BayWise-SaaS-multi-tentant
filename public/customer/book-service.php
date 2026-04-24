<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/database.php';
require_once __DIR__ . '/../../app/Core/csrf.php';

require_customer();

$pageTitle = 'Book Service';

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

$vehicles = [];

if ($customer) {
    $v = $pdo->prepare("
        SELECT * 
        FROM vehicles
        WHERE customer_id = ?
        AND garage_id = ?
        ORDER BY created_at DESC
    ");
    $v->execute([$customer['id'], $garageId]);
    $vehicles = $v->fetchAll();
}

$servicesStmt = $pdo->prepare("
    SELECT * 
    FROM services
    WHERE is_active = 1
    AND garage_id = ?
    ORDER BY name ASC
");
$servicesStmt->execute([$garageId]);
$services = $servicesStmt->fetchAll();

$slotsStmt = $pdo->prepare("
    SELECT 
        calendar_slots.*,
        (
            SELECT COUNT(*) 
            FROM bookings 
            WHERE bookings.slot_id = calendar_slots.id
            AND bookings.garage_id = ?
            AND bookings.status != 'Cancelled'
        ) AS booked_count
    FROM calendar_slots
    WHERE is_active = 1
    AND garage_id = ?
    AND slot_date >= CURDATE()
    ORDER BY slot_date ASC, start_time ASC
");
$slotsStmt->execute([$garageId, $garageId]);
$slots = $slotsStmt->fetchAll();

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>

    <div class="relative">
        <p class="text-sm font-bold text-white/80">Customer booking</p>
        <h2 class="mt-2 text-4xl font-black tracking-tight">Book a service</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
            Choose your vehicle, pick a service and select an available garage slot.
        </p>
    </div>
</section>

<?php if (!$vehicles): ?>
    <section class="mt-5 rounded-[2rem] bg-white/80 p-6 text-center shadow-soft backdrop-blur">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-bay-blueSoft text-2xl">
            🚗
        </div>

        <h3 class="text-xl font-black">Add a vehicle first</h3>
        <p class="mt-2 text-sm text-bay-muted">
            You need at least one saved vehicle before booking a service.
        </p>

        <a href="<?= app_url('customer/vehicles.php') ?>"
           class="mt-5 inline-flex rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
            Add vehicle
        </a>
    </section>
<?php else: ?>

<form action="<?= app_url('actions/customer/create-booking.php') ?>" method="post"
      class="mt-5 rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
    <?= csrf_field() ?>

    <div class="space-y-6">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Step 1</p>
            <label class="mt-2 mb-2 block text-xl font-black">Choose vehicle</label>

            <select name="vehicle_id" required
                    class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 font-bold outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                <option value="">Choose vehicle</option>
                <?php foreach ($vehicles as $vehicle): ?>
                    <option value="<?= (int)$vehicle['id'] ?>">
                        <?= h($vehicle['make']) ?> <?= h($vehicle['model']) ?>
                        <?= $vehicle['registration'] ? ' - ' . h($vehicle['registration']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Step 2</p>
            <label class="mt-2 mb-3 block text-xl font-black">Choose service</label>

            <div class="grid gap-3 lg:grid-cols-2">
                <?php foreach ($services as $service): ?>
                    <label class="block cursor-pointer rounded-2xl border border-bay-line bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-bay-primary">
                        <div class="flex gap-3">
                            <input type="radio" name="service_id" value="<?= (int)$service['id'] ?>" required class="mt-1 accent-bay-primary">

                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-black"><?= h($service['name']) ?></h3>
                                    <span class="rounded-full bg-bay-greenSoft px-3 py-1 text-sm font-black text-green-600">
                                        <?= money_gbp($service['base_price']) ?>
                                    </span>
                                </div>

                                <p class="mt-2 text-sm leading-6 text-bay-muted">
                                    <?= h($service['description']) ?>
                                </p>

                                <p class="mt-3 text-xs font-black uppercase tracking-wide text-bay-muted">
                                    <?= (int)$service['duration_minutes'] ?> minutes
                                </p>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Step 3</p>
            <label class="mt-2 mb-3 block text-xl font-black">Choose available slot</label>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($slots as $slot): ?>
                    <?php
                    $remaining = (int)$slot['capacity'] - (int)$slot['booked_count'];
                    $disabled = $remaining <= 0;
                    ?>

                    <label class="block rounded-2xl border p-4 shadow-sm transition <?= $disabled ? 'border-slate-200 bg-slate-100 opacity-60' : 'cursor-pointer border-bay-line bg-white hover:-translate-y-0.5 hover:border-bay-primary' ?>">
                        <div class="flex gap-3">
                            <input type="radio"
                                   name="slot_id"
                                   value="<?= (int)$slot['id'] ?>"
                                   required
                                   <?= $disabled ? 'disabled' : '' ?>
                                   class="mt-1 accent-bay-primary">

                            <div>
                                <p class="font-black">
                                    <?= h(date('D d M Y', strtotime($slot['slot_date']))) ?>
                                </p>

                                <p class="mt-1 text-sm font-bold text-bay-muted">
                                    <?= h(substr($slot['start_time'], 0, 5)) ?> - <?= h(substr($slot['end_time'], 0, 5)) ?>
                                </p>

                                <p class="mt-3 rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide <?= $disabled ? 'bg-red-50 text-red-600' : 'bg-bay-greenSoft text-green-600' ?>">
                                    <?= $disabled ? 'Fully booked' : $remaining . ' space(s) left' ?>
                                </p>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>

                <?php if (!$slots): ?>
                    <div class="rounded-2xl bg-bay-amberSoft p-5 text-center sm:col-span-2 lg:col-span-3">
                        <p class="font-black">No slots available</p>
                        <p class="mt-1 text-sm text-bay-muted">Please check again later.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Step 4</p>
            <label class="mt-2 mb-2 block text-xl font-black">Notes for the garage</label>

            <textarea name="notes" rows="4"
                      placeholder="Example: Car makes a noise when braking, worse at low speed."
                      class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft"></textarea>
        </div>
    </div>

    <button class="mt-6 w-full rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-4 font-black text-white shadow-soft">
        Request booking
    </button>
</form>

<?php endif; ?>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>