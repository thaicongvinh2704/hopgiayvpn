<?php
defined('ABSPATH') || exit;

/** Popup enquiries use the existing private inbox, session and rate limits; never SMTP. */
final class VPN_Chat_Email_Quote {
    private static function receipt(string $id, int $seq): array {
        return ['saved' => true, 'id' => $id, 'seq' => $seq, 'receipt' => 'vpn-inbox-' . $id . '-' . $seq];
    }
    private static function retry(array $s, string $client, string $body): ?array {
        global $wpdb;
        $old = $wpdb->get_row($wpdb->prepare('SELECT c.public_id,m.seq,m.payload_hash FROM ' . VPN_Chat_Store::table('conversations') . ' c JOIN ' . VPN_Chat_Store::table('messages') . " m ON m.conversation_id=c.id WHERE c.customer_id=%d AND m.sender_scope='guest' AND m.client_message_id=%s LIMIT 1", $s['customer_id'], $client), ARRAY_A);
        if ($wpdb->last_error) { throw new VPN_Chat_Fault('storage_unavailable', 503); }
        if (!$old) { return null; }
        if (!hash_equals($old['payload_hash'], hash('sha256', 'guest:' . $body))) { throw new VPN_Chat_Fault('idempotency_conflict', 409); }
        return self::receipt($old['public_id'], (int) $old['seq']);
    }
    public static function capture(array $s, array $p): array {
        global $wpdb;
        $email = VPN_Chat_Service::required_email($p['email'] ?? '');
        $client = VPN_Chat_Security::client_id($p['client_message_id'] ?? null);
        $details = $p['message'] ?? '';
        if (!is_string($details)) { throw new VPN_Chat_Fault('invalid_text'); }
        $details = trim($details) === '' ? '' : VPN_Chat_Security::text($details, 1000);
        $title = $p['product_title'] ?? '';
        if (!is_string($title)) { throw new VPN_Chat_Fault('invalid_text'); }
        $title = trim($title) === '' ? '' : VPN_Chat_Security::text($title, 160);
        $path = VPN_Chat_Security::source($p['source_path'] ?? '');
        $guard = $p['guard'] ?? '';
        if (!is_string($guard) || $guard !== '') { throw new VPN_Chat_Fault('invalid_request'); }
        $body = "Packaging quote request (website popup)\nEmail: " . $email;
        if ($title !== '') { $body .= "\nProduct / page: " . $title; }
        if ($path !== '') { $body .= "\nSource: " . mb_substr($path, 0, 400); }
        if ($details !== '') { $body .= "\nProject details:\n" . $details; }
        $body = VPN_Chat_Security::text($body, 2000);
        if ($retry = self::retry($s, $client, $body)) { return $retry; }
        if (!VPN_Chat_Settings::ready()) { throw new VPN_Chat_Fault('new_chat_unavailable', 503); }
        VPN_Chat_Security::message_rate($s);
        VPN_Chat_Security::quota('popup:customer:' . $s['customer_id'], VPN_Chat_Settings::get()['conversation_limit'], 600);
        VPN_Chat_Security::quota('popup:ip:' . VPN_Chat_Security::ip(), 100, 600);
        $metadata = ['source' => 'website_email_popup', 'email_notifications' => false];
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $key) {
            if (isset($p['metadata'][$key]) && is_string($p['metadata'][$key])) { $metadata[$key] = sanitize_text_field(mb_substr($p['metadata'][$key], 0, 100)); }
        }
        return VPN_Chat_Store::transaction(static function () use ($wpdb, $s, $client, $body, $email, $path, $metadata) {
            $session = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . VPN_Chat_Store::table('sessions') . ' WHERE id=%d FOR UPDATE', $s['id']), ARRAY_A);
            if (!$session || $session['revoked'] || (int) $session['customer_id'] !== (int) $s['customer_id'] || strtotime($session['expires_at'] . ' UTC') <= time()) { throw new VPN_Chat_Fault('session_expired', 401); }
            VPN_Chat_Store::query($wpdb->prepare('SELECT id FROM ' . VPN_Chat_Store::table('customers') . ' WHERE id=%d FOR UPDATE', $s['customer_id']));
            if ($retry = self::retry($s, $client, $body)) { return $retry; }
            // Reuse only this browser's authenticated customer, never match somebody by email.
            $existing = $wpdb->get_var($wpdb->prepare('SELECT public_id FROM ' . VPN_Chat_Store::table('conversations') . " WHERE customer_id=%d AND status NOT IN ('closed','spam') ORDER BY updated_at DESC,id DESC LIMIT 1", $s['customer_id']));
            if ($wpdb->last_error) { throw new VPN_Chat_Fault('storage_unavailable', 503); }
            if ($existing) {
                $c = VPN_Chat_Store::conversation($existing, true);
                VPN_Chat_Store::authorize($c, $s, true);
                VPN_Chat_Store::query($wpdb->prepare('UPDATE ' . VPN_Chat_Store::table('conversations') . ' SET email=%s,version=version+1 WHERE id=%d', $email, $c['id']));
                $c = VPN_Chat_Store::conversation($existing, true);
            } else {
                $id = wp_generate_uuid4();
                VPN_Chat_Store::insert('conversations', ['public_id' => $id, 'session_id' => $s['id'], 'customer_id' => $s['customer_id'], 'name' => '', 'email' => $email, 'source_path' => $path, 'metadata' => wp_json_encode($metadata), 'label' => 'needs_quote', 'needs' => '', 'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s')]);
                $c = VPN_Chat_Store::conversation($id, true);
            }
            $result = VPN_Chat_Service::send_locked($c, $body, $client, 'guest', 0, true);
            VPN_Chat_Store::audit('popup_quote_received', (int) $c['id']);
            return self::receipt($result['id'], (int) $result['seq']);
        });
    }
}
