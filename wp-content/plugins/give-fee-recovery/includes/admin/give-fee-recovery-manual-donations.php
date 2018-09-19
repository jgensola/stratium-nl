<?php
/**
 * Give Fee Recovery functions for Manual Donation Add-on.
 *
 * @package    Give_Fee_Recovery
 * @subpackage Give_Fee_Recovery/includes
 * @author     WordImpress <https://wordimpress.com>
 */



/**
 * Add new header to donation table in manual Donation Page
 *
 * @since 1.4
 */
function give_fee_donation_table_head_for_donation_fees() {
	?>
	<th style="padding: 10px;">
		<?php _e( 'Fee Percentage', 'give-fee-recovery' ); ?>
		<span class="give-percentage-symbol-before">(%)</span>
	</th>

	<th style="padding: 10px;">
		<?php _e( 'Additional Fee Amount', 'give-fee-recovery' ); ?>
	</th>
	<?php

}

add_action( 'give_md_donation_table_head', 'give_fee_donation_table_head_for_donation_fees' );

/**
 * Add new body to donation table in manual Donation Page
 *
 * @since 1.4
 */
function give_fee_donation_table_body_for_donation_fees() {
	$percentage  = give_fee_default_fee_percentage_manual();
	$base_amount = give_fee_default_fee_amount_manual();
	?>
	<td>
		<input type="text" name="give_fee_percentage" id="give_fee_percentage"
		       value="<?php echo $percentage; ?>"
		       placeholder="<?php echo $percentage; ?>"
		       class="give-field give-fee-recovery-field give-text_small give-fee_gateway_field_value">
	</td>

	<td>
		<input type="text" name="give_fee_base_amount" id="give_fee_base_amount"
		       value="<?php echo $base_amount; ?>"
		       placeholder="<?php $base_amount; ?>" class="give-field give-text_small give-price-field">
	</td>
	<?php
}

add_action( 'give_md_donation_table_body', 'give_fee_donation_table_body_for_donation_fees' );

/**
 * Update amount when fees check is checked
 *
 * @since 1.4
 *
 * @param $payment
 *
 * @return $payment
 */
function give_fee_donation_total_fees_manual( $payment ) {
	if ( empty( $_POST['give_fee_mode_checkbox'] ) ) {
		return $payment;
	}

	$give_total = give_maybe_sanitize_amount( $payment->total );

	$percentage = ! empty( $_POST['give_fee_percentage'] ) ? (float) $_POST['give_fee_percentage'] : '0.00';

	$base_amount = ! empty( $_POST['give_fee_base_amount'] ) ? (float) $_POST['give_fee_base_amount'] : '0.00';

	$give_fee = give_fee_calculate( $percentage, $base_amount, $give_total, false );

	$give_total += $give_fee;

	$payment->total = give_sanitize_amount_for_db( $give_total );

	return $payment;
}

add_filter( 'give_manual_before_payment_add', 'give_fee_donation_total_fees_manual' );


/**
 * Update Fees meta in Donation that are being add from Donation Manual Page
 *
 * @since 1.4
 *
 * @param $payment_id
 * @param $payment
 * @param $data
 */
function give_fee_manual_insert_payment_fees( $payment_id, $payment, $data ) {
	// Is form fees is included
	if ( ! empty( $_POST['give_fee_mode_checkbox'] ) ) {

		$total   = $payment->total;

		$donation_amount = give_sanitize_amount_for_db( $data['forms']['amount'] );
		give_update_payment_meta( $payment_id, '_give_fee_donation_amount', $donation_amount );

		$donation_fees = $total - $donation_amount;
		give_update_payment_meta( $payment_id, '_give_fee_amount', $donation_fees );
	}
}

add_action( 'give_manual_insert_payment', 'give_fee_manual_insert_payment_fees', 10, 3 );

/**
 * Get the default value from form and or option key.
 *
 * * @since 1.4
 *
 * @param $form_id
 * @param string $meta_key
 * @param string $option_key
 * @param string $type
 * @param string $default_meta_value
 * @param string $fetch_from_option
 */
function give_fee_check_for_key( $form_id, $meta_key = '', $option_key = '', $default_meta_value = 'global', $fetch_from_option = 'global', $type = 'string' ) {
	if ( empty( $meta_key ) || empty( $option_key ) ) {
		return $default_meta_value;
	}

	$return = array();

	// Get the value of fee recovery enable or not.
	$is_fee_recovery = give_get_meta( $form_id, $meta_key, true );
	$is_fee_recovery = ! empty( $is_fee_recovery ) ? $is_fee_recovery : $default_meta_value;

	$return['key'] = 'meta_key';

	if ( $fetch_from_option === $is_fee_recovery ) {
		$return['key']   = 'option_key';
		$is_fee_recovery = give_get_option( $option_key, $default_meta_value );
	}

	$return['value'] = $is_fee_recovery;

	return ( 'string' === $type ) ? $return['value'] : $return;
}

/**
 * Modity Responce if Form is has Give fees Add-on options.
 *
 * @since 1.4
 *
 * @param array $response
 * @param int/bool $form_id
 *
 * @return array $response
 */
function give_fee_check_form_setup_response_for_fees( $response, $form_id ) {
	if ( empty( $form_id ) ) {
		return $response;
	}

	$form_fees           = array();
	$form_fees['status'] = false;

	$fee_value = give_fee_check_for_key( $form_id, '_form_give_fee_recovery', 'give_fee_recovery', 'global', 'global', 'array' );

	if ( 'enabled' === $fee_value['value'] ) {

		$form_fees['status'] = true;
		$form_fees['key']    = $fee_value['key'];

		ob_start();
		Give_Fee_Recovery()->plugin_public->hidden_field_data( $form_id, array() );
		$hidden_field = ob_get_clean();

		$form_fees['hidden'] = $hidden_field;
	}

	$form_fees['message']  = __( 'Would you like to add the transaction fees of %s to the donation.', 'give-fee-recovery' );

	$form_fees['message'] = sprintf( $form_fees['message'], '{fees}' );

	$form_fees['checkbox'] = '<input name="give_fee_mode_checkbox" type="checkbox" id="give_fee_mode_checkbox" class="give_fee_mode_checkbox" value="1">';

	$response['fee'] = $form_fees;

	return $response;
}

add_filter( 'give_md_check_form_setup_response', 'give_fee_check_form_setup_response_for_fees', 10, 2 );



/**
 * Get default percentage for donation in Give Manual Donation Page.
 * Note: Only internal use for Manual Donation.
 *
 * @since 1.4
 *
 * @param float $default Fees Percentage.
 *
 * @return float Fees Percentage.
 */
function give_fee_default_fee_percentage_manual( $default = 2.90 ) {
	// Get Global Fee Percentage because Gateway set is All Gateway.
	$percentage = (float) give_get_option( 'give_fee_percentage' );

	if ( empty( $percentage ) ) {
		$percentage = $default;
	}

	return give_format_decimal( $percentage );
}

/**
 * Get default amount for donation in Give Manual Donation Page.
 * Note: Only internal use for Manual Donation.
 *
 * @since 1.4
 *
 * @param float $default Fees Amount.
 *
 * @return float Fees Amount.
 */
function give_fee_default_fee_amount_manual( $default = 0.30 ) {
	// Get Global Fee base amount because Gateway set is All Gateway.
	$base_amount = (float) give_get_option( 'give_fee_base_amount' );

	if ( empty( $base_amount ) ) {
		$base_amount = $default;
	}

	return give_format_decimal( $base_amount );
}