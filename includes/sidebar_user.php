<?php
declare(strict_types=1);


$currentPage = $currentPage ?? '';
$user = current_user();
$displayName = trim((string) (($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')));
?>
<aside class="user-sidebar">
    <div class="sidebar-panel">
        <span class="sidebar-label">Espace utilisateur</span>
        <h2><?= e($displayName !== '' ? $displayName : 'Utilisateur QueueLess'); ?></h2>
        <p>Accedez rapidement a vos reservations, a votre profil et a votre historique.</p>
    </div>

    <nav class="sidebar-menu">
        <a class="sidebar-link <?= nav_is_active('dashboard', $currentPage); ?>" href="<?= url('pages/user_dashboard.php'); ?>">
            Dashboard
        </a>
        <a class="sidebar-link <?= nav_is_active('profile', $currentPage); ?>" href="<?= url('pages/profile.php'); ?>">
            Profil
        </a>
        <a class="sidebar-link <?= nav_is_active('reservations', $currentPage); ?>" href="<?= url('pages/my_reservations.php'); ?>">
            Mes reservations
        </a>
        <a class="sidebar-link" href="<?= url('pages/services.php'); ?>">
            Services
        </a>
        <a class="sidebar-link danger" href="<?= url('actions/logout.php'); ?>">
            Deconnexion
        </a>
    </nav>
</aside>

