# Tối ưu hiệu năng local — 03/11/2026

Đã triển khai local, chưa commit/push hoặc sửa hosting/Cloudflare. Giữ nguyên CSS, bố cục, menu, màu, nội dung, liên kết và form.

## Thay đổi

- Bản cache định dạng tường minh cho ảnh đại diện của 73 blog đã public và 3 ảnh catalog ở hero trang paper bags. 548 đường dẫn ảnh gốc/thumbnail được map sang các file có tên SHA-256, giữ nguyên từng byte. Chỉ nội dung ảnh đã công khai; không thu thập ảnh nháp, ảnh khách gửi, thông tin chat hay dữ liệu form.
- Thư mục tĩnh riêng ngoài uploads/WebPC, `Cache-Control: public, max-age=31536000, immutable` cho ảnh có tên hash. Không bỏ cơ chế `private`/`Vary: Accept` của bộ chuyển định dạng uploads. Thử HTTP với Accept AVIF/WebP và JPEG trả cùng byte/MIME đúng. Chưa khẳng định Cloudflare HIT trước khi deploy và kiểm tra hosting.
- Nếu ảnh gốc trên hosting khác bản local, thiếu file hoặc không có trong manifest: dùng lại URL gốc. Mỗi request chỉ kiểm tra hash một lần cho mỗi file được dùng; không sinh ảnh/copy hàng loạt trong frontend.
- Ba ảnh đầu trang paper bags tải eager, chỉ ảnh đầu ưu tiên cao; danh sách sản phẩm bên dưới vẫn lazy. Không đổi ảnh hoặc crop.
- Ba ảnh nhà máy thêm nguồn responsive 480px khi phù hợp màn hình; vẫn giữ bản 1280px cho màn hình/DPR lớn và liên kết xem ảnh gốc. Ba file 480px tổng 148.232 byte, giảm khoảng 48% so với 286.630 byte bản gốc. Không tạo nguồn resized lớn hơn file gốc; giữ nguyên CSS aspect ratio/object-fit/caption.
- 6 handle JS dùng chiến lược defer chuẩn WordPress khi được enqueue: main, hai script landing paper, widget/launcher chat và popup. WordPress tự xét phụ thuộc/inline script; không ép defer jQuery/WooCommerce, không delay theo tương tác, không đổi timer popup hoặc mã Google Ads/GA4.

## Kiểm tra

- `tests/frontend-performance-20261103.php`: **571/571** — hash ảnh, URL không được phép/query, nguồn responsive/visual attributes, draft/unrelated context, eager hero/lazy card và phụ thuộc script.
- `tools/verify-frontend-performance-20261103.py`: **407/407** trước lần bổ sung guard hash runtime; guard mới sau đó đã chạy lại bộ PHP. HTTP trước–sau trên home, paper, boxes, pizza, product, blog và kraft: CSS/visible content/CTA/form/tracking IDs/script order không đổi. Loại trừ riêng nội dung recommendation vốn random của WooCommerce; giữ kiểm tra section vẫn tồn tại. Kiểm tra toàn bộ phần sản phẩm chính/gallery/form.
- DOM desktop đối chiếu trước–sau: copy, kích thước/vị trí ảnh workshop, CSS và CTA trùng nhau. Mobile: ảnh workshop đủ, không tràn ngang ở viewport thử, menu có 2 email, mở/đóng chat được và có ô email bắt buộc. Sau bổ sung cache hero: 3/3 ảnh đều loaded + eager + dùng URL cache.
- Không gửi lead thử vào inbox thật/SMTP hoặc tạo sự kiện conversion giả. Chưa nghiệm thu lại giao dịch gửi form end-to-end trên staging, chưa đo INP/LCP thực tế sau deploy.
- Lint PHP và diff check ba file theme đạt; không thay đổi CSS.

## Phạm vi và triển khai tiếp

- Dung lượng asset bổ sung khoảng 17,3 MB: các bản cache nguyên byte và 3 ảnh workshop responsive. Có fallback nếu tắt MU plugin. Không xóa ảnh Media Library.
- Các ảnh mới chưa có trong manifest tiếp tục dùng URL cũ; cần chạy lại exporter/builder có kiểm soát khi mở rộng snapshot. Không được hiểu là toàn bộ uploads của website đã chuyển sang public cache.
- Vấn đề TTFB thực tế cao, payload Google tag và CLS loader trong audit gốc chưa được giải quyết hoàn toàn bởi bản local này. Không gỡ tag hoặc đổi loader vì chưa đủ bằng chứng và ràng buộc giữ giao diện. Sau duyệt/deploy phải đo lại HTML cache HIT/MISS, Cloudflare image HIT, PSI và trải nghiệm khách quốc tế; tiếp tục tối ưu hosting/trackers nếu còn chậm.
- GSC dùng dữ liệu người dùng thực theo cửa sổ 28 ngày; không thể xác nhận 77 URL hết kém từ local hoặc hứa điểm xanh ngay sau deploy.

Mã chính: `wp-content/mu-plugins/vpn-frontend-performance.php`. Asset: `wp-content/themes/custom-box-theme/assets/images/performance-20261103/`. Theme chỉ sửa 3 dòng render/loading ảnh tại landing/helper workshop; không sửa stylesheet.
