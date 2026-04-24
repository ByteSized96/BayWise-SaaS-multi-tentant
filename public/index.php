<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/auth.php';
require_once __DIR__ . '/../app/Core/database.php';

$brandName = setting($pdo, 'brand_name', 'BayWise Portal');
$tagline = setting($pdo, 'brand_tagline', 'Book services, track repairs, and keep customers updated.');
$intro = setting($pdo, 'brand_intro', 'A modern garage booking and repair tracking platform for customers and mechanics.');
$heroImage = setting($pdo, 'hero_image', 'assets/img/garage-hero.jpg');
$primaryCta = setting($pdo, 'primary_cta', 'Create customer account');

$pageTitle = $brandName;
$metaTitle = $brandName . ' | Garage Booking & Repair Tracking Portal';
$metaDescription = 'A mobile-first garage booking portal where customers can book services, add vehicles and track repair progress online.';

require_once __DIR__ . '/../app/Layout/header.php';
?>

<section class="relative -mx-4 -mt-5 overflow-hidden bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink px-4 py-8 text-white sm:rounded-b-[3rem] lg:mx-0 lg:mt-0 lg:rounded-[3rem] lg:px-8 lg:py-10">
    <div class="absolute inset-0 opacity-25">
        <img src="<?= h(app_url($heroImage)) ?>" alt="<?= h($brandName) ?>" class="h-full w-full object-cover">
    </div>

    <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-bay-primary/35 to-bay-pink/40"></div>
    <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/30 blur-3xl"></div>
    <div class="absolute -bottom-24 left-10 h-72 w-72 rounded-full bg-bay-blue/30 blur-3xl"></div>

    <div class="relative grid gap-8 lg:grid-cols-[1fr_430px] lg:items-center">
        <div class="py-6 lg:py-12">
            <p class="inline-flex rounded-full border border-white/30 bg-white/20 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-white backdrop-blur">
                Modern garage portal
            </p>

            <h1 class="mt-5 max-w-4xl text-5xl font-black tracking-tight text-white sm:text-6xl lg:text-7xl">
                <?= h($tagline) ?>
            </h1>

            <p class="mt-5 max-w-2xl text-lg leading-8 text-white/85">
                <?= h($intro) ?>
            </p>

            <div class="mt-7 grid gap-3 sm:flex">
                <a href="<?= app_url('register.php') ?>" class="rounded-2xl bg-white px-6 py-4 text-center font-black text-bay-ink shadow-soft">
                    <?= h($primaryCta) ?>
                </a>

                <a href="<?= app_url('login.php') ?>" class="rounded-2xl border border-white/30 bg-white/20 px-6 py-4 text-center font-black text-white backdrop-blur">
                    Login to portal
                </a>
           
            <a href="<?= app_url('register-garage.php') ?>" class="rounded-2xl bg-white/20 px-6 py-4 text-center font-black text-white backdrop-blur">
    Create garage portal
</a>
 </div>
            <div class="mt-8 grid max-w-xl grid-cols-3 gap-3">
                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-2xl font-black">24/7</p>
                    <p class="mt-1 text-xs font-bold text-white/80">Online booking</p>
                </div>

                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-2xl font-black">Live</p>
                    <p class="mt-1 text-xs font-bold text-white/80">Repair updates</p>
                </div>

                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-2xl font-black">CRM</p>
                    <p class="mt-1 text-xs font-bold text-white/80">Garage admin</p>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-white/25 bg-white/20 p-4 shadow-2xl backdrop-blur-xl">
            <div class="rounded-[1.5rem] bg-white/95 p-4 text-bay-ink shadow-soft">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Current repair</p>
                        <h2 class="mt-1 text-2xl font-black">Brake inspection</h2>
                    </div>

                    <span class="rounded-full bg-bay-amberSoft px-3 py-1 text-xs font-black text-amber-600">
                        Inspection
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    <?php foreach (['Booking received', 'Vehicle checked in', 'Inspection started'] as $step): ?>
                        <div class="flex items-center gap-3 rounded-2xl bg-bay-primarySoft p-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-bay-primary text-sm font-black text-white">
                                ✓
                            </div>
                            <span class="text-sm font-black"><?= h($step) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="rounded-2xl border border-dashed border-bay-primary/40 bg-bay-pinkSoft p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-bay-muted">Next update</p>
                        <p class="mt-1 text-sm font-black">Mechanic will add findings and collection status.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-[1.25rem] bg-white/90 p-4 text-bay-ink shadow-soft">
                    <p class="text-xs font-bold text-bay-muted">Service</p>
                    <p class="mt-1 font-black">Brake check</p>
                </div>

                <div class="rounded-[1.25rem] bg-white/90 p-4 text-bay-ink shadow-soft">
                    <p class="text-xs font-bold text-bay-muted">Estimate</p>
                    <p class="mt-1 font-black">From £59.99</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-6 grid gap-4 lg:grid-cols-3">
    <a href="<?= app_url('register.php') ?>" class="group rounded-[2rem] bg-white/80 p-6 shadow-soft backdrop-blur transition hover:-translate-y-1">
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-bay-primary text-xl font-black text-white">
            01
        </div>

        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Customer</p>
        <h3 class="mt-2 text-2xl font-black">Book a service</h3>
        <p class="mt-3 text-sm leading-6 text-bay-muted">
            Create an account, add your vehicle and request a garage appointment online.
        </p>
        <p class="mt-5 text-sm font-black text-bay-primary">Start booking →</p>
    </a>

    <a href="<?= app_url('login.php') ?>" class="group rounded-[2rem] bg-white/80 p-6 shadow-soft backdrop-blur transition hover:-translate-y-1">
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-bay-pinkSoft text-xl font-black text-pink-500">
            02
        </div>

        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Portal</p>
        <h3 class="mt-2 text-2xl font-black">Track repairs</h3>
        <p class="mt-3 text-sm leading-6 text-bay-muted">
            See progress stages, garage notes and collection readiness from your phone.
        </p>
        <p class="mt-5 text-sm font-black text-pink-500">Login to track →</p>
    </a>

    <a href="<?= app_url('login.php') ?>" class="group rounded-[2rem] bg-gradient-to-br from-bay-primary to-bay-purple p-6 text-white shadow-soft transition hover:-translate-y-1">
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl font-black text-bay-primary">
            03
        </div>

        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/70">Garage admin</p>
        <h3 class="mt-2 text-2xl font-black">Manage the workshop</h3>
        <p class="mt-3 text-sm leading-6 text-white/80">
            Control services, booking slots, repair updates and customer progress.
        </p>

        <p class="mt-5 rounded-2xl bg-white/20 p-3 text-xs font-bold text-white/80">
            Demo: admin@baywise.test / password
        </p>
    </a>
</section>

<section class="mt-6 overflow-hidden rounded-[2.5rem] bg-white/80 shadow-soft backdrop-blur">
    <div class="grid lg:grid-cols-[0.8fr_1.2fr]">
        <div class="bg-gradient-to-br from-bay-primary to-bay-pink p-6 text-white lg:p-8">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-white/70">How it works</p>

            <h2 class="mt-3 text-3xl font-black">
                From booking to collection, everything is visible.
            </h2>

            <p class="mt-4 text-sm leading-7 text-white/80">
                Customers don’t need to phone repeatedly. The garage updates the booking, and the portal shows the current repair stage.
            </p>
        </div>

        <div class="grid gap-4 p-6 sm:grid-cols-2 lg:p-8">
            <?php
            $features = [
                ['Online booking', 'Customers choose a service and available time slot.', 'bg-bay-blueSoft'],
                ['Vehicle records', 'Customers save cars for faster repeat bookings.', 'bg-bay-greenSoft'],
                ['Repair timeline', 'Progress stages make every job easy to understand.', 'bg-bay-amberSoft'],
                ['Brand settings', 'Change the garage name, hero image and homepage copy.', 'bg-bay-purpleSoft'],
            ];
            ?>

            <?php foreach ($features as $feature): ?>
                <div class="rounded-[1.5rem] <?= h($feature[2]) ?> p-5">
                    <h3 class="font-black"><?= h($feature[0]) ?></h3>
                    <p class="mt-2 text-sm leading-6 text-bay-muted"><?= h($feature[1]) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../app/Layout/footer.php'; ?>