<?php
// Real REST/storage checks against the isolated chat database only.
if ('cli' !== PHP_SAPI) { exit; }
ob_start();
define('VPN_CHAT_TURNSTILE_SECRET', '');
require __DIR__ . '/runtime/wp/wp-load.php';
if (DB_NAME !== 'vpn_chat_test' || DB_HOST !== '127.0.0.1:3311' || !VPN_Chat_Settings::local()) { exit(1); }
$passed = array();
function re_check($ok, $label) { global $passed; if (!$ok) { throw new RuntimeException($label); } $passed[] = $label; }
function re_rest($path, $body, $guest = null) {
    $_COOKIE = $guest['cookies'] ?? array(); wp_set_current_user(0);
    $request = new WP_REST_Request('POST', '/vpn-chat/v1/' . $path);
    $request->set_header('origin', 'http://127.0.0.1:8091');
    $request->set_header('content-type', 'application/json');
    $request->set_header('x-vpn-csrf', $guest['csrf'] ?? '');
    $request->set_body(wp_json_encode($body)); return rest_do_request($request);
}
function re_guest() {
    $response = re_rest('bootstrap', array());
    re_check($response->get_status() === 200, 'Guest session created');
    return array('cookies' => $_COOKIE, 'csrf' => $response->get_data()['csrf']);
}
global $wpdb;
$original = VPN_Chat_Settings::get();
try {
    foreach (VPN_Chat_Schema::TABLES as $table) { VPN_Chat_Store::query('DELETE FROM ' . VPN_Chat_Store::table($table)); }
    $settings = $original; $settings['accept_new'] = true; $settings['site_key'] = ''; $settings['auto_reply'] = false;
    $settings['short_limit'] = 100; $settings['long_limit'] = 1000; update_option('vpn_chat_settings', $settings, false);
    $guest = re_guest(); $other = re_guest();
    $payload = array('message' => 'Required email QA', 'client_message_id' => bin2hex(random_bytes(16)));
    foreach (array('omitted' => null, 'empty' => '', 'whitespace' => '   ', 'invalid' => 'bad', 'array' => array('buyer@example.invalid'), 'oversized' => str_repeat('x', 250).'@example.invalid') as $case => $email) {
        $data = $payload; if ($case !== 'omitted') { $data['email'] = $email; }
        $result = re_rest('guest/start', $data, $guest);
        re_check($result->get_status() === 400, 'Reject first message: ' . $case);
        re_check((int) $wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations')) === 0, 'No conversation created: ' . $case);
    }
    $payload['email'] = '  buyer+quote@gmail.com  ';
    $response = re_rest('guest/start', $payload, $guest);
    re_check($response->get_status() === 200, 'Valid personal email accepted without a name');
    $receipt = $response->get_data(); $id = $receipt['id']; $conversation = VPN_Chat_Store::conversation($id);
    re_check($conversation['name'] === '' && $conversation['email'] === 'buyer+quote@gmail.com', 'Trimmed email saved; name stays optional');
    re_check($receipt['contact_email'] === $conversation['email'], 'Receipt confirms saved email');
    re_check(re_rest('guest/start', $payload, $guest)->get_data()['seq'] === $receipt['seq'], 'Start retries are idempotent');
    re_check((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages')) === 1, 'Retry does not duplicate message');
    re_check(re_rest('guest/contact', array('id'=>$id,'email'=>''), $guest)->get_status() === 400, 'Cannot remove required email');
    re_check(VPN_Chat_Store::conversation($id)['email'] === $conversation['email'], 'Rejected clear preserves saved email');
    re_check(re_rest('guest/contact', array('id'=>$id,'email'=>'bad'), $guest)->get_status() === 400, 'Invalid update rejected');
    re_check(re_rest('guest/contact', array('id'=>$id,'email'=>'second@example.invalid'), $other)->get_status() === 404, 'Another guest cannot update contact');
    usleep(1100000);
    $message = array('id'=>$id,'message'=>'Follow-up without retyping email','client_message_id'=>bin2hex(random_bytes(16)));
    re_check(re_rest('guest/send', $message, $guest)->get_status() === 200, 'Saved-email conversation sends without retyping email');
    re_check(re_rest('guest/send', $message, $guest)->get_status() === 200, 'Follow-up retry idempotent');
    $wpdb->update(VPN_Chat_Store::table('conversations'), array('email'=>''), array('id'=>$conversation['id']));
    usleep(1100000); $message['client_message_id'] = bin2hex(random_bytes(16));
    $before = (int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages'));
    $response = re_rest('guest/send', $message, $guest);
    re_check($response->get_status() === 400 && $response->get_data()['code'] === 'email_required', 'Legacy anonymous chat must add email before sending');
    re_check((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages')) === $before, 'Legacy rejection does not save message');
    re_check(re_rest('guest/sync', array('id'=>$id,'cursor'=>0), $guest)->get_status() === 200, 'Legacy transcript can still be read');
    re_check(re_rest('guest/send', $message, $other)->get_status() === 404, 'Other guest cannot send to legacy chat');
    re_check(re_rest('guest/contact', array('id'=>$id,'email'=>'legacy@example.invalid'), $guest)->get_status() === 200, 'Legacy visitor can add email');
    usleep(1100000);
    re_check(re_rest('guest/send', $message, $guest)->get_status() === 200, 'Legacy visitor can resume after email saved');
    $answer = VPN_Chat_Auto_Reply::answer('Why is email required?', array(array('sender'=>'guest','body'=>'Why is email required?')), 'legacy@example.invalid');
    re_check(str_contains($answer['body'], 'required') && !str_contains($answer['body'], 'optional'), 'Automated privacy answer matches required-email policy');
    echo wp_json_encode(array('passed'=>count($passed),'checks'=>$passed), JSON_PRETTY_PRINT).PHP_EOL;
} finally {
    update_option('vpn_chat_settings', $original, false);
    foreach (VPN_Chat_Schema::TABLES as $table) { VPN_Chat_Store::query('DELETE FROM ' . VPN_Chat_Store::table($table)); }
}
