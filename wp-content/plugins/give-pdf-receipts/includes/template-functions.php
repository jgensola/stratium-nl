<?php
/**
 * Template Functions
 *
 * @description: All the template functions for the PDF receipt when they are being built or generated.
 *
 * @package    Give PDF Receipts
 * @since      1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Settings
 *
 * Gets the settings for PDF Receipts plugin if they exist.
 *
 * @since 1.0
 *
 * @param object $give_pdf PDF receipt object
 * @param string $setting Setting name
 *
 * @return string Returns option if it exists.
 */
function give_pdf_get_settings( $give_pdf, $setting ) {

	$payment_id       = ! empty( $_GET['payment_id'] ) ? $_GET['payment_id'] : '';
	$give_pdf_payment = get_post( $payment_id );
	$form_id          = give_get_payment_form_id( $payment_id );
	$form_id          = ! empty( $form_id ) ? $form_id : 0;

	// Get Form ID if Preview  Per-Form.
	if ( ! empty( $_GET['form_id'] ) ) {
		$form_id = absint( $_GET['form_id'] );
	}

	// Get PDF Receipts Settings based on Global or Per-Form.
	$give_options = give_get_pdf_receipts_all_settings( $form_id );

	if ( 'name' === $setting ) {
		if ( isset( $give_options['give_pdf_name'] ) ) {
			return $give_options['give_pdf_name'];
		}
	}

	if ( 'addr_line1' === $setting ) {
		if ( isset( $give_options['give_pdf_address_line1'] ) ) {
			return $give_options['give_pdf_address_line1'];
		}
	}

	if ( 'addr_line2' === $setting ) {
		if ( isset( $give_options['give_pdf_address_line2'] ) ) {
			return $give_options['give_pdf_address_line2'];
		}
	}

	if ( 'city_state_zip' === $setting ) {
		if ( isset( $give_options['give_pdf_address_city_state_zip'] ) ) {
			return $give_options['give_pdf_address_city_state_zip'];
		}
	}

	if ( 'email' === $setting ) {
		if ( isset( $give_options['give_pdf_email_address'] ) ) {
			return $give_options['give_pdf_email_address'];
		}
	}

	if ( 'notes' === $setting ) {
		if ( isset( $give_options['give_pdf_additional_notes'] ) && ! empty( $give_options['give_pdf_additional_notes'] ) ) {
			$give_pdf_additional_notes = $give_options['give_pdf_additional_notes'];
			$give_pdf_payment_date     = ! empty( $give_pdf_payment->post_date ) ? strtotime( $give_pdf_payment->post_date ) : current_time( 'timestamp', 1 );
			$receipt_id                = ! empty( $give_pdf_payment->ID ) ? give_pdf_get_payment_number( $give_pdf_payment->ID ) : '123456789';

			$give_pdf_additional_notes = str_replace( '{page}', 'Page' . $give_pdf->getPage(), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{sitename}', get_bloginfo( 'name' ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{today}', date_i18n( get_option( 'date_format' ), time() ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{date}', date_i18n( get_option( 'date_format' ), strtotime( $give_pdf_payment_date ) ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{receipt_id}', $receipt_id, $give_pdf_additional_notes );
			$give_pdf_additional_notes = strip_tags( $give_pdf_additional_notes );
			$give_pdf_additional_notes = stripslashes_deep( html_entity_decode( $give_pdf_additional_notes, ENT_COMPAT, 'UTF-8' ) );

			return $give_pdf_additional_notes;
		}
	}

	return '';
}

/**
 * Calculate Line Heights
 *
 * Calculates the line heights for the 'To' block
 *
 * @since 1.0
 *
 * @param string $setting Setting name.
 *
 * @return string Returns line height.
 */
function give_pdf_calculate_line_height( $setting ) {
	if ( empty( $setting ) ) {
		return 0;
	} else {
		return 6;
	}
}

/**
 * Retrieve the payment number
 *
 * If sequential order numbers are enabled, this returns the order numbered
 *
 * @since       1.0
 *
 * @param int $payment_id
 *
 * @return int|string
 */
function give_pdf_get_payment_number( $payment_id = 0 ) {
	if ( function_exists( 'give_get_payment_number' ) ) {
		return give_get_payment_number( $payment_id );
	} else {
		return $payment_id;
	}
}

/**
 * Create html content by template.
 *
 * @since   1.0
 * @updated 2.2.0
 *
 * @param string $template_content Template content
 * @param WP_Post|string $give_pdf_payment Payment information related to the Donation.
 * @param string $give_pdf_payment_method Payment method.
 * @param string $give_pdf_payment_status Payment status.
 * @param array $give_pdf_payment_meta Payment meta.
 * @param array $give_pdf_buyer_info Donor address information.
 * @param string $give_pdf_payment_date Donation date.
 * @param string $transaction_id The gateway payment ID save to payment meta.
 * @param string $receipt_link Receipt link.
 * @param bool $is_div_layout true = div based layout | false = table based layout.
 * @param bool $pdf_preview true = For preview PDF in browser |  false = For download PDF in browser.
 *
 * @return string Returns html content
 */
function give_pdf_get_compile_html( $template_content, $give_pdf_payment, $give_pdf_payment_method, $give_pdf_payment_status, $give_pdf_payment_meta, $give_pdf_buyer_info, $give_pdf_payment_date,  $transaction_id, $receipt_link, $is_div_layout, $pdf_preview ) {

	$payment_id    = isset( $give_pdf_payment->ID ) ? $give_pdf_payment->ID : 0;
	$currency_code = give_get_currency( $payment_id );
	$currency_code = ! empty( $currency_code ) ? $currency_code : give_get_currency();

	// Actual donation amount.
	$donation_amount = give_currency_filter(
		give_format_amount( give_donation_amount( $payment_id ), array(
			'currency' => $currency_code,
		) ),
		array( 'currency_code' => $currency_code, 'decode_currency' => true )
	);

	// Set default amount.
	$default_amount = give_currency_filter(
		give_format_amount( 25, array(
			'currency' => $currency_code,
		) ),
		array( 'currency_code' => $currency_code, 'decode_currency' => true )
	);

	$give_pdf_total_price = ! empty( $payment_id ) ? $donation_amount : $default_amount;

	$user_info = isset( $give_pdf_buyer_info['id'] ) ? get_userdata( $give_pdf_buyer_info['id'] ) : '';
	$form_id   = give_get_payment_form_id( $payment_id );
	$form_id   = ! empty( $form_id ) ? $form_id : 0;

	// Get Form ID if Preview  Per-Form.
	if ( ! empty( $_GET['form_id'] ) ) {
		$form_id = absint( $_GET['form_id'] );
	}

	// Get Global/PerForm based settings.
	$give_options = give_get_pdf_receipts_all_settings( $form_id );
	$is_per_form  = give_pdf_receipts_is_per_form_customized( $form_id );

	if ( $is_per_form ) {
		$page_size         = give_get_meta( $form_id, 'give_pdf_builder_page_size', true );
		$page_size         = ! empty( $page_size ) ? $page_size : 'letter';
		$css_font_override = give_is_setting_enabled( give_get_meta( $form_id, 'give_pdf_builder_special_chars', true ) ) ? 'font-family: DejaVu Sans !important;' : '';
	} else {
		$page_size         = give_get_option( 'give_pdf_builder_page_size', array( 0, 0, 595.28, 841.89 ) );
		$css_font_override = give_is_setting_enabled( give_get_option( 'give_pdf_builder_special_chars' ) ) ? 'font-family: DejaVu Sans !important;' : '';
	}

	$billing_address       = isset( $give_pdf_buyer_info['address'] ) ? give_pdf_get_formatted_billing_address( $give_pdf_buyer_info['address'] ) : '';
	$receipt_id            = isset( $give_pdf_payment->ID ) ? give_pdf_get_payment_number( $give_pdf_payment->ID ) : '123456789';
	$transaction_key       = isset( $give_pdf_payment_meta['key'] ) ? $give_pdf_payment_meta['key'] : '90120939030939239';
	$payment_id            = isset( $give_pdf_payment->ID ) ? Give()->seq_donation_number->get_serial_code( $give_pdf_payment->ID ) : '123456789';
	$full_name             = ( isset( $give_pdf_buyer_info['first_name'] ) && isset( $give_pdf_buyer_info['last_name'] ) ) ? $give_pdf_buyer_info['first_name'] . ' ' . $give_pdf_buyer_info['last_name'] : 'John Doe';
	$give_pdf_payment_date = ! empty( $give_pdf_payment_date ) ? date_i18n( get_option( 'date_format' ), strtotime( $give_pdf_payment->post_date ) ) : date_i18n( get_option( 'date_format' ), current_time( 'timestamp', 1 ) );
	$user_email            = isset( $give_pdf_buyer_info['email'] ) ? $give_pdf_buyer_info['email'] : 'my.email@email.com';
	$username              = isset( $user_info->user_login ) ? $user_info->user_login : __( 'No Username Found', 'give-pdf-receipts' );

	// Transaction ID.
	$transaction_id        = ! empty( $transaction_id) ? $transaction_id : give_get_meta( $payment_id,'_give_payment_transaction_id', true );

	// Fee Recovery support.
	$give_fee_amount = give_get_meta( $payment_id, '_give_fee_amount', true );

	if ( ! empty( $give_fee_amount ) && method_exists( 'Give_Fee_Recovery_Admin', 'give_fee_email_tag_amount' ) ) {
		$fee_recovery     = new Give_Fee_Recovery_Admin();
		$new_amount       = $fee_recovery->give_fee_email_tag_amount( $payment_id );
		$new_amount       = ( '123456789' !== $payment_id ) ? $new_amount : $default_amount;
		$template_content = str_replace( '{price}', $new_amount, $template_content );
		$template_content = str_replace( '{amount}', $new_amount, $template_content );
	} else {
		$template_content = str_replace( '{price}', $give_pdf_total_price, $template_content );
		$template_content = str_replace( '{amount}', $give_pdf_total_price, $template_content );
	}

	// Replace tags.
	$template_content = str_replace( '{donation_name}', isset( $give_pdf_payment_meta['form_title'] ) ? $give_pdf_payment_meta['form_title'] : __( 'Example Donation Form Title', 'give-pdf-receipts' ), $template_content );
	$template_content = str_replace( '{first_name}', isset( $give_pdf_buyer_info['first_name'] ) ? $give_pdf_buyer_info['first_name'] : 'John', $template_content );
	$template_content = str_replace( '{full_name}', $full_name, $template_content );
	$template_content = str_replace( '{username}', $username, $template_content );
	$template_content = str_replace( '{user_email}', $user_email, $template_content );
	$template_content = str_replace( '{billing_address}', $billing_address, $template_content );
	$template_content = str_replace( '{date}', $give_pdf_payment_date, $template_content );
	$template_content = str_replace( '{payment_id}', $payment_id, $template_content );
	$template_content = str_replace( '{receipt_id}', $receipt_id, $template_content );
	$template_content = str_replace( '{payment_method}', $give_pdf_payment_method, $template_content );
	$template_content = str_replace( '{sitename}', get_bloginfo( 'name' ), $template_content );
	$template_content = str_replace( '{receipt_link}', $receipt_link, $template_content );
	$template_content = str_replace( '{transaction_id}', $transaction_id, $template_content );
	$template_content = str_replace( '{transaction_key}', $transaction_key, $template_content );
	$template_content = str_replace( '{payment_status}', $give_pdf_payment_status, $template_content );
	$template_content = str_replace( '{today}', date_i18n( get_option( 'date_format' ), time() ), $template_content );

	// FFM {all_custom_fields} support.
	if ( class_exists( 'Give_FFM_Emails' ) ) {
		$ffm_email        = new Give_FFM_Emails();
		$template_content = str_replace( '{all_custom_fields}', $ffm_email->all_custom_email_tag( $transaction_id ), $template_content );
	}

	// Support recurring tags.
	if ( class_exists( 'Give_Recurring_Emails' ) ) {
		$recurring_email = new Give_Recurring_Emails();
		$tag_args        = array( 'payment_id' => $payment_id );

		$renewal_link     = $recurring_email::filter_email_tags( $tag_args, 'renewal_link' );
		$template_content = str_replace( '{renewal_link}', $renewal_link, $template_content );

		$completion_date  = $recurring_email::filter_email_tags( $tag_args, 'completion_date' );
		$template_content = str_replace( '{completion_date}', $completion_date, $template_content );

		$subscription_frequency = $recurring_email::filter_email_tags( $tag_args, 'subscription_frequency' );
		$template_content       = str_replace( '{subscription_frequency}', $subscription_frequency, $template_content );

		$subscriptions_completed = $recurring_email::filter_email_tags( $tag_args, 'subscriptions_completed' );
		$template_content        = str_replace( '{subscriptions_completed}', $subscriptions_completed, $template_content );

		$cancellation_date = $recurring_email::filter_email_tags( $tag_args, 'cancellation_date' );
		$template_content  = str_replace( '{cancellation_date}', $cancellation_date, $template_content );
	}

	// Return Template content if Div based layout.
	if ( $is_div_layout ) {
		$styles = apply_filters( 'give_pdf_header_styles', '<style> html, body{ margin: 0; padding: 0; ' . $css_font_override . ' } </style>', $payment_id );

		// Wrap in proper HTML5 template tags.
		$template_content = apply_filters( 'give_pdf_header', '<!DOCTYPE html>
			<html lang="en">
			  <head>
			    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			    <title>' . apply_filters( 'give_custom_pdf_receipt_title', __( 'PDF Preview', 'give-pdf-receipts' ) ) . '</title>
			    ' . $styles . '
			  </head>
			  <body>' ) . $template_content . apply_filters( 'give_pdf_footer', '</body></html>' );

		return apply_filters( 'give_pdf_get_template_content', $template_content, $payment_id );

	}

	/**
	 * TCPDF Generation Method.
	 *
	 * Generate pdf using legacy TCPDF default template.
	 */
	require_once GIVE_PLUGIN_DIR . '/includes/libraries/tcpdf/tcpdf.php';
	include_once( GIVE_PDF_PLUGIN_DIR . '/includes/class-give-tcpdf.php' );

	$give_pdf = new Give_PDF_Receipt( 'P', 'mm', $page_size, true, 'UTF-8', false );
	$give_pdf->SetMargins( 0, 0, 0, false );
	$give_pdf->setPrintHeader( false ); // Remove default Header.
	$give_pdf->setPrintFooter( false ); // Remove default footer.
	$give_pdf->setCellPaddings( 0, 0, 0, 0 );
	$give_pdf->setImageScale( 1.6 );
	$give_pdf->setCellMargins( 0, 0, 0, 0 );
	$font = apply_filters( 'give_pdf_receipt_default_font', 'Helvetica' );

	if ( ! isset( $give_options['give_pdf_builder_special_chars'] ) ) {
		$give_options['give_pdf_builder_special_chars'] = 'disabled';
	}
	$currency_font = ! give_is_setting_enabled( $give_options['give_pdf_builder_special_chars'] ) ? $font : 'DejaVuSans';

	// Set 'CODE2000' font for Iranian Rial and Russian Rubble country for support currency sign.
	if ( in_array( give_get_currency(), give_pdf_receipt_code2000_supported_countries() )
	     || in_array( $currency_code, give_pdf_receipt_code2000_supported_countries() )
	) {
		$currency_font = apply_filters( 'give_pdf_receipt_currency_font', 'CODE2000' );
	}

	$give_pdf->AddPage( 'P', $page_size );
	$give_pdf->SetFont( $currency_font, '' );
	$give_pdf->SetAuthor( apply_filters( 'give_custom_pdf_receipt_author', get_option( 'blogname' ) ) );

	if ( '123456789' === $payment_id ) {
		$give_pdf->SetTitle( apply_filters( 'give_custom_pdf_receipt_title', __( 'PDF Preview', 'give-pdf-receipts' ) ) );
		$pdf_receipt_filename = apply_filters( 'give_custom_pdf_receipt_filename_prefix', __( 'Receipt-Preview', 'give-pdf-receipts' ) ) . '.pdf';
	} else {
		$give_pdf->SetTitle( apply_filters( 'give_custom_pdf_receipt_title', __( 'Receipt ', 'give-pdf-receipts' ) ) . give_pdf_get_payment_number( $payment_id ) );
		$pdf_receipt_filename = apply_filters( 'give_custom_pdf_receipt_filename_prefix', __( 'Receipt ', 'give-pdf-receipts' ) ) . give_pdf_get_payment_number( $payment_id ) . '.pdf';
	}

	// Output Blank PDF if table of content blank.
	if ( empty( $template_content ) ) {
		$pdf_output = ( $pdf_preview ) ? 'I' : 'D';
		$give_pdf->Output( $pdf_receipt_filename, wp_is_mobile() ? 'I' : $pdf_output );

		exit;
	}

	$give_pdf->writeHTMLCell( '', '', '', '', $template_content, 0, 0, false, false, '', false );
	$give_pdf->setCellMargins( 0, 0, 0, 0 );
	$give_pdf->SetMargins( 0, 0, 0, false );

	$last_cell_height     = $give_pdf->getLastH();
	$page_height          = $give_pdf->getPageHeight();
	$remain_height        = $page_height - $last_cell_height;
	$bottom_margin        = $give_pdf->getBreakMargin( 1 );
	$remain_margin_height = $page_height - $bottom_margin;

	$bg = array( 237, 237, 237 );
	if ( false !== strpos( $template_content, 'fresh_blue' ) || false !== strpos( $template_content, 'light_gray' ) ) {
		$bg = array( 237, 237, 237 );
	} elseif ( false !== strpos( $template_content, 'night_white' ) ) {
		$bg = array( 61, 61, 61 );
	} elseif ( false !== strpos( $template_content, 'professional_serif' ) ) {
		$bg = array( 82, 82, 82 );
	}

	/**
	 * Fill Bottom BG expand based on template.
	 *
	 * Calculate bottom Margin for each page and fill.
	 * Provided filter to change BG color.
	 *
	 * @since 2.2.0
	 */
	$give_pdf->Rect( 0, $remain_margin_height, $give_pdf->getPageWidth(), $bottom_margin, 'F', array(
		'L' => 0,
		'T' => 0,
		'R' => 0,
		'B' => 0,
	), apply_filters( 'give_pdf_receipt_remain_bottom_height_bg', $bg ) );

	$give_pdf->lastPage();

	/**
	 * Fill BG expand based on template.
	 *
	 * Calculate remaining height and fill background color based on template.
	 * Provided filter to change BG color.
	 *
	 * @since 2.2.0
	 */
	$give_pdf->Rect( 0, $last_cell_height, $give_pdf->getPageWidth(), $remain_height, 'F', array(
		'L' => 0,
		'T' => 0,
		'R' => 0,
		'B' => 0,
	), apply_filters( 'give_pdf_receipt_remain_height_bg', $bg ) );

	$pdf_output = ( $pdf_preview ) ? 'I' : 'D';
	$give_pdf->Output( $pdf_receipt_filename, wp_is_mobile() ? 'I' : $pdf_output );

	exit;

}

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Generate a PDF preview.
 *
 * When the admin clicks the "preview" button to view the PDF rendered.
 */
function give_pdf_receipts_template_preview() {

	// Sanity Checks.
	if (
		! isset( $_GET['give_pdf_receipts_action'] )
		|| 'preview_pdf' !== $_GET['give_pdf_receipts_action']
	) {
		return;
	}

	// Admin's only.
	if ( ! is_admin() ) {
		return;
	}

	// Get Form ID if Preview  Per-Form.
	$form_id = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
	if ( ! empty( $form_id ) ) {
		$page_size   = give_get_meta( $form_id, 'give_pdf_builder_page_size', true );
		$page_size   = ! empty( $page_size ) ? $page_size : array( 0, 0, 595.28, 841.89 );
		$template_id = give_get_meta( $form_id, 'give_pdf_receipt_template', true );
		$per_form    = true;
	} else {
		$page_size   = give_get_option( 'give_pdf_builder_page_size', array( 0, 0, 595.28, 841.89 ) );
		$template_id = give_get_option( 'give_pdf_receipt_template' );
		$per_form    = false;
	}

	// Sample PDF.
	$sample_give_pdf_buyer_info = array(
		'address' => array(
			'line1'   => '123 Sample Road',
			'line2'   => 'Apt. 201',
			'city'    => 'San Diego',
			'state'   => 'California',
			'country' => 'US',
			'zip'     => '92101',
		),
	);

	if ( $template_id ) {
		$template         = get_post( $template_id );
		$template_content = ( $per_form ) ? give_get_meta( $form_id, 'give_pdf_builder', true ) : $template->post_content;

		// Set PDF Preview. true for preview it in browser else download it.
		$pdf_preview = true;

		$is_div_layout = false;
		// Check whether content is div based or table based.
		if ( false !== strpos( $template_content, '<div' ) ) {
			$is_div_layout = true;
		}

		// Output w/ dummy data.
		$html = give_pdf_get_compile_html(
			$template_content,
			'',
			'Test Donation',
			__( 'Complete', 'give-pdf-receipts' ),
			'',
			$sample_give_pdf_buyer_info,
			'',
			'ch_190aZ9x90a',
			'http://sample.com/receipt-url-example/',
			$is_div_layout,
			$pdf_preview
		);

		// Use DOMPDF if div based layout set for the Backward compatibility.
		if ( $is_div_layout ) {
			// Include dompdf library.
			include_once( GIVE_PDF_PLUGIN_DIR . 'vendor/autoload.php' );

			$options = new Options();
			$options->set( 'isRemoteEnabled', true );
			$options->set( 'isHtml5ParserEnabled', true );
			$options->set( 'setIsRemoteEnabled', true );

			// Create pdf document
			$dompdf = new Dompdf( $options );

			$page_size = apply_filters( 'give_dompdf_paper_size', $page_size );

			$dompdf->setPaper( $page_size );

			$dompdf->loadHtml( $html );
			$dompdf->render();
			$dompdf->stream(
				apply_filters( 'give_custom_pdf_receipt_filename_prefix', __( 'Receipt-Preview', 'give-pdf-receipts' ) ) . '.pdf',
				array(
					'Attachment' => false,
				)
			);
			die();
		}

	}// End if().

	exit;

}

add_action( 'admin_init', 'give_pdf_receipts_template_preview' );

/**
 * Generate a Set PDF preview.
 *
 * @since 2.1.0
 *
 * When the admin clicks the "Preview Set PDF Template" button to view the PDF rendered.
 */
function give_set_pdf_receipt_template_preview() {

	// Sanity Checks.
	if (
		! isset( $_GET['give_pdf_receipts_action'] )
		|| 'preview_set_pdf_template' !== $_GET['give_pdf_receipts_action']
	) {
		return;
	}

	// Admin's only.
	if ( ! is_admin() ) {
		return;
	}

	$give_options = give_get_settings();

	if ( ! empty( $_GET['form_id'] ) ) {
		$give_options = give_pdf_receipts_per_form_settings( $_GET['form_id'] );
	}

	// Sample PDF.
	$give_pdf_buyer_info = array(
		'first_name' => 'John',
		'last_name'  => 'Doe',
		'address'    => array(
			'line1'   => '123 Sample Road',
			'line2'   => 'Apt. 201',
			'city'    => 'San Diego',
			'state'   => 'California',
			'country' => 'US',
			'zip'     => '92101',
		),
	);

	$give_pdf_payment_meta = array(
		'email' => 'email@hotmail.com',
		'key'   => 'ab45dsedsf54542dfs458edsfs',
	);

	$company_name            = isset( $give_options['give_pdf_company_name'] ) ? $give_options['give_pdf_company_name'] : '';
	$give_pdf_payment_date   = date_i18n( get_option( 'date_format' ), current_time( 'timestamp', 1 ) );
	$give_pdf_payment_status = __( 'Complete', 'give-pdf-receipts' );

	/**
	 * TCPDF Generation Method.
	 *
	 * Generate pdf using legacy TCPDF default template.
	 */
	require_once GIVE_PLUGIN_DIR . '/includes/libraries/tcpdf/tcpdf.php';
	include_once( GIVE_PDF_PLUGIN_DIR . '/includes/class-give-tcpdf.php' );

	$give_pdf = new Give_PDF_Receipt( 'P', 'mm', 'A4', true, 'UTF-8', false );

	$give_pdf->SetDisplayMode( 'real' );
	$give_pdf->setJPEGQuality( 100 );

	$give_pdf->SetTitle( __( 'Example Donation Receipt', 'give-pdf-receipts' ) );
	$give_pdf->SetCreator( __( 'Give', 'give-pdf-receipts' ) );
	$give_pdf->SetAuthor( get_option( 'blogname' ) );

	$address_line_2_line_height = isset( $give_options['give_pdf_address_line2'] ) ? 6 : 0;

	if ( ! isset( $give_options['give_pdf_templates'] ) ) {
		$give_options['give_pdf_templates'] = 'default';
	}

	do_action( 'give_pdf_template_' . $give_options['give_pdf_templates'], $give_pdf, array(), $give_pdf_payment_meta, $give_pdf_buyer_info, 'Test Donation', 'Test Donation', $address_line_2_line_height, $company_name, $give_pdf_payment_date, $give_pdf_payment_status );

	if ( ob_get_length() ) {
		ob_end_clean();
	}

	$give_pdf->Output( apply_filters( 'give_pdf_receipt_filename_prefix', __( 'Example Donation Receipt', 'give-pdf-receipts' ) ) . '.pdf', 'I' );

	exit();

}

add_action( 'admin_init', 'give_set_pdf_receipt_template_preview' );


/**
 * Function to output the "Download Receipt" text.
 *
 * @since 2.1
 *
 * @param bool $right_arrow
 *
 * @return string $output
 */
function give_pdf_receipts_download_pdf_text( $right_arrow = true ) {

	$output = __( 'Download Receipt', 'give-pdf-receipts' );
	if ( $right_arrow ) {
		$output .= ' &raquo;';
	}

	return apply_filters( 'give_pdf_receipt_shortcode_link_text', $output );
}

/**
 * This function will change hex code to rgb color code.
 *
 * @param string $hex hexadecimal Color Code.
 *
 * @since 2.2.0
 *
 * @return array $rgb RGB Color code.
 */
function give_hex_to_rgb( $hex ) {
	$hex      = str_replace( '#', '', $hex );
	$length   = strlen( $hex );
	$rgb['r'] = hexdec( $length == 6 ? substr( $hex, 0, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 0, 1 ), 2 ) : 0 ) );
	$rgb['g'] = hexdec( $length == 6 ? substr( $hex, 2, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 1, 1 ), 2 ) : 0 ) );
	$rgb['b'] = hexdec( $length == 6 ? substr( $hex, 4, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 2, 1 ), 2 ) : 0 ) );

	return $rgb;
}

/**
 * Return RGB Color Code from setting Global or Per-Form.
 *
 * @param int $form_id Form ID.
 *
 * @since 2.2.0
 *
 * @return array $rgb_code_array Color Code.
 */
function give_get_chosen_color( $form_id ) {
	$is_per_form = give_pdf_receipts_is_per_form_customized( $form_id );
	if ( $is_per_form ) {
		$chosen_color_code = give_get_meta( $form_id, 'give_pdf_colorpicker', true );
		$chosen_color_code = ! empty( $chosen_color_code ) ? $chosen_color_code : '#333';
		$rgb_code_array    = give_hex_to_rgb( $chosen_color_code );
	} else {
		// Get Color Picker color.
		$rgb_code_array = give_hex_to_rgb( give_get_option( 'give_pdf_colorpicker', '#333' ) );
	}

	return $rgb_code_array;
}

/**
 * Get Code2000 supported currencies.
 *
 * @since 2.2.0
 *
 * @return array
 */
function give_pdf_receipt_code2000_supported_countries() {
	return apply_filters( 'give_pdf_receipt_code2000_supported_countries', array( 'RIAL', 'RUB', 'IRR' ) );
}

/**
 * Build PDF args.
 *
 * @since 2.2.0
 *
 * @param int $payment_id Payment ID.
 * @param int $form_id Form ID.
 * @param array $give_pdf_payment_meta Payment meta.
 *
 * @return array $pdf_receipt_args
 */
function give_pdf_get_args( $payment_id, $form_id, $give_pdf_payment_meta ) {

	// Set Font and style.
	$font         = apply_filters( 'give_pdf_receipt_default_font', 'Helvetica' );
	$font_size_32 = apply_filters( 'give_pdf_receipt_font_size_32', 32 );
	$font_size_18 = apply_filters( 'give_pdf_receipt_font_size_18', 18 );
	$font_size_16 = apply_filters( 'give_pdf_receipt_font_size_16', 16 );
	$font_size_14 = apply_filters( 'give_pdf_receipt_font_size_14', 14 );
	$font_size_12 = apply_filters( 'give_pdf_receipt_font_size_12', 12 );
	$font_size_10 = apply_filters( 'give_pdf_receipt_font_size_10', 10 );

	// Get RGB Color Code from the Settings.
	$rgb_code_array = give_get_chosen_color( $form_id );

	// Get PDF Receipts Settings based on Global or Per-Form.
	$give_options = give_get_pdf_receipts_all_settings( $form_id );

	$currency_font = empty( $give_options['give_pdf_enable_char_support'] ) ? $font : 'DejaVuSans';
	$currency_code = give_get_payment_currency_code( $payment_id );

	// Set 'CODE2000' font for Iranian Rial and Russian Rubble country for support currency sign.
	$currency_font_style = '';
	if ( in_array( give_get_currency(), give_pdf_receipt_code2000_supported_countries() )
	     || in_array( $currency_code, give_pdf_receipt_code2000_supported_countries() )
	) {
		$currency_font       = 'CODE2000';
		$currency_font_style = 'B';
	}

	// Set Donation Name and Donation Amount.
	$payment_amount = ( ! empty( $payment_id ) && 123456789 !== $payment_id ) ? give_donation_amount( $payment_id ) : 25.00;
	$total          = give_currency_filter(
		give_format_amount( $payment_amount, array(
			'currency' => $currency_code,
		) ),
		array( 'currency_code' => $currency_code, 'decode_currency' => true )
	);

	$give_pdf_title  = get_the_title( $form_id );
	$give_form_title = ! empty( $give_pdf_title ) ? $give_pdf_title : apply_filters( 'give_pdf_default_form_title', __( 'Example Donation Form Title', 'give-pdf-receipts' ) );
	$give_form_title = html_entity_decode( $give_form_title, ENT_COMPAT, 'UTF-8' );

	// If multi-level, Custom amount, append to the form title.
	if ( give_has_variable_prices( $form_id ) ) {

		$price_id = isset( $give_pdf_payment_meta['price_id'] ) ? $give_pdf_payment_meta['price_id'] : null;

		if ( 'custom' === $price_id ) {
			$custom_amount_text = give_get_meta( $form_id, '_give_custom_amount_text', true );
			$custom_amount_text = ! empty( $custom_amount_text ) ? $custom_amount_text : __( 'Custom Amount', 'give-pdf-receipts' );
			$give_form_title    .= ' - ' . $custom_amount_text;
		} else {
			$give_form_title .= ' - ' . give_get_price_option_name( $form_id, $price_id, $payment_id );
		}

	}

	// Fee Recovery support.
	$fee_amount = give_get_meta( $payment_id, '_give_fee_amount', true );
	$fee        = give_currency_filter(
		give_format_amount( $fee_amount, array(
			'currency' => $currency_code,
		) ),
		array( 'currency_code' => $currency_code, 'decode_currency' => true )
	);

	$amount = give_donation_amount( $payment_id );
	// Subtract Fee amount from donation amount.
	if ( ! empty( $fee_amount ) ) {
		$amount -= $fee_amount;
	}

	$donation_amount = give_currency_filter(
		give_format_amount( $amount, array(
			'currency' => $currency_code,
		) ),
		array( 'currency_code' => $currency_code, 'decode_currency' => true )
	);

	$fee_recovery_support = false;
	if ( ! empty( $fee_amount ) ) {
		$fee_recovery_support = true;
	}

	$pdf_receipt_args                         = array();
	$pdf_receipt_args['font']                 = $font;
	$pdf_receipt_args['font_size_32']         = $font_size_32;
	$pdf_receipt_args['font_size_18']         = $font_size_18;
	$pdf_receipt_args['font_size_16']         = $font_size_16;
	$pdf_receipt_args['font_size_14']         = $font_size_14;
	$pdf_receipt_args['font_size_12']         = $font_size_12;
	$pdf_receipt_args['font_size_10']         = $font_size_10;
	$pdf_receipt_args['rgb_code_array']       = $rgb_code_array;
	$pdf_receipt_args['currency_font']        = $currency_font;
	$pdf_receipt_args['currency_font_style']  = $currency_font_style;
	$pdf_receipt_args['total']                = $total;
	$pdf_receipt_args['give_form_title']      = $give_form_title;
	$pdf_receipt_args['fee']                  = $fee;
	$pdf_receipt_args['donation_amount']      = $donation_amount;
	$pdf_receipt_args['fee_recovery_support'] = $fee_recovery_support;
	$pdf_receipt_args['give_options']         = $give_options;

	return $pdf_receipt_args;

}

/**
 * Format the billing address for PDF receipts.
 *
 * @since 2.2.1
 *
 * @param array $address
 *
 * @return string
 */
function give_pdf_get_formatted_billing_address( $address ) {

	if ( empty( $address ) ) {
		return '';
	}

	$formatted_address = '';

	// Line 1.
	if ( isset( $address['line1'] ) && ! empty( $address['line1'] ) ) {
		$formatted_address .= $address['line1'];
	}

	// Line 2.
	if ( isset( $address['line2'] ) && ! empty( $address['line2'] ) ) {
		$formatted_address .= ' <br/>' . $address['line2'];
	}

	// City.
	if ( isset( $address['city'] ) && ! empty( $address['city'] ) ) {
		$formatted_address .= ' <br/>' . $address['city'];

		// State.
		if ( isset( $address['state'] ) && ! empty( $address['state'] ) ) {
			$formatted_address .= ', ' . $address['state'];
		}

		// Zip.
		if ( isset( $address['zip'] ) && ! empty( $address['zip'] ) ) {
			$formatted_address .= ', ' . $address['zip'];
		}

	}

	// Country.
	if ( isset( $address['country'] ) && ! empty( $address['country'] ) ) {
		$countries         = give_get_country_list();
		$country           = isset( $countries[ $address['country'] ] ) ? $countries[ $address['country'] ] : $address['country'];
		$formatted_address .= ' <br/>' . $country;
	}

	return apply_filters( 'give_pdf_get_formatted_billing_address', $formatted_address, $address );

}