<?php
define('VPN_CHAT_TURNSTILE_SECRET','');
require __DIR__.'/runtime/wp/wp-load.php';
function fcheck($value,string $name):void{if(!$value)throw new RuntimeException('FAIL '.$name);echo 'PASS '.$name."\n";}
fcheck(!VPN_Chat_Settings::ready()&&!VPN_Chat_Settings::public_config()['accepting'],'missing Turnstile secret disables new chats');
$secret=bin2hex(random_bytes(32));$id=VPN_Chat_Store::insert('sessions',['secret_hash'=>hash('sha256',$secret),'created_at'=>gmdate('Y-m-d H:i:s'),'last_seen'=>gmdate('Y-m-d H:i:s'),'expires_at'=>gmdate('Y-m-d H:i:s',time()+3600)]);
try{VPN_Chat_Service::start(['id'=>$id],['name'=>'Fail','email'=>'fail@example.invalid','message'=>'Protected','client_message_id'=>bin2hex(random_bytes(16))]);throw new RuntimeException('Unexpected start');}catch(VPN_Chat_Fault $e){fcheck($e->status===503,'no unprotected creation when secret missing');}
global $wpdb;
$table=VPN_Chat_Store::table('canned');$wpdb->query('ALTER TABLE '.$table.' ENGINE=MyISAM');
try{fcheck(!VPN_Chat_Schema::healthy(),'nontransactional table fails health gate');}finally{$wpdb->query('ALTER TABLE '.$table.' ENGINE=InnoDB');}
fcheck(VPN_Chat_Schema::healthy(),'transaction engine restored');
$c=wp_generate_uuid4();$cid=VPN_Chat_Store::insert('conversations',['public_id'=>$c,'session_id'=>$id,'name'=>'Retention','email'=>'retention@example.invalid','metadata'=>'{}','needs'=>'','status'=>'closed','created_at'=>gmdate('Y-m-d H:i:s',time()-172800),'updated_at'=>gmdate('Y-m-d H:i:s',time()-172800)]);
$settings=VPN_Chat_Settings::get();$s=$settings;$s['retention_days']=0;update_option('vpn_chat_settings',$s,false);VPN_Chat_Jobs::run();fcheck((int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations').' WHERE id=%d',$cid))===1,'unchosen retention does not erase');
$s['retention_days']=1;update_option('vpn_chat_settings',$s,false);VPN_Chat_Jobs::run();fcheck((int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations').' WHERE id=%d',$cid))===0,'configured retention removes old closed conversations');update_option('vpn_chat_settings',$settings,false);
$_COOKIE[VPN_Chat_Security::COOKIE]=$secret;try{VPN_Chat_Security::session();throw new RuntimeException('Revoked accepted');}catch(VPN_Chat_Fault $e){fcheck($e->status===401,'erase revokes owning session');}
echo "TOTAL 7 configuration/retention checks passed\n";
