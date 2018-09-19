<?php
/**
 * Scripts
 *
 * @description Registers js scripts and css styles
 *
 * @package     Give PDF Receipts
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load scripts
 *
 * @param string $hook Hook name.
 */
function give_pdf_receipts_load_admin_scripts( $hook ) {

	// Give Admin Only.
	if ( ! apply_filters( 'give_load_admin_scripts', give_is_admin_page(), $hook ) ) {
		return;
	}

	// Override TinyMCE CSS only on General settings in PDF Receipt.
	add_filter( 'mce_css', 'give_pdf_receipts_filter_mce_css' );

	// CSS.
	wp_register_style( 'give_admin_pdf_receipt_css', GIVE_PDF_PLUGIN_URL . 'assets/css/admin-style.css', false, GIVE_PDF_PLUGIN_VERSION );
	wp_enqueue_style( 'give_admin_pdf_receipt_css' );

	// JS.
	wp_register_script( 'give_admin_pdf_receipt_js', GIVE_PDF_PLUGIN_URL . 'assets/js/admin-forms.js', array( 'jquery' ), GIVE_PDF_PLUGIN_VERSION, false );

	wp_enqueue_script( 'give_admin_pdf_receipt_js' );

	// Localize the script with new data.
	$ajax_data = array(
		'receipt_name_placeholder' => get_bloginfo( 'name' ) . ' ' . __( 'Donation Receipt', 'give-pdf-receipts' ),
		'not_saved'                => __( 'You haven\'t saved the template yet. Are you sure you want to proceed?', 'give-pdf-receipts' ),
		'confirm_delete'           => __( 'Are you sure you want to delete this receipt template?', 'give-pdf-receipts' ),
		'template_customized'      => __( 'Please provide a unique receipt template name for this PDF receipt in order to apply your changes.', 'give-pdf-receipts' ),
	);
	wp_localize_script( 'give_admin_pdf_receipt_js', 'give_pdf_vars', $ajax_data );


}

add_action( 'admin_enqueue_scripts', 'give_pdf_receipts_load_admin_scripts' );

/**
 * Add CSS for TinyMCE.
 *
 * @since 2.1
 *
 * @param $mce_css
 *
 * @return string
 */
function give_pdf_receipts_filter_mce_css( $mce_css ) {

	$mce_css .= ', ' . GIVE_PDF_PLUGIN_URL . 'assets/css/admin-pdf-tinymce.css?version=' . GIVE_PDF_PLUGIN_VERSION;

	return $mce_css;

}