/**
 * Transaction Screen Functionality
 *
 * Handles transaction screen client-side functionality for editing existing
 * donations.
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.2
 */

;
(function ($) {

	$(function () {
		// mask phone fields with domestic formatting
		$('.js-phone-domestic').mask('(999) 999-9999');
	});

})(jQuery);