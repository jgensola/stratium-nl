<?php
/**
 * Give Fee Recovery functions.
 *
 * @package    Give_Fee_Recovery
 * @subpackage Give_Fee_Recovery/includes
 * @author     WordImpress <https://wordimpress.com>
 */

/**
 * List out the all of gateway setting inside donation form edit section.
 *
 * @since 1.0.0
 *
 * @param array $field Gateway's various fields.
 */
function give_fee_all_gateways( $field ) {

	global $thepostid, $post;

	// Get the current donation form ID.
	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

	// Get the field value by field key and post id.
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Fee Recovery gateway fields.
	$gateway_fields = $field['all_fields'];

	// Get Give payment gateways in a ordered list.
	$gateways = give_get_ordered_payment_gateways( give_get_enabled_payment_gateways() );

	// Return if there is no Gateway.
	if ( ! isset( $gateways ) ) {
		return;
	}
	?>
	<div <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . $field['wrapper_class'] . '"' : '' ?>>
		<?php
		foreach ( $gateways as $key => $gateway ) :

			?>
			<fieldset class="give_fee_gateway">
				<legend><?php echo $gateway['admin_label']; ?></legend>
				<?php
				// Loop for the gateway's field.
				foreach ( $gateway_fields as $gateway_key => $gateway_field ) {
					// Store reconstruct array from the Gateway fields.
					$customized_field = array();

					// Check if gateway is array and isset.
					if ( isset( $gateway_field ) && is_array( $gateway_field ) ) {
						// Loop for reconstruct array.
						foreach ( $gateway_field as $field_key => $field ) {
							if ( 'id' === $field_key ) {
								// Append gateway slugs.
								$customized_field[ $field_key ] = $field . '_' . $key;
							} else {
								$customized_field[ $field_key ] = $field;
							}
						}
					}
					// Output custom Give Fee Recovery Gateway Configuration.
					give_render_field( $customized_field );
				}
				?>
			</fieldset>
		<?php
		endforeach;
		?>
	</div>
	<?php
}

/**
 * Create custom donation setting fields for custom fields like percentage etc.
 *
 * @since 1.0.0
 *
 * @param array $field fields data.
 */
function give_fee_gateway_field_value( $field ) {

	global $thepostid, $post;

	// Get the data type.
	$data_type = empty( $field['data_type'] ) ? '' : $field['data_type'];

	// Get the Donation form id.
	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

	// Get the styles if passed with the field array.
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';

	// Get the option value by field and donation form id.
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Generate name for option field.
	$field['name'] = isset( $field['name'] ) ? $field['name'] : $field['id'];

	switch ( $data_type ) {

		case 'price' :
			$field['value']        = ( ! empty( $field['value'] ) ? give_fee_format_amount( $field['value'] ) : ( $field['value'] ) );
			$field['before_field'] = 'before' === give_get_option( 'currency_position' ) ? '<span class="give-money-symbol give-money-symbol-before">' . give_currency_symbol() . '</span>' : '';
			$field['after_field']  = 'after' === give_get_option( 'currency_position' ) ? '<span class="give-money-symbol give-money-symbol-after">' . give_currency_symbol() . '</span>' : '';
			break;

		case 'percent' :
			$field['value']        = ( ! empty( $field['value'] ) ? give_fee_format_amount( $field['value'] ) : ( $field['value'] ) );
			$field['before_field'] = '';
			$field['after_field']  = '<span class="give-percentage-symbol give-percentage-symbol-before"> % </span>';
			break;

		default :
			// Hook to add new data type to Give Fee recovery.
			do_action( "give_fee_recovery_form_{$data_type}", $field, $post );
			break;
	}
	?>
	<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
		<?php echo $field['before_field']; ?>
		<input
				type="text" style="<?php echo esc_attr( $field['style'] ); ?>"
				name="<?php echo give_get_field_name( $field ); ?>"
				id="<?php echo esc_attr( $field['id'] ); ?>"
				value="<?php echo esc_attr( $field['value'] ); ?>" <?php echo give_get_custom_attributes( $field ); ?>
		/>
		<?php
		echo $field['after_field'];
		echo give_get_field_description( $field );
		?>
	</p>
	<?php
}

/**
 * Give fee recover amount format function.
 *
 * @since 1.0.0
 *
 * @param integer         $amount   Donation amount.
 * @param boolean|integer $decimals Number of decimals.
 *
 * @return double
 */
function give_fee_number_format( $amount, $decimals = true ) {
	// If amount is empty and not containing any value.
	if ( empty( $amount ) ) {
		// Return zero.
		$amount = 0;
	} else {
		// Sanitize amount before formatting.
		$amount = give_sanitize_amount( $amount );
	}

	$number_decimals = give_get_option( 'number_decimals', 2 );

	if ( $decimals && ( 1 >= $number_decimals ) ) {
		$decimals = 2;
	} else {
		$decimals = $number_decimals;
	}

	// Round the total amount according to number of decimals.
	$amount = round( $amount, $decimals );

	// Format the amount value and return it.
	return apply_filters( 'give_fee_format_amount', $amount, $decimals );
}

/**
 * Modify give sanitize amount decimal.
 *
 * @since 1.5
 *
 * @param $number_decimals
 *
 * @return int
 */
function give_fee_sanitize_amount_decimals( $number_decimals ) {

	if ( $number_decimals >=  2 ) {
		return $number_decimals;
	}

	if ( is_admin()
	     && ! is_give_fee_edit_page()
	     && ( 1 >= $number_decimals
	     )
	) {
		$number_decimals = 2;
	} else if ( ( isset( $_POST['give-fee-status'] )
	              || isset( $_GET['give_action'] )
	              || give_is_success_page()
	              || give_is_donation_history_page()
	            )
	            && ( 1 >= $number_decimals )
	) {
		$number_decimals = 2;
	}

	return $number_decimals;
}

add_filter( 'give_sanitize_amount_decimals', 'give_fee_sanitize_amount_decimals', 10, 1 );


/**
 * Update donation amount to support Fee.
 *
 * @since 1.5
 *
 * @param int   $formatted_amount
 * @param int   $amount
 * @param int   $donation_id
 * @param array $format_args
 *
 * @return float|mixed|string
 */
function give_fee_donation_amount( $formatted_amount, $amount, $donation_id, $format_args ) {

	if ( $format_args['amount'] || $format_args['currency'] ) {
		// Backward compatibility.
		if( $donation_id instanceof Give_Payment ) {
			$donation_id = $donation_id->ID;
		}

		$donation_currency = give_get_payment_currency_code( $donation_id );

		if ( $format_args['amount'] ) {

			$number_decimals = give_get_option( 'number_decimals', 2 );

			if ( 1 >= $number_decimals ) {
				$decimals = 2;
			} else {
				$decimals = $number_decimals;
			}

			$formatted_amount = give_format_amount(
				give_fee_number_format( $amount ),
				! is_array( $format_args['amount'] ) ?
					array(
						'sanitize' => true,
						'currency' => $donation_currency,
						'decimal'  => $decimals,
					) :
					$format_args['amount']
			);
		}

		if ( $format_args['currency'] ) {
			$formatted_amount = give_currency_filter(
				$formatted_amount,
				! is_array( $format_args['currency'] ) ?
					array( 'currency_code' => $donation_currency ) :
					$format_args['currency']
			);
		}
	}

	return $formatted_amount;
}

add_filter( 'give_donation_amount', 'give_fee_donation_amount', 10, 4 );

/**
 * Returns a nicely formatted amount.
 *
 * @since 1.5
 *
 * @param string $amount Price amount to format
 * @param array  $args   Array of arguments.
 *
 * @return string $amount   Newly formatted amount or Price Not Available
 */
function give_fee_format_amount( $amount, $args = array() ) {
	// Backward compatibility.
	if ( is_bool( $args ) ) {
		$args = array(
			'decimal' => $args,
		);
	}

	$default_args = array(
		'decimal'     => true,
		'sanitize'    => true,
		'donation_id' => 0,
		'currency'    => '',
	);

	$args = wp_parse_args( $args, $default_args );

	// Set Currency based on donation id, if required.
	if ( $args['donation_id'] && empty( $args['currency'] ) ) {
		$donation_meta    = give_get_meta( $args['donation_id'], '_give_payment_meta', true );
		$args['currency'] = $donation_meta['currency'];
	}

	$formatted     = 0;
	$currency      = ! empty( $args['currency'] ) ? $args['currency'] : give_get_currency( $args['donation_id'] );
	$thousands_sep = give_get_price_thousand_separator( $currency );
	$decimal_sep   = give_get_price_decimal_separator( $currency );
	$decimals      = ! empty( $args['decimal'] ) ? give_get_price_decimals( $currency ) : 0;

	if ( ( 1 >= $decimals ) ) {
		$decimals = 2;
	}

	if ( ! empty( $amount ) ) {
		// Sanitize amount before formatting.
		$amount = ! empty( $args['sanitize'] ) ?
			give_maybe_sanitize_amount( $amount, array( 'number_decimals' => $decimals, 'currency' => $currency ) ) :
			number_format( $amount, $decimals, '.', '' );

		switch ( $currency ) {
			case 'INR':
				$decimal_amount = '';

				// Extract decimals from amount
				if ( ( $pos = strpos( $amount, '.' ) ) !== false ) {
					if ( ! empty( $decimals ) ) {
						$decimal_amount = substr( round( substr( $amount, $pos ), $decimals ), 1 );
						$amount         = substr( $amount, 0, $pos );

						if ( ! $decimal_amount ) {
							$decimal_amount = substr( "{$decimal_sep}0000000000", 0, ( $decimals + 1 ) );
						} elseif ( ( $decimals + 1 ) > strlen( $decimal_amount ) ) {
							$decimal_amount = substr( "{$decimal_amount}000000000", 0, ( $decimals + 1 ) );
						}
					} else {
						$amount = number_format( $amount, $decimals, $decimal_sep, '' );
					}
				}

				// Extract last 3 from amount
				$result = substr( $amount, - 3 );
				$amount = substr( $amount, 0, - 3 );

				// Apply digits 2 by 2
				while ( strlen( $amount ) > 0 ) {
					$result = substr( $amount, - 2 ) . $thousands_sep . $result;
					$amount = substr( $amount, 0, - 2 );
				}

				$formatted = $result . $decimal_amount;
				break;

			default:
				$formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );
		}
	}

	/**
	 * Filter the formatted amount
	 *
	 * @since 1.5
	 */
	return apply_filters( 'give_fee_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep, $currency, $args );
}

/**
 * Helper function to check Give Form Edit page.
 *
 * @since 1.5
 *
 * @return bool
 */
function is_give_fee_edit_page() {
	global $pagenow;

	// Make sure we are on the backend.
	if ( ! is_admin() ) {
		return false;
	}

	// Check for either new or edit.
	return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
}

/**
 * Get Fee amount total based on Form ID.
 *
 * @since 1.0.1
 *
 * @param int $form_id Give Form ID.
 *
 * @return string $give_payment_fee_total Form based Fee recovery total.
 */
function give_get_fee_earnings( $form_id = 0 ) {
	if ( empty( $form_id ) ) {
		return false;
	}

	global $wpdb;
	$donation_meta_table = Give()->payment_meta->table_name;

	$query = $wpdb->prepare(
		"
			SELECT payment_id, meta_value as payment_total
			FROM {$donation_meta_table}
			WHERE meta_key=%s
			AND payment_id IN (
				SELECT ID
				FROM {$wpdb->posts}
				INNER JOIN {$donation_meta_table}
				ON {$wpdb->posts}.ID={$donation_meta_table}.payment_id
				WHERE {$wpdb->posts}.post_status=%s
				AND {$donation_meta_table}.meta_key=%s
				AND {$donation_meta_table}.meta_value=%s
			)
			",
		'_give_fee_amount',
		'publish',
		'_give_payment_form_id',
		$form_id
	);

	$form_payments = $wpdb->get_results( $query, ARRAY_A );

	$fees = $formatted_fee_total = 0;

	// Must have payments.
	if ( ! empty( $form_payments ) ) {
		// Loop through an add up fees.
		foreach ( $form_payments as $payment ) {

			/**
			 * Filter the fee amount
			 *
			 * @param float Fee amount
			 * @param int   Payment ID
			 */
			$fees += apply_filters( 'give_fee_earning_amount', floatval( $payment['payment_total'] ), $payment['payment_id'] );
		}
	}

	$formatted_fee_total = give_fee_format_amount( $fees, array( 'sanitize' => false ) );

	/**
	 * Filter the fee earnings
	 *
	 * @param string Total fee earnings
	 */
	return apply_filters( 'give_get_fee_earnings', $formatted_fee_total );

}

/**
 * Calculate Recovery Fee.
 *
 * @param float $percentage       Percentage of Fee.
 * @param float $base_amount      Amount of Fee.
 * @param float $give_total       Total donation amount.
 * @param bool  $give_fee_disable Check whether fee option enable/disable.
 *
 * @return float $fee Fee.
 */
function give_fee_calculate( $percentage, $base_amount, $give_total, $give_fee_disable ) {
	// Set default percentage if empty.
	$percentage = ( false !== $percentage && '' !== $percentage ) ? $percentage : 2.90;
	$percentage = give_sanitize_amount( $percentage );

	/**
	 * Filter Fee base amount.
	 *
	 * @param float $base_amount Fee base amount.
	 */
	$base_amount = apply_filters( 'give_fee_base_amount', $base_amount );

	// Set default amount if empty.
	$base_amount = ( false !== $base_amount && '' !== $base_amount ) ? $base_amount : 0.30;
	$base_amount = give_sanitize_amount( $base_amount );
	$give_total  = give_sanitize_amount( $give_total );
	$fee         = 0;

	if ( isset( $percentage ) && isset( $base_amount ) && ! $give_fee_disable ) {

		// Calculate Fee based on Flat or not.
		if ( $percentage > 0 && $base_amount > 0 ) {
			$fee = give_fee_formula( $percentage, $base_amount, $give_total );
		} else {
			$fee = give_fee_flat_formula( $percentage, $base_amount, $give_total );
		}

		$fee = give_fee_number_format( $fee );
	}

	return $fee;
}

/**
 * Calculate Recovery Fee.
 *
 * @param float $percentage  Percentage of Fee.
 * @param float $base_amount Amount of Fee.
 * @param float $give_total  Total donation amount.
 *
 * @return float $fee Flat Fee.
 */
function give_fee_flat_formula( $percentage, $base_amount, $give_total ) {
	// Calculate Fee based on Flat Formula.
	$fee = $give_total * ( $percentage / 100 ) + $base_amount;

	return apply_filters( 'give_fee_flat_formula', $fee );
}

/**
 * Calculate Recovery Fee.
 *
 * @param float $percentage  Percentage of Fee.
 * @param float $base_amount Amount of Fee.
 * @param float $give_total  Total donation amount.
 *
 * @return float $fee Without Flat Fee.
 */
function give_fee_formula( $percentage, $base_amount, $give_total ) {
	// Calculate Fee based on new Formula.
	$total_with_fee = ( $give_total + $base_amount ) / ( 1 - ( $percentage / 100 ) );
	$fee            = $total_with_fee - $give_total;

	return apply_filters( 'give_fee_formula', $fee );
}

/**
 *  Inserts a new key/value after the key in the array.
 *
 * @TODO: Remove when helpers added to Give core.
 *
 * @param string $key       The key to insert after.
 * @param array  $array     An array to insert in to.
 * @param string $new_key   The key to insert.
 * @param string $new_value An value to insert.
 *
 * @return array The new array if the key exists, the old array otherwise.
 *
 * @see array_insert_before()
 */
function give_fee_recovery_array_insert_before( $key, array &$array, $new_key, $new_value ) {
	if ( array_key_exists( $key, $array ) ) {
		$new = array();
		foreach ( $array as $k => $value ) {
			if ( $k === $key ) {
				$new[ $new_key ] = $new_value;
			}
			$new[ $k ] = $value;
		}

		return $new;
	}

	return $array;
}

/**
 * Give Fee Recovery settings.
 *
 * @since 1.3.0
 *
 * @param bool $is_global Check if Global/Per-Form settings.
 *
 * @return array $settings Give Fee Recovery Settings array.
 */
function give_fee_settings( $is_global ) {
	// Per Form Prefix.
	$prefix = '_form_';

	// All settings key.
	$all_settings = array(
		'give_fee_settings',
		'give_fee_enable_disable',
		'give_fee_configuration',
		'give_fee_opt_in',
		'give_fee_opt_in_message',
		'give_fee_message',
		'give_fee_checkbox_location',
		'give_fee_break_down',
		'give_fee_percentage',
		'give_fee_base_amount',
		'give_fee_per_gateway',
		'give_fee_doc_link',
		'give_fee_settings_section_end',
	);

	$default_fee_percentage = give_format_decimal( '2.9' );
	$default_fee_decimal    = give_format_decimal( '0.30' );

	// Store global and Per form settings array into new array.
	$settings = array();
	if ( in_array( 'give_fee_settings', $all_settings, true ) ) {
		if ( $is_global ) {
			$settings[] = array(
				'type' => 'title',
				'id'   => 'give_fee_recovery_admin_settings',
			);
		}
	}// End if().

	// Fee Enable/Disable by Global or Per-Form.
	if ( in_array( 'give_fee_enable_disable', $all_settings, true ) ) {
		$options        = array();
		$default_option = 'global'; // Set default option as a global.

		if ( $is_global ) {
			$options['enabled']  = __( 'Enabled', 'give-fee-recovery' );
			$options['disabled'] = __( 'Disabled', 'give-fee-recovery' );
			$default_option      = 'disabled';
		} else {
			$options['global']   = __( 'Global Option', 'give-fee-recovery' );
			$options['enabled']  = __( 'Customize', 'give-fee-recovery' );
			$options['disabled'] = __( 'Disabled', 'give-fee-recovery' );
		}

		$settings[] = array(
			'name'          => __( 'Fee Recovery', 'give-fee-recovery' ),
			'desc'          => $is_global ? __( 'This enables the Fee Recovery feature for all your forms. Note: You can enable/disable and customize the fees options per form as well.', 'give-fee-recovery' ) : __( 'This enables the Fee Recovery feature for all your forms.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_recovery' : $prefix . 'give_fee_recovery',
			'wrapper_class' => 'give_fee_recovery',
			'type'          => 'radio_inline',
			'default'       => $default_option,
			'options'       => $options,
		);

	}// End if().

	// Fee Configuration.
	if ( in_array( 'give_fee_configuration', $all_settings, true ) ) {
		$options                 = array();
		$options['all_gateways'] = __( 'All Gateways', 'give-fee-recovery' );
		$options['per_gateway']  = __( 'Set Per Gateway', 'give-fee-recovery' );

		$settings[] = array(
			'name'          => __( 'Gateway Fee Support', 'give-fee-recovery' ),
			'desc'          => __( 'Set the fee recovery amount to be the same for all gateways or configure the fees per gateway.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_configuration' : $prefix . 'give_fee_configuration',
			'wrapper_class' => 'give_fee_all_fields give_fee_configuration give-hidden',
			'type'          => 'radio_inline',
			'default'       => 'all_gateways',
			'options'       => $options,
		);

	}// End if().

	// Fee Opt-in.
	if ( in_array( 'give_fee_opt_in', $all_settings, true ) ) {
		$options                  = array();
		$options['donor_opt_in']  = __( 'Donor Opt-in', 'give-fee-recovery' );
		$options['forced_opt_in'] = __( 'Forced Opt-in', 'give-fee-recovery' );

		$settings[] = array(
			'name'          => __( 'Fee Opt-In', 'give-fee-recovery' ),
			'desc'          => __( 'You can allow donors to opt-in to cover the fees, or force the opt-in.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_mode' : $prefix . 'give_fee_mode',
			'wrapper_class' => 'give_fee_all_fields give_fee_mode give-hidden',
			'type'          => 'radio_inline',
			'default'       => 'donor_opt_in',
			'options'       => $options,
		);

	}// End if().

	// Fee Opt-in message.
	if ( in_array( 'give_fee_opt_in_message', $all_settings, true ) ) {
		$settings[] = array(
			'name'          => __( 'Opt-in Message', 'give-fee-recovery' ),
			'default'       => __( 'I\'d like to help cover the transaction fees of {fee_amount} for my donation.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_checkbox_label' : $prefix . 'give_fee_checkbox_label',
			'wrapper_class' => 'give_fee_all_fields give_fee_checkbox_label give-hidden',
			'type'          => 'text',
			'description'   => __( 'This is the message the donor sees next to a checkbox indicating that they choose to donate the credit card fees.', 'give-fee-recovery' ),
		);

	}// End if().

	// Fee message.
	if ( in_array( 'give_fee_message', $all_settings, true ) ) {
		$settings[] = array(
			'name'          => __( 'Fee Message', 'give-fee-recovery' ),
			'default'       => __( 'Plus an additional {fee_amount} to cover gateway fees.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_explanation' : $prefix . 'give_fee_explanation',
			'wrapper_class' => 'give_fee_all_fields give_fee_explanation give-hidden',
			'type'          => 'text',
			'description'   => __( 'This is the message displayed below the total amount indicating that an additional amount is added to their donation automatically.', 'give-fee-recovery' ),
		);

	}// End if().

	// Checkbox location.
	if ( in_array( 'give_fee_checkbox_location', $all_settings, true ) ) {
		$options                                            = array();
		$options['give_after_donation_levels']              = __( 'Below the donation level fields', 'give-fee-recovery' );
		$options['give_after_donation_amount']              = __( 'Below the top donation amount field', 'give-fee-recovery' );
		$options['give_payment_mode_top']                   = __( 'Above the payment options', 'give-fee-recovery' );
		$options['give_payment_mode_bottom']                = __( 'Below the payment options', 'give-fee-recovery' );
		$options['give_donation_form_before_personal_info'] = __( 'Above the personal info fields', 'give-fee-recovery' );
		$options['give_donation_form_after_personal_info']  = __( 'Below the personal info fields', 'give-fee-recovery' );
		$options['give_donation_form_before_cc_form']       = __( 'Above the credit card fields', 'give-fee-recovery' );
		$options['give_donation_form_after_cc_form']        = __( 'Below the credit card fields', 'give-fee-recovery' );

		$settings[] = array(
			'name'          => __( 'Checkbox location', 'give-fee-recovery' ),
			'desc'          => __( 'Choose option to place checkbox location.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_checkbox_location' : $prefix . 'give_fee_checkbox_location',
			'wrapper_class' => 'give_fee_all_fields give_fee_checkbox_location give-hidden',
			'type'          => 'select',
			'default'       => 'give_after_donation_levels',
			'options'       => $options,
		);

	}// End if().

	// Include Fee Breakdown.
	if ( in_array( 'give_fee_break_down', $all_settings, true ) ) {
		$options             = array();
		$options['enabled']  = __( 'Enabled', 'give-fee-recovery' );
		$options['disabled'] = __( 'Disabled', 'give-fee-recovery' );

		$settings[] = array(
			'name'          => __( 'Include Fee Breakdown', 'give-fee-recovery' ),
			'desc'          => __( 'If enabled a text breakdown of the donation total and fee will show below the final total amount on the donation form.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_breakdown' : $prefix . 'breakdown',
			'wrapper_class' => 'give_fee_all_fields give_fee_breakdown give-hidden',
			'type'          => 'radio_inline',
			'default'       => 'enabled',
			'options'       => $options,
		);

	}// End if().

	// Fee Percentage.
	if ( in_array( 'give_fee_percentage', $all_settings, true ) ) {
		$settings[] = array(
			'name'          => __( 'Fee Percentage', 'give-fee-recovery' ),
			'desc'          => __( 'Enter the fee percentage. This is typically between 1.5-3.5% depending on the gateway.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_percentage' : $prefix . 'give_fee_percentage',
			'wrapper_class' => 'give_fee_all_fields give_fee_percentage give_fee_all_gateway give-hidden',
			'data_type'     => $is_global ? 'decimal' : 'percent',
			'type'          => $is_global ? 'fee_recovery_percentage_text' : 'fee_gateway_field_value',
			'attributes'    => array(
				'placeholder' => $default_fee_percentage,
				'class'       => $is_global ? 'give-money-field' : 'give-fee-recovery-field give-text_small',
			),
		);

	}// End if().

	// Fee Base amount.
	if ( in_array( 'give_fee_base_amount', $all_settings, true ) ) {
		$settings[] = array(
			'name'          => __( 'Additional Fee Amount', 'give-fee-recovery' ),
			'desc'          => __( 'This is an additional amount added to the percentage fee. For example, 2.9% + 30 cents.', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_base_amount' : $prefix . 'give_fee_base_amount',
			'wrapper_class' => 'give_fee_all_fields give_fee_base_amount give_fee_all_gateway give-hidden',
			'data_type'     => $is_global ? 'decimal' : 'price',
			'type'          => $is_global ? 'fee_recovery_base_amount_text' : 'fee_gateway_field_value',
			'attributes'    => array(
				'placeholder' => $default_fee_decimal,
				'class'       => $is_global ? 'give-money-field' : 'give-fee-recovery-field give-text_small',
			),
		);

	}// End if().

	// All Gateway.
	if ( in_array( 'give_fee_per_gateway', $all_settings, true ) ) {
		$all_fields = array(
			array(
				'name'          => __( 'Fee Recovery', 'give-fee-recovery' ),
				'desc'          => __( 'Enable this to configure Fee Recovery for this Gateway.', 'give-fee-recovery' ),
				'id'            => $is_global ? 'give_fee_gateway_fee_enable_option' : $prefix . 'gateway_fee_enable',
				'wrapper_class' => 'give_fee_all_fields give_fee_gateway_fee_enable_disable_option',
				'type'          => 'radio_inline',
				'default'       => 'enabled',
				'options'       => array(
					'enabled'  => __( 'Enabled', 'give-fee-recovery' ),
					'disabled' => __( 'Disabled', 'give-fee-recovery' ),
				),
			),
			array(
				'name'          => __( 'Fee Percentage', 'give-fee-recovery' ),
				'id'            => $is_global ? 'give_fee_gateway_fee_percentage' : $prefix . 'gateway_fee_percentage',
				'wrapper_class' => 'give_fee_all_fields give_fee_gateway_fee_percentage give_fee_percentage',
				'type'          => $is_global ? 'fee_recovery_percentage_text' : 'fee_gateway_field_value',
				'css'           => 'width:12em;',
				'data_type'     => $is_global ? 'decimal' : 'percent',
				'attributes'    => array(
					'placeholder' => $default_fee_percentage,
					'class'       => $is_global ? 'give-money-field' : 'give-fee-recovery-field give-text_small',
				),
				'description'   => __( 'Enter the fee percentage. This is typically between 1.5-3.5% depending on the gateway.', 'give-fee-recovery' ),
			),
			array(
				'name'          => __( 'Additional Fee Amount', 'give-fee-recovery' ),
				'id'            => $is_global ? 'give_fee_gateway_fee_base_amount' : $prefix . 'gateway_fee_base_amount',
				'wrapper_class' => 'give_fee_all_fields give_fee_gateway_fee_base_amount give_fee_base_amount',
				'type'          => $is_global ? 'fee_recovery_base_amount_text' : 'fee_gateway_field_value',
				'css'           => 'width:12em;',
				'data_type'     => $is_global ? 'decimal' : 'price',
				'attributes'    => array(
					'placeholder' => $default_fee_decimal,
					'class'       => $is_global ? 'give-money-field' : 'give-fee-recovery-field give-text_small',
				),
				'description'   => __( 'This is an additional amount added to the percentage fee. For example, 2.9% + 30 cents.', 'give-fee-recovery' ),
			),
		);

		$settings[] = array(
			'name'          => __( 'Per Gateway', 'give-fee-recovery' ),
			'id'            => $is_global ? 'give_fee_gateway_fee_base_amount' : $prefix . 'gateway_fee_base_amount',
			'wrapper_class' => 'give_fee_all_fields give_fee_gateways_fields give_fee_per_gateway give-hidden',
			'type'          => $is_global ? 'give_fee_gateways_fields' : 'fee_all_gateways',
			'all_fields'    => $all_fields,
		);

	}// End if().

	// Set Doc Link.
	if ( in_array( 'give_fee_doc_link', $all_settings, true ) ) {
		$docs_link_type = 'docs_link'; // Set doc type for per form.
		if ( $is_global ) {
			$docs_link_type = 'give_docs_link';
		}

		$settings[] = array(
			'name'  => __( 'Give Fee Recovery Settings Docs Link', 'give-fee-recovery' ),
			'id'    => 'give_fee_recovery_settings_docs_link',
			'url'   => esc_url( 'http://docs.givewp.com/addon-fee-recovery' ),
			'title' => __( 'Give Fee Recovery Settings', 'give-fee-recovery' ),
			'type'  => $docs_link_type,
		);
	}// End if().

	if ( in_array( 'give_fee_settings_section_end', $all_settings, true ) ) {
		if ( $is_global ) {
			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'give_fee_recovery_admin_settings',
			);
		}
	}// End if().

	return $settings;
}

/**
 * Increase Form Fee earnings amount.
 *
 * @since 1.5.1
 *
 * @param int   $payment_id
 * @param float $diff_amount
 */
function give_fee_increase_form_fee_amount( $payment_id, $diff_amount ) {
	$form_id           = give_get_meta( $payment_id, '_give_payment_form_id', true );
	$form_fee_earnings = give_get_meta( $form_id, '_give_form_fee_earnings', true );
	$form_fee_earnings = ! empty( $form_fee_earnings ) ? $form_fee_earnings : 0;

	/**
	 * Update Fee amount.
	 *
	 * @since 1.5.1
	 *
	 * @param int $payment_id
	 */
	$diff_amount = apply_filters( 'give_fee_recovery_fee_amount', $diff_amount, $payment_id );

	$form_fee_earnings += (float) $diff_amount;
	give_update_meta( $form_id, '_give_form_fee_earnings', give_sanitize_amount_for_db( $form_fee_earnings ) );
}

/**
 * Decrease Form Fee earnings amount.
 *
 * @since 1.5.1
 *
 * @param int   $payment_id
 * @param float $diff_amount
 */
function give_fee_decrease_form_fee_amount( $payment_id, $diff_amount ) {
	$form_id           = give_get_meta( $payment_id, '_give_payment_form_id', true );
	$form_fee_earnings = give_get_meta( $form_id, '_give_form_fee_earnings', true );
	$form_fee_earnings = ! empty( $form_fee_earnings ) ? $form_fee_earnings : 0;

	/**
	 * Update Fee amount.
	 *
	 * @since 1.5.1
	 *
	 * @param int $payment_id
	 */
	$diff_amount = apply_filters( 'give_fee_recovery_fee_amount', $diff_amount, $payment_id );

	$form_fee_earnings -= (float) $diff_amount;
	give_update_meta( $form_id, '_give_form_fee_earnings', give_sanitize_amount_for_db( $form_fee_earnings ) );
}

/**
 * Store Form fee earnings for normal and renewal donation.
 *
 * @since 1.5.1
 *
 * @param int $payment_id
 */
function give_fee_store_form_fee_meta( $payment_id ) {
	$form_id    = give_get_meta( $payment_id, '_give_payment_form_id', true );
	$fee_amount = give_get_meta( $payment_id, '_give_fee_amount', true );
	$fee_amount = ! empty( $fee_amount ) ? $fee_amount : 0;

	/**
	 * Update Fee amount.
	 *
	 * @since 1.5.1
	 *
	 * @param int $payment_id
	 */
	$fee_amount = apply_filters( 'give_fee_recovery_fee_amount', $fee_amount, $payment_id );

	$form_fee_earnings = give_get_meta( $form_id, '_give_form_fee_earnings', true );
	$form_fee_earnings = ! empty( $form_fee_earnings ) ? $form_fee_earnings : 0;

	$form_fee_earnings += (float) $fee_amount;
	give_update_meta( $form_id, '_give_form_fee_earnings', give_sanitize_amount_for_db( $form_fee_earnings ) );
}