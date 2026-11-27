<?php
/**
 * Plugin Name: VPN Packaging Product Release
 * Description: Adds the reviewed seven-product release after an administrator confirms it.
 * Version: 2026.11.27
 */
defined('ABSPATH') || exit;

const VPN_PACKAGING_NOV_RELEASE = 'vpn-packaging-products-20261127';

function vpn_packaging_nov_allowed(): bool {
    return current_user_can('manage_options') && current_user_can('manage_woocommerce') &&
        (wp_get_environment_type() === 'local' ||
         in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true));
}

add_action('admin_notices', static function (): void {
    if (!vpn_packaging_nov_allowed() || wp_doing_ajax()) {
        return;
    }
    $state = get_option(VPN_PACKAGING_NOV_RELEASE, []);
    if (isset($state['products'])) {
        if (isset($_GET['vpn_packaging_nov_updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Seven packaging products are published.</p><ul>';
            foreach ($state['products'] as $product) {
                echo '<li><a href="' . esc_url($product['url']) . '">' . esc_html($product['name']) . '</a></li>';
            }
            echo '</ul></div>';
        }
        return;
    }
    echo '<div class="notice notice-' . (isset($state['error']) ? 'error' : 'info') . '">';
    echo '<p><strong>Packaging product update ready</strong></p>';
    echo '<p>Confirm to publish the seven reviewed packaging products with 42 gallery images and Vietnam SEO metadata.</p>';
    if (isset($state['error'])) {
        echo '<p>' . esc_html($state['error']) . '</p>';
    }
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="vpn_packaging_nov_confirm">';
    wp_nonce_field('vpn_packaging_nov_confirm');
    submit_button('Confirm 7 packaging products', 'primary', 'submit', false);
    echo '</form><p></p></div>';
});

add_action('admin_post_vpn_packaging_nov_confirm', static function (): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !vpn_packaging_nov_allowed()) {
        wp_die('Administrator access and a POST request are required.', '', ['response' => 403]);
    }
    check_admin_referer('vpn_packaging_nov_confirm');
    require_once __DIR__ . '/' . VPN_PACKAGING_NOV_RELEASE . '/import.php';
    try {
        vpn_packaging_nov_publish();
    } catch (Throwable $error) {
        if (!(get_option(VPN_PACKAGING_NOV_RELEASE, [])['products'] ?? null)) {
            update_option(VPN_PACKAGING_NOV_RELEASE, ['error' => $error->getMessage()], false);
        }
    }
    wp_safe_redirect(add_query_arg('vpn_packaging_nov_updated', '1', admin_url('index.php')));
    exit;
});

function vpn_packaging_nov_quote_product(): bool {
    return function_exists('is_product') && is_product() &&
        get_post_meta(get_queried_object_id(), '_vpn_packaging_release', true) === VPN_PACKAGING_NOV_RELEASE;
}

foreach (['product_price_amount', 'product_price_currency', 'product_availability'] as $field) {
    add_filter('rank_math/opengraph/facebook/' . $field, static function ($value) {
        return vpn_packaging_nov_quote_product() ? '' : $value;
    }, 100);
}

add_filter('rank_math/json_ld', static function (array $data): array {
    if (!vpn_packaging_nov_quote_product()) {
        return $data;
    }
    foreach ($data as &$entity) {
        if (is_array($entity) && in_array('Product', (array) ($entity['@type'] ?? []), true)) {
            unset($entity['offers'], $entity['review'], $entity['aggregateRating']);
        }
    }
    unset($entity);
    return $data;
}, 100);

add_filter('woocommerce_structured_data_product', static function (array $entity, $product): array {
    if ($product instanceof WC_Product &&
        $product->get_meta('_vpn_packaging_release') === VPN_PACKAGING_NOV_RELEASE) {
        unset($entity['offers'], $entity['review'], $entity['aggregateRating']);
    }
    return $entity;
}, 100, 2);
