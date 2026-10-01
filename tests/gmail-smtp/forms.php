<?php
/** Run the real theme mail handler and WordPress wp_mail(), without network delivery. */
define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins' );
define( 'WPMU_PLUGIN_DIR', ABSPATH . 'wp-content/mu-plugins' );
$wp_plugin_paths = array();
require ABSPATH . 'wp-includes/plugin.php';
require ABSPATH . 'wp-includes/formatting.php';
require ABSPATH . 'wp-includes/class-wp-error.php';
require ABSPATH . 'wp-includes/PHPMailer/Exception.php';
require ABSPATH . 'wp-includes/PHPMailer/PHPMailer.php';

$form_options = array();
$form_meta = array();
$form_messages = array();
$form_fail = false;
function get_option( $name, $default = false ) { return $GLOBALS['form_options'][ $name ] ?? $default; }
function get_site_option( $name, $default = false ) { return $default; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function wp_salt( $scheme ) { return 'form-test-salt-not-a-real-secret'; }
function wp_normalize_path( $path ) { return str_replace( '\\', '/', $path ); }
function is_utf8_charset() { return true; }
function wp_is_valid_utf8( $value ) { return 1 === preg_match( '//u', $value ); }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function network_home_url() { return 'https://hopgiayvpn.com'; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function get_bloginfo( $key ) { return 'charset' === $key ? 'UTF-8' : 'VPN Packaging Factory'; }
function absint( $value ) { return abs( (int) $value ); }
function get_post_type( $id ) { return isset( $GLOBALS['form_meta'][ $id ] ) ? 'custom_box_quote' : false; }
function get_post_meta( $id, $key, $single = false ) { return $GLOBALS['form_meta'][ $id ][ $key ] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['form_meta'][ $id ][ $key ] = $value; }
function delete_post_meta( $id, $key ) { unset( $GLOBALS['form_meta'][ $id ][ $key ] ); }
function current_time( $type ) { return '2026-10-01 12:00:00'; }

require ABSPATH . 'wp-includes/pluggable.php';
require ABSPATH . 'wp-content/plugins/vpn-gmail-smtp/vpn-gmail-smtp.php';
require ABSPATH . 'wp-content/themes/custom-box-theme/inc/quote-form-handler.php';
add_filter( 'custom_box_quote_form_logging_enabled', '__return_false' );
function __return_false() { return false; }

class FormTestMailer extends PHPMailer\PHPMailer\PHPMailer {
	public function send() {
		$GLOBALS['form_messages'][] = array(
			'host' => $this->Host, 'mailer' => $this->Mailer, 'from' => $this->From,
			'to' => $this->getToAddresses(), 'reply' => $this->getReplyToAddresses(),
			'body' => $this->Body, 'subject' => $this->Subject, 'attachments' => $this->getAttachments(),
		);
		if ( $GLOBALS['form_fail'] ) { throw new PHPMailer\PHPMailer\Exception( 'Simulated SMTP failure' ); }
		return true;
	}
}
$phpmailer = new FormTestMailer( true );
$form_options[ VPN_Gmail_SMTP::OPTION ] = array(
	'enabled' => true, 'email' => 'website-test@gmail.com',
	'password' => VPN_Gmail_SMTP::encrypt_password( 'abcdefghijklmnop' ),
	'name' => 'VPN Packaging Factory', 'port' => 587, 'test_recipient' => 'sales.vpn@hopgiayvpn.com',
);
do_action( 'plugins_loaded' );
$checks = 0;
function check_form( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	++$GLOBALS['checks'];
}

$sources = array(
	'home' => '', 'contact' => '', 'product' => '',
	'packaging_quick' => 'packaging_landing_quick_form',
	'paper_bag_quick' => 'custom_paper_bags_manufacturer_quick_form',
	'paper_bag_full' => 'custom_paper_bags_manufacturer',
	'paper_box' => 'paper_box_manufacturer',
	'pizza_box' => 'custom_pizza_boxes_manufacturer',
);
$id = 100;
foreach ( $sources as $location => $source ) {
	++$id;
	$form_meta[ $id ] = array(
		'_custom_box_quote_mail_status' => 'queued',
		'_custom_box_quote_data' => array(
			'full_name' => 'Test Customer', 'email' => 'customer@example.com', 'product_name' => 'Test packaging',
			'quantity' => '5000', 'quote_source' => $source, 'form_location' => $location,
			'utm_source' => 'form-integration-test', 'message' => 'Test project brief',
		),
		'_custom_box_quote_attachments' => array( ABSPATH . 'wp-content/plugins/vpn-gmail-smtp/readme.txt' ),
	);
	check_form( custom_box_send_queued_quote_email( $id ), $location . ': handler failed' );
	$mail = end( $form_messages );
	check_form( $mail['host'] === 'smtp.gmail.com' && $mail['mailer'] === 'smtp' && $mail['from'] === 'website-test@gmail.com', $location . ': incorrect transport' );
	check_form( $mail['to'][0][0] === 'sales.vpn@hopgiayvpn.com' && $mail['reply'][0][0] === 'customer@example.com', $location . ': incorrect recipient or Reply-To' );
	check_form( strpos( $mail['body'], 'Test project brief' ) !== false && strpos( $mail['body'], 'form-integration-test' ) !== false && count( $mail['attachments'] ) === 1, $location . ': lost project details or attachments' );
	check_form( get_post_meta( $id, '_custom_box_quote_mail_status', true ) === 'sent', $location . ': sent status not persisted' );
	$count = count( $form_messages );
	check_form( custom_box_send_queued_quote_email( $id ) && count( $form_messages ) === $count, $location . ': already-sent request was sent again' );
}

$form_meta[200] = $form_meta[101];
$form_meta[200]['_custom_box_quote_mail_status'] = 'queued';
$form_meta[200]['_custom_box_quote_mail_attempts'] = 0;
$form_fail = true;
check_form( ! custom_box_send_queued_quote_email( 200 ), 'SMTP failure must return false' );
check_form( get_post_meta( 200, '_custom_box_quote_mail_status', true ) === 'failed' && get_post_meta( 200, '_custom_box_quote_mail_error', true ) === 'Simulated SMTP failure', 'Failure must be retained for the quote record' );
$form_fail = false;
do_action( 'custom_box_send_queued_quote_email', 200 );
check_form( get_post_meta( 200, '_custom_box_quote_mail_status', true ) === 'sent' && get_post_meta( 200, '_custom_box_quote_mail_attempts', true ) === 2, 'Background retry must use the same Gmail transport' );
check_form( has_action( 'admin_post_custom_box_quote_form', 'custom_box_handle_quote_form' ) !== false && has_action( 'admin_post_nopriv_custom_box_quote_form', 'custom_box_handle_quote_form' ) !== false, 'Both signed-in and guest forms must use the shared handler' );

echo 'PASS: ' . $checks . " form integration checks with the real theme handler, WordPress wp_mail() and PHPMailer; no network mail sent.\n";
