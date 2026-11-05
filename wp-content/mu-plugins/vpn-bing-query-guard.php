<?php
/**
 * Plugin Name: VPN Verified Bing Query URL Guard
 * Description: Return an immediate 404 for exact spam query paths verified in Bing; preserve normal site parameters.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;

/** WordPress normalizes dots in query keys to underscores; compare that equivalent form too. */
function vpn_bing_query_guard_key(string $value): string {
    for ($i = 0; $i < 3; ++$i) {
        $decoded = rawurldecode($value);
        if ($decoded === $value) { break; }
        $value = $decoded;
    }
    if (substr($value, -1) === '=') { $value = substr($value, 0, -1); }
    return (string) preg_replace('/\.(s?html?|pdf)$/i', '_$1', $value);
}

function vpn_bing_query_guard_data(): array {
    static $data;
    if (null === $data) {
        $file = __DIR__ . '/vpn-bing-query-guard/verified-queries.json';
        $data = is_readable($file) ? json_decode((string) file_get_contents($file), true) : [];
        $data = is_array($data) ? $data : [];
    }
    return $data;
}

/** Only root/index.php and bare query keys on the reviewed denylist are rejected. */
function vpn_bing_query_guard_matches(string $request_uri, string $home_path = '/'): bool {
    $parts = parse_url($request_uri);
    if (!is_array($parts) || empty($parts['query'])) { return false; }
    $base = rtrim($home_path, '/');
    if (!in_array($parts['path'] ?? '/', [$base ?: '/', $base . '/', $base . '/index.php'], true)) { return false; }
    // Avoid reading the manifest for ordinary Ads/search/cart query strings.
    if (!preg_match('~(?:/|%2f|%252f|%25252f)2025-\d{2}-\d{2}(?:/|%2f|%252f|%25252f)~i', $parts['query'])) { return false; }
    static $keys;
    if (null === $keys) {
        $keys = [];
        foreach (vpn_bing_query_guard_data()['queries'] ?? [] as $query) {
            $keys[vpn_bing_query_guard_key($query)] = true;
        }
    }
    // Whole-query match also covers the few reviewed spam slugs containing a literal '&'.
    $candidates = array_merge([$parts['query']], explode('&', $parts['query']));
    foreach ($candidates as $candidate) {
        $candidate = vpn_bing_query_guard_key($candidate);
        if (substr($candidate, -1) === '=') { $candidate = substr($candidate, 0, -1); }
        // Never inspect parameter values: s=..., utm_*=..., gclid=..., cart data, etc.
        if (strpos($candidate, '=') === false && isset($keys[$candidate])) { return true; }
    }
    return false;
}

/** Stop before normal plugins, theme redirects, the homepage query and template rendering. */
function vpn_bing_query_guard_reject(): void {
    if (PHP_SAPI === 'cli' || is_admin() || wp_doing_ajax() || wp_doing_cron()
        || (defined('REST_REQUEST') && REST_REQUEST)) { return; }
    $home_path = (string) parse_url(home_url('/'), PHP_URL_PATH);
    if (!vpn_bing_query_guard_matches((string) ($_SERVER['REQUEST_URI'] ?? ''), $home_path)) { return; }
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    header_remove('Location');
    status_header(404);
    nocache_headers();
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0', true);
    header('X-LiteSpeed-Cache-Control: no-cache', true);
    header('X-Robots-Tag: noindex, follow', true);
    header('Content-Type: text/html; charset=UTF-8', true);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>404 - Page not found</title><main><h1>Page not found</h1><p>The requested page does not exist.</p></main></html>';
    }
    exit;
}
add_action('muplugins_loaded', 'vpn_bing_query_guard_reject', 0);

// Supersede the older, broader theme-only rule if present. Normal requests are unaffected.
add_action('after_setup_theme', static function () {
    remove_action('wp', 'custom_box_reject_legacy_spam_query_request', 0);
}, 0);
