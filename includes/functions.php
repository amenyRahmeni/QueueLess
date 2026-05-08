<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    if ($path === '') {
        return APP_URL !== '' ? APP_URL . '/' : '/';
    }

    return (APP_URL !== '' ? APP_URL : '') . '/' . $path;
}

function asset(string $path = ''): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    $location = preg_match('#^https?://#i', $path) ? $path : url($path);
    header('Location: ' . $location);
    exit;
}

function set_flash_message(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash_message(): ?array
{
    if (empty($_SESSION['flash_message'])) {
        return null;
    }

    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $flashMessage;
}

function set_old_input(array $input): void
{
    $_SESSION['old_input'] = $input;
}

function old(string $key, string $default = ''): string
{
    return $_SESSION['old_input'][$key] ?? $default;
}

function clear_old_input(): void
{
    unset($_SESSION['old_input']);
}

function is_post_request(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? 'user') === 'admin';
}

function is_service_owner(): bool
{
    return (current_user()['role'] ?? 'user') === 'owner';
}

function user_dashboard_path(): string
{
    $role = current_user()['role'] ?? 'user';

    if ($role === 'admin') {
        return 'admin/dashboard.php';
    }

    if ($role === 'owner') {
        return 'owner/slots.php';
    }

    return 'pages/user_dashboard.php';
}

function current_user_owns_service(PDO $pdo, int $serviceId): bool
{
    $userId = (int) (current_user()['id'] ?? 0);

    if ($userId <= 0 || $serviceId <= 0) {
        return false;
    }

    $statement = $pdo->prepare('SELECT COUNT(*) FROM services WHERE id = :id AND owner_id = :owner_id');
    $statement->execute([
        'id' => $serviceId,
        'owner_id' => $userId,
    ]);

    return (int) $statement->fetchColumn() > 0;
}

function nav_is_active(string $expectedPage, string $currentPage): string
{
    return $expectedPage === $currentPage ? 'is-active' : '';
}

function format_datetime(?string $value, string $format = 'd/m/Y H:i'): string
{
    if (empty($value)) {
        return '-';
    }

    try {
        return (new DateTime($value))->format($format);
    } catch (Throwable $exception) {
        return (string) $value;
    }
}

function service_image_url(?string $imagePath): string
{
    if (!empty($imagePath)) {
        if (preg_match('#^(?:https?://|data:)#i', $imagePath)) {
            return $imagePath;
        }

        return UPLOADS_URL . '/' . ltrim($imagePath, '/');
    }

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 400">
  <rect width="640" height="400" fill="#e8f1f1"/>
  <circle cx="520" cy="98" r="48" fill="#8db0b3"/>
  <rect x="58" y="124" width="316" height="26" rx="13" fill="#1f5057"/>
  <rect x="58" y="170" width="244" height="18" rx="9" fill="#6a8f94"/>
  <rect x="58" y="202" width="192" height="18" rx="9" fill="#6a8f94"/>
  <rect x="58" y="274" width="162" height="44" rx="22" fill="#122f35"/>
  <text x="58" y="88" fill="#1f5057" font-size="34" font-family="Trebuchet MS, sans-serif">QueueLess</text>
</svg>
SVG;

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function reservation_status_label(string $status): string
{
    $labels = [
        'reservee' => 'Reservee',
        'annulee' => 'Annulee',
        'terminee' => 'Terminee',
        'disponible' => 'Disponible',
        'occupe' => 'Occupe',
    ];

    return $labels[$status] ?? ucfirst($status);
}

function account_status_label(string $status): string
{
    $labels = [
        'en_attente' => 'En attente',
        'actif' => 'Accepte',
        'refuse' => 'Refuse',
    ];

    return $labels[$status] ?? ucfirst($status);
}

function badge_class_for(string $status): string
{
    $status = strtolower($status);

    if (in_array($status, ['reservee', 'disponible', 'actif', 'success'], true)) {
        return 'status-badge success';
    }

    if (in_array($status, ['annulee', 'inactif', 'refuse', 'danger'], true)) {
        return 'status-badge danger';
    }

    if (in_array($status, ['terminee', 'en_attente', 'warning'], true)) {
        return 'status-badge warning';
    }

    if (in_array($status, ['occupe', 'muted'], true)) {
        return 'status-badge muted';
    }

    return 'status-badge';
}

function upload_service_image(array $file): ?string
{
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Le televersement de l image a echoue.');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Extension d image non autorisee. Utilisez JPG, PNG ou WEBP.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $mimeType = function_exists('mime_content_type') ? (string) mime_content_type($tmpName) : '';
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if ($mimeType !== '' && !in_array($mimeType, $allowedMimeTypes, true)) {
        throw new RuntimeException('Le fichier televerse n est pas une image valide.');
    }

    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0777, true);
    }

    $fileName = 'service_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = UPLOADS_DIR . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Impossible d enregistrer l image televersee.');
    }

    return $fileName;
}
