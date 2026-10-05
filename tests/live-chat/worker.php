<?php
require __DIR__.'/runtime/wp/wp-load.php';
[$script,$mode,$arg,$user,$barrier]=$argv;
wp_set_current_user((int)$user);
$deadline=microtime(true)+30;
while(!is_file($barrier)){if(microtime(true)>$deadline)throw new RuntimeException('Barrier timeout');usleep(10000);}
try{
    if($mode==='claim'){$p=json_decode($arg,true);$result=VPN_Chat_Service::update($p['id'],['action'=>'claim','version'=>$p['version']]);}
    elseif($mode==='quota'){VPN_Chat_Security::quota($arg,5,3600);$result=['allowed'=>true];}
    elseif($mode==='send'){
        $p=json_decode($arg,true);
        $result=VPN_Chat_Store::transaction(static function()use($p){
            $c=VPN_Chat_Store::conversation($p['id'],true);
            $result=VPN_Chat_Service::send_locked($c,$p['message'],$p['client'],'guest',0);
            if(!empty($p['locked']))file_put_contents($p['locked'],'locked');
            if(!empty($p['delay']))usleep($p['delay']*1000);
            return $result;
        });
    }elseif($mode==='outbox'){VPN_Chat_Jobs::run();$result=['ran'=>true];}
    else throw new RuntimeException('Unknown worker');
    echo wp_json_encode(['status'=>200,'result'=>$result]);
}catch(VPN_Chat_Fault $e){echo wp_json_encode(['status'=>$e->status,'code'=>$e->getMessage()]);}
