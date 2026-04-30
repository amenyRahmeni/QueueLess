<?php
declare(strict_types=1);

$pageTitle = 'Connexion';
$pageDescription = 'Connectez-vous a votre espace QueueLess.';
$currentPage = 'login';
$extraCss = ['auth.css'];
$extraJs = ['auth.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-layout">
            <div class="auth-showcase">
                <span class="section-badge">Connexion securisee</span>
                <h1>Retrouvez vos reservations en quelques secondes</h1>
                <p>
                    Connectez-vous pour consulter vos rendez-vous, suivre vos reservations
                    et acceder rapidement aux services disponibles.
                </p>

                <div class="auth-showcase-list">
                    <div class="auth-showcase-item">
                        <strong>Suivi centralise</strong>
                        <span>Visualisez vos rendez-vous a venir et votre historique.</span>
                    </div>

                    <div class="auth-showcase-item">
                        <strong>Reservation rapide</strong>
                        <span>Accedez aux creneaux disponibles sans perdre de temps.</span>
                    </div>

                    <div class="auth-showcase-item">
                        <strong>Interface moderne</strong>
                        <span>Une experience simple, claire et adaptee a mobile.</span>
                    </div>
                </div>
            </div>

            <div class="auth-card">
                <div class="auth-card-header">
                    <span class="auth-kicker">Espace utilisateur</span>
                    <h2>Connexion</h2>
                    <p>Entrez vos identifiants pour acceder a votre compte QueueLess.</p>
                </div>

                <form class="auth-form" method="POST" action="<?= url('actions/login_action.php'); ?>" data-validate>
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="exemple@email.com"
                            value="<?= e(old('email')); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="password-field">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Votre mot de passe"
                                required
                            >
                            <button type="button" class="password-toggle" data-password-toggle="#password" aria-pressed="false">
                                Afficher
                            </button>
                        </div>
                    </div>

                    <button class="btn btn-primary auth-submit" type="submit">Se connecter</button>

                    <p class="auth-switch">
                        Vous n'avez pas encore de compte ?
                        <a href="<?= url('pages/register.php'); ?>">Creer un compte</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
