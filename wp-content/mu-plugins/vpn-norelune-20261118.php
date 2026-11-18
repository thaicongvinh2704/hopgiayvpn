<?php
/**
 * Plugin Name: VPN Norelune Product Release
 * Description: Imports the five approved nail polish packaging products on the first hosting administrator visit.
 * Version: 2026.11.18.1
 */

defined('ABSPATH') || exit;

function vpn_norelune_deploy(): void {
    if (get_option('vpn_norelune_20261118_done') === 'vpn-norelune-20261118'
        || !current_user_can('manage_options') || !current_user_can('manage_woocommerce')
        || wp_doing_ajax() || wp_doing_cron()
        || !in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true)
        || (int) get_option('vpn_norelune_20261118_retry') > time()) {
        return;
    }

    global $wpdb;
    $lock = 'vpn_norelune_' . md5(DB_NAME . $wpdb->prefix);
    if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== '1') {
        return;
    }

    try {
        wp_cache_delete('vpn_norelune_20261118_done', 'options');
        wp_cache_delete('notoptions', 'options');
        if (get_option('vpn_norelune_20261118_done') === 'vpn-norelune-20261118') {
            return;
        }
        @set_time_limit(180);
        require_once __DIR__ . '/vpn-norelune-20261118/import.php';
        $rows = norelune_release_package();
        $context = norelune_release_context();
        $errors = [];
        $imported = false;
        foreach ($rows as $row) {
            try {
                $result = norelune_release_product($row, $context);
                $imported = $imported || $result['published'];
            } catch (Throwable $error) {
                $errors[] = $row['slug'] . ' — ' . $error->getMessage();
            }
        }
        if ($imported && class_exists('RankMath\Sitemap\Cache')) {
            \RankMath\Sitemap\Cache::invalidate_storage();
        }
        if ($errors) {
            throw new RuntimeException(implode(' | ', $errors));
        }
        update_option('vpn_norelune_20261118_done', NORELUNE_BATCH, false);
        delete_option('vpn_norelune_20261118_error');
        delete_option('vpn_norelune_20261118_retry');
        set_transient('vpn_norelune_20261118_notice', true, DAY_IN_SECONDS);
    } catch (Throwable $error) {
        update_option('vpn_norelune_20261118_error', $error->getMessage(), false);
        update_option('vpn_norelune_20261118_retry', time() + 300, false);
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}
add_action('admin_init', 'vpn_norelune_deploy', 110);

add_action('admin_notices', static function (): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    $error = get_option('vpn_norelune_20261118_error');
    if ($error) {
        echo '<div class="notice notice-warning"><p>' . esc_html('Norelune products: ' . $error) . '</p></div>';
    } elseif (get_transient('vpn_norelune_20261118_notice')) {
        delete_transient('vpn_norelune_20261118_notice');
        echo '<div class="notice notice-success is-dismissible"><p>Norelune products are ready. <a href="'
            . esc_url(admin_url('edit.php?post_type=product')) . '">Open Products</a>.</p></div>';
    }
});
