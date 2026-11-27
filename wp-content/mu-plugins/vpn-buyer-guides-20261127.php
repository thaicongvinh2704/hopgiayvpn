<?php
/**
 * Plugin Name: VPN Buyer Guide Release
 * Description: Publishes the exact approved WordPress previews when an administrator confirms the update.
 * Version: 2026.11.27.2
 */
defined('ABSPATH') || exit;

const VPN_BUYER_GUIDES_RELEASE = 'vpn-buyer-guides-20261127';
const VPN_BUYER_GUIDES_STATE = 'vpn-buyer-guides-exact-20261127';

function vpn_buyer_guides_release_allowed(): bool {
    return current_user_can('manage_options') && current_user_can('publish_posts') &&
        current_user_can('edit_others_posts') && current_user_can('unfiltered_html') &&
        (wp_get_environment_type() === 'local' ||
         in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true));
}

add_action('admin_notices', static function (): void {
    if (!vpn_buyer_guides_release_allowed() || wp_doing_ajax()) {
        return;
    }
    $current = get_option(VPN_BUYER_GUIDES_STATE, []);
    if (isset($current['articles'])) {
        if (isset($_GET['vpn_buyer_guides_updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Three buyer guides are published.</p><ul>';
            foreach ($current['articles'] as $article) {
                echo '<li><a href="' . esc_url($article['url']) . '">' . esc_html($article['title']) . '</a></li>';
            }
            echo '</ul></div>';
        }
        return;
    }
    echo '<div class="notice notice-' . (isset($current['error']) ? 'error' : 'info') . '">';
    echo '<p><strong>The approved local versions of the buyer guides are ready</strong></p>';
    echo '<p>Confirm to publish the three guides with the text, images and layout verified against the local WordPress previews.</p>';
    if (isset($current['error'])) {
        echo '<p>' . esc_html($current['error']) . '</p>';
    }
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="vpn_buyer_guides_confirm">';
    wp_nonce_field('vpn_buyer_guides_confirm');
    submit_button('Xác nhận 3 bài blog đúng bản local', 'primary', 'submit', false);
    echo '</form><p></p></div>';
});

add_action('admin_post_vpn_buyer_guides_confirm', static function (): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !vpn_buyer_guides_release_allowed()) {
        wp_die('Administrator access and a POST request are required.', '', ['response' => 403]);
    }
    check_admin_referer('vpn_buyer_guides_confirm');
    require_once __DIR__ . '/' . VPN_BUYER_GUIDES_RELEASE . '/import.php';
    try {
        vpn_buyer_guides_publish();
    } catch (Throwable $error) {
        if (!isset(get_option(VPN_BUYER_GUIDES_STATE, [])['articles'])) {
            update_option(VPN_BUYER_GUIDES_STATE, ['error' => $error->getMessage()], false);
        }
    }
    wp_safe_redirect(add_query_arg('vpn_buyer_guides_updated', '1', admin_url('index.php')));
    exit;
});
