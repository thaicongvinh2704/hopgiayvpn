<?php
/** Real WordPress/WooCommerce deployment lifecycle in isolated test tables on port 3311. */
if (PHP_SAPI !== 'cli') { exit; }
if (in_array('--cleanup', $argv, true)) {
    $db = new mysqli('127.0.0.1', 'root', '', 'vpn_chat_test', 3311);
    foreach ($db->query("SHOW TABLES LIKE 'vfp\\_%'")->fetch_all() as [$table]) {
        if (!str_starts_with($table, 'vfp_') || !preg_match('/^[a-z0-9_]+$/', $table)) { throw new RuntimeException('Unsafe test table'); }
        $db->query('DROP TABLE `' . $table . '`');
    }
    $db->close(); echo "Isolated vfp_ fixture tables removed.\n"; exit;
}
$root = dirname(__DIR__);
define('ABSPATH', __DIR__ . '/live-chat/runtime/wp/');
define('DB_NAME', 'vpn_chat_test'); define('DB_USER', 'root'); define('DB_PASSWORD', ''); define('DB_HOST', '127.0.0.1:3311');
define('DB_CHARSET', 'utf8mb4'); define('DB_COLLATE', '');
define('WP_HOME', 'http://127.0.0.1:8091/four-product-test'); define('WP_SITEURL', WP_HOME);
define('WP_ENVIRONMENT_TYPE', 'local'); define('DISABLE_WP_CRON', true); define('WP_DEBUG', false);
define('WP_ADMIN', true); define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
foreach (['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'] as $key) { define($key, 'four-product-test-only-' . $key); }
$table_prefix = 'vfp_';
$install = in_array('--install', $argv, true);
if ($install) { define('WP_INSTALLING', true); }
require ABSPATH . 'wp-settings.php';
if ($wpdb->prefix !== 'vfp_' || DB_HOST !== '127.0.0.1:3311' || DB_NAME !== 'vpn_chat_test') { throw new RuntimeException('Wrong test database'); }
if ($install) {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    if (!is_blog_installed()) { wp_install('Four product deployment test', 'four-product-admin', 'admin@example.invalid', false, '', 'local-test-only-password'); }
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    foreach (['woocommerce/woocommerce.php', 'seo-by-rank-math/rank-math.php'] as $plugin) {
        $result = activate_plugin($plugin);
        if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    }
    update_option('permalink_structure', '/%postname%/');
    update_option('rank_math_registration_skip', true); // Use the free plugin's normal skip-setup state, without any account/API.
    update_option('woocommerce_permalinks', ['product_base' => '/product', 'category_base' => '/products']);
    echo "Isolated test installation ready.\n"; exit;
}
require_once $root . '/wp-content/themes/custom-box-theme/inc/four-products-20261019-product-sync.php';
require_once $root . '/wp-content/themes/custom-box-theme/inc/post-sync-loader.php';
$checks = [];
function verify_four_test(bool $value, string $label): void { global $checks; if (!$value) { throw new RuntimeException($label); } $checks[] = $label; }
$admin = get_user_by('login', 'four-product-admin'); wp_set_current_user($admin->ID);
require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
require_once ABSPATH . 'wp-admin/includes/screen.php';
$GLOBALS['current_screen'] = WP_Screen::get('dashboard');
$rows = vpn_four_20261019_source()['products'];
foreach (['paper-bags-with-logo', 'bakery-packaging-boxes', 'corrugated-mailer-boxes'] as $slug) { wp_insert_term($slug, 'product_cat', ['slug' => $slug]); }
$refs = ['how-to-make-paper-bags-stronger' => 'post', 'e-flute-corrugated-cardboard-thickness-mm' => 'post',
    'custom-birthday-paper-gift-bag-with-candle-print' => 'product', 'custom-printed-flat-bread-paper-bags' => 'product',
    'custom-white-kraft-paper-bags-twisted-handles' => 'product', 'custom-white-mailer-box-with-logo' => 'product', 'contact' => 'page'];
foreach ($refs as $slug => $type) { wp_insert_post(['post_type' => $type, 'post_status' => 'publish', 'post_title' => $slug, 'post_name' => $slug]); }
$owned_ids = []; $created_images = []; $uploads_before = get_option('upload_path');
update_option('upload_path', $root . '/tests/live-chat/runtime/four-product-uploads');
try {
    verify_four_test(in_array('inc/four-products-20261019-product-sync.php', custom_box_post_sync_files_to_load(), true), 'Incomplete release loads first in admin');
    wp_set_current_user(0); custom_box_sync_four_products_20261019();
    verify_four_test(!get_page_by_path($rows[0]['slug'], OBJECT, 'product'), 'Anonymous admin trigger cannot import');
    wp_set_current_user($admin->ID);
    $conflict = wp_insert_post(['post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Unrelated product', 'post_name' => $rows[0]['slug']]);
    custom_box_sync_four_products_20261019();
    verify_four_test(!get_option(VPN_FOUR_20261019_OPTION) && get_post($conflict)->post_title === 'Unrelated product', 'Unrelated slug conflict preserved and no completion marker');
    wp_delete_post($conflict, true);
    update_option('vpn_four_20261019_deploy_lock', time(), false);
    custom_box_sync_four_products_20261019();
    verify_four_test(!get_page_by_path($rows[0]['slug'], OBJECT, 'product') && !get_option(VPN_FOUR_20261019_OPTION), 'Concurrent migration lock prevents mutation');
    delete_option('vpn_four_20261019_deploy_lock');
    custom_box_sync_four_products_20261019();
    verify_four_test(get_option(VPN_FOUR_20261019_OPTION) === VPN_FOUR_20261019_VERSION, 'Admin trigger marks complete only after import: ' . wp_json_encode(get_transient('vpn_four_20261019_sync_notice')));
    $report = vpn_four_20261019_verify($rows, VPN_FOUR_20261019_VERSION, true);
    verify_four_test(count($report) === 4, 'Four fresh products published with strict content/meta/image readback');
    $owned_ids = array_column($report, 'id');
    $created_images = array_merge(...array_column($report, 'image_ids'));
    verify_four_test(count(array_unique($created_images)) === 24, '24 independent image attachments');
    foreach ($report as $r) {
        $body = get_post($r['id'])->post_content;
        verify_four_test(!str_contains($body, '{{') && str_contains($body, WP_HOME), 'Target installation URLs resolved: ' . $r['id']);
    }
    $count_before = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}");
    custom_box_sync_four_products_20261019();
    vpn_four_20261019_run();
    verify_four_test($count_before === (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}"), 'Repeat admin/core runs do not duplicate posts/media');
    $edited_id = $owned_ids[0];
    wp_update_post(['ID' => $edited_id, 'post_status' => 'draft', 'post_content' => '<p>Later owner revision.</p>']);
    delete_option(VPN_FOUR_20261019_OPTION);
    custom_box_sync_four_products_20261019();
    verify_four_test(get_post($edited_id)->post_status === 'draft' && get_post($edited_id)->post_content === '<p>Later owner revision.</p>', 'Later edits and unpublishing preserved');
    $hidden_id = $owned_ids[1]; $hidden = get_attached_file($created_images[6]);
    $away = $hidden . '.test-away'; rename($hidden, $away);
    try {
        custom_box_sync_four_products_20261019();
        verify_four_test(!get_option(VPN_FOUR_20261019_OPTION), 'Missing image clears completion marker and reports failure');
    } finally { rename($away, $hidden); }
    custom_box_sync_four_products_20261019();
    verify_four_test(get_option(VPN_FOUR_20261019_OPTION) === VPN_FOUR_20261019_VERSION, 'Resolved missing file can retry successfully');
    $is_admin = $GLOBALS['current_screen']; $GLOBALS['current_screen'] = new class { public function in_admin() { return false; } };
    verify_four_test(custom_box_post_sync_files_to_load() === [], 'Frontend does not load admin migration');
    $GLOBALS['current_screen'] = $is_admin;
    wp_set_current_user(0);
    verify_four_test(custom_box_post_sync_files_to_load() === [], 'Anonymous request cannot load migration');
    echo wp_json_encode(['passed' => count($checks), 'checks' => $checks], JSON_PRETTY_PRINT) . PHP_EOL;
} finally {
    // Keep fixture tables until shutdown hooks finish; the separate cleanup phase drops them.
    wp_set_current_user(0);
}
