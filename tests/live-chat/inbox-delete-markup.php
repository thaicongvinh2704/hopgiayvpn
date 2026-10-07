<?php
// Render the real inbox markup only from the dedicated test installation.
ob_start();
require __DIR__.'/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311'||!VPN_Chat_Settings::local())exit(1);
ob_end_clean();
wp_set_current_user(get_user_by('login',($argv[1]??'manager')==='manager'?'chat-test-manager':'agent-a')->ID);
VPN_Chat_UI::inbox();
