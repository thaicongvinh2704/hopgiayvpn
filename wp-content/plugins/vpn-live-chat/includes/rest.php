<?php
defined('ABSPATH') || exit;
final class VPN_Chat_REST {
    public static function boot(): void {
        add_action('rest_api_init', [self::class, 'routes']);
        add_filter('rest_pre_dispatch',static function($result,$server,$request){
            if(str_starts_with($request->get_route(),'/vpn-chat/v1/')){
                try{VPN_Chat_Security::origin($request);}
                catch(VPN_Chat_Fault $e){return new WP_Error($e->getMessage(),$e->getMessage(),['status'=>$e->status]);}
            }
            return $result;
        },10,3);
        // WP's default reflected CORS headers are inappropriate for this cookie API.
        add_filter('rest_pre_serve_request', static function ($served, $result, $request) {
            if (str_starts_with($request->get_route(), '/vpn-chat/v1/')) {
                header_remove('Access-Control-Allow-Origin'); header_remove('Access-Control-Allow-Credentials');
                header('Cache-Control: private, no-store, max-age=0'); header('Vary: Cookie, Origin');
            }
            return $served;
        }, 20, 3);
        add_filter('rest_post_dispatch', static function ($response, $server, $request) {
            if (str_starts_with($request->get_route(), '/vpn-chat/v1/')) {
                $response->header('Cache-Control', 'private, no-store, max-age=0');
                $response->header('Vary', 'Cookie, Origin');
            }
            return $response;
        }, 20, 3);
    }
    public static function routes(): void {
        foreach (['bootstrap' => 'POST', 'guest/sync' => 'GET', 'guest/start' => 'POST', 'guest/send' => 'POST', 'guest/identity/request'=>'POST','guest/identity/verify'=>'POST','guest/history'=>'GET','guest/contact'=>'POST', 'guest/read'=>'POST', 'guest/revoke' => 'POST', 'agent/sync' => 'POST', 'agent/send' => 'POST', 'agent/update' => 'POST', 'agent/canned' => 'POST', 'manager/health' => 'GET', 'manager/block' => 'POST', 'manager/privacy' => 'POST'] as $path => $method) {
            register_rest_route('vpn-chat/v1', '/' . $path, ['methods' => $method, 'permission_callback' => [self::class, 'permission'], 'callback' => [self::class, 'dispatch']]);
        }
    }
    public static function permission(WP_REST_Request $r) {
        try {
            VPN_Chat_Security::origin($r);
            $path = $r->get_route();
            if (str_contains($path, '/agent/') || str_contains($path, '/manager/')) {
                if (!is_user_logged_in() || !wp_verify_nonce($r->get_header('x-wp-nonce'), 'wp_rest') || !current_user_can(str_contains($path, '/manager/') ? 'vpn_chat_manage' : 'vpn_chat_agent')) { throw new VPN_Chat_Fault('forbidden', 403); }
                VPN_Chat_Security::quota('agent:' . get_current_user_id(), 180, 60);
            }
            return true;
        } catch (VPN_Chat_Fault $e) { return new WP_Error($e->getMessage(), $e->getMessage(), ['status' => $e->status]); }
    }
    public static function dispatch(WP_REST_Request $r): WP_REST_Response {
        try {
            $p = $r->get_method() === 'GET' ? $r->get_query_params() : ($r->get_json_params() ?? []);
            $path = substr($r->get_route(), strlen('/vpn-chat/v1/'));
            VPN_Chat_Security::$request_csrf = (string) $r->get_header('x-vpn-csrf');
            if (in_array($path,['guest/start','guest/send','agent/send'],true)) {
                foreach (['attachment','attachments','file','files','upload','uploads'] as $field) {
                    if (array_key_exists($field,$p)) { throw new VPN_Chat_Fault('attachments_not_supported',400); }
                }
            }
            if ($path === 'bootstrap') { $data = VPN_Chat_Security::bootstrap(); }
            elseif (str_starts_with($path, 'guest/')) {
                $s = VPN_Chat_Security::session($r->get_method() !== 'GET');
                if($path==='guest/identity/request'){$data=VPN_Chat_Identity::request($s,$p);}
                elseif($path==='guest/identity/verify'){$data=VPN_Chat_Identity::verify($s,$p);}
                elseif($path==='guest/history'){VPN_Chat_Security::quota('history:'.$s['id'],30,60);$data=['history'=>VPN_Chat_Identity::history((int)$s['customer_id'])];}
                elseif ($path === 'guest/start') { $data = VPN_Chat_Service::start($s, $p); }
                elseif ($path === 'guest/send') { $data = VPN_Chat_Service::send((string) ($p['id'] ?? ''), $p, $s); }
                elseif ($path === 'guest/contact') {
                    VPN_Chat_Security::quota('contact:'.$s['id'],10,60);
                    $email=$p['email']??'';
                    if(!is_string($email))throw new VPN_Chat_Fault('invalid_email');
                    $email=trim($email);
                    if($email!=='' && (!is_email($email)||mb_strlen($email)>254))throw new VPN_Chat_Fault('invalid_email');
                    $data=VPN_Chat_Store::transaction(static function()use($p,$s,$email){
                        global $wpdb;
                        $c=VPN_Chat_Store::conversation((string)($p['id']??''),true);VPN_Chat_Store::authorize($c,$s);
                        VPN_Chat_Store::query($wpdb->prepare('UPDATE '.VPN_Chat_Store::table('conversations').' SET email=%s,version=version+1 WHERE id=%d',$email,$c['id']));
                        return ['saved'=>true,'email'=>$email];
                    });
                }
                elseif ($path === 'guest/read') {
                    global $wpdb;
                    VPN_Chat_Security::quota('read:'.$s['id'],60,60);
                    $c=VPN_Chat_Store::conversation((string)($p['id']??''));VPN_Chat_Store::authorize($c,$s);
                    $seq=max(0,min((int)$c['seq'],(int)($p['cursor']??0)));
                    VPN_Chat_Store::query($wpdb->prepare('UPDATE '.VPN_Chat_Store::table('conversations').' SET guest_read_seq=GREATEST(guest_read_seq,%d) WHERE id=%d',$seq,$c['id']));
                    $data=VPN_Chat_Store::guest_summary(VPN_Chat_Store::conversation($c['public_id']));
                }
                elseif ($path === 'guest/revoke') {
                    global $wpdb;
                    VPN_Chat_Store::query($wpdb->prepare('UPDATE ' . VPN_Chat_Store::table('sessions') . ' SET revoked=1 WHERE id=%d', $s['id']));
                    setcookie(VPN_Chat_Security::COOKIE, '', ['expires' => 1, 'path' => '/', 'secure' => !VPN_Chat_Settings::local(), 'httponly' => true, 'samesite' => 'Strict']);
                    VPN_Chat_Identity::forget();
                    $data = ['revoked' => true];
                } else {
                    VPN_Chat_Security::quota('sync:' . $s['id'], 60, 60);
                    $c = VPN_Chat_Store::conversation((string) ($p['id'] ?? ''));
                    VPN_Chat_Store::authorize($c, $s);
                    $data = VPN_Chat_Store::messages($c, (int) ($p['cursor'] ?? 0), true) + ['conversation' => VPN_Chat_Store::guest_summary($c), 'config' => VPN_Chat_Settings::public_config((int)$c['owner_id'])];
                }
            } elseif ($path === 'agent/sync') { $data = self::sync($p); }
            elseif ($path === 'agent/send') { $data = VPN_Chat_Service::send((string) ($p['id'] ?? ''), $p, null); }
            elseif ($path === 'agent/update') { $data = VPN_Chat_Service::update((string) ($p['id'] ?? ''), $p); }
            elseif ($path === 'agent/canned') { $data = self::canned($p); }
            elseif ($path === 'manager/health') { $data = VPN_Chat_Jobs::health(); }
            elseif ($path === 'manager/block') { $data = self::block($p); }
            else { $data = self::privacy($p); }
            $response = new WP_REST_Response($data, 200);
        } catch (VPN_Chat_Fault $e) {
            $response = new WP_REST_Response(['code' => $e->getMessage()], $e->status);
            if ($e->status === 429) { $response->header('Retry-After', (string)($e->retry_after?:30)); }
        } catch (Throwable $e) {
            $response = new WP_REST_Response(['code' => 'service_unavailable'], 503);
        }
        $response->header('Cache-Control', 'private, no-store, max-age=0');
        return $response;
    }
    public static function sync(array $p): array {
        global $wpdb;
        if (isset($p['presence']) && in_array($p['presence'], ['available', 'away', 'offline'], true)) {
            $interval = VPN_Chat_Settings::get()['heartbeat_seconds'];
            VPN_Chat_Store::query($wpdb->prepare('INSERT INTO ' . VPN_Chat_Store::table('agent_presence') . ' (user_id,state,heartbeat_at) VALUES (%d,%s,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE heartbeat_at=IF(state<>VALUES(state) OR heartbeat_at<=DATE_SUB(UTC_TIMESTAMP(),INTERVAL %d SECOND),UTC_TIMESTAMP(),heartbeat_at),state=VALUES(state)', get_current_user_id(), $p['presence'], $interval));
        }
        $filter = $p['filter'] ?? 'unassigned';
        $where = '1=1'; $args = [];
        if (!current_user_can('vpn_chat_manage')) { $where .= " AND ((status='unassigned' AND owner_id=0) OR owner_id=%d)"; $args[] = get_current_user_id(); }
        if ($filter === 'mine') { $where .= ' AND owner_id=%d AND status NOT IN (\'closed\',\'spam\')'; $args[] = get_current_user_id(); }
        elseif ($filter === 'inbox') { $where .= " AND status NOT IN ('closed','spam')"; }
        elseif (in_array($filter, ['unassigned','follow_up','closed','spam'], true)) { $where .= ' AND status=%s'; $args[] = $filter; }
        elseif ($filter !== 'all' || !current_user_can('vpn_chat_manage')) { throw new VPN_Chat_Fault('invalid_filter'); }
        if (!empty($p['search'])) { $term = '%' . $wpdb->esc_like(mb_substr((string) $p['search'], 0, 100)) . '%'; $where .= ' AND (name LIKE %s OR email LIKE %s OR CONCAT(\'Khách #\',LPAD(customer_id,6,\'0\')) LIKE %s OR customer_id IN (SELECT id FROM '.VPN_Chat_Store::table('customers').' WHERE verified_email LIKE %s))'; $args[] = $term; $args[] = $term; $args[]=$term;$args[]=$term; }
        $page = max(1, min(1000, (int) ($p['page'] ?? 1)));
        // Group only conversations visible under the agent's existing scope/filter.
        $table=VPN_Chat_Store::table('conversations');
        $eligible="SELECT * FROM $table WHERE $where";
        if($args)$eligible=$wpdb->prepare($eligible,$args);
        $sql="SELECT c.customer_id,c.public_id AS id,c.name,c.email,c.source_path,c.status,c.label,c.owner_id,c.version,c.guest_seq,c.read_seq,c.created_at,c.updated_at,c.follow_up_at,g.group_unread,g.conversation_count,(SELECT LEFT(body,160) FROM ".VPN_Chat_Store::table('messages')." m WHERE m.conversation_id=c.id AND m.sender!='note' ORDER BY m.seq DESC LIMIT 1) AS preview FROM ($eligible) c JOIN (SELECT customer_id,MAX(CONCAT(updated_at,LPAD(id,20,'0'))) AS latest,MAX(guest_seq>read_seq) AS group_unread,COUNT(*) AS conversation_count FROM ($eligible) e GROUP BY customer_id) g ON g.customer_id=c.customer_id AND CONCAT(c.updated_at,LPAD(c.id,20,'0'))=g.latest ORDER BY c.updated_at DESC,c.id DESC LIMIT 26 OFFSET ".(($page-1)*25);
        $list=$wpdb->get_results($sql,ARRAY_A);if($wpdb->last_error)throw new VPN_Chat_Fault('storage_unavailable',503);
        $has_more = count($list) > 25; $list = array_slice($list, 0, 25);
        foreach ($list as &$row) {
            $row['unread'] = !empty($row['group_unread']);
            $identity=VPN_Chat_Identity::summary((int)$row['customer_id']);$row['customer_code']=$identity['code'];$row['verified_email']=$identity['verified_email'];if($row['verified_email'])$row['email']=$row['verified_email'];
            $row['overdue'] = ($row['status'] === 'unassigned' && strtotime($row['created_at'] . ' UTC') < time() - VPN_Chat_Settings::get()['sla_minutes'] * 60) || ($row['follow_up_at'] && strtotime($row['follow_up_at'] . ' UTC') <= time());
        } unset($row);
        $data = ['list' => $list, 'has_more' => $has_more, 'page' => $page, 'canned' => $wpdb->get_results('SELECT id,title,body FROM ' . VPN_Chat_Store::table('canned') . ' ORDER BY id LIMIT 100', ARRAY_A)];
        if (!empty($p['id'])) {
            $c = VPN_Chat_Store::conversation((string) $p['id']); VPN_Chat_Store::authorize($c);
            $identity=VPN_Chat_Identity::summary((int)$c['customer_id']);$c['customer_code']=$identity['code'];$c['verified_email']=$identity['verified_email'];if($c['verified_email'])$c['email']=$c['verified_email'];$c['history']=VPN_Chat_Identity::history((int)$c['customer_id'],false);
            $data['selected'] = $c;
            $data['delta'] = VPN_Chat_Store::messages($c, (int) ($p['cursor'] ?? 0), false);
        }
        return $data;
    }
    public static function canned(array $p): array {
        global $wpdb;
        if (!current_user_can('vpn_chat_manage')) { throw new VPN_Chat_Fault('forbidden', 403); }
        $count = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . VPN_Chat_Store::table('canned'));
        if (($p['action'] ?? '') === 'delete') { $wpdb->delete(VPN_Chat_Store::table('canned'), ['id' => (int) ($p['id'] ?? 0)]); }
        else {
            $data = ['title' => VPN_Chat_Security::text($p['title'] ?? null,100), 'body' => VPN_Chat_Security::text($p['body'] ?? null,VPN_Chat_Settings::get()['max_chars'])];
            if (!empty($p['id'])) { $wpdb->update(VPN_Chat_Store::table('canned'), $data, ['id' => (int)$p['id']]); }
            else { if ($count >= 100) { throw new VPN_Chat_Fault('canned_limit'); } VPN_Chat_Store::insert('canned', $data); }
        }
        VPN_Chat_Store::audit('canned_' . ($p['action'] ?? 'save'), (int) ($p['id'] ?? 0));
        return ['saved' => true];
    }
    public static function block(array $p): array {
        global $wpdb;
        if (($p['action'] ?? '') === 'list') { return ['blocks' => $wpdb->get_results('SELECT * FROM ' . VPN_Chat_Store::table('blocks') . ' ORDER BY id DESC LIMIT 100', ARRAY_A)]; }
        if (($p['action'] ?? '') === 'unban') {
            $wpdb->update(VPN_Chat_Store::table('blocks'), ['revoked' => 1], ['id' => (int) ($p['block_id'] ?? 0)]);
            VPN_Chat_Store::audit('unban', (int)($p['block_id'] ?? 0));
        } else {
            $c = VPN_Chat_Store::conversation((string)($p['id'] ?? ''));
            if (($p['action'] ?? '') === 'challenge') { throw new VPN_Chat_Fault('unsupported_action',400); }
            else {
                VPN_Chat_Store::insert('blocks', ['session_id' => $c['session_id'], 'reason' => VPN_Chat_Security::text($p['reason'] ?? null,240), 'actor_id' => get_current_user_id(), 'expires_at' => gmdate('Y-m-d H:i:s', time() + max(1,min(168,(int)($p['hours'] ?? 24))) * 3600), 'created_at' => gmdate('Y-m-d H:i:s')]);
                VPN_Chat_Store::audit('block', (int)$c['id']);
            }
        }
        return ['saved' => true];
    }
    public static function privacy(array $p): array {
        global $wpdb;
        $c = VPN_Chat_Store::conversation((string)($p['id'] ?? ''));
        if (($p['action'] ?? '') === 'export') {
            VPN_Chat_Store::audit('export', (int)$c['id']);
            $after=max(0,(int)($p['cursor']??0));
            $messages=$wpdb->get_results($wpdb->prepare('SELECT seq,sender,body,created_at FROM ' . VPN_Chat_Store::table('messages') . ' WHERE conversation_id=%d AND seq>%d AND seq<=%d ORDER BY seq LIMIT 1000', $c['id'],$after,$c['seq']),ARRAY_A);
            $next=$messages?(int)end($messages)['seq']:$after;
            return ['conversation'=>$c,'messages'=>$messages,'cursor'=>$next,'more'=>$next<(int)$c['seq']];
        }
        if (($p['action'] ?? '') !== 'delete' || empty($p['verified_request'])) { throw new VPN_Chat_Fault('verified_request_required'); }
        VPN_Chat_Jobs::erase((int)$c['id']);
        return ['deleted' => true];
    }
}
