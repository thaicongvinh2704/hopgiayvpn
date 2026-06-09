<?php
/**
 * Admin-only one-time product category migration helper.
 */

defined('ABSPATH') || exit;

function custom_box_category_migration_targets() {
    return array(
        'custom-paper-boxes'          => 'Custom Paper Boxes',
        'custom-printed-paper-boxes'  => 'Custom Printed Paper Boxes',
        'rigid-boxes'                 => 'Rigid Boxes',
        'folding-carton-boxes'        => 'Folding Carton Boxes',
        'magnetic-closure-boxes'      => 'Magnetic Closure Boxes',
        'drawer-boxes'                => 'Drawer Boxes',
        'lid-and-base-boxes'          => 'Lid and Base Boxes',
        'paper-tube-packaging'        => 'Paper Tube Packaging',
        'corrugated-mailer-boxes'     => 'Corrugated Mailer Boxes',
        'cosmetic-paper-boxes'        => 'Cosmetic Paper Boxes',
        'perfume-packaging-boxes'     => 'Perfume Packaging Boxes',
        'skincare-packaging-boxes'    => 'Skincare Packaging Boxes',
        'jewelry-paper-boxes'         => 'Jewelry Paper Boxes',
        'gift-paper-boxes'            => 'Gift Paper Boxes',
        'chocolate-gift-boxes'        => 'Chocolate Gift Boxes',
        'food-paper-boxes'            => 'Food Paper Boxes',
        'bakery-packaging-boxes'      => 'Bakery Packaging Boxes',
        'candle-packaging-boxes'      => 'Candle Packaging Boxes',
        'paper-bags-with-logo'        => 'Paper Bags with Logo',
        'packaging-accessories'       => 'Packaging Accessories',
        'pharmaceutical-packaging-boxes'        => 'Pharmaceutical Packaging Boxes',
        'supplement-packaging-boxes'            => 'Supplement Packaging Boxes',
        'beauty-skincare-packaging'             => 'Beauty and Skincare Packaging',
        'premium-food-beverage-packaging'       => 'Premium Food and Beverage Packaging',
        'electronics-accessories-packaging'     => 'Electronics Accessories Packaging',
        'fashion-sportswear-packaging'          => 'Fashion and Sportswear Packaging',
        'sports-packaging-boxes'                => 'Sports Packaging Boxes',
        'wine-premium-drink-packaging'          => 'Wine and Premium Drink Packaging',
        'corporate-gift-packaging'              => 'Corporate Gift Packaging',
        'home-lifestyle-packaging'              => 'Home and Lifestyle Packaging',
        'back-to-school-stationery-packaging'   => 'Back-to-School and Stationery Packaging',
    );
}

function custom_box_category_migration_old_slug_map() {
    return array(
        'cosmetic-packaging-boxes'               => 'beauty-skincare-packaging',
        'electronics-packaging-boxes'            => 'electronics-accessories-packaging',
        'food-packaging-boxes'                   => 'premium-food-beverage-packaging',
        'gift-packaging-boxes'                   => 'corporate-gift-packaging',
        'health-supplement-packaging-boxes'      => 'supplement-packaging-boxes',
        'healthcare-packaging-boxes'             => 'pharmaceutical-packaging-boxes',
        'paper-bags'                             => 'beauty-skincare-packaging',
        'retail-packaging-boxes'                 => 'home-lifestyle-packaging',
        'stationery-packaging-boxes'             => 'back-to-school-stationery-packaging',
        'wine-packaging-boxes'                   => 'wine-premium-drink-packaging',
        'custom-paper-tube-packaging-boxes'      => 'paper-tube-packaging',
        'luxury-rigid-gift-boxes'                => 'rigid-boxes',
        'custom-cake-packaging-boxes'            => 'bakery-packaging-boxes',
        'luxury-drawer-gift-boxes'               => 'drawer-boxes',
        'luxury-wine-bottle-packaging-boxes'     => 'gift-paper-boxes',
        'candle-jar-packaging-boxes'             => 'candle-packaging-boxes',
        'custom-chocolate-gift-boxes'            => 'chocolate-gift-boxes',
        'custom-chocolate-display-boxes'         => 'chocolate-gift-boxes',
        'rigid-sliding-drawer-boxes'             => 'drawer-boxes',
        'pink-ribbon-gift-boxes'                 => 'gift-paper-boxes',
        'wine-bottle-gift-boxes'                 => 'gift-paper-boxes',
        'pizza-packaging-boxes'                  => 'food-paper-boxes',
        'cosmetic-mailer-packaging-boxes'        => 'cosmetic-paper-boxes',
        'luxury-perfume-packaging-boxes'         => 'perfume-packaging-boxes',
        'premium-ribbon-gift-boxes'              => 'gift-paper-boxes',
        'kraft-round-gift-boxes'                 => 'gift-paper-boxes',
        'custom-soap-packaging-boxes'            => 'skincare-packaging-boxes',
        'dessert-gift-packaging-boxes'           => 'bakery-packaging-boxes',
        'bakery-food-packaging-boxes'            => 'bakery-packaging-boxes',
        'dessert-packaging-boxes-with-inserts'   => 'bakery-packaging-boxes',
        'mooncake-chocolate-gift-boxes'          => 'chocolate-gift-boxes',
        'printed-paper-shopping-bags'            => 'paper-bags-with-logo',
        'luxury-retail-paper-bags'               => 'paper-bags-with-logo',
        'mooncake-gift-packaging-boxes'          => 'gift-paper-boxes',
        'custom-red-paper-bags'                  => 'paper-bags-with-logo',
        'luxury-teal-paper-bags'                 => 'paper-bags-with-logo',
        'cosmetic-set-packaging-boxes'           => 'cosmetic-paper-boxes',
        'luxury-watch-packaging-boxes'           => 'jewelry-paper-boxes',
        'watch-packaging-boxes'                  => 'jewelry-paper-boxes',
        'custom-packaging-boxes'                 => 'custom-paper-boxes',
        'uncategorized'                          => 'custom-paper-boxes',
    );
}

function custom_box_category_migration_keyword_map() {
    return array(
        'pharmaceutical-packaging-boxes'        => array('pharmaceutical', 'medicine', 'medical', 'pill', 'tablet', 'vial'),
        'supplement-packaging-boxes'            => array('supplement', 'vitamin', 'wellness', 'collagen', 'probiotic'),
        'beauty-skincare-packaging'             => array('beauty', 'skincare', 'cosmetic', 'ampoule', 'serum', 'makeup', 'perfume'),
        'premium-food-beverage-packaging'       => array('food', 'beverage', 'coffee', 'tea', 'chocolate', 'mug', 'paper tube', 'pet food'),
        'electronics-accessories-packaging'     => array('electronics', 'phone', 'charging cable', 'cable', 'adapter'),
        'fashion-sportswear-packaging'          => array('fashion', 'sportswear', 'apparel', 'clothing', 'shoe'),
        'sports-packaging-boxes'                => array('sports equipment', 'fitness product', 'athletic accessory', 'gym accessory'),
        'wine-premium-drink-packaging'          => array('wine', 'drink bottle', 'beverage bottle'),
        'corporate-gift-packaging'              => array('corporate', 'gift', 'rigid gift', 'magnetic gift', 'drawer gift'),
        'home-lifestyle-packaging'              => array('home', 'lifestyle', 'dinnerware', 'thermos', 'knife', 'homeware'),
        'back-to-school-stationery-packaging'   => array('stationery', 'school', 'pencil', 'crayon', 'pen'),
        'paper-tube-packaging'     => array('tube', 'cylindrical'),
        'corrugated-mailer-boxes'  => array('corrugated', 'mailer', 'ecommerce', 'pet food'),
        'magnetic-closure-boxes'   => array('magnetic'),
        'drawer-boxes'             => array('drawer', 'sliding'),
        'lid-and-base-boxes'       => array('lid and base', 'lid-base'),
        'folding-carton-boxes'     => array('folding carton', 'carton'),
        'cosmetic-paper-boxes'     => array('cosmetic', 'essential oil', 'soap'),
        'skincare-packaging-boxes' => array('skincare', 'skin care'),
        'perfume-packaging-boxes'  => array('perfume'),
        'jewelry-paper-boxes'      => array('jewelry', 'watch'),
        'chocolate-gift-boxes'     => array('chocolate'),
        'candle-packaging-boxes'   => array('candle'),
        'paper-bags-with-logo'     => array('bag', 'handbag'),
        'bakery-packaging-boxes'   => array('bakery', 'pastry', 'cake', 'dessert'),
        'food-paper-boxes'         => array('food', 'pizza', 'tea', 'coffee'),
        'gift-paper-boxes'         => array('gift', 'mooncake', 'wine', 'ribbon'),
        'rigid-boxes'              => array('rigid'),
    );
}

function custom_box_category_migration_explicit_product_map() {
    return array(
        'custom-pharmaceutical-medicine-packaging-boxes' => array('custom-paper-boxes'),
        'custom-medical-kit-packaging-box'               => array('pharmaceutical-packaging-boxes'),
        'custom-pill-packaging-box'                      => array('pharmaceutical-packaging-boxes'),
        'custom-vial-packaging-box'                      => array('pharmaceutical-packaging-boxes'),
        'custom-ampoule-packaging-box'                   => array('pharmaceutical-packaging-boxes', 'supplement-packaging-boxes'),

        'custom-supplement-vitamin-packaging-boxes'      => array('custom-paper-boxes'),
        'custom-supplement-drawer-packaging-box'         => array('supplement-packaging-boxes'),
        'custom-kraft-paper-bag-for-supplement-packaging' => array('supplement-packaging-boxes'),
        'custom-tablet-packaging-box'                    => array('supplement-packaging-boxes'),

        'custom-cosmetic-skincare-packaging-boxes'       => array('custom-paper-boxes'),
        'custom-skincare-gift-box-with-insert'           => array('beauty-skincare-packaging'),
        'custom-pink-cosmetic-gift-box-with-satin-lining' => array('beauty-skincare-packaging'),
        'custom-cosmetic-packaging-box'                  => array('beauty-skincare-packaging'),
        'custom-cosmetic-paper-bag'                      => array('beauty-skincare-packaging'),

        'premium-tea-coffee-chocolate-packaging-boxes'   => array('premium-food-beverage-packaging'),
        'custom-paper-tube-food-packaging-box'           => array('premium-food-beverage-packaging'),
        'custom-printed-corrugated-pet-food-box'         => array('premium-food-beverage-packaging'),
        'custom-paper-tube-packaging-box'                => array('premium-food-beverage-packaging'),
        'custom-mug-packaging-box-with-window'           => array('premium-food-beverage-packaging', 'home-lifestyle-packaging'),

        'custom-phone-accessories-packaging-boxes'       => array('custom-paper-boxes'),
        'custom-phone-packaging-box-with-paper-bag'      => array('electronics-accessories-packaging'),
        'custom-phone-packaging-box'                     => array('electronics-accessories-packaging'),
        'custom-charging-cable-packaging-box'            => array('electronics-accessories-packaging'),
        'custom-phone-case-packaging-box'                => array('electronics-accessories-packaging'),
        'custom-corporate-gift-set-packaging-boxes'      => array('custom-paper-boxes'),

        'custom-wine-premium-beverage-packaging-boxes'   => array('custom-paper-boxes'),
        'custom-wine-bottle-packaging-box'               => array('wine-premium-drink-packaging'),
        'custom-wine-bottle-gift-box-with-paper-bag'     => array('wine-premium-drink-packaging'),
        'custom-double-wine-bottle-gift-box'             => array('premium-food-beverage-packaging', 'wine-premium-drink-packaging'),
        'custom-single-wine-bottle-gift-box'             => array('wine-premium-drink-packaging'),

        'custom-luxury-gift-box-with-paper-bag'          => array('corporate-gift-packaging'),
        'custom-magnetic-gift-box'                       => array('corporate-gift-packaging'),
        'custom-rigid-gift-box'                          => array('corporate-gift-packaging'),
        'custom-drawer-gift-box'                         => array('corporate-gift-packaging'),
        'custom-teal-rigid-gift-box'                     => array('custom-paper-boxes'),

        'custom-home-lifestyle-product-packaging-boxes'  => array('custom-paper-boxes'),
        'custom-thermos-bottle-packaging-box'            => array('home-lifestyle-packaging'),
        'custom-dinnerware-packaging-box'                => array('home-lifestyle-packaging'),
        'custom-knife-set-packaging-box'                 => array('home-lifestyle-packaging'),

        'custom-stationery-packaging-box'                => array('back-to-school-stationery-packaging'),
        'custom-stationery-school-supplies-packaging-boxes' => array('custom-paper-boxes'),
        'custom-colored-pencil-packaging-box'            => array('back-to-school-stationery-packaging'),
        'custom-crayon-packaging-box'                    => array('back-to-school-stationery-packaging'),
        'custom-fountain-pen-gift-box'                   => array('back-to-school-stationery-packaging'),

        'custom-red-paper-shopping-bag'                  => array('paper-bags-with-logo'),
    );
}

function custom_box_category_migration_can_run() {
    return current_user_can('manage_woocommerce') || current_user_can('manage_options');
}

function custom_box_category_migration_parent_term_id() {
    if (function_exists('custom_box_get_packaging_parent_category')) {
        $parent = custom_box_get_packaging_parent_category();
    } else {
        $parent = get_term_by('slug', 'custom-packaging-boxes', 'product_cat');
    }

    return ($parent && !is_wp_error($parent)) ? (int) $parent->term_id : 0;
}

function custom_box_category_migration_admin_menu() {
    add_management_page(
        'Product Category Migration',
        'Product Category Migration',
        'manage_options',
        'custom-box-category-migration',
        'custom_box_category_migration_page'
    );
}
add_action('admin_menu', 'custom_box_category_migration_admin_menu');

function custom_box_category_migration_get_or_create_term($slug, $name, $create, $parent_id = 0) {
    $term = get_term_by('slug', $slug, 'product_cat');

    if ($term && !is_wp_error($term)) {
        if ($parent_id > 0 && (int) $term->term_id !== $parent_id && (int) $term->parent !== $parent_id) {
            wp_update_term((int) $term->term_id, 'product_cat', array(
                'parent' => $parent_id,
            ));
        }

        return (int) $term->term_id;
    }

    if (!$create) {
        return 0;
    }

    $args = array('slug' => $slug);

    if ($parent_id > 0) {
        $args['parent'] = $parent_id;
    }

    $created = wp_insert_term($name, 'product_cat', $args);

    if (is_wp_error($created) || empty($created['term_id'])) {
        return 0;
    }

    return (int) $created['term_id'];
}

function custom_box_category_migration_target_for_product($product_id) {
    $target_slugs = custom_box_category_migration_target_slugs_for_product($product_id);

    return $target_slugs[0] ?? 'custom-paper-boxes';
}

function custom_box_category_migration_target_slugs_for_product($product_id) {
    $target_slugs = array_keys(custom_box_category_migration_targets());
    $explicit_map = custom_box_category_migration_explicit_product_map();
    $old_slug_map = custom_box_category_migration_old_slug_map();
    $post_slug = get_post_field('post_name', $product_id);

    if (!empty($explicit_map[$post_slug])) {
        return array_values(array_unique(array_intersect($explicit_map[$post_slug], $target_slugs)));
    }

    $terms = get_the_terms($product_id, 'product_cat');

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            if (!empty($old_slug_map[$term->slug])) {
                return array($old_slug_map[$term->slug]);
            }

            if (in_array($term->slug, $target_slugs, true)) {
                return array($term->slug);
            }
        }
    }

    $haystack = strtolower(get_the_title($product_id) . ' ' . get_post_field('post_name', $product_id));

    foreach (custom_box_category_migration_keyword_map() as $target_slug => $keywords) {
        foreach ($keywords as $keyword) {
            if (false !== strpos($haystack, $keyword)) {
                return array($target_slug);
            }
        }
    }

    return array('custom-paper-boxes');
}

function custom_box_category_migration_products() {
    return get_posts(array(
        'post_type'      => 'product',
        'post_status'    => array('publish', 'draft', 'pending', 'private'),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ));
}

function custom_box_category_migration_apply_products_to_targets() {
    $targets = custom_box_category_migration_targets();
    $target_ids = array();
    $parent_id = custom_box_category_migration_parent_term_id();

    foreach ($targets as $slug => $name) {
        $target_ids[$slug] = custom_box_category_migration_get_or_create_term($slug, $name, true, $parent_id);
    }

    $updated = 0;

    foreach (custom_box_category_migration_products() as $product_id) {
        $target_slugs = custom_box_category_migration_target_slugs_for_product($product_id);
        $term_ids = array();

        foreach ($target_slugs as $target_slug) {
            if (!empty($target_ids[$target_slug])) {
                $term_ids[] = (int) $target_ids[$target_slug];
            }
        }

        if (!$term_ids) {
            continue;
        }

        wp_set_object_terms($product_id, array_values(array_unique($term_ids)), 'product_cat', false);
        $updated++;
    }

    flush_rewrite_rules(false);

    return $updated;
}

function custom_box_category_migration_sync_hierarchy() {
    $parent_id = custom_box_category_migration_parent_term_id();

    if (!$parent_id) {
        return new WP_Error('missing_parent', __('Missing Custom Packaging Boxes parent category.', 'custom-box-theme'));
    }

    $targets = custom_box_category_migration_targets();
    $active_slugs = function_exists('custom_box_get_packaging_category_slugs')
        ? custom_box_get_packaging_category_slugs()
        : array_keys($targets);
    $active_ids = array();
    $attached = 0;
    $detached = 0;
    $missing = array();

    foreach ($active_slugs as $slug) {
        $term = get_term_by('slug', $slug, 'product_cat');

        if (!$term || is_wp_error($term)) {
            $term_id = custom_box_category_migration_get_or_create_term(
                $slug,
                isset($targets[$slug]) ? $targets[$slug] : ucwords(str_replace('-', ' ', $slug)),
                true,
                $parent_id
            );

            if (!$term_id) {
                $missing[] = $slug;
                continue;
            }

            $active_ids[] = (int) $term_id;
            $attached++;
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

        if (!is_wp_error($updated)) {
            $attached++;
        }
    }

    $old_children = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => $parent_id,
        'hide_empty' => false,
    ));

    if (is_wp_error($old_children)) {
        return $old_children;
    }

    foreach ($old_children as $child) {
        $child_id = (int) $child->term_id;

        if (in_array($child_id, $active_ids, true)) {
            continue;
        }

        $updated = wp_update_term($child_id, 'product_cat', array(
            'parent' => 0,
        ));

        if (!is_wp_error($updated)) {
            $detached++;
        }
    }

    flush_rewrite_rules(false);

    return array(
        'attached' => $attached,
        'detached' => $detached,
        'missing'  => $missing,
    );
}

function custom_box_category_migration_apply() {
    check_admin_referer('custom_box_category_migration_apply');

    if (!custom_box_category_migration_can_run()) {
        wp_die(esc_html__('You do not have permission to run this migration.', 'custom-box-theme'));
    }

    $updated = custom_box_category_migration_apply_products_to_targets();

    wp_safe_redirect(add_query_arg(array(
        'page'    => 'custom-box-category-migration',
        'updated' => $updated,
    ), admin_url('tools.php')));
    exit;
}
add_action('admin_post_custom_box_category_migration_apply', 'custom_box_category_migration_apply');

function custom_box_category_migration_sync_hierarchy_action() {
    check_admin_referer('custom_box_category_migration_sync_hierarchy');

    if (!custom_box_category_migration_can_run()) {
        wp_die(esc_html__('You do not have permission to run this sync.', 'custom-box-theme'));
    }

    $result = custom_box_category_migration_sync_hierarchy();
    $args = array('page' => 'custom-box-category-migration');

    if (is_wp_error($result)) {
        $args['hierarchy_error'] = rawurlencode($result->get_error_message());
    } else {
        $args['hierarchy_attached'] = (int) $result['attached'];
        $args['hierarchy_detached'] = (int) $result['detached'];
        $args['hierarchy_missing'] = count($result['missing']);
    }

    wp_safe_redirect(add_query_arg($args, admin_url('tools.php')));
    exit;
}
add_action('admin_post_custom_box_category_migration_sync_hierarchy', 'custom_box_category_migration_sync_hierarchy_action');

function custom_box_category_migration_page() {
    if (!custom_box_category_migration_can_run()) {
        wp_die(esc_html__('You do not have permission to view this page.', 'custom-box-theme'));
    }

    $targets = custom_box_category_migration_targets();
    $products = custom_box_category_migration_products();
    ?>
    <div class="wrap">
        <h1>Product Category Migration</h1>

        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html(sprintf('Updated %d products.', absint($_GET['updated']))); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['hierarchy_attached'], $_GET['hierarchy_detached'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html(sprintf(
                    'Synced category hierarchy. Attached %d active categories and detached %d inactive old children.',
                    absint($_GET['hierarchy_attached']),
                    absint($_GET['hierarchy_detached'])
                )); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['hierarchy_error'])) : ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html(rawurldecode(wp_unslash($_GET['hierarchy_error']))); ?></p>
            </div>
        <?php endif; ?>

        <p>This tool moves all WooCommerce products into the 20 homepage product categories. It does not delete old categories.</p>

        <h2>Category Hierarchy Sync</h2>
        <p>This sync only updates the children shown under Custom Packaging Boxes. It attaches the active categories and detaches inactive old child categories without deleting terms or moving products.</p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 16px 0 24px;">
            <?php wp_nonce_field('custom_box_category_migration_sync_hierarchy'); ?>
            <input type="hidden" name="action" value="custom_box_category_migration_sync_hierarchy">
            <?php submit_button('Sync Packaging Category Hierarchy', 'secondary', 'submit', false); ?>
        </form>

        <h2>Target Categories</h2>
        <ul>
            <?php foreach ($targets as $slug => $name) : ?>
                <?php $term = get_term_by('slug', $slug, 'product_cat'); ?>
                <li>
                    <code><?php echo esc_html($slug); ?></code>
                    - <?php echo esc_html($name); ?>
                    <?php echo $term && !is_wp_error($term) ? '<strong>exists</strong>' : '<em>will be created</em>'; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Preview</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Current Categories</th>
                    <th>New Category</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product_id) : ?>
                    <?php
                    $current_terms = get_the_terms($product_id, 'product_cat');
                    $current_names = array();

                    if (!empty($current_terms) && !is_wp_error($current_terms)) {
                        foreach ($current_terms as $term) {
                            $current_names[] = $term->name . ' (' . $term->slug . ')';
                        }
                    }

                    $target_slugs = custom_box_category_migration_target_slugs_for_product($product_id);
                    $target_names = array();

                    foreach ($target_slugs as $target_slug) {
                        $target_names[] = ($targets[$target_slug] ?? $target_slug) . ' (' . $target_slug . ')';
                    }
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url(get_edit_post_link($product_id)); ?>">
                                <?php echo esc_html(get_the_title($product_id)); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($current_names ? implode(', ', $current_names) : 'None'); ?></td>
                        <td>
                            <?php echo esc_html(implode(', ', $target_names)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 20px;">
            <?php wp_nonce_field('custom_box_category_migration_apply'); ?>
            <input type="hidden" name="action" value="custom_box_category_migration_apply">
            <?php submit_button('Apply Migration', 'primary', 'submit', false); ?>
        </form>
    </div>
    <?php
}
