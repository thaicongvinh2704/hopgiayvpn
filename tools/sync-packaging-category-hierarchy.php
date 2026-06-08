<?php
/**
 * Sync WooCommerce packaging category hierarchy to the active theme taxonomy.
 *
 * Usage:
 *   php tools/sync-packaging-category-hierarchy.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

if (!function_exists('custom_box_get_packaging_parent_category') || !function_exists('custom_box_get_packaging_category_slugs')) {
    fwrite(STDERR, "Custom packaging category helpers are not available.\n");
    exit(1);
}

$parent = custom_box_get_packaging_parent_category();

if (!$parent || is_wp_error($parent)) {
    fwrite(STDERR, "Missing parent category: custom-packaging-boxes.\n");
    exit(1);
}

$parent_id = (int) $parent->term_id;
$active_slugs = custom_box_get_packaging_category_slugs();
$active_ids = array();
$attached = 0;
$detached = 0;
$missing = array();

foreach ($active_slugs as $slug) {
    $term = get_term_by('slug', $slug, 'product_cat');

    if (!$term || is_wp_error($term)) {
        $missing[] = $slug;
        continue;
    }

    $term_id = (int) $term->term_id;
    $active_ids[] = $term_id;

    if ($term_id === $parent_id || (int) $term->parent === $parent_id) {
        continue;
    }

    $updated = wp_update_term($term_id, 'product_cat', array(
        'parent' => $parent_id,
    ));

    if (is_wp_error($updated)) {
        fwrite(STDERR, "Failed attaching {$slug}: " . $updated->get_error_message() . "\n");
        continue;
    }

    $attached++;
}

$old_children = get_terms(array(
    'taxonomy'   => 'product_cat',
    'parent'     => $parent_id,
    'hide_empty' => false,
));

if (is_wp_error($old_children)) {
    fwrite(STDERR, "Failed loading current child categories: " . $old_children->get_error_message() . "\n");
    exit(1);
}

foreach ($old_children as $child) {
    $child_id = (int) $child->term_id;

    if (in_array($child_id, $active_ids, true)) {
        continue;
    }

    $updated = wp_update_term($child_id, 'product_cat', array(
        'parent' => 0,
    ));

    if (is_wp_error($updated)) {
        fwrite(STDERR, "Failed detaching {$child->slug}: " . $updated->get_error_message() . "\n");
        continue;
    }

    $detached++;
}

flush_rewrite_rules(false);

echo 'Packaging parent: ' . $parent->name . ' (' . $parent->slug . ')' . PHP_EOL;
echo 'Active categories attached: ' . $attached . PHP_EOL;
echo 'Inactive old children detached: ' . $detached . PHP_EOL;

if ($missing) {
    echo 'Missing active categories: ' . implode(', ', $missing) . PHP_EOL;
}
