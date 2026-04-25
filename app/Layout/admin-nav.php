<aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:block lg:w-72 lg:border-r lg:border-bay-line lg:bg-white lg:px-4 lg:py-5">
    <div class="mb-8">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-bay-muted">BayWise</p>
        <h2 class="text-xl font-black">Garage Admin</h2>
    </div>

<nav class="space-y-2 text-sm font-black">
    <a href="<?= app_url('admin/dashboard.php') ?>" class="block rounded-2xl px-4 py-3 <?= active_class('dashboard.php') ?>">Dashboard</a>
    <a href="<?= app_url('admin/bookings.php') ?>" class="block rounded-2xl px-4 py-3 <?= active_class('bookings.php') ?>">Bookings</a>
    <a href="<?= app_url('admin/slots.php') ?>" class="block rounded-2xl px-4 py-3 <?= active_class('slots.php') ?>">Calendar Slots</a>
    <a href="<?= app_url('admin/services.php') ?>" class="block rounded-2xl px-4 py-3 <?= active_class('services.php') ?>">Services</a>
    <a href="<?= app_url('admin/settings.php') ?>" class="block rounded-2xl px-4 py-3 <?= active_class('settings.php') ?>">Settings</a>
</nav>

    <div class="absolute bottom-5 left-4 right-4 rounded-[1.5rem] bg-slate-50 p-4">
        <p class="text-sm font-black"><?= h(user()['name'] ?? 'Admin') ?></p>
        <p class="mt-1 text-xs font-bold text-bay-muted">Garage administrator</p>
        <a href="<?= app_url('logout.php') ?>" class="mt-3 inline-flex text-sm font-black text-slate-900">
            Logout →
        </a>
    </div>
</aside>

<nav class="fixed bottom-0 left-0 right-0 z-50 border-t border-bay-line bg-white/95 px-2 py-2 shadow-soft backdrop-blur lg:hidden">
<div class="grid grid-cols-5 gap-2 text-center text-xs font-black">
    <a href="<?= app_url('admin/dashboard.php') ?>" class="rounded-2xl px-2 py-3 <?= active_class('dashboard.php') ?>">Home</a>
    <a href="<?= app_url('admin/bookings.php') ?>" class="rounded-2xl px-2 py-3 <?= active_class('bookings.php') ?>">Bookings</a>
    <a href="<?= app_url('admin/slots.php') ?>" class="rounded-2xl px-2 py-3 <?= active_class('slots.php') ?>">Slots</a>
    <a href="<?= app_url('admin/services.php') ?>" class="rounded-2xl px-2 py-3 <?= active_class('services.php') ?>">Services</a>
    <a href="<?= app_url('admin/settings.php') ?>" class="rounded-2xl px-2 py-3 <?= active_class('settings.php') ?>">More</a>
</div>
</nav>