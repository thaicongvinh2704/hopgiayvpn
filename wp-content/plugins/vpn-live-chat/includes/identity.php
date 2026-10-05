<?php
defined('ABSPATH') || exit;
final class VPN_Chat_Identity {
    const COOKIE = 'vpn_chat_visitor';
    public static function cookie(string $secret): void {
        setcookie(self::COOKIE,$secret,['expires'=>time()+180*DAY_IN_SECONDS,'path'=>'/','secure'=>!VPN_Chat_Settings::local()||is_ssl(),'httponly'=>true,'samesite'=>'Strict']);
        $_COOKIE[self::COOKIE]=$secret;
    }
    public static function grant(int $customer): void {
        $secret=bin2hex(random_bytes(32));
        VPN_Chat_Store::insert('devices',['customer_id'=>$customer,'token_hash'=>hash('sha256',$secret),'expires_at'=>gmdate('Y-m-d H:i:s',time()+180*DAY_IN_SECONDS)]);
        self::cookie($secret);
    }
    public static function forget(): void {
        global $wpdb;
        $wpdb->update(VPN_Chat_Store::table('devices'),['revoked'=>1],['token_hash'=>hash('sha256',(string)($_COOKIE[self::COOKIE]??''))]);
        setcookie(self::COOKIE,'',['expires'=>1,'path'=>'/','secure'=>!VPN_Chat_Settings::local()||is_ssl(),'httponly'=>true,'samesite'=>'Strict']);
        unset($_COOKIE[self::COOKIE]);
    }
    public static function restore(): int {
        global $wpdb;
        $secret=(string)($_COOKIE[self::COOKIE]??'');
        if(!preg_match('/^[a-f0-9]{64}$/D',$secret))return 0;
        $device=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.VPN_Chat_Store::table('devices').' WHERE token_hash=%s AND revoked=0 AND expires_at>UTC_TIMESTAMP()',hash('sha256',$secret)),ARRAY_A);
        if(!$device||!$wpdb->get_var($wpdb->prepare('SELECT id FROM '.VPN_Chat_Store::table('customers').' WHERE id=%d',$device['customer_id'])))return 0;
        self::check((int)$device['customer_id']);
        $wpdb->update(VPN_Chat_Store::table('devices'),['expires_at'=>gmdate('Y-m-d H:i:s',time()+180*DAY_IN_SECONDS)],['id'=>$device['id']]);
        self::cookie($secret);
        return (int)$device['customer_id'];
    }
    public static function check(int $customer): void {
        global $wpdb;
        $blocked=$wpdb->get_var($wpdb->prepare('SELECT b.id FROM '.VPN_Chat_Store::table('blocks').' b JOIN '.VPN_Chat_Store::table('sessions').' s ON s.id=b.session_id WHERE b.revoked=0 AND b.expires_at>UTC_TIMESTAMP() AND (s.customer_id=%d OR EXISTS (SELECT 1 FROM '.VPN_Chat_Store::table('conversations').' c WHERE c.session_id=b.session_id AND c.customer_id=%d)) LIMIT 1',$customer,$customer));
        if($blocked)throw new VPN_Chat_Fault('temporarily_blocked',403);
    }
    public static function create(): int {
        return VPN_Chat_Store::insert('customers',['created_at'=>gmdate('Y-m-d H:i:s')]);
    }
    public static function summary(int $id): array {
        global $wpdb;
        $email=$wpdb->get_var($wpdb->prepare('SELECT verified_email FROM '.VPN_Chat_Store::table('customers').' WHERE id=%d',$id));
        return ['code'=>'Khách #'.str_pad((string)$id,6,'0',STR_PAD_LEFT),'verified_email'=>$email?:''];
    }
    public static function history(int $customer,bool $guest=true): array {
        global $wpdb;
        $rows=$wpdb->get_results($wpdb->prepare('SELECT * FROM '.VPN_Chat_Store::table('conversations').' WHERE customer_id=%d ORDER BY updated_at DESC,id DESC LIMIT 100',$customer),ARRAY_A);
        $out=[];
        foreach($rows as $c){
            if(!$guest){try{VPN_Chat_Store::authorize($c);}catch(VPN_Chat_Fault $e){continue;}}
            $out[]=['id'=>$c['public_id'],'status'=>$c['status']==='spam'?'closed':$c['status'],'updated_at'=>$c['updated_at'],'unread'=>$guest?VPN_Chat_Store::guest_summary($c)['unread']:((int)$c['guest_seq']>(int)$c['read_seq'])];
        }
        return $out;
    }
    public static function request(array $s,array $p): array {
        global $wpdb;
        $email=strtolower(trim(is_string($p['email']??null)?$p['email']:''));
        if(!is_email($email)||strlen($email)>254)throw new VPN_Chat_Fault('invalid_email');
        self::check((int)$s['customer_id']);
        VPN_Chat_Security::quota('verify-session:'.$s['id'],3,900);
        VPN_Chat_Security::quota('verify-email:'.hash('sha256',$email),3,900);
        VPN_Chat_Security::quota('verify-ip:'.VPN_Chat_Security::ip(),15,900);
        $id=wp_generate_uuid4();$code=(string)random_int(100000,999999);
        VPN_Chat_Store::query($wpdb->prepare('UPDATE '.VPN_Chat_Store::table('email_codes').' SET used=1 WHERE session_id=%d',$s['id']));
        VPN_Chat_Store::insert('email_codes',['public_id'=>$id,'session_id'=>$s['id'],'email'=>$email,'code_hash'=>hash_hmac('sha256',$id.':'.$code,wp_salt('auth')),'expires_at'=>gmdate('Y-m-d H:i:s',time()+600)]);
        $sent=apply_filters('vpn_chat_identity_mail',null,$email,$code,$id);
        if($sent===null)$sent=wp_mail($email,'Your VPN Packaging chat verification code',"Your verification code is: $code\nThis code expires in 10 minutes. Only enter it in the VPN Packaging chat you opened. If you did not request it, ignore this email.");
        if(!$sent){$wpdb->update(VPN_Chat_Store::table('email_codes'),['used'=>1],['public_id'=>$id]);throw new VPN_Chat_Fault('email_unavailable',503);}
        return ['request_id'=>$id,'sent'=>true];
    }
    public static function verify(array $s,array $p): array {
        global $wpdb;
        VPN_Chat_Security::quota('verify-attempt:'.$s['id'],15,900);
        $id=is_string($p['request_id']??null)?$p['request_id']:'';$code=is_string($p['code']??null)?$p['code']:'';
        $result=VPN_Chat_Store::transaction(static function()use($wpdb,$s,$id,$code){
            // Serialize identity changes before message ownership can be reassigned.
            $session=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.VPN_Chat_Store::table('sessions').' WHERE id=%d FOR UPDATE',$s['id']),ARRAY_A);
            if(!$session||$session['revoked']||strtotime($session['expires_at'].' UTC')<=time()||(int)$session['customer_id']!==(int)$s['customer_id'])throw new VPN_Chat_Fault('session_expired',401);
            $r=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.VPN_Chat_Store::table('email_codes').' WHERE public_id=%s AND session_id=%d FOR UPDATE',$id,$s['id']),ARRAY_A);
            if(!$r||$r['used']||(int)$r['attempts']>=5||strtotime($r['expires_at'].' UTC')<=time())return ['error'=>'verification_expired'];
            $wpdb->update(VPN_Chat_Store::table('email_codes'),['attempts'=>(int)$r['attempts']+1],['id'=>$r['id']]);
            if(!preg_match('/^[0-9]{6}$/D',$code)||!hash_equals($r['code_hash'],hash_hmac('sha256',$id.':'.$code,wp_salt('auth'))))return ['error'=>'invalid_verification_code'];
            $source=(int)$s['customer_id'];
            $current=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.VPN_Chat_Store::table('customers').' WHERE id=%d FOR UPDATE',$source),ARRAY_A);
            if($current['verified_email']&&$current['verified_email']!==$r['email'])return ['error'=>'different_verified_identity'];
            // Unique email index plus upsert serializes concurrent claims of one email.
            VPN_Chat_Store::query($wpdb->prepare('INSERT INTO '.VPN_Chat_Store::table('customers').' (verified_email,created_at) VALUES (%s,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)',$r['email']));
            $target=(int)$wpdb->insert_id;
            self::check($target);
            if(!$current['verified_email']){
                VPN_Chat_Store::query($wpdb->prepare('UPDATE '.VPN_Chat_Store::table('conversations').' SET customer_id=%d,version=version+1 WHERE customer_id=%d',$target,$source));
                VPN_Chat_Store::query($wpdb->prepare('UPDATE '.VPN_Chat_Store::table('devices').' SET revoked=1 WHERE customer_id=%d',$source));
                VPN_Chat_Store::query($wpdb->prepare('UPDATE '.VPN_Chat_Store::table('sessions').' SET revoked=1 WHERE customer_id=%d AND id<>%d',$source,$s['id']));
                VPN_Chat_Store::query($wpdb->prepare('UPDATE '.VPN_Chat_Store::table('sessions').' SET customer_id=%d WHERE id=%d',$target,$s['id']));
            }
            $wpdb->update(VPN_Chat_Store::table('email_codes'),['used'=>1],['id'=>$r['id']]);
            $secret=bin2hex(random_bytes(32));
            $wpdb->update(VPN_Chat_Store::table('sessions'),['secret_hash'=>hash('sha256',$secret)],['id'=>$s['id']]);
            return ['customer'=>$target,'secret'=>$secret,'expires'=>$session['expires_at']];
        });
        if(isset($result['error']))throw new VPN_Chat_Fault($result['error']);
        self::grant($result['customer']);
        setcookie(VPN_Chat_Security::COOKIE,$result['secret'],['expires'=>strtotime($result['expires'].' UTC'),'path'=>'/','secure'=>!VPN_Chat_Settings::local()||is_ssl(),'httponly'=>true,'samesite'=>'Strict']);
        return ['verified'=>true,'customer'=>self::summary($result['customer'])];
    }
}
