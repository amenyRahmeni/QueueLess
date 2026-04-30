<?php
declare(strict_types=1);

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));

$sessionPath = BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';

if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}

if (session_status() === PHP_SESSION_NONE) {
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }

    session_start();
}

date_default_timezone_set('Africa/Tunis');

defined('APP_NAME') || define('APP_NAME', 'QueueLess');
defined('APP_ENV') || define('APP_ENV', 'local');
defined('APP_DEBUG') || define('APP_DEBUG', true);
defined('SESSION_PATH') || define('SESSION_PATH', $sessionPath);

$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
$projectPath = realpath(BASE_PATH);
$baseUrl = '/queueless';

if ($documentRoot && $projectPath) {
    $normalizedProject = str_replace('\\', '/', $projectPath);
    $normalizedRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');

    if (strpos(strtolower($normalizedProject), strtolower($normalizedRoot)) === 0) {
        $relativePath = substr($normalizedProject, strlen($normalizedRoot));
        $relativePath = trim((string) $relativePath, '/');
        $baseUrl = $relativePath === '' ? '' : '/' . $relativePath;
    }
}

defined('APP_URL') || define('APP_URL', $baseUrl);
defined('UPLOADS_DIR') || define('UPLOADS_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads');
defined('UPLOADS_URL') || define('UPLOADS_URL', (APP_URL !== '' ? APP_URL : '') . '/assets/uploads');
