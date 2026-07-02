<?php
/**
 * Admin-only one-time product category migration helper.
 */

defined('ABSPATH') || exit;

function custom_box_category_migration_targets() {
    return array(
        'custom-packaging-boxes'       => 'Custom Packaging Boxes',
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
    return 0;
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

    if (function_exists('custom_box_sync_curated_category_product_assignments')) {
        custom_box_sync_curated_category_product_assignments();
    }

    flush_rewrite_rules(false);

    return $updated;
}

function custom_box_category_migration_sync_hierarchy() {
    $parent_id = custom_box_category_migration_parent_term_id();

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
                0
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

        if (0 === (int) $term->parent) {
            continue;
        }

        $updated = wp_update_term($term_id, 'product_cat', array(
            'parent' => 0,
        ));

        if (!is_wp_error($updated)) {
            $attached++;
        }
    }

    $legacy_parent = get_term_by('slug', 'custom-packaging-boxes', 'product_cat');
    $old_children = $legacy_parent && !is_wp_error($legacy_parent) ? get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => (int) $legacy_parent->term_id,
        'hide_empty' => false,
    )) : array();

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

        <p>This tool moves WooCommerce products into the final SEO product category set. It does not delete old categories.</p>

        <h2>Category Hierarchy Sync</h2>
        <p>This sync keeps product category URLs flat and prevents Custom Packaging Boxes from acting as an all-categories parent. It creates missing active terms and detaches old child category relationships without deleting terms or moving products.</p>
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

function custom_box_curated_category_product_assignments() {
    return array(
        'cosmetic-paper-boxes' => array(
            'include' => array(
                'custom-ampoule-packaging-box',
                'custom-cosmetic-drawer-box-with-insert',
                'custom-cosmetic-packaging-box',
                'custom-cosmetic-paper-bag',
                'custom-essential-oil-packaging-box-with-insert',
                'custom-perfume-box-with-insert',
                'custom-perfume-display-box-with-sleeve',
                'custom-perfume-gift-set-box-with-insert',
                'custom-round-jar-drawer-box',
                'custom-skincare-gift-box-with-insert',
                'custom-skincare-jar-packaging-box-with-insert',
            ),
            'exclude' => array(
                'custom-cosmetic-tube-packaging-box-with-insert',
            ),
        ),
        'magnetic-closure-boxes' => array(
            'include' => array(
                'custom-luxury-gift-box-with-paper-bag',
                'custom-magnetic-closure-gift-box',
                'custom-magnetic-gift-box',
                'custom-magnetic-gift-box-with-insert-tray',
                'custom-rigid-gift-box',
            ),
            'exclude' => array(),
        ),
        'rigid-boxes' => array(
            'include' => explode(',', 'custom-double-wine-bottle-gift-box,custom-flat-rigid-gift-box-with-ribbon,custom-fountain-pen-gift-box,custom-gift-box-with-ribbon-bow,custom-luxury-gift-box-with-paper-bag,custom-luxury-watch-box-with-drawer,custom-magnetic-closure-gift-box,custom-magnetic-gift-box,custom-magnetic-gift-box-with-insert-tray,custom-rigid-gift-box,custom-rigid-gift-box-with-matching-paper-bag,custom-rigid-gift-box-with-ribbon-closure,custom-rigid-square-gift-box-with-foil-logo,custom-single-wine-bottle-gift-box,custom-watch-box-with-drawer,custom-watch-box-with-pillow-insert,custom-wine-bottle-packaging-box,luxury-wine-bottle-packaging-boxes,premium-pickleball-set-rigid-paper-box'),
            'exclude' => array(),
        ),
        'lid-and-base-boxes' => array(
            'include' => explode(',', 'custom-belt-packaging-box,custom-double-wine-bottle-gift-box,custom-flat-rigid-gift-box-with-ribbon,custom-fountain-pen-gift-box,custom-gift-box-with-ribbon-bow,custom-knife-set-packaging-box,custom-nested-kraft-gift-boxes-with-ribbon,custom-perfume-box-with-insert,custom-rigid-gift-box,custom-rigid-square-gift-box-with-foil-logo,custom-shoe-packaging-box,custom-skincare-gift-box-with-insert,custom-sports-shoe-packaging-box,custom-t-shirt-packaging-box,custom-wallet-packaging-box,custom-watch-box-with-pillow-insert,custom-wine-bottle-packaging-box,luxury-wine-bottle-packaging-boxes'),
            'exclude' => array(),
        ),
        'drawer-boxes' => array(
            'include' => explode(',', 'custom-apparel-drawer-box-with-ribbon-pull,custom-cosmetic-drawer-box-with-insert,custom-drawer-gift-box,custom-kraft-pastry-drawer-box-with-insert,custom-luxury-watch-box-with-drawer,custom-mooncake-cabinet-gift-box-with-drawers,custom-pastry-display-box-with-drawer,custom-pastry-sleeve-box-with-insert,custom-round-jar-drawer-box,custom-supplement-drawer-packaging-box,custom-wallet-drawer-box,custom-watch-box-with-drawer,custom-wine-drawer-box-with-insert'),
            'exclude' => array(),
        ),
        'folding-carton-boxes' => array(
            'include' => explode(',', 'custom-ampoule-packaging-box,custom-candle-jar-box-with-insert,custom-charging-cable-packaging-box,custom-colored-pencil-packaging-box,custom-cosmetic-packaging-box,custom-cosmetic-tube-packaging-box-with-insert,custom-crayon-packaging-box,custom-essential-oil-packaging-box-with-insert,custom-gift-box-with-shredded-paper-filler,custom-incense-packaging-box-with-window,custom-knee-support-packaging-box,custom-medical-kit-packaging-box,custom-men-underwear-packaging-box,custom-mug-packaging-box-with-window,custom-perfume-display-box-with-sleeve,custom-phone-case-packaging-box,custom-pill-packaging-box,custom-pizza-packaging-box,custom-sports-underwear-packaging-box,custom-tablet-packaging-box,custom-vial-packaging-box'),
            'exclude' => array(),
        ),
        'gift-paper-boxes' => array(
            'include' => explode(',', 'custom-double-wine-bottle-gift-box,custom-drawer-gift-box,custom-flat-rigid-gift-box-with-ribbon,custom-fountain-pen-gift-box,custom-gift-box-with-ribbon-bow,custom-gift-box-with-shredded-paper-filler,custom-luxury-gift-box-with-paper-bag,custom-luxury-wine-bottle-packaging-box,custom-magnetic-closure-gift-box,custom-magnetic-gift-box,custom-magnetic-gift-box-with-insert-tray,custom-mooncake-handbag-gift-box-with-insert,custom-nested-kraft-gift-boxes-with-ribbon,custom-rigid-gift-box,custom-rigid-gift-box-with-matching-paper-bag,custom-rigid-gift-box-with-ribbon-closure,custom-rigid-square-gift-box-with-foil-logo,custom-single-wine-bottle-gift-box,custom-tea-gift-box-with-window,custom-wine-bottle-gift-box-with-paper-bag,custom-wine-bottle-packaging-box,custom-wine-drawer-box-with-insert,custom-wine-gift-box-with-accessories,luxury-wine-bottle-packaging-boxes'),
            'exclude' => array(),
        ),
        'food-paper-boxes' => array(
            'include' => explode(',', 'custom-kraft-bakery-box-with-window,custom-kraft-pastry-drawer-box-with-insert,custom-luxury-mooncake-gift-box-with-insert,custom-mooncake-cabinet-gift-box-with-drawers,custom-mooncake-gift-box-set-with-paper-bag,custom-mooncake-handbag-gift-box-with-insert,custom-mooncake-packaging-box,custom-mug-packaging-box-with-window,custom-paper-egg-packaging-boxes,custom-paper-tube-food-packaging-box,custom-pastry-box-with-clear-window,custom-pastry-display-box-with-drawer,custom-pastry-gift-box-with-insert,custom-pastry-sleeve-box-with-insert,custom-pizza-packaging-box,custom-printed-corrugated-pet-food-box,custom-tea-gift-box-with-window'),
            'exclude' => array(),
        ),
        'bakery-packaging-boxes' => array(
            'include' => explode(',', 'custom-kraft-bakery-box-with-window,custom-kraft-pastry-drawer-box-with-insert,custom-luxury-mooncake-gift-box-with-insert,custom-mooncake-cabinet-gift-box-with-drawers,custom-mooncake-gift-box-set-with-paper-bag,custom-mooncake-packaging-box,custom-pastry-box-with-clear-window,custom-pastry-display-box-with-drawer,custom-pastry-gift-box-with-insert,custom-pastry-sleeve-box-with-insert'),
            'exclude' => array(),
        ),
        'custom-printed-paper-boxes' => array(
            'include' => explode(',', 'custom-colored-pencil-packaging-box,custom-crayon-packaging-box,custom-essential-oil-packaging-box-with-insert,custom-incense-packaging-box-with-window,custom-mooncake-packaging-box,custom-paper-shopping-bag-with-handles,custom-perfume-display-box-with-sleeve,custom-pizza-packaging-box,custom-printed-corrugated-ecommerce-box,custom-printed-corrugated-pet-food-box,custom-rigid-square-gift-box-with-foil-logo,custom-stationery-packaging-box,custom-watch-box-with-drawer'),
            'exclude' => array(),
        ),
        'paper-bags-with-logo' => array(
            'include' => explode(',', 'custom-cosmetic-paper-bag,custom-kraft-paper-bag-for-supplement-packaging,custom-luxury-gift-box-with-paper-bag,custom-luxury-paper-gift-bag-with-ribbon-handles,custom-mooncake-gift-box-set-with-paper-bag,custom-paper-shopping-bag-with-handles,custom-phone-packaging-box-with-paper-bag,custom-red-paper-shopping-bag,custom-rigid-gift-box-with-matching-paper-bag,custom-wine-bottle-gift-box-with-paper-bag'),
            'exclude' => array(),
        ),
        'corrugated-mailer-boxes' => array(
            'include' => explode(',', 'custom-corrugated-mailer-box,custom-printed-corrugated-ecommerce-box,custom-printed-corrugated-pet-food-box'),
            'exclude' => array(),
        ),
        'paper-tube-packaging' => array(
            'include' => explode(',', 'custom-cylindrical-paper-tube-box,custom-paper-tube-food-packaging-box'),
            'exclude' => array(),
        ),
        'jewelry-paper-boxes' => array(
            'include' => explode(',', 'custom-luxury-watch-box-with-drawer,custom-watch-box-with-drawer,custom-watch-box-with-pillow-insert'),
            'exclude' => array(),
        ),
        'perfume-packaging-boxes' => array(
            'include' => explode(',', 'custom-perfume-box-with-insert,custom-perfume-display-box-with-sleeve,custom-perfume-gift-set-box-with-insert'),
            'exclude' => array(),
        ),
        'skincare-packaging-boxes' => array(
            'include' => explode(',', 'custom-round-jar-drawer-box,custom-skincare-gift-box-with-insert,custom-skincare-jar-packaging-box-with-insert'),
            'exclude' => array(),
        ),
        'chocolate-gift-boxes' => array(
            'include' => explode(',', 'custom-luxury-mooncake-gift-box-with-insert,custom-mooncake-cabinet-gift-box-with-drawers,custom-mooncake-gift-box-set-with-paper-bag,custom-mooncake-handbag-gift-box-with-insert,custom-mooncake-packaging-box'),
            'exclude' => array(),
        ),
        'packaging-accessories' => array(
            'include' => explode(',', 'custom-gift-box-with-shredded-paper-filler,custom-luxury-gift-box-with-paper-bag,custom-mooncake-gift-box-set-with-paper-bag,custom-phone-packaging-box-with-paper-bag,custom-rigid-gift-box-with-matching-paper-bag,custom-wine-bottle-gift-box-with-paper-bag,custom-wine-gift-box-with-accessories'),
            'exclude' => array(),
        ),
        'candle-packaging-boxes' => array(
            'include' => array('custom-candle-jar-box-with-insert'),
            'exclude' => array(),
        ),
        'bird-nest-packaging-boxes' => array(
            'include' => explode(',', 'custom-blue-bird-nest-drawer-gift-box,custom-green-bird-nest-cabinet-gift-box,custom-green-bird-nest-compartment-gift-box,custom-green-bird-nest-gift-box-with-handle'),
            'exclude' => array(),
        ),
    );
}

function custom_box_sync_curated_category_product_assignments() {
    if (!taxonomy_exists('product_cat')) {
        return new WP_Error('missing_product_cat', 'WooCommerce product_cat taxonomy is not available.');
    }

    $updated_terms = array();

    foreach (custom_box_curated_category_product_assignments() as $category_slug => $assignment) {
        $term = get_term_by('slug', $category_slug, 'product_cat');

        if (!$term || is_wp_error($term)) {
            continue;
        }

        $term_id = (int) $term->term_id;
        $updated_terms[] = (int) $term->term_taxonomy_id;

        foreach ($assignment['include'] as $product_slug) {
            $product = get_page_by_path($product_slug, OBJECT, 'product');

            if (!$product) {
                continue;
            }

            wp_set_object_terms((int) $product->ID, array($term_id), 'product_cat', true);
        }

        foreach ($assignment['exclude'] as $product_slug) {
            $product = get_page_by_path($product_slug, OBJECT, 'product');

            if (!$product) {
                continue;
            }

            $current_terms = wp_get_object_terms((int) $product->ID, 'product_cat', array('fields' => 'ids'));

            if (is_wp_error($current_terms)) {
                continue;
            }

            $current_terms = array_values(array_diff(array_map('intval', $current_terms), array($term_id)));
            wp_set_object_terms((int) $product->ID, $current_terms, 'product_cat', false);
        }
    }

    if ($updated_terms) {
        wp_update_term_count_now(array_values(array_unique($updated_terms)), 'product_cat');
    }

    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients();
    }

    return true;
}

function custom_box_maybe_sync_curated_category_product_assignments() {
    $sync_version = 'curated-category-products-v3';

    if (get_option('custom_box_curated_category_products_version') === $sync_version) {
        return;
    }

    $result = custom_box_sync_curated_category_product_assignments();

    if (is_wp_error($result)) {
        return;
    }

    update_option('custom_box_curated_category_products_version', $sync_version, false);
}
