<?php
/** Git-bundled, authenticated admin sync for the complete October Christmas release. */
defined('ABSPATH') || exit;
require_once __DIR__ . '/christmas-gift-boxes-20261001-support.php';

const VPN_XMAS_20261001_OPTION = 'custom_box_christmas_gift_boxes_20261001_sync_version';

function custom_box_christmas_20261001_validate(): array {
    ob_start();
    try {
        (static function () {
            require __DIR__ . '/product-sample-deploy-tools/verify-christmas-gift-boxes-20261001.php';
        })();
        $result = json_decode((string) ob_get_contents(), true);
        if (!is_array($result) || 5 !== count($result['products'] ?? array()) || !empty($result['failures'])) {
            return array('The saved October product batch did not pass verification.');
        }
        return array();
    } catch (Throwable $error) {
        return array($error->getMessage());
    } finally {
        ob_end_clean();
    }
}

function custom_box_sync_christmas_20261001_products(): void {
    if (!is_admin() || !current_user_can('manage_options')
        || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)) {
        return;
    }
    if (VPN_XMAS_20261001_RELEASE === get_option(VPN_XMAS_20261001_OPTION)
        && !custom_box_christmas_20261001_validate()) {
        return;
    }
    $lock = 'vpn_christmas_20261001_sync_lock';
    if (get_transient($lock)) {
        return;
    }
    set_transient($lock, 1, 3 * MINUTE_IN_SECONDS);
    delete_option(VPN_XMAS_20261001_OPTION);
    delete_transient('vpn_christmas_20261001_sync_notice');
    ob_start();
    try {
        (static function () {
            require __DIR__ . '/product-sample-deploy-tools/import-christmas-gift-boxes-20261001.php';
        })();
        $errors = custom_box_christmas_20261001_validate();
        if ($errors) {
            throw new RuntimeException(implode('; ', $errors));
        }
        update_option(VPN_XMAS_20261001_OPTION, VPN_XMAS_20261001_RELEASE, false);
        set_transient('vpn_christmas_20261001_sync_notice', array(
            'type' => 'success',
            'message' => 'October Christmas release verified: 5 products, 30 product images, 2 shared company photographs, SEO metadata and internal links synced.',
        ), HOUR_IN_SECONDS);
    } catch (Throwable $error) {
        delete_option(VPN_XMAS_20261001_OPTION);
        set_transient('vpn_christmas_20261001_sync_notice', array(
            'type' => 'warning',
            'message' => 'October Christmas release is incomplete and will retry on the next admin request: ' . $error->getMessage(),
        ), HOUR_IN_SECONDS);
    } finally {
        ob_end_clean();
        delete_transient($lock);
    }
}
add_action('admin_init', 'custom_box_sync_christmas_20261001_products', 30);

function custom_box_christmas_20261001_sync_notice(): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    $notice = get_transient('vpn_christmas_20261001_sync_notice');
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }
    delete_transient('vpn_christmas_20261001_sync_notice');
    echo '<div class="notice notice-' . esc_attr($notice['type']) . ' is-dismissible"><p>' . esc_html($notice['message']) . '</p></div>';
}
add_action('admin_notices', 'custom_box_christmas_20261001_sync_notice');
