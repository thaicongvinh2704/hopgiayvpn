<?php
require __DIR__.'/runtime/wp/wp-load.php';
$fixtures=['guests'=>[],'sales'=>[]];
for($i=0;$i<30;$i++){
    $secret=bin2hex(random_bytes(32));$s=VPN_Chat_Store::insert('sessions',['secret_hash'=>hash('sha256',$secret),'created_at'=>gmdate('Y-m-d H:i:s'),'last_seen'=>gmdate('Y-m-d H:i:s'),'expires_at'=>gmdate('Y-m-d H:i:s',time()+604800)]);
    $id=wp_generate_uuid4();VPN_Chat_Store::insert('conversations',['public_id'=>$id,'session_id'=>$s,'name'=>'Load Fixture '.$i,'email'=>'load-'.$i.'@example.invalid','metadata'=>'{}','needs'=>'','created_at'=>gmdate('Y-m-d H:i:s'),'updated_at'=>gmdate('Y-m-d H:i:s')]);
    $fixtures['guests'][]=['cookie'=>'vpn_chat_session='.$secret,'id'=>$id];
}
foreach(['chat-test-manager','agent-a','agent-b']as $login){
    $user=get_user_by('login',$login);wp_set_current_user($user->ID);$token=WP_Session_Tokens::get_instance($user->ID)->create(time()+3600);
    $value=wp_generate_auth_cookie($user->ID,time()+3600,'logged_in',$token);$_COOKIE[LOGGED_IN_COOKIE]=$value;
    $fixtures['sales'][]=['cookie'=>LOGGED_IN_COOKIE.'='.$value,'nonce'=>wp_create_nonce('wp_rest')];
}
file_put_contents(__DIR__.'/runtime/load-fixtures.json',wp_json_encode($fixtures));echo "30 synthetic guests / 3 sales prepared\n";
