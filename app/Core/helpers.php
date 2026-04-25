<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money_gbp(float|int|string|null $amount): string
{
    return '£' . number_format((float)$amount, 2);
}

function app_url(string $path = ''): string
{
    $config = require __DIR__ . '/../../config/app.php';

    return rtrim($config['app_url'], '/') . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return app_url('assets/' . ltrim($path, '/'));
}

function current_page(): string
{
    return basename($_SERVER['SCRIPT_NAME']);
}

function active_class(string $page): string
{
    return current_page() === $page
        ? 'bg-slate-900 text-white'
        : 'text-slate-500';
}


function status_badge(string $status): string
{
    return match ($status) {
        'Requested' => 'bg-bay-blueSoft text-blue-600',
        'Confirmed' => 'bg-bay-purpleSoft text-purple-600',
        'Inspection' => 'bg-bay-amberSoft text-amber-600',
        'In Progress' => 'bg-bay-primarySoft text-indigo-600',
        'Completed' => 'bg-bay-greenSoft text-green-600',
        'Cancelled' => 'bg-red-50 text-red-600',
        default => 'bg-slate-100 text-slate-600',
    };
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $value;
}
function setting(PDO $pdo, string $key, string $fallback = ''): string
{
    static $settingsByGarage = [];

    $garageId = garage_id();

    if (!isset($settingsByGarage[$garageId])) {
        $settingsByGarage[$garageId] = [];

        try {
            $stmt = $pdo->prepare("
                SELECT setting_key, setting_value 
                FROM site_settings
                WHERE garage_id = ?
            ");
            $stmt->execute([$garageId]);

            foreach ($stmt->fetchAll() as $row) {
                $settingsByGarage[$garageId][$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            return $fallback;
        }
    }

    return $settingsByGarage[$garageId][$key] ?? $fallback;
}
function garage_id(): int
{
    return (int)($_SESSION['user']['garage_id'] ?? 1);
}

function garage(PDO $pdo): ?array
{
    static $garage = null;

    if ($garage !== null) {
        return $garage;
    }

    $stmt = $pdo->prepare("SELECT * FROM garages WHERE id = ? LIMIT 1");
    $stmt->execute([garage_id()]);
    $garage = $stmt->fetch();

    return $garage ?: null;
}
function app_log(string $message, array $context = []): void
{
    $logFile = __DIR__ . '/../../storage/logs/app.log';

    // Make sure directory exists
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $entry = [
        'time' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
    ];

    error_log(json_encode($entry) . PHP_EOL, 3, $logFile);
}
?>