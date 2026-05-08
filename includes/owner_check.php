<?php
declare(strict_types=1);


require_once __DIR__ . '/auth_check.php';

if (!is_service_owner()) {
    set_flash_message('error', 'Acces refuse. Cette page est reservee au proprietaire du service.');
    redirect(user_dashboard_path());
}
