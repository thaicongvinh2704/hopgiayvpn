<?php
/**
 * Must-use loader for the supplement packaging layout post sync.
 *
 * This keeps the deploy-triggered post import available even if the active
 * theme bootstrap is cached by the hosting runtime after a Git pull. Completed
 * syncs are left to the theme's central loader and are not parsed on every
 * frontend or Admin request.
 */

function custom_box_maybe_load_supplement_post_sync(): void
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

    $sync_file = WP_CONTENT_DIR . '/themes/custom-box-theme/inc/how-to-design-supplement-packaging-layout-post-sync.php';
    $sync_version = '2026-07-17-v1';
    $sync_option = 'custom_box_supplement_packaging_layout_sync_version';
    $force_sync = isset($_GET['custom_box_run_post_syncs'])
        && '1' === sanitize_text_field(wp_unslash($_GET['custom_box_run_post_syncs']));
    $sync_pending = $sync_version !== get_option($sync_option);

    if (($force_sync || $sync_pending) && file_exists($sync_file)) {
        require_once $sync_file;
    }
}

add_action('plugins_loaded', 'custom_box_maybe_load_supplement_post_sync', 1);
