<?php
/**
 * Plugin Name: VPN Apollo Website Visitors
 * Description: Optional Apollo tracking for visitors who opt in on public VPN pages.
 */

defined('ABSPATH') || exit;

function vpn_apollo_visitor_mode(): string {
    if (
        'GET' !== ($_SERVER['REQUEST_METHOD'] ?? '') ||
        is_admin() || is_user_logged_in() || is_feed() || is_preview() ||
        is_search() || is_404() ||
        (is_singular() && ('publish' !== get_post_status() || post_password_required())) ||
        (function_exists('is_cart') && is_cart()) ||
        (function_exists('is_checkout') && is_checkout()) ||
        (function_exists('is_account_page') && is_account_page()) ||
        (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url())
    ) {
        return '';
    }

    if ('local' === wp_get_environment_type()) {
        return 'preview';
    }

    $production_hosts = array('hopgiayvpn.com', 'www.hopgiayvpn.com');
    if (
        defined('VPN_APOLLO_WEBSITE_VISITORS') && true === VPN_APOLLO_WEBSITE_VISITORS &&
        in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), $production_hosts, true)
    ) {
        return 'live';
    }

    return '';
}

function vpn_apollo_visitor_preferences(): void {
    $mode = vpn_apollo_visitor_mode();
    if ('' === $mode) {
        return;
    }

    $script = 'vpn-apollo/website-visitors.js';
    $script_file = __DIR__ . '/' . $script;
    if (!is_readable($script_file)) {
        return;
    }
    wp_enqueue_script('vpn-apollo-website-visitors', plugins_url($script, __FILE__), array(), (string) filemtime($script_file), true);
    ?>
    <section class="container" aria-label="Apollo privacy preferences">
        <details data-vpn-apollo-mode="<?php echo esc_attr($mode); ?>" hidden>
            <summary>Cookie preferences</summary>
            <p>Apollo helps us understand which companies visit our website. If you allow it, Apollo may use a cookie and receive visit information such as your IP address and the pages you view. You can change this choice here.</p>
            <p><a href="https://www.apollo.io/privacy-policy" rel="noopener noreferrer" target="_blank">Apollo Privacy Policy</a></p>
            <p>
                <button type="button" data-vpn-apollo-choice="allow">Allow Apollo</button>
                <button type="button" data-vpn-apollo-choice="deny">Decline Apollo</button>
            </p>
            <p role="status" data-vpn-apollo-status></p>
            <?php if ('preview' === $mode) : ?>
                <p>Local preview. No visit is sent to Apollo.</p>
            <?php endif; ?>
        </details>
    </section>
    <?php
}
// Run before WordPress prints its footer scripts.
add_action('wp_footer', 'vpn_apollo_visitor_preferences', 5);
