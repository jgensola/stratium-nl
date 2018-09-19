<?php
/**
 * Plugin Name:       Give - Fee Recovery
 * Plugin URI:        https://wordimpress.com
 * Description:       Keep more of your donations by asking donor's to take care of the fees.
 * Version:           1.7.1
 * Author:            WordImpress
 * Author URI:        https://wordimpress.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       give-fee-recovery
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! class_exists( 'Give_Fee_Recovery' ) ) :

	/**
	 * Give_Fee_Recovery Class
	 *
	 * @package Give_Fee_Recovery
	 * @since   1.2.0
	 */
	final class Give_Fee_Recovery {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of Give_Fee_Recovery exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @var object
		 * @static
		 */
		private static $instance;

		/**
		 * Give Fee Recovery Admin Object.
		 *
		 * @since  1.2.0
		 * @access public
		 *
		 * @var Give_Fee_Recovery_Admin object.
		 */
		public $plugin_admin;

		/**
		 * Give Fee Recovery Public Object.
		 *
		 * @since  1.2.0
		 * @access public
		 *
		 * @var Give_Fee_Recovery_Public object.
		 */
		public $plugin_public;

		/**
		 * Get the instance and store the class inside it. This plugin utilises
		 * the PHP singleton design pattern.
		 *
		 * @since     1.2.0
		 * @static
		 * @staticvar array $instance
		 * @access    public
		 *
		 * @see       Give_Fee_Recovery();
		 *
		 * @uses      Give_Fee_Recovery::hooks() Setup hooks and actions.
		 * @uses      Give_Fee_Recovery::includes() Loads all the classes.
		 * @uses      Give_Fee_Recovery::licensing() Add Give Fee Recovery License.
		 *
		 * @return object self::$instance Instance
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_Fee_Recovery ) ) {
				self::$instance = new Give_Fee_Recovery();
				self::$instance->setup();
			}

			return self::$instance;
		}

		/**
		 * Setup Fee Recovery.
		 *
		 * @since  1.3.0
		 * @access private
		 */
		private function setup() {
			self::$instance->setup_constants();

			// Activation and deactivation hooks.
			$this->init_hooks();

			add_action( 'give_init', array( $this, 'init' ), 10 );
			add_action( 'plugins_loaded', array( $this, 'check_environment' ), 999 );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since  1.5.1
		 */
		private function init_hooks() {
			register_activation_hook( GIVE_FEE_RECOVERY_PLUGIN_FILE, array( $this, 'fee_recovery_install' ) );
		}

		/**
		 * Fee recovery install, Upgrade.
		 *
		 * @access public
		 * @since  1.5.1
		 */
		public function fee_recovery_install() {
			global $wpdb;

			update_option( 'give_fee_recovery_version_upgraded_from', get_option( 'give_fee_recovery_version', GIVE_FEE_RECOVERY_VERSION ) );
			update_option( 'give_fee_recovery_version', GIVE_FEE_RECOVERY_VERSION );

			$payment_count = $wpdb->get_var(
				$wpdb->prepare(
					"
				SELECT count(*)
				FROM $wpdb->donationmeta
				WHERE meta_key=%s
				",
					'_give_fee_amount'
				)
			);

			if ( ! empty( $payment_count ) ) {
				return;
			}

			$completed_upgrades = array(
				'give_fee_recovery_v151_form_fee_earnings'
			);

			foreach ( $completed_upgrades as $completed_upgrade ) {
				give_set_upgrade_complete( $completed_upgrade );
			}
		}

		/**
		 * Init Fee Recovery.
		 *
		 * Sets up hooks, licensing and includes files.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return void
		 */
		public function init() {
			if ( ! self::$instance->check_environment() ) {
				return;
			}

			self::$instance->hooks();
			self::$instance->licensing();
			self::$instance->includes();
		}

		/**
		 * Check plugin environment.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return bool
		 */
		public function check_environment() {

			// Load plugin helper functions.
			if ( ! function_exists( 'deactivate_plugins' ) || ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}

			// Flag to check whether deactivate plugin or not.
			$is_deactivate = false;

			// Verify dependency cases.
			switch ( true ) {
				case doing_action( 'give_init' ):
					if (
						defined( 'GIVE_VERSION' ) &&
						version_compare( GIVE_VERSION, GIVE_FEE_RECOVERY_MIN_GIVE_VER, '<' )
					) {
						/* Min. Give. plugin version. */
						// Show admin notice.
						$message = sprintf(
							'<strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a> %5$s',
							__( 'Activation Error:', 'give-fee-recovery' ),
							__( 'You must have', 'give-fee-recovery' ),
							esc_url( 'https://givewp.com' ),
							__( 'Give', 'give-fee-recovery' ),
							sprintf( __( 'core version %1$s+ for the Give - Fee Recovery add-on to activate.', 'give-fee-recovery' ), GIVE_FEE_RECOVERY_MIN_GIVE_VER )
						);

						$class = 'notice notice-error';
						printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );

						$is_deactivate = true;
					}

					break;

				case doing_action( 'plugins_loaded' ) && ! did_action( 'give_init' ):
					/* Check to see if Give is activated, if it isn't deactivate and show a banner. */

					// Check for if give plugin activate or not.
					$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

					if ( ! $is_give_active ) {
						// Show admin notice.
						$message = sprintf(
							'<strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a> %5$s',
							__( 'Activation Error:', 'give-fee-recovery' ),
							__( 'You must have', 'give-fee-recovery' ),
							esc_url( 'https://givewp.com' ),
							__( 'Give', 'give-fee-recovery' ),
							__( 'plugin installed and activated for Give - Fee Recovery to activate.', 'give-fee-recovery' )
						);

						$class = 'notice notice-error';
						printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );

						$is_deactivate = true;
					}

					break;
			}// End switch().

			// Don't let this plugin activate.
			if ( $is_deactivate ) {
				return false;
			}

			return true;
		}

		/**
		 * Setup constants.
		 *
		 * @since   1.0.0
		 * @access  private
		 */
		private function setup_constants() {
			if ( ! defined( 'GIVE_FEE_RECOVERY_VERSION' ) ) {
				define( 'GIVE_FEE_RECOVERY_VERSION', '1.7.1' );
			}
			if ( ! defined( 'GIVE_FEE_RECOVERY_MIN_GIVE_VER' ) ) {
				define( 'GIVE_FEE_RECOVERY_MIN_GIVE_VER', '2.2.0' );
			}
			if ( ! defined( 'GIVE_FEE_RECOVERY_SLUG' ) ) {
				define( 'GIVE_FEE_RECOVERY_SLUG', 'give-fee-recovery' );
			}
			if ( ! defined( 'GIVE_FEE_RECOVERY_PLUGIN_FILE' ) ) {
				define( 'GIVE_FEE_RECOVERY_PLUGIN_FILE', __FILE__ );
			}
			if ( ! defined( 'GIVE_FEE_RECOVERY_PLUGIN_DIR' ) ) {
				define( 'GIVE_FEE_RECOVERY_PLUGIN_DIR', dirname( GIVE_FEE_RECOVERY_PLUGIN_FILE ) );
			}
			if ( ! defined( 'GIVE_FEE_RECOVERY_PLUGIN_URL' ) ) {
				define( 'GIVE_FEE_RECOVERY_PLUGIN_URL', plugin_dir_url( GIVE_FEE_RECOVERY_PLUGIN_FILE ) );
			}
			if ( ! defined( 'GIVE_FEE_RECOVERY_BASENAME' ) ) {
				define( 'GIVE_FEE_RECOVERY_BASENAME', plugin_basename( GIVE_FEE_RECOVERY_PLUGIN_FILE ) );
			}
		}

		/**
		 * Throw error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since  1.2.0
		 * @access protected
		 *
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give-fee-recovery' ), '1.2.0' );
		}

		/**
		 * Disable Unserialize of the class.
		 *
		 * @since  1.2.0
		 * @access protected
		 *
		 * @return void
		 */
		public function __wakeup() {
			// Unserialize instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give-fee-recovery' ), '1.2.0' );
		}

		/**
		 * Constructor Function.
		 *
		 * @since  1.0.0
		 * @access protected
		 */
		public function __construct() {
			self::$instance = $this;
		}

		/**
		 * Reset the instance of the class
		 *
		 * @since  1.0.0
		 * @access public
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Includes.
		 *
		 * @since  1.0.0
		 * @access private
		 */
		private function includes() {
			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once( GIVE_FEE_RECOVERY_PLUGIN_DIR . '/includes/admin/class-give-fee-recovery-admin.php' );

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once( GIVE_FEE_RECOVERY_PLUGIN_DIR . '/includes/public/class-give-fee-recovery-public.php' );

			/**
			 * Give Fee Recovery helper functions.
			 */
			require_once( GIVE_FEE_RECOVERY_PLUGIN_DIR . '/includes/class-give-fee-recovery-helper.php' );

			/**
			 * Give Fee recovery Upgrade.
			 */
			require_once GIVE_FEE_RECOVERY_PLUGIN_DIR . '/includes/admin/upgrades/upgrade-functions.php';

			/**
			 * Give Fee recovery export.
			 */
			require_once GIVE_FEE_RECOVERY_PLUGIN_DIR . '/includes/admin/tools/class-give-export-donations-fee-recovery-details.php';

			if ( is_admin() ) {

				if ( class_exists( 'Give_Manual_Donations' ) ) {
					/**
					 * Give Fee Recovery helper functions for manual Donation Add-on.
					 */
					require_once( GIVE_FEE_RECOVERY_PLUGIN_DIR . '/includes/admin/give-fee-recovery-manual-donations.php' );

				}

			}

			self::$instance->plugin_admin  = new Give_Fee_Recovery_Admin();
			self::$instance->plugin_public = new Give_Fee_Recovery_Public();

		}

		/**
		 * Hooks.
		 *
		 * @since  1.0.0
		 * @access public
		 */
		public function hooks() {
			add_action( 'init', array( $this, 'load_textdomain' ) );
			add_action( 'admin_init', array( $this, 'activation_banner' ) );
			add_filter( 'plugin_action_links_' . GIVE_FEE_RECOVERY_BASENAME, array( $this, 'action_links' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		}

		/**
		 * Implement Give Licensing for Give Fee Recovery Add On.
		 *
		 * @since  1.0.0
		 * @access private
		 */
		private function licensing() {
			if ( class_exists( 'Give_License' ) ) {
				new Give_License(
					GIVE_FEE_RECOVERY_PLUGIN_FILE,
					'Fee Recovery',
					GIVE_FEE_RECOVERY_VERSION,
					'WordImpress'
				);
			}
		}

		/**
		 * Load Plugin Text Domain
		 *
		 * Looks for the plugin translation files in certain directories and loads
		 * them to allow the plugin to be localised
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return bool True on success, false on failure.
		 */
		public function load_textdomain() {
			// Traditional WordPress plugin locale filter.
			$locale = apply_filters( 'plugin_locale', get_locale(), 'give-fee-recovery' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'give-fee-recovery', $locale );

			// Setup paths to current locale file.
			$mofile_local = trailingslashit( plugin_dir_path( GIVE_FEE_RECOVERY_PLUGIN_FILE ) . 'languages' ) . $mofile;

			if ( file_exists( $mofile_local ) ) {
				// Look in the /wp-content/plugins/give-fee-recovery/languages/ folder.
				load_textdomain( 'give-fee-recovery', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'give-fee-recovery', false, trailingslashit( plugin_dir_path( GIVE_FEE_RECOVERY_PLUGIN_FILE ) . 'languages' ) );
			}

			return false;
		}

		/**
		 * Activation banner.
		 *
		 * Uses Give's core activation banners.
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		public function activation_banner() {

			// Check for activation banner inclusion.
			if ( ! class_exists( 'Give_Addon_Activation_Banner' ) && file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' ) ) {
				include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
			}

			// Initialize activation welcome banner.
			if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

				// Only runs on admin.
				$args = array(
					'file'              => GIVE_FEE_RECOVERY_PLUGIN_FILE,
					'name'              => __( 'Fee Recovery', 'give-fee-recovery' ),
					'version'           => GIVE_FEE_RECOVERY_VERSION,
					'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=givefeerecovery' ),
					'documentation_url' => 'http://docs.givewp.com/addon-fee-recovery/',
					'support_url'       => 'https://givewp.com/support/',
					'testing'           => false,
				);
				new Give_Addon_Activation_Banner( $args );
			}

			return true;
		}

		/**
		 * Adding additional setting page link along plugin's action link.
		 *
		 * @since   1.0.0
		 * @access  public
		 *
		 * @param   array $actions get all actions.
		 *
		 * @return  array       return new action array
		 */
		function action_links( $actions ) {

			if ( ! class_exists( 'Give' ) ) {
				return $actions;
			}

			// Check min Give version.
			if ( defined( 'GIVE_FEE_RECOVERY_MIN_GIVE_VER' ) && version_compare( GIVE_VERSION, GIVE_FEE_RECOVERY_MIN_GIVE_VER, '<' ) ) {
				return $actions;
			}

			$new_actions = array(
				'settings' => sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=givefeerecovery' ), __( 'Settings', 'give-fee-recovery' ) ),
			);

			return array_merge( $new_actions, $actions );

		}

		/**
		 * Plugin row meta links.
		 *
		 * @since   1.0.0
		 * @access  public
		 *
		 * @param   array  $plugin_meta An array of the plugin's metadata.
		 * @param   string $plugin_file Path to the plugin file, relative to the plugins directory.
		 *
		 * @return  array  return meta links for plugin.
		 */
		function plugin_row_meta( $plugin_meta, $plugin_file ) {

			if ( ! class_exists( 'Give' ) ) {
				return $plugin_meta;
			}

			// Return if not Give Fee Recovery plugin.
			if ( $plugin_file !== GIVE_FEE_RECOVERY_BASENAME ) {
				return $plugin_meta;
			}

			$new_meta_links = array(
				sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'http://docs.givewp.com/addon-fee-recovery' ) ), __( 'Documentation', 'give-fee-recovery' ) ),
				sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'https://givewp.com/addons/' ) ), __( 'Add-ons', 'give-fee-recovery' ) ),
			);

			return array_merge( $plugin_meta, $new_meta_links );

		}

	} //End Give_Fee_Recovery Class.

endif;

/**
 * Loads a single instance of Give Fee Recovery.
 *
 * This follows the PHP singleton design pattern.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @example <?php $give_fee_recovery = Give_Fee_Recovery(); ?>
 *
 * @since   1.0.0
 *
 * @see     Give_Fee_Recovery::get_instance()
 *
 * @return object Give_Fee_Recovery Returns an instance of the  class
 */
function Give_Fee_Recovery() {
	return Give_Fee_Recovery::get_instance();
}

Give_Fee_Recovery();