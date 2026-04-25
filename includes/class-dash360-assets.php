<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dash360_Assets {
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	public static function register_assets() {
		wp_register_style(
			'dash360-pannellum',
			'https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css',
			array(),
			'2.5.6'
		);

		wp_register_script(
			'dash360-pannellum',
			'https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js',
			array(),
			'2.5.6',
			true
		);

		wp_register_style(
			'dash360-viewer',
			DASH360_URL . 'assets/css/dash360-viewer.css',
			array(),
			DASH360_VERSION
		);

		wp_register_script(
			'dash360-viewer',
			DASH360_URL . 'assets/js/dash360-viewer.js',
			array( 'dash360-pannellum' ),
			DASH360_VERSION,
			true
		);
	}

	public static function enqueue_viewer_assets() {
		wp_enqueue_style( 'dash360-pannellum' );
		wp_enqueue_style( 'dash360-viewer' );
		wp_enqueue_script( 'dash360-pannellum' );
		wp_enqueue_script( 'dash360-viewer' );
	}
}
