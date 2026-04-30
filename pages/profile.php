<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Profil';
$pageDescription = 'Profil utilisateur QueueLess.';
$currentPage = 'profile';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$profile = $user ?? [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $profileStatement = $pdo->prepare(
        'SELECT id, nom, prenom, email, telephone, role, created_at
        FROM users
        WHERE id = :id
        LIMIT 1'
    );
    $profileStatement->execute([
        'id' => $userId,
    ]);

    $profileRow = $profileStatement->fetch();

    if ($profileRow) {
        $profile = $profileRow;
    }
} catch (Throwable $exception) {
    $databaseWarning = 'La base de donnees n est pas encore disponible. Les informations affichees viennent de la session courante.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_user.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Profil personnel</span>
                <h1>Vos informations</h1>
                <p>Consultez et mettez a jour vos informations personnelles depuis un espace simple et organise.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <div class="profile-grid">
                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Modifier le profil</span>
                            <h2>Informations generales</h2>
                        </div>
                    </div>

                    <form class="profile-form" method="POST" action="<?= url('actions/update_profile_action.php'); ?>" data-validate>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom</label>
                                <input
                                    type="text"
                                    id="nom"
                                    name="nom"
                                    value="<?= e(old('nom', (string) ($profile['nom'] ?? ''))); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="prenom">Prenom</label>
                                <input
                                    type="text"
                                    id="prenom"
                                    name="prenom"
                                    value="<?= e(old('prenom', (string) ($profile['prenom'] ?? ''))); ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Adresse email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= e(old('email', (string) ($profile['email'] ?? ''))); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="telephone">Telephone</label>
                            <input
                                type="tel"
                                id="telephone"
                                name="telephone"
                                value="<?= e(old('telephone', (string) ($profile['telephone'] ?? ''))); ?>"
                                required
                            >
                        </div>

                        <button class="btn btn-primary profile-submit" type="submit">Enregistrer les modifications</button>
                    </form>
                </section>

                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Etat du compte</span>
                            <h2>Resume rapide</h2>
                        </div>
                    </div>

                    <div class="summary-stack">
                        <div class="summary-line">
                            <span>Nom complet</span>
                            <strong><?= e(trim((string) (($profile['prenom'] ?? '') . ' ' . ($profile['nom'] ?? '')))); ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Email</span>
                            <strong><?= e((string) ($profile['email'] ?? '-')); ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Telephone</span>
                            <strong><?= e((string) ($profile['telephone'] ?? '-')); ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Date de creation</span>
                            <strong><?= e(format_datetime((string) ($profile['created_at'] ?? ''), 'd/m/Y')); ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Role</span>
                            <strong><?= e(ucfirst((string) ($profile['role'] ?? 'user'))); ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Etape suivante</span>
                            <strong>Reservation et suivi depuis le dashboard</strong>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
