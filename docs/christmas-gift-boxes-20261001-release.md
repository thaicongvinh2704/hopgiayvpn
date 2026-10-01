# Christmas gift boxes — pull-deploy release v2 — 01/10/2026

5 sản phẩm đã tối ưu và cập nhật trên WordPress local, ID 8814–8818. Release: `2026-10-01-christmas-v2`. Chưa triển khai lên domain live.

| Sản phẩm | Focus keyword thống nhất với tên | Mô tả dài sau nhập |
| --- | --- | --- |
| Hộp nến burgundy | burgundy christmas candle window box | 1828 từ |
| Hộp ngôi nhà có quai | christmas house window carry gift box | 1818 từ |
| Hộp cửa sổ cây thông | christmas tree window handle gift box | 1835 từ |
| Hộp xanh buộc nơ | green ribbon christmas rigid gift box | 1814 từ |
| Hộp trụ đỏ | red round christmas gift cylinder | 1809 từ |

## Nội dung hoàn thiện

- Mỗi trang giữ phần kỹ thuật riêng theo kết cấu, đoạn trả lời trực tiếp, checklist duyệt mẫu, FAQ và CTA báo giá. Rút gọn phần chung lặp lại.
- Bổ sung VPN, Ho Chi Minh City, Vietnam, nhu cầu custom/bulk/wholesale và custom logo trong ngữ cảnh phù hợp.
- SEO title và meta description riêng; keyword khớp tên sản phẩm; canonical tạo bằng permalink tại môi trường đang chạy. Danh mục nhận keyword rộng; sản phẩm giữ ý định kết cấu cụ thể.
- 30 ảnh thiết kế gốc, mỗi trang có 1 ảnh đại diện, 5 gallery và 3 ảnh thiết kế trong mô tả.
- Bổ sung 2 ảnh công ty dùng chung: công nhân tại thiết bị in và khu vực sản xuất. Nguồn là ảnh được Git theo dõi và dùng trên About; ảnh WebP được đóng gói nguyên byte vào bundle tháng 10 và đăng ký Media Library.
- Mỗi trang có 2 ảnh công ty trong nội dung, alt/caption theo cảnh và liên kết About. Phân biệt ảnh công ty với ảnh thiết kế; không gọi đây là đợt sản xuất 5 mẫu Noel.
- Không thêm chứng nhận, công suất, khách hàng, người rà soát, giá bán hoặc kết quả kiểm nghiệm chưa có hồ sơ hỗ trợ.

## Cơ chế pull deploy

Theme chứa bộ nhập, bộ xác minh, helper, JSON sản phẩm, 5 file nội dung và 32 ảnh nguồn. Không cần lấy database local hoặc upload ảnh thủ công.

`inc/christmas-gift-boxes-20261001-product-sync.php` đăng ký qua post-sync-loader, dùng `admin_init`, chỉ chạy với `manage_options`, bỏ qua frontend/AJAX/REST/cron. Batch ở đầu registry; cả 5 slug được nhận diện khi mở sửa sản phẩm.

Trình tự: kiểm tra nguồn → sao chép ảnh → đăng ký Media Library/metadata → nhập sản phẩm và liên kết bằng slug → xác minh dữ liệu đã lưu → ghi option hoàn tất. Có lock, thông báo lỗi và retry. Chỉ ghi hoàn tất sau khi cả 5 sản phẩm đạt xác minh. Metadata, hash nội dung và ảnh được kiểm tra lại khi health audit hoặc mở sửa sản phẩm.

Sản phẩm mới chuẩn bị ở draft rồi publish; sản phẩm private giữ private. Slug ngoài batch không bị ghi đè. Nhập lại giữ nguyên sản phẩm và attachment.

## Kiểm tra đã đạt

- PHP lint cho importer, verifier, helper, sync, loader và cấu hình admin.
- Database: 5 sản phẩm, 21 thông số/trang, 30 ảnh sản phẩm và 2 ảnh công ty dùng chung. Keyword/title/meta/canonical đúng payload; hash nội dung và ảnh nguồn khớp.
- Mô phỏng Admin sau pull: release được chọn và hoàn tất; chạy lại không tạo trùng. Cả 5 slug được nhận diện ở màn hình sửa.
- Thử thay đổi nội dung local: validator phát hiện và sync sửa lại đúng nguồn.
- Frontend: 5 trang HTTP 200, một H1 và một Product schema/trang, meta description hiện diện; 25 ảnh trong nội dung tải thành công; mobile 390px không tràn ngang.
- 9 đường dẫn nội dung đến sản phẩm, danh mục, About, hướng dẫn và contact trả 200. Đã xem khu vực ảnh công nhân/xưởng trên mobile.
- Mô phỏng visibility public riêng từng request: 5 trang xuất canonical đúng và index/follow; không thay đổi option local. Đây không thay cho kiểm tra domain hosting.

## Triển khai

1. Đưa commit release lên remote hosting rồi `git pull` tại WordPress root.
2. Mở một trang WP Admin bằng tài khoản quản trị. Sync chạy trên request Admin tiếp theo; chờ thông báo xác minh 5 sản phẩm, 30 ảnh thiết kế và 2 ảnh công ty.
3. Nếu báo lỗi, sửa theo thông báo rồi mở lại Admin. Không đánh dấu hoàn tất thủ công. Nút dự phòng: **Tools → Product Sample Deploy → Sync 5 October Christmas Gift Boxes**.
4. Kiểm tra live: 5 URL, ảnh, About, canonical đúng domain, robots/indexability và sitemap. Local giữ `blog_public=0`; hosting cần Search Engine Visibility cho phép index.

CLI tùy chọn: `php tools/import-christmas-gift-boxes-20261001.php` rồi `php tools/verify-christmas-gift-boxes-20261001.php`. Test mutation/repair chỉ cho chạy local: `php tools/test-christmas-gift-boxes-pull-sync.php`.

## Giới hạn bằng chứng

Sẵn sàng triển khai về nội dung và cơ chế đồng bộ, không phải chứng nhận E-E-A-T hoặc bảo đảm thứ hạng. Ảnh tái sử dụng từ tài sản công ty trên website; chưa xác minh độc lập quyền ảnh, thời gian chụp hoặc hồ sơ sản xuất từng mẫu. Case study và mẫu Noel thật có thể bổ sung khi có tư liệu.

[Google AI features](https://developers.google.com/search/docs/appearance/ai-features) và [nội dung đáng tin cậy](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) là nguồn đối chiếu; không có schema riêng bảo đảm xuất hiện trong AI.
