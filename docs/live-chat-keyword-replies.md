# VPN Live Chat 1.9.0 — trả lời tự động theo từ khóa

Yêu cầu ngày 13/10/2026: phản hồi ngay khi khách nhắn để họ có câu trả lời trong lúc sales chưa tiếp nhận. Chủ website chọn kịch bản theo từ khóa, không dùng AI/API.

## Cách hoạt động

Khi bật **VPN Live Chat → Cấu hình → Trả lời tự động theo từ khóa**, hội thoại chưa có người phụ trách nhận câu trả lời trong cùng request lưu tin. Receipt gửi về gồm tin khách và tin tự động đã commit; giao diện hiển thị ngay cả khi lượt sync tiếp theo lỗi. Không dùng WP-Cron, dịch vụ bên ngoài hay API key. Thời gian nhận vẫn phụ thuộc mạng và hosting.

Tin có nhãn **VPN Automated Assistant**, không dùng danh tính Tho Nguyen để giả làm nhân viên. Tin được lưu trong bảng chat hiện có, hiện ở inbox và khôi phục khi tải lại. Hàng chờ chưa nhận, marker chưa đọc của sales và nhắc SLA được giữ nguyên.

Trợ lý dừng khi sales nhận chat, đã có tin sales, khách yêu cầu gặp người, có vấn đề đơn hàng/khiếu nại hoặc sau 8 phản hồi. Khách vẫn nhắn tiếp bình thường; yêu cầu gặp người chỉ dừng automation cho hội thoại đó. Nhận chat bằng nút hoặc phản hồi đầu tiên của sales hoạt động như trước.

## Kịch bản đã viết

| Câu hỏi / từ khóa | Cách trả lời |
|---|---|
| Hi / Hello / Xin chào | Chào khách, hỏi loại túi/hộp và số lượng |
| Price / quote / giá / báo giá | Giải thích các yếu tố tính giá, hỏi thông tin còn thiếu |
| MOQ / minimum / tối thiểu | Mức chung để lên kế hoạch 1.000 chiếc; xác nhận theo mẫu |
| Số lượng dưới 1.000 | Sales cần kiểm tra khả năng nhận đơn, chưa xác nhận |
| Sample / mẫu thử | Sales xác nhận khả năng làm mẫu, phí và thời gian |
| Logo / printing / artwork | Nhận yêu cầu in riêng; phương án theo giấy/artwork |
| Lead time / bao lâu / thời gian | Hỏi hạn nhận hàng, chưa cam kết số ngày |
| Shipping / delivery / vận chuyển | Cần số lượng, kích thước và nơi giao để kiểm tra |
| FSC / food safe / GSM / material | Cần bằng chứng và kiểm tra theo đúng sản phẩm |
| Factory / manufacturer / nhà máy | Nhà sản xuất bao bì giấy tại Việt Nam |
| Contact / email / liên hệ | Tiếp tục chat, Add email tùy chọn hoặc trang Contact |
| Human / nhân viên / đơn hàng / khiếu nại | Ghi nhận và dừng tự động để sales xử lý |
| Câu không khớp | Báo chưa nhận diện, gợi ý chủ đề và cách gặp nhân viên |

Tiếng Anh mặc định; nhận các câu tiếng Việt có dấu và một số cụm không dấu. Giữ ngôn ngữ trước đó với câu ngắn. Đọc tối đa 24 tin công khai gần nhất của chính hội thoại để giảm hỏi lại loại sản phẩm, số lượng, kích thước, quốc gia và email đã cung cấp. Câu chỉ có số lượng được nhận khi đi sau câu hỏi số lượng. Đây là kịch bản hữu hạn, không hiểu mọi cách diễn đạt hay mọi quốc gia.

Nội dung cố định trong `wp-content/plugins/vpn-live-chat/includes/auto-reply.php`; quản lý bật/tắt trong Cấu hình. Không tự sinh giá, lịch giao, chứng nhận, tải trọng hoặc kết quả kiểm tra đơn hàng. Email vẫn tùy chọn; email khách gõ trong transcript không tự cấp quyền lịch sử.

## Deploy

Pull/deploy **toàn bộ thư mục plugin `wp-content/plugins/vpn-live-chat/`**, không chỉ file chính. Không cần đổi schema, tạo API key hay dùng Product Sample Deploy. Purge cache HTML/assets của website để frontend dùng JS 1.9.0. Vào **VPN Live Chat → Cấu hình**, bật **Trả lời tự động theo từ khóa** và lưu. Mặc định khi nâng cấp là tắt; lựa chọn đã lưu được giữ. Local đã bật để chủ website thử.

Production chưa được triển khai hoặc gửi tin thử trong tác vụ này. Sau deploy, xác nhận nhận chat đang bật, REST không bị cache, gửi một câu MOQ từ trình duyệt thử và nhận chat trong inbox để xác nhận tự động dừng. Tắt checkbox để quay về cách chat trước đó; không xóa lịch sử.

## Kiểm tra

Database thật cô lập `vpn_chat_test` tại port 3311, HTTP 8091, có sink chặn HTTP/email ra ngoài. Bộ `keyword-replies.php` kiểm tra kịch bản, receipt, retry, unread, SLA, quyền, chuyển sang sales, cap và chat đóng. `keyword-browser.mjs` kiểm tra desktop 1366px, mobile 390/320px, receipt khi sync 503, khôi phục transcript, nhãn tự động và bàn giao. `final-acceptance.php` kiểm tra hồi quy chat trước đó với automation tắt. Dữ liệu/cấu hình test được dọn sau suite.
