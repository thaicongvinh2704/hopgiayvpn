<?php
/** Import the approved WooCommerce foodservice package. */

defined('ABSPATH') || exit;

const FOOD_BATCH = 'foodservice-concepts-20261125';
const FOOD_UPLOAD_SUBDIR = '2026/11/vpn-foodservice-20261125/';

function vpn_foodservice_require(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function vpn_foodservice_post(string $slug, string $type): ?WP_Post {
    if ($type !== 'product') {
        return get_page_by_path($slug, OBJECT, $type);
    }
    global $wpdb;
    $matches = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_name IN (%s, %s) AND post_type = 'product'",
        $slug, $slug . '__trashed'
    ));
    vpn_foodservice_require(count($matches) <= 1, 'Duplicate product slug: ' . $slug);
    return $matches ? get_post((int) $matches[0]) : null;
}

function vpn_foodservice_link(string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
}

function vpn_foodservice_existing_link(array $link, string $type): string {
    [$slug, $label] = $link;
    foreach (array_filter([$slug, $link[2] ?? null]) as $candidate) {
        $post = vpn_foodservice_post($candidate, $type);
        if ($post && $post->post_status === 'publish') {
            return vpn_foodservice_link(get_permalink($post), $label);
        }
    }
    throw new RuntimeException('Missing published internal link: ' . $slug);
}

function vpn_foodservice_specs(array $p): array {
    [$feature, $industry, $paper, $type, $shape, $accessories, $liner, $print, $color, $size, $thickness] = $p['spec'];
    $values = [
        'Feature' => $feature, 'Industrial Use' => $industry, 'Paper Type' => $paper, 'Box Type' => $type,
        'Shape' => $shape, 'Place of Origin' => 'Vietnam', 'Model Number' => $p['sku'], 'Brand Name' => 'VPN',
        'Province' => 'Ho Chi Minh City', 'Accessories' => $accessories,
        'Custom Order' => 'Available for project review and physical sample approval.',
        'Liner Type' => $liner, 'Logo Printing' => 'Custom logo artwork reviewed on the approved dieline.',
        'Printing Handling' => $print, 'Color' => $color, 'Size' => $size, 'Thickness' => $thickness,
        'Single Piece Price' => 'Written quotation based on specification and quantity.',
        'Minimum Order Quantity (MOQ)' => 'Confirmed by size, artwork and specification in the quotation.',
        'Product Name' => $p['name'], 'Design' => 'Design visualization. Final construction agreed on a physical sample.',
    ];
    $rows = [];
    foreach (custom_box_product_spec_default_rows() as $field) {
        $label = $field['label'];
        vpn_foodservice_require(!empty($values[$label]), 'Missing specification: ' . $label);
        $rows[] = ['label' => $label, 'value' => $values[$label]];
    }
    return $rows;
}

function vpn_foodservice_attach(array $asset, string $upload_root, int $product_id): int {
    global $wpdb;
    $name = $asset['filename'];
    $rel = FOOD_UPLOAD_SUBDIR . $name;
    $source = __DIR__ . '/webp/' . $name;
    $target = trailingslashit($upload_root) . $rel;
    $found = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT m.post_id FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id
         WHERE m.meta_key = '_wp_attached_file' AND m.meta_value = %s AND p.post_type = 'attachment'",
        $rel
    ));
    vpn_foodservice_require(count($found) <= 1, 'Conflicting media attachments: ' . $rel);
    if (is_file($target)) {
        vpn_foodservice_require(hash_file('sha256', $target) === hash_file('sha256', $source), 'Different existing media: ' . $rel);
    } else {
        vpn_foodservice_require(copy($source, $target), 'Unable to copy media: ' . $rel);
    }
    if ($found) {
        $id = (int) $found[0];
        vpn_foodservice_require(
            get_post_meta($id, '_vpn_foodservice_visual', true) === FOOD_BATCH || (int) wp_get_post_parent_id($id) === $product_id,
            'Unrelated existing media attachment: ' . $rel
        );
    } else {
        $id = wp_insert_attachment([
            'post_parent' => $product_id, 'post_status' => 'inherit',
            'post_mime_type' => 'image/webp', 'post_title' => $asset['title'],
            'post_content' => 'Product design visualization.',
        ], $target, $product_id, true);
        vpn_foodservice_require(!is_wp_error($id) && (bool) $id, 'Unable to create media attachment: ' . $rel);
        $id = (int) $id;
    }
    update_post_meta($id, '_vpn_foodservice_visual', FOOD_BATCH);
    update_post_meta($id, '_wp_attachment_image_alt', $asset['alt']);
    $meta = wp_get_attachment_metadata($id);
    if (!$meta || empty($meta['width'])) {
        $meta = wp_generate_attachment_metadata($id, $target);
        vpn_foodservice_require(is_array($meta) && !empty($meta['width']), 'Unable to generate image metadata: ' . $rel);
        wp_update_attachment_metadata($id, $meta);
    }
    vpn_foodservice_require((int) $meta['width'] === 450 && (int) $meta['height'] === 570, 'Unexpected original dimensions: ' . $rel);
    return $id;
}

function vpn_foodservice_html(array $p, array $images, array $batch_slugs): string {
    $fragments = ['<p><small>Images show the proposed packaging design. Confirm materials, dimensions and performance on an approved physical sample.</small></p>'];
    foreach ($p['sections'] as $index => [$heading, $paragraphs]) {
        $fragments[] = '<h2>' . esc_html($heading) . '</h2>';
        foreach ($paragraphs as $paragraph) {
            $fragments[] = '<p>' . esc_html($paragraph) . '</p>';
        }
        // Place structural shots beside the corresponding buyer questions in the text.
        $figure_index = array_search($index, [0, 2, 4], true);
        if ($figure_index !== false && isset($p['figures'][$figure_index])) {
            $view = $p['figures'][$figure_index];
            $figure = wp_get_attachment_image($images[$view - 1], 'full', false, [
                'loading' => 'lazy', 'decoding' => 'async',
                'sizes' => '(max-width: 767px) calc(100vw - 36px), 450px',
            ]);
            vpn_foodservice_require((bool) $figure, 'Unable to render inline image');
            $fragments[] = '<figure class="product-inline-figure product-inline-figure-small">' . $figure .
                '<figcaption>' . esc_html($p['captions'][$view]) . '</figcaption></figure>';
        }
    }
    $fragments[] = '<h2>Frequently asked buyer questions</h2>';
    foreach ($p['faq'] as [$question, $answer]) {
        $fragments[] = '<h3>' . esc_html($question) . '</h3><p>' . esc_html($answer) . '</p>';
    }
    $term = get_term_by('slug', $p['category'], 'product_cat');
    $links = [vpn_foodservice_link(get_term_link($term), $term->name), vpn_foodservice_existing_link($p['guide'], 'post')];
    foreach ($p['related'] as [$slug, $label]) {
        $post = vpn_foodservice_post($slug, 'product');
        if ($post && $post->post_status === 'publish') {
            $links[] = vpn_foodservice_link(get_permalink($post), $label);
        } elseif (in_array($slug, $batch_slugs, true)) {
            $structure = get_post_type_object('product')->rewrite['slug'];
            $links[] = vpn_foodservice_link(home_url(user_trailingslashit('/' . trim($structure, '/') . '/' . $slug, 'single')), $label);
        } else {
            throw new RuntimeException('Missing published product link: ' . $slug);
        }
    }
    $fragments[] = '<p>Compare related packaging and buyer resources.</p><ul><li>' . implode('</li><li>', $links) . '</li></ul>';
    $fragments[] = '<p>' . vpn_foodservice_existing_link(['contact', 'Request a sample and project quotation for ' . strtolower($p['name'])], 'page') . '</p>';
    return wp_kses_post(implode("\n", $fragments));
}

function vpn_foodservice_verify(array $p, string $expected_status): array {
    $post = vpn_foodservice_post($p['slug'], 'product');
    vpn_foodservice_require($post && $post->post_status === $expected_status && $post->post_password === '', 'Unexpected product visibility: ' . $p['slug']);
    $id = $post->ID;
    $wc = wc_get_product($id);
    vpn_foodservice_require($wc && $wc->get_catalog_visibility() === 'visible' && $wc->get_price() === '' && $wc->get_sku() === $p['sku'], 'Commercial fields differ: ' . $p['slug']);
    vpn_foodservice_require(get_post_meta($id, '_vpn_sample_import', true) === FOOD_BATCH, 'Unexpected product ownership');
    $specs = get_post_meta($id, '_custom_box_product_specs', true);
    vpn_foodservice_require(is_array($specs) && count($specs) === 21, 'Specification rows differ');
    $categories = wp_get_object_terms($id, 'product_cat', ['fields' => 'slugs']);
    vpn_foodservice_require($categories === [$p['category']], 'Unexpected categories');
    foreach (['rank_math_title' => 'title', 'rank_math_description' => 'meta', 'rank_math_focus_keyword' => 'keyword'] as $key => $field) {
        vpn_foodservice_require(get_post_meta($id, $key, true) === $p[$field], 'Unexpected Rank Math metadata: ' . $key);
    }
    $images = array_merge([$wc->get_image_id()], $wc->get_gallery_image_ids());
    $expected_images = count($p['images']);
    vpn_foodservice_require(count($images) === $expected_images && count(array_unique($images)) === $expected_images, 'Unexpected number of distinct image attachments');
    foreach ($images as $index => $image) {
        $asset = $p['images'][$index];
        $path = get_attached_file($image);
        vpn_foodservice_require($path && is_file($path) && wp_basename($path) === $asset['filename'] &&
            hash_equals($asset['sha256'], hash_file('sha256', $path)) &&
            (int) wp_get_post_parent_id($image) === $id &&
            get_post_meta($image, '_wp_attachment_image_alt', true) === $asset['alt'], 'Image or alt mismatch');
    }
    $description = $wc->get_description();
    $words = str_word_count(wp_strip_all_tags($description));
    vpn_foodservice_require($words >= 1500 && $words <= 2000 && !preg_match('/<h1\b/i', $description) &&
        substr_count($description, 'product-inline-figure-small') === count($p['figures']) &&
        substr_count($description, '<h3>') === 4, 'Description is incomplete: ' . $p['slug'] . ' (' . $words . ')');
    return [
        'id' => $id, 'status' => $expected_status, 'slug' => $p['slug'], 'url' => get_permalink($id),
        'images' => count($images), 'words' => $words, 'focus_keyword' => $p['keyword'],
        'seo_title' => $p['title'], 'meta_description' => $p['meta'],
    ];
}

function vpn_foodservice_release_publish(): array {
    global $wpdb;
    $lock = 'vpn_foodservice_' . md5(DB_NAME . $wpdb->prefix);
    vpn_foodservice_require((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) === '1', 'Another import is running');
    try {
        return vpn_foodservice_release('--publish');
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}

function vpn_foodservice_release(string $action): array {
    vpn_foodservice_require(class_exists('WC_Product_Simple') && defined('RANK_MATH_VERSION'), 'WooCommerce and Rank Math required');
    vpn_foodservice_require(in_array($action, ['--preflight', '--publish', '--verify'], true), 'Unknown action');
    $known = [
        'custom-printed-baguette-paper-bags', 'custom-paper-bread-bags-with-window',
        'custom-square-bottom-bakery-paper-bags', 'custom-flat-handle-kraft-paper-bags',
    ];
    $excluded = [];
    foreach ($known as $slug) {
        $post = vpn_foodservice_post($slug, 'product');
        $excluded[$slug] = $post ? $post->ID : null;
    }
    $catalog_count = (int) wp_count_posts('product')->publish;
    $rows = json_decode(file_get_contents(__DIR__ . '/products.json'), true, 512, JSON_THROW_ON_ERROR);
    vpn_foodservice_require(count($rows) === 4, 'Expected four release products');
    $products = [];
    $base = wp_upload_dir(null, false);
    vpn_foodservice_require(empty($base['error']), 'Upload directory unavailable');
    foreach ($rows as $p) {
        $keyword = explode(',', $p['keyword'], 2)[0];
        vpn_foodservice_require(strlen($p['title']) <= 60 && strlen($p['meta']) <= 155 &&
            stripos($p['title'], $keyword) !== false && stripos($p['meta'], $keyword) !== false &&
            str_contains($p['title'], 'from Vietnam') && str_contains($p['meta'], 'VPN Packaging in Vietnam'),
            'Invalid SEO fields for ' . $p['slug']);
        $short_words = str_word_count($p['short']);
        vpn_foodservice_require($short_words >= 120 && $short_words <= 180, 'Invalid short description length');
        $term = get_term_by('slug', $p['category'], 'product_cat');
        vpn_foodservice_require($term instanceof WP_Term && !is_wp_error(get_term_link($term)), 'Missing category ' . $p['category']);
        vpn_foodservice_existing_link($p['guide'], 'post');
        vpn_foodservice_existing_link(['contact', 'Quote'], 'page');
        $p['term'] = (int) $term->term_id;
        $post = vpn_foodservice_post($p['slug'], 'product');
        vpn_foodservice_require(!$post || (get_post_meta($post->ID, '_vpn_sample_import', true) === FOOD_BATCH &&
            in_array($post->post_status, ['draft', 'publish'], true)), 'Unrelated slug collision: ' . $p['slug']);
        $sku_id = wc_get_product_id_by_sku($p['sku']);
        vpn_foodservice_require(!$sku_id || ($post && (int) $sku_id === $post->ID), 'SKU collision: ' . $p['sku']);
        $expected_images = $p['slug'] === 'custom-kraft-food-delivery-bags-with-paper-seal' ? 6 : 5;
        vpn_foodservice_require(count($p['images']) === $expected_images, 'Unexpected source image count: ' . $p['slug']);
        foreach ($p['images'] as $index => $asset) {
            $file = $asset['filename'];
            vpn_foodservice_require(wp_basename($file) === $file && str_starts_with($file, $p['prefix'] . '-' . sprintf('%02d', $index + 1) . '-'),
                'Unexpected asset filename: ' . $file);
            $source = __DIR__ . '/webp/' . $file;
            $info = is_file($source) ? getimagesize($source) : false;
            vpn_foodservice_require($info && $info[0] === 450 && $info[1] === 570 && $info['mime'] === 'image/webp' &&
                filesize($source) < 100000 && hash_equals($asset['sha256'], hash_file('sha256', $source)) &&
                trim($asset['alt']) !== '', 'Invalid source image: ' . $file);
            $target = trailingslashit($base['basedir']) . FOOD_UPLOAD_SUBDIR . $file;
            vpn_foodservice_require(!is_file($target) || hash_file('sha256', $target) === hash_file('sha256', $source), 'Different upload already exists: ' . $file);
        }
        $products[$p['slug']] = $p;
    }
    foreach ($products as $p) {
        foreach ($p['related'] as $link) {
            if (!isset($products[$link[0]])) {
                vpn_foodservice_existing_link($link, 'product');
            }
        }
    }
    if ($action === '--preflight') {
        return ['preflight' => 'passed', 'published_before' => $catalog_count, 'excluded' => $excluded,
            'candidates' => array_keys($products), 'selected_images' => array_sum(array_map(static fn($p) => count($p['images']), $products))];
    }
    if ($action === '--publish') {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        vpn_foodservice_require(wp_mkdir_p(trailingslashit($base['basedir']) . FOOD_UPLOAD_SUBDIR), 'Unable to create upload directory');
        $ready = [];
        foreach ($products as $slug => $p) {
            $post = vpn_foodservice_post($slug, 'product');
            if ($post && $post->post_status === 'publish') {
                vpn_foodservice_verify($p, 'publish');
                continue;
            }
            $wc = $post ? new WC_Product_Simple($post->ID) : new WC_Product_Simple();
            $wc->set_name($p['name']);
            $wc->set_slug($slug);
            $wc->set_sku($p['sku']);
            $wc->set_status('draft');
            $wc->set_catalog_visibility('visible');
            $wc->set_category_ids([$p['term']]);
            $wc->set_reviews_allowed(false);
            $wc->set_manage_stock(false);
            $wc->set_regular_price('');
            $wc->set_sale_price('');
            $wc->set_price('');
            $wc->set_short_description($p['short']);
            $wc->update_meta_data('_vpn_sample_import', FOOD_BATCH);
            $wc->update_meta_data('_vpn_visual_status', 'Product design visualization');
            $id = $wc->save();
            vpn_foodservice_require((bool) $id, 'Could not save product draft: ' . $slug);
            $tags = array_map(static fn($tag) => ucwords(str_replace('-', ' ', $tag)), $p['tag']);
            vpn_foodservice_require(!is_wp_error(wp_set_object_terms($id, $tags, 'product_tag')), 'Could not set product tags');
            $images = [];
            foreach ($p['images'] as $asset) {
                $images[] = vpn_foodservice_attach($asset, $base['basedir'], $id);
            }
            $wc->set_image_id($images[0]);
            $wc->set_gallery_image_ids(array_slice($images, 1));
            $wc->set_description(vpn_foodservice_html($p, $images, array_keys($products)));
            $wc->update_meta_data('_custom_box_product_specs', vpn_foodservice_specs($p));
            $wc->save();
            foreach ([
                'rank_math_title' => $p['title'], 'rank_math_description' => $p['meta'],
                'rank_math_focus_keyword' => $p['keyword'], 'rank_math_primary_product_cat' => $p['term'],
                'rank_math_facebook_title' => $p['title'], 'rank_math_facebook_description' => $p['meta'],
                'rank_math_twitter_title' => $p['title'], 'rank_math_twitter_description' => $p['meta'],
            ] as $key => $value) {
                update_post_meta($id, $key, $value);
            }
            clean_post_cache($id);
            vpn_foodservice_verify($p, 'draft');
            $ready[] = $id;
        }
        $published_now = [];
        try {
            foreach ($ready as $id) {
                $published_now[] = $id;
                $wc = new WC_Product_Simple($id);
                $wc->set_status('publish');
                $wc->save();
                wc_delete_product_transients($id);
                clean_post_cache($id);
            }
            foreach ($products as $p) {
                vpn_foodservice_verify($p, 'publish');
            }
        } catch (Throwable $e) {
            foreach ($published_now as $id) {
                wp_update_post(['ID' => $id, 'post_status' => 'draft']);
            }
            throw $e;
        }
        if ($ready && class_exists('RankMath\Sitemap\Cache')) {
            \RankMath\Sitemap\Cache::invalidate_storage();
        }
    }
    $verified = array_values(array_map(static fn($p) => vpn_foodservice_verify($p, 'publish'), $products));
    return ['status' => 'verified', 'excluded' => $excluded, 'products' => $verified,
        'published_count' => (int) wp_count_posts('product')->publish];
}
