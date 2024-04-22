<?php

require 'plugin-update-checker-5.4/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$WooLclSherlockUpdateChecker = PucFactory::buildUpdateChecker(
    'https://raw.githubusercontent.com/cebannemich/woocommerce-lcl-sherlock/main/info.json',
    __FILE__,
    'woocommerce-lcl-sherlock'
);

// Vérifie si une mise à jour est disponible
add_action('init', 'woocommerce_lcl_sherlock_check_update');
function woocommerce_lcl_sherlock_check_update() {
    global $WooLclSherlockUpdateChecker;
    $WooLclSherlockUpdateChecker->requestUpdate();
}

?>