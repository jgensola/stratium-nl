<?php
/**
 * Plugin Name: Give - Form Field Manager
 * Plugin URI:  https://givewp.com/addons/form-field-manager/
 * Description: Easily add and control Give's form fields using an easy-to-use interface.
 * Version:     1.3
 * Author:      WordImpress
 * Author URI:  https://wordimpress.com
 * Text Domain: give-ffm
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Form_Fields_Manager
 */
final class Give_Form_Fields_Manager {

	/** Singleton *************************************************************/

	/**
	 * @var Give_Form_Fields_Manager The one true Give_Form_Fields_Manager
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * @var string
	 */
	public $id = 'give-form-field-manager';

	/**
	 * The title of the FFM plugin.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * @var Give_FFM_Render_Form
	 */
	public $render_form;

	/**
	 * @var Give_FFM_Setup
	 */
	public $setup;

	/**
	 * @var Give_FFM_Upload
	 */
	public $upload;

	/**
	 * @var Give_FFM_Frontend_Form
	 */
	public $frontend_form_post;

	/**
	 * @var Give_FFM_Admin_Form
	 */
	public $admin_form;

	/**
	 * @var Give_FFM_Admin_Posting
	 */
	public $admin_posting;

	/**
	 * Notices (array).
	 *
	 * @since 1.1.3
	 *
	 * @var array
	 */
	public $notices = array();

	/**
	 * Main Give_Form_Fields_Manager Instance.
	 *
	 * Insures that only one instance of Give_Form_Fields_Manager exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since     1.0
	 * @staticvar array $instance
	 * @return Give_Form_Fields_Manager|object - The one true FFM.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_Form_Fields_Manager ) ) {
			self::$instance = new Give_Form_Fields_Manager;
			self::$instance->define_globals();
			self::$instance->hooks();
			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->setup();
		}

		return self::$instance;
	}


	/**
	 * Defines all the globally used constants
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_globals() {

		$this->title = __( 'Form Field Manager', 'give-form-field-manager' );

		// Plugin Name.
		if ( ! defined( 'GIVE_FFM_PRODUCT_NAME' ) ) {
			define( 'GIVE_FFM_PRODUCT_NAME', 'Form Field Manager' );
		}

		// Plugin Version.
		if ( ! defined( 'GIVE_FFM_VERSION' ) ) {
			define( 'GIVE_FFM_VERSION', '1.3' );
		}

		// Min Give Version.
		if ( ! defined( 'GIVE_FFM_MIN_GIVE_VERSION' ) ) {
			define( 'GIVE_FFM_MIN_GIVE_VERSION', '2.1.0' );
		}

		// Plugin Root File.
		if ( ! defined( 'GIVE_FFM_PLUGIN_FILE' ) ) {
			define( 'GIVE_FFM_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Folder Path.
		if ( ! defined( 'GIVE_FFM_PLUGIN_DIR' ) ) {
			define( 'GIVE_FFM_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( GIVE_FFM_PLUGIN_FILE ) ) . '/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'GIVE_FFM_PLUGIN_URL' ) ) {
			define( 'GIVE_FFM_PLUGIN_URL', plugin_dir_url( GIVE_FFM_PLUGIN_FILE ) );
		}

		// Plugin basename.
		if ( ! defined( 'GIVE_FFM_BASENAME' ) ) {
			define( 'GIVE_FFM_BASENAME', apply_filters( 'give_ffm_plugin_basename', plugin_basename( GIVE_FFM_PLUGIN_FILE ) ) );
		}

		if ( class_exists( 'Give_License' ) ) {
			new Give_License( GIVE_FFM_PLUGIN_FILE, GIVE_FFM_PRODUCT_NAME, GIVE_FFM_VERSION, 'WordImpress' );
		}
	}

	/**
	 * Hooks for main class.
	 *
	 * @since 1.1.3
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'check_plugin_requirements' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Check Plugin Requirements.
	 *
	 * @return bool
	 */
	public function check_plugin_requirements() {

		//Check for Give - if not active, deactivate/bail.
		if ( ! class_exists( 'Give' ) ) {

			$this->add_admin_notice( 'prompt_connect', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core plugin installed and activated for Form Field Manager to activate.', 'give-ffm' ), 'https://givewp.com' ) );

			deactivate_plugins( GIVE_FFM_BASENAME );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

		}

		//Min. Give. plugin version.
		if ( defined( 'GIVE_VERSION' ) && version_compare( GIVE_VERSION, '1.7', '<' ) ) {

			$this->add_admin_notice( 'prompt_connect', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version 1.7+ for the Form Field Manager add-on to activate.', 'give-ffm' ), 'https://givewp.com' ) );

			deactivate_plugins( GIVE_FFM_BASENAME );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			return false;
		}

		return true;

	}

	/**
	 * Allow this class and other classes to add notices.
	 *
	 * @since 1.1.3
	 *
	 * @param $slug
	 * @param $class
	 * @param $message
	 */
	public function add_admin_notice( $slug, $class, $message ) {
		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message,
		);
	}

	/**
	 * Handles the displaying of any notices in the admin area.
	 *
	 * @since  1.1.3
	 * @access public
	 * @return mixed
	 */
	public function admin_notices() {

		$allowed_tags = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
		);

		foreach ( (array) $this->notices as $notice_key => $notice ) {
			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses( $notice['message'], $allowed_tags );
			echo "</p></div>";
		}

	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since  v1.0
	 * @access private
	 * @uses   dirname()
	 * @uses   plugin_basename()
	 * @uses   apply_filters()
	 * @uses   load_textdomain()
	 * @uses   get_locale()
	 * @uses   load_plugin_textdomain()
	 */
	private function load_textdomain() {

		// Set filter for plugin's languages directory.
		$give_lang_dir = apply_filters( 'give_languages_directory', dirname( GIVE_FFM_BASENAME ) . '/languages/' );

		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-form-field-manager' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-form-field-manager', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $give_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-form-field-manager/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-form-field-manager folder.
			load_textdomain( 'give-form-field-manager', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-form-field-manager/languages/ folder.
			load_textdomain( 'give-form-field-manager', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'give-form-field-manager', false, $give_lang_dir );
		}
	}

	/**
	 * Include all files.
	 *
	 * @since 1.0.0
	 * @return void|bool
	 */
	private function includes() {

		//We need Give to continue.
		if ( ! class_exists( 'Give' ) ) {
			return false;
		}

		self::includes_general();
		self::includes_admin();
	}

	/**
	 * Load general files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function includes_general() {
		$files = array(
			'class-setup.php',
			'class-render-form.php',
			'class-frontend-form.php',
			'class-upload.php',
			'class-emails.php',
			'functions.php',
		);

		foreach ( $files as $file ) {
			require( sprintf( '%s/includes/%s', untrailingslashit( GIVE_FFM_PLUGIN_DIR ), $file ) );
		}
	}

	/**
	 * Load admin files.
	 *
	 * @since 1.0
	 * @return void
	 */
	private function includes_admin() {
		if ( is_admin() ) {
			$files = array(
				'admin-activation.php',
				'admin-settings.php',
				'admin-form.php',
				'admin-posting.php',
				'admin-template.php',
				'export-donations.php',
			);

			foreach ( $files as $file ) {
				require( sprintf( '%s/includes/admin/%s', untrailingslashit( GIVE_FFM_PLUGIN_DIR ), $file ) );
			}
		}
	}

	/**
	 * Setup FFM, loading scripts, styles and meta info.
	 *
	 * @since 1.0
	 * @return void
	 */
	private function setup() {
		if ( class_exists( 'Give' ) ) {

			do_action( 'give_ffm_setup_actions' );

			// Setup Instances
			self::$instance->render_form        = new Give_FFM_Render_Form;
			self::$instance->setup              = new Give_FFM_Setup;
			self::$instance->upload             = new Give_FFM_Upload;
			self::$instance->frontend_form_post = new Give_FFM_Frontend_Form;

			if ( is_admin() ) {
				self::$instance->admin_form    = new Give_FFM_Admin_Form;
				self::$instance->admin_posting = new Give_FFM_Admin_Posting;
			}
		}
	}

}

/**
 * The main function responsible for returning the one true Give_Form_Fields_Manager
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $give_ffm = Give_FFM(); ?>
 *
 * @since 1.0
 * @return Give_Form_Fields_Manager The one true Give_Form_Fields_Manager instance.
 */

function Give_FFM() {
	return Give_Form_Fields_Manager::instance();
}

add_action( 'plugins_loaded', 'Give_FFM' );
