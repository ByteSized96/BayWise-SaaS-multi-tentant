<?php
declare(strict_types=1);

// Turn OFF display (never show users errors)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Turn ON logging
ini_set('log_errors', '1');

// Set log file path
ini_set('error_log', __DIR__ . '/../../storage/logs/app.log');
require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/auth.php';
require_once __DIR__ . '/../app/Core/csrf.php';
require_once __DIR__ . '/../app/Core/database.php';

if (is_logged_in()) {
    header('Location: ' . (is_admin() ? app_url('admin/dashboard.php') : app_url('customer/dashboard.php')));
    exit;
}

$pageTitle = 'Create Garage Portal';
$error = '';

function make_slug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    return $slug ?: 'garage';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $garageName = trim($_POST['garage_name'] ?? '');
    $garageEmail = strtolower(trim($_POST['garage_email'] ?? ''));
    $garagePhone = trim($_POST['garage_phone'] ?? '');

    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail = strtolower(trim($_POST['admin_email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($garageName === '' || $garageEmail === '' || $adminName === '' || $adminEmail === '' || strlen($password) < 6) {
        $error = 'Please complete all required fields. Password must be at least 6 characters.';
    } else {
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkEmail->execute([$adminEmail]);

        if ($checkEmail->fetch()) {
            $error = 'An account already exists with that admin email.';
        } else {
            $baseSlug = make_slug($garageName);
            $slug = $baseSlug;
            $counter = 2;

            while (true) {
                $checkSlug = $pdo->prepare("SELECT id FROM garages WHERE slug = ? LIMIT 1");
                $checkSlug->execute([$slug]);

                if (!$checkSlug->fetch()) {
                    break;
                }

                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            try {
                $pdo->beginTransaction();

                $garageStmt = $pdo->prepare("
                    INSERT INTO garages (name, slug, email, phone)
                    VALUES (?, ?, ?, ?)
                ");
                $garageStmt->execute([$garageName, $slug, $garageEmail, $garagePhone]);

                $garageId = (int)$pdo->lastInsertId();

                $userStmt = $pdo->prepare("
                    INSERT INTO users (garage_id, name, email, password_hash, role)
                    VALUES (?, ?, ?, ?, 'admin')
                ");
                $userStmt->execute([
                    $garageId,
                    $adminName,
                    $adminEmail,
                    password_hash($password, PASSWORD_DEFAULT),
                ]);

                $userId = (int)$pdo->lastInsertId();

                $settingsStmt = $pdo->prepare("
                    INSERT INTO site_settings (garage_id, setting_key, setting_value)
                    VALUES (?, ?, ?)
                ");

                $defaultSettings = [
                    'brand_name' => $garageName,
                    'brand_tagline' => 'Book services, track repairs, and stay updated online.',
                    'brand_intro' => 'A modern online portal for booking garage services and tracking repair progress.',
                    'hero_image' => 'assets/img/garage-hero.jpg',
                    'primary_cta' => 'Create customer account',
                ];

                foreach ($defaultSettings as $key => $value) {
                    $settingsStmt->execute([$garageId, $key, $value]);
                }

                $serviceStmt = $pdo->prepare("
                    INSERT INTO services (garage_id, name, description, duration_minutes, base_price)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $defaultServices = [
                    ['MOT Check', 'Basic MOT preparation and safety inspection.', 60, 49.99],
                    ['Full Service', 'Oil, filters, fluid checks and general inspection.', 120, 189.99],
                    ['Brake Inspection', 'Brake pads, discs, brake fluid and safety check.', 60, 59.99],
                    ['Diagnostic Check', 'Fault code scan and vehicle issue investigation.', 60, 69.99],
                ];

                foreach ($defaultServices as $service) {
                    $serviceStmt->execute([
                        $garageId,
                        $service[0],
                        $service[1],
                        $service[2],
                        $service[3],
                    ]);
                }

                $slotStmt = $pdo->prepare("
                    INSERT INTO calendar_slots (garage_id, slot_date, start_time, end_time, capacity)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $slotStmt->execute([$garageId, date('Y-m-d', strtotime('+1 day')), '09:00:00', '10:00:00', 1]);
                $slotStmt->execute([$garageId, date('Y-m-d', strtotime('+1 day')), '11:00:00', '12:00:00', 1]);
                $slotStmt->execute([$garageId, date('Y-m-d', strtotime('+2 days')), '14:00:00', '15:00:00', 1]);

                $pdo->commit();

                $_SESSION['user'] = [
                    'id' => $userId,
                    'garage_id' => $garageId,
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'role' => 'admin',
                ];

                flash('success', 'Garage portal created successfully. Welcome to your admin dashboard.');

                header('Location: ' . app_url('admin/dashboard.php'));
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                app_log('Garage onboarding failed', [
                    'error' => $e->getMessage(),
                    'garage_name' => $garageName,
                    'admin_email' => $adminEmail,
                ]);

                $error = 'Something went wrong while creating your garage portal.';
            }
        }
    }
}

require_once __DIR__ . '/../app/Layout/header.php';
?>

<section class="relative flex min-h-[78vh] items-center justify-center overflow-hidden py-8">
    <div class="absolute -left-24 top-10 h-72 w-72 rounded-full bg-bay-purple/30 blur-3xl"></div>
    <div class="absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-bay-pink/30 blur-3xl"></div>

    <div class="relative grid w-full max-w-6xl overflow-hidden rounded-[2.5rem] bg-white/80 shadow-soft backdrop-blur lg:grid-cols-[0.9fr_1.1fr]">
        <div class="hidden bg-gradient-to-br from-bay-primary via-bay-purple to-bay-pink p-8 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <p class="inline-flex rounded-full bg-white/20 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] backdrop-blur">
                    SaaS onboarding
                </p>

                <h1 class="mt-6 text-4xl font-black tracking-tight">
                    Create a garage booking portal in minutes.
                </h1>

                <p class="mt-4 text-sm leading-7 text-white/80">
                    This creates a garage workspace, admin account, default services, demo slots and editable branding.
                </p>
            </div>

            <div class="grid gap-3">
                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-sm font-black">Multi-garage ready</p>
                    <p class="mt-1 text-xs font-bold text-white/75">Each garage gets isolated services, slots and bookings.</p>
                </div>

                <div class="rounded-2xl bg-white/20 p-4 backdrop-blur">
                    <p class="text-sm font-black">Admin created instantly</p>
                    <p class="mt-1 text-xs font-bold text-white/75">The garage owner lands straight inside their dashboard.</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <p class="mb-3 inline-flex rounded-full bg-bay-primarySoft px-3 py-1 text-xs font-black uppercase tracking-wide text-bay-primary">
                Start your portal
            </p>

            <h2 class="text-3xl font-black">Create garage account</h2>
            <p class="mt-2 text-sm text-bay-muted">Set up the garage workspace and first admin user.</p>

            <?php if ($error): ?>
                <div class="mt-5 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= app_url('register-garage.php') ?>" class="mt-5 space-y-5">
                <?= csrf_field() ?>

                <div class="rounded-2xl bg-bay-blueSoft p-4">
                    <p class="mb-3 text-sm font-black">Garage details</p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-bold">Garage name</label>
                            <input name="garage_name" required
                                   class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold">Garage email</label>
                            <input name="garage_email" type="email" required
                                   class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold">Garage phone</label>
                            <input name="garage_phone"
                                   class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-bay-purpleSoft p-4">
                    <p class="mb-3 text-sm font-black">Admin account</p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-bold">Your name</label>
                            <input name="admin_name" required
                                   class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold">Admin email</label>
                            <input name="admin_email" type="email" required
                                   class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold">Password</label>
                            <input name="password" type="password" required
                                   class="w-full rounded-2xl border border-bay-line bg-white/90 px-4 py-3 outline-none focus:border-bay-primary focus:ring-4 focus:ring-bay-primarySoft">
                        </div>
                    </div>
                </div>

                <button class="w-full rounded-2xl bg-gradient-to-br from-bay-primary to-bay-purple px-5 py-4 font-black text-white shadow-soft">
                    Create my garage portal
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-bay-muted">
                Already have an account?
                <a href="<?= app_url('login.php') ?>" class="font-black text-bay-primary">Login</a>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../app/Layout/footer.php'; ?>