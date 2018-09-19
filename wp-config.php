<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'newlif48_nlmain');

/** MySQL database username */
define('DB_USER', 'newlif48_nlmain');

/** MySQL database password */
define('DB_PASSWORD', '29Sp3N6Q)@');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'rw8eb4mwgcjkxjvtlf4ew6eidjx9yijvup61dgqtjkmn3ykcd2jy0hworakaq2ak');
define('SECURE_AUTH_KEY',  '2qsmzuhplxtw4u6z4yi1tnbiaj9idwjfshti3jflevhd9cq6btbqfhgcyintaymo');
define('LOGGED_IN_KEY',    'a1mrkuavek2codaxpairptgnekor82siffudlfq24fvita2wiokffbguzvrxhsr5');
define('NONCE_KEY',        'ay7hkt1boxaoqofliqdlbdyluanoo49q9oifzqskebgh1br7qsl8cxxqwxuwob9x');
define('AUTH_SALT',        'gqbo7mkd8ovmm4vdywxkonqwyuedsfbmomxayfqg6dyztmeokumrr0yp8l6swh3q');
define('SECURE_AUTH_SALT', '4dwlft7ioamjhbdo1rqpdujrhasmiifidwcquw0e7rvpasvh9lagzbfolwx4qado');
define('LOGGED_IN_SALT',   'bsh4uypfxwvbf3watd3pu4egdixeqfyeqsbe4tyg2rhakgwiwoafctj2mo0o4y66');
define('NONCE_SALT',       'mz2irbwkld6arqc4xa2lohycrdumfpztprywhlee1ng6ohvty0ppcyoofp1j9p4c');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'nl_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Multisite */
define( 'WP_ALLOW_MULTISITE', true );
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'www.newlife.ph');
define('PATH_CURRENT_SITE', '/main/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
