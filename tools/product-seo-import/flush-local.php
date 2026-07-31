<?php
/**
 * Flush local rewrite rules and object/application caches without requiring WP-CLI.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/hopgiayvpn/';
$_SERVER['HTTPS'] = 'off';

require dirname(__DIR__, 2) . '/wp-load.php';

if ('hopgiayvpnmoi' !== DB_NAME) {
    fwrite(STDERR, "Refusing unexpected database: " . DB_NAME . "\n");
    exit(2);
}

flush_rewrite_rules(false);
wp_cache_flush();

$cache_actions = array(
    'litespeed_purge_all',
    'rocket_clean_domain',
    'autoptimize_action_cachepurged',
);

foreach ($cache_actions as $cache_action) {
    do_action($cache_action);
}

echo json_encode(
    array(
        'database' => DB_NAME,
        'rewrite_rules_flushed' => true,
        'object_cache_flushed' => true,
        'application_cache_hooks' => $cache_actions,
        'timestamp_utc' => gmdate('c'),
    ),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
