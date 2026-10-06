<?php
defined('ABSPATH') || exit;
final class VPN_Chat_Fault extends RuntimeException {
    public int $status;
    public int $retry_after = 0;
    public function __construct(string $code, int $status = 400) { parent::__construct($code); $this->status = $status; }
}
final class VPN_Chat_Store {
    public static function table(string $name): string { global $wpdb; return $wpdb->prefix . 'vpn_chat_' . $name; }
    public static function query(string $sql): int {
        global $wpdb;
        $result = $wpdb->query($sql);
        if ($result === false) { throw new VPN_Chat_Fault('storage_unavailable', 503); }
        return (int) $result;
    }
    public static function insert(string $name, array $data): int {
        global $wpdb;
        if ($wpdb->insert(self::table($name), $data) === false) { throw new VPN_Chat_Fault('storage_unavailable', 503); }
        return (int) $wpdb->insert_id;
    }
    public static function transaction(callable $fn) {
        self::query('START TRANSACTION');
        try { $value = $fn(); self::query('COMMIT'); return $value; }
        catch (Throwable $e) { self::query('ROLLBACK'); throw $e; }
    }
    public static function conversation(string $id, bool $lock = false): array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . self::table('conversations') . ' WHERE public_id=%s' . ($lock ? ' FOR UPDATE' : ''), $id), ARRAY_A);
        if (!$row) { throw new VPN_Chat_Fault('not_found', 404); }
        return $row;
    }
    public static function audit(string $action, int $target, array $metadata = []): void {
        self::insert('audit', ['actor_id' => get_current_user_id(), 'action' => $action, 'target_id' => $target, 'metadata' => wp_json_encode($metadata), 'created_at' => gmdate('Y-m-d H:i:s')]);
    }
    public static function authorize(array $c, ?array $session = null, bool $write = false): void {
        if ($session !== null) {
            if (empty($session['customer_id']) || (int) $c['customer_id'] !== (int) $session['customer_id']) { throw new VPN_Chat_Fault('not_found', 404); }
        } elseif (!current_user_can('vpn_chat_manage') && ((int) $c['owner_id'] !== get_current_user_id() && !(!$write && $c['status'] === 'unassigned' && !$c['owner_id']))) {
            throw new VPN_Chat_Fault('forbidden', 403);
        }
    }
    public static function messages(array $c, int $cursor, bool $guest): array {
        global $wpdb;
        if ($cursor < 0 || $cursor > (int) $c['seq']) { throw new VPN_Chat_Fault('invalid_cursor'); }
        $rows = $wpdb->get_results($wpdb->prepare('SELECT seq,sender,actor_id,sender_profile,body,created_at FROM ' . self::table('messages') . ' WHERE conversation_id=%d AND seq>%d AND seq<=%d' . ($guest ? " AND sender!='note'" : '') . ' ORDER BY seq LIMIT 50', $c['id'], $cursor, $c['seq']), ARRAY_A);
        foreach($rows as &$row){
            if($row['sender']!=='guest')$row['profile']=json_decode((string)($row['sender_profile']??''),true) ?: VPN_Chat_Profiles::user((int)$row['actor_id']);
            unset($row['actor_id'],$row['sender_profile']);
        }unset($row);
        $next = count($rows) === 50 ? (int) end($rows)['seq'] : (int) $c['seq'];
        return ['messages' => $rows, 'cursor' => $next, 'more' => $next < (int) $c['seq']];
    }
    public static function guest_summary(array $c): array {
        global $wpdb;
        $unread=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.self::table('messages')." WHERE conversation_id=%d AND sender='agent' AND seq>%d",$c['id'],(int)$c['guest_read_seq']));
        return ['id' => $c['public_id'], 'email'=>$c['email'], 'status' => $c['status'] === 'spam' ? 'closed' : $c['status'], 'unread'=>$unread,'guest_read_seq'=>(int)$c['guest_read_seq'],'agent_read_seq'=>(int)$c['read_seq']];
    }
}
