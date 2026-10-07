<?php
/** Keep recurring content maintenance out of every admin navigation/save. */
defined('ABSPATH') || exit;

function custom_box_admin_task_due(string $task, string $source, int $interval = 900): bool {
    if (!is_admin() || !current_user_can('manage_options') || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return false;
    }
    // Preserve the existing explicit deployment/repair trigger.
    if (isset($_GET['custom_box_run_post_syncs']) && '1' === sanitize_text_field(wp_unslash($_GET['custom_box_run_post_syncs']))) {
        return true;
    }
    $revision = is_file($source) ? (string) filemtime($source) . ':' . filesize($source) : '';
    $key = 'custom_box_admin_task_' . md5($task . ':' . $revision);
    if (get_transient($key)) {
        return false;
    }
    // Failed/missing-content repairs also back off, instead of retrying each click.
    set_transient($key, 1, max(60, $interval));
    return true;
}

function custom_box_repair_self_parent_posts_once(): void {
    if (!is_admin() || !current_user_can('manage_options') || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }
    $version = '2026-10-07.1';
    if ($version === get_option('custom_box_self_parent_repair_version')) {
        return;
    }
    if (!custom_box_admin_task_due(__FUNCTION__, __FILE__, 5 * MINUTE_IN_SECONDS)) {
        return;
    }
    global $wpdb;
    $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE ID=post_parent AND post_parent>0");
    if ($wpdb->last_error) {
        return;
    }
    if ($ids) {
        $repaired = $wpdb->query("UPDATE {$wpdb->posts} SET post_parent=0 WHERE ID=post_parent AND post_parent>0");
        if (false === $repaired) {
            return;
        }
        foreach ($ids as $id) {
            clean_post_cache((int) $id);
        }
    }
    update_option('custom_box_self_parent_repair_version', $version, false);
}
add_action('admin_init', 'custom_box_repair_self_parent_posts_once', 0);

function custom_box_prevent_self_parent_post(array $data, array $postarr): array {
    $id = isset($postarr['ID']) ? (int) $postarr['ID'] : 0;
    if ($id > 0 && (int) ($data['post_parent'] ?? 0) === $id) {
        $data['post_parent'] = 0;
    }
    return $data;
}
add_filter('wp_insert_post_data', 'custom_box_prevent_self_parent_post', 10, 2);
