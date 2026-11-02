<?php
/** Reviewed product rendering, SEO fields and readback checks. */
defined('ABSPATH') || exit;
function vpn_lb02_meta(array $p, int $category): array {
    return ['rank_math_title' => $p['seo_title'], 'rank_math_description' => $p['seo_description'],
        'rank_math_focus_keyword' => implode(',', array_merge([$p['keyword']], $p['secondary'])),
        'rank_math_primary_product_cat' => $category, 'rank_math_facebook_title' => $p['seo_title'],
        'rank_math_facebook_description' => $p['seo_description'], 'rank_math_twitter_title' => $p['seo_title'],
        'rank_math_twitter_description' => $p['seo_description'], 'rank_math_robots' => ['index', 'follow']];
}
function vpn_lb02_body(array $p, array $imageIds, string $source, string $moneyUrl, $term, $contact): string {
    $tokens = ['{{MONEY_URL}}' => $moneyUrl, '{{CATEGORY_URL}}' => get_term_link($term), '{{QUOTE_URL}}' => get_permalink($contact) . '#quote'];
    foreach ($p['inline_order'] as $n) {
        $tokens['{{FIGURE_' . $n . '}}'] = '<figure class="product-inline-figure product-inline-figure-small">'
            . wp_get_attachment_image($imageIds[$n - 1], 'full', false, ['loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) calc(100vw - 36px), 450px'])
            . '<figcaption>' . esc_html($p['images'][$n - 1]['caption']) . '</figcaption></figure>';
    }
    $body = wp_kses_post(strtr(file_get_contents($source . '/' . $p['content_file']), $tokens));
    vpn_lb02_check(!str_contains($body, '{{'), 'Unresolved content token.');
    return $body;
}
function vpn_lb02_verify(array $p, string $marker, int $termId, string $status = 'publish'): array {
    $post = get_page_by_path($p['slug'], OBJECT, 'product');
    vpn_lb02_check($post && get_post_meta($post->ID, '_vpn_luxury_import', true) === $marker, 'Missing owned product.');
    $id = (int) $post->ID; $wc = wc_get_product($id);
    vpn_lb02_check($wc && $wc->get_status() === $status && $wc->get_catalog_visibility() === 'visible', 'Unexpected product status.');
    vpn_lb02_check($wc->get_name() === $p['title'] && $wc->get_sku() === $p['sku'], 'Title or SKU mismatch.');
    $body = $wc->get_description();
    vpn_lb02_check(hash_equals(get_post_meta($id, '_vpn_luxury_body_hash', true), hash('sha256', $body)), 'Saved content changed.');
    vpn_lb02_check(!preg_match('/\{\{|IMAGE_SLOT|<h1\b|file:\/\/|C:\\\\/i', $body), 'Unresolved content or local file path.');
    vpn_lb02_check(substr_count($body, 'product-inline-figure-small') === 3 && substr_count($body, '<h3>') === 4 && str_contains($body, '<table>'), 'Body sections missing.');
    vpn_lb02_check(wp_get_object_terms($id, 'product_cat', ['fields' => 'ids']) === [$termId], 'Incorrect category.');
    foreach (vpn_lb02_meta($p, $termId) as $key => $value) {
        vpn_lb02_check(get_post_meta($id, $key, true) == $value, 'SEO readback mismatch: ' . $key);
    }
    $images = array_merge([$wc->get_image_id()], $wc->get_gallery_image_ids());
    vpn_lb02_check(count($images) === 6 && count(array_unique($images)) === 6, 'Featured and gallery images incomplete.');
    foreach ($images as $i => $aid) {
        $file = get_attached_file($aid); $spec = $p['images'][$i];
        vpn_lb02_check($file && is_file($file) && hash_equals($spec['sha256'], hash_file('sha256', $file)), 'Wrong image: ' . $spec['file']);
        vpn_lb02_check(get_post_meta($aid, '_wp_attachment_image_alt', true) === $spec['alt'], 'Alt mismatch.');
        vpn_lb02_check(get_post_field('post_title', $aid) === $spec['title'] && get_post_field('post_excerpt', $aid) === $spec['caption'], 'Media title/caption mismatch.');
        vpn_lb02_check(!empty(wp_get_attachment_metadata($aid)['sizes']), 'Thumbnails missing.');
    }
    return ['code' => $p['code'], 'title' => $wc->get_name(), 'id' => $id, 'slug' => $p['slug'], 'status' => $wc->get_status(),
        'url' => get_permalink($id), 'image_ids' => $images, 'images' => 6, 'wordcount_source' => $p['wordcount'],
        'seo_title' => get_post_meta($id, 'rank_math_title', true), 'meta_description' => get_post_meta($id, 'rank_math_description', true),
        'focus_keywords' => get_post_meta($id, 'rank_math_focus_keyword', true), 'verified' => true];
}
