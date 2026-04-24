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
    $garageStmt = $pdo->prepare("SELECT * FROM garages WHERE id = 1 LIMIT 1");
    $garageStmt->execute();
    $garage = $garageStmt->fetch();
}

if (!$garage) {
    exit('Garage portal not found.');
}

$garageId = (int)$garage['id'];

$pageTitle = 'Create Account';
$metaTitle = 'Create Account | ' . $garage['name'];
$metaDescription = 'Create a customer account to book services and track repair progress online.';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($name === '' || $email === '' || strlen($password) < 6) {
        $error = 'Please enter your name, email and a password with at least 6 characters.';
    } else {
        $check = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE email = ? 
            AND garage_id = ?
            LIMIT 1
        ");
        $check->execute([$email, $garageId]);

        if ($check->fetch()) {
            $error = 'An account already exists with that email for this garage.';
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    INSERT INTO users (garage_id, name, email, password_hash, role)
                    VALUES (?, ?, ?, ?, 'customer')
                ");
                $stmt->execute([
                    $garageId,
                    $name,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                ]);

                $userId = (int)$pdo->lastInsertId();

                $customer = $pdo->prepare("
                    INSERT INTO customers (garage_id, user_id, name, email, phone)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $customer->execute([$garageId, $userId, $name, $email, $phone]);

                $pdo->commit();

                $_SESSION['user'] = [
                    'id' => $userId,
                    'garage_id' => $garageId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'customer',
                ];

                header('Location: ' . app_url('customer/dashboard.php'));
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Something went wrong while creating your account.';
            }
        }
    }
}

require_once __DIR__ . '/../app/Layout/header.php';
?>

<section class="relative flex min-h-[78vh] items-center justify-center overflow-hidden py-8">
    <div class="absolute -left-24 top-10 h-72 w-72 rounded-full bg-bay-purple/30 blur-3xl"></div>
    <div class="absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-bay-pink/30 blur-3xl"></div>

    <div class="relative grid w-full max-w-5xl overflow-hidden rounded-[2.5rem] bg-white/80 shadow-soft backdrop-blur lg:grid-cols-[0.9fr_1.1fr]">

        <div class="hidden bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-8 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <p class="inline-flex rounded-full bg-white/20 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] backdrop-blur">
                    <?= h($garage['name']) ?>
                </p>

                <h1 class="mt-6 text-4xl font-black tracking-tight">
                    Start booking and tracking your vehicle repairs.
                </h1>

                <p class="mt-4 text-sm leading-7 text-white/80">
                    Create your account, add your vehicle, and manage everything from your phone.
                </p>
            </div>

            <div class="grid gap-3">
                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-sm font-black">Quick booking</p>
                    <p class="mt-1 text-xs font-bold text-white/75">Choose a service and time slot instantly.</p>
                </div>

                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-sm font-black">Live updates</p>
                    <p class="mt-1 text-xs font-bold text-white/75">Track repair progress without calling.</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <p class="mb-3 inline-flex rounded-full bg-bay-primarySoft px-3 py-1 text-xs font-black uppercase tracking-wide text-bay-primary">
                Create account
            </p>

            <h2 class="text-3xl font-black">Get started</h2>
            <p class="mt-2 text-sm text-bay-muted">
                Create your account with <?= h($garage['name']) ?> to book services and track repair progress.
            </p>

            <?php if ($error): ?>
                <div class="mt-5 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="mt-5 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="garage_slug" value="<?= h($garage['slug']) ?>">

                <div>
                    <label class="mb-1 block text-sm font-bold">Full name</label>
                    <input name="name" required
                           class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-bold">Email</label>
                    <input name="email" type="email" required
                           class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-bold">Phone</label>
                    <input name="phone"
                           class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-bold">Password</label>
                    <input name="password" type="password" required
                           class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                </div>

                <button class="w-full rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-3 font-black text-white shadow-soft">
                    Create account
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-bay-muted">
                Already have an account?
                <a href="<?= app_url('login.php?garage=' . urlencode($garage['slug'])) ?>" class="font-black text-bay-primary">Login</a>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../app/Layout/footer.php'; ?>