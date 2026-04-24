<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/helpers.php';
require_once __DIR__ . '/../../app/Core/auth.php';
require_once __DIR__ . '/../../app/Core/csrf.php';
require_once __DIR__ . '/../../app/Core/database.php';

require_admin();

$pageTitle = 'Settings';

$garageId = garage_id();

$currentGarage = garage($pdo);
$portalUrl = $currentGarage
    ? app_url('g/' . urlencode($currentGarage['slug']))
    : app_url('index.php');

$settings = [
    'brand_name' => setting($pdo, 'brand_name', 'BayWise Portal'),
    'brand_tagline' => setting($pdo, 'brand_tagline', 'Book services, track repairs, and keep customers updated.'),
    'brand_intro' => setting($pdo, 'brand_intro', 'A modern garage booking and repair tracking platform for customers and mechanics.'),
    'hero_image' => setting($pdo, 'hero_image', 'assets/img/garage-hero.jpg'),
    'primary_cta' => setting($pdo, 'primary_cta', 'Create customer account'),
];

require_once __DIR__ . '/../../app/Layout/header.php';
?>

<section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-6 text-white shadow-soft lg:p-8">
    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/25 blur-3xl"></div>

    <div class="relative">
        <p class="text-sm font-bold text-white/80">Garage admin</p>
        <h2 class="mt-2 text-4xl font-black tracking-tight">Brand settings</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">
            Customise the public homepage copy, CTA and hero image without editing code.
        </p>
    </div>
</section>

<section class="mt-5 rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
    <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Customer portal link</p>
    <h3 class="mt-2 text-2xl font-black">Share your booking page</h3>

    <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
        <input id="portalUrl" value="<?= h($portalUrl) ?>" readonly
               class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 text-sm font-bold outline-none">

        <button type="button"
                onclick="navigator.clipboard.writeText(document.getElementById('portalUrl').value); this.innerText='Copied';"
                class="rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
            Copy link
        </button>
    </div>

    <a href="<?= h($portalUrl) ?>" target="_blank" class="mt-3 inline-flex text-sm font-black text-bay-primary">
        Open public portal →
    </a>
</section>

<section class="mt-5 grid gap-4 lg:grid-cols-[0.95fr_1.05fr]">
    <form action="<?= app_url('actions/admin/update-settings.php') ?>" method="post"
          class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <?= csrf_field() ?>

        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Homepage controls</p>
        <h3 class="mt-2 text-2xl font-black">Edit public branding</h3>

        <div class="mt-5 grid gap-4">
            <div>
                <label class="mb-1 block text-sm font-bold">Brand name</label>
                <input name="brand_name" value="<?= h($settings['brand_name']) ?>" required
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Homepage headline</label>
                <input name="brand_tagline" value="<?= h($settings['brand_tagline']) ?>" required
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Intro text</label>
                <textarea name="brand_intro" rows="4"
                          class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft"><?= h($settings['brand_intro']) ?></textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Hero image path</label>
                <input name="hero_image" value="<?= h($settings['hero_image']) ?>"
                       placeholder="assets/img/garage-hero.jpg"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                <p class="mt-2 text-xs font-bold text-bay-muted">
                    Upload the image into <code>public/assets/img/</code>, then paste the path here.
                </p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold">Primary button text</label>
                <input name="primary_cta" value="<?= h($settings['primary_cta']) ?>"
                       class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
            </div>
        </div>

        <button class="mt-5 w-full rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
            Save settings
        </button>
    </form>

    <div class="rounded-[2rem] bg-white/80 p-5 shadow-soft backdrop-blur">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-bay-muted">Live-style preview</p>
        <h3 class="mt-2 text-2xl font-black">Homepage preview</h3>

        <div class="mt-5 overflow-hidden rounded-[2rem] bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-5 text-white shadow-soft">
            <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-white/70">
                    Modern garage portal
                </p>

                <h4 class="mt-3 text-3xl font-black">
                    <?= h($settings['brand_tagline']) ?>
                </h4>

                <p class="mt-3 text-sm leading-6 text-white/80">
                    <?= h($settings['brand_intro']) ?>
                </p>

                <div class="mt-5 inline-flex rounded-2xl bg-white px-4 py-3 text-sm font-black text-bay-ink">
                    <?= h($settings['primary_cta']) ?>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-bay-blueSoft p-4">
                <p class="text-xs font-bold text-bay-muted">Brand</p>
                <p class="mt-1 font-black"><?= h($settings['brand_name']) ?></p>
            </div>

            <div class="rounded-2xl bg-bay-purpleSoft p-4">
                <p class="text-xs font-bold text-bay-muted">Hero image</p>
                <p class="mt-1 break-all text-sm font-black"><?= h($settings['hero_image']) ?></p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../app/Layout/footer.php'; ?>