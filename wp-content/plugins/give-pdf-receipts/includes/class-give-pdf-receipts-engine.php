<?php

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Class Give_PDF_Receipts_Engine
 *
 * @since 2.0.4
 */
class Give_PDF_Receipts_Engine {

	/**
	 * Give_PDF_Receipts_Engine constructor.
	 */
	public function __construct() {

		add_action( 'give_generate_pdf_receipt', array( $this, 'generate_pdf_receipt' ) );
		add_action( 'give_donation_history_header_after', array( $this, 'donation_history_header' ) );
		add_action( 'give_donation_history_row_end', array( $this, 'donation_history_link' ), 10, 2 );
		add_action( 'give_payment_receipt_after', array( $this, 'receipt_shortcode_link' ), 10 );
		add_action( 'init', array( $this, 'verify_receipt_link' ), 10 );
		add_filter( 'give_payment_row_actions', array( $this, 'receipt_link' ), 10, 2 );

		// Register PDF Receipts Section and Settings on Give Donation form.
		add_action( 'give_metabox_form_data_settings', array( $this, 'give_pdf_receipts_settings' ), 10, 2 );
		add_filter( 'give_form_pdf_receipts_metabox_fields', array( $this, 'give_pdf_receipts_per_form_callback' ), 10, 2 );

		// Show PDF Receipt Download button under the Update Donation.
		add_action( 'give_view_donation_details_update_after', array( $this, 'donation_detail_download_button' ), 10, 1 );

	}

	/**
	 * Register 'PDF Receipts' section on edit donation form page.
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param array $setting section array.
	 * @param int   $post_id donation form id.
	 *
	 * @return array $settings return the pdf receipts sections array.
	 */
	public function give_pdf_receipts_settings( $setting, $post_id ) {

		// Appending the form PDF Receipts option section.
		$setting['form_pdf_receipts'] = apply_filters( 'give_form_pdf_receipts_options', array(
			'id'        => 'form_pdf_receipts_options',
			'title'     => __( 'PDF Receipts', 'give-pdf-receipts' ),
			'icon-html' => '<span class="pdf-receipts-icon"></span>',
			'fields'    => apply_filters( 'give_form_pdf_receipts_metabox_fields', array(), $post_id ),
		) );

		return $setting;
	}

	/**
	 * Register Setting fields for 'PDF Receipts' section in donation form edit page.
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param array $settings setting fields array.
	 * @param int   $form_id  Form ID.
	 *
	 * @return array
	 */
	public function give_pdf_receipts_per_form_callback( $settings, $form_id ) {

		$is_global = false; // Set Global flag.

		$settings = give_pdf_receipts_settings( $is_global, $form_id );

		return $settings;
	}

	/**
	 * Generate PDF Receipt
	 *
	 * Loads and stores all of the data for the payment.  The HTML2PDF class is
	 * instantiated and do_action() is used to call the receipt template which goes
	 * ahead and renders the receipt.
	 *
	 * @since 1.0
	 * @uses  HTML2PDF
	 * @uses  wp_is_mobile()
	 */
	public function generate_pdf_receipt() {

		// Sanity check: need transaction ID.
		if ( ! isset( $_GET['payment_id'] ) ) {
			return;
		}

		// Sanity check: Make sure the the receipt link is allowed.
		if ( ! $this->is_receipt_link_allowed( $_GET['payment_id'] ) ) {
			return;
		}

		do_action( 'give_pdf_generate_receipt', $_GET['payment_id'] );

		$give_pdf_payment      = get_post( $_GET['payment_id'] );
		$give_pdf_payment_meta = give_get_payment_meta( $_GET['payment_id'] );
		$payment_id            = isset( $_GET['payment_id'] ) ? sanitize_text_field( $_GET['payment_id'] ) : '';
		$form_id               = $give_pdf_payment_meta['form_id'];
		$is_per_form           = give_pdf_receipts_is_per_form_customized( $form_id );

		// Check if enabled Global/Per-Form.
		if ( $is_per_form ) {
			$page_size    = give_get_meta( $form_id, 'give_pdf_builder_page_size', true );
			$page_size    = ! empty( $page_size ) ? $page_size : array( 0, 0, 595.28, 841.89 );
			$template_id  = give_get_meta( $form_id, 'give_pdf_receipt_template', true );
			$give_options = give_pdf_receipts_per_form_settings( $form_id ); // Build Per-Form based settings array.
			$per_form     = true;
		} else {
			$give_options = give_get_settings();
			$page_size    = give_get_option( 'give_pdf_builder_page_size', array( 0, 0, 595.28, 841.89 ) );
			$template_id  = $give_options['give_pdf_receipt_template'];
			$per_form     = false;
		}

		// Must have payment ID to continue.
		if ( empty( $payment_id ) ) {
			give_die( __( 'Missing payment ID!', 'give-pdf-receipts' ) );
		}

		// If url parameters has _wpnonce=give_pdf_generate_receipt.
		$give_pdf_receipt_nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : null;
		$is_nonce               = is_admin() && wp_verify_nonce( $give_pdf_receipt_nonce, 'give_pdf_generate_receipt' );

		if ( $is_nonce ) {
			$give_pdf_buyer_info      = maybe_unserialize( $give_pdf_payment_meta['user_info'] );
			$give_pdf_payment_gateway = give_get_meta( $give_pdf_payment->ID, '_give_payment_gateway', true );
		} else {
			$give_pdf_buyer_info      = give_get_payment_meta_user_info( $give_pdf_payment->ID );
			$give_pdf_payment_gateway = give_get_payment_gateway( $give_pdf_payment->ID );
		}
		$give_pdf_payment_method = give_get_gateway_checkout_label( $give_pdf_payment_gateway );

		$company_name = isset( $give_options['give_pdf_company_name'] ) ? $give_options['give_pdf_company_name'] : '';

		$give_pdf_payment_date   = date_i18n( get_option( 'date_format' ), strtotime( $give_pdf_payment->post_date ) );
		$give_pdf_payment_status = give_get_payment_status( $give_pdf_payment, true );

		// WPML Support.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$lang = give_get_meta( $_GET['payment_id'], 'wpml_language', true );
			if ( ! empty( $lang ) ) {
				global $sitepress;
				$sitepress->switch_lang( $lang );
			}
		}

		/**
		 * DOMPDF Generation Method.
		 *
		 * Generate pdf using DOMPDF template.
		 */
		if (
			( isset( $give_options['give_pdf_generation_method'] )
			  && 'custom_pdf_builder' === $give_options['give_pdf_generation_method']
			) || empty( $give_options['give_pdf_generation_method'] )
		) {

			$template = get_post( $template_id );

			$template_content = '';
			if ( isset( $template ) ) {
				$template_content = ( $per_form ) ? give_get_meta( $form_id, 'give_pdf_builder', true ) : $template->post_content;
			}

			// Set PDF Preview. true for preview it in browser else download it.
			$pdf_preview = false;

			$is_div_layout = false;
			// Check whether content is div based or table based.
			if ( false !== strpos( $template_content, '<div' ) ) {
				$is_div_layout = true;
			}

			$html = give_pdf_get_compile_html(
				$template_content,
				$give_pdf_payment,
				$give_pdf_payment_method,
				$give_pdf_payment_status,
				$give_pdf_payment_meta,
				$give_pdf_buyer_info,
				$give_pdf_payment_date,
				$payment_id,
				$this->get_pdf_receipt_url( $give_pdf_payment->ID ),
				$is_div_layout,
				$pdf_preview
			);

			// Use DOMPDF if div based layout set for the Backward compatibility.
			if ( $is_div_layout ) {
				// Include dompdf library.
				include_once( GIVE_PDF_PLUGIN_DIR . 'vendor/autoload.php' );

				// Set options.
				$options = new Options();
				$options->set( 'isRemoteEnabled', true );
				$options->set( 'isHtml5ParserEnabled', true );
				$options->set( 'setIsRemoteEnabled', true );

				// Initialize Dompdf.
				$dompdf = new Dompdf( $options );

				$page_size = apply_filters( 'give_dompdf_paper_size', $page_size );

				$dompdf->setPaper( $page_size );

				$dompdf->loadHtml( $html );
				$dompdf->render();
				$dompdf->stream(
					apply_filters( 'give_pdf_receipt_filename_prefix', __( 'Receipt-', 'give-pdf-receipts' ) ) . give_pdf_get_payment_number( $give_pdf_payment->ID ),
					array( 'Attachment' => ! wp_is_mobile() )
				);
			}

		} else {

			/**
			 * TCPDF Generation Method.
			 *
			 * Generate pdf using legacy TCPDF default template.
			 */
			require_once GIVE_PLUGIN_DIR . '/includes/libraries/tcpdf/tcpdf.php';
			include_once( GIVE_PDF_PLUGIN_DIR . 'includes/class-give-tcpdf.php' );

			$give_pdf = new Give_PDF_Receipt( 'P', 'mm', 'A4', true, 'UTF-8', false );

			$give_pdf->SetDisplayMode( 'real' );
			$give_pdf->setJPEGQuality( 100 );

			$give_pdf->SetTitle( sprintf( ( $is_nonce ? __( 'Receipt %s', 'give-pdf-receipts' ) : __( 'Donation Receipt %s', 'give-pdf-receipts' ) ), give_pdf_get_payment_number( $give_pdf_payment->ID ) ) );
			$give_pdf->SetCreator( __( 'Give', 'give-pdf-receipts' ) );
			$give_pdf->SetAuthor( get_option( 'blogname' ) );

			$address_line_2_line_height = isset( $give_options['give_pdf_address_line2'] ) ? 6 : 0;

			if ( ! isset( $give_options['give_pdf_templates'] ) ) {
				$give_options['give_pdf_templates'] = 'default';
			}

			// Set Backward compatibility for the color templates.
			$color_templates = array(
				'blue',
				'green',
				'orange',
				'pink',
				'purple',
				'red',
				'yellow',
			);
			if ( in_array( $give_options['give_pdf_templates'], $color_templates, true ) ) {
				$give_options['give_pdf_templates'] = 'default';
			}
			do_action( 'give_pdf_template_' . $give_options['give_pdf_templates'], $give_pdf, $give_pdf_payment, $give_pdf_payment_meta, $give_pdf_buyer_info, $give_pdf_payment_gateway, $give_pdf_payment_method, $address_line_2_line_height, $company_name, $give_pdf_payment_date, $give_pdf_payment_status );

			if ( ob_get_length() ) {
				if ( $is_nonce ) {
					ob_clean();
				}
				ob_end_clean();
			}

			$give_pdf->Output( apply_filters( 'give_pdf_receipt_filename_prefix', __( 'Receipt-', 'give-pdf-receipts' ) ) . give_pdf_get_payment_number( $give_pdf_payment->ID ) . '.pdf', wp_is_mobile() ? 'I' : 'D' );
		}

		die(); // Stop the rest of the page from processing and being sent to the browser.
	}


	/**
	 * Donation History Page Table Heading
	 *
	 * Appends to the table header (<thead>) on the Purchase History page for the
	 * Receipt column to be displayed
	 *
	 * @since 1.0
	 */
	function donation_history_header() {
		echo '<th class="give_receipt">' . __( 'Receipt', 'give-pdf-receipts' ) . '</th>';
	}

	/**
	 * Outputs the Receipt link.
	 *
	 * Adds the receipt link to the [donation_history] shortcode underneath the previously created "Receipt" header.
	 *
	 * @since 1.0
	 *
	 * @param int   $post_id       Payment post ID.
	 * @param array $donation_data All the donation data.
	 */
	function donation_history_link( $post_id, $donation_data ) {
		if ( ! $this->is_receipt_link_allowed( $post_id ) || give_pdf_receipts_disable( $post_id ) ) {
			echo apply_filters( 'give_pdf_receipts_receipt_not_allowed_td', '<td>-</td>' );

			return;
		}

		echo '<td class="give_receipt"><a class="give_receipt_link" title="' . give_pdf_receipts_download_pdf_text( false ) . '" href="' . esc_url( $this->get_pdf_receipt_url(
				$post_id ) ) . '">' . give_pdf_receipts_download_pdf_text() . '</td>';
	}

	/**
	 * Verify Receipt Link.
	 *
	 * Verifies the receipt link submitted from the front-end.
	 *
	 * @since 1.0
	 */
	public function verify_receipt_link() {
		if ( isset( $_GET['payment_id'] ) && isset( $_GET['email'] ) && isset( $_GET['payment_key'] ) ) {

			if ( ! $this->is_receipt_link_allowed( $_GET['payment_id'] ) ) {
				return;
			}

			$key   = $_GET['payment_key'];
			$email = $_GET['email'];

			$meta_query = array(
				'relation' => 'OR',
				array(
					'key'   => '_give_payment_purchase_key',
					'value' => $key,
				),
				array(
					'key'   => '_give_payment_donor_email',
					'value' => $email,
				),
			);

			$payments = new Give_Payments_Query( array(
				'meta_query' => $meta_query,
			) );

			if ( $payments ) {
				$this->generate_pdf_receipt();
			} else {
				wp_die( __( 'The receipt that you requested was not found.', 'give-pdf-receipts' ), __( 'Receipt Not Found', 'give-pdf-receipts' ) );
			}
		}
	}

	/**
	 * Receipt Shortcode Receipt Link.
	 *
	 * Adds the receipt link to the [give_receipt] shortcode.
	 *
	 * @since 1.0.4
	 *
	 * @param object $payment All the payment data
	 */
	public function receipt_shortcode_link( $payment ) {

		// Sanity check.
		if ( ! $this->is_receipt_link_allowed( $payment->ID ) ) {
			return;
		}

		// Bail out, if PDF Receipt disable from Per-Form or disable globally (if globally enabled).
		if ( give_pdf_receipts_disable( $payment->ID ) ) {
			return;
		}
		?>
		<tr>
			<td><strong><?php _e( 'Receipt', 'give-pdf-receipts' ); ?>:</strong></td>
			<td>
				<a class="give_receipt_link" title="<?php echo give_pdf_receipts_download_pdf_text( false ); ?>"
				   href="<?php echo esc_url( $this->get_pdf_receipt_url( $payment->ID ) ); ?>"><?php echo give_pdf_receipts_download_pdf_text(); ?>
				</a>
			</td>
		</tr>
		<?php
	}

	/**
	 * Check is receipt link is allowed.
	 *
	 * @since  1.0
	 * @access private
	 * @global    $give_options
	 *
	 * @param int $id Payment ID to verify total
	 *
	 * @return bool
	 */
	public function is_receipt_link_allowed( $id = null ) {

		$ret = true;

		if ( ! give_is_payment_complete( $id ) ) {
			$ret = false;
		}

		return apply_filters( 'give_pdf_is_receipt_link_allowed', $ret, $id );
	}


	/**
	 * Gets the Receipt URL.
	 *
	 * Generates an receipt URL and adds the necessary query arguments.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment post ID
	 *
	 * @return string $receipt Receipt URL
	 */
	public function get_pdf_receipt_url( $payment_id ) {

		$give_pdf_params = array(
			'payment_id'  => $payment_id,
			'email'       => urlencode( give_get_payment_user_email( $payment_id ) ),
			'payment_key' => give_get_payment_key( $payment_id ),
		);

		$receipt = esc_url( add_query_arg( $give_pdf_params, home_url() ) );

		return $receipt;
	}


	/**
	 * Creates Link to Download the Receipt.
	 *
	 * Creates a link on the Payment History admin page for each payment to
	 * allow the ability to download a receipt for that payment.
	 *
	 * @since 1.0
	 *
	 * @param array  $row_actions      All the row actions on the Payment History page
	 * @param object $give_pdf_payment Payment object containing all the payment data
	 *
	 * @return array Modified row actions with Download Receipt link
	 */
	public function receipt_link( $row_actions, $give_pdf_payment ) {
		$row_actions_pdf_receipt_link = array();

		$give_pdf_generate_receipt_nonce = wp_create_nonce( 'give_pdf_generate_receipt' );

		if ( $this->is_receipt_link_allowed( $give_pdf_payment->ID ) && ! give_pdf_receipts_disable( $give_pdf_payment->ID ) ) {
			$row_actions_pdf_receipt_link = array(
				'receipt' => '<a href="' . esc_url( add_query_arg( array(
						'give-action' => 'generate_pdf_receipt',
						'payment_id'  => $give_pdf_payment->ID,
						'_wpnonce'    => $give_pdf_generate_receipt_nonce,
					) ) ) . '">' . give_pdf_receipts_download_pdf_text( false ) . '</a>',
			);
		}

		return array_merge( $row_actions, $row_actions_pdf_receipt_link );
	}

	/**
	 * Per-form set pdf preview button.
	 *
	 * @since 2.1.0
	 *
	 * @param array $field Field array.
	 */
	public function per_form_set_pdf_preview_button( $field ) {
		global $thepostid, $post;

		// Get the Donation form id.
		$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

		// Get the styles if passed with the field array.
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';

		// Get the option value by field and donation form id.
		$field['value'] = give_get_field_value( $field, $thepostid );

		// Generate name for option field.
		$field['name'] = isset( $field['name'] ) ? $field['name'] : $field['id'];

		?>
		<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
			<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
			<a href="<?php echo esc_url( add_query_arg( array( 'give_pdf_receipts_action' => 'preview_set_pdf_template', 'form_id' => $thepostid, ), admin_url() ) ); ?>"
			   class="button-secondary" target="_blank"
			   title="<?php _e( 'Preview Set PDF Template', 'give-pdf-receipts' ); ?> "><?php _e( 'Preview Set PDF Template', 'give-pdf-receipts' ); ?></a>
			<?php
			echo give_get_field_description( $field );
			?>
		</p>
		<?php
	}

	/**
	 * PDF Receipt Download button under the Update Donation
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param int $payment_id Donation ID.
	 */
	public function donation_detail_download_button( $payment_id ) {
		/**
		 * Fires in order details page, before the sidebar PDF Receipt Download button.
		 *
		 * @since 2.2.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_pdf_receipt_download_before', $payment_id );

		if ( $this->is_receipt_link_allowed( $payment_id ) && ! give_pdf_receipts_disable( $payment_id ) ) {
			$give_pdf_generate_receipt_nonce = wp_create_nonce( 'give_pdf_generate_receipt' );
			?>
			<div id="major-publishing-actions">
				<div id="publishing-action">
					<?php
					echo sprintf(
						'<a href="%1$s" id="pdf-receipt-download" class="button-secondary right dashicons-download">%2$s</a>',
						esc_url(
							add_query_arg(
								array(
									'give-action' => 'generate_pdf_receipt',
									'payment_id'  => $payment_id,
									'_wpnonce'    => $give_pdf_generate_receipt_nonce,
								)
							)
						),
						give_pdf_receipts_download_pdf_text( false )
					);
					?>
				</div>
				<div class="clear"></div>
			</div>
			<?php

			/**
			 * Fires in order details page, after the sidebar PDF Receipt Download button.
			 *
			 * @since 2.2.0
			 *
			 * @param int $payment_id Payment id.
			 */
			do_action( 'give_view_donation_details_pdf_receipt_download_after', $payment_id );
		}
	}

}