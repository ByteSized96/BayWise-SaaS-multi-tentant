<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/csrf.php';
require_once __DIR__ . '/../../app/Core/database.php';

require_admin();

$pageTitle = 'Services';

$garageId = garage_id();

$servicesStmt = $pdo->prepare("
    SELECT * 
    FROM services
    WHERE garage_id = ?
    ORDER BY is_active DESC, name ASC
");
$servicesStmt->execute([$garageId]);
$services = $servicesStmt->fetchAll();

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>

    <div class="relative">
        <p class="text-sm font-bold text-white/80">Garage admin</p>
        <h2 class="mt-2 text-4xl font-black tracking-tight">Services</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
            Create and manage the services customers can book through the portal.
        </p>
    </div>
</section>

<section class="mt-5 grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
    <form action="<?= app_url('actions/admin/create-service.php') ?>" method="post"
          class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <?= csrf_field() ?>

        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Add service</p>
        <h3 class="mt-2 text-2xl font-black">Service details</h3>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold">Service name</label>
                <input name="name" required placeholder="Example: Full Service"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold">Description</label>
                <textarea name="description" rows="4"
                          class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft"></textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Duration minutes</label>
                <input name="duration_minutes" type="number" value="60"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Base price</label>
                <input name="base_price" type="number" step="0.01" value="0.00"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>
        </div>

        <button class="mt-5 w-full rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
            Save service
        </button>
    </form>

    <div class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Available services</p>
                <h3 class="mt-2 text-2xl font-black">Bookable list</h3>
            </div>

            <span class="rounded-full bg-bay-primarySoft px-3 py-1 text-xs font-black text-bay-primary">
                <?= count($services) ?> total
            </span>
        </div>

        <div class="space-y-3">
            <?php foreach ($services as $service): ?>
                <div class="rounded-2xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-black"><?= h($service['name']) ?></h3>
                            <p class="mt-2 text-sm leading-6 text-bay-muted"><?= h($service['description']) ?></p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-black <?= $service['is_active'] ? 'bg-bay-greenSoft text-green-600' : 'bg-slate-100 text-slate-600' ?>">
                            <?= $service['is_active'] ? 'Active' : 'Hidden' ?>
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-bay-purpleSoft p-3">
                            <p class="text-xs font-bold text-bay-muted">Duration</p>
                            <p class="font-black"><?= (int)$service['duration_minutes'] ?> mins</p>
                        </div>

                        <div class="rounded-2xl bg-bay-greenSoft p-3">
                            <p class="text-xs font-bold text-bay-muted">Base price</p>
                            <p class="font-black"><?= money_gbp($service['base_price']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!$services): ?>
                <div class="rounded-2xl bg-bay-blueSoft p-6 text-center">
                    <p class="font-black">No services yet</p>
                    <p class="mt-1 text-sm text-bay-muted">Add your first service to start accepting bookings.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>