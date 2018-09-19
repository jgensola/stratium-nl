<?php
/**
 * Give Form Field Manager Admin Settings.
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.1.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_FFM_Admin_Setting {
	/**
	 * Instance.
	 *
	 * @since
	 * @access static
	 * @var
	 */
	static private $instance;


	/**
	 * Setting ID
	 * @var string
	 */
	private $id;

	/**
	 * Singleton pattern.
	 *
	 * @since
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since
	 * @updated 1.1.4
	 * @access  static
	 *
	 * @return static
	 */
	static function get_instance() {
		if (
			! isset( self::$instance ) &&
			! ( self::$instance instanceof Give_FFM_Admin_Setting )
		) {
			self::$instance = new Give_FFM_Admin_Setting();
		}

		return self::$instance;
	}



	/**
	 * Setup
	 *
	 * @access public
	 */
	public function setup() {
		$this->id = 'form-field-manager';

		// Filters.
		add_filter( 'give_get_sections_display', array( $this, 'register_sections' ) );
		add_filter( 'give_get_settings_display', array( $this, 'register_settings' ) );
	}


	/**
	 * Register admin aettings.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function register_settings( $settings ) {
		if( $this->id !== give_get_current_setting_section() ) {
			return $settings;
		}

		$settings = array(
			array(
				'id'   => 'give_title_ffm',
				'type' => 'title'
			),
			array(
				'id'      => 'ffm_datepicker_css',
				'name'    => __( 'Datepicker CSS', 'give-form-field-manager' ),
				'desc'    => __( 'Would you like to output the datepicker CSS provided by the Form Field Manager? Some themes may provide their own styling to the datepicker. If that is the case, you may disable the CSS output to use your theme\'s styles.', 'give-form-field-manager' ),
				'options' => array(
					'enabled'  => __( 'Enabled', 'give-form-field-manager' ),
					'disabled' => __( 'Disabled', 'give-form-field-manager' ),
				),
				'default' => 'enabled',
				'type'    => 'radio_inline'
			),
			array(
				'id'   => 'give_title_ffm',
				'type' => 'sectionend'
			),
		);

		return $settings;
	}

	/**
	 * Register admin settings.
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function register_sections( $sections ) {
		$sections[$this->id] = __( 'Form Field Manager', 'give-form-field-manager' );

		return $sections;
	}
}

// Initialize class.
Give_FFM_Admin_Setting::get_instance()->setup();