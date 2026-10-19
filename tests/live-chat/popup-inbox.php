<?php
/** Real REST + InnoDB integration. Isolated DB only; all mail/HTTP intercepted by test MU. */
if (PHP_SAPI !== 'cli') { exit; }
ob_start();
require __DIR__ . '/runtime/wp/wp-load.php';
if (DB_NAME !== 'vpn_chat_test' || DB_HOST !== '127.0.0.1:3311' || !VPN_Chat_Settings::local()) { exit(1); }
if (!function_exists('vpn_email_popup_token')) { require_once dirname(__DIR__, 2) . '/wp-content/mu-plugins/vpn-email-quote-popup.php'; }
if (($argv[1] ?? '') === 'legacy') {
    $fixture = json_decode(base64_decode($argv[2]), true);
    $_COOKIE = $fixture['cookies']; wp_set_current_user(0);
    $_SERVER['HTTP_ORIGIN'] = 'http://127.0.0.1:8091'; $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = ['quote_source' => 'website_email_popup', 'response_format' => 'email_popup_json', 'email' => 'legacy@example.invalid', 'message' => 'Legacy cached popup request',
        'product_name' => 'Paper bags', 'current_page_url' => 'http://127.0.0.1:8091/paper-bags/', 'website_url' => 'autofilled@example.invalid',
        'custom_box_quote_nonce' => wp_create_nonce('custom_box_quote_form')];
    vpn_email_popup_legacy_inbox(); exit(1);
}
if (($argv[1] ?? '') === 'session') { vpn_email_popup_token(); }
$checks = [];
function pi_check($value, $name) { global $checks; if (!$value) { throw new RuntimeException($name); } $checks[] = $name; }
function pi_rest($path, $payload = [], $guest = null, $extra = [], $user = 0) {
    $_COOKIE = $guest['cookies'] ?? []; wp_set_current_user($user);
    $r = new WP_REST_Request('POST', '/vpn-chat/v1/' . $path);
    foreach (array_merge(['origin' => 'http://127.0.0.1:8091', 'content-type' => 'application/json', 'x-vpn-csrf' => $guest['csrf'] ?? ''], $extra) as $key => $value) { $r->set_header($key, $value); }
    if ($user) { $r->set_header('x-wp-nonce', wp_create_nonce('wp_rest')); }
    $r->set_body(wp_json_encode($payload)); return rest_do_request($r);
}
function pi_guest() {
    $r = pi_rest('bootstrap'); pi_check($r->get_status() === 200, 'Session bootstraps');
    return ['cookies' => $_COOKIE, 'csrf' => $r->get_data()['csrf']];
}
function pi_count($table) { global $wpdb; return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . VPN_Chat_Store::table($table)); }
global $wpdb;
$original = VPN_Chat_Settings::get(); $mail_before = (int) get_option('vpn_test_mail_count', 0);
try {
    foreach (VPN_Chat_Schema::TABLES as $table) { VPN_Chat_Store::query('DELETE FROM ' . VPN_Chat_Store::table($table)); }
    $settings = $original; $settings['accept_new'] = true; $settings['auto_reply'] = true;
    $settings['short_limit'] = 100; $settings['long_limit'] = 1000; $settings['conversation_limit'] = 20;
    update_option('vpn_chat_settings', $settings, false);
    $a = pi_guest(); $b = pi_guest();
    $payload = ['email' => 'buyer+quote@gmail.com', 'message' => '50,000 printed paper bags', 'product_title' => 'Custom paper bags', 'source_path' => '/paper-bags/', 'client_message_id' => 'popup-' . bin2hex(random_bytes(16)), 'metadata' => ['utm_source' => 'google']];
    foreach (['' , 'invalid', ['x@example.invalid']] as $email) {
        pi_check(pi_rest('guest/email-quote', array_merge($payload, ['email' => $email]), $a)->get_status() === 400, 'Invalid/absent email rejected');
    }
    pi_check(pi_rest('guest/email-quote', $payload, $a, ['origin' => 'https://attacker.invalid'])->get_status() === 403, 'Cross-origin rejected');
    pi_check(pi_rest('guest/email-quote', $payload, $a, ['x-vpn-csrf' => 'bad'])->get_status() === 403, 'Bad CSRF rejected');
    pi_check(pi_rest('guest/email-quote', $payload)->get_status() === 401, 'Missing session rejected');
    pi_check(pi_rest('guest/email-quote', $payload + ['attachments' => []], $a)->get_status() === 400, 'Attachments rejected');
    pi_check(pi_rest('guest/email-quote', array_merge($payload, ['message' => str_repeat('x', 1001)]), $a)->get_status() === 400, 'Oversized details rejected');
    pi_check(pi_count('conversations') === 0, 'Invalid attempts leave no conversation');
    $r = pi_rest('guest/email-quote', $payload, $a); $receipt = $r->get_data();
    pi_check($r->get_status() === 200 && $receipt['saved'] === true, 'Valid quote saved without Google or SMTP');
    pi_check((bool) preg_match('/^vpn-inbox-[a-f0-9-]{36}-1$/', $receipt['receipt']), 'Receipt references committed inbox message');
    $id = $receipt['id']; $c = VPN_Chat_Store::conversation($id);
    pi_check($c['email'] === $payload['email'] && $c['label'] === 'needs_quote' && $c['name'] === '', 'Email and quote label saved; name optional');
    pi_check($c['source_path'] === '/paper-bags/' && json_decode($c['metadata'], true)['utm_source'] === 'google', 'Source and attribution saved');
    pi_check(pi_count('messages') === 1 && pi_count('outbox') === 0, 'No bot noise or notification email queued');
    pi_check(!isset($receipt['contact_email']) && !isset($receipt['messages']), 'Receipt does not disclose customer transcript');
    pi_check(pi_rest('guest/email-quote', $payload, $a)->get_data() === $receipt && pi_count('messages') === 1, 'Network retry does not duplicate request');
    pi_check(pi_rest('guest/email-quote', array_merge($payload, ['message' => 'Changed body']), $a)->get_status() === 409, 'Changed retry payload conflicts');
    pi_check(pi_rest('guest/sync', ['id' => $id, 'cursor' => 0], $b)->get_status() === 404, 'Another browser cannot read the request');
    $admin = get_user_by('login', 'chat-test-manager');
    $inbox = pi_rest('agent/sync', ['filter' => 'inbox', 'id' => $id, 'cursor' => 0], null, [], $admin->ID);
    pi_check($inbox->get_status() === 200 && in_array($id, array_column($inbox->get_data()['list'], 'id')), 'Request appears in the existing customer inbox');
    $same = pi_rest('guest/email-quote', $payload, $b)->get_data();
    pi_check($same['id'] !== $id, 'Identical unverified email never merges different customers');
    $next = array_merge($payload, ['client_message_id' => 'popup-' . bin2hex(random_bytes(16)), 'message' => 'Second brief']);
    $second = pi_rest('guest/email-quote', $next, $a)->get_data();
    pi_check($second['id'] === $id && $second['seq'] === 2 && pi_count('conversations') === 2, 'Existing visitor conversation receives the new enquiry');
    // A later reassignment/SLA worker must not resurrect company mail for a popup-only conversation.
    VPN_Chat_Store::insert('outbox', ['conversation_id' => $c['id'], 'dedup_key' => 'test-popup', 'due_at' => gmdate('Y-m-d H:i:s', time() - 10)]);
    VPN_Chat_Jobs::run();
    pi_check((int) get_option('vpn_test_mail_count', 0) === $mail_before, 'Background SLA worker sends no company mail for popup');
    $legacy_guest = pi_guest();
    $arg = base64_encode(wp_json_encode($legacy_guest));
    $legacy = json_decode(shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' legacy ' . escapeshellarg($arg)), true);
    pi_check(($legacy['success'] ?? false) && preg_match('/^vpn-quote-\d+$/', $legacy['data']['receipt'] ?? ''), 'Cached old popup returns its compatible receipt');
    pi_check(pi_count('conversations') === 3 && (int) get_option('vpn_test_mail_count', 0) === $mail_before, 'Cached old popup saves to inbox without company mail');
    $before = pi_count('conversations'); $rollback_guest = pi_guest();
    $fail = static fn($sql) => str_starts_with($sql, 'INSERT INTO `' . VPN_Chat_Store::table('messages') . '`') ? 'INSERT INTO missing_popup_test_table (x) VALUES (1)' : $sql;
    add_filter('query', $fail); $wpdb->suppress_errors(true);
    $failed = pi_rest('guest/email-quote', $payload, $rollback_guest);
    remove_filter('query', $fail); $wpdb->suppress_errors(false);
    pi_check($failed->get_status() === 503 && pi_count('conversations') === $before, 'Storage failure rolls back conversation and never returns success');
    $settings['accept_new'] = false; update_option('vpn_chat_settings', $settings, false);
    pi_check(pi_rest('guest/email-quote', $payload, $rollback_guest)->get_status() === 503, 'Inbox disabled fails closed');
    $session = json_decode(shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' session'), true);
    pi_check(($session['data']['delivery'] ?? '') === 'customer_inbox' && $session['data']['recaptcha_site_key'] === '', 'Fresh and cached JavaScript discover inbox-only delivery');
    pi_check((int) get_option('vpn_test_mail_count', 0) === $mail_before, 'Entire suite sent zero emails');
    echo wp_json_encode(['passed' => count($checks), 'checks' => $checks], JSON_PRETTY_PRINT) . PHP_EOL;
} finally {
    update_option('vpn_chat_settings', $original, false);
    foreach (VPN_Chat_Schema::TABLES as $table) { VPN_Chat_Store::query('DELETE FROM ' . VPN_Chat_Store::table($table)); }
}
