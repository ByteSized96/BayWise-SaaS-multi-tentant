<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/auth.php';
require_once __DIR__ . '/../app/Core/csrf.php';
require_once __DIR__ . '/../app/Core/database.php';

if (is_logged_in()) {
    header('Location: ' . (is_admin() ? app_url('admin/dashboard.php') : app_url('customer/dashboard.php')));
    exit;
}

$slug = trim($_GET['garage'] ?? $_POST['garage_slug'] ?? '');

if ($slug !== '') {
    $garageStmt = $pdo->prepare("SELECT * FROM garages WHERE slug = ? LIMIT 1");
    $garageStmt->execute([$slug]);
    $garage = $garageStmt->fetch();
} else {
    $garage = null;
}

$pageTitle = 'Login';
$metaTitle = 'Login | ' . ($garage['name'] ?? 'BayWise Portal');
$metaDescription = 'Login as a customer or garage admin.';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($garage) {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM users 
            WHERE email = ?
            AND garage_id = ?
            LIMIT 1
        ");
        $stmt->execute([$email, (int)$garage['id']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM users 
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
    }

    $foundUser = $stmt->fetch();

    if ($foundUser && password_verify($password, $foundUser['password_hash'])) {
        $_SESSION['user'] = [
            'id' => (int)$foundUser['id'],
            'garage_id' => (int)($foundUser['garage_id'] ?? 1),
            'name' => $foundUser['name'],
            'email' => $foundUser['email'],
            'role' => $foundUser['role'],
        ];

        header('Location: ' . ($foundUser['role'] === 'admin' ? app_url('admin/dashboard.php') : app_url('customer/dashboard.php')));
        exit;
    }

    $error = 'Invalid email or password.';
}

require_once __DIR__ . '/../app/Layout/header.php';
?>

<section class="relative flex min-h-[78vh] items-center justify-center overflow-hidden py-8">
    <div class="absolute -left-24 top-10 h-72 w-72 rounded-full bg-bay-pink/30 blur-3xl"></div>
    <div class="absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-bay-primary/30 blur-3xl"></div>

    <div class="relative grid w-full max-w-5xl overflow-hidden rounded-[2.5rem] bg-white/80 shadow-soft backdrop-blur lg:grid-cols-[0.9fr_1.1fr]">
        <div class="hidden bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-8 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <p class="inline-flex rounded-full bg-white/20 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] backdrop-blur">
                    <?= h($garage['name'] ?? 'BayWise Portal') ?>
                </p>

                <h1 class="mt-6 text-4xl font-black tracking-tight">
                    Garage booking and repair tracking, all in one place.
                </h1>

                <p class="mt-4 text-sm leading-7 text-white/80">
                    Customers can book services and track repair progress, while garages manage bookings, slots and updates.
                </p>
            </div>

            <div class="grid gap-3">
                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-sm font-black">Customer portal</p>
                    <p class="mt-1 text-xs font-bold text-white/75">Book services and follow repair progress.</p>
                </div>

                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-sm font-black">Garage admin</p>
                    <p class="mt-1 text-xs font-bold text-white/75">Manage bookings, services and calendar slots.</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <p class="mb-3 inline-flex rounded-full bg-bay-primarySoft px-3 py-1 text-xs font-black uppercase tracking-wide text-bay-primary">
                Secure login
            </p>

            <h2 class="text-3xl font-black">Welcome</h2>

            <p class="mt-2 text-sm text-bay-muted">
                <?php if ($garage): ?>
                    Login to <?= h($garage['name']) ?>.
                <?php else: ?>
                    Login as a customer or garage admin.
                <?php endif; ?>
            </p>

            <?php if ($error): ?>
                <div class="mt-5 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= app_url('login.php' . ($garage ? '?garage=' . urlencode($garage['slug']) : '')) ?>" class="mt-5 space-y-4">
                <?= csrf_field() ?>

                <?php if ($garage): ?>
                    <input type="hidden" name="garage_slug" value="<?= h($garage['slug']) ?>">
                <?php endif; ?>

                <div>
                    <label class="mb-1 block text-sm font-bold">Email</label>
                    <input name="email" type="email" required
                           class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-bold">Password</label>
                    <input name="password" type="password" required
                           class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                </div>

                <button class="w-full rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
                    Login
                </button>
            </form>

            <?php if (!$garage): ?>
                <div class="mt-5 rounded-2xl bg-bay-blueSoft p-4 text-sm font-bold text-slate-600">
                    Demo admin: <span class="text-bay-ink">admin@baywise.test</span> / <span class="text-bay-ink">password</span>
                </div>
            <?php endif; ?>

            <p class="mt-5 text-center text-sm text-bay-muted">
                Need a customer account?
                <a href="<?= app_url('register.php' . ($garage ? '?garage=' . urlencode($garage['slug']) : '')) ?>" class="font-black text-bay-primary">Register</a>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../app/Layout/footer.php'; ?>