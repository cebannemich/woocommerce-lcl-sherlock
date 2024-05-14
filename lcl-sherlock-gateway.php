<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class WC Sherclock Gateway heritant la classe Woocommerce payement gateway
 */
class WC_LCL_Sherlock_Gateway extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'lcl_sherlock';
        $this->has_fields = false;
        $this->method_title = 'LCL Sherlock';
        $this->method_description = 'Paiement sécurisé via LCL Sherlock.';
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }


    /**
     * Initialisation de champs pour le formulaire dans configuration
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => 'Activer/Désactiver',
                'type' => 'checkbox',
                'label' => 'Activer le paiement via LCL Sherlock',
                'default' => 'yes',
                ],
            'title' => [
                'title' => 'Titre',
                'type' => 'text',
                'description' => 'Ce titre s\'affichera sur la page de paiement.',
                'default' => 'LCL Sherlock',
                ],
            'description' => [
                'title' => 'Description',
                'type' => 'textarea',
                'description' => 'Description de la méthode de paiement qui s\'affichera sur la page de paiement.',
                'default' => 'Paiement sécurisé via LCL Sherlock.',
                ],
            'id_merchant' => [
                'title' => 'Identifiant Marchand',
                'type' => 'text',
                'description' => 'Veuillez saisir l\'identifiant marchand',
                'default' => '',
                ],
            'secret_key' => [
                'title' => 'Clé secret',
                'type' => 'text',
                'description' => 'Veuillez saisir la clé secrète', 'default' => '',
                ],
            'key_version' => [
                'title' => 'Version de la clé',
                'type' => 'number',
                'description' => 'Veuillez saisir la version de votre clé',
                'default' => '1',
                ],
            'interface_version' => [
                'title' => 'Version de l\' interface',
                'type' => 'text',
                'description' => '',
                'default' => 'HP_3.0',
                ],
            'environment' => [
                'title' => __('Sélectionner Environnement', ''),
                'type' => 'select',
                'description' => __('Environnement de travaille', ''),
                'options' => [
                    'https://sherlocks-payment-webinit-simu.secure.lcl.fr/paymentInit' => 'SIMULATION',
                    'https://sherlocks-payment-webinit.secure.lcl.fr/paymentInit' => 'PRODUCTION',
                        ],
                'default' => 'https://sherlocks-payment-webinit-simu.secure.lcl.fr/paymentInit',
                ],
            'msg_command_confirm' => [
                'title' => 'Message après la commande est validée',
                'type' => 'textarea',
                'description' => '',
                'default' => 'Votre commande a bien été validée',
            ],
            'msg_command_annuler' => [
                'title' => 'Message pour la commande annulée',
                'type' => 'textarea',
                'description' => 'test',
                'default' => 'Votre commande a  été annuler',
            ],
            'msg_command_echec' => [
                'title' => 'Message pour la commande echouer',
                'type' => 'textarea',
                'description' => '',
                'default' => 'Votre commande a  été echouée',
            ],
            ];
    }

    /**
     * Encrypt data
     * @param $str
     * @return string
     */
    public function stringToBase64($str)
    {
        // Encodez la chaîne en base64
        $base64 = base64_encode($str);

        // Remplacez les caractères spéciaux
        $base64URL = strtr($base64, '+/', '-_');

        // Supprimez les caractères de remplissage
        $base64URL = rtrim($base64URL, '=');

        return $base64URL;
    }

    /**
     * Decrypt data
     * @param $str
     * @return false|string
     */
    public function decodeBase64($str)
    {
        // Ajoutez des caractères de remplissage si nécessaire
        $padding = strlen($str) % 4;
        if ($padding !== 0) {
            $str .= str_repeat('=', 4 - $padding);
        }
        // Remplacez les caractères spéciaux
        $base64 = strtr($str, '-_', '+/');
        // Décoder la chaîne base64
        $string = base64_decode($base64);

        return $string;
    }

    /**
     * Proccess payment
     * @param $order_id
     * @return string[]
     */
    public function process_payment($order_id)
    {

        $order = wc_get_order($order_id);
        // Récupérer le montant total de la commande
        $order_total = $order->get_total();
        // Définir un statut temporaire pour la commande
        $order->update_status('pending', __('En attente de paiement via LCL Sherlock.', '...'));
        //GET PAGE to submit paiement
        $page_slug = 'submit-paiement-lcl';
        $page_url = get_permalink(get_page_by_path($page_slug));
        $order_id = $this->stringToBase64($order_id);

        return ['result' => 'success', 'redirect' => $page_url . '/?order=' . $order_id];
    }

    /**
     * Submit data
     * @param $order_id
     * @param $order_total
     * @return void
     */
    public function submit_lcl($order_id, $order_total)
    {

        $id_merchant = $this->get_option('id_merchant');
        $secret_key = $this->get_option('secret_key');
        $interface_version = $this->get_option('interface_version');
        $destination_url = $this->get_option('environment');
        $key_version = $this->get_option('key_version');
        $normalReturnUrl = $this->get_url_return_paiement($order_id);
        $order_total = $this->normalization_price($order_total);
        $ref_transaction = $this->generate_reference_transaction();
        $data = 'amount=' . $order_total . '|currencyCode=978|merchantId=' . $id_merchant . '|normalReturnUrl=' . $normalReturnUrl . '|keyVersion=' . $key_version . '|transactionReference=' . $ref_transaction . '|orderChannel=INTERNET';
        $seal = hash('sha256', $data . $secret_key);

        echo "<script>
    document.addEventListener('DOMContentLoaded', function() {

          var form = document.createElement('form');
          form.method = 'POST'; // Méthode POST
          form.action = '" . $destination_url . "';
          form.style.visibility = 'hidden';


          var inputData = document.createElement('input');
          inputData.name = 'Data';
          inputData.value = '" . $data . "'
          form.appendChild(inputData);

          var inputSeal = document.createElement('input');
          inputSeal.name = 'Seal';
          inputSeal.value = '" . $seal . "';
          form.appendChild(inputSeal);

          var interface_version = document.createElement('input');
          interface_version.name = 'InterfaceVersion';
          interface_version.value = '" . $interface_version . "' ;
          form.appendChild(interface_version);

          document.body.appendChild(form);

          // Soumission automatique du formulaire
          form.submit();
          })
        </script>";

    }

    /**
     * Normalization price
     * @param $order_total
     * @return float|int
     */
    public function normalization_price($order_total)
    {
        $order_total = str_replace(',', '.', $order_total);
        $order_total = floatval($order_total);
        $order_total = round($order_total, 2);

        return $order_total * 100;;
    }

    /**
     * generate ref transcation
     * @return string
     */
    public function generate_reference_transaction()
    {
        $length = 12;
        // Caractères autorisés
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        // Longueur de la chaîne de caractères autorisée
        $char_length = strlen($characters);

        // Initialiser le nom aléatoire
        $random_ref = '';

        // Générer une chaîne aléatoire de la longueur spécifiée
        for ($i = 0; $i < $length; $i++) {
            $random_ref .= $characters[rand(0, $char_length - 1)];
        }

        return $random_ref;
    }
    /**
     * Generate URL to redirect
     * @param $order_id
     * @return string
     */
    public function get_url_return_paiement($order_id)
    {
        $page_slug = 'return-paiement-lcl';
        $order_id = $this->stringToBase64($order_id);

        return get_permalink(get_page_by_path($page_slug)) . '?order_id=' . $order_id;
    }

    /**
     * @param $order_id
     * @param $post
     * @return void
     */
    public function get_data_return_lcl($order_id, $post)
    {
        $order_id = $this->decodeBase64($order_id);
        $order = wc_get_order($order_id);

        $data = explode('|', $post['Data']);
        $responseCode = null;
        // Parcourir toutes les paires clé-valeur
        foreach ($data as $pair) {
            // Diviser chaque paire en clé et valeur
            $parts = explode('=', $pair);

            // Vérifier si la clé est "responseCode"
            if ($parts[0] === 'responseCode') {
                // Stocker la valeur de responseCode
                $responseCode = $parts[1];
                // Sortir de la boucle car nous avons trouvé responseCode
                break;
            }
        }
        if ($responseCode == '00') {
            // Met à jour le statut de la commande en 'processing' pour indiquer un paiement réussi
            $order->update_status( 'processing', __( 'Paiement validé', 'woocommerce' ) );
        }
        else {
            // Annuler la commande et mettre à jour le statut
            $order->update_status('cancelled', __('Commande annulée par l\'utilisateur.'));

            // Ajouter une note à la commande pour indiquer la raison de l\'annulation
            $order->add_order_note(__('Commande annulée par l\' utilisateur.'));

        }
    }

    public function post_submit_lcl($order_id)
    {
        $order = $this->decodeBase64($order_id);
        $order = wc_get_order($order);

        if ($order) {
            // Récupérer le montant total de la commande
            $order_id = $order->get_id();
            $total_amount = $order->get_total();
            $this->submit_lcl($order_id, $total_amount);
            exit();
        }
        else {
            die('Désolé votre commande n\' existe pas');
        }
    }

    /**
     * @param $order_id
     * @param $post
     * @return string
     */
    public function check_return_for_message($order_id, $post)
    {
        $message_return = '';
        $order_id = $this->decodeBase64($order_id);
        $order = wc_get_order($order_id);

        $data = explode('|', $post['Data']);
        $responseCode = null;
        // Parcourir toutes les paires clé-valeur
        foreach ($data as $pair) {
            // Diviser chaque paire en clé et valeur
            $parts = explode('=', $pair);

            // Vérifier si la clé est "responseCode"
            if ($parts[0] === 'responseCode') {
                // Stocker la valeur de responseCode
                $responseCode = $parts[1];
                // Sortir de la boucle car nous avons trouvé responseCode
                break;
            }
        }
        //traitement status

        if ($responseCode == '00') {
            $message_return .= $this->get_option('msg_command_confirm');
        }elseif ($responseCode == '17'){
            $message_return .= $this->get_option('msg_command_echec');
        }
        else {
            $message_return .= $this->get_option('msg_command_annuler');
        }

        return $message_return;
    }
}

