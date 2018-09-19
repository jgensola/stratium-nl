<?php
/**
 * AJAX Functions
 *
 * Process the AJAX actions.
 *
 * @package Give - PDF Receipts
 * @since   2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template data by template id
 */
function get_builder_content() {

	$template_id = $_POST['template_id'];
	$post        = get_post( $template_id );

	echo json_encode( array(
		'post_title'   => $post->post_title,
		'post_content' => $post->post_content,
	) );

	wp_die();
}

add_action( 'wp_ajax_get_builder_content', 'get_builder_content' );


/**
 * Delete PDF Receipt Templates.
 *
 * @since 2.1
 */
function delete_customized_pdf_template() {

	// Need the template (post ID) to continue.
	if ( ! isset( $_POST['template_id'] ) || empty( $_POST['template_id'] ) ) {
		return false;
	}

	// Ensure we're not deleting a template.
	$default_template_flag = get_post_meta( $_POST['template_id'], '_give_pdf_receipts_template', true );

	if ( $default_template_flag ) {
		return false;
	}

	$post = wp_delete_post( $_POST['template_id'], true );


	echo json_encode( array(
		'post_title'   => $post->post_title,
		'post_content' => $post->post_content,
	) );

	wp_die();
}

add_action( 'wp_ajax_delete_pdf_template', 'delete_customized_pdf_template' );