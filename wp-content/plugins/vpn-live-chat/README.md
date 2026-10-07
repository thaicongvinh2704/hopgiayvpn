# VPN Live Chat 1.8.5

Plugin độc lập cho WordPress, guest UI tiếng Anh, inbox sales tiếng Việt. PHP/WordPress REST + MySQL/MariaDB InnoDB + JavaScript thuần. ZIP chứa assets sẵn; production không chạy npm, Node, Redis hay WebSocket. Đây là bản MVP đã kiểm thử local; chưa phê duyệt production/pilot trên shared hosting.

## 1.8.5 — Hiển thị chat trên toàn website, 07/10/2026

Khi bật widget, chat hiển thị trên tất cả trang phía khách, gồm trang chủ, bài viết, sản phẩm, danh mục, tìm kiếm và trang liên hệ. Bỏ giới hạn Paths pilot và trường cấu hình đường dẫn. Các path lưu từ phiên bản cũ không còn giới hạn hiển thị; không cần sửa database hoặc khai báo từng URL. Nút bật/tắt widget vẫn có hiệu lực. Sau cập nhật cần purge cache HTML của toàn website để các trang cũ tải widget.

## 1.8.4 — Giảm độ trễ inbox admin, 07/10/2026

Chọn khách/tìm kiếm/phân trang ưu tiên request mới, bỏ snapshot poll cũ. Chọn khách tải riêng detail có kiểm tra quyền; list JOIN email đã xác minh, giảm 27 xuống 2 truy vấn chat cho 25 khách trong benchmark. Giữ nhóm khách/bộ lọc/unread/phạm vi quyền. Reply đầu nhận chat và lưu tin trong một transaction; hiển thị receipt đã lưu ngay, không chờ sync, giữ thứ tự và loại trùng. Không dựng lại list/header/câu trả lời mẫu khi dữ liệu không đổi. Poll hội thoại đang mở khoảng 2–2,4 giây, tab ẩn dừng poll.

209 kiểm tra backend/UI/browser HTTP thật đạt. Benchmark UI với độ trễ giả lập: chọn khách 1.401 → 96 ms, hiện trả lời 1.384 → 243 ms; chưa phải số đo hosting. Chưa deploy production. Cập nhật toàn bộ plugin 1.8.4 và purge cache; bao gồm nút xóa từ 1.8.3.

## 1.8.3 — Xóa hội thoại ngay trong inbox, 07/10/2026

Quản trị thấy nút **Xóa hội thoại** cạnh **Chi tiết**. Bấm và xác nhận để xóa vĩnh viễn hội thoại đang chọn, gồm tin nhắn/ghi chú/outbox. Không cần ô xác minh trong menu ẩn. API yêu cầu `confirmed_delete: true`, vẫn hỗ trợ `verified_request: true` từ client cũ; giữ quyền manager, nonce và origin. Các hội thoại khác của khách được giữ; cơ chế erase thu hồi phiên/thiết bị hiện có của khách.

Xóa thành công dọn transcript/nháp và làm mới danh sách. Xóa thất bại giữ nháp và cho thử lại. Chặn xóa lặp, gửi và chuyển khách trong lúc xóa; phản hồi sync cũ không khôi phục hội thoại vừa xóa. 74 kiểm tra backend cô lập và 37 kiểm tra UI desktop/mobile đạt; PHP/JS lint đạt. Chưa triển khai production.

## 1.8.2 — Tin đã lưu phải hiện ngay phía khách, 06/10/2026

Tái hiện được lỗi: guest/start hoặc guest/send thành công nhưng UI chỉ hiện tin sau guest/sync. Sync thất bại khiến admin thấy tin mà khách không thấy. Poll đang chạy cũng có thể trả snapshot trước lần gửi. Kiểm tra hồi quy đã thất bại trước sửa và đạt sau sửa; chưa xác định riêng lỗi mạng/cache nào xảy ra trên hosting của ảnh người dùng.

Phản hồi gửi/retry trả nội dung, seq và thời gian của tin đã lưu. Widget hiển thị receipt ngay, không chờ sync và không khóa gửi trong lúc tải lịch sử. Poll sau gửi phải thực hiện một lượt mới sau poll đang chạy. Dùng POST/CSRF để tránh cache GET ở proxy, vẫn hỗ trợ GET cho client cũ. Không tăng cursor chỉ vì receipt, nên không bỏ sót tin sales hoặc ghi chú có seq xen giữa. Dedup theo seq và chèn theo thứ tự server khi receipt đến trước lịch sử.

61 kiểm tra backend, 34 browser HTTP thật và 117 UI/mock đạt (212 kiểm tra chạy trong lần sửa này); 35 launcher giữ kết quả 1.8.1, tổng hiện hành 247. Có tình huống sync 503 sau gửi, gửi tiếp khi sync lỗi, khôi phục/reload, snapshot poll cũ đang chạy, tin sales xen giữa, thứ tự và không nhân đôi. Cú pháp PHP/JS đạt. Database test đã dọn, không gửi mẫu hoặc xóa tin thật trên production. Deploy toàn bộ 1.8.2 rồi purge cache HTML/assets/CDN. Production chưa nghiệm thu bản mới.

## 1.8.1 — Mở chat tức thì, 06/10/2026

Tải widget.js/chat.css cùng trang, trước launcher, và dựng sẵn khung ẩn. Nhấn biểu tượng mở khung đồng bộ ngay trong click handler; không khóa launcher trong lúc bootstrap/sync. Kết nối và lịch sử tải nền. Mở lại giữ transcript đã có và composer, không xóa lịch sử để chờ tải. Khách gõ khi đang kết nối vẫn giữ nháp; nếu bootstrap tìm được hội thoại cũ, chuyển nháp sang composer trả lời. Gửi chỉ bật khi đã có phiên/CSRF hợp lệ.

233 kiểm tra đạt: backend 55, browser HTTP thật 34, UI/mock 109, launcher 35. Kiểm tra bổ sung trì hoãn bootstrap/sync 1,6 giây xác nhận mở ngay, đóng/mở nhanh, giữ nháp và xóa thông báo connecting khi sẵn sàng. Không tạo phiên/chat chỉ vì tải sẵn giao diện. Cú pháp 12 PHP/4 JS đạt. Deploy 1.8.1 và purge cache HTML/assets/CDN; production chưa nghiệm thu bản mới. Mạng vẫn quyết định tốc độ nhận lịch sử/gửi tin, nhưng không cản việc mở khung.

## 1.8.0 — Chống spam đơn giản theo yêu cầu, 06/10/2026

Thay yêu cầu Turnstile bằng giới hạn trên máy chủ: 2 tin/giây; mặc định 10 tin/30 giây, 60 tin/5 phút, 3 hội thoại mới/10 phút theo mã khách. Nhiều tab/phiên dùng chung quota; có giới hạn IP bổ sung. Khi quá giới hạn, báo thời gian chờ và giữ nháp. Không cần site key/secret, không có CAPTCHA. Giữ cookie/CSRF/origin, quyền hội thoại và chỉ nhận văn bản.

Nâng cấp bật nhận chat mới một lần nếu widget đã bật, giữ lựa chọn tắt của admin sau đó. Deploy plugin 1.8.0 và purge cache; vẫn cần HTTPS/database khỏe. 220 kiểm tra đạt, gồm gửi/nhận HTTP thật trên WordPress test và giới hạn đồng thời. Chưa kiểm tra gửi thật bản mới trên production; SMTP/delivery email và hiệu năng hosting vẫn chưa nghiệm thu. Báo cáo hiện hành: docs/live-chat-final-check.md. Các đoạn Turnstile trong nhật ký phiên bản cũ chỉ mô tả lịch sử, không còn là yêu cầu của 1.8.0.

## Cài đặt và cấu hình

1. Sao lưu database và plugin đang dùng. Deploy thư mục `wp-content/plugins/vpn-live-chat/` từ Git, rồi kích hoạt plugin; có thể dùng ZIP đúng phiên bản nếu cần. **Widget và nhận chat mới mặc định tắt**, lịch trực mặc định trống. Activation tạo bảng prefix thực tế, role và cron; không sửa core/theme/quote.
2. Yêu cầu WordPress >=6.2, PHP >=8.0, HTTPS, database user có CREATE/ALTER và tất cả bảng chat dùng InnoDB. HTTPS bắt buộc cho khách; chỉ local loopback được dùng HTTP khi `WP_ENVIRONMENT_TYPE=local`.
3. Không cần Turnstile. Máy chủ giới hạn 2 tin/giây, mặc định 10 tin/30 giây, 60 tin/5 phút và 3 hội thoại mới/10 phút theo mã khách; có quota IP bổ sung. Quá giới hạn trả HTTP 429/Retry-After, giữ nháp để gửi lại. Có thể chỉnh các giới hạn dài hơn trong Cấu hình.
4. Nhập fallback thực tế. Project hiện có `/contact/#quote`, email footer `sales.vpn@hopgiayvpn.com`, `paperbox@hopgiayvpn.com`, WhatsApp trong theme. Chủ website xác nhận kênh mong muốn rồi lưu URL đầy đủ hoặc `mailto:`; plugin không tự chọn hay gửi email tới khách.
5. Nhập email đội sales cho SLA; cấu hình/kiểm tra SMTP đang có trên staging với mail sink. Plugin gọi `wp_mail`, tương thích adapter `vpn-gmail-smtp` trong repo. `wp_mail=true` chỉ là transport chấp nhận, không chứng minh delivery vào inbox. Health báo adapter/configuration; không tự gửi mail thử cho khách.
6. Tạo WP users với role **VPN Chat Sales** (chỉ read + `vpn_chat_agent`) hoặc **VPN Chat Manager** (thêm `vpn_chat_manage`). Administrator được hai capability. Agent thấy hàng chờ chưa nhận và chat của mình; manager thấy tất cả. Không gán sales Administrator để sử dụng inbox. Bật 2FA qua giải pháp WP đang được công ty sử dụng/đánh giá tương thích; plugin này không tự triển khai 2FA.
7. Chọn timezone IANA, lịch JSON theo ngày ISO 1–7, ví dụ `{"1":[["08:00","17:00"]],"2":[["08:00","17:00"]]}`. Ngày nghỉ mỗi dòng `YYYY-MM-DD`. Ca qua đêm chia thành hai ngày. Lịch trống = ngoài giờ; tự viết copy tiếng Anh với thời gian phản hồi mà sales chấp thuận. Không có cam kết 24/7 mặc định.
8. Widget đã bật hiển thị trên tất cả trang phía khách; không cần nhập paths. Bottom offset mặc định 100px để tránh thanh CTA mobile; kiểm tra thêm cookie banner/nút nổi thực tế.
9. Loại REST namespace khỏi cache/CDN/service worker và kiểm tra response. Hoàn tất test staging/load trước khi bật hai flags; rollout từng nhóm trang. HTTPS thiếu hoặc bảng không InnoDB làm `accepting=false` và vẫn có fallback nếu đã cấu hình.

## Cron, SMTP và cache

- Worker event `vpn_chat_worker` mỗi phút, tối đa 10 jobs hoặc khoảng 15 giây mỗi lần. SMTP timeout 10 giây cho mail của worker. Khi host cho phép, panel scheduled task chạy `wp cron event run vpn_chat_worker --path=/duong/dan/wordpress` mỗi phút, với user có quyền trên site. Kiểm tra WP-CLI/PHP path thực tế trước khi dùng.
- Nếu không có scheduler, WP-Cron là fallback phụ thuộc traffic. **Không tắt WP-Cron toàn site** để cài plugin. Health ghi `cron_disabled`, `last_runner`, pending, failed và oldest_due. Trong repo local `DISABLE_WP_CRON=true`, test gọi worker trực tiếp.
- Outbox dedup theo hội thoại/lần quay lại hàng chờ, row lock + lease 120 giây chống runner chồng, backoff tới một giờ, tối đa 8 attempts. Jobs failed cần quản lý xử lý transport và requeue bằng công cụ quản trị database được kiểm soát (chỉ các rows đã xác định, đặt state=pending/due_at hiện tại/lease=NULL/lease_until=NULL). Không resend cả queue một cách mù quáng. Email SMTP có semantics at-least-once khi process chết ngay sau gửi trước lúc đánh dấu done; dedup giảm flood nhưng không bảo đảm exactly-once của SMTP.
- CDN bypass cả `/wp-json/vpn-chat/v1/*` và biến thể `?rest_route=/vpn-chat/v1/...`; query có thể URL-encode dấu `/`. Không cache POST, nonce/bootstrap, login/admin. Plugin gửi `Cache-Control: private, no-store` cả response lỗi và bỏ CORS reflection mặc định của WP cho namespace này. Không tắt cache toàn site.
- Exclude handles `vpn-chat-launcher`, `vpn-chat-admin`, globals `VPNChatLauncher`/`VPNChatAdmin`, widget.js và Turnstile khỏi combine/delay/Rocket Loader nếu công cụ tối ưu làm sai thứ tự. Plugin tải đầy đủ widget.js/Turnstile sau thao tác mở, không fetch/bootstrap/poll khi chỉ đọc trang. Launcher config chỉ chứa public URLs; cookie/CSRF không phát trong cached HTML.
- Không có service worker riêng. Nếu site có worker, bổ sung network-only/bypass namespace chat và admin; kiểm tra caches thật. Không tự fetch link người khách gửi.

## Phiên, dữ liệu và API

- Guest secret 32 bytes CSPRNG trong cookie host-only HttpOnly, Secure trên HTTPS, SameSite Strict. DB chỉ lưu SHA-256; token không có trong URL/localStorage/log. CSRF HMAC riêng từ secret + WP nonce salt, header `X-VPN-CSRF` trên guest mutations. Idle 24h, absolute 7 ngày mặc định, có revoke. Đổi email không cấp lịch sử cũ; email luôn tự khai báo.
- JSON mutations yêu cầu Origin đúng origin của `home_url`, Fetch Metadata same-origin/none nếu có, content type application/json, body <=16 KiB. CLI/integrations phải gửi Origin; cookies + CSRF/nonce vẫn bắt buộc. REST agent dùng login cookie + `X-WP-Nonce` + capability. Không có API guest theo email hoặc public download.
- Mỗi hội thoại khóa dòng để cấp `seq` trong transaction; first lead + message + outbox + audit cùng commit. Ack sau commit; retry `client_message_id` (16–64 chữ/số/_/-) có unique `(conversation_id,sender_scope,client_message_id)`, payload khác trả 409. Tin và note có cùng sequence nhưng guest SELECT loại note; guest serializer không trả lead/email/owner/metadata/needs. Cursors chụp seq đã commit và paginate 50; không dùng global auto increment để đồng bộ.
- Claims/update dùng row lock + version; transfer được audit. Bảng `messages.id` là khóa nội bộ, không làm cursor. Agent read acknowledgment phải bấm **Đánh dấu đã đọc đến đây**; guest UI chỉ báo Saved, không tự báo đã đọc.
- Rate limit dùng counter rows atomic, fixed UTC windows: 10 send/30s, 60/5min, 3 new conversations/10min theo phiên; ngưỡng IP rộng hơn 100 create/10min và 1000 sends/5min. Bootstrap 120/IP/10min, auth/CSRF failures 100/IP/5min, sync 60/session/minute, agent 180/user/minute. Window boundary có thể cho burst hai windows; tuning/WAF trên staging nếu cần sliding window. 429 có Retry-After 30s. IP được HMAC, chỉ dùng `REMOTE_ADDR`; **không tin X-Forwarded-For hoặc CF-Connecting-IP tùy ý**.
- Với Cloudflare, host phải allowlist proxy thật ở web server, normalize REMOTE_ADDR bằng module trusted proxy và chặn đường truy cập origin ngoài proxy trước khi dùng header IP. Tự xác minh ranges/cấu hình host; plugin không tự cấu hình firewall/proxy.
- Manager có thể yêu cầu re-challenge, block phiên có lý do/thời hạn, review/unban. Lệnh đánh spam và block riêng; spam đóng gửi tin nhưng không tự cấm một IP dùng chung. Không cấm Gmail/VPN/country/Hi/Price/link doanh nghiệp. Không có hệ thống chấm điểm doanh nghiệp hay autofill honeypot.

API namespace `/wp-json/vpn-chat/v1`:

| Routes | Quyền |
|---|---|
| POST bootstrap | Same-origin, quota; phát phiên và CSRF sau khi mở |
| GET guest/sync; POST guest/start/send/revoke | Cookie phiên hợp lệ, ownership; mutation thêm CSRF; start Turnstile |
| POST agent/sync/send/update | WP user + REST nonce + capability, ownership kiểm tra backend |
| POST agent/canned | Manager CRUD nội dung mẫu |
| GET manager/health; POST manager/block/privacy | Manager; xóa cần confirmed_delete=true (hoặc verified_request=true từ client cũ) |

## Vận hành và giới hạn

Xem [hướng dẫn sales](docs/SALES.md), [dữ liệu/retention](docs/DATA.md), [monitoring/rollback](docs/OPERATIONS.md) và [báo cáo kiểm thử](docs/TEST-REPORT.md). Upload tắt; không AI, CRM đầy đủ, native app, email verification xuyên thiết bị, nhận email hai chiều hay push đảm bảo khi đóng browser. Nhu cầu/nhãn/source link dùng cho follow-up thủ công, không sửa backend quote. Không phát GA4/dataLayer events trong MVP; nếu thêm sau này, phải xử lý consent trước và không gửi PII/IDs/body.

Dependencies runtime: WordPress APIs, PHP JSON/CSPRNG/mysqli/UTF-8 helpers, InnoDB, outbound HTTPS tới Siteverify khi nhận mới, browser Fetch/AbortController. Không có dependency Composer/npm cho plugin. Developer tests dùng Chrome + Playwright và MariaDB local; `tests/live-chat` có setup riêng với credentials giả, mail sink và Siteverify mock, **không đóng gói tests/config/mocks trong ZIP**. Khi đóng widget hoặc tab hidden, polling dừng; mở/focus sync lại. BroadcastChannel nhắc tab cùng browser sync; không có leader election, nhiều tab vẫn dùng chung quota.


## Deploy bản 1.5.1: điều kiện sử dụng thực tế

Mã nguồn từ Git không bao gồm option database, Media Library, secret Turnstile hoặc SMTP của local. Deploy mới không tự bật widget. Nếu production đã có plugin và cấu hình hợp lệ, cập nhật code giữ cấu hình hiện tại; migration schema chạy khi plugin được tải, không cần xóa bảng. Xóa cache assets/CDN sau cập nhật.

Lần đầu vào VPN Live Chat → Cấu hình:

- Bật widget và nhận chat mới; widget hiển thị toàn website, không cần thay path local bằng path production.
- Cấu hình Turnstile site key cho domain thật, secret ngoài Git trong wp-config/env, HTTPS và kiểm tra Health/InnoDB.
- Chọn Tho Nguyen, tải avatar vào Media Library production và chọn đúng ID; ID 8869 của local không được mặc định dùng cho production. Bật danh tính chung, Online cố định và lời mời chat nếu muốn giống cấu hình local đã duyệt.
- Đặt thông báo ngoài giờ: “Thanks for reaching out! Please leave your email address so we can get back to you as soon as possible.” Giá trị mặc định mới chỉ áp dụng khi chưa lưu giá trị cũ; nếu production đã có copy khác, thay tại Cấu hình.
- Đặt lịch trực/timezone và nhân viên Online trong inbox khi trực. Lời nhắc dựa lịch/heartbeat thật, kể cả khi header luôn Online. Tin tự động ẩn sau khi khách lưu email hoặc nhân viên available.
- Kiểm tra SMTP thật cho email xác minh và nhắc SLA; scheduler cho outbox/cleanup; bypass cache cho REST chat. Chỉ deploy plugin production, không chạy installer demo hoặc sao chép runtime/database local.

Khách để lại email là lưu thông tin liên hệ. Phản hồi sales trong inbox được gửi tới widget qua polling, **chưa tự gửi phản hồi chat tới email khách**. Việc gửi email liên hệ do sales thực hiện qua kênh email hiện có. SMTP production, tải nhiều khách và hosting thật vẫn cần nghiệm thu trước khi coi là hoàn tất vận hành.

## Nâng cấp lời mời chat — 1.6.0

Deploy toàn bộ thư mục plugin, gồm ảnh `assets/tho-nguyen.png`. Bản nâng cấp bật `greeting_enabled` một lần và ghi marker `vpn_chat_invitation_version`; lựa chọn tắt sau đó của admin được giữ. Purge cache HTML/assets/CDN để trang dùng launcher 1.6.0 và public config mới. Không bật lại plugin, thay secret hay sao chép database local chỉ để cập nhật lời mời.

Card xuất hiện sau 2,5 giây, có CTA và avatar hỗ trợ. Nút **Chat with Sales** có vòng sáng nhẹ ba lần rồi dừng; reduced motion tắt animation. Đóng/mở chat sẽ ẩn lời mời trong tab. Khi tên hỗ trợ là Tho Nguyen và không có avatar Media Library hợp lệ, ảnh đi kèm plugin được dùng; ảnh tùy chỉnh vẫn được ưu tiên.
