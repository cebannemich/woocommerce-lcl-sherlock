<?php
/**
 * Plugin Name: Woocommerce LCL Sherlock Payment
 * Description: Intégration du service de paiement LCL Sherlock en utilisant la methode POST avec Woocommerce.
 * Version: 1.2.4
 * Author: Cebanne NAMBININJANAHARY
 */

// Inclure le fichier plugin-update-checker.php
require_once plugin_dir_path(__FILE__) . 'plugin-update-checker.php';

add_action( 'plugins_loaded', 'init_lcl_sherlock_gateway' );

function init_lcl_sherlock_gateway() {
    // Vérifier si WooCommerce est activé
    if ( class_exists( 'WC_Payment_Gateway' ) ) {
        // Inclure votre classe de passerelle de paiement et déclarer la passerelle
        include_once 'lcl-sherlock-gateway.php';
        add_filter( 'woocommerce_payment_gateways', 'add_lcl_sherlock_gateway' );
    }
}

function add_lcl_sherlock_gateway( $gateways ) {
    $gateways[] = 'WC_LCL_Sherlock_Gateway';
    return $gateways;
}

function copy_file_to_theme() {

    // Copie du fichier template submit lcl
    $source_template_submit_file = plugin_dir_path( __FILE__ ) . 'template-submit-lcl.php';

    // Copie du fichier template Return lcl
    $source_template_return_file = plugin_dir_path( __FILE__ ) . 'template-return-lcl.php';

    // Chemin du répertoire du thème actif
    $theme_directory = get_stylesheet_directory();

    // Chemin de destination pour la copie
    $destination_submit_file = $theme_directory . '/template-submit-lcl.php';
    $destination_return_file = $theme_directory . '/template-return-lcl.php';

    // Copier le fichier template submit vers le thème actif
    if ( copy( $source_template_submit_file, $destination_submit_file ) ) {
        echo 'copie de fichier de template de paiement vers le theme par defaut avec success';
    } else {
        echo 'echec copie tempate';
    }


    // Copier le fichier template return  vers le thème actif
    if ( copy( $source_template_return_file, $destination_return_file ) ) {
        echo 'copie de fichier de template de paiement vers le theme par defaut avec success';
    } else {
        echo 'echec copie tempate';
    }
}
register_activation_hook( __FILE__, 'copy_file_to_theme' );

function create_custom_page_on_activation() {
    // Vérifier si la page existe déjà pour éviter les doublons
    $page_submit_order_exists = get_page_by_path( 'submit-paiement-lcl' );
    $page_return_order_exists = get_page_by_path( 'return-paiement-lcl' );

    if ( ! $page_submit_order_exists ) {
        $new_page_id = wp_insert_post( array(
            'post_title'    => 'Submit paiement LCL sherlock',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'    => 'submit-paiement-lcl',
        ) );

        // Affecter un modèle à la nouvelle page
        if ( $new_page_id ) {
            update_post_meta( $new_page_id, '_wp_page_template', 'template-submit-lcl.php' );
        }
    }

    if ( ! $page_return_order_exists ) {
        $new_page_return_id = wp_insert_post( array(
            'post_title'    => 'Return paiement LCL sherlock',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'    => 'return-paiement-lcl',
        ) );

        // Affecter un modèle à la nouvelle page
        if ( $new_page_return_id ) {
            update_post_meta( $new_page_return_id, '_wp_page_template', 'template-return-lcl.php' );
        }
    }
}

register_activation_hook( __FILE__, 'create_custom_page_on_activation' );

function my_copy_template_file_on_theme_switch() {
    copy_file_to_theme();
    create_custom_page_on_activation();
}

add_action( 'switch_theme', 'my_copy_template_file_on_theme_switch' );
?>