<?php
declare(strict_types=1);


$currentPage = $currentPage ?? '';
$owner = current_user();
$displayName = trim((string) (($owner['prenom'] ?? '') . ' ' . ($owner['nom'] ?? '')));
$initials = strtoupper(substr((string) ($owner['prenom'] ?? 'S'), 0, 1) . substr((string) ($owner['nom'] ?? 'O'), 0, 1));
?>
<aside class="user-sidebar admin-sidebar">
    <div class="sidebar-panel admin-sidebar-brand">
        <span class="brand-mark">Q</span>
        <div>
            <span class="sidebar-label">Espace service</span>
            <h2><?= e($displayName !== '' ? $displayName : 'Proprietaire'); ?></h2>
        </div>
    </div>

    <nav class="sidebar-menu">
        <a class="sidebar-link <?= nav_is_active('owner_services', $currentPage); ?>" href="<?= url('owner/services.php'); ?>">
            <span class="nav-icon">S</span>
            <span>Mes services</span>
        </a>
        <a class="sidebar-link <?= nav_is_active('owner_slots', $currentPage); ?>" href="<?= url('owner/slots.php'); ?>">
            <span class="nav-icon">C</span>
            <span>Mes creneaux</span>
        </a>
        <a class="sidebar-link <?= nav_is_active('owner_reservations', $currentPage); ?>" href="<?= url('owner/reservations.php'); ?>">
            <span class="nav-icon">R</span>
            <span>Reservations</span>
        </a>
        <a class="sidebar-link danger" href="<?= url('actions/logout.php'); ?>">
            <span class="nav-icon">X</span>
            <span>Deconnexion</span>
        </a>
    </nav>

    <div class="admin-sidebar-footer">
        <span class="admin-avatar"><?= e($initials); ?></span>
        <div>
            <strong>Proprietaire</strong>
            <p>Connecte</p>
        </div>
    </div>
</aside>
