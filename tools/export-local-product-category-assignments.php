<?php
/**
 * Export local WooCommerce product/category assignments to a JSON manifest.
 *
 * Usage:
 *   php tools/export-local-product-category-assignments.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

$products = get_posts(array(
    'post_type'      => 'product',
    'post_status'    => array('publish', 'draft', 'pending', 'private'),
    'posts_per_page' => -1,
    'orderby'        => 'post_name',
    'order'          => 'ASC',
));

$category_slugs = array();
$manifest = array(
    'version'      => 1,
    'generated_at' => gmdate('c'),
    'source_url'   => home_url('/'),
    'taxonomy'     => 'product_cat',
    'mode'         => 'strict_manifest_product_categories',
    'categories'   => array(),
    'products'     => array(),
);

foreach ($products as $product) {
    $terms = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'all'));
    $slugs = array();

    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $slugs[] = $term->slug;
            $category_slugs[$term->slug] = array(
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }
    }

    sort($slugs);

    $manifest['products'][$product->post_name] = array(
        'title'      => get_the_title($product),
        'status'     => get_post_status($product),
        'categories' => $slugs,
    );
}

ksort($category_slugs);
ksort($manifest['products']);
$manifest['categories'] = $category_slugs;

$output_path = dirname(__DIR__) . '/wp-content/themes/custom-box-theme/inc/product-category-assignment-manifest.json';
$encoded = wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (!$encoded) {
    fwrite(STDERR, "Could not encode product category manifest.\n");
    exit(1);
}

file_put_contents($output_path, $encoded . PHP_EOL);

echo 'Exported ' . count($manifest['products']) . ' products and ' . count($manifest['categories']) . ' categories.' . PHP_EOL;
echo $output_path . PHP_EOL;
