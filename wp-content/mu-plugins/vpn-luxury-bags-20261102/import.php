<?php
/** Fixed owner-approved batch. No request input, uploaded executable, or public import endpoint. */
defined('ABSPATH') || exit;
const VPN_LB02_MARKER = 'luxury-paper-bags-20261102-v1';
function vpn_lb02_check($ok, string $message): void {
    if (!$ok) { throw new RuntimeException($message); }
}
require_once __DIR__ . '/helpers.php';

function vpn_lb02_package(): array {
    $data = json_decode(file_get_contents(__DIR__ . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $expected = ['custom-black-boutique-paper-bags', 'custom-ivory-jewelry-paper-bags',
        'custom-blush-pink-cosmetic-paper-bags', 'custom-navy-wine-paper-gift-bags', 'custom-olive-green-paper-gift-bags'];
    vpn_lb02_check($data['marker'] === VPN_LB02_MARKER && $data['status'] === 'publish'
        && array_column($data['products'], 'slug') === $expected, 'Unexpected luxury bag release.');
    foreach ($data['products'] as $p) {
        vpn_lb02_check(basename($p['content_file']) === $p['content_file'], 'Unsafe content path.');
        $body = file_get_contents(__DIR__ . '/' . $p['content_file']);
        vpn_lb02_check(hash_equals($p['content_sha256'], hash('sha256', $body)) && count($p['images']) === 6
            && !preg_match('~<h1\b|localhost|file://~i', $body), 'Invalid content: ' . $p['slug']);
        vpn_lb02_check(stripos($p['seo_title'], $p['keyword']) !== false && strlen($p['seo_title']) <= 60
            && stripos($p['seo_description'], $p['keyword']) !== false && strlen($p['seo_description']) <= 160, 'Invalid SEO fields.');
        foreach ($p['images'] as $img) {
            $path = __DIR__ . '/' . $img['file'];
            vpn_lb02_check(!str_contains($img['file'], '..') && str_starts_with($img['file'], 'source/'), 'Unsafe asset path.');
            $size = is_file($path) ? wp_getimagesize($path) : false;
            vpn_lb02_check($size && $size[0] === 450 && $size[1] === 570 && $size['mime'] === 'image/webp'
                && filesize($path) < 100000 && hash_equals($img['sha256'], hash_file('sha256', $path)), 'Invalid asset: ' . $img['file']);
        }
    }
    return $data;
}

function vpn_lb02_context(): array {
    vpn_lb02_check(current_user_can('manage_options') && current_user_can('manage_woocommerce'), 'Administrator required.');
    vpn_lb02_check(class_exists('WC_Product_Simple') && taxonomy_exists('product_cat') && defined('RANK_MATH_VERSION'), 'WooCommerce and Rank Math required.');
    vpn_lb02_check(wp_image_editor_supports(['mime_type' => 'image/webp']), 'A WebP-capable WordPress image editor is required.');
    $term = get_term_by('slug', 'paper-bags-with-logo', 'product_cat');
    $contact = get_page_by_path('contact', OBJECT, 'page');
    $moneyUrl = function_exists('custom_box_custom_paper_bags_url') ? custom_box_custom_paper_bags_url() : '';
    vpn_lb02_check($term && !is_wp_error($term) && $contact && $contact->post_status === 'publish'
        && $moneyUrl && is_file(get_template_directory() . '/page-custom-paper-bags-manufacturer.php'), 'Existing category or contact/manufacturer route missing.');
    $uploads = wp_upload_dir();
    vpn_lb02_check(!$uploads['error'], 'Uploads unavailable.');
    return ['term' => $term, 'contact' => $contact, 'money_url' => $moneyUrl, 'source' => __DIR__,
        'target' => trailingslashit($uploads['basedir']) . '2026/11/vpn-luxury-bags-20261102/',
        'asset_marker' => VPN_LB02_MARKER, 'status' => 'publish'];
}

/** Find across all statuses (including trash); never reuse an unrelated slug or SKU. */
function vpn_lb02_find(array $p): ?WP_Post {
    global $wpdb;
    $ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type NOT IN ('attachment','revision')", $p['slug']));
    vpn_lb02_check(count($ids) <= 1, 'Duplicate slug needs review: ' . $p['slug']);
    $post = $ids ? get_post((int) $ids[0]) : null;
    vpn_lb02_check(!$post || ($post->post_type === 'product' && get_post_meta($post->ID, '_vpn_luxury_import', true) === VPN_LB02_MARKER),
        'Unrelated existing slug preserved: ' . $p['slug']);
    $skuId = wc_get_product_id_by_sku($p['sku']);
    vpn_lb02_check(!$skuId || ($post && (int) $skuId === (int) $post->ID), 'Existing SKU preserved: ' . $p['sku']);
    return $post;
}

function vpn_lb02_import_one(array $p, array $context): array {
    $existing = vpn_lb02_find($p);
    // Never reset subsequent owner edits, changed gallery, or unpublishing of a completed product.
    if ($existing && get_post_meta($existing->ID, '_vpn_luxury_complete', true)) {
        return ['id' => (int) $existing->ID, 'status' => $existing->post_status, 'result' => 'preserved completed product'];
    }
    if ($existing) {
        $savedHash = get_post_meta($existing->ID, '_vpn_luxury_body_hash', true);
        // Recover a timeout immediately after publication without rewriting the saved page.
        if ($existing->post_status === 'publish' && $savedHash && hash_equals($savedHash, hash('sha256', $existing->post_content))) {
            $result = vpn_lb02_verify($p, VPN_LB02_MARKER, (int) $context['term']->term_id);
            update_post_meta($existing->ID, '_vpn_luxury_complete', true);
            $result['result'] = 'verified interrupted publication';
            return $result;
        }
        vpn_lb02_check($existing->post_status === 'draft' && $existing->post_title === $p['title']
            && (!trim($existing->post_content) || ($savedHash && hash_equals($savedHash, hash('sha256', $existing->post_content)))),
            'Edited or published partial product preserved: ' . $p['slug']);
    }
    $marker = VPN_LB02_MARKER; $source = $context['source']; $term = $context['term'];
    $contact = $context['contact']; $moneyUrl = $context['money_url']; $target = $context['target'];
    vpn_lb02_check(wp_mkdir_p($target), 'Cannot create upload folder.');
    $wc = $existing ? new WC_Product_Simple($existing->ID) : new WC_Product_Simple();
    $wc->set_name($p['title']); $wc->set_slug($p['slug']); $wc->set_status('draft'); $wc->set_sku($p['sku']);
    $wc->set_category_ids([(int) $term->term_id]); $wc->set_catalog_visibility('visible');
    $wc->set_regular_price(''); $wc->set_price(''); $wc->set_manage_stock(false); $wc->set_stock_status('onbackorder');
    $wc->update_meta_data('_vpn_luxury_import', $marker); $id = $wc->save();
    vpn_lb02_check($id, 'Draft save failed.');
    wp_update_post(['ID' => $id, 'post_author' => get_current_user_id()]);
    $imageIds = [];
    foreach ($p['images'] as $i => $img) {
        $asset = $context['asset_marker'] . '/' . $p['code'] . '/' . $i;
        $dest = $target . strtolower($p['code']) . '-' . basename($img['file']);
        vpn_lb02_check(!is_file($dest) || hash_equals($img['sha256'], hash_file('sha256', $dest)), 'Different upload file preserved.');
        if (!is_file($dest)) { vpn_lb02_check(copy($source . '/' . $img['file'], $dest), 'Image copy failed.'); }
        $found = get_posts(['post_type' => 'attachment', 'post_status' => 'any', 'numberposts' => 2,
            'meta_key' => '_vpn_luxury_asset', 'meta_value' => $asset, 'fields' => 'ids']);
        vpn_lb02_check(count($found) <= 1, 'Duplicate attachment marker.');
        if ($found) {
            $aid = (int) $found[0]; $file = get_attached_file($aid);
            vpn_lb02_check($file && is_file($file) && hash_equals($img['sha256'], hash_file('sha256', $file))
                && (int) get_post_field('post_parent', $aid) === (int) $id
                && get_post_meta($aid, '_wp_attachment_image_alt', true) === $img['alt']
                && get_post_field('post_title', $aid) === $img['title'] && wp_get_attachment_caption($aid) === $img['caption'], 'Edited partial attachment preserved.');
        } else {
            $aid = wp_insert_attachment(['post_mime_type' => 'image/webp', 'post_title' => $img['title'],
                'post_excerpt' => $img['caption'], 'post_parent' => $id, 'post_status' => 'inherit'], $dest, $id, true);
            vpn_lb02_check(!is_wp_error($aid), 'Attachment creation failed.');
            update_post_meta($aid, '_vpn_luxury_asset', $asset);
            update_post_meta($aid, '_wp_attachment_image_alt', $img['alt']);
        }
        if (empty(wp_get_attachment_metadata($aid)['sizes'])) { wp_update_attachment_metadata($aid, wp_generate_attachment_metadata($aid, $dest)); }
        $imageIds[] = (int) $aid;
        update_post_meta($id, '_vpn_luxury_media_progress', $imageIds);
    }
    $body = vpn_lb02_body($p, $imageIds, $source, $moneyUrl, $term, $contact);
    $wc = new WC_Product_Simple($id); $wc->set_description($body); $wc->set_short_description($p['short_description']);
    $wc->set_image_id($imageIds[0]); $wc->set_gallery_image_ids(array_slice($imageIds, 1));
    require __DIR__ . '/save-copy.php';
    // Keys verified against the installed Rank Math plugin and approved local readback.
    foreach (vpn_lb02_meta($p, (int) $term->term_id) as $key => $value) { update_post_meta($id, $key, wp_slash($value)); }
    clean_post_cache($id);
    // Verify all content/media/SEO while still draft; no incomplete page becomes public.
    vpn_lb02_verify($p, $marker, (int) $term->term_id, 'draft');
    $wc = new WC_Product_Simple($id); $wc->set_status($context['status']); $wc->save();
    wc_delete_product_transients($id); clean_post_cache($id);
    $result = vpn_lb02_verify($p, $marker, (int) $term->term_id, $context['status']);
    update_post_meta($id, '_vpn_luxury_complete', true);
    $result['result'] = 'imported and verified';
    return $result;
}

function vpn_lb02_run(): array {
    $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    vpn_lb02_check(in_array($host, ['hopgiayvpn.com', 'www.hopgiayvpn.com', 'localhost', '127.0.0.1'], true), 'Unapproved target host.');
    $data = vpn_lb02_package(); $context = vpn_lb02_context();
    require_once ABSPATH . 'wp-admin/includes/image.php';
    // Check every slug/SKU before creating anything; one article's runtime error does not block the rest.
    foreach ($data['products'] as $p) { vpn_lb02_find($p); }
    $results = [];
    foreach ($data['products'] as $p) {
        try { $results[$p['code']] = vpn_lb02_import_one($p, $context); }
        catch (Throwable $error) { $results[$p['code']] = ['slug' => $p['slug'], 'error' => $error->getMessage()]; }
        update_option('vpn_lb02_results', $results, false);
    }
    if (class_exists('RankMath\Sitemap\Cache')) { \RankMath\Sitemap\Cache::invalidate_storage(); }
    return $results;
}
