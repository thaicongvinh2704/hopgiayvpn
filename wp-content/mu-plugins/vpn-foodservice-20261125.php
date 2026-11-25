<?php
/**
 * Plugin Name: VPN Foodservice Product Release
 * Description: Publishes the four approved foodservice products through Tools > Foodservice Product Release.
 * Version: 2026.11.25.1
 */
defined('ABSPATH') || exit;

function vpn_foodservice_release_page(): void {
    if (!current_user_can('manage_options') || !current_user_can('manage_woocommerce')) {
        wp_die('Administrator access is required.', '', ['response' => 403]);
    }

    echo '<div class="wrap"><h1>Foodservice Product Release</h1>';
    if (wp_get_environment_type() !== 'local' &&
        !in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true)) {
        echo '<p>This collection is available on the local VPN site and hopgiayvpn.com.</p></div>';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('vpn_foodservice_release');
        try {
            require_once __DIR__ . '/vpn-foodservice-20261125/import.php';
            $result = vpn_foodservice_release_publish();
            echo '<div class="notice notice-success inline"><p>Four published foodservice products verified.</p></div><ul>';
            foreach ($result['products'] as $product) {
                echo '<li><a href="' . esc_url($product['url']) . '">' . esc_html($product['seo_title']) . '</a></li>';
            }
            echo '</ul>';
        } catch (Throwable $error) {
            echo '<div class="notice notice-error inline"><p>' . esc_html($error->getMessage()) . '</p></div>';
        }
    }

    echo '<p>Import the four foodservice products, their 21 product photos and approved Vietnam SEO metadata.</p><form method="post">';
    wp_nonce_field('vpn_foodservice_release');
    submit_button('Sync 4 Foodservice Products');
    echo '</form></div>';
}

add_action('admin_menu', static function (): void {
    add_management_page('Foodservice Product Release', 'Foodservice Product Release', 'manage_options',
        'vpn-foodservice-20261125', 'vpn_foodservice_release_page');
});

function vpn_foodservice_quote_product(): bool {
    return function_exists('is_product') && is_product()
        && get_post_meta(get_queried_object_id(), '_vpn_sample_import', true) === 'foodservice-concepts-20261125';
}

function vpn_foodservice_og_value($value) {
    return vpn_foodservice_quote_product() ? '' : $value;
}

add_filter('rank_math/opengraph/facebook/product_price_amount', 'vpn_foodservice_og_value', 100);
add_filter('rank_math/opengraph/facebook/product_price_currency', 'vpn_foodservice_og_value', 100);
add_filter('rank_math/opengraph/facebook/product_availability', 'vpn_foodservice_og_value', 100);

add_filter('rank_math/json_ld', static function (array $data): array {
    if (!vpn_foodservice_quote_product()) {
        return $data;
    }
    $product = wc_get_product(get_queried_object_id());
    if (!$product) {
        return $data;
    }
    foreach ($data as &$entity) {
        if (!is_array($entity) || !in_array('Product', (array) ($entity['@type'] ?? []), true)) {
            continue;
        }
        unset($entity['offers'], $entity['review'], $entity['aggregateRating']);
        $entity['name'] = $product->get_name();
        $entity['description'] = wp_strip_all_tags($product->get_short_description());
        $entity['url'] = get_permalink($product->get_id());
        $entity['image'] = array_values(array_filter(array_map('wp_get_attachment_url',
            array_merge([$product->get_image_id()], $product->get_gallery_image_ids())
        )));
    }
    unset($entity);
    return $data;
}, 100);
