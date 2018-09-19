<?php
/**
 * Attachment Uploader class
 *
 * @since   1.0.0
 * @package ffm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_FFM_Upload {

	function __construct() {

		add_action( 'wp_ajax_ffm_file_upload', array( $this, 'upload_file' ) );
		add_action( 'wp_ajax_nopriv_ffm_file_upload', array( $this, 'upload_file' ) );

		add_action( 'wp_ajax_ffm_file_del', array( $this, 'delete_file' ) );
		add_action( 'wp_ajax_nopriv_ffm_file_del', array( $this, 'delete_file' ) );
	}

	/**
	 * Upload File
	 *
	 * @param bool|false $image_only
	 */
	function upload_file( $image_only = false ) {

		$upload = array(
			'name'     => $_FILES['ffm_file']['name'],
			'type'     => $_FILES['ffm_file']['type'],
			'tmp_name' => $_FILES['ffm_file']['tmp_name'],
			'error'    => $_FILES['ffm_file']['error'],
			'size'     => $_FILES['ffm_file']['size']
		);

		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

		$attach = $this->handle_upload( $upload );

		if ( $attach['success'] ) {

			$response         = array( 'success' => true );
			$response['html'] = $this->attach_html( $attach['attach_id'], null, $upload );
			echo $response['html'];

		} else {
			echo __( 'Upload error', 'give-form-field-manager' );
		}

		exit;
	}

	/**
	 * Generic function to upload a file
	 *
	 * @param string     $upload_data file input field name
	 *
	 * @param bool|false $upload
	 *
	 * @return array
	 */
	function handle_upload( $upload_data, $upload = false ) {

		$uploaded_file = wp_handle_upload( $upload_data, array( 'test_form' => false ) );

		// If the wp_handle_upload call returned a local path for the image
		if ( isset( $uploaded_file['file'] ) ) {

			$file_loc  = $uploaded_file['file'];
			$file_name = basename( $upload_data['name'] );
			$file_type = wp_check_filetype( $file_name );

			$attachment = array(
				'post_mime_type' => $file_type['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			$attach_id   = wp_insert_attachment( $attachment, $file_loc );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );

			wp_update_attachment_metadata( $attach_id, $attach_data );

			return array( 'success' => true, 'attach_id' => $attach_id );
		}

		return array( 'success' => false, 'error' => $uploaded_file['error'] );
	}

	/**
	 * Attach HTML
	 *
	 * @param      $attach_id
	 * @param null $type
	 * @param null $upload
	 *
	 * @return string|void
	 */
	public static function attach_html( $attach_id, $type = null, $upload = null ) {
		if ( ! $type ) {
			$type = isset( $_GET['type'] ) ? $_GET['type'] : 'image';
		}

		$attachment = get_post( $attach_id );

		if ( ! $attachment ) {
			return;
		}

		if ( wp_attachment_is_image( $attach_id ) ) {
			$image = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
			$image = $image[0];
		} else {
			$image = false;
		}

		$admin_delete_button = sprintf( '<a href="%s" class="button button-small button-primary give-download-file">%s</a>', wp_get_attachment_url( $attach_id ), __( 'Download File', 'give-form-field-manager' ) );

		if ( ! $image ) {
			$upload_name = isset( $upload['name'] ) ? $upload['name'] : '';
			$upload_type = isset( $attachment->post_mime_type ) ? $attachment->post_mime_type : '';
			if ( empty( $upload_name ) ) {
				$upload_name = isset( $attachment->post_title ) ? $attachment->post_title : '';
			}

			$html = '<li class="give-li-wrap give-thumbnail give-non-image-li" style="width: 150px">';
			$html .= '<div class="give-attachment-name">' . $upload_name . ' - ' . $upload_type . '</div>';
			$html .= '<div class="caption">';
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				$html .= $admin_delete_button;
			}
			$html .= sprintf( '<a href="#" class="attachment-delete" data-attach_id="%d">%s</a>', $attach_id, __( 'Delete Permanently', 'give-form-field-manager' ) );
			$html .= sprintf( '<input type="hidden" name="ffm_files[%s][]" value="%d">', $type, $attach_id );
			$html .= '</div>';
			$html .= '</li>';
		} else {
			$html = '<li class="give-image-wrap give-thumbnail" style="width: 150px">';
			$html .= sprintf( '<div class="give-attachment-name"><img src="%s" alt="%s" /></div>', $image, esc_attr( $attachment->post_title ) );
			$html .= '<div class="caption">';
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				$html .= $admin_delete_button;
			}
			$html .= sprintf( '<a href="#" class="attachment-delete" data-attach_id="%d">%s</a>', $attach_id, __( 'Delete Permanently', 'give-form-field-manager' ) );
			$html .= '</div>';
			$html .= sprintf( '<input type="hidden" name="ffm_files[%s][]" value="%d">', $type, $attach_id );
			$html .= '</li>';
		}


		return $html;
	}

	/**
	 * Delete File
	 */
	function delete_file() {

		check_ajax_referer( 'ffm_nonce', 'nonce' );

		$attach_id  = isset( $_POST['attach_id'] ) ? intval( $_POST['attach_id'] ) : 0;
		$attachment = get_post( $attach_id );

		//post author or editor role
		if ( get_current_user_id() == $attachment->post_author || current_user_can( 'delete_private_pages' ) ) {
			wp_delete_attachment( $attach_id, true );
			echo 'success';
		}

		exit;
	}

	/**
	 * Associate File
	 *
	 * @param $attach_id
	 * @param $post_id
	 */
	function associate_file( $attach_id, $post_id ) {
		wp_update_post( array(
			'ID'          => $attach_id,
			'post_parent' => $post_id
		) );
	}

}