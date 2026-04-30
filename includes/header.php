<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'QueueLess simplifie la reservation de creneaux horaires.';
$currentPage = $currentPage ?? '';
$bodyClass = $bodyClass ?? '';
$extraCss = $extraCss ?? [];
$flashMessage = get_flash_message();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription); ?>">
    <title><?= e($pageTitle); ?> | <?= e(APP_NAME); ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css'); ?>">
    <?php foreach ($extraCss as $cssFile): ?>
        <link rel="stylesheet" href="<?= asset('css/' . ltrim($cssFile, '/')); ?>">
    <?php endforeach; ?>
</head>
<body class="<?= e($bodyClass); ?>">
    <?php include __DIR__ . '/navbar.php'; ?>

    <?php if ($flashMessage): ?>
        <div class="container flash-wrapper">
            <div class="alert alert-<?= e($flashMessage['type']); ?>">
                <span><?= e($flashMessage['message']); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <main>

