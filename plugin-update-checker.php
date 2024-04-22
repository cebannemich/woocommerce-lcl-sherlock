<?php
require_once 'plugin-update-checker-5.4/plugin-update-checker.php';

$WooLclSherlockUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://raw.githubusercontent.com/cebannemich/woocommerce-lcl-sherlock/main/info.json?token=GHSAT0AAAAAACRJYVXQUZ2RIC2EIPTL2BSAZRGIQSQ',
    __FILE__,
    'woocommerce-lcl-sherlock'
);

// Vérifie si une mise à jour est disponible
add_action('init', 'woocommerce_lcl_sherlock_check_update');
function woocommerce_lcl_sherlock_check_update() {
    global $WooLclSherlockUpdateChecker;
    $WooLclSherlockUpdateChecker->requestUpdate();
}