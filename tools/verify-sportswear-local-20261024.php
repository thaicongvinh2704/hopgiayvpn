<?php
if ('cli' !== PHP_SAPI) { http_response_code(404); exit; }
require dirname(__DIR__) . '/wp-load.php';
if ('local' !== wp_get_environment_type() || 'localhost' !== wp_parse_url(home_url(), PHP_URL_HOST)) { exit(1); }
add_filter('pre_wp_mail', '__return_false');
$data = vpn_sportswear_release_data();
$post = get_page_by_path($data['slug'], OBJECT, 'product');
$before = json_decode((string) file_get_contents(dirname(__DIR__) . '/outputs/sportswear-ctr-20261024/local-before.json'), true);
$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
wp_set_current_user((int) $admins[0]);
$checks = array();
$checks['existing_id'] = (int) $post->ID === $before['id'];
foreach (array('post_title' => 'title', 'post_name' => 'slug', 'post_status' => 'status', 'post_date' => 'date') as $field => $saved) {
    $checks[$field . '_preserved'] = $post->$field === $before[$saved];
}
$checks['content_saved'] = $post->post_content === vpn_sportswear_release_content();
$checks['excerpt_saved'] = $post->post_excerpt === $data['excerpt'];
$checks['rank_math_title_saved'] = get_post_meta($post->ID, 'rank_math_title', true) === $data['title'];
$checks['rank_math_description_saved'] = get_post_meta($post->ID, 'rank_math_description', true) === $data['description'];
$checks['focus_keywords_saved'] = get_post_meta($post->ID, 'rank_math_focus_keyword', true) === $data['keyword'];
$checks['images_preserved'] = (string) get_post_meta($post->ID, '_thumbnail_id', true) === $before['meta']['_thumbnail_id'][0]
    && (string) get_post_meta($post->ID, '_product_image_gallery', true) === $before['meta']['_product_image_gallery'][0];
$checks['specifications_preserved'] = maybe_serialize(get_post_meta($post->ID, '_custom_box_product_specs', true)) === $before['meta']['_custom_box_product_specs'][0];
$checks['category_added'] = has_term('fashion-sportswear-packaging', 'product_cat', $post->ID);
$checks['old_category_retained'] = has_term('custom-paper-boxes', 'product_cat', $post->ID);
$backup = get_option('vpn_sportswear_release_before_' . $post->ID);
$checks['backup_matches'] = $backup['content'] === $before['content'] && $backup['excerpt'] === $before['excerpt'];
$state_before = hash('sha256', serialize(array(get_post($post->ID), get_post_meta($post->ID), $backup)));
$repeat = vpn_sportswear_release_apply();
$checks['idempotent'] = 'already applied' === ($repeat['status'] ?? '')
    && $state_before === hash('sha256', serialize(array(get_post($post->ID), get_post_meta($post->ID), get_option('vpn_sportswear_release_before_' . $post->ID))));
$related = wc_get_related_products($post->ID, 8);
$slugs = array_map(static fn($id) => get_post_field('post_name', $id), $related);
$expected = $data['related'];
sort($slugs); sort($expected);
$checks['related_products_exactly_four_apparel_products'] = $slugs === $expected;
$control = get_page_by_path('custom-t-shirt-packaging-box', OBJECT, 'product');
$sentinel = array(101, 202);
$checks['related_filter_unrelated_product_unchanged'] = apply_filters('woocommerce_related_products', $sentinel, $control->ID, array('limit' => 8)) === $sentinel;
$product_count = (int) count(get_posts(array('post_type' => 'product', 'name' => $data['slug'], 'post_status' => 'any', 'numberposts' => -1)));
$checks['no_duplicate_product'] = 1 === $product_count;
$result = array('id' => $post->ID, 'checks' => $checks, 'related' => $slugs, 'category_slugs' => wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'slugs')),
    'before_words' => str_word_count(wp_strip_all_tags($before['content'])), 'after_words' => str_word_count(wp_strip_all_tags($post->post_content)),
    'seo_title' => get_post_meta($post->ID, 'rank_math_title', true), 'seo_description' => get_post_meta($post->ID, 'rank_math_description', true));
file_put_contents(dirname(__DIR__) . '/outputs/sportswear-ctr-20261024/local-database-verification.json', wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
exit(in_array(false, $checks, true) ? 1 : 0);
