<?php
/**
 * Plugin Name: VPN Approved Category Studio Images
 * Description: Applies the approved 38-image category set on local and hosting after deployment.
 */
defined('ABSPATH') || exit;

function vpn_category_studio_manifest() {
    static $manifest = null;
    if (null === $manifest) {
        $file = __DIR__ . '/vpn-category-studio/manifest.json';
        $decoded = is_readable($file) ? json_decode(file_get_contents($file), true) : null;
        $manifest = is_array($decoded) && !empty($decoded['images']) ? $decoded : array();
    }
    return $manifest;
}

add_filter('custom_box_home_packaging_category_groups', function ($groups) {
    $manifest = vpn_category_studio_manifest();
    $images = isset($manifest['images']) ? $manifest['images'] : array();
    $directory = get_template_directory() . '/assets/images/category-studio-20261010/';
    $uri = get_template_directory_uri() . '/assets/images/category-studio-20261010/';
    foreach ($groups as &$group) {
        foreach ($group['items'] as &$item) {
            $slug = sanitize_title($item[1]);
            $filename = isset($images[$slug]['file']) ? $images[$slug]['file'] : '';
            if ($filename && basename($filename) === $filename && is_file($directory . $filename)) {
                $item[2] = $uri . $filename;
            }
        }
        unset($item);
    }
    unset($group);
    return $groups;
});

// Runs once per approved release on the first uncached WordPress request.
// LiteSpeed API: https://docs.litespeedtech.com/lscache/lscwp/api/#litespeed_purge_all
add_action('wp_loaded', function () {
    $manifest = vpn_category_studio_manifest();
    $release = isset($manifest['release']) ? $manifest['release'] : '';
    if (!$release || get_option('vpn_category_studio_cache_release') === $release
        || !function_exists('custom_box_get_home_packaging_category_groups')
        || false === has_action('litespeed_purge_all')) {
        return;
    }
    $directory = get_template_directory() . '/assets/images/category-studio-20261010/';
    foreach ($manifest['images'] as $image) {
        if (basename($image['file']) !== $image['file'] || !is_file($directory . $image['file'])) {
            return;
        }
    }
    do_action('litespeed_purge_all', 'VPN approved category studio images: ' . $release);
    update_option('vpn_category_studio_cache_release', $release, false);
});
