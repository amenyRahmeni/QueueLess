<?php
declare(strict_types=1);

$pageTitle = 'Inscription';
$pageDescription = 'Creez votre compte QueueLess.';
$currentPage = 'register';
$extraCss = ['auth.css'];
$extraJs = ['auth.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-layout">
            <div class="auth-showcase">
                <span class="section-badge">Inscription rapide</span>
                <h1>Creez votre compte et commencez a reserver sans attendre</h1>
                <p>
                    Rejoignez QueueLess pour gagner du temps, suivre vos reservations
                    et profiter d'une organisation plus fluide.
                </p>

                <div class="auth-showcase-list">
                    <div class="auth-showcase-item">
                        <strong>Profil personnel</strong>
                        <span>Conservez vos informations et simplifiez vos futures reservations.</span>
                    </div>

                    <div class="auth-showcase-item">
                        <strong>Acces rapide aux services</strong>
                        <span>Parcourez les categories et choisissez le meilleur creneau.</span>
                    </div>

                    <div class="auth-showcase-item">
                        <strong>Historique clair</strong>
                        <span>Retrouvez vos reservations et vos avis dans une interface organisee.</span>
                    </div>
                </div>
            </div>

            <div class="auth-card">
                <div class="auth-card-header">
                    <span class="auth-kicker">Nouveau compte</span>
                    <h2>Inscription</h2>
                    <p>Remplissez le formulaire ci-dessous pour rejoindre QueueLess.</p>
                </div>

                <form class="auth-form" method="POST" action="<?= url('actions/register_action.php'); ?>" data-validate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input
                                type="text"
                                id="nom"
                                name="nom"
                                placeholder="Votre nom"
                                value="<?= e(old('nom')); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prenom</label>
                            <input
                                type="text"
                                id="prenom"
                                name="prenom"
                                placeholder="Votre prenom"
                                value="<?= e(old('prenom')); ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-email">Adresse email</label>
                        <input
                            type="email"
                            id="register-email"
                            name="email"
                            placeholder="exemple@email.com"
                            value="<?= e(old('email')); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="telephone">Telephone</label>
                        <input
                            type="tel"
                            id="telephone"
                            name="telephone"
                            placeholder="+216 00 000 000"
                            value="<?= e(old('telephone')); ?>"
                            required
                        >
                    </div>

                    <div class="form-group account-type-group">
                        <div class="choice-heading">
                            <label>Choisir le type de compte</label>
                        </div>

                        <div class="account-type-grid">
                            <label class="account-type-card">
                                <input
                                    type="radio"
                                    name="account_type"
                                    value="user"
                                    <?= old('account_type', 'user') === 'user' ? 'checked' : ''; ?>
                                >
                                <span class="account-type-content">
                                    <strong>Client</strong>
                                </span>
                            </label>

                            <label class="account-type-card">
                                <input
                                    type="radio"
                                    name="account_type"
                                    value="owner"
                                    <?= old('account_type') === 'owner' ? 'checked' : ''; ?>
                                >
                                <span class="account-type-content">
                                    <strong>Client (services)</strong>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="register-password">Mot de passe</label>
                            <div class="password-field">
                                <input
                                    type="password"
                                    id="register-password"
                                    name="password"
                                    placeholder="Minimum 8 caracteres"
                                    minlength="8"
                                    required
                                >
                                <button type="button" class="password-toggle" data-password-toggle="#register-password" aria-pressed="false">
                                    Afficher
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm-password">Confirmer le mot de passe</label>
                            <div class="password-field">
                                <input
                                    type="password"
                                    id="confirm-password"
                                    name="confirm_password"
                                    placeholder="Retapez le mot de passe"
                                    minlength="8"
                                    required
                                >
                                <button type="button" class="password-toggle" data-password-toggle="#confirm-password" aria-pressed="false">
                                    Afficher
                                </button>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary auth-submit" type="submit">Creer mon compte</button>

                    <p class="auth-switch">
                        Vous avez deja un compte ?
                        <a href="<?= url('pages/login.php'); ?>">Se connecter</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
