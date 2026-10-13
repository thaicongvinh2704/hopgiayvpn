# Rà soát live chat 1.10.0 — 13/10/2026

Yêu cầu: trả lời ngay bằng kịch bản từ khóa, mọi nhóm hướng tới khách tự nguyện để lại email để sales tiếp tục tư vấn. Đã đối chiếu bộ 30 chủ đề sales và bổ sung 28 câu ngắn/tiếng Việt/trường hợp liên quan. Đây là bộ tình huống mẫu, chưa phải số liệu tần suất hoặc tỷ lệ chuyển đổi từ khách thật.

## Phủ các nhu cầu chính

| Nhóm | Xử lý và lý do mời email |
|---|---|
| Giá, MOQ, đơn nhỏ, ngân sách, wholesale | Thu thông số; sales xác nhận giá/khả năng nhận đơn và phương án phù hợp ngân sách |
| Mẫu thử, mẫu miễn phí, catalogue, ảnh mẫu | Mời email để sales chọn mẫu tham khảo hoặc kiểm tra phương án/chi phí mẫu |
| Logo, chưa có thiết kế, chưa biết kích thước, artwork | Mời email để sales xem cấu tạo/kích thước và hướng dẫn gửi thiết kế |
| Ép kim, UV, dập, Pantone, khay lót | Thu yêu cầu hiệu ứng/màu/sản phẩm; sales kiểm tra phương án hoàn thiện |
| Hộp nam châm, mỹ phẩm/nến, trang sức, túi đồng bộ | Thu nhu cầu theo sản phẩm; sales xem phương án phù hợp |
| Giấy, định lượng, thực phẩm, tái chế, FSC | Yêu cầu tài liệu theo đúng mẫu; mời email để sales cung cấp thông số phù hợp |
| Lịch sản xuất, cần gấp, ngày mai/tuần sau | Hỏi hạn nhận; mời email để sales kiểm tra lịch, không hứa ngày giao |
| Vận chuyển, phí giao, thuế/điều kiện giao | Thu nơi nhận; sales xác nhận phạm vi báo giá và điều kiện bằng văn bản |
| Đặt hàng, thanh toán, đặt lại | Thu thông số/mã đơn cũ; mời email nhận bước đặt hàng và báo giá |
| Chào hỏi, cảm ơn, câu không khớp, khách phải đi | Ghi nhận nhu cầu; mời email để tiếp tục tư vấn sau khi khách rời trang |
| Gặp nhân viên, kiểm tra đơn, khiếu nại | Dừng bot; lưu tin trong hàng chờ, mời liên hệ để sales xử lý |
| Email có bắt buộc/lo spam | Giải thích email tùy chọn để tư vấn yêu cầu; chat không tự đăng ký marketing |

Mỗi lượt hỏi tối đa một chi tiết chính còn thiếu, kèm lời mời email theo mục đích. Dùng số lượng mới nhất khách cung cấp; tránh lặp hỏi kích thước khi khách nói chưa biết. Quốc gia và tên công ty có thể được nhận từ câu trả lời trực tiếp sau câu hỏi tương ứng.

## Luồng email

- Có thể gửi ngay trong chat hoặc dùng Add email. Chấp nhận email cá nhân, không bắt buộc tên miền doanh nghiệp.
- Một địa chỉ hợp lệ, được cung cấp rõ là email liên hệ của khách, được lưu vào trường email hội thoại khi automation bật. Receipt cập nhật giao diện ngay; bot xác nhận và ngừng mời email lần nữa.
- Sau handoff, email vẫn có thể được lưu nhưng bot tiếp tục dừng.
- Địa chỉ sai định dạng được yêu cầu sửa. Không tự chọn giữa nhiều địa chỉ, nhận nhầm các cách nói bên thứ ba được hỗ trợ, hoặc ghi đè contact đã lưu; khách dùng Edit email để đổi.
- Không hỏi lại email đã có; từ chối rõ ràng thì tiếp tục thu thông tin dự án. Email tự khai báo không xác minh danh tính hoặc cấp lịch sử khách khác. Không tự gửi báo giá/tài liệu/marketing.
- Receipt retry không ghi trùng; phiên bản hội thoại ngăn sync cũ làm mất trạng thái email vừa lưu trên giao diện.

## Bằng chứng và giới hạn

311 checks backend WordPress/REST/database, 58 checks browser HTTP thật ở 1366/390/320px, 83 checks hồi quy đạt. PHP/JS syntax đạt; ảnh mobile đã xem. Chỉ dùng database `vpn_chat_test:3311`, HTTP 8091, sink chặn HTTP/email bên ngoài; dữ liệu/cấu hình test được dọn.

Danh sách 58 câu và intent/lời mời: [keyword-scenario-audit.json](../artifacts/vpn-live-chat/evidence/keyword-scenario-audit.json). Báo cáo backend/browser và ảnh `keyword-email-received-390.png` cùng thư mục evidence.

Đây vẫn là bộ từ khóa hữu hạn: không hiểu mọi lỗi chính tả, ngôn ngữ hoặc câu nhiều ý. Câu chưa khớp mời sales tiếp nhận và email để follow-up; không có cam kết mọi khách sẽ để lại email. Bot dừng sau tối đa 8 phản hồi hoặc khi sales tiếp nhận. Chưa đo tỷ lệ chuyển đổi hoặc tốc độ hosting.

Local bật sẵn. Chủ website pull/deploy cả `wp-content/plugins/vpn-live-chat/`, purge HTML/assets cache; giữ tùy chọn đã lưu. Không cần Product Sample Deploy/API key/schema mới. Production chưa được triển khai trong tác vụ này.
