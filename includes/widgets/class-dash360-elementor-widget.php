<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dash360_Elementor_Widget extends \Elementor\Widget_Base {
	public function get_name() {
		return 'dash360_product_button';
	}

	public function get_title() {
		return __( 'Dash360 Product Button', 'dash360' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_categories() {
		return array( 'woocommerce-elements', 'general' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'dash360_content',
			array(
				'label' => __( 'Settings', 'dash360' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => __( 'Button Text', 'dash360' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'View 360°', 'dash360' ),
				'placeholder' => __( 'View 360°', 'dash360' ),
			)
		);

		$this->add_control(
			'product_id',
			array(
				'label'       => __( 'Fallback Product ID', 'dash360' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'description' => __( 'Only needed if template context has no product.', 'dash360' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$product_id = 0;

		if ( is_singular( 'product' ) ) {
			$product_id = (int) get_queried_object_id();
		}

		if ( $product_id <= 0 && ! empty( $settings['product_id'] ) ) {
			$product_id = (int) $settings['product_id'];
		}

		if ( $product_id <= 0 || ! Dash360_WooCommerce::has_panorama_for_product( $product_id ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="dash360-widget-notice">' . esc_html__( 'Dash360: no valid 360 image found for this product.', 'dash360' ) . '</div>';
			}
			return;
		}

		Dash360_Assets::enqueue_viewer_assets();
		echo Dash360_WooCommerce::get_open_button_html( $product_id, isset( $settings['button_text'] ) ? (string) $settings['button_text'] : '' );
	}
}
