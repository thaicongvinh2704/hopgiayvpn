<?php
/** Import five Christmas gift-box concepts from the 2026-10-01 bundled image and content package. */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

defined('ABSPATH') || exit;

require_once get_template_directory() . '/inc/christmas-gift-boxes-20261001-support.php';
$marker = 'christmas-gift-boxes-20261001';
$theme_dir = get_template_directory();
$content_dir = $theme_dir . '/inc/product-content/' . $marker . '/';
$bundle_dir = $theme_dir . '/inc/product-sample-deploy-assets/uploads/2026/10/';
$uploads = wp_upload_dir();
$upload_dir = trailingslashit($uploads['basedir']) . '2026/10/';
$image_roles = array(
    1 => array('hero', 'front hero'),
    2 => array('front-view', 'front view'),
    3 => array('material-detail', 'surface and window detail'),
    4 => array('side-view', 'side view'),
    5 => array('top-view', 'top view'),
    6 => array('feature-callouts', 'annotated feature callouts'),
);

$products = json_decode((string) file_get_contents($content_dir . 'products.json'), true, 512, JSON_THROW_ON_ERROR);

if (!function_exists('vpn_xmas_gift_20261001_image_files')) {
function vpn_xmas_gift_20261001_image_files(string $prefix, string $directory): array {
    $files = glob(trailingslashit($directory) . $prefix . '-0*.webp') ?: array();
    $pattern = '/^' . preg_quote($prefix, '/') . '-0[1-6]-(?:hero|front-view|material-detail|side-view|top-view|feature-callouts)\.webp$/i';
    $files = array_values(array_filter($files, static function ($file) use ($pattern) {
        return (bool) preg_match($pattern, wp_basename($file));
    }));
    usort($files, static function ($a, $b) { return strnatcasecmp(wp_basename($a), wp_basename($b)); });
    return $files;
}

function vpn_xmas_gift_20261001_copy_assets(array $products, string $source_dir, string $target_dir): array {
    if (!is_dir($target_dir) && !wp_mkdir_p($target_dir)) {
        return array('Upload directory could not be created.');
    }
    $errors = array();
    foreach ($products as $product) {
        foreach (vpn_xmas_gift_20261001_image_files($product['prefix'], $source_dir) as $source) {
            $target = trailingslashit($target_dir) . wp_basename($source);
            if (file_exists($target)) {
                if (hash_file('sha256', $target) !== hash_file('sha256', $source)) {
                    $errors[] = 'Refusing to overwrite a different upload file: ' . wp_basename($source);
                }
                continue;
            }
            if (!copy($source, $target)) {
                $errors[] = 'Could not copy upload file: ' . wp_basename($source);
            }
        }
    }
    return $errors;
}

function vpn_xmas_gift_20261001_attachment(string $file, int $parent_id, array $product, int $view, array $roles): int {
    global $wpdb;
    $filename = wp_basename($file);
    $existing_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_vpn_christmas_gift_boxes_20261001_file' AND meta_value = %s ORDER BY post_id DESC LIMIT 1",
        $filename
    ));
    $role = $roles[$view][1] ?? 'product view';
    $alt = $product['title'] . ' for Christmas gifting, ' . $role . '; AI design visualization';
    $caption = 6 === $view
        ? 'Annotated AI-generated design visualization; labels are illustrative and are not production specifications.'
        : 'AI-generated design visualization, ' . $role . '; not a photograph of a manufactured sample.';

    if ($existing_id && 'attachment' === get_post_type($existing_id)) {
        wp_update_post(array('ID' => $existing_id, 'post_parent' => $parent_id, 'post_title' => $product['title'] . ' - ' . $role, 'post_excerpt' => $caption));
        update_post_meta($existing_id, '_wp_attachment_image_alt', $alt);
        return $existing_id;
    }
    if (!file_exists($file)) {
        return 0;
    }
    $type = wp_check_filetype($filename, null);
    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => $type['type'] ?: 'image/webp',
        'post_title' => $product['title'] . ' - ' . $role,
        'post_excerpt' => $caption,
        'post_status' => 'inherit',
        'post_parent' => $parent_id,
    ), $file, $parent_id, true);
    if (is_wp_error($attachment_id)) {
        return 0;
    }
    update_post_meta((int) $attachment_id, '_vpn_christmas_gift_boxes_20261001_file', $filename);
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $alt);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $file);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    return (int) $attachment_id;
}

function vpn_xmas_gift_20261001_inline_figure(int $attachment_id, string $caption, string $alt): string {
    $image = wp_get_attachment_image($attachment_id, 'large', false, array(
        'alt' => $alt,
        'loading' => 'lazy',
        'decoding' => 'async',
        'sizes' => '(max-width: 767px) calc(100vw - 36px), 560px',
    ));
    return '<figure class="product-inline-figure product-inline-figure-small">' . $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_xmas_gift_20261001_product_link(string $slug, string $anchor): string {
    $post = get_page_by_path($slug, OBJECT, 'product');
    return '<a href="' . esc_url($post ? get_permalink($post) : home_url('/products/christmas-packaging/')) . '">' . esc_html($anchor) . '</a>';
}

}

$category_ids = array();
$failures = array();
foreach ($products as $product) {
    $content_file = $content_dir . $product['content_file'];
    if (!is_readable($content_file)) {
        $failures[] = $product['slug'] . ': missing content file';
    } else {
        $content = (string) file_get_contents($content_file);
        $word_count = str_word_count(wp_strip_all_tags($content));
        if ($word_count < 1500 || $word_count > 2000) {
            $failures[] = $product['slug'] . ': long description word count is ' . $word_count . ', expected 1500-2000';
        }
        if (preg_match('/<h1\b/i', $content) || 3 !== substr_count($content, '{{FIGURE_')) {
            $failures[] = $product['slug'] . ': long description must have no H1 and three inline figure slots';
        }
        $known_tokens = array(
            '{{FIGURE_OPEN}}', '{{FIGURE_DETAIL}}', '{{FIGURE_TOP}}', '{{CATEGORY_LINK}}',
            '{{RELATED_ONE_URL}}', '{{RELATED_TWO_URL}}', '{{GUIDE_URL}}', '{{CONTACT_URL}}', '{{FACTORY_PROOF}}',
        );
        if (preg_match('/\{\{[A-Z0-9_]+\}\}/', str_replace($known_tokens, '', $content))) {
            $failures[] = $product['slug'] . ': unexpected unresolved content placeholder';
        }
    }
    $short_words = str_word_count(wp_strip_all_tags($product['short']));
    if ($short_words < 120 || $short_words > 180) {
        $failures[] = $product['slug'] . ': short description word count is ' . $short_words . ', expected 120-180';
    }
    if (strlen($product['seo_title']) > 60 || strlen($product['seo_description']) > 155 || !$product['keyword']) {
        $failures[] = $product['slug'] . ': SEO title, description or focus keyword is invalid';
    }
    foreach ($product['categories'] as $category_slug) {
        if (!isset($category_ids[$category_slug])) {
            $term = get_term_by('slug', $category_slug, 'product_cat');
            if (!$term || is_wp_error($term)) {
                $failures[] = 'Missing product category: ' . $category_slug;
            } else {
                $category_ids[$category_slug] = (int) $term->term_id;
            }
        }
    }
    $files = vpn_xmas_gift_20261001_image_files($product['prefix'], $bundle_dir);
    if (6 !== count($files)) {
        $failures[] = $product['slug'] . ': expected six source images, found ' . count($files);
    }
    foreach ($files as $file) {
        $info = @getimagesize($file);
        if (!$info || 'image/webp' !== $info['mime'] || 450 !== $info[0] || 570 !== $info[1] || filesize($file) > 102400) {
            $failures[] = 'Invalid WebP source: ' . wp_basename($file);
        }
    }
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    if ($existing && $marker !== (string) get_post_meta($existing->ID, '_vpn_sample_import', true)) {
        $failures[] = $product['slug'] . ': slug already belongs to a product outside this batch';
    }
}
$christmas_term = get_term_by('slug', 'christmas-packaging', 'product_cat');
if (!$christmas_term || is_wp_error($christmas_term)) {
    $failures[] = 'Primary Christmas product category is missing';
} else {
    $category_ids['christmas-packaging'] = (int) $christmas_term->term_id;
}
if ($failures) {
    throw new RuntimeException(implode('; ', array_unique($failures)));
}

$asset_errors = vpn_xmas_gift_20261001_copy_assets($products, $bundle_dir, $upload_dir);
if ($asset_errors) {
    throw new RuntimeException(implode('; ', $asset_errors));
}

$factory_proof = vpn_xmas_20261001_factory_proof();

// Create all product records first so cross-links between the five products resolve.
$product_ids = array();
foreach ($products as $product) {
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    $post_data = array(
        'post_title' => $product['title'],
        'post_name' => $product['slug'],
        'post_type' => 'product',
        'post_status' => $existing ? $existing->post_status : 'draft',
        'post_excerpt' => $product['short'],

    );
    if ($existing) {
        $post_data['ID'] = (int) $existing->ID;
        wp_untrash_post((int) $existing->ID);
        $result = wp_update_post($post_data, true);
        $product_id = (int) $existing->ID;
    } else {
        $result = wp_insert_post($post_data, true);
        $product_id = is_wp_error($result) ? 0 : (int) $result;
    }
    if (is_wp_error($result) || !$product_id) {
        $failures[] = $product['slug'] . ': product write failed';
        continue;
    }
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    $product_ids[$product['slug']] = $product_id;
}

$imported = array();
foreach ($products as $product) {
    if (empty($product_ids[$product['slug']])) {
        continue;
    }
    $product_id = (int) $product_ids[$product['slug']];
    $files = vpn_xmas_gift_20261001_image_files($product['prefix'], $upload_dir);
    if (6 !== count($files)) {
        $failures[] = $product['slug'] . ': expected six uploaded images, found ' . count($files);
        continue;
    }
    $attachment_ids = array();
    foreach ($files as $index => $file) {
        $attachment_id = vpn_xmas_gift_20261001_attachment($file, $product_id, $product, $index + 1, $image_roles);
        if (!$attachment_id) {
            $failures[] = $product['slug'] . ': image attachment failed for ' . wp_basename($file);
        } else {
            $attachment_ids[] = $attachment_id;
        }
    }
    if (6 !== count($attachment_ids)) {
        continue;
    }

    $term_ids = array();
    foreach ($product['categories'] as $category_slug) {
        $term_ids[] = $category_ids[$category_slug];
    }
    wp_set_object_terms($product_id, array_values(array_unique($term_ids)), 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type');
    wp_set_object_terms($product_id, $product['tags'], 'product_tag', false);

    update_post_meta($product_id, '_vpn_visual_status', 'AI-generated design visualization; not a manufactured sample');
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', array(
        array('label' => 'Feature', 'value' => $product['feature']),
        array('label' => 'Industrial Use', 'value' => $product['industrial_use']),
        array('label' => 'Paper Type', 'value' => $product['paper_type']),
        array('label' => 'Box Type', 'value' => $product['box_type']),
        array('label' => 'Shape', 'value' => $product['shape']),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => $product['visual_reference']),
        array('label' => 'Brand Name', 'value' => 'VPN'),
        array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
        array('label' => 'Accessories', 'value' => 'Insert, ribbon, card, seal and other components must be specified and quoted separately.'),
        array('label' => 'Custom Order', 'value' => 'Accept; confirm the structure and specification by approved sample.'),
        array('label' => 'Liner Type', 'value' => $product['liner_type']),
        array('label' => 'Logo Printing', 'value' => 'Customer logo and artwork to be placed on the approved dieline.'),
        array('label' => 'Printing Handling', 'value' => 'Custom print and finish to be selected from the artwork, material and approved physical sample.'),
        array('label' => 'Color', 'value' => $product['color']),
        array('label' => 'Size', 'value' => 'Customized to the measured product and approved internal fit.'),
        array('label' => 'Thickness', 'value' => 'Board grade and thickness to be selected from the packed-product brief and confirmed by sample.'),
        array('label' => 'Single Piece Price', 'value' => 'Quote after structure, material, quantity, finish, packing and destination are specified.'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes; confirm eligible specifications and quantity breaks in the written quotation.'),
        array('label' => 'Product Name', 'value' => $product['title']),
        array('label' => 'Design', 'value' => 'Customer-specific artwork and structure; supplied images are AI-generated design visualizations, not production samples.'),
    ));
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['seo_title']);
    update_post_meta($product_id, 'rank_math_description', $product['seo_description']);
    update_post_meta($product_id, 'rank_math_primary_product_cat', $category_ids[$product['seo_category']]);
    update_post_meta($product_id, 'rank_math_robots', array('index', 'follow'));
    update_post_meta($product_id, 'rank_math_canonical_url', get_permalink($product_id));
    set_post_thumbnail($product_id, $attachment_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($attachment_ids, 1)));

    $content = (string) file_get_contents($content_dir . $product['content_file']);
    $figures = array(
        '{{FIGURE_OPEN}}' => vpn_xmas_gift_20261001_inline_figure($attachment_ids[1], 'Front view of the ' . strtolower($product['title']) . '; AI design visualization.', $product['title'] . ' front view for seasonal gift packaging, AI design visualization'),
        '{{FIGURE_DETAIL}}' => vpn_xmas_gift_20261001_inline_figure($attachment_ids[2], $product['caption_detail'], $product['title'] . ' printed detail for holiday gift packaging, AI design visualization'),
        '{{FIGURE_TOP}}' => vpn_xmas_gift_20261001_inline_figure($attachment_ids[4], 'Top view of the ' . strtolower($product['title']) . '; confirm panel positions on the production dieline.', $product['title'] . ' top artwork view for seasonal packaging, AI design visualization'),
    );
    $content = str_replace(array_keys($figures), array_values($figures), $content);
    $replacements = array(
        '{{FACTORY_PROOF}}' => $factory_proof,
        '{{CATEGORY_LINK}}' => '<a href="' . esc_url(home_url('/products/christmas-packaging/')) . '">Christmas gift-box and paper-bag collection</a>',
        '{{RELATED_ONE_URL}}' => vpn_xmas_gift_20261001_product_link($product['related_one'], $product['related_one_text']),
        '{{RELATED_TWO_URL}}' => vpn_xmas_gift_20261001_product_link($product['related_two'], $product['related_two_text']),
        '{{GUIDE_URL}}' => '<a href="' . esc_url(home_url('/christmas-packaging-ideas/')) . '">Christmas packaging design and approval guide</a>',
        '{{CONTACT_URL}}' => '<a href="' . esc_url(home_url('/contact/#quote')) . '">request a Christmas packaging quote</a>',
    );
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);
    if (preg_match('/\{\{[A-Z0-9_]+\}\}/', $content)) {
        $failures[] = $product['slug'] . ': unresolved content placeholder';
    }
    if (preg_match('/\{\{[A-Z0-9_]+\}\}/', $content)) { continue; }
    $saved = wp_update_post(array('ID' => $product_id, 'post_content' => $content, 'post_status' => ('private' === get_post_status($product_id) ? 'private' : 'publish')), true);
    if (is_wp_error($saved)) { $failures[] = $product['slug'] . ': content save failed'; continue; }
    update_post_meta($product_id, '_vpn_christmas_20261001_source_hash', vpn_xmas_20261001_source_hash($product));
    update_post_meta($product_id, '_vpn_christmas_20261001_content_hash', hash('sha256', (string) get_post_field('post_content', $product_id)));
    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients($product_id);
    }
    clean_post_cache($product_id);
    $imported[] = array(
        'id' => $product_id,
        'slug' => $product['slug'],
        'url' => get_permalink($product_id),
        'images' => count($attachment_ids),
        'short_words' => str_word_count(wp_strip_all_tags($product['short'])),
        'long_words' => str_word_count(wp_strip_all_tags($content)),
    );
}

echo wp_json_encode(array('scope' => 'current WordPress site', 'marker' => $marker, 'expected_products' => count($products), 'imported' => $imported, 'failures' => $failures), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures || count($imported) !== count($products)) {
    throw new RuntimeException('Christmas product import did not complete: ' . implode('; ', $failures));
}
