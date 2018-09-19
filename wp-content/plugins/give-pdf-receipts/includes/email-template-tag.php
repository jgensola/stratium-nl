<?php
/**
 * Template Tags
 *
 * Creates and renders the additional template tags for the PDF receipt.
 *
 * @package Give - PDF Receipts
 * @since   1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register PDF Receipt Email tag.
 */
function give_pdf_register_email_tag() {
	give_add_email_tag(
		'pdf_receipt',
		__( 'Creates a link to a downloadable PDF receipt', 'give-pdf-receipts' ),
		'give_pdf_email_template_tags'
	);
}

add_action( 'give_add_email_tags', 'give_pdf_register_email_tag' );

/**
 *
 * Email Template Tags
 *
 * Additional template tags for the Donation Receipt.
 *
 * @since       1.0
 * @uses        give_pdf_receipts()->engine->get_pdf_receipt_url()
 *
 * @param array $tag_args
 *
 * @return bool|string Receipt Link.
 */
function give_pdf_email_template_tags( $tag_args ) {
	// Backward compatibility: we can still render email tag with  payment id.
	if( ! is_array( $tag_args ) ) {
		$tag_args = array( 'payment_id' => absint( $tag_args ) );
	}

	if ( ! give_pdf_receipts()->engine->is_receipt_link_allowed( $tag_args['payment_id'] ) || give_pdf_receipts_disable( $tag_args['payment_id'] ) ) {
		return false;
	}

	return sprintf(
		'<a href="%1$s">%2$s</a>',
		give_pdf_receipts()->engine->get_pdf_receipt_url( $tag_args['payment_id'] ),
		give_pdf_receipts_download_pdf_text()
	);
}

/**
 * Set Backward compatibility for PDF Receipt email template.
 * It will set Default Email template if any customer has chosen PDF Receipt Email Template.
 *
 * @param mixed $template Template name.
 *
 * @since 2.2.0
 *
 * @return string $template Return chosen template.
 */
function give_pdf_email_template( $template ) {
	$email_templates = array(
		'receipt_default',
		'blue_stripe',
		'lines',
		'minimal',
		'traditional',
		'receipt_blue',
		'receipt_green',
		'receipt_orange',
		'receipt_pink',
		'receipt_purple',
		'receipt_red',
		'receipt_yellow',
	);
	if ( in_array( $template, $email_templates, true ) ) {
		$template = 'default';
	}

	return $template;
}

add_filter( 'give_email_template', 'give_pdf_email_template', 10, 1 );