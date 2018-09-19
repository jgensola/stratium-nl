/**
 * Form Field Builder - JS
 *
 * Handles form builder client side (JS) functionality.
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

; //<- here for good measure
(function ($) {

	var $formEditor = $('ul#give-form-fields-editor');

	var Editor = {

		init: function () {

			this.makeSortable();

			// collapse all
			$('button.ffm-collapse').on('click', this.collapseEditFields);

			// add field click
			$('.give-form-fields-buttons').on('click', 'button', this.addNewField);

			// remove form field
			$formEditor.on('click', '.item-delete', this.removeFormField);

			// on blur event: set meta key
			$formEditor.on('blur', '.js-ffm-field-label', this.setMetaKey);
			$formEditor.on('blur', '.js-ffm-meta-key', this.setMetaKey);
			$formEditor.on('blur', '.js-ffm-meta-key', this.setEmailTag);
			$('.js-ffm-meta-key', $formEditor ).blur();

			// on blur event: check meta key
			$formEditor.on('blur', '.js-ffm-meta-key', this.checkDuplicateMetaKeys);

			// on change event: checkbox|radio fields
			$formEditor.on('change', '.give-form-fields-sub-fields input[type=text]', function () {
				$(this).prev('input[type=checkbox], input[type=radio]').val($(this).val());
			});

			// on change event: checkbox field for enabling/disabling ffm fields.
			$formEditor.on( 'change', '.hide-field-label input', this.showHideFFMFields );

			// on change event: checkbox|radio fields
			$formEditor.on('click', 'input[type=checkbox].multicolumn', function () {
				var $self = $(this),
					$parent = $self.closest('.give-form-fields-rows');

				if ($self.is(':checked')) {
					$parent.next().hide().next().hide();
					$parent.siblings('.column-names').show();
				} else {
					$parent.next().show().next().show();
					$parent.siblings('.column-names').hide();
				}
			});

			// clone and remove repeated field
			$formEditor.on('click', '.ffm-clone-field', this.cloneField);
			$formEditor.on('click', '.ffm-remove-field', this.removeField);

			$formEditor.on( 'click', '.give-icon-locked-anchor', this.unlock_meta_key );

			// show hide ffm field in export donation page
			$( document ).on( 'give_export_donations_form_response', function ( ev, response ) {
				/**
				 * FFM Fields
				 */
				var ffm_fields = (
					'undefined' !== typeof response.ffm_fields &&
					null !== response.ffm_fields
				) ? response.ffm_fields : '';

				if ( ffm_fields ) {

					var ffm_field_list = $( '.give-export-donations-ffm ul' );

					// Loop through FFM fields & output
					$( ffm_fields ).each( function ( index, value ) {

						// Repeater sections.
						var repeater_sections = (
							typeof value.repeaters !== 'undefined'
						) ? value.repeaters : '';

						if ( repeater_sections ) {

							ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

							var parent_title = '';
							// Repeater section field.
							$( repeater_sections ).each( function ( index, value ) {
								if ( parent_title !== value.parent_title ) {
									ffm_field_list.append( '<li class="give-export-donation-checkbox-remove repeater-section-title" data-parent-meta="' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.parent_meta + '"><input type="checkbox" name="give_give_donations_export_parent[' + value.parent_meta + ']" id="give-give-donations-ffm-field-' + value.parent_meta + '">' + value.parent_title + '</label></li>' );
								}
								parent_title = value.parent_title;
								ffm_field_list.append( '<li class="give-export-donation-checkbox-remove repeater-section repeater-section-' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.subkey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label></li>' );
							} );
						}
						// Repeater sections.
						var single_repeaters = (
							typeof value.single !== 'undefined'
						) ? value.single : '';

						if ( single_repeaters ) {

							ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

							// Repeater section field.
							$( single_repeaters ).each( function ( index, value ) {
								ffm_field_list.append( '<li class="give-export-donation-checkbox-remove"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.metakey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label> </li>' );
							} );
						}
					} );
				}
			} );
		},

		unlock_meta_key: function( e ) {

			var user_input = confirm( give_ffm_formbuilder.notify_meta_key_lock );

			if( user_input ) {
				$( this ).closest( '.give-meta-key-wrap' ).find( 'input[type="text"]' ).removeAttr('readonly');
				$( this ).remove();
			}

			e.preventDefault();
		},

		/**
		 * Make Sortable
		 */
		makeSortable: function () {
			$formEditor = $('ul#give-form-fields-editor');

			if ($formEditor) {
				$formEditor.sortable({
					placeholder: "sortable-placeholder",
					handle: '> .ffm-legend',
					distance: 5
				});
			}
		},

		/**
		 * Add New Field
		 *
		 * @param e
		 */
		addNewField: function (e) {
			e.preventDefault();

			$('.ffm-loading').fadeIn();

			var $self = $(this),
				$formEditor = $('ul#give-form-fields-editor'),
				$metaBox = $('#ffm-metabox-editor'),
				name = $self.data('name'),
				type = $self.data('type'),
				data = {
					name: name,
					type: type,
					order: $formEditor.find('li').length + 1,
					action: 'give-form-fields_add_el'
				};

			$.post(ajaxurl, data, function (res) {
				$formEditor.append(res);
				Editor.makeSortable();
				$('.ffm-loading').fadeOut(); //hide loading
				$('.ffm-no-fields').hide(); //hide no fields placeholder
			});
		},

		/**
		 * Remove Form Field
		 * @param e
		 */
		removeFormField: function (e) {
			e.preventDefault();

			if (confirm('Are you sure you want to remove this form field?')) {
				$(this).closest('li').fadeOut(function () {
					$(this).remove();
				});
			}
		},

		/**
		 * Clone Field
		 *
		 * @param e
		 */
		cloneField: function (e) {
			e.preventDefault();

			var $div = $(this).closest('div');
			var $clone = $div.clone();

			//clear the inputs
			$clone.find('input').val('');
			$clone.find(':checked').attr('checked', '');
			$div.after($clone);
		},

		/**
		 * Remove Field
		 */
		removeField: function () {
			//check if it's the only item
			var $parent = $(this).closest('div');
			var items = $parent.siblings().andSelf().length;

			if (items > 1) {
				$parent.remove();
			}
		},

		/**
		 * Set Meta Key
		 */
		setMetaKey: function () {
			var $self = $(this);

			if ($self.hasClass('js-ffm-field-label')) {
				$fieldLabel = $self;
				$metaKey = $self.closest('.give-form-fields-rows').next().find('.js-ffm-meta-key');
			} else if ($self.hasClass('js-ffm-meta-key')) {
				$fieldLabel = $self.closest('.give-form-fields-rows').prev().find('.js-ffm-field-label');
				$metaKey = $self;
			} else {
				return false;
			}

			// only set meta key if input exists and is empty
			if ($metaKey.length && !$metaKey.val()) {

				var val = $fieldLabel
					.val() // get value of Field Label input
					.trim() // remove leading and trailing whitespace
					.toLowerCase() // convert to lowercase
					.replace(/[\s\-]/g, '_') // replace spaces and - with _
					.replace(/[^a-z0-9_]/g, ''); // remove all chars except lowercase, numeric, or _

				if (val.length > 200) {
					val = val.substring(0, 200);
				}

				if ( $metaKey.val() !== val ) {
					$metaKey.val( val ).blur();
				}
			}
		},

		/**
		 * Set Meta Key
		 */
		setEmailTag: function () {
			var $parent = $(this).closest('.give-form-fields-holder');

			$( '.give-form-field-email-tag-field', $parent ).val( '{meta_donation_' + $(this).val() + '}' );
		},

		/**
		 * Collapse
		 * @param e
		 */
		collapseEditFields: function (e) {
			e.preventDefault();

			$('ul#give-form-fields-editor').children('li').find('.collapse').collapse('toggle');
		},

		/**
		 * Check for duplicate Meta Keys
		 *
		 * @param e
		 */
		checkDuplicateMetaKeys: function (e) {
			$metaKey = $(e.target)
			justChecked = $metaKey.data('justChecked');

			// do not run if Meta Key is blank
			if ('' === $metaKey.val()) {
				return;
			}

			// prevent infinite alert loop after refocusing
			if (justChecked) {
				$metaKey.data('justChecked', false);
				return;
			}

			// get all Meta Key values in array and sort alphabetically
			var $allMetaKeys = $('#give-form-fields-editor').find('.js-ffm-meta-key').map(function () {
				return $(this).val();
			}).sort();

			// check for duplicates
			for (var i = 0; i < $allMetaKeys.length - 1; i++) {
				// only trigger alert if duplicate found and not blank
				if ($allMetaKeys[i + 1] == $allMetaKeys[i] && $allMetaKeys[i].length) {
					$metaKey.data('justChecked', true);
					alert(give_ffm_formbuilder.error_duplicate_meta);

					// refocus on duplicate Meta Key input
					setTimeout(function () {
						$metaKey.data('justChecked', false);
						$metaKey.focus();
					}, 50);

					return;
				}
			}
		},


		/**
		 * Sets the label title for enabled/disabled fields.
		 */
		showHideFFMFields: function() {
			if ( this.checked ) {
				$( this ).closest( '.hide-field-label' ).attr( 'title', give_ffm_formbuilder.hidden_field_enable );
			} else {
				$( this ).closest( '.hide-field-label' ).attr( 'title', give_ffm_formbuilder.hidden_field_disable );
			}
		}
	};

	// on DOM ready
	$(function () {
		Editor.init();
	});

})(jQuery);


/**
 * This JS is releated to repeatation fields
 *
 * @since 1.2.1
 */
jQuery( function ( $ ) {
	var give_ffm = {
		init: function () {
			$( 'body' ).on( 'click', 'span.ffm-clone-field', this.cloneField );
			$( 'body' ).on( 'click', 'span.ffm-remove-field', this.removeField );
		},
		cloneField: function ( e ) {
			e.preventDefault();

			var $div = $( this ).closest( 'tr' );
			var $clone = $div.clone();
			// console.log($clone);

			//clear the inputs
			$clone.find( 'input' ).val( '' );
			$clone.find( ':checked' ).attr( 'checked', '' );
			$div.after( $clone );
		},

		removeField: function () {
			//check if it's the only item
			var $parent = $( this ).closest( 'tr' );
			var items = $parent.siblings().andSelf().length;

			if ( items > 1 ) {
				$parent.remove();
			}
		}
	};

	give_ffm.init();
} );