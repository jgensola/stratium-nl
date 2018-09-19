/**
 * Give Fees Recovery Add-on file that contain function that are been used in frontend and backend both.
 */

/**
 * Calculate Fee.
 *
 * @param percentage
 * @param base_amount
 * @param give_total
 * @param give_fee_disable
 *
 * @returns {number}
 */
function give_fee_calculate( percentage, base_amount, give_total, give_fee_disable ) {
	var fee = 0;
	if ( '' !== percentage && '' !== base_amount && false === give_fee_disable ) {
		// Calculate Fee based on Flat or not.
		if ( percentage > 0 && base_amount > 0 ) {
			fee = give_fee_formula( percentage, base_amount, give_total );
		} else {
			fee = give_fee_flat_formula( percentage, base_amount, give_total );
		}
	}
	return fee;
}

/**
 * Calculate Fee Formula.
 *
 * @param percentage
 * @param base_amount
 * @param give_total
 *
 * @returns {number}
 */
function give_fee_formula( percentage, base_amount, give_total ) {
	// Calculate Fee based on new Formula.
	var total_with_fee = ( give_total + base_amount ) / ( 1 - ( percentage / 100 ) );

	return total_with_fee - give_total;
}

/**
 * Calculate Fee with Flat formula.
 *
 * @param percentage
 * @param base_amount
 * @param give_total
 * @returns {*}
 */
function give_fee_flat_formula( percentage, base_amount, give_total ) {
	// Calculate Fee based on Flat Formula.
	return give_total * ( percentage / 100 ) + base_amount;
}

/**
 * Unformat Currency.
 *
 * @param price
 * @param decimal_separator
 * @returns {number}
 */
function give_fee_unformat_amount( price, decimal_separator ) {

	if ( decimal_separator ) {
		return Math.abs( parseFloat( accounting.unformat( price, decimal_separator ) ) );
	}

	if ( 'undefined' !== typeof( give_global_vars ) && 'undefined' !== typeof( give_global_vars.decimal_separator ) ) {
		return Math.abs( parseFloat( accounting.unformat( price, give_global_vars.decimal_separator ) ) );
	} else if ( 'undefined' !== typeof( give_vars ) && 'undefined' !== typeof( give_vars.decimal_separator ) ) {
		return Math.abs( parseFloat( accounting.unformat( price, give_vars.decimal_separator ) ) );
	}
}

/**
 * Helper function to get the formatted amount.
 *
 * @param {string/number} amount
 * @param {jQuery} form
 */
function give_fee_format_amount( amount, form ) {

	var vars,
		precision;

	if ( 'undefined' !== typeof( give_global_vars ) ) {
		vars = give_global_vars;
		precision = give_global_vars.number_decimals;

		// Get currency symbol by form.
		vars[ 'currency_sign' ] = Give.form.fn.getInfo( 'currency_symbol', form );
		vars[ 'decimal_separator' ] = Give.form.fn.getInfo( 'decimal_separator', form );
		vars[ 'thousands_separator' ] = Give.form.fn.getInfo( 'thousands_separator', form );
	} else {
		vars = give_vars;
		precision = give_vars.currency_decimals;
		vars[ 'decimal_separator' ] = vars.decimal_separator;
	}

	// Set precision.
	if ( 1  >= parseInt( precision ) ) {
		precision = 2;
	}

	// Set the custom amount input value format properly.
	var format_args = {
		symbol: vars.currency_sign,
		decimal: vars.decimal_separator,
		thousand: vars.thousands_separator,
		precision: precision,
		format: 'before' === vars.currency_pos ? '%s%v' : '%v%s'
	};
	return accounting.formatMoney( amount, format_args ); // Get the formatted currency value.
}