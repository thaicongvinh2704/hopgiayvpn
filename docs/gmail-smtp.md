# VPN Gmail SMTP

Plugin WordPress tự viết để gửi email qua Gmail cá nhân. Cấu hình ngay trong **Cài đặt → VPN Gmail SMTP**, không cần sửa `wp-config.php`, nhập mật khẩu qua chat hoặc cài dịch vụ SendLayer/Brevo.

## Cài đặt

1. Pull/deploy thư mục `wp-content/plugins/vpn-gmail-smtp` lên website, rồi vào **Plugins → VPN Gmail SMTP → Kích hoạt**. Hoặc tải file ZIP plugin tại **Plugins → Cài mới → Tải plugin lên**.
2. Vào **Cài đặt → VPN Gmail SMTP**. Nhập Gmail cá nhân, tên người gửi và email nhận thư thử. Email nhận báo giá hiện tại vẫn là `sales.vpn@hopgiayvpn.com`.
3. Bật Xác minh 2 bước cho Gmail rồi [tạo mật khẩu ứng dụng](https://myaccount.google.com/apppasswords) riêng cho website. Nhập mật khẩu ứng dụng 16 ký tự vào trang cài đặt. Không nhập mật khẩu đăng nhập Google thông thường. Xem [hướng dẫn Google](https://support.google.com/accounts/answer/185833?hl=en).
4. Chọn **Gửi email WordPress qua Gmail này**, lưu cấu hình, rồi tắt **WP Mail SMTP** đang dùng SendLayer trong trang Plugins. VPN Gmail SMTP cảnh báo và chưa sử dụng Gmail khi WP Mail SMTP vẫn đang bật.
5. Bấm **Gửi thư thử**. Khi Gmail chấp nhận thư, kiểm tra Inbox và Spam của email nhận. Sau đó gửi một form thử, kiểm tra email báo giá và xác nhận nút Trả lời trỏ tới email khách.

Ô mật khẩu trống sau khi lưu là bình thường: plugin không hiển thị lại mật khẩu đã lưu. Từ phiên bản 1.0.1, có thể gửi thư thử trước khi tích Bật gửi. Nếu nút gửi thử bị mờ, lý do xuất hiện ngay phía trên nút (chưa lưu cấu hình, không đọc được mật khẩu hoặc WP Mail SMTP còn bật). Gửi thư thử không tự thay đổi lựa chọn Bật gửi của website.

Chưa có cấu hình thì plugin không thay đổi cách gửi email hiện tại. Chỉ người có quyền quản trị `manage_options` được cấu hình hoặc gửi thư thử; các thao tác đều kiểm tra nonce.

## Phạm vi

Plugin dùng PHPMailer có sẵn trong WordPress, kết nối `smtp.gmail.com` bằng TLS. Email người gửi luôn là Gmail đã đăng nhập; nội dung, người nhận, tệp đính kèm và Reply-To của form được giữ nguyên. Plugin áp dụng cho các thư qua `wp_mail()`, bao gồm thư hệ thống WordPress. Các cơ chế gửi thư qua API riêng cần cấu hình riêng.

Không phải chỉnh cấu hình máy chủ khi hosting đã cho phép SMTP ra ngoài. Nếu cổng 587 bị chặn, chọn 465 và lưu lại. Nếu cả hai cổng đều bị chặn, cần nhà cung cấp hosting cho phép kết nối; PHP hoặc plugin không thể tự mở firewall.

## Mật khẩu

Mật khẩu ứng dụng được mã hóa AES-256-GCM trong database, không nằm trong Git hoặc gói ZIP. Trường mật khẩu không hiển thị lại giá trị đã lưu. Để trống khi lưu sẽ giữ mật khẩu cũ; khi đổi Gmail cần nhập mật khẩu ứng dụng tương ứng.

Máy chủ cần PHP 7.4 trở lên và OpenSSL hỗ trợ AES-256-GCM. Nếu thay khóa/salt của WordPress, phải nhập lại mật khẩu ứng dụng vì khóa mã hóa thay đổi. Google cũng thu hồi mật khẩu ứng dụng khi thay mật khẩu tài khoản; cần tạo và nhập lại mật khẩu ứng dụng trong trường hợp đó.

Gmail áp dụng giới hạn gửi của tài khoản cá nhân. SMTP chấp nhận thư không đảm bảo thư đã vào Inbox, nên luôn kiểm tra hộp thư nhận.

## Chuyển từ bản cấu hình trước

MU plugin và công cụ CLI cũ đã được thay bằng plugin này. Nếu đã đưa bản cũ lên hosting, xóa `wp-content/mu-plugins/custom-box-gmail-smtp.php` và bỏ các hằng `CUSTOM_BOX_GMAIL_ADDRESS`, `CUSTOM_BOX_GMAIL_APP_PASSWORD` trước khi sử dụng plugin mới. Nếu chưa triển khai bản cũ thì không cần bước này.

Để ngừng dùng Gmail, tắt VPN Gmail SMTP trong trang Plugins. Dữ liệu cài đặt được giữ để lần sau kích hoạt không phải nhập lại. Nếu quay về SendLayer, cần xử lý lỗi timeout trước đó để gửi thư hoạt động.

## Kiểm tra local

Chạy `php tests/gmail-smtp/run.php`. Kiểm tra lưu và mã hóa cấu hình, giữ mật khẩu khi lưu lại, từ chối Gmail/mật khẩu không hợp lệ, giới hạn quyền quản trị, kiểm tra nonce, cấu hình PHPMailer, Reply-To và tệp đính kèm. Không gửi email trong các kiểm tra local.
