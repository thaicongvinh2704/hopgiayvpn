# Kiểm tra VPN Live Chat 1.8.2 — 06/10/2026

## 1.8.2 — Tin đã lưu phải hiện ngay phía khách, 06/10/2026

Tái hiện được lỗi: guest/start hoặc guest/send thành công nhưng UI chỉ hiện tin sau guest/sync. Sync thất bại khiến admin thấy tin mà khách không thấy. Poll đang chạy cũng có thể trả snapshot trước lần gửi. Kiểm tra hồi quy đã thất bại trước sửa và đạt sau sửa; chưa xác định riêng lỗi mạng/cache nào xảy ra trên hosting của ảnh người dùng.

Phản hồi gửi/retry trả nội dung, seq và thời gian của tin đã lưu. Widget hiển thị receipt ngay, không chờ sync và không khóa gửi trong lúc tải lịch sử. Poll sau gửi phải thực hiện một lượt mới sau poll đang chạy. Dùng POST/CSRF để tránh cache GET ở proxy, vẫn hỗ trợ GET cho client cũ. Không tăng cursor chỉ vì receipt, nên không bỏ sót tin sales hoặc ghi chú có seq xen giữa. Dedup theo seq và chèn theo thứ tự server khi receipt đến trước lịch sử.

61 kiểm tra backend, 34 browser HTTP thật và 117 UI/mock đạt (212 kiểm tra chạy trong lần sửa này); 35 launcher giữ kết quả 1.8.1, tổng hiện hành 247. Có tình huống sync 503 sau gửi, gửi tiếp khi sync lỗi, khôi phục/reload, snapshot poll cũ đang chạy, tin sales xen giữa, thứ tự và không nhân đôi. Cú pháp PHP/JS đạt. Database test đã dọn, không gửi mẫu hoặc xóa tin thật trên production. Deploy toàn bộ 1.8.2 rồi purge cache HTML/assets/CDN. Production chưa nghiệm thu bản mới.

## Kết quả

247 kiểm tra hiện hành đạt (212 chạy lại ở 1.8.2, 35 launcher giữ kết quả 1.8.1); cú pháp 12 file PHP và 4 file JavaScript đạt. Chat dùng giới hạn tần suất trên máy chủ, không cần Turnstile site key/secret. Gửi thật qua WordPress HTTP/database đã xác nhận trong môi trường cô lập. Chưa nghiệm thu bản 1.8.2 trên production sau deploy.

| Bộ kiểm tra | Số kiểm tra | Kết quả |
|---|---:|---|
| REST/database WordPress thật, test port 3311 | 61 | Đạt |
| Browser/HTTP WordPress thật, port 8091 | 34 | Đạt |
| Giao diện, lỗi mạng, mobile bằng HTTP giả lập | 117 | Đạt |
| Launcher, lời mời, animation, reduced motion | 35 | Đạt |

HTTP thật không dùng mock CAPTCHA; request ra ngoài và email bị chặn trong test. Database vpn_chat_test riêng, không dùng database local chính/production. Dữ liệu chat test đã dọn; không gửi tin mẫu lên production.

## 1.8.1 — Mở chat tức thì, 06/10/2026

Tải widget.js/chat.css cùng trang, trước launcher, và dựng sẵn khung ẩn. Nhấn biểu tượng mở khung đồng bộ ngay trong click handler; không khóa launcher trong lúc bootstrap/sync. Kết nối và lịch sử tải nền. Mở lại giữ transcript đã có và composer, không xóa lịch sử để chờ tải. Khách gõ khi đang kết nối vẫn giữ nháp; nếu bootstrap tìm được hội thoại cũ, chuyển nháp sang composer trả lời. Gửi chỉ bật khi đã có phiên/CSRF hợp lệ.

233 kiểm tra đạt: backend 55, browser HTTP thật 34, UI/mock 109, launcher 35. Kiểm tra bổ sung trì hoãn bootstrap/sync 1,6 giây xác nhận mở ngay, đóng/mở nhanh, giữ nháp và xóa thông báo connecting khi sẵn sàng. Không tạo phiên/chat chỉ vì tải sẵn giao diện. Cú pháp 12 PHP/4 JS đạt. Deploy 1.8.1 và purge cache HTML/assets/CDN; production chưa nghiệm thu bản mới. Mạng vẫn quyết định tốc độ nhận lịch sử/gửi tin, nhưng không cản việc mở khung.

## Chống spam

- Tối đa 2 tin trong mỗi giây; mặc định 10 tin/30 giây, 60 tin/5 phút, 3 hội thoại mới/10 phút theo mã khách. Tin đầu và tin tiếp theo dùng chung bộ đếm; nhiều tab/phiên của cùng khách cũng dùng chung.
- Giới hạn phụ theo IP: 1.000 tin/5 phút, 100 hội thoại mới/10 phút. Không dùng IP để ghép danh tính khách.
- Bộ đếm theo khoảng thời gian cố định, cập nhật nguyên tử bằng database. Có thể có đợt gửi sát ranh giới hai khoảng. Bốn tiến trình đồng thời với quota 2 chỉ có 2 request được chấp nhận.
- HTTP 429 trả Retry-After theo khoảng còn lại. Giao diện báo số giây cần chờ và giữ nháp; khách nhấn gửi lại. Retry tin đã lưu với cùng client ID không tạo bản sao, không tính thêm quota.
- Giữ cookie, CSRF, kiểm tra origin/quyền hội thoại, giới hạn độ dài và chỉ nhận văn bản. Không upload tệp qua chat.

## Luồng đã kiểm tra

Nút gửi và Enter; Shift+Enter xuống dòng; IME không gửi nhầm; ẩn danh không cần tên/email; lưu và khôi phục lịch sử; retry mất phản hồi không nhân đôi, không mất nháp mới; email tùy chọn lưu/sửa/xóa; nhân viên nhận chat và trả lời qua HTTP thật; badge chưa đọc/xác nhận đã đọc; ngăn đọc/gửi vào hội thoại khách khác; ghi chú riêng không lộ cho khách; hội thoại đóng không nhận tin mới. Desktop/mobile 1366/390/320px giữ composer trong viewport và không có lỗi JavaScript.

## Deploy và phần còn lại

Deploy toàn bộ plugin 1.8.2 rồi purge cache HTML/assets/CDN. Nâng cấp tự bật nhận chat mới một lần nếu widget đã bật; marker vpn_chat_rate_limits_version=1.8.0 giữ lựa chọn tắt của admin sau đó. Cài mới vẫn cần activate, bật widget/nhận chat và cấu hình paths. HTTPS và bảng database khỏe vẫn là điều kiện nhận chat; không cần tạo khóa CAPTCHA.

Trước cập nhật, production được kiểm tra chỉ đọc: bootstrap HTTP 200 nhưng accepting=false/site key rỗng. Đây là trạng thái bản cũ, không phải kết quả 1.8.1. Sau deploy cần xác nhận accepting=true, gửi được và nhận phản hồi trong inbox. SMTP thật, delivery email, scheduler và hiệu năng hosting chưa nghiệm thu. Chat không tự gửi phản hồi sales tới email khách.

## Bằng chứng

- artifacts/vpn-live-chat/evidence/final-audit-report.json
- artifacts/vpn-live-chat/evidence/final-backend-report.json
- artifacts/vpn-live-chat/evidence/final-real-browser-report.json
- artifacts/vpn-live-chat/evidence/gentle-followup-report.json
- tests/live-chat/final-acceptance.php, rate-worker.php, final-browser.mjs, gentle-followup.mjs, invitation.mjs

Backend và real-browser chạy tuần tự trên database test riêng. Các dịch vụ test do lần kiểm tra này khởi động được dừng khi hoàn tất.
