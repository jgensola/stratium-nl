<?php
/**
 * Base newsletter class
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//Load Constant Contact SDK.
include GIVE_CONSTANT_CONTACT_PATH . '/constant-contact-sdk/src/Ctct/autoload.php';
use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;

/**
 * Give_Constant_Contact
 */
class Give_Constant_Contact {

	/**
	 * The ID for this newsletter Add-on, such as 'constant_contact'.
	 */
	public $id;

	/**
	 * The label for the Add-on, probably just shown as the title of the metabox.
	 */
	public $label;

	/**
	 * Newsletter lists retrieved from the API
	 */
	public $lists;

	/**
	 * Text shown on the checkout, if none is set in the settings
	 */
	public $checkout_label;

	/**
	 * Give Options
	 */
	public $give_options;

	/**
	 * Retrieve groups for a list
	 *
	 * @param  string $list_id List id for which groupings should be returned
	 *
	 * @return array  $groups_data Data about the groups
	 */
	public function get_groupings( $list_id = '' ) {
		return array();
	}


	/**
	 * Give_Newsletter constructor.
	 *
	 * @param string $_id
	 * @param string $_label
	 */
	public function __construct( $_id = 'newsletter', $_label = 'Newsletter' ) {

		$this->id    = $_id;
		$this->label = $_label;

		add_action( 'init', array( $this, 'textdomain' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ) );

		add_filter( 'give_settings_addons', array( $this, 'settings' ) );
		add_action( 'give_donation_form_before_submit', array( $this, 'donation_form_field' ), 100, 1 );


		add_action( 'cmb2_save_options-page_fields', array( $this, 'save_settings' ), 10, 4 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 100 );
		add_action( 'give_insert_payment', array( $this, 'completed_donation_signup' ), 10, 2 );

		add_action( 'cmb2_render_give_constant_list_select', array(
			$this,
			'give_constant_list_select',
		), 10, 5 );


		add_action( 'wp_ajax_give_reset_constant_contact_lists', array( $this, 'give_reset_constant_contact_lists' ) );

	}


	/**
	 * Load the plugin's textdomain
	 */
	public function textdomain() {

		// Set filter for language directory.
		$lang_dir = GIVE_CONSTANT_CONTACT_DIR . '/languages/';
		$lang_dir = apply_filters( 'give_constant_contact_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-constant-contact' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-constant-contact', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-constant-contact/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-constant-contact/ folder.
			load_textdomain( 'give-constant-contact', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-constant-contact/languages/ folder.
			load_textdomain( 'give-constant-contact', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'give-constant-contact', false, $lang_dir );
		}
	}

	/**
	 * Load Admin Scripts
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since       1.0
	 * @global       $post
	 *
	 * @param string $hook Page hook
	 *
	 * @return void
	 */
	function admin_scripts( $hook ) {

		global $post_type;

		//Directories of assets
		$js_dir  = GIVE_CONSTANT_CONTACT_URL . 'assets/js/';
		$css_dir = GIVE_CONSTANT_CONTACT_URL . 'assets/css/';

		wp_register_script( 'give_constant_contact_admin_ajax_js', $js_dir . 'admin-ajax.js', array( 'jquery' ) );


		//Forms CPT Script
		if ( $post_type === 'give_forms' ) {

			//CSS
			wp_register_style( 'give_constant_contact_admin_css', $css_dir . 'admin-forms.css', GIVE_CONSTANT_CONTACT_VERSION );
			wp_enqueue_style( 'give_constant_contact_admin_css' );

			//JS
			wp_register_script( 'give-constant-contact-admin-forms-scripts', $js_dir . 'admin-forms.js', array( 'jquery' ), GIVE_CONSTANT_CONTACT_VERSION, false );
			wp_enqueue_script( 'give-constant-contact-admin-forms-scripts' );

			wp_enqueue_script( 'give_constant_contact_admin_ajax_js' );

		}

		//Admin settings.
		if ( $hook == 'give_forms_page_give-settings' ) {

			wp_enqueue_script( 'give_constant_contact_admin_ajax_js' );

		}


	}

	/**
	 * Output the signup checkbox on the checkout screen, if enabled.
	 *
	 * @param int $form_id
	 */
	public function donation_form_field( $form_id ) {

		$enable_constant_contact_form  = get_post_meta( $form_id, '_give_constant_contact_enable', true );
		$disable_constant_contact_form = get_post_meta( $form_id, '_give_constant_contact_disable', true );

		//Check disable vars to see if this form should have the opt-in field.
		if ( ! $this->show_checkout_signup() && $enable_constant_contact_form !== 'true' || $disable_constant_contact_form === 'true' ) {
			return;
		}

		$this->give_options    = give_get_settings();
		$custom_checkout_label = get_post_meta( $form_id, '_give_constant_contact_custom_label', true );

		//What's the label gonna be?
		if ( ! empty( $custom_checkout_label ) ) {
			$this->checkout_label = trim( $custom_checkout_label );
		} elseif ( ! empty( $this->give_options['give_constant_contact_label'] ) ) {
			$this->checkout_label = trim( $this->give_options['give_constant_contact_label'] );
		} else {
			$this->checkout_label = esc_html__( 'Subscribe to our newsletter', 'give-constant-contact' );
		}

		//Should the opt-on be checked or unchecked by default?
		$form_option    = get_post_meta( $form_id, '_give_constant_contact_checked_default', true );
		$checked_option = 'on';

		if ( ! empty( $form_option ) ) {
			//Nothing to do here, option already set above.
			$checked_option = $form_option;
		} elseif ( ! empty( $this->give_options['give_constant_contact_checked_default'] ) ) {
			$checked_option = $this->give_options['give_constant_contact_checked_default'];
		}

		ob_start(); ?>
		<fieldset id="give_<?php echo $this->id . '_' . $form_id; ?>" class="give-constant-contact-fieldset">
			<p>
				<input name="give_<?php echo $this->id; ?>_signup"
				       id="give_<?php echo $this->id . '_' . $form_id; ?>_signup"
				       type="checkbox" <?php echo( $checked_option !== 'no' ? 'checked="checked"' : '' ); ?> />
				<label
					for="give_<?php echo $this->id . '_' . $form_id; ?>_signup"><?php echo $this->checkout_label; ?></label>
			</p>
		</fieldset>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Retrieves the lists from Constant Contact
	 *
	 * @return array|bool
	 */
	public function get_lists() {

		$give_options = give_get_settings();

		//Sanity checks
		if ( empty( $give_options['give_constant_contact_api'] ) || empty( $give_options['give_constant_contact_access_token'] ) ) {
			return false;
		}


		//Transient here?
		$list_data = get_transient( 'give_constant_contact_lists' );

		//New list data wanted?
		if ( false === $list_data ) {

			$cc = new ConstantContact( $give_options['give_constant_contact_api'] );

			// attempt to fetch lists in the account, catching any exceptions and printing the errors to screen
			try {
				$list_data = $cc->getLists( $give_options['give_constant_contact_access_token'] );
			} catch ( CtctException $ex ) {
				foreach ( $ex->getErrors() as $error ) {

					if ( Give()->session->get_session_expiration() ) {
						give_set_error( 'give_cc_list_error', $error['error_message'] );
					} else {
						echo '<div class="error updated"><p>' . $error['error_message'] . '</p></div>';

					}


				}
				if ( ! isset( $lists ) ) {
					$lists = null;
				}
			}

			set_transient( 'give_constant_contact_lists', $list_data, 24 * 24 * 24 );

		}

		//Create array for select field CMB2
		if ( ! empty( $list_data ) ) {

			foreach ( $list_data as $key => $list ) {

				$this->lists[ $list->id ] = $list->name;

			}
		}

		//Return the list data
		return (array) $this->lists;

	}

	/**
	 * Registers the plugin settings
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function settings( $settings ) {

		$give_constant_contact_settings = array(
			array(
				'id'   => 'give_constant_contact_settings',
				'name' => esc_html__( 'Constant Contact Settings', 'give-constant-contact' ),
				'desc' => '<hr>',
				'type' => 'give_title'
			),
			array(
				'id'   => 'give_constant_contact_api',
				'name' => esc_html__( 'API Key', 'give-constant-contact' ),
				'desc' => esc_html__( 'Enter your Constant Contact API Key. You will need to register with the Constant Contact Developer Network to get an API Key.', 'give-constant-contact' ),
				'type' => 'text',
			),
			array(
				'id'   => 'give_constant_contact_access_token',
				'name' => esc_html__( 'Access Token', 'give-constant-contact' ),
				'desc' => esc_html__( 'Enter your Constant Contact Access Token for the API Key above.', 'give-constant-contact' ),
				'type' => 'text',
			),
			array(
				'id'   => 'give_constant_contact_show_checkout_signup',
				'name' => esc_html__( 'Enable Globally?', 'give-constant-contact' ),
				'desc' => esc_html__( 'Allow customers to sign-up for the list selected below on all forms? Note: the list(s) can be customized per form.', 'give-constant-contact' ),
				'type' => 'checkbox'
			),
			array(
				'id'      => 'give_constant_contact_checked_default',
				'name'    => __( 'Opt-in Default', 'give-constant-contact' ),
				'desc'    => __( 'Would you like the newsletter opt-in checkbox checked by default? This option can be customized per form.', 'give-constant-contact' ),
				'options' => array(
					'yes' => __( 'Checked', 'give-constant-contact' ),
					'no'  => __( 'Unchecked', 'give-constant-contact' ),
				),
				'default' => 'yes',
				'type'    => 'radio_inline'
			),
			array(
				'id'   => 'give_constant_contact_list',
				'name' => esc_html__( 'Default List', 'give-constant-contact' ),
				'desc' => esc_html__( 'Enter your List ID. It will be in the form of a number.  Note: the list(s) can be customized per form.', 'give-constant-contact' ),
				'type' => 'give_constant_list_select'
			),
			array(
				'id'         => 'give_constant_contact_label',
				'name'       => esc_html__( 'Global Label', 'give-constant-contact' ),
				'desc'       => esc_html__( 'This is the text shown by default next to the Constant Contact sign up checkbox. Yes, this can also be customized per form.', 'give-constant-contact' ),
				'type'       => 'text',
				'attributes' => array(
					'placeholder' => esc_html__( 'Subscribe to our newsletter', 'give-constant-contact' ),
				),
			),

		);

		return array_merge( $settings, $give_constant_contact_settings );

	}


	/**
	 * Flush the CC list transient on save
	 *
	 * Hooks into CMB2 options save action and deleted transient
	 *
	 * @param $object_id
	 * @param $cmb_id
	 * @param $updated
	 * @param $this_object
	 *
	 * @return mixed
	 */
	public function save_settings( $object_id, $cmb_id, $updated, $this_object ) {

		$api_option = give_get_option( 'give_constant_contact_api' );

		if ( isset( $api_option ) && ! empty( $api_option ) ) {
			delete_transient( 'give_constant_contact_list_data' );
		}

	}

	/**
	 * Determines if the checkout signup option should be displayed
	 */
	public function show_checkout_signup() {
		$give_options = give_get_settings();

		return ! empty( $give_options['give_constant_contact_show_checkout_signup'] );
	}

	/**
	 * Subscribe Email
	 *
	 * Subscribe an email to a list
	 *
	 * @param array $user_info
	 * @param bool  $list_id
	 * @param int   $payment_id
	 *
	 * @return bool
	 */
	public function subscribe_email( $user_info = array(), $list_id = false, $payment_id ) {

		$give_options = give_get_settings();

		//Sanity check: Check to ensure our API keys are present before anything
		if ( ! isset( $give_options['give_constant_contact_api'] ) && strlen( trim( $give_options['give_constant_contact_api'] ) ) !== 0 ) {
			return false;
		}
		if ( ! isset( $give_options['give_constant_contact_access_token'] ) && strlen( trim( $give_options['give_constant_contact_access_token'] ) ) !== 0 ) {
			return false;
		}

		$api_key      = $give_options['give_constant_contact_api'];
		$access_token = $give_options['give_constant_contact_access_token'];

		// check if the form was submitted
		if ( isset( $user_info['email'] ) && strlen( $user_info['email'] ) > 1 ) {

			$action = esc_html__( 'Getting Contact By Email Address', 'give-constant-contact' );
			$cc     = new ConstantContact( $api_key );

			try {
				// check to see if a contact with the email address already exists in the account.
				$response = $cc->getContactByEmail( $access_token, $user_info['email'] );

				// create a new contact if one does not exist
				if ( empty( $response->results ) ) {
					$action = esc_html__( 'Creating Contact', 'give-constant-contact' );

					$contact = new Contact();
					$contact->addEmail( $user_info['email'] );
					$contact->addList( $list_id );
					$contact->first_name = $user_info['first_name'];
					$contact->last_name  = $user_info['last_name'];
					$returnContact       = $cc->addContact( $access_token, $contact );

				} else {
					// update the existing contact if address already existed.
					$action = esc_html__( 'Updating Contact', 'give-constant-contact' );

					$contact = $response->results[0];
					$contact->addList( $list_id );
					$contact->first_name     = $user_info['first_name'];
					$contact->last_name      = $user_info['last_name'];
					$contact->source         = get_bloginfo( 'url' );
					$contact->source_details = isset( $_POST['give-form-title'] ) ? $_POST['give-form-title'] : '';
					$returnContact           = $cc->updateContact( $access_token, $contact );

				}

				// catch any exceptions thrown during the process and print the errors to screen.
			} catch ( CtctException $e ) {

				give_insert_payment_note( $payment_id, __( 'Constant Contact Error: ', 'give-constant-contact' ) . $e->getMessage() );

				return false;

			}
		}

		return false;

	}


	/**
	 * Complete Donation Sign up
	 *
	 * Check if a customer needs to be subscribed on completed donation on a specific form
	 *
	 * @param $payment_id
	 * @param $payment_data array
	 */
	public function completed_donation_signup( $payment_id, $payment_data ) {

		//check to see if the user has elected to subscribe.
		if ( ! isset( $_POST['give_constant_contact_signup'] ) || $_POST['give_constant_contact_signup'] !== 'on' ) {
			return;
		}

		$form_lists = get_post_meta( $payment_data['give_form_id'], '_give_' . $this->id, true );

		//Check if $form_lists is set
		if ( empty( $form_lists ) ) {
			//Not set so use global list.
			$form_lists = array( 0 => give_get_option( 'give_constant_contact_list' ) );
		}

		//Add meta to the donation post that this donation opted-in to CC.
		add_post_meta( $payment_id, '_give_contact_contact_donation_optin_status', $form_lists );

		//Subscribe if array.
		if ( is_array( $form_lists ) ) {
			$lists = array_unique( $form_lists );
			foreach ( $lists as $list ) {
				//Subscribe the donor to the email lists.
				$this->subscribe_email( $payment_data['user_info'], $list, $payment_id );
			}
		} else {
			//Subscribe to single.
			$this->subscribe_email( $payment_data['user_info'], $form_lists, $payment_id );
		}

	}


	/**
	 * Display the metabox, which is a list of newsletter lists
	 */
	public function render_metabox() {

		global $post;
		$this->give_options = give_get_settings();

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'give_constant_contact_meta_box', 'give_constant_contact_meta_box_nonce' );

		//Using a custom label?
		$custom_label = get_post_meta( $post->ID, '_give_constant_contact_custom_label', true );

		//Global label
		$global_label = isset( $this->give_options['give_constant_contact_label'] ) ? $this->give_options['give_constant_contact_label'] : esc_html__( 'Signup for the newsletter', 'give-constant-contact' );;

		//Globally enabled option
		$globally_enabled = give_get_option( 'give_constant_contact_show_checkout_signup' );
		$enable_option    = get_post_meta( $post->ID, '_give_constant_contact_enable', true );
		$disable_option   = get_post_meta( $post->ID, '_give_constant_contact_disable', true );
		$checked_option   = get_post_meta( $post->ID, '_give_constant_contact_checked_default', true );

		if ( $globally_enabled == 'on' ) {

			//Output option to DISABLE CC for this form ?>
			<p style="margin: 1em 0 0;"><label>
					<input type="checkbox" name="_give_constant_contact_disable" class="give-constant-contact-disable"
					       value="true" <?php echo checked( 'true', $disable_option, false ); ?>>
					<?php echo '&nbsp;' . esc_html__( 'Disable Constant Contact Opt-in', 'give-constant-contact' ); ?>
				</label></p>

		<?php } else {
			//Output option to ENABLE CC for this form ?>
			<p style="margin: 1em 0 0;"><label>
					<input type="checkbox" name="_give_constant_contact_enable" class="give-constant-contact-enable"
					       value="true" <?php echo checked( 'true', $enable_option, false ); ?>>
					<?php echo '&nbsp;' . esc_html__( 'Enable Constant Contact Opt-in', 'give-constant-contact' ); ?>
				</label></p>
		<?php }

		// Display the form, using the current value. ?>
		<div
			class="give-constant-contact-field-wrap" <?php echo( $globally_enabled == false && empty( $enable_option ) ? "style='display:none;'" : '' ) ?>>

			<p>
				<label for="_give_constant_contact_custom_label"
				       style="font-weight:bold;"><?php echo esc_html__( 'Custom Label', 'give-constant-contact' ); ?></label>
				<span class="cmb2-metabox-description"
				      style="margin: 0 0 10px;"><?php echo esc_html__( 'Customize the label for the Constant Contact opt-in checkbox', 'give-constant-contact' ); ?></span>
				<input type="text" id="_give_constant_contact_custom_label" name="_give_constant_contact_custom_label"
				       value="<?php echo esc_attr( $custom_label ); ?>"
				       placeholder="<?php echo esc_attr( $global_label ); ?>" size="25"/>
			</p>

			<div>
				<label for="_give_constant_contact_checked_default"
				       style="font-weight:bold;"><?php _e( 'Opt-in Default', 'give-constant-contact' ); ?></label>
				<span class="cmb2-metabox-description"
				      style="margin: 0 0 10px;"><?php _e( 'Customize the newsletter opt-in option for this form.', 'give-constant-contact' ); ?></span>

				<ul class="cmb2-radio-list cmb2-list">
					<li>
						<input type="radio" class="cmb2-option" name="_give_constant_contact_checked_default"
						       id="give_constant_contact_checked_default1"
						       value="" <?php echo checked( '', $checked_option, false ); ?>>
						<label
							for="give_constant_contact_checked_default1"><?php _e( 'Global Option', 'give-constant-contact' ); ?></label>
					</li>

					<li>
						<input type="radio" class="cmb2-option" name="_give_constant_contact_checked_default"
						       id="give_constant_contact_checked_default2"
						       value="yes" <?php echo checked( 'yes', $checked_option, false ); ?>>
						<label
							for="give_constant_contact_checked_default2"><?php _e( 'Checked', 'give-constant-contact' ); ?></label>
					</li>
					<li>
						<input type="radio" class="cmb2-option" name="_give_constant_contact_checked_default"
						       id="give_constant_contact_checked_default3"
						       value="no" <?php echo checked( 'no', $checked_option, false ); ?>>
						<label
							for="give_constant_contact_checked_default3"><?php _e( 'Unchecked', 'give-constant-contact' ); ?></label>
					</li>
				</ul>

			</div>

			<div>
				<label for="give_constant_contact_lists"
				       style="font-weight:bold; float:left;"><?php echo esc_html__( 'Email Lists', 'give-constant-contact' ); ?>
				</label>

				<button class="give-reset-constant-contact-button button button-small" style="float:left; margin: -2px 0 0 15px;"
				        data-action="give_reset_constant_contact_lists"
				        data-field_type="checkbox"><?php echo esc_html__( 'Refresh Lists', 'give-constant-contact' ); ?></button>

				<span class="give-spinner spinner" style="float:left;margin: 0 0 0 10px;"></span>

				<span class="cmb2-metabox-description"
				      style="margin: 10px 0; clear: both;"><?php echo esc_html__( 'Customize the list you wish donors to subscribe to if they opt-in.', 'give-constant-contact' ); ?>
			</span>

				<?php
				//CC List.
				$checked = (array) get_post_meta( $post->ID, '_give_' . esc_attr( $this->id ), true );

				//No post meta yet? Default to global.
				if ( isset( $checked[0] ) && empty( $checked[0] ) ) {
					$checked = array( 0 => give_get_option( 'give_constant_contact_list' ) );
				} ?>

				<div class="give-constant-contact-list-wrap">
					<?php
					if ( $lists = $this->get_lists() ) :
						foreach ( $lists as $list_id => $list_name ) { ?>
							<label class="list">
								<input type="checkbox" name="_give_<?php echo esc_attr( $this->id ); ?>[]"
								       value="<?php echo esc_attr( $list_id ); ?>" <?php echo checked( true, in_array( $list_id, $checked ), false ); ?>>
								<span><?php echo $list_name; ?></span>
							</label>

						<?php }
					endif; ?>
				</div>

			</div>
		</div>

		<?php
	}


	/**
	 * Save the metabox data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @return void
	 */
	public function save_metabox( $post_id ) {

		$this->give_options = give_get_settings();

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */
		// Check if our nonce is set.
		if ( ! isset( $_POST['give_constant_contact_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['give_constant_contact_meta_box_nonce'], 'give_constant_contact_meta_box' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( $_POST['post_type'] == 'give_forms' ) {

			if ( ! current_user_can( 'edit_give_forms', $post_id ) ) {
				return $post_id;
			}

		} else {

			if ( ! current_user_can( 'edit_give_forms', $post_id ) ) {
				return $post_id;
			}

		}

		// OK, its safe for us to save the data now.

		// Sanitize the user input.
		$give_constant_contact_custom_label = isset( $_POST['_give_constant_contact_custom_label'] ) ? sanitize_text_field( $_POST['_give_constant_contact_custom_label'] ) : '';
		$give_constant_contact_custom_lists = isset( $_POST['_give_constant_contact'] ) ? $_POST['_give_constant_contact'] : give_get_option( 'give_constant_contact_list' );
		$give_constant_contact_enable       = isset( $_POST['_give_constant_contact_enable'] ) ? esc_html( $_POST['_give_constant_contact_enable'] ) : '';
		$give_constant_contact_disable      = isset( $_POST['_give_constant_contact_disable'] ) ? esc_html( $_POST['_give_constant_contact_disable'] ) : '';
		$give_constant_contact_checked      = isset( $_POST['_give_constant_contact_checked_default'] ) ? esc_html( $_POST['_give_constant_contact_checked_default'] ) : '';

		// Update the meta fields.
		update_post_meta( $post_id, '_give_constant_contact_custom_label', $give_constant_contact_custom_label );
		update_post_meta( $post_id, '_give_constant_contact', $give_constant_contact_custom_lists );
		update_post_meta( $post_id, '_give_constant_contact_enable', $give_constant_contact_enable );
		update_post_meta( $post_id, '_give_constant_contact_disable', $give_constant_contact_disable );
		update_post_meta( $post_id, '_give_constant_contact_checked_default', $give_constant_contact_checked );

	}

	/**
	 * Register the metabox on the 'give_forms' post type
	 */
	public function add_metabox() {

		if ( current_user_can( 'edit_give_forms', get_the_ID() ) ) {
			add_meta_box( 'give_' . $this->id, $this->label, array( $this, 'render_metabox' ), 'give_forms', 'side' );
		}

	}


	/**
	 * Give add constant_contact list select with refresh button.
	 *
	 * @param $field
	 * @param $value
	 * @param $object_id
	 * @param $object_type
	 * @param $field_type CMB2_Types
	 */
	public function give_constant_list_select( $field, $value, $object_id, $object_type, $field_type ) {

		$lists = $this->get_lists();

		ob_start(); ?>
		<div class="give-constant-contact-lists">
			<label class=""
			       for="<?php echo "{$field->args['id']}_day"; ?>"><?php _e( '', 'give-constant-contact' ); ?></label>

			<select class="cmb2_select give-constant-contact-list-select" name="<?php echo "{$field->args['id']}"; ?>"
			        id="<?php echo "{$field->args['id']}"; ?>">
				<?php echo $this->get_list_options( $lists, $value ); ?>
			</select>

			<button class="give-reset-constant-contact-button button-secondary" style="margin:3px 0 0 2px !important;"
			        data-action="give_reset_constant_contact_lists"
			        data-field_type="select"><?php echo esc_html__( 'Refresh Lists', 'give-constant-contact' ); ?></button>
			<span class="give-spinner spinner"></span>

			<p class="cmb2-metabox-description"><?php echo "{$field->args['desc']}"; ?></p>

		</div>

		<?php echo ob_get_clean();
	}

	/**
	 * Get the list options in an appropriate field format. This is used to output on page load and also refresh via AJAX.
	 *
	 * @param        $lists
	 * @param string $value
	 * @param string $field_type
	 *
	 * @return string
	 */
	public function get_list_options( $lists, $value = '', $field_type = 'select' ) {

		$options = '';

		if ( $field_type == 'select' ) {
			//Select options
			foreach ( $lists as $list_id => $list ) {
				$options .= '<option value="' . $list_id . '"' . selected( $value, $list_id, false ) . '>' . $list . '</option>';
			}

		} else {

			//Checkboxes.
			foreach ( $this->get_lists() as $list_id => $list_name ) {

				$options .= '<label class="list"><input type="checkbox" name="_give_' . esc_attr( $this->id ) . '[]"  value="' . esc_attr( $list_id ) . '" ' . checked( true, in_array( $list_id, $value ), false ) . '> <span>' . $list_name . '</span></label>';

			}
		}

		return $options;

	}

	/**
	 * AJAX reset Constant Contact lists.
	 */
	public function give_reset_constant_contact_lists() {

		//Delete transient.
		delete_transient( 'give_constant_contact_lists' );

		if ( isset( $_POST['field_type'] ) && $_POST['field_type'] == 'select' ) {
			$lists = $this->get_list_options( $this->get_lists(), give_get_option( 'give_constant_contact_list' ) );
		} elseif ( isset( $_POST['post_id'] ) ) {
			$lists = $this->get_list_options( $this->get_lists(), get_post_meta( $_POST['post_id'], '_give_constant_contact', true ), 'checkboxes' );
		} else {
			wp_send_json_error();
		}

		$return = array(
			'lists' => $lists,
		);

		wp_send_json_success( $return );
	}

}