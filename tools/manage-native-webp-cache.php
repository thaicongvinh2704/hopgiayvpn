<?php
/** CLI-only operational rollback/status. No secrets or configuration backups printed. */
if (PHP_SAPI !== 'cli' || !in_array($argv[1]??'', ['status','rollback','enable'], true)) {
    exit("Usage: php tools/manage-native-webp-cache.php status|rollback|enable\n");
}
require dirname(__DIR__) . '/wp-load.php';
if ($argv[1]==='rollback') {
    if (!vpn_native_webp_cache_restore()) { fwrite(STDERR,"Unable to restore all previous rules.\n"); exit(1); }
} elseif ($argv[1]==='enable') {
    update_option('vpn_native_webp_cache_disabled', false, false);
    delete_option('vpn_native_webp_cache_revision');
    // The next normal WP request loads Converter settings with the active policy.
}
echo json_encode(['disabled'=>(bool)get_option('vpn_native_webp_cache_disabled'), 'revision'=>get_option('vpn_native_webp_cache_revision')]);
