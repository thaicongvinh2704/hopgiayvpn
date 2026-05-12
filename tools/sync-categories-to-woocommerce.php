<?php
/**
 * Copy existing WordPress categories into WooCommerce product categories.
 *
 * Usage:
 * php tools/sync-categories-to-woocommerce.php
 */

require dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    echo "WooCommerce product_cat taxonomy is not available.\n";
    exit(1);
}

$source_parent = get_term_by('name', 'Custom Packaging Boxes', 'category');

if (!$source_parent || is_wp_error($source_parent)) {
    echo "Source parent category \"Custom Packaging Boxes\" was not found.\n";
    exit(1);
}

$source_terms = get_terms(array(
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'orderby'    => 'term_id',
    'order'      => 'ASC',
));

if (is_wp_error($source_terms)) {
    echo "Could not read source categories: " . $source_terms->get_error_message() . "\n";
    exit(1);
}

$term_map = array();
$created = 0;
$updated = 0;
$skipped = 0;

function custom_box_sync_product_category($source_term, $parent_product_term_id = 0) {
    global $created, $updated, $skipped;

    $existing = get_term_by('slug', $source_term->slug, 'product_cat');
    $args = array(
        'description' => $source_term->description,
        'parent'      => $parent_product_term_id,
        'slug'        => $source_term->slug,
    );

    if ($existing && !is_wp_error($existing)) {
        $result = wp_update_term($existing->term_id, 'product_cat', array(
            'name'        => $source_term->name,
            'description' => $source_term->description,
            'parent'      => $parent_product_term_id,
        ));

        if (is_wp_error($result)) {
            echo "Skipped {$source_term->name}: " . $result->get_error_message() . "\n";
            $skipped++;
            return 0;
        }

        $product_term_id = (int) $existing->term_id;
        $updated++;
    } else {
        $result = wp_insert_term($source_term->name, 'product_cat', $args);

        if (is_wp_error($result)) {
            echo "Skipped {$source_term->name}: " . $result->get_error_message() . "\n";
            $skipped++;
            return 0;
        }

        $product_term_id = (int) $result['term_id'];
        $created++;
    }

    $image_id = (int) get_term_meta($source_term->term_id, 'custom_box_category_image_id', true);
    $is_featured = (int) get_term_meta($source_term->term_id, 'custom_box_category_featured', true);

    if ($image_id) {
        update_term_meta($product_term_id, 'thumbnail_id', $image_id);
        update_term_meta($product_term_id, 'custom_box_category_image_id', $image_id);
    }

    if ($is_featured) {
        update_term_meta($product_term_id, 'custom_box_category_featured', 1);
    } else {
        delete_term_meta($product_term_id, 'custom_box_category_featured');
    }

    return $product_term_id;
}

$root_product_term_id = custom_box_sync_product_category($source_parent, 0);

if (!$root_product_term_id) {
    echo "Could not sync source parent category.\n";
    exit(1);
}

$term_map[$source_parent->term_id] = $root_product_term_id;

foreach ($source_terms as $source_term) {
    if ((int) $source_term->term_id === (int) $source_parent->term_id) {
        continue;
    }

    if ((int) $source_term->parent !== (int) $source_parent->term_id) {
        continue;
    }

    $term_map[$source_term->term_id] = custom_box_sync_product_category($source_term, $root_product_term_id);
}

echo "WooCommerce category sync complete.\n";
echo "Created: {$created}\n";
echo "Updated: {$updated}\n";
echo "Skipped: {$skipped}\n";
