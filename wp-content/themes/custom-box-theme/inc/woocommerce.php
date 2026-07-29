<?php
/**
 * WooCommerce theme integration.
 */

function custom_box_woocommerce_setup() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'custom_box_woocommerce_setup');

function custom_box_remove_default_product_purchase_ui() {
    if (!class_exists('WooCommerce')) {
        return;
    }

    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
}
add_action('wp', 'custom_box_remove_default_product_purchase_ui');

function custom_box_admin_product_list_layout_fix() {
    $screen = get_current_screen();

    if (!$screen || 'edit-product' !== $screen->id) {
        return;
    }
    ?>
    <style>
        .post-type-product .wp-list-table .column-thumb {
            width: 58px;
        }

        .post-type-product .wp-list-table .column-rank_math_seo_details {
            min-width: 180px;
            white-space: normal;
            word-break: normal;
        }

        .post-type-product .wp-list-table .column-rank_math_seo_details * {
            overflow-wrap: normal;
            word-break: normal;
        }
    </style>
    <?php
}
add_action('admin_head-edit.php', 'custom_box_admin_product_list_layout_fix');

function custom_box_loop_shop_per_page($per_page) {
    return 12;
}
add_filter('loop_shop_per_page', 'custom_box_loop_shop_per_page', 20);

function custom_box_get_product_category_asset_image_url($term_or_slug) {
    $slug = is_object($term_or_slug) && isset($term_or_slug->slug) ? $term_or_slug->slug : (string) $term_or_slug;
    $slug = sanitize_title($slug);

    if (!$slug) {
        return '';
    }

    $asset_images = array(
        'pharmaceutical-packaging-boxes'      => 'custom-pharmaceutical-medicine-packaging-boxes-gray-background.webp',
        'supplement-packaging-boxes'          => 'custom-supplement-vitamin-packaging-boxes-gray-background.webp',
        'beauty-skincare-packaging'           => 'custom-cosmetic-skincare-packaging-boxes-gray-background.webp',
        'premium-food-beverage-packaging'     => 'premium-tea-coffee-chocolate-packaging-boxes-gray-background.webp',
        'electronics-accessories-packaging'   => 'custom-phone-accessories-packaging-boxes-gray-background.webp',
        'fashion-sportswear-packaging'        => 'custom-apparel-packaging-boxes-gray-background.webp',
        'sports-packaging-boxes'              => 'sport-packaging-box-thumbnail.webp',
        'wine-premium-drink-packaging'        => 'custom-wine-premium-beverage-packaging-boxes-gray-background.webp',
        'corporate-gift-packaging'            => 'custom-corporate-gift-set-packaging-boxes-gray-background.webp',
        'home-lifestyle-packaging'            => 'custom-home-lifestyle-product-packaging-boxes-gray-background.webp',
        'back-to-school-stationery-packaging' => 'custom-stationery-school-supplies-packaging-boxes-gray-background.webp',
    );

    if (empty($asset_images[$slug])) {
        return '';
    }

    return get_template_directory_uri() . '/assets/images/' . $asset_images[$slug];
}

function custom_box_get_product_category_card_image_url($term_or_slug, $size = 'medium_large') {
    $term = is_object($term_or_slug) && isset($term_or_slug->term_id)
        ? $term_or_slug
        : null;

    if (!$term && taxonomy_exists('product_cat')) {
        $slug = sanitize_title((string) $term_or_slug);
        $term = $slug ? get_term_by('slug', $slug, 'product_cat') : null;
    }

    if (!$term || is_wp_error($term)) {
        return get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
    }

    $image_url = function_exists('custom_box_get_home_packaging_category_image_url')
        ? custom_box_get_home_packaging_category_image_url($term)
        : '';

    if (!$image_url) {
        $image_url = custom_box_get_product_category_asset_image_url($term);
    }

    if (!$image_url) {
        $image_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);

        if (!$image_id) {
            $image_id = (int) get_term_meta($term->term_id, 'custom_box_category_image_id', true);
        }

        $image_url = $image_id ? wp_get_attachment_image_url($image_id, $size) : '';
    }

    if (!$image_url && function_exists('wc_get_products')) {
        $category_products = wc_get_products(array(
            'status'   => 'publish',
            'limit'    => 1,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'category' => array($term->slug),
            'return'   => 'ids',
        ));

        if (!empty($category_products[0])) {
            $product_image_id = get_post_thumbnail_id((int) $category_products[0]);
            $image_url = $product_image_id ? wp_get_attachment_image_url($product_image_id, $size) : '';
        }
    }

    return $image_url ? $image_url : get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
}

/**
 * Resolve a category card image to a real local file and intrinsic dimensions.
 *
 * Category card assets are intentionally rendered from their verified source
 * file. Asking WordPress for an intermediate attachment size can return a
 * generated filename that no longer exists after a media/database sync.
 *
 * @param WP_Term|string $term_or_slug Product category term or slug.
 * @return array{url:string,path:string,width:int,height:int}
 */
function custom_box_get_product_category_card_image_data($term_or_slug) {
    $image_url = custom_box_get_product_category_card_image_url($term_or_slug, 'full');

    if (function_exists('custom_box_get_local_image_data')) {
        return custom_box_get_local_image_data($image_url, 'Cardboard-Packaging.webp');
    }

    return array(
        'url'    => $image_url,
        'path'   => '',
        'width'  => 640,
        'height' => 480,
    );
}

function custom_box_get_flat_product_category_url($term) {
    if (!$term || is_wp_error($term)) {
        return '';
    }

    $term = is_object($term) ? $term : get_term($term, 'product_cat');

    if (!$term || is_wp_error($term) || 'product_cat' !== $term->taxonomy) {
        return '';
    }

    return home_url(user_trailingslashit('products/' . $term->slug));
}

function custom_box_get_legacy_product_category_slug_map() {
    return array(
        'bakery-food-packaging-boxes'             => 'bakery-packaging-boxes',
        'candle-gift-packaging-boxes'             => 'candle-packaging-boxes',
        'candle-jar-packaging-boxes'              => 'candle-packaging-boxes',
        'cosmetic-mailer-packaging-boxes'         => 'cosmetic-paper-boxes',
        'cosmetic-packaging-boxes'                => 'cosmetic-paper-boxes',
        'cosmetic-set-packaging-boxes'            => 'cosmetic-paper-boxes',
        'custom-cake-packaging-boxes'             => 'bakery-packaging-boxes',
        'custom-chocolate-display-boxes'          => 'chocolate-gift-boxes',
        'custom-chocolate-gift-boxes'             => 'chocolate-gift-boxes',
        'custom-paper-tube-packaging-boxes'       => 'paper-tube-packaging',
        'custom-red-paper-bags'                   => 'paper-bags-with-logo',
        'custom-soap-packaging-boxes'             => 'beauty-skincare-packaging',
        'dessert-gift-packaging-boxes'            => 'food-paper-boxes',
        'dessert-packaging-boxes-with-inserts'    => 'food-paper-boxes',
        'electronics-packaging-boxes'             => 'electronics-accessories-packaging',
        'food-packaging-boxes'                    => 'food-paper-boxes',
        'gift-packaging-boxes'                    => 'gift-paper-boxes',
        'health-supplement-packaging-boxes'       => 'supplement-packaging-boxes',
        'healthcare-packaging-boxes'              => 'pharmaceutical-packaging-boxes',
        'kraft-round-gift-boxes'                  => 'gift-paper-boxes',
        'luxury-drawer-gift-boxes'                => 'drawer-boxes',
        'luxury-perfume-packaging-boxes'          => 'perfume-packaging-boxes',
        'luxury-retail-paper-bags'                => 'paper-bags-with-logo',
        'luxury-rigid-gift-boxes'                 => 'rigid-boxes',
        'luxury-teal-paper-bags'                  => 'paper-bags-with-logo',
        'luxury-watch-packaging-boxes'            => 'jewelry-paper-boxes',
        'luxury-wine-bottle-packaging-boxes'      => 'wine-premium-drink-packaging',
        'mooncake-chocolate-gift-boxes'           => 'chocolate-gift-boxes',
        'mooncake-gift-packaging-boxes'           => 'gift-paper-boxes',
        'paper-bags'                              => 'paper-bags-with-logo',
        'pink-ribbon-gift-boxes'                  => 'gift-paper-boxes',
        'pizza-packaging-boxes'                   => 'food-paper-boxes',
        'premium-ribbon-gift-boxes'               => 'gift-paper-boxes',
        'printed-paper-shopping-bags'             => 'paper-bags-with-logo',
        'retail-packaging-boxes'                  => 'custom-printed-paper-boxes',
        'rigid-sliding-drawer-boxes'              => 'drawer-boxes',
        'stationery-packaging-boxes'              => 'back-to-school-stationery-packaging',
        'watch-packaging-boxes'                   => 'jewelry-paper-boxes',
        'wine-bottle-gift-boxes'                  => 'wine-premium-drink-packaging',
        'wine-packaging-boxes'                    => 'wine-premium-drink-packaging',
    );
}

function custom_box_get_product_category_by_current_or_legacy_slug($slug) {
    $slug = sanitize_title($slug);

    if (!$slug) {
        return null;
    }

    $term = get_term_by('slug', $slug, 'product_cat');

    if ($term && !is_wp_error($term)) {
        return $term;
    }

    $legacy_map = custom_box_get_legacy_product_category_slug_map();

    if (empty($legacy_map[$slug])) {
        return null;
    }

    $term = get_term_by('slug', $legacy_map[$slug], 'product_cat');

    return ($term && !is_wp_error($term)) ? $term : null;
}

function custom_box_flat_product_category_link($termlink, $term, $taxonomy) {
    if ('product_cat' !== $taxonomy) {
        return $termlink;
    }

    $flat_link = custom_box_get_flat_product_category_url($term);

    return $flat_link ? $flat_link : $termlink;
}
add_filter('term_link', 'custom_box_flat_product_category_link', 10, 3);

function custom_box_add_flat_product_category_rewrite_rules() {
    add_rewrite_rule(
        '^products/page/([0-9]+)/?$',
        'index.php?post_type=product&paged=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^products/([^/]+)/page/([0-9]+)/?$',
        'index.php?product_cat=$matches[1]&paged=$matches[2]',
        'top'
    );

    add_rewrite_rule(
        '^products/([^/]+)/?$',
        'index.php?product_cat=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^packaging/([^/]+)/page/([0-9]+)/?$',
        'index.php?product_cat=$matches[1]&paged=$matches[2]',
        'top'
    );

    add_rewrite_rule(
        '^packaging/([^/]+)/?$',
        'index.php?product_cat=$matches[1]',
        'top'
    );
}
add_action('init', 'custom_box_add_flat_product_category_rewrite_rules', 20);

function custom_box_maybe_flush_product_category_rewrites() {
    $rewrite_version = 'products-category-base-v3';

    if (get_option('custom_box_product_category_rewrite_version') === $rewrite_version) {
        return;
    }

    custom_box_add_flat_product_category_rewrite_rules();
    flush_rewrite_rules(false);
    update_option('custom_box_product_category_rewrite_version', $rewrite_version, false);
}
add_action('init', 'custom_box_maybe_flush_product_category_rewrites', 30);

function custom_box_flush_rewrite_rules_on_theme_switch() {
    custom_box_add_flat_product_category_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'custom_box_flush_rewrite_rules_on_theme_switch');

function custom_box_quote_product_name() {
    if (is_product()) {
        return get_the_title();
    }

    return '';
}

function custom_box_redirect_legacy_shop_slug() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $request_path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
    $home_path = wp_parse_url(home_url('/'), PHP_URL_PATH);
    $relative_path = trim(preg_replace('#^' . preg_quote($home_path, '#') . '#', '', $request_path), '/');

    if ('shop' !== $relative_path) {
        return;
    }

    $products_url = custom_box_get_products_url();

    if ($products_url && !is_wp_error($products_url)) {
        wp_safe_redirect($products_url, 301);
        exit;
    }
}
add_action('template_redirect', 'custom_box_redirect_legacy_shop_slug', 1);

function custom_box_redirect_legacy_products_hub_urls() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $relative_path = custom_box_get_relative_request_path();

    if (!preg_match('#^(p/products|custom-packaging-product-categories)(?:/(.*))?$#', $relative_path, $matches)) {
        return;
    }

    $legacy_tail = isset($matches[2]) ? trim($matches[2], '/') : '';

    if ('' === $legacy_tail) {
        $target_url = custom_box_get_products_url();
    } elseif (preg_match('#^page/([0-9]+)$#', $legacy_tail, $page_matches)) {
        $target_url = home_url(user_trailingslashit('products/page/' . absint($page_matches[1])));
    } elseif (preg_match('#^([^/]+)/page/([0-9]+)$#', $legacy_tail, $category_page_matches)) {
        $target_url = home_url(user_trailingslashit('products/' . sanitize_title($category_page_matches[1]) . '/page/' . absint($category_page_matches[2])));
    } else {
        $slug = custom_box_get_product_category_slug_from_path($legacy_tail);
        $term = $slug ? custom_box_get_product_category_by_current_or_legacy_slug($slug) : null;
        $target_url = $term && !is_wp_error($term)
            ? custom_box_get_flat_product_category_url($term) . custom_box_get_product_category_paged_path($legacy_tail)
            : home_url(user_trailingslashit('products/' . $legacy_tail));
    }

    $query_string = isset($_SERVER['QUERY_STRING']) && '' !== $_SERVER['QUERY_STRING']
        ? '?' . wp_unslash($_SERVER['QUERY_STRING'])
        : '';

    wp_safe_redirect($target_url . $query_string, 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_legacy_products_hub_urls', 1);

function custom_box_redirect_numeric_shop_pagination() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $relative_path = custom_box_get_relative_request_path();

    if (!preg_match('#^products/([0-9]+)/?$#', $relative_path, $matches)) {
        return;
    }

    wp_safe_redirect(home_url(user_trailingslashit('products/page/' . absint($matches[1]))), 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_numeric_shop_pagination', 2);

function custom_box_get_relative_request_path() {
    $request_path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
    $home_path = wp_parse_url(home_url('/'), PHP_URL_PATH);

    return trim(preg_replace('#^' . preg_quote($home_path, '#') . '#', '', $request_path), '/');
}

function custom_box_get_product_category_slug_from_path($relative_path) {
    $path_parts = array_values(array_filter(explode('/', trim($relative_path, '/'))));

    if (count($path_parts) >= 2 && 'page' === $path_parts[count($path_parts) - 2]) {
        array_splice($path_parts, -2);
    }

    return $path_parts ? end($path_parts) : '';
}

function custom_box_get_product_category_paged_path($relative_path) {
    $path_parts = array_values(array_filter(explode('/', trim($relative_path, '/'))));

    if (count($path_parts) >= 2 && 'page' === $path_parts[count($path_parts) - 2]) {
        return user_trailingslashit('page/' . absint(end($path_parts)));
    }

    return '';
}

function custom_box_redirect_legacy_product_category_base() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $relative_path = custom_box_get_relative_request_path();

    if (0 !== strpos($relative_path, 'product-category/')) {
        return;
    }

    $slug = custom_box_get_product_category_slug_from_path($relative_path);
    $term = custom_box_get_product_category_by_current_or_legacy_slug($slug);
    $target_url = $term && !is_wp_error($term)
        ? custom_box_get_flat_product_category_url($term) . custom_box_get_product_category_paged_path($relative_path)
        : home_url('/' . preg_replace('#^product-category/#', 'products/', $relative_path) . '/');

    wp_safe_redirect($target_url, 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_legacy_product_category_base', 2);

function custom_box_redirect_legacy_packaging_category_urls() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $relative_path = custom_box_get_relative_request_path();

    if (!preg_match('#^packaging/(.+)$#', $relative_path, $matches)) {
        return;
    }

    $legacy_tail = trim($matches[1], '/');
    $slug = custom_box_get_product_category_slug_from_path($legacy_tail);
    $term = $slug ? custom_box_get_product_category_by_current_or_legacy_slug($slug) : null;

    if (!$term || is_wp_error($term)) {
        return;
    }

    $target_url = custom_box_get_flat_product_category_url($term) . custom_box_get_product_category_paged_path($legacy_tail);

    if (!$target_url) {
        return;
    }

    $query_string = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']
        ? '?' . wp_unslash($_SERVER['QUERY_STRING'])
        : '';

    wp_safe_redirect($target_url . $query_string, 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_legacy_packaging_category_urls', 2);

function custom_box_redirect_hierarchical_product_category_urls() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $relative_path = custom_box_get_relative_request_path();

    if (!preg_match('#^packaging/.+/.+$#', $relative_path)) {
        return;
    }

    $slug = custom_box_get_product_category_slug_from_path($relative_path);
    $term = custom_box_get_product_category_by_current_or_legacy_slug($slug);

    if (!$term || is_wp_error($term)) {
        return;
    }

    $target_url = custom_box_get_flat_product_category_url($term) . custom_box_get_product_category_paged_path($relative_path);

    if (!$target_url) {
        return;
    }

    $query_string = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']
        ? '?' . wp_unslash($_SERVER['QUERY_STRING'])
        : '';

    wp_safe_redirect($target_url . $query_string, 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_hierarchical_product_category_urls', 3);

function custom_box_redirect_noncanonical_product_category_url() {
    if (is_admin() || wp_doing_ajax() || !is_product_taxonomy()) {
        return;
    }

    $term = get_queried_object();
    $target_url = custom_box_get_flat_product_category_url($term);
    $paged = max(1, (int) get_query_var('paged'));

    if (!$target_url) {
        return;
    }

    if ($paged > 1) {
        $target_url .= user_trailingslashit('page/' . $paged);
    }

    $request_path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
    $target_path = wp_parse_url($target_url, PHP_URL_PATH);

    if (untrailingslashit($request_path) === untrailingslashit($target_path)) {
        return;
    }

    wp_safe_redirect($target_url, 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_noncanonical_product_category_url', 9);

function custom_box_redirect_noncanonical_product_url() {
    if (is_admin() || wp_doing_ajax() || !is_product()) {
        return;
    }

    $target_url = get_permalink(get_queried_object_id());
    $request_path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
    $target_path = wp_parse_url($target_url, PHP_URL_PATH);

    if (!$target_url || untrailingslashit($request_path) === untrailingslashit($target_path)) {
        return;
    }

    wp_safe_redirect($target_url, 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_noncanonical_product_url', 10);
