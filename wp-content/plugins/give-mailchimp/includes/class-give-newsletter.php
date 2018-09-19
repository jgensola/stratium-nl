<?php

/**
 * Class Give_Newsletter
 *
 * Base newsletter class
 *
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Give_Newsletter {

	/**
	 * The properties and functions in this section may be overwritten by the Add-on using this class
	 * but are not mandatory
	 */

	/**
	 * The ID for this newsletter Add-on, such as 'mailchimp'
	 */
	public $id;

	/**
	 * The label for the Add-on, probably just shown as the title of the metabox
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
	 * The functions in this section must be overwritten by the Add-on using this class
	 */

	/**
	 * Defines the default label shown on checkout
	 *
	 * Other things can be done here if necessary, such as additional filters or actions
	 */
	public function init() {
	}

	/**
	 * Retrieve the newsletter lists
	 *
	 * Must return an array like this:
	 *   array(
	 *     'some_id'  => 'value1',
	 *     'other_id' => 'value2'
	 *   )
	 */
	public function get_lists() {
		return (array) $this->lists;
	}

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
	 * Determines if the signup checkbox should be shown on checkout
	 */
	public function show_signup_checkbox() {
		return true;
	}

	/**
	 * Subscribe a donor to a list
	 *
	 * $user_info is an array containing the user ID, email, first name, and last name
	 *
	 * $list_id is the list ID the user should be subscribed to. If it is false, sign the user
	 * up for the default list defined in settings
	 */
	public function subscribe_email( $user_info = array(), $list_id = false ) {
		return true;
	}

	/**
	 * Register the plugin settings
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function settings( $settings ) {
		return $settings;
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

		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ) );

		add_filter( 'give_settings_addons', array( $this, 'settings' ) );
		add_action( 'give_donation_form_before_submit', array( $this, 'form_fields' ), 100, 1 );
		add_action( 'give_insert_payment', array( $this, 'completed_donation_signup' ), 10, 2 );

		add_action( 'give_render_give_mailchimp_list_select', array(
			$this,
			'give_mailchimp_list_select',
		), 10, 5 );
		add_action( 'wp_ajax_give_reset_mailchimp_lists', array( $this, 'give_reset_mailchimp_lists' ) );

		// Donation metabox.
		add_filter( 'give_view_donation_details_totals_after', array( $this, 'donation_metabox_notification' ), 10, 1 );

		// Get it started.
		add_action( 'init', array( $this, 'init' ) );

	}

	/**
	 * Output the sign up checkbox on the donation form if enabled.
	 *
	 * @param int $form_id
	 */
	public function form_fields( $form_id ) {

		$enable_mc_form  = give_get_meta( $form_id, '_give_mailchimp_enable', true );
		$disable_mc_form = give_get_meta( $form_id, '_give_mailchimp_disable', true );

		// Check disable vars to see if this form should have the MC Opt-in field
		if ( ! $this->show_signup_checkbox() && $enable_mc_form !== 'true' || $disable_mc_form === 'true' ) {
			return;
		}

		$this->give_options    = give_get_settings();
		$custom_checkout_label = give_get_meta( $form_id, '_give_mailchimp_custom_label', true );

		// What's the label gonna be?
		if ( ! empty( $custom_checkout_label ) ) {
			$this->checkout_label = trim( $custom_checkout_label );
		} elseif ( ! empty( $this->give_options['give_mailchimp_label'] ) ) {
			$this->checkout_label = trim( $this->give_options['give_mailchimp_label'] );
		} else {
			$this->checkout_label = __( 'Subscribe to our newsletter', 'give-mailchimp' );
		}

		// Should the opt-on be checked or unchecked by default?
		$form_option    = give_get_meta( $form_id, '_give_mailchimp_checked_default', true );
		$checked_option = 'on';

		if ( ! empty( $form_option ) ) {
			// Nothing to do here, option already set above.
			$checked_option = $form_option;
		} elseif ( ! empty( $this->give_options['give_mailchimp_checked_default'] ) ) {
			$checked_option = $this->give_options['give_mailchimp_checked_default'];
		}

		ob_start(); ?>
		<fieldset id="give_<?php echo $this->id . '_' . $form_id; ?>" class="give-mailchimp-fieldset">
			<label for="give_<?php echo $this->id . '_' . $form_id; ?>_signup" class="give-mc-optin-label">
				<input name="give_<?php echo $this->id; ?>_signup"
				       id="give_<?php echo $this->id . '_' . $form_id; ?>_signup"
				       type="checkbox" <?php echo( $checked_option !== 'no' ? 'checked="checked"' : '' ); ?>/>
				<span class="give-mc-message-text" style="font-weight:normal;"><?php echo $this->checkout_label; ?></span>
			</label>
		</fieldset>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Complete Donation Sign up
	 *
	 * Check if a donor needs to be subscribed upon completing donation on a specific donation form.
	 *
	 * @param $payment_id
	 * @param $payment_data array
	 */
	public function completed_donation_signup( $payment_id, $payment_data ) {

		// Check to see if the user has elected to subscribe.
		if ( ! isset( $_POST['give_mailchimp_signup'] ) || $_POST['give_mailchimp_signup'] !== 'on' ) {
			return;
		}

		$form_lists = give_get_meta( $payment_data['give_form_id'], '_give_' . $this->id, true );

		// Check if $form_lists is set.
		if ( empty( $form_lists ) ) {

			// Get lists.
			$lists = give_get_option( 'give_mailchimp_list' );

			// Not set so use global list.
			$form_lists = ! is_array( $lists ) ? array( 0 => $lists ) : $lists;
		}

		// Add meta to the donation post that this donation opted-in to MC.
		add_post_meta( $payment_id, '_give_mc_donation_optin_status', $form_lists );

		// Subscribe if array.
		if ( is_array( $form_lists ) ) {
			$lists = array_unique( $form_lists );
			foreach ( $lists as $list ) {
				// Subscribe the donor to the email lists.
				$this->subscribe_email( $payment_data['user_info'], $list );
			}
		} else {
			// Subscribe to single.
			$this->subscribe_email( $payment_data['user_info'], $form_lists );
		}

	}

	/**
	 * Show Line item on donation details screen if the donor opted-in to the newsletter.
	 *
	 * @param $payment_id
	 */
	function donation_metabox_notification( $payment_id ) {

		$opt_in_meta = give_get_meta( $payment_id, '_give_mc_donation_optin_status', true );

		if ( $opt_in_meta ) { ?>
			<div class="give-admin-box-inside">
				<p>
					<span class="label"><?php _e( 'MailChimp', 'give-mailchimp' ); ?>:</span>&nbsp;
					<span><?php _e( 'Opted-in', 'give-mailchimp' ); ?></span>
				</p>
			</div>
		<?php }

	}

	/**
	 * Register the metabox on the 'give_forms' post type.
	 */
	public function add_metabox() {

		if ( current_user_can( 'edit_give_forms', get_the_ID() ) ) {
			add_meta_box( 'give_' . $this->id, $this->label, array( $this, 'render_metabox' ), 'give_forms', 'side' );
		}

	}

	/**
	 * Display the metabox, which is a list of newsletter lists
	 */
	public function render_metabox() {

		global $post;
		$this->give_options = give_get_settings();

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'give_mailchimp_meta_box', 'give_mailchimp_meta_box_nonce' );

		// Using a custom label?
		$custom_label = give_get_meta( $post->ID, '_give_mailchimp_custom_label', true );

		// Global label
		$global_label = isset( $this->give_options['give_mailchimp_label'] ) ? $this->give_options['give_mailchimp_label'] : __( 'Signup for the newsletter', 'give-mailchimp' );;

		// Globally enabled option.
		$globally_enabled      = give_get_option( 'give_mailchimp_show_checkout_signup' );
		$enable_option         = give_get_meta( $post->ID, '_give_mailchimp_enable', true );
		$checked_option        = give_get_meta( $post->ID, '_give_mailchimp_checked_default', true );
		$disable_option        = give_get_meta( $post->ID, '_give_mailchimp_disable', true );
		$send_ffm_to_mailchimp = give_get_meta( $post->ID, '_give_mailchimp_send_ffm', true );
		$send_donation_data    = give_get_meta( $post->ID, '_give_mailchimp_send_donation_data', true );

		// Start the buffer.
		ob_start();

		// Output option to DISABLE MC for this form
		if ( $globally_enabled == 'on' ) { ?>
			<p style="margin: 1em 0 0;">
				<label>
					<input type="checkbox" name="_give_mailchimp_disable" class="give-mailchimp-disable"
					       value="true" <?php echo checked( 'true', $disable_option, false ); ?>>&nbsp;<?php _e( 'Disable MailChimp Opt-in', 'give-mailchimp' ); ?>
				</label>
			</p>

			<?php
		} else {
			// Output option to ENABLE MC for this form
			?>
			<p style="margin: 1em 0 0;">
				<label>
					<input type="checkbox" name="_give_mailchimp_enable" class="give-mailchimp-enable"
					       value="true" <?php echo checked( 'true', $enable_option, false ) ?>>&nbsp;<?php _e( 'Enable MailChimp Opt-in', 'give-mailchimp' ); ?>
				</label>
			</p>
		<?php }

		// Display the form, using the current value. ?>
		<div class="give-mailchimp-field-wrap" <?php echo( $globally_enabled == false && empty( $enable_option ) ? "style='display:none;'" : '' ) ?>>
			<p>
				<label for="_give_mailchimp_custom_label" class="give-mc-label"><?php _e( 'Custom Label', 'give-mailchimp' ); ?></label>
				<span class="give-field-description"
				      style="margin: 0 0 10px;"><?php _e( 'Customize the label for the MailChimp opt-in checkbox', 'give-mailchimp' ); ?></span>
				<input type="text" id="_give_mailchimp_custom_label" name="_give_mailchimp_custom_label"
				       value="<?php echo esc_attr( $custom_label ); ?>"
				       placeholder="<?php echo esc_attr( $global_label ); ?>" style="width:100%; margin: 10px 0 0;" />
			</p>

			<?php // Field: Default checked or unchecked option. ?>
			<div>

				<label for="_give_mailchimp_checked_default" class="give-mc-label"><?php _e( 'Opt-in Default', 'give-mailchimp' ); ?></label>
				<span class="give-field-description"
				      style="margin: 0 0 10px;"><?php _e( 'Customize the newsletter opt-in option for this form.', 'give-mailchimp' ); ?></span>

				<ul class="give-radio-list give-list">

					<li>
						<label for="give_mailchimp_checked_default1">
							<input type="radio" class="give-radio" name="_give_mailchimp_checked_default"
							       id="give_mailchimp_checked_default1"
							       value="" <?php echo checked( '', $checked_option, false ); ?>>
							<?php _e( 'Global Option', 'give-mailchimp' ); ?>
						</label>
					</li>

					<li>
						<label for="give_mailchimp_checked_default2">
							<input type="radio" class="give-radio" name="_give_mailchimp_checked_default"
							       id="give_mailchimp_checked_default2"
							       value="yes" <?php echo checked( 'yes', $checked_option, false ); ?>>
							<?php _e( 'Checked', 'give-mailchimp' ); ?>
						</label>
					</li>
					<li>
						<label for="give_mailchimp_checked_default3">
							<input type="radio" class="give-radio" name="_give_mailchimp_checked_default"
							       id="give_mailchimp_checked_default3"
							       value="no" <?php echo checked( 'no', $checked_option, false ); ?>>
							<?php _e( 'Unchecked', 'give-mailchimp' ); ?>
						</label>
					</li>
				</ul>

			</div>
			<div>
				<label for="_give_mailchimp_send_donation_data" class="give-mc-label"><?php _e( 'Send Donation Data', 'give-mailchimp' ); ?></label>
				<span class="give-field-description"
				      style="margin: 0 0 10px;"><?php _e( 'Enabling this option will send donation data such as Donation Form Title, ID, Payment Method and more to MailChimp under the Subscriber\'s Details.',
						'give-mailchimp'	); ?></span>
				<ul class="give-radio-list give-list">
					<li>
						<label for="_give_mailchimp_send_donation_data1">
							<input type="radio" class="give-radio" name="_give_mailchimp_send_donation_data"
							       id="_give_mailchimp_send_donation_data1"
							       value="" <?php echo checked( '', $send_donation_data, false ); ?>>
							<?php _e( 'Global Option', 'give-mailchimp' ); ?>
						</label>
					</li>
					<li>
						<label for="_give_mailchimp_send_donation_data2">
							<input type="radio" class="give-radio" name="_give_mailchimp_send_donation_data"
							       id="_give_mailchimp_send_donation_data2"
							       value="on" <?php echo checked( 'on', $send_donation_data, false ); ?>>
							<?php _e( 'Send Data', 'give-mailchimp' ); ?>
						</label>
					</li>
					<li>
						<label for="_give_mailchimp_send_donation_data3">
							<input type="radio" class="give-radio" name="_give_mailchimp_send_donation_data"
							       id="_give_mailchimp_send_donation_data3"
							       value="no" <?php echo checked( 'no', $send_donation_data, false ); ?>>
							<?php _e( 'Don\'t Send Data', 'give-mailchimp' ); ?>
						</label>
					</li>
				</ul>
			</div>
			<?php
			if ( give_mc_ffm_is_activated() ) {
				?>
				<div>
					<label for="_give_mailchimp_send_ffm" class="give-mc-label"><?php _e( 'Send FFM Field Data', 'give-mailchimp' ); ?></label>
					<span class="give-field-description"
					      style="margin: 0 0 10px;"><?php _e( 'Enabling this option will send custom fields created within Form Field Manager to MailChimp when a donor subscribes. Note: you need to have filled in the ', 'give-mailchimp' ); ?></span>

					<ul class="give-radio-list give-list">
						<li>
							<label
									for="_give_mailchimp_send_ffm1">
								<input type="radio" class="give-radio" name="_give_mailchimp_send_ffm"
								       id="_give_mailchimp_send_ffm1"
								       value="" <?php echo checked( '', $send_ffm_to_mailchimp, false ); ?>>
								<?php _e( 'Global Option', 'give-mailchimp' ); ?>
							</label>
						</li>

						<li>
							<label for="_give_mailchimp_send_ffm2">
								<input type="radio" class="give-radio" name="_give_mailchimp_send_ffm"
								       id="_give_mailchimp_send_ffm2"
								       value="on" <?php echo checked( 'on', $send_ffm_to_mailchimp, false ); ?>>
								<?php _e( 'Send FFM Fields', 'give-mailchimp' ); ?>
							</label>
						</li>
						<li>
							<label
									for="_give_mailchimp_send_ffm3">
								<input type="radio" class="give-radio" name="_give_mailchimp_send_ffm"
								       id="_give_mailchimp_send_ffm3"
								       value="no" <?php echo checked( 'no', $send_ffm_to_mailchimp, false ); ?>>
								<?php _e( 'Don\'t Send FFM Fields', 'give-mailchimp' ); ?>
							</label>
						</li>
					</ul>
				</div>
				<?php
			} ?>
			<?php // Field: MailChimp lists and groups. ?>
			<div class="give-mailchimp-list-container">
				<label for="give_mailchimp_lists"
				       style="font-weight:bold; float:left;"><?php _e( 'MailChimp Opt-in', 'give-mailchimp' ); ?></label>

				<button class="give-reset-mailchimp-button button button-small"
				        style="float:left; margin: -2px 0 0 15px;"
				        data-action="give_reset_mailchimp_lists" data-field_type="checkbox">Refresh Lists
				</button>
				<span class="give-spinner spinner" style="float:left;margin: 0 0 0 10px;"></span>

				<span class="give-field-description"
				      style="margin: 0 0 10px;clear:both; display:block;"><?php _e( 'Customize the lists and/or groups you wish donors to subscribe to.', 'give-mailchimp' ); ?></span>

				<div class="give-mailchimp-list-wrap">

					<?php
					$value = (array) give_get_meta( $post->ID, '_give_' . $this->id, true );
					echo $this->get_list_options( $this->get_lists(), $value ); ?>

				</div><!-- give-mailchimp-list-wrap -->
			</div> <!-- give-mailchimp-list-container -->
		</div>
		<?php

		// Return the metabox.
		echo ob_get_clean();
	}

	/**
	 * Save the metabox data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @return string
	 */
	public function save_metabox( $post_id ) {

		$this->give_options = give_get_settings();

		/**
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */
		// Check if our nonce is set.
		if ( ! isset( $_POST['give_mailchimp_meta_box_nonce'] ) ) {
			return false;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['give_mailchimp_meta_box_nonce'], 'give_mailchimp_meta_box' ) ) {
			return false;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Check the user's permissions.
		if ( 'give_forms' === $_POST['post_type'] ) {

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
		$give_mailchimp_custom_label = isset( $_POST['_give_mailchimp_custom_label'] ) ? sanitize_text_field( $_POST['_give_mailchimp_custom_label'] ) : '';
		$give_mailchimp_custom_lists = isset( $_POST['_give_mailchimp'] ) ? $_POST['_give_mailchimp'] : give_get_option( 'give_mailchimp_list' );
		$give_mailchimp_enable       = isset( $_POST['_give_mailchimp_enable'] ) ? esc_html( $_POST['_give_mailchimp_enable'] ) : '';
		$give_mailchimp_disable      = isset( $_POST['_give_mailchimp_disable'] ) ? esc_html( $_POST['_give_mailchimp_disable'] ) : '';
		$give_mailchimp_checked      = isset( $_POST['_give_mailchimp_checked_default'] ) ? esc_html( $_POST['_give_mailchimp_checked_default'] ) : '';

		// Update the meta field.
		update_post_meta( $post_id, '_give_mailchimp_custom_label', $give_mailchimp_custom_label );
		update_post_meta( $post_id, '_give_mailchimp', $give_mailchimp_custom_lists );
		update_post_meta( $post_id, '_give_mailchimp_enable', $give_mailchimp_enable );
		update_post_meta( $post_id, '_give_mailchimp_disable', $give_mailchimp_disable );
		update_post_meta( $post_id, '_give_mailchimp_checked_default', $give_mailchimp_checked );

		// Save ffm field send or not data.
		if ( give_mc_ffm_is_activated() ) {
			$give_mailchimp_send_ffm_field = isset( $_POST['_give_mailchimp_send_ffm'] ) ? esc_html( $_POST['_give_mailchimp_send_ffm'] ) : '';
			update_post_meta( $post_id, '_give_mailchimp_send_ffm', $give_mailchimp_send_ffm_field );
		}

		return true;

	}

	/**
	 * Get the list options in an appropriate field format.
	 * This is used to output on page load and also refresh via AJAX.
	 *
	 * @since 1.4 Remove $field_type argument and replaced it with $field argument.
	 *
	 * @param        $lists
	 * @param array  $value
	 * @param array  $field_args Field args array.
	 *
	 * @return string
	 */
	public function get_list_options( $lists, $value, $field_args = array() ) {
		ob_start();
		$lists        = ! empty( $lists ) ? $lists : $this->get_lists();

		// Checkboxes.
		foreach ( $lists as $list_id => $list_name ) :
			// Get list groups.
			$groupings = $this->get_groupings( $list_id );
			$field_id = isset( $field_args['id'] ) ? $field_args['id'] . '[]' : "_give_{$this->id}[]";
			?>

			<label class="list">
				<input type="checkbox" name="<?php echo esc_attr( $field_id ); ?>"
				       value="<?php echo esc_attr( $list_id ); ?>" <?php checked( true, in_array( $list_id, $value ), true ); ?>>
				<span><?php echo $list_name; ?></span>
			</label>

			<?php if ( ! empty( $groupings ) ) {
			foreach ( $groupings as $group_id => $group_name ) : ?>
				<label class="list list-group">
					<input type="checkbox"
					       name="<?php echo esc_attr( $field_id ); ?>"
					       value="<?php echo esc_attr( $group_id ); ?>" <?php checked( true, in_array( $group_id, $value ), true ); ?>>
					<span><?php echo $group_name; ?></span>
				</label>
			<?php endforeach; ?>
		<?php } ?>

		<?php endforeach; // end foreach lists.

		return ob_get_clean();
	}


	/**
	 * AJAX reset MailChimp lists.
	 */
	public function give_reset_mailchimp_lists() {

		// Delete transient.
		delete_transient( 'give_mailchimp_list_data' );
		$lists = '';

		if ( isset( $_POST['field_type'] ) && 'global' === $_POST['field_type'] ) {
			// Get the saved list.
			$saved_list = give_get_option( 'give_mailchimp_list' );
			$list_items = ! is_array( $saved_list ) ? (array) $saved_list : $saved_list;
			$lists      = $this->get_list_options( $this->get_lists(), $list_items, array( 'id' => 'give_mailchimp_list' ) );
		} elseif ( isset( $_POST['post_id'] ) ) {
			$lists = $this->get_list_options( $this->get_lists(), give_get_meta( $_POST['post_id'], '_give_mailchimp', true ) );
		} else {
			wp_send_json_error();
		}

		$return = array(
			'lists' => $lists,
		);

		wp_send_json_success( $return );
	}

}
