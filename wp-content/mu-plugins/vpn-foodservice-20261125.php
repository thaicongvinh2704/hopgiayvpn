<?php
/**
 * Plugin Name: VPN Foodservice Product Release
 * Description: Offers the approved foodservice product update in WordPress admin after deployment.
 * Version: 2026.11.25.2
 */
defined('ABSPATH') || exit;

const VPN_FOODSERVICE_ADMIN_VERSION = '2026.11.25.2';

function vpn_foodservice_can_update(): bool {
    return current_user_can('manage_options') && current_user_can('manage_woocommerce') &&
        (wp_get_environment_type() === 'local' ||
        in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true));
}

function vpn_foodservice_apply_release(): array {
    if (!vpn_foodservice_can_update()) {
        throw new RuntimeException('Administrator access on the VPN website is required.');
    }
    try {
        require_once __DIR__ . '/vpn-foodservice-20261125/import.php';
        $result = vpn_foodservice_release_publish();
        update_option('vpn_foodservice_admin_result', [
            'version' => VPN_FOODSERVICE_ADMIN_VERSION, 'status' => 'complete',
            'products' => $result['products'],
        ], false);
        return $result;
    } catch (Throwable $error) {
        update_option('vpn_foodservice_admin_result', [
            'version' => VPN_FOODSERVICE_ADMIN_VERSION, 'status' => 'error',
            'message' => $error->getMessage(),
        ], false);
        throw $error;
    }
}

function vpn_foodservice_update_notice(): void {
    if (!vpn_foodservice_can_update() || wp_doing_ajax()) {
        return;
    }
    $state = get_option('vpn_foodservice_admin_result', []);
    $current = is_array($state) && ($state['version'] ?? '') === VPN_FOODSERVICE_ADMIN_VERSION;
    if ($current && ($state['status'] ?? '') === 'complete') {
        if (!isset($_GET['vpn_foodservice_updated'])) {
            return;
        }
        echo '<div class="notice notice-success is-dismissible"><p>Four foodservice products updated and verified.</p><ul>';
        foreach ($state['products'] as $product) {
            echo '<li><a href="' . esc_url($product['url']) . '">' . esc_html($product['seo_title']) . '</a></li>';
        }
        echo '</ul></div>';
        return;
    }
    echo '<div class="notice notice-' . ($current && ($state['status'] ?? '') === 'error' ? 'error' : 'info') . '">';
    echo '<p><strong>Foodservice product update ready</strong></p>';
    echo '<p>Confirm the update to add or verify the four approved products, their 21 images and Vietnam SEO metadata.</p>';
    if ($current && ($state['status'] ?? '') === 'error') {
        echo '<p>' . esc_html($state['message'] ?? 'The update could not finish. Review and retry.') . '</p>';
    }
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="vpn_foodservice_confirm_update">';
    wp_nonce_field('vpn_foodservice_confirm_update');
    submit_button('Confirm product update', 'primary', 'submit', false);
    echo '</form><p></p></div>';
}
add_action('admin_notices', 'vpn_foodservice_update_notice');

add_action('admin_post_vpn_foodservice_confirm_update', static function (): void {
    if (!vpn_foodservice_can_update()) {
        wp_die('Administrator access on the VPN website is required.', '', ['response' => 403]);
    }
    check_admin_referer('vpn_foodservice_confirm_update');
    try {
        vpn_foodservice_apply_release();
    } catch (Throwable $error) {
        // The stored result is shown on the dashboard, with a retry button.
    }
    wp_safe_redirect(add_query_arg('vpn_foodservice_updated', '1', admin_url('index.php')));
    exit;
});

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
            $result = vpn_foodservice_apply_release();
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
