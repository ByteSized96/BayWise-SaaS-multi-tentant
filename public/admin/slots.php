<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/csrf.php';
require_once __DIR__ . '/../../app/Core/database.php';

require_admin();

$pageTitle = 'Calendar Slots';

$garageId = garage_id();

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
    WHERE calendar_slots.garage_id = ?
    ORDER BY slot_date ASC, start_time ASC
");
$slotsStmt->execute([$garageId, $garageId]);
$slots = $slotsStmt->fetchAll();

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>

    <div class="relative">
        <p class="text-sm font-bold text-white/80">Garage admin</p>
        <h2 class="mt-2 text-4xl font-black tracking-tight">Calendar slots</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
            Create appointment windows customers can book online.
        </p>
    </div>
</section>

<section class="mt-5 grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
    <form action="<?= app_url('actions/admin/create-slot.php') ?>" method="post"
          class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <?= csrf_field() ?>

        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Add slot</p>
        <h3 class="mt-2 text-2xl font-black">Appointment window</h3>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-bold">Date</label>
                <input name="slot_date" type="date" required
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Capacity</label>
                <input name="capacity" type="number" min="1" value="1"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Start time</label>
                <input name="start_time" type="time" required
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">End time</label>
                <input name="end_time" type="time" required
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>
        </div>

        <button class="mt-5 w-full rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
            Save slot
        </button>
    </form>

    <div class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Availability</p>
                <h3 class="mt-2 text-2xl font-black">Upcoming slots</h3>
            </div>

            <span class="rounded-full bg-bay-primarySoft px-3 py-1 text-xs font-black text-bay-primary">
                <?= count($slots) ?> total
            </span>
        </div>

        <div class="space-y-3">
            <?php foreach ($slots as $slot): ?>
                <?php
                $remaining = (int)$slot['capacity'] - (int)$slot['booked_count'];
                ?>

                <div class="rounded-2xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-black">
                                <?= h(date('D d M Y', strtotime($slot['slot_date']))) ?>
                            </h3>

                            <p class="mt-1 text-sm font-bold text-bay-muted">
                                <?= h(substr($slot['start_time'], 0, 5)) ?> - <?= h(substr($slot['end_time'], 0, 5)) ?>
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-black <?= $slot['is_active'] ? 'bg-bay-greenSoft text-green-600' : 'bg-slate-100 text-slate-600' ?>">
                            <?= $slot['is_active'] ? 'Active' : 'Hidden' ?>
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-2xl bg-bay-purpleSoft p-3">
                            <p class="text-xs font-bold text-bay-muted">Capacity</p>
                            <p class="font-black"><?= (int)$slot['capacity'] ?></p>
                        </div>

                        <div class="rounded-2xl bg-bay-blueSoft p-3">
                            <p class="text-xs font-bold text-bay-muted">Booked</p>
                            <p class="font-black"><?= (int)$slot['booked_count'] ?></p>
                        </div>

                        <div class="rounded-2xl bg-bay-greenSoft p-3">
                            <p class="text-xs font-bold text-bay-muted">Left</p>
                            <p class="font-black"><?= max(0, $remaining) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!$slots): ?>
                <div class="rounded-2xl bg-bay-blueSoft p-6 text-center">
                    <p class="font-black">No calendar slots yet</p>
                    <p class="mt-1 text-sm text-bay-muted">Add your first appointment slot to start taking bookings.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>