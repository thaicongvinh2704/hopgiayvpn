<?php
/**
 * Lightweight branded transition shown while an internal page is loading.
 */

defined('ABSPATH') || exit;

function custom_box_enqueue_page_transition_assets(): void
{
    $css_path = get_template_directory() . '/assets/css/page-transition.css';
    $js_path = get_template_directory() . '/assets/js/page-transition.js';

    wp_enqueue_style(
        'custom-box-page-transition',
        get_template_directory_uri() . '/assets/css/page-transition.css',
        array('main-style'),
        file_exists($css_path) ? (string) filemtime($css_path) : '1.0.0'
    );

    wp_enqueue_script(
        'custom-box-page-transition',
        get_template_directory_uri() . '/assets/js/page-transition.js',
        array(),
        file_exists($js_path) ? (string) filemtime($js_path) : '1.0.0',
        true
    );
    wp_script_add_data('custom-box-page-transition', 'defer', true);
}
add_action('wp_enqueue_scripts', 'custom_box_enqueue_page_transition_assets', 20);

/**
 * Prepare the first home-page loader before the page paints.
 *
 * The overlay is delayed so fast visits never see a flash of loading UI. An
 * inline safety timer also removes it even when the deferred JavaScript fails.
 * Session storage prevents it from repeating during the same browser tab.
 */
function custom_box_page_transition_initial_bootstrap(): void
{
    if (!is_front_page()) {
        return;
    }
    ?>
    <script id="vpn-initial-page-transition">
        (function () {
            var root = document.documentElement;
            var key = 'vpnInitialHomeLoaderSeenV1';
            var showDelay = 250;
            var hardLimit = 1500;

            function prepareInitialLoader() {
                root.classList.add('vpn-initial-page-loading-pending');

                window.vpnInitialLoaderShowTimer = window.setTimeout(function () {
                    if (!root.classList.contains('vpn-initial-page-loading-pending')) {
                        return;
                    }

                    root.classList.remove('vpn-initial-page-loading-pending');
                    root.classList.add('vpn-initial-page-loading');
                }, showDelay);

                window.vpnInitialLoaderSafetyTimer = window.setTimeout(function () {
                    root.classList.remove(
                        'vpn-initial-page-loading-pending',
                        'vpn-initial-page-loading'
                    );
                }, hardLimit);
            }

            try {
                var navigation = window.performance && window.performance.getEntriesByType
                    ? window.performance.getEntriesByType('navigation')[0]
                    : null;
                var isHistoryRestore = navigation && navigation.type === 'back_forward';

                if (!isHistoryRestore && !window.sessionStorage.getItem(key)) {
                    window.sessionStorage.setItem(key, '1');
                    prepareInitialLoader();
                }
            } catch (error) {
                prepareInitialLoader();
            }
        }());
    </script>
    <?php
}
add_action('wp_head', 'custom_box_page_transition_initial_bootstrap', 0);

function custom_box_render_page_transition(): void
{
    if (is_admin()) {
        return;
    }
    ?>
    <div class="vpn-page-transition" data-page-transition data-nosnippet hidden aria-hidden="true">
        <div class="vpn-page-transition__folds" aria-hidden="true">
            <span class="vpn-page-transition__fold vpn-page-transition__fold--left-field"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--left-top"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--left-shadow"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--left-blue"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--left-edge"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--right-field"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--right-top"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--right-light"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--right-shadow"></span>
            <span class="vpn-page-transition__fold vpn-page-transition__fold--right-edge"></span>
        </div>
        <div class="vpn-page-transition__content">
            <img class="vpn-page-transition__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-hop-giay-vpn-loader.webp'); ?>" width="711" height="567" alt="VPN" decoding="async">
            <p class="vpn-page-transition__title">VPN Paper Box Manufacturer</p>
            <div class="vpn-page-transition__progress" aria-hidden="true"><span></span></div>
            <p class="vpn-page-transition__loading" role="status" aria-live="polite" data-transition-status data-loading-text="<?php esc_attr_e('LOADING...', 'custom-box-theme'); ?>"></p>
            <p class="vpn-page-transition__tagline"><span aria-hidden="true"></span>PACKAGING IDEAS BROUGHT TO LIFE<span aria-hidden="true"></span></p>
        </div>
        <button class="vpn-page-transition__dismiss" type="button" data-transition-dismiss hidden aria-label="<?php esc_attr_e('Hide loading screen', 'custom-box-theme'); ?>">&times;</button>
    </div>
    <?php
}
add_action('wp_body_open', 'custom_box_render_page_transition', 1);
