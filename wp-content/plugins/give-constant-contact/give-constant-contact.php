<?php
/**
 * Plugin Name: Give - Constant Contact
 * Plugin URI:  https://givewp.com/addons/constant-contact/
 * Description: Integrate Constant Contact sign-up with your Give donation forms.
 * Version:     1.2.1
 * Author:      WordImpress
 * Author URI:  https://wordimpress.com
 * Text Domain: give-constant-contact
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
if ( ! defined( 'GIVE_CONSTANT_CONTACT_VERSION' ) ) {
	define( 'GIVE_CONSTANT_CONTACT_VERSION', '1.2.1' );
}
if ( ! defined( 'GIVE_CONSTANT_CONTACT_MIN_GIVE_VER' ) ) {
	define( 'GIVE_CONSTANT_CONTACT_MIN_GIVE_VER', '1.7' );
}
if ( ! defined( 'GIVE_CONSTANT_CONTACT_PATH' ) ) {
	define( 'GIVE_CONSTANT_CONTACT_PATH', dirname( __FILE__ ) );
}
if ( ! defined( 'GIVE_CONSTANT_CONTACT_URL' ) ) {
	define( 'GIVE_CONSTANT_CONTACT_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'GIVE_CONSTANT_CONTACT_BASENAME' ) ) {
	define( 'GIVE_CONSTANT_CONTACT_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'GIVE_CONSTANT_CONTACT_DIR' ) ) {
	define( 'GIVE_CONSTANT_CONTACT_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Constant Contact Licensing.
 */
function give_add_constant_contact_licensing() {
	if ( class_exists( 'Give_License' ) ) {
		new Give_License( __FILE__, 'Constant Contact', GIVE_CONSTANT_CONTACT_VERSION, 'WordImpress' );
	}
}

add_action( 'plugins_loaded', 'give_add_constant_contact_licensing' );


/**
 * Give Constant Contact Includes.
 */
function give_constant_contact_includes() {

	include( dirname( __FILE__ ) . '/includes/give-constant-contact-activation.php' );

	if ( ! class_exists( 'Give' ) ) {
		return false;
	}

	include( dirname( __FILE__ ) . '/includes/class-give-constant-contact.php' );

	new Give_Constant_Contact( 'constant_contact', 'Constant Contact' );

}

add_action( 'plugins_loaded', 'give_constant_contact_includes', 999 );