<?php
/**
 * Plugin Name: VPN Live Chat
 * Description: Trò chuyện riêng tư với đội sales qua WordPress REST API và polling.
 * Version: 1.7.2
 * Requires at least: 6.2
 * Requires PHP: 8.0
 * Author: VPN Packaging
 */
defined('ABSPATH') || exit;
define('VPN_CHAT_FILE', __FILE__);
define('VPN_CHAT_VERSION', '1.7.2');
foreach (['settings', 'profiles', 'schema', 'store', 'security', 'identity', 'service', 'rest', 'jobs', 'ui'] as $part) {
    require_once __DIR__ . '/includes/' . $part . '.php';
}
register_activation_hook(__FILE__, ['VPN_Chat_Schema', 'activate']);
register_deactivation_hook(__FILE__, ['VPN_Chat_Jobs', 'deactivate']);
add_action('plugins_loaded', static function () {
    if (get_option('vpn_chat_schema_version') !== VPN_Chat_Schema::VERSION) {
        VPN_Chat_Schema::activate();
    }
    VPN_Chat_Settings::upgrade_invitation();
    VPN_Chat_REST::boot();
    VPN_Chat_Profiles::boot();
    VPN_Chat_Jobs::boot();
    VPN_Chat_UI::boot();
});
