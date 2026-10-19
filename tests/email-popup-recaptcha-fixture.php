<?php
/** CLI-only WordPress adapter: render the actual popup/session/legacy handler without a database. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
define('ABSPATH', dirname(__DIR__) . '/');
define('DAY_IN_SECONDS', 86400);
define('CUSTOM_BOX_RECAPTCHA_SITE_KEY', ($argv[2] ?? '') === 'off' ? '' : 'test-public-key');
define('CUSTOM_BOX_RECAPTCHA_SECRET_KEY', 'test-secret-never-exposed');
$actions = array();
function add_action($name, $callback, $priority = 10, $args = 1) { global $actions; $actions[$name][$priority][] = $callback; }
function add_filter() {}
function apply_filters($name, $value, ...$args) { return $name === 'custom_box_quote_form_recaptcha_temporarily_disabled' ? (($GLOBALS['argv'][2] ?? '') === 'suppressed') : $value; }
function home_url($path = '') { return 'https://popup.test' . $path; }
function admin_url($path = '') { return home_url('/wp-admin/' . $path); }
function plugin_dir_url($path) { return home_url('/wp-content/mu-plugins/'); }
function wp_get_environment_type() { return 'production'; }
function is_admin() { return false; }
function is_feed() { return false; }
function is_404() { return false; }
function is_preview() { return false; }
function is_page($pages) { return false; }
function is_page_template($template) { return false; }
function is_front_page() { return true; }
function is_singular($type) { return false; }
function get_privacy_policy_url() { return home_url('/privacy-policy/'); }
function wp_nonce_field() { echo '<input name="custom_box_quote_nonce" type="hidden" value="cached-nonce">'; }
function wp_salt($type) { return 'fixture-salt'; }
function absint($value) { return abs((int) $value); }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower($value)); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES); }
function esc_url($value) { return esc_attr($value); }
function wp_json_encode($value) { return json_encode($value); }
function wp_create_nonce($action) { return 'fresh-nonce'; }
function nocache_headers() {}
function wp_send_json_success($value) { echo json_encode(array('success' => true, 'data' => $value)); exit; }
function wp_send_json_error($value, $status) { exit(1); }
function add_query_arg($args, $url) { return $url . '?' . http_build_query($args); }
require ABSPATH . 'wp-content/themes/custom-box-theme/inc/quote-form-handler.php';
require ABSPATH . 'wp-content/mu-plugins/vpn-email-quote-popup.php';
if (($argv[1] ?? '') === 'session') { vpn_email_popup_token(); }
echo '<!doctype html><html><head><title>Popup regression</title></head><body><h1>Custom paper bags</h1>';
custom_box_print_recaptcha_v3_submit_handler();
foreach ($actions['wp_footer'][90] as $callback) { $callback(); }
echo '<form id="other-quote"><input name="action" value="custom_box_quote_form" type="hidden"><button type="submit">Other quote</button></form>';
echo '<script>window.vpnEmailPopup={scope:"regression",cookiePath:"/",tokenUrl:"/session",preview:"1"};</script><script src="/popup.js"></script></body></html>';
