=== VPN Gmail SMTP ===
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Gửi email WordPress qua Gmail cá nhân, cấu hình trong Cài đặt → VPN Gmail SMTP.

== Installation ==

1. Deploy thư mục vpn-gmail-smtp vào wp-content/plugins hoặc tải ZIP plugin qua WordPress.
2. Kích hoạt VPN Gmail SMTP và mở Cài đặt → VPN Gmail SMTP.
3. Nhập Gmail và mật khẩu ứng dụng 16 ký tự sau khi bật Xác minh 2 bước.
4. Bật gửi Gmail, lưu cấu hình, tắt WP Mail SMTP/SendLayer, rồi gửi thư thử.

== Notes ==

Sử dụng PHPMailer có sẵn trong WordPress, không cần thư viện bên ngoài.
Gửi qua smtp.gmail.com với STARTTLS cổng 587 hoặc TLS cổng 465.
Mật khẩu ứng dụng lưu mã hóa bằng OpenSSL và khóa/salt WordPress.
Nếu thay salt WordPress hoặc mật khẩu Google, nhập lại mật khẩu ứng dụng.
Áp dụng cho thư gửi qua wp_mail(), giữ Reply-To và tệp đính kèm.
Hosting cần cho phép SMTP ra ngoài; Gmail vẫn áp dụng giới hạn tài khoản.
