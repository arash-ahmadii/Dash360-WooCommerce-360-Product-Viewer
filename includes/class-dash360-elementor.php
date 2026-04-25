<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dash360_Elementor {
	public static function init() {
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
	}

	public static function register_widgets( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		require_once DASH360_DIR . 'includes/widgets/class-dash360-elementor-widget.php';
		$widgets_manager->register( new Dash360_Elementor_Widget() );
	}
}
