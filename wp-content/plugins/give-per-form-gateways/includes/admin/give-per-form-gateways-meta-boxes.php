<?php
/**
 * Meta boxes.
 *
 * @since   1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register new meta box.
 *
 * @since       1.0.0
 * @return      void
 */
function give_per_form_gateways_add_meta_box() {
	add_meta_box(
		'per-form-gateways',
		__( 'Form Gateway Options', 'give-per-form-gateways' ),
		'give_per_form_gateways_render_meta_box',
		'give_forms',
		'side',
		'default'
	);
}

add_action( 'add_meta_boxes', 'give_per_form_gateways_add_meta_box' );


/**
 * Render the per-form emails meta box.
 *
 * @since       1.0.0
 * @global      object $post The WordPress object for a given post
 * @return      void
 */
function give_per_form_gateways_render_meta_box() {

	global $post;

	$per_form_gateways = get_post_meta( $post->ID, '_give_per_form_gateways', true );
	$all_gateways      = give_get_enabled_payment_gateways();

	/**
	 * Per form gateways top.
	 *
	 * @since 1.0
	 */
	do_action( 'give_per_form_gateways_meta_box_fields_top', $post->ID );
	?>

	<p><strong><?php _e( 'Allowed Gateways:', 'give-per-form-gateways' ); ?></strong></p>

	<?php foreach ( $all_gateways as $key => $gateway ) : ?>
		<p>
			<input type="checkbox" name="_give_per_form_gateways[<?php echo $key; ?>]"
			       id="_give_per_form_gateways[<?php echo $key; ?>]"
			       value="1"<?php echo( is_array( $per_form_gateways ) && array_key_exists( $key, $per_form_gateways ) ? ' checked' : '' ); ?> />
			<label for="_give_per_form_gateways[<?php echo $key; ?>]"><?php echo $gateway['admin_label']; ?></label>
		</p>

	<?php endforeach; ?>

	<p class="give-field-description"><?php _e( 'All <strong>checked</strong> gateways will be <strong>enabled</strong> for this donation form. If all are unchecked, the default gateways will be enabled.', 'give-per-form-gateways' ); ?></p>

	<?php
	/**
	 * Per form gateways bottom.
	 *
	 * @since 1.0
	 */
	do_action( 'give_per_form_gateways_meta_box_fields_bottom', $post->ID );

	//Nonce for security.
	wp_nonce_field( basename( __FILE__ ), 'give_per_form_gateways_nonce' );

}


/**
 * Save post meta when the save_post action is called.
 *
 * @since       1.0
 *
 * @param       int $post_id The ID of the post we are saving.
 *
 * @global      object $post The WordPress object for this post.
 *
 * @param $post_id
 *
 * @return mixed
 */
function give_per_form_gateways_meta_box_save( $post_id ) {
	global $post;

	// Bail if nonce can't be validated.
	if ( ! isset( $_POST['give_per_form_gateways_nonce'] ) || ! wp_verify_nonce( $_POST['give_per_form_gateways_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// Bail if this is an autosave or bulk edit.
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	// Bail if this is a revision.
	if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
		return $post_id;
	}

	// Bail if the current user shouldn't be here.
	if ( ! current_user_can( 'edit_give_forms', $post_id ) ) {
		return $post_id;
	}

	// The default fields that are saved.
	$fields = apply_filters( 'give_per_form_gateways_meta_box_fields_save', array(
		'_give_per_form_gateways_default',
		'_give_per_form_gateways'
	) );

	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			if ( is_string( $_POST[ $field ] ) ) {
				$new = esc_attr( $_POST[ $field ] );
			} else {
				$new = $_POST[ $field ];
			}

			$new = apply_filters( 'give_per_form_gateways_meta_box_save_' . $field, $new );

			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}
}

add_action( 'save_post', 'give_per_form_gateways_meta_box_save' );
