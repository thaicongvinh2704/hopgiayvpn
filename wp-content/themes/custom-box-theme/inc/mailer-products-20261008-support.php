<?php
/** Shared manifest, attachment and content helpers for the five mailer design concepts. */
defined('ABSPATH') || exit;
const VPN_MAILER_20261008_MARKER = 'mailer-products-20261008';
function vpn_mailer_20261008_products(): array {
    return json_decode((string) file_get_contents(__DIR__ . '/product-content/mailer-products-20261008/products.json'), true, 512, JSON_THROW_ON_ERROR);
}
function vpn_mailer_20261008_files(string $prefix): array {
    $dir = __DIR__ . '/product-sample-deploy-assets/uploads/2026/10/';
    $roles = array('01-hero', '02-alternate-view', '03-detail', '04-side-angle', '05-overhead', '06-callouts');
    return array_map(static function ($role) use ($dir, $prefix) { return $dir . $prefix . '-' . $role . '.webp'; }, $roles);
}
function vpn_mailer_20261008_source_hash(array $p): string {
    return hash('sha256', wp_json_encode($p) . file_get_contents(__DIR__ . '/product-content/mailer-products-20261008/' . $p['content_file']));
}
function vpn_mailer_20261008_link(string $slug, string $text, array $types = array('post', 'page', 'product')): string {
    foreach ($types as $type) {
        $post = get_page_by_path($slug, OBJECT, $type);
        if ($post && ('publish' === $post->post_status || ('product' === $type && 'draft' === $post->post_status && VPN_MAILER_20261008_MARKER === get_post_meta($post->ID, '_vpn_sample_import', true)))) {
            // Drafts exist during the first import. Resolve their eventual public
            // permalink without publishing an incomplete record or retaining ?p= links.
            if ('draft' === $post->post_status) {
                $post = clone $post;
                $post->post_status = 'publish';
            }
            return '<a href="' . esc_url(get_permalink($post)) . '">' . esc_html($text) . '</a>';
        }
    }
    throw new RuntimeException('Missing published internal link: ' . $slug);
}
function vpn_mailer_20261008_attachment(string $file, int $parent, array $p, int $index): int {
    global $wpdb;
    $relative = '2026/10/' . wp_basename($file);
    $ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT pm.post_id FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE pm.meta_key='_wp_attached_file' AND pm.meta_value=%s AND p.post_type='attachment'", $relative));
    if (count($ids) > 1) { throw new RuntimeException('Duplicate attachment path: ' . $relative); }
    $roles = array('closed hero view', 'alternate closed view', 'corner and surface detail', 'closed side angle', 'closed overhead view', 'annotated exterior details');
    $role = $roles[$index];
    $data = array('post_mime_type' => 'image/webp', 'post_status' => 'inherit', 'post_parent' => $parent,
        'post_title' => $p['title'] . ' - ' . $role,
        'post_excerpt' => 'AI-assisted design visualization, ' . $role . '; not a manufactured-sample photograph or verified production specification.');
    if ($ids) { $data['ID'] = (int) $ids[0]; $result = wp_update_post($data, true); }
    else { $result = wp_insert_attachment($data, $file, $parent, true); }
    if (is_wp_error($result) || !$result) { throw new RuntimeException('Attachment write failed: ' . $relative); }
    $id = (int) $result;
    update_post_meta($id, '_wp_attachment_image_alt', $p['title'] . ', ' . $role . '; AI-assisted packaging design visualization');
    update_post_meta($id, '_vpn_mailer_20261008_file', wp_basename($file));
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $meta = wp_get_attachment_metadata($id);
    if (!$meta || empty($meta['width']) || empty($meta['height'])) {
        $meta = wp_generate_attachment_metadata($id, $file);
        if (!is_array($meta) || empty($meta['width']) || empty($meta['height'])) { throw new RuntimeException('Image metadata failed: ' . $relative); }
        wp_update_attachment_metadata($id, $meta);
    }
    return $id;
}
function vpn_mailer_20261008_content(array $p, array $images): string {
    $term = get_term_by('slug', 'corrugated-mailer-boxes', 'product_cat');
    $category_url = get_term_link($term);
    if (is_wp_error($category_url)) { throw new RuntimeException($category_url->get_error_message()); }
    $contact = vpn_mailer_20261008_link('contact', 'request a custom mailer quotation', array('page'));
    $tokens = array(
        '{{CATEGORY_LINK}}' => '<a href="' . esc_url($category_url) . '">corrugated mailer box collection</a>',
        '{{RELATED_ONE_URL}}' => vpn_mailer_20261008_link($p['related_one'], $p['related_one_text'], array('product')),
        '{{RELATED_TWO_URL}}' => vpn_mailer_20261008_link($p['related_two'], $p['related_two_text'], array('product')),
        '{{GUIDE_URL}}' => vpn_mailer_20261008_link($p['guide_slug'], $p['guide_text'], array('post')),
        '{{ABOUT_URL}}' => vpn_mailer_20261008_link('about', 'VPN Packaging company background', array('page')),
        '{{CONTACT_URL}}' => $contact,
    );
    foreach (array('ALTERNATE' => 1, 'DETAIL' => 2, 'TOP' => 4) as $slot => $index) {
        $caption = 'DETAIL' === $slot ? $p['caption_detail'] : ('ALTERNATE' === $slot ? 'Alternate closed view of ' : 'Overhead closed view of ') . strtolower($p['title']) . '; AI-assisted design visualization.';
        $tokens['{{FIGURE_' . $slot . '}}'] = '<figure class="product-inline-figure product-inline-figure-small">' . wp_get_attachment_image($images[$index], 'full', false, array('loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) calc(100vw - 36px), 450px')) . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
    }
    $content = strtr((string) file_get_contents(__DIR__ . '/product-content/mailer-products-20261008/' . $p['content_file']), $tokens);
    if (preg_match('/\{\{[A-Z0-9_]+\}\}/', $content)) { throw new RuntimeException('Unresolved content token: ' . $p['slug']); }
    return wp_kses_post($content);
}
// These products require project quotations. Do not manufacture zero-price offers or ratings.
function vpn_mailer_20261008_schema(array $data): array {
    if (!function_exists('is_product') || !is_product() || VPN_MAILER_20261008_MARKER !== get_post_meta(get_queried_object_id(), '_vpn_sample_import', true)) { return $data; }
    $product = wc_get_product(get_queried_object_id());
    foreach ($data as &$entity) {
        if (!is_array($entity) || !in_array('Product', (array) ($entity['@type'] ?? array()), true)) { continue; }
        unset($entity['offers'], $entity['aggregateRating'], $entity['review']);
        $entity['name'] = $product->get_name();
        $entity['description'] = wp_strip_all_tags($product->get_short_description());
        $entity['sku'] = $product->get_sku();
        $entity['category'] = 'Custom Corrugated Mailer Packaging';
        $entity['url'] = get_permalink($product->get_id());
        $entity['image'] = array_values(array_filter(array_map('wp_get_attachment_url', array_merge(array($product->get_image_id()), $product->get_gallery_image_ids()))));
        $entity['additionalProperty'] = array(array('@type' => 'PropertyValue', 'name' => 'Design status', 'value' => 'AI-assisted design visualization; production specification confirmed by approved sample'));
    }
    unset($entity);
    return $data;
}
add_filter('rank_math/json_ld', 'vpn_mailer_20261008_schema', 100);

function vpn_mailer_20261008_quote_meta($value) {
    return function_exists('is_product') && is_product() && VPN_MAILER_20261008_MARKER === get_post_meta(get_queried_object_id(), '_vpn_sample_import', true) ? '' : $value;
}
add_filter('rank_math/opengraph/facebook/product_price_amount', 'vpn_mailer_20261008_quote_meta', 100);
add_filter('rank_math/opengraph/facebook/product_price_currency', 'vpn_mailer_20261008_quote_meta', 100);
add_filter('rank_math/opengraph/facebook/product_availability', 'vpn_mailer_20261008_quote_meta', 100);
function vpn_mailer_20261008_slack_meta($data) {
    return function_exists('is_product') && is_product() && VPN_MAILER_20261008_MARKER === get_post_meta(get_queried_object_id(), '_vpn_sample_import', true) ? array() : $data;
}
add_filter('rank_math/opengraph/slack_enhanced_data', 'vpn_mailer_20261008_slack_meta', 100);
