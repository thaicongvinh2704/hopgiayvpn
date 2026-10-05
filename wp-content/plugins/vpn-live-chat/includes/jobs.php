<?php
defined('ABSPATH') || exit;
final class VPN_Chat_Jobs {
    public static function boot(): void {
        add_filter('cron_schedules', static function ($s) { $s['vpn_chat_minute'] = ['interval' => 60, 'display' => 'VPN Chat mỗi phút']; return $s; });
        add_action('vpn_chat_worker', [self::class, 'run']);
        self::schedule();
    }
    public static function schedule(): void {
        add_filter('cron_schedules', static function ($s) { $s['vpn_chat_minute'] = ['interval' => 60, 'display' => 'VPN Chat mỗi phút']; return $s; });
        if (!wp_next_scheduled('vpn_chat_worker')) { wp_schedule_event(time()+60, 'vpn_chat_minute', 'vpn_chat_worker'); }
    }
    public static function deactivate(): void {
        global $wpdb;
        wp_clear_scheduled_hook('vpn_chat_worker');
        $wpdb->query('UPDATE ' . VPN_Chat_Store::table('agent_presence') . " SET state='offline'");
    }
    public static function run(): void {
        global $wpdb;
        $deadline = microtime(true) + 15;
        for ($n=0; $n<10 && microtime(true)<$deadline; $n++) {
            $lease = bin2hex(random_bytes(16));
            $row = VPN_Chat_Store::transaction(static function () use ($wpdb,$lease) {
                $row = $wpdb->get_row('SELECT * FROM ' . VPN_Chat_Store::table('outbox') . " WHERE state='pending' AND due_at<=UTC_TIMESTAMP() AND (lease_until IS NULL OR lease_until<UTC_TIMESTAMP()) ORDER BY id LIMIT 1 FOR UPDATE", ARRAY_A);
                if (!$row) { return null; }
                VPN_Chat_Store::query($wpdb->prepare('UPDATE ' . VPN_Chat_Store::table('outbox') . ' SET lease=%s,lease_until=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 120 SECOND) WHERE id=%d', $lease,$row['id']));
                return $row;
            });
            if (!$row) { break; }
            $c = $wpdb->get_row($wpdb->prepare('SELECT owner_id,status FROM ' . VPN_Chat_Store::table('conversations') . ' WHERE id=%d', $row['conversation_id']), ARRAY_A);
            $skip = !$c || $c['owner_id'] || $c['status'] !== 'unassigned';
            $email = VPN_Chat_Settings::get()['sales_email'];
            $timeout=static function($mailer){$mailer->Timeout=10;$mailer->Timelimit=10;};
            add_action('phpmailer_init',$timeout,PHP_INT_MAX);
            try { $sent = $skip || (is_email($email) && wp_mail($email, 'VPN Live Chat: hội thoại chờ nhận', "Có hội thoại chờ quá thời gian SLA. Đăng nhập inbox để xử lý:\n" . admin_url('admin.php?page=vpn-live-chat'))); }
            finally { remove_action('phpmailer_init',$timeout,PHP_INT_MAX); }
            $attempts = (int)$row['attempts'] + 1;
            VPN_Chat_Store::query($wpdb->prepare('UPDATE ' . VPN_Chat_Store::table('outbox') . ' SET state=%s,attempts=%d,due_at=%s,lease=NULL,lease_until=NULL,last_error=%s WHERE id=%d AND lease=%s', $sent ? 'done' : ($attempts>=8 ? 'failed' : 'pending'), $attempts, gmdate('Y-m-d H:i:s',time()+min(3600,60*(2 ** min(6,$attempts)))), $sent ? '' : 'mail_transport_failed', $row['id'],$lease));
        }
        $wpdb->query('DELETE FROM ' . VPN_Chat_Store::table('rate_limits') . ' WHERE expires_at<UTC_TIMESTAMP() LIMIT 1000');
        $wpdb->query('DELETE FROM '.VPN_Chat_Store::table('email_codes').' WHERE expires_at<UTC_TIMESTAMP() LIMIT 1000');
        $wpdb->query('DELETE FROM '.VPN_Chat_Store::table('devices').' WHERE expires_at<UTC_TIMESTAMP() OR revoked=1 LIMIT 1000');
        $days = (int) VPN_Chat_Settings::get()['retention_days'];
        if ($days > 0) {
            $ids = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . VPN_Chat_Store::table('conversations') . " WHERE status IN ('closed','spam') AND updated_at<%s LIMIT 20",gmdate('Y-m-d H:i:s',time()-$days*DAY_IN_SECONDS)));
            foreach ($ids as $id) { self::erase((int)$id); }
        }
        // Keep expired session records while their conversations are retained.
        $wpdb->query('UPDATE ' . VPN_Chat_Store::table('sessions') . ' SET revoked=1 WHERE expires_at<UTC_TIMESTAMP()');
        $wpdb->query('DELETE s FROM ' . VPN_Chat_Store::table('sessions') . ' s LEFT JOIN ' . VPN_Chat_Store::table('conversations') . ' c ON c.session_id=s.id LEFT JOIN '.VPN_Chat_Store::table('blocks').' b ON b.session_id=s.id AND b.revoked=0 AND b.expires_at>UTC_TIMESTAMP() WHERE s.revoked=1 AND c.id IS NULL AND b.id IS NULL');
        update_option('vpn_chat_last_runner', gmdate('c'), false);
    }
    public static function erase(int $id): void {
        global $wpdb;
        VPN_Chat_Store::transaction(static function () use ($wpdb,$id) {
            $customer=(int)$wpdb->get_var($wpdb->prepare('SELECT customer_id FROM '.VPN_Chat_Store::table('conversations').' WHERE id=%d',$id));
            $s = $wpdb->get_var($wpdb->prepare('SELECT session_id FROM ' . VPN_Chat_Store::table('conversations') . ' WHERE id=%d FOR UPDATE',$id));
            foreach (['messages','outbox'] as $t) { VPN_Chat_Store::query($wpdb->prepare('DELETE FROM ' . VPN_Chat_Store::table($t) . ' WHERE conversation_id=%d',$id)); }
            VPN_Chat_Store::query($wpdb->prepare('DELETE FROM ' . VPN_Chat_Store::table('conversations') . ' WHERE id=%d',$id));
            VPN_Chat_Store::query($wpdb->prepare('DELETE FROM ' . VPN_Chat_Store::table('audit') . " WHERE target_id=%d AND action IN ('created','claim','update','export','challenge','block','erased')",$id));
            if($customer){$wpdb->update(VPN_Chat_Store::table('devices'),['revoked'=>1],['customer_id'=>$customer]);$wpdb->update(VPN_Chat_Store::table('sessions'),['revoked'=>1],['customer_id'=>$customer]);}
            if ($s) { $wpdb->update(VPN_Chat_Store::table('sessions'),['revoked'=>1],['id'=>$s]); }
            if($customer && !$wpdb->get_var($wpdb->prepare('SELECT id FROM '.VPN_Chat_Store::table('conversations').' WHERE customer_id=%d LIMIT 1',$customer))){
                VPN_Chat_Store::query($wpdb->prepare('DELETE r FROM '.VPN_Chat_Store::table('email_codes').' r JOIN '.VPN_Chat_Store::table('sessions').' s ON s.id=r.session_id WHERE s.customer_id=%d',$customer));
                $wpdb->delete(VPN_Chat_Store::table('customers'),['id'=>$customer]);
            }
            VPN_Chat_Store::audit('erased', $id);
        });
    }
    public static function health(): array {
        global $wpdb;
        return ['innodb' => VPN_Chat_Schema::healthy(), 'accepting' => VPN_Chat_Settings::ready(), 'turnstile_configured' => (bool)VPN_Chat_Settings::secret(), 'cron_disabled' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON, 'last_runner' => get_option('vpn_chat_last_runner',null), 'pending' => (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . VPN_Chat_Store::table('outbox') . " WHERE state='pending'"), 'failed' => (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . VPN_Chat_Store::table('outbox') . " WHERE state='failed'"), 'oldest_due' => $wpdb->get_var('SELECT MIN(due_at) FROM ' . VPN_Chat_Store::table('outbox') . " WHERE state='pending'"), 'smtp_adapter_present' => class_exists('VPN_Gmail_SMTP'), 'smtp_ready' => class_exists('VPN_Gmail_SMTP') && VPN_Gmail_SMTP::ready(), 'local_outbound_disabled' => VPN_Chat_Settings::local()];
    }
}
