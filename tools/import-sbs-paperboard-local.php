<?php
/** Local-only import and publication. Run with PHP CLI from the project. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('WP_INSTALLING', true); // Prevent unrelated deployment hooks during local bootstrap.
require dirname(__DIR__) . '/wp-load.php';
$host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
$db_host = strtolower((string) DB_HOST);
if (!in_array($host, ['localhost', '127.0.0.1'], true)
    || !in_array($db_host, ['localhost', '127.0.0.1', 'localhost:3306', '127.0.0.1:3306'], true)) {
    fwrite(STDERR, "Refusing to import outside the local WordPress database.\n");
    exit(1);
}
try {
    require_once __DIR__ . '/../wp-content/mu-plugins/vpn-sbs-paperboard-article-import.php';
    $id = vpn_sbs_paperboard_import_run(true);
    $post = get_post($id);
    $images = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'post_parent' => $id, 'numberposts' => -1]);
    echo json_encode([
        'id' => $id,
        'status' => $post->post_status,
        'url' => get_permalink($id),
        'title' => $post->post_title,
        'category' => wp_get_post_categories($id, ['fields' => 'names']),
        'tags' => wp_get_post_terms($id, 'post_tag', ['fields' => 'names']),
        'inline_figures' => substr_count($post->post_content, '<figure'),
        'unresolved_slots' => strpos($post->post_content, 'IMAGE_SLOT_') !== false,
        'featured_image_id' => get_post_thumbnail_id($id),
        'attachment_count' => count($images),
        'rank_math' => [
            'title' => get_post_meta($id, 'rank_math_title', true),
            'description' => get_post_meta($id, 'rank_math_description', true),
            'focus_keyword' => get_post_meta($id, 'rank_math_focus_keyword', true),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
