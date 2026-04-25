# Dash360 | WooCommerce 360 Product Viewer

Dash360 is a lightweight WordPress plugin that adds a smooth 360° product viewer to WooCommerce product pages with mobile drag/swipe support, fullscreen mode, and SEO-safe fallback behavior.

Built by [Dashweb](https://dashweb.agency).

## Why Dash360

- Adds an interactive 360 panorama experience for WooCommerce products.
- Works on desktop and mobile with drag/swipe controls.
- Includes an Elementor widget so you can place the 360 button exactly where you want in Single Product templates.
- Optimized for performance with smart format selection and conditional asset loading.
- Maintains SEO compatibility by preserving image-first behavior and graceful fallback.

## Key Features

- 360 equirectangular panorama viewer (best for 2:1 images).
- Fullscreen 360 modal experience.
- Product-level controls in admin:
  - Enable/disable 360 per product.
  - Optional custom 360 image per product.
- Elementor widget:
  - Widget name: `Dash360 Product Button`
  - Custom button label
  - Fallback Product ID for template preview cases
- Smart image source preference:
  - AVIF -> WebP -> original file
- Preload hint for product panorama image to improve first render.

## Requirements

- WordPress 6.0+
- WooCommerce installed and active
- PHP 7.4+
- Elementor (optional, only for widget usage)

## Installation

1. Upload this plugin folder to `wp-content/plugins/dash360` (or via Plugins > Add New > Upload Plugin).
2. Activate **Dash360** from WordPress admin.
3. Open a WooCommerce product and configure Dash360 settings.

## Product Setup

1. Edit your product in WordPress admin.
2. In `Dash360 Settings`:
   - Enable `Enable 360 viewer for this product`.
   - Optionally select a custom 360 image.
3. Use a proper 360 panorama image:
   - Equirectangular format
   - Ratio close to `2:1` (example: `6000x3000`)
   - Left and right edges should stitch seamlessly

## Elementor Usage

1. Open Elementor Theme Builder (Single Product template).
2. Drag widget `Dash360 Product Button` under product gallery (or any desired area).
3. Set button text (for example: `View 360°`).
4. Save and test on a product with valid 360 image.

## Performance and SEO Notes

- Loads viewer assets only when needed on product pages.
- Uses lazy behavior and lightweight integration.
- Uses best available image format automatically.
- Keeps non-interactive fallback paths for robust rendering.

## Developer Hook

You can override 360 detection logic:

```php
add_filter('dash360_is_360_image', function ($is360, $attachment_id, $meta) {
    return $is360;
}, 10, 3);
```

## Suggested GitHub Topics

`wordpress` `woocommerce` `elementor` `360-viewer` `panorama` `product-viewer` `equirectangular` `wordpress-plugin` `webp` `avif` `seo` `fullscreen`

## Roadmap

- Advanced UI style controls for Elementor widget.
- Optional hotspots and custom annotations.
- Analytics events for 360 open/close/engagement.
- Optional Gutenberg block.

## License

GPLv2 or later.

