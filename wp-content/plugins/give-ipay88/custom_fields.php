<?php
/**
 * Add Custom Donation Form Fields
 *
 * @param $form_id
 */ 
function give_ipay88_custom_form_fields( $form_id ) {
?>
	<div id="give-contact-wrap" class="form-row give-ffm-form-row-responsive give-ffm-form-row-full">
		<label class="give-label" for="give-contact"><?php _e( 'Contact Number:', 'give' ); ?>
			<span class="give-tooltip icon icon-question" data-tooltip="<?php _e( 'Please provide your contact number.', 'give' ) ?>"></span>
		</label>
		<input type="text" class="give-input" name="give-contact" id="give-contact"></textarea>
	</div>
<?php
} 

add_action( 'give_after_donation_levels', 'give_ipay88_custom_form_fields', 10, 1);