# Báo cáo bàn giao — 05/10/2026

## Phạm vi đã xác minh

Repo thực tế là thư mục con `hopgiayvpn-main` trong workspace, branch main. Không tìm thấy AGENTS.md trong cây repo và các thư mục cha đã kiểm tra. Giữ nguyên thay đổi có sẵn: một XML sitemap Rank Math bị xóa. Chỉ thêm plugin, tests, builder và artifacts; không sửa WordPress core, theme hoặc backend quote. Không deploy, không tạo quote qua form thật, không gửi email tới khách thật.

Project có WordPress 6.9.4, theme custom-box-theme, namespace custom-box/v1/search-suggestions; plugins cài sẵn gồm WooCommerce, Rank Math, WPForms Lite, Classic Editor, Akismet, TinyMCE Advanced, WebP converter và vpn-gmail-smtp. Đây là inventory filesystem; không suy ra tất cả đang active trên production. Theme có script defer/asset optimization, reCAPTCHA v3 riêng cho quote, privacy page, thanh quote/WhatsApp mobile và SEO tích hợp Rank Math. Không xác minh được CMP/CDN/page cache/SMTP credentials hay cấu hình active của hosting từ repo.

PHP XAMPP 8.2.12, MariaDB 10.4.32 port 3311 được dựng trong workspace với WordPress/database test độc lập. Database XAMPP hiện có không được dùng để cài chat/test: startup gặp lỗi quyền ghi InnoDB; đã chuyển sang runtime riêng. Test core wp-admin phải copy thay cho junction để bảo đảm các require dirname trỏ đúng config test. Site chạy HTTP loopback; cookie Secure ở production HTTPS chưa kiểm thử trên host thật. MU sink chặn mail/HTTP thật; Siteverify response/token replay và script browser đều giả lập. Theme browser chặn tracking/external requests.

## Kết quả suites

| Suite | Kết quả |
|---|---:|
| WordPress REST/DB integration | 86 assertions đạt |
| Multi-process concurrency | 7 assertions đạt |
| Missing secret / engine / retention / revoke | 7 assertions đạt |
| Chrome desktop/mobile widget và inbox | 22 assertions đạt |
| Quote helpers hồi quy, không submit form | 17 assertions đạt |
| Theme gốc + WooCommerce + Rank Math | 16 assertions đạt |
| ZIP mới / lifecycle qua WordPress plugin APIs | 7 assertions đạt |
| PHP lint và JS syntax | Đạt |

Tổng 162 assertions trong 7 suites. Concurrency thật: hai sales claim chỉ một winner và loser 409; 12 requests vào quota 5 chỉ 5 accepted; transaction chậm không phát cursor chưa commit; send thứ hai chờ row lock và cả hai đã ack đều có trong delta; retry song song cùng client ID chỉ một tin; hai worker outbox chỉ một mail vào sink. REST tests chứng minh guest A không đọc/ghi B, cùng email không cấp lịch sử, agent không truy cập chat của owner khác, internal note không leak, nonce/CSRF/challenge/origin/body/UTF-8/content type/quota bị enforcement phía server.

Browser: người chỉ đọc không tải full widget CSS/JS/bootstrap/poll; text XSS không chạy; draft còn khi challenge hết hạn/mất acknowledgment; retry send/start không nhân bản; reload khôi phục bằng cookie; Escape/focus/closed/hidden/resume; mobile viewport không tràn; guest không thấy note và nhận reply của sales; tab cùng browser khôi phục/nhận tin. iOS/Android thiết bị thật, quyền desktop notification/âm thanh thực, cookie consent, real minify/Rocket Loader/service worker/Cloudflare không được suite local mô phỏng đầy đủ.

Theme suite giữ menu/WhatsApp/quote form, SEO description/schema và search namespace trên desktop/mobile; launcher không đè thanh conversion mobile trong viewport 390x844. WPForms và tất cả plugin/bản dịch/cấu hình production không được restore vào test; theme phụ thuộc WooCommerce, Rank Math cần thiết lập offline skip registration cho test. Test quote cũ có expectation đã lỗi thời; dùng suite helpers mới đối chiếu implementation hiện tại, không sửa form/handler.

Activation/migrations được chạy lặp, deactivate/reactivate giữ lead. v1 chỉ additive; engine MyISAM bị health gate từ chối, khôi phục InnoDB đạt. Delete verified xóa transcript/outbox và revoke; retention 0 không xóa, retention được cấu hình xóa closed/spam quá hạn. Package integrity/checksums được builder kiểm tra. Chưa thực hiện backup restore đầy đủ trên staging; cần drill deletion ledger/backup và rollback production thực tế.

## Đo tải local (lượt cuối chạy riêng, không chạy browser đồng thời)

Windows PHP built-in **một worker**, theme Twenty Twenty-Five, plugin chat active; guest sync 3s, 3 sales sync 5s, thêm một website request mỗi 2s, mỗi tier khoảng 15s theo đồng hồ chạy (finish requests làm kéo dài tier). Dùng dữ liệu giả, không request production.

| Guests + sales | API samples | p95 | Max | HTTP errors |
|---|---:|---:|---:|---:|
| 5 + 3 | 34 | 1.939s | 2.139s | 0 |
| 10 + 3 | 59 | 2.623s | 3.116s | 0 |
| 20 + 3 | 89 | 5.289s | 5.302s | 0 |
| 30 + 3 | 100 | 7.372s | 7.375s | 0 |

**Mục tiêu pilot p95 <1 giây chưa đạt ở local này.** Worker đơn tạo hàng đợi; không dùng kết quả để khẳng định shared hosting chịu được 10/20/30 khách. Poll timers nối tiếp và backoff làm giảm số samples ở tier cao. Đây là bài đo ngắn để lộ backlog/error, chưa có soak test hay delivery p95 5 giây dưới tải. CPU, PHP worker limits, I/O, DB latency/locks và load cùng theme/caches tương đương host **chưa đo trên staging**. Phải đo lại ở staging với resources giống host; không bật pilot dựa riêng vào test local.

## Cấu hình và xác minh còn thiếu trước rollout

- Real Turnstile site key/secret, hostname allowlist và outbound Siteverify; không tái sử dụng dummy/mock test keys.
- HTTPS/cookie Secure thực; Cloudflare trusted proxy/origin firewall/cache bypass cả REST pretty/query paths, minify/defer và service worker exclusions.
- Sales users/2FA, manager, email đội sales/SMTP deliverability/quota, cron panel và monitoring owner.
- Lịch/ngày nghỉ/timezone, copy SLA, exact pilot paths, fallback contact thực đã duyệt, vị trí với cookie banner, templates sales phê duyệt.
- Chủ website chốt retention/privacy/cookie notice, backup vòng đời, deletion ledger ngoài DB và restore drill.
- Đo staging 5/10/20/30 + web traffic, latency/CPU/PHP workers/I/O/DB/errors/delivery lag; real mobile/keyboard/background/notification QA.

Plugin mới cài mặc định disabled. Website local hiện có `http://localhost/hopgiayvpn/` đã được kích hoạt để người dùng thử trực tiếp, với adapter demo riêng bên ngoài ZIP giả lập Turnstile và chặn email. Kiểm tra bổ sung trên local: 8 browser checks (gửi/nhận tin, đăng nhập sales với WooCommerce, nhận chat, reload lịch sử, JS errors, HTTP deny file mật khẩu) và 8 role-access checks. WooCommerce chỉ được phép bỏ redirect trên trang chat cho user có capability phù hợp; không cấp quyền posts/store/admin. Login mặc định của tài khoản chỉ có quyền chat đi vào inbox. Xem `docs/live-chat-local-preview.md` ở repo để thử.

Không gọi bản này production-ready khi các kiểm tra deployment/performance/recovery trên còn chưa hoàn tất. Có thể review code/ZIP ngay và tiếp tục staging mà không cần build assets trên host.
