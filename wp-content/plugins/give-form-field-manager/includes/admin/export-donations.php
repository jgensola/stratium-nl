<?php
/**
 * Admin Export Donation
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Donation Form fields manager fields in export donation page
 *
 * @since 1.3
 */
function give_ffm_export_donation_form_fields_manager_fields() {
	?>
	<tr class="give-hidden give-export-donations-hide give-export-donations-ffm">
		<td scope="row" class="row-title">
			<label><?php _e( 'Form Field Manager Fields:', 'give-ffm' ); ?></label>
		</td>
		<td class="give-field-wrap">
			<div class="give-clearfix">
				<ul class="give-export-option-ul"></ul>
				<p class="give-field-description"><?php _e( 'The following fields have been created by Form Field Manager.', 'give-ffm' ); ?></p>
			</div>
		</td>
	</tr>
	<?php
}

add_action( 'give_export_donation_fields', 'give_ffm_export_donation_form_fields_manager_fields', 20 );

/**
 * Display all the fields that are present in the donation form
 *
 * @since 1.3
 *
 * @param array $responses contain the list of filed that are going to be display when donation form is selected
 * @param array $form_id Donation form id
 *
 * @return  array $responses contain the list of form filed manage that need to be display when donation form is selected
 */
function give_ffm_export_donations_get_custom_fields( $responses, $form_id ) {

	$ffm_field_array           = array();
	$non_multi_column_ffm_keys = array();

	// Get the custom fields for the payment's form.
	$ffm = new Give_FFM_Render_Form();

	list( $post_fields, $taxonomy_fields, $custom_fields ) = $ffm->get_input_fields( absint( $form_id ) );

	foreach ( $custom_fields as $field ) {

		// Assemble multi-column repeater fields.
		if ( isset( $field['multiple'] ) && 'repeat' === $field['input_type'] ) {
			$non_multi_column_ffm_keys[] = $field['name'];

			foreach ( $field['columns'] as $column ) {

				// All other fields.
				$ffm_field_array['repeaters'][] = array(
					'subkey'       => 'repeater_' . give_export_donations_create_column_key( $column ),
					'metakey'      => $field['name'],
					'label'        => $column,
					'multi'        => 'true',
					'parent_meta'  => $field['name'],
					'parent_title' => $field['label'],
				);
			}
		} else {
			// All other fields.
			$ffm_field_array['single'][] = array(
				'subkey'  => $field['name'],
				'metakey' => $field['name'],
				'label'   => $field['label'],
				'multi'   => 'false',
				'parent'  => '',
			);
			$non_multi_column_ffm_keys[] = $field['name'];
		}
	}
	$responses['ffm_fields'] = $ffm_field_array;

	// Unset ignored FFM keys for standard meta.
	if ( ! empty( $responses['standard_fields'] ) ) {

		$standard_fields = $responses['standard_fields'];

		foreach ( $non_multi_column_ffm_keys as $key ) {
			if ( ( $key = array_search( $key, $standard_fields ) ) !== false ) {
				unset( $standard_fields[ $key ] );
			}
		}

		$responses['standard_fields'] = empty( $standard_fields ) ? array() : array_values( $standard_fields );
	}

	return $responses;
}

add_filter( 'give_export_donations_get_custom_fields', 'give_ffm_export_donations_get_custom_fields', 10, 2 );

/**
 * Filter to add donation Form fields manager data in CSV columns
 *
 * @since 1.3
 *
 * @param array $data Donation Data for CSV
 * @param Give_Payment $payment Instance of Give_Payment
 * @param array $columns Donation columns
 * @param Give_Export_Donations_CSV $instance Instance of Give_Export_Donations_CSV
 *
 * @return  array $data Donation Data for CSV
 */
function give_ffm_give_export_donation_data( $data, $payment, $columns, $instance ) {

	// Get the custom fields for the payment's form.
	$ffm = new Give_FFM_Render_Form();
	list(
		$post_fields,
		$taxonomy_fields,
		$custom_fields
		) = $ffm->get_input_fields( $payment->form_id );
	$parents = isset( $instance->data['give_give_donations_export_parent'] ) ? $instance->data['give_give_donations_export_parent'] : array();


	// Loop through the fields.
	foreach ( $custom_fields as $field ) {

		// Check if this custom field should be exported first.
		if ( empty( $parents[ $field['name'] ] ) ) {
			continue;
		}

		// Check for Repeater Columns
		if ( isset( $field['multiple'] ) ) {

			$num_columns = count( $field['columns'] );

			// Loop through columns
			for ( $count = 0; $count < $num_columns; $count ++ ) {
				$keyname = 'repeater_' . give_export_donations_create_column_key( $field['columns'][ $count ] );
				$items   = (array) $ffm->get_meta( $payment->ID, $field['name'], 'post', false );

				// Reassemble arrays.
				if ( $items ) {

					$final_vals = array();

					foreach ( $items as $item_val ) {

						$item_val = explode( $ffm::$separator, $item_val );

						// Add relevant fields to array.
						$final_vals[ $count ][] = $item_val[ $count ];

					}

					$data[ $keyname ] = implode( '| ', $final_vals[ $count ] );

				} else {
					$data[ $keyname ] = '';
				}

				$instance->cols[ $keyname ] = '';

				unset( $columns[ $keyname ] );

			}

			unset( $instance->cols[ $field['name'] ] );
			// Unset this to prevent field from catchall field loop below.
			unset( $columns[ $field['name'] ] );
		}
	}

	return $data;
}

add_filter( 'give_export_donation_data', 'give_ffm_give_export_donation_data', 10, 4 );