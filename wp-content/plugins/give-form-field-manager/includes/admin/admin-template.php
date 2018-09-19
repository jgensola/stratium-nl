<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_FFM_Admin_Template
 *
 * The FFM Form builder template class.
 */
class Give_FFM_Admin_Template {

	/**
	 * Input name.
	 *
	 * @var string
	 */
	static $input_name = 'ffm_input';

	/**
	 * Legend of a form item.
	 *
	 * @param        $field_id
	 * @param string $title
	 * @param array  $values
	 * @param bool   $removeable
	 * @param bool   $custom
	 */
	public static function legend( $field_id, $title = 'Field Name', $values = array(), $removeable = true, $custom = false ) {
		if ( $custom ) {
			$title = '';
		}
		$field_label = $values ? '<strong>' . $values['label'] . '</strong>' : '';
		?>
		<div class="ffm-legend form-field-item-bar"
		     title="<?php _e( 'Drag and drop to re-arrange the field order.', 'give-form-field-manager' ); ?>"
		     data-position="left center">
			<div class="form-field-item-handle">

				<?php
				/**
				 * The below checkbox with the label class `hide-field-label`
				 * is used to show/hide FFM fields on the front-end.
				 */

				$tpl                 = '%s[%d][%s]';
				$hide_field_checkbox = sprintf( $tpl, self::$input_name, $field_id, 'hide_field' );
				$hidden              = $values && isset( $values['hide_field'] ) ? esc_attr( $values['hide_field'] ) : 'no';
				$label_text          = ( 'on' === $hidden ) ? 'This field is disabled. Click to enable it.' : 'Click to disable this field.'
				?>


                <span class="item-title">
                <?php
                if ( empty( $field_label ) ) {
	                echo '<em>' . __( 'Field Label not set', 'give-form-field-manager' ) . '</em>';
                } else {
	                echo $field_label;
                }
                ?>
                </span>
				<label class="hide-field-label" title="<?php echo esc_html( $label_text ); ?>">

					<input type="checkbox" name="<?php echo $hide_field_checkbox; ?>" <?php checked( $hidden, 'on' ); ?>>

					<!-- The span container holds the show/hide icon. Do not see it as an unused element! -->
					<span></span>
				</label>
				<span class="item-controls">
                    <span class="item-type"><?php echo $title; ?></span>
                    <a class="item-edit" href="#form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
                       data-toggle="collapse" aria-expanded="false"
                       aria-controls="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>" role="button">
	                    <?php echo __( 'Toggle Panel', 'give-form-field-manager' ); ?>
                    </a>
                </span>
			</div>
		</div> <!-- .ffm-legend -->
		<?php
	}

	/**
	 * Common Fields for a input field.
	 *
	 * Contains required, label, meta_key, help text, css class name.
	 *
	 * @param int   $id               field order.
	 * @param mixed $field_name_value Assign name to form field collapsible.
	 * @param bool  $custom_field     True, if it is a custom field.
	 * @param array $values           Assign values to fields in form field collapsible.
	 * @param array $toggle           Array of toggles.
	 *
	 * @updated 1.2
	 */
	public static function common( $id, $field_name_value = '', $custom_field = true, $values = array(), $toggle = array() ) {
		$tpl               = '%s[%d][%s]';

		$required_name     = sprintf( $tpl, self::$input_name, $id, 'required' );
		$field_name        = sprintf( $tpl, self::$input_name, $id, 'name' );
		$label_name        = sprintf( $tpl, self::$input_name, $id, 'label' );
		$is_meta_name      = sprintf( $tpl, self::$input_name, $id, 'is_meta' );
		$help_name         = sprintf( $tpl, self::$input_name, $id, 'help' );
		$css_name          = sprintf( $tpl, self::$input_name, $id, 'css' );
		$field_width_name  = sprintf( $tpl, self::$input_name, $id, 'field_width' );
		$required          = $values && isset( $values['required'] ) ? esc_attr( $values['required'] ) : 'no';
		$label_value       = $values && isset( $values['label'] ) ? esc_attr( $values['label'] ) : '';
		$help_value        = $values && isset( $values['help'] ) ? esc_textarea( $values['help'] ) : '';
		$css_value         = $values && isset( $values['css'] ) ? esc_attr( $values['css'] ) : '';
		$field_width_value = $values && isset( $values['field_width'] ) ? esc_attr( $values['field_width'] ) : '';
		$meta_key_disabled = '';

		if ( $custom_field && $values ) {
			$field_name_value = $values['name'];
		}

		do_action( 'give_ffm_add_field_to_common_form_element', $tpl, self::$input_name, $id, $values );

		if( ! empty( $field_name_value ) ) {
			$meta_key_disabled = ' readonly';
		}

		// Default Toggle Values.
		$defaults = array(
			'help'        => true,
			'css'         => true,
			'required'    => true,
			'field_width' => true,
		);

		// Update Toggle array with the values of each form field.
		$toggle = wp_parse_args( $toggle, $defaults );

		// If Required Toggle is enabled, then show Required Form Field.
		if ( ! empty( $toggle['required'] ) && true === $toggle['required'] ) {
			?>
			<div class="give-form-fields-rows required-field wide">
				<label>
					<?php
					_e( 'Required', 'give-form-field-manager' );

					echo Give()->tooltips->render_help( array(
						'label'    => __( 'Is this a required field? Required fields must be completed prior to a donation.', 'give-form-field-manager' ),
						'position' => 'right'
					) );
					?>
				</label>
				<div class="give-form-fields-sub-fields">
					<label>
						<input type="radio" name="<?php echo $required_name; ?>" value="yes"<?php checked( $required, 'yes' ); ?>> <?php _e( 'Yes', 'give-form-field-manager' ); ?>
					</label>
					<label>
						<input type="radio" name="<?php echo $required_name; ?>" value="no"<?php checked( $required, 'no' ); ?>> <?php _e( 'No', 'give-form-field-manager' ); ?>
					</label>
				</div>
			</div> <!-- .give-form-fields-rows -->
			<?php
		}
		?>

		<div class="give-form-fields-rows">
			<label><?php _e( 'Field Label', 'give-form-field-manager' ); ?>
				<span class="give-tooltip give-icon give-icon-question"
				      data-tooltip="<?php _e( 'Enter a label for this field. The label is like a title for the field.', 'give-form-field-manager' ); ?>"></label>
			<input class="js-ffm-field-label" type="text" data-type="label" name="<?php echo $label_name; ?>"
			       value="<?php echo $label_value; ?>">
		</div> <!-- .give-form-fields-rows -->

		<?php if ( $custom_field ) { ?>
			<div class="give-form-fields-rows">
				<label><?php _e( 'Meta Key', 'give-form-field-manager' ); ?>
					<span class="give-tooltip give-icon give-icon-question"
					      data-tooltip="<?php _e( 'The name of the meta key this field will save to in the database. This should not have any spaces or foreign characters.', 'give-form-field-manager' ); ?>"></label>
				<div class="give-meta-key-wrap">
					<input class="js-ffm-meta-key" type="text" name="<?php echo $field_name; ?>"
						<?php echo $meta_key_disabled; ?>
						   value="<?php echo $field_name_value; ?>" maxlength="200">

					<?php if( ! empty( $field_name_value ) ) { ?>
						<a class="give-icon-locked-anchor" href="javascript;">
							<i class="give-icon give-icon-locked"></i>
						</a>
					<?php } ?>

					<input type="hidden" name="<?php echo $is_meta_name; ?>" value="yes">
				</div>
			</div> <!-- .give-form-fields-rows -->
		<?php } else { ?>
			<input type="hidden" name="<?php echo $field_name; ?>" value="<?php echo $field_name_value; ?>">
			<input type="hidden" name="<?php echo $is_meta_name; ?>" value="no">
			<?php
		}

		// If Field Width Toggle is enabled, then show Field Width Form Field.
		if ( ! empty( $toggle['field_width'] ) && true === $toggle['field_width'] ) {
			?>
			<div class="give-form-fields-rows">
				<label><?php _e( 'Field Width', 'give-form-field-manager' ); ?>
					<span class="give-tooltip give-icon give-icon-question"
					      data-tooltip="<?php _e( 'Define width of the form field to manage your form', 'give-form-field-manager' ); ?>"></span></label>

				<select name="<?php echo $field_width_name; ?>">
					<option value="full"<?php selected( $field_width_value, 'full' ) ?>>
						<?php _e( 'Full Width', 'give-form-field-manager' ); ?>
					</option>
					<option value="half"<?php selected( $field_width_value, 'half' ) ?>>
						<?php _e( 'Half Width', 'give-form-field-manager' ); ?>
					</option>
					<option value="one-third"<?php selected( $field_width_value, 'one-third' ) ?>>
						<?php _e( 'One-Third Width', 'give-form-field-manager' ); ?>
					</option>
					<option value="two-third"<?php selected( $field_width_value, 'two-third' ) ?>>
						<?php _e( 'Two-Third Width', 'give-form-field-manager' ); ?>
					</option>
				</select>
			</div> <!-- .give-form-fields-rows -->
			<?php
		}

		// If Required and CSS Toggle is enabled, then show CSS Class Name Form Field.
		if ( ! empty( $toggle['required'] ) && true === $toggle['required'] && ! empty( $toggle['css'] ) && true === $toggle['css'] ) { ?>
			<div class="give-form-fields-rows">
				<label><?php _e( 'CSS Class Name', 'give-form-field-manager' ); ?>
					<span class="give-tooltip give-icon give-icon-question"
					      data-tooltip="<?php _e( 'Add a CSS class name for this field', 'give-form-field-manager' ); ?>"></label>
				<input type="text" name="<?php echo $css_name; ?>" value="<?php echo $css_value; ?>">
			</div> <!-- .give-form-fields-rows -->
		<?php } else { ?>
			<input type="hidden" name="<?php echo $css_name; ?>" value="">
			<?php
		}

		// If Help Toggle is enabled, then show Help Form Field.
		if ( ! empty( $toggle['help'] ) && true === $toggle['help'] ) {
			?>
			<div class="give-form-fields-rows wide">
				<label><?php _e( 'Help text', 'give-form-field-manager' ); ?>
					<span class="give-tooltip give-icon give-icon-question"
					      data-tooltip="<?php _e( 'Give the user some information about this field', 'give-form-field-manager' ); ?>"></label>
				<textarea name="<?php echo $help_name; ?>"><?php echo $help_value; ?></textarea>
			</div> <!-- .give-form-fields-rows -->
			<?php
		}

		self::common_email_tag_field();

		/**
		 * Contains required, label, meta_key, help text, css class name.
		 *
		 * @since 1.2.7
		 *
		 * @param string  $tpl        Field name format.
		 * @param mixed   $input_name Assign name to form field collapsible.
		 * @param integer $id         Field order.
		 * @param array   $values     Assign values to fields in form field collapsible.
		 */
		do_action( 'give_ffm_add_field_to_common_form_element_after', $tpl, self::$input_name, $id, $values );
	}

	/**
	 * Common fields for a text area.
	 *
	 * @param int   $id                 Form Field ID
	 * @param array $values             Assign value to fields of form fields collapsible.
	 * @param bool  $placeholder_toggle Toggle for displaying Placeholder field in form field collapsible.
	 * @param bool  $default_toggle     Toggle for displaying Default field in form field collapsible.
	 * @param bool  $maxlength_toggle   Toggle for displaying Maxlength field in form field collapsible.
	 *
	 * @updated 1.2
	 */
	public static function common_text( $id, $values = array(), $placeholder_toggle = true, $default_toggle = true, $maxlength_toggle = true ) {
		$tpl              = '%s[%d][%s]';
		$placeholder_name = sprintf( $tpl, self::$input_name, $id, 'placeholder' );
		$default_name     = sprintf( $tpl, self::$input_name, $id, 'default' );
		$maxlength_name   = sprintf( $tpl, self::$input_name, $id, 'maxlength' );

		$placeholder_value = $values && isset( $values['placeholder'] ) ? esc_attr( $values['placeholder'] ) : '';
		$default_value     = $values && isset( $values['default'] ) ? esc_attr( $values['default'] ) : '';
		$maxlength_value   = $values && isset( $values['maxlength'] ) ? esc_attr( $values['maxlength'] ) : '';

		if ( $placeholder_toggle ) {
			?>
			<div class="give-form-fields-rows">
				<label><?php _e( 'Placeholder text', 'give-form-field-manager' ); ?>
					<span class="give-tooltip give-icon give-icon-question"
					      data-tooltip="<?php esc_attr_e( 'Text for HTML5 placeholder attribute', 'give-form-field-manager' ); ?>"></label>
				<input type="text" name="<?php echo $placeholder_name; ?>" value="<?php echo $placeholder_value; ?>" />
			</div> <!-- .give-form-fields-rows -->
			<?php
		}

		if ( $default_toggle ) {
			?>
			<div class="give-form-fields-rows">
				<label><?php _e( 'Default value', 'give-form-field-manager' ); ?>
					<span class="give-tooltip give-icon give-icon-question"
					      data-tooltip="<?php esc_attr_e( 'The default value this field will have', 'give-form-field-manager' ); ?>"></label>
				<input type="text" name="<?php echo $default_name; ?>" value="<?php echo $default_value; ?>" />
			</div> <!-- .give-form-fields-rows -->
			<?php
		}

		if ( $maxlength_toggle ) {
			?>
			<div class="give-form-fields-rows">
				<label><?php _e( 'Max Length', 'give-form-field-manager' ); ?>
					<span class="give-tooltip give-icon give-icon-question"
					      data-tooltip="<?php esc_attr_e( 'Maxlength of this input field', 'give-form-field-manager' ); ?>"></label>
				<input type="text" name="<?php echo $maxlength_name; ?>" value="<?php echo $maxlength_value; ?>" />
			</div> <!-- .give-form-fields-rows -->
			<?php
		}
	}

	/**
	 * Common fields for a textarea.
	 *
	 * @param int   $id
	 * @param array $values
	 */
	public static function common_textarea( $id, $values = array() ) {
		$tpl              = '%s[%d][%s]';
		$rows_name        = sprintf( $tpl, self::$input_name, $id, 'rows' );
		$cols_name        = sprintf( $tpl, self::$input_name, $id, 'cols' );
		$rich_name        = sprintf( $tpl, self::$input_name, $id, 'rich' );
		$placeholder_name = sprintf( $tpl, self::$input_name, $id, 'placeholder' );
		$default_name     = sprintf( $tpl, self::$input_name, $id, 'default' );

		$rows_value        = $values && isset( $values['rows'] ) ? esc_attr( $values['rows'] ) : '5';
		$cols_value        = $values && isset( $values['cols'] ) ? esc_attr( $values['cols'] ) : '25';
		$rich_value        = $values && isset( $values['rich'] ) ? esc_attr( $values['rich'] ) : 'no';
		$placeholder_value = $values && isset( $values['placeholder'] ) ? esc_attr( $values['placeholder'] ) : '';
		$default_value     = $values && isset( $values['default'] ) ? esc_attr( $values['default'] ) : '';

		?>
		<div class="give-form-fields-rows">
			<label>
				<?php
				_e( 'Rows', 'give-form-field-manager' );

				echo Give()->tooltips->render_help( array(
					'label'    => __( 'The number of rows in the textarea. This affects the height of the textarea.', 'give-form-field-manager' ),
					'position' => 'right'
				) );
				?>
			</label>
			<input type="text" name="<?php echo $rows_name; ?>" value="<?php echo $rows_value; ?>" />
		</div> <!-- .give-form-fields-rows -->

		<div class="give-form-fields-rows">
			<label><?php _e( 'Columns', 'give-form-field-manager' ); ?>
				<span class="give-tooltip give-icon give-icon-question"
				      data-tooltip="<?php _e( 'Number of columns in textarea.', 'give-form-field-manager' ); ?>"></label>
			<input type="text" name="<?php echo $cols_name; ?>" value="<?php echo $cols_value; ?>" />
		</div> <!-- .give-form-fields-rows -->

		<div class="give-form-fields-rows">
			<label><?php _e( 'Placeholder text', 'give-form-field-manager' ); ?>
				<span class="give-tooltip give-icon give-icon-question"
				      data-tooltip="<?php _e( 'The text for an HTML5 placeholder attribute.', 'give-form-field-manager' ); ?>"></label>
			<input type="text" name="<?php echo $placeholder_name; ?>" value="<?php echo $placeholder_value; ?>" />
		</div> <!-- .give-form-fields-rows -->

		<div class="give-form-fields-rows wide">
			<label><?php _e( 'Default value', 'give-form-field-manager' ); ?>
				<span class="give-tooltip give-icon give-icon-question"
				      data-tooltip="<?php _e( 'The default value this field will have.', 'give-form-field-manager' ); ?>"></label>
			<textarea name="<?php echo $default_name; ?>" ><?php echo $default_value; ?></textarea>
		</div> <!-- .give-form-fields-rows -->

		<div class="give-form-fields-rows wide">
			<label><?php _e( 'Textarea', 'give-form-field-manager' ); ?></label>

			<div class="give-form-fields-sub-fields">
				<label><input type="radio" name="<?php echo $rich_name; ?>"
				              value="no"<?php checked( $rich_value, 'no' ); ?>> <?php _e( 'Normal', 'give-form-field-manager' ); ?>
				</label>
				<label><input type="radio" name="<?php echo $rich_name; ?>"
				              value="yes"<?php checked( $rich_value, 'yes' ); ?>> <?php _e( 'Rich textarea', 'give-form-field-manager' ); ?>
				</label>
				<label><input type="radio" name="<?php echo $rich_name; ?>"
				              value="teeny"<?php checked( $rich_value, 'teeny' ); ?>> <?php _e( 'Small Rich textarea', 'give-form-field-manager' ); ?>
				</label>
			</div>
		</div> <!-- .give-form-fields-rows -->
		<?php
	}


	/**
	 * Common email tag field
	 *
	 * Note: This is readonly field which helps to admin to key meta email tag
	 *
	 * @since  1.2.9
	 * @access public
	 *
	 */
	public static function common_email_tag_field() {
		?>
		<div class="give-form-fields-rows">
			<label><?php _e( 'Email Tag', 'give-form-field-manager' ); ?>
				<span class="give-tooltip give-icon give-icon-question"
					  data-tooltip="<?php esc_attr_e( 'You can use this email tag to show this data in email.', 'give-form-field-manager' ); ?>"></label>
			<input type="text" name="" value="" class="give-form-field-email-tag-field" readonly/>
		</div> <!-- .give-form-fields-rows -->
		<?php
	}

	/**
	 * Hidden field helper function.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public static function hidden_field( $name, $value = '' ) {
		printf( '<input type="hidden" name="%s" value="%s" />', self::$input_name . $name, $value );
	}

	/**
	 * Displays a radio custom field.
	 *
	 * @param int    $field_id
	 * @param string $name
	 * @param array  $values
	 */
	public static function radio_fields( $field_id, $name, $values = array() ) {
		$selected_name = sprintf( '%s[%d][selected]', self::$input_name, $field_id );
		$input_name    = sprintf( '%s[%d][%s]', self::$input_name, $field_id, $name );

		$selected_value = ( $values && isset( $values['selected'] ) ) ? $values['selected'] : '';

		if ( $values && $values['options'] > 0 ) {
			foreach ( $values['options'] as $key => $value ) {
				?>
				<div>
					<input type="radio" name="<?php echo $selected_name ?>"
					       value="<?php echo $value; ?>" <?php checked( $selected_value, $value ); ?>>
					<input type="text" name="<?php echo $input_name; ?>[]" value="<?php echo $value; ?>">

					<?php self::remove_button(); ?>
				</div>
				<?php
			}
		} else {
			?>
			<div>
				<input type="radio" name="<?php echo $selected_name ?>">
				<input type="text" name="<?php echo $input_name; ?>[]" value="">

				<?php self::remove_button(); ?>
			</div>
			<?php
		}
	}

	/**
	 * Displays a checkbox custom field
	 *
	 * @param int    $field_id
	 * @param string $name
	 * @param array  $values
	 */
	public static function common_checkbox( $field_id, $name, $values = array() ) {
		$selected_name = sprintf( '%s[%d][selected]', self::$input_name, $field_id );
		$input_name    = sprintf( '%s[%d][%s]', self::$input_name, $field_id, $name );

		$selected_value = ( $values && isset( $values['selected'] ) ) ? $values['selected'] : array();

		if ( $values && $values['options'] > 0 ) {
			foreach ( $values['options'] as $key => $value ) {
				?>
				<div>
					<input type="checkbox" name="<?php echo $selected_name ?>[]"
					       value="<?php echo $value; ?>"<?php echo in_array( $value, $selected_value ) ? ' checked="checked"' : ''; ?> />
					<input type="text" name="<?php echo $input_name; ?>[]" value="<?php echo $value; ?>">

					<?php self::remove_button(); ?>
				</div>
				<?php
			}
		} else {
			?>
			<div>
				<input type="checkbox" name="<?php echo $selected_name ?>[]">
				<input type="text" name="<?php echo $input_name; ?>[]" value="">

				<?php self::remove_button(); ?>
			</div>
			<?php
		}
	}

	/**
	 * Add/remove buttons for repeatable fields
	 *
	 * @return void
	 */
	public static function remove_button() {
		?>
		<a href="#add-another-choice" data-tooltip="<?php echo esc_attr( __( 'Add another choice', 'give-form-field-manager' ) ); ?>"
		   class="ffm-clone-field give-tooltip"><span class="give-icon give-icon-plus"></span></a>
		<a href="#remove-this-choice" data-tooltip="<?php echo esc_attr( __( 'Remove this choice', 'give-form-field-manager' ) ); ?>"
		   class="ffm-remove-field give-tooltip"><span class="give-icon give-icon-minus"></span></a>
		<?php
	}

	/**
	 * Get Buffered
	 *
	 * @param $func
	 * @param $field_id
	 * @param $label
	 *
	 * @return string
	 */
	public static function get_buffered( $func, $field_id, $label ) {
		ob_start();

		self::$func( $field_id, $label );

		return ob_get_clean();
	}


	/**
	 * Section Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function section_field( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		$title_name  = sprintf( '%s[%d][label]', self::$input_name, $field_id );
		$title_value = $values ? esc_attr( $values['label'] ) : '';
		$class_name  = sprintf( '%s[%d][class]', self::$input_name, $field_id );
		$class_value  = $values ? esc_attr( $values['class'] ) : '';
		?>
		<li class="custom-field text_field">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'section' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'section_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<div class="give-form-fields-rows wide">
					<label><?php _e( 'Section Name', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'The name of the section title.', 'give-form-field-manager' ); ?>"></span></label>

					<div class="give-form-fields-sub-fields">
						<input type="text" name="<?php echo $title_name; ?>"
						       value="<?php echo esc_attr( $title_value ); ?>" />

						<div class="description" style="margin-top: 8px;">
							<p class="give-field-description"><?php _e( 'Sections are helpful to break up sections of a form.', 'give-form-field-manager' ); ?></p>
						</div>
					</div>
				</div>
				<div class="give-form-fields-rows">
					<label><?php _e( 'CSS Class Name', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'Add a CSS class name for the section.', 'give-form-field-manager' ); ?>"></label>
					<input type="text" name="<?php  echo $class_name; ?>" value="<?php echo $class_value; ?>">
				</div> <!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}


	/**
	 * Text Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function text_field( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		?>
		<li class="custom-field text_field">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'text_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>
				<?php self::common_text( $field_id, $values ); ?>

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Hidden Form Field
	 *
	 * @param           $field_id  Form Field ID.
	 * @param           $label     Label of Form Field.
	 * @param array     $values    List of form field values.
	 * @param bool|true $removable Toggle to add option for removing the form field collapsible from the FFM holder.
	 *
	 * @since 1.2
	 */
	public static function hidden_form_field( $field_id, $label, $values = array(), $removable = true ) {
		?>
		<li class="custom-field hidden_form_field">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'hidden' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'hidden_form_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php
				self::common(
					$field_id,
					'',
					true,
					$values,
					array(
						'required'    => false,
						'help'        => false,
						'css'         => false,
						'field_width' => false,
					)
				);

				self::common_text( $field_id, $values, false, true, false );
				?>

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Textarea field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function textarea_field( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		?>
		<li class="custom-field textarea_field">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'textarea' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'textarea_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>
				<?php self::common_textarea( $field_id, $values ); ?>

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Radio Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function radio_field( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		?>
		<li class="custom-field radio_field">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'radio' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'radio_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>

				<div class="give-form-fields-rows wide">
					<label><?php _e( 'Options', 'give-form-field-manager' ); ?></label>

					<div class="give-form-fields-sub-fields give-form-fields-options-fields">
						<?php self::radio_fields( $field_id, 'options', $values ); ?>
					</div>
					<!-- .give-form-fields-sub-fields -->
				</div>
				<!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Checkbox Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function checkbox_field( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		?>
		<li class="custom-field checkbox_field">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'checkbox' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'checkbox_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>

				<div class="give-form-fields-rows wide">
					<label><?php _e( 'Options', 'give-form-field-manager' ); ?></label>

					<div class="give-form-fields-sub-fields give-form-fields-options-fields">
						<?php self::common_checkbox( $field_id, 'options', $values ); ?>
					</div>
					<!-- .give-form-fields-sub-fields -->
				</div>
				<!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Dropdown (select) Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function dropdown_field( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		$first_name  = sprintf( '%s[%d][first]', self::$input_name, $field_id );
		$first_value = $values ? $values['first'] : ' - select -';
		$help        = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'give-form-field-manager' ) );
		?>
		<li class="custom-field dropdown_field">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'select' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'dropdown_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>

				<div class="give-form-fields-rows">
					<label>
						<?php
						_e( 'Select Text', 'give-form-field-manager' );

						echo Give()->tooltips->render_help( array(
							'label'    => $help,
							'position' => 'right',
						) );
						?>
					</label>
					<input type="text" name="<?php echo $first_name; ?>" value="<?php echo $first_value; ?>">
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows wide">
					<label><?php _e( 'Options', 'give-form-field-manager' ); ?></label>

					<div class="give-form-fields-sub-fields give-form-fields-options-fields">
						<?php self::radio_fields( $field_id, 'options', $values ); ?>
					</div>
					<!-- .give-form-fields-sub-fields -->
				</div>
				<!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Multiple Select Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function multiple_select( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		$first_name  = sprintf( '%s[%d][first]', self::$input_name, $field_id );
		$first_value = $values ? $values['first'] : ' - select -';
		$help        = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'give-form-field-manager' ) );
		?>
		<li class="custom-field multiple_select">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'multiselect' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'multiple_select' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>

				<div class="give-form-fields-rows">
					<label>
						<?php
						_e( 'Select Text', 'give-form-field-manager' );

						echo Give()->tooltips->render_help( array(
							'label'    => $help,
							'position' => 'right',
						) );
						?>
					</label>
					<input type="text" name="<?php echo $first_name; ?>" value="<?php echo $first_value; ?>">
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows wide">
					<label><?php _e( 'Options', 'give-form-field-manager' ); ?></label>

					<div class="give-form-fields-sub-fields give-form-fields-options-fields">
						<?php self::common_checkbox( $field_id, 'options', $values ); ?>
					</div>
					<!-- .give-form-fields-sub-fields -->
				</div>
				<!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * File Upload Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function file_upload( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		$max_size_name   = sprintf( '%s[%d][max_size]', self::$input_name, $field_id );
		$max_files_name  = sprintf( '%s[%d][count]', self::$input_name, $field_id );
		$extensions_name = sprintf( '%s[%d][extension][]', self::$input_name, $field_id );

		$max_size_value   = $values ? $values['max_size'] : '1024';
		$max_files_value  = $values ? $values['count'] : '1';
		$extensions_value = $values ? $values['extension'] : array(
			'images',
			'pdf',
		);

		$extensions = give_ffm_allowed_extension();

		$help  = esc_attr( __( 'Enter maximum upload size limit in KB', 'give-form-field-manager' ) );
		$count = esc_attr( __( 'Number of images can be uploaded', 'give-form-field-manager' ) );
		?>
		<li class="custom-field custom_image">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'file_upload' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'file_upload' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>

				<div class="give-form-fields-rows">
					<label><?php _e( 'Max. file size', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php echo $help; ?>"></span></label>
					<input type="text" name="<?php echo $max_size_name; ?>" value="<?php echo $max_size_value; ?>">
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows">
					<label><?php _e( 'Max. files', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'How many files should the user be allowed to upload?', 'give-form-field-manager' ); ?>"></span></label>
					<input type="text" name="<?php echo $max_files_name; ?>" value="<?php echo $max_files_value; ?>">
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows wide">
					<label><?php _e( 'Allowed Upload File Types', 'give-form-field-manager' ); ?> <span
								class="give-tooltip give-icon give-icon-question"
								data-tooltip="<?php _e( 'Below are all the extensions allowed by donors to upload. Use extreme caution when allowing zip files, executables and large file sizes for important server and security reasons.', 'give-form-field-manager' ); ?>"></span></label>

					<div class="give-form-fields-sub-fields">
						<?php foreach ( $extensions as $key => $value ) {
							?>
							<label>
								<input type="checkbox" name="<?php echo $extensions_name; ?>"
								       value="<?php echo $key; ?>"<?php echo in_array( $key, $extensions_value ) ? ' checked="checked"' : ''; ?>>
								<?php printf( '%s (%s)', $value['label'], str_replace( ',', ', ', $value['ext'] ) ) ?>
							</label> <br />
						<?php } ?>
					</div>
				</div>
				<!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Website URL Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function website_url( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		?>
		<li class="custom-field website_url">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'url' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'website_url' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>
				<?php self::common_text( $field_id, $values ); ?>

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Email Address Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function email_address( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		?>
		<li class="custom-field eamil_address">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'email' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'email_address' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>
				<?php self::common_text( $field_id, $values ); ?>

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<!-- .give-form-fields-holder -->

		</li>
		<?php
	}

	/**
	 * Repeat Field
	 *
	 * @param int       $field_id  Form Field ID.
	 * @param string    $label     Label for this field.
	 * @param array     $values    List of Values.
	 * @param bool|true $removable Status to show Remove button for this field.
	 * @param bool|true $required  Status to show Required toggle for this field.
	 *
	 * @updated 1.2
	 */
	public static function repeat_field( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		$tpl = '%s[%d][%s]';

		$enable_column_name = sprintf( '%s[%d][multiple]', self::$input_name, $field_id );
		$column_names       = sprintf( '%s[%d][columns]', self::$input_name, $field_id );
		$has_column         = ( $values && isset( $values['multiple'] ) ) ? true : false;

		$placeholder_name    = sprintf( $tpl, self::$input_name, $field_id, 'placeholder' );
		$default_name        = sprintf( $tpl, self::$input_name, $field_id, 'default' );
		$maxlength_name      = sprintf( $tpl, self::$input_name, $field_id, 'maxlength' );
		$maximum_repeat_name = sprintf( $tpl, self::$input_name, $field_id, 'maximum_repeat' );

		$placeholder_value    = $values && ! empty( $values['placeholder'] ) ? esc_attr( $values['placeholder'] ) : '';
		$default_value        = $values && ! empty( $values['default'] ) ? esc_attr( $values['default'] ) : '';
		$maxlength_value      = $values && ! empty( $values['maxlength'] ) ? esc_attr( $values['maxlength'] ) : '';
		$maximum_repeat_value = $values && ! empty( $values['maximum_repeat'] ) ? esc_attr( $values['maximum_repeat'] ) : '';

		?>
		<li class="custom-field custom_repeater">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'repeat' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'repeat_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php
				self::common(
					$field_id,
					'',
					true,
					$values,
					array(
						'field_width' => false,
					)
				);
				?>

				<div class="give-form-fields-rows wide">
					<label><?php _e( 'Multiple Column', 'give-form-field-manager' ); ?></label>

					<div class="give-form-fields-sub-fields">
						<label><input type="checkbox" class="multicolumn"
						              name="<?php echo $enable_column_name ?>"<?php echo $has_column ? ' checked="checked"' : ''; ?>
						              value="true"> <?php _e( 'Enable Multi Column', 'give-form-field-manager' ); ?>
						</label>
					</div>
				</div>

				<div class="give-form-fields-rows<?php echo $has_column ? ' ffm-hide' : ''; ?>">
					<label><?php _e( 'Placeholder text', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'Text for HTML5 placeholder attribute', 'give-form-field-manager' ); ?>"></span></label>
					<input type="text" name="<?php echo $placeholder_name; ?>"
					       value="<?php echo $placeholder_value; ?>" />
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows<?php echo $has_column ? ' ffm-hide' : ''; ?>">
					<label><?php _e( 'Default value', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'The default value for this field.', 'give-form-field-manager' ); ?>"></span></label>
					<input type="text" name="<?php echo $default_name; ?>" value="<?php echo $default_value; ?>" />
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows">
					<label><?php _e( 'Maxlength', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'Maxlength of this input field.', 'give-form-field-manager' ); ?>"></span></label>
					<input type="text" name="<?php echo $maxlength_name; ?>" value="<?php echo $maxlength_value; ?>" />
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows">
					<label><?php _e( 'Maximum Number', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'The maximum number of times this field can be created. This allows you to set limits to the number of fields.', 'give-form-field-manager' );
						      ?>"></span></label>
					<input type="text" name="<?php echo $maximum_repeat_name; ?>" value="<?php echo $maximum_repeat_value; ?>" />
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows column-names<?php echo $has_column ? '' : ' ffm-hide'; ?> wide">
					<label><?php _e( 'Columns', 'give-form-field-manager' ); ?></label>

					<div class="give-form-fields-sub-fields give-form-fields-options-fields">
						<?php

						if ( $values && $values['columns'] > 0 ) {
							foreach ( $values['columns'] as $key => $value ) {
								?>
								<div>
									<input type="text" name="<?php echo $column_names; ?>[]"
									       value="<?php echo $value; ?>">

									<?php self::remove_button(); ?>
								</div>
								<?php
							}
						} else {
							?>
							<div>
								<input type="text" name="<?php echo $column_names; ?>[]" value="">

								<?php self::remove_button(); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Custom HTML Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function custom_html( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		$title_name = sprintf( '%s[%d][label]', self::$input_name, $field_id );
		$html_name  = sprintf( '%s[%d][html]', self::$input_name, $field_id );

		$title_value = $values ? esc_attr( $values['label'] ) : '';
		$html_value  = $values ? esc_attr( $values['html'] ) : '';
		?>
		<li class="custom-field custom_html">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'html' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'custom_html' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<div class="give-form-fields-rows wide">
					<label>
						<?php 
						_e( 'Title', 'give-form-field-manager' ); 
						
						echo Give()->tooltips->render_help( array(
							'label'    => __( 'The title field is only for admin field reference and will not be output on the frontend.', 'give-form-field-manager' ),
							'position' => 'right',
						) );
						?>
					</label>
					<input type="text" name="<?php echo $title_name; ?>"
					       value="<?php echo esc_attr( $title_value ); ?>" />
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows wide">
					<label><?php _e( 'HTML Code', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'Add html code in the textarea below. You can add images, text, links, and more!', 'give-form-field-manager' ); ?>"></span></label>
					<textarea name="<?php echo $html_name; ?>"
					          rows="10"><?php echo esc_html( wp_slash( $html_value ) ); ?></textarea>
				</div>

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Action Hook Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array     $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function action_hook( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		$title_name  = sprintf( '%s[%d][label]', self::$input_name, $field_id );
		$title_value = $values ? esc_attr( $values['label'] ) : '';
		?>
		<li class="custom-field custom_html">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'action_hook' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'action_hook' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<div class="give-form-fields-rows wide">
					<label><?php _e( 'Hook Name', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'The name of the hook', 'give-form-field-manager' ); ?>"></span></label>

					<div class="give-form-fields-sub-fields">
						<input type="text" name="<?php echo $title_name; ?>"
						       value="<?php echo esc_attr( $title_value ); ?>" />

						<div class="description" style="margin-top: 8px;">
							<p><?php echo sprintf( __( 'This form field is for developers to add their own custom %1$sWordPress actions%2$s. You can hook your own functions to this action and are provided 3 parameters: <code>$form_id</code>, <code>$post_id</code>, and <code>$form_settings</code>.', 'give-form-field-manager' ), '<a href="https://codex.wordpress.org/Plugin_API/Action_Reference" target="_blank">', '</a>' ); ?></p>

							<p><?php _e( '', 'give-form-field-manager' ); ?></p>
							<pre>
add_action( 'HOOK_NAME', 'your_function_name', 10, 3 );
function your_function_name( $form_id, $post_id, $form_settings ) {
    // do whatever you want
}
</pre>
						</div>
					</div>
					<!-- .give-form-fields-rows -->
				</div>
				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Date Field
	 *
	 * @param           $field_id
	 * @param           $label
	 * @param array $values
	 * @param bool|true $removable
	 * @param bool|true $required
	 */
	public static function date_field( $field_id, $label, $values = array(), $removable = true, $required = true ) {
		$date_format_name = sprintf( '%s[%d][format]', self::$input_name, $field_id );
		$time_format_name = sprintf( '%s[%d][format_time]', self::$input_name, $field_id );
		$time_name        = sprintf( '%s[%d][time]', self::$input_name, $field_id );

		$date_format_value = $values ? $values['format'] : 'mm/dd/yy';
		$time_format_value = isset( $values['format_time'] ) ? $values['format_time'] : 'h:mm tt';
		$time_value        = $values ? $values['time'] : 'no';
		?>
		<li class="custom-field custom_image">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'date' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'date_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>

				<div class="give-form-fields-rows">
					<label>
						<?php
						_e( 'Date Format', 'give-form-field-manager' );

						echo Give()->tooltips->render_help( array(
							'label'    => esc_attr(
								__( 'The format of the date field. For help with formatting, see Give documentation.', 'give-form-field-manager' )
							),
							'position' => 'right',
						) );
						?>
					</label>
					<input type="text" name="<?php echo $date_format_name; ?>"
					       value="<?php echo $date_format_value; ?>">
				</div>
				<!-- .give-form-fields-rows -->

				<div class="give-form-fields-rows">
					<label><?php _e( 'Time', 'give-form-field-manager' ); ?></label>

					<div class="give-form-fields-sub-fields">
						<label>
							<?php self::hidden_field( "[$field_id][time]", 'no' ); ?>
							<input type="checkbox" name="<?php echo $time_name ?>"
							       value="yes"<?php checked( $time_value, 'yes' ); ?> />
							<?php _e( 'Enable time input', 'give-form-field-manager' ); ?>
						</label>
					</div>
				</div>
				<div class="give-form-fields-rows">
					<label><?php _e( 'Time Format', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php echo esc_attr( __( 'The format of the datepicker\'s time field.', 'give-form-field-manager' ) ); ?>"></span></label>
					<input type="text" name="<?php echo $time_format_name; ?>"
					       value="<?php echo $time_format_value; ?>">
				</div>
				<!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<a class="item-delete submitdelete deletion button button-small"
					   data-field-id="<?php echo esc_attr( $field_id ); ?>"
					   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
				</div>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Give First
	 *
	 * @param       $field_id
	 * @param       $label
	 * @param array $values
	 */
	public static function give_first( $field_id, $label, $values = array() ) {
		if ( ! isset( $values['label'] ) || $values['label'] == '' ) {
			$values['label'] = $label;
		}
		?>
		<li class="give_first">
			<?php self::legend( $field_id, $label, $values, false, true ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'give_first' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, 'give_first', false, $values ); ?>
				<?php self::common_text( $field_id, $values ); ?>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Give Last Name Field
	 *
	 * @param       $field_id
	 * @param       $label
	 * @param array $values
	 */
	public static function give_last( $field_id, $label, $values = array() ) {
		if ( ! isset( $values['label'] ) || $values['label'] == '' ) {
			$values['label'] = $label;
		}
		?>
		<li class="give_last">
			<?php self::legend( $field_id, $label, $values, true, true ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'give_last' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, 'give_last', false, $values, array( 'help' => false ) ); ?>
				<?php self::common_text( $field_id, $values ); ?>
				<div class="form-field-actions">
					<a class="item-delete submitdelete deletion button button-small"
					   data-field-id="<?php echo esc_attr( $field_id ); ?>"
					   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
				</div>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}


	/**
	 * User Email
	 *
	 * @param       $field_id
	 * @param       $label
	 * @param array $values
	 */
	public static function user_email( $field_id, $label, $values = array() ) {
		Give_FFM_Admin_Template::give_email( $field_id, $label, $values = array() );
	}

	/**
	 * Give Email
	 *
	 * @param       $field_id
	 * @param       $label
	 * @param array $values
	 */
	public static function give_email( $field_id, $label, $values = array() ) {
		if ( ! isset( $values['label'] ) || $values['label'] == '' ) {
			$values['label'] = $label;
		}
		?>
		<li class="give_email">
			<?php self::legend( $field_id, $label, $values, false, true ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'email' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'give_email' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, 'give_email', false, $values, array( 'required' => false ) ); ?>
				<?php self::common_text( $field_id, $values ); ?>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}


	/**
	 * Description
	 *
	 * @param       $field_id
	 * @param       $label
	 * @param array $values
	 */
	public static function description( $field_id, $label, $values = array() ) {
		if ( ! isset( $values['label'] ) || $values['label'] == '' ) {
			$values['label'] = $label;
		}
		?>
		<li class="user_bio">
			<?php self::legend( $field_id, $label, $values ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'textarea' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'description' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, 'description', false, $values ); ?>
				<?php self::common_textarea( $field_id, $values ); ?>
			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

	/**
	 * Phone Field
	 *
	 * @param           $field_id  Form Field ID.
	 * @param           $label     Label of form field.
	 * @param array     $values    Values of form field.
	 * @param bool|true $removable Is Form field removable?
	 *
	 * @updated 1.2
	 */
	public static function phone_field( $field_id, $label, $values = array(), $removable = true ) {
		$tpl          = '%s[%d][%s]';
		$format_name  = sprintf( $tpl, self::$input_name, $field_id, 'format' );
		$format_value = $values && isset( $values['format'] ) ? esc_attr( $values['format'] ) : 'domestic';

		?>
		<li class="custom-field phone_field">
			<?php self::legend( $field_id, $label, $values, $removable ); ?>
			<?php self::hidden_field( "[$field_id][input_type]", 'phone' ); ?>
			<?php self::hidden_field( "[$field_id][template]", 'phone_field' ); ?>

			<div id="form-field-item-settings-<?php echo esc_attr( $field_id ); ?>"
			     class="give-form-fields-holder collapse">
				<?php self::common( $field_id, '', true, $values ); ?>
				<?php self::common_text( $field_id, $values, true, true, false ); ?>

				<div class="give-form-fields-rows">
					<label><?php _e( 'Phone Format', 'give-form-field-manager' ); ?>
						<span class="give-tooltip give-icon give-icon-question"
						      data-tooltip="<?php _e( 'Format in which the phone number is saved and displayed', 'give-form-field-manager' ); ?>"></span></label>

					<select name="<?php echo $format_name; ?>">
						<option
								value="domestic"<?php selected( $format_value, 'domestic' ) ?>><?php _e( '(###) ###-####', 'give-form-field-manager' ); ?></option>
						<option
								value="unformatted"<?php selected( $format_value, 'unformatted' ) ?>><?php _e( 'Unformatted', 'give-form-field-manager' ); ?></option>
					</select>
				</div> <!-- .give-form-fields-rows -->

				<div class="form-field-actions">
					<?php if ( $removable ) : ?>
						<a class="item-delete submitdelete deletion button button-small"
						   data-field-id="<?php echo esc_attr( $field_id ); ?>"
						   href="#"><?php echo __( 'Remove', 'give-form-field-manager' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /.form-field-actions -->

			</div>
			<!-- .give-form-fields-holder -->
		</li>
		<?php
	}

}
