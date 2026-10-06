# Kiểm tra cuối VPN Live Chat 1.7.2 — 06/10/2026

## Kết quả

207 kiểm tra đạt; kiểm tra cú pháp 12 file PHP và 4 file JS của plugin đạt. Các kiểm tra dưới đây xác nhận luồng ứng dụng trong môi trường test. Chưa xác nhận gửi tin thành công trên production vì website thật hiện không nhận chat mới và chưa có Turnstile site key.

| Bộ kiểm tra | Số kiểm tra | Kết quả |
|---|---:|---|
| REST / database WordPress thật, riêng port 3311 | 43 | Đạt |
| Browser / HTTP WordPress thật, riêng port 8091 | 30 | Đạt |
| Giao diện, lỗi mạng và mobile bằng API giả lập | 99 | Đạt |
| Lời mời, launcher, animation và reduced motion | 35 | Đạt |

Backend và browser HTTP dùng database `vpn_chat_test`, không dùng database local chính hoặc production. Chỉ chống spam và email bên ngoài được giả lập trong môi trường riêng này; REST, cookie, CSRF, database, login nhân viên và inbox là thật. Dữ liệu chat test đã được dọn. Không gửi tin mẫu lên production.

## Các luồng được xác nhận

- Gửi bằng nút và Enter; Shift+Enter xuống dòng; Enter trong lúc IME đang ghép chữ không gửi nhầm.
- Gửi ẩn danh không cần tên/email; tin đầu và các tin tiếp theo lưu được, khôi phục sau reload.
- Retry cùng client ID không nhân đôi tin đầu hoặc tin sau; không cho đổi payload với cùng retry ID.
- Lưu/sửa/xóa email, email tùy chọn trước tin đầu, validation, lỗi lưu giữ nháp; email đã lưu khôi phục được.
- Menu mã khách/lịch sử, nhập mã xác minh qua menu, Escape đóng menu trước; các kiểm tra xác minh UI dùng API giả lập, SMTP production chưa được nghiệm thu.
- Inbox nhân viên chọn khách, nhận hội thoại rồi gửi phản hồi qua HTTP thật; khách nhận đúng phản hồi.
- Widget thu nhỏ vẫn hiển thị badge tin sales thật; mở xem đúng vùng cuối thì xác nhận đã đọc và xóa badge.
- Cookie/CSRF/origin/xác minh chống spam, giới hạn nội dung, từ chối upload, khách khác không đọc/gửi vào hội thoại, tài khoản không có quyền không trả lời, ghi chú riêng không lộ cho khách.
- Hội thoại đóng không nhận tin mới; có thể tạo hội thoại mới. Thiếu cấu hình chống spam bị từ chối ở cả client và backend.
- Desktop/mobile 1366/390/320px, composer trong viewport, card email tùy chọn và có thể đóng, animation chạy giới hạn và reduced motion.

## Hai sửa đổi cuối trong 1.7.2

1. Mất phản hồi khi gửi tin đầu: nếu khách viết nội dung mới trước khi retry tin cũ, nội dung mới được chuyển sang ô nhắn tiếp theo sau khi tin cũ xác nhận. Không mất nháp và không tự gửi nội dung mới.
2. Enter không vượt qua nút gửi đã khóa của hội thoại đóng. Nháp vẫn được giữ và thông báo yêu cầu tạo hội thoại mới.

Lỗi mất nháp đã được tái hiện bằng kiểm tra thất bại trước sửa và kiểm tra đạt sau sửa. Các luồng browser/HTTP thật được chạy lại trên 1.7.2.

## Trạng thái production tại lần kiểm tra cuối

Trình duyệt mở trang và bootstrap trên hopgiayvpn.com: HTTP 200, `accepting=false`, `site_key` rỗng, challenge không hiện, nút gửi bị khóa. Chỉ mở chat để đọc cấu hình công khai; không gửi tin hay xem hội thoại khách. Online trong header là cấu hình hiển thị, không chứng minh backend sẵn sàng nhận tin.

Trước nghiệm thu production: đặt Turnstile site key cho domain thật ở VPN Live Chat → Cấu hình; đặt `VPN_CHAT_TURNSTILE_SECRET` trên hosting ngoài Git; bật nhận chat mới; đảm bảo Health/InnoDB/HTTPS đạt. Deploy 1.7.2 và purge cache HTML/assets/CDN. Sau khi cấu hình, mới kiểm tra gửi/nhận thật trên production; việc deploy code không tự tạo key hoặc copy options từ local.

## Bằng chứng và chạy lại

- `artifacts/vpn-live-chat/evidence/final-audit-report.json`
- `artifacts/vpn-live-chat/evidence/final-backend-report.json`
- `artifacts/vpn-live-chat/evidence/final-real-browser-report.json`
- `artifacts/vpn-live-chat/evidence/gentle-followup-report.json`
- `tests/live-chat/final-acceptance.php`: có guard DB test/port 3311, dọn bảng chat test trước/sau.
- `tests/live-chat/final-browser.mjs`: HTTP loopback 8091, gọi backend suite để dọn dữ liệu test khi kết thúc.
- `tests/live-chat/gentle-followup.mjs` và `invitation.mjs`: API giả lập; không tạo hội thoại database.

Không chạy hai bộ backend/real-browser đồng thời vì chúng dùng cùng database test. Các dịch vụ test do lượt kiểm tra này khởi động đã được dừng sau khi hoàn tất.
