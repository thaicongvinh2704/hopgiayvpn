<?php
/**
 * Load the food packaging article sync once after deployment, from WordPress Admin.
 */

defined('ABSPATH') || exit;

function hgvn_maybe_load_food_packaging_design_post_sync(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    if (
        (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)
    ) {
        return;
    }

    $sync_file = WP_CONTENT_DIR . '/themes/custom-box-theme/inc/food-packaging-and-design-post-sync.php';
    $sync_version = '2026-10-03-v1';
    $sync_option = 'hgvn_food_packaging_design_sync_version';
    $force_sync = isset($_GET['custom_box_run_post_syncs'])
        && '1' === sanitize_text_field(wp_unslash($_GET['custom_box_run_post_syncs']));

    if (($force_sync || $sync_version !== get_option($sync_option)) && is_readable($sync_file)) {
        require_once $sync_file;
    }
}

add_action('plugins_loaded', 'hgvn_maybe_load_food_packaging_design_post_sync', 1);
