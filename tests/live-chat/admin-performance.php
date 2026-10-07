<?php
ob_start();
require __DIR__.'/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311'||!VPN_Chat_Settings::local())exit(1);
require __DIR__.'/runtime/rest-before.php'; // Captured 1.8.3 sync for a same-database comparison.
global $wpdb;
$passed=[];$measure=[];
function pc($v,$n){global $passed;if(!$v)throw new RuntimeException($n);$passed[]=$n;}
function measured_sync($class,$p){global $wpdb;$q=$wpdb->num_queries;$t=hrtime(true);$result=$class::sync($p);return ['result'=>$result,'queries'=>$wpdb->num_queries-$q,'ms'=>(hrtime(true)-$t)/1e6];}
$manager=(int)get_user_by('login','chat-test-manager')->ID;$a=(int)get_user_by('login','agent-a')->ID;$b=(int)get_user_by('login','agent-b')->ID;
try{
 foreach(VPN_Chat_Schema::TABLES as $table)VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table($table));
 wp_set_current_user($manager);
 VPN_Chat_Store::transaction(static function()use($a,$b){
  $now=gmdate('Y-m-d H:i:s');$states=['unassigned','assigned','waiting_customer','follow_up','closed','spam'];
  for($i=0;$i<750;$i++){
   $customer=VPN_Chat_Store::insert('customers',['created_at'=>$now,'verified_email'=>$i%5===0?'verified-'.$i.'@example.invalid':null]);
   for($j=0;$j<2;$j++){
    $status=$states[($i+$j)%6];$owner=$status==='unassigned'?0:($i%2?$a:$b);
    $id=VPN_Chat_Store::insert('conversations',['public_id'=>wp_generate_uuid4(),'customer_id'=>$customer,'session_id'=>0,'name'=>'Perf '.$i,'email'=>'contact-'.$i.'@example.invalid','needs'=>'','metadata'=>'{}','source_path'=>'/','status'=>$status,'owner_id'=>$owner,'seq'=>2,'guest_seq'=>1,'read_seq'=>$j,'created_at'=>$now,'updated_at'=>$now]);
    foreach(['guest'=>'Visible message','note'=>'Private note'] as $sender=>$body)VPN_Chat_Store::insert('messages',['conversation_id'=>$id,'seq'=>$sender==='guest'?1:2,'sender'=>$sender,'actor_id'=>0,'sender_scope'=>$sender,'client_message_id'=>bin2hex(random_bytes(16)),'payload_hash'=>hash('sha256',$body),'body'=>$body,'created_at'=>$now]);
   }
  }
 });
 foreach([$manager,$a] as $user){
  wp_set_current_user($user);
  foreach(['inbox','unassigned','mine','closed','spam','follow_up'] as $filter){
   foreach([1,2] as $page){$p=['filter'=>$filter,'page'=>$page];$old=measured_sync(VPN_Chat_Before_REST::class,$p);$new=measured_sync(VPN_Chat_REST::class,$p);pc($old['result']==$new['result'],"same grouped data and scope: $user/$filter/page$page");}
  }
  foreach(['Perf 1','verified-10@','Private note','Khách #'] as $search){$p=['filter'=>'inbox','search'=>$search];pc(VPN_Chat_Before_REST::sync($p)==VPN_Chat_REST::sync($p),"same search and scope: $user/$search");}
 }
 wp_set_current_user($manager);$p=['filter'=>'all'];
 for($i=0;$i<5;$i++){foreach(['before'=>VPN_Chat_Before_REST::class,'after'=>VPN_Chat_REST::class] as $label=>$class){$r=measured_sync($class,$p);$measure[$label][]=['queries'=>$r['queries'],'ms'=>round($r['ms'],3)];}}
 pc($measure['before'][0]['queries']-$measure['after'][0]['queries']>=20,'list removes at least 20 per-customer queries');
 $list=VPN_Chat_REST::sync($p)['list'];$id=$list[0]['id'];
 $full=VPN_Chat_REST::sync($p+['id'=>$id,'cursor'=>0]);$detail=measured_sync(VPN_Chat_REST::class,['id'=>$id,'cursor'=>0,'detail_only'=>true]);
 pc($full['selected']==$detail['result']['selected']&&$full['delta']==$detail['result']['delta'],'lightweight detail returns the same authorized transcript');
 pc(!isset($detail['result']['list'],$detail['result']['canned']),'lightweight detail omits list/canned');
 pc($detail['queries']<=5,'opening a chat uses at most five chat queries');
 wp_set_current_user($a);$foreign=$wpdb->get_var($wpdb->prepare('SELECT public_id FROM '.VPN_Chat_Store::table('conversations').' WHERE owner_id=%d LIMIT 1',$b));
 try{VPN_Chat_REST::sync(['detail_only'=>true,'id'=>$foreign]);pc(false,'foreign detail must fail');}catch(VPN_Chat_Fault $e){pc($e->status===403,'lightweight detail enforces owner scope');}
}finally{
 foreach(VPN_Chat_Schema::TABLES as $table)VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table($table));
 $report=['version'=>VPN_CHAT_VERSION,'checks'=>count($passed),'passed'=>$passed,'fixtures'=>['customers'=>750,'conversations'=>1500,'messages'=>3000],'samples'=>$measure,'detail_queries'=>$detail['queries']??null,'database'=>'isolated vpn_chat_test:3311; not production latency','cleaned'=>true];
 file_put_contents(__DIR__.'/../../artifacts/vpn-live-chat/evidence/admin-performance-backend.json',wp_json_encode($report,JSON_PRETTY_PRINT));ob_end_clean();echo wp_json_encode($report,JSON_PRETTY_PRINT);
}
