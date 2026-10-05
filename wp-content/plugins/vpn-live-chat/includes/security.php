<?php
defined('ABSPATH') || exit;
final class VPN_Chat_Security {
    const COOKIE = 'vpn_chat_session';
    public static function origin(WP_REST_Request $r): void {
        $origin = $r->get_header('origin');
        $site = wp_parse_url(home_url());
        $expected = $site['scheme'] . '://' . $site['host'] . (isset($site['port']) ? ':' . $site['port'] : '');
        $fetch = $r->get_header('sec-fetch-site');
        if (($origin && !hash_equals($expected, rtrim($origin, '/'))) || ($fetch && !in_array($fetch, ['same-origin', 'none'], true))) { throw new VPN_Chat_Fault('wrong_origin', 403); }
        // Browsers send Origin for JSON POSTs. Non-browser clients must provide it too.
        if ($r->get_method() !== 'GET' && !$origin) { throw new VPN_Chat_Fault('origin_required', 403); }
        if (strlen((string)$r->get_body()) > 16384) { throw new VPN_Chat_Fault('body_too_large', 413); }
        if ($r->get_method() !== 'GET') {
            if (!preg_match('~^application/json(?:\s*;.*)?$~i', $r->get_header('content-type'))) { throw new VPN_Chat_Fault('json_required', 415); }
            $body = json_decode($r->get_body(), true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($body) || array_is_list($body) && $body !== []) { throw new VPN_Chat_Fault('invalid_json'); }
        }
    }
    public static function ip(): string {
        // REMOTE_ADDR only. A trusted reverse proxy may normalize it before PHP.
        return hash_hmac('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), wp_salt('auth'));
    }
    public static function quota(string $scope, int $limit, int $seconds): void {
        global $wpdb;
        $window = intdiv(time(), $seconds);
        $key = hash('sha256', "$scope:$window");
        $expires = gmdate('Y-m-d H:i:s', ($window + 1) * $seconds);
        // Serialize each counter row. Consume even rejected requests; no transient races.
        $hits = VPN_Chat_Store::transaction(static function () use ($wpdb, $key, $expires) {
            VPN_Chat_Store::query($wpdb->prepare('INSERT INTO ' . VPN_Chat_Store::table('rate_limits') . ' (bucket,hits,expires_at) VALUES (%s,1,%s) ON DUPLICATE KEY UPDATE hits=hits+1', $key, $expires));
            return (int) $wpdb->get_var($wpdb->prepare('SELECT hits FROM ' . VPN_Chat_Store::table('rate_limits') . ' WHERE bucket=%s FOR UPDATE', $key));
        });
        if ($hits > $limit) { throw new VPN_Chat_Fault('rate_limited', 429); }
    }
    public static function session(bool $csrf = false): array {
        global $wpdb;
        $secret = (string) ($_COOKIE[self::COOKIE] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/D', $secret)) { self::quota('auth:' . self::ip(), 100, 300); throw new VPN_Chat_Fault('session_expired', 401); }
        $s = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . VPN_Chat_Store::table('sessions') . ' WHERE secret_hash=%s', hash('sha256', $secret)), ARRAY_A);
        $settings = VPN_Chat_Settings::get();
        if (!$s || $s['revoked'] || strtotime($s['expires_at'] . ' UTC') <= time() || strtotime($s['last_seen'] . ' UTC') + $settings['idle_hours'] * 3600 <= time()) {
            self::quota('auth:' . self::ip(), 100, 300); throw new VPN_Chat_Fault('session_expired', 401);
        }
        if ($csrf && !hash_equals(self::csrf($secret), (string) self::$request_csrf)) { self::quota('csrf:' . self::ip(),100,300); throw new VPN_Chat_Fault('csrf_required', 403); }
        VPN_Chat_Identity::check((int)$s['customer_id']);
        $blocked = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . VPN_Chat_Store::table('blocks') . ' WHERE session_id=%d AND revoked=0 AND expires_at>UTC_TIMESTAMP() LIMIT 1', $s['id']));
        if ($blocked) { throw new VPN_Chat_Fault('temporarily_blocked', 403); }
        VPN_Chat_Store::query($wpdb->prepare('UPDATE ' . VPN_Chat_Store::table('sessions') . ' SET last_seen=UTC_TIMESTAMP() WHERE id=%d', $s['id']));
        return $s;
    }
    public static string $request_csrf = '';
    public static function csrf(string $secret): string { return hash_hmac('sha256', 'vpn-chat-csrf:' . $secret, wp_salt('nonce')); }
    public static function bootstrap(): array {
        self::quota('bootstrap:' . self::ip(), 120, 600);
        if (!is_ssl() && !VPN_Chat_Settings::local()) { throw new VPN_Chat_Fault('https_required', 503); }
        try { $session = self::session(); $secret = $_COOKIE[self::COOKIE]; }
        catch (VPN_Chat_Fault $e) {
            if ($e->status !== 401) { throw $e; }
            $customer=VPN_Chat_Identity::restore() ?: VPN_Chat_Identity::create();
            $secret = bin2hex(random_bytes(32));
            $expires = time() + VPN_Chat_Settings::get()['absolute_days'] * DAY_IN_SECONDS;
            $id = VPN_Chat_Store::insert('sessions', ['customer_id'=>$customer,'secret_hash' => hash('sha256', $secret), 'created_at' => gmdate('Y-m-d H:i:s'), 'last_seen' => gmdate('Y-m-d H:i:s'), 'expires_at' => gmdate('Y-m-d H:i:s', $expires)]);
            setcookie(self::COOKIE, $secret, ['expires' => $expires, 'path' => '/', 'secure' => !VPN_Chat_Settings::local() || is_ssl(), 'httponly' => true, 'samesite' => 'Strict']);
            $session = ['id' => $id,'customer_id'=>$customer];
            $_COOKIE[self::COOKIE]=$secret;
        }
        global $wpdb;
        if(VPN_Chat_Identity::restore()!==(int)$session['customer_id'])VPN_Chat_Identity::grant((int)$session['customer_id']);
        $id = $wpdb->get_var($wpdb->prepare('SELECT public_id FROM ' . VPN_Chat_Store::table('conversations') . ' WHERE customer_id=%d ORDER BY (status NOT IN (\'closed\',\'spam\')) DESC,updated_at DESC,id DESC LIMIT 1', $session['customer_id']));
        return ['csrf' => self::csrf($secret), 'conversation' => $id, 'config' => VPN_Chat_Settings::public_config(),'customer'=>VPN_Chat_Identity::summary((int)$session['customer_id']),'history'=>VPN_Chat_Identity::history((int)$session['customer_id'])];
    }
    public static function challenge(string $token): void {
        if (!$token || strlen($token) > 2048 || !VPN_Chat_Settings::secret()) { throw new VPN_Chat_Fault('challenge_required', 403); }
        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', ['timeout' => 8, 'body' => ['secret' => VPN_Chat_Settings::secret(), 'response' => $token]]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) { throw new VPN_Chat_Fault('challenge_unavailable', 503); }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data) || ($data['success'] ?? false) !== true || ($data['action'] ?? '') !== 'vpn_chat_start' || strtolower($data['hostname'] ?? '') !== strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST))) { throw new VPN_Chat_Fault('challenge_required', 403); }
    }
    public static function text($value, int $max): string {
        if (!is_string($value) || !preg_match('//u', $value) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $value)) { throw new VPN_Chat_Fault('invalid_text'); }
        $value = trim($value);
        if ($value === '' || mb_strlen($value, 'UTF-8') > $max) { throw new VPN_Chat_Fault('invalid_text'); }
        return $value;
    }
    public static function client_id($value): string {
        if (!is_string($value) || !preg_match('/^[a-zA-Z0-9_-]{16,64}$/D', $value)) { throw new VPN_Chat_Fault('invalid_client_id'); }
        return $value;
    }
    public static function source($value): string {
        if (!is_string($value) || strlen($value) > 512 || !str_starts_with($value, '/') || str_starts_with($value, '//') || str_contains($value, '\\') || preg_match('/[?#\x00-\x20]/', $value)) { return ''; }
        return $value;
    }
}
