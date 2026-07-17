<?php
/**
 * Must-use loader for the supplement packaging layout post sync.
 *
 * This keeps the deploy-triggered post import available even if the active
 * theme bootstrap is cached by the hosting runtime after a Git pull.
 */

$custom_box_supplement_sync_file = WP_CONTENT_DIR . '/themes/custom-box-theme/inc/how-to-design-supplement-packaging-layout-post-sync.php';

if (file_exists($custom_box_supplement_sync_file)) {
    require_once $custom_box_supplement_sync_file;
}
