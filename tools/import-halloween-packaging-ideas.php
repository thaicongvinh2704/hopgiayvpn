<?php
/**
 * Run the Halloween packaging ideas sync locally.
 *
 * Usage: php tools/import-halloween-packaging-ideas.php [import|audit]
 */

if ('cli' !== PHP_SAPI) {
    exit("CLI only.\n");
}

require_once dirname(__DIR__) . '/wp-load.php';

$admins = get_users(array(
    'role' => 'administrator',
    'number' => 1,
    'fields' => 'ID',
));
if (!$admins) {
    fwrite(STDERR, "No administrator account is available for the sync.\n");
    exit(1);
}
wp_set_current_user((int) $admins[0]);

require_once get_template_directory() . '/inc/halloween-packaging-ideas-post-sync.php';

$mode = $argv[1] ?? 'import';
if (!in_array($mode, array('import', 'audit'), true)) {
    fwrite(STDERR, "Unknown mode: {$mode}\n");
    exit(1);
}

if ('import' === $mode) {
    custom_box_sync_halloween_packaging_ideas_post();
}

$report = custom_box_halloween_packaging_ideas_report();
echo wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
exit(!empty($report['complete']) ? 0 : 1);
