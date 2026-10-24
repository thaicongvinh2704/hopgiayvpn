# Sportswear product — bản local đã triển khai 24/10/2026

Xem: http://localhost/hopgiayvpn/product/custom-sportswear-packaging-box/

Trạng thái: đã cập nhật **sản phẩm hiện có ID 872 trên local**, vẫn publish. Đã chuẩn bị bản Git riêng cho sản phẩm; chưa thay đổi hosting; chưa gửi yêu cầu index.

## Đã thay đổi

- SEO title: **Custom Sportswear Packaging Boxes | MOQ 1,000 | VPN** (51 ký tự).
- Meta description: **Custom sportswear packaging boxes for activewear sets, jerseys and gym apparel. Made in Vietnam with your logo and size. Bulk orders from 1,000 boxes.** (150 ký tự).
- Rank Math focus keywords: custom sportswear packaging boxes; activewear packaging boxes; sportswear set packaging. Các trường social title/description cũng đồng bộ.
- Thay đoạn giới thiệu và viết lại nội dung chính từ 1.524 còn 659 từ. Bỏ ghi chú SEO, các đoạn lặp Sportswear Box và nội dung lệch sang những mặt hàng khác. Dùng tiếng Anh tự nhiên, hướng đến khách đặt số lượng lớn.
- Giải thích rõ ảnh hiện tại là hộp nắp rời có áo polo; tùy chọn carton gấp/mailer được phân biệt, không coi chúng là cùng một kết cấu.
- Làm rõ MOQ 1.000, đo quần áo sau gấp, thiết kế/in hoàn thiện, làm mẫu, QC và báo giá. Thời gian mẫu 7–15 ngày, sản xuất 7–45 ngày tùy đơn; tách thời gian vận chuyển.
- Thêm 4 FAQ về MOQ, bộ activewear, duyệt mẫu và dữ liệu báo giá.
- Thêm sản phẩm vào danh mục Fashion and Sportswear Packaging, giữ Custom Paper Boxes. Danh mục hiện có tạo đường dẫn tìm sản phẩm; không tạo danh mục mới.
- Chọn lại 4 sản phẩm liên quan: T-shirt box, corrugated apparel mailer, apparel magnetic gift box, fold-flat apparel mailer with inside print. Bộ lọc chỉ áp dụng cho slug sportswear này.
- Thêm hai ảnh nhà xưởng sẵn có (in offset, hoàn thiện hộp) qua gallery dùng trên website. Ảnh lazy load, tổng khoảng 189 KB. Hai ảnh sản phẩm cũ giữ nguyên vì chưa có ảnh thực phù hợp để thay; không tạo ảnh giả làm bằng chứng sản xuất.
- Giữ các liên kết có ích tới danh mục thời trang, bài hướng dẫn chuẩn bị bao bì, hộp mailer và trang năng lực sản xuất. Không sửa thêm nội dung blog/danh mục khác trong lượt này.

## Giữ nguyên

URL, H1/tên sản phẩm, ngày đăng, trạng thái publish, featured/gallery media ID, thông số sản phẩm, menu, màu sắc, CSS hiện có và hệ thống form. Gallery xưởng được bổ sung bằng thành phần có sẵn; không thiết kế lại giao diện.

“YourCUSTOM…” trong bản trích text cũ là hai vế ngăn bởi thẻ `<br>`, không phải bằng chứng thiếu khoảng trắng trên giao diện. Template tiêu đề báo giá được giữ nguyên.

## Kiểm tra thực tế

- PHP lint: module release, helper local, gallery và cả hai bản importer đều hợp lệ.
- 19 kiểm tra cơ sở dữ liệu đạt: đúng ID, dữ liệu SEO/nội dung/excerpt, giữ URL/H1/ngày/trạng thái/ảnh/thông số, lưu bản trước sửa, giữ danh mục cũ và thêm danh mục mới, đúng 4 related products, không tác động bộ lọc của sản phẩm khác, không tạo trùng và chạy lại không sửa thêm.
- 17 kiểm tra HTTP/HTML đạt: HTTP 200, đúng title/meta, 1 H1, 4 FAQ, 4 related products, 2 ảnh xưởng có alt/CSS, 1 Product schema với description mới, không còn ghi chú SEO hay placeholder; toàn bộ 11 URL ảnh/liên kết được kiểm tra trả 200.
- Local cố ý noindex/nofollow do `hopgiayvpn-local-only.php`; Rank Math không xuất canonical trong chế độ này. Không tắt biện pháp cách ly local và không coi đây là lỗi canonical của hosting. Product schema vẫn trỏ đúng URL local.
- Chưa xác minh lại bố cục bằng ảnh chụp trình duyệt desktop/mobile trong lượt này. Các lớp bố cục và CSS responsive đang dùng được giữ; ảnh xưởng dùng gallery responsive có sẵn.
- Không có thay đổi/kiểm chứng mới về index, click, thứ hạng hay chuyển đổi trên hosting.

Bằng chứng: `outputs/sportswear-ctr-20261024/local-before.json`, `local-database-verification.json`, `local-http-verification.json`, `local-after.html`.

## Đồng bộ sau này

Bản Git gồm MU-plugin/release/content, hai ảnh xưởng và CSS gallery được đóng gói ngay trong MU-plugin, manifest danh mục, hai importer và báo cáo này. Không cần đưa các thay đổi theme khác đang làm dở vào bản release.

Trên hosting chưa có module gallery trước đây, MU-plugin chèn gallery qua hook của thành phần footer CTA sẵn có. Nếu theme đã hiển thị gallery cho sản phẩm thì bỏ qua phần chèn để tránh trùng. Không sửa functions.php hoặc template single-product trong bản Git này.

Sau pull/deploy, quản trị viên mở một trang WordPress admin để module tự áp dụng đúng một lần. Module tìm bằng slug, không dùng ID local trên hosting; lưu bản trước sửa vào option, chỉ cập nhật nếu nội dung/excerpt/title/description khớp phiên bản đã rà soát. Nếu khác, báo cảnh báo để rà soát, không tự ghi đè. Sau đó xóa cache trang/CDN và kiểm tra URL public.

Không cần chạy toàn bộ Product Sample Deploy cho bản sửa này. Hai importer được bổ sung điều kiện giữ nội dung đã duyệt khi chủ website chạy lại batch thời trang trong tương lai.

Bản trước sửa trong DB: option `vpn_sportswear_release_before_872` (ID hosting có thể khác). Muốn hoàn tác phải phục hồi content, excerpt, metadata và danh mục từ bản này; gỡ plugin đơn thuần không tự khôi phục DB.
