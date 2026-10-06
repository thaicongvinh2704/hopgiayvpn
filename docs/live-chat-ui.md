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

## 1.6.0 — Lời mời nổi bật, 06/10/2026

Nút Chat with Sales có nhãn dễ đọc. Card avatar/Tho Nguyen/Sale Manager mời khách hỏi báo giá hoặc chọn bao bì, hiện sau 2,5 giây và giữ qua đổi trang cho đến khi khách đóng hoặc mở chat. Animation vào nhẹ, vòng sáng và nudge chỉ ba lần; hỗ trợ reduced motion. Nâng cấp bật lời mời một lần để sửa cấu hình greeting_enabled=false trên production, không ghi đè lựa chọn admin về sau. Avatar mặc định nhỏ đi kèm plugin giúp deploy không phụ thuộc Media Library local. 25 checks browser, 3 checks migration và kiểm tra trang local thật desktop/mobile đạt; không tạo chat mẫu.

## 1.6.1 — Mẫu 01 Friendly Bubble được chọn, 06/10/2026

Thay card lớn bằng bong bóng trắng gọn (260px desktop / 246px mobile), tiêu đề “Hi! Need help with packaging?”, dòng “Chat with Tho Nguyen, Sale Manager” và CTA teal “Let’s chat →”. Launcher dùng avatar thật 80px, viền/chấm xanh và icon chat nhỏ ở góc; mở khung vẫn đổi sang nút đóng 58px. Không thay đổi luồng chat, dữ liệu khách hay lời nhắc email.

Giữ delay 2,5 giây, dismissal trong tab, animation 3 lần rồi dừng và reduced motion. Tự né thanh liên hệ dưới cùng; ẩn lời mời khi chiều cao khả dụng quá nhỏ. Không tạo hội thoại/tin nhắn mẫu.

Đã đạt 35 checks browser với assets thật và API giả lập ở 1366px, 390px, 320px; kiểm tra trang WordPress local thật ở desktop/mobile và ảnh friendly-bubble-local trong evidence; JS syntax và PHP lint đạt. Báo cáo invitation-v161-report.json. Bản 1.6.1 đổi version assets để tránh dùng CSS/JS cũ; migration bật lời mời của 1.6.0 giữ nguyên, không ghi đè lựa chọn tắt của quản trị viên. Production cần deploy commit mới và purge cache; chưa nghiệm thu phiên bản này trên production.

## 1.7.0 — Mẫu 03 Gentle Follow-up được chọn, 06/10/2026

Hiện trạng giao diện này thay thế form/lời nhắc của các phiên bản 1.3–1.5 bên trên:

- Mở chat: header Tho Nguyen / Sale Manager / chấm xanh, lời chào ngắn và ô nhắn tin. Bỏ form tên/email và các nút gợi ý để tránh nhiều tầng thông tin.
- Sau khi có hội thoại thật, khách chưa có email sẽ thấy một card Follow-up: “Want our quote by email?”, ô email, Save và “Optional — keep chatting here”. Không cần điền để gửi tin. Không hiển thị thêm bubble nhắc offline ở đầu khung; lời mời mới hiện cả khi có/không có người trực.
- Khách có thể đóng card, tiếp tục chat, rồi mở lại qua Add email (optional) luôn nằm dưới composer. Có email đổi thành Edit email; cho phép sửa/xóa. Save trước tin đầu chỉ giữ email trong bộ nhớ trang và gửi cùng tin đầu, không tạo hội thoại chỉ vì điền email.
- Lưu email thành công thu gọn card; giữ nháp và báo lỗi khi lưu thất bại. Polling không nhân đôi hoặc bật lại card đã đóng trong lượt mở trang. Tải lại trang nếu chưa có email có thể mời lại; nếu có email đã lưu thì giữ ẩn.
- Mã khách, chọn lịch sử và khôi phục qua mã email chuyển vào menu ba chấm. Escape đóng menu trước khi đóng chat. Nhận diện khách, backend, xác minh email và inbox không đổi.
- Thu gọn header, composer luôn nằm dưới transcript; card email cuộn cùng hội thoại. Sửa selector chấm xanh của launcher để không ảnh hưởng trạng thái trong header. Thông báo lỗi/mất kết nối vẫn hiển thị; bỏ các dòng trạng thái thành công lặp lại.

Kiểm tra: 86 checks với assets thật/API giả lập, gồm desktop 1366px, mobile 390px và 320px, gửi ẩn danh, khôi phục/xác minh qua menu, nhập email trước/sau tin đầu, validation, lưu thất bại/nháp, reload, sửa/xóa email, hội thoại đóng/new chat, không có upload và bố cục tránh thanh liên hệ. Báo cáo gentle-followup-report.json; ảnh open/sent tại evidence. Kiểm tra trang WordPress local thật desktop/mobile không tạo tin mẫu, JS syntax/PHP lint đạt.

Bộ 30 câu trả lời mời khách để lại email được lưu riêng tại live-chat-sales-email-playbook.md; chưa cài bot trả lời theo bộ này, chưa tự gửi báo giá hay phản hồi chat qua email khách. Các bài kiểm tra giao diện cũ là bằng chứng cho phiên bản cũ; dùng gentle-followup.mjs cho luồng UI 1.7.0.

Deploy plugin 1.7.0 và purge cache HTML/assets/CDN để áp dụng trên production. Không đổi tùy chọn accepting, Turnstile hoặc lịch trực; chưa nghiệm thu phiên bản 1.7.0 trên production.

## 1.7.1 — Chẩn đoán lỗi gửi trên production, 06/10/2026

Kiểm tra trình duyệt chỉ mở chat, không gửi tin mẫu: bootstrap trên hopgiayvpn.com trả HTTP 200 nhưng accepting=false, site_key rỗng; nút gửi bị khóa và không hiện challenge. Online vẫn hiển thị do cấu hình danh tính, không có nghĩa máy chủ đã sẵn sàng nhận tin. Chưa xác minh secret hoặc toàn bộ cấu hình hosting; thiếu site key đã đủ khiến ready() trả false.

Đã sửa Enter/requestSubmit không được gửi khi không nhận chat mới; tin đầu phải chờ token chống spam khi có site key. Giữ nháp, báo cụ thể unavailable, xác minh, phiên, quota, email và phản hồi không phải JSON. Console chỉ ghi mã lỗi/status, không ghi nội dung/email/cookie/token. Backend không bỏ kiểm tra chống spam.

93 checks UI/mock HTTP đạt (gồm 7 kiểm tra bổ sung về khóa Enter, giữ nháp và lỗi xác minh/máy chủ), JS syntax/PHP lint đạt. Bản 1.7.1 cần deploy/purge cache. Việc nhận tin production chưa hoàn tất: chủ hosting phải cấu hình Turnstile site key trong VPN Live Chat → Cấu hình, secret qua VPN_CHAT_TURNSTILE_SECRET trong wp-config.php ngoài Git, bật nhận chat mới và kiểm tra Health/HTTPS. Chưa thay cấu hình hay gửi tin thử trên production.

## 1.7.2 — Kiểm tra cuối trước cập nhật, 06/10/2026

207 kiểm tra đạt: backend 43, browser qua HTTP/WordPress/database test thật 30, UI/mobile/lỗi mạng 99, launcher 35; lint 12 PHP/4 JS đạt. Sửa mất nội dung mới khi retry tin đầu đã lưu nhưng mất phản hồi; chuyển nháp sang composer tiếp theo. Khóa Enter khi hội thoại đã đóng. Không đổi backend hoặc bỏ chống spam.

Báo cáo đầy đủ: live-chat-final-check.md và evidence/final-audit-report.json. Database test riêng port 3311, HTTP 8091, đã dọn mẫu; không tạo tin trên local chính/production. Production vẫn accepting=false/site_key rỗng, nên chưa nghiệm thu gửi tin production; cần cấu hình Turnstile trên hosting trước. Bản 1.7.2 sẵn sàng cập nhật mã nguồn, không có nghĩa cấu hình production đã hoàn tất.
## 1.8.0 — Chống spam đơn giản theo yêu cầu, 06/10/2026

Thay yêu cầu Turnstile bằng giới hạn trên máy chủ: 2 tin/giây; mặc định 10 tin/30 giây, 60 tin/5 phút, 3 hội thoại mới/10 phút theo mã khách. Nhiều tab/phiên dùng chung quota; có giới hạn IP bổ sung. Khi quá giới hạn, báo thời gian chờ và giữ nháp. Không cần site key/secret, không có CAPTCHA. Giữ cookie/CSRF/origin, quyền hội thoại và chỉ nhận văn bản.

Nâng cấp bật nhận chat mới một lần nếu widget đã bật, giữ lựa chọn tắt của admin sau đó. Deploy plugin 1.8.0 và purge cache; vẫn cần HTTPS/database khỏe. 220 kiểm tra đạt, gồm gửi/nhận HTTP thật trên WordPress test và giới hạn đồng thời. Chưa kiểm tra gửi thật bản mới trên production; SMTP/delivery email và hiệu năng hosting vẫn chưa nghiệm thu. Báo cáo hiện hành: docs/live-chat-final-check.md. Các đoạn Turnstile trong nhật ký phiên bản cũ chỉ mô tả lịch sử, không còn là yêu cầu của 1.8.0.
