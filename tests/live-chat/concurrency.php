<?php
require __DIR__.'/runtime/wp/wp-load.php';
global $wpdb;
function ok($value,string $name):void{if(!$value)throw new RuntimeException('FAIL '.$name);echo "PASS $name\n";}
function launch_worker(string $mode,string $arg,int $user,string $barrier):array{
    $pipes=[];$proc=proc_open([PHP_BINARY,__DIR__.'/worker.php',$mode,$arg,(string)$user,$barrier],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);return [$proc,$pipes];
}
function collect(array $worker):array{[$proc,$pipes]=$worker;$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($proc);$result=json_decode($out,true);if($code!==0||!$result)throw new RuntimeException($out.$err);return $result;}
function barrier_path():string{return __DIR__.'/runtime/barrier-'.bin2hex(random_bytes(8));}
$a=(int)get_user_by('login','agent-a')->ID;$b=(int)get_user_by('login','agent-b')->ID;
$session=VPN_Chat_Store::insert('sessions',['secret_hash'=>hash('sha256',random_bytes(32)),'created_at'=>gmdate('Y-m-d H:i:s'),'last_seen'=>gmdate('Y-m-d H:i:s'),'expires_at'=>gmdate('Y-m-d H:i:s',time()+604800)]);
function conversation_fixture(int $session):string{$id=wp_generate_uuid4();VPN_Chat_Store::insert('conversations',['public_id'=>$id,'session_id'=>$session,'name'=>'Concurrency','email'=>'concurrency@example.invalid','metadata'=>'{}','needs'=>'','created_at'=>gmdate('Y-m-d H:i:s'),'updated_at'=>gmdate('Y-m-d H:i:s')]);return $id;}
$id=conversation_fixture($session);$barrier=barrier_path();$payload=wp_json_encode(['id'=>$id,'version'=>1]);$workers=[launch_worker('claim',$payload,$a,$barrier),launch_worker('claim',$payload,$b,$barrier)];file_put_contents($barrier,'go');$results=array_map('collect',$workers);$codes=array_column($results,'status');sort($codes);ok($codes===[200,409],'simultaneous claim exactly one winner and one conflict');unlink($barrier);
$barrier=barrier_path();$scope='parallel-'.bin2hex(random_bytes(8));$workers=[];for($i=0;$i<12;$i++)$workers[]=launch_worker('quota',$scope,0,$barrier);file_put_contents($barrier,'go');$results=array_map('collect',$workers);ok(count(array_filter($results,static fn($r)=>$r['status']===200))===5 && count(array_filter($results,static fn($r)=>$r['status']===429))===7,'12 parallel requests enforce atomic quota of 5');unlink($barrier);
$id=conversation_fixture($session);$barrier=barrier_path();$locked=barrier_path();$first=launch_worker('send',wp_json_encode(['id'=>$id,'message'=>'first','client'=>bin2hex(random_bytes(16)),'locked'=>$locked,'delay'=>1000]),0,$barrier);file_put_contents($barrier,'go');$deadline=microtime(true)+30;while(!is_file($locked)){if(microtime(true)>$deadline)throw new RuntimeException('Lock timeout');usleep(10000);}
$second=launch_worker('send',wp_json_encode(['id'=>$id,'message'=>'second','client'=>bin2hex(random_bytes(16))]),0,$barrier);
$snapshot=VPN_Chat_Store::conversation($id);ok((int)$snapshot['seq']===0,'uncommitted message never advances visible cursor');
$r1=collect($first);$r2=collect($second);ok($r1['result']['seq']===1&&$r2['result']['seq']===2,'row lock serializes concurrent sequence and commit');
$snapshot=VPN_Chat_Store::conversation($id);$delta=VPN_Chat_Store::messages($snapshot,0,true);ok(array_column($delta['messages'],'body')===['first','second']&&$delta['cursor']===2,'delta includes both acknowledged concurrent messages');unlink($barrier);unlink($locked);
$client=bin2hex(random_bytes(16));$barrier=barrier_path();$payload=wp_json_encode(['id'=>$id,'message'=>'same retry','client'=>$client]);$workers=[launch_worker('send',$payload,0,$barrier),launch_worker('send',$payload,0,$barrier)];file_put_contents($barrier,'go');$results=array_map('collect',$workers);ok($results[0]['result']['seq']===$results[1]['result']['seq'],'simultaneous same client ID stores one message');unlink($barrier);
// Dedicated queue item, atomic test sink rows prove the two runners do not both deliver.
$wpdb->query('UPDATE '.VPN_Chat_Store::table('outbox')." SET state='done'");$target=conversation_fixture($session);$c=VPN_Chat_Store::conversation($target);VPN_Chat_Store::insert('outbox',['conversation_id'=>$c['id'],'dedup_key'=>'lease-test-'.uniqid(),'due_at'=>gmdate('Y-m-d H:i:s',time()-1)]);
update_option('vpn_test_mail_success',true,false);$before=(int)$wpdb->get_var('SELECT COUNT(*) FROM vct_test_mails');$barrier=barrier_path();$workers=[launch_worker('outbox','',0,$barrier),launch_worker('outbox','',0,$barrier)];file_put_contents($barrier,'go');array_map('collect',$workers);ok((int)$wpdb->get_var('SELECT COUNT(*) FROM vct_test_mails')===$before+1,'outbox lease prevents parallel duplicate mail');unlink($barrier);
echo "TOTAL 7 concurrency checks passed\n";
