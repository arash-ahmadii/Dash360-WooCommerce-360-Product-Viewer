<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dash360_WooCommerce {
	private static $viewer_image_cache = array();

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_product_assets' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'render_preload_link' ), 1 );
	}

	public static function maybe_enqueue_product_assets() {
		if ( ! is_product() ) {
			return;
		}

		$product_id = get_queried_object_id();
		if ( $product_id <= 0 ) {
			return;
		}

		$source = self::get_product_panorama_source( $product_id );
		if ( empty( $source['url'] ) ) {
			return;
		}

		Dash360_Assets::enqueue_viewer_assets();
	}

	public static function render_open_button() {
		if ( ! is_product() ) {
			return;
		}

		$product_id = get_queried_object_id();
		if ( $product_id <= 0 ) {
			return;
		}

		echo self::get_open_button_html( $product_id );
	}

	public static function get_open_button_html( $product_id, $button_text = '' ) {
		$product_id = (int) $product_id;
		if ( $product_id <= 0 ) {
			return '';
		}

		$source = self::get_product_panorama_source( $product_id );
		if ( empty( $source['url'] ) ) {
			return '';
		}

		$modal_id = 'dash360-modal-' . $product_id;
		$data     = array(
			'image'           => esc_url_raw( $source['url'] ),
			'autoLoad'        => true,
			'showControls'    => true,
			'mouseZoom'       => true,
			'doubleClickZoom' => true,
			'draggable'       => true,
			'yaw'             => 0,
			'pitch'           => 0,
			'hfov'            => 105,
		);

		$label = $button_text ? $button_text : __( 'View 360°', 'dash360' );

		$html  = '<div class="dash360-trigger-wrap">';
		$html .= '<button type="button" class="button dash360-open-button js-dash360-open" data-dash360-target="' . esc_attr( $modal_id ) . '" data-dash360="' . esc_attr( wp_json_encode( $data ) ) . '">';
		$html .= esc_html( $label );
		$html .= '</button>';
		$html .= '</div>';
		$html .= '<div id="' . esc_attr( $modal_id ) . '" class="dash360-modal js-dash360-modal" hidden>';
		$html .= '<button type="button" class="dash360-close js-dash360-close" aria-label="' . esc_attr__( 'Close 360 viewer', 'dash360' ) . '">&times;</button>';
		$html .= '<div class="dash360-modal-stage js-dash360-modal-stage"></div>';
		$html .= '<div class="dash360-modal-loader js-dash360-modal-loader" aria-hidden="true"></div>';
		$html .= '<div class="dash360-modal-message js-dash360-modal-message" hidden>' . esc_html__( '360 view unavailable for this image.', 'dash360' ) . '</div>';
		$html .= '</div>';

		return $html;
	}

	public static function has_panorama_for_product( $product_id ) {
		$source = self::get_product_panorama_source( (int) $product_id );

		return ! empty( $source['url'] );
	}

	public static function replace_main_image_html( $html, $attachment_id ) {
		if ( ! is_product() ) {
			return $html;
		}

		global $product;

		if ( ! $product instanceof WC_Product ) {
			return $html;
		}

		$product_id      = $product->get_id();
		$featured_id     = (int) get_post_thumbnail_id( $product_id );
		$current_image   = (int) $attachment_id;
		$enabled_by_meta = get_post_meta( $product_id, '_dash360_enabled', true );
		$is_enabled      = 'yes' === $enabled_by_meta || '' === $enabled_by_meta;
		$viewer_image_id = self::get_product_viewer_image_id( $product_id, $featured_id );

		if ( $current_image !== $featured_id || ! $is_enabled ) {
			return $html;
		}

		$source = self::get_best_panorama_source( $viewer_image_id );
		if ( empty( $source['url'] ) ) {
			return $html;
		}

		if ( ! self::is_probable_360_image( $viewer_image_id ) ) {
			return $html;
		}

		Dash360_Assets::enqueue_viewer_assets();

		$img_fallback = wp_get_attachment_image(
			$viewer_image_id,
			'large',
			false,
			array(
				'class'    => 'dash360-fallback-image',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);

		$image_meta = wp_get_attachment_metadata( $viewer_image_id );
		$width      = is_array( $image_meta ) && ! empty( $image_meta['width'] ) ? (int) $image_meta['width'] : 0;
		$height     = is_array( $image_meta ) && ! empty( $image_meta['height'] ) ? (int) $image_meta['height'] : 0;

		$data = array(
			'image'         => esc_url_raw( $source['url'] ),
			'autoLoad'      => true,
			'showControls'  => false,
			'mouseZoom'     => false,
			'doubleClickZoom' => false,
			'draggable'     => true,
			'yaw'           => 0,
			'pitch'         => 0,
			'hfov'          => 105,
		);

		$ratio_style = '';
		if ( $width > 0 && $height > 0 ) {
			$ratio_style = '--dash360-ratio:' . esc_attr( $width ) . '/' . esc_attr( $height ) . ';';
		}

		return sprintf(
			'<div class="dash360-product-media"><div class="dash360-viewer js-dash360-viewer is-loading" style="%1$s" data-dash360="%2$s"><div class="dash360-stage js-dash360-stage" aria-hidden="true"></div><div class="dash360-loader" aria-hidden="true"></div><div class="dash360-hint">%3$s</div>%4$s<noscript>%4$s</noscript></div></div>',
			esc_attr( $ratio_style ),
			esc_attr( wp_json_encode( $data ) ),
			esc_html__( 'Drag or swipe to explore 360°', 'dash360' ),
			$img_fallback
		);
	}

	public static function render_preload_link() {
		if ( ! is_product() ) {
			return;
		}

		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$product_id = $product->get_id();
		$source     = self::get_product_panorama_source( $product_id );
		if ( empty( $source['url'] ) ) {
			return;
		}

		$featured_id     = (int) get_post_thumbnail_id( $product_id );
		$viewer_image_id = self::get_product_viewer_image_id( $product_id, $featured_id );
		$srcset = wp_get_attachment_image_srcset( $viewer_image_id, 'full' );
		$sizes  = wp_get_attachment_image_sizes( $viewer_image_id, 'full' );

		printf(
			'<link rel="preload" as="image" href="%1$s" fetchpriority="high"%2$s%3$s%4$s />' . "\n",
			esc_url( $source['url'] ),
			! empty( $source['mime'] ) ? ' type="' . esc_attr( $source['mime'] ) . '"' : '',
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
			$sizes ? ' imagesizes="' . esc_attr( $sizes ) . '"' : ''
		);
	}

	private static function get_product_panorama_source( $product_id ) {
		$enabled    = get_post_meta( $product_id, '_dash360_enabled', true );
		$is_enabled = 'yes' === $enabled || '' === $enabled;
		if ( ! $is_enabled ) {
			return array(
				'url'  => '',
				'mime' => '',
			);
		}

		$featured_id = (int) get_post_thumbnail_id( $product_id );
		if ( $featured_id <= 0 ) {
			return array(
				'url'  => '',
				'mime' => '',
			);
		}

		$viewer_image_id = self::get_product_viewer_image_id( $product_id, $featured_id );
		if ( ! self::is_probable_360_image( $viewer_image_id ) ) {
			return array(
				'url'  => '',
				'mime' => '',
			);
		}

		return self::get_best_panorama_source( $viewer_image_id );
	}

	private static function is_probable_360_image( $attachment_id ) {
		$meta = wp_get_attachment_metadata( $attachment_id );
		if ( ! is_array( $meta ) || empty( $meta['width'] ) || empty( $meta['height'] ) ) {
			return false;
		}

		$width  = (float) $meta['width'];
		$height = (float) $meta['height'];
		$ratio  = $height > 0 ? $width / $height : 0;

		$is_ratio_valid = $ratio > 1.9 && $ratio < 2.1;

		return (bool) apply_filters( 'dash360_is_360_image', $is_ratio_valid, $attachment_id, $meta );
	}

	private static function get_product_viewer_image_id( $product_id, $featured_id ) {
		if ( isset( self::$viewer_image_cache[ $product_id ] ) ) {
			return self::$viewer_image_cache[ $product_id ];
		}

		$custom_image_id = (int) get_post_meta( $product_id, '_dash360_image_id', true );
		$resolved_id     = $custom_image_id > 0 ? $custom_image_id : (int) $featured_id;

		self::$viewer_image_cache[ $product_id ] = $resolved_id;

		return $resolved_id;
	}

	private static function get_best_panorama_source( $attachment_id ) {
		$default_url = wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( ! $default_url ) {
			return array(
				'url'  => '',
				'mime' => '',
			);
		}

		$attached_file = get_attached_file( $attachment_id );
		if ( ! $attached_file ) {
			return array(
				'url'  => $default_url,
				'mime' => 'image/jpeg',
			);
		}

		$file_ext = strtolower( pathinfo( $attached_file, PATHINFO_EXTENSION ) );
		$priority = array( 'avif', 'webp', $file_ext );
		$priority = array_values( array_unique( $priority ) );

		foreach ( $priority as $ext ) {
			$candidate_path = preg_replace( '/\.[^.]+$/', '.' . $ext, $attached_file );
			if ( ! is_string( $candidate_path ) || ! file_exists( $candidate_path ) ) {
				continue;
			}

			$candidate_url = preg_replace( '/\.[^.]+$/', '.' . $ext, $default_url );
			if ( ! is_string( $candidate_url ) || '' === $candidate_url ) {
				continue;
			}

			$mime = wp_check_filetype( $candidate_path )['type'];

			return array(
				'url'  => $candidate_url,
				'mime' => is_string( $mime ) ? $mime : '',
			);
		}

		$file_type = get_post_mime_type( $attachment_id );

		return array(
			'url'  => $default_url,
			'mime' => is_string( $file_type ) ? $file_type : '',
		);
	}
}
