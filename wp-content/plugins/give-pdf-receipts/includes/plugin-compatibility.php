<?php
/**
 * Plugin Compatibility
 *
 * Provides compatibility with plugins conflicting with PDF generation and output.
 *
 * @package Give - PDF Receipts
 * @since   2.0.1
 */


/**
 * Prevent Autoptimize from Caching Generated PDFs
 *
 * @see: https://github.com/WordImpress/Give-PDF-Receipts/issues/35
 *
 * @return bool
 */
function give_pdfs_ao_noptimize() {

	if ( strpos( $_SERVER['REQUEST_URI'], 'give_pdf_receipts_action' ) !== false ) {
		return true;
	} else {
		return false;
	}

}

add_filter( 'autoptimize_filter_noptimize', 'give_pdfs_ao_noptimize', 10, - 2 );
