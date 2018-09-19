<?php
/**
 * The MailChimp helpers functions.
 *
 * @since 1.4
 */

/**
 * Give MailChimp Manual Donations Opt-in
 *
 * Adds a subscription checkbox to the manual donations page.
 *
 * @since 1.4
 */
function give_mailchimp_donation_newletter() {
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<?php _e( 'Send Newsletter Subscribe', 'give-mailchimp' ); ?>
		</th>

		<td class="give-md-newletter">
			<label for="give_mailchimp_signup" class="give_mailchimp_signup"
			       style="margin-right: 30px; font-weight: 500;">
				<input name="give_mailchimp_signup" id="give_mailchimp_signup" type="checkbox" value="1"/>
				<?php _e( 'Subscribe the donor to MailChimp?', 'give-mailchimp' ); ?>
			</label>

			<label for="give_mailchimp_send_confirmation_immediately">
				<input type="radio" name="give_mailchimp_send_confirmation" value="0"
				       id="give_mailchimp_send_confirmation_immediately"
				       class="give_mailchimp_send_confirmation_immediately" checked/>
				<?php _e( 'Subscribe Immediately', 'give-mailchimp' ); ?>
			</label>

			<label for="give_mailchimp_send_confirmation_double_optin">
				<input type="radio" name="give_mailchimp_send_confirmation" value="1"
				       id="give_mailchimp_send_confirmation_double_optin"
				       class="give_mailchimp_send_confirmation_double_optin"/>
				<?php _e( 'Double Opt-In', 'give-mailchimp' ); ?>
			</label>
			<p class="description"><?php _e( 'When checked the donor will be subscribed to the appropriate MailChimp lists assigned to this donation form. Note: if you have double opt-in enabled the donor will be required to confirm their subscription.', 'give-mailchimp' ); ?></p>
		</td>
	</tr>
	<?php
}

add_action( 'give_manual_donation_table_before_note_field', 'give_mailchimp_donation_newletter', 10 );

/**
 * Update the donation meta and send the MailChimp request to the donor
 *
 * @since 1.4
 *
 * @param $payment_id
 * @param $payment
 */
function give_mailchimp_insert_payment_manual_donation( $payment_id, $payment ) {

	if ( empty( $payment->form_id ) || empty( $payment_id ) ) {
		return;
	}

	$form_id    = absint( $payment->form_id );
	$payment_id = absint( $payment_id );

	$form_lists = give_get_meta( $form_id, '_give_mailchimp', true );

	// Check if $form_lists is set.
	if ( empty( $form_lists ) ) {
		// Get lists.
		$lists = give_get_option( 'give_mailchimp_list' );

		// Not set so use global list.
		$form_lists = ! is_array( $lists ) ? array( 0 => $lists ) : $lists;
	}
	// Add meta to the donation post that this donation opted-in to MC.
	add_post_meta( $payment_id, '_give_mc_donation_optin_status', $form_lists );

	$mail_chimp = new Give_MailChimp( 'mailchimp', 'MailChimp' );

	add_filter( 'give_mc_subscribe_vars', 'give_mc_subscribe_vars_manual_donation', 11 );
	// Subscribe if array.
	if ( is_array( $form_lists ) ) {
		$lists = array_unique( $form_lists );
		foreach ( $lists as $list ) {
			// Subscribe the donor to the email lists.
			$mail_chimp->subscribe_email( $payment->user_info, $list );
		}
	} else {
		// Subscribe to single.
		$mail_chimp->subscribe_email( $payment->user_info, $form_lists );
	}
	remove_filter( 'give_mc_subscribe_vars', 'give_mc_subscribe_vars_manual_donation', 11 );
}

add_action( 'give_manual_insert_payment', 'give_mailchimp_insert_payment_manual_donation', 10, 2 );

/**
 * Filter to modify double_opt in in MailChimp
 *
 * @since 1.4
 *
 * @param $api_args
 *
 * @return mixed
 */
function give_mc_subscribe_vars_manual_donation( $api_args ) {

	if ( isset( $_POST['give_mailchimp_send_confirmation'] ) ) {

		$double_opt_in = (bool) $_POST['give_mailchimp_send_confirmation'];

		$api_args['double_optin'] = $double_opt_in;
	}

	return $api_args;
}