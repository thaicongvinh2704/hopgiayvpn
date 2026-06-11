<?php
/**
 * Audit and optionally fix post/page slugs that conflict with product category slugs.
 *
 * Usage:
 *   php tools/audit-fix-post-category-slug-conflicts.php --dry-run
 *   php tools/audit-fix-post-category-slug-conflicts.php --apply
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

$apply = in_array('--apply', $argv, true);
$dry_run = in_array('--dry-run', $argv, true) || !$apply;

$terms = get_terms(array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
));

if (is_wp_error($terms)) {
    fwrite(STDERR, $terms->get_error_message() . PHP_EOL);
    exit(1);
}

$category_by_slug = array();

foreach ($terms as $term) {
    $category_by_slug[$term->slug] = $term;
}

$conflicts = get_posts(array(
    'post_type'      => array('post', 'page'),
    'post_status'    => array('publish', 'future', 'draft', 'pending', 'private'),
    'posts_per_page' => -1,
    'orderby'        => 'post_type title',
    'order'          => 'ASC',
    'post_name__in'  => array_keys($category_by_slug),
));

echo 'Product category slugs audited: ' . count($category_by_slug) . PHP_EOL;
echo 'Conflicting posts/pages found: ' . count($conflicts) . PHP_EOL;

$planned = array();

foreach ($conflicts as $post) {
    $category = $category_by_slug[$post->post_name] ?? null;
    $suffix = 'post' === $post->post_type ? 'article' : 'page';
    $base_slug = sanitize_title($post->post_name . '-' . $suffix);
    $new_slug = wp_unique_post_slug($base_slug, (int) $post->ID, $post->post_status, $post->post_type, (int) $post->post_parent);

    $planned[] = array(
        'post_id' => (int) $post->ID,
        'post_type' => $post->post_type,
        'post_status' => $post->post_status,
        'title' => get_the_title($post),
        'old_slug' => $post->post_name,
        'new_slug' => $new_slug,
        'old_url' => get_permalink($post),
        'category_term_id' => $category ? (int) $category->term_id : 0,
        'category_name' => $category ? $category->name : '',
        'category_url' => $category && function_exists('custom_box_get_flat_product_category_url')
            ? custom_box_get_flat_product_category_url($category)
            : '',
    );
}

foreach ($planned as $item) {
    echo sprintf(
        "- %s #%d %s: %s -> %s | category: %s\n",
        $item['post_type'],
        $item['post_id'],
        $item['post_status'],
        $item['old_slug'],
        $item['new_slug'],
        $item['category_name']
    );
}

if ($dry_run) {
    echo "Dry run only. Re-run with --apply to back up and rename conflicting posts/pages.\n";
    exit(0);
}

$backup_dir = __DIR__ . '/slug-conflict-backups';

if (!is_dir($backup_dir) && !mkdir($backup_dir, 0775, true)) {
    fwrite(STDERR, "Could not create backup directory: {$backup_dir}\n");
    exit(1);
}

$timestamp = gmdate('Ymd_His');
$backup_file = $backup_dir . "/post-category-slug-conflicts-{$timestamp}.json";
$backup = array(
    'created_at_utc' => gmdate('c'),
    'site_url' => home_url('/'),
    'planned' => $planned,
);

if (false === file_put_contents($backup_file, wp_json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL)) {
    fwrite(STDERR, "Could not write backup file: {$backup_file}\n");
    exit(1);
}

$renamed = array();
$skipped = array();

foreach ($planned as $item) {
    $result = wp_update_post(array(
        'ID' => $item['post_id'],
        'post_name' => $item['new_slug'],
    ), true);

    if (is_wp_error($result)) {
        $skipped[] = $item['old_slug'] . ': ' . $result->get_error_message();
        continue;
    }

    $renamed[] = $item['old_slug'] . ' -> ' . $item['new_slug'];
}

echo 'Backup file: ' . $backup_file . PHP_EOL;
echo 'Renamed posts/pages: ' . count($renamed) . PHP_EOL;

foreach ($renamed as $line) {
    echo '- ' . $line . PHP_EOL;
}

if ($skipped) {
    echo 'Skipped:' . PHP_EOL;
    foreach ($skipped as $line) {
        echo '- ' . $line . PHP_EOL;
    }
}
