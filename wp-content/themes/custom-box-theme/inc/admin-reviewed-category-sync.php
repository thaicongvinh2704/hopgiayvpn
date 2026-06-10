<?php
/**
 * Admin tool for applying the reviewed multi-category product assignments.
 */

defined('ABSPATH') || exit;

function custom_box_reviewed_category_sync_can_run() {
    return current_user_can('manage_woocommerce') || current_user_can('manage_options');
}

function custom_box_reviewed_category_add(array &$set, array $slugs): void {
    foreach ($slugs as $slug) {
        $set[$slug] = true;
    }
}

function custom_box_reviewed_category_has(string $value, array $needles): bool {
    foreach ($needles as $needle) {
        if (str_contains($value, $needle)) {
            return true;
        }
    }

    return false;
}

function custom_box_reviewed_categories_for_slug(string $slug): array {
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
        if (custom_box_reviewed_category_has($slug, $needles)) {
            custom_box_reviewed_category_add($set, array($category));
        }
    }

    if (custom_box_reviewed_category_has($slug, array('cosmetic', 'skincare', 'essential-oil', 'ampoule', 'perfume'))) {
        custom_box_reviewed_category_add($set, array('beauty-skincare-packaging'));
    }
    if (custom_box_reviewed_category_has($slug, array('bakery', 'pastry', 'cake'))) {
        custom_box_reviewed_category_add($set, array('bakery-packaging-boxes', 'food-paper-boxes', 'premium-food-beverage-packaging'));
    }
    if (custom_box_reviewed_category_has($slug, array('pizza', 'tea-', 'food-', 'pet-food', 'mug-', 'mooncake', 'chocolate'))) {
        custom_box_reviewed_category_add($set, array('food-paper-boxes', 'premium-food-beverage-packaging'));
    }
    if (custom_box_reviewed_category_has($slug, array('mooncake', 'chocolate'))) {
        custom_box_reviewed_category_add($set, array('chocolate-gift-boxes'));
    }
    if (str_contains($slug, 'wine')) {
        custom_box_reviewed_category_add($set, array('wine-premium-drink-packaging', 'gift-paper-boxes'));
    }

    $rigid = array(
        'rigid', 'drawer', 'magnetic', 'watch-box', 'wine-gift-box', 'tea-gift-box',
        'mooncake-gift-box', 'luxury-mooncake', 'perfume-gift-set', 'pastry-gift-box',
        'luxury-wine-bottle', 'luxury-gift-box', 'single-wine-bottle-gift-box',
        'wine-bottle-gift-box',
    );
    if (custom_box_reviewed_category_has($slug, $rigid)) {
        custom_box_reviewed_category_add($set, array('rigid-boxes'));
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
        custom_box_reviewed_category_add($set, array('lid-and-base-boxes', 'rigid-boxes'));
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
        custom_box_reviewed_category_add($set, array('folding-carton-boxes'));
    }

    if (custom_box_reviewed_category_has($slug, array(
        'printed-', 'incense-', 'essential-oil-', 'colored-pencil-', 'crayon-', 'stationery-',
        'pizza-', 'rigid-square-', 'mooncake-packaging-', 'paper-shopping-bag-', 'perfume-display-',
    ))) {
        custom_box_reviewed_category_add($set, array('custom-printed-paper-boxes'));
    }
    if (str_contains($slug, 'gift-box') && !custom_box_reviewed_category_has($slug, array('pastry-', 'mooncake-', 'wine-', 'perfume-', 'skincare-'))) {
        custom_box_reviewed_category_add($set, array('gift-paper-boxes', 'corporate-gift-packaging'));
    }
    if (custom_box_reviewed_category_has($slug, array('matching-paper-bag', 'with-paper-bag', 'with-accessories', 'shredded-paper-filler'))) {
        custom_box_reviewed_category_add($set, array('packaging-accessories'));
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

    $structures = array(
        'rigid-boxes', 'folding-carton-boxes', 'magnetic-closure-boxes', 'drawer-boxes',
        'lid-and-base-boxes', 'paper-tube-packaging', 'corrugated-mailer-boxes',
    );
    if (!array_intersect(array_keys($set), $structures) && !custom_box_reviewed_category_has($slug, array('paper-bag', 'shopping-bag', 'gift-bag'))) {
        custom_box_reviewed_category_add($set, array('custom-paper-boxes'));
    }

    return array_keys($set);
}

function custom_box_reviewed_category_sync_assignments(): array {
    $products = get_posts(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ));
    $assignments = array();

    foreach ($products as $product) {
        $assignments[$product->ID] = custom_box_reviewed_categories_for_slug($product->post_name);
    }

    return $assignments;
}

function custom_box_reviewed_category_sync_validate(array $assignments): array {
    $missing = array();
    $all_slugs = $assignments ? array_unique(array_merge(...array_values($assignments))) : array();

    foreach ($all_slugs as $slug) {
        $term = get_term_by('slug', $slug, 'product_cat');
        if (!$term || is_wp_error($term)) {
            $missing[] = $slug;
        }
    }

    return $missing;
}

function custom_box_reviewed_category_sync_current_slugs(int $product_id): array {
    $slugs = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));

    if (is_wp_error($slugs)) {
        return array();
    }

    sort($slugs);
    return $slugs;
}

function custom_box_reviewed_category_sync_apply(): array {
    global $wpdb;

    $assignments = custom_box_reviewed_category_sync_assignments();
    $missing = custom_box_reviewed_category_sync_validate($assignments);

    if ($missing) {
        return array(
            'error' => 'Missing categories: ' . implode(', ', $missing),
            'updated' => 0,
            'unchanged' => 0,
            'backup_table' => '',
        );
    }

    $term_ids = array();
    foreach (array_unique(array_merge(...array_values($assignments))) as $slug) {
        $term = get_term_by('slug', $slug, 'product_cat');
        $term_ids[$slug] = (int) $term->term_id;
    }

    $pending = array();
    $unchanged = 0;

    foreach ($assignments as $product_id => $slugs) {
        $target_slugs = $slugs;
        sort($target_slugs);

        if (custom_box_reviewed_category_sync_current_slugs((int) $product_id) === $target_slugs) {
            ++$unchanged;
        } else {
            $pending[$product_id] = $slugs;
        }
    }

    if (!$pending) {
        return array(
            'error' => '',
            'updated' => 0,
            'unchanged' => $unchanged,
            'backup_table' => '',
        );
    }

    $backup_suffix = gmdate('Ymd_His');
    $backup_table = $wpdb->prefix . 'product_cat_backup_' . $backup_suffix;
    $created = $wpdb->query(
        "CREATE TABLE {$backup_table} AS
         SELECT tr.object_id, tr.term_taxonomy_id, tr.term_order
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tt.taxonomy = 'product_cat' AND p.post_type = 'product'"
    );

    if (false === $created) {
        return array(
            'error' => 'Could not create the category backup table.',
            'updated' => 0,
            'unchanged' => 0,
            'backup_table' => '',
        );
    }

    $updated = 0;

    foreach ($pending as $product_id => $slugs) {
        $ids = array_map(static fn($slug) => $term_ids[$slug], $slugs);
        $result = wp_set_object_terms((int) $product_id, $ids, 'product_cat', false);

        if (is_wp_error($result)) {
            return array(
                'error' => $result->get_error_message(),
                'updated' => $updated,
                'unchanged' => $unchanged,
                'backup_table' => $backup_table,
            );
        }

        ++$updated;
    }

    return array(
        'error' => '',
        'updated' => $updated,
        'unchanged' => $unchanged,
        'backup_table' => $backup_table,
    );
}

function custom_box_reviewed_category_sync_admin_menu() {
    add_management_page(
        'Reviewed Category Sync',
        'Reviewed Category Sync',
        'manage_options',
        'custom-box-reviewed-category-sync',
        'custom_box_reviewed_category_sync_page'
    );
}
add_action('admin_menu', 'custom_box_reviewed_category_sync_admin_menu');

function custom_box_reviewed_category_sync_admin_post() {
    if (!custom_box_reviewed_category_sync_can_run()) {
        wp_die(esc_html__('You do not have permission to sync product categories.', 'custom-box-theme'));
    }

    check_admin_referer('custom_box_reviewed_category_sync_apply');
    $result = custom_box_reviewed_category_sync_apply();
    set_transient(
        'custom_box_reviewed_category_sync_result_' . get_current_user_id(),
        $result,
        10 * MINUTE_IN_SECONDS
    );

    wp_safe_redirect(add_query_arg(
        array(
            'page' => 'custom-box-reviewed-category-sync',
            'sync_done' => '1',
        ),
        admin_url('tools.php')
    ));
    exit;
}
add_action('admin_post_custom_box_reviewed_category_sync_apply', 'custom_box_reviewed_category_sync_admin_post');

function custom_box_reviewed_category_sync_page() {
    if (!custom_box_reviewed_category_sync_can_run()) {
        wp_die(esc_html__('You do not have permission to view this tool.', 'custom-box-theme'));
    }

    $assignments = custom_box_reviewed_category_sync_assignments();
    $missing = custom_box_reviewed_category_sync_validate($assignments);
    $changed = 0;
    $projected_counts = array();

    foreach ($assignments as $product_id => $slugs) {
        $target_slugs = $slugs;
        sort($target_slugs);
        if (custom_box_reviewed_category_sync_current_slugs((int) $product_id) !== $target_slugs) {
            ++$changed;
        }
        foreach ($slugs as $slug) {
            $projected_counts[$slug] = ($projected_counts[$slug] ?? 0) + 1;
        }
    }
    ksort($projected_counts);

    $result = get_transient('custom_box_reviewed_category_sync_result_' . get_current_user_id());
    if (isset($_GET['sync_done'])) {
        delete_transient('custom_box_reviewed_category_sync_result_' . get_current_user_id());
    } else {
        $result = false;
    }
    ?>
    <div class="wrap">
        <h1>Reviewed Product Category Sync</h1>
        <p>Applies the reviewed multi-category assignments by product slug. The tool validates every target category and creates a database backup before changing products.</p>

        <?php if (is_array($result)) : ?>
            <div class="notice <?php echo $result['error'] ? 'notice-error' : 'notice-success'; ?> is-dismissible">
                <p>
                    <?php if ($result['error']) : ?>
                        <strong>Sync failed:</strong> <?php echo esc_html($result['error']); ?>
                    <?php else : ?>
                        <strong>Sync completed.</strong>
                        Updated <?php echo esc_html((string) $result['updated']); ?> products;
                        <?php echo esc_html((string) $result['unchanged']); ?> already matched.
                        <?php if ($result['backup_table']) : ?>
                            Backup: <code><?php echo esc_html($result['backup_table']); ?></code>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="notice notice-info inline">
            <p>
                Published products: <strong><?php echo esc_html((string) count($assignments)); ?></strong>.
                Products needing changes: <strong><?php echo esc_html((string) $changed); ?></strong>.
                Missing categories: <strong><?php echo esc_html((string) count($missing)); ?></strong>.
            </p>
        </div>

        <?php if ($missing) : ?>
            <div class="notice notice-error inline">
                <p><strong>Apply is disabled.</strong> Missing categories: <code><?php echo esc_html(implode(', ', $missing)); ?></code></p>
            </div>
        <?php endif; ?>

        <h2>Projected Category Counts</h2>
        <table class="widefat striped" style="max-width: 760px;">
            <thead><tr><th>Category slug</th><th>Products</th></tr></thead>
            <tbody>
                <?php foreach ($projected_counts as $slug => $count) : ?>
                    <tr><td><code><?php echo esc_html($slug); ?></code></td><td><?php echo esc_html((string) $count); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Products Needing Changes</h2>
        <table class="widefat striped">
            <thead><tr><th>Product</th><th>Current categories</th><th>Reviewed categories</th></tr></thead>
            <tbody>
                <?php foreach ($assignments as $product_id => $slugs) : ?>
                    <?php
                    $current = custom_box_reviewed_category_sync_current_slugs((int) $product_id);
                    $target = $slugs;
                    sort($target);
                    if ($current === $target) {
                        continue;
                    }
                    ?>
                    <tr>
                        <td><a href="<?php echo esc_url(get_edit_post_link($product_id)); ?>"><?php echo esc_html(get_the_title($product_id)); ?></a></td>
                        <td><?php echo esc_html($current ? implode(', ', $current) : 'None'); ?></td>
                        <td><?php echo esc_html(implode(', ', $target)); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (0 === $changed) : ?>
                    <tr><td colspan="3">All published products already match the reviewed assignments.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 20px;">
            <?php wp_nonce_field('custom_box_reviewed_category_sync_apply'); ?>
            <input type="hidden" name="action" value="custom_box_reviewed_category_sync_apply">
            <?php submit_button('Apply Reviewed Category Sync', 'primary', 'submit', false, $missing ? array('disabled' => 'disabled') : array()); ?>
        </form>
    </div>
    <?php
}
