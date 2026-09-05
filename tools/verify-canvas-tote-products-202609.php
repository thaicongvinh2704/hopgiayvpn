<?php

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

require_once get_template_directory() . '/inc/product-sample-deploy-tools/import-canvas-tote-products-202609.php';

function vpn_canvas_tote_202609_verify_one($definition) {
    global $wpdb;

    $errors = array();
    $product = get_page_by_path($definition['slug'], OBJECT, 'product');

    if (!$product instanceof WP_Post) {
        return array('slug' => $definition['slug'], 'errors' => array('Product does not exist.'));
    }

    $product_id = (int) $product->ID;
    $source = file_get_contents(vpn_canvas_tote_202609_source_path($definition));
    $alt_map = vpn_canvas_tote_202609_source_image_alt_map($source);
    $expected_category = $definition['category_slug'];
    $category_slugs = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));
    sort($category_slugs);

    if (array($expected_category) !== $category_slugs) {
        $errors[] = 'Category mismatch: ' . implode(', ', $category_slugs);
    }

    $expected_tag_slugs = array_map('sanitize_title', $definition['tags']);
    $actual_tag_slugs = wp_get_post_terms($product_id, 'product_tag', array('fields' => 'slugs'));
    sort($expected_tag_slugs);
    sort($actual_tag_slugs);

    if ($expected_tag_slugs !== $actual_tag_slugs) {
        $errors[] = 'Tag set mismatch.';
    }

    if ($definition['title'] !== $product->post_title) {
        $errors[] = 'Product title mismatch.';
    }

    if ('publish' !== $product->post_status || $definition['slug'] !== $product->post_name) {
        $errors[] = 'Product status or slug mismatch.';
    }

    if (defined('VPN_CANVAS_TOTE_202609_MARKER') && VPN_CANVAS_TOTE_202609_MARKER !== get_post_meta($product_id, '_vpn_sample_import', true)) {
        $errors[] = 'Product deploy marker mismatch.';
    }

    $thumbnail_id = (int) get_post_thumbnail_id($product_id);
    $gallery_ids = array_filter(array_map('intval', explode(',', (string) get_post_meta($product_id, '_product_image_gallery', true))));
    $attachment_ids = array_merge(array($thumbnail_id), $gallery_ids);

    if (6 !== count($attachment_ids) || 6 !== count(array_unique($attachment_ids))) {
        $errors[] = 'Expected six unique gallery attachment IDs.';
    }

    foreach ($definition['images'] as $index => $filename) {
        $attachment_id = isset($attachment_ids[$index]) ? (int) $attachment_ids[$index] : 0;
        $attached_file = $attachment_id ? (string) get_post_meta($attachment_id, '_wp_attached_file', true) : '';
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $actual_base = pathinfo(wp_basename($attached_file), PATHINFO_FILENAME);

        if (!$attachment_id || $base !== $actual_base) {
            $errors[] = 'Image order/base mismatch at slot ' . ($index + 1) . '.';
            continue;
        }

        if ((int) get_post_field('post_parent', $attachment_id) !== $product_id) {
            $errors[] = 'Attachment parent mismatch for ' . $filename . '.';
        }

        if ($alt_map[$base] !== (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) {
            $errors[] = 'Alt text mismatch for ' . $filename . '.';
        }

        $path = trailingslashit(wp_upload_dir()['basedir']) . $attached_file;

        if (!file_exists($path)) {
            $errors[] = 'Missing uploads file for ' . $filename . '.';
            continue;
        }

        $dimensions = @getimagesize($path);

        if (!$dimensions || 450 !== (int) $dimensions[0] || 570 !== (int) $dimensions[1] || 'image/webp' !== strtolower((string) $dimensions['mime'])) {
            $errors[] = 'Image dimensions or format mismatch for ' . $filename . '.';
        }

        $like = '%' . $wpdb->esc_like($base) . '%';
        $candidate_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
            $like
        ));
        $exact_count = 0;

        foreach ($candidate_ids as $candidate_id) {
            $candidate_file = (string) get_post_meta((int) $candidate_id, '_wp_attached_file', true);

            if ($base === pathinfo(wp_basename($candidate_file), PATHINFO_FILENAME)) {
                $exact_count++;
            }
        }

        if (1 !== $exact_count) {
            $errors[] = 'Expected one attachment for ' . $base . ', found ' . $exact_count . '.';
        }
    }

    $content = (string) $product->post_content;
    preg_match_all('/<!--\s*stable-product-image:slot_\d+\s*-->/i', $content, $markers);
    preg_match_all('/<figure\b/i', $content, $figures);
    preg_match_all('/<img\b/i', $content, $images);

    if (4 !== count($markers[0]) || 4 !== count($figures[0]) || 4 !== count($images[0])) {
        $errors[] = 'Expected four stable inline markers, figures and images.';
    }

    if (false !== strpos($content, 'IMAGE_SLOT_') || false !== strpos($content, 'SEO setup') || false !== strpos($content, 'Image SEO:') || false !== strpos($content, 'Structured-data notes') || false !== strpos($content, 'Fields to confirm before publishing')) {
        $errors[] = 'Administrative source sections or image placeholders leaked into customer content.';
    }

    if (preg_match('/<h1\b/i', $content)) {
        $errors[] = 'Saved product content contains an extra H1; the template supplies the single H1.';
    }

    foreach (array_slice($definition['images'], 1, 4) as $filename) {
        if (false === strpos($content, pathinfo($filename, PATHINFO_FILENAME))) {
            $errors[] = 'Inline image filename base is missing: ' . $filename . '.';
        }
    }

    if (false === strpos($content, home_url('/products/')) || false === strpos($content, home_url('/contact/'))) {
        $errors[] = 'Required internal links are missing.';
    }

    $faq_html = (string) get_post_meta($product_id, '_custom_box_product_faq_html', true);
    if (6 !== preg_match_all('/<details\b/i', $faq_html, $faq_items)) {
        $errors[] = 'Expected six visible FAQ items.';
    }

    $seo_values = array(
        'rank_math_title'         => vpn_canvas_tote_202609_source_meta($source, 'Meta title'),
        'rank_math_description'   => vpn_canvas_tote_202609_source_meta($source, 'Meta description'),
        'rank_math_focus_keyword' => vpn_canvas_tote_202609_source_meta($source, 'Primary keyword'),
        'rank_math_canonical_url' => get_permalink($product_id),
    );

    foreach ($seo_values as $meta_key => $expected) {
        if ($expected !== get_post_meta($product_id, $meta_key, true)) {
            $errors[] = 'SEO meta mismatch: ' . $meta_key . '.';
        }
    }

    $featured_url = wp_get_attachment_url($thumbnail_id);
    $social_values = array(
        'rank_math_facebook_title'       => $seo_values['rank_math_title'],
        'rank_math_facebook_description' => $seo_values['rank_math_description'],
        'rank_math_facebook_image_id'    => (string) $thumbnail_id,
        'rank_math_facebook_image'       => $featured_url,
        'rank_math_twitter_title'        => $seo_values['rank_math_title'],
        'rank_math_twitter_description'  => $seo_values['rank_math_description'],
        'rank_math_twitter_image_id'     => (string) $thumbnail_id,
        'rank_math_twitter_image'        => $featured_url,
        'rank_math_twitter_card_type'    => 'summary_large_image',
    );

    foreach ($social_values as $meta_key => $expected) {
        if ($expected !== get_post_meta($product_id, $meta_key, true)) {
            $errors[] = 'Social SEO meta mismatch: ' . $meta_key . '.';
        }
    }

    $robots = get_post_meta($product_id, 'rank_math_robots', true);
    if (array('index', 'follow') !== $robots) {
        $errors[] = 'Rank Math robots are not index/follow.';
    }

    foreach (array('_sku', '_regular_price', '_sale_price', '_price') as $price_or_identifier) {
        if ('' !== (string) get_post_meta($product_id, $price_or_identifier, true)) {
            $errors[] = 'Unverified product meta is populated: ' . $price_or_identifier . '.';
        }
    }

    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);
    $spec_values = array();

    if (is_array($specs)) {
        foreach ($specs as $row) {
            if (is_array($row) && isset($row['label'], $row['value'])) {
                $spec_values[$row['label']] = $row['value'];
            }
        }
    }

    if (21 !== count($spec_values) || 'Available on request' !== ($spec_values['Minimum Order Quantity (MOQ)'] ?? '') || 'Request a quote' !== ($spec_values['Single Piece Price'] ?? '')) {
        $errors[] = 'Unverified MOQ or price is present in product specs.';
    }

    return array(
        'id'          => $product_id,
        'slug'        => $definition['slug'],
        'url'         => get_permalink($product_id),
        'attachments' => count($attachment_ids),
        'figures'     => count($figures[0]),
        'errors'      => $errors,
    );
}

try {
    $results = array();
    $failed = false;

    foreach (vpn_canvas_tote_202609_product_definitions() as $definition) {
        $result = vpn_canvas_tote_202609_verify_one($definition);
        $results[] = $result;
        $failed = $failed || !empty($result['errors']);
    }

    echo wp_json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit($failed ? 1 : 0);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
