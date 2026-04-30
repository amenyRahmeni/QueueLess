<?php
declare(strict_types=1);

$currentPage = $currentPage ?? '';
$admin = current_user();
$displayName = trim((string) (($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? '')));
?>
<aside class="user-sidebar">
    <div class="sidebar-panel">
        <span class="sidebar-label">Administration</span>
        <h2><?= e($displayName !== '' ? $displayName : 'Admin QueueLess'); ?></h2>
        <p>Gerez les services, les creneaux, les reservations et les statistiques depuis un seul espace.</p>
    </div>

    <nav class="sidebar-menu">
        <a class="sidebar-link <?= nav_is_active('admin_dashboard', $currentPage); ?>" href="<?= url('admin/dashboard.php'); ?>">
            Dashboard
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_users', $currentPage); ?>" href="<?= url('admin/users.php'); ?>">
            Utilisateurs
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_services', $currentPage); ?>" href="<?= url('admin/services.php'); ?>">
            Services
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_slots', $currentPage); ?>" href="<?= url('admin/slots.php'); ?>">
            Creneaux
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_reservations', $currentPage); ?>" href="<?= url('admin/reservations.php'); ?>">
            Reservations
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_reviews', $currentPage); ?>" href="<?= url('admin/reviews.php'); ?>">
            Avis
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_stats', $currentPage); ?>" href="<?= url('admin/stats.php'); ?>">
            Statistiques
        </a>
        <a class="sidebar-link danger" href="<?= url('actions/logout.php'); ?>">
            Deconnexion
        </a>
    </nav>
</aside>
