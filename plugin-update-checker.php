<?php

require 'plugin-update-checker-5.4/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

try {
    $WooLclSherlockUpdateChecker = PucFactory::buildUpdateChecker(
        'https://raw.githubusercontent.com/cebannemich/woocommerce-lcl-sherlock/main/info.json',
        __FILE__,
        'woocommerce-lcl-sherlock'
    );
    //Set the branch that contains the stable release.
    //    $WooLclSherlockUpdateChecker->setBranch('main');

    // Déclencher la vérification des mises à jour
    add_action('init', function () use ($WooLclSherlockUpdateChecker) {
        $WooLclSherlockUpdateChecker->requestUpdate();
    });

    // Vérifier si la dernière mise à jour a réussi
    $update = $WooLclSherlockUpdateChecker->getUpdate();
    if ($update !== null) {
//        echo 'La dernière mise à jour a été vérifiée avec succès !';
//        // Vous pouvez accéder aux métadonnées de la mise à jour avec $lastUpdateMetadata
//        var_dump($update);
    } else {
//        echo 'La dernière mise à jour n\'a pas pu être vérifiée.';
    }

} catch (Exception $e) {
    // Gestion des erreurs survenues lors de la création de l'instance de mise à jour
    // Vous pouvez personnaliser le traitement de l'erreur ici
//    var_dump($e->getMessage());
}
?>
