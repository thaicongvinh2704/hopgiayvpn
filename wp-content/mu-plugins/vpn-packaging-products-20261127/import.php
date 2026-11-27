<?php
defined('ABSPATH') || exit;

function vpn_packaging_nov_require(bool $valid, string $message): void {
    if (!$valid) {
        throw new RuntimeException($message);
    }
}

function vpn_packaging_nov_find(string $slug): ?WP_Post {
    global $wpdb;
    $matches = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_name IN (%s, %s)",
        $slug, $slug . '__trashed'
    ));
    vpn_packaging_nov_require(count($matches) <= 1, 'Duplicate product slug: ' . $slug);
    return $matches ? get_post((int) $matches[0]) : null;
}

function vpn_packaging_nov_attach(array $asset, string $basedir, int $product_id): int {
    global $wpdb;
    $relative = '2026/11/' . VPN_PACKAGING_NOV_RELEASE . '/' . $asset['file'];
    $target = trailingslashit($basedir) . $relative;
    $source = __DIR__ . '/webp/' . $asset['file'];
    $existing = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON p.ID = m.post_id
         WHERE p.post_type = 'attachment' AND m.meta_key = '_wp_attached_file' AND m.meta_value = %s",
        $relative
    ));
    vpn_packaging_nov_require(count($existing) <= 1, 'Duplicate attachment: ' . $asset['file']);
    if ($existing) {
        vpn_packaging_nov_require((int) wp_get_post_parent_id($existing[0]) === $product_id,
            'An unrelated attachment uses ' . $asset['file']);
    }
    if (!is_file($target)) {
        $temporary = wp_tempnam($asset['file'], dirname($target));
        vpn_packaging_nov_require($temporary !== false, 'Cannot stage image: ' . $asset['file']);
        try {
            vpn_packaging_nov_require(copy($source, $temporary) && hash_file('sha256', $temporary) === $asset['sha256']
                && rename($temporary, $target), 'Cannot copy image: ' . $asset['file']);
        } finally {
            if (is_file($temporary)) {
                wp_delete_file($temporary);
            }
        }
    }
    vpn_packaging_nov_require(hash_file('sha256', $target) === $asset['sha256'], 'Unexpected image already exists: ' . $asset['file']);
    if ($existing) {
        $id = (int) $existing[0];
    } else {
        $id = wp_insert_attachment([
            'post_parent' => $product_id, 'post_status' => 'inherit', 'post_mime_type' => 'image/webp',
            'post_title' => $asset['alt'], 'post_excerpt' => $asset['caption'],
        ], $target, $product_id, true);
        vpn_packaging_nov_require(is_int($id) && $id > 0, 'Cannot create attachment: ' . $asset['file']);
    }
    update_post_meta($id, '_wp_attachment_image_alt', $asset['alt']);
    $metadata = wp_get_attachment_metadata($id);
    if (empty($metadata['width'])) {
        $metadata = wp_generate_attachment_metadata($id, $target);
        vpn_packaging_nov_require(!empty($metadata['width']) && !empty($metadata['sizes']),
            'Cannot generate image sizes: ' . $asset['file']);
        wp_update_attachment_metadata($id, $metadata);
    }
    return $id;
}

function vpn_packaging_nov_metadata(array $row): array {
    return [
        'rank_math_title' => $row['seo_title'],
        'rank_math_description' => $row['seo_description'],
        'rank_math_focus_keyword' => $row['focus_keyword'],
        'rank_math_primary_product_cat' => (string) $row['terms'][0],
        'rank_math_facebook_title' => $row['seo_title'],
        'rank_math_facebook_description' => $row['seo_description'],
        'rank_math_twitter_title' => $row['seo_title'],
        'rank_math_twitter_description' => $row['seo_description'],
    ];
}

function vpn_packaging_nov_verify(array $row, string $status): array {
    $post = vpn_packaging_nov_find($row['slug']);
    vpn_packaging_nov_require($post && $post->post_name === $row['slug'] &&
        $post->post_status === $status && $post->post_password === '', 'Unexpected visibility: ' . $row['slug']);
    $product = wc_get_product($post->ID);
    vpn_packaging_nov_require($product instanceof WC_Product_Simple &&
        $product->get_meta('_vpn_packaging_release') === VPN_PACKAGING_NOV_RELEASE &&
        $product->get_catalog_visibility() === 'visible' && $product->get_price() === '' &&
        $product->get_sku() === $row['sku'] && $product->get_name() === $row['name'] &&
        $product->get_description() === $row['body'] && $product->get_short_description() === $row['excerpt'] &&
        $product->get_meta('_custom_box_product_specs') === $row['specs'],
        'Product fields differ: ' . $row['slug']);
    $actual_terms = array_map('intval', $product->get_category_ids());
    $expected_terms = $row['terms'];
    sort($actual_terms);
    sort($expected_terms);
    vpn_packaging_nov_require($actual_terms === $expected_terms, 'Unexpected categories: ' . $row['slug']);
    foreach (vpn_packaging_nov_metadata($row) as $key => $value) {
        vpn_packaging_nov_require(get_post_meta($post->ID, $key, true) === $value, 'Metadata differs: ' . $key . ' / ' . $row['slug']);
    }
    $images = array_merge([$product->get_image_id()], $product->get_gallery_image_ids());
    vpn_packaging_nov_require(count($images) === 6 && count(array_unique($images)) === 6,
        'Expected six distinct images: ' . $row['slug']);
    foreach ($images as $index => $image) {
        $asset = $row['images'][$index];
        $path = get_attached_file($image);
        $meta = wp_get_attachment_metadata($image);
        vpn_packaging_nov_require($path && is_file($path) && wp_basename($path) === $asset['file'] &&
            hash_file('sha256', $path) === $asset['sha256'] &&
            (int) ($meta['width'] ?? 0) === $asset['width'] && (int) ($meta['height'] ?? 0) === $asset['height'] &&
            (int) wp_get_post_parent_id($image) === $post->ID &&
            get_post_meta($image, '_wp_attachment_image_alt', true) === $asset['alt'] &&
            wp_get_attachment_caption($image) === $asset['caption'], 'Invalid product attachment: ' . $asset['file']);
    }
    return ['id' => $post->ID, 'name' => $row['name'], 'url' => get_permalink($post), 'images' => count($images)];
}

function vpn_packaging_nov_release_rows(): array {
    vpn_packaging_nov_require(class_exists('WC_Product_Simple') && defined('RANK_MATH_VERSION'),
        'WooCommerce and Rank Math must be active.');
    vpn_packaging_nov_require(wp_image_editor_supports(['mime_type' => 'image/webp']), 'WordPress requires WebP image support.');
    $rows = json_decode(file_get_contents(__DIR__ . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    vpn_packaging_nov_require(count($rows) === 7, 'Expected seven packaging products.');
    $slugs = $skus = $files = [];
    foreach ($rows as &$row) {
        $slug = $row['slug'];
        vpn_packaging_nov_require(sanitize_title($slug) === $slug
            && !isset($slugs[$slug]) && !isset($skus[$row['sku']]), 'Duplicate or invalid product identifier: ' . $slug);
        $slugs[$slug] = $skus[$row['sku']] = true;
        $path = __DIR__ . '/content/' . $slug . '.html';
        $raw_body = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
        vpn_packaging_nov_require(is_string($raw_body), 'Missing or unreadable product content: ' . $slug);
        // Manifest checksums use LF so Git checkouts can use different line endings.
        $source_body = str_replace(["\r\n", "\r"], "\n", $raw_body);
        vpn_packaging_nov_require(hash_equals($row['content_sha'], hash('sha256', $source_body)),
            'Product content checksum differs: ' . $slug);
        $body = trim($source_body);
        $row['body'] = wp_kses_post(str_replace('{{HOME}}', esc_url(untrailingslashit(home_url('/'))), $body));
        $row['excerpt'] = trim(wp_kses_post($row['excerpt']));
        vpn_packaging_nov_require(substr_count($row['body'], '<h2>') >= 3 && str_contains($row['body'], '/contact/#quote') &&
            !str_contains($row['body'], '{{HOME}}') && !preg_match('/<h1\b/i', $row['body']) &&
            str_contains($row['seo_title'], 'Vietnam') && strlen($row['seo_title']) <= 65 &&
            str_contains($row['seo_description'], 'Vietnam') && strlen($row['seo_description']) <= 160 &&
            count($row['images']) === 6 && count($row['specs']) >= 5, 'Incomplete product content: ' . $slug);
        $row['terms'] = [];
        foreach ($row['categories'] as $category) {
            $term = get_term_by('slug', $category, 'product_cat');
            vpn_packaging_nov_require($term instanceof WP_Term, 'Missing product category: ' . $category);
            $row['terms'][] = (int) $term->term_id;
        }
        vpn_packaging_nov_require(!empty($row['terms']), 'No product category: ' . $slug);
        $post = vpn_packaging_nov_find($slug);
        vpn_packaging_nov_require(!$post || ($post->post_name === $slug &&
            get_post_meta($post->ID, '_vpn_packaging_release', true) === VPN_PACKAGING_NOV_RELEASE &&
            in_array($post->post_status, ['draft', 'publish'], true)), 'Existing unrelated product: ' . $slug);
        $sku = wc_get_product_id_by_sku($row['sku']);
        vpn_packaging_nov_require(!$sku || ($post && (int) $sku === $post->ID), 'SKU already used: ' . $row['sku']);
        foreach ($row['images'] as $asset) {
            $file = $asset['file'];
            vpn_packaging_nov_require(!isset($files[$file]) && preg_match('/^[a-z0-9-]+\.webp$/D', $file)
                && str_starts_with($file, $slug . '-') && trim($asset['alt']) !== '', 'Invalid image name: ' . $file);
            $files[$file] = true;
            $path = __DIR__ . '/webp/' . $file;
            $info = is_file($path) ? getimagesize($path) : false;
            vpn_packaging_nov_require($info && $info['mime'] === 'image/webp' && $info[0] === $asset['width'] &&
                $info[1] === $asset['height'] && filesize($path) < 250000 &&
                hash_file('sha256', $path) === $asset['sha256'], 'Invalid image file: ' . $file);
        }
    }
    unset($row);
    return $rows;
}

function vpn_packaging_nov_publish(): array {
    global $wpdb;
    $lock = 'vpn_packaging_20261127_' . md5(DB_NAME . $wpdb->prefix);
    vpn_packaging_nov_require((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) === '1', 'Another product import is running.');
    try {
        $state = get_option(VPN_PACKAGING_NOV_RELEASE, []);
        if (isset($state['products'])) {
            return $state['products'];
        }
        $rows = vpn_packaging_nov_release_rows();
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $uploads = wp_upload_dir(null, false);
        vpn_packaging_nov_require(empty($uploads['error']) &&
            wp_mkdir_p(trailingslashit($uploads['basedir']) . '2026/11/' . VPN_PACKAGING_NOV_RELEASE),
            'WordPress uploads are unavailable.');
        $ready = [];
        foreach ($rows as $row) {
            $post = vpn_packaging_nov_find($row['slug']);
            if ($post && $post->post_status === 'publish') {
                vpn_packaging_nov_verify($row, 'publish');
                continue;
            }
            if (!$post) {
                $id = wp_insert_post(wp_slash([
                    'post_type' => 'product', 'post_status' => 'draft', 'post_name' => $row['slug'], 'post_title' => $row['name'],
                    'meta_input' => ['_vpn_packaging_release' => VPN_PACKAGING_NOV_RELEASE],
                ]), true);
                vpn_packaging_nov_require(is_int($id) && $id > 0, 'Cannot create draft: ' . $row['slug']);
                $post = get_post($id);
            }
            $product = new WC_Product_Simple($post->ID);
            $product->set_name($row['name']);
            $product->set_slug($row['slug']);
            $product->set_status('draft');
            $product->set_sku($row['sku']);
            $product->set_catalog_visibility('visible');
            $product->set_category_ids($row['terms']);
            $product->set_description($row['body']);
            $product->set_short_description($row['excerpt']);
            $product->set_reviews_allowed(false);
            $product->set_regular_price('');
            $product->set_sale_price('');
            $product->set_price('');
            $product->set_manage_stock(false);
            $ids = [];
            foreach ($row['images'] as $asset) {
                $ids[] = vpn_packaging_nov_attach($asset, $uploads['basedir'], $post->ID);
            }
            $product->set_image_id($ids[0]);
            $product->set_gallery_image_ids(array_slice($ids, 1));
            $product->update_meta_data('_custom_box_product_specs', $row['specs']);
            $product->save();
            foreach (vpn_packaging_nov_metadata($row) as $key => $value) {
                update_post_meta($post->ID, $key, wp_slash($value));
            }
            clean_post_cache($post->ID);
            vpn_packaging_nov_verify($row, 'draft');
            $ready[] = $post->ID;
        }
        $published = [];
        try {
            foreach ($ready as $id) {
                $published[] = $id;
                $product = new WC_Product_Simple($id);
                $product->set_status('publish');
                $product->save();
                clean_post_cache($id);
            }
            $result = array_map(static fn(array $row): array => vpn_packaging_nov_verify($row, 'publish'), $rows);
            if ($ready && class_exists('RankMath\Sitemap\Cache')) {
                \RankMath\Sitemap\Cache::invalidate_storage();
            }
            vpn_packaging_nov_require(update_option(VPN_PACKAGING_NOV_RELEASE, ['products' => $result], false),
                'Unable to record the completed update.');
            return $result;
        } catch (Throwable $error) {
            foreach ($published as $id) {
                wp_update_post(['ID' => $id, 'post_status' => 'draft']);
                wc_delete_product_transients($id);
            }
            throw $error;
        }
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}
