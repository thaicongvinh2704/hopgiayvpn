<?php
/** Read-only inventory. No customer uploads, drafts or password-protected posts. */
if (PHP_SAPI !== 'cli') { exit(1); }
require dirname(__DIR__) . '/wp-load.php';
$uploads = wp_upload_dir();
$items = [];
$attachment_ids = [];
foreach (get_posts(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','has_password'=>false]) as $post_id) {
    $attachment_ids[] = get_post_thumbnail_id($post_id);
}
// These three published catalog photos are already public in the money-page hero.
if (function_exists('custom_box_paper_bag_hero_products')) {
    foreach (custom_box_paper_bag_hero_products() as $product) {
        if ($product->get_status() === 'publish') { $attachment_ids[] = $product->get_image_id(); }
    }
}
foreach (array_unique($attachment_ids) as $id) {
    if (!$id) { continue; }
    $meta = wp_get_attachment_metadata($id);
    if (empty($meta['file'])) { continue; }
    $files = [$meta['file']];
    $dir = dirname($meta['file']);
    foreach (($meta['sizes'] ?? []) as $size) {
        if (!empty($size['file'])) { $files[] = $dir . '/' . $size['file']; }
    }
    foreach (array_unique($files) as $file) {
        if (!preg_match('/\.(webp|jpe?g|png|avif)$/i', $file) || str_contains($file, '..')) { continue; }
        $path = $uploads['basedir'] . '/' . $file;
        if (is_file($path)) { $items[$file] = ['file'=>$file,'bytes'=>filesize($path),'attachment_id'=>$id]; }
    }
}
$out = dirname(__DIR__) . '/outputs/performance-20261103';
if (!is_dir($out)) { mkdir($out, 0755, true); }
file_put_contents($out . '/public-featured-inventory.json', wp_json_encode(['basedir'=>$uploads['basedir'],'items'=>array_values($items)], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode(['files'=>count($items),'bytes'=>array_sum(array_column($items,'bytes'))]);
