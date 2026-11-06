<?php
/**
 * Plugin Name: VPN Virtual Landing Sitemap
 * Description: Include the two public template-backed landing pages in Rank Math's page sitemap.
 */
defined('ABSPATH') || exit;

/** These routes have virtual WP_Post objects, so the normal DB provider misses them. */
function vpn_virtual_landing_sitemap_urls(): array {
    if (!get_option('blog_public')) { return []; }
    $routes = [
        'custom-paper-bags-manufacturer' => ['custom_box_custom_paper_bags_url', 'page-custom-paper-bags-manufacturer.php'],
        'custom-pizza-boxes-manufacturer' => ['custom_box_pizza_boxes_manufacturer_url', 'page-pizza-boxes-manufacturer.php'],
    ];
    $urls = [];
    foreach ($routes as $slug => [$callback, $template]) {
        if (!function_exists($callback) || !is_readable(get_template_directory() . '/' . $template)) { continue; }
        // A real page belongs to Rank Math's normal provider. Respect its status,
        // password, robots and exclusions instead of overriding editorial settings.
        if (get_page_by_path($slug, OBJECT, 'page')) { continue; }
        $url = $callback();
        if ($url === home_url('/' . $slug . '/')) { $urls[] = $url; }
    }
    return $urls;
}

function vpn_virtual_landing_sitemap_content(string $content): string {
    foreach (vpn_virtual_landing_sitemap_urls() as $url) {
        $loc = esc_xml($url);
        if (str_contains($content, '<loc>' . $loc . '</loc>')) { continue; }
        // No synthetic lastmod: request time is not the content's modification date.
        $content .= '<url><loc>' . $loc . '</loc></url>' . "\n";
    }
    return $content;
}
// Rank Math applies this hook only on page-sitemap.xml's first page.
add_filter('rank_math/sitemap/page_content', 'vpn_virtual_landing_sitemap_content', 90);

/** Refresh generated XML once after deployment; no frontend HTML or CDN rule changes. */
function vpn_virtual_landing_sitemap_rollout(): void {
    $revision = '2026-11-06.1';
    if (get_option('vpn_virtual_landing_sitemap_revision') === $revision
        || !class_exists('RankMath\\Sitemap\\Cache') || !vpn_virtual_landing_sitemap_urls()) { return; }
    try {
        \RankMath\Sitemap\Cache::invalidate_storage('page');
        do_action('litespeed_purge_url', home_url('/page-sitemap.xml'));
        do_action('litespeed_purge_url', home_url('/sitemap_index.xml'));
        update_option('vpn_virtual_landing_sitemap_revision', $revision, false);
    } catch (Throwable $error) {
        error_log('VPN virtual landing sitemap cache refresh failed; retrying next request.');
    }
}
add_action('wp_loaded', 'vpn_virtual_landing_sitemap_rollout', 95);
