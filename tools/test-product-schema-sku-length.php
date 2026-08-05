<?php
/**
 * Verifies Product JSON-LD SKU length handling without changing product data.
 */

require_once dirname(__DIR__) . '/wp-load.php';

function custom_box_schema_sku_test_fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$boundary_cases = array(
    '' => false,
    'A' => true,
    str_repeat('a', 50) => true,
    str_repeat('a', 51) => false,
);

foreach ($boundary_cases as $sku => $expected) {
    if (custom_box_product_schema_sku_is_valid($sku) !== $expected) {
        custom_box_schema_sku_test_fail('Boundary validation failed for SKU length ' . strlen($sku) . '.');
    }
}

$product_ids = wc_get_products(array(
    'status' => 'publish',
    'limit' => -1,
    'return' => 'ids',
));

$before = array();
$valid_count = 0;
$empty_count = 0;
$overlong_count = 0;
$valid_lookup = null;
$overlong_lookup = null;
$empty_product_id = 0;

foreach ($product_ids as $product_id) {
    $product = wc_get_product($product_id);
    if (!$product) {
        continue;
    }

    $sku = (string) $product->get_sku();
    $length = function_exists('mb_strlen') ? mb_strlen($sku, 'UTF-8') : strlen($sku);
    $before[(int) $product_id] = $sku;

    $entity = custom_box_add_quote_product_schema_fields(array(
        '@type' => 'Product',
        'name' => $product->get_name(),
        'sku' => $sku,
    ), $product);

    if ($length >= 1 && $length <= 50) {
        ++$valid_count;
        if (!isset($entity['sku']) || $entity['sku'] !== $sku) {
            custom_box_schema_sku_test_fail('A valid SKU was changed or removed for product ' . $product_id . '.');
        }
        if (null === $valid_lookup) {
            $valid_lookup = array(
                'id' => (int) $product_id,
                'sku' => $sku,
                'lookup_before' => (int) wc_get_product_id_by_sku($sku),
            );
        }
    } else {
        if (isset($entity['sku'])) {
            custom_box_schema_sku_test_fail('An invalid SKU remained in schema for product ' . $product_id . '.');
        }

        if (0 === $length) {
            ++$empty_count;
            if (!$empty_product_id) {
                $empty_product_id = (int) $product_id;
            }
        } else {
            ++$overlong_count;
            if (null === $overlong_lookup) {
                $overlong_lookup = array(
                    'id' => (int) $product_id,
                    'sku' => $sku,
                    'lookup_before' => (int) wc_get_product_id_by_sku($sku),
                );
            }
        }
    }
}

if ($valid_lookup && (int) wc_get_product_id_by_sku($valid_lookup['sku']) !== $valid_lookup['lookup_before']) {
    custom_box_schema_sku_test_fail('Valid SKU lookup changed.');
}
if ($overlong_lookup && (int) wc_get_product_id_by_sku($overlong_lookup['sku']) !== $overlong_lookup['lookup_before']) {
    custom_box_schema_sku_test_fail('Overlong SKU lookup changed.');
}

$after = array();
foreach (array_keys($before) as $product_id) {
    $product = wc_get_product($product_id);
    $after[$product_id] = $product ? (string) $product->get_sku() : null;
}

if ($before !== $after) {
    custom_box_schema_sku_test_fail('Product SKU data changed during schema validation.');
}

$original_wp_query = isset($GLOBALS['wp_query']) ? $GLOBALS['wp_query'] : null;
$original_post = isset($GLOBALS['post']) ? $GLOBALS['post'] : null;
$integration_cases = array(
    array('id' => $valid_lookup['id'], 'expect_sku' => true),
    array('id' => $overlong_lookup['id'], 'expect_sku' => false),
    array('id' => $empty_product_id, 'expect_sku' => false),
);

foreach ($integration_cases as $case) {
    $query = new WP_Query(array(
        'p' => $case['id'],
        'post_type' => 'product',
    ));
    $GLOBALS['wp_query'] = $query;
    $GLOBALS['post'] = $query->post;
    setup_postdata($query->post);

    if (!function_exists('is_product') || !is_product()) {
        custom_box_schema_sku_test_fail('Could not establish product-page context for integration test.');
    }

    $product = wc_get_product($case['id']);
    $filtered = custom_box_rank_math_json_ld(array(
        'schema-testProduct' => array(
            '@type' => 'Product',
            'name' => $product->get_name(),
            'sku' => (string) $product->get_sku(),
        ),
    ));
    $has_sku = isset($filtered['schema-testProduct']['sku']);

    if ($has_sku !== $case['expect_sku']) {
        custom_box_schema_sku_test_fail('Product-page JSON-LD integration failed for product ' . $case['id'] . '.');
    }
    if (
        $filtered['schema-testProduct']['name'] !== $product->get_name()
        || 'VPN Packaging' !== $filtered['schema-testProduct']['brand']['name']
        || 'Offer' !== $filtered['schema-testProduct']['offers']['@type']
    ) {
        custom_box_schema_sku_test_fail('Unrelated Product schema fields changed for product ' . $case['id'] . '.');
    }
}

wp_reset_postdata();
$GLOBALS['wp_query'] = $original_wp_query;
$GLOBALS['post'] = $original_post;

$article_sku = str_repeat('z', 51);
$non_product = custom_box_rank_math_json_ld(array(
    'schema-testArticle' => array(
        '@type' => 'Article',
        'sku' => $article_sku,
    ),
));
if (!isset($non_product['schema-testArticle']['sku']) || $article_sku !== $non_product['schema-testArticle']['sku']) {
    custom_box_schema_sku_test_fail('Non-product schema was changed.');
}

echo wp_json_encode(array(
    'published_products' => count($before),
    'valid_skus_preserved' => $valid_count,
    'empty_skus_removed_from_schema' => $empty_count,
    'overlong_skus_removed_from_schema' => $overlong_count,
    'product_json_ld_integration_cases' => count($integration_cases),
    'product_schema_name_brand_offers_preserved' => true,
    'non_product_schema_unchanged' => true,
    'woocommerce_sku_data_unchanged' => true,
    'woocommerce_sku_lookup_unchanged' => true,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
