<?php
/**
* Plugin Name: Give - iPay88
* Description: iPay88 gateway extension for GiveWP
* Author: Stratium Software Group
* Version: 1.0
**/

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Remove CC form
 */
add_action( 'give_ipay88_cc_form', '__return_false' );

/**
 * Register our plugin so it shows up as an option in the Give gateway settings
 */
function f_give_payment_gateways( $gateways ) {
    // Here replace 'give_free' with a unique slug for your plugin.  You will use this slug throughout this plugin.
    $gateways['ipay88'] = array(
        'admin_label'    => esc_attr__( 'iPay88', 'give-ipay88' ),
        'checkout_label' => esc_attr__( 'iPay88', 'give-ipay88' )
    );
    return $gateways;
}
add_filter( 'give_payment_gateways', 'f_give_payment_gateways' );

/**
 * This action will run the function attached to it when it's time to process the donation submission.
 *
 * Here you use the slug you made above at the end of the action name, give_gateway_YOUR_SLUG
 **/
function f_give_gateway_ipay88( $purchase_data ) {
    session_start();
    $request_url = give_is_test_mode() ? 'https://sandbox.ipay88.com.ph/epayment/entry.asp' : 'https://payment.ipay88.com.ph/epayment/entry.asp';
    $merchant_key = give_is_test_mode() ? give_get_option('ipay88_test_merchant_key') : give_get_option('ipay88_live_merchant_key');
    $merchant_code = give_is_test_mode() ? give_get_option('ipay88_test_merchant_code') : give_get_option('ipay88_live_merchant_code');

    /**
     * iPay88 data requirements
     */
    $ipay88_data['MerchantKey'] = $merchant_key; // required
    $ipay88_data['MerchantCode'] = $merchant_code; // required
    $ipay88_data['PaymentId'] = 1; // fixed value
    $ipay88_data['RefNo'] = rand(00000000, 99999999); // unique, required
    $ipay88_data['Amount'] = number_format($purchase_data['price'], 2); // required
    $ipay88_data['Currency'] = give_get_currency(); // required
    $ipay88_data['ProdDesc'] = isset($purchase_data['post_data']['purpose']) ? (is_array($purchase_data['post_data']['purpose']) ? $purchase_data['post_data']['purpose'][0] : $purchase_data['post_data']['purpose']) : $purchase_data['post_data']['give-form-title']; //required
    $ipay88_data['UserName'] = $purchase_data['user_info']['first_name'] . ' ' . $purchase_data['user_info']['last_name']; // required
    $ipay88_data['UserEmail'] = $purchase_data['user_email']; // required
    $ipay88_data['UserContact'] = isset($purchase_data['post_data']['give-contact']) ? $purchase_data['post_data']['give-contact'] : ''; // required
    $ipay88_data['Remark'] = isset($purchase_data['post_data']['location']) ? (is_array($purchase_data['post_data']['location']) ? $purchase_data['post_data']['location'][0] : $purchase_data['post_data']['location']) : '';
    $ipay88_data['Lang'] = 'UTF-8'; // fixed value
    $ipay88_data['Signature'] = iPay88_signature($ipay88_data['MerchantKey'] . $ipay88_data['MerchantCode'] . $ipay88_data['RefNo'] . preg_replace('/[,.s]/', '', $ipay88_data['Amount']) . $ipay88_data['Currency']); // computed value
    $ipay88_data['ResponseURL'] = 'https://newlifethefort.com/v18/giving-confirmation/'; // required
    array_shift($ipay88_data);

    $payment_data = array(
        'price'           => $ipay88_data['Amount'],
        'give_form_title' => $ipay88_data['ProdDesc'],
        'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
        'give_price_id'   => isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : '',
        'date'            => $purchase_data['date'],
        'user_email'      => $purchase_data['user_email'],
        'purchase_key'    => $purchase_data['purchase_key'],
        'currency'        => $ipay88_data['Currency'],
        'user_info'       => $purchase_data['user_info'],
        'status'          => 'pending', /** THIS MUST BE SET TO PENDING TO AVOID PHP WARNINGS */
        'gateway'         => 'ipay88' /** USE YOUR SLUG AGAIN HERE */
    );

    /**
     * Here you will reach out to whatever payment processor you are building for and record a successful payment
     *
     * If it's not correct, make $payment false and attach errors
     */
    // record the payment which is super important so you have the proper records in the Give administration
    $payment = give_insert_payment( $payment_data );
     if ( $payment ) {
        // set current transaction id via session
        $_SESSION['current_transaction'] = $payment;
    } else {
        // if errors are present, send the user back to the donation form so they can be corrected
        give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
    } 
?>

    <form action="<?= $request_url ?>" id="ePayment" method="post">
    <?php foreach($ipay88_data as $key => $val) { ?>
        <input type="hidden" name="<?= $key ?>" value="<?= $val ?>" />
    <?php } ?>
    </form>
    <script>document.getElementById('ePayment').submit()</script>

<?php
}
add_action( 'give_gateway_ipay88', 'f_give_gateway_ipay88' );

/**
 * Adds the settings to the Payment Gateways section
 */
function give_add_ipay88_settings( $settings ) {
    $give_settings = array(
        array(
            'name' => '<strong>' . __( 'iPay88', 'give-ipay88' ) . '</strong>',
            'desc' => '<hr>',
            'type' => 'give_title',
            'id'   => 'give_title_ipay88',
        ),
        array(
            'id'   => 'ipay88_live_merchant_key',
            'name' => esc_html__( 'Live Merchant Key', 'give-ipay88' ),
            'desc' => esc_html__( 'Please enter your iPay88 live merchant key.', 'give-ipay88' ),
            'type' => 'text'
        ),
        array(
            'id'   => 'ipay88_live_merchant_code',
            'name' => esc_html__( 'Live Merchant Code', 'give-ipay88' ),
            'desc' => esc_html__( 'Please enter your iPay88 live merchant code.', 'give-ipay88' ),
            'type' => 'text'
        ),
        array(
            'id'   => 'ipay88_test_merchant_key',
            'name' => esc_html__( 'Test Merchant Key', 'give-ipay88' ),
            'desc' => esc_html__( 'Please enter your iPay88 test merchant key.', 'give-ipay88' ),
            'type' => 'text'
        ),
        array(
            'id'   => 'ipay88_test_merchant_code',
            'name' => esc_html__( 'Test Merchant Code', 'give-ipay88' ),
            'desc' => esc_html__( 'Please enter your iPay88 test merchant code.', 'give-ipay88' ),
            'type' => 'text'
        )
    );

    return array_merge( $settings, $give_settings );
}
add_filter( 'give_settings_gateways', 'give_add_ipay88_settings' );

function iPay88_signature($source) {
        return base64_encode(hex2bins(sha1($source)));
}

function hex2bins($hexSource) {
    $bin = "";
    for ($i=0;$i<strlen($hexSource);$i=$i+2)
    {
    $bin .= chr(hexdec(substr($hexSource,$i,2)));
    }
    return $bin;
}
/** include_once 'custom_fields.php'; **/