# Lịch sử thay đổi Live Chat UI — 1.1.0 đến 1.2.0

> Tài liệu này giữ diễn biến theo từng phiên bản. Các mục 1.1.0 mô tả hiện trạng cũ (avatar TN, teaser ngoài khung, ảnh tham khảo chưa có). Xem [tài liệu chức năng và trạng thái hiện tại 1.2.0](live-chat-status.md) để biết phần đã hoàn thành và còn mở.

## Hiện trạng tại mốc 1.1.0

Đã sửa trực tiếp plugin `wp-content/plugins/vpn-live-chat` trên local `http://localhost/hopgiayvpn/`. Không deploy production. Hai ảnh tham khảo không có trong attachment của yêu cầu này (chỉ có `Pasted text.txt`), nên triển khai theo đặc tả chữ; không nhận đã xem hoặc đối chiếu pixel với ảnh.

## Palette và nguồn

Đọc stylesheet của `custom-box-theme` và đối chiếu computed styles của homepage local thật qua Chrome: `.hero .btn-primary` có background `rgb(42,106,146)`, chữ trắng, font Arial. Chat dùng cùng màu CTA này; không dùng màu đỏ mẫu hay màu xanh lá của bản chat trước.

| Chat token | Theme reference / fallback đã đối chiếu | Nguồn |
|---|---|---|
| primary | `--color-primary` / `--mobile-ux-primary` / `#2A6A92` | `assets/css/main.css:4947`, `responsive.css:4086`; CTA “Get Instant Quote” |
| primary-hover | `--color-primary-dark` / `--mobile-ux-primary-dark` / `#164C6D` | `main.css:4948`, `responsive.css:4087` |
| surface | `--color-surface` / `#FFFFFF` | `main.css:4953` |
| background | `--color-surface-soft` / `--mobile-ux-soft` / `#F7FAFC` | `main.css:4954`; `responsive.css:4091` (`#F4F8FA`) |
| text | `--color-ink` / `--mobile-ux-ink` / `#17313B` | `main.css:4950`, `responsive.css:4088` |
| muted | `--color-muted` / `--mobile-ux-muted` / `#5D6B73` | `main.css:4951`, `responsive.css:4089` |
| border | `--color-line` / `--mobile-ux-line` / `#DCE8EE` | `main.css:4952`, `responsive.css:4090` |
| unread | `--color-primary-dark` / `#164C6D`, viền trắng | Palette theme; số trắng dễ đọc |
| agent bubble | `--color-primary-soft` / `#E9F3F8` | `main.css:4949` |

Các token `--vpn-chat-*` chỉ áp dụng trên `.vpn-chat-launch`, `.vpn-chat-greeting`, `.vpn-chat-panel`; không khai báo selector global hay sửa theme. Font Arial/Helvetica/sans-serif theo body và CTA hiện tại. Nút 58px tròn; panel rộng tối đa 380px, desktop cao tối đa 580px, bo 18px; shadow dùng cùng RGB nền mực của theme, độ đậm phù hợp overlay. CTA theme gốc bo 6px, header contact bo pill, mobile controls bo 12px; chat dùng bo 9–14px bên trong để hài hòa và tuân theo đặc tả panel.

Contrast tính theo sRGB: chữ trắng/primary **5.87:1**, chữ mực/agent bubble **12.11:1**, muted/background **5.25:1**, số trắng/unread **9.18:1**. Trạng thái có nhãn chữ Online/Away/Offline, không dựa riêng vào chấm màu.

## Đổi tên/avatar trong quản trị

- **VPN Live Chat → Profile nhân viên**: sales sửa profile của chính mình; manager chọn và sửa profile của nhân viên chat khác. Tên và ảnh lấy từ server theo user đã đăng nhập, không nhận profile/actor do guest hoặc client tự truyền.
- Sales có thể tải ảnh ngay trong **Profile nhân viên** bằng input riêng: JPG/PNG/WebP tối đa 2 MB, 12 megapixel, kiểm tra nội dung ảnh; nonce, capability và quyền sửa đúng user được kiểm tra trước upload. Không cấp thêm quyền upload/quản trị toàn website. Admin còn có nút Media Library **Chọn / tải ảnh** trong profile/settings. ID = 0 và không chọn ảnh mới dùng chữ viết tắt. Thumbnail nhẹ, hiển thị hình tròn `object-fit: cover`; ảnh lỗi trở lại avatar chữ.
- **VPN Live Chat → Cấu hình**: tên hỗ trợ mặc định **Tho Nguyen**, avatar mặc định **TN**, checkbox lời chào và nội dung mặc định “Hi! How can we help with your packaging project?”. Bật “Dùng danh tính hỗ trợ chung” chỉ khi công ty chủ động chọn chính sách đó; mặc định tắt.
- Tài khoản demo local `vpn-chat-demo` đã được cấu hình profile Tho Nguyen/TN. Không tìm thấy ảnh được chỉ định cho người này nên không tạo chân dung giả.
- Header trước khi nhận chat dùng profile hỗ trợ mặc định và trạng thái đội sales. Sau khi nhận chat, header dùng profile/presence của người phụ trách. Ngoài ca, heartbeat quá hạn hoặc Offline sẽ không hiển thị Online.
- Tin mới lưu `sender_profile` cùng `actor_id` trong transaction. Đổi profile/transfer không thay danh tính tin đã gửi. Tin cũ chưa có snapshot được migration giữ profile hiện tại của đúng tài khoản gửi, vì tên tại thời điểm gửi trước đây không được lưu.

## Hành vi và bảo mật

Lời chào chủ động hiện tối đa một lần mỗi phiên tab (sessionStorage), không lặp khi chuyển trang và không tạo tin khách/phiên chat. Có nút đóng riêng. Chỉ sau khi khách đã có hội thoại, flag không chứa PII cho phép khôi phục sync trên trang mới.

Thu nhỏ giữ draft trong bộ nhớ, lịch sử và unread; chat hiện có sync chậm 15 giây khi tab visible. Tab hidden dừng sync và hoạt động lại khi visible. Badge đếm tin agent thật, không tính ghi chú nội bộ và không reset do polling. `guest_read_seq` phía server chỉ tăng sau API `guest/read` có cookie, ownership, CSRF, origin checks và quota; chỉ acknowledge khi panel mở/tab visible và đang ở cuối transcript. Dữ liệu đã đọc còn giữ khi chuyển trang; draft không lưu tên/email/nội dung vào storage.

Header/composer không cuộn; phần giữa cuộn riêng và nhóm tin bắt đầu ở trên. Panel tăng chiều cao theo nội dung hội thoại từ khoảng 360px đến giới hạn viewport/580px desktop, tránh vùng trắng lớn khi ít tin. Tin khách bên phải, tin sales bên trái với profile riêng. “Saved” dựa trên commit thật; “Read” dựa trên thao tác đánh dấu đã đọc của sales, không giả lập receipt. Đang gửi/lỗi hiển thị trạng thái thật, giữ draft và cùng client ID để retry không nhân đôi. Enter gửi, Shift+Enter xuống dòng; IME composition/229 không gửi. Avatar chữ là fallback thật, lời chào ghi rõ “Automatic welcome”.

Giữ form tên/email/nội dung và Turnstile, outbox, ownership/notes, idempotency. Module hiện có **không hỗ trợ attachment, emoji picker hay typing realtime**; không thêm nút giả cho các tính năng đó. Emoji gõ trực tiếp vẫn hiển thị. Không tự xin quyền notification, không tải thư viện icon mới.

Vị trí lấy visualViewport, safe-area và khoảng đáy cấu hình; tránh mobile conversion CTA, các cookie-banner/WhatsApp floating selectors phổ biến đang hiện. Cần kiểm tra lại selector nếu thay plugin cookie/WhatsApp. Panel xử lý viewport nhỏ và resize khi bàn phím; Enter/IME bằng Chrome automation, chưa thay thế kiểm tra bộ gõ/bàn phím iOS/Android thật.

## Files và kiểm thử

Frontend: `assets/launcher.js`, `launcher.css`, `widget.js`, `chat.css`, `admin.js`, `profiles.js`.
Backend: `includes/profiles.php`, `settings.php`, `schema.php` (v2), `service.php`, `store.php`, `rest.php`, `ui.php`, plugin entry (v1.1.0). Schema thêm snapshot profile và cursor đã đọc, giữ các dữ liệu cũ.

Tests: `tests/live-chat/profiles-unread.php`, `ui-profile.mjs`; cập nhật assertions migration và minimize trong `integration.php`, `browser.mjs`. Báo cáo JSON/ảnh ở `tests/live-chat/results/` và bản bàn giao `artifacts/vpn-live-chat/evidence/`.

Nghiệm thu ngày 2026-10-05: **231 checks pass** — backend 86; concurrency 7; config/retention 7; profile/unread 21; browser retry/session 22; UI/profile 30; avatar upload 9; theme 16; quote regression 17; role access local 8; install local 8. PHP lint và JS syntax check đều qua. Quote regression không submit form thật. Upload dùng ảnh logo QA trong DB test, profile được trả về avatar chữ. Runtime test được dừng sau nghiệm thu; Apache/MySQL website local vẫn chạy.

`artifacts/vpn-live-chat/ui-update-from-1.0.0.diff` đối chiếu plugin hiện tại với ZIP 1.0.0 đã bàn giao trước đó (không phải so với ảnh tham khảo hoặc baseline Git). `ui-acceptance.json` ghi số kiểm tra và giới hạn. Ảnh local thật: `evidence/local-chat-minimized.png`, `local-chat-panel.png`, `local-chat-start.png`; ảnh desktop/mobile với nhiều sales trong DB cô lập có tiền tố `ui-`.

Giới hạn: chưa so trực tiếp với hai ảnh thiếu; chưa thử chân dung thật do chưa có ảnh được chỉ định; chưa kiểm tra bàn phím/notification/background throttling trên thiết bị thật. Kiểm thử dùng local/DB cô lập, email và HTTP ngoài bị chặn; không chứng minh production load/SMTP/Turnstile thật đã sẵn sàng.

## Cập nhật ảnh Thọ Nguyễn và lời chào khi mở — 1.1.1

Đã đọc ảnh chân dung và ảnh mẫu khung chat do người dùng gửi ngày 2026-10-05. Avatar thật được cài vào Media Library local, attachment ID 8869, thumbnail 150 × 150 (42 KB); gán cho profile hỗ trợ và tài khoản demo `vpn-chat-demo`. Các tin cũ vẫn giữ snapshot profile tại thời điểm gửi. Có thể thay ảnh ở Profile nhân viên/Cấu hình như trên.

Yêu cầu mới thay hành vi lời chào thu nhỏ: mặc định tắt teaser ngoài khung; cấu hình local cũng đã tắt. Sau khi mở khung và bootstrap thành công, lời chào xuất hiện sau 850 ms, có avatar/tên và nhãn “Automatic welcome”. Đóng khung hoặc ẩn tab hủy timer; mở lại không nhân đôi lời chào. Lời chào không tạo tin sales, unread, typing giả hay trạng thái Online giả. Hội thoại đang có khôi phục tin thật thay cho lời chào đầu chat.

Ô nhập tin đầu tiên và nút gửi nằm cố định ở composer dưới cùng, giữ form tên/email và Turnstile. Khung theo bố cục mẫu, giữ palette xanh của website theo yêu cầu trước. Khách chỉ gửi văn bản: không có nút tệp/like/emoji picker; REST guest/start, guest/send và agent/send từ chối trường attachment/file/upload bằng HTTP 400; multipart bị chặn HTTP 415. Upload avatar trong quản trị vẫn dành cho profile nhân viên có quyền, không phải upload tệp qua chat.

Kiểm tra bổ sung: **22 checks giao diện/avatar/arrival/text-only trên desktop 1366px và mobile 390px**, cùng **8 checks luồng cài local** (khách gửi, sales nhận/trả lời, reload, không lỗi JS, bảo vệ credentials) đều pass. Không chạy lại toàn bộ 231 checks của 1.1.0; báo cáo bổ sung `evidence/tho-avatar-report.json`. Ảnh hiện tại: `evidence/tho-avatar-1366.png`, `tho-avatar-390.png`, `local-chat-panel.png`. Chưa kiểm tra thiết bị iOS/Android thật hoặc production. Chỉ cập nhật local, không tạo ZIP mới.

## Inbox đơn giản như ứng dụng chat — 1.2.0

Màn hình **VPN Live Chat** đã đổi thành danh sách khách bên trái, lịch sử hội thoại bên phải và ô trả lời cố định dưới cùng. Danh sách có avatar chữ, tên, giờ, tin gần nhất và dấu chưa đọc; không hiển thị ghi chú nội bộ trong preview. Mặc định mục Tin nhắn gồm khách mới và các hội thoại đang hoạt động trong phạm vi quyền của tài khoản, không chỉ nhóm Chưa nhận. Tìm kiếm tự cập nhật khi nhập.

Luồng thường: **bấm khách → nhập tin → Enter hoặc nút gửi**. Lần trả lời đầu tự nhận hội thoại bằng API claim hiện có, giữ khóa/version/ownership khi nhiều sales cùng thao tác. Không cần bấm Nhận chat thủ công. Các hội thoại đã có chủ vẫn giữ quyền hiện tại. Tin retry giữ cùng client_message_id. Tin đang soạn lưu trong bộ nhớ riêng từng hội thoại, không lưu PII vào storage. Shift+Enter xuống dòng; IME không gửi nhầm.

Nút **Chi tiết** mở form thông tin khách, chuyển người phụ trách, trạng thái và các thao tác nâng cao; mặc định đóng. Câu trả lời mẫu và ghi chú riêng vẫn dùng được dưới composer. Công cụ quản trị và thông báo hệ thống thu gọn bên ngoài vùng chat. Không xóa cảnh báo hệ thống. Trạng thái Online/Offline vẫn do nhân viên chọn và heartbeat/lịch trực xác nhận.

Mobile hiển thị danh sách hoặc hội thoại, có nút quay lại; đồng bộ nền không tự mở lại khung. Chỉ tự ghi nhận đã đọc khi transcript thật đang hiển thị và ở cuối, với đúng quyền. Đang xem danh sách mobile không đánh dấu tin đến là đã đọc.

Kiểm tra 1.2.0: 25 checks UI/workflow desktop/mobile (bao gồm Enter, IME, draft riêng, tự nhận, ghi chú, receipt theo visibility); 7 checks inbox preview/phạm vi quyền; 8 checks local role access; 8 checks luồng khách → sales → reload. Báo cáo UI: `artifacts/vpn-live-chat/evidence/inbox-simple-report.json`; ảnh desktop/mobile: `inbox-simple-desktop.png`, `inbox-simple-mobile.png`. PHP lint và JS syntax đều qua. Chưa kiểm thử thiết bị mobile thật hay production; không chạy lại toàn bộ bộ test phiên bản trước.

Files chính: `includes/ui.php`, `includes/rest.php`, `assets/admin.js`, `assets/admin.css`, entry plugin (version/cache 1.2.0). Tests: `tests/live-chat/inbox-simple.mjs`, `inbox-access.php`, cập nhật `local-preview.mjs` theo luồng không cần nhận chat thủ công. Chỉ sửa local.


## Màu admin và thông tin liên hệ — 1.2.1

Tin chưa đọc dùng nền #FFF5E0, vạch #D77708, badge #9B4700/chữ trắng và tên/preview đậm. Selection xanh #DCEEF8; nếu chưa đọc vẫn giữ nền/vạch amber. Thông tin email dùng #E1EFF7 với chữ #164C6D, avatar xanh; thiếu email dùng màu trung tính. Không thay đổi sorting/quyền/luồng claim. Nhãn Có email không hàm ý email xác minh. 14 kiểm tra browser với dữ liệu giả đạt; ảnh/báo cáo inbox-colors trong evidence, tài liệu hiện trạng được cập nhật.


## Dễ đọc và gợi mở trò chuyện — 1.3.0

Tăng font/spacing cho cả admin và khách; xem bảng trong live-chat-status.md. Admin có sidebar 380/340px và welcome card/CTA; widget tối đa 400px, nội dung 16px. Các gợi ý điền draft, không gửi tự động; form và ownership không đổi. Nền/header/bubble dùng các sắc xanh brand; unread amber giữ. Assets không thêm thư viện/font/ảnh AI; reduced-motion giữ nguyên. Đã kiểm tra 25 readability + 22 avatar/text-only + 8 luồng local; ảnh chat-readability trong evidence.


## Email tùy chọn — 1.3.2

Khách không cần email để bắt đầu; ô email vẫn nằm phía trên, ghi rõ optional. Nếu nhập, validation vẫn giữ. Tên và nội dung bắt buộc; inbox phân biệt Có email/Chưa có email. 12 checks đạt, đã dọn 5 hội thoại QA; xem live-chat-status.md và optional-email-report.json.


## 1.5.0 — Hồ sơ khách và lịch sử

Khách ẩn danh có mã riêng. Widget đặt nút Continue a previous conversation và menu lịch sử dưới header, xác minh email qua mã 6 số tùy chọn. Admin gom một dòng mỗi khách trong phạm vi bộ lọc/quyền, badge Đã xác minh và menu chọn lịch sử. Phiên ngắn hạn tách cookie nhận diện 180 ngày; không tự ghép theo email chưa xác minh. Chi tiết và phần chưa nghiệm thu production tại live-chat-status.md.


## 1.5.1 — Tin tự động nhắc email

Khi không có nhân viên available theo lịch/heartbeat thật, hiện một bubble Automatic message nhắc để lại email và nút Leave your email. Header Online cố định vẫn theo cấu hình người dùng. Ẩn nhắc sau khi lưu email, có người trực hoặc cuộc chat đã đóng; không tạo tin database/unread giả.
