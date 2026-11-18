<?php
/** Imports the fixed owner-approved WooCommerce package. */

defined('ABSPATH') || exit;

const NORELUNE_BATCH = 'vpn-norelune-20261118';

function norelune_require($result, string $message): void {
    if (is_wp_error($result)) {
        throw new RuntimeException($message . ' ' . $result->get_error_message());
    }
    if (!$result) {
        throw new RuntimeException($message);
    }
}

function norelune_lookup_product(string $slug): ?WP_Post {
    global $wpdb;
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_name IN (%s, %s) AND post_type NOT IN ('attachment', 'revision')",
        $slug, $slug . '__trashed'
    ));
    norelune_require(count($ids) <= 1, 'Duplicate slug: ' . $slug);
    return $ids ? get_post((int) $ids[0]) : null;
}

function norelune_seo(array $row, int $category): array {
    return [
        'rank_math_title' => $row['seo'],
        'rank_math_description' => $row['description'],
        'rank_math_focus_keyword' => $row['keyword'],
        'rank_math_primary_product_cat' => $category,
        'rank_math_facebook_title' => $row['seo'],
        'rank_math_facebook_description' => $row['description'],
        'rank_math_twitter_title' => $row['seo'],
        'rank_math_twitter_description' => $row['description'],
        'rank_math_robots' => ['index', 'follow'],
    ];
}

function norelune_assets(array $row, int $id): array {
    $attachments = [];
    foreach ($row['assets'] as $index => $source) {
        $index = (int) $index;
        $key = NORELUNE_BATCH . '/' . $row['sku'] . '/' . $index;
        $found = get_posts([
            'post_type' => 'attachment', 'post_status' => ['inherit', 'private', 'trash'],
            'numberposts' => 2, 'fields' => 'ids', 'meta_key' => '_vpn_norelune_asset', 'meta_value' => $key,
        ]);
        norelune_require(count($found) <= 1, 'Duplicate media marker.');
        if ($found) {
            $attachment = (int) $found[0];
        } else {
            $temp = wp_tempnam(basename($source));
            norelune_require($temp && copy($source, $temp), 'Could not stage ' . basename($source));
            $attachment = media_handle_sideload(['name' => basename($source), 'tmp_name' => $temp], $id, '', wp_slash([
                'post_title' => $row['alts'][$index],
                'post_excerpt' => $row['captions'][$index],
                'post_content' => 'Product design reference from the supplied Norelune website image collection.',
                'meta_input' => ['_vpn_norelune_asset' => $key, '_wp_attachment_image_alt' => $row['alts'][$index]],
            ]));
            if (is_wp_error($attachment) && is_file($temp)) {
                wp_delete_file($temp);
            }
            norelune_require($attachment, 'Image import failed.');
        }
        $path = get_attached_file($attachment);
        norelune_require((int) get_post_field('post_parent', $attachment) === $id
            && get_post_status($attachment) !== 'trash' && $path && is_file($path)
            && hash_file('sha256', $source) === hash_file('sha256', $path)
            && get_post_meta($attachment, '_wp_attachment_image_alt', true) === $row['alts'][$index]
            && get_post_field('post_title', $attachment) === $row['alts'][$index]
            && wp_get_attachment_caption($attachment) === $row['captions'][$index]
            && !empty(wp_get_attachment_metadata($attachment)['sizes']), 'Image validation failed for ' . $source);
        $attachments[] = $attachment;
    }
    return $attachments;
}

function norelune_figure(array $row, array $images, int $index): string {
    $image = wp_get_attachment_image($images[$index], 'large', false, [
        'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 600px) calc(100vw - 40px), 560px',
    ]);
    return '<figure class="product-inline-figure product-inline-figure-small">' . $image
        . '<figcaption>' . esc_html($row['captions'][$index]) . '</figcaption></figure>';
}

function norelune_body(array $row, array $images, WP_Term $cosmetic, WP_Term $structure, string $related): string {
    $out = '<h2>' . esc_html($row['label'] . ' Design Reference') . '</h2><p>'
        . esc_html('The Norelune artwork and bottle markings illustrate one packaging design. VPN supplies made-to-order paper packaging from Vietnam. Confirm the structure, print and fit on your approved sample.') . '</p>';
    $out .= '<p>Explore <a href="' . esc_url(get_term_link($cosmetic)) . '">custom cosmetic packaging</a> or compare other <a href="'
        . esc_url(get_term_link($structure)) . '">' . esc_html(strtolower($structure->name)) . '</a> for your range.</p>';
    $positions = [0 => 2, 2 => 3, 3 => 4];
    foreach ($row['sections'] as $index => [$heading, $paragraph]) {
        $content = str_replace('{related}', '<a href="' . esc_url($related) . '">' . esc_html($row['related_text']) . '</a>', esc_html($paragraph));
        $out .= '<h2>' . esc_html($heading) . '</h2><p>' . $content . '</p>';
        if (isset($positions[$index])) {
            $out .= norelune_figure($row, $images, $positions[$index]);
        }
    }
    $out .= '<h2>Questions Before Ordering</h2>';
    foreach ($row['faq'] as [$question, $answer]) {
        $out .= '<h3>' . esc_html($question) . '</h3><p>' . esc_html($answer) . '</p>';
    }
    $out .= '<p><a href="' . esc_url(home_url('/custom-packaging-boxes-manufacturer/#quote')) . '">'
        . esc_html('Request a bulk quote for the ' . $row['label'] . ' format') . '</a></p>';
    return wp_kses_post($out);
}

function norelune_specs(array $row): array {
    $fields = [
        'Feature' => $row['shape'], 'Industrial Use' => $row['use'], 'Paper Type' => $row['board'],
        'Box Type' => $row['style'], 'Shape' => $row['shape'], 'Place of Origin' => 'Vietnam',
        'Model Number' => $row['sku'], 'Brand Name' => 'VPN', 'Province' => 'Ho Chi Minh City',
        'Accessories' => $row['insert'], 'Custom Order' => 'Accept', 'Liner Type' => 'Confirm liner and contact surfaces on the sample',
        'Logo Printing' => 'Custom logo', 'Printing Handling' => 'Review colors and finishes against approved artwork and sample',
        'Color' => $row['design'], 'Size' => 'Customized to the filled bottles and approved layout',
        'Thickness' => 'Confirmed from the proposed board and structure', 'Single Piece Price' => 'Quoted by specification and quantity',
        'Minimum Order Quantity (MOQ)' => 'General MOQ 1,000 boxes. Confirm quantity per design in the quote.',
        'Product Name' => $row['title'], 'Design' => 'Customer artwork and requirements, subject to sample approval',
    ];
    $specs = [];
    foreach ($fields as $label => $value) {
        $specs[] = ['label' => $label, 'value' => $value];
    }
    return $specs;
}

function norelune_release_package(): array {
    $rows = json_decode((string) file_get_contents(__DIR__ . '/products.json'), true, 512, JSON_THROW_ON_ERROR);
    norelune_require(count($rows) === 5 && count(array_unique(array_column($rows, 'slug'))) === 5
        && count(array_unique(array_column($rows, 'sku'))) === 5, 'Unexpected product bundle.');
    foreach ($rows as &$row) {
        norelune_require((bool) preg_match('/^[a-z0-9-]+$/D', $row['slug'])
            && count($row['files']) === 5 && count($row['alts']) === 5 && count($row['captions']) === 5
            && strlen($row['seo']) <= 60 && strlen($row['description']) <= 155
            && stripos($row['seo'], $row['keyword']) !== false
            && stripos($row['description'], $row['keyword']) !== false, 'Invalid product bundle: ' . $row['slug']);
        $row['assets'] = [];
        foreach ($row['files'] as $index => $asset) {
            $name = $asset['name'];
            $file = __DIR__ . '/images/' . $name;
            norelune_require(basename($name) === $name && str_starts_with($name, sprintf('%02d-norelune-', $index + 1))
                && is_file($file) && hash_equals($asset['sha256'], hash_file('sha256', $file)), 'Invalid image file: ' . $name);
            $size = wp_getimagesize($file);
            norelune_require($size && $size[0] === 1000 && $size[1] === 1000 && $size['mime'] === 'image/webp', 'Invalid image dimensions.');
            $row['assets'][] = $file;
        }
    }
    unset($row);
    return $rows;
}

function norelune_release_context(): WP_Term {
    norelune_require(class_exists('WC_Product_Simple') && defined('RANK_MATH_VERSION'), 'WooCommerce or Rank Math unavailable.');
    norelune_require(wp_image_editor_supports(['mime_type' => 'image/webp']), 'WebP image processing unavailable.');
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $category = get_term_by('slug', 'cosmetic-paper-boxes', 'product_cat');
    norelune_require($category instanceof WP_Term, 'Cosmetic Paper Boxes category unavailable.');
    return $category;
}

function norelune_release_verify(WC_Product_Simple $product, array $row, array $images, string $body, array $terms, array $meta, string $status): void {
    $id = $product->get_id();
    norelune_require($product->get_status() === $status && $product->get_name() === $row['title']
        && $product->get_slug() === $row['slug'] && $product->get_sku() === $row['sku']
        && $product->get_catalog_visibility() === 'visible'
        && $product->get_description('edit') === $body && $product->get_short_description('edit') === $row['intro']
        && !$product->is_purchasable() && count(array_intersect($terms, $product->get_category_ids())) === 2
        && array_map('intval', array_merge([$product->get_image_id()], $product->get_gallery_image_ids())) === $images,
        'Product readback failed.');
    foreach ($meta as $key => $expected) {
        norelune_require(get_post_meta($id, $key, true) == $expected, 'SEO readback failed: ' . $key);
    }
}

function norelune_release_product(array $row, WP_Term $cosmetic): array {
    $slug = $row['slug'];
    $existing = norelune_lookup_product($slug);
    $sku_owner = wc_get_product_id_by_sku($row['sku']);
    norelune_require(!$existing || ($existing->post_type === 'product' && $existing->post_name === $slug
        && get_post_meta($existing->ID, '_vpn_norelune_batch', true) === NORELUNE_BATCH), 'Existing product or slug preserved.');
    norelune_require(!$sku_owner || ($existing && (int) $existing->ID === (int) $sku_owner), 'Existing product SKU preserved.');

    if ($existing && get_post_meta($existing->ID, '_vpn_norelune_complete', true)) {
        return ['id' => (int) $existing->ID, 'published' => false];
    }

    $structure = get_term_by('slug', $row['structure_cat'], 'product_cat');
    norelune_require($structure instanceof WP_Term, 'Structure category unavailable: ' . $row['structure_cat']);
    $terms = [(int) $cosmetic->term_id, (int) $structure->term_id];

    $product = $existing ? wc_get_product($existing->ID) : new WC_Product_Simple();
    norelune_require($product instanceof WC_Product_Simple, 'Existing product type preserved.');
    if ($existing) {
        $recorded = get_post_meta($existing->ID, '_vpn_norelune_copy_hash', true);
        norelune_require($product->get_name() === $row['title'] && $product->get_sku() === $row['sku']
            && in_array($product->get_status(), ['draft', 'publish'], true)
            && (!$product->get_description('edit') || ($recorded && hash_equals($recorded, hash('sha256', $product->get_description('edit'))))),
            'Manually edited or unpublished listing preserved.');
    } else {
        $product->set_name($row['title']);
        $product->set_slug($slug);
        $product->set_sku($row['sku']);
        $product->set_status('draft');
        $product->set_catalog_visibility('visible');
        $product->set_category_ids($terms);
        $product->update_meta_data('_vpn_norelune_batch', NORELUNE_BATCH);
        $product->update_meta_data('_custom_box_hide_auto_description_heading', true);
        norelune_require($product->save(), 'Draft could not be created.');
    }

    $id = $product->get_id();
    $images = norelune_assets($row, $id);
    $body = norelune_body($row, $images, $cosmetic, $structure, home_url('/product/' . $row['related'] . '/'));
    norelune_require(substr_count($body, '<figure') === 3 && !str_contains($body, '{related}'), 'Invalid product content.');
    $meta = norelune_seo($row, (int) $cosmetic->term_id);
    $meta['rank_math_facebook_image'] = wp_get_attachment_url($images[0]);
    $meta['rank_math_facebook_image_id'] = (string) $images[0];

    if ($product->get_status() === 'publish') {
        // Recover a request interrupted immediately after publication without rewriting the product.
        norelune_release_verify($product, $row, $images, $body, $terms, $meta, 'publish');
        update_post_meta($id, '_vpn_norelune_complete', true);
        return ['id' => $id, 'published' => false];
    }

    $product->set_image_id($images[0]);
    $product->set_gallery_image_ids(array_slice($images, 1));
    $product->set_short_description($row['intro']);
    $product->set_description($body);
    $product->set_regular_price('');
    $product->set_price('');
    $product->set_manage_stock(false);
    $product->set_stock_status('onbackorder');
    $product->update_meta_data('_custom_box_product_specs', norelune_specs($row));
    $product->update_meta_data('_custom_box_product_hero_bullets', [
        'Feature' => $row['shape'], 'Industrial Use' => $row['use'], 'Paper Type' => $row['board'], 'Box Type' => $row['style'],
    ]);
    $product->update_meta_data('_vpn_norelune_copy_hash', hash('sha256', $body));
    norelune_require($product->save(), 'Content could not be saved.');
    foreach ($meta as $key => $value) {
        update_post_meta($id, $key, wp_slash($value));
    }
    norelune_release_verify(wc_get_product($id), $row, $images, $body, $terms, $meta, 'draft');
    $product->set_status('publish');
    norelune_require($product->save(), 'Product could not be published.');
    norelune_release_verify(wc_get_product($id), $row, $images, $body, $terms, $meta, 'publish');
    update_post_meta($id, '_vpn_norelune_complete', true);

    return ['id' => $id, 'published' => true];
}
