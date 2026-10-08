<?php
/** Publish or repair the five supplied mailer concepts. Use --preflight for a read-only check. */
if (!defined('ABSPATH')) { require_once dirname(__DIR__) . '/wp-load.php'; }
if (PHP_SAPI !== 'cli' && (!is_admin() || !(current_user_can('manage_options') || current_user_can('manage_woocommerce')))) { wp_die('Authorized admin or CLI required.', '', array('response' => 403)); }
require_once get_template_directory() . '/inc/mailer-products-20261008-support.php';
if (!class_exists('WC_Product_Simple')) { throw new RuntimeException('WooCommerce must be active.'); }
$products = vpn_mailer_20261008_products();
$uploads = wp_upload_dir();
$target_dir = trailingslashit($uploads['basedir']) . '2026/10/';
$term = get_term_by('slug', 'corrugated-mailer-boxes', 'product_cat');
$failures = array();
if (!$term || is_wp_error($term)) { $failures[] = 'Missing Corrugated Mailer Boxes category.'; }
if (5 !== count($products)) { $failures[] = 'Manifest must contain five products.'; }
$known = array('{{FIGURE_ALTERNATE}}','{{FIGURE_DETAIL}}','{{FIGURE_TOP}}','{{CATEGORY_LINK}}','{{RELATED_ONE_URL}}','{{RELATED_TWO_URL}}','{{GUIDE_URL}}','{{ABOUT_URL}}','{{CONTACT_URL}}');
foreach ($products as $p) {
    $content_file = get_template_directory() . '/inc/product-content/mailer-products-20261008/' . $p['content_file'];
    $html = is_readable($content_file) ? (string) file_get_contents($content_file) : '';
    $words = str_word_count(wp_strip_all_tags($html));
    if ($words < 1500 || $words > 2000 || preg_match('/<h1\b/i', $html) || 3 !== substr_count($html, '{{FIGURE_') || preg_match('/\{\{[A-Z0-9_]+\}\}/', str_replace($known, '', $html))) { $failures[] = $p['slug'] . ': invalid source content (' . $words . ' words).'; }
    $short_words = str_word_count(wp_strip_all_tags($p['short']));
    if ($short_words < 120 || $short_words > 180 || strlen($p['seo_title']) > 60 || strlen($p['seo_description']) > 155) { $failures[] = $p['slug'] . ': invalid short description or SEO metadata.'; }
    $existing = get_page_by_path($p['slug'], OBJECT, 'product');
    if ($existing && VPN_MAILER_20261008_MARKER !== get_post_meta($existing->ID, '_vpn_sample_import', true)) { $failures[] = 'Refusing to overwrite unrelated product: ' . $p['slug']; }
    $sku_id = wc_get_product_id_by_sku($p['visual_reference']);
    if ($sku_id && (!$existing || $sku_id !== (int) $existing->ID)) { $failures[] = 'Conflicting SKU: ' . $p['visual_reference']; }
    try { vpn_mailer_20261008_link($p['guide_slug'], $p['guide_text'], array('post')); } catch (Throwable $e) { $failures[] = $e->getMessage(); }
    foreach (vpn_mailer_20261008_files($p['prefix']) as $file) {
        $info = is_file($file) ? @getimagesize($file) : false;
        if (!$info || $info[0] !== 450 || $info[1] !== 570 || $info['mime'] !== 'image/webp' || filesize($file) >= 100000) { $failures[] = 'Invalid 450x570 under-100k WebP: ' . wp_basename($file); continue; }
        $target = $target_dir . wp_basename($file);
        if (is_file($target) && hash_file('sha256', $target) !== hash_file('sha256', $file)) { $failures[] = 'Different existing upload: ' . wp_basename($file); }
    }
}
foreach (array('about','contact') as $slug) { try { vpn_mailer_20261008_link($slug, $slug, array('page')); } catch (Throwable $e) { $failures[] = $e->getMessage(); } }
if ($failures) { throw new RuntimeException(implode('; ', array_unique($failures))); }
if (in_array('--preflight', $argv ?? array(), true)) { echo wp_json_encode(array('preflight' => 'passed', 'products' => 5, 'images' => 30)) . PHP_EOL; return; }
if (!wp_mkdir_p($target_dir)) { throw new RuntimeException('Upload directory unavailable.'); }
foreach ($products as $p) { foreach (vpn_mailer_20261008_files($p['prefix']) as $file) { $target = $target_dir . wp_basename($file); if (!is_file($target) && !copy($file, $target)) { throw new RuntimeException('Asset copy failed: ' . $file); } } }
$ids = array();
// Create all drafts before resolving cross-product links. Existing published records keep their status.
foreach ($products as $p) {
    $post = get_page_by_path($p['slug'], OBJECT, 'product');
    $wc = $post ? new WC_Product_Simple((int) $post->ID) : new WC_Product_Simple();
    $wc->set_name($p['title']); $wc->set_slug($p['slug']);
    $wc->set_status($post && 'publish' === $post->post_status ? 'publish' : 'draft');
    $wc->set_short_description($p['short']); $wc->set_sku($p['visual_reference']);
    $wc->set_catalog_visibility('visible'); $wc->set_regular_price(''); $wc->set_sale_price(''); $wc->set_price('');
    $wc->set_manage_stock(false); $wc->set_stock_quantity(null); $wc->set_stock_status('onbackorder');
    $wc->set_category_ids(array((int) $term->term_id));
    $wc->update_meta_data('_vpn_sample_import', VPN_MAILER_20261008_MARKER);
    $wc->update_meta_data('_vpn_visual_status', 'AI-assisted design visualization; not a manufactured sample');
    $id = $wc->save(); if (!$id) { throw new RuntimeException('Product draft save failed.'); }
    $ids[$p['slug']] = $id;
}
$results = array();
foreach ($products as $p) {
    $id = $ids[$p['slug']]; $images = array();
    foreach (vpn_mailer_20261008_files($p['prefix']) as $index => $source) { $images[] = vpn_mailer_20261008_attachment($target_dir . wp_basename($source), $id, $p, $index); }
    $content = vpn_mailer_20261008_content($p, $images);
    $wc = new WC_Product_Simple($id); $wc->set_image_id($images[0]); $wc->set_gallery_image_ids(array_slice($images,1)); $wc->set_description($content); $wc->set_status('publish'); $wc->save();
    $tags = wp_set_object_terms($id, $p['tags'], 'product_tag', false); if (is_wp_error($tags)) { throw new RuntimeException($tags->get_error_message()); }
    $specs = array(
        'Feature' => $p['feature'], 'Industrial Use' => $p['industrial_use'], 'Paper Type' => $p['paper_type'],
        'Box Type' => $p['box_type'], 'Shape' => $p['shape'], 'Place of Origin' => 'Vietnam',
        'Model Number' => $p['visual_reference'], 'Brand Name' => 'VPN', 'Province' => 'Ho Chi Minh City',
        'Accessories' => 'Protective inserts, cards and sealing components quoted separately; none verified by closed views.',
        'Custom Order' => 'Custom project; structure and specification confirmed by approved sample.',
        'Liner Type' => $p['liner_type'], 'Logo Printing' => 'Customer artwork on an approved dieline; printing scope confirmed in quotation.',
        'Printing Handling' => 'Process and finish selected from artwork and physical material proof.', 'Color' => $p['color'],
        'Size' => 'Internal dimensions developed from measured packed contents.', 'Thickness' => 'Board grade and thickness confirmed by project specification.',
        'Single Piece Price' => 'Written quotation required; no fixed price established by the image package.',
        'Minimum Order Quantity (MOQ)' => 'Confirmed in the written project quotation.', 'Product Name' => $p['title'],
        'Design' => 'AI-assisted design visualization; final production based on an approved physical sample.'
    );
    $rows = array(); foreach ($specs as $label => $value) { $rows[] = array('label' => $label, 'value' => $value); }
    update_post_meta($id, '_custom_box_product_specs', $rows);
    foreach (array('rank_math_title'=>'seo_title','rank_math_description'=>'seo_description','rank_math_focus_keyword'=>'keyword') as $key => $field) { update_post_meta($id, $key, $p[$field]); }
    update_post_meta($id, 'rank_math_primary_product_cat', (int) $term->term_id);
    update_post_meta($id, 'rank_math_robots', array('index','follow'));
    update_post_meta($id, 'rank_math_canonical_url', get_permalink($id));
    update_post_meta($id, '_vpn_mailer_20261008_source_hash', vpn_mailer_20261008_source_hash($p));
    update_post_meta($id, '_vpn_mailer_20261008_content_hash', hash('sha256', (string) get_post_field('post_content',$id)));
    wc_delete_product_transients($id); clean_post_cache($id);
    $results[] = array('id'=>$id,'slug'=>$p['slug'],'url'=>get_permalink($id),'images'=>count($images),'words'=>str_word_count(wp_strip_all_tags($content)));
}
echo wp_json_encode(array('marker'=>VPN_MAILER_20261008_MARKER,'imported'=>$results), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . PHP_EOL;
