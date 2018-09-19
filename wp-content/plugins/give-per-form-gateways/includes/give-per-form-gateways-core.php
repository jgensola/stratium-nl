<?php
/**
 * Give Per Form Gateways functions.
 *
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Per_Form_Gateways_Core
 */
class Give_Per_Form_Gateways_Core {


	/**
	 * The form ID.
	 *
	 * @var string
	 */
	var $form_id;

	/**
	 * Give_Per_Form_Gateways_Core constructor.
	 */
	public function __construct() {

		add_filter( 'give_pre_form', array( $this, 'set_form_id' ), 1, 2 );

	}

	/**
	 * Set the Form ID with an earlier action than `give_payment_gateways`.
	 *
	 * Allows to query the form for per-form gateways.
	 *
	 * @param $form_id
	 * @param $args
	 */
	function set_form_id( $form_id, $args ) {

		//Set form ID.
		$this->form_id = $form_id;
		add_filter( 'give_payment_gateways', array( $this, 'filter_gateways' ) );

	}

	/**
	 * Filter payment gateways on the donation form.
	 *
	 * @access      public
	 * @since       1.0
	 *
	 * @param       array $gateways The available gateways
	 *
	 * @return      array $gateways The allowed gateways
	 */
	public function filter_gateways( $gateways ) {

		if ( ! is_admin() ) {

			$allowed = $gateways;

			foreach ( $gateways as $key => $gateway ) {
				if ( $this->is_form_gateway_per_form( $this->form_id ) ) {
					if ( ! $this->is_form_gateway_enabled( $this->form_id, $key ) ) {
						unset( $allowed[ $key ] );
					}
				}
			}

			$gateways = $allowed;
		}

		if ( empty( $gateways ) ) {
			$message = give_get_option( 'give_per_form_gateways_payment_error', __( 'This donation form has no available payment methods. Please try your donation again later.', 'give-per-form-gateways' ) );

			give_set_error( 'no-allowed-gateways', $message );
		}

		return $gateways;

	}


	/**
	 * Check if a gateway is allowed for a given donation form.
	 *
	 * @since       1.0
	 *
	 * @param       int $form_id The ID of a given download
	 * @param       string $gateway The gateway to check
	 *
	 * @return      bool $allowed True if allowed, false otherwise
	 */
	public function is_form_gateway_enabled( $form_id, $gateway ) {
		$allowed  = true;
		$gateways = get_post_meta( $form_id, '_give_per_form_gateways', true );

		if ( is_array( $gateways ) && ! array_key_exists( $gateway, $gateways ) ) {
			$allowed = false;
		}

		return $allowed;
	}


	/**
	 * Check if a donation form is limited to a gateway.
	 *
	 * @since       1.0
	 *
	 * @param       $form_id int The ID of a given donation form
	 *
	 * @param $form_id
	 *
	 * @return bool True if a donation form is limited, false otherwise
	 */
	function is_form_gateway_per_form( $form_id ) {

		$limited  = false;
		$gateways = get_post_meta( $form_id, '_give_per_form_gateways', true );

		if ( is_array( $gateways ) ) {
			$limited = true;
		}

		return $limited;
	}


}

new Give_Per_Form_Gateways_Core();