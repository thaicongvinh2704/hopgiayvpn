<?php
/** Verify actual stored products, exact source assets and rendered internal link targets. */
if (!defined('ABSPATH')) { require_once dirname(__DIR__) . '/wp-load.php'; }
if (PHP_SAPI !== 'cli' && (!is_admin() || !(current_user_can('manage_options') || current_user_can('manage_woocommerce')))) { wp_die('Authorized admin or CLI required.', '', array('response' => 403)); }
require_once get_template_directory() . '/inc/mailer-products-20261008-support.php';
$failures = array(); $results = array(); $products = vpn_mailer_20261008_products();
foreach ($products as $p) {
    $post = get_page_by_path($p['slug'], OBJECT, 'product');
    if (!$post) { $failures[] = 'Missing product: ' . $p['slug']; continue; }
    $id = (int) $post->ID; $wc = wc_get_product($id); $content = (string) $post->post_content;
    $words = str_word_count(wp_strip_all_tags($content)); $short_words = str_word_count(wp_strip_all_tags($post->post_excerpt));
    if ('publish' !== $post->post_status || $p['title'] !== $post->post_title || $p['short'] !== $post->post_excerpt || VPN_MAILER_20261008_MARKER !== get_post_meta($id,'_vpn_sample_import',true)) { $failures[] = $p['slug'] . ': product state mismatch'; }
    if ($words < 1500 || $words > 2000 || $short_words < 120 || $short_words > 180 || preg_match('/<h1\b/i',$content) || preg_match('/\{\{[A-Z0-9_]+\}\}/',$content) || 3 !== substr_count($content,'<figure ') || 9 !== substr_count($content,'<h2>')) { $failures[] = $p['slug'] . ': content structure mismatch'; }
    if (false === stripos(wp_strip_all_tags($content), $p['keyword']) || false === strpos($content,'AI-assisted') || false === strpos($content,'Ho Chi Minh City, Vietnam')) { $failures[] = $p['slug'] . ': missing keyword or source/company context'; }
    if (vpn_mailer_20261008_source_hash($p) !== get_post_meta($id,'_vpn_mailer_20261008_source_hash',true) || hash('sha256',$content) !== get_post_meta($id,'_vpn_mailer_20261008_content_hash',true)) { $failures[] = $p['slug'] . ': release hash mismatch'; }
    if (!$wc || $wc->get_price() !== '' || $wc->get_sku() !== $p['visual_reference'] || $wc->get_stock_status() !== 'onbackorder') { $failures[] = $p['slug'] . ': quote-only commercial data mismatch'; }
    $term = get_term_by('slug','corrugated-mailer-boxes','product_cat');
    if (!$term || array((int)$term->term_id) !== $wc->get_category_ids() || (int)get_post_meta($id,'rank_math_primary_product_cat',true) !== (int)$term->term_id) { $failures[] = $p['slug'] . ': wrong category'; }
    $tag_names = wp_get_object_terms($id,'product_tag',array('fields'=>'names')); $expected_tags = $p['tags']; sort($tag_names); sort($expected_tags);
    if ($tag_names !== $expected_tags) { $failures[] = $p['slug'] . ': wrong tags'; }
    foreach (array('rank_math_title'=>'seo_title','rank_math_description'=>'seo_description','rank_math_focus_keyword'=>'keyword') as $key=>$field) { if (get_post_meta($id,$key,true) !== $p[$field]) { $failures[] = $p['slug'] . ': wrong ' . $key; } }
    if (get_permalink($id) !== get_post_meta($id,'rank_math_canonical_url',true) || array('index','follow') !== get_post_meta($id,'rank_math_robots',true)) { $failures[] = $p['slug'] . ': canonical or robots mismatch'; }
    $specs = get_post_meta($id,'_custom_box_product_specs',true);
    if (!is_array($specs) || 21 !== count($specs) || false === strpos($specs[18]['value'] ?? '', 'written project quotation')) { $failures[] = $p['slug'] . ': specs or unverified MOQ'; }
    $images = array_merge(array($wc->get_image_id()),$wc->get_gallery_image_ids());
    if (6 !== count(array_unique($images))) { $failures[] = $p['slug'] . ': expected six unique gallery images'; }
    foreach (vpn_mailer_20261008_files($p['prefix']) as $index=>$source) {
        $aid = $images[$index] ?? 0; $file = $aid ? get_attached_file($aid) : ''; $meta = $aid ? wp_get_attachment_metadata($aid) : false;
        if (!$file || !is_file($file) || !is_file($source) || wp_basename($source) !== wp_basename($file) || hash_file('sha256',$file) !== hash_file('sha256',$source) || filesize($file) >= 100000 || !is_array($meta) || ($meta['width']??0) !== 450 || ($meta['height']??0) !== 570 || (int)wp_get_post_parent_id($aid) !== $id || false === strpos(get_post_meta($aid,'_wp_attachment_image_alt',true),'AI-assisted')) { $failures[] = $p['slug'] . ': image metadata, hash or ordering mismatch ' . $index; }
    }
    if ($content !== vpn_mailer_20261008_content($p,$images)) { $failures[] = $p['slug'] . ': saved copy differs from current source or links'; }
    $results[] = array('id'=>$id,'url'=>get_permalink($id),'words'=>$words,'short_words'=>$short_words,'images'=>count($images),'specs'=>is_array($specs)?count($specs):0);
}
$batch_ids = get_posts(array('post_type'=>'product','post_status'=>'any','numberposts'=>-1,'fields'=>'ids','meta_key'=>'_vpn_sample_import','meta_value'=>VPN_MAILER_20261008_MARKER));
if (count($batch_ids) !== 5) { $failures[] = 'Batch count is not five.'; }
echo wp_json_encode(array('products'=>$results,'failures'=>array_values(array_unique($failures))),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . PHP_EOL;
if ($failures) { throw new RuntimeException('Mailer batch verification failed: ' . implode('; ',$failures)); }
