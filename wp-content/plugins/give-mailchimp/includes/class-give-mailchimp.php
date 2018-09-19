<?php

/**
 * Class Give_MailChimp
 *
 * Extends the base newsletter class with MailChimp specific functionality.
 *
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */
class Give_MailChimp extends Give_Newsletter {

	/**
	 * Sets up the checkout label
	 */
	public function init() {
		add_action( 'give_save_options-page_fields', array( $this, 'save_settings' ), 10, 4 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 100 );
		add_action( 'give_admin_field_mailchimp_list_select', array( $this, 'give_mailchimp_list_select' ), 10, 2 );
	}

	/**
	 * Load Admin Scripts
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since 1.0
	 * @global       $post
	 *
	 * @param string $hook Page hook
	 *
	 * @return void
	 */
	public function admin_scripts( $hook ) {

		global $post_type;

		// Directories of assets.
		$js_dir  = GIVE_MAILCHIMP_URL . 'assets/js/';
		$css_dir = GIVE_MAILCHIMP_URL . 'assets/css/';

		wp_register_script( 'give-' . $this->id . '-admin-ajax-js', $js_dir . 'admin-ajax.js', array( 'jquery' ) );

		// Forms CPT Script.
		if ( $post_type === 'give_forms' || ( give_is_admin_page() && isset( $_GET['tab'] ) && 'addons' === $_GET['tab'] ) ) {

			// CSS.
			wp_register_style( 'give-mailchimp-admin-css', $css_dir . 'admin-forms.css', GIVE_MAILCHIMP_VERSION );
			wp_enqueue_style( 'give-mailchimp-admin-css' );

			// JS.
			wp_register_script( 'give-mailchimp-admin-forms-scripts', $js_dir . 'admin-forms.js', array( 'jquery' ), GIVE_MAILCHIMP_VERSION, false );
			wp_enqueue_script( 'give-mailchimp-admin-forms-scripts' );

			wp_enqueue_script( 'give-' . $this->id . '-admin-ajax-js' );

		}

		// Admin settings.
		if ( $hook == 'give_forms_page_give-settings' ) {
			wp_enqueue_script( 'give-' . $this->id . '-admin-ajax-js' );
		}

	}


	/**
	 * Retrieves the lists from MailChimp
	 *
	 * @return array
	 */
	public function get_lists() {

		$give_options = give_get_settings();

		// Bail if no API key.
		if ( empty( $give_options['give_mailchimp_api'] ) ) {
			return array();
		}

		// Get existing list data.
		$list_data = get_transient( 'give_mailchimp_list_data' );

		if ( false === $list_data || empty( $list_data ) ) {

			$api = new Give_MailChimp_API( trim( $give_options['give_mailchimp_api'] ) );

			$list_data = $api->call( 'lists/list' );

			// Check for errors.
			if ( isset( $list_data->status ) && $list_data->status == 'error' ) {

				// Display error from MC in WP-Admin.
				add_settings_error( 'give-mailchimp-notices', 'give-error', __( 'MailChimp API Error:', 'give-mailchimp' ) . ' ' . $list_data->error, 'error' );
				settings_errors( 'give-mailchimp-notices' );
				give_update_option( 'give_mailchimp_api', '' ); // delete API key option.
				delete_transient( 'give_mailchimp_list_data' );

				// Bounce.
				return array();

			} else {

				// Save transient data for usage later.
				set_transient( 'give_mailchimp_list_data', $list_data, 24 * 24 * 24 );

			}
		}

		if ( ! empty( $list_data ) ) {
			foreach ( $list_data->data as $key => $list ) {
				$this->lists[ $list->id ] = $list->name;
			}
		}

		return (array) $this->lists;

	}

	/**
	 * Retrieve the list of groupings associated with a list id
	 *
	 * @param  string $list_id List id for which groupings should be returned
	 *
	 * @return bool|array  $groups_data Data about the groups
	 */
	public function get_groupings( $list_id = '' ) {

		$give_options = give_get_settings();

		$groups_data = array();

		if ( empty( $give_options['give_mailchimp_api'] ) ) {
			return false;
		}

		$grouping_data = get_transient( 'give_mailchimp_groupings_' . $list_id );

		if ( false === $grouping_data ) {

			if ( ! class_exists( 'Give_MailChimp_API' ) ) {
				require_once( GIVE_MAILCHIMP_PATH . '/includes/MailChimp.class.php' );
			}

			$api           = new Give_MailChimp_API( trim( $give_options['give_mailchimp_api'] ) );
			$grouping_data = $api->call( 'lists/interest-groupings', array( 'id' => $list_id ) );

			set_transient( 'give_mailchimp_groupings_' . $list_id, $grouping_data, 24 * 24 * 24 );
		}

		if ( $grouping_data && ! isset( $grouping_data->status ) ) {

			foreach ( $grouping_data as $grouping ) {

				$grouping_id   = $grouping->id;
				$grouping_name = $grouping->name;

				foreach ( $grouping->groups as $groups ) {

					$group_name                                       = $groups->name;
					$groups_data["$list_id|$grouping_id|$group_name"] = $grouping_name . ' - ' . $group_name;

				}
			}
		}

		return $groups_data;

	}

	/**
	 * Registers the plugin settings.
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function settings( $settings ) {

		$give_mailchimp_settings = array(
			array(
				'name' => __( 'MailChimp Settings', 'give-mailchimp' ),
				'id'   => 'give_title_mailchimp',
				'type' => 'give_title',
			),
			array(
				'id'   => 'give_mailchimp_api',
				'name' => __( 'MailChimp API Key', 'give-mailchimp' ),
				'desc' => __( 'Enter your MailChimp API key', 'give-mailchimp' ),
				'type' => 'api_key',
			),
			array(
				'id'   => 'give_mailchimp_double_opt_in',
				'name' => __( 'Double Opt-In', 'give-mailchimp' ),
				'desc' => __( 'When checked, users will be sent a confirmation email after signing up, and will only be added once they have confirmed the subscription.', 'give-mailchimp' ),
				'type' => 'checkbox',
			),
			array(
				'id'   => 'give_mailchimp_show_checkout_signup',
				'name' => __( 'Enable Globally', 'give-mailchimp' ),
				'desc' => __( 'Allow donors to sign up for the list selected below on all donation forms? Note: the list(s) can be customized per form.', 'give-mailchimp' ),
				'type' => 'checkbox',
			),
			array(
				'id'      => 'give_mailchimp_checked_default',
				'name'    => __( 'Opt-in Default', 'give-mailchimp' ),
				'desc'    => __( 'Would you like the newsletter opt-in checkbox checked by default? This option can be customized per form.', 'give-mailchimp' ),
				'options' => array(
					'yes' => __( 'Checked', 'give-mailchimp' ),
					'no'  => __( 'Unchecked', 'give-mailchimp' ),
				),
				'default' => 'no',
				'type'    => 'radio_inline',
			),
			array(
				'id'   => 'give_mailchimp_list',
				'name' => __( 'Default List', 'give-mailchimp' ),
				'desc' => __( 'Select the list you wish for all donors to subscribe to by default. Note: the list(s) can be customized per form.', 'give-mailchimp' ),
				'type' => 'mailchimp_list_select',
			),
			array(
				'id'         => 'give_mailchimp_label',
				'name'       => __( 'Default Label', 'give-mailchimp' ),
				'desc'       => __( 'This is the text shown by default next to the MailChimp sign up checkbox. Yes, this can also be customized per form.', 'give-mailchimp' ),
				'type'       => 'text',
				'attributes' => array(
					'placeholder' => __( 'Subscribe to our newsletter', 'give-mailchimp' ),
				),
			),
			array(
				'id'   => 'give_mailchimp_donation_data',
				'name' => __( 'Send Donation Data', 'give-mailchimp' ),
				'desc' => __( 'Enabling this option will send donation data such as Donation Form Title, ID, Payment Method and more to MailChimp under the Subscriber\'s Details.', 'give-mailchimp' ),
				'type' => 'checkbox',
			),

		);

		/**
		 * If FFM is activated then add new option which ask to send custom
		 * field data to Mail Chimp or not.
		 *
		 * @since 1.4
		 */
		if ( give_mc_ffm_is_activated() ) {

			$give_mailchimp_settings[] = array(
				'id'   => 'give_mailchimp_ffm_pass_field',
				'name' => __( 'Send FFM field to mailchimp?', 'give-mailchimp' ),
				'desc' => __( 'Would you like to send FFM field data to mailchimp? IF MailChimp option is set.', 'give-mailchimp' ),
				'type' => 'checkbox',
			);
		}

		// Docs link is always last.
		$give_mailchimp_settings[] = array(
			'name'  => __( 'MailChimp Docs Link', 'give-mailchimp' ),
			'id'    => 'mailchimp_settings_docs_link',
			'url'   => esc_url( 'http://docs.givewp.com/addon-mailchimp' ),
			'title' => __( 'MailChimp Settings', 'give-mailchimp' ),
			'type'  => 'give_docs_link',
		);

		return array_merge( $settings, $give_mailchimp_settings );
	}


	/**
	 * Flush the list transient on settings save.
	 */
	public function save_settings() {

		$api_key = give_get_option( 'give_mailchimp_api' );

		// Is this a new API key?
		if ( isset( $_POST['give_mailchimp_api'] ) && $api_key !== $_POST['give_mailchimp_api'] ) {
			delete_transient( 'give_mailchimp_list_data' );
			echo '<script>window.location.reload();</script>';
		}

	}

	/**
	 * Determines if the donation form signup option should be displayed.
	 */
	public function show_signup_checkbox() {

		$give_options = give_get_settings();

		return ! empty( $give_options['give_mailchimp_show_checkout_signup'] );
	}

	/**
	 * Subscribe Email.
	 *
	 * Subscribe an email to a list.
	 *
	 * @param array $user_info
	 * @param bool  $list_id
	 * @param bool  $opt_in_override
	 *
	 * @return bool
	 */
	public function subscribe_email( $user_info = array(), $list_id = false, $opt_in_override = false ) {

		$give_options = give_get_settings();

		// Make sure an API key has been entered.
		if ( empty( $give_options['give_mailchimp_api'] ) ) {
			return false;
		}

		// Retrieve the global list ID if none is provided.
		if ( ! $list_id ) {
			$list_id = ! empty( $give_options['give_mailchimp_list'] ) ? $give_options['give_mailchimp_list'] : false;
			if ( ! $list_id ) {
				return false;
			}
		}

		if ( ! class_exists( 'Give_MailChimp_API' ) ) {
			require_once( GIVE_MAILCHIMP_PATH . '/includes/MailChimp.class.php' );
		}

		$api    = new Give_MailChimp_API( trim( $give_options['give_mailchimp_api'] ) );
		$opt_in = ( isset( $give_options['give_mailchimp_double_opt_in'] ) && ! empty( $give_options['give_mailchimp_double_opt_in'] ) ) && ! $opt_in_override;

		$merge_vars = array( 'FNAME' => $user_info['first_name'], 'LNAME' => $user_info['last_name'] );

		if ( strpos( $list_id, '|' ) !== false ) {
			$parts = explode( '|', $list_id );

			$list_id     = $parts[0];
			$grouping_id = $parts[1];
			$group_name  = $parts[2];

			$groupings = array(
				array(
					'id'     => $grouping_id,
					'groups' => array( $group_name ),
				),
			);

			$merge_vars['groupings'] = $groupings;
		}
		$api_args = apply_filters( 'give_mc_subscribe_vars', array(
			'id'                => $list_id,
			'email'             => array( 'email' => $user_info['email'] ),
			'merge_vars'        => $merge_vars,
			'double_optin'      => $opt_in,
			'update_existing'   => true,
			'replace_interests' => false,
			'send_welcome'      => false,
		) );

		$result = $api->call( 'lists/subscribe', $api_args );

		if ( $result ) {
			return true;
		}

		return false;

	}

	/**
	 * Give add MailChimp list select with refresh button.
	 *
	 * @param              $field
	 * @param array|string $value
	 */
	public function give_mailchimp_list_select( $field, $value) {

		$lists = $this->get_lists();

		// Backward compatibility.
		$value = ! is_array( $value ) ? (array) $value : $value;

		ob_start(); ?>

		<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
			<th scope="row" class="titledesc">
				<label for=""> <?php _e( 'Default List', 'give-mailchimp' ) ?></label>
			</th>
			<td class="give-mailchimp-list-td">

				<div class="give-mailchimp-list-container">
					<label class=""
					       style="font-weight:bold; float:left;"
					       for="<?php echo "{$field['id']}"; ?>"><?php _e( '', 'give-mailchimp' ); ?></label>

					<div class="give-mailchimp-list-wrap">
						<?php echo $this->get_list_options( $lists, $value, $field ); ?>
					</div>
					<div class="give-mailchimp-list-refresh-btn">
						<button class="give-reset-mailchimp-button button-secondary" style="margin:1px 0 0 !important;"
						        data-action="give_reset_mailchimp_lists"
						        data-field_type="global"><?php echo esc_html__( 'Refresh Lists', 'give-mailchimp' ); ?></button>
						<span class="give-spinner spinner"></span>
					</div>
					<p class="give-field-description"><?php echo "{$field['desc']}"; ?></p>
				</div>


			</td>
		</tr>

		<?php echo ob_get_clean();
	}

}
