<?php
/**
 * Plugin Name: VPN Website Email Quote Popup
 * Description: Sitewide, frequency-capped email enquiries using the shared private quote inbox.
 * Version: 1.0.2
 */
defined('ABSPATH') || exit;

function vpn_email_popup_scope() {
    return substr(md5(home_url('/')), 0, 12);
}

function vpn_email_popup_preview_allowed() {
    return 'local' === wp_get_environment_type() && current_user_can('manage_options');
}

add_action('admin_bar_menu', function ($bar) {
    if (!vpn_email_popup_preview_allowed() || !vpn_email_popup_enabled()) { return; }
    $path = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $bar->add_node(array('id' => 'vpn-email-popup-preview', 'title' => 'Xem thử popup',
        'href' => add_query_arg('vpn_popup_preview', '1', $path ?: home_url('/'))));
}, 100);

add_action('custom_box_quote_saved', function () {
    if (headers_sent()) { return; }
    setcookie('vpn_quote_received_' . vpn_email_popup_scope(), '1', array(
        'expires' => time() + YEAR_IN_SECONDS,
        'path' => wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/',
        'secure' => is_ssl(), 'httponly' => false, 'samesite' => 'Lax',
    ));
});

function vpn_email_popup_enabled() {
    return !is_admin() && !is_feed() && !is_404() && !is_preview()
        && !is_page(array('privacy-policy', 'terms-and-conditions', 'thank-you'))
        && !(function_exists('is_checkout') && is_checkout())
        && !(function_exists('is_account_page') && is_account_page())
        && function_exists('custom_box_handle_quote_form');
}

add_action('wp_enqueue_scripts', function () {
    if (!vpn_email_popup_enabled()) { return; }
    $base = plugin_dir_url(__FILE__) . 'vpn-email-quote-popup/';
    $dir = __DIR__ . '/vpn-email-quote-popup/';
    wp_enqueue_style('vpn-email-quote-popup', $base . 'popup.css', array(), filemtime($dir . 'popup.css'));
    wp_enqueue_script('vpn-email-quote-popup', $base . 'popup.js', array(), filemtime($dir . 'popup.js'), true);
    wp_localize_script('vpn-email-quote-popup', 'vpnEmailPopup', array(
        'scope' => vpn_email_popup_scope(),
        'cookiePath' => wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/',
        'tokenUrl' => admin_url('admin-ajax.php?action=vpn_email_popup_token'),
        'preview' => vpn_email_popup_preview_allowed() && isset($_GET['vpn_popup_preview']) && '1' === $_GET['vpn_popup_preview'],
    ));
});

// Refresh tokens immediately before sending: a cached page must not strand a lead.
function vpn_email_popup_token() {
    nocache_headers();
    if (!function_exists('custom_box_quote_form_timestamp_signature')) { wp_send_json_error(null, 503); }
    $started = time() - 10;
    wp_send_json_success(array('nonce' => wp_create_nonce('custom_box_quote_form'),
        'started' => $started, 'signature' => custom_box_quote_form_timestamp_signature($started, 'quote')));
}
add_action('wp_ajax_nopriv_vpn_email_popup_token', 'vpn_email_popup_token');
add_action('wp_ajax_vpn_email_popup_token', 'vpn_email_popup_token');

add_action('wp_footer', function () {
    if (!vpn_email_popup_enabled()) { return; }
    $privacy = get_privacy_policy_url() ?: home_url('/privacy-policy/');
    ?>
    <dialog id="vpn-email-popup" class="vep" aria-labelledby="vep-title" aria-describedby="vep-description">
        <div class="vep-layout">
            <div class="vep-photo" aria-hidden="true"><img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'vpn-email-quote-popup/packaging.webp'); ?>" alt="" width="480" height="720" loading="lazy"></div>
            <div class="vep-content">
                <button class="vep-close" type="button" aria-label="Close quote invitation" autofocus>×</button>
                <div class="vep-intro"><h2 id="vep-title">Need a packaging quote?</h2><p id="vep-description">Leave your email. Our team will contact you.</p></div>
                <form class="vep-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="custom_box_quote_form">
                    <input type="hidden" name="quote_source" value="website_email_popup">
                    <input type="hidden" name="response_format" value="email_popup_json">
                    <input type="hidden" name="form_location" value="sitewide_email_popup">
                    <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); custom_box_quote_form_anti_spam_fields('quote'); ?>
                    <label class="vep-email-label" for="vep-email">Email <input id="vep-email" name="email" type="email" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" maxlength="254" placeholder="you@company.com" required></label>
                    <details class="vep-details"><summary><span aria-hidden="true">＋</span> Add project details <span class="vep-chevron" aria-hidden="true">⌄</span></summary><label for="vep-message">What packaging do you need? <span>(optional)</span></label><textarea id="vep-message" name="message" rows="3" maxlength="3000" placeholder="Product, size, quantity and delivery country…"></textarea></details>
                    <p class="vep-status" role="status" aria-live="polite" hidden></p>
                    <button class="vep-submit" type="submit">Request a quote</button>
                    <p class="vep-contact-notice">By submitting, you agree to be contacted about your enquiry. <a href="<?php echo esc_url($privacy); ?>" target="_blank" rel="noopener">Privacy policy</a></p>
                </form>
                <button type="button" class="vep-not-now">Not now</button>
            </div>
        </div>
    </dialog>
    <?php
}, 90);
