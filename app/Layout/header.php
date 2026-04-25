<?php
$config = require __DIR__ . '/../../config/app.php';

$siteName = $siteName ?? $config['app_name'];
$pageTitle = $pageTitle ?? $siteName;
$metaTitle = $metaTitle ?? $pageTitle . ' | ' . $siteName;
$metaDescription = $metaDescription ?? 'BayWise Portal is a modern garage booking and repair tracking platform for customers and mechanics.';
$metaKeywords = $metaKeywords ?? 'garage booking system, vehicle service booking, repair tracking portal, garage CRM, mechanic booking software';
$canonicalUrl = $canonicalUrl ?? app_url(ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$ogImage = $ogImage ?? app_url('assets/img/garage-hero.jpg');

$structuredData = $structuredData ?? [
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => $siteName,
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem' => 'Web',
    'description' => $metaDescription,
    'url' => app_url('index.php'),
];
?>
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <title><?= h($metaTitle) ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= h($metaDescription) ?>">
    <meta name="keywords" content="<?= h($metaKeywords) ?>">
    <meta name="robots" content="<?= h($robots ?? 'index, follow') ?>">
    <meta name="author" content="<?= h($siteName) ?>">
    <meta name="theme-color" content="#111827">

    <link rel="canonical" href="<?= h($canonicalUrl) ?>">

    <meta property="og:type" content="<?= h($ogType ?? 'website') ?>">
    <meta property="og:title" content="<?= h($metaTitle) ?>">
    <meta property="og:description" content="<?= h($metaDescription) ?>">
    <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= h($siteName) ?>">
    <meta property="og:image" content="<?= h($ogImage) ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= h($metaTitle) ?>">
    <meta name="twitter:description" content="<?= h($metaDescription) ?>">
    <meta name="twitter:image" content="<?= h($ogImage) ?>">

    <script type="application/ld+json">
        <?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
colors: {
    bay: {
        bg: '#F8FAFC',        // soft background
        ink: '#1E293B',       // softer dark (not pure black)
        muted: '#64748B',     // slate muted
        line: '#E2E8F0',
        card: '#FFFFFF',

        // 🎨 pastel accents
        primary: '#7C8CFF',   // soft indigo
        primarySoft: '#EEF2FF',

        pink: '#F9A8D4',
        pinkSoft: '#FDF2F8',

        blue: '#93C5FD',
        blueSoft: '#EFF6FF',

        green: '#86EFAC',
        greenSoft: '#F0FDF4',

        amber: '#FCD34D',
        amberSoft: '#FFFBEB',

        purple: '#C4B5FD',
        purpleSoft: '#F5F3FF'
    }
},
                    boxShadow: {
                        soft: '0 18px 40px rgba(15, 23, 42, 0.08)'
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-gradient-to-br from-bay-blueSoft via-bay-pinkSoft to-bay-purpleSoft text-bay-ink">
<div class="min-h-screen pb-24 lg:pb-0 lg:pl-72">

<header class="sticky top-0 z-40 border-b border-bay-line bg-white/90 backdrop-blur lg:hidden">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
        <a href="<?= app_url('index.php') ?>" aria-label="<?= h($siteName) ?> homepage">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-bay-muted"><?= h($siteName) ?></p>
            <h1 class="text-lg font-black tracking-tight"><?= h($pageTitle) ?></h1>
        </a>

        <?php if (is_logged_in()): ?>
            <a href="<?= app_url('logout.php') ?>" class="rounded-full bg-gradient-to-br from-bay-primary to-bay-purple text-white px-4 py-2 text-sm font-black text-white">
                Logout
            </a>
        <?php else: ?>
            <a href="<?= app_url('login.php') ?>" class="rounded-full bg-gradient-to-br from-bay-primary to-bay-purple text-white px-4 py-2 text-sm font-black text-white">
                Login
            </a>
        <?php endif; ?>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 py-5">
    <?php if ($success = flash('success')): ?>
        <div class="mb-5 rounded-2xl bg-green-50 p-4 text-sm font-black text-green-700">
            <?= h($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error = flash('error')): ?>
        <div class="mb-5 rounded-2xl bg-red-50 p-4 text-sm font-black text-red-700">
            <?= h($error) ?>
        </div>
    <?php endif; ?>