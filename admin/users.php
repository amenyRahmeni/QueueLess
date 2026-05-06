<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';


$pageTitle = 'Utilisateurs';
$pageDescription = 'Gestion des utilisateurs QueueLess.';
$currentPage = 'admin_users';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$users = [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $usersStatement = $pdo->query(
        'SELECT id, nom, prenom, email, telephone, role, created_at
        FROM users
        ORDER BY created_at DESC'
    );
    $users = $usersStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de charger les utilisateurs pour le moment.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Gestion des comptes</span>
                <h1>Utilisateurs</h1>
                <p>Consultez la liste complete des comptes inscrits sur la plateforme.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Comptes</span>
                        <h2>Liste des utilisateurs</h2>
                    </div>
                </div>

                <?php if ($users): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom complet</th>
                                    <th>Email</th>
                                    <th>Telephone</th>
                                    <th>Role</th>
                                    <th>Creation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $account): ?>
                                    <tr>
                                        <td><?= e(trim((string) (($account['prenom'] ?? '') . ' ' . ($account['nom'] ?? '')))); ?></td>
                                        <td><?= e((string) $account['email']); ?></td>
                                        <td><?= e((string) ($account['telephone'] ?? '-')); ?></td>
                                        <td><span class="<?= e(badge_class_for((string) $account['role'] === 'admin' ? 'warning' : 'success')); ?>"><?= e(ucfirst((string) $account['role'])); ?></span></td>
                                        <td><?= e(format_datetime((string) $account['created_at'], 'd/m/Y')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-panel">
                        <p>Aucun utilisateur disponible.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

