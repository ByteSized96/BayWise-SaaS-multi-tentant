</main>

<?php if (!is_logged_in()): ?>
    <footer class="mx-auto max-w-7xl px-4 pb-8">
        <div class="rounded-[2rem] bg-slate-900 p-6 text-white shadow-soft">
            <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr_0.7fr]">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">BayWise Portal</p>
                    <h2 class="mt-2 text-2xl font-black">Garage booking made cleaner.</h2>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">
                        A modern mobile-first garage booking and repair tracking platform built with PHP, MySQL and Tailwind CSS.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-black">Portal</h3>
                    <div class="mt-3 grid gap-2 text-sm text-slate-300">
                        <a href="<?= app_url('register.php') ?>" class="hover:text-white">Create account</a>
                        <a href="<?= app_url('login.php') ?>" class="hover:text-white">Login</a>
                        <a href="<?= app_url('index.php') ?>" class="hover:text-white">Home</a>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black">Demo</h3>
                    <p class="mt-3 rounded-2xl bg-white/10 p-3 text-xs font-bold leading-5 text-slate-300">
                        Admin login:<br>
                        admin@baywise.test<br>
                        password
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-2 border-t border-white/10 pt-4 text-xs font-bold text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                <p>© <?= date('Y') ?> BayWise Portal. Portfolio showcase project.</p>
                <p>Built for garages, mechanics and service teams.</p>
            </div>
        </div>
    </footer>
<?php else: ?>
    <footer class="mx-auto hidden max-w-7xl px-4 pb-6 lg:block">
        <div class="flex items-center justify-between rounded-[1.5rem] bg-white px-5 py-4 text-sm font-bold text-bay-muted shadow-soft">
            <p>© <?= date('Y') ?> BayWise Portal</p>
            <p><?= is_admin() ? 'Garage admin workspace' : 'Customer repair tracking portal' ?></p>
        </div>
    </footer>
<?php endif; ?>

</div>

<?php if (is_admin()): ?>
    <?php require __DIR__ . '/admin-nav.php'; ?>
<?php elseif (is_customer()): ?>
    <?php require __DIR__ . '/customer-nav.php'; ?>
<?php endif; ?>

</body>
</html>