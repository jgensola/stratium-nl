/**
 * Give MailChimp Admin Settings JS
 *
 * @package:     Give
 * @since:       1.0
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2017, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

var give_vars;
jQuery.noConflict();
jQuery(document).ready(function ($) {

	/**
	 * Refresh Lists Button click.
	 */
	$('.give-reset-mailchimp-button').on('click', function (e) {

		e.preventDefault();
		var field_type = $(this).data('field_type');

		var data = {
				action: $(this).data('action'),
				field_type: field_type,
				post_id: give_vars.post_id
			},
			refresh_button = $(this),
			spinner = $(this).next();

		$.ajax({
			method: 'POST',
			url: ajaxurl,
			data: data,
			beforeSend: function () {
				spinner.addClass('is-active');
			},
			success: function (res) {
				if ( true == res.success ) {
					$( '.give-mailchimp-list-wrap' ).empty().append( res.data.lists );

					refresh_button.hide();
					spinner.removeClass('is-active');
				}
			}
		});
	});

});
