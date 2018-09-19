<?php
/**
 * Plugin Name:     Give - Per Form Gateways
 * Plugin URI:      https://givewp.com/addons/per-form-gateways/
 * Description:     Choose on a per-form basis which payment gateways you would like enabled for donors.
 * Version:         1.0.1
 * Author:          WordImpress
 * Author URI:      https://wordimpress.com
 * Text Domain:     give-per-form-gateways
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Per_Form_Gateways' ) ) {

	/**
	 * Class Give_Per_Form_Gateways
	 *
	 * @since 1.0
	 */
	class Give_Per_Form_Gateways {


		/**
		 * @var         Give_Per_Form_Gateways $instance The one true Give_Per_Form_Gateways
		 * @since       1.0
		 */
		private static $instance;


		/**
		 * Get active instance.
		 *
		 * @access      public
		 * @since       1.0
		 * @return      self::$instance The one true Give_Per_Form_Gateways
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new Give_Per_Form_Gateways();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants.
		 *
		 * @access      public
		 * @since       1.0
		 * @return      void
		 */
		private function setup_constants() {

			// Plugin version.
			if ( ! defined( 'GIVE_PER_FORM_GATEWAYS_VERSION' ) ) {
				define( 'GIVE_PER_FORM_GATEWAYS_VERSION', '1.0.1' );
			}

			// Min Give Core version.
			if ( ! defined( 'GIVE_PER_FORM_GATEWAYS_MIN_GIVE_VERSION' ) ) {
				define( 'GIVE_PER_FORM_GATEWAYS_MIN_GIVE_VERSION', '1.7' );
			}

			// Plugin path.
			if ( ! defined( 'GIVE_PER_FORM_GATEWAYS_DIR' ) ) {
				define( 'GIVE_PER_FORM_GATEWAYS_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin URL.
			if ( ! defined( 'GIVE_PER_FORM_GATEWAYS_URL' ) ) {
				define( 'GIVE_PER_FORM_GATEWAYS_URL', plugin_dir_url( __FILE__ ) );
			}

			// Basename.
			if ( ! defined( 'GIVE_PER_FORM_GATEWAYS_BASENAME' ) ) {
				define( 'GIVE_PER_FORM_GATEWAYS_BASENAME', plugin_basename( __FILE__ ) );
			}

		}


		/**
		 * Include necessary files.
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function includes() {

			require_once GIVE_PER_FORM_GATEWAYS_DIR . 'includes/give-per-form-gateways-core.php';

			if ( is_admin() ) {
				require_once GIVE_PER_FORM_GATEWAYS_DIR . 'includes/admin/give-per-form-gateways-activation.php';
				require_once GIVE_PER_FORM_GATEWAYS_DIR . 'includes/admin/give-per-form-gateways-meta-boxes.php';
			}

		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function hooks() {
			// Handle licensing.
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( __FILE__, 'Per Form Gateways', GIVE_PER_FORM_GATEWAYS_VERSION, 'WordImpress' );
			}
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'give_per_form_gateways_language_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), '' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'give-per-form-gateways', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/give-per-form-gateways/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/give-per-form-gateways/ folder
				load_textdomain( 'give-per-form-gateways', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/give-per-form-gateways/ folder
				load_textdomain( 'give-per-form-gateways', $mofile_local );
			} else {
				// Load the traditional language files
				load_plugin_textdomain( 'give-per-form-gateways', false, $lang_dir );
			}
		}
	}
}


/**
 * The main function responsible for returning the Give_Per_Form_Gateways instance.
 *
 * @since       1.0
 * @return      Give_Per_Form_Gateways The one true Give_Per_Form_Gateways
 */
function give_per_form_gateways() {
	return Give_Per_Form_Gateways::instance();
}

add_action( 'plugins_loaded', 'give_per_form_gateways' );
