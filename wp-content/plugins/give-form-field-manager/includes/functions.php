<?php
/**
 * Returns an array of allowed extensions to be used with upload form field
 * @return array an array of allowed extensions
 */
function give_ffm_allowed_extension() {
	$extensions = array(
		'images' => array(
			'ext'   => 'jpg,jpeg,gif,png,bmp',
			'label' => __( 'Images', 'give-form-field-manager' )
		),
		'audio'  => array(
			'ext'   => 'mp3,wav,ogg,wma,mka,m4a,ra,mid,midi',
			'label' => __( 'Audio', 'give-form-field-manager' )
		),
		'video'  => array(
			'ext'   => 'avi,divx,flv,mov,ogv,mkv,mp4,m4v,divx,mpg,mpeg,mpe',
			'label' => __( 'Videos', 'give-form-field-manager' )
		),
		'pdf'    => array(
			'ext'   => 'pdf',
			'label' => __( 'PDF', 'give-form-field-manager' )
		),
		'office' => array(
			'ext'   => 'doc,ppt,pps,xls,mdb,docx,xlsx,pptx,odt,odp,ods,odg,odc,odb,odf,rtf,txt',
			'label' => __( 'Office Documents', 'give-form-field-manager' )
		),
		'zip'    => array(
			'ext'   => 'zip,gz,gzip,rar,7z',
			'label' => __( 'Zip Archives' )
		),
		'exe'    => array(
			'ext'   => 'exe',
			'label' => __( 'Executable Files', 'give-form-field-manager' )
		),
		'csv'    => array(
			'ext'   => 'csv',
			'label' => __( 'CSV', 'give-form-field-manager' )
		)
	);

	return apply_filters( 'ffm_allowed_extensions', $extensions );

}

/**
 * Associate attachment to a transaction post
 *
 * @since 1.0
 *
 * @param $attachment_id
 * @param $post_id
 */
function give_ffm_associate_attachment( $attachment_id, $post_id ) {
	wp_update_post( array(
		'ID'          => $attachment_id,
		'post_parent' => $post_id
	) );
}

/**
 * Add FFM Form Fields to donation receipt.
 *
 * @param $donation \Give_Payment Object.
 * @param $args     Donation Receipt Arguments.
 *
 * @since 1.2
 */
function give_ffm_donation_receipt_output( $donation, $args ) {

	// Get this form ID from payment.
	$form_id      = give_get_payment_form_id( $donation->ID );

	// Get input field data.
	$ffm          = new Give_FFM_Render_Form();
	$form_data    = $ffm->get_input_fields( $form_id );
	$ignore_lists = array( 'section', 'html', 'action_hook', 'file_upload', 'hidden' );
	$html         = '';

	// Loop through form fields and match.
	foreach ( $form_data as $key => $value ) {

		if ( empty( $value ) ) {
			continue;
		}

		foreach ( $value as $field ) {

			// Double check this input type is set.
			if ( ! isset( $field['input_type'] ) ) {
				continue;
			}

			// Ignore section break and HTML input type.
			if ( in_array( $field['input_type'], $ignore_lists ) ) {
				continue;
			}

			// Whether to return a single value (complex repeaters return array).
			if ( isset( $field['columns'] ) && ! empty( $field['columns'][0] ) ) {
				$field_data = give_get_meta( $donation->ID, $field['name'], false );
			} else {
				$field_data = give_get_meta( $donation->ID, $field['name'], true );
			}

			// Only show fields with data.
			if ( empty( $field_data ) ) {
				continue;
			}

			// Handle repeaters.
			$repeaters = array( 'repeat', 'multiselect' );

			// Handle various input types' output.
			switch ( $field['input_type'] ) {

				case in_array( $field['input_type'], $repeaters ):

					// Complex repeater output.
					if ( is_array( $field_data ) ) {

						// Sanity checks.
						if ( empty( $field_data ) || ! is_array( $field_data ) ) {
							return false;
						}

						$html .= '<tr>';
						$html .= '<td colspan="2">';
						$html .= '<strong>' . $field['label'] . '</strong>';
						$html .= '<table class="give-table">';
						$html .= '<thead>';
						$html .= '<tr>';

						foreach ( $field['columns'] as $column ) {

							$html .= '<th>';
							$html .= $column;
							$html .= '</th>';

						}
						$html .= '</tr>';
						$html .= '</thead>';
						$html .= '<tbody>';

						foreach ( $field_data as $data ) {
							$data = explode( '| ', $data );
							$html .= '<tr>';
							foreach ( $data as $th ) {
								$html .= '<td scope="row">';
								$html .= $th;
								$html .= '</td>';
							}
							$html .= '</tr>';
						}

						$html .= '</tbody>';
						$html .= '</table>';
						$html .= '</td>';
						$html .= '</tr>';

					} else {
						// Simple repeater output.

						$field_data = explode( '| ', $field_data );

						// Sanity checks.
						if ( empty( $field_data ) || ! is_array( $field_data ) ) {
							return false;
						}

						$count            = 1;
						$field_data_count = count( $field_data );

						foreach ( $field_data as $data ) {
							$html .= '<tr>';

							// Show Label once only.
							if( 1 === $count ) {
								$html .= '<td rowspan=" ' . $field_data_count . ' "><strong>' . $field['label'] . '</strong></td>';
							}

							$html .= '<td>' . $data . '</td>';
							$html .= '</tr>';
							$count ++;
						}

					}

					break;

				default :
					$html .= '<tr>';
					$html .= '<td><strong>' . $field['label'] . '</strong></td>';
					$html .= '<td>' . $field_data . '</td>';
					$html .= '</tr>';
			}
		}
	}

	echo $html;
}

add_action( 'give_payment_receipt_after', 'give_ffm_donation_receipt_output', 10, 2 );

/**
 * Generates a random text drawn from the defined set of characters.
 *
 * @since 1.2.2
 *
 * @param int  $length Optional. The length of password to generate. Default 12.
 *
 * @return string The random text.
 */
function give_ffm_random_text_generate( $length = 12 ) {
	$chars = 'abcdefghijklmnopqrstuvwxyz';
	$text = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$text .= substr($chars, wp_rand(0, strlen($chars) - 1), 1);
	}

	/**
	 * Filters the Random Text Generate.
	 *
	 * @since 1.2.2
	 *
	 * @param string $password The generated password.
	 */
	return apply_filters( 'give_ffm_random_text_generate', $text );
}