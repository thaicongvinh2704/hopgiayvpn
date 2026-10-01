<?php
/** Local checks using WordPress hooks and PHPMailer, without a database or mail delivery. */
define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
define( 'WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins' );
define( 'WPMU_PLUGIN_DIR', ABSPATH . 'wp-content/mu-plugins' );
$wp_plugin_paths = array();
require ABSPATH . 'wp-includes/plugin.php';
require ABSPATH . 'wp-includes/formatting.php';
require ABSPATH . 'wp-includes/class-wp-error.php';
require ABSPATH . 'wp-includes/PHPMailer/Exception.php';
require ABSPATH . 'wp-includes/PHPMailer/PHPMailer.php';

$test_options = array();
$test_can_manage = true;
$test_nonce_valid = true;
$test_nonce_checks = array();
$test_result = null;
$test_db_writable = true;
class TestDenied extends RuntimeException {}
class TestRedirect extends RuntimeException {}
function get_option( $key, $default = false ) { return $GLOBALS['test_options'][ $key ] ?? $default; }
function get_site_option( $key, $default = false ) { return $default; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function wp_salt( $scheme ) { return 'local-test-salt-not-a-real-secret'; }
function wp_normalize_path( $path ) { return str_replace( '\\', '/', $path ); }
function is_utf8_charset() { return true; }
function wp_is_valid_utf8( $text ) { return 1 === preg_match( '//u', $text ); }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function current_user_can( $cap ) { return $GLOBALS['test_can_manage']; }
function wp_die( $message, $title = '', $args = array() ) { throw new TestDenied( $message ); }
function check_admin_referer( $action ) {
	$GLOBALS['test_nonce_checks'][] = $action;
	if ( ! $GLOBALS['test_nonce_valid'] ) { throw new TestDenied( 'Invalid nonce' ); }
}
function update_option( $name, $value, $autoload = null ) {
	if ( ! $GLOBALS['test_db_writable'] ) { return false; }
	$GLOBALS['test_options'][ $name ] = $value;
	$GLOBALS['test_autoload'] = $autoload;
}
function get_current_user_id() { return 1; }
function set_transient( $key, $value, $ttl ) { $GLOBALS['test_result'] = $value; }
function admin_url( $path ) { return 'https://example.com/wp-admin/' . $path; }
function wp_safe_redirect( $url ) { throw new TestRedirect( $url ); }
function home_url() { return 'https://example.com'; }
function wp_mail( $to, $subject, $message, $headers = array() ) {
	$GLOBALS['test_sent_to'] = $to;
	if ( 'short_circuit' === $GLOBALS['test_mail_outcome'] ) { return true; }
	$mailer = new PHPMailer\PHPMailer\PHPMailer( true );
	do_action_ref_array( 'phpmailer_init', array( &$mailer ) );
	if ( 'auth_failure' === $GLOBALS['test_mail_outcome'] ) {
		do_action( 'wp_mail_failed', new WP_Error( 'mail', 'SMTP Error: Could not authenticate.' ) );
		return false;
	}
	return true;
}

require ABSPATH . 'wp-content/plugins/vpn-gmail-smtp/vpn-gmail-smtp.php';
$checks = 0;
function verify( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	++$GLOBALS['checks'];
}

$old = VPN_Gmail_SMTP::settings();
verify( ! VPN_Gmail_SMTP::ready(), 'An unconfigured plugin must be inactive.' );
$input = array( 'email' => 'owner@gmail.com', 'password' => 'abcd efgh ijkl mnop', 'name' => 'VPN', 'port' => '587', 'test_recipient' => 'sales.vpn@hopgiayvpn.com', 'enabled' => '1' );
$saved = VPN_Gmail_SMTP::validate_settings( $input, $old );
verify( ! is_wp_error( $saved ), 'Valid Gmail configuration must save.' );
$unicode_input = $input;
$unicode_input['password'] = "abcd\u{00A0}efgh\u{200B}ijkl\u{FEFF}mnop";
$unicode_saved = VPN_Gmail_SMTP::validate_settings( $unicode_input, $old );
verify( ! is_wp_error( $unicode_saved ) && VPN_Gmail_SMTP::decrypt_password( $unicode_saved['password'] ) === 'abcdefghijklmnop', 'Grouped Google passwords must accept copied Unicode whitespace.' );
verify( VPN_Gmail_SMTP::decrypt_password( $saved['password'] ) === 'abcdefghijklmnop', 'Encryption must round-trip.' );
verify( false === strpos( serialize( $saved ), 'abcdefghijklmnop' ), 'App password must not be stored in plaintext.' );
$raw = base64_decode( $saved['password'] );
$raw[28] = chr( ord( $raw[28] ) ^ 1 );
verify( false === VPN_Gmail_SMTP::decrypt_password( base64_encode( $raw ) ), 'Modified ciphertext must fail authentication.' );
$input['password'] = '';
$preserved = VPN_Gmail_SMTP::validate_settings( $input, $saved );
verify( $preserved['password'] === $saved['password'], 'Blank password must preserve the existing credential.' );
$input['email'] = 'different@gmail.com';
verify( is_wp_error( VPN_Gmail_SMTP::validate_settings( $input, $saved ) ), 'Changing Gmail must require its app password.' );
$input['email'] = 'owner@gmail.com';
$input['password'] = 'wrong';
verify( is_wp_error( VPN_Gmail_SMTP::validate_settings( $input, $saved ) ), 'Reject an ordinary short password.' );
$input['password'] = 'abcdefghijklmnop';
$input['email'] = 'bad@example.com';
verify( is_wp_error( VPN_Gmail_SMTP::validate_settings( $input, $saved ) ), 'Reject a non-personal-Gmail sender.' );

$test_options[ VPN_Gmail_SMTP::OPTION ] = $saved;
verify( VPN_Gmail_SMTP::ready(), 'Configured Gmail must be ready.' );
$test_options['active_plugins'] = array( 'wp-mail-smtp/wp_mail_smtp.php' );
verify( ! VPN_Gmail_SMTP::ready(), 'An active WP Mail SMTP plugin must prevent conflicting transports.' );
verify( false !== strpos( VPN_Gmail_SMTP::test_blocker(), 'WP Mail SMTP' ), 'The disabled test button must explain the mail plugin conflict.' );
$test_options['active_plugins'] = array();
VPN_Gmail_SMTP::register_transport();
$mailer = new PHPMailer\PHPMailer\PHPMailer( true );
$mailer->setFrom( 'customer@example.com', 'Customer' );
$mailer->addReplyTo( 'customer@example.com', 'Customer' );
$mailer->addAddress( 'sales.vpn@hopgiayvpn.com' );
$mailer->addStringAttachment( 'sample', 'sample.txt' );
do_action_ref_array( 'phpmailer_init', array( &$mailer ) );
verify( $mailer->Mailer === 'smtp' && $mailer->Host === 'smtp.gmail.com' && $mailer->Port === 587 && $mailer->SMTPSecure === 'tls', '587 must use authenticated Gmail STARTTLS.' );
verify( $mailer->SMTPAuth && $mailer->Username === 'owner@gmail.com' && $mailer->Password === 'abcdefghijklmnop', 'PHPMailer must authenticate with the configured account.' );
verify( $mailer->From === 'owner@gmail.com' && $mailer->Sender === 'owner@gmail.com', 'From must match the authenticated Gmail account.' );
verify( $mailer->getReplyToAddresses()[0][0] === 'customer@example.com', 'Preserve customer Reply-To.' );
verify( $mailer->getToAddresses()[0][0] === 'sales.vpn@hopgiayvpn.com' && count( $mailer->getAttachments() ) === 1, 'Preserve recipient and attachments.' );
$test_options[ VPN_Gmail_SMTP::OPTION ]['port'] = 465;
VPN_Gmail_SMTP::configure_mailer( $mailer );
verify( $mailer->Port === 465 && $mailer->SMTPSecure === 'ssl', '465 must use implicit TLS.' );

$test_can_manage = false;
try { VPN_Gmail_SMTP::save(); verify( false, 'Unauthorized save was allowed.' ); } catch ( TestDenied $error ) {}
try { VPN_Gmail_SMTP::test_email(); verify( false, 'Unauthorized email test was allowed.' ); } catch ( TestDenied $error ) {}
$test_can_manage = true;
$test_nonce_valid = false;
try { VPN_Gmail_SMTP::save(); verify( false, 'Save without valid nonce was allowed.' ); } catch ( TestDenied $error ) {}
try { VPN_Gmail_SMTP::test_email(); verify( false, 'Email test without valid nonce was allowed.' ); } catch ( TestDenied $error ) {}
verify( $test_nonce_checks === array( 'vpn_gmail_smtp_save', 'vpn_gmail_smtp_test' ), 'Both write endpoints must check their nonce.' );
$test_nonce_valid = true;
$_POST = array( 'email' => 'owner@gmail.com', 'password' => '', 'name' => 'VPN', 'port' => '587', 'test_recipient' => 'sales.vpn@hopgiayvpn.com', 'enabled' => '1' );
try { VPN_Gmail_SMTP::save(); } catch ( TestRedirect $error ) {}
verify( $test_result['success'] && false === $test_autoload, 'Save must retain encrypted credentials without autoloading them.' );
$test_mail_outcome = 'success';
try { VPN_Gmail_SMTP::test_email(); } catch ( TestRedirect $error ) {}
verify( $test_result['success'] && $test_sent_to === 'sales.vpn@hopgiayvpn.com', 'The test must use its configured recipient and recognize Gmail acceptance.' );
$test_mail_outcome = 'short_circuit';
try { VPN_Gmail_SMTP::test_email(); } catch ( TestRedirect $error ) {}
verify( ! $test_result['success'], 'Do not report success when another mailer short-circuits WordPress.' );
$test_mail_outcome = 'auth_failure';
try { VPN_Gmail_SMTP::test_email(); } catch ( TestRedirect $error ) {}
verify( ! $test_result['success'] && false !== strpos( $test_result['message'], 'Gmail từ chối' ), 'Show an actionable Gmail authentication failure.' );
$test_options[ VPN_Gmail_SMTP::OPTION ]['enabled'] = false;
remove_action( 'phpmailer_init', array( 'VPN_Gmail_SMTP', 'configure_mailer' ), 100 );
remove_filter( 'wp_mail_from', array( 'VPN_Gmail_SMTP', 'from_email' ), 100 );
remove_filter( 'wp_mail_from_name', array( 'VPN_Gmail_SMTP', 'from_name' ), 100 );
VPN_Gmail_SMTP::register_transport();
verify( ! has_action( 'phpmailer_init', array( 'VPN_Gmail_SMTP', 'configure_mailer' ) ), 'Disabled Gmail must not take over normal website mail.' );
verify( '' === VPN_Gmail_SMTP::test_blocker(), 'A saved account can be tested before enabling Gmail for forms.' );
$test_mail_outcome = 'success';
try { VPN_Gmail_SMTP::test_email(); } catch ( TestRedirect $error ) {}
verify( $test_result['success'] && ! VPN_Gmail_SMTP::settings()['enabled'], 'Testing a disabled account must use Gmail without enabling the website transport.' );
$test_db_writable = false;
$_POST['name'] = 'Changed name';
try { VPN_Gmail_SMTP::save(); } catch ( TestRedirect $error ) {}
verify( ! $test_result['success'], 'A failed database write must not be reported as saved.' );
$test_db_writable = true;
$test_can_manage = false;
try { VPN_Gmail_SMTP::apply_all_forms(); verify( false, 'Unauthorized form integration was allowed.' ); } catch ( TestDenied $error ) {}
$test_can_manage = true;
$test_nonce_valid = false;
try { VPN_Gmail_SMTP::apply_all_forms(); verify( false, 'Integration without valid nonce was allowed.' ); } catch ( TestDenied $error ) {}
$test_nonce_valid = true;
$before_apply = VPN_Gmail_SMTP::settings();
try { VPN_Gmail_SMTP::apply_all_forms(); } catch ( TestRedirect $error ) {}
$after_apply = VPN_Gmail_SMTP::settings();
verify( $test_result['success'] && $after_apply['enabled'], 'Applying Gmail must enable the shared transport.' );
verify( $before_apply['email'] === $after_apply['email'] && $before_apply['password'] === $after_apply['password'], 'Applying Gmail must preserve the account and saved credential.' );
verify( end( $test_nonce_checks ) === 'vpn_gmail_smtp_apply', 'Apply must check its dedicated nonce.' );
$test_options['active_plugins'] = array( 'wp-mail-smtp/wp_mail_smtp.php' );
try { VPN_Gmail_SMTP::apply_all_forms(); } catch ( TestRedirect $error ) {}
verify( ! $test_result['success'], 'Applying Gmail must reject an active conflicting mailer.' );
echo 'PASS: ' . $checks . " checks, WordPress hooks and real PHPMailer; no email sent.\n";
