<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dash360_Admin {
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_product_metabox' ) );
		add_action( 'save_post_product', array( __CLASS__, 'save_product_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	public static function add_product_metabox() {
		add_meta_box(
			'dash360_product_settings',
			__( 'Dash360 Settings', 'dash360' ),
			array( __CLASS__, 'render_product_metabox' ),
			'product',
			'side',
			'default'
		);
	}

	public static function render_product_metabox( $post ) {
		$enabled = get_post_meta( $post->ID, '_dash360_enabled', true );
		$checked = 'yes' === $enabled || '' === $enabled;
		$image_id = (int) get_post_meta( $post->ID, '_dash360_image_id', true );
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';

		wp_nonce_field( 'dash360_product_settings', 'dash360_product_settings_nonce' );
		?>
		<p>
			<label>
				<input type="checkbox" name="dash360_enabled" value="yes" <?php checked( $checked ); ?> />
				<?php esc_html_e( 'Enable 360 viewer for this product', 'dash360' ); ?>
			</label>
		</p>
		<p style="margin:0;color:#646970;">
			<?php esc_html_e( 'Use a 2:1 featured image for best 360 results.', 'dash360' ); ?>
		</p>
		<hr />
		<p style="margin-bottom:8px;">
			<strong><?php esc_html_e( 'Custom 360 image (optional)', 'dash360' ); ?></strong>
		</p>
		<input type="hidden" id="dash360_image_id" name="dash360_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
		<div id="dash360-image-preview" style="<?php echo $image_url ? '' : 'display:none;'; ?>margin-bottom:10px;">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:100%;height:auto;display:block;border:1px solid #dcdcde;border-radius:6px;" />
		</div>
		<p style="display:flex;gap:8px;flex-wrap:wrap;margin:0 0 8px;">
			<button type="button" class="button" id="dash360-select-image"><?php esc_html_e( 'Select image', 'dash360' ); ?></button>
			<button type="button" class="button" id="dash360-remove-image" style="<?php echo $image_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'dash360' ); ?></button>
		</p>
		<p style="margin:0;color:#646970;">
			<?php esc_html_e( 'If selected, this image is used instead of featured image for viewer.', 'dash360' ); ?>
		</p>
		<?php
	}

	public static function enqueue_admin_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'dash360-admin',
			DASH360_URL . 'assets/js/dash360-admin.js',
			array( 'jquery' ),
			DASH360_VERSION,
			true
		);
	}

	public static function save_product_settings( $post_id ) {
		if ( ! isset( $_POST['dash360_product_settings_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dash360_product_settings_nonce'] ) ), 'dash360_product_settings' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$enabled = isset( $_POST['dash360_enabled'] ) ? 'yes' : 'no';
		$image_id = isset( $_POST['dash360_image_id'] ) ? absint( wp_unslash( $_POST['dash360_image_id'] ) ) : 0;

		update_post_meta( $post_id, '_dash360_enabled', $enabled );

		if ( $image_id > 0 ) {
			update_post_meta( $post_id, '_dash360_image_id', $image_id );
		} else {
			delete_post_meta( $post_id, '_dash360_image_id' );
		}
	}
}
