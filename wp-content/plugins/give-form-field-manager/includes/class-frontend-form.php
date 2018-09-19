<?php
/**
 * Form Field Manager Frontend Form
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_FFM_Frontend_Form
 */
class Give_FFM_Frontend_Form extends Give_FFM_Render_Form {

	/**
	 * Instance
	 *
	 * @var $_instance
	 */
	private static $_instance;

	/**
	 * Give_FFM_Frontend_Form constructor.
	 */
	function __construct() {

		// Add FFM to donation forms.
		add_action( 'give_insert_payment', array( $this, 'submit_post' ), 10, 2 );
		add_action( 'give_pre_form_output', array( $this, 'place_fields' ), 10, 1 );

		// Used to place fields after the donor AJAX switches payment methods before give version 1.8.8.
		add_action( 'give_donation_form_top', array( $this, 'place_fields' ), 10, 1 );

		// Used to place fields after the donor AJAX switches payment methods after give version 1.8.9.
		add_action( 'give_payment_fields_top', array( $this, 'place_fields' ), 10, 1 );

		// Donation Receipt.
		add_filter( 'shortcode_atts_give_receipt', array( $this, 'add_donation_receipt_attr' ), 10, 3 );
		add_action( 'give_payment_receipt_after', array( $this, 'donation_receipt' ), 10, 2 );

		add_action( 'give_checkout_before_gateway', array( $this, 'set_cookies_after_validations' ), 10, 1 );

		add_action( 'give_pre_process_donation', array( $this, 'validate_required_fields' ), 10, 1 );
	}

	/**
	 * Init
	 *
	 * @return Give_FFM_Frontend_Form
	 */
	public static function init() {
		if ( ! self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Set Cookie after validations.
	 *
	 * @param array $posted_data Posted Data.
	 *
	 * @since 1.2.6
	 */
	function set_cookies_after_validations( $posted_data ) {
		if( ! isset( $posted_data['give_ajax'] ) ) {
			setcookie( 'ffm-flush-form', $posted_data['give-form-id'], 0, '/' . get_post_field( 'post_name', give_get_option( 'success_page' ) ) );
		}
	}

	/**
	 * Place Fields
	 *
	 * Uses various Give forms actions to output the custom fields.
	 *
	 * @param int $form_id Form ID.
	 *
	 * @updated 1.2
	 */
	public function place_fields( $form_id ) {

		$ffm_placement = give_get_meta( $form_id, '_give_ffm_placement', true );
		$ffm_fields    = give_get_meta( $form_id, 'give-form-fields', true );

		// Bail out, if FFM fields for a form doesn't exists.
		if ( empty( $ffm_fields ) ) {
			return;
		}

		// Bail out, if FFM Placement is not defined.
		if ( empty( $ffm_placement ) ) {
			return;
		}

		// Don't wrap add_action in did_action hook.
		// This can lead to conflict with FFM fields when multiple forms called in single page.
		add_action( $ffm_placement, array( $this, 'add_fields' ), 10, 1 );

	}

	/**
	 * Add Fields.
	 *
	 * @param int $form_id Form ID.
	 *
	 * @updated 1.2.4
	 */
	public function add_fields( $form_id ) {
		$ffm_placement = give_get_meta( $form_id, '_give_ffm_placement', true );

		// Bail out, if current action doesn't match with ffm placement action.
		if ( current_action() !== $ffm_placement ) {
			return;
		}

		ob_start();
		$this->render_form( $form_id );
		$content = ob_get_contents();
		ob_end_clean();
		echo $content;
	}


	/**
	 * Validates if required fields are filled.
	 *
	 * @since 1.2.9
	 * @return void
	 */
	public function validate_required_fields() {

		$posted_data = is_array( $_POST ) ? give_clean( $_POST ) : array();

		$form_id         = ! empty( $posted_data['give-form-id'] ) ? $posted_data['give-form-id'] : '';
		$form_vars       = $this->get_input_fields( $form_id );
		$required_fields = give_get_meta( $form_id, 'give-form-required-fields', true );
		$required_flag   = false;

		list( $post_vars, $tax_vars, $meta_vars ) = $form_vars;

		// Ensure that required fields are not empty.
		if ( empty( $required_fields ) || empty( $meta_vars ) ) {
			return;
		}

		/**
		 * Fields like radio, checkbox and file upload don't show up in
		 * $_POST unless they are filled. To following
		 * loop takes care of that.
		 */
		foreach ( $required_fields as $key => $value ) {
			if ( ! array_key_exists( $key, $posted_data ) ) {
				$required_flag = true;

				break;
			}
		}

		/**
		 * The following loop takes care of fields such
		 * as text, textarea, email, phone and url.
		 */
		foreach ( $meta_vars as $key => $value ) {

			$valid_data[ $value['name'] ] = ! empty( $posted_data[ $value['name'] ] ) ? $posted_data[ $value['name'] ] : '';

			if ( true === $required_flag ) {
				break;
			}

			if ( 'yes' === $value['required'] ) {
				switch ( $value['input_type'] ) {
					case 'text':
					case 'textarea':
					case 'email':
					case 'phone':
					case 'url':
					case 'date':
						if ( empty( $valid_data[ $value['name'] ] ) ) {
							$required_flag = true;
						}

						break;

					case 'select':
					case 'multiselect':
						if ( empty( $valid_data[ $value['name'] ][0] ) ) {
							$required_flag = true;
						}

						break;

					case 'repeat':
						$repeat_fields = $valid_data[ $value['name'] ];

						foreach ( $repeat_fields as $data ) {
							if ( empty( $data ) ) {
								$required_flag = true;
							}
						}

						break;

					default:
						break;
				}
			} // End if().
		} // End foreach().

		if ( $required_flag ) {
			give_set_error( 'incomplete-required-fields', sprintf( __( 'Please complete all required fields', 'give-form-field-manager' ) ) );
		}

		/**
		 * Hook that fires after validating all the
		 * FFM required fields.
		 *
		 * @since 1.2.9
		 */
		do_action( 'give_ffm_fields_validated' );
	}


	/**
	 * Submit Post.
	 *
	 * @param int   $payment      Payment ID.
	 * @param array $payment_data Payment Data.
	 */
	public function submit_post( $payment, $payment_data ) {

		$form_id       = $payment_data['give_form_id'];
		$form_vars     = $this->get_input_fields( $form_id );
		$form_settings = give_get_meta( $form_id, 'give-form-fields_settings', true );

		list( $post_vars, $tax_vars, $meta_vars ) = $form_vars;

		$post_id = $payment;

		self::update_post_meta( $meta_vars, $post_id, $form_vars );

		// Set the post form_id for later usage.
		update_post_meta( $post_id, self::$config_id, $form_id );

	}

	/**
	 * Update Post Meta.
	 *
	 * Updates individual meta fields and _give_payment_meta as
	 * an array of all meta fields combined.
	 *
	 * @param $meta_vars
	 * @param $post_id
	 * @param $form_vars
	 */
	public static function update_post_meta( $meta_vars, $post_id, $form_vars ) {
		// Prepare the meta vars.
		list( $meta_key_value, $multi_repeated, $files ) = self::prepare_meta_fields( $meta_vars );

		// Get default payment meta so we can add to it below.
		$default_meta = give_get_meta( $post_id, '_give_payment_meta', true );

		// Array of file fields formatted as key-value pairs.
		$files_key_value = array();

		// Save custom fields.
		foreach ( $form_vars[2] as $key => $value ) {
			if ( isset( $_POST[ $value['name'] ] ) ) {
				update_post_meta( $post_id, $value['name'], $_POST[ $value['name'] ] );
			}
		}

		// Save all custom fields.
		foreach ( $meta_key_value as $meta_key => $meta_value ) {
			$values     = array_values( array_filter( give_clean( explode( '|', $meta_value ) ) ) );
			$meta_value = implode( ' | ', $values );
			update_post_meta( $post_id, $meta_key, $meta_value );
		}

		// Save any multi column repeatable fields.
		foreach ( $multi_repeated as $repeat_key => $repeat_value ) {
			// First, delete any previous repeatable fields.
			delete_post_meta( $post_id, $repeat_key );
			// Now add them.
			foreach ( $repeat_value as $repeat_field ) {
				// Filter out the empty value for repeater fields.
				if( '|' !== give_clean( $repeat_field ) ) {
					add_post_meta( $post_id, $repeat_key, $repeat_field );
				}
			}
		}

		// Save any files attached.
		foreach ( $files as $file_input ) {
			// Delete any previous value.
			delete_post_meta( $post_id, $file_input['name'] );
			foreach ( $file_input['value'] as $attachment_id ) {
				give_ffm_associate_attachment( $attachment_id, $post_id );
				add_post_meta( $post_id, $file_input['name'], $attachment_id );
				$files_key_value[ $file_input['name'] ] = $file_input['value'];
			}
		}

		// Combine all meta fields.
		$all_meta = array_merge(
			$default_meta, // meta associated with all Give donations (user_info, email, date, etc.).
			$meta_key_value, // singular custom fields added via FFM.
			$multi_repeated, // multi-column repeatable custom fields added via FFM.
			$files_key_value // file fields added via FFM.
		);

		// update one meta field with array of all meta fields combined.
		update_post_meta( $post_id, '_give_payment_meta', $all_meta );
	}

	/**
	 * Required Fields
	 *
	 * @param bool|false $fields
	 *
	 * @return array|bool
	 */
	public static function req_fields( $fields = false ) {
		$form_id   = get_option( 'give_ffm_id' );
		$form_vars = Give_FFM_Render_Form::get_input_fields( $form_id );
		$new_req   = array();

		foreach ( $form_vars[2] as $key => $value ) {
			if ( isset ( $value['required'] ) && $value['required'] == 'yes' ) {
				$new_req[ $value['name'] ] = array(
					'error_id'      => 'invalid_' . $value['name'],
					'error_message' => __( 'Please enter ', 'give' ) . strtolower( $value['label'] )
				);
			}
		}

		$fields = array_merge( $fields, $new_req );

		return $fields;
	}


	/**
	 *
	 * @param $payment
	 * @param $give_receipt_args
	 */
	public function donation_receipt( $payment, $give_receipt_args ) {

	}

	/**
	 * Add Donation Receipt Args
	 *
	 * Adds the `custom_fields` attribute to the [give_receipt] shortcode so that custom fields can be output if admin desires.
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function add_donation_receipt_attr( $out, $pairs, $atts ) {

		$out['custom_fields'] = false;

		return $out;

	}

}