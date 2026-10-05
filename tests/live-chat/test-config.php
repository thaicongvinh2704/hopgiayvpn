<?php
// Dummy credentials, isolated test DB. Never copy this config into production.
define('DB_NAME','vpn_chat_test');
define('DB_USER','root'); define('DB_PASSWORD',''); define('DB_HOST','127.0.0.1:3311');
define('DB_CHARSET','utf8mb4'); define('DB_COLLATE','');
$table_prefix='vct_';
define('WP_HOME','http://127.0.0.1:8091'); define('WP_SITEURL','http://127.0.0.1:8091');
define('WP_ENVIRONMENT_TYPE','local'); define('DISABLE_WP_CRON',true);
if(!defined('VPN_CHAT_TURNSTILE_SECRET'))define('VPN_CHAT_TURNSTILE_SECRET','TEST-ONLY-NOT-A-REAL-SECRET');
foreach(['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $name) define($name,'isolated-chat-tests-' . $name . '-never-production');
define('WP_DEBUG',true); define('WP_DEBUG_DISPLAY',false); define('WP_DEBUG_LOG',false);
if(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/'); require_once ABSPATH.'wp-settings.php';
