<?php
/** Persist-first quote submission + background mail checks. Isolated DB and fake mail only. */
if (PHP_SAPI !== 'cli') { exit; }
ob_start();
require __DIR__ . '/runtime/wp/wp-load.php';
if (DB_NAME !== 'vpn_chat_test' || DB_HOST !== '127.0.0.1:3311' || wp_get_environment_type() !== 'local') { exit(1); }
require_once dirname(__DIR__, 2) . '/wp-content/themes/custom-box-theme/inc/quote-form-handler.php';
custom_box_register_quote_request_post_type();
add_filter('custom_box_quote_form_logging_enabled', '__return_false');
global $wpdb;
if (($argv[1] ?? '') === 'submit') {
    $mode = $argv[2];
    // Simulate Action Scheduler being unavailable, failing or accepting the task.
    function as_enqueue_async_action($hook, $args, $group, $unique = false) {
        if ($GLOBALS['fast_mode'] === 'as-throws') { throw new RuntimeException('Test queue failure'); }
        return $GLOBALS['fast_mode'] === 'as-success' ? 123 : 0;
    }
    $GLOBALS['fast_mode'] = $mode;
    if ($mode === 'queue-fails') { add_filter('pre_schedule_event', '__return_false'); }
    $mail_calls = 0;
    add_filter('pre_wp_mail', static function ($pre) use (&$mail_calls) { $mail_calls++; usleep(1500000); return false; }, PHP_INT_MAX);
    $started = time() - 10; wp_set_current_user(0);
    $_SERVER['REQUEST_METHOD'] = 'POST'; $_SERVER['REMOTE_ADDR'] = '198.51.100.244';
    $_POST = ['quote_source' => 'custom_paper_bags_manufacturer', 'response_format' => 'paper_bag_json', 'product_name' => 'Fast quote QA',
        'full_name' => 'Fast submit test', 'email' => 'fast-submit@example.invalid', 'quantity' => '5000', 'country' => 'Germany', 'stock_option' => 'Custom paper',
        'privacy_consent' => 'yes', 'website_url' => '', 'custom_box_form_context' => 'quote', 'custom_box_form_started_at' => $started,
        'custom_box_form_signature' => custom_box_quote_form_timestamp_signature($started, 'quote'), 'custom_box_quote_nonce' => wp_create_nonce('custom_box_quote_form'),
        '_wp_http_referer' => home_url('/contact/')];
    if ($mode === 'generic') { $_POST['quote_source'] = 'contact'; $_POST['response_format'] = ''; }
    if ($mode === 'pizza') { $_POST['quote_source'] = 'custom_pizza_boxes_manufacturer'; $_POST['response_format'] = ''; }
    if ($mode === 'manufacturer') { $_POST['quote_source'] = 'paper_box_manufacturer'; $_POST['response_format'] = ''; }
    if ($mode === 'invalid-email') { $_POST['email'] = ''; }
    $duplicate = custom_box_quote_form_duplicate_key($_POST['email'], '', $_POST['product_name'], $_POST['quantity']); delete_transient($duplicate);
    $rate = 'custom_box_form_rate_' . md5('quote|' . custom_box_quote_form_ip_hash()); delete_transient($rate);
    $saved = []; $redirect = ''; $raw = ''; $begin = microtime(true);
    add_action('wp_insert_post', static function ($id, $post) use (&$saved) { if ($post->post_type === 'custom_box_quote') { $saved[] = $id; } }, 10, 2);
    add_filter('wp_redirect', static function ($url) use (&$redirect) { $redirect = $url; return false; });
    ob_start(static function ($buffer) use (&$raw) { $raw .= $buffer; return ''; });
    register_shutdown_function(static function () use (&$saved, &$mail_calls, &$raw, &$redirect, $begin) {
        if (ob_get_level()) { ob_end_flush(); }
        echo wp_json_encode(['response' => json_decode($raw, true), 'redirect' => $redirect, 'ids' => $saved, 'mail_calls' => $mail_calls, 'elapsed_ms' => round(1000 * (microtime(true) - $begin), 2)]);
    });
    custom_box_handle_quote_form(); exit(1);
}
$checks = []; $timings = []; $ids = [];
function fq_check($value, $name) { global $checks; if (!$value) { throw new RuntimeException($name); } $checks[] = $name; }
try {
    wp_unschedule_hook('custom_box_send_queued_quote_email');
    foreach (['json', 'generic', 'pizza', 'manufacturer', 'as-success', 'as-throws', 'queue-fails', 'invalid-email'] as $mode) {
        $r = json_decode(shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' submit ' . escapeshellarg($mode)), true);
        // Child wrote options via a different connection/process; discard this process's object cache.
        wp_cache_flush();
        fq_check(is_array($r), $mode . ': child response parsed');
        fq_check($r['mail_calls'] === 0, $mode . ': visitor never waits for SMTP');
        $timings[$mode] = $r['elapsed_ms']; $ids = array_merge($ids, $r['ids']);
        if ($mode === 'invalid-email') { fq_check(!$r['ids'] && !$r['response']['success'], 'Invalid email never produces accepted receipt'); continue; }
        fq_check(count($r['ids']) === 1, $mode . ': lead is stored before acknowledgement');
        $id = $r['ids'][0];
        fq_check(get_post_status($id) === 'private', $mode . ': saved enquiry stays private');
        fq_check(get_post_meta($id, '_custom_box_quote_mail_status', true) === ($mode === 'queue-fails' ? 'schedule_failed' : 'queued'), $mode . ': mail queue status accurate');
        if (!in_array($mode, ['as-success', 'queue-fails'], true)) fq_check((bool) wp_next_scheduled('custom_box_send_queued_quote_email', [$id]), $mode . ': WP-Cron fallback retains delivery');
        if ($mode === 'manufacturer') fq_check(str_contains($r['redirect'], '/thank-you-packaging-quote/'), 'Manufacturer thank-you destination preserved');
        elseif ($mode === 'generic' || $mode === 'pizza') fq_check(str_contains($r['redirect'], 'quote_status=success'), $mode . ': existing form acknowledgement preserved');
        else fq_check(($r['response']['success'] ?? false) && ($r['response']['data']['status'] ?? '') === 'received' && preg_match('/^vpn-quote-\d+$/', $r['response']['data']['receipt'] ?? ''), $mode . ': persisted receipt returned without email claim');
    }
    $id = $ids[0]; wp_clear_scheduled_hook('custom_box_send_queued_quote_email', [$id]);
    $calls = 0; $success = false;
    $slow = static function ($pre) use (&$calls, &$success) { $calls++; usleep(1500000); return $success; };
    add_filter('pre_wp_mail', $slow, PHP_INT_MAX);
    $begin = microtime(true); custom_box_process_queued_quote_email($id); $worker_ms = round(1000 * (microtime(true) - $begin), 2);
    fq_check($calls === 1 && get_post_meta($id, '_custom_box_quote_mail_status', true) === 'failed', 'SMTP failure is recorded by background worker');
    fq_check((bool) wp_next_scheduled('custom_box_send_queued_quote_email', [$id]), 'SMTP failure schedules bounded retry');
    fq_check($timings['json'] < $worker_ms && $worker_ms >= 1400, 'Slow SMTP latency is outside the visitor request');
    wp_clear_scheduled_hook('custom_box_send_queued_quote_email', [$id]); $success = true;
    custom_box_process_queued_quote_email($id); custom_box_process_queued_quote_email($id);
    fq_check($calls === 2 && get_post_meta($id, '_custom_box_quote_mail_status', true) === 'sent', 'Successful worker is idempotent on repeated jobs');
    $id = $ids[1]; update_post_meta($id, '_custom_box_quote_mail_attempts', 5); custom_box_process_queued_quote_email($id);
    fq_check($calls === 2, 'Automatic attempts stop at five');
    $id = $ids[2]; $name = 'custom_box_quote_mail_lock_' . $id; add_option($name, time() . ':other-worker', '', false);
    custom_box_process_queued_quote_email($id); fq_check($calls === 2, 'Concurrent worker lock prevents duplicate mail'); delete_option($name);
    $id = $ids[3]; $name = 'custom_box_quote_mail_lock_' . $id; add_option($name, (time() - 400) . ':crashed-worker', '', false);
    custom_box_process_queued_quote_email($id); fq_check($calls === 3 && !get_option($name), 'Expired worker lock recovers and releases');
    remove_filter('pre_wp_mail', $slow, PHP_INT_MAX);
    $id = $ids[4]; $handlers_before = has_action('wp_mail_failed');
    $throw = static function () { throw new RuntimeException('Simulated transport exception'); };
    add_filter('pre_wp_mail', $throw, PHP_INT_MAX);
    custom_box_process_queued_quote_email($id); remove_filter('pre_wp_mail', $throw, PHP_INT_MAX);
    fq_check(get_post_meta($id, '_custom_box_quote_mail_status', true) === 'failed' && (int) get_post_meta($id, '_custom_box_quote_mail_attempts', true) === 1, 'Transport exception is recorded as a bounded failed attempt');
    fq_check(!get_option('custom_box_quote_mail_lock_' . $id) && wp_next_scheduled('custom_box_send_queued_quote_email', [$id]), 'Transport exception releases lock and schedules retry');
    fq_check(has_action('wp_mail_failed') === $handlers_before, 'Transport exception does not leak failure listeners');
    echo wp_json_encode(['passed' => count($checks), 'submit_ms' => $timings, 'slow_worker_ms' => $worker_ms, 'checks' => $checks], JSON_PRETTY_PRINT) . PHP_EOL;
} finally {
    foreach ($ids as $id) { wp_clear_scheduled_hook('custom_box_send_queued_quote_email', [$id]); delete_option('custom_box_quote_mail_lock_' . $id); wp_delete_post($id, true); }
}
