<?php
/**
 * Form Field Manager form generation and posting for add/edit post in frontend
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_FFM_Render_Form
 */
class Give_FFM_Render_Form {

	/**
	 * @var string
	 */
	static $meta_key = 'give-form-fields';

	/**
	 * @var string
	 */
	static $separator = '| ';

	/**
	 * @var string
	 */
	static $config_id = '_give-form-fields_id';

	/**
	 * Send json error message
	 *
	 * @param string $error
	 */
	function send_error( $error ) {

		$message = array(
			'success' => false,
			'error'   => $error,
		);

		echo json_encode( $message );

		die();
	}

	/**
	 * Search on multi dimensional array.
	 *
	 * @param array  $array
	 * @param string $key   name of key
	 * @param string $value the value to search
	 *
	 * @return array
	 */
	function search( $array, $key, $value ) {
		$results = array();

		if ( is_array( $array ) ) {
			if ( isset( $array[ $key ] ) && $array[ $key ] == $value ) {
				$results[] = $array;
			}

			foreach ( $array as $subarray ) {
				$results = array_merge( $results, $this->search( $subarray, $key, $value ) );
			}
		}

		return $results;
	}

	/**
	 * Get input meta fields separated as post vars, taxonomy and meta vars.
	 *
	 * @param int $form_id form id
	 *
	 * @return array
	 */
	public static function get_input_fields( $form_id ) {
		$form_vars = give_get_meta( $form_id, self::$meta_key, true );

		$ignore_lists = array( 'section', 'html' );
		$post_vars    = $meta_vars = $taxonomy_vars = array();

		if ( $form_vars == null ) {
			return array( array(), array(), array() );
		}

		foreach ( $form_vars as $key => $value ) {

			// ignore section break and HTML input type
			if ( in_array( $value['input_type'], $ignore_lists ) ) {
				continue;
			}

			// separate the post and custom fields
			if ( isset( $value['is_meta'] ) && $value['is_meta'] == 'yes' ) {
				$meta_vars[] = $value;
				continue;
			}

			$post_vars[] = $value;
		}

		return array( $post_vars, $taxonomy_vars, $meta_vars );
	}

	/**
	 * Prepare Meta Fields.
	 *
	 * @param $meta_vars
	 *
	 * @return array
	 */
	public static function prepare_meta_fields( $meta_vars ) {

		// Loop through custom fields skip files, put in a key => value paired array for later execution process repeatable fields separately if the input is array type, implode with separator in a field.
		$files          = array();
		$meta_key_value = array();
		$multi_repeated = array(); // multi repeated fields will in store duplicated meta key.

		foreach ( $meta_vars as $key => $value ) {

			// put files in a separate array, we'll process it later.
			if ( ( $value['input_type'] == 'file_upload' ) || ( $value['input_type'] == 'image_upload' ) ) {

				$files[] = array(
					'name'  => $value['name'],
					'value' => isset( $_POST['ffm_files'][ $value['name'] ] ) ? $_POST['ffm_files'][ $value['name'] ] : array(),
				);

				// process repeatable fields
			} elseif ( $value['input_type'] == 'repeat' ) {

				// if it is a multi column repeat field
				if ( isset( $value['multiple'] ) ) {

					// if there's any items in the array, process it
					if ( $_POST[ $value['name'] ] ) {

						$ref_arr = array();
						$cols    = count( $value['columns'] );
						$ar_vals = array_values( $_POST[ $value['name'] ] );
						$first   = array_shift( $ar_vals ); // first element
						$rows    = count( $first );

						// loop through columns
						for ( $i = 0; $i < $rows; $i ++ ) {

							// loop through the rows and store in a temp array
							$temp = array();
							for ( $j = 0; $j < $cols; $j ++ ) {

								$temp[] = $_POST[ $value['name'] ][ $j ][ $i ];
							}

							// store all fields in a row with self::$separator separated
							$ref_arr[] = implode( self::$separator, $temp );
						}

						// now, if we found anything in $ref_arr, store to $multi_repeated
						if ( $ref_arr ) {
							$multi_repeated[ $value['name'] ] = array_slice( $ref_arr, 0, $rows );
						}
					}
				} else {
					$meta_key_value[ $value['name'] ] = implode( self::$separator, $_POST[ $value['name'] ] );
				}
			} elseif ( ! empty( $_POST[ $value['name'] ] ) ) {

				// if it's an array, implode with this->separator
				if ( is_array( $_POST[ $value['name'] ] ) ) {
					$meta_key_value[ $value['name'] ] = implode( self::$separator, $_POST[ $value['name'] ] );
				} else {
					$meta_key_value[ $value['name'] ] = trim( $_POST[ $value['name'] ] );
				}
			}// End if().
		} // End foreach().

		return array( $meta_key_value, $multi_repeated, $files );
	}

	/**
	 * Render Form
	 *
	 * Handles the add post shortcode.
	 *
	 * @param            $form_id
	 * @param null       $post_id
	 * @param bool|false $preview
	 */
	function render_form( $form_id, $post_id = null, $preview = false ) {

		$form_vars     = give_get_meta( $form_id, self::$meta_key, true );
		$form_settings = give_get_meta( $form_id, 'give-form-fields_settings', true );

		if ( $form_vars ) {
			?>
			<fieldset id="give-ffm-section">

				<?php
				if ( ! $post_id ) {
					do_action( 'ffm_add_post_form_top', $form_id, $form_settings );
				} else {
					do_action( 'ffm_edit_post_form_top', $form_id, $post_id, $form_settings );
				}

				$this->render_items( $form_vars, $post_id, 'post', $form_id, $form_settings );

				if ( ! $post_id ) {
					do_action( 'ffm_add_post_form_bottom', $form_id, $form_settings );
				} else {
					do_action( 'ffm_edit_post_form_bottom', $form_id, $post_id, $form_settings );
				}
				?>

			</fieldset>
			<?php
		} // End if().

	}

	/**
	 * Render Item Before
	 *
	 * @param array $form_field List of form field attributes.
	 * @param int   $post_id    Post ID.
	 */
	function render_item_before( $form_field, $post_id ) {
		$label_exclude = apply_filters( 'give_ffm_label_exclude', array( 'section', 'html', 'action_hook', 'toc', 'hidden' ) );

		// use the name for element ID, if no name then use input type and random number for unique
		$el_name    = ! empty( $form_field['name'] ) ? $form_field['name'] : $form_field['input_type'] . '-' . rand( 1, 1000 );
		$class_name = ! empty( $form_field['css'] ) ? ' ' . $form_field['css'] : '';

		if ( 'hidden' !== $form_field['input_type'] ) {
			printf( '<div id="%s-wrap" class="form-row %s %s">', $el_name, $this->field_width_class( $form_field ), $class_name );
		}

		if ( isset( $form_field['input_type'] ) && ! in_array( $form_field['input_type'], $label_exclude ) ) {
			$this->label( $form_field, $post_id );
		}
	}

	/**
	 * Render item after
	 *
	 * @param $form_field
	 */
	function render_item_after( $form_field ) {
		if ( 'hidden' !== $form_field['input_type'] ) {
			echo '</div>';
		}
	}

	/**
	 * Render form items
	 *
	 * @param array  $form_vars     Form Variables.
	 * @param int    $post_id       Post ID.
	 * @param string $type          Type of the form. post or user.
	 * @param int    $form_id       Form ID.
	 * @param array  $form_settings Form Settings.
	 */
	function render_items( $form_vars, $post_id, $type = 'post', $form_id, $form_settings ) {

		$hidden_fields = array();

		foreach ( $form_vars as $key => $form_field ) {

			// Hide the field if it is hidden from the backend.
			if ( isset( $form_field['hide_field'] ) && 'on' === $form_field['hide_field'] ) {
				continue;
			}

			$this->render_item_before( $form_field, $post_id );

			switch ( $form_field['input_type'] ) {

				case 'text':
					$this->text( $form_field, $post_id, $type, $form_id );
					break;
				case 'hidden':
					$this->hidden( $form_field, $post_id, $type, $form_id );
					break;
				case 'textarea':
					$this->textarea( $form_field, $post_id, $type, $form_id );
					break;
				case 'select':
					$this->select( $form_field, false, $post_id, $type, $form_id );
					break;
				case 'multiselect':
					$this->select( $form_field, true, $post_id, $type, $form_id );
					break;
				case 'radio':
					$this->radio( $form_field, $post_id, $type, $form_id );
					break;
				case 'checkbox':
					$this->checkbox( $form_field, $post_id, $type, $form_id );
					break;
				case 'file_upload':
					$this->file_upload( $form_field, $post_id, $type );
					break;
				case 'url':
					$this->url( $form_field, $post_id, $type, $form_id );
					break;
				case 'email':
					$this->email( $form_field, $post_id, $type, $form_id );
					break;
				case 'repeat':
					$this->repeat( $form_field, $post_id, $type, $form_id );
					break;
				case 'section':
					$this->section( $form_field );
					break;
				case 'html':
					$this->html( $form_field );
					break;
				case 'action_hook':
					$this->action_hook( $form_field, $form_id, $post_id, $form_settings );
					break;
				case 'date':
					$this->date( $form_field, $post_id, $type, $form_id );
					break;
				case 'phone':
					$this->phone( $form_field, $post_id, $type, $form_id );
					break;

			}// End switch().

			$this->render_item_after( $form_field );

		} // End foreach().

		if ( $hidden_fields ) {
			foreach ( $hidden_fields as $field ) {
				printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $field['name'] ), esc_attr( $field['meta_value'] ) );
				echo "\r\n";
			}
		}
	}

	/**
	 * Prints required field asterisk
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	function required_mark( $attr ) {
		if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
			return ' <span class="give-required-indicator">*</span>';
		}

		return false;
	}

	/**
	 * Prints HTML5 required attribute
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	function required_html5( $attr ) {
		if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
			echo ' required="required"';
		}
	}

	/**
	 * Print required class name.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	function required_class( $attr ) {
		if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
			echo ' required';
		}

		return;
	}

	/**
	 * Print field width class name.
	 *
	 * @param array $attr List of form field attributes.
	 *
	 * @since 1.2
	 *
	 * @return string
	 */
	function field_width_class( $attr ) {

		$full_width_fields = array( 'section', 'repeat', 'html' );

		if ( ! empty( $attr['field_width'] ) ) {
			return 'give-ffm-form-row-responsive give-ffm-form-row-' . $attr['field_width'];
		} elseif ( in_array( $attr['input_type'], $full_width_fields ) ) {
			return 'give-ffm-form-row-full';
		}
	}

	/**
	 * Prints form input label.
	 *
	 * @param array $attr    List of form field attributes.
	 * @param int   $post_id Post ID.
	 */
	function label( $attr, $post_id = 0 ) {
		?>
		<label class="give-label" for="ffm-<?php echo isset( $attr['name'] ) ? $attr['name'] : 'cls'; ?>">
			<?php echo $attr['label'] . $this->required_mark( $attr ); ?>
			<?php $this->tooltip( $attr ); ?>
		</label>
		<?php
	}

	/**
	 * Check if its a meta field
	 *
	 * @param array $attr
	 *
	 * @return boolean
	 */
	function is_meta( $attr ) {
		if ( isset( $attr['is_meta'] ) && $attr['is_meta'] == 'yes' ) {
			return true;
		}

		return false;
	}

	/**
	 * Get a meta value.
	 *
	 * @param int    $object_id user_ID or post_ID
	 * @param string $meta_key
	 * @param string $type      post or user
	 * @param bool   $single
	 *
	 * @return string
	 */
	function get_meta( $object_id, $meta_key, $type = 'post', $single = true ) {
		if ( ! $object_id ) {
			return '';
		}

		if ( $type == 'post' ) {
			return give_get_meta( $object_id, $meta_key, $single );
		}

		return get_user_meta( $object_id, $meta_key, $single );
	}

	/**
	 * Get User Data.
	 *
	 * @param $user_id
	 * @param $field
	 *
	 * @return mixed
	 */
	function get_user_data( $user_id, $field ) {
		return get_user_by( 'id', $user_id )->$field;
	}

	/**
	 * Tooltip.
	 *
	 * @param $attr
	 */
	function tooltip( $attr ) {
		if ( isset( $attr['help'] ) && ! empty( $attr['help'] ) ) {
			?>
			<span class="give-tooltip give-icon give-icon-question"
			      data-tooltip="<?php echo esc_attr( $attr['help'] ); ?>"></span>
			<?php
		}
	}

	/**
	 * Prints a text field.
	 *
	 * @param array    $attr    List of form field attributes.
	 * @param int|null $post_id Post ID.
	 * @param string   $type    Type of form field.
	 * @param int|null $form_id Form ID.
	 *
	 * @updated 1.2
	 */
	function text( $attr, $post_id, $type = 'post', $form_id ) {
		// checking for user profile username
		$username = false;
		$taxonomy = false;
		if ( $post_id ) {
			if ( $this->is_meta( $attr ) ) {
				$value = $this->get_meta( $post_id, $attr['name'], $type );
			} else {
				// applicable for post tags
				if ( $type == 'post' && $attr['name'] == 'tags' ) {
					$post_tags = wp_get_post_tags( $post_id );
					$tagsarray = array();
					foreach ( $post_tags as $tag ) {
						$tagsarray[] = $tag->name;
					}
					$value    = implode( ', ', $tagsarray );
					$taxonomy = true;
				} elseif ( $type == 'post' ) {
					$value = get_post_field( $attr['name'], $post_id );
				} elseif ( $type == 'user' ) {
					$value = get_user_by( 'id', $post_id )->$attr['name'];
					if ( $attr['name'] == 'user_login' ) {
						$username = true;
					}
				}
			}
		} else {
			$value = $attr['default'];
		}
		if ( $attr['name'] == 'give_first' ) {
			if ( is_user_logged_in() ) {
				$user_data = get_userdata( get_current_user_id() );
				$value     = $user_data->first_name;
			}
		}
		if ( $attr['name'] == 'give_last' ) {
			if ( is_user_logged_in() ) {
				$user_data = get_userdata( get_current_user_id() );
				$value     = $user_data->last_name;
			}
		}
		?>
		<input class="textfield<?php echo esc_attr( $this->required_class( $attr ) ); ?>"
		       id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" type="text"
		       data-required="<?php echo esc_attr( $attr['required'] ); ?>"
		       data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>"
		       placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>"
			<?php if ( isset( $attr['maxlength'] ) && ! empty( $attr['maxlength'] ) ) { ?>
				maxlength="<?php echo esc_attr( $attr['maxlength'] ) ?>"
			<?php } ?>
			<?php echo $username ? 'disabled' : ''; ?> />
		<?php if ( $taxonomy ) { ?>
			<script type="text/javascript">
				jQuery( function( $ ) {
					$( 'fieldset.tags input[name=tags]' ).suggest( ajaxurl + '?action=ajax-tag-search&tax=post_tag', {
						delay: 500,
						minchars: 2,
						multiple: true,
						multipleSep: ', '
					} );
				} );
			</script>
		<?php } ?>

		<?php
	}

	/**
	 * Prints a hidden field.
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 *
	 * @since 1.2
	 */
	function hidden( $attr, $post_id, $type, $form_id ) {
		$value = $attr['default'];

		// Show values in different way for admin and front end.
		if ( is_admin() ) {
			echo $value;
		} else {
			?>
			<input class="hiddenfield"
			       id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" type="hidden"
			       name="<?php echo esc_attr( $attr['name'] ); ?>"
			       value="<?php echo esc_attr( $value ) ?>" />
			<?php
		}
	}

	/**
	 * Prints a textarea field
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 */
	function textarea( $attr, $post_id, $type, $form_id ) {
		$req_class = ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) ? 'required' : '';
		if ( $post_id ) {
			if ( $this->is_meta( $attr ) ) {
				$value = $this->get_meta( $post_id, $attr['name'], $type, true );
			} else {
				if ( $type == 'post' ) {
					$value = get_post_field( $attr['name'], $post_id );
				} else {
					$value = $this->get_user_data( $post_id, $attr['name'] );
				}
			}
		} else {
			$value = $attr['default'];
		}
		?>

		<?php if ( isset( $attr['insert_image'] ) && $attr['insert_image'] == 'yes' ) { ?>
			<div id="ffm-insert-image-container">
				<a class="ffm-button" id="ffm-insert-image" href="#">
					<span class="ffm-media-icon"></span>
					<?php _e( 'Insert Photo', 'give-form-field-manager' ); ?>
				</a>
			</div>
		<?php } ?>

		<?php
		$rich = isset( $attr['rich'] ) ? $attr['rich'] : '';
		if ( $rich == 'yes' ) {
			printf( '<span class="ffm-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $attr['required'], $attr['name'] );
			wp_editor( $value, $attr['name'], array(
				'editor_height' => $attr['rows'],
				'quicktags'     => false,
				'media_buttons' => false,
				'editor_class'  => $req_class . ' rich-editor',
			) );
		} elseif ( $rich == 'teeny' ) {
			printf( '<span class="ffm-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $attr['required'], $attr['name'] );
			wp_editor( $value, $attr['name'], array(
				'editor_height' => $attr['rows'],
				'quicktags'     => false,
				'media_buttons' => false,
				'teeny'         => true,
				'editor_class'  => $req_class . ' rich-editor',
			) );
		} else {
			?>
			<textarea class="textareafield<?php echo $this->required_class( $attr ); ?>"
			          id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" name="<?php echo $attr['name']; ?>"
			          data-required="<?php echo $attr['required'] ?>"
			          data-type="textarea"<?php $this->required_html5( $attr ); ?>
			          placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" rows="<?php echo $attr['rows']; ?>"
			          cols="<?php echo $attr['cols']; ?>"><?php echo esc_textarea( $value ) ?></textarea>
			<?php
		}
	}


	/**
	 * File Upload
	 *
	 * Prints a file upload field.
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 */
	function file_upload( $attr, $post_id, $type ) {

		$allowed_ext = '';
		$extensions  = give_ffm_allowed_extension();
		if ( is_array( $attr['extension'] ) ) {
			foreach ( $attr['extension'] as $ext ) {
				$allowed_ext .= $extensions[ $ext ]['ext'] . ',';
			}
		} else {
			$allowed_ext = '*';
		}

		$uploaded_items = $post_id ? $this->get_meta( $post_id, $attr['name'], $type, false ) : array();
		?>

		<div id="ffm-<?php echo $attr['name']; ?>-upload-container">
			<div class="ffm-attachment-upload-filelist">
				<a id="ffm-<?php echo $attr['name']; ?>-pickfiles" class="button file-selector"
				   href="#"><?php _e( 'Select File(s)', 'give-form-field-manager' ); ?></a>

				<?php printf( '<span class="ffm-file-validation" data-required="%s" data-type="file"></span>', $attr['required'] ); ?>

				<ul class="ffm-attachment-list give-thumbnails">
					<?php
					if ( $uploaded_items ) {
						foreach ( $uploaded_items as $attach_id ) {
							echo Give_FFM()->upload->attach_html( $attach_id, $attr['name'] );
						}
					}
					?>
				</ul>
			</div>
		</div><!-- .container -->


		<script type="text/javascript">
			jQuery( function( $ ) {
				new Give_FFM_Uploader( 'ffm-<?php echo $attr['name']; ?>-pickfiles', 'ffm-<?php echo $attr['name']; ?>-upload-container', <?php echo $attr['count']; ?>, '<?php echo $attr['name']; ?>', '<?php echo $allowed_ext; ?>', <?php echo $attr['max_size'] ?> );
			} );
		</script>
		<?php
	}

	/**
	 * Prints a select or multiselect field.
	 *
	 * @param array    $attr        List of attributes for hidden form field rendering.
	 * @param bool     $multiselect Whether the select field support multiselect or not?
	 * @param int|null $post_id     Post ID.
	 * @param string   $type        Post or User.
	 * @param int|null $form_id     Form ID.
	 */
	function select( $attr, $multiselect = false, $post_id, $type, $form_id ) {

		if ( $post_id ) {
			$selected = $this->get_meta( $post_id, $attr['name'], $type );
			$selected = $multiselect ? array_map( 'trim', explode( self::$separator, $selected ) ) : $selected;
		} else {
			$selected = isset( $attr['selected'] ) ? $attr['selected'] : '';
			$selected = $multiselect ? ( is_array( $selected ) ? $selected : array() ) : $selected;
		}

		$multi     = $multiselect ? ' multiple="multiple" ' : '';
		$data_type = $multiselect ? 'multiselect' : 'select';
		$css       = $multiselect ? ' class="multiselect"' : '';
		?>

		<select
				id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>"
				name="<?php echo $attr['name'] ?>[]"<?php echo $multi; ?>
				data-required="<?php echo $attr['required'] ?>"
				data-type="<?php echo $data_type; ?>"<?php $this->required_html5( $attr ); ?>>
			<?php
			echo $css;

			if ( ! empty( $attr['first'] ) ) {
				?>
				<option value=""><?php echo $attr['first']; ?></option>
				<?php
			}

			if ( $attr['options'] && count( $attr['options'] ) > 0 ) {
				foreach ( $attr['options'] as $option ) {
					$current_select = $multiselect ? selected( in_array( $option, $selected ), true, false ) : selected( $selected, $option, false );
					?>
					<option
							value="<?php echo esc_attr( $option ); ?>"<?php echo $current_select; ?>><?php echo $option; ?></option>
					<?php
				}
			}
			?>
		</select>
		<?php
	}

	/**
	 * Prints a radio field
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 */
	function radio( $attr, $post_id, $type, $form_id ) {
		$selected = isset( $attr['selected'] ) ? $attr['selected'] : '';
		if ( $post_id ) {
			$selected = $this->get_meta( $post_id, $attr['name'], $type, true );
		}
		?>
		<span data-required="<?php echo $attr['required'] ?>" data-type="radio"></span>

		<span id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" class="ffm-fields ffm-radio-field">
			<?php
			if ( $attr['options'] && count( $attr['options'] ) > 0 ) {
				foreach ( $attr['options'] as $option ) {
					?>

					<label>
						<input class="radiofield<?php echo esc_attr( $this->required_class( $attr ) ); ?>"
						       data-required="<?php echo $attr['required'] ?>"
						       name="<?php echo $attr['name']; ?>" type="radio"
						       value="<?php echo esc_attr( $option ); ?>"<?php checked( $selected, $option ); ?> />
						<?php echo $option; ?>
					</label>
					<?php
				}
			}
			?>
		</span>
		<?php
	}

	/**
	 * Prints a checkbox field
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 */
	function checkbox( $attr, $post_id, $type, $form_id ) {
		$selected = isset( $attr['selected'] ) ? $attr['selected'] : array();
		if ( $post_id ) {
			$selected = array_map( 'trim', explode( self::$separator, $this->get_meta( $post_id, $attr['name'], $type, true ) ) );
		}
		?>
		<span data-required="<?php echo $attr['required'] ?>" data-type="checkbox"></span>

		<span id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" class="ffm-fields ffm-checkbox-field">
			<?php
			if ( $attr['options'] && count( $attr['options'] ) > 0 ) {
				foreach ( $attr['options'] as $option ) {
					?>
					<label>
			            <input type="checkbox" name="<?php echo $attr['name']; ?>[]"
			                   value="<?php echo esc_attr( $option ); ?>"<?php echo in_array( $option, $selected ) ? ' checked="checked"' : ''; ?> />
						<?php echo $option; ?>
		            </label>
					<?php
				}
			}
			?>
		</span>
		<?php
	}

	/**
	 * Prints a url field
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 *
	 * @updated 1.2
	 */
	function url( $attr, $post_id, $type, $form_id ) {
		if ( $post_id ) {
			if ( $this->is_meta( $attr ) ) {
				$value = $this->get_meta( $post_id, $attr['name'], $type, true );
			} else {
				// must be user profile url
				$value = $this->get_user_data( $post_id, $attr['name'] );
			}
		} else {
			$value = $attr['default'];
		}
		?>
		<input id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" type="url" class="give-url"
		       data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?>
		       name="<?php echo esc_attr( $attr['name'] ); ?>"
		       placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>"
			<?php if ( isset( $attr['maxlength'] ) && ! empty( $attr['maxlength'] ) ) { ?>
				maxlength="<?php echo esc_attr( $attr['maxlength'] ) ?>"
			<?php } ?>
		/>
		<?php
	}

	/**
	 * Prints an email field.
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 *
	 * @updated 1.2
	 */
	function email( $attr, $post_id, $type = 'post', $form_id ) {
		if ( $post_id ) {
			if ( $this->is_meta( $attr ) ) {
				$value = $this->get_meta( $post_id, $attr['name'], $type, true );
			}
		} else {
			$value = $attr['default'];
		}
		?>
		<input id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" type="email" class="give_email"
		       data-required="<?php echo $attr['required'] ?>" data-type="email"<?php $this->required_html5( $attr ); ?>
		       name="<?php echo esc_attr( $attr['name'] ); ?>"
		       placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ); ?>"
			<?php if ( isset( $attr['maxlength'] ) && ! empty( $attr['maxlength'] ) ) { ?>
				maxlength="<?php echo esc_attr( $attr['maxlength'] ) ?>"
			<?php } ?>
		/>
		<?php
	}

	/**
	 * Prints a repeatable field
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 *
	 * @updated 1.2
	 */
	function repeat( $attr, $post_id, $type, $form_id ) {
		$maximum_repeat = $attr && ! empty( $attr['maximum_repeat'] ) && is_numeric( $attr['maximum_repeat'] ) ? $attr['maximum_repeat'] : 0;

		if ( isset( $attr['multiple'] ) ) {
			?>
			<table id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" class="give-repeater-table" data-max-repeat="<?php echo $maximum_repeat; ?>" data-field-type="repeat">
				<thead>
				<tr>
					<?php
					$num_columns = count( $attr['columns'] );
					foreach ( $attr['columns'] as $column ) {
						?>
						<th>
							<?php echo $column; ?>
						</th>
					<?php } ?>
					<th style="visibility: hidden;">
						<?php _e( 'Actions', 'give-form-field-manager' ); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$items = $post_id ? $this->get_meta( $post_id, $attr['name'], $type, false ) : array();

				if ( $items ) {
					foreach ( $items as $item_val ) {
						$column_vals = explode( self::$separator, $item_val );
						?>

						<tr>
							<?php
							for ( $count = 0; $count < $num_columns; $count ++ ) {
								?>
								<td>
									<input type="text" name="<?php echo $attr['name'] . '[' . $count . ']'; ?>[]"
									       value="<?php echo esc_attr( $column_vals[ $count ] ); ?>"
									       data-required="<?php echo $attr['required'] ?>"
									       data-type="text"<?php $this->required_html5( $attr ); ?>
										<?php if ( isset( $attr['maxlength'] ) && ! empty( $attr['maxlength'] ) ) { ?>
											maxlength="<?php echo esc_attr( $attr['maxlength'] ) ?>"
										<?php } ?>
									/>
								</td>
								<?php
							}
							?>
							<td>
								<span class="ffm-clone-field give-tooltip give-icon give-icon-plus"
								      data-tooltip="<?php esc_attr_e( 'Click here to add another field', 'give-form-field-manager' ); ?>"></span>
								<span class="ffm-remove-field give-tooltip give-icon give-icon-minus"
								      data-tooltip="<?php esc_attr_e( 'Click here to remove this field', 'give-form-field-manager' ); ?>"></span>
							</td>
						</tr>

						<?php
					} // End foreach().
				} else {
					?>
					<tr>
						<?php for ( $count = 0; $count < $num_columns; $count ++ ) { ?>
							<td>
								<input type="text" name="<?php echo $attr['name'] . '[' . $count . ']'; ?>[]"
								       data-required="<?php echo $attr['required'] ?>"
								       data-type="text"<?php $this->required_html5( $attr ); ?>
									<?php if ( isset( $attr['maxlength'] ) && ! empty( $attr['maxlength'] ) ) { ?>
										maxlength="<?php echo esc_attr( $attr['maxlength'] ) ?>"
									<?php } ?>
								/>
							</td>
						<?php } ?>
						<td>
							<span class="ffm-clone-field give-tooltip give-icon give-icon-plus"
							      data-tooltip="<?php esc_attr_e( 'Click here to add another field', 'give-form-field-manager' ); ?>"></span>
							<span class="ffm-remove-field give-tooltip give-icon give-icon-minus"
							      data-tooltip="<?php esc_attr_e( 'Click here to remove this field', 'give-form-field-manager' ); ?>"></span>
						</td>
					</tr>

				<?php }// End if().
				?>
				</tbody>
			</table>
			<?php
		} else {
			?>
			<table id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" class="give-repeater-table" data-max-repeat="<?php echo $maximum_repeat; ?>" data-field-type="repeat">
				<?php
				$items = $post_id ? explode( self::$separator, $this->get_meta( $post_id, $attr['name'], $type, true ) ) : array();
				if ( $items ) {
					foreach ( $items as $item ) {
						?>
						<tr>
							<td>
								<input type="text"
								       data-required="<?php echo $attr['required'] ?>"
								       data-type="text"<?php $this->required_html5( $attr ); ?>
								       name="<?php echo esc_attr( $attr['name'] ); ?>[]"
								       placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>"
								       value="<?php echo esc_attr( $item ) ?>"
									<?php if ( isset( $attr['maxlength'] ) && ! empty( $attr['maxlength'] ) ) { ?>
										maxlength="<?php echo esc_attr( $attr['maxlength'] ) ?>"
									<?php } ?>
								/>
							</td>
							<td>
								<span class="ffm-clone-field give-tooltip give-icon give-icon-plus"
								      data-tooltip="<?php esc_attr_e( 'Click here to add another field', 'give-form-field-manager' ); ?>"></span>
								<span class="ffm-remove-field give-tooltip give-icon give-icon-minus"
								      data-tooltip="<?php esc_attr_e( 'Click here to remove this field', 'give-form-field-manager' ); ?>"></span>
							</td>
						</tr>
						<?php
					} // End foreach().

				} else {
					?>

					<tr>
						<td>
							<input type="text"
							       data-required="<?php echo $attr['required'] ?>"
							       data-type="text"<?php $this->required_html5( $attr ); ?>
							       name="<?php echo esc_attr( $attr['name'] ); ?>[]"
							       placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>"
							       value="<?php echo esc_attr( $attr['default'] ) ?>"
								<?php if ( isset( $attr['maxlength'] ) && ! empty( $attr['maxlength'] ) ) { ?>
									maxlength="<?php echo esc_attr( $attr['maxlength'] ) ?>"
								<?php } ?>
							/>
						</td>
						<td>
							<span class="ffm-clone-field give-tooltip give-icon give-icon-plus"
							      data-tooltip="<?php esc_attr_e( 'Click here to add another field', 'give-form-field-manager' ); ?>"></span>
							<span class="ffm-remove-field give-tooltip give-icon give-icon-minus"
							      data-tooltip="<?php esc_attr_e( 'Click here to remove this field', 'give-form-field-manager' ); ?>"></span>
						</td>
					</tr>
					<?php
				} // End if().
				?>
			</table>
		<?php }// End if().
	}

	/**
	 * Prints a Section field
	 *
	 * @param array $attr
	 */
	function section( $attr ) {

		$classes = ( isset( $attr['class'] ) && ! empty( $attr['class'] ) ) ? $attr['class'] : '';

		if ( isset( $attr['label'] ) ) {

			echo '<legend class="give-ffm-section ' . $classes . '">' . $attr['label'] . '</legend>';

		} else {

			echo '<hr>';
		}

	}

	/**
	 * Prints a HTML field
	 *
	 * @param array $attr
	 */
	function html( $attr ) {
		echo do_shortcode( $attr['html'] );
	}

	/**
	 * Prints a action hook
	 *
	 * @param array    $attr
	 * @param int      $form_id
	 * @param int|null $post_id
	 * @param array    $form_settings
	 */
	function action_hook( $attr, $form_id, $post_id, $form_settings ) {
		if ( ! empty( $attr['label'] ) ) {
			do_action( $attr['label'], $form_id, $post_id, $form_settings );
		}
	}

	/**
	 * Prints a date field.
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 */
	function date( $attr, $post_id, $type, $form_id ) {
		$class_name = ( $attr['time'] == 'yes' ? 'give-ffm-timepicker' : 'give-ffm-datepicker' );
		$class_name .= ' give-ffm-datepicker-' . $attr["name"];
		$value = $post_id ? $this->get_meta( $post_id, $attr['name'], $type, true ) : '';
		?>
		<input id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>"
		       type="text"
		       data-dateformat="<?php echo $attr['format']; ?>"
		       data-timeformat="<?php echo isset( $attr['format_time'] ) ? $attr['format_time'] : 'HH:mm'; ?>"
		       class="give-ffm-date <?php echo $class_name; ?>"
		       data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?>
		       name="<?php echo esc_attr( $attr['name'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="30"/>
		<?php
	}

	/**
	 * Prints a phone field.
	 *
	 * @param array    $attr List of attributes for hidden form field rendering.
	 * @param int|null $post_id Post ID.
	 * @param string   $type Post or User.
	 * @param int|null $form_id Form ID.
	 *
	 * @updated 1.2
	 */
	function phone( $attr, $post_id, $type = 'post', $form_id ) {
		if ( $post_id ) {
			$value = $this->get_meta( $post_id, $attr['name'], $type, true );
		} else {
			$value = $attr['default'];
		}
		?>

		<input
				class="phone<?php echo esc_attr( $this->phone_format_class( $attr ) ); ?><?php echo esc_attr( $this->required_class( $attr ) ); ?>"
				id="<?php echo $this->get_form_field_id( $form_id, $attr ); ?>" type="tel"
				data-required="<?php echo esc_attr( $attr['required'] ); ?>"
				data-type="tel"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>"
				placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>" />

		<?php
	}

	/**
	 * Print phone format class name for JS masking
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	function phone_format_class( $attr ) {
		if ( isset( $attr['format'] ) && $attr['format'] == 'domestic' ) {
			return ' js-phone-domestic';
		}
	}

	/**
	 * Get Unique Form Field ID.
	 *
	 * @param int   $id         Form ID/Donation ID.
	 * @param array $form_field List of Form Field Attributes.
	 *
	 * @since 1.2.6
	 *
	 * @return string
	 */
	function get_form_field_id( $id, $form_field ) {
		return sprintf(
			'ffm-%s',
			$form_field['name']
		);
	}

}

new Give_FFM_Render_Form();
