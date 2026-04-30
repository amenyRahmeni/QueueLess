<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Accueil';
$pageDescription = 'QueueLess est une plateforme moderne de reservation de creneaux horaires pour eviter les files d attente.';
$currentPage = 'home';

$stats = [
    'services' => 12,
    'reservations' => 180,
    'users' => 96,
    'slots' => 42,
];
$featuredServices = [
    [
        'id' => 1,
        'nom' => 'Salon Signature',
        'categorie' => 'Salon de coiffure',
        'description' => 'Coupe, brushing et soins dans un espace moderne avec prise en charge rapide.',
        'adresse' => 'Tunis',
        'image' => null,
        'duree_moyenne' => 45,
        'available_slots' => 4,
        'average_note' => 4.8,
    ],
    [
        'id' => 2,
        'nom' => 'Clinique Nova',
        'categorie' => 'Cabinet medical',
        'description' => 'Consultations planifiees avec creneaux clairs et attente reduite.',
        'adresse' => 'Ariana',
        'image' => null,
        'duree_moyenne' => 30,
        'available_slots' => 6,
        'average_note' => 4.7,
    ],
    [
        'id' => 3,
        'nom' => 'Banque Horizon',
        'categorie' => 'Banque',
        'description' => 'Rendez-vous client pour ouverture de compte, assistance et conseil.',
        'adresse' => 'Sfax',
        'image' => null,
        'duree_moyenne' => 20,
        'available_slots' => 5,
        'average_note' => 4.5,
    ],
];
$latestReviews = [
    [
        'service_nom' => 'Salon Signature',
        'full_name' => 'Nour Ben Salem',
        'note' => 5,
        'commentaire' => 'Reservation simple et service rapide. L experience etait tres fluide.',
    ],
    [
        'service_nom' => 'Clinique Nova',
        'full_name' => 'Ines Gharbi',
        'note' => 5,
        'commentaire' => 'Le creneau etait bien respecte, sans attente inutile a l arrivee.',
    ],
    [
        'service_nom' => 'Banque Horizon',
        'full_name' => 'Sami Kallel',
        'note' => 4,
        'commentaire' => 'Interface claire et prise de rendez-vous pratique pour une demande rapide.',
    ],
];
$categories = ['Salon', 'Restaurant', 'Banque', 'Medical', 'Administration'];
$databaseMode = 'demo';

try {
    $pdo = getPDO();

    $stats['services'] = (int) $pdo->query('SELECT COUNT(*) FROM services WHERE actif = 1')->fetchColumn();
    $stats['reservations'] = (int) $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
    $stats['users'] = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetchColumn();
    $stats['slots'] = (int) $pdo->query('SELECT COUNT(*) FROM slots WHERE disponible = 1 AND date_slot >= CURDATE()')->fetchColumn();

    $featuredStatement = $pdo->query(
        'SELECT
            s.id,
            s.nom,
            s.categorie,
            s.description,
            s.adresse,
            s.image,
            s.duree_moyenne,
            (
                SELECT COUNT(*)
                FROM slots sl
                WHERE sl.service_id = s.id
                    AND sl.disponible = 1
                    AND sl.date_slot >= CURDATE()
            ) AS available_slots,
            (
                SELECT AVG(a.note)
                FROM avis a
                WHERE a.service_id = s.id
            ) AS average_note
        FROM services s
        WHERE s.actif = 1
        ORDER BY available_slots DESC, s.created_at DESC
        LIMIT 3'
    );
    $featuredRows = $featuredStatement->fetchAll();
    if ($featuredRows) {
        $featuredServices = array_slice(array_merge($featuredRows, $featuredServices), 0, 3);
    }

    $reviewsStatement = $pdo->query(
        'SELECT
            s.nom AS service_nom,
            CONCAT(u.prenom, " ", u.nom) AS full_name,
            a.note,
            a.commentaire
        FROM avis a
        INNER JOIN users u ON u.id = a.user_id
        INNER JOIN services s ON s.id = a.service_id
        ORDER BY a.created_at DESC
        LIMIT 3'
    );
    $reviewRows = $reviewsStatement->fetchAll();
    if ($reviewRows) {
        $mergedReviews = array_merge($reviewRows, $latestReviews);
        $uniqueReviews = [];
        $reviewKeys = [];

        foreach ($mergedReviews as $reviewItem) {
            $reviewKey = strtolower(
                (string) (($reviewItem['service_nom'] ?? '') . '|' . ($reviewItem['full_name'] ?? '') . '|' . ($reviewItem['commentaire'] ?? ''))
            );

            if (isset($reviewKeys[$reviewKey])) {
                continue;
            }

            $reviewKeys[$reviewKey] = true;
            $uniqueReviews[] = $reviewItem;
        }

        $latestReviews = array_slice($uniqueReviews, 0, 3);
    }

    $categoryStatement = $pdo->query(
        'SELECT DISTINCT categorie
        FROM services
        WHERE actif = 1
        ORDER BY categorie ASC
        LIMIT 5'
    );
    $categoryRows = $categoryStatement->fetchAll();
    if ($categoryRows) {
        $categories = array_map(
            static fn (array $row): string => (string) ($row['categorie'] ?? ''),
            $categoryRows
        );
    }

    $databaseMode = 'live';
} catch (Throwable $exception) {
    $databaseMode = 'demo';
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-content" data-reveal="up">
            <span class="section-badge">Plateforme intelligente de reservation</span>
            <h1>Reservez sans attendre</h1>
            <p class="hero-subtitle">Une solution moderne pour organiser les rendez-vous, reduire l attente et offrir une experience fluide aux utilisateurs comme aux administrateurs.</p>

            <div class="hero-chip-row">
                <?php foreach ($categories as $category): ?>
                    <span class="hero-chip"><?= e($category); ?></span>
                <?php endforeach; ?>
            </div>

            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= url('pages/services.php'); ?>">Voir les services</a>
                <?php if (is_logged_in()): ?>
                    <a class="btn btn-secondary" href="<?= is_admin() ? url('admin/dashboard.php') : url('pages/user_dashboard.php'); ?>">
                        Ouvrir mon espace
                    </a>
                <?php else: ?>
                    <a class="btn btn-secondary" href="<?= url('pages/register.php'); ?>">Creer un compte</a>
                <?php endif; ?>
            </div>

            <div class="hero-mini-stats">
                <div class="hero-mini-stat">
                    <strong data-countup="<?= e((string) $stats['services']); ?>"><?= e((string) $stats['services']); ?></strong>
                    <span>services actifs</span>
                </div>
                <div class="hero-mini-stat">
                    <strong data-countup="<?= e((string) $stats['slots']); ?>"><?= e((string) $stats['slots']); ?></strong>
                    <span>creneaux disponibles</span>
                </div>
                <div class="hero-mini-stat">
                    <strong data-countup="<?= e((string) $stats['users']); ?>"><?= e((string) $stats['users']); ?></strong>
                    <span>utilisateurs inscrits</span>
                </div>
            </div>
        </div>

        <div class="hero-card" data-reveal="up" data-delay="120">
            <div class="hero-card-top">
                <span class="hero-dot"></span>
                <span class="hero-dot"></span>
                <span class="hero-dot"></span>
            </div>

            <div class="hero-card-body">
                <div class="mini-panel">
                    <span class="mini-label">Mode de demonstration</span>
                    <strong><?= e($databaseMode === 'live' ? 'Connecte a la base locale' : 'Mode vitrine local'); ?></strong>
                </div>

                <div class="mini-panel">
                    <span class="mini-label">Reservations enregistrees</span>
                    <strong data-countup="<?= e((string) $stats['reservations']); ?>"><?= e((string) $stats['reservations']); ?></strong>
                </div>

                <div class="mini-panel highlight">
                    <span class="mini-label">Promesse produit</span>
                    <strong>Moins de file d attente, plus de visibilite</strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-block section-soft">
    <div class="container">
        <div class="section-heading centered" data-reveal="up">
            <span class="section-badge section-badge-light">Vue d ensemble</span>
            <h2>Une plateforme pensee pour l usage reel</h2>
            <p>QueueLess combine une interface publique claire, un espace utilisateur pratique et un panneau d administration simple a gerer.</p>
        </div>

        <div class="insight-grid">
            <article class="insight-card" data-reveal="up">
                <span class="insight-label">Utilisateurs</span>
                <strong data-countup="<?= e((string) $stats['users']); ?>"><?= e((string) $stats['users']); ?></strong>
                <p>Comptes capables de reserver, annuler, suivre leurs rendez-vous et laisser un avis.</p>
            </article>

            <article class="insight-card" data-reveal="up" data-delay="100">
                <span class="insight-label">Services</span>
                <strong data-countup="<?= e((string) $stats['services']); ?>"><?= e((string) $stats['services']); ?></strong>
                <p>Services publics et prives classes par categorie, avec creneaux et images.</p>
            </article>

            <article class="insight-card" data-reveal="up" data-delay="200">
                <span class="insight-label">Reservations</span>
                <strong data-countup="<?= e((string) $stats['reservations']); ?>"><?= e((string) $stats['reservations']); ?></strong>
                <p>Suivi des demandes reservees, annulees ou terminees depuis les deux espaces.</p>
            </article>

            <article class="insight-card" data-reveal="up" data-delay="300">
                <span class="insight-label">Creneaux libres</span>
                <strong data-countup="<?= e((string) $stats['slots']); ?>"><?= e((string) $stats['slots']); ?></strong>
                <p>Disponibilites consultables en un coup d oeil pour accelerer la prise de decision.</p>
            </article>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <div class="section-heading centered" data-reveal="up">
            <span class="section-badge section-badge-light">Fonctionnement</span>
            <h2>Trois etapes pour supprimer l attente inutile</h2>
            <p>Le parcours utilisateur reste simple, lisible et facile a demontrer dans un contexte academique.</p>
        </div>

        <div class="process-grid">
            <article class="process-card" data-reveal="up">
                <div class="process-number">01</div>
                <h3>Choisir un service</h3>
                <p>L utilisateur explore les categories et accede rapidement aux fiches detaillees.</p>
            </article>

            <article class="process-card" data-reveal="up" data-delay="120">
                <div class="process-number">02</div>
                <h3>Selectionner un creneau</h3>
                <p>Les disponibilites sont visibles en temps reel, avec une logique claire de reservation.</p>
            </article>

            <article class="process-card" data-reveal="up" data-delay="240">
                <div class="process-number">03</div>
                <h3>Suivre et evaluer</h3>
                <p>Le dashboard utilisateur centralise l historique, les annulations et les avis post-rendez-vous.</p>
            </article>
        </div>
    </div>
</section>

<section class="section-block section-soft">
    <div class="container">
        <div class="section-heading" data-reveal="up">
            <div>
                <span class="section-badge section-badge-light">Services mis en avant</span>
                <h2>Des fiches services pretes a etre explorees</h2>
            </div>
            <a class="text-link" href="<?= url('pages/services.php'); ?>">Voir tout le catalogue</a>
        </div>

        <div class="services-grid">
            <?php foreach ($featuredServices as $service): ?>
                <?php
                $serviceName = (string) ($service['nom'] ?? '');
                $serviceDescription = (string) ($service['description'] ?? '');
                $averageNote = $service['average_note'] !== null ? number_format((float) $service['average_note'], 1) : null;
                ?>
                <article class="service-catalog-card" data-reveal="up">
                    <div class="service-catalog-media">
                        <img src="<?= e(service_image_url((string) ($service['image'] ?? ''))); ?>" alt="<?= e($serviceName); ?>">
                    </div>

                    <div class="service-catalog-body">
                        <div class="service-topline">
                            <span class="status-badge"><?= e((string) ($service['categorie'] ?? 'Service')); ?></span>
                            <?php if ($averageNote !== null): ?>
                                <span class="service-rating">Note <?= e($averageNote); ?>/5</span>
                            <?php endif; ?>
                        </div>

                        <h3><?= e($serviceName); ?></h3>
                        <p><?= e(strlen($serviceDescription) > 150 ? substr($serviceDescription, 0, 147) . '...' : $serviceDescription); ?></p>

                        <div class="service-info-list">
                            <span><?= e((string) ($service['adresse'] ?? '-')); ?></span>
                            <span><?= e((string) ($service['duree_moyenne'] ?? 0)); ?> min</span>
                            <span><?= e((string) ($service['available_slots'] ?? 0)); ?> creneau(x)</span>
                        </div>

                        <div class="service-card-actions">
                            <a class="btn btn-primary" href="<?= url('pages/service_details.php?id=' . (int) ($service['id'] ?? 0)); ?>">Decouvrir</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <div class="section-heading centered" data-reveal="up">
            <span class="section-badge section-badge-light">Retours utilisateurs</span>
            <h2>Une experience simple, claire et credible</h2>
            <p>Quelques avis recents pour illustrer la valeur du produit lors d une demonstration.</p>
        </div>

        <div class="testimonial-grid">
            <?php foreach ($latestReviews as $review): ?>
                <article class="testimonial-card" data-reveal="up">
                    <div class="testimonial-top">
                        <strong><?= e((string) ($review['full_name'] ?? 'Utilisateur QueueLess')); ?></strong>
                        <span class="service-rating">Note <?= e((string) ($review['note'] ?? 5)); ?>/5</span>
                    </div>
                    <p><?= e((string) ($review['commentaire'] ?? 'Experience tres satisfaisante.')); ?></p>
                    <span class="testimonial-service"><?= e((string) ($review['service_nom'] ?? 'Service QueueLess')); ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-block cta-section">
    <div class="container cta-card" data-reveal="up">
        <div>
            <span class="section-badge">Pret pour la demonstration</span>
            <h2>Un projet PHP natif clair, moderne et facile a presenter</h2>
            <p>QueueLess est maintenant structure pour la navigation publique, l espace utilisateur, l administration et la logique metier essentielle.</p>
        </div>

        <div class="cta-actions">
            <a class="btn btn-primary" href="<?= url('pages/services.php'); ?>">Explorer les services</a>
            <?php if (!is_logged_in()): ?>
                <a class="btn btn-secondary" href="<?= url('pages/login.php'); ?>">Se connecter</a>
            <?php else: ?>
                <a class="btn btn-secondary" href="<?= is_admin() ? url('admin/dashboard.php') : url('pages/user_dashboard.php'); ?>">Acceder au dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
