<?php
/*
Template Name: Return Paiement LCL
 */

get_header();
$sherlock_gateway = new WC_LCL_Sherlock_Gateway();
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sherlock_gateway->get_data_return_lcl($_GET['order_id'], $_POST);
    $message = $sherlock_gateway->check_return_for_message($_GET['order_id'], $_POST);
}
?>
<div <?php generate_do_attr('content'); ?>>
    <main <?php generate_do_attr('main'); ?>>
        <div>
            <?php
                 echo $message;
            ?>
        </div>
    </main>
</div>
