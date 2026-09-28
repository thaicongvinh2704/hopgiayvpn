<?php
/** CLI integration check. Mail and retry transports are intercepted, never sent. */
if ('cli' !== PHP_SAPI) {
    http_response_code(403);
    exit;
}
$case = isset($argv[1]) ? $argv[1] : 'success';
$expected = array(
    'success' => 'success', 'minimal' => 'success', 'mail-failure' => 'received',
    'missing' => 'missing', 'consent' => 'consent', 'invalid' => 'invalid',
    'spam' => 'spam', 'captcha-success' => 'success', 'captcha-failure' => 'captcha', 'render-captcha' => '',
);
if (!isset($expected[$case])) exit(2);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '198.51.100.247';
$_SERVER['REQUEST_URI'] = '/hopgiayvpn/wp-admin/admin-post.php';
require dirname(__DIR__) . '/wp-load.php';
wp_set_current_user(0);
add_filter('custom_box_quote_form_logging_enabled', '__return_false');
add_filter('custom_box_quote_form_recaptcha_site_key', static function () use ($case) {
    return 0 === strpos($case, 'captcha-') ? 'integration-test-site-key' : '';
});
add_filter('custom_box_quote_form_recaptcha_secret_key', static function () { return 'integration-test-secret'; });
if ('render-captcha' === $case) {
    add_filter('custom_box_quote_form_recaptcha_site_key', static function () { return 'integration-test-site-key'; });
    add_filter('custom_box_quote_form_should_enqueue_recaptcha', '__return_true');
    custom_box_print_recaptcha_v3_submit_handler();
    exit;
}
add_filter('pre_http_request', static function ($pre, $args, $url) use ($case) {
    if ('https://www.google.com/recaptcha/api/siteverify' !== $url) return $pre;
    return array('headers' => array(), 'response' => array('code' => 200, 'message' => 'OK'), 'cookies' => array(),
        'body' => wp_json_encode(array('success' => 'captcha-success' === $case,
            'action' => 'quote_submit', 'score' => 0.9, 'hostname' => wp_parse_url(home_url('/'), PHP_URL_HOST))));
}, 10, 3);
$saved_ids = array();
$mail_calls = 0;
$retry_calls = 0;
$redirect = '';
$errors = array();
add_action('wp_insert_post', static function ($id, $post) use (&$saved_ids) {
    if ('custom_box_quote' === $post->post_type) $saved_ids[] = (int) $id;
}, 10, 2);
add_filter('pre_wp_mail', static function ($pre, $mail) use ($case, &$mail_calls, &$errors) {
    $mail_calls++;
    if ('sales.vpn@hopgiayvpn.com' !== $mail['to']) $errors[] = 'Unexpected recipient.';
    if (false === strpos($mail['message'], 'Custom pizza box packaging')) $errors[] = 'Product missing from email.';
    if (false === strpos($mail['message'], 'pizza-qa@example.invalid')) $errors[] = 'Contact missing from email.';
    return 'mail-failure' !== $case;
}, PHP_INT_MAX, 2);
// Exercise the retry branch without adding a runnable job or external mail.
add_filter('pre_as_enqueue_async_action', static function () use (&$retry_calls) { $retry_calls++; return 1; });
add_filter('pre_schedule_event', static function ($pre, $event) use (&$retry_calls) {
    if ('custom_box_send_queued_quote_email' === $event->hook) { $retry_calls++; return true; }
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
    'product_name' => 'Custom pizza box packaging', 'full_name' => 'Pizza Landing QA',
    'email' => 'pizza-qa@example.invalid', 'quantity' => '1000', 'country' => 'Vietnam',
    'stock_option' => 'Square pizza delivery box', 'message' => 'Integration check for pizza-box enquiry fields.',
    'privacy_consent' => 'yes', 'website_url' => '', 'custom_box_form_context' => 'quote',
    'custom_box_form_started_at' => $started_at,
    'custom_box_form_signature' => custom_box_quote_form_timestamp_signature($started_at, 'quote'),
    'custom_box_quote_nonce' => wp_create_nonce('custom_box_quote_form'),
    'g-recaptcha-response' => 'integration-test-token',
);
if ('minimal' === $case) {
    unset($_POST['quantity'], $_POST['country'], $_POST['message']);
}
if ('missing' === $case) $_POST['email'] = '';
if ('consent' === $case) unset($_POST['privacy_consent']);
if ('invalid' === $case) $_POST['custom_box_quote_nonce'] = 'invalid-token';
if ('spam' === $case) $_POST['website_url'] = 'https://example.invalid';
register_shutdown_function(static function () use ($case, $expected, &$saved_ids, &$mail_calls, &$retry_calls, &$redirect, &$errors, $rate_key, $previous_rate) {
    $saved_ids = array_values(array_unique($saved_ids));
    $should_save = in_array($expected[$case], array('success', 'received'), true);
    parse_str((string) wp_parse_url($redirect, PHP_URL_QUERY), $query);
    if (!isset($query['quote_status']) || $expected[$case] !== $query['quote_status']) $errors[] = 'Wrong redirect status.';
    if ('vpb-quote' !== wp_parse_url($redirect, PHP_URL_FRAGMENT)) $errors[] = 'Wrong redirect anchor.';
    if (count($saved_ids) !== ($should_save ? 1 : 0)) $errors[] = 'Wrong saved request count.';
    if ($mail_calls !== ($should_save ? 1 : 0)) $errors[] = 'Wrong mail call count.';
    if ('mail-failure' === $case && 1 !== $retry_calls) $errors[] = 'Retry was not requested exactly once.';
    $mail_status = '';
    foreach ($saved_ids as $id) {
        $data = get_post_meta($id, '_custom_box_quote_data', true);
        $mail_status = get_post_meta($id, '_custom_box_quote_mail_status', true);
        if (!is_array($data) || 'custom_pizza_boxes_manufacturer' !== $data['quote_source'] || 'yes' !== $data['privacy_consent']) $errors[] = 'Saved source or consent missing.';
        if ('private' !== get_post_status($id)) $errors[] = 'Request is not private.';
        if ($mail_status !== ('mail-failure' === $case ? 'failed' : 'sent')) $errors[] = 'Wrong mail state.';
        wp_clear_scheduled_hook('custom_box_send_queued_quote_email', array($id));
        wp_delete_post($id, true);
    }
    delete_transient($rate_key);
    if (false !== $previous_rate) set_transient($rate_key, $previous_rate, 10 * MINUTE_IN_SECONDS);
    echo wp_json_encode(array('case' => $case, 'passed' => !$errors, 'status' => isset($query['quote_status']) ? $query['quote_status'] : '',
        'saved' => count($saved_ids), 'mail_calls' => $mail_calls, 'retry_calls' => $retry_calls, 'mail_status' => $mail_status, 'errors' => $errors)) . "\n";
    if ($errors) exit(1);
});
custom_box_handle_quote_form();
