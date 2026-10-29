<?php
/** Local integration: a selected pizza format reaches private storage; no mail or jobs escape. */
if ('cli' !== PHP_SAPI) { http_response_code(403); exit; }
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '198.51.100.249';
$_SERVER['REQUEST_URI'] = '/hopgiayvpn/wp-admin/admin-post.php';
require dirname(__DIR__) . '/wp-load.php';
if (!in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), array('localhost', '127.0.0.1'), true)) {
    throw new RuntimeException('Local site required.');
}
wp_set_current_user(0);
$ids = array(); $redirect = ''; $mail_calls = 0; $queue_calls = 0;
add_filter('custom_box_quote_form_logging_enabled', '__return_false');
add_filter('custom_box_quote_form_recaptcha_site_key', '__return_empty_string');
add_action('wp_insert_post', static function ($id, $post) use (&$ids) {
    if ('custom_box_quote' === $post->post_type) { $ids[] = $id; }
}, 10, 2);
add_filter('pre_wp_mail', static function () use (&$mail_calls) { $mail_calls++; return true; }, PHP_INT_MAX);
add_filter('pre_as_enqueue_async_action', static function () use (&$queue_calls) { $queue_calls++; return 1; });
add_filter('pre_schedule_event', static function ($pre, $event) use (&$queue_calls) {
    if ('custom_box_send_queued_quote_email' === $event->hook) { $queue_calls++; return true; }
    return $pre;
}, 10, 2);
add_filter('wp_redirect', static function ($url) use (&$redirect) { $redirect = $url; return $url; });
$rate_key = 'custom_box_form_rate_' . md5('quote|' . custom_box_quote_form_ip_hash());
$previous_rate = get_transient($rate_key);
delete_transient($rate_key);
$started_at = time() - 10;
$_POST = array(
    'action' => 'custom_box_quote_form', 'quote_source' => 'custom_pizza_boxes_manufacturer',
    'form_location' => 'pizza_boxes_manufacturer_hero', 'form_anchor' => 'vpb-quote',
    '_wp_http_referer' => custom_box_pizza_boxes_manufacturer_url(),
    'current_page_url' => custom_box_pizza_boxes_manufacturer_url(),
    'product_name' => 'Rectangular Flatbread Pizza Box', 'full_name' => 'Local Pizza QA',
    'email' => 'pizza-selected-format@example.invalid', 'quantity' => '1000', 'country' => 'Vietnam',
    'privacy_consent' => 'yes', 'website_url' => '', 'custom_box_form_context' => 'quote',
    'custom_box_form_started_at' => $started_at,
    'custom_box_form_signature' => custom_box_quote_form_timestamp_signature($started_at, 'quote'),
    'custom_box_quote_nonce' => wp_create_nonce('custom_box_quote_form'),
);
register_shutdown_function(static function () use (&$ids, &$redirect, &$mail_calls, &$queue_calls, $rate_key, $previous_rate) {
    $checks = array('one_private_lead' => count(array_unique($ids)) === 1, 'no_mail_sent' => $mail_calls === 0, 'queue_intercepted' => $queue_calls === 1);
    parse_str((string) wp_parse_url($redirect, PHP_URL_QUERY), $query);
    $checks['success_redirect'] = isset($query['quote_status']) && 'success' === $query['quote_status'] && 'vpb-quote' === wp_parse_url($redirect, PHP_URL_FRAGMENT);
    foreach (array_unique($ids) as $id) {
        $data = get_post_meta($id, '_custom_box_quote_data', true);
        $checks['selected_product_saved'] = is_array($data) && 'Rectangular Flatbread Pizza Box' === $data['product_name'];
        $checks['source_and_privacy_preserved'] = is_array($data) && 'custom_pizza_boxes_manufacturer' === $data['quote_source'] && 'yes' === $data['privacy_consent'];
        $checks['private_storage'] = 'private' === get_post_status($id);
        $checks['mail_queued'] = 'queued' === get_post_meta($id, '_custom_box_quote_mail_status', true);
        wp_clear_scheduled_hook('custom_box_send_queued_quote_email', array($id));
        wp_delete_post($id, true);
    }
    delete_transient($rate_key);
    if (false !== $previous_rate) { set_transient($rate_key, $previous_rate, 10 * MINUTE_IN_SECONDS); }
    echo wp_json_encode(array('passed' => !in_array(false, $checks, true), 'checks' => $checks)) . PHP_EOL;
    if (in_array(false, $checks, true)) { exit(1); }
});
custom_box_handle_quote_form();
