<?php
/** Local-only verification mailbox. Never included in the production plugin. */
defined('ABSPATH') || exit;
add_filter('vpn_chat_identity_mail',static function($sent,$email,$code,$id){
    if(wp_get_environment_type()!=='local'||DB_NAME!=='hopgiayvpnmoi'||!in_array(wp_parse_url(home_url(),PHP_URL_HOST),['localhost','127.0.0.1','::1'],true))return $sent;
    $dir=ABSPATH.'tests/live-chat/runtime';
    if(!is_dir($dir))return false;
    $file=$dir.'/verification-mailbox.json';
    $handle=fopen($file,'c+');if(!$handle)return false;
    if(!flock($handle,LOCK_EX)){fclose($handle);return false;}
    $rows=json_decode(stream_get_contents($handle),true)?:[];
    $rows=array_filter($rows,static fn($r)=>($r['expires']??0)>time());
    $rows[$id]=['email'=>$email,'code'=>$code,'expires'=>time()+600];
    rewind($handle);ftruncate($handle,0);$ok=fwrite($handle,wp_json_encode($rows))!==false;fflush($handle);flock($handle,LOCK_UN);fclose($handle);
    return $ok;
},10,4);
