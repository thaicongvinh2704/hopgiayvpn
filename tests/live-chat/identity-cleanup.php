<?php
require dirname(__DIR__,2).'/wp-load.php';
if(!VPN_Chat_Settings::local()||DB_NAME!=='hopgiayvpnmoi')exit(1);
$report=json_decode(file_get_contents(__DIR__.'/../../artifacts/vpn-live-chat/evidence/identity-report.json'),true);$count=0;
foreach($report['created_fixture_ids'] as $uuid){$c=VPN_Chat_Store::conversation($uuid);$body=$wpdb->get_var($wpdb->prepare('SELECT body FROM '.VPN_Chat_Store::table('messages').' WHERE conversation_id=%d AND seq=1',$c['id']));if(!str_starts_with($body,$report['marker']))throw new Exception('fixture mismatch');VPN_Chat_Jobs::erase((int)$c['id']);$count++;}
foreach($wpdb->get_results('SELECT id,name,email FROM '.VPN_Chat_Store::table('conversations')." WHERE name LIKE 'Local installation test %' AND email='local-preview@example.invalid'",ARRAY_A) as $c){if(!preg_match('/^Local installation test [0-9]+$/D',$c['name']))continue;VPN_Chat_Jobs::erase((int)$c['id']);$count++;}
$remaining=(int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations'));
file_put_contents(__DIR__.'/../../artifacts/vpn-live-chat/evidence/identity-cleanup-report.json',wp_json_encode(['deleted_fixture_conversations'=>$count,'preserved_conversations'=>$remaining,'healthy'=>VPN_Chat_Schema::healthy()]));
echo wp_json_encode(['deleted_fixture_conversations'=>$count,'preserved_conversations'=>$remaining,'healthy'=>VPN_Chat_Schema::healthy()]);
