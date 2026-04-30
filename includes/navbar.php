<?php
declare(strict_types=1);

$user = current_user();
?>
<header class="site-header">
    <div class="container navbar">
        <a class="logo" href="<?= url('index.php'); ?>">QueueLess</a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-label="Ouvrir le menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav-links" data-nav-menu>
            <a class="<?= nav_is_active('home', $currentPage); ?>" href="<?= url('index.php'); ?>">Accueil</a>
            <a class="<?= nav_is_active('services', $currentPage); ?>" href="<?= url('pages/services.php'); ?>">Services</a>
            <?php if ($user): ?>
                <?php if (is_admin()): ?>
                    <a class="<?= strpos($currentPage, 'admin_') === 0 ? 'is-active' : ''; ?>" href="<?= url('admin/dashboard.php'); ?>">Admin</a>
                <?php else: ?>
                    <a class="<?= nav_is_active('dashboard', $currentPage); ?>" href="<?= url('pages/user_dashboard.php'); ?>">Dashboard</a>
                <?php endif; ?>
                <span class="nav-user"><?= e(trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''))); ?></span>
                <a href="<?= url('actions/logout.php'); ?>">Logout</a>
            <?php else: ?>
                <a class="<?= nav_is_active('login', $currentPage); ?>" href="<?= url('pages/login.php'); ?>">Login</a>
                <a class="nav-register <?= nav_is_active('register', $currentPage); ?>" href="<?= url('pages/register.php'); ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
