CREATE DATABASE IF NOT EXISTS queueless CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE queueless;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS slots;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(30) DEFAULT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    duree_moyenne INT UNSIGNED NOT NULL DEFAULT 30,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_services_categorie (categorie),
    INDEX idx_services_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE slots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id INT UNSIGNED NOT NULL,
    date_slot DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    disponible TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_service_slot (service_id, date_slot, heure_debut, heure_fin),
    INDEX idx_slots_service_date (service_id, date_slot),
    INDEX idx_slots_disponible (disponible),
    CONSTRAINT fk_slots_service
        FOREIGN KEY (service_id) REFERENCES services(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    slot_id INT UNSIGNED NOT NULL,
    statut ENUM('reservee', 'annulee', 'terminee') NOT NULL DEFAULT 'reservee',
    commentaire TEXT DEFAULT NULL,
    date_reservation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reservations_user (user_id),
    INDEX idx_reservations_service (service_id),
    INDEX idx_reservations_slot (slot_id),
    INDEX idx_reservations_statut (statut),
    CONSTRAINT fk_reservations_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_reservations_service
        FOREIGN KEY (service_id) REFERENCES services(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_reservations_slot
        FOREIGN KEY (slot_id) REFERENCES slots(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE avis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    note TINYINT UNSIGNED NOT NULL,
    commentaire TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_avis_user_service (user_id, service_id),
    INDEX idx_avis_service (service_id),
    CONSTRAINT fk_avis_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_avis_service
        FOREIGN KEY (service_id) REFERENCES services(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (nom, prenom, email, password, telephone, role) VALUES
('Admin', 'QueueLess', 'admin@queueless.local', '$2y$10$3E14uNW72/ARLxZHr7..J.sT9ZjyvPV0KpnYfSbhwnzjwqhc9mmH6', '+21620000000', 'admin'),
('Ben Salem', 'Nour', 'user@queueless.local', '$2y$10$9BIDTkth2ErdazH3wU3xw.6Mq.Amcwa.eTzTt/6fHW3d0s6V3bdey', '+21621111222', 'user');

INSERT INTO services (nom, description, categorie, adresse, image, duree_moyenne, actif) VALUES
('Salon Signature', 'Salon moderne avec prise en charge rapide pour coupe, brushing et soins.', 'Salon de coiffure', '15 Avenue Habib Bourguiba, Tunis', NULL, 45, 1),
('Clinique Nova', 'Cabinet medical pour consultations generales avec gestion simple des rendez-vous.', 'Cabinet medical', '8 Rue des Jasmins, Ariana', NULL, 30, 1),
('Banque Horizon', 'Agence bancaire avec rendez-vous pour ouverture de compte et conseil client.', 'Banque', '22 Avenue de la Liberte, Sfax', NULL, 20, 1),
('Bistronome Central', 'Restaurant urbain proposant une reservation anticipee pour reduire l attente.', 'Restaurant', '3 Place de la Republique, Sousse', NULL, 60, 1);

INSERT INTO slots (service_id, date_slot, heure_debut, heure_fin, disponible) VALUES
(1, CURDATE() + INTERVAL 1 DAY, '09:00:00', '09:45:00', 0),
(1, CURDATE() + INTERVAL 1 DAY, '10:00:00', '10:45:00', 1),
(1, CURDATE() + INTERVAL 2 DAY, '14:00:00', '14:45:00', 1),
(2, CURDATE() - INTERVAL 2 DAY, '10:00:00', '10:30:00', 0),
(2, CURDATE() + INTERVAL 1 DAY, '08:30:00', '09:00:00', 1),
(2, CURDATE() + INTERVAL 2 DAY, '11:00:00', '11:30:00', 1),
(2, CURDATE() + INTERVAL 2 DAY, '15:00:00', '15:30:00', 1),
(3, CURDATE() + INTERVAL 1 DAY, '09:00:00', '09:20:00', 1),
(3, CURDATE() + INTERVAL 1 DAY, '09:30:00', '09:50:00', 1),
(4, CURDATE() + INTERVAL 1 DAY, '12:30:00', '13:30:00', 1),
(4, CURDATE() + INTERVAL 1 DAY, '13:45:00', '14:45:00', 1),
(4, CURDATE() + INTERVAL 2 DAY, '19:00:00', '20:00:00', 1);

INSERT INTO reservations (user_id, service_id, slot_id, statut, commentaire) VALUES
(2, 1, 1, 'reservee', 'Coupe et brushing'),
(2, 2, 4, 'terminee', 'Consultation terminee avec succes'),
(2, 3, 8, 'annulee', 'Reservation deplacee');

INSERT INTO avis (user_id, service_id, note, commentaire) VALUES
(2, 1, 5, 'Service rapide et accueil tres professionnel.');
