<?php
defined('ABSPATH') || exit;
final class VPN_Chat_Schema {
    const VERSION = '3';
    const TABLES = ['sessions', 'conversations', 'messages', 'agent_presence', 'rate_limits', 'blocks', 'outbox', 'audit', 'canned', 'customers', 'devices', 'email_codes'];
    public static function activate(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $c = $wpdb->get_charset_collate();
        $definitions = [
            'customers' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n verified_email varchar(254) DEFAULT NULL,\n created_at datetime NOT NULL,\n PRIMARY KEY  (id),\n UNIQUE KEY verified_email (verified_email)",
            'devices' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n customer_id bigint unsigned NOT NULL,\n token_hash char(64) NOT NULL,\n expires_at datetime NOT NULL,\n revoked tinyint NOT NULL DEFAULT 0,\n PRIMARY KEY  (id),\n UNIQUE KEY token_hash (token_hash),\n KEY customer_id (customer_id),\n KEY expiry (expires_at)",
            'email_codes' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n public_id char(36) NOT NULL,\n session_id bigint unsigned NOT NULL,\n email varchar(254) NOT NULL,\n code_hash char(64) NOT NULL,\n expires_at datetime NOT NULL,\n attempts int NOT NULL DEFAULT 0,\n used tinyint NOT NULL DEFAULT 0,\n PRIMARY KEY  (id),\n UNIQUE KEY public_id (public_id),\n KEY session_id (session_id),\n KEY expiry (expires_at)",
            'sessions' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n customer_id bigint unsigned NOT NULL DEFAULT 0,\n secret_hash char(64) NOT NULL,\n created_at datetime NOT NULL,\n last_seen datetime NOT NULL,\n expires_at datetime NOT NULL,\n revoked tinyint NOT NULL DEFAULT 0,\n challenge_required tinyint NOT NULL DEFAULT 0,\n PRIMARY KEY  (id),\n UNIQUE KEY secret_hash (secret_hash),\n KEY customer_id (customer_id),\n KEY expiry (expires_at)",
            'conversations' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n public_id char(36) NOT NULL,\n session_id bigint unsigned NOT NULL,\n customer_id bigint unsigned NOT NULL DEFAULT 0,\n name varchar(100) NOT NULL,\n email varchar(254) NOT NULL,\n source_path varchar(512) NOT NULL DEFAULT '',\n metadata text NOT NULL,\n needs text NOT NULL,\n status varchar(24) NOT NULL DEFAULT 'unassigned',\n label varchar(24) NOT NULL DEFAULT '',\n owner_id bigint unsigned NOT NULL DEFAULT 0,\n version bigint unsigned NOT NULL DEFAULT 1,\n seq bigint unsigned NOT NULL DEFAULT 0,\n guest_seq bigint unsigned NOT NULL DEFAULT 0,\n read_seq bigint unsigned NOT NULL DEFAULT 0,\n guest_read_seq bigint unsigned NOT NULL DEFAULT 0,\n follow_up_at datetime DEFAULT NULL,\n created_at datetime NOT NULL,\n updated_at datetime NOT NULL,\n PRIMARY KEY  (id),\n UNIQUE KEY public_id (public_id),\n KEY session_id (session_id),\n KEY customer_id (customer_id),\n KEY inbox (status,owner_id,updated_at),\n KEY follow_up (follow_up_at),\n KEY updated_at (updated_at)",
            'messages' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n conversation_id bigint unsigned NOT NULL,\n seq bigint unsigned NOT NULL,\n sender varchar(24) NOT NULL,\n actor_id bigint unsigned NOT NULL DEFAULT 0,\n sender_profile text DEFAULT NULL,\n sender_scope varchar(40) NOT NULL,\n client_message_id varchar(64) NOT NULL,\n payload_hash char(64) NOT NULL,\n body text NOT NULL,\n created_at datetime NOT NULL,\n PRIMARY KEY  (id),\n UNIQUE KEY sequence (conversation_id,seq),\n UNIQUE KEY retry (conversation_id,sender_scope,client_message_id)",
            'agent_presence' => "user_id bigint unsigned NOT NULL,\n state varchar(12) NOT NULL DEFAULT 'offline',\n heartbeat_at datetime NOT NULL,\n PRIMARY KEY  (user_id)",
            'rate_limits' => "bucket char(64) NOT NULL,\n hits int unsigned NOT NULL DEFAULT 0,\n expires_at datetime NOT NULL,\n PRIMARY KEY  (bucket),\n KEY expiry (expires_at)",
            'blocks' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n session_id bigint unsigned NOT NULL,\n reason varchar(240) NOT NULL,\n actor_id bigint unsigned NOT NULL,\n expires_at datetime NOT NULL,\n created_at datetime NOT NULL,\n revoked tinyint NOT NULL DEFAULT 0,\n PRIMARY KEY  (id),\n KEY scope (session_id,revoked,expires_at)",
            'outbox' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n conversation_id bigint unsigned NOT NULL,\n dedup_key varchar(100) NOT NULL,\n due_at datetime NOT NULL,\n state varchar(12) NOT NULL DEFAULT 'pending',\n attempts int NOT NULL DEFAULT 0,\n lease char(32) DEFAULT NULL,\n lease_until datetime DEFAULT NULL,\n last_error varchar(100) NOT NULL DEFAULT '',\n PRIMARY KEY  (id),\n UNIQUE KEY dedup (dedup_key),\n KEY queue (state,due_at,lease_until)",
            'audit' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n actor_id bigint unsigned NOT NULL,\n action varchar(40) NOT NULL,\n target_id bigint unsigned NOT NULL,\n metadata text NOT NULL,\n created_at datetime NOT NULL,\n PRIMARY KEY  (id),\n KEY target_id (target_id)",
            'canned' => "id bigint unsigned NOT NULL AUTO_INCREMENT,\n title varchar(100) NOT NULL,\n body text NOT NULL,\n PRIMARY KEY  (id)",
        ];
        foreach ($definitions as $name => $definition) { dbDelta('CREATE TABLE ' . VPN_Chat_Store::table($name) . " (\n $definition\n) ENGINE=InnoDB $c;"); }
        if (!self::healthy()) { return; }
        // Migrate each browser session independently; unverified email never joins profiles.
        foreach($wpdb->get_col('SELECT id FROM '.VPN_Chat_Store::table('sessions').' WHERE customer_id=0') as $session){
            VPN_Chat_Store::transaction(static function()use($wpdb,$session){
                $row=$wpdb->get_row($wpdb->prepare('SELECT customer_id FROM '.VPN_Chat_Store::table('sessions').' WHERE id=%d FOR UPDATE',$session),ARRAY_A);
                if(!$row||$row['customer_id'])return;
                $customer=VPN_Chat_Identity::create();
                $wpdb->update(VPN_Chat_Store::table('sessions'),['customer_id'=>$customer],['id'=>$session]);
                $wpdb->update(VPN_Chat_Store::table('conversations'),['customer_id'=>$customer],['session_id'=>$session]);
            });
        }
        // Legacy rows had actor IDs but no display snapshot. Preserve each account's
        // current profile once rather than relabelling all historical sales messages.
        foreach($wpdb->get_col('SELECT DISTINCT actor_id FROM '.VPN_Chat_Store::table('messages').' WHERE actor_id>0 AND sender_profile IS NULL') as $actor){
            $wpdb->update(VPN_Chat_Store::table('messages'),['sender_profile'=>wp_json_encode(VPN_Chat_Profiles::user((int)$actor))],['actor_id'=>(int)$actor,'sender_profile'=>null]);
        }
        update_option('vpn_chat_schema_version', self::VERSION, false);
        add_option('vpn_chat_settings', VPN_Chat_Settings::get(), '', false);
        $agent = ['read' => true, 'vpn_chat_agent' => true];
        add_role('vpn_chat_sales', 'VPN Chat Sales', $agent);
        add_role('vpn_chat_manager', 'VPN Chat Manager', $agent + ['vpn_chat_manage' => true]);
        $admin = get_role('administrator');
        if ($admin) { $admin->add_cap('vpn_chat_agent'); $admin->add_cap('vpn_chat_manage'); }
        VPN_Chat_Jobs::schedule();
    }
    public static function healthy(): bool {
        global $wpdb;
        foreach (self::TABLES as $name) {
            $table = VPN_Chat_Store::table($name);
            $engine = $wpdb->get_var($wpdb->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s', $table));
            if (strtoupper((string) $engine) !== 'INNODB') { return false; }
        }
        return true;
    }
}
