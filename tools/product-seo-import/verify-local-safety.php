<?php
/**
 * Verify local isolation controls without sending mail or making HTTP requests.
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/hopgiayvpn/';
$_SERVER['HTTPS'] = 'off';
require $root . '/wp-load.php';

$mailResult = apply_filters('pre_wp_mail', null, [
    'to' => 'nobody@example.invalid',
    'subject' => 'local safety test',
    'message' => 'This message must never be sent.',
    'headers' => [],
    'attachments' => [],
]);
$remoteResult = apply_filters('pre_http_request', false, [], 'https://example.invalid/webhook');
$localResult = apply_filters('pre_http_request', false, [], 'http://localhost/hopgiayvpn/');
$gatewayResult = apply_filters('woocommerce_available_payment_gateways', ['dummy' => new stdClass()]);
$webhookResult = apply_filters('woocommerce_webhook_should_deliver', true, null, null);
$emailResult = apply_filters('woocommerce_email_enabled_new_order', true, null, null);

$checks = [
    'database_is_hopgiayvpnmoi' => 'hopgiayvpnmoi' === DB_NAME,
    'prefix_is_wp' => 'wp_' === $GLOBALS['wpdb']->prefix,
    'home_is_local' => 'http://localhost/hopgiayvpn' === untrailingslashit(home_url()),
    'environment_is_local' => 'local' === wp_get_environment_type(),
    'blog_public_zero' => 0 === (int) get_option('blog_public'),
    'wp_cron_disabled' => defined('DISABLE_WP_CRON') && true === DISABLE_WP_CRON,
    'mail_preempted' => false === $mailResult,
    'remote_http_blocked' => is_wp_error($remoteResult) && 'hopgiayvpn_local_outbound_blocked' === $remoteResult->get_error_code(),
    'local_http_allowed' => false === $localResult,
    'payment_gateways_empty' => [] === $gatewayResult,
    'webhook_delivery_disabled' => false === $webhookResult,
    'woocommerce_email_disabled' => false === $emailResult,
];

$payload = [
    'created_at_utc' => gmdate('c'),
    'checks' => $checks,
    'passed' => !in_array(false, $checks, true),
    'production_requests_or_writes_performed_by_this_test' => 0,
];
$output = $root . '/artifacts/product-seo-final-v1/local-safety-qa.json';
file_put_contents($output, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

echo json_encode([
    'ok' => $payload['passed'],
    'passed_checks' => count(array_filter($checks)),
    'total_checks' => count($checks),
    'report' => $output,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($payload['passed'] ? 0 : 3);
