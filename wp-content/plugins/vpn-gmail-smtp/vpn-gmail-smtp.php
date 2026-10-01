<?php
/**
 * Plugin Name: VPN Gmail SMTP
 * Description: Gửi email WordPress qua Gmail cá nhân, cấu hình và gửi thư thử ngay trong trang quản trị.
 * Version: 1.0.1
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: VPN Packaging
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class VPN_Gmail_SMTP {
	const OPTION = 'vpn_gmail_smtp_settings';
	const RESULT = 'vpn_gmail_smtp_result_';
	const PAGE   = 'vpn-gmail-smtp';

	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_vpn_gmail_smtp_save', array( __CLASS__, 'save' ) );
		add_action( 'admin_post_vpn_gmail_smtp_test', array( __CLASS__, 'test_email' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'action_links' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'register_transport' ), 100 );
	}

	public static function settings() {
		return wp_parse_args(
			(array) get_option( self::OPTION, array() ),
			array(
				'enabled' => false,
				'email' => '',
				'password' => '',
				'name' => 'VPN Packaging Factory',
				'port' => 587,
				'test_recipient' => 'sales.vpn@hopgiayvpn.com',
			)
		);
	}

	public static function encryption_available() {
		return function_exists( 'openssl_encrypt' ) && in_array( 'aes-256-gcm', openssl_get_cipher_methods(), true );
	}

	private static function encryption_key() {
		return hash( 'sha256', wp_salt( 'auth' ) . '|vpn-gmail-smtp-v1', true );
	}

	public static function encrypt_password( $password ) {
		if ( ! self::encryption_available() ) {
			return false;
		}
		$iv = random_bytes( 12 );
		$tag = '';
		$encrypted = openssl_encrypt( $password, 'aes-256-gcm', self::encryption_key(), OPENSSL_RAW_DATA, $iv, $tag );
		return false === $encrypted ? false : base64_encode( $iv . $tag . $encrypted );
	}

	public static function decrypt_password( $encrypted ) {
		if ( ! self::encryption_available() || ! is_string( $encrypted ) ) {
			return false;
		}
		$raw = base64_decode( $encrypted, true );
		if ( false === $raw || strlen( $raw ) <= 28 ) {
			return false;
		}
		return openssl_decrypt( substr( $raw, 28 ), 'aes-256-gcm', self::encryption_key(), OPENSSL_RAW_DATA, substr( $raw, 0, 12 ), substr( $raw, 12, 16 ) );
	}

	public static function conflict() {
		$plugins = array_merge(
			(array) get_option( 'active_plugins', array() ),
			array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) )
		);
		foreach ( $plugins as $plugin ) {
			if ( false !== strpos( $plugin, 'wp-mail-smtp' ) ) {
				return 'WP Mail SMTP đang bật. Hãy tắt plugin đó tại Plugins trước khi dùng VPN Gmail SMTP.';
			}
		}
		if ( defined( 'CUSTOM_BOX_GMAIL_ADDRESS' ) || defined( 'CUSTOM_BOX_GMAIL_APP_PASSWORD' ) ) {
			return 'Phát hiện cấu hình Gmail cũ trong wp-config. Hãy bỏ cấu hình cũ trước khi sử dụng trang cài đặt này.';
		}
		return '';
	}

	public static function ready() {
		$settings = self::settings();
		return ! empty( $settings['enabled'] ) && '' === self::test_blocker();
	}

	public static function test_blocker() {
		$settings = self::settings();
		if ( ! is_email( $settings['email'] ) ) {
			return 'Chưa lưu Gmail người gửi. Điền cấu hình và bấm Lưu cấu hình trước.';
		}
		if ( ! self::encryption_available() ) {
			return 'Máy chủ cần OpenSSL hỗ trợ AES-256-GCM để lưu và đọc mật khẩu ứng dụng.';
		}
		if ( '' === $settings['password'] ) {
			return 'Chưa lưu mật khẩu ứng dụng. Nhập mật khẩu 16 ký tự rồi bấm Lưu cấu hình.';
		}
		if ( 16 !== strlen( (string) self::decrypt_password( $settings['password'] ) ) ) {
			return 'Không đọc được mật khẩu đã lưu. Nhập lại mật khẩu ứng dụng rồi lưu cấu hình.';
		}
		return self::conflict();
	}

	public static function register_transport() {
		if ( ! self::ready() ) {
			return;
		}
		self::bind_transport();
	}

	private static function bind_transport() {
		add_filter( 'wp_mail_from', array( __CLASS__, 'from_email' ), 100 );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ), 100 );
		add_action( 'phpmailer_init', array( __CLASS__, 'configure_mailer' ), 100 );
	}

	public static function from_email() {
		return self::settings()['email'];
	}

	public static function from_name() {
		return self::settings()['name'];
	}

	public static function configure_mailer( $mailer ) {
		$settings = self::settings();
		$mailer->setFrom( $settings['email'], $settings['name'], false );
		$mailer->isSMTP();
		$mailer->Host = 'smtp.gmail.com';
		$mailer->Port = (int) $settings['port'];
		$mailer->SMTPSecure = 465 === $mailer->Port ? 'ssl' : 'tls';
		$mailer->SMTPAuth = true;
		$mailer->Username = $settings['email'];
		$mailer->Password = self::decrypt_password( $settings['password'] );
		$mailer->Sender = $settings['email'];
		$mailer->Timeout = 20;
		$mailer->SMTPDebug = 0;
	}

	public static function menu() {
		add_options_page( 'VPN Gmail SMTP', 'VPN Gmail SMTP', 'manage_options', self::PAGE, array( __CLASS__, 'render' ) );
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">Cài đặt</a>' );
		return $links;
	}

	private static function authorize( $nonce ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Bạn không có quyền cấu hình gửi email.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( $nonce );
	}

	private static function input( $key ) {
		return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
	}

	private static function result( $success, $message ) {
		set_transient( self::RESULT . get_current_user_id(), array( 'success' => $success, 'message' => $message ), 120 );
		wp_safe_redirect( admin_url( 'options-general.php?page=' . self::PAGE ) );
		exit;
	}

	public static function validate_settings( $input, $old ) {
		$email = sanitize_email( $input['email'] );
		$recipient = sanitize_email( $input['test_recipient'] );
		if ( ! is_email( $email ) || ! preg_match( '/@(gmail|googlemail)\.com$/i', $email ) ) {
			return new WP_Error( 'email', 'Nhập địa chỉ Gmail cá nhân hợp lệ, ví dụ ten@gmail.com.' );
		}
		if ( ! is_email( $recipient ) ) {
			return new WP_Error( 'recipient', 'Email nhận thư thử không hợp lệ.' );
		}
		// Google may copy the grouped password with non-breaking spaces.
		$password = preg_replace( '/[\s\x{00A0}\x{200B}\x{FEFF}]+/u', '', $input['password'] );
		if ( ! is_string( $password ) ) {
			return new WP_Error( 'password', 'Mật khẩu ứng dụng chứa ký tự không hợp lệ. Hãy dán lại mật khẩu Google đã cấp.' );
		}
		$encrypted = $old['password'];
		if ( '' !== $password ) {
			if ( ! preg_match( '/^[a-zA-Z0-9]{16}$/', $password ) ) {
				return new WP_Error( 'password', 'Dùng mật khẩu ứng dụng Gmail gồm 16 ký tự. Không nhập mật khẩu đăng nhập Gmail.' );
			}
			$encrypted = self::encrypt_password( $password );
			if ( false === $encrypted ) {
				return new WP_Error( 'encryption', 'Máy chủ cần OpenSSL hỗ trợ AES-256-GCM để lưu mật khẩu ứng dụng.' );
			}
		} elseif ( $email !== $old['email'] || false === self::decrypt_password( $encrypted ) ) {
			return new WP_Error( 'password', 'Hãy nhập mật khẩu ứng dụng cho Gmail này.' );
		}
		return array(
			'enabled' => ! empty( $input['enabled'] ),
			'email' => $email,
			'password' => $encrypted,
			'name' => sanitize_text_field( $input['name'] ) ?: 'VPN Packaging Factory',
			'port' => '465' === (string) $input['port'] ? 465 : 587,
			'test_recipient' => $recipient,
		);
	}

	public static function save() {
		self::authorize( 'vpn_gmail_smtp_save' );
		$input = array();
		foreach ( array( 'email', 'password', 'name', 'port', 'test_recipient', 'enabled' ) as $key ) {
			$input[ $key ] = self::input( $key );
		}
		$settings = self::validate_settings( $input, self::settings() );
		if ( is_wp_error( $settings ) ) {
			self::result( false, $settings->get_error_message() );
		}
		update_option( self::OPTION, $settings, false );
		if ( get_option( self::OPTION ) !== $settings ) {
			self::result( false, 'Không lưu được cấu hình vào cơ sở dữ liệu WordPress. Hãy thử lưu lại.' );
		}
		$message = 'Đã lưu Gmail và mật khẩu ứng dụng. Ô mật khẩu để trống khi tải lại là bình thường; mật khẩu vẫn được lưu.';
		if ( empty( $settings['enabled'] ) ) {
			$message .= ' Bạn có thể gửi thư thử ngay. Để dùng cho form, tích Bật gửi rồi lưu lại.';
		}
		self::result( true, $message );
	}

	public static function test_email() {
		self::authorize( 'vpn_gmail_smtp_test' );
		if ( self::test_blocker() ) {
			self::result( false, self::test_blocker() );
		}
		// Test the saved account for this request without changing the enabled setting.
		self::bind_transport();
		// Verify that the configured transport was reached; a mail plugin may short-circuit wp_mail().
		$used_gmail = false;
		$error_message = '';
		$observe = static function ( $mailer ) use ( &$used_gmail ) {
			$used_gmail = 'smtp' === $mailer->Mailer && 'smtp.gmail.com' === $mailer->Host && self::from_email() === $mailer->Username;
		};
		$failure = static function ( $error ) use ( &$error_message ) {
			$error_message = $error->get_error_message();
		};
		add_action( 'phpmailer_init', $observe, PHP_INT_MAX );
		add_action( 'wp_mail_failed', $failure );
		$settings = self::settings();
		$sent = wp_mail( $settings['test_recipient'], '[VPN Gmail SMTP] Kiểm tra gửi email', 'Thư thử từ ' . home_url() . '. Nếu bạn nhận được thư này, website đã gửi được email qua Gmail cá nhân.', array( 'Content-Type: text/plain; charset=UTF-8' ) );
		remove_action( 'phpmailer_init', $observe, PHP_INT_MAX );
		remove_action( 'wp_mail_failed', $failure );
		if ( $sent && $used_gmail ) {
			self::result( true, 'Gmail đã chấp nhận thư thử. Kiểm tra Inbox và Spam của ' . $settings['test_recipient'] . '.' );
		}
		$message = 'Không gửi được thư thử. Kiểm tra mật khẩu ứng dụng; nếu kết nối bị chặn, thử chuyển cổng 587 sang 465 rồi lưu lại.';
		if ( ! $used_gmail ) {
			$message = 'Một cấu hình gửi mail khác đang can thiệp. Tắt plugin gửi mail khác rồi thử lại.';
		} elseif ( false !== stripos( $error_message, 'authenticate' ) ) {
			$message = 'Gmail từ chối đăng nhập. Kiểm tra địa chỉ Gmail và tạo lại mật khẩu ứng dụng.';
		}
		self::result( false, $message );
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings = self::settings();
		$has_password = false !== self::decrypt_password( $settings['password'] );
		$test_blocker = self::test_blocker();
		$result = get_transient( self::RESULT . get_current_user_id() );
		delete_transient( self::RESULT . get_current_user_id() );
		?>
		<div class="wrap">
			<h1>VPN Gmail SMTP</h1>
			<p>Gửi email của website bằng Gmail cá nhân. Các form báo giá hiện gửi tới <strong>sales.vpn@hopgiayvpn.com</strong>; email khách vẫn được giữ ở Reply-To.</p>
			<?php if ( $result ) : ?>
				<div class="notice <?php echo $result['success'] ? 'notice-success' : 'notice-error'; ?>"><p><?php echo esc_html( $result['message'] ); ?></p></div>
			<?php endif; ?>
			<?php if ( self::conflict() ) : ?>
				<div class="notice notice-warning"><p><?php echo esc_html( self::conflict() ); ?> <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Mở Plugins</a></p></div>
			<?php endif; ?>
			<?php if ( ! self::encryption_available() ) : ?>
				<div class="notice notice-error"><p>Máy chủ cần OpenSSL hỗ trợ AES-256-GCM để lưu mật khẩu ứng dụng.</p></div>
			<?php endif; ?>
			<p><strong>Gửi cho form:</strong> <?php echo empty( $settings['enabled'] ) ? 'Đang tắt. Tích Bật gửi và lưu cấu hình để sử dụng cho website.' : ( self::ready() ? 'Đã bật. Cần gửi thư thử để xác nhận kết nối.' : 'Đã chọn bật, nhưng cấu hình đang bị chặn. Xem lý do bên dưới.' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="vpn_gmail_smtp_save">
				<?php wp_nonce_field( 'vpn_gmail_smtp_save' ); ?>
				<table class="form-table" role="presentation">
					<tr><th scope="row"><label for="vpn-gmail-email">Gmail người gửi</label></th><td><input class="regular-text" type="email" id="vpn-gmail-email" name="email" value="<?php echo esc_attr( $settings['email'] ); ?>" placeholder="ten@gmail.com" required></td></tr>
					<tr><th scope="row"><label for="vpn-gmail-password">Mật khẩu ứng dụng</label></th><td><input class="regular-text" type="password" id="vpn-gmail-password" name="password" value="" autocomplete="new-password" placeholder="<?php echo $has_password ? 'Đã lưu — để trống để giữ nguyên' : ''; ?>" <?php echo $has_password ? '' : 'required'; ?>>
						<p class="description"><?php echo $has_password ? 'Đã lưu mật khẩu. Để trống để giữ nguyên, hoặc nhập mật khẩu ứng dụng mới.' : 'Nhập mật khẩu ứng dụng Gmail gồm 16 ký tự.'; ?></p>
						<p class="description">Bật Xác minh 2 bước, sau đó <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer">tạo mật khẩu ứng dụng</a>. Không dùng mật khẩu đăng nhập Gmail.</p></td></tr>
					<tr><th scope="row"><label for="vpn-gmail-name">Tên người gửi</label></th><td><input class="regular-text" type="text" id="vpn-gmail-name" name="name" value="<?php echo esc_attr( $settings['name'] ); ?>"></td></tr>
					<tr><th scope="row"><label for="vpn-gmail-port">Cổng kết nối</label></th><td><select id="vpn-gmail-port" name="port"><option value="587" <?php selected( $settings['port'], 587 ); ?>>587 — STARTTLS</option><option value="465" <?php selected( $settings['port'], 465 ); ?>>465 — TLS</option></select></td></tr>
					<tr><th scope="row"><label for="vpn-gmail-recipient">Email nhận thư thử</label></th><td><input class="regular-text" type="email" id="vpn-gmail-recipient" name="test_recipient" value="<?php echo esc_attr( $settings['test_recipient'] ); ?>" required><p class="description">Chỉ dùng cho nút gửi thử. Email nhận báo giá vẫn theo cấu hình form hiện tại.</p></td></tr>
					<tr><th scope="row">Bật gửi</th><td><label><input type="checkbox" name="enabled" value="1" <?php checked( $settings['enabled'] ); ?>> Gửi email WordPress qua Gmail này</label><p class="description">Áp dụng cho form và các thư hệ thống gửi qua wp_mail().</p></td></tr>
				</table>
				<?php submit_button( 'Lưu cấu hình' ); ?>
			</form>
			<hr>
			<h2>Gửi thư thử</h2>
			<p>Lưu cấu hình trước, rồi gửi thư thử và kiểm tra hộp thư nhận. Có thể gửi thử khi Bật gửi đang tắt; gửi thử không tự bật gửi cho form.</p>
			<?php if ( $test_blocker ) : ?>
				<div class="notice notice-warning inline"><p><strong>Chưa thể gửi thử:</strong> <?php echo esc_html( $test_blocker ); ?></p></div>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="vpn_gmail_smtp_test">
				<?php wp_nonce_field( 'vpn_gmail_smtp_test' ); ?>
				<?php submit_button( 'Gửi thư thử', 'secondary', 'submit', false, '' === $test_blocker ? array() : array( 'disabled' => 'disabled' ) ); ?>
			</form>
		</div>
		<?php
	}
}

VPN_Gmail_SMTP::boot();
