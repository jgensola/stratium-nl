/**
 * Give PDF Receipts Admin Settings JS
 */

jQuery.noConflict();
var give_pdf_vars;

(function( $ ) {

	$( function() {

		// Set up the vars.
		var give_pdf_receipts_enable_disable = $( 'input[name="give_pdf_receipts_enable_disable"]:radio' ),
			give_pdf_receipts_fields = $( '.pdf-receipts-fields' ),
			give_pdf_generation_method = $( 'input[name="give_pdf_generation_method"]' ),
			give_pdf_template = $( '#give_pdf_receipt_template' ),
			give_pdf_template_name = $( '#give_pdf_receipt_template_name' ),
			give_pdf_template_actions_wrap = $( '.give-pdf-action-wrap' ),
			template_id_prev = give_pdf_template.val(),
			is_template_name_change = false;

		/**
		 *  Show/Hide PDF Receipts field options.
		 */
		give_pdf_receipts_enable_disable.on( 'change', function() {

			var value = $( 'input[name="give_pdf_receipts_enable_disable"]:radio:checked' ).val();

			// If enable show other fields.
			if ( 'enabled' === value ) {
				give_pdf_receipts_fields.show();
			} else {
				give_pdf_receipts_fields.hide(); // Otherwise, hide rest of fields.
			}

			give_pdf_generation_method.change();

		} ).change();

		/**
		 * Switch PDF generation method
		 */
		give_pdf_generation_method.on( 'change', function() {

			var generation_method_val = $( 'input[name="give_pdf_generation_method"]' ).filter( ':checked' ).val(),
				give_pdf_receipts_global_value = $( 'input[name="give_pdf_receipts_enable_disable"]:radio:checked' ).val(),
				give_pdf_generation_method = $( '.give_pdf_generation_method' ),
				give_set_pdf_field = $( '.set-pdf-builder-option' ),
				give_custom_pdf_field = $( '.custom-pdf-builder-option' );

			if ( give_pdf_receipts_global_value !== 'enabled' ) {
				return false;
			}

			give_pdf_generation_method.show();

			switch ( generation_method_val ) {
				case 'custom_pdf_builder':

					// Hide set template fields
					give_set_pdf_field.hide();
					// Show custom template fields
					give_custom_pdf_field.show();

					break;
				case 'set_pdf_templates':

					// Show set template fields
					give_set_pdf_field.show();
					// Hide custom template fields
					give_custom_pdf_field.hide();
					break;
			}

		} ).change();

		/**
		 * Flag when template name input changed.
		 */
		give_pdf_template_name.on( 'input', function() {
			is_template_name_change = true;
		} );

		/**
		 * If template content changed this function shows the confirm window.
		 *
		 * @param template_id Template id
		 * @param callback Callback function
		 */
		function tinymce_change_confirm( template_id, callback ) {

			// If pdf title or content changed
			if ( is_template_name_change || tinyMCE.get( 'give_pdf_builder' ).isDirty() ) {
				if ( confirm( give_pdf_vars.not_saved ) ) {
					template_id_prev = template_id;
					callback();

					tinyMCE.get( 'give_pdf_builder' ).isNotDirty = 1;
					is_template_name_change = false;
					// Return previous selected option
				} else {
					give_pdf_template.val( template_id_prev );
				}
			} else {
				template_id_prev = template_id;
				callback();
			}
		}

		/**
		 * On PDF Receipt Dropdown - Template Change
		 *
		 * Prompt to save or output a new template
		 */
		give_pdf_template.on( 'change', function() {

			var template_id = give_pdf_template.val();
			give_pdf_template_actions_wrap.removeClass( 'give-pdf-actions-disabled' );
			give_pdf_template_name.attr( 'readonly', true );

			// Selecting existing template
			if ( 'create_new' !== template_id ) {
				tinymce_change_confirm( template_id, function() {
					load_template_fields( template_id );
					tinyMCE.get( 'give_pdf_builder' ).isNotDirty = 1;
				} );
				// Reset fields if creating a new template:
			} else {
				tinymce_change_confirm( template_id, function() {
					tinyMCE.get( 'give_pdf_builder' ).setContent( '' );
				} );
			}

			// Update title field.
			var template = give_pdf_template.find( 'option:selected' ),
				template_optgroup = template.parents( 'optgroup' ).attr( 'label' ),
				template_name = template.text();

			// Admin chose a template: provide them a unique title for their own template.
			if ( 'Starter Templates' === template_optgroup ) {

				// Disable Options.
				give_pdf_template_actions_wrap.addClass( 'give-pdf-actions-disabled' );

				// Get number of current custom receipt templates.
				var custom_templates_num = give_pdf_template.find( 'optgroup[label="My Templates"] option' ).length;

				give_pdf_template_name.attr( 'readonly', false ).val( give_pdf_vars.receipt_name_placeholder + ' ' + (custom_templates_num + 1) );

			} else {
				// Selected a previously created template.
				give_pdf_template_name.val( template_name );
			}

		} );

		/**
		 * Set template title and content
		 *
		 * @param template_id
		 */
		function load_template_fields( template_id ) {

			// Get template data
			$.post( ajaxurl, {
				action: 'get_builder_content',
				template_id: template_id
			}, function( data ) {

				// Update template content.
				tinyMCE.get( 'give_pdf_builder' ).setContent( data.post_content );

			}, 'json' );
		}

		/**
		 * Button to rename a template.
		 *
		 * @since 2.1
		 */
		$( '.give-pdf-action-rename' ).on( 'click', function( e ) {

			e.preventDefault();
			// Focus at the end of the
			give_pdf_template_name.focus();
			var prev_val = give_pdf_template_name.val();
			give_pdf_template_name.val( '' ).val( prev_val );

			give_pdf_template_name.attr( 'readonly', false ).focus();

		} );

		/**
		 * Button to delete a template.
		 *
		 * @since 2.1
		 */
		$( '.give-pdf-action-delete' ).on( 'click', function( e ) {

			e.preventDefault();

			// Can't delete when disabled
			if ( $( this ).hasClass( 'give-pdf-action-delete-disabled' ) ) {
				return false;
			}

			var template_id = give_pdf_template.val();

			// Can't delete the "Create New" option.
			if ( 'create_new' === template_id ) {
				return false;
			}

			// Confirm delete
			if ( window.confirm( give_pdf_vars.confirm_delete ) ) {

				// AJAX delete post ID.
				$.post( ajaxurl, {
					action: 'delete_pdf_template',
					template_id: template_id
				}, function( data ) {

					// Post was deleted.
					if ( data.post_content ) {
						give_pdf_template.find( 'option[value=' + template_id + ']' ).remove();
						give_pdf_template.change();
					}

				}, 'json' );

			}

		} );

	} );

})( jQuery );