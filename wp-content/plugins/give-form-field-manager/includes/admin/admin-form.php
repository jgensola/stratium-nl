<?php
/**
 * Admin Form UI Builder - Give_FFM_Admin_Form
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_FFM_Admin_Form
 *
 * Admin Form UI Builder
 */
class Give_FFM_Admin_Form {

	/**
	 * @var string
	 */
	private $form_data_key = 'give-form-fields';

	/**
	 * @var string
	 */
	private $form_data_required_key = 'give-form-required-fields';

	/**
	 * @var string
	 */
	private $form_settings_key = 'give-form-fields_settings';

	/**
	 * Add necessary actions and filters
	 */
	function __construct() {
		add_filter( 'post_updated_messages', array( $this, 'form_updated_message' ) );
		add_action( 'save_post', array( $this, 'save_form_meta' ), 1, 2 );

		// meta boxes
		add_action( 'add_meta_boxes_give_forms', array( $this, 'add_meta_boxes' ) );

		// ajax actions for post forms
		add_action( 'wp_ajax_give-form-fields_dump', array( $this, 'form_dump' ) );
		add_action( 'wp_ajax_give-form-fields_add_el', array( $this, 'ajax_post_add_element' ) );
	}

	/**
	 * Returns the default form fields available with the Give Form
	 *
	 * @return array
	 */
	function get_default_form_fields() {
		$data = array(
			1 => array(
				'input_type'  => 'email',
				'template'    => 'give_email',
				'required'    => 'yes',
				'label'       => 'Email',
				'name'        => 'give_email',
				'is_meta'     => 'no',
				'help'        => 'We will send the purchase receipt to this address.',
				'css'         => '',
				'placeholder' => '',
				'default'     => '',
				'maxlength'   => '120'
			),
			2 => array(
				'input_type'  => 'text',
				'template'    => 'give_first',
				'required'    => 'yes',
				'label'       => 'First Name',
				'name'        => 'give_first',
				'is_meta'     => 'no',
				'help'        => 'We will use this to personalize your account experience.',
				'css'         => '',
				'placeholder' => '',
				'default'     => '',
				'maxlength'   => ''
			),
			3 => array(
				'input_type'  => 'text',
				'template'    => 'give_last',
				'required'    => 'yes',
				'label'       => 'Last Name',
				'name'        => 'give_last',
				'is_meta'     => 'no',
				'help'        => 'We will use this as well to personalize your account experience.',
				'css'         => '',
				'placeholder' => '',
				'default'     => '',
				'maxlength'   => ''
			)
		);

		return $data;
	}

	/**
	 *  Checks if we are in admin edit post or new post
	 *
	 * @return  boolean
	 */
	function is_edit_page( $new_edit = null ) {
		global $pagenow;

		if ( ! is_admin() ) {
			return false;
		}

		if ( $new_edit == 'edit' ) {
			return in_array( $pagenow, array( 'post.php' ) );
		} elseif ( $new_edit == 'new' ) {
			return in_array( $pagenow, array( 'post-new.php' ) );
		} else {
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}
	}

	/**
	 * Form Updated Message
	 *
	 * @param $messages
	 *
	 * @return mixed
	 */
	function form_updated_message( $messages ) {
		$message = array(
			0  => '',
			1  => __( 'Donation form fields updated!', 'give-form-field-manager' ),
			2  => __( 'Custom field updated.', 'give-form-field-manager' ),
			3  => __( 'Custom field deleted.', 'give-form-field-manager' ),
			4  => __( 'Donation form updated.', 'give-form-field-manager' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Form restored to revision from %s', 'give-form-field-manager' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Donation form published.', 'give-form-field-manager' ),
			7  => __( 'Donation form fields saved!', 'give-form-field-manager' ),
			8  => __( 'Donation form submitted.', 'give-form-field-manager' ),
			9  => '',
			10 => __( 'Donation form draft updated.', 'give-form-field-manager' ),
		);

		$messages['give-form-fields'] = $message;

		return $messages;
	}

	/**
	 * Add meta boxes to form builders
	 *
	 * @return void
	 */
	function add_meta_boxes() {
		global $post;
		add_meta_box( 'ffm-metabox-editor', __( 'Form Field Manager', 'give-form-field-manager' ), array(
			$this,
			'metabox_post_form'
		), 'give_forms', 'normal', 'high' );
	}

	/**
	 * Metabox Post Form
	 *
	 * @param $post
	 */
	function metabox_post_form( $post ) {
		?>
		<div class="tab-content">
			<div id="ffm-metabox" class="group">
				<?php $this->edit_form_area(); ?>
			</div>
			<?php do_action( 'ffm_post_form_tab_content' ); ?>
		</div>
		<?php
	}

	/**
	 * Form elements for post form builder
	 *
	 * @return void
	 */
	function form_elements_post() {
		$title = __( 'Click to add to the editor', 'give-form-field-manager' );
		?>
		<div class="ffm-loading"><span class="give-icon give-icon-loading give-icon-spinner2 fa-spin"></span></div>
		<div class="give-form-fields-buttons">
			<?php do_action( 'give_form_field_buttons_before' ); ?>
			<button class="ffm-button button" data-name="custom_text" data-type="text" title="<?php echo $title; ?>"><?php _e( 'Text', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_hidden" data-type="hidden" title="<?php echo $title; ?>"><?php _e( 'Hidden', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_textarea" data-type="textarea" title="<?php echo $title; ?>"><?php _e( 'Textarea', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_select" data-type="select" title="<?php echo $title; ?>"><?php _e( 'Dropdown', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_date" data-type="date" title="<?php echo $title; ?>"><?php _e( 'Date', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_radio" data-type="radio" title="<?php echo $title; ?>"><?php _e( 'Radio', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_checkbox" data-type="checkbox" title="<?php echo $title; ?>"><?php _e( 'Checkbox', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_email" data-type="email" title="<?php echo $title; ?>"><?php _e( 'Email', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_phone" data-type="phone" title="<?php echo $title; ?>"><?php _e( 'Phone', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_file" data-type="file" title="<?php echo $title; ?>"><?php _e( 'File Upload', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_url" data-type="url" title="<?php echo $title; ?>"><?php _e( 'URL', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_multiselect" data-type="multiselect" title="<?php echo $title; ?>"><?php _e( 'Multi Select', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_repeater" data-type="repeat" title="<?php echo $title; ?>"><?php _e( 'Repeat Field', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_html" data-type="html" title="<?php echo $title; ?>"><?php _e( 'HTML', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="custom_section" data-type="section" title="<?php echo $title; ?>"><?php _e( 'Section', 'give-form-field-manager' ); ?></button>
			<button class="ffm-button button" data-name="action_hook" data-type="action" title="<?php echo $title; ?>"><?php _e( 'Do Action', 'give-form-field-manager' ); ?></button>
			<?php do_action( 'give_form_field_buttons_after' ); ?>
		</div>
		<?php
	}

	/**
	 * Saves the form settings
	 *
	 * @param int $post_id
	 * @param object $post
	 *
	 * @return int|void
	 */
	function save_form_meta( $post_id, $post ) {

		$required_fields = array();

		if ( ! isset( $_POST['give-form-fields_editor'] ) ) {
			return $post->ID;
		}

		if ( ! wp_verify_nonce( $_POST['give-form-fields_editor'], GIVE_FFM_BASENAME ) ) {
			return $post->ID;
		}

		// Is the user allowed to edit the post or page?
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		//Update form field placement
		if ( isset( $_POST['_give_ffm_placement'] ) ) {
			update_post_meta( $post->ID, '_give_ffm_placement', $_POST['_give_ffm_placement'] );
		}

		give_delete_meta( $post->ID, $this->form_data_required_key );

		// Sanitize and update ffm input
		if ( isset( $_POST['ffm_input'] ) ) {
			$ffm_input = $_POST['ffm_input'];
			$used_names = array_column( $ffm_input, 'name' );
			// loop through all user-defined input fields
			foreach( $ffm_input as $input => $field ) {

				if ( 'yes' === $field['required'] ) {
					$required_fields[ $field['name'] ] = $field['template'];
				}

				// check 'name' key because not all input types use it
				if ( ! empty( $field['name'] ) ) {
					// sanitize and truncate to 200 characters
					$sanitized_name = sanitize_title( $field['name'] );

					// replace '-' with '_' to match meta_key conventions
					$formatted_name = str_replace( '-', '_', $sanitized_name );
				} else {
					// field is blank, so let's set a generic fallback
					$generic_base = 'ffm_' . $ffm_input[ $input ]['input_type'];

					if ( in_array( $generic_base, $used_names ) ) {
						// add suffix in attempt to make name unique
						$suffix = 2;
						$formatted_name = $generic_base . '_' . $suffix;

						// increment suffix until we find unique, unused string
						while ( in_array( $formatted_name, $used_names ) ) {
							$suffix++;
							$formatted_name = $generic_base . '_' . $suffix;
						}
					} else {
						$formatted_name = $generic_base;
					}
				}

				// add to array of used names for future checks
				if ( ! in_array( $formatted_name, $used_names ) ) {
					$used_names[] = $formatted_name;
				}

				// update 'name' with sanitized, formatted value
				$ffm_input[ $input ]['name'] = $formatted_name;
			}

			update_post_meta( $post->ID, $this->form_data_key, $ffm_input );

			/**
			 * Empty radio, checkbox and file upload fields don't show up in $_POST array.
			 * This is why we save the required fields separately so that we can check
			 * if "required" radio, checkbox and file upload fields are filled or not.
			 */
			give_update_meta( $post->ID, $this->form_data_required_key, $required_fields );

		} else {
			//No form fields, set as empty meta so no blank fields leftover
			update_post_meta( $post->ID, $this->form_data_key, '' );
		}


	}

	/**
	 * Edit form elements area for post
	 *
	 * @global object $post
	 * @global string $pagenow
	 */
	function edit_form_area() {
		global $post, $pagenow, $current_screen;

		if ( $current_screen->post_type == 'give_forms' && $current_screen->action == 'add' ) {
			$form_inputs = '';
		} else {
			$form_inputs = give_get_meta( $post->ID, $this->form_data_key, true );
		} ?>

		<div class="form-edit-area-header">
			<?php $this->form_elements_post(); ?>

			<input type="hidden" name="give-form-fields_editor" id="give-form-fields_editor" value="<?php echo wp_create_nonce( GIVE_FFM_BASENAME ); ?>"/>

			<div class="form-edit-header-info">

				<p class="cmb2-metabox-description ffm-instruction-tip"><?php echo __( 'Click on a field above to add it to the donation form. ', 'give-form-field-manager' ); ?></p>

				<div class="form-field-options-wrap">

					<label for="field-placement">
						<?php
						_e( 'Form Field Location', 'give-form-field-manager' );

						echo Give()->tooltips->render_help( array(
							'label'    => __( 'Where would you like the fields to display on the form? Note, if you do not accept credit cards the fields will not display in those locations if set.', 'give-form-field-manager' ),
							'position' => 'top-left',
						) );
						?>
					</label>

					<select id="field-placement" name="_give_ffm_placement">
						<?php
						$default_value = give_get_meta( $post->ID, '_give_ffm_placement', true );

						//Setup available FFM positions
						$position_array = array(
							array(
								'value'  => 'give_donation_form_top',
								'option' => __( 'Above all fields', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_donation_form_bottom',
								'option' => __( 'Below all fields', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_before_donation_levels',
								'option' => __( 'Above donation fields', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_after_donation_levels',
								'option' => __( 'Below donation fields', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_payment_mode_top',
								'option' => __( 'Above payment options', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_payment_mode_bottom',
								'option' => __( 'Below payment options', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_donation_form_before_personal_info',
								'option' => __( 'Above personal info fields', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_donation_form_after_personal_info',
								'option' => __( 'Below personal info fields', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_donation_form_before_cc_form',
								'option' => __( 'Above credit card fields', 'give-form-field-manager' )
							),
							array(
								'value'  => 'give_donation_form_after_cc_form',
								'option' => __( 'Below credit card fields', 'give-form-field-manager' )
							),
						);

						foreach ( $position_array as $key => $value ) {

							$selected = '';

							if ( $value['value'] == $default_value ) {
								$selected = 'selected="selected"';
							}

							echo '<option value="' . $value['value'] . '" ' . $selected . ' >' . $value['option'] . '</option>';

						}
						?>
					</select>

				</div>

				<button class="button button-small ffm-collapse"><?php _e( 'Toggle Fields', 'give-form-field-manager' ); ?></button>

				<p class="cmb2-metabox-description ffm-help-tip"><?php echo sprintf( __( 'Need help? Check out the %1$s and %2$s.', 'give-form-field-manager' ), '<a href="https://givewp.com/documentation/add-ons/form-field-manager/" target="_blank">'.__('documentation', 'give-form-field-manager').'</a>', '<a href="https://givewp.com/support/" target="_blank" class="new-window">'.__('support', 'give-form-field-manager').'</a>' ); ?></p>

			</div>
		</div>
		<?php
		//No Fields
		if ( empty( $form_inputs ) ) { ?>
			<div class="ffm-no-fields">
				<span class="give-icon dashicons-give"></span>
				<p><?php _e( 'This donation form has no custom form fields.', 'give-form-field-manager' ); ?></p>
			</div>
		<?php } ?>
		<ul id="give-form-fields-editor" class="give-form-fields-editor unstyled">

			<?php
			if ( $form_inputs ) {
				$count = 0;
				foreach ( $form_inputs as $order => $input_field ) {
					$name               = ucwords( str_replace( '_', ' ', $input_field['template'] ) );
					$ffm_admin_template = new Give_FFM_Admin_Template();
					call_user_func_array( array( $ffm_admin_template, $input_field['template'] ), array(
						$count,
						$name,
						$input_field
					) );
					$count ++;
				}
			}
			?>
		</ul>

		<?php
	}

	/**
	 * Ajax Callback handler for inserting fields in forms
	 *
	 * @return void
	 */
	function ajax_post_add_element() {

		$name     = $_POST['name'];
		$type     = $_POST['type'];
		$field_id = $_POST['order'];

		switch ( $name ) {
			case 'custom_text':
				Give_FFM_Admin_Template::text_field( $field_id, __( 'Custom field: Text', 'give-form-field-manager' ) );
				break;

			case 'custom_hidden':
				Give_FFM_Admin_Template::hidden_form_field( $field_id, __( 'Custom field: Hidden', 'give-form-field-manager' ) );
				break;

			case 'custom_textarea':
				Give_FFM_Admin_Template::textarea_field( $field_id, __( 'Custom field: Textarea', 'give-form-field-manager' ) );
				break;

			case 'custom_section':
				Give_FFM_Admin_Template::section_field( $field_id, __( 'Custom field: Section', 'give-form-field-manager' ) );
				break;

			case 'custom_select':
				Give_FFM_Admin_Template::dropdown_field( $field_id, __( 'Custom field: Select', 'give-form-field-manager' ) );
				break;

			case 'custom_multiselect':
				Give_FFM_Admin_Template::multiple_select( $field_id, __( 'Custom field: Multiselect', 'give-form-field-manager' ) );
				break;

			case 'custom_radio':
				Give_FFM_Admin_Template::radio_field( $field_id, __( 'Custom field: Radio', 'give-form-field-manager' ) );
				break;

			case 'custom_checkbox':
				Give_FFM_Admin_Template::checkbox_field( $field_id, __( 'Custom field: Checkbox', 'give-form-field-manager' ) );
				break;

			case 'custom_file':
				Give_FFM_Admin_Template::file_upload( $field_id, __( 'Custom field: File Upload', 'give-form-field-manager' ) );
				break;

			case 'custom_url':
				Give_FFM_Admin_Template::website_url( $field_id, __( 'Custom field: URL', 'give-form-field-manager' ) );
				break;

			case 'custom_email':
				Give_FFM_Admin_Template::email_address( $field_id, __( 'Custom field: E-Mail', 'give-form-field-manager' ) );
				break;

			case 'custom_repeater':
				Give_FFM_Admin_Template::repeat_field( $field_id, __( 'Custom field: Repeat Field', 'give-form-field-manager' ) );
				break;

			case 'custom_html':
				Give_FFM_Admin_Template::custom_html( $field_id, __( 'HTML', 'give-form-field-manager' ) );
				break;

			case 'action_hook':
				Give_FFM_Admin_Template::action_hook( $field_id, __( 'Action Hook', 'give-form-field-manager' ) );
				break;

			case 'custom_date':
				Give_FFM_Admin_Template::date_field( $field_id, __( 'Custom Field: Date', 'give-form-field-manager' ) );
				break;

			case 'give_email':
				Give_FFM_Admin_Template::give_email( $field_id, __( 'Email', 'give-form-field-manager' ) );
				break;

			case 'custom_phone':
				Give_FFM_Admin_Template::phone_field( $field_id, __( 'Phone', 'give-form-field-manager' ) );
				break;

			default:
				do_action( 'ffm_admin_field_' . $name, $type, $field_id );
				break;
		}

		exit;
	}

}
