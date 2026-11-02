<?php
/**
 * Plugin Name: VPN Approved Luxury Paper Bags — November 2
 * Description: One-time import of five reviewed public products and 30 images on the first administrator visit after deployment.
 * Version: 2026.11.02.1
 */
defined('ABSPATH') || exit;
const VPN_LB02_DONE = 'vpn_luxury_bags_20261102_done';

function vpn_lb02_deploy(): void {
    if (get_option(VPN_LB02_DONE) === 'luxury-paper-bags-20261102-v1' || !current_user_can('manage_options')
        || wp_doing_ajax() || wp_doing_cron() || (defined('WP_INSTALLING') && WP_INSTALLING)) { return; }
    $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    if (!in_array($host, ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true) || (int) get_option('vpn_lb02_retry', 0) > time()) { return; }
    global $wpdb;
    $lock = substr($wpdb->prefix . 'vpn_lb02_release', 0, 64);
    if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)', $lock)) !== '1') { return; }
    try {
        // Another request may have completed the batch while this request loaded.
        if (get_option(VPN_LB02_DONE) === 'luxury-paper-bags-20261102-v1') { return; }
        @set_time_limit(180);
        require_once __DIR__ . '/vpn-luxury-bags-20261102/import.php';
        $results = vpn_lb02_run();
        if (count($results) !== 5 || array_filter($results, static fn($r) => isset($r['error']))) {
            throw new RuntimeException('Some products need review; see the per-product results.');
        }
        update_option(VPN_LB02_DONE, VPN_LB02_MARKER, false);
        delete_option('vpn_lb02_error'); delete_option('vpn_lb02_retry');
        set_transient('vpn_lb02_notice', true, 120);
    } catch (Throwable $error) {
        update_option('vpn_lb02_error', $error->getMessage(), false);
        update_option('vpn_lb02_retry', time() + 300, false);
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}
add_action('admin_init', 'vpn_lb02_deploy', 110);

add_action('admin_notices', static function (): void {
    if (!current_user_can('manage_options')) { return; }
    if ($error = get_option('vpn_lb02_error')) {
        $details = [];
        foreach ((array) get_option('vpn_lb02_results', []) as $key => $row) {
            if (isset($row['error'])) { $details[] = $key . ': ' . $row['error']; }
        }
        echo '<div class="notice notice-error"><p>' . esc_html('Luxury paper bags: ' . $error . ' ' . implode(' | ', $details)
            . ' Existing products are preserved; retry after five minutes. Results: vpn_lb02_results.') . '</p></div>';
    } elseif (get_transient('vpn_lb02_notice')) {
        delete_transient('vpn_lb02_notice');
        echo '<div class="notice notice-success is-dismissible"><p>Luxury paper bags: five reviewed products synced. Open Products to check their current status.</p></div>';
    }
});
