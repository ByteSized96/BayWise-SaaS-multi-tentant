<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/auth.php';
require_once __DIR__ . '/../app/Core/database.php';

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    header('Location: ' . app_url('index.php'));
    exit;
}

$garageStmt = $pdo->prepare("SELECT * FROM garages WHERE slug = ? LIMIT 1");
$garageStmt->execute([$slug]);
$garage = $garageStmt->fetch();

if (!$garage) {
    http_response_code(404);
    exit('Garage portal not found.');
}

$garageId = (int)$garage['id'];

$settingsStmt = $pdo->prepare("
    SELECT setting_key, setting_value
    FROM site_settings
    WHERE garage_id = ?
");
$settingsStmt->execute([$garageId]);

$settings = [];
foreach ($settingsStmt->fetchAll() as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$brandName = $settings['brand_name'] ?? $garage['name'];
$tagline = $settings['brand_tagline'] ?? 'Book services, track repairs, and stay updated online.';
$intro = $settings['brand_intro'] ?? 'A modern online portal for booking garage services and tracking repair progress.';
$heroImage = $settings['hero_image'] ?? 'assets/img/garage-hero.jpg';
$primaryCta = $settings['primary_cta'] ?? 'Create customer account';

$servicesStmt = $pdo->prepare("
    SELECT *
    FROM services
    WHERE garage_id = ?
    AND is_active = 1
    ORDER BY name ASC
    LIMIT 6
");
$servicesStmt->execute([$garageId]);
$services = $servicesStmt->fetchAll();

$pageTitle = $brandName;
$metaTitle = $brandName . ' | Online Garage Booking Portal';
$metaDescription = $intro;

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
                <?= h($brandName) ?>
            </p>

            <h1 class="mt-5 max-w-4xl text-5xl font-black tracking-tight text-white sm:text-6xl lg:text-7xl">
                <?= h($tagline) ?>
            </h1>

            <p class="mt-5 max-w-2xl text-lg leading-8 text-white/85">
                <?= h($intro) ?>
            </p>

            <div class="mt-7 grid gap-3 sm:flex">
                <a href="<?= app_url('g/' . urlencode($garage['slug']) . '/register' . urlencode($garage['slug'])) ?>" class="rounded-2xl bg-white px-6 py-4 text-center font-black text-bay-ink shadow-soft">
                    <?= h($primaryCta) ?>
                </a>

                <a href="<a href="<?= app_url('g/' . urlencode($garage['slug']) . '/login') ?>">" class="rounded-2xl border border-white/30 bg-white/20 px-6 py-4 text-center font-black text-white backdrop-blur">
                    Login
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
                    <p class="text-2xl font-black">Secure</p>
                    <p class="mt-1 text-xs font-bold text-white/80">Customer portal</p>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-white/25 bg-white/20 p-4 shadow-2xl backdrop-blur-xl">
            <div class="rounded-[1.5rem] bg-white/95 p-4 text-bay-ink shadow-soft">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Bookable services</p>
                <h2 class="mt-1 text-2xl font-black">Popular services</h2>

                <div class="mt-5 space-y-3">
                    <?php foreach ($services as $service): ?>
                        <div class="rounded-2xl bg-bay-primarySoft p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black"><?= h($service['name']) ?></p>
                                    <p class="mt-1 text-xs font-bold text-bay-muted">
                                        <?= (int)$service['duration_minutes'] ?> mins
                                    </p>
                                </div>

                                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-bay-primary">
                                    <?= money_gbp($service['base_price']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!$services): ?>
                        <div class="rounded-2xl bg-bay-blueSoft p-4 text-center">
                            <p class="font-black">Services coming soon</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-[1.25rem] bg-white/90 p-4 text-bay-ink shadow-soft">
                    <p class="text-xs font-bold text-bay-muted">Garage</p>
                    <p class="mt-1 font-black"><?= h($garage['name']) ?></p>
                </div>

                <div class="rounded-[1.25rem] bg-white/90 p-4 text-bay-ink shadow-soft">
                    <p class="text-xs font-bold text-bay-muted">Contact</p>
                    <p class="mt-1 font-black"><?= h($garage['phone'] ?: 'Online') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-6 grid gap-4 lg:grid-cols-3">
    <a href="<?= app_url('register.php?garage=' . urlencode($garage['slug'])) ?>" class="group rounded-[2rem] bg-white/80 p-6 shadow-soft backdrop-blur transition hover:-translate-y-1">
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-bay-primary text-xl font-black text-white">01</div>
        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Step one</p>
        <h3 class="mt-2 text-2xl font-black">Create account</h3>
        <p class="mt-3 text-sm leading-6 text-bay-muted">Register with this garage so your vehicles and bookings stay connected.</p>
    </a>

    <a href="<?= app_url('register.php?garage=' . urlencode($garage['slug'])) ?>" class="group rounded-[2rem] bg-white/80 p-6 shadow-soft backdrop-blur transition hover:-translate-y-1">
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-bay-pinkSoft text-xl font-black text-pink-500">02</div>
        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Step two</p>
        <h3 class="mt-2 text-2xl font-black">Add vehicle</h3>
        <p class="mt-3 text-sm leading-6 text-bay-muted">Save your car details once, then reuse them for future bookings.</p>
    </a>

    <a href="<?= app_url('login.php?garage=' . urlencode($garage['slug'])) ?>" class="group rounded-[2rem] bg-gradient-to-br from-bay-primary to-bay-purple p-6 text-white shadow-soft transition hover:-translate-y-1">
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl font-black text-bay-primary">03</div>
        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/70">Step three</p>
        <h3 class="mt-2 text-2xl font-black">Track repair</h3>
        <p class="mt-3 text-sm leading-6 text-white/80">Follow booking status, repair stages and garage updates online.</p>
    </a>
</section>

<?php require_once __DIR__ . '/../app/Layout/footer.php'; ?>