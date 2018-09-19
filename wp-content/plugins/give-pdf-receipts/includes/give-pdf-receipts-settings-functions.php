<?php
/**
 * Give PDF Receipts settings.
 *
 * @since 2.1.0
 *
 * @param bool $is_global Check if Global/Per-Form settings.
 * @param int  $form_id   Form ID.
 *
 * @return array $settings Give PDF Receipts Settings array.
 */
function give_pdf_receipts_settings( $is_global, $form_id ) {

	$give_pdf_receipt_template = empty( $form_id ) ? give_get_option( 'give_pdf_receipt_template' ) : give_get_meta( $form_id, 'give_pdf_receipt_template', true );

	// Get template.
	$template_id = isset( $GLOBALS['give_pdf_receipt_template_id'] ) ? $GLOBALS['give_pdf_receipt_template_id'] : $give_pdf_receipt_template;
	$template    = get_post( $template_id );

	$post_title   = '';
	$post_content = '';
	if ( $template ) {
		$post_title   = $template->post_title;
		$post_content = $template->post_content;
	}

	$options        = array();
	$default_option = 'global'; // Set default option as a global.

	if ( $is_global ) {
		$options['enabled']  = __( 'Enabled', 'give-pdf-receipts' );
		$options['disabled'] = __( 'Disabled', 'give-pdf-receipts' );
		$default_option      = 'enabled';
	} else {
		$options['global']   = __( 'Global Option', 'give-pdf-receipts' );
		$options['enabled']  = __( 'Customize', 'give-pdf-receipts' );
		$options['disabled'] = __( 'Disabled', 'give-pdf-receipts' );
	}

	// Settings array for the donation form 'PDF Receipts' section.
	$pdf_receipts_fields = array(
		array(
			'id'   => 'give_pdf_settings',
			'type' => 'title',
		),
		array(
			'name'          => __( 'PDF Receipts', 'give-pdf-receipts' ),
			'desc'          => $is_global ? __( 'This enables the PDF Receipts feature for all your website\'s donation forms. Note: You can disable the global options and enable and customize options per form as well.', 'give-pdf-receipts' ) : __( 'This allows you to customize the PDF Receipts settings for just this donation form. You can disable PDF Receipts for just this form as well or simply use the global settings.', 'give-pdf-receipts' ),
			'id'            => 'give_pdf_receipts_enable_disable',
			'wrapper_class' => 'give_pdf_receipts_enable_disable',
			'type'          => 'radio_inline',
			'default'       => $default_option,
			'options'       => $options,
		),
		array(
			'id'            => 'give_pdf_generation_method',
			'name'          => __( 'Generation Method', 'give-pdf-receipts' ),
			'description'   => __( 'Choose the method you would like to generate PDF receipts. The Custom PDF Builder allows you to customize your own templates using a rich editor that allows for custom text, images and HTML to be easily inserted. The Set PDF Templates option will generate PDFs using preconfigured templates.', 'give-pdf-receipts' ),
			'default'       => 'custom_pdf_builder',
			'type'          => 'radio_inline',
			'wrapper_class' => 'pdf-receipts-fields give_pdf_generation_method give-hidden',
			'options'       => array(
				'custom_pdf_builder' => __( 'Custom PDF Builder', 'give-pdf-receipts' ),
				'set_pdf_templates'  => __( 'Set PDF Templates', 'give-pdf-receipts' ),
			),
		),
		array(
			'id'            => 'give_pdf_receipt_template',
			'name'          => __( 'Receipt Template', 'give-pdf-receipts' ),
			'description'   => __( 'Please select a template for your PDF receipts or create a new custom template using your own HTML and CSS. The selected template is viewable in the PDF builder field below.', 'give-pdf-receipts' ),
			'type'          => 'pdf_receipt_template_select',
			'default'       => $template_id,
			'wrapper_class' => 'custom-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_receipt_template_name',
			'name'          => __( 'Template Name', 'give-pdf-receipts' ),
			'description'   => __( 'Please provide your customized receipt template a unique name.', 'give-pdf-receipts' ),
			'type'          => 'pdf_receipt_template_name',
			'default'       => $post_title,
			'wrapper_class' => 'custom-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_builder',
			'name'          => __( 'PDF Builder', 'give-pdf-receipts' ),
			'description'   => sprintf( __( 'Use "dejavusans" font-family as a inline css in Table based html layout to support Greek characters. Available template tags: %s', 'give-pdf-receipts' ), get_supported_pdf_tags() ),
			'type'          => 'wysiwyg',
			'default'       => $post_content,
			'wrapper_class' => 'custom-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_builder_page_size',
			'name'          => __( 'Page Size', 'give-pdf-receipts' ),
			'description'   => __( 'Select the page size you would like the PDF receipts to be generated at. Note: You may need to adjust the content width if you generate a page size smaller than the default size.', 'give-pdf-receipts' ),
			'type'          => 'select',
			'default'       => 'letter',
			'options'       => array(
				'letter' => __( 'Letter (8.5 by 11 inches)', 'give-pdf-receipts' ),
				'A4'     => __( 'A4 (8.27 by 11.69 inches)', 'give-pdf-receipts' ),
				'A5'     => __( 'A5 (5.8 by 8.3 inches)', 'give-pdf-receipts' ),
				'A6'     => __( 'A6 (4.1 by 5.8 inches)', 'give-pdf-receipts' ),
			),
			'wrapper_class' => 'custom-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_builder_special_chars',
			'name'          => __( 'Special Characters', 'give-pdf-receipts' ),
			'description'   => __( 'Characters not displaying correctly? Check to enable the DejaVu Sans font replacing Open Sans/Helvetica/Times. Enable this option if you have characters which do not display correctly (e.g. Greek characters, Japanese, Mandarin, etc.)', 'give-pdf-receipts' ),
			'type'          => 'radio_inline',
			'default'       => 'disabled',
			'options'       => array(
				'enabled'  => 'Enabled',
				'disabled' => 'Disabled',
			),
			'wrapper_class' => 'custom-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_preview_template',
			'name'          => __( 'Preview Template', 'give-pdf-receipts' ),
			'description'   => __( 'Click the button above to preview how the PDF will appear to donors. Be sure to save your changes prior to previewing. Please note that sample data will be added in the place of your template tags.', 'give-pdf-receipts' ),
			'type'          => 'pdf_receipts_preview_button',
			'wrapper_class' => 'custom-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		//--------------------------------------
		// Set PDF Templates Options
		//--------------------------------------
		array(
			'id'            => 'give_pdf_templates',
			'name'          => __( 'Receipt Template', 'give-pdf-receipts' ),
			'description'   => __( 'Please select a template for your PDF Receipts. This template will be used for all Give PDF Receipts.', 'give-pdf-receipts' ),
			'type'          => 'select',
			'options'       => apply_filters( 'give_pdf_templates_list', array(
				'default'     => __( 'Default', 'give-pdf-receipts' ),
				'blue_stripe' => __( 'Stacked', 'give-pdf-receipts' ),
				'lines'       => __( 'Lines', 'give-pdf-receipts' ),
				'minimal'     => __( 'Minimal', 'give-pdf-receipts' ),
				'traditional' => __( 'Traditional', 'give-pdf-receipts' ),
			) ),
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_colorpicker',
			'name'          => __( 'Color Picker', 'give-pdf-receipts' ),
			'type'          => 'colorpicker',
			'description'   => __( 'Customize the main color used for headings and some backgrounds within the PDF receipt template.', 'give-pdf-receipts' ),
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
			'default'       => '#333',
		),
		array(
			'id'            => 'give_set_pdf_preview_template',
			'name'          => __( 'Preview PDF', 'give-pdf-receipts' ),
			'description'   => __( 'Click the button above to preview how the PDF will appear to donors. Be sure to save your changes prior to previewing. Please note that sample data will be added in the place of your template tags.', 'give-pdf-receipts' ),
			'type'          => 'set_pdf_receipts_preview_button',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_logo_upload',
			'name'          => __( 'Logo Upload', 'give-pdf-receipts' ),
			'description'   => __( 'Upload the logo that you would like to display on the receipt. If the logo is greater than 90px in height, it will not be shown. On the Traditional template, if the logo is greater than 80px in height, it will not be shown. Also note that the logo will be output at 96 dpi.', 'give-pdf-receipts' ),
			'type'          => 'file',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_company_name',
			'name'          => __( 'Organization Name', 'give-pdf-receipts' ),
			'description'   => __( 'Enter the organization name that will be shown on the receipt.', 'give-pdf-receipts' ),
			'type'          => 'text',
			'size'          => 'regular',
			'default'       => '',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_name',
			'name'          => __( 'Name', 'give-pdf-receipts' ),
			'description'   => __( 'Enter the name that will be shown on the receipt.', 'give-pdf-receipts' ),
			'type'          => 'text',
			'default'       => '',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_address_line1',
			'name'          => __( 'Address Line 1', 'give-pdf-receipts' ),
			'description'   => __( 'Enter the first address line that will appear on the receipt.', 'give-pdf-receipts' ),
			'type'          => 'text',
			'default'       => '',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_address_line2',
			'name'          => __( 'Address Line 2', 'give-pdf-receipts' ),
			'description'   => __( 'Enter the second address line that will appear on the receipt.', 'give-pdf-receipts' ),
			'type'          => 'text',
			'default'       => '',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_address_city_state_zip',
			'name'          => __( 'City, State and Zip Code', 'give-pdf-receipts' ),
			'description'   => __( 'Enter the city, state/province/county and zip/postal code that will appear on the receipt.', 'give-pdf-receipts' ),
			'type'          => 'text',
			'default'       => '',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_email_address',
			'name'          => __( 'Email Address', 'give-pdf-receipts' ),
			'description'   => __( 'Enter the email address that will appear on the receipt.', 'give-pdf-receipts' ),
			'type'          => 'text',
			'default'       => get_option( 'admin_email' ),
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_url',
			'name'          => __( 'Display Website URL', 'give-pdf-receipts' ),
			'description'   => __( 'Check this box if you would like your website url to be displayed on the receipt.', 'give-pdf-receipts' ),
			'type'          => 'checkbox',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_header_message',
			'name'          => __( 'Header Message', 'give-pdf-receipts' ),
			'description'   => __( 'Enter the message you would like to be shown on the header of the receipt. Please note that the header will not show up on the Blue Stripe and Traditional template.', 'give-pdf-receipts' ),
			'type'          => 'text',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_footer_message',
			'name'          => __( 'Footer Message', 'give-pdf-receipts' ),
			'description'   => __( 'Enter the message you would like to be shown on the footer of the receipt.', 'give-pdf-receipts' ),
			'type'          => 'text',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_additional_notes',
			'name'          => __( 'Additional Notes', 'give-pdf-receipts' ),
			'description'   => __( 'Enter any messages you would to be displayed at the end of the receipt. Only plain text is currently supported. Any HTML will not be shown on the receipt.', 'give-pdf-receipts' ) . __( 'The following template tags will work for the Header and Footer message as well as the Additional Notes:', 'give-pdf-receipts' ) . '<ul><li>' . __( '<code>{page}</code> - Page Number', 'give-pdf-receipts' ) . '</li><li>' . __( '<code>{sitename}</code> - Site Name', 'give-pdf-receipts' ) . '</li><li>' . __( '<code>{today}</code> - Date of Receipt Generation', 'give-pdf-receipts' ) . '</li><li>' . __( '<code>{date}</code> - Receipt Date', 'give-pdf-receipts' ) . '</li><li>' . __( '<code>{receipt_id}</code> - Receipt ID', 'give-pdf-receipts' ) . '</li></ul>',
			'type'          => 'textarea',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'id'            => 'give_pdf_enable_char_support',
			'name'          => __( 'Special Characters', 'give-pdf-receipts' ),
			'description'   => __( 'Characters not displaying correctly? Check to enable the DejaVu Sans Full font replacing Open Sans/Helvetica/Times. Enable this option if you have characters which do not display correctly (e.g. Greek characters, Japanese, Mandarin, etc.)', 'give-pdf-receipts' ),
			'type'          => 'checkbox',
			'wrapper_class' => 'set-pdf-builder-option pdf-receipts-fields give-hidden',
		),
		array(
			'name'  => esc_html__( 'PDF Receipts Docs', 'give-pdf-receipts' ),
			'id'    => 'pdf_receipt_docs_link',
			'url'   => esc_url( 'http://docs.givewp.com/addon-pdf-receipts' ),
			'title' => __( 'PDF Receipts Docs', 'give-pdf-receipts' ),
			'type'  => 'give_docs_link',
		),
		array(
			'id'   => 'pdf_receipts_settings',
			'type' => 'sectionend',
		),
	);

	return $pdf_receipts_fields;
}

/**
 * Supported PDF Tags.
 *
 * @return string
 */
function get_supported_pdf_tags() {
	ob_start();
	?>
	<br />
	<ul class="give-email-tags-wrap">
	<?php
	// Is FFM available? Take care of repeater fields.
	if ( class_exists( 'Give_FFM_Render_Form' ) ) { ?>
		<li class="give_donation_tag"><code>{all_custom_fields}</code>: <?php echo __( 'This tag can be used to output a donation form\'s custom field data created through Form Field Manager.', 'give-pdf-receipts' ); ?></li>
	<?php } ?>
		<li class="give_donation_tag"><code>{donation_name}</code>: <?php echo _x( 'The name of completed donation form', 'An explanation of the {donation_name} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{first_name}</code>: <?php echo _x( 'The donor\'s first name', 'An explanation of the {first_name} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{full_name}</code>: <?php echo _x( 'The donor\'s full name, first and last', 'An explanation of the {full_name} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{username}</code>: <?php echo _x( 'The donor\'s user name on the site, if they registered an account', 'An explanation of the {username} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{user_email}</code>: <?php echo _x( 'The donor\'s email address', 'An explanation of the {user_email} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{billing_address}</code>: <?php echo _x( 'The donor\'s billing address', 'An explanation of the {billing_address} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{date}</code>: <?php echo _x( 'The date of the donation', 'An explanation of the {date} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{today}</code>: <?php echo _x( 'The date of the receipt', 'An explanation of the {today} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{amount}</code>: <?php echo _x( 'The total price of the donation', 'An explanation of the {amount} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{payment_id}</code>: <?php echo _x( 'The unique ID number for this donation', 'An explanation of the {payment_id} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{receipt_id}</code>: <?php echo _x( 'The unique ID number for this donation receipt', 'An explanation of the {receipt_id} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{payment_method}</code>: <?php echo _x( 'The method of payment used for this donation', 'An explanation of the {payment_method} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{sitename}</code>: <?php echo _x( 'Your site name', 'An explanation of the {sitename} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{receipt_link}</code>: <?php echo _x( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly', 'An explanation of the {receipt_link} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{transaction_id}</code>: <?php echo _x( 'The transaction ID returned from the payment gateway. If none is found then it will return Give\'s internal donation payment ID.', 'An explanation of the {transaction_id} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{transaction_key}</code>: <?php echo _x( 'The donation transaction key', 'An explanation of the {transaction_key} template tag', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{payment_status}</code>:<?php echo _x( 'Status of the donation', 'An explanation of the {payment_status} template tag', 'give-pdf-receipts' ); ?></li>
	<?php
	// Support recurring donation email tag.
	if ( class_exists( 'Give_Recurring_Emails' ) ) { ?>
		<li class="give_donation_tag"><code>{renewal_link}</code>: <?php echo __( 'The Recurring donation renewal links.', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{completion_date}</code>: <?php echo __( 'The Recurring donation completion date.', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{subscription_frequency}</code>: <?php echo __( 'The Recurring donation subscription frequency.', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{subscriptions_completed}</code>: <?php echo __( 'The Recurring donation subscription completed.', 'give-pdf-receipts' ); ?></li>
		<li class="give_donation_tag"><code>{cancellation_date}</code>: <?php echo __( 'The Recurring donation cancellation date.', 'give-pdf-receipts' ); ?></li>
	<?php } ?>
	</ul>
	<?php
	return ob_get_clean();

}

/**
 * Per-form set pdf preview button.
 *
 * @since 2.1.0
 *
 * @param array $field Field array.
 */
function give_set_pdf_receipts_preview_button( $field ) {
	global $thepostid, $post;

	// Get the Donation form id.
	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

	// Get the styles if passed with the field array.
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';

	// Get the option value by field and donation form id.
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Generate name for option field.
	$field['name'] = isset( $field['name'] ) ? $field['name'] : $field['id'];

	?>
	<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
		<a href="<?php echo esc_url( add_query_arg(
			array(
				'give_pdf_receipts_action' => 'preview_set_pdf_template',
				'form_id'                  => $thepostid,
			), admin_url() ) ); ?>"
		   class="button-secondary" target="_blank"
		   title="<?php _e( 'Preview Set PDF Template', 'give-pdf-receipts' ); ?> ">
			<?php _e( 'Preview Set PDF Template', 'give-pdf-receipts' ); ?>
		</a>
		<?php
		echo give_get_field_description( $field );
		?>
	</p>
	<?php
}

/**
 * Per-form pdf preview button.
 *
 * @since 2.1.0
 *
 * @param array $field Field array.
 */
function give_pdf_receipts_preview_button( $field ) {
	global $thepostid, $post;

	// Get the Donation form id.
	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

	// Get the styles if passed with the field array.
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';

	// Get the option value by field and donation form id.
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Generate name for option field.
	$field['name'] = isset( $field['name'] ) ? $field['name'] : $field['id'];

	?>
	<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
		<a href="<?php echo esc_url( add_query_arg(
			array(
				'give_pdf_receipts_action' => 'preview_pdf',
				'form_id'                  => $thepostid,
			), admin_url() ) ); ?>"
		   class="button-secondary" target="_blank"
		   title="<?php _e( 'Preview PDF', 'give-pdf-receipts' ); ?> ">
			<?php _e( 'Preview PDF', 'give-pdf-receipts' ); ?>
		</a>
		<?php
		echo give_get_field_description( $field );
		?>
	</p>
	<?php
}

/**
 * Is per form enabled.
 *
 * Returns true if PDF Receipt is customized on the form.
 * Useful for checking if a form has PDF Receipt customized.
 *
 * @since 2.1.0
 *
 * @param int $form_id Form ID.
 *
 * @return bool
 */
function give_pdf_receipts_is_per_form_customized( $form_id ) {
	return apply_filters( 'give_pdf_receipts_is_per_form_customized', give_is_setting_enabled( give_get_meta( $form_id, 'give_pdf_receipts_enable_disable', true ) ) );
}

/**
 * Is PDF Receipt enable/disable.
 *
 * Returns true if PDF Receipt is disable as a per form or Global disabled(if enabled Globally in Per-Form).
 *
 * @since 2.1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return bool
 */
function give_pdf_receipts_disable( $payment_id ) {

	$form_id       = give_get_payment_form_id( $payment_id );
	$per_form      = give_get_meta( $form_id, 'give_pdf_receipts_enable_disable', true );
	$global_option = give_get_option( 'give_pdf_receipts_enable_disable', 'enabled' );

	$output = false;

	if (
		'disabled' === $per_form
		|| ( 'global' === $per_form && ! give_is_setting_enabled( $global_option ) )
	) {
		$output = true;
	}

	return apply_filters( 'give_pdf_receipts_disable', $output, $form_id, $payment_id );
}

/**
 * Build PDF Receipts Per-Form settings array.
 *
 * @since 2.1.0
 *
 * @param int $form_id
 *
 * @return  array $give_options
 */
function give_pdf_receipts_per_form_settings( $form_id ) {
	$give_options        = array();
	$give_post_meta_data = give_get_meta( $form_id, '' );

	foreach ( $give_post_meta_data as $key => $post_meta_value ) {
		if ( false !== strpos( $key, 'give_pdf_' ) ) {
			$give_options[ $key ] = $post_meta_value[0];
		}
	}

	return $give_options;
}

/**
 * Get PDF Receipts settings array based on Global or Per-Form.
 *
 * @since 2.1.0
 *
 * @param int $form_id
 *
 * @return array $give_options PDF Receipt settings.
 */
function give_get_pdf_receipts_all_settings( $form_id ) {
	// If per form then grab data from particular Form.
	$is_per_form = give_pdf_receipts_is_per_form_customized( $form_id );
	if ( $is_per_form ) {
		$give_options = give_pdf_receipts_per_form_settings( $form_id );
	} else {
		$give_options = give_get_settings();
	}

	return $give_options;
}

/**
 * PDF Receipt Name Field
 *
 * @param $value
 */
function give_pdf_receipt_template_name( $value ) {

	$template_id = isset( $_GET['post'] ) ? give_get_meta( $_GET['post'], 'give_pdf_receipt_template', true ) : give_get_option( 'give_pdf_receipt_template' );
	$screen      = get_current_screen();

	ob_start(); ?>
	<input id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>" type="text" value="<?php echo get_the_title( $template_id ); ?>"
	       class="give-input-field" readonly />

	<div class="give-pdf-action-wrap">
		<a href="#" class="give-pdf-action-rename"><?php esc_html_e( 'Rename', 'give-pdf-receipts' ); ?></a>
		<span class="give-pdf-action-sep">|</span>
		<a href="#" class="give-pdf-action-delete"><?php esc_html_e( 'Delete', 'give-pdf-receipts' ); ?></a>
	</div>
	<p class="give-field-description"><?php echo give_get_field_description( $value ); ?></p>
	<?php
	$field_content = ob_get_clean();

	// Output field type per form or global.
	ob_start();

	// Global settings fields wrap.
	if ( isset( $_GET['page'] ) && 'give-settings' === $_GET['page'] ) : ?>
		<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_attr( $value['name'] ); ?></label>
			</th>
			<td class="give-pdf-receipts-td" colspan="2">
				<?php echo $field_content; ?>
			</td>
		</tr>
	<?php // Single post type field wrap.
	elseif ( isset( $screen->post_type ) && 'give_forms' === $screen->post_type ) : ?>
		<fieldset class="give-field-wrap <?php echo esc_attr( $value['id'] ); ?>_field <?php echo esc_attr( $value['wrapper_class'] ); ?>">
			<label for="<?php echo give_get_field_name( $value ); ?>"><?php echo wp_kses_post( $value['name'] ); ?></label>
			<?php echo $field_content; ?>
		</fieldset>
		<?php
	endif;

	echo ob_get_clean();

}

/**
 * PDF Receipt Template Dropdown Select
 *
 * @param $value
 */
function give_pdf_receipt_template_select( $value ) {

	// Get customized templates
	$custom_posts = get_posts( apply_filters( 'give_custom_pdf_receipts_templates_query_args', array(
			'post_type'      => 'Give_PDF_Template',
			'post_status'    => array( 'draft', 'publish' ),
			'posts_per_page' => - 1,
			'meta_query'     => array(
				array(
					'key'     => '_give_pdf_receipts_template',
					'compare' => 'NOT EXISTS' // this should work...
				),
			),
		)
	) );

	// Get default templates
	$default_posts = get_posts( apply_filters( 'give_pdf_receipts_templates_query_args', array(
			'post_type'      => 'Give_PDF_Template',
			'post_status'    => array( 'draft', 'publish' ),
			'posts_per_page' => - 1,
			'meta_query'     => array(
				array(
					'key'     => '_give_pdf_receipts_template',
					'compare' => 'EXISTS' // this should work...
				),
			),
		)
	) );

	$default = isset( $value['default'] ) ? $value['default'] : '';
	$screen  = get_current_screen();

	ob_start(); ?>
	<select id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>">

		<?php if ( $custom_posts ) : ?>
			<optgroup label="<?php esc_attr_e( 'My Templates', 'give-pdf-receipts' ) ?>">
				<?php do_action( 'give_pdf_custom_optgroup_templates_before' ); ?>
				<?php foreach ( $custom_posts as $post ) : ?>
					<option value="<?php echo $post->ID; ?>" <?php echo $post->ID == $default ? 'selected' : ''; ?>><?php echo $post->post_title; ?></option>
				<?php endforeach; ?>
				<?php do_action( 'give_pdf_custom_optgroup_templates_after' ); ?>
			</optgroup>
		<?php endif; ?>

		<optgroup label="<?php esc_attr_e( 'Starter Templates', 'give-pdf-receipts' ) ?>">
			<?php do_action( 'give_pdf_starter_optgroup_templates_before' ); ?>
			<option value="create_new"><?php _e( 'Blank Template', 'give-pdf-receipts' ); ?></option>
			<?php if ( $default_posts ) : ?>
				<?php foreach ( $default_posts as $post ) : ?>
					<option value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php do_action( 'give_pdf_starter_optgroup_templates_after' ); ?>
		</optgroup>
	</select>

	<p class="give-field-description"><?php echo give_get_field_description( $value ); ?></p>
	<?php
	$field_content = ob_get_clean();

	ob_start();
	// Single post type
	if ( isset( $_GET['page'] ) && 'give-settings' === $_GET['page'] ) : ?>
		<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_attr( $value['name'] ); ?></label>
			</th>
			<td class="give-pdf-receipts-td" colspan="2">
				<?php echo $field_content; ?>
			</td>
		</tr>
	<?php // Global Settings
	elseif ( isset( $screen->post_type ) && 'give_forms' === $screen->post_type ) : ?>
		<fieldset class="give-field-wrap <?php echo esc_attr( $value['id'] ); ?>_field <?php echo esc_attr( $value['wrapper_class'] ); ?>">
			<label for="<?php echo give_get_field_name( $value ); ?>"><?php echo wp_kses_post( $value['name'] ); ?></label>
			<?php echo $field_content; ?>
		</fieldset>
		<?php

	endif;

	echo ob_get_clean();
}