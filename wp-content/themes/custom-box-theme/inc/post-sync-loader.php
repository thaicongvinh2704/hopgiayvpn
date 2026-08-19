<?php
/**
 * Loads deployable post syncs only when they need work.
 *
 * Keeping completed syncs out of normal requests avoids repeatedly parsing large
 * canonical content bundles and running database-heavy completion validators.
 */

defined('ABSPATH') || exit;

function custom_box_post_sync_registry(): array
{
    return array(
        'inc/custom-vial-box-product-sync.php' => array(
            'version' => 'custom-vial-boxes-seo-20260810-v4',
            'option' => 'custom_box_custom_vial_boxes_sync_version',
            'slug' => 'custom-vial-packaging-box',
        ),
        'inc/kraft-paper-bags-with-handles-post-sync.php' => array(
            'version' => '2026-08-13-v1',
            'option' => 'custom_box_kraft_handles_sync_version',
            'slug' => 'kraft-paper-bags-with-handles',
        ),
        'inc/tissue-paper-for-gift-bags-post-sync.php' => array(
            'version' => '2026-08-15-tissue-paper-v2',
            'option' => 'custom_box_tissue_paper_gift_bags_sync_version',
            'slug' => 'tissue-paper-for-gift-bags',
        ),
        'inc/wrapping-presents-with-kraft-paper-post-sync.php' => array(
            'version' => '2026-08-17-wrapping-kraft-v1',
            'option' => 'custom_box_wrapping_presents_kraft_sync_version',
            'slug' => 'wrapping-presents-with-kraft-paper',
        ),
        'inc/kraft-paper-bag-custom-printing-post-sync.php' => array(
            'version' => '2026-08-17-kraft-bag-printing-v1',
            'option' => 'custom_box_kraft_bag_printing_sync_version',
            'slug' => 'kraft-paper-bag-custom-printing',
        ),
        'inc/eco-friendly-food-packaging-small-business-post-sync.php' => array(
            'version' => '2026-08-17-eco-food-packaging-v1',
            'option' => 'custom_box_eco_food_packaging_sync_version',
            'slug' => 'eco-friendly-food-packaging-small-business',
        ),
        'inc/sustainable-packaging-small-businesses-post-sync.php' => array(
            'version' => '2026-08-18-sustainable-packaging-v2',
            'option' => 'custom_box_sustainable_packaging_sync_version',
            'slug' => 'sustainable-packaging-small-businesses',
        ),
        'inc/best-compostable-packaging-small-batch-cosmetics-post-sync.php' => array(
            'version' => '2026-08-19-compostable-cosmetics-v1',
            'option' => 'custom_box_compostable_cosmetics_sync_version',
            'slug' => 'best-compostable-packaging-small-batch-cosmetics',
        ),
        'inc/why-vpn-paper-box-manufacturer-vietnam-post-sync.php' => array(
            'version' => '2026-08-15-vpn-paper-box-manufacturer-v3',
            'option' => 'custom_box_why_vpn_paper_box_manufacturer_vietnam_sync_version',
            'slug' => 'why-vpn-paper-box-manufacturer-vietnam',
        ),
        'inc/light-brown-recycled-kraft-paper-roll-post-sync.php' => array(
            'version' => '2026-08-15-light-brown-recycled-kraft-paper-roll-v2',
            'option' => 'custom_box_light_brown_recycled_kraft_paper_roll_sync_version',
            'slug' => 'light-brown-recycled-kraft-paper-roll',
        ),
        'inc/custom-vial-box-product-sync.php' => array(
            'version' => 'custom-vial-boxes-seo-20260810-v4',
            'option' => 'custom_box_custom_vial_boxes_sync_version',
            'slug' => 'custom-vial-packaging-box',
        ),
        'inc/how-good-product-packaging-helps-business-grow-post-sync.php' => array(
            'version' => '2026-08-11-v1',
            'option' => 'custom_box_good_packaging_growth_sync_version',
            'slug' => 'how-good-product-packaging-helps-business-grow',
        ),
        'inc/does-product-weight-include-packaging-post-sync.php' => array(
            'version' => '2026-08-11-v1',
            'option' => 'custom_box_product_weight_sync_version',
            'slug' => 'does-product-weight-include-packaging',
        ),
        'inc/food-truck-packaging-and-disposables-post-sync.php' => array(
            'version' => '2026-08-11-v1',
            'option' => 'custom_box_food_truck_packaging_sync_version',
            'slug' => 'food-truck-packaging-and-disposables',
        ),
        'inc/kraft-paper-stand-up-pouches-post-sync.php' => array(
            'version' => '2026-08-11-v1',
            'option' => 'custom_box_kraft_pouch_sync_version',
            'slug' => 'kraft-paper-stand-up-pouches',
        ),
        'inc/kraft-paper-for-bouquet-wrapping-post-sync.php' => array(
            'version' => '2026-08-12-v1',
            'option' => 'custom_box_bouquet_wrap_sync_version',
            'slug' => 'kraft-paper-for-bouquet-wrapping',
        ),
        'inc/girdle-product-packaging-post-sync.php' => array(
            'version' => '2026-08-10-v1',
            'option' => 'custom_box_girdle_packaging_sync_version',
            'slug' => 'girdle-product-packaging-definition-uses',
        ),
        'inc/shoe-box-dimensions-post-sync.php' => array(
            'version' => '2026-08-10-v2',
            'option' => 'custom_box_shoe_box_dimensions_sync_version',
            'slug' => 'shoe-box-dimensions',
        ),
        'inc/large-pizza-box-dimensions-post-sync.php' => array(
            'version' => '2026-08-07-v1',
            'option' => 'custom_box_large_pizza_box_dimensions_sync_version',
            'slug' => 'large-pizza-box-dimensions',
        ),
        'inc/cereal-box-dimensions-post-sync.php' => array(
            'version' => '2026-08-06-v1',
            'option' => 'custom_box_cereal_box_dimensions_sync_version',
            'slug' => 'cereal-box-dimensions',
        ),
        'inc/bakery-paper-packaging-material-selection-post-sync.php' => array(
            'version' => '2026-07-13-v1',
            'option' => 'custom_box_bakery_paper_packaging_material_selection_sync_version',
            'slug' => 'how-to-choose-bakery-packaging-materials',
        ),
        'inc/cardboard-box-weight-post-sync.php' => array(
            'version' => '2026-07-27-v1',
            'option' => 'custom_box_cardboard_box_weight_sync_version',
            'slug' => 'how-much-does-a-cardboard-box-weigh',
        ),
        'inc/how-are-cardboard-boxes-made-post-sync.php' => array(
            'version' => '2026-08-03-v4',
            'option' => 'custom_box_cardboard_boxes_made_sync_version',
            'slug' => 'how-are-cardboard-boxes-made',
        ),
        'inc/packaging-materials-testing-post-sync.php' => array(
            'version' => '2026-08-05-v1',
            'option' => 'custom_box_packaging_materials_testing_sync_version',
            'slug' => 'testing-methods-packaging-materials',
        ),
        'inc/food-packaging-seal-integrity-testing-post-sync.php' => array(
            'version' => '2026-08-06-v1',
            'option' => 'custom_box_food_packaging_seal_integrity_testing_sync_version',
            'slug' => 'food-packaging-seal-integrity-testing',
        ),
        'inc/cosmetic-brand-perception-post-sync.php' => array(
            'version' => '2026-07-06-approved-v1',
            'option' => 'custom_box_cosmetic_brand_perception_sync_version',
            'slug' => 'how-paper-packaging-affects-cosmetic-brand-perception',
        ),
        'inc/cosmetic-paper-packaging-design-post-sync.php' => array(
            'version' => '2026-07-13-v1',
            'option' => 'custom_box_cosmetic_paper_packaging_design_sync_version',
            'slug' => 'how-to-design-paper-packaging-cosmetic-products',
        ),
        'inc/elegant-paper-box-material-selection-post-sync.php' => array(
            'version' => '2026-07-29-v1',
            'option' => 'custom_box_elegant_paper_box_sync_version',
            'slug' => 'what-type-of-paper-for-elegant-packaging-boxes',
        ),
        'inc/e-flute-cardboard-thickness-post-sync.php' => array(
            'version' => '2026-07-29-v1',
            'option' => 'custom_box_e_flute_cardboard_thickness_sync_version',
            'slug' => 'e-flute-corrugated-cardboard-thickness-mm',
        ),
        'inc/food-paper-packaging-selection-post-sync.php' => array(
            'version' => '2026-07-13-v1',
            'option' => 'custom_box_food_paper_packaging_selection_sync_version',
            'slug' => 'what-to-consider-food-paper-packaging',
        ),
        'inc/home-lifestyle-paper-packaging-selection-post-sync.php' => array(
            'version' => '2026-07-23-v1',
            'option' => 'custom_box_home_lifestyle_packaging_sync_version',
            'slug' => 'choose-paper-packaging-home-lifestyle-products',
        ),
        'inc/how-paper-bags-support-retail-packaging-post-sync.php' => array(
            'version' => '2026-07-21-v3',
            'option' => 'custom_box_retail_paper_bags_sync_version',
            'slug' => 'how-paper-bags-support-retail-packaging',
        ),
        'inc/where-do-paper-bags-come-from-post-sync.php' => array(
            'version' => '2026-07-25-v3',
            'option' => 'custom_box_paper_bag_origin_sync_version',
            'slug' => 'where-do-paper-bags-come-from',
        ),
        'inc/how-to-choose-candle-packaging-materials-post-sync.php' => array(
            'version' => '2026-07-15-v2',
            'option' => 'custom_box_candle_packaging_materials_sync_version',
            'slug' => 'how-to-choose-candle-packaging-materials',
        ),
        'inc/how-to-create-premium-food-packaging-with-paper-boxes-post-sync.php' => array(
            'version' => '2026-07-13-v2',
            'option' => 'custom_box_premium_food_packaging_sync_version',
            'slug' => 'how-to-create-premium-food-packaging-with-paper-boxes',
        ),
        'inc/how-to-design-supplement-packaging-layout-post-sync.php' => array(
            'version' => '2026-07-17-v1',
            'option' => 'custom_box_supplement_packaging_layout_sync_version',
            'slug' => 'how-to-design-supplement-packaging-layout',
        ),
        'inc/how-to-package-chocolate-gift-sets-post-sync.php' => array(
            'version' => '2026-07-13-v1',
            'option' => 'custom_box_chocolate_gift_sets_packaging_sync_version',
            'slug' => 'how-to-package-chocolate-gift-sets',
        ),
        'inc/how-to-package-electronics-accessories-safely-post-sync.php' => array(
            'version' => '2026-07-18-v4',
            'option' => 'custom_box_electronics_accessories_packaging_sync_version',
            'slug' => 'how-to-package-electronics-accessories-safely',
        ),
        'inc/how-to-plan-fashion-product-packaging-post-sync.php' => array(
            'version' => '2026-07-22-v1',
            'option' => 'custom_box_fashion_packaging_sync_version',
            'slug' => 'how-to-plan-fashion-product-packaging',
        ),
        'inc/how-to-package-stationery-sets-post-sync.php' => array(
            'version' => '2026-07-25-v1',
            'option' => 'custom_box_stationery_packaging_sync_version',
            'slug' => 'how-to-package-stationery-sets-paper-boxes',
        ),
        'inc/how-to-plan-corporate-gift-packaging-post-sync.php' => array(
            'version' => '2026-07-24-v1',
            'option' => 'custom_box_corporate_gift_packaging_sync_version',
            'slug' => 'how-to-plan-corporate-gift-packaging',
        ),
        'inc/how-to-protect-bottles-in-paper-gift-packaging-post-sync.php' => array(
            'version' => '2026-07-14-v1',
            'option' => 'custom_box_bottle_gift_packaging_protection_sync_version',
            'slug' => 'how-to-protect-bottles-in-paper-gift-packaging',
        ),
        'inc/jewelry-paper-box-packaging-post-sync.php' => array(
            'version' => '2026-07-13-v1',
            'option' => 'custom_box_jewelry_paper_box_packaging_sync_version',
            'slug' => 'how-to-package-jewelry-products-with-paper-boxes',
        ),
        'inc/packaging-inserts-protection-presentation-post-sync.php' => array(
            'version' => '2026-07-20-v1',
            'option' => 'custom_box_packaging_inserts_presentation_sync_version',
            'slug' => 'packaging-inserts-protection-presentation',
        ),
        'inc/paper-bag-production-post-sync.php' => array(
            'version' => '2026-07-30-v1',
            'option' => 'custom_box_paper_bag_production_sync_version',
            'slug' => 'how-to-produce-paper-bags',
        ),
        'inc/paper-box-dieline-post-sync.php' => array(
            'version' => '2026-07-30-v4',
            'option' => 'custom_box_paper_box_dieline_sync_version',
            'slug' => 'what-is-a-dieline-in-packaging',
        ),
        'inc/perfume-paper-box-structure-post-sync.php' => array(
            'version' => '2026-07-13-v1',
            'option' => 'custom_box_perfume_paper_box_structure_sync_version',
            'slug' => 'how-to-choose-paper-box-structure-for-perfume-packaging',
        ),
        'inc/pharmaceutical-paper-packaging-information-post-sync.php' => array(
            'version' => '2026-07-16-v1',
            'option' => 'custom_box_pharmaceutical_packaging_information_sync_version',
            'slug' => 'what-information-pharmaceutical-paper-packaging',
        ),
        'inc/rigid-box-vs-folding-carton-post-sync.php' => array(
            'version' => '2026-07-27-v1',
            'option' => 'custom_box_rigid_box_folding_carton_sync_version',
            'slug' => 'rigid-box-vs-folding-carton',
        ),
        'inc/skincare-paper-packaging-selection-post-sync.php' => array(
            'version' => '2026-07-13-v1',
            'option' => 'custom_box_skincare_paper_packaging_selection_sync_version',
            'slug' => 'what-to-consider-for-skincare-packaging',
        ),
        'inc/standard-box-dimensions-shipping-post-sync.php' => array(
            'version' => '2026-07-23-v1',
            'option' => 'custom_box_shipping_box_dimensions_sync_version',
            'slug' => 'standard-box-dimensions-for-shipping',
        ),
    );
}

function custom_box_post_sync_version_values(array $registry): array
{
    global $wpdb;

    $option_names = array_values(array_unique(array_column($registry, 'option')));
    if (!$option_names) {
        return array();
    }

    $placeholders = implode(', ', array_fill(0, count($option_names), '%s'));
    $query = "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name IN ({$placeholders})";
    $prepared = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($query), $option_names));
    $rows = $wpdb->get_results($prepared, ARRAY_A);
    $values = array();

    foreach ((array) $rows as $row) {
        $values[(string) $row['option_name']] = (string) maybe_unserialize($row['option_value']);
    }

    return $values;
}

function custom_box_post_sync_requested_slug(array $registry): string
{
    $pagenow = isset($GLOBALS['pagenow']) ? (string) $GLOBALS['pagenow'] : '';
    if ('post.php' !== $pagenow || empty($_GET['post'])) {
        return '';
    }

    $post_id = absint(wp_unslash($_GET['post']));
    if (!$post_id) {
        return '';
    }

    $slug = (string) get_post_field('post_name', $post_id);
    if (!$slug) {
        return '';
    }

    foreach ($registry as $entry) {
        if ($slug === $entry['slug']) {
            return $slug;
        }
    }

    return '';
}

function custom_box_post_sync_health_audit_file(array $registry, array $versions): string
{
    if (!current_user_can('manage_options')) {
        return '';
    }

    $interval = max(15 * MINUTE_IN_SECONDS, (int) apply_filters('custom_box_post_sync_health_interval', HOUR_IN_SECONDS));
    $last_run = (int) get_option('custom_box_post_sync_health_last_run', 0);

    if ($last_run && (time() - $last_run) < $interval) {
        return '';
    }

    $files = array_keys($registry);
    $count = count($files);
    if (!$count) {
        return '';
    }

    $cursor = (int) get_option('custom_box_post_sync_health_cursor', 0);
    $selected = '';

    for ($offset = 0; $offset < $count; $offset++) {
        $index = ($cursor + $offset) % $count;
        $file = $files[$index];
        $entry = $registry[$file];
        $stored = isset($versions[$entry['option']]) ? $versions[$entry['option']] : '';

        if ($stored === $entry['version']) {
            $selected = $file;
            $cursor = ($index + 1) % $count;
            break;
        }
    }

    update_option('custom_box_post_sync_health_last_run', time(), false);
    update_option('custom_box_post_sync_health_cursor', $cursor, false);

    return $selected;
}

function custom_box_post_sync_files_to_load(): array
{
    if (!is_admin()) {
        return array();
    }

    if (!current_user_can('manage_options')) {
        return array();
    }

    if (
        (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)
    ) {
        return array();
    }

    $registry = custom_box_post_sync_registry();
    $versions = custom_box_post_sync_version_values($registry);
    $requested_slug = custom_box_post_sync_requested_slug($registry);
    $force_all = isset($_GET['custom_box_run_post_syncs'])
        && '1' === sanitize_text_field(wp_unslash($_GET['custom_box_run_post_syncs']));
    $files = array();

    foreach ($registry as $file => $entry) {
        $stored = isset($versions[$entry['option']]) ? $versions[$entry['option']] : '';

        if ($force_all || $stored !== $entry['version'] || ($requested_slug && $requested_slug === $entry['slug'])) {
            $files[] = $file;
        }
    }

    if (!$force_all && !$files) {
        $health_file = custom_box_post_sync_health_audit_file($registry, $versions);
        if ($health_file) {
            $files[] = $health_file;
        }
    }

    return array_values(array_unique($files));
}
