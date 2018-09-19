<?php
/**
 * Traditional PDF Receipt Template
 *
 * Builds and renders the traditional PDF receipt template .
 *
 * @since 1.0
 *
 * @uses  HTML2PDF
 * @uses  TCPDF
 *
 * @param object $give_pdf                 PDF Receipt Object
 * @param object $give_pdf_payment         Payment Data Object
 * @param array  $give_pdf_payment_meta    Payment Meta
 * @param array  $give_pdf_buyer_info      Buyer Info
 * @param string $give_pdf_payment_gateway Payment Gateway
 * @param string $give_pdf_payment_method  Payment Method
 * @param string $company_name             Company Name
 * @param string $give_pdf_payment_date    Payment Date
 * @param        string                    give_pdf_payment_status Payment Status
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Traditional PDF Receipt Template.
 *
 * Builds and renders the blue stripe PDF receipt template.
 *
 * @since 1.0
 *
 * @uses  HTML2PDF
 * @uses  TCPDF
 *
 * @param \Give_PDF_Receipt $give_pdf                 PDF Receipt Object
 * @param object            $give_pdf_payment         Payment Data Object
 * @param array             $give_pdf_payment_meta    Payment Meta
 * @param array             $give_pdf_buyer_info      Buyer Info
 * @param string            $give_pdf_payment_gateway Payment Gateway
 * @param string            $give_pdf_payment_method  Payment Method
 * @param string            $company_name             Company Name
 * @param string            $give_pdf_payment_date    Payment Date
 * @param string            $give_pdf_payment_status  Payment Status
 */
function give_pdf_template_traditional( $give_pdf, $give_pdf_payment, $give_pdf_payment_meta, $give_pdf_buyer_info, $give_pdf_payment_gateway, $give_pdf_payment_method, $address_line_2_line_height, $company_name, $give_pdf_payment_date, $give_pdf_payment_status ) {
	$form_id    = ! empty( $give_pdf_payment_meta['form_id'] ) ? $give_pdf_payment_meta['form_id'] : 0;
	$payment_id = ! empty( $give_pdf_payment->ID ) ? $give_pdf_payment->ID : 123456789;

	// Get Form ID if Preview Per-Form.
	if ( ! empty( $_GET['form_id'] ) ) {
		$form_id = absint( $_GET['form_id'] );
	}

	$pdf_receipt_args     = give_pdf_get_args( $payment_id, $form_id, $give_pdf_payment_meta );
	$font                 = $pdf_receipt_args['font'];
	$font_size_32         = $pdf_receipt_args['font_size_32'];
	$font_size_18         = $pdf_receipt_args['font_size_18'];
	$font_size_16         = $pdf_receipt_args['font_size_16'];
	$font_size_14         = $pdf_receipt_args['font_size_14'];
	$font_size_12         = $pdf_receipt_args['font_size_12'];
	$font_size_10         = $pdf_receipt_args['font_size_10'];
	$rgb_code_array       = $pdf_receipt_args['rgb_code_array'];
	$currency_font        = $pdf_receipt_args['currency_font'];
	$currency_font_style  = $pdf_receipt_args['currency_font_style'];
	$total                = $pdf_receipt_args['total'];
	$give_form_title      = $pdf_receipt_args['give_form_title'];
	$fee                  = $pdf_receipt_args['fee'];
	$donation_amount      = $pdf_receipt_args['donation_amount'];
	$fee_recovery_support = $pdf_receipt_args['fee_recovery_support'];
	$give_options         = $pdf_receipt_args['give_options'];

	// Set Donation Receipt.
	$give_pdf->AddPage();
	$give_pdf->SetFont( $font, 'B', $font_size_18 );
	$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
	$give_pdf->SetXY( 12, 30 );
	$give_pdf->Cell( 0, 60, apply_filters( 'give_pdf_receipt_heading', __( 'Donation Receipt', 'give-pdf-receipts' ) ), 0, 0, 'C', false );

	if ( isset( $give_options['give_pdf_logo_upload'] ) && ! empty( $give_options['give_pdf_logo_upload'] ) ) {
		$give_pdf->setImageScale( 2.25 );
		$give_pdf->Image( $give_options['give_pdf_logo_upload'], '', 20, '', '30', '', false, 'C', true, 300, 'C', false, false, 0, false, false, false );
	} else {
		$give_pdf->SetFont( $currency_font, 'B', $font_size_32 );
		$give_pdf->SetXY( 10, 31 );
		$give_pdf->Cell( 0, 0, $company_name, 0, 0, 'C', false );
	}

	// Get Form title count.
	$word_count = absint( $give_pdf->GetStringWidth( $give_form_title, $currency_font, '', $font_size_14 ) );

	$rect_bottom_height = 22.5;
	$note_title_y       = 191;
	$note_title_val_y   = 204;
	if ( ! $fee_recovery_support ) {
		if ( $word_count > 1 && $word_count <= 96 ) {
			// row 1.
			$rect_bottom_height = 22.5;
			$note_title_y       = 191;
			$note_title_val_y   = 204;
		} else if ( $word_count > 97 && $word_count <= 188 ) {
			// row 2.
			$rect_bottom_height = 30;
			$note_title_y       = 198;
			$note_title_val_y   = 210;
		} else if ( $word_count > 183 && $word_count <= 282 ) {
			// row 3.
			$rect_bottom_height = 35;
			$note_title_y       = 202;
			$note_title_val_y   = 214;
		} else if ( $word_count > 283 ) {
			// row 4.
			$rect_bottom_height = 42;
			$note_title_y       = 210;
			$note_title_val_y   = 222;
		}
	} else {
		if ( $word_count > 1 && $word_count <= 56 ) {
			// row 1.
			$rect_bottom_height = 22.5;
			$note_title_y       = 191;
			$note_title_val_y   = 204;
		} else if ( $word_count > 57 && $word_count <= 115 ) {
			// row 2.
			$rect_bottom_height = 30;
			$note_title_y       = 198;
			$note_title_val_y   = 210;
		} else if ( $word_count > 116 && $word_count <= 172 ) {
			// row 3.
			$rect_bottom_height = 35;
			$note_title_y       = 202;
			$note_title_val_y   = 214;
		} else if ( $word_count > 173 ) {
			// row 4.
			$rect_bottom_height = 42;
			$note_title_y       = 210;
			$note_title_val_y   = 222;
		}
	}

	/*** Start display Donor and Organization Information ***/
	$give_pdf->SetDrawColor( 219, 219, 219 );
	// Big Rectangle.
	$give_pdf->Rect( 15, 110, 180, 55, 'F', array(
		'L' => 1,
		'T' => 1,
		'R' => 1,
		'B' => 1,
	), apply_filters( 'give_pdf_traditional_main_box_color', array( 249, 249, 249 ) ) );

	// Bottom Rectangle.
	$give_pdf->Rect( 15, 165, 180, apply_filters( 'give_pdf_rect_bottom_height', $rect_bottom_height ), 'F', array(
		'L' => 1,
		'T' => 1,
		'R' => 1,
		'B' => 1,
	), apply_filters( 'give_pdf_minimal_bottom_box_color', array( 241, 241, 241 ) ) );

	/*** Start Other details like Donation Date ***/
	// Set Donation Date.
	$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
	$give_pdf->SetXY( 20, 114 );
	$give_pdf->SetFont( $font, 'B', $font_size_12 );
	$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_donation_date_title', __( 'Donation Date', 'give-pdf-receipts' ) ), '', 0, '', false );

	$give_pdf->SetXY( 20, 121 );
	$give_pdf->SetFont( $currency_font, '', $font_size_10 );
	$give_pdf->SetTextColor( 50, 50, 50 );
	$give_pdf->Cell( 0, 0, $give_pdf_payment_date, 0, 2, '', false );

	// Set Payment Method.
	$give_pdf->SetXY( 20, 131 );
	$give_pdf->SetFont( $font, 'B', $font_size_12 );
	$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
	$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_payment_method_title', __( 'Payment Method', 'give-pdf-receipts' ) ), '', 0, '', false );

	// Set Payment Method Value.
	$give_pdf->SetXY( 20, 138 );
	$give_pdf->SetFont( $currency_font, '', $font_size_10 );
	$give_pdf->SetTextColor( 50, 50, 50 );
	$give_pdf->Cell( 0, 0, $give_pdf_payment_method, 0, 2, '', false );

	// Set Payment ID.
	$give_pdf->SetXY( 75, 114 );
	$give_pdf->SetFont( $font, 'B', $font_size_12 );
	$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
	$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_payment_id_title', __( 'Payment ID', 'give-pdf-receipts' ) ), '', 0, '', false );

	// Set Payment ID Value.
	$give_pdf->SetXY( 75, 121 );
	$give_pdf->SetFont( $currency_font, '', $font_size_10 );
	$give_pdf->SetTextColor( 50, 50, 50 );
	$give_pdf->Cell( 0, 0, ! empty( $give_pdf_payment->ID ) ? give_pdf_get_payment_number( $give_pdf_payment->ID ) : '123456789', 0, 2, 'L', false );

	// Set Donation Status.
	$give_pdf->SetXY( 75, 131 );
	$give_pdf->SetFont( $font, 'B', $font_size_12 );
	$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
	$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_donation_status_title', __( 'Donation Status', 'give-pdf-receipts' ) ), '', 0, '', false );

	// Set Donation Status Value.
	$give_pdf->SetXY( 75, 138 );
	$give_pdf->SetFont( $currency_font, '', $font_size_10 );
	$give_pdf->SetTextColor( 50, 50, 50 );
	$give_pdf->Cell( 0, 0, $give_pdf_payment_status, 0, 2, '', false );
	/*** End Other details like Donation Date ***/

	/*** Start set Organization Information ***/
	$give_pdf->SetFont( $currency_font, '', $font_size_10 );
	$give_pdf->SetXY( 10, 70 );
	$give_pdf->SetTextColor( 50, 50, 50 );
	if ( ! empty( $company_name ) ) {
		$give_pdf->Cell( 0, 6, $company_name, 0, 2, 'C', false );
	}
	if ( ! empty( $give_options['give_pdf_address_line1'] ) ) {
		$line_count = $give_pdf->getNumLines( give_pdf_get_settings( $give_pdf, 'addr_line1' ), 20 );
		$give_pdf->Cell( 0, $line_count, give_pdf_get_settings( $give_pdf, 'addr_line1' ), 0, 2, 'C', false );
	}
	if ( ! empty( $give_options['give_pdf_address_line2'] ) ) {
		$give_pdf->Cell( 0, give_pdf_calculate_line_height( $give_options['give_pdf_address_line2'] ), give_pdf_get_settings( $give_pdf, 'addr_line2' ), 0, 2, 'C', false );
	}
	if ( ! empty( $give_options['give_pdf_address_city_state_zip'] ) ) {
		$give_pdf->Cell( 0, give_pdf_calculate_line_height( $give_options['give_pdf_address_city_state_zip'] ), give_pdf_get_settings( $give_pdf, 'city_state_zip' ), 0, 2, 'C', false );
	}

	$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
	if ( ! empty( $give_options['give_pdf_email_address'] ) ) {

		$give_pdf->Cell( 0, give_pdf_calculate_line_height( $give_options['give_pdf_email_address'] ), give_pdf_get_settings( $give_pdf, 'email' ), 0, 2, 'C', false );
	}
	if ( isset( $give_options['give_pdf_url'] ) && $give_options['give_pdf_url'] ) {
		$give_pdf->Cell( 0, 6, get_option( 'siteurl' ), 0, 2, 'C', false );
	}
	/*** End set Organization Information ***/

	/*** Start Donor information. ***/
	$give_pdf->SetXY( 120, 114 );
	$give_pdf->SetFont( $font, 'B', $font_size_12 );

	$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
	$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_donor_title', __( 'Donor', 'give-pdf-receipts' ) ), '', 2, 'L', false );

	$give_pdf->SetXY( 120, 121 );
	$give_pdf->SetFont( $currency_font, '', $font_size_10 );
	$give_pdf->SetTextColor( 50, 50, 50 );
	$give_pdf->Cell( 0, give_pdf_calculate_line_height( $give_pdf_buyer_info['first_name'] ), $give_pdf_buyer_info['first_name'] . ' ' . $give_pdf_buyer_info['last_name'], 0, 2, 'L', false );
	$give_pdf->Cell( 0, 6, $give_pdf_payment_meta['email'], 0, 2, 'L', false );

	if ( ! empty( $give_pdf_buyer_info['address'] ) ) {

		if ( ! empty( $give_pdf_buyer_info['address']['line1'] ) ) {
			$give_pdf->Cell( 0, 6, $give_pdf_buyer_info['address']['line1'], 0, 2, 'L', false );
		}

		if ( ! empty( $give_pdf_buyer_info['address']['line2'] ) ) {
			$give_pdf->Cell( 0, 6, $give_pdf_buyer_info['address']['line2'], 0, 2, 'L', false );
		}

		if ( ! empty( $give_pdf_buyer_info['address']['city'] )
		     || ! empty( $give_pdf_buyer_info['address']['state'] )
		     || ! empty( $give_pdf_buyer_info['address']['zip'] )
		) {
			$give_pdf->Cell( 0, 6, $give_pdf_buyer_info['address']['city'] . ' ' . $give_pdf_buyer_info['address']['state'] . ' ' . $give_pdf_buyer_info['address']['zip'], 0, 2, 'L', false );
		}

		if ( ! empty( $give_pdf_buyer_info['address']['country'] ) ) {
			$countries = give_get_country_list();
			$country   = isset( $countries[ $give_pdf_buyer_info['address']['country'] ] ) ? $countries[ $give_pdf_buyer_info['address']['country'] ] : $give_pdf_buyer_info['address']['country'];
			$give_pdf->Cell( 0, 6, $country, 0, 2, 'L', false );
		}
	}
	/*** End Donor information. ***/

	// For Fee Recovery Support.
	if ( $fee_recovery_support ) {
		// Set Donation Name.
		$give_pdf->SetXY( 20, 169 );
		$give_pdf->SetFont( $font, 'B', $font_size_12 );
		$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
		$give_pdf->Cell( 102, 0, apply_filters( 'give_pdf_donation_name_title', __( 'Donation Name', 'give-pdf-receipts' ) ), '', '', 'L', false );

		// Set Donation.
		$give_pdf->SetXY( 80, 169 );
		$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_donation_donation_title', __( 'Donation', 'give-pdf-receipts' ) ), '', '', 'L', false );

		// Set Donation Fee.
		$give_pdf->SetXY( 120, 169 );
		$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_donation_fee_title', __( 'Fee', 'give-pdf-receipts' ) ), '', '', 'L', false );

		// Set Donation Total.
		$give_pdf->SetXY( 150, 169 );
		$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_donation_total_title', __( 'Total', 'give-pdf-receipts' ) ), '', '', 'L', false );

		// Set Donation Name Title.
		$give_pdf->SetTextColor( 50, 50, 50 );
		$give_pdf->SetFont( $currency_font, $currency_font_style, $font_size_14 );
		$give_pdf->MultiCell( 60, 6, $give_form_title, '0', '', false, 0, 20, 177, false, 0, false, true, apply_filters( 'give_pdf_form_title_max_height', 32 ), '', false );

		// Set Donation amount.
		$give_pdf->SetXY( 80, 177 );
		$give_pdf->Cell( 0, 0, $donation_amount, '', '', 'L', 0 );

		// Set Donation Fee.
		$give_pdf->SetXY( 120, 177 );
		$give_pdf->Cell( 0, 0, $fee, '', '', 'L', 0 );

		// Set Donation Total.
		$give_pdf->SetXY( 150, 177 );
		$give_pdf->SetFont( $currency_font, $currency_font_style, $font_size_16 );
		$give_pdf->Cell( 0, 0, $total, '', '', 'L', 0 );
	} else {
		// Set Donation Name.
		$give_pdf->SetXY( 20, 169 );
		$give_pdf->SetFont( $font, 'B', $font_size_12 );
		$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
		$give_pdf->Cell( 102, 0, apply_filters( 'give_pdf_donation_name_title', __( 'Donation Name', 'give-pdf-receipts' ) ), '', '', 'L', false );

		// Set Donation amount.
		$give_pdf->SetXY( 120, 169 );
		$give_pdf->Cell( 0, 0, apply_filters( 'give_pdf_donation_amount_title', __( 'Donation Amount', 'give-pdf-receipts' ) ), '', '', 'L', false );

		$give_pdf->SetTextColor( 50, 50, 50 );
		$give_pdf->SetFont( $currency_font, $currency_font_style, $font_size_14 );
		$give_pdf->MultiCell( 100, 7, $give_form_title, '0', '', false, 0, 20, 177, true, 0, false, true, apply_filters( 'give_pdf_form_title_max_height', 32 ), 'L', false );

		$give_pdf->SetXY( 120, 177 );
		$give_pdf->Cell( 0, 0, $total, '', '', 'L', 0 );
	}

	/*** Start Additional Notes Section ***/
	if ( isset( $give_options['give_pdf_additional_notes'] ) && ! empty( $give_options['give_pdf_additional_notes'] ) ) {
		$give_pdf->SetTextColor( $rgb_code_array['r'], $rgb_code_array['g'], $rgb_code_array['b'] );
		$give_pdf->SetXY( 14, apply_filters( 'give_pdf_note_title_position', $note_title_y ) );
		$give_pdf->SetFont( $font, 'B', $font_size_12 );
		$give_pdf->Cell( 0, 12, apply_filters( 'give_pdf_additional_notes_title', __( 'Additional Notes', 'give-pdf-receipts' ) ), '', 2, '', false );

		$give_pdf->Ln( 0 );
		$give_pdf->setCellHeightRatio( 1.6 );
		$give_pdf->SetXY( 14, apply_filters( 'give_pdf_note_title_value_position', $note_title_val_y ) );
		$give_pdf->SetFont( $currency_font, '', $font_size_10 );
		// Set Text Color for the Value.
		$give_pdf->SetTextColor( 50, 50, 50 );
		$give_pdf->MultiCell( 183, '', give_pdf_get_settings( $give_pdf, 'notes' ), 0, '', false );
	}
	/*** End Additional Notes Section ***/

}

add_action( 'give_pdf_template_traditional', 'give_pdf_template_traditional', 10, 10 );