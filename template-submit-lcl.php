<?php
/*
Template Name: Submit Paiement LCL
 */
get_header();
$sherlock_gateway = new WC_LCL_Sherlock_Gateway();

    if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['order'] != '') {
        $sherlock_gateway->post_submit_lcl($_GET['order']);
    }
?>