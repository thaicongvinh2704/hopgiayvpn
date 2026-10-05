# Thử Live Chat 1.5.0 trên website local

[Tài liệu tổng hợp chức năng, phần đã hoàn thành và còn thiếu](live-chat-status.md) là nguồn hiện trạng mới nhất. Plugin đã cài/bật trực tiếp trên WordPress tại `http://localhost/hopgiayvpn/`; không cần ZIP để thử.

## Thử gửi và trả lời

1. Mở `http://localhost/hopgiayvpn/wp-admin/admin.php?page=vpn-live-chat`, đăng nhập bằng admin WordPress hoặc tài khoản demo có quyền chat.
2. Chọn **Online** khi trực. Cấu hình local hiện hiển thị Tho Nguyen / Sale Manager luôn Online; trạng thái trực của nhân viên vẫn được lưu riêng.
3. Mở cửa sổ ẩn danh tại `http://localhost/hopgiayvpn/`, bấm chat. Lời chào tự động có avatar Thọ Nguyễn hiện sau khoảng 850 ms. Tên/email đều tùy chọn; nhập tin ở đáy khung và gửi.
4. Trong inbox bấm khách, nhập trả lời và Enter/nút gửi. Hội thoại mới tự nhận khi gửi phản hồi đầu; không cần bấm Nhận chat thủ công.
5. Thử đổi khách để kiểm tra draft riêng, reload widget để khôi phục lịch sử bằng cookie nhận diện 180 ngày gia hạn. Đổi trình duyệt cần xác minh email bằng nút Continue a previous conversation. Shift+Enter xuống dòng. Khách chỉ gửi văn bản.
6. **Chi tiết** chứa các thao tác quản lý; **Công cụ quản trị** và thông báo hệ thống mặc định thu gọn. Mobile có nút quay lại danh sách.

Dùng Ctrl+F5 sau khi sửa assets. Widget hiện bật ở homepage và `/hopgiayvpn/contact/`. Lời mời ngoài khung đang bật; khung chat mới có lời chào khi mở. Nội dung lời chào mặc định tiếng Anh, phân biệt rõ với phản hồi nhân viên thật.

## Tài khoản và giới hạn demo

Tài khoản riêng `vpn-chat-demo` có quyền quản lý chat, không có quyền quản trị website/bài viết/WooCommerce. Mật khẩu nằm trong `tests/live-chat/runtime/local-demo-access.json`, chỉ mở trực tiếp trong trình soạn thảo; không chép vào tài liệu. Apache chặn toàn bộ `tests/live-chat` qua HTTP bằng `.htaccess`.

MU `wp-content/mu-plugins/hopgiayvpn-live-chat-local-demo.php` giả lập Turnstile để thử khi chưa có khóa thật. Adapter giới hạn môi trường local, database local, localhost và loopback; token ngắn hạn, một lần dùng, gắn với phiên. Adapter và credentials không nằm trong ZIP plugin.

Email ra ngoài bị chặn, chưa cấu hình email đội sales. WP-Cron local đang tắt; demo không xác minh SMTP/cron nhắc SLA thật. Chưa deploy production. Cần thực hiện các mục còn mở trong tài liệu tổng hợp trước khi dùng thật. ZIP 1.0.0 là bản cũ; không ghi đè local 1.2.0 bằng ZIP đó.

Cấu hình trước demo được giữ tại option `vpn_chat_before_local_demo`. Khi cần ngừng thử, tắt widget/nhận chat mới trong **Cấu hình**; không xóa dữ liệu cũ.

## Kiểm thử đã dùng

Chạy từ repo thực tế `C:/xampp/htdocs/hopgiayvpn-main/hopgiayvpn-main`:

```powershell
C:/xampp/php/php.exe tests/live-chat/local-role-access.php
C:/xampp/php/php.exe tests/live-chat/inbox-access.php
node tests/live-chat/local-preview.mjs
node tests/live-chat/inbox-simple.mjs
```

Các browser test tạo hội thoại giả với email `example.invalid`, dùng tài khoản demo và chặn HTTP ngoài local. Chỉ chạy khi muốn kiểm thử; không phải bước cần làm để sử dụng inbox. Báo cáo và ảnh ở `tests/live-chat/results/` và `artifacts/vpn-live-chat/evidence/`. Mật khẩu/token không nằm trong báo cáo.

## Lưu trên Git

Mã plugin, công cụ local, test và báo cáo văn bản được lưu trong repository. Runtime, mật khẩu demo, mã xác minh, database, ZIP và ảnh chụp local được bỏ qua; không có cấu hình database hoặc nội dung chat thật trong commit này. Ảnh bằng chứng được nhắc trong tài liệu chỉ có ở máy local.
