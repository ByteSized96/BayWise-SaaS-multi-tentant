<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/database.php';
require_once __DIR__ . '/../../app/Core/csrf.php';

require_customer();

$pageTitle = 'My Vehicles';

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

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>

    <div class="relative">
        <p class="text-sm font-bold text-white/80">Customer garage</p>
        <h2 class="mt-2 text-4xl font-black tracking-tight">My vehicles</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
            Save your vehicle details once, then reuse them whenever you book a service.
        </p>
    </div>
</section>

<section class="mt-5 grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
    <form action="<?= app_url('actions/customer/create-vehicle.php') ?>" method="post"
          class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <?= csrf_field() ?>

        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Add vehicle</p>
        <h3 class="mt-2 text-2xl font-black">Vehicle details</h3>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-bold">Make</label>
                <input name="make" required
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Model</label>
                <input name="model" required
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Year</label>
                <input name="year" type="number"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Registration</label>
                <input name="registration"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 uppercase outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold">Mileage</label>
                <input name="mileage" type="number"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>
        </div>

        <button class="mt-5 w-full rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
            Save vehicle
        </button>
    </form>

    <div class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Saved cars</p>
                <h3 class="mt-2 text-2xl font-black">Your garage</h3>
            </div>

            <span class="rounded-full bg-bay-primarySoft px-3 py-1 text-xs font-black text-bay-primary">
                <?= count($vehicles) ?> saved
            </span>
        </div>

        <div class="space-y-3">
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="rounded-2xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-black">
                                <?= h($vehicle['make']) ?> <?= h($vehicle['model']) ?>
                            </h3>

                            <p class="mt-1 text-sm font-bold text-bay-muted">
                                <?= h($vehicle['registration'] ?: 'No registration') ?>
                            </p>
                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-bay-blueSoft text-xl">
                            🚗
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl bg-bay-purpleSoft p-3">
                            <p class="text-xs font-bold text-bay-muted">Year</p>
                            <p class="font-black"><?= h((string)($vehicle['year'] ?: 'Unknown')) ?></p>
                        </div>

                        <div class="rounded-2xl bg-bay-greenSoft p-3">
                            <p class="text-xs font-bold text-bay-muted">Mileage</p>
                            <p class="font-black">
                                <?= $vehicle['mileage'] ? number_format((int)$vehicle['mileage']) . ' mi' : 'Unknown' ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!$vehicles): ?>
                <div class="rounded-2xl bg-bay-blueSoft p-6 text-center">
                    <p class="font-black">No vehicles added yet</p>
                    <p class="mt-1 text-sm text-bay-muted">Add your first vehicle to start booking services.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>