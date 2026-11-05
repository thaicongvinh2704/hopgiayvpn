<?php
/** Matcher tests are deliberately independent of database availability. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('ABSPATH', dirname(__DIR__) . '/');
function add_action(...$args) {}
require dirname(__DIR__) . '/wp-content/mu-plugins/vpn-bing-query-guard.php';
$count = 0;
$assert = static function ($actual, $expected, $case) use (&$count) {
    ++$count;
    if ($actual !== $expected) { throw new RuntimeException('Unexpected classification: ' . $case); }
};
foreach (vpn_bing_query_guard_data()['queries'] as $query) {
    foreach (['/', '/hopgiayvpn/'] as $home) {
        foreach ([$query, rawurlencode($query), rawurlencode(rawurlencode($query)), rawurlencode(rawurlencode(rawurlencode($query)))] as $encoded) {
            $assert(vpn_bing_query_guard_matches($home . '?' . $encoded, $home), true, $encoded);
        }
        $assert(vpn_bing_query_guard_matches($home . 'index.php?' . rawurlencode($query), $home), true, 'index.php');
        $assert(vpn_bing_query_guard_matches($home . '?gclid=test&utm_source=google&' . rawurlencode($query) . '=', $home), true, 'spam with campaign tracking');
        foreach (['s', 'gclid', 'gbraid', 'wbraid', 'utm_source', 'utm_campaign', 'redirect_to'] as $key) {
            $assert(vpn_bing_query_guard_matches($home . '?' . $key . '=' . rawurlencode($query), $home), false, 'legitimate parameter value: ' . $key);
        }
    }
}
$safe = ['/', '/?s=188bet', '/?s=1xslots-casino', '/?s=lixi88', '/?s=paper+bags&post_type=product',
    '/?gclid=test&gbraid=test&wbraid=test&utm_source=google&utm_medium=cpc&utm_campaign=paper-bags',
    '/?add-to-cart=123&quantity=2', '/?remove_item=abc&_wpnonce=test', '/?wc-ajax=get_refreshed_fragments',
    '/?wc-api=payment_callback', '/?rest_route=/wp/v2/posts', '/?community/new-packaging-project',
    '/?research/2025-06-18/unreviewed-slug.htm', '/?case/2026-11-05/not-reviewed',
    '/?service/2025-06-19/unreviewed-product.html', '/cart/?coupon_code=SAVE10', '/checkout/?key=order_key',
    '/wp-admin/?research/2025-06-19/1xslots-casino.html', '/wp-json/?research/2025-06-19/1xslots-casino.html',
    '/product/custom-white-kraft-mailer-boxes/?research/2025-06-19/1xslots-casino.html'];
foreach ($safe as $uri) { $assert(vpn_bing_query_guard_matches($uri), false, $uri); }
$result = ['checks' => $count, 'verified_entries' => count(vpn_bing_query_guard_data()['queries']), 'errors' => []];
$output_dir = dirname(__DIR__) . '/outputs/bing-query-spam-20261105';
if (!is_dir($output_dir) && !mkdir($output_dir, 0755, true) && !is_dir($output_dir)) {
    throw new RuntimeException('Cannot create verification output directory.');
}
file_put_contents($output_dir . '/matcher-verification.json', json_encode($result, JSON_PRETTY_PRINT));
echo json_encode($result), PHP_EOL;
