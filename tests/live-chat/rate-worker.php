<?php
ob_start();require __DIR__.'/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311')exit(1);
$key=$argv[1];$barrier=$argv[2];$slot=$argv[3];
if(!preg_match('/^rate-test-[a-f0-9]{16}$/D',$key)||dirname($barrier)!==__DIR__.'/runtime')exit(1);
file_put_contents($barrier.'.ready'.$slot,'ready');$until=microtime(true)+10;
while(!file_exists($barrier)){if(microtime(true)>$until)exit(1);usleep(2000);}
try{VPN_Chat_Security::quota($key,2,30);$result='accepted';}catch(VPN_Chat_Fault $e){$result=$e->getMessage();}
ob_end_clean();echo json_encode(['result'=>$result]);
