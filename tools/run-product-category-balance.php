<?php
/**
 * Run sample product category balance from CLI.
 */

require dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_category_migration_apply_products_to_targets')) {
    echo 'Missing category migration function.' . PHP_EOL;
    exit(1);
}

$published = 0;
if (function_exists('custom_box_product_sample_publish_category_balance_products')) {
    $published = custom_box_product_sample_publish_category_balance_products();
}

$updated = custom_box_category_migration_apply_products_to_targets();

echo 'Published balanced sample products: ' . (int) $published . PHP_EOL;
echo 'Product category migration updated: ' . (int) $updated . PHP_EOL;

if (function_exists('custom_box_product_sample_category_balance_report')) {
    echo custom_box_product_sample_category_balance_report();
}

echo PHP_EOL . 'Published sample products in local DB:' . PHP_EOL;
$products = get_posts(array(
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'post_name',
    'order'          => 'ASC',
    'meta_query'     => array(
        array(
            'key'     => '_vpn_sample_import',
            'compare' => 'EXISTS',
        ),
    ),
));

foreach ($products as $product) {
    $terms = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'slugs'));
    if (is_wp_error($terms)) {
        $terms = array();
    }

    echo '- ' . $product->post_name . ' | ' . implode(', ', $terms) . PHP_EOL;
}
