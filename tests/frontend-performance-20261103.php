<?php
/** Read-only checks against local WP; no form submission, mail or conversions. */
if (PHP_SAPI !== 'cli') { exit(1); }
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'local') { fwrite(STDERR, "Run these checks only in the local WordPress environment.\n"); exit(1); }
$result_dir = dirname(__DIR__) . '/outputs/performance-20261103';
if (!is_dir($result_dir)) { mkdir($result_dir, 0755, true); }
$checks = [];
function perf_check($name, $condition) { global $checks; $checks[] = ['name'=>$name,'pass'=>(bool)$condition]; }
$manifest = vpn_performance_media_manifest();
$uploads = wp_upload_dir();
$asset_dir = get_template_directory() . '/assets/images/performance-20261103/';
foreach ($manifest['featured'] as $relative=>$filename) {
    perf_check('Identical bytes: ' . $relative, hash_file('sha256', $uploads['basedir'].'/'.$relative) === hash_file('sha256', $asset_dir.$filename));
}
$file = '2026/11/container-thumbnail-768x432.webp';
$url = $uploads['baseurl'].'/'.$file;
perf_check('Mapped public derivative', vpn_performance_public_image_url($url) !== $url);
perf_check('Explicit webp extension', str_ends_with(vpn_performance_public_image_url($url), '.webp'));
foreach ([$url.'?original', $url.'#detail', 'https://external.example/wp-content/uploads/'.$file, $uploads['baseurl'].'/private-unlisted.webp'] as $excluded) {
    perf_check('No rewrite for excluded URL ' . $excluded, vpn_performance_public_image_url($excluded) === $excluded);
}
$post = get_page_by_path('how-many-paper-bags-fit-in-a-container', OBJECT, 'post');
global $wp_query;
$previous_query = $wp_query;
$wp_query = new WP_Query(['p'=>$post->ID,'post_type'=>'post']);
$thumb = get_post_thumbnail_id($post->ID);
remove_filter('wp_get_attachment_image_attributes', 'vpn_performance_featured_image_attributes', 30);
$before = wp_get_attachment_image($thumb, 'large', false, ['class'=>'wp-post-image','loading'=>'eager','fetchpriority'=>'high']);
add_filter('wp_get_attachment_image_attributes', 'vpn_performance_featured_image_attributes', 30, 3);
$after = wp_get_attachment_image($thumb, 'large', false, ['class'=>'wp-post-image','loading'=>'eager','fetchpriority'=>'high']);
function image_attrs($html) {
    $doc = new DOMDocument(); @$doc->loadHTML($html); $attrs=[];
    foreach ($doc->getElementsByTagName('img')->item(0)->attributes as $attr) { $attrs[$attr->name]=$attr->value; }
    return $attrs;
}
$original = image_attrs($before); $changed = image_attrs($after);
perf_check('Featured source cached', $original['src'] !== $changed['src']);
perf_check('Responsive sources cached', str_contains($changed['srcset'] ?? '', 'performance-20261103'));
unset($original['src'], $original['srcset'], $changed['src'], $changed['srcset']);
perf_check('All visual and loading attributes preserved', $original === $changed);
$attr = ['src'=>$url,'width'=>768,'height'=>432,'alt'=>'Existing image'];
perf_check('Unrelated attachment untouched', vpn_performance_featured_image_attributes($attr, (object)['ID'=>0], 'large') === $attr);
$draft_filter = static function ($status) { return 'draft'; };
add_filter('get_post_status', $draft_filter);
perf_check('Draft context untouched', vpn_performance_featured_image_attributes($attr, (object)['ID'=>$thumb], 'large') === $attr);
remove_filter('get_post_status', $draft_filter);
$wp_query = $previous_query;
foreach (custom_box_paper_bag_hero_products() as $product) {
    $hero_attrs = image_attrs(custom_box_paper_bag_product_image($product, true, false));
    $card_attrs = image_attrs(custom_box_paper_bag_product_image($product, false, false));
    perf_check('Hero starts without scroll: '.$product->get_slug(), $hero_attrs['loading'] === 'eager');
    perf_check('Below-fold cards stay lazy: '.$product->get_slug(), $card_attrs['loading'] === 'lazy');
    perf_check('Only first hero has high priority: '.$product->get_slug(), $hero_attrs['fetchpriority'] === 'auto');
}
$scripts = wp_scripts();
wp_register_script('main-js','/main.js',[],null,true);
wp_register_script('vpn-chat-widget','/widget.js',[],null,true);
wp_register_script('vpn-chat-launcher','/launcher.js',['vpn-chat-widget'],null,true);
wp_register_script('unrelated-script','/unrelated.js',[],null,true);
vpn_performance_defer_frontend_scripts();
perf_check('Known script uses native strategy', $scripts->get_data('main-js','strategy') === 'defer');
perf_check('Chat dependency order retained', $scripts->registered['vpn-chat-launcher']->deps === ['vpn-chat-widget']);
perf_check('Unrelated scripts untouched', !$scripts->get_data('unrelated-script','strategy'));
$result=['passed'=>count(array_filter($checks,fn($r)=>$r['pass'])),'total'=>count($checks),'checks'=>$checks];
file_put_contents(dirname(__DIR__).'/outputs/performance-20261103/local-code-checks.json',json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode(['passed'=>$result['passed'],'total'=>$result['total'],'failures'=>array_values(array_filter($checks,fn($r)=>!$r['pass']))]);
exit($result['passed'] === $result['total'] ? 0 : 1);
