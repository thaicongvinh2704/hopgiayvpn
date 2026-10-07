# Sửa độ trễ dùng chung trong WordPress admin

Phạm vi: toàn bộ `/wp-admin/` sử dụng `custom-box-theme`, không chỉ màn hình Live Chat. Đã sửa trong mã nguồn local; chưa triển khai hoặc đo trang admin của hosting hopgiayvpn.com.

## Các điểm đã sửa

- Sáu bộ đồng bộ danh mục trước đây kiểm tra/sửa lại trong `admin_init` ở mỗi lần mở trang. Chúng được giới hạn một lần mỗi 15 phút, kể cả khi thiếu sản phẩm hoặc ảnh khiến kiểm tra chưa hoàn tất. Mã nguồn thay đổi sẽ bắt đầu khoảng kiểm tra mới.
- Bỏ câu `UPDATE` quét toàn bộ bảng bài viết khỏi đường tải chung của admin. Thay bằng bước sửa quan hệ bài viết tự trỏ làm cha chạy một lần, dọn cache cho bản ghi đã sửa, và ngăn quan hệ đó khi lưu qua WordPress.
- Đồng bộ gói nội dung chưa hoàn tất có khoảng chờ 5 phút. Thao tác triển khai chủ động và mở bài viết liên quan vẫn có thể chạy ngay. Gói đã hoàn tất vẫn theo lịch kiểm tra hiện có.
- Importer Custom Vial Boxes chỉ được nạp khi cần. Công cụ triển khai mẫu nạp importer ngay trong thao tác được yêu cầu; các hook cho trang sản phẩm vẫn được giữ.
- Tách hoàn tất cấu hình sitemap/IndexNow khỏi việc gửi mạng. Liệt kê URL và gửi IndexNow chạy qua WP-Cron, không nằm trong thao tác mở trang admin. Gửi lỗi có lịch thử lại sau 15 phút, không làm cấu hình bị đánh dấu thiếu rồi sửa lại mỗi lần bấm.

Các tác vụ đồng bộ nội dung vẫn có thể chạy ở lần kiểm tra đến hạn hoặc khi triển khai chủ động. Bản sửa giảm công việc lặp lại; không bảo đảm mọi nguyên nhân chậm của hosting đã được giải quyết.

## Kiểm chứng

- 35 kiểm tra trên WordPress, WooCommerce và Rank Math thật với database thử riêng: quyền quản trị, khoảng chờ, sửa quan hệ cha, gói nội dung, IndexNow thành công/lỗi và chạy nền khi không có người dùng đăng nhập.
- Kiểm tra trình duyệt qua HTTP: Bảng tin, Bài viết, Thư viện, Sản phẩm, Plugin, Cài đặt; tạo/sửa/đọc lại bản nháp; trang chủ và lỗi JavaScript. Bản nháp thử được xóa sau kiểm tra.
- 17 kiểm tra hồi quy cho bảo vệ biểu mẫu báo giá; không gửi biểu mẫu hoặc email thật. Kiểm tra cú pháp tất cả PHP thay đổi.

Báo cáo nằm ở `artifacts/admin-performance/`. Profile chỉ đo các callback bảo trì tùy chỉnh, với danh mục chưa có đủ dữ liệu thử. Trước sửa, sáu callback vẫn thực hiện 98 truy vấn khi gọi lần thứ hai trong cùng request. Sau sửa, một request PHP mới trong thời gian chờ thực hiện 6 truy vấn đọc trạng thái, và không chạy phần sửa danh mục. Cache giữa các lần gọi khác nhau; không dùng số này để suy ra phần trăm cải thiện tốc độ toàn bộ admin.

Các số đo trình duyệt dùng máy local, database nhỏ, PHP development server, gói nội dung trung tâm được đánh dấu hoàn tất trong database thử và kết nối bên ngoài bị chặn. TTFB và thời gian DOMContentLoaded không phải số đo hosting. IndexNow trong kiểm tra riêng được mô phỏng độ trễ 300 ms; không gửi URL lên công cụ tìm kiếm.

## Áp dụng trên hosting

Gói `artifacts/admin-performance/admin-performance-patch-2026-10-07.zip` chứa 12 file theme thay đổi và ghi chú này. Đây là bản vá đường dẫn website, không phải ZIP plugin để tải qua màn hình cài plugin của WordPress.

1. Sao lưu các file theme đang chạy và database trước khi áp dụng. Đối chiếu với phiên bản mã nguồn hiện tại nếu hosting có chỉnh sửa riêng.
2. Dùng quy trình triển khai của dự án hoặc SFTP/File Manager, chép các file `inc/` vào đúng thư mục `wp-content/themes/custom-box-theme/inc/`, rồi cập nhật `functions.php` cuối cùng. Không ghi đè các file ngoài danh sách manifest.
3. Đảm bảo WP-Cron thực sự chạy. Nếu hosting đặt `DISABLE_WP_CRON=true`, giữ hoặc cấu hình tác vụ cron của hosting gọi WordPress cron; nếu không, IndexNow sẽ nằm trong hàng đợi. Môi trường local thử cố ý tắt cron tự động và gọi worker trong bài kiểm tra.
4. Mở một trang admin để hoàn tất sửa một lần, sau đó đo chuyển trang và lưu nội dung. Kiểm tra lịch `custom_box_search_indexing_submit` và option `custom_box_search_indexing_sync_status` khi cần xác minh việc gửi nền.

Nếu hosting vẫn chậm sau áp dụng, cần số đo request thực tế và log PHP/MySQL để phân biệt thời gian PHP, truy vấn database và kết nối bên ngoài. Bản vá hiện tại chưa xác minh CPU/RAM, PHP workers, database hoặc các plugin khác trên hosting.

Hoàn tác bằng cách khôi phục các file theme đã sao lưu. Giữ file helper dư cũng không ảnh hưởng khi `functions.php` cũ không nạp nó. Không cần xóa dữ liệu nội dung hay tắt plugin để hoàn tác mã nguồn.
