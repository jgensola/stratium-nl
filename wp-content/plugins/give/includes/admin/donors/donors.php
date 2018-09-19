<?php
/**
 * Donors.
 *
 * @package     Give
 * @subpackage  Admin/Donors
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get formatted address
 *
 * @since 2.0
 *
 * @param array $address
 * @param array $address_args
 *
 * @return string
 */
function __give_get_format_address( $address, $address_args = array() ) {
	$address_html = '';
	$address_args = wp_parse_args(
		$address_args,
		array(
			'type'            => '',
			'id'              => null,
			'index'           => null,
			'default_address' => false,
		)
	);

	$address_id = $address_args['type'];

	// Bailout.
	if ( empty( $address ) || ! is_array( $address ) ) {
		return $address_html;
	}

	// Address html.
	$address_html = '';
	$address_html .= sprintf(
		'<span data-address-type="line1">%1$s</span>%2$s',
		$address['line1'],
		( ! empty( $address['line2'] ) ? '<br>' : '' )
	);
	$address_html .= sprintf(
		'<span data-address-type="line2">%1$s</span>%2$s',
		$address['line2'],
		( ! empty( $address['city'] ) ? '<br>' : '' )
	);
	$address_html .= sprintf(
		'<span data-address-type="city">%1$s</span><span data-address-type="state">%2$s</span><span data-address-type="zip">%3$s</span>%4$s',
		$address['city'],
		( ! empty( $address['state'] ) ? ", {$address['state']}" : '' ),
		( ! empty( $address['zip'] ) ? " {$address['zip']}" : '' ),
		( ! empty( $address['country'] ) ? '<br>' : '' )
	);
	$address_html .= sprintf(
		'<span data-address-type="country">%s</span><br>',
		$address['country']
	);

	// Address action.
	$address_html .= sprintf(
		'<br><a href="#" class="js-edit">%1$s</a> | <a href="#" class="js-remove">%2$s</a>',
		__( 'Edit', 'give' ),
		__( 'Remove', 'give' )
	);

	/**
	 * Filter the address label
	 *
	 * @since 2.0
	 */
	$address_label = apply_filters( "give_donor_{$address_args['type']}_address_label", ucfirst( $address_args['type'] ), $address_args );

	// Set unique id and index for multi type address.
	if ( isset( $address_args['index'] ) ) {
		$address_label = "{$address_label} #{$address_args['index']}";
	}

	if ( isset( $address_args['id'] ) ) {
		$address_id = "{$address_id}_{$address_args['id']}";
	}

	// Add address wrapper.
	$address_html = sprintf(
		'<div class="give-grid-col-4"><div data-address-id="%s" class="address"><span class="alignright address-number-label">%s</span>%s</div></div>',
		$address_id,
		$address_label,
		$address_html
	);

	return $address_html;
}

/**
 * Donors Page.
 *
 * Renders the donors page contents.
 *
 * @since  1.0
 * @return void
 */
function give_donors_page() {
	$default_views  = give_donor_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'donors';
	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		give_render_donor_view( $requested_view, $default_views );
	} else {
		give_donors_list();
	}
}

/**
 * Register the views for donor management.
 *
 * @since  1.0
 * @return array Array of views and their callbacks.
 */
function give_donor_views() {

	$views = array();

	return apply_filters( 'give_donor_views', $views );

}

/**
 * Register the tabs for donor management.
 *
 * @since  1.0
 * @return array Array of tabs for the donor.
 */
function give_donor_tabs() {

	$tabs = array();

	return apply_filters( 'give_donor_tabs', $tabs );

}

/**
 * List table of donors.
 *
 * @since  1.0
 * @return void
 */
function give_donors_list() {

	include GIVE_PLUGIN_DIR . 'includes/admin/donors/class-donor-table.php';

	$donors_table = new Give_Donor_List_Table();
	$donors_table->prepare_items();
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php echo get_admin_page_title(); ?></h1>
		<?php
		/**
		 * Fires in donors screen, above the table.
		 *
		 * @since 1.0
		 */
		do_action( 'give_donors_table_top' );
		?>

		<hr class="wp-header-end">
		<form id="give-donors-search-filter" method="get"
		      action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors' ); ?>">
			<?php $donors_table->search_box( __( 'Search Donors', 'give' ), 'give-donors' ); ?>
			<input type="hidden" name="post_type" value="give_forms"/>
			<input type="hidden" name="page" value="give-donors"/>
			<input type="hidden" name="view" value="donors"/>
		</form>
		<form id="give-donors-filter" method="get">
			<?php $donors_table->display(); ?>
			<input type="hidden" name="post_type" value="give_forms"/>
			<input type="hidden" name="page" value="give-donors"/>
			<input type="hidden" name="view" value="donors"/>
		</form>
		<?php
		/**
		 * Fires in donors screen, below the table.
		 *
		 * @since 1.0
		 */
		do_action( 'give_donors_table_bottom' );
		?>
	</div>
	<?php
}

/**
 * Renders the donor view wrapper.
 *
 * @since  1.0
 *
 * @param  string $view The View being requested.
 * @param  array $callbacks The Registered views and their callback functions.
 *
 * @return void
 */
function give_render_donor_view( $view, $callbacks ) {

	$render = true;

	$donor_view_role = apply_filters( 'give_view_donors_role', 'view_give_reports' );

	if ( ! current_user_can( $donor_view_role ) ) {
		give_set_error( 'give-no-access', __( 'You are not permitted to view this data.', 'give' ) );
		$render = false;
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		give_set_error( 'give-invalid_donor', __( 'Invalid Donor ID.', 'give' ) );
		$render = false;
	}

	$donor_id          = (int) $_GET['id'];
	$reconnect_user_id = ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : '';
	$donor             = new Give_Donor( $donor_id );

	// Reconnect User with Donor profile.
	if ( $reconnect_user_id ) {
		give_connect_user_donor_profile( $donor, array( 'user_id' => $reconnect_user_id ), array() );
	}

	if ( empty( $donor->id ) ) {
		give_set_error( 'give-invalid_donor', __( 'Invalid Donor ID.', 'give' ) );
		$render = false;
	}

	$donor_tabs = give_donor_tabs();
	?>

	<div class='wrap'>

		<?php if ( give_get_errors() ) : ?>
			<div class="error settings-error">
				<?php Give()->notices->render_frontend_notices( 0 ); ?>
			</div>
		<?php endif; ?>

		<h1 class="wp-heading-inline">
			<?php
			printf(
			/* translators: %s: donor first name */
				__( 'Edit Donor: %s %s', 'give' ),
				$donor->get_first_name(),
				$donor->get_last_name()
			);
			?>
		</h1>

		<hr class="wp-header-end">

		<?php if ( $donor && $render ) : ?>

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $donor_tabs as $key => $tab ) :
					$active = $key === $view ? true : false;
					$class  = $active ? 'nav-tab nav-tab-active' : 'nav-tab';
					printf(
						'<a href="%1$s" class="%2$s"><span class="dashicons %3$s"></span>%4$s</a>' . "\n",
						esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=' . $key . '&id=' . $donor->id ) ),
						esc_attr( $class ),
						sanitize_html_class( $tab['dashicon'] ),
						esc_html( $tab['title'] )
					);
				endforeach;
				?>
			</h2>

			<div id="give-donor-card-wrapper">
				<?php $callbacks[ $view ]( $donor ) ?>
			</div>

		<?php endif; ?>

	</div>
	<?php

}


/**
 * View a donor
 *
 * @since  1.0
 *
 * @param  Give_Donor $donor The Donor object being displayed.
 *
 * @return void
 */
function give_donor_view( $donor ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_give_payments' );

	/**
	 * Fires in donor profile screen, above the donor card.
	 *
	 * @since 1.0
	 *
	 * @param object $donor The donor object being displayed.
	 */
	do_action( 'give_donor_card_top', $donor );

	// Set Read only to the fields which needs to be locked.
	$read_only = '';
	if ( $donor->user_id ) {
		$read_only = 'readonly="readonly"';
	}

	// List of title prefixes.
	$title_prefixes = give_get_name_title_prefixes();

	// Prepend title prefix to name if it is set.
	$title_prefix = Give()->donor_meta->get_meta( $donor->id, '_give_donor_title_prefix', true );
	$donor->name  = give_get_donor_name_with_title_prefixes( $title_prefix, $donor->name );
	?>
	<div id="donor-summary" class="info-wrapper donor-section postbox">
		<form id="edit-donor-info" method="post"
		      action="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ) ); ?>">
			<div class="donor-info">
				<div class="donor-bio-header clearfix">
					<div class="avatar-wrap left" id="donor-avatar">
						<?php echo get_avatar( $donor->email ); ?>
					</div>
					<div id="donor-name-wrap" class="left">
						<span class="donor-name info-item edit-item">
							<select name="donor_info[title]">
								<option disabled value="0"><?php esc_html_e( 'Title', 'give' ); ?></option>
								<?php
								if ( is_array( $title_prefixes ) && count( $title_prefixes ) > 0 ) {
									foreach ( $title_prefixes as $title ) {
										echo sprintf(
											'<option %1$s value="%2$s">%2$s</option>',
											selected( $title_prefix, $title, false ),
											esc_html( $title )
										);
									}
								}
								?>
							</select>
							<input <?php echo $read_only; ?> size="15" data-key="first_name"
							                                 name="donor_info[first_name]" type="text"
							                                 value="<?php echo esc_html( $donor->get_first_name() ); ?>"
							                                 placeholder="<?php esc_html_e( 'First Name', 'give' ); ?>"/>
							<?php if ( $donor->user_id ) : ?>
								<a href="#" class="give-lock-block">
									<i class="give-icon give-icon-locked"></i>
								</a>
							<?php endif; ?>
							<input <?php echo $read_only; ?> size="15" data-key="last_name"
							                                 name="donor_info[last_name]" type="text"
							                                 value="<?php echo esc_html( $donor->get_last_name() ); ?>"
							                                 placeholder="<?php esc_html_e( 'Last Name', 'give' ); ?>"/>
							<?php if ( $donor->user_id ) : ?>
								<a href="#" class="give-lock-block">
									<i class="give-icon give-icon-locked"></i>
								</a>
							<?php endif; ?>
						</span>
						<span class="donor-name info-item editable">
							<span data-key="name"><?php echo esc_html( $donor->name ); ?></span>
						</span>
					</div>
					<p class="donor-since info-item">
						<?php esc_html_e( 'Donor since', 'give' ); ?>
						<?php echo date_i18n( give_date_format(), strtotime( $donor->date_created ) ) ?>
					</p>
					<?php if ( current_user_can( $donor_edit_role ) ) : ?>
						<a href="#" id="edit-donor" class="button info-item editable donor-edit-link">
							<?php esc_html_e( 'Edit Donor', 'give' ); ?>
						</a>
					<?php endif; ?>
				</div>
				<!-- /donor-bio-header -->

				<div class="donor-main-wrapper">

					<table class="widefat striped">
						<tbody>
						<tr>
							<th scope="col"><label for="tablecell"><?php esc_html_e( 'Donor ID:', 'give' ); ?></label>
							</th>
							<td><?php echo intval( $donor->id ); ?></td>
						</tr>
						<tr>
							<th scope="col"><label for="tablecell"><?php esc_html_e( 'User ID:', 'give' ); ?></label>
							</th>
							<td>
									<span class="donor-user-id info-item edit-item">
										<?php

										$user_id = $donor->user_id > 0 ? $donor->user_id : '';

										$data_atts = array(
											'key'         => 'user_login',
											'search-type' => 'user',
										);
										$user_args = array(
											'name'  => 'donor_info[user_id]',
											'class' => 'give-user-dropdown',
											'data'  => $data_atts,
										);

										if ( ! empty( $user_id ) ) {
											$userdata              = get_userdata( $user_id );
											$user_args['selected'] = $user_id;
										}

										echo Give()->html->ajax_user_search( $user_args );
										?>
									</span>

								<span class="donor-user-id info-item editable">
										<?php if ( ! empty( $userdata ) ) : ?>
											<span
												data-key="user_id">#<?php echo $donor->user_id . ' - ' . $userdata->display_name; ?></span>
										<?php else : ?>
											<span
												data-key="user_id"><?php esc_html_e( 'Unregistered', 'give' ); ?></span>
										<?php endif; ?>
									<?php if ( current_user_can( $donor_edit_role ) && intval( $donor->user_id ) > 0 ) :

										echo sprintf(
											'- <span class="disconnect-user">
												<a id="disconnect-donor" href="#disconnect" aria-label="%1$s">%2$s</a>
											</span> | 
											<span class="view-user-profile">
												<a id="view-user-profile" href="%3$s" aria-label="%4$s">%5$s</a>
											</span>',
											esc_html__( 'Disconnects the current user ID from this donor record.', 'give' ),
											esc_html__( 'Disconnect User', 'give' ),
											esc_url( 'user-edit.php?user_id=' . $donor->user_id ),
											esc_html__( 'View User Profile of current user ID.', 'give' ),
											esc_html__( 'View User Profile', 'give' )
										);

									endif; ?>
									</span>
							</td>
						</tr>

						<?php
						$donor_company = $donor->get_meta( '_give_donor_company', true );
						?>
						<tr class="alternate">
							<th scope="col">
								<label for="tablecell"><?php esc_html_e( 'Company Name:', 'give' ); ?></label>
							</th>
							<td>
								<span class="donor-user-id info-item edit-item">
									<input name="give_donor_company" value="<?php echo $donor_company ?>" type="text">
								</span>

								<span class="donor-user-id info-item editable">
									<?php echo $donor_company; ?>
								</span>
							</td>
						</tr>

						<?php $anonymous_donor = absint( $donor->get_meta( '_give_anonymous_donor', true ) ); ?>
						<tr class="alternate">
							<th scope="col">
								<label for="tablecell"><?php _e( 'Anonymous Donor:', 'give' ); ?></label>
							</th>
							<td>
								<span class="donor-anonymous-donor info-item edit-item">
									<ul class="give-radio-inline">
										<li>
											<label>
												<input
													name="give_anonymous_donor"
													value="1"
													type="radio"
													<?php checked( 1, $anonymous_donor ) ?>
												><?php _e( 'Yes', 'give' ); ?>
											</label>
										</li>
										<li>
											<label>
												<input
													name="give_anonymous_donor"
													value="0"
													type="radio"
													<?php checked( 0, $anonymous_donor ) ?>
												><?php _e( 'No', 'give' ); ?>
											</label>
										</li>
									</ul>
								</span>
								<span class="donor-anonymous-donor info-item editable">
									<?php echo( $anonymous_donor ? __( 'Yes', 'give' ) : __( 'No', 'give' ) ); ?>
								</span>
							</td>
						</tr>
						</tbody>
					</table>

				</div>

			</div>

			<span id="donor-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="donor_info[id]" value="<?php echo intval( $donor->id ); ?>"/>
				<?php wp_nonce_field( 'edit-donor', '_wpnonce', false, true ); ?>
				<input type="hidden" name="give_action" value="edit-donor"/>
				<input type="submit" id="give-edit-donor-save" class="button-secondary"
				       value="<?php esc_html_e( 'Update Donor', 'give' ); ?>"/>
				<a id="give-edit-donor-cancel" href="" class="delete"><?php esc_html_e( 'Cancel', 'give' ); ?></a>
			</span>

		</form>

	</div>

	<?php
	/**
	 * Fires in donor profile screen, above the stats list.
	 *
	 * @since 1.0
	 *
	 * @param Give_Donor $donor The donor object being displayed.
	 */
	do_action( 'give_donor_before_stats', $donor );
	?>

	<div id="donor-stats-wrapper" class="donor-section postbox clear">
		<ul>
			<li>
				<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&do