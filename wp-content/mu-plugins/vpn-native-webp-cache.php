<?php
/**
 * Plugin Name: VPN Native WebP Cache
 * Description: Serve existing WebP files as WebP, so public catalog images can be cached without Accept negotiation.
 */
defined('ABSPATH') || exit;

function vpn_native_webp_cache_enabled(): bool {
    return !is_multisite() && !get_option('vpn_native_webp_cache_disabled', false);
}

/** Leave the stored Converter settings intact for a reversible rollout. */
function vpn_native_webp_cache_settings($settings) {
    if (!vpn_native_webp_cache_enabled() || !is_array($settings)) { return $settings; }
    $extensions = $settings['extensions'] ?? ['jpg', 'jpeg', 'png', 'webp'];
    $settings['extensions'] = array_values(array_diff((array) $extensions, ['webp']));
    return $settings;
}
add_filter('option_webpc_settings', 'vpn_native_webp_cache_settings', 20);
add_filter('default_option_webpc_settings', 'vpn_native_webp_cache_settings', 20);

/** Converter headers still protect negotiated JPG/PNG variants. */
function vpn_native_webp_cache_headers(string $rules): string {
    if (!vpn_native_webp_cache_enabled()) { return $rules; }
    return $rules . "\n# VPN Native WebP: explicit format, public dated media only.\n"
        . "<IfModule mod_setenvif.c>\n"
        . '  SetEnvIf Request_URI "/wp-content/uploads/[0-9]{4}/[0-9]{2}/.*[.]webp$" VPN_NATIVE_WEBP_CACHE=1' . "\n"
        . "</IfModule>\n<IfModule mod_headers.c>\n"
        . '  <FilesMatch "(?i)^(?!.*[.](jpg|jpeg|png|gif)[.]webp$).+[.]webp$">' . "\n"
        . '    Header set Cache-Control "public, max-age=604800" env=VPN_NATIVE_WEBP_CACHE' . "\n"
        . "  </FilesMatch>\n"
        // Internal rewrites may lose the parent request's environment flag.
        // Converted output filenames themselves identify the negotiated format.
        . '  <FilesMatch "(?i)[.](jpg|jpeg|png|gif)[.](webp|avif)$">' . "\n"
        . '    Header always set Cache-Control "private"' . "\n"
        . '    Header always set X-LiteSpeed-Cache-Control "no-cache"' . "\n"
        . '    Header merge Vary "Accept"' . "\n"
        . "  </FilesMatch>\n</IfModule>";
}
add_filter('webpc_htaccess_mod_headers', 'vpn_native_webp_cache_headers', 200);

/** Regenerate through the installed converter, once per rollout. No CDN API calls. */
function vpn_native_webp_cache_deploy(): void {
    $revision = '2026-11-04.2';
    if (!vpn_native_webp_cache_enabled() || get_option('vpn_native_webp_cache_revision') === $revision
        || get_transient('vpn_native_webp_cache_retry') || !class_exists('WebpConverter\\Loader\\LoaderIntegrator')
        || !has_action('webpc_refresh_loader')) { return; }
    if (!in_array(PHP_SAPI, ['cli','cli-server'], true) && !preg_match('/Apache|LiteSpeed/i', $_SERVER['SERVER_SOFTWARE'] ?? '')) { return; }
    // Several uncached requests can arrive immediately after deployment. Only
    // one may rewrite the server files; a dropped DB connection releases this lock.
    global $wpdb;
    $lock = 'vpn_webp_cache_' . md5(ABSPATH . $wpdb->prefix);
    if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== '1') { return; }
    try {
        if (get_option('vpn_native_webp_cache_revision') !== $revision) {
            vpn_native_webp_cache_apply_locked($revision);
        }
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}

/** @internal Called only while holding the rollout lock. */
function vpn_native_webp_cache_apply_locked(string $revision): void {
    $uploads = wp_upload_dir();
    $directories = [WP_CONTENT_DIR, $uploads['basedir'], WP_CONTENT_DIR . '/uploads-webpc'];
    $backup = [];
    foreach ($directories as $dir) {
        if (!is_dir($dir) || !is_writable($dir)) { set_transient('vpn_native_webp_cache_retry', 1, 300); return; }
        $path = $dir . '/.htaccess';
        if (is_file($path) && !is_writable($path)) { set_transient('vpn_native_webp_cache_retry', 1, 300); return; }
        $backup[$path] = is_file($path) ? file_get_contents($path) : null;
    }
    // Keep server configuration backups in a non-public WP option, never an upload.
    if (!get_option('vpn_native_webp_cache_backup')) { add_option('vpn_native_webp_cache_backup', $backup, '', false); }
    try {
        global $wp_filter;
        $loaders = [];
        foreach ($wp_filter['webpc_refresh_loader']->callbacks as $callbacks) {
            foreach ($callbacks as $callback) {
                $function = $callback['function'];
                if (is_array($function) && $function[0] instanceof WebpConverter\Loader\LoaderIntegrator) {
                    $loaders[spl_object_id($function[0])] = $function[0];
                }
            }
        }
        foreach ($loaders as $loader) { $loader->load_admin_actions(); }
        do_action('webpc_refresh_loader', true, false);
        $parent = (string) file_get_contents(WP_CONTENT_DIR . '/.htaccess');
        $upload_rules = (string) file_get_contents($uploads['basedir'] . '/.htaccess');
        if (!str_contains($parent, '# VPN Native WebP:') || str_contains($upload_rules, '.webp.avif')) {
            throw new RuntimeException('Native WebP rules were not generated correctly.');
        }
        update_option('vpn_native_webp_cache_revision', $revision, false);
    } catch (Throwable $error) {
        foreach ($backup as $path => $contents) {
            if ($contents !== null) { file_put_contents($path, $contents); }
        }
        set_transient('vpn_native_webp_cache_retry', 1, 300);
        error_log('VPN native WebP cache: previous server rules restored.');
    }
}
add_action('wp_loaded', 'vpn_native_webp_cache_deploy', 90);

/** Explicit CLI rollback; the stored converter settings have never been changed. */
function vpn_native_webp_cache_restore(): bool {
    $backup = get_option('vpn_native_webp_cache_backup');
    if (!is_array($backup)) { return false; }
    update_option('vpn_native_webp_cache_disabled', true, false);
    foreach ($backup as $path => $contents) {
        if ($contents !== null && file_put_contents($path, $contents) === false) { return false; }
    }
    return true;
}
