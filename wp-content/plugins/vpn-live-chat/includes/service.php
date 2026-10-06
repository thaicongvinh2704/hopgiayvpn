<?php
defined('ABSPATH') || exit;
final class VPN_Chat_Service {
    const STATES = ['unassigned', 'assigned', 'waiting_customer', 'follow_up', 'closed', 'spam'];
    const LABELS = ['', 'needs_sample', 'needs_quote', 'qualified', 'quote_sent', 'won', 'lost'];
    private static function receipt(array $c, int $seq, string $sender): array {
        global $wpdb;
        $result=['saved'=>true,'seq'=>$seq,'id'=>$c['public_id']];
        if($sender==='guest')$result['message']=$wpdb->get_row($wpdb->prepare('SELECT seq,sender,body,created_at FROM '.VPN_Chat_Store::table('messages')." WHERE conversation_id=%d AND seq=%d AND sender='guest'",$c['id'],$seq),ARRAY_A);
        return $result;
    }
    public static function send_locked(array $c, string $body, string $client, string $sender, int $actor): array {
        global $wpdb;
        $scope = $sender === 'guest' ? 'guest' : 'user:' . $actor;
        $hash = hash('sha256', $sender . ':' . $body);
        $old = $wpdb->get_row($wpdb->prepare('SELECT seq,payload_hash FROM ' . VPN_Chat_Store::table('messages') . ' WHERE conversation_id=%d AND sender_scope=%s AND client_message_id=%s', $c['id'], $scope, $client), ARRAY_A);
        if ($old) {
            if (!hash_equals($old['payload_hash'], $hash)) { throw new VPN_Chat_Fault('idempotency_conflict', 409); }
            return self::receipt($c,(int)$old['seq'],$sender);
        }
        if ($sender !== 'note' && in_array($c['status'], ['spam', 'closed'], true)) { throw new VPN_Chat_Fault('conversation_closed', 409); }
        $seq = (int) $c['seq'] + 1;
        VPN_Chat_Store::insert('messages', ['conversation_id' => $c['id'], 'seq' => $seq, 'sender' => $sender, 'actor_id' => $actor, 'sender_profile'=>$actor ? wp_json_encode(VPN_Chat_Profiles::user($actor)) : null, 'sender_scope' => $scope, 'client_message_id' => $client, 'payload_hash' => $hash, 'body' => $body, 'created_at' => gmdate('Y-m-d H:i:s')]);
        $status = $sender === 'agent' ? 'waiting_customer' : ($sender === 'guest' && $c['owner_id'] ? 'assigned' : $c['status']);
        VPN_Chat_Store::query($wpdb->prepare('UPDATE ' . VPN_Chat_Store::table('conversations') . ' SET seq=%d,guest_seq=%d,status=%s,version=version+1,updated_at=UTC_TIMESTAMP() WHERE id=%d', $seq, $sender === 'guest' ? $seq : $c['guest_seq'], $status, $c['id']));
        if ($sender === 'guest') {
            VPN_Chat_Store::query($wpdb->prepare('INSERT IGNORE INTO ' . VPN_Chat_Store::table('outbox') . ' (conversation_id,dedup_key,due_at) VALUES (%d,%s,%s)', $c['id'], 'unassigned:' . $c['id'], gmdate('Y-m-d H:i:s', time() + VPN_Chat_Settings::get()['sla_minutes'] * 60)));
        }
        return self::receipt($c,$seq,$sender);
    }
    public static function start(array $s, array $p): array {
        global $wpdb;
        $body = VPN_Chat_Security::text($p['message'] ?? null, VPN_Chat_Settings::get()['max_chars']);
        $client = VPN_Chat_Security::client_id($p['client_message_id'] ?? null);
        $name = $p['name'] ?? '';
        if (!is_string($name)) { throw new VPN_Chat_Fault('invalid_text'); }
        $name = trim($name);
        if ($name !== '') { $name = VPN_Chat_Security::text($name, 100); }
        $email = $p['email'] ?? '';
        if (!is_string($email)) { throw new VPN_Chat_Fault('invalid_email'); }
        $email = trim($email);
        if ($email !== '') {
            $email = VPN_Chat_Security::text($email, 254);
            if (!is_email($email)) { throw new VPN_Chat_Fault('invalid_email'); }
        }
        $metadata = [];
        foreach (['utm_source','utm_medium','utm_campaign'] as $key) {
            if (isset($p['metadata'][$key]) && is_string($p['metadata'][$key])) { $metadata[$key] = sanitize_text_field(mb_substr($p['metadata'][$key], 0, 100)); }
        }
        $path = VPN_Chat_Security::source($p['source_path'] ?? '');
        $fingerprint = hash('sha256', wp_json_encode([$name,$email,$body,$path,$metadata]));
        // Retry discovery is session scoped; never look up by email. Payload includes all lead fields.
        $old = $wpdb->get_row($wpdb->prepare('SELECT c.public_id,c.metadata FROM ' . VPN_Chat_Store::table('conversations') . ' c JOIN ' . VPN_Chat_Store::table('messages') . " m ON m.conversation_id=c.id AND m.seq=1 WHERE c.customer_id=%d AND m.client_message_id=%s AND m.sender_scope='guest' LIMIT 1", $s['customer_id'], $client), ARRAY_A);
        if ($old) {
            if ((json_decode($old['metadata'], true)['start_hash'] ?? '') !== $fingerprint) { throw new VPN_Chat_Fault('idempotency_conflict', 409); }
            return self::receipt(VPN_Chat_Store::conversation($old['public_id']),1,'guest');
        }
        if (!VPN_Chat_Settings::ready()) { throw new VPN_Chat_Fault('new_chat_unavailable', 503); }
        VPN_Chat_Security::message_rate($s);
        VPN_Chat_Security::quota('create:c:' . $s['customer_id'], VPN_Chat_Settings::get()['conversation_limit'], 600);
        VPN_Chat_Security::quota('create:ip:' . VPN_Chat_Security::ip(), 100, 600);
        return VPN_Chat_Store::transaction(static function () use ($wpdb, $s, $client, $body, $name, $email, $path, $metadata, $fingerprint) {
            // A session lock prevents simultaneous starts creating multiple conversations.
            $locked_session=$wpdb->get_row($wpdb->prepare('SELECT * FROM ' . VPN_Chat_Store::table('sessions') . ' WHERE id=%d FOR UPDATE', $s['id']),ARRAY_A);
            if(!$locked_session || $locked_session['revoked'] || (int)$locked_session['customer_id']!==(int)$s['customer_id'] || strtotime($locked_session['expires_at'].' UTC')<=time()){throw new VPN_Chat_Fault('session_expired',401);}
            VPN_Chat_Store::query($wpdb->prepare('SELECT id FROM '.VPN_Chat_Store::table('customers').' WHERE id=%d FOR UPDATE',$s['customer_id']));
            $existing = $wpdb->get_var($wpdb->prepare('SELECT public_id FROM ' . VPN_Chat_Store::table('conversations') . ' WHERE customer_id=%d AND status NOT IN (\'closed\',\'spam\') LIMIT 1', $s['customer_id']));
            if ($existing) { throw new VPN_Chat_Fault('active_conversation_exists', 409); }
            $id = wp_generate_uuid4();
            $internal = VPN_Chat_Store::insert('conversations', ['public_id' => $id, 'session_id' => $s['id'], 'customer_id'=>$s['customer_id'], 'name' => $name, 'email' => $email, 'source_path' => $path, 'metadata' => wp_json_encode($metadata + ['start_hash' => $fingerprint]), 'needs' => '', 'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s')]);
            $c = VPN_Chat_Store::conversation($id, true);
            $result = self::send_locked($c, $body, $client, 'guest', 0);
            VPN_Chat_Store::audit('created', $internal);
            return $result;
        });
    }
    public static function send(string $id, array $p, ?array $s): array {
        $body = VPN_Chat_Security::text($p['message'] ?? null, VPN_Chat_Settings::get()['max_chars']);
        $client = VPN_Chat_Security::client_id($p['client_message_id'] ?? null);
        if ($s) {
            global $wpdb;
            $prior=VPN_Chat_Store::conversation($id);VPN_Chat_Store::authorize($prior,$s,true);
            $retry=$wpdb->get_var($wpdb->prepare('SELECT id FROM '.VPN_Chat_Store::table('messages')." WHERE conversation_id=%d AND sender_scope='guest' AND client_message_id=%s",$prior['id'],$client));
            if(!$retry)VPN_Chat_Security::message_rate($s);
        }
        return VPN_Chat_Store::transaction(static function () use ($id, $body, $client, $s, $p) {
            if ($s) {
                global $wpdb;
                $current = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . VPN_Chat_Store::table('sessions') . ' WHERE id=%d FOR UPDATE', $s['id']), ARRAY_A);
                if (!$current || $current['revoked'] || (int)$current['customer_id']!==(int)$s['customer_id'] || strtotime($current['expires_at'].' UTC')<=time()) { throw new VPN_Chat_Fault('session_expired',401); }
            }
            $c = VPN_Chat_Store::conversation($id, true);
            VPN_Chat_Store::authorize($c, $s, true);
            return self::send_locked($c, $body, $client, $s ? 'guest' : (!empty($p['note']) ? 'note' : 'agent'), $s ? 0 : get_current_user_id());
        });
    }
    public static function update(string $id, array $p): array {
        global $wpdb;
        return VPN_Chat_Store::transaction(static function () use ($wpdb,$id,$p) {
            $c = VPN_Chat_Store::conversation($id, true);
            $claim = ($p['action'] ?? '') === 'claim';
            if ($claim && ($c['owner_id'] || $c['status'] !== 'unassigned')) { throw new VPN_Chat_Fault('already_claimed',409); }
            VPN_Chat_Store::authorize($c, null, !$claim);
            if ((int) ($p['version'] ?? 0) !== (int) $c['version']) { throw new VPN_Chat_Fault('version_conflict', 409); }
            $updates = [];
            if ($claim) {
                if ($c['owner_id'] || $c['status'] !== 'unassigned') { throw new VPN_Chat_Fault('already_claimed', 409); }
                $updates = ['owner_id' => get_current_user_id(), 'status' => 'assigned', 'read_seq' => 0];
            } else {
                if (isset($p['owner_id'])) {
                    $owner = (int) $p['owner_id'];
                    $user = get_user_by('id', $owner);
                    if ($owner && (!$user || !user_can($user, 'vpn_chat_agent'))) { throw new VPN_Chat_Fault('invalid_agent'); }
                    $updates['owner_id'] = $owner;
                    $updates['read_seq'] = 0;
                    $updates['status'] = $owner ? 'assigned' : 'unassigned';
                }
                if (isset($p['status'])) {
                    if (!in_array($p['status'], self::STATES, true) || ($p['status'] === 'unassigned' && ($updates['owner_id'] ?? $c['owner_id'])) || (in_array($p['status'], ['assigned','waiting_customer'], true) && !($updates['owner_id'] ?? $c['owner_id']))) { throw new VPN_Chat_Fault('invalid_status'); }
                    $updates['status'] = $p['status'];
                }
                if (isset($p['label'])) { if (!in_array($p['label'], self::LABELS, true)) { throw new VPN_Chat_Fault('invalid_label'); } $updates['label'] = $p['label']; }
                if (isset($p['needs'])) { $updates['needs'] = $p['needs'] === '' ? '' : VPN_Chat_Security::text($p['needs'], 2000); }
                if (array_key_exists('follow_up_at', $p)) {
                    $date = $p['follow_up_at'];
                    if ($date && (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $date) || !strtotime($date . ' UTC'))) { throw new VPN_Chat_Fault('invalid_date'); }
                    $updates['follow_up_at'] = $date ?: null;
                    if ($date && !in_array($updates['status']??$c['status'],['closed','spam'],true)) { $updates['status'] = 'follow_up'; }
                }
                if (isset($p['read_seq'])) { $updates['read_seq'] = max((int)$c['read_seq'], min((int)$c['seq'], (int)$p['read_seq'])); }
            }
            if(in_array($updates['status']??'', ['closed','spam'],true)){$updates['follow_up_at']=null;}
            if(array_diff_key($updates,['read_seq'=>true])){$updates['updated_at']=gmdate('Y-m-d H:i:s');}
            $updates['version'] = (int) $c['version'] + 1;
            if ($wpdb->update(VPN_Chat_Store::table('conversations'), $updates, ['id' => $c['id'], 'version' => $c['version']]) !== 1) { throw new VPN_Chat_Fault('version_conflict', 409); }
            if(($updates['status']??$c['status'])==='unassigned' && !($updates['owner_id']??$c['owner_id']) && $c['status']!=='unassigned'){
                VPN_Chat_Store::query($wpdb->prepare('INSERT IGNORE INTO ' . VPN_Chat_Store::table('outbox') . ' (conversation_id,dedup_key,due_at) VALUES (%d,%s,%s)',$c['id'],'unassigned:'.$c['id'].':v'.$updates['version'],gmdate('Y-m-d H:i:s',time()+VPN_Chat_Settings::get()['sla_minutes']*60)));
            }
            VPN_Chat_Store::audit($claim ? 'claim' : 'update', (int) $c['id'], array_diff_key($updates, ['needs' => true]));
            return VPN_Chat_Store::conversation($id);
        });
    }
}
