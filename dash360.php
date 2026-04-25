<?php
/**
 * Plugin Name: Dash360
 * Description: Replaces WooCommerce product featured image with a lightweight 360 panorama viewer.
 * Version: 0.1.0
 * Author: Dashweb
 * Author URI: https://dashweb.agency
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: dash360
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DASH360_VERSION', '0.1.0' );
define( 'DASH360_FILE', __FILE__ );
define( 'DASH360_DIR', plugin_dir_path( DASH360_FILE ) );
define( 'DASH360_URL', plugin_dir_url( DASH360_FILE ) );

require_once DASH360_DIR . 'includes/class-dash360-assets.php';
require_once DASH360_DIR . 'includes/class-dash360-admin.php';
require_once DASH360_DIR . 'includes/class-dash360-woocommerce.php';
require_once DASH360_DIR . 'includes/class-dash360-elementor.php';

function dash360_boot() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	Dash360_Assets::init();
	Dash360_Admin::init();
	Dash360_WooCommerce::init();
	if ( did_action( 'elementor/loaded' ) ) {
		Dash360_Elementor::init();
	}
}
add_action( 'plugins_loaded', 'dash360_boot' );
