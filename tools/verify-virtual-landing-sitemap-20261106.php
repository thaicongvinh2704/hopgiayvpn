<?php
/** Exercise the installed Rank Math generator and real WP filter engine, without a DB. */
namespace RankMath\Sitemap {
    class Cache {
        public static int $calls = 0;
        public static array $types = [];
        public static bool $fail = false;
        public static function invalidate_storage($type = null) {
            self::$calls++;
            self::$types[] = $type;
            if (self::$fail) { throw new \RuntimeException('Fixture cache failure'); }
        }
    }
}
namespace {
    if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
    define('ABSPATH', dirname(__DIR__) . '/');
    define('OBJECT', 'OBJECT');
    require ABSPATH . 'wp-includes/plugin.php';
    $GLOBALS['fixture_options'] = ['blog_public' => 1];
    $GLOBALS['fixture_pages'] = [];
    $GLOBALS['fixture_base'] = 'https://hopgiayvpn.com';
    $GLOBALS['fixture_purges'] = [];
    function get_option($name, $default = false) { return $GLOBALS['fixture_options'][$name] ?? $default; }
    function update_option($name, $value, $autoload = null) { $GLOBALS['fixture_options'][$name] = $value; }
    function get_template_directory() { return ABSPATH . 'wp-content/themes/custom-box-theme'; }
    function home_url($path = '') { return $GLOBALS['fixture_base'] . $path; }
    function get_page_by_path($slug, $output = OBJECT, $post_type = 'page') { return $GLOBALS['fixture_pages'][$slug] ?? null; }
    function esc_xml($value) { return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8'); }
    function custom_box_custom_paper_bags_url() { return home_url('/custom-paper-bags-manufacturer/'); }
    function custom_box_pizza_boxes_manufacturer_url() { return home_url('/custom-pizza-boxes-manufacturer/'); }
    require ABSPATH . 'wp-content/plugins/seo-by-rank-math/includes/traits/class-hooker.php';
    require ABSPATH . 'wp-content/plugins/seo-by-rank-math/includes/modules/sitemap/abstract-xml.php';
    require ABSPATH . 'wp-content/plugins/seo-by-rank-math/includes/modules/sitemap/class-generator.php';
    require ABSPATH . 'wp-content/mu-plugins/vpn-virtual-landing-sitemap.php';
    add_action('litespeed_purge_url', static function ($url) { $GLOBALS['fixture_purges'][] = $url; });
    $generator = (new \ReflectionClass(\RankMath\Sitemap\Generator::class))->newInstanceWithoutConstructor();
    $checks = 0;
    function verify($ok, $label) {
        global $checks; $checks++;
        if (!$ok) { throw new \RuntimeException($label); }
    }
    $xml = $generator->get_sitemap([], 'page', 1);
    $parsed = simplexml_load_string($xml);
    verify($parsed !== false, 'Generated sitemap must be valid XML');
    verify(count($parsed->url) === 2, 'Two virtual URLs must be present');
    verify(!str_contains($xml, '<lastmod>'), 'No fabricated lastmod');
    foreach (['custom-paper-bags-manufacturer', 'custom-pizza-boxes-manufacturer'] as $slug) {
        verify(substr_count($xml, '<loc>' . home_url('/' . $slug . '/') . '</loc>') === 1, 'Each URL once');
    }
    verify(vpn_virtual_landing_sitemap_content(vpn_virtual_landing_sitemap_content('')) === vpn_virtual_landing_sitemap_content(''), 'Duplicate-safe append');
    foreach (['page', 'post', 'product', 'product_cat'] as $type) {
        foreach ([1, 2, 3] as $page) {
            $out = $generator->get_sitemap([], $type, $page);
            verify(substr_count($out, '<loc>') === ($type === 'page' && $page === 1 ? 2 : 0), 'Only page sitemap first page');
        }
    }
    $existing = '<url><loc>https://hopgiayvpn.com/about/</loc></url>';
    $filter = static function ($content) use ($existing) { return $content . $existing; };
    add_filter('rank_math/sitemap/page_content', $filter, 10);
    verify(str_contains($generator->get_sitemap([], 'page', 1), $existing), 'Other sitemap entries preserved');
    remove_filter('rank_math/sitemap/page_content', $filter, 10);
    foreach (['publish', 'draft', 'private', 'trash'] as $status) {
        $GLOBALS['fixture_pages']['custom-paper-bags-manufacturer'] = (object) ['post_status' => $status];
        verify(count(vpn_virtual_landing_sitemap_urls()) === 1, 'Real page settings belong to normal provider: ' . $status);
    }
    $GLOBALS['fixture_pages'] = [];
    $GLOBALS['fixture_options']['blog_public'] = 0;
    verify(vpn_virtual_landing_sitemap_urls() === [], 'Local/private site stays out of sitemap');
    vpn_virtual_landing_sitemap_rollout();
    verify(\RankMath\Sitemap\Cache::$calls === 0, 'No rollout on private site');
    $GLOBALS['fixture_options']['blog_public'] = 1;
    $GLOBALS['fixture_base'] = 'http://localhost/hopgiayvpn';
    verify(str_contains($generator->get_sitemap([], 'page', 1), 'http://localhost/hopgiayvpn/custom-paper-bags-manufacturer/'), 'Subdirectory-aware URLs');
    $GLOBALS['fixture_base'] = 'https://hopgiayvpn.com';
    \RankMath\Sitemap\Cache::$fail = true;
    vpn_virtual_landing_sitemap_rollout();
    verify(!get_option('vpn_virtual_landing_sitemap_revision'), 'Failure not marked applied');
    \RankMath\Sitemap\Cache::$fail = false;
    vpn_virtual_landing_sitemap_rollout();
    verify(get_option('vpn_virtual_landing_sitemap_revision') === '2026-11-06.1', 'Successful rollout recorded');
    verify($GLOBALS['fixture_purges'] === [home_url('/page-sitemap.xml'), home_url('/sitemap_index.xml')], 'Only sitemap URLs purged');
    verify(array_unique(\RankMath\Sitemap\Cache::$types) === ['page'], 'Only Rank Math page cache invalidated');
    $calls = \RankMath\Sitemap\Cache::$calls;
    vpn_virtual_landing_sitemap_rollout();
    verify(\RankMath\Sitemap\Cache::$calls === $calls, 'No repeat cache invalidation');
    $out = ABSPATH . 'outputs/seo-foundation-20261106/';
    file_put_contents($out . 'local-page-sitemap-fixture.xml', $xml);
    $result = ['checks' => $checks, 'passed' => true, 'mode' => 'Installed Rank Math generator + WordPress hooks; DB-free fixture, not local HTTP or hosting deployment'];
    file_put_contents($out . 'sitemap-verification.json', json_encode($result, JSON_PRETTY_PRINT));
    echo json_encode($result), PHP_EOL;
}
