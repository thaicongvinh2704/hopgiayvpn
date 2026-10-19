<?php
/** Authenticated pull/deploy migration for four reviewed products and their media. */
defined('ABSPATH') || exit;
require_once __DIR__ . '/four-products-20261019-support.php';

function custom_box_sync_four_products_20261019(): void {
    if (!is_admin() || !current_user_can('manage_options') || wp_doing_ajax()
        || (defined('REST_REQUEST') && REST_REQUEST) || (defined('DOING_CRON') && DOING_CRON)) { return; }
    try {
        if (get_option(VPN_FOUR_20261019_OPTION) === VPN_FOUR_20261019_VERSION && !vpn_four_20261019_completion_errors()) { return; }
        delete_option(VPN_FOUR_20261019_OPTION);
        @set_time_limit(300);
        $ids = vpn_four_20261019_run();
        vpn_four_20261019_assert(count($ids) === 4 && !vpn_four_20261019_completion_errors(), 'Four-product completion check failed.');
        update_option(VPN_FOUR_20261019_OPTION, VPN_FOUR_20261019_VERSION, false);
        set_transient('vpn_four_20261019_sync_notice', ['type' => 'success',
            'message' => 'Four-product release complete: 4 products, 24 images, product content and SEO metadata verified.'], HOUR_IN_SECONDS);
        // Purge only the affected products and catalog/category pages on hosting.
        if (in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true)) {
            foreach ($ids as $id) { do_action('litespeed_purge_post', $id); }
            foreach (['/products/', '/products/paper-bags-with-logo/', '/products/bakery-packaging-boxes/', '/products/corrugated-mailer-boxes/'] as $path) {
                do_action('litespeed_purge_url', home_url($path));
            }
        }
    } catch (Throwable $error) {
        delete_option(VPN_FOUR_20261019_OPTION);
        set_transient('vpn_four_20261019_sync_notice', ['type' => 'warning',
            'message' => 'Four-product release is incomplete; review and reload this admin page to retry. ' . $error->getMessage()], HOUR_IN_SECONDS);
    }
}
add_action('admin_init', 'custom_box_sync_four_products_20261019', 30);
add_action('admin_notices', static function (): void {
    if (!current_user_can('manage_options')) { return; }
    $notice = get_transient('vpn_four_20261019_sync_notice');
    if (!is_array($notice) || empty($notice['message'])) { return; }
    delete_transient('vpn_four_20261019_sync_notice');
    echo '<div class="notice notice-' . esc_attr($notice['type']) . ' is-dismissible"><p>' . esc_html($notice['message']) . '</p></div>';
});
