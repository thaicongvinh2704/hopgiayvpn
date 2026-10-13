<?php
require __DIR__.'/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311'||!VPN_Chat_Settings::local())exit(1);
if(($argv[1]??'')==='enable'){
    add_option('vpn_keyword_test_original',VPN_Chat_Settings::get(),'',false);
    $s=VPN_Chat_Settings::get();$s['auto_reply']=true;$s['accept_new']=true;$s['widget']=true;$s['site_key']='';$s['greeting_enabled']=false;
    $s['short_limit']=100;$s['long_limit']=1000;update_option('vpn_chat_settings',$s,false);
}elseif(($argv[1]??'')==='cleanup'){
    $original=get_option('vpn_keyword_test_original');if($original){update_option('vpn_chat_settings',$original,false);delete_option('vpn_keyword_test_original');}
    foreach(VPN_Chat_Schema::TABLES as $t)VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table($t));
}else{exit(1);}
echo 'Test fixture '.$argv[1].PHP_EOL;
