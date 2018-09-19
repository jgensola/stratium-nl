<?php
/**
 * PDF Receipt Class
 *
 * Extends the TCPDF class to add the extra functionality for the PDF Receipts
 *
 * @since   1.0
 * @package Give - PDF Receipts
 */

/**
 * Give_PDF_Receipt Class
 */
class Give_PDF_Receipt extends TCPDF {

	/**
	 * Header
	 *
	 * Outputs the header message configured in the Settings on all the receipts
	 * as well as display the background images on certain templates
	 *
	 * @since 2.0
	 */
	public function Header() {

		global $give_options;
		$transaction_id = ! empty( $_GET['payment_id'] ) ? $_GET['payment_id'] : 0;
		$form_id        = give_get_payment_form_id( $transaction_id );
		$form_id        = ! empty( $form_id ) ? $form_id : 0;
		$font           = 'helvetica';

		// Get Form ID if Preview  Per-Form.
		if ( ! empty( $_GET['form_id'] ) ) {
			$form_id = absint( $_GET['form_id'] );
		}

		// Get PDF Receipts Settings based on Global or Per-Form.
		$give_options = give_get_pdf_receipts_all_settings( $form_id );

		// Set 'DejaVuSans' for not supported font.
		$currency_font = empty( $give_options['give_pdf_enable_char_support'] ) ? $font : 'DejaVuSans';

		// Get RGB Color Code from the Settings.
		$rgb_code_array = give_get_chosen_color( $form_id );

		$this->SetTextColor( 50, 50, 50 );

		if ( isset( $give_options['give_pdf_templates'] ) && 'blue_stripe' === $give_options['give_pdf_templates'] ) {

			$this->Rect( 0, 0, $this->getPageWidth(), 15, 'F', array(
				'L' => 0,
				'T' => 0,
				'R' => 0,
				'B' => 0,
			), array( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] ) );

		} elseif ( isset( $give_options['give_pdf_templates'] ) && 'lines' === $give_options['give_pdf_templates'] ) {

			// Left Side.
			$this->Rect( 0, 0, 5, 297, 'F', array(
				'L' => 0,
				'T' => 0,
				'R' => 0,
				'B' => 0,
			), array( 225, 225, 225 ) );

			$this->Rect( 6, 0, 5, 297, 'F', array(
				'L' => 0,
				'T' => 0,
				'R' => 0,
				'B' => 0,
			), array( 232, 230, 217 ) );

			$this->Rect( 12, 0, 1, 297, 'F', array(
				'L' => 0,
				'T' => 0,
				'R' => 0,
				'B' => 0,
			), array( 225, 225, 225 ) );

			// Right Side.
			$this->Rect( 205, 0, 5, 297, 'F', array(
				'L' => 0,
				'T' => 0,
				'R' => 0,
				'B' => 0,
			), array( 225, 225, 225 ) );

			$this->Rect( 199, 0, 5, 297, 'F', array(
				'L' => 0,
				'T' => 0,
				'R' => 0,
				'B' => 0,
			), array( 232, 230, 217 ) );

			$this->Rect( 197, 0, 1, 297, 'F', array(
				'L' => 0,
				'T' => 0,
				'R' => 0,
				'B' => 0,
			), array( 225, 225, 225 ) );
		} // End if().

		if ( 'blue_stripe' === $give_options['give_pdf_templates'] ) {
			$this->AddFont( 'helvetica', 'B' );
			$this->SetTextColor( 255, 255, 255 );
			$this->SetFont( $currency_font, 'B', 10 );
		} elseif ( 'lines' === $give_options['give_pdf_templates'] ) {
			$this->AddFont( 'helvetica', 'I' );
			$this->SetFont( $currency_font, 'I', 8 );
		} elseif ( 'minimal' === $give_options['give_pdf_templates'] ) {
			$this->AddFont( 'helvetica', 'B' );
			$this->SetFont( $currency_font, 'B', 10 );
		} elseif ( 'traditional' === $give_options['give_pdf_templates'] ) {
			$this->AddFont( 'helvetica', 'I' );
			$this->SetFont( $currency_font, 'I', 8 );
		} else {
			$this->AddFont( 'helvetica', 'I' );
			$this->SetFont( $currency_font, 'I', 8 );
		} // End if().

		$transaction_id = ! empty( $_GET['payment_id'] ) ? $_GET['payment_id'] : '';
		if ( isset( $give_options['give_pdf_header_message'] ) ) {
			$give_pdf_payment      = get_post( $transaction_id );
			$give_pdf_payment_date = ! empty( $give_pdf_payment->post_date ) ? strtotime( $give_pdf_payment->post_date ) : current_time( 'timestamp', 1 );
			$receipt_id            = ! empty( $give_pdf_payment->ID ) ? $give_pdf_payment->ID : '123456789';

			$give_pdf_header = isset( $give_options['give_pdf_header_message'] ) ? $give_options['give_pdf_header_message'] : '';
			$give_pdf_header = str_replace( '{page}', 'Page ' . $this->PageNo(), $give_pdf_header );
			$give_pdf_header = str_replace( '{sitename}', get_bloginfo( 'name' ), $give_pdf_header );
			$give_pdf_header = str_replace( '{today}', date_i18n( get_option( 'date_format' ), time() ), $give_pdf_header );
			$give_pdf_header = str_replace( '{date}', date_i18n( get_option( 'date_format' ), strtotime( $give_pdf_payment_date ) ), $give_pdf_header );
			$give_pdf_header = str_replace( '{receipt_id}', $receipt_id, $give_pdf_header );

			$this->Cell( 0, 15, stripslashes_deep( html_entity_decode( $give_pdf_header, ENT_COMPAT, 'UTF-8' ) ), 0, 0, 'C' );
		} // End if().

	} // end Header

	/**
	 * Footer
	 *
	 * Outputs the footer message configured in the Settings on all the receipts
	 *
	 * @since 2.0
	 */
	public function Footer() {
		$transaction_id = ! empty( $_GET['payment_id'] ) ? $_GET['payment_id'] : 0;
		$form_id        = give_get_payment_form_id( $transaction_id );
		$form_id        = ! empty( $form_id ) ? $form_id : 0;
		$font           = 'helvetica';

		// Get Form ID if Preview  Per-Form.
		if ( ! empty( $_GET['form_id'] ) ) {
			$form_id = absint( $_GET['form_id'] );
		}

		// Get RGB Color Code from the Settings.
		$rgb_code_array = give_get_chosen_color( $form_id );

		// Get PDF Receipts Settings based on Global or Per-Form.
		$give_options = give_get_pdf_receipts_all_settings( $form_id );
		$currency_font = empty( $give_options['give_pdf_enable_char_support'] ) ? $font : 'DejaVuSans';

		$this->SetTextColor( 50, 50, 50 );

		if ( 'lines' === $give_options['give_pdf_templates'] ) {
			$this->SetFont( $currency_font, 'I', 8 );
		} elseif ( 'blue_stripe' === $give_options['give_pdf_templates'] ) {
			$this->SetFont( $currency_font, 'B', 10 );
			$this->SetTextColor( 255, 255, 255 );
			$this->Rect( 0, $this->getPageHeight() - 15, $this->getPageWidth(), 15, 'F', array(
				'L' => 0,
				'T' => 0,
				'R' => 0,
				'B' => 0,
			), array( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] ) );

		} elseif ( 'minimal' === $give_options['give_pdf_templates'] ) {
			$this->AddFont( 'helvetica', 'B' );
			$this->SetFont( $currency_font, 'B', 10 );
		} elseif ( 'traditional' === $give_options['give_pdf_templates'] ) {
			$this->AddFont( 'helvetica', 'I' );
			$this->SetFont( $currency_font, 'I', 8 );
		} else {
			$this->SetFont( $currency_font, 'I', 8 );
		} // End if().

		if ( isset( $give_options['give_pdf_footer_message'] ) ) {
			$transaction_id        = ! empty( $_GET['payment_id'] ) ? $_GET['payment_id'] : '';
			$give_pdf_payment      = get_post( $transaction_id );
			$give_pdf_payment_date = ! empty( $give_pdf_payment->post_date ) ? strtotime( $give_pdf_payment->post_date ) : current_time( 'timestamp', 1 );
			$receipt_id            = ! empty( $give_pdf_payment->ID ) ? $give_pdf_payment->ID : '123456789';
			$give_pdf_footer       = isset( $give_options['give_pdf_footer_message'] ) ? $give_options['give_pdf_footer_message'] : '';
			$give_pdf_footer       = str_replace( '{page}', 'Page ' . $this->PageNo(), $give_pdf_footer );
			$give_pdf_footer       = str_replace( '{sitename}', get_bloginfo( 'name' ), $give_pdf_footer );
			$give_pdf_footer       = str_replace( '{today}', date( get_option( 'date_format' ), time() ), $give_pdf_footer );
			$give_pdf_footer       = str_replace( '{date}', date( get_option( 'date_format' ), strtotime( $give_pdf_payment_date ) ), $give_pdf_footer );
			$give_pdf_footer       = str_replace( '{receipt_id}', $receipt_id, $give_pdf_footer );
			$this->setXY( 10, $this->getPageHeight() - 15 );
			$this->Cell( 0, 15, stripslashes_deep( html_entity_decode( $give_pdf_footer, ENT_COMPAT, 'UTF-8' ) ), 0, 0, 'C' );
		} // End if().

	} // End Footer().

}
