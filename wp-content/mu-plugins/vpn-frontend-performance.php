<?php
/**
 * Plugin Name: VPN Frontend Performance
 * Description: Explicit-format editorial image caching and dependency-aware deferred scripts. No CSS or layout changes.
 */
defined('ABSPATH') || exit;

function vpn_performance_media_manifest(): array {
    static $manifest;
    if (isset($manifest)) { return $manifest; }
    $path = get_template_directory() . '/assets/images/performance-20261103/manifest.json';
    $manifest = is_readable($path) ? json_decode(file_get_contents($path), true) : [];
    return is_array($manifest) ? $manifest : ($manifest = []);
}

function vpn_performance_public_image_url(string $url): string {
    static $verified = [];
    $parts = wp_parse_url($url);
    if (!$parts || !empty($parts['query']) || !empty($parts['fragment'])) { return $url; }
    $uploads = wp_upload_dir();
    $allowed_hosts = array_filter([wp_parse_url($uploads['baseurl'], PHP_URL_HOST), wp_parse_url(home_url(), PHP_URL_HOST), 'hopgiayvpn.com', 'www.hopgiayvpn.com']);
    if (!empty($parts['host']) && !in_array(strtolower($parts['host']), $allowed_hosts, true)) { return $url; }
    $marker = '/wp-content/uploads/';
    $pos = strpos($parts['path'] ?? '', $marker);
    if ($pos === false) { return $url; }
    $file = rawurldecode(substr($parts['path'], $pos + strlen($marker)));
    $manifest = vpn_performance_media_manifest();
    $mapped = $manifest['featured'][$file] ?? '';
    if (!$mapped || !is_file(get_template_directory() . '/assets/images/performance-20261103/' . $mapped)) { return $url; }
    // A hosting image may have been replaced since the local build. Never
    // substitute a different photograph merely because its filename matches.
    if (!array_key_exists($file, $verified)) {
        $original = $uploads['basedir'] . '/' . $file;
        $verified[$file] = is_file($original) && hash_equals(pathinfo($mapped, PATHINFO_FILENAME), hash_file('sha256', $original));
    }
    if (!$verified[$file]) { return $url; }
    return get_template_directory_uri() . '/assets/images/performance-20261103/' . $mapped;
}

/** Public blog featured images and the paper landing's allowlisted catalog photos. */
function vpn_performance_featured_image_attributes(array $attr, $attachment, $size): array {
    if (is_admin() || is_preview() || post_password_required()) { return $attr; }
    $paper_page = function_exists('custom_box_is_custom_paper_bags_manufacturer_landing') && custom_box_is_custom_paper_bags_manufacturer_landing();
    $post_id = get_queried_object_id();
    if (!$paper_page && (!is_singular('post') || get_post_status($post_id) !== 'publish' || (int) get_post_thumbnail_id($post_id) !== (int) $attachment->ID)) { return $attr; }
    if (!empty($attr['src'])) { $attr['src'] = vpn_performance_public_image_url($attr['src']); }
    if (!empty($attr['srcset'])) {
        $attr['srcset'] = preg_replace_callback('/(^|,\s*)(\S+)(\s+\d+(?:w|x))/', static function ($match) {
            return $match[1] . vpn_performance_public_image_url($match[2]) . $match[3];
        }, $attr['srcset']);
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'vpn_performance_featured_image_attributes', 30, 3);

/** Responsive sources, without changing the original link, ratio or CSS crop. */
function vpn_performance_workshop_attributes(string $file, string $sizes): string {
    $manifest = vpn_performance_media_manifest();
    $sources = [];
    foreach (($manifest['workshop'][$file] ?? []) as $width => $name) {
        if (is_file(get_template_directory() . '/assets/images/performance-20261103/' . $name)) {
            $sources[] = get_template_directory_uri() . '/assets/images/performance-20261103/' . $name . ' ' . (int) $width . 'w';
        }
    }
    if (!$sources) { return ''; }
    $sources[] = get_template_directory_uri() . '/assets/images/supplier-factory-20261015/' . $file . ' 1280w';
    return ' srcset="' . esc_attr(implode(', ', $sources)) . '" sizes="' . esc_attr($sizes) . '"';
}

/** Use WordPress' eligibility calculation, rather than forcing defer onto tags. */
function vpn_performance_defer_frontend_scripts(): void {
    if (is_admin()) { return; }
    foreach (['main-js', 'custom-paper-bags-script', 'paper-bag-ads-script', 'vpn-chat-widget', 'vpn-chat-launcher', 'vpn-email-quote-popup'] as $handle) {
        if (wp_script_is($handle, 'registered')) { wp_script_add_data($handle, 'strategy', 'defer'); }
    }
}
add_action('wp_enqueue_scripts', 'vpn_performance_defer_frontend_scripts', 1000);
