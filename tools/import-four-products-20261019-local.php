<?php
/** Fixed CLI-only import of four owner-supplied products into the local site. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (!extension_loaded('gd') || !gd_info()['WebP Support']) { throw new RuntimeException('Run PHP with -d extension=gd for WebP thumbnails.'); }
$root = dirname(__DIR__);
// Keep historical frontend migrations from changing unrelated content in this CLI run.
$GLOBALS['wp_filter']['after_setup_theme'][999][] = ['function' => static function () {
    global $wp_filter;
    if (empty($wp_filter['init']) || !($wp_filter['init'] instanceof WP_Hook)) { return; }
    foreach ($wp_filter['init']->callbacks as $priority => $items) {
        foreach ($items as $item) {
            $callback = $item['function'];
            if (!($callback instanceof Closure) && !(is_string($callback) && function_exists($callback))) { continue; }
            $file = str_replace('\\', '/', (string) (new ReflectionFunction($callback))->getFileName());
            if (str_contains($file, '/mu-plugins/') && preg_match('~/(vpn-(?:.*-import|.*-deploy|paper-bag-phone-20261016))\.php$~', $file)) {
                remove_action('init', $callback, $priority);
            }
        }
    }
}, 'accepted_args' => 0];
require $root . '/wp-load.php';
if (!in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['localhost', '127.0.0.1'], true)
    || DB_NAME !== 'hopgiayvpnmoi'
    || !in_array(DB_HOST, ['localhost', '127.0.0.1', 'localhost:3306', '127.0.0.1:3306'], true)
    || wp_get_environment_type() !== 'local') { throw new RuntimeException('Local website/database required.'); }
if (!class_exists('WC_Product_Simple')) { throw new RuntimeException('WooCommerce is required.'); }

require_once $root . '/wp-content/themes/custom-box-theme/inc/four-products-20261019-support.php';
$manifest = vpn_four_20261019_source();
if (count($argv) > 1 && $argv[1] !== '--verify') { throw new RuntimeException('Supported option: --verify (read-only).'); }
if (in_array('--verify', $argv, true)) {
    echo wp_json_encode(vpn_four_20261019_verify($manifest['products'], VPN_FOUR_20261019_VERSION, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit;
}
$admins = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'fields' => 'ID']);
if (!$admins) { throw new RuntimeException('Local administrator unavailable.'); }
$previous = get_current_user_id();
try { wp_set_current_user((int) $admins[0]); echo wp_json_encode(['ids' => vpn_four_20261019_run()]) . PHP_EOL; }
finally { wp_set_current_user($previous); }
