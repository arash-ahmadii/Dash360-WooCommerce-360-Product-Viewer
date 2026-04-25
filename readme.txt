=== Dash360 ===
Contributors: dashweb
Tags: woocommerce, 360, panorama, product image
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Render WooCommerce product featured images as interactive 360 panorama views.

Author: Dashweb
Website: https://dashweb.agency

== Description ==

Dash360 converts the featured image on single WooCommerce product pages into an interactive 360 viewer when the image looks like an equirectangular panorama.

SEO and performance approach:
- Keeps a regular image fallback in HTML for crawlers and no-JS users.
- Loads viewer scripts only when a valid 360 featured image is detected.
- Uses lazy initialization with IntersectionObserver.
- Maintains stable layout via aspect-ratio to reduce CLS.
- Selects best available image format (AVIF, then WebP, then original).
- Adds smart preload link on product pages for faster first render.

== Installation ==

1. Copy this plugin folder into `wp-content/plugins/dash360`.
2. Activate **Dash360** in WordPress Admin.
3. In WooCommerce, set a product featured image that is a 360 panorama.
4. Optional: in product editor, use Dash360 metabox to select a custom 360 image.

== Usage ==

For best 360 results:
- Use an equirectangular image with around 2:1 ratio (example: 6000x3000).
- Ensure left and right edges of the image stitch seamlessly.
- Keep image size optimized for web delivery (prefer compressed JPG/WebP).

By default, the plugin assumes 360 is enabled when `_dash360_enabled` meta is empty or `yes`.
If `_dash360_image_id` is set, that image is used for the 360 viewer instead of featured image.

Developers can override 360 detection:

`add_filter( 'dash360_is_360_image', function( $is360, $attachment_id, $meta ) { return $is360; }, 10, 3 );`

== Changelog ==

= 0.1.0 =
* Initial MVP release.
