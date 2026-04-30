<?php
declare(strict_types=1);

$pageTitle = 'Accueil';
$pageDescription = 'Plateforme intelligente de reservation de creneaux horaires pour eviter les files d attente.';
$currentPage = 'home';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container hero">
        <div class="hero-card stack">
            <span class="eyebrow">Reservation intelligente et sans file d attente</span>
            <div class="stack">
                <h1>Gagnez du temps avec des rendez-vous clairs, rapides et modernes.</h1>
                <p>
                    QueueLess aide les utilisateurs a reserver un creneau en quelques secondes pour un salon,
                    un restaurant, une banque, un cabinet medical ou tout autre service local.
                </p>
            </div>

            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= url('pages/services.php'); ?>">Explorer les services</a>
                <a class="btn btn-outline" href="<?= url('pages/register.php'); ?>">Creer un compte</a>
            </div>

            <div class="stats-strip">
                <article class="stat-card">
                    <span class="muted">Services geres</span>
                    <strong>6+</strong>
                    <p>Salons, restaurants, banques, cabinets medicaux et centres de services.</p>
                </article>

                <article class="stat-card">
                    <span class="muted">Reservation rapide</span>
                    <strong>24/7</strong>
                    <p>Consultation des disponibilites et prise de rendez-vous a tout moment.</p>
                </article>

                <article class="stat-card">
                    <span class="muted">Experience fluide</span>
                    <strong>100%</strong>
                    <p>Interface simple, responsive et adaptee a une demonstration academique.</p>
                </article>
            </div>
        </div>

        <aside class="panel stack">
            <span class="chip">Pourquoi QueueLess ?</span>
            <h3>Une base solide pour un projet web moderne en PHP natif.</h3>

            <div class="stack">
                <div class="feature-item">
                    <strong>Reservation claire</strong>
                    <p>Les utilisateurs consultent les creneaux disponibles et reservent sans confusion.</p>
                </div>

                <div class="feature-item">
                    <strong>Gestion admin simple</strong>
                    <p>Le futur dashboard admin permettra de piloter services, slots, avis et statistiques.</p>
                </div>

                <div class="feature-item">
                    <strong>Architecture maintainable</strong>
                    <p>Configuration, includes, pages, actions et assets sont deja separes proprement.</p>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Services cibles</span>
                <h2>Une plateforme adaptee a plusieurs secteurs</h2>
            </div>
            <p>Ces cartes servent de vitrine initiale en attendant la page dynamique complete des services.</p>
        </div>

        <div class="card-grid">
            <article class="card service-card">
                <div class="card-media">
                    <img src="<?= e(service_image_url(null)); ?>" alt="Salon de coiffure">
                </div>
                <div class="card-body">
                    <span class="status-badge success">Salon de coiffure</span>
                    <h3>Salon Signature</h3>
                    <p>Gestion des coupes, soins, brushing et rendez-vous a heure fixe.</p>
                </div>
            </article>

            <article class="card service-card">
                <div class="card-media">
                    <img src="<?= e(service_image_url(null)); ?>" alt="Cabinet medical">
                </div>
                <div class="card-body">
                    <span class="status-badge warning">Cabinet medical</span>
                    <h3>Clinique Nova</h3>
                    <p>Consultations generales avec creneaux visibles et annulation simplifiee.</p>
                </div>
            </article>

            <article class="card service-card">
                <div class="card-media">
                    <img src="<?= e(service_image_url(null)); ?>" alt="Banque">
                </div>
                <div class="card-body">
                    <span class="status-badge">Banque</span>
                    <h3>Banque Horizon</h3>
                    <p>Prise de rendez-vous pour conseil client, ouverture de compte et assistance.</p>
                </div>
            </article>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

