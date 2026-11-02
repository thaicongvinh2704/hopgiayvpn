<?php
/** CLI bootstrap that keeps unrelated theme/MU content migrations out of local product work. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$GLOBALS['wp_filter']['after_setup_theme'][PHP_INT_MAX][] = ['function' => static function () {
    global $wp_filter;
    if (empty($wp_filter['init']) || !($wp_filter['init'] instanceof WP_Hook)) { return; }
    foreach ($wp_filter['init']->callbacks as $priority => $items) {
        foreach ($items as $item) {
            $cb = $item['function'];
            if (!($cb instanceof Closure) && !(is_string($cb) && function_exists($cb))) { continue; }
            $path = str_replace('\\', '/', (string) (new ReflectionFunction($cb))->getFileName());
            if (str_contains($path, '/mu-plugins/') || str_contains($path, '/themes/custom-box-theme/')) {
                remove_action('init', $cb, $priority);
            }
        }
    }
}, 'accepted_args' => 0];
require dirname(__DIR__) . '/wp-load.php';
if (!in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['localhost', '127.0.0.1'], true)
    || !in_array(DB_HOST, ['localhost', '127.0.0.1', 'localhost:3306', '127.0.0.1:3306'], true)
    || wp_get_environment_type() !== 'local') { throw new RuntimeException('Local environment required.'); }
