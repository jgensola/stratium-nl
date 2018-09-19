<?php
/**
* Plugin Name: Give - iPay88
* Description: iPay88 gateway extension for GiveWP
* Author: Stratium Software Group
* Version: 1.0
**/

session_start();

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class/iPay88.php';

$iPay88 = new iPay88();

add_action( 'give_ipay88_cc_form', '__return_false' );

add_filter( 'give_payment_gateways', array($iPay88, 'f_give_payment_gateways') );

add_action( 'give_gateway_ipay88', array($iPay88, 'f_give_gateway_ipay88') );

add_filter( 'give_settings_gateways', array($iPay88, 'give_add_ipay88_settings') );