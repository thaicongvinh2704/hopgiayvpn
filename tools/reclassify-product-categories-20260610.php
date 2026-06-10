<?php
/**
 * Apply the reviewed product-category assignments.
 *
 * Usage:
 *   php tools/reclassify-product-categories-20260610.php --dry-run
 *   php tools/reclassify-product-categories-20260610.php --apply
 */

require dirname(__DIR__) . '/wp-load.php';

$apply = in_array('--apply', $argv, true);

function vpn_cat_add(array &$set, array $slugs): void {
    foreach ($slugs as $slug) {
        $set[$slug] = true;
    }
}

function vpn_cat_has(string $value, array $needles): bool {
    foreach ($needles as $needle) {
        if (str_contains($value, $needle)) {
            return true;
        }
    }
    return false;
}

function vpn_reviewed_product_categories(string $slug): array {
    $set = array();

    $groups = array(
        'paper-tube-packaging' => array('paper-tube', 'cylindrical'),
        'corrugated-mailer-boxes' => array('corrugated', 'mailer'),
        'magnetic-closure-boxes' => array('magnetic'),
        'drawer-boxes' => array('drawer'),
        'cosmetic-paper-boxes' => array('cosmetic', 'essential-oil'),
        'skincare-packaging-boxes' => array('skincare'),
        'perfume-packaging-boxes' => array('perfume'),
        'jewelry-paper-boxes' => array('watch', 'jewelry'),
        'candle-packaging-boxes' => array('candle'),
        'paper-bags-with-logo' => array('paper-bag', 'shopping-bag', 'gift-bag'),
        'electronics-accessories-packaging' => array('phone-', 'charging-cable'),
        'fashion-sportswear-packaging' => array('apparel', 'shoe-', 'belt-', 'underwear-', 'sportswear-', 't-shirt-', 'wallet-'),
        'sports-packaging-boxes' => array('sports-', 'pickleball', 'knee-support'),
        'pharmaceutical-packaging-boxes' => array('medical-', 'pill-', 'tablet-', 'vial-', 'ampoule-'),
        'supplement-packaging-boxes' => array('supplement-', 'tablet-', 'ampoule-'),
        'back-to-school-stationery-packaging' => array('colored-pencil', 'crayon', 'fountain-pen', 'stationery'),
        'home-lifestyle-packaging' => array('candle', 'dinnerware', 'knife-set', 'thermos', 'tumbler', 'incense'),
    );

    foreach ($groups as $category => $needles) {
        if (vpn_cat_has($slug, $needles)) {
            vpn_cat_add($set, array($category));
        }
    }

    if (vpn_cat_has($slug, array('cosmetic', 'skincare', 'essential-oil', 'ampoule', 'perfume'))) {
        vpn_cat_add($set, array('beauty-skincare-packaging'));
    }
    if (vpn_cat_has($slug, array('bakery', 'pastry', 'cake'))) {
        vpn_cat_add($set, array('bakery-packaging-boxes', 'food-paper-boxes', 'premium-food-beverage-packaging'));
    }
    if (vpn_cat_has($slug, array('pizza', 'tea-', 'food-', 'pet-food', 'mug-', 'mooncake', 'chocolate'))) {
        vpn_cat_add($set, array('food-paper-boxes', 'premium-food-beverage-packaging'));
    }
    if (vpn_cat_has($slug, array('mooncake', 'chocolate'))) {
        vpn_cat_add($set, array('chocolate-gift-boxes'));
    }
    if (str_contains($slug, 'wine')) {
        vpn_cat_add($set, array('wine-premium-drink-packaging', 'gift-paper-boxes'));
    }

    $rigid = array(
        'rigid', 'drawer', 'magnetic', 'watch-box', 'wine-gift-box', 'tea-gift-box',
        'mooncake-gift-box', 'luxury-mooncake', 'perfume-gift-set', 'pastry-gift-box',
        'luxury-wine-bottle', 'luxury-gift-box', 'single-wine-bottle-gift-box',
        'wine-bottle-gift-box',
    );
    if (vpn_cat_has($slug, $rigid)) {
        vpn_cat_add($set, array('rigid-boxes'));
    }

    $lid_base = array(
        'custom-flat-rigid-gift-box-with-ribbon', 'custom-perfume-box-with-insert',
        'custom-gift-box-with-ribbon-bow', 'custom-nested-kraft-gift-boxes-with-ribbon',
        'custom-rigid-square-gift-box-with-foil-logo', 'custom-watch-box-with-pillow-insert',
        'custom-rigid-gift-box', 'custom-double-wine-bottle-gift-box',
        'custom-fountain-pen-gift-box', 'custom-knife-set-packaging-box',
        'custom-skincare-gift-box-with-insert', 'custom-wine-bottle-packaging-box',
        'custom-shoe-packaging-box', 'custom-belt-packaging-box',
        'custom-t-shirt-packaging-box', 'custom-wallet-packaging-box',
        'custom-sports-shoe-packaging-box', 'luxury-wine-bottle-packaging-boxes',
    );
    if (in_array($slug, $lid_base, true)) {
        vpn_cat_add($set, array('lid-and-base-boxes', 'rigid-boxes'));
    }

    $folding = array(
        'custom-candle-jar-box-with-insert', 'custom-incense-packaging-box-with-window',
        'custom-essential-oil-packaging-box-with-insert', 'custom-pizza-packaging-box',
        'custom-cosmetic-tube-packaging-box-with-insert', 'custom-gift-box-with-shredded-paper-filler',
        'custom-perfume-display-box-with-sleeve', 'custom-medical-kit-packaging-box',
        'custom-mug-packaging-box-with-window', 'custom-pill-packaging-box',
        'custom-ampoule-packaging-box', 'custom-charging-cable-packaging-box',
        'custom-colored-pencil-packaging-box', 'custom-cosmetic-packaging-box',
        'custom-crayon-packaging-box', 'custom-phone-case-packaging-box',
        'custom-tablet-packaging-box', 'custom-vial-packaging-box',
        'custom-knee-support-packaging-box', 'custom-men-underwear-packaging-box',
        'custom-sports-underwear-packaging-box',
    );
    if (in_array($slug, $folding, true)) {
        vpn_cat_add($set, array('folding-carton-boxes'));
    }

    if (vpn_cat_has($slug, array(
        'printed-', 'incense-', 'essential-oil-', 'colored-pencil-', 'crayon-', 'stationery-',
        'pizza-', 'rigid-square-', 'mooncake-packaging-', 'paper-shopping-bag-',
        'perfume-display-',
    ))) {
        vpn_cat_add($set, array('custom-printed-paper-boxes'));
    }
    if (str_contains($slug, 'gift-box') && !vpn_cat_has($slug, array('pastry-', 'mooncake-', 'wine-', 'perfume-', 'skincare-'))) {
        vpn_cat_add($set, array('gift-paper-boxes', 'corporate-gift-packaging'));
    }
    if (vpn_cat_has($slug, array('matching-paper-bag', 'with-paper-bag', 'with-accessories', 'shredded-paper-filler'))) {
        vpn_cat_add($set, array('packaging-accessories'));
    }

    $overrides = array(
        'custom-apparel-drawer-box-with-ribbon-pull' => array('drawer-boxes', 'fashion-sportswear-packaging', 'rigid-boxes'),
        'custom-bottle-carrier-box-with-handle' => array('custom-paper-boxes', 'premium-food-beverage-packaging'),
        'custom-essential-oil-packaging-box-with-insert' => array('beauty-skincare-packaging', 'cosmetic-paper-boxes', 'custom-printed-paper-boxes', 'folding-carton-boxes'),
        'custom-incense-packaging-box-with-window' => array('custom-printed-paper-boxes', 'folding-carton-boxes', 'home-lifestyle-packaging'),
        'custom-kraft-paper-bag-for-supplement-packaging' => array('paper-bags-with-logo', 'supplement-packaging-boxes'),
        'custom-mooncake-handbag-gift-box-with-insert' => array('chocolate-gift-boxes', 'food-paper-boxes', 'gift-paper-boxes', 'premium-food-beverage-packaging', 'rigid-boxes'),
        'custom-pastry-sleeve-box-with-insert' => array('bakery-packaging-boxes', 'drawer-boxes', 'food-paper-boxes', 'premium-food-beverage-packaging'),
        'custom-round-jar-drawer-box' => array('beauty-skincare-packaging', 'cosmetic-paper-boxes', 'drawer-boxes', 'rigid-boxes', 'skincare-packaging-boxes'),
        'custom-tea-gift-box-with-window' => array('food-paper-boxes', 'gift-paper-boxes', 'premium-food-beverage-packaging', 'rigid-boxes'),
        'custom-watch-box-with-drawer' => array('custom-printed-paper-boxes', 'drawer-boxes', 'jewelry-paper-boxes', 'rigid-boxes'),
        'custom-watch-box-with-pillow-insert' => array('jewelry-paper-boxes', 'lid-and-base-boxes', 'rigid-boxes'),
        'luxury-wine-bottle-packaging-boxes' => array('gift-paper-boxes', 'lid-and-base-boxes', 'rigid-boxes', 'wine-premium-drink-packaging'),
        'premium-pickleball-set-rigid-paper-box' => array('rigid-boxes', 'sports-packaging-boxes'),
    );
    if (isset($overrides[$slug])) {
        return $overrides[$slug];
    }

    $structures = array('rigid-boxes', 'folding-carton-boxes', 'magnetic-closure-boxes', 'drawer-boxes', 'lid-and-base-boxes', 'paper-tube-packaging', 'corrugated-mailer-boxes');
    if (!array_intersect(array_keys($set), $structures) && !vpn_cat_has($slug, array('paper-bag', 'shopping-bag', 'gift-bag'))) {
        vpn_cat_add($set, array('custom-paper-boxes'));
    }

    return array_keys($set);
}

$products = get_posts(array(
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
));

$assignments = array();
foreach ($products as $product) {
    $slugs = vpn_reviewed_product_categories($product->post_name);
    if (!$slugs) {
        fwrite(STDERR, "No categories for {$product->post_name}\n");
        exit(1);
    }
    $assignments[$product->ID] = $slugs;
    echo $product->post_name . ': ' . implode(', ', $slugs) . PHP_EOL;
}

if (!$apply) {
    echo "Dry run only. Re-run with --apply to update categories.\n";
    exit(0);
}

global $wpdb;
$term_ids_by_slug = array();
foreach (array_unique(array_merge(...array_values($assignments))) as $slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term || is_wp_error($term)) {
        fwrite(STDERR, "Missing category {$slug}; migration aborted before making changes.\n");
        exit(1);
    }
    $term_ids_by_slug[$slug] = (int) $term->term_id;
}

$backup_table = $wpdb->prefix . 'product_cat_backup_20260610';
$wpdb->query("CREATE TABLE IF NOT EXISTS {$backup_table} AS
    SELECT tr.object_id, tr.term_taxonomy_id, tr.term_order
    FROM {$wpdb->term_relationships} tr
    INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
    INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
    WHERE tt.taxonomy = 'product_cat' AND p.post_type = 'product'");

foreach ($assignments as $product_id => $slugs) {
    $term_ids = array_map(static fn($slug) => $term_ids_by_slug[$slug], $slugs);
    $result = wp_set_object_terms((int) $product_id, $term_ids, 'product_cat', false);
    if (is_wp_error($result)) {
        fwrite(STDERR, $result->get_error_message() . PHP_EOL);
        exit(1);
    }
}

echo "Category migration completed. Backup table: {$backup_table}\n";
