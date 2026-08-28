<?php
/**
 * Create or update the Custom Lunar New Year Gift Boxes WooCommerce product.
 *
 * Local review usage:
 *   php tools/import-custom-lunar-new-year-gift-box-product.php
 *
 * The five image files in the Git-tracked bundle and the canonical HTML file
 * are derived only from the approved lunar-new-year-gift-box-seo.zip package.
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$slug = 'custom-lunar-new-year-gift-boxes';
$title = 'Custom Lunar New Year Gift Boxes';
$batch_marker = 'product-samples-custom-lunar-new-year-gift-box-202608';
$category_slugs = array(
    'gift-paper-boxes',
    'rigid-boxes',
    'corporate-gift-packaging',
);
$featured_base = 'custom-tet-gift-box-double-door-open';
$gallery_bases = array(
    'red-double-door-rigid-gift-box-front-view',
    'red-double-door-tet-gift-box-closed',
    'tet-gift-box-top-view-wine-snacks',
    'gold-foil-tet-calligraphy-close-up',
);
$image_map = array(
    'custom-tet-gift-box-double-door-open' => array(
        'alt' => 'Custom red Tet gift box with two hinged doors open around a wine and food gift set',
        'title' => 'Custom Tet Gift Box Double-Door Open View',
        'caption' => 'A custom double-door Tet gift box designed for premium wine and gourmet gift sets.',
    ),
    'red-double-door-rigid-gift-box-front-view' => array(
        'alt' => 'Front view of red double-door rigid Lunar New Year gift box with gold festive artwork',
        'title' => 'Red Double-Door Rigid Gift Box Front View',
        'caption' => 'Front-facing view showing the symmetrical opening and structured multi-product interior.',
    ),
    'red-double-door-tet-gift-box-closed' => array(
        'alt' => 'Closed red double-door Tet gift box with large gold calligraphy, blossoms and lantern artwork',
        'title' => 'Closed Red Double-Door Tet Gift Box',
        'caption' => 'Closed presentation view showing the centered two-door design and gold Tet calligraphy.',
    ),
    'tet-gift-box-top-view-wine-snacks' => array(
        'alt' => 'Top view of open Tet gift box with wine bottle and divided snack compartments',
        'title' => 'Tet Gift Box Top View with Wine and Snacks',
        'caption' => 'Top view showing how the wine bottle and smaller gifts are separated into fitted compartments.',
    ),
    'gold-foil-tet-calligraphy-close-up' => array(
        'alt' => 'Macro close-up of metallic gold Tet calligraphy on textured red Lunar New Year gift box',
        'title' => 'Gold Foil Tet Calligraphy Close-Up',
        'caption' => 'Close-up emphasizing the metallic gold finish and textured red paper surface.',
    ),
);

function vpn_lunar_gift_box_find_attachment(string $base): int
{
    global $wpdb;

    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
            '%' . $wpdb->esc_like($base) . '%'
        )
    );

    foreach ($ids as $id) {
        $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
        if ($base === pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
            return (int) $id;
        }
    }

    return 0;
}

function vpn_lunar_gift_box_attachment(
    string $base,
    int $parent_id,
    array $image
): int {
    $relative = '2026/08/' . $base . '.webp';
    $uploads = wp_upload_dir();

    if (!empty($uploads['error']) || empty($uploads['basedir'])) {
        return 0;
    }

    $path = trailingslashit($uploads['basedir']) . $relative;
    $bundle = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;

    if (!file_exists($path) && file_exists($bundle)) {
        if (!wp_mkdir_p(dirname($path)) || !copy($bundle, $path)) {
            return 0;
        }
    }

    $attachment_id = vpn_lunar_gift_box_find_attachment($base);

    if ($attachment_id) {
        if (!file_exists($path)) {
            return 0;
        }

        update_post_meta($attachment_id, '_wp_attached_file', $relative);
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
        wp_update_post(array(
            'ID' => $attachment_id,
            'post_parent' => $parent_id,
            'post_title' => $image['title'],
            'post_excerpt' => $image['caption'],
        ));

        if (!get_post_meta($attachment_id, '_wp_attachment_metadata', true)) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $metadata = wp_generate_attachment_metadata($attachment_id, $path);
            if (is_array($metadata)) {
                wp_update_attachment_metadata($attachment_id, $metadata);
            }
        }

        return $attachment_id;
    }

    if (!file_exists($path)) {
        return 0;
    }

    $type = wp_check_filetype(wp_basename($path), null);
    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => !empty($type['type']) ? $type['type'] : 'image/webp',
            'post_title' => $image['title'],
            'post_excerpt' => $image['caption'],
            'post_status' => 'inherit',
            'post_parent' => $parent_id,
        ),
        $path,
        $parent_id,
        true
    );

    if (is_wp_error($attachment_id)) {
        return 0;
    }

    update_post_meta((int) $attachment_id, '_wp_attached_file', $relative);
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $path);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }

    return (int) $attachment_id;
}

function vpn_lunar_gift_box_figure(int $attachment_id, string $caption, bool $narrow = false): string
{
    if (!$attachment_id) {
        return '';
    }

    $class = 'product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '');

    return '<figure class="' . esc_attr($class) . '">'
        . wp_get_attachment_image(
            $attachment_id,
            'large',
            false,
            array(
                'loading' => 'lazy',
                'decoding' => 'async',
            )
        )
        . '<figcaption>' . esc_html($caption) . '</figcaption>'
        . '</figure>';
}

function vpn_lunar_gift_box_specs(string $title): array
{
    return array(
        array('label' => 'Feature', 'value' => 'Premium red double-door rigid presentation box with gold decorative artwork, fitted multi-product interior, and custom seasonal branding'),
        array('label' => 'Industrial Use', 'value' => 'Lunar New Year gift sets, corporate gifts, wine, tea, confectionery, nuts, dried fruit, wellness and premium food assortments'),
        array('label' => 'Paper Type', 'value' => 'Rigid greyboard or recycled solid board wrapped with printed art paper or specialty textured paper'),
        array('label' => 'Box Type', 'value' => 'Double-door rigid presentation box with two hinged front panels opening from the center'),
        array('label' => 'Shape', 'value' => 'Rectangular / customized'),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => 'CUSTOM LUNAR NEW YEAR GIFT BOXES'),
        array('label' => 'Brand Name', 'value' => 'VPN'),
        array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
        array('label' => 'Accessories', 'value' => 'Paperboard divider, custom insert, molded pulp tray, EVA insert, compartment partition, tissue and gift card'),
        array('label' => 'Custom Order', 'value' => 'Accept'),
        array('label' => 'Liner Type', 'value' => 'Printed paper lining / specialty textured paper / paperboard insert / molded pulp tray / EVA insert'),
        array('label' => 'Logo Printing', 'value' => 'Custom logo'),
        array('label' => 'Printing Handling', 'value' => 'Offset printing, CMYK, Pantone, hot foil stamping, embossing, debossing, spot UV and matte lamination'),
        array('label' => 'Color', 'value' => 'Red and gold / CMYK / Pantone customized'),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => 'Price based on size, board thickness, wrap paper, insert, finishing, assembly and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $title),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_lunar_gift_box_short_description(): string
{
    return 'Custom Lunar New Year Gift Boxes are premium double-door rigid presentation boxes designed for Vietnamese Tet gift sets, corporate appreciation programs, wine, tea, confectionery, nuts, specialty foods and wellness assortments. The rigid paperboard structure opens from the center to frame a bottle and fitted compartments, while red textured wrapping and gold decorative finishing create a ceremonial presentation. Buyers can customize dimensions, artwork, colors, company logos, door-panel messages, inserts, dividers, foil stamping, embossing, lamination and other finishing details around the products placed inside. VPN Packaging can develop the structure, paper wrap, insert and production specification for brands, distributors, retailers and gifting agencies. Send the product dimensions, packed weight, quantity, artwork direction, insert requirements, destination country and deadline for an accurate quotation. MOQ is 1000 boxes, with a physical sample recommended before bulk production.';
}

function vpn_lunar_gift_box_content(array $images): string
{
    $path = get_template_directory() . '/inc/product-content/custom-lunar-new-year-gift-boxes.html';
    $content = is_readable($path) ? file_get_contents($path) : '';

    if (!is_string($content) || '' === trim($content)) {
        return '';
    }

    return strtr(
        $content,
        array(
            '{{IMAGE_FEATURED}}' => vpn_lunar_gift_box_figure(
                $images['custom-tet-gift-box-double-door-open']['id'] ?? 0,
                $images['custom-tet-gift-box-double-door-open']['caption'] ?? ''
            ),
            '{{IMAGE_FRONT}}' => vpn_lunar_gift_box_figure(
                $images['red-double-door-rigid-gift-box-front-view']['id'] ?? 0,
                $images['red-double-door-rigid-gift-box-front-view']['caption'] ?? ''
            ),
            '{{IMAGE_DETAIL}}' => vpn_lunar_gift_box_figure(
                $images['gold-foil-tet-calligraphy-close-up']['id'] ?? 0,
                $images['gold-foil-tet-calligraphy-close-up']['caption'] ?? '',
                true
            ),
            '{{IMAGE_TOP}}' => vpn_lunar_gift_box_figure(
                $images['tet-gift-box-top-view-wine-snacks']['id'] ?? 0,
                $images['tet-gift-box-top-view-wine-snacks']['caption'] ?? ''
            ),
        )
    );
}

$category_ids = array();
$missing_categories = array();

foreach ($category_slugs as $category_slug) {
    $category = get_term_by('slug', $category_slug, 'product_cat');

    if (!$category || is_wp_error($category)) {
        $missing_categories[] = $category_slug;
        continue;
    }

    $category_ids[$category_slug] = (int) $category->term_id;
}

if ($missing_categories) {
    fwrite(STDERR, 'Required product categories were not found: ' . implode(', ', $missing_categories) . PHP_EOL);
    exit(1);
}

$missing_bundle_images = array();
foreach ($image_map as $base => $image) {
    $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/08/' . $base . '.webp';
    if (!file_exists($bundle_path)) {
        $missing_bundle_images[] = $base . '.webp';
    }
}

if ($missing_bundle_images) {
    fwrite(STDERR, 'Missing bundled ZIP images: ' . implode(', ', $missing_bundle_images) . PHP_EOL);
    exit(1);
}

global $wpdb;
$existing = get_posts(array(
    'name' => $slug,
    'post_type' => 'product',
    'post_status' => array('publish', 'draft', 'pending', 'private', 'trash'),
    'posts_per_page' => 1,
));

if (!$existing) {
    $existing_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status <> 'trash' AND post_title = %s ORDER BY ID DESC LIMIT 1",
            $title
        )
    );
    if ($existing_id) {
        $existing = array(get_post($existing_id));
    }
}

$post_data = array(
    'post_title' => $title,
    'post_name' => $slug,
    'post_type' => 'product',
    'post_status' => 'publish',
    'post_excerpt' => vpn_lunar_gift_box_short_description(),
    'post_content' => '',
);

if ($existing) {
    $product_id = (int) $existing[0]->ID;
    wp_untrash_post($product_id);
    $post_data['ID'] = $product_id;
    $result = wp_update_post($post_data, true);
} else {
    $result = wp_insert_post($post_data, true);
    $product_id = is_wp_error($result) ? 0 : (int) $result;
}

if (is_wp_error($result) || !$product_id) {
    fwrite(STDERR, is_wp_error($result) ? $result->get_error_message() . PHP_EOL : "Could not create product.\n");
    exit(1);
}

$image_ids = array();
$missing_images = array();

foreach ($image_map as $base => $image) {
    $attachment_id = vpn_lunar_gift_box_attachment($base, $product_id, $image);

    if (!$attachment_id) {
        $missing_images[] = $base;
        continue;
    }

    $image_ids[$base] = $attachment_id;
    $image_map[$base]['id'] = $attachment_id;
}

if ($missing_images) {
    fwrite(STDERR, 'Missing product images: ' . implode(', ', $missing_images) . PHP_EOL);
    exit(1);
}

wp_set_object_terms($product_id, array_values($category_ids), 'product_cat', false);
wp_set_object_terms($product_id, 'simple', 'product_type');
wp_set_object_terms($product_id, array(
    'Lunar New Year gift boxes',
    'Tet gift box packaging',
    'Double-door rigid gift box',
    'Corporate Lunar New Year packaging',
    'Wine gift packaging',
    'Premium gift box packaging',
), 'product_tag', false);

update_post_meta($product_id, '_vpn_sample_import', $batch_marker);
update_post_meta($product_id, '_regular_price', '');
update_post_meta($product_id, '_price', '');
update_post_meta($product_id, '_stock_status', 'instock');
update_post_meta($product_id, '_manage_stock', 'no');
update_post_meta($product_id, '_visibility', 'visible');
update_post_meta($product_id, '_custom_box_product_specs', vpn_lunar_gift_box_specs($title));
update_post_meta($product_id, 'rank_math_focus_keyword', 'custom lunar new year gift boxes');
update_post_meta($product_id, 'rank_math_title', 'Custom Lunar New Year Gift Boxes | Tet Packaging Manufacturer');
update_post_meta($product_id, 'rank_math_description', 'Custom Lunar New Year gift boxes for Tet, corporate gifts, wine and premium foods. Custom sizes, inserts, printing and finishing from Vietnam.');
update_post_meta($product_id, 'rank_math_primary_product_cat', $category_ids['gift-paper-boxes']);
update_post_meta($product_id, '_vpn_content_duplicate_risk', '3/10 - low; seasonal double-door rigid gift-box structure and Tet B2B sourcing angle');
update_post_meta($product_id, '_vpn_image_duplicate_risk', '4/10 - five approved ZIP images include two closely related open views, retained in the gallery as source-of-truth assets');

set_post_thumbnail($product_id, $image_ids[$featured_base]);

$gallery_ids = array();
foreach ($gallery_bases as $base) {
    $gallery_ids[] = $image_ids[$base];
}
update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));

$content = vpn_lunar_gift_box_content($image_map);
if ('' === trim($content) || false !== strpos($content, '{{IMAGE_')) {
    fwrite(STDERR, "Canonical product content or image replacement is missing.\n");
    exit(1);
}

wp_update_post(array(
    'ID' => $product_id,
    'post_content' => $content,
));

if (function_exists('wc_delete_product_transients')) {
    wc_delete_product_transients($product_id);
}

echo 'Product ID: ' . $product_id . PHP_EOL;
echo 'Product URL: ' . get_permalink($product_id) . PHP_EOL;
echo 'Categories: ' . implode(', ', $category_slugs) . PHP_EOL;
echo 'Featured image: ' . $image_ids[$featured_base] . PHP_EOL;
echo 'Gallery images: ' . implode(', ', $gallery_ids) . PHP_EOL;
echo 'Short description words: ' . str_word_count(wp_strip_all_tags(vpn_lunar_gift_box_short_description())) . PHP_EOL;
echo 'Long description words: ' . str_word_count(wp_strip_all_tags($content)) . PHP_EOL;
echo 'Specifications: ' . count(vpn_lunar_gift_box_specs($title)) . PHP_EOL;
