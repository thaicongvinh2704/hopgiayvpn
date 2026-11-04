# Rà soát tốc độ toàn diện — 04/11/2026

**Kết luận:** cache HTML/TTFB là ưu tiên cao nhất; tiếp theo là cache ảnh và tải Google Tag. Đã bổ sung sửa local, giữ nguyên CSS/giao diện. Chưa push Git, deploy, purge hosting hoặc thay đổi Cloudflare.

## Số đo hiện tại trên hosting — trước khi triển khai bản local

| Mobile PageSpeed, một lần đo mỗi trang | Điểm | LCP | TBT | CLS |
|---|---:|---:|---:|---:|
| [Paper bags](https://pagespeed.web.dev/analysis/https-hopgiayvpn-com-custom-paper-bags-manufacturer/gzt7oh146o?form_factor=mobile) | 95 | 1,4 s | 130 ms | 0 |
| [Trang chủ](https://pagespeed.web.dev/analysis/https-hopgiayvpn-com/8jbg120gbe?form_factor=mobile) | 72 | 4,0 s | 360 ms | 0,129 |
| [Container guide](https://pagespeed.web.dev/analysis/https-hopgiayvpn-com-how-many-paper-bags-fit-in-a-container/d80lw0zwrs?form_factor=mobile) | 63 | 7,9 s | 380 ms | 0,079 |

Các lần chạy là lab Moto G Power/Slow 4G, không phải cam kết tốc độ khách hàng và không thể dùng chênh lệch so với hôm qua làm hiệu quả của bản chưa deploy. Dữ liệu người dùng thực mobile ở cả ba báo cáo đều fallback cấp origin trong 28 ngày: LCP 7,6 s, INP 239 ms, TTFB 5,4 s, CLS 0,01.

Đã đo HTTP 8 nhóm: home, paper, boxes, pizza, sản phẩm túi bánh mì có cửa sổ, blog, kraft và bakery. Mỗi URL hai GET, không đăng nhập. Phần lớn qua POP Singapore. Phân biệt rõ thời gian từ request đến headers với LCP và với thời gian PHP thuần:

| URL | MISS → HIT: thời gian đến header |
|---|---:|
| Blog container | 5,264 → 0,359 s |
| Sản phẩm bread/window | 4,010 → 0,579 s |
| Pizza | 3,404 → 1,136 s |
| Kraft category | 2,694 → 0,377 s |

Header `Server-Timing` tương ứng cho thấy `cfOrigin` giảm mạnh sau cache HIT, nhưng vẫn gồm giao tiếp Cloudflare–origin, không phải chỉ thời gian chạy PHP. Đây là hai mẫu mỗi URL, không phải thống kê ổn định. Lần kiểm tra bakery đầu dùng sai đường dẫn trong công cụ; đã sửa theo link thật `/products/custom-bakery-bread-paper-bags/` và xác minh HTTP 200. Không kết luận danh mục thật bị 404.

## Phát hiện và sửa local bổ sung

1. **WebP gốc bị `private`/Cloudflare BYPASS dù không đổi định dạng.** Hai ảnh quan trọng trên hosting trả cùng byte/MIME WebP với cả Accept AVIF/WebP và JPEG nhưng vẫn BYPASS. Plugin mới `vpn-native-webp-cache.php` loại WebP gốc khỏi đầu vào chuyển AVIF và cho cache 7 ngày với file WebP trong thư mục media năm/tháng. JPG/PNG tiếp tục chuyển định dạng; file output có hậu tố `.jpg.webp`, `.png.webp`, `.jpg.avif` giữ private/Vary để không bị cache sai định dạng. Không áp dụng cache mới cho HTML, API, PHP, customer inbox hoặc thư mục upload riêng ngoài year/month.
2. **Menu danh mục truy vấn lặp.** Batch lấy các slug, vẫn giữ grouping, thứ tự, term name/count và fallback. Kiểm tra độc lập: 76 → 2 truy vấn, tiết kiệm 74 truy vấn; menu serialized trùng trước–sau. Không suy diễn thành phần trăm tăng tốc toàn site.
3. **Logo loader là ảnh LCP trang chủ trong PSI.** Thêm preload ưu tiên cao ở `<head>` chỉ khi màn hình chờ đầu phiên được dùng. Giữ nguyên show delay 250 ms, minimum visibility 650 ms, hard limit, animation và thiết kế. Browser local xác nhận lần đầu có một preload, reload cùng tab không thêm preload. Chưa có bằng chứng CLS 0,129 đã hết; không tuyên bố đã sửa CLS chỉ từ preload.

Các sửa hôm trước tiếp tục có hiệu lực: cache immutable của featured public blog, 3 hero paper bags eager, workshop responsive và defer các handle an toàn. Không ép async/defer jQuery hoặc xóa tag đo lường.

## Kiểm tra thực tế

- 571 kiểm tra bộ performance cũ đạt; 407 kiểm tra HTTP trước–sau trên 7 loại trang đạt. CSS, visible content, CTA, form fields, script order và tracking IDs không đổi. Phần recommendation WooCommerce vốn random được kiểm tra shell, không so ngẫu nhiên như dữ liệu cố định.
- Native WebP: 12 kiểm tra cấu hình và 71 kiểm tra HTTP đạt. 12 ảnh WebP đại diện trả đúng byte/MIME với hai nhóm Accept; JPG/PNG trả đúng original/converted MIME và giữ private. Fixture bị chặn vẫn 403, không có public cache; endpoint nonce popup vẫn không cache công khai.
- Menu: tên/nhóm/thứ tự/ID/count giống nhau, slug không tồn tại vẫn trả null. File `menu-before.json` và `menu-after.json` lưu kết quả.
- Home preload kiểm tra bằng browser DOM; không gửi lead thử hoặc tạo Ads conversion giả. Chưa xác minh lại giao dịch form end-to-end trên staging, chưa có PSI/CrUX của bản sửa sau deploy.
- PHP local là 8.2.12, OPcache CLI tắt; không dùng thông tin này để kết luận phiên bản hoặc OPcache của hosting. Profiling local warm có khoảng 1,7–2,0 s render và 120–172 queries tùy giai đoạn/cache, không phải benchmark trước–sau đáng tin về tổng thời gian.

## Hướng tiếp theo theo mức tác động

**P0 — Deploy bản local và kiểm chứng cache ảnh.** MU tự áp dụng khi WordPress thực sự chạy sau deploy; nếu HTML LiteSpeed vẫn HIT, cần một lượt admin/dynamic request để kích hoạt rollout. Xóa cache HTML trong quy trình deploy để mã preload mới xuất hiện. Không commit ba `.htaccess` sinh trên XAMPP; MU dùng plugin Converter đang cài để sinh đường dẫn phù hợp hosting. Kiểm tra MIME, bytes, native image `CF-Cache-Status: MISS → HIT`, form/bootstrap và desktop/mobile rồi mới đánh giá hiệu quả.

**P0 — Cache HTML gần người dùng.** Cloudflare được kiểm tra qua API read-only: Free plan, Brotli/HTTP3 on, development mode off, không có Cache Rules/Page Rules hiện hữu. HTML trả DYNAMIC trong toàn bộ mẫu; LiteSpeed vẫn có page cache. Đã chuẩn bị `cloudflare-html-pilot-PROPOSED.json`, disabled: chỉ một blog công khai, không cookie/query/Authorization, GET/HEAD; tôn trọng Cache-Control của origin, không cache-everything cả site. Chưa validate biểu thức qua API và chưa bật. Cần xác minh MISS/HIT, cookies/Set-Cookie, nonce và cơ chế purge trước khi mở rộng ra money pages. Free plan tối thiểu 2 giờ nếu ép edge TTL; không đề xuất override TTL thấp không được hỗ trợ.

**P1 — Rà soát tích hợp Google Tag.** PSI paper tải khoảng 500,2 KiB Google Tag qua ba URL, trong đó Ads ID xuất hiện ở hai URL khác query. Lab ước lượng 248,9 KiB Google code chưa dùng trong lần tải đầu; không đồng nghĩa có thể xóa toàn bộ hoặc chứng minh event trùng. Cần xác định owner từng loader, hợp nhất theo hướng một Google tag với các destination phù hợp, kiểm tra page_view/generate_lead/Ads conversion trước–sau. Chưa xóa, delay theo tương tác hoặc đổi bất kỳ ID nào.

**P1 — Kiểm chứng CLS loader.** Node gây CLS được PSI chỉ ra là `.vpn-page-transition__content`. Preload giúp discovery nhưng chưa chứng minh giải quyết reflow. Nếu vẫn còn sau deploy, đo trace/layout-shift trên staging để ổn định kích thước; mọi thay đổi hiệu ứng/hình thức phải được chủ website duyệt riêng.

**P2 — CSS/font và ảnh còn lại.** Paper còn 8 stylesheet, jQuery/migrate head trên hosting, PSI ước tính render-blocking 1.080 ms. Không cộng thời gian từng request như mức tiết kiệm. Local không có cùng plugin đo lường như hosting, nên cần xác minh dependency/inline script trước khi gỡ jQuery. Tối ưu nguồn ảnh theo DPR, font theo icon thực dùng có fallback; không virtualize nội dung SEO hoặc gỡ CSS để lấy điểm bằng cách làm hỏng giao diện.

## Vận hành và giới hạn

Native WebP policy chỉ ghi lại ba file cấu hình của Converter một lần cho revision; không xóa ảnh hoặc thay nội dung DB. Backup cấu hình trong WP option không autoload, không nằm trong uploads công khai. `php tools/manage-native-webp-cache.php rollback` khôi phục và tắt policy; `enable` cho áp dụng lại ở request tiếp theo. Rollback tool có sẵn, chưa diễn tập phục hồi end-to-end trong lần rà soát này. Native WebP sẽ giữ WebP thay vì có thể đổi sang AVIF: hai ảnh quan trọng hosting được probe hiện đã trả WebP; các ảnh khác vẫn cần kiểm tra payload sau deploy nếu hosting có thêm AVIF.

Không đưa ra số giây tiết kiệm toàn site trước khi đo hosting sau deploy. CrUX/GSC theo cửa sổ 28 ngày, không chuyển tốt ngay khi local test đạt.

Nguồn kỹ thuật: [Google tối ưu LCP](https://web.dev/articles/optimize-lcp), [Google tối ưu TTFB](https://web.dev/articles/optimize-ttfb), [Google tag và destination](https://developers.google.com/tag-platform/gtagjs/configure), [WordPress script strategy](https://developer.wordpress.org/reference/functions/wp_enqueue_script/), [Cloudflare TTL](https://developers.cloudflare.com/cache/how-to/edge-browser-cache-ttl/), [Cloudflare dynamic/session caching](https://developers.cloudflare.com/cache/troubleshooting/dynamic-content-and-login-issues/).
