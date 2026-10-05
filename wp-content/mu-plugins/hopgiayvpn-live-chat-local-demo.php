<?php
/** Local preview adapter only. NEVER include this file in a production release. */
defined('ABSPATH') || exit;
function vpn_chat_local_demo_allowed(): bool {
    $peer=(string)($_SERVER['REMOTE_ADDR']??'');
    return wp_get_environment_type()==='local' && DB_NAME==='hopgiayvpnmoi'
        && in_array(strtolower((string)wp_parse_url(home_url(),PHP_URL_HOST)),['localhost','127.0.0.1','::1'],true)
        && (PHP_SAPI==='cli' || in_array($peer,['127.0.0.1','::1'],true));
}
if(!vpn_chat_local_demo_allowed())return;
if(defined('VPN_CHAT_TURNSTILE_SECRET') && VPN_CHAT_TURNSTILE_SECRET!=='LOCAL-DEMO-NOT-A-TURNSTILE-SECRET')return;
if(!defined('VPN_CHAT_TURNSTILE_SECRET'))define('VPN_CHAT_TURNSTILE_SECRET','LOCAL-DEMO-NOT-A-TURNSTILE-SECRET');

add_action('plugins_loaded',static function(){
    if(!class_exists('VPN_Chat_Security'))return;
    // Installed after the existing local outbound blocker: only simulated Siteverify is handled.
    add_filter('pre_http_request',static function($pre,$args,$url){
        if($url!=='https://challenges.cloudflare.com/turnstile/v0/siteverify')return $pre;
        if(!vpn_chat_local_demo_allowed() || ($args['body']['secret']??'')!==VPN_CHAT_TURNSTILE_SECRET)return $pre;
        $valid=false;$parts=explode('.',(string)($args['body']['response']??''));
        if(count($parts)===4 && ctype_digit($parts[0]) && ctype_digit($parts[1]) && (int)$parts[1]>=time() && (int)$parts[1]<=time()+120 && preg_match('/^[a-f0-9]{32}$/D',$parts[2])){
            $payload=implode('.',array_slice($parts,0,3));
            if(hash_equals(hash_hmac('sha256','local-demo:'.$payload,wp_salt('nonce')),$parts[3])){
                try{
                    $session=VPN_Chat_Security::session();
                    if((int)$session['id']===(int)$parts[0]){
                        global $wpdb;
                        $key=hash('sha256','local-demo-token:'.$payload);
                        $valid=$wpdb->query($wpdb->prepare('INSERT IGNORE INTO '.VPN_Chat_Store::table('rate_limits').' (bucket,hits,expires_at) VALUES (%s,1,%s)',$key,gmdate('Y-m-d H:i:s',(int)$parts[1])))===1;
                    }
                }catch(VPN_Chat_Fault $e){$valid=false;}
            }
        }
        return ['headers'=>[],'response'=>['code'=>200,'message'=>'Local simulation'],'body'=>wp_json_encode(['success'=>$valid,'hostname'=>wp_parse_url(home_url(),PHP_URL_HOST),'action'=>'vpn_chat_start']),'cookies'=>[]];
    },PHP_INT_MAX,3);
    add_filter('pre_wp_mail','__return_false',PHP_INT_MAX);
},PHP_INT_MAX);

add_action('rest_api_init',static function(){
    if(!class_exists('VPN_Chat_Security'))return;
    register_rest_route('vpn-chat-local/v1','/challenge',[
        'methods'=>'GET',
        'permission_callback'=>static function($r){
            if(!vpn_chat_local_demo_allowed())return new WP_Error('local_only','Local preview only',['status'=>403]);
            try{VPN_Chat_Security::origin($r);VPN_Chat_Security::session();return true;}
            catch(VPN_Chat_Fault $e){return new WP_Error($e->getMessage(),$e->getMessage(),['status'=>$e->status]);}
        },
        'callback'=>static function(){
            try{
                $s=VPN_Chat_Security::session();VPN_Chat_Security::quota('local-demo-challenge:'.$s['id'],20,60);
                $payload=$s['id'].'.'.(time()+120).'.'.bin2hex(random_bytes(16));
                $r=new WP_REST_Response(['token'=>$payload.'.'.hash_hmac('sha256','local-demo:'.$payload,wp_salt('nonce'))]);
            }catch(VPN_Chat_Fault $e){$r=new WP_REST_Response(['code'=>$e->getMessage()],$e->status);}
            $r->header('Cache-Control','private, no-store');return $r;
        }
    ]);
});
add_action('wp_enqueue_scripts',static function(){
    if(!wp_script_is('vpn-chat-launcher','enqueued'))return;
    $url=wp_json_encode(rest_url('vpn-chat-local/v1/challenge'));
    $script=<<<'JS'
(() => {
 const widgets=new Map();let next=0;
 async function issue(id){const widget=widgets.get(id);if(!widget)return;try{const r=await fetch(DEMO_URL,{credentials:'same-origin',cache:'no-store'});const data=await r.json();if(!r.ok)throw new Error('Local challenge');widget.options.callback(data.token);}catch{widget.options['error-callback']?.();}}
 window.turnstile={render:(el,options)=>{const id=++next;const note=document.createElement('p');note.textContent='LOCAL PREVIEW: anti-bot check simulated; emails disabled.';note.setAttribute('role','status');el.replaceChildren(note);widgets.set(id,{options});issue(id);return id;},reset:id=>issue(id)};
})();
JS;
    wp_add_inline_script('vpn-chat-launcher',str_replace('DEMO_URL',$url,$script),'before');
},20);
add_action('admin_notices',static function(){
    if(current_user_can('vpn_chat_agent'))echo '<div class="notice notice-warning"><p>VPN Live Chat — LOCAL DEMO: Turnstile được giả lập, email bị chặn. Adapter này chỉ chạy trên local/loopback/database local và không nằm trong ZIP production.</p></div>';
});
