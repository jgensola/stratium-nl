<?php
/**
 * Plugin Name: Give - MailChimp
 * Plugin URI: https://givewp.com/addons/mailchimp/
 * Description: Easily integrate MailChimp opt-ins within your Give donation forms.
 * Version: 1.4.1
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Contributors: wordimpress
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
if ( ! defined( 'GIVE_MAILCHIMP_VERSION' ) ) {
	define( 'GIVE_MAILCHIMP_VERSION', '1.4.1' );
}
if ( ! defined( 'GIVE_MAILCHIMP_MIN_GIVE_VER' ) ) {
	define( 'GIVE_MAILCHIMP_MIN_GIVE_VER', '2.0' );
}
if ( ! defined( 'GIVE_MAILCHIMP_STORE_API_URL' ) ) {
	define( 'GIVE_MAILCHIMP_STORE_API_URL', 'https://givewp.com' );
}
if ( ! defined( 'GIVE_MAILCHIMP_PRODUCT_NAME' ) ) {
	define( 'GIVE_MAILCHIMP_PRODUCT_NAME', 'MailChimp' );
}
if ( ! defined( 'GIVE_MAILCHIMP_FILE' ) ) {
	define( 'GIVE_MAILCHIMP_FILE', __FILE__ );
}
if ( ! defined( 'GIVE_MAILCHIMP_PATH' ) ) {
	define( 'GIVE_MAILCHIMP_PATH', dirname( GIVE_MAILCHIMP_FILE ) );
}
if ( ! defined( 'GIVE_MAILCHIMP_URL' ) ) {
	define( 'GIVE_MAILCHIMP_URL', plugin_dir_url( GIVE_MAILCHIMP_FILE ) );
}
if ( ! defined( 'GIVE_MAILCHIMP_BASENAME' ) ) {
	define( 'GIVE_MAILCHIMP_BASENAME', plugin_basename( GIVE_MAILCHIMP_FILE ) );
}
if ( ! defined( 'GIVE_MAILCHIMP_DIR' ) ) {
	define( 'GIVE_MAILCHIMP_DIR', plugin_dir_path( GIVE_MAILCHIMP_FILE ) );
}


/**
 * Give - MailChimp Add-on Licensing
 */
function give_add_mailchimp_licensing() {
	if ( class_exists( 'Give_License' ) ) {
		new Give_License( GIVE_MAILCHIMP_FILE, GIVE_MAILCHIMP_PRODUCT_NAME, GIVE_MAILCHIMP_VERSION, 'WordImpress' );
	}
}

add_action( 'plugins_loaded', 'give_add_mailchimp_licensing' );


/**
 * Load the plugin's textdomain
 */
function give_mailchimp_textdomain() {

	// Set filter for language directory.
	$lang_dir = GIVE_MAILCHIMP_DIR . '/languages/';
	$lang_dir = apply_filters( 'give_mailchimp_languages_directory', $lang_dir );

	// Traditional WordPress plugin locale filter.
	$locale = apply_filters( 'plugin_locale', get_locale(), 'give-mailchimp' );
	$mofile = sprintf( '%1$s-%2$s.mo', 'give-mailchimp', $locale );

	// Setup paths to current locale file.
	$mofile_local  = $lang_dir . $mofile;
	$mofile_global = WP_LANG_DIR . '/give-mailchimp/' . $mofile;

	if ( file_exists( $mofile_global ) ) {
		// Look in global /wp-content/languages/give-mailchimp/ folder.
		load_textdomain( 'give-mailchimp', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) {
		// Look in local /wp-content/plugins/give-mailchimp/languages/ folder.
		load_textdomain( 'give-mailchimp', $mofile_local );
	} else {
		// Load the default language files.
		load_plugin_textdomain( 'give-mailchimp', false, $lang_dir );
	}

	// Load the translations
	load_plugin_textdomain( 'give-mailchimp', false, GIVE_MAILCHIMP_PATH . '/languages/' );
}

add_action( 'init', 'give_mailchimp_textdomain' );


/**
 * Give MailChimp Includes
 */
function give_mailchimp_includes() {

	include( GIVE_MAILCHIMP_PATH . '/includes/give-mailchimp-activation.php' );

	if ( ! class_exists( 'Give' ) ) {
		return false;
	}

	if ( class_exists( 'Give_Manual_Donations' ) && is_admin() ) {
		/**
		 * Give Mailchimp helper functions for manual Donation Add-on.
		 */
		include( GIVE_MAILCHIMP_PATH . '/includes/give-mailchimp-manual-donations.php' );
	}

	if ( ! class_exists( 'Give_Newsletter' ) ) {
		include( GIVE_MAILCHIMP_PATH . '/includes/class-give-newsletter.php' );
	}

	if ( ! class_exists( 'Give_MailChimp_API' ) ) {
		include( GIVE_MAILCHIMP_PATH . '/includes/class-give-mailchimp-api.php' );
	}

	if ( ! class_exists( 'Give_MailChimp' ) ) {
		include( GIVE_MAILCHIMP_PATH . '/includes/class-give-mailchimp.php' );
	}

	if ( ! class_exists( 'Give_MC_Ecommerce_360' ) ) {
		include( GIVE_MAILCHIMP_PATH . '/includes/class-give-ecommerce360.php' );
	}

	new Give_MailChimp( 'mailchimp', 'MailChimp' );
	new Give_MC_Ecommerce_360;

}

add_action( 'plugins_loaded', 'give_mailchimp_includes' );

/**
 * Append field value to MailChimp data array.
 *
 * @since 1.4
 *
 * @param array $api_fields MailChimp data.
 *
 * @return array $api_fields
 */
function give_mc_ffm_fields( $api_fields ) {

	$give_options = give_get_settings();

	// Get submitted post data.
	$posted_data = array_map( 'give_clean', $_POST );
	$form_id     = absint( $posted_data['give-form-id'] );

	// Get all of the activated lists.
	$form_lists = give_get_meta( $form_id, '_give_mailchimp', true );

	// Should the opt-on be checked or unchecked by default?
	$mailchimp_send_donation_data = give_get_meta( $form_id, '_give_mailchimp_send_donation_data', true );
	$mailchimp_send_donation_data = ! empty( $mailchimp_send_donation_data ) ?
		$mailchimp_send_donation_data
		: ( ! empty( $give_options['give_mailchimp_donation_data'] )
			? $give_options['give_mailchimp_donation_data']
			: 'on'
		);

	if ( ! give_is_setting_enabled( $mailchimp_send_donation_data ) ) {
		return $api_fields;
	}

	// Check if $form_lists is set.
	$form_lists = empty( $form_lists ) ? give_get_option( 'give_mailchimp_list' ) : $form_lists;

	/**
	 * List of the fields to be passed in Mail-Chimp.
	 *
	 * @since 1.4
	 *
	 * Eg.
	 *      'MERGE_FIELD_TAG' <- Should not exceed string length 10.
	 *              => Label : 'Field Label'
	 *              => Value : 'Field Value'
	 *              [ Extra arguments... ]
	 */
	$donation_data = array(
		'FORM_ID'    => array(
			'label' => __( 'Form ID', 'give-mailchimp' ),
			'value' => $posted_data['give-form-id'],
		),
		'FORM_TITLE' => array(
			'label' => __( 'Form Title', 'give-mailchimp' ),
			'value' => $posted_data['give-form-title'],
		),
		'GATEWAY'    => array(
			'label' => __( 'Payment Gateway', 'give-mailchimp' ),
			'value' => $posted_data['give-gateway'],
		),
		'AMOUNT'     => array(
			'label' => __( 'Donation Amount', 'give-mailchimp' ),
			'value' => give_format_amount( $posted_data['give-amount'] ),
		),
	);

	// WP User ID
	if ( isset( $posted_data['give-user-id'] ) && ! empty( $posted_data['give-user-id'] ) ) {
		$donation_data['USER_ID'] = array(
			'label' => __( 'WP User ID', 'give-mailchimp' ),
			'value' => $posted_data['give-user-id'],
		);
	}

	// Support for Recurring
	if ( isset( $posted_data['_give_is_donation_recurring'] ) && ! empty( $posted_data['_give_is_donation_recurring'] ) ) {

		// Get period / pretty frequency.
		$frequency = isset( $posted_data['give-recurring-period-donors-choice'] ) ? $posted_data['give-recurring-period-donors-choice'] : '';

		if ( function_exists( 'give_recurring_pretty_subscription_frequency' ) && ! empty( $frequency ) ) {
			$frequency = give_recurring_pretty_subscription_frequency( $frequency );
		}

		$donation_data['RECURRING'] = array(
			'label' => __( 'Recurring Donation', 'give-mailchimp' ),
			'value' => ( ! empty( $frequency ) ? $frequency : 'TRUE' ),
		);
	}

	// Support for Fee Recovery
	if ( isset( $posted_data['give-fee-mode-enable'] ) && ! empty( $posted_data['give-fee-mode-enable'] ) ) {
		$donation_data['FEE'] = array(
			'label' => __( 'Fee Recovery', 'give-mailchimp' ),
			'value' => give_format_amount( $posted_data['give-fee-amount'] ),
		);
	}

	/**
	 * Array of data which will be sent to MailChimp.
	 *
	 * @since 1.4
	 *
	 * @param array   $donation_data Submitted data.
	 * @param integer $form_id       Donation Form ID.
	 */
	$donation_data = apply_filters( 'give_mailchimp_pass_fields', $donation_data, $posted_data['give-form-id'] );

	// Get all of the list to be subscribed.
	$activated_lists = is_array( $form_lists ) ? $form_lists : (array) $form_lists;

	foreach ( $activated_lists as $list_id ) {
		foreach ( $donation_data as $field_tag => $field ) {
			// Create merge field in Mail-Chimp if not exists.
			$api_fields['merge_vars'][ $field_tag ] = give_mc_create_merge_field( $list_id, $field, $field_tag );
		}
	}

	return $api_fields;
}

// Add FFM fields.
add_filter( 'give_mc_subscribe_vars', 'give_mc_ffm_fields', 10, 1 );

/**
 * MailChimp create field if not available.
 *
 * @since 1.4
 *
 * @param $list_id
 * @param $field
 *
 * @return array
 */
function give_mc_create_merge_field( $list_id, $field, $tag ) {
	global $give_options;

	/** @var \Give_MailChimp_API $api */
	$api = new Give_MailChimp_API( trim( $give_options['give_mailchimp_api'] ) );

	// API call to get all of the existing merge field(s).
	$var_lists = $api->call_merge_field_request( $list_id, array() );

	// If merge field array is not empty.
	$exists_merge_fields = isset( $var_lists->merge_fields ) ? $var_lists->merge_fields : array();

	if ( ! empty( $exists_merge_fields ) ) {

		// If tag is not exists in MailChimp.
		if ( ! in_array( $tag, array_column( $exists_merge_fields, 'tag' ), true ) ) {

			$api_args = array(
				'name' => $field['label'],
				'type' => give_mc_get_ffm_field_type( $field ),
				'tag'  => $tag,
			);

			/**
			 * Alter Api args while processing fields for MailChimp.
			 *
			 * @since 1.4
			 *
			 * @param array  $api_args API Arguments
			 * @param array  $field    Field array.
			 * @param string $list_id  List ID.
			 */
			$api_args = apply_filters( 'give_mailchimp_processing_field', $api_args, $field, $list_id );

			// Create merge field in MailChimp for specific list.
			$api->call_merge_field_request( $list_id, $api_args, 'POST' );
		}
	}

	// Get the field value.
	$field_value = isset( $field['name'] ) ? $_POST[ $field['name'] ] : $field['value'];

	// If field value is array.
	$field_value = is_array( $field_value ) ? ( ( 1 === count( $field_value ) ) ? $field_value[0] : json_encode( $field_value ) ) : give_clean( $field_value );

	// Return field value.
	return $field_value;
}

/**
 * Get the type of the field for MailChimp.
 *
 * @since 1.4
 *
 * @param array $field Field options.
 *
 * @return bool|mixed
 */
function give_mc_get_ffm_field_type( $field ) {
	$field_type = ( isset( $field['input_type'] ) ) ? $field['input_type'] : '';

	switch ( $field_type ) {
		case 'date':
			$type = 'date';
			break;
		case 'radio':
			$type = 'radio';
			break;
		case 'select':
			$type = 'dropdown';
			break;
		case 'url':
		case 'phone':
			$type = $field_type;
			break;
		case 'checkbox':
		case 'email':
		case 'text':
		case 'hidden':
		case 'textarea':
		case 'html':
		default:
			$type = 'text';
			break;
	}

	/**
	 * Alter field type.
	 *
	 * @since 1.4
	 */
	return apply_filters( 'give_mc_ffm_field_type', $type, $field );
}

/**
 * Return if FFM is activated or not.
 *
 * @since 1.4
 *
 * @return bool
 */
function give_mc_ffm_is_activated() {
	return class_exists( 'Give_Form_Fields_Manager' );
}


/**
 * Pass FFM fields to pass in MailChimp.
 *
 * @since 1.4
 *
 * @param array   $fields_array Fields array.
 * @param integer $form_id      Form ID.
 *
 * @return mixed
 */
function give_mc_ffm_add_fields( $fields_array, $form_id ) {

	// If form field manager is not activated.
	if ( ! class_exists( 'Give_Form_Fields_Manager' ) ) {
		return $fields_array;
	}

	// Should the opt-on be checked or unchecked by default?
	$mailchimp_ffm_send = give_get_meta( $form_id, '_give_mailchimp_send_ffm', true );
	$mailchimp_ffm_send = ! empty( $mailchimp_ffm_send ) ? $mailchimp_ffm_send : ( ! empty( $give_options['give_mailchimp_ffm_pass_field'] ) ? $give_options['give_mailchimp_ffm_pass_field'] : 'on' );

	if ( ! give_is_setting_enabled( $mailchimp_ffm_send ) ) {
		return $fields_array;
	}

	// Get the ffm fields.
	$ffm_fields = give_get_meta( $form_id, 'give-form-fields', true );

	foreach ( $ffm_fields as $ffm_field ) {

		$field_label = '';

		if ( isset( $ffm_field['label'] ) && ! empty( $ffm_field['label'] ) ) {
			$field_label = $ffm_field['label'];
		}

		$field_label = apply_filters( 'give_mailchimp_ffm_field_label', $field_label, $form_id, $fields_array );

		if ( array_key_exists( $ffm_field['name'], $_POST ) && ! empty( $field_label ) ) {

			// Generate MailChimp Merge field's tag name.
			$mailchimp_tag_name = substr( strtoupper( str_replace( ' ', '_', $field_label ) ), 0, 10 );

			// Get all fields.
			$fields_array[ $mailchimp_tag_name ] = array(
				'label'      => give_clean( $field_label ),
				'value'      => $_POST[ $ffm_field['name'] ],
				'input_type' => $ffm_field['input_type'],
			);

			// Get the FFM field type.
			$field_type = give_mc_get_ffm_field_type( $ffm_field );

			// If type is date.
			if ( 'date' === $field_type ) {
				$fields_array[ $mailchimp_tag_name ]['args']['date_format'] = $ffm_field['format'];
			} else if ( in_array( $field_type, array( 'dropdown', 'radio' ), 1 ) ) {
				$fields_array[ $mailchimp_tag_name ]['args']['choices'] = $ffm_field['options'];
			}
		}
	}

	return apply_filters( 'give_mc_ffm_add_fields', $fields_array );

}

// Add FFM field(s) to be passed in MailChimp.
add_filter( 'give_mailchimp_pass_fields', 'give_mc_ffm_add_fields', 10, 2 );

/**
 * Set field type for FFM field.
 *
 * @since 1.4
 *
 * @param array $api_arg MC API Arguments.
 * @param array $field   Field arguments.
 *
 * @return mixed
 */
function give_mc_ffm_processing_field( $api_arg, $field ) {

	// Get the field type.
	$field_type = $api_arg['type'];

	// If field type is date then add 'date' format.
	if ( 'date' === $field_type ) {
		$api_arg['options']['date_format'] = $field['args']['date_format'];
	} else if ( in_array( $api_arg['type'], array( 'dropdown', 'radio' ), 1 ) ) {
		// ...Or if field type is drop-down or radio then include choices.
		$api_arg['options']['choices'] = $field['args']['choices'];
	}

	return $api_arg;
}

// Add field additional option while sending them to MailChimp account.
add_filter( 'give_mailchimp_processing_field', 'give_mc_ffm_processing_field', 10, 2 );
