<?php
/**
* Plugin Name: Give - Elvanto
* Description: Elvanto extension for GiveWP
* Author: Stratium Software Group
* Version: 1.0
**/

session_start();

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class/class-elvanto.php';
require_once plugin_dir_path( __FILE__ ) . 'class/class-elvanto-api.php';

add_action('hook_name', array(&$this, 'give_elvanto_user_info'));

$elvanto_api = new Elvanto();
