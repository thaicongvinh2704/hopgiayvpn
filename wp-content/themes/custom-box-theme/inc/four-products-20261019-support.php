<?php
/** Fixed four-product bundle; invoked only by the authenticated admin sync or CLI wrapper. */
defined('ABSPATH') || exit;
const VPN_FOUR_20261019_VERSION = 'four-products-20261019-v1';
const VPN_FOUR_20261019_OPTION = 'custom_box_four_products_20261019_sync_version';
function vpn_four_20261019_source(): array {
    $data = json_decode(file_get_contents(__DIR__ . '/product-content/four-products-20261019/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $expected = ['custom-happy-birthday-gift-paper-bags', 'custom-brown-kraft-bakery-paper-bags', 'custom-printed-white-flat-paper-bags', 'custom-white-kraft-mailer-boxes'];
    if (($data['marker'] ?? '') !== VPN_FOUR_20261019_VERSION || array_column($data['products'], 'slug') !== $expected) { throw new RuntimeException('Unexpected four-product manifest.'); }
    return $data;
}
function vpn_four_20261019_body(array $p): string {
    return str_replace(["\r\n", "\r"], "\n", file_get_contents(__DIR__ . '/product-content/four-products-20261019/' . basename($p['content_file'])));
}
function vpn_four_20261019_completion_errors(): array {
    $errors = [];
    foreach (vpn_four_20261019_source()['products'] as $p) {
        $post = get_page_by_path($p['slug'], OBJECT, 'product');
        $product = $post ? wc_get_product($post->ID) : false;
        if (!$product || get_post_meta($post->ID, '_vpn_sample_import', true) !== VPN_FOUR_20261019_VERSION || !get_post_meta($post->ID, '_vpn_four_20261019_complete', true)) {
            $errors[] = 'Product not complete: ' . $p['slug']; continue;
        }
        // Later owner edits and unpublishing are preserved. Never rewrite a completed page.
        $images = array_merge([$product->get_image_id()], $product->get_gallery_image_ids());
        if (count($images) !== 6 || count(array_unique($images)) !== 6) { $errors[] = 'Review changed gallery: ' . $p['slug']; continue; }
        foreach ($images as $index => $aid) {
            $file = get_attached_file($aid);
            if (!$file || !is_file($file) || !hash_equals($p['images'][$index]['sha256'], hash_file('sha256', $file)) || empty(wp_get_attachment_metadata($aid)['sizes'])) {
                $errors[] = 'Missing or changed media: ' . $p['images'][$index]['file'];
            }
        }
    }
    return $errors;
}
function vpn_four_20261019_assert(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
function vpn_four_20261019_meta(array $p, int $category): array {
    return ['rank_math_title' => $p['seo_title'], 'rank_math_description' => $p['seo_description'],
        'rank_math_focus_keyword' => implode(',', array_merge([$p['keyword']], $p['secondary'])),
        'rank_math_primary_product_cat' => $category,
        'rank_math_facebook_title' => $p['seo_title'], 'rank_math_facebook_description' => $p['seo_description'],
        'rank_math_twitter_title' => $p['seo_title'], 'rank_math_twitter_description' => $p['seo_description']];
}
function vpn_four_20261019_verify(array $rows, string $marker, bool $published): array {
    $results = [];
    foreach ($rows as $p) {
        $post = get_page_by_path($p['slug'], OBJECT, 'product');
        $wc = $post ? wc_get_product($post->ID) : false;
        vpn_four_20261019_assert($wc && get_post_meta($post->ID, '_vpn_sample_import', true) === $marker, 'Missing owned product: ' . $p['slug']);
        $id = (int) $post->ID;
        vpn_four_20261019_assert($wc->get_status() === ($published ? 'publish' : 'draft') && $post->post_password === '', 'Unexpected product visibility');
        vpn_four_20261019_assert($wc->get_name() === $p['title'] && $wc->get_catalog_visibility() === 'visible', 'Title/catalog mismatch');
        $body = $wc->get_description();
        $words = str_word_count(wp_strip_all_tags($body));
        vpn_four_20261019_assert($words >= 1500 && $words <= 2000 && !preg_match('/<h1\b/i', $body), 'Content length/headings mismatch');
        vpn_four_20261019_assert(!preg_match('/\{\{[A-Z0-9_]+\}\}/', $body) && substr_count($body, 'product-inline-figure-small') === 3, 'Unresolved content or missing figures');
        vpn_four_20261019_assert(substr_count($body, '<h3>') === 6, 'Expected six visible FAQs');
        $specs = get_post_meta($id, '_custom_box_product_specs', true);
        vpn_four_20261019_assert(is_array($specs) && count($specs) === 21, 'Missing specifications');
        $categories = wp_get_object_terms($id, 'product_cat', ['fields' => 'slugs']);
        $a = $categories; $b = $p['categories']; sort($a); sort($b);
        vpn_four_20261019_assert($a === $b, 'Category mismatch');
        $tags = wp_get_object_terms($id, 'product_tag', ['fields' => 'slugs']);
        $a = $tags; $b = $p['tags']; sort($a); sort($b);
        vpn_four_20261019_assert($a === $b, 'Tag mismatch');
        $primary = get_term_by('slug', $p['categories'][0], 'product_cat');
        foreach (vpn_four_20261019_meta($p, (int) $primary->term_id) as $key => $value) {
            vpn_four_20261019_assert((string) get_post_meta($id, $key, true) === (string) $value, 'SEO readback mismatch: ' . $key);
        }
        $images = array_merge([$wc->get_image_id()], $wc->get_gallery_image_ids());
        vpn_four_20261019_assert(count($images) === 6 && count(array_unique($images)) === 6, 'Featured/gallery image count mismatch');
        foreach ($images as $index => $aid) {
            $file = get_attached_file($aid);
            vpn_four_20261019_assert(is_file($file) && hash_equals($p['images'][$index]['sha256'], hash_file('sha256', $file)), 'Saved image checksum mismatch');
            vpn_four_20261019_assert(get_post_meta($aid, '_wp_attachment_image_alt', true) === $p['images'][$index]['alt'], 'Image alt mismatch');
            vpn_four_20261019_assert((int) get_post($aid)->post_parent === $id && !empty(wp_get_attachment_metadata($aid)['sizes']), 'Attachment parent or thumbnails missing');
        }
        vpn_four_20261019_assert($wc->get_price() === '' && !$wc->get_review_count(), 'Unexpected price or review claim');
        $results[] = ['id' => $id, 'title' => $p['title'], 'status' => $wc->get_status(), 'url' => get_permalink($id),
            'keyword' => $p['keyword'], 'seo_title' => $p['seo_title'], 'meta_description' => $p['seo_description'],
            'wordcount' => $words, 'categories' => $categories, 'image_ids' => $images];
    }
    return $results;
}


function vpn_four_20261019_run(): array {
    vpn_four_20261019_assert(current_user_can('manage_options'), 'Administrator permission required.');
    $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    vpn_four_20261019_assert(in_array($host, ['hopgiayvpn.com', 'www.hopgiayvpn.com', 'localhost', '127.0.0.1'], true), 'Unapproved target host.');
    vpn_four_20261019_assert(class_exists('WC_Product_Simple') && function_exists('rest_get_server'), 'WooCommerce and Rank Math required.');
    $dir = __DIR__ . '/product-content/four-products-20261019';
    $assets = __DIR__ . '/product-sample-deploy-assets/uploads/2026/10/vpn-four-products-20261019';
    $manifest = vpn_four_20261019_source(); $marker = VPN_FOUR_20261019_VERSION; $rows = $manifest['products'];
    vpn_four_20261019_assert(isset(rest_get_server()->get_routes()['/rankmath/v1/updateMeta']), 'Rank Math own metadata route required.');
    vpn_four_20261019_assert(wp_image_editor_supports(['mime_type' => 'image/webp']), 'A WebP-capable image editor is required.');
$references = ['BAG_GUIDE_URL' => ['how-to-make-paper-bags-stronger', 'post'],
    'MAILER_GUIDE_URL' => ['e-flute-corrugated-cardboard-thickness-mm', 'post'],
    'RELATED_BIRTHDAY_URL' => ['custom-birthday-paper-gift-bag-with-candle-print', 'product'],
    'RELATED_BAKERY_URL' => ['custom-printed-flat-bread-paper-bags', 'product'],
    'RELATED_WHITE_URL' => ['custom-white-kraft-paper-bags-twisted-handles', 'product'],
    'RELATED_MAILER_URL' => ['custom-white-mailer-box-with-logo', 'product'], 'CONTACT_URL' => ['contact', 'page']];
$tokens = ['{{BAGS_MONEY_URL}}' => home_url('/custom-paper-bags-manufacturer/'), '{{BOXES_MONEY_URL}}' => home_url('/custom-packaging-boxes-manufacturer/')];
foreach ($references as $token => [$slug, $type]) {
    $post = get_page_by_path($slug, OBJECT, $type);
    vpn_four_20261019_assert($post && $post->post_status === 'publish', 'Missing internal link: ' . $slug);
    $tokens['{{' . $token . '}}'] = get_permalink($post);
}
$preflight = []; $complete = 0;
foreach ($rows as $p) {
    $body = vpn_four_20261019_body($p);
    vpn_four_20261019_assert(hash_equals($p['content_sha256'], hash('sha256', $body)) && $p['wordcount'] >= 1500 && $p['wordcount'] <= 2000, 'Content preflight failed');
    vpn_four_20261019_assert(stripos($p['seo_title'], $p['keyword']) !== false && strlen($p['seo_title']) <= 60 && stripos($p['seo_description'], $p['keyword']) !== false && strlen($p['seo_description']) <= 155, 'SEO preflight failed');
    foreach ($p['categories'] as $slug) { vpn_four_20261019_assert((bool) get_term_by('slug', $slug, 'product_cat'), 'Missing category: ' . $slug); }
    $post = get_page_by_path($p['slug'], OBJECT, 'product');
    if ($post) {
        vpn_four_20261019_assert(get_post_meta($post->ID, '_vpn_sample_import', true) === $marker, 'Unrelated slug collision: ' . $p['slug']);
        if (get_post_meta($post->ID, '_vpn_four_20261019_complete', true)) { $complete++; }
        else {
            $previous = get_post_meta($post->ID, '_vpn_four_20261019_body_hash', true);
            vpn_four_20261019_assert($post->post_status === 'draft' && $post->post_title === $p['title'] && ($post->post_content === '' || ($previous && hash_equals($previous, hash('sha256', $post->post_content)))), 'Edited partial product preserved');
        }
    }
    $sku = 'VPN-20261019-' . $p['code']; $sku_id = wc_get_product_id_by_sku($sku);
    vpn_four_20261019_assert(!$sku_id || ($post && (int) $post->ID === $sku_id), 'Unrelated SKU collision');
    vpn_four_20261019_assert(count($p['images']) === 6, 'Expected six source images');
    foreach ($p['images'] as $image) {
        vpn_four_20261019_assert(basename($image['file']) === $image['file'], 'Invalid image filename');
        $file = $assets . '/' . basename($image['file']); $size = getimagesize($file);
        vpn_four_20261019_assert($size && $size[0] === 450 && $size[1] === 570 && $size['mime'] === 'image/webp' && filesize($file) < 100000 && hash_equals($image['sha256'], hash_file('sha256', $file)), 'Source image preflight failed');
    }
    $preflight[] = ['slug' => $p['slug'], 'existing_id' => $post ? (int) $post->ID : null, 'sku' => $sku];
}
$pending = array_values(array_filter($rows, static function ($p) {
    $post = get_page_by_path($p['slug'], OBJECT, 'product');
    return !$post || !get_post_meta($post->ID, '_vpn_four_20261019_complete', true);
}));
if (!$pending) {
    $errors = vpn_four_20261019_completion_errors();
    vpn_four_20261019_assert(!$errors, implode('; ', $errors));
    return array_map(static fn($p) => (int) get_page_by_path($p['slug'], OBJECT, 'product')->ID, $rows);
}
$original_user = get_current_user_id();
$lock = 'vpn_four_20261019_deploy_lock';
$started = (int) get_option($lock, 0);
if ($started && $started < time() - 900) { delete_option($lock); }
vpn_four_20261019_assert(add_option($lock, time(), '', false), 'Another import is running');
try {
    vpn_four_20261019_assert(isset(rest_get_server()->get_routes()['/rankmath/v1/updateMeta']), 'Rank Math own metadata route required');
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $uploads = wp_upload_dir(); vpn_four_20261019_assert(!$uploads['error'], 'Uploads unavailable');
    $target = trailingslashit($uploads['basedir']) . '2026/10/vpn-four-products-20261019/';
    vpn_four_20261019_assert(wp_mkdir_p($target), 'Cannot create uploads destination');
    $ids = [];
    foreach ($pending as $p) {
        $old = get_page_by_path($p['slug'], OBJECT, 'product');
        $wc = $old ? new WC_Product_Simple($old->ID) : new WC_Product_Simple();
        $wc->set_name($p['title']); $wc->set_slug($p['slug']); $wc->set_status('draft');
        $wc->set_sku('VPN-20261019-' . $p['code']); $wc->set_catalog_visibility('visible');
        $wc->set_regular_price(''); $wc->set_sale_price(''); $wc->set_price('');
        $wc->set_manage_stock(false); $wc->set_stock_status('onbackorder'); $wc->set_reviews_allowed(false);
        $category_ids = array_map(static fn($slug) => (int) get_term_by('slug', $slug, 'product_cat')->term_id, $p['categories']);
        $wc->set_category_ids($category_ids); $wc->update_meta_data('_vpn_sample_import', $marker);
        $id = $wc->save(); vpn_four_20261019_assert((bool) $id, 'Draft save failed'); $ids[] = $id;
        wp_update_post(['ID' => $id, 'post_author' => get_current_user_id(), 'post_password' => '']);
        $tag_ids = wp_set_object_terms($id, $p['tags'], 'product_tag');
        vpn_four_20261019_assert(!is_wp_error($tag_ids), 'Cannot save tags');
        $images = [];
        foreach ($p['images'] as $image) {
            $destination = $target . $image['file'];
            if (is_file($destination)) { vpn_four_20261019_assert(hash_equals($image['sha256'], hash_file('sha256', $destination)), 'Different existing image preserved'); }
            else { vpn_four_20261019_assert(copy($assets . '/' . basename($image['file']), $destination), 'Image copy failed'); }
            $asset = $marker . '/' . $image['file'];
            $found = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'meta_key' => '_vpn_four_20261019_asset', 'meta_value' => $asset, 'fields' => 'ids']);
            if ($found) { $aid = (int) $found[0]; }
            else {
                $aid = wp_insert_attachment(['post_mime_type' => 'image/webp', 'post_title' => $image['title'], 'post_excerpt' => $image['caption'], 'post_parent' => $id, 'post_status' => 'inherit'], $destination, $id, true);
                vpn_four_20261019_assert(!is_wp_error($aid), 'Attachment insert failed');
                update_post_meta($aid, '_vpn_four_20261019_asset', $asset);
            }
            if (empty(wp_get_attachment_metadata($aid)['sizes'])) { wp_update_attachment_metadata($aid, wp_generate_attachment_metadata($aid, $destination)); }
            update_post_meta($aid, '_wp_attachment_image_alt', $image['alt']); $images[] = $aid;
        }
        $local_tokens = $tokens;
        $local_tokens['{{CATEGORY_URL}}'] = get_term_link($category_ids[0], 'product_cat');
        foreach ([2, 4, 5] as $slot) {
            $local_tokens['{{FIGURE_' . $slot . '}}'] = '<figure class="product-inline-figure product-inline-figure-small">' . wp_get_attachment_image($images[$slot - 1], 'full', false, ['loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) calc(100vw - 36px), 450px']) . '<figcaption>' . esc_html($p['images'][$slot - 1]['caption']) . '</figcaption></figure>';
        }
        $body = wp_kses_post(strtr(vpn_four_20261019_body($p), $local_tokens));
        $wc = new WC_Product_Simple($id); $wc->set_description($body); $wc->set_short_description($p['short_description']);
        $wc->set_image_id($images[0]); $wc->set_gallery_image_ids(array_slice($images, 1));
        $specs = ['Feature' => $p['feature'], 'Industrial Use' => $p['use'], 'Paper Type' => $p['paper'], 'Box Type' => $p['type'], 'Shape' => $p['shape'],
            'Place of Origin' => 'Vietnam', 'Model Number' => $wc->get_sku(), 'Brand Name' => 'VPN', 'Province' => 'Ho Chi Minh City',
            'Accessories' => $p['accessories'], 'Custom Order' => 'Accept', 'Liner Type' => $p['liner'], 'Logo Printing' => 'Custom logo',
            'Printing Handling' => 'Print process and colors agreed with selected paper, artwork and sample', 'Color' => $p['color'],
            'Size' => 'Customized size; usable dimensions agreed before sampling', 'Thickness' => 'Customized thickness; grade confirmed in the specification',
            'Single Piece Price' => 'Written quotation based on specification and quantity',
            'Minimum Order Quantity (MOQ)' => '1,000 units general planning minimum; confirm by size and artwork in the quotation',
            'Product Name' => $p['title'], 'Design' => "Customer's Specific Requirement"];
        $formatted = []; foreach ($specs as $label => $value) { $formatted[] = ['label' => $label, 'value' => $value]; }
        $wc->update_meta_data('_custom_box_product_specs', $formatted);
        $wc->update_meta_data('_custom_box_product_faq_html', '');
        $wc->update_meta_data('_vpn_four_20261019_body_hash', hash('sha256', $body)); $wc->save();
        $request = new WP_REST_Request('POST', '/rankmath/v1/updateMeta');
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(wp_json_encode(['objectType' => 'post', 'objectID' => $id, 'meta' => vpn_four_20261019_meta($p, $category_ids[0])]));
        $response = rest_do_request($request);
        vpn_four_20261019_assert(!$response->is_error(), 'Rank Math metadata write failed');
    }
    vpn_four_20261019_verify($pending, $marker, false);
    foreach ($ids as $id) {
        $wc = new WC_Product_Simple($id); $wc->set_status('publish');
        $wc->update_meta_data('_vpn_four_20261019_complete', true); $wc->save();
        wc_delete_product_transients($id); clean_post_cache($id);
    }
    $errors = vpn_four_20261019_completion_errors();
    vpn_four_20261019_assert(!$errors, implode('; ', $errors));
    return array_map(static fn($p) => (int) get_page_by_path($p['slug'], OBJECT, 'product')->ID, $rows);
} finally { wp_set_current_user($original_user); delete_option($lock); }

}
