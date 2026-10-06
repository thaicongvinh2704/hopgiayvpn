<?php
defined('ABSPATH') || exit;
final class VPN_Chat_Settings {
    public static function get(): array {
        return array_merge([
            'widget' => false, 'accept_new' => false, 'paths' => [],
            'site_key' => '', 'fallback_url' => '', 'sales_email' => '',
            'timezone' => 'Asia/Ho_Chi_Minh', 'hours' => [], 'holidays' => [],
            'offline_copy' => 'Thanks for reaching out! Please leave your email address so we can get back to you as soon as possible.',
            'max_chars' => 2000, 'idle_hours' => 24, 'absolute_days' => 7,
            'retention_days' => 0, 'sla_minutes' => 15, 'heartbeat_seconds' => 30,
            'presence_seconds' => 90, 'short_limit' => 10, 'long_limit' => 60,
            'conversation_limit' => 3, 'bottom_offset' => 100,
            'support_name'=>'Tho Nguyen', 'support_avatar_id'=>0, 'shared_identity'=>false, 'always_online'=>false,
            'greeting_enabled'=>true, 'greeting_text'=>'Hi! How can we help with your packaging project?',
        ], (array) get_option('vpn_chat_settings', []));
    }
    public static function upgrade_invitation(): void {
        // Enable the approved invitation once on upgrade; later admin choices persist.
        if(get_option('vpn_chat_invitation_version')==='1.6.0')return;
        $settings=self::get();$settings['greeting_enabled']=true;
        update_option('vpn_chat_settings',$settings,false);
        update_option('vpn_chat_invitation_version','1.6.0',false);
    }
    public static function secret(): string {
        return defined('VPN_CHAT_TURNSTILE_SECRET') ? (string) VPN_CHAT_TURNSTILE_SECRET : '';
    }
    public static function upgrade_rate_limits(): void {
        if(get_option('vpn_chat_rate_limits_version')==='1.8.0')return;
        $s=self::get();
        // The approved upgrade enables chat on sites already displaying the widget.
        if($s['widget'])$s['accept_new']=true;
        update_option('vpn_chat_settings',$s,false);
        update_option('vpn_chat_rate_limits_version','1.8.0',false);
    }
    public static function ready(): bool {
        $s = self::get();
        return $s['accept_new'] && VPN_Chat_Schema::healthy()
            && (is_ssl() || self::local());
    }
    public static function local(): bool {
        return wp_get_environment_type() === 'local' && in_array(wp_parse_url(home_url(), PHP_URL_HOST), ['localhost', '127.0.0.1', '::1'], true);
    }
    public static function on_duty(?DateTimeImmutable $now = null): bool {
        $s = self::get();
        try { $now = ($now ?? new DateTimeImmutable('now'))->setTimezone(new DateTimeZone($s['timezone'])); }
        catch (Exception $e) { return false; }
        if (in_array($now->format('Y-m-d'), $s['holidays'], true)) { return false; }
        $slots = $s['hours'][$now->format('N')] ?? [];
        foreach ($slots as $slot) {
            if (is_array($slot) && count($slot) === 2 && $now->format('H:i') >= $slot[0] && $now->format('H:i') < $slot[1]) { return true; }
        }
        return false;
    }
    public static function public_config(int $owner=0): array {
        global $wpdb;
        $s = self::get();
        $sql='SELECT state FROM '.VPN_Chat_Store::table('agent_presence').' WHERE heartbeat_at > %s'.($owner ? ' AND user_id=%d' : '')." ORDER BY FIELD(state,'available','away','offline') LIMIT 1";
        $args=[gmdate('Y-m-d H:i:s',time()-$s['presence_seconds'])];if($owner)$args[]=$owner;
        $state=(string)$wpdb->get_var($wpdb->prepare($sql,$args));
        $presence=self::on_duty() ? (['available'=>'online','away'=>'away'][$state]??'offline') : 'offline';
        $agent_available=$presence==='online';
        if($s['always_online'])$presence='online';
        return ['accepting' => (bool) self::ready(), 'online' => $presence==='online', 'presence'=>$presence, 'agent_available'=>$agent_available, 'support'=>$owner ? VPN_Chat_Profiles::user($owner) : VPN_Chat_Profiles::support(), 'offline_copy' => $s['offline_copy'], 'site_key' => '', 'max_chars' => $s['max_chars'], 'fallback_url' => $s['fallback_url']];
    }
}
