<?php
declare(strict_types=1);

$currentPage = $currentPage ?? '';
$admin = current_user();
$displayName = trim((string) (($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? '')));
$initials = strtoupper(substr((string) ($admin['prenom'] ?? 'A'), 0, 1) . substr((string) ($admin['nom'] ?? 'Q'), 0, 1));
?>
<aside class="user-sidebar admin-sidebar">
    <div class="sidebar-panel admin-sidebar-brand">
        <span class="brand-mark">Q</span>
        <div>
            <span class="sidebar-label">QueueLess</span>
            <h2><?= e($displayName !== '' ? $displayName : 'Admin QueueLess'); ?></h2>
        </div>
    </div>

    <nav class="sidebar-menu">
        <a class="sidebar-link <?= nav_is_active('admin_dashboard', $currentPage); ?>" href="<?= url('admin/dashboard.php'); ?>">
            <span class="nav-icon">D</span>
            <span>Dashboard</span>
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_users', $currentPage); ?>" href="<?= url('admin/users.php'); ?>">
            <span class="nav-icon">U</span>
            <span>Utilisateurs</span>
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_services', $currentPage); ?>" href="<?= url('admin/services.php'); ?>">
            <span class="nav-icon">S</span>
            <span>Services</span>
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_reviews', $currentPage); ?>" href="<?= url('admin/reviews.php'); ?>">
            <span class="nav-icon">A</span>
            <span>Avis</span>
        </a>
        <a class="sidebar-link <?= nav_is_active('admin_stats', $currentPage); ?>" href="<?= url('admin/stats.php'); ?>">
            <span class="nav-icon">T</span>
            <span>Statistiques</span>
        </a>
        <a class="sidebar-link danger" href="<?= url('actions/logout.php'); ?>">
            <span class="nav-icon">X</span>
            <span>Deconnexion</span>
        </a>
    </nav>

    <div class="admin-sidebar-footer">
        <span class="admin-avatar"><?= e($initials); ?></span>
        <div>
            <strong>Admin</strong>
            <p>Connecte</p>
        </div>
    </div>
</aside>
