# VPN Live Chat — Tài liệu chức năng và tình trạng hoàn thành

**Cập nhật:** 06/10/2026 · **Plugin:** 1.7.0 · **Schema:** 3

Đây là tài liệu tổng hợp hiện trạng mới nhất, dùng để biết đã làm gì, còn thiếu gì và tiếp tục công việc sau này. Các báo cáo phiên bản cũ được giữ làm bằng chứng kiểm thử, không thay thế hiện trạng trong tài liệu này. Khi sửa tính năng, cần cập nhật phiên bản, các bảng trạng thái và kết quả kiểm thử tại đây.

## 1. Kết quả hiện tại

**Đã hoàn thành phần chức năng và giao diện để thử trực tiếp trên WordPress local. Chưa triển khai hoặc nghiệm thu trên production hopgiayvpn.com.**

Khách mở widget và gửi tin bằng văn bản ngay; không có ô tên, email vẫn tùy chọn qua nút Add email. Sau khi bắt đầu hội thoại mới hiện lời mời email theo mẫu 03 Gentle Follow-up. Nhân viên mở inbox kiểu WhatsApp, bấm khách và trả lời; hệ thống tự nhận hội thoại mới khi gửi phản hồi đầu tiên. Lịch sử được lưu trong database, có phân quyền, trạng thái đã đọc và ghi chú riêng.

| Thông tin | Hiện trạng |
|---|---|
| Website thử | [http://localhost/hopgiayvpn/](http://localhost/hopgiayvpn/) |
| Inbox nhân viên | [VPN Live Chat](http://localhost/hopgiayvpn/wp-admin/admin.php?page=vpn-live-chat) |
| Repo thực tế | `C:\xampp\htdocs\hopgiayvpn-main\hopgiayvpn-main` |
| Plugin | `wp-content/plugins/vpn-live-chat/` |
| Môi trường | Local; database `hopgiayvpnmoi` |
| Bảng chat | 12 bảng InnoDB; kiểm tra health hiện tại đạt |
| Widget / nhận chat mới | Đã bật trên local |
| Trang có widget hiện tại | `/hopgiayvpn/` và `/hopgiayvpn/contact/` |
| Profile hỗ trợ | Tho Nguyen; ảnh Thọ Nguyễn do người dùng cung cấp |
| Avatar local | Media Library ID 8869; thumbnail 150 × 150, khoảng 42 KB |
| Lời chào ngoài khung | Đã bật trên local; mẫu 01 Friendly Bubble được người dùng chọn |
| Retention local | 0: chưa tự xóa hội thoại theo thời hạn |
| Email đội sales / cron local | Chưa cấu hình email; WP-Cron bị tắt, email ngoài bị chặn |
| Đóng gói | ZIP 1.0.0 là bản cũ; thay đổi 1.2.0 đã sửa trực tiếp local, chưa tạo ZIP mới |

Các dòng cấu hình trên đã được đọc lại từ WordPress bằng thao tác chỉ đọc khi viết tài liệu. Không chứa mật khẩu, secret, cookie hay token.

## 2. Những chức năng đã hoàn thành

### 2.1. Khung chat phía khách

| Chức năng | Kết quả và cách hoạt động |
|---|---|
| Nút mở chat | Avatar tròn 80px, viền/chấm xanh và icon chat; đổi sang nút đóng 58px khi mở |
| Giao diện | Header, vùng hội thoại cuộn riêng, ô nhập cố định dưới cùng; màu xanh theo CTA website |
| Desktop/mobile | Rộng tối đa 400px; giới hạn chiều cao theo viewport; xử lý safe-area và resize |
| Avatar và tên | Ảnh thật Thọ Nguyễn trong header/lời chào; ảnh lỗi có chữ viết tắt dự phòng |
| Lời chào khi mở | Khi chưa có hội thoại, sau bootstrap thành công khoảng 850 ms mới hiện lời chào; có nhãn “Automatic welcome” |
| Hủy lời chào đang chờ | Đóng khung/ẩn tab hủy timer; mở lại không nhân đôi lời chào trong khung hiện tại |
| Hội thoại đang có | Khôi phục lịch sử thật thay cho lời chào đầu chat |
| Nhận thông tin khách | Bỏ ô tên; lời mời email tùy chọn trong hội thoại sau tin đầu. Nút Add email/Edit email luôn có; email chưa xác minh không tự ghép danh tính |
| Ô nhập và gửi | Chỉ văn bản; Enter gửi, Shift+Enter xuống dòng; xử lý composition/IME để tránh gửi nhầm |
| Hiển thị nội dung | Tiếng Việt, emoji gõ trực tiếp và chuỗi dài xuống dòng; nội dung tin được hiển thị như text |
| Lịch sử | Lưu database theo hồ sơ khách; cookie nhận diện 180 ngày gia hạn khi quay lại, khôi phục đa trình duyệt bằng mã email |
| Retry và draft | Mất phản hồi giữ tin đang soạn trong bộ nhớ; retry cùng client ID tránh nhân đôi tin |
| Unread | Badge tính tin sales thật, không tính ghi chú riêng/lời chào và không reset chỉ vì polling |
| Đã đọc | Chỉ xác nhận khi khung đang hiển thị, tab visible và người dùng ở cuối transcript |
| Online/Away/Offline | Dựa trên ca trực, heartbeat và trạng thái nhân viên; không giả lập người đang trả lời |
| Thu nhỏ / Escape | Giữ lịch sử, draft và unread; Escape đóng, focus quay về nút mở |

Lời chào tự động không phải tin nhân viên gửi, không tạo unread hay typing giả. Nó hiện theo nội dung cấu hình hiện tại bằng tiếng Anh: “Hi! How can we help with your packaging project?”.

### 2.2. Hộp chat phía nhân viên

| Chức năng | Kết quả và cách hoạt động |
|---|---|
| Bố cục kiểu WhatsApp | Danh sách khách bên trái; lịch sử và ô trả lời bên phải |
| Danh sách khách | Avatar chữ, tên, giờ, tin gần nhất, dấu chưa đọc; không đưa note riêng vào preview |
| Mục mặc định | “Tin nhắn”: gồm khách mới và hội thoại đang hoạt động trong phạm vi quyền của tài khoản |
| Tìm kiếm | Nhập tên/email tự cập nhật kết quả; có nút tìm và phân trang |
| Trả lời nhanh | Bấm khách → nhập tin → Enter/nút gửi; lần trả lời đầu tự nhận hội thoại chưa có chủ |
| Nhiều nhân viên | Claim/update dùng version và khóa phía server; không bỏ kiểm tra ownership khi đơn giản hóa UI |
| Quyền sales | Xem hàng chờ chưa nhận và chat được giao; không truy cập chat của sales khác |
| Quyền manager | Quản lý chat, profile và cấu hình chat; tài khoản demo không có quyền quản trị bài viết/cửa hàng |
| Draft riêng | Chuyển khách giữ nội dung đang soạn cho đúng hội thoại; chỉ lưu trong bộ nhớ trang |
| Ghi chú riêng | Chỉ nhân viên có quyền xem; không gửi sang widget hoặc đưa vào preview |
| Câu trả lời mẫu | Chọn mẫu để điền tin; manager tạo/sửa/xóa mẫu trong công cụ quản trị |
| Đã đọc tự động | Khi transcript thật đang hiển thị và ở cuối, ghi marker đã đọc theo quyền |
| Mobile | Danh sách và hội thoại là hai màn; có nút quay lại, bấm lại cùng khách vẫn mở được |
| Sync nền mobile | Không tự bật hội thoại khi đang xem danh sách, không đánh dấu tin đến là đã đọc |
| Chi tiết | Mở thông tin nguồn, người phụ trách, trạng thái, nhãn, nhu cầu, lịch hẹn UTC; mặc định thu gọn |
| Quản trị nâng cao | Export, xóa đã xác minh, chặn/bỏ chặn, yêu cầu xác minh và health vẫn có trong khu vực thu gọn |
| Thông báo | Người dùng chủ động bật thông báo/âm thanh; không tự xin quyền khi vừa tải trang |
| Thông báo hệ thống WP | Thu gọn bên ngoài vùng chat, không xóa các cảnh báo |

Giao diện đã đơn giản hóa thao tác thường dùng. Những công cụ quản lý vẫn có để xử lý trường hợp đặc biệt.

### 2.3. Server, dữ liệu và bảo mật đã triển khai

| Hạng mục | Đã có trong code |
|---|---|
| REST API | Namespace `vpn-chat/v1`, tách guest, agent và manager |
| Phiên khách | Cookie HttpOnly/SameSite Strict, hash secret trong database, expiry và revoke |
| Kiểm tra request | Origin, content type JSON, giới hạn body/text, CSRF khách, WP nonce và capability nhân viên |
| Phân quyền dữ liệu | Ownership theo hồ sơ khách đã được cấp quyền và nhân viên; email tự khai báo không cấp quyền lịch sử |
| Chống spam | Quota phía server, counter atomic, challenge, chặn phiên có thời hạn và audit |
| Tệp đính kèm | Không có nút gửi tệp; payload attachment/file/upload bị từ chối 400, multipart bị chặn 415 |
| Lưu tin | Transaction, sequence, client ID và hash để retry không trùng/đổi payload |
| Danh tính người gửi | Lấy từ tài khoản phía server; lưu snapshot profile theo từng tin, không nhận danh tính giả từ client |
| Nhiều sales | Chuyển người phụ trách không đổi người gửi trong lịch sử đã lưu |
| Avatar quản trị | Upload ảnh JPG/PNG/WebP tối đa 2 MB/12 MP, kiểm tra ảnh, nonce và quyền sửa profile |
| Hàng đợi email | Outbox, dedup, lease và retry cho nhắc đội sales; đã có code, chưa nghiệm thu gửi mail thật |
| Retention/export/delete | Có cơ chế cấu hình, export và xóa hội thoại đã xác minh; mặc định chưa tự xóa |
| Vòng đời plugin | Activation/migration, kiểm tra InnoDB; deactivate/uninstall không tự xóa lead |

Upload avatar trong quản trị là thao tác của nhân viên có quyền, tách biệt với chức năng chat văn bản của khách.

## 3. Những phần chưa hoàn tất hoặc chưa kiểm chứng

| Hạng mục còn lại | Tình trạng cụ thể | Điều kiện hoàn thành |
|---|---|---|
| Triển khai hopgiayvpn.com | Chưa triển khai production | Chuẩn bị staging, kiểm tra vận hành rồi triển khai theo phạm vi được giao |
| Turnstile thật | Local dùng adapter giả lập có giới hạn môi trường | Cấu hình key/secret đúng hostname và kiểm tra Siteverify thật, expiry/replay/lỗi mạng |
| SMTP và email nhắc sales | Có code outbox; local chặn mail, chưa cấu hình email đội sales | Cấu hình SMTP, email nhận, chạy cron và kiểm tra gửi/nhận/retry thực |
| Scheduler | WP-Cron local tắt | Thiết lập scheduler trên môi trường triển khai, kiểm tra runner và độ trễ |
| Hiệu năng hosting | Chưa đạt mục tiêu trong bài đo local một worker; chưa đo staging | Đo tải cùng traffic website trên staging tương đương hosting; xác nhận latency, lỗi và độ trễ nhận tin |
| HTTPS/CDN/cache | Chưa kiểm chứng host thật | Kiểm tra cookie Secure, proxy, cache bypass REST, minify/defer và service worker |
| Điện thoại thật | Đã mô phỏng viewport Chrome; chưa thử máy iOS/Android thật | Kiểm tra bàn phím, bộ gõ, nền/foreground, safe-area và thao tác chạm trên thiết bị |
| Thông báo/âm thanh thực | Có chức năng chủ động xin quyền; chưa nghiệm thu đầy đủ OS/browser | Thử quyền cho phép/từ chối, âm thanh và tab nền trên môi trường dùng thực |
| Backup/restore | Có hướng dẫn; chưa diễn tập đầy đủ trên staging | Backup và restore có kiểm soát, áp dụng lại danh sách dữ liệu đã xóa, kiểm tra rollback tương thích schema 3 |
| Chính sách dữ liệu | Retention local 0, chưa chốt lịch tự xóa | Chủ website chốt retention, privacy/cookie notice và quyền truy cập backup/export |
| Cấu hình vận hành | Chưa nghiệm thu nhân sự/ca trực/SLA/mẫu/fallback production | Chốt tài khoản sales, người quản lý, lịch, nội dung và đầu mối theo dõi health |
| Gói cài mới | ZIP hiện có là 1.0.0 | Nếu cần cài môi trường khác, tạo gói từ code mới; không dùng ZIP cũ để ghi đè local 1.2.0 |
| Hồi quy đầy đủ 1.2.0 | Đã chạy các kiểm tra liên quan; chưa chạy lại toàn bộ suite cũ | Trước phát hành, chạy bộ hồi quy đầy đủ trên phiên bản và môi trường sẽ triển khai |

**Lưu ý về số đo hiệu năng:** bài đo cũ dùng PHP built-in một worker, có p95 API khoảng 2,623 giây ở 10 khách + 3 sales, cao hơn mục tiêu pilot <1 giây. Đây là kết quả chưa đạt trong môi trường đo đó, không phải bằng chứng về năng lực production. Chưa kiểm chứng mục tiêu nhận tin khoảng 5 giây dưới tải trên hosting thật. Xem [báo cáo đo tải gốc](../wp-content/plugins/vpn-live-chat/docs/TEST-REPORT.md).

## 4. Các chức năng không thuộc bản hiện tại

Các mục sau không được tính là lỗi hoặc việc dang dở của yêu cầu hiện tại:

- Gửi tệp, ảnh, voice, gọi điện/video qua chat: không triển khai; tệp bị chặn theo yêu cầu.
- Đồng bộ với tài khoản WhatsApp/Zalo: không tích hợp; “kiểu WhatsApp” mô tả cách dùng inbox.
- Typing realtime, emoji picker và nút like: không triển khai. Emoji gõ trực tiếp vẫn được hỗ trợ.
- Native push cho khách khi đóng browser/widget: không có.
- Trả lời qua email, chatbot AI hoặc tự tạo báo giá/đơn hàng: không có.
- Tin tự động giả làm phản hồi nhân viên, Online/đã đọc giả: không có.
- Tự động đồng bộ deletion ledger ra một dịch vụ ngoài: chưa có tích hợp.

Chat hiện dùng REST + polling; có độ trễ tùy hoạt động, tab, mạng và tải server. Không có kết nối WebSocket hoặc ứng dụng điện thoại riêng.

## 5. Cách sử dụng bản local

### Nhân viên

1. Đăng nhập WordPress và mở **VPN Live Chat**.
2. Chọn **Online** khi đang trực. Trạng thái còn phụ thuộc lịch trực và heartbeat.
3. Bấm khách trong danh sách, nhập tin, nhấn **Enter** hoặc nút gửi. Chat mới tự nhận khi gửi phản hồi đầu.
4. **Shift+Enter** xuống dòng. Chờ trạng thái **Đã gửi**; nếu chưa xác nhận, dùng retry cùng tin thay vì tạo tin khác.
5. Dùng **Chi tiết** khi cần chuyển người phụ trách, đổi trạng thái, ghi nhu cầu hoặc hẹn follow-up UTC.
6. Chọn **Ghi chú riêng** chỉ khi muốn lưu nội bộ. Bỏ chọn để gửi phản hồi cho khách.
7. Chọn **Offline** khi kết thúc ca. Nút **Thông báo** là tùy chọn, cần quyền browser.

### Khách và người kiểm thử

1. Mở homepage local trong cửa sổ ẩn danh để tạo phiên khách riêng.
2. Bấm nút chat, chờ lời chào, điền tên và email nếu muốn, nhập tin ở đáy khung và gửi.
3. Trả lời từ inbox nhân viên; mở widget khách để xem phản hồi.
4. Reload để kiểm tra lịch sử. Cookie nhận diện riêng được giữ 180 ngày, gia hạn khi mở chat lại. Trình duyệt khác cần nút Continue a previous conversation và mã xác minh email.

Tài khoản demo `vpn-chat-demo` là tài khoản thử riêng. Mật khẩu nằm trong file runtime được bảo vệ, không chép vào tài liệu hoặc đưa lên URL công khai. Khi sửa giao diện, dùng Ctrl+F5 để tải lại assets mới.

### Đổi tên, ảnh và cấu hình

- **Profile nhân viên:** sửa tên/ảnh của sales; manager có thể chọn nhân viên khác. Tin cũ giữ snapshot đã lưu.
- **Cấu hình:** profile hỗ trợ mặc định, widget, nhận chat mới, đường dẫn, lời chào, lịch trực, fallback, quota và retention.
- Checkbox lời chào chủ động điều khiển teaser ngoài khung; hiện đang tắt. Lời chào tự động trong khung vẫn xuất hiện khi khách mở chat mới.
- **Công cụ quản trị:** mẫu trả lời, health và danh sách chặn. Manager export/xóa/chặn trong **Chi tiết** theo đúng quyền.

## 6. Kiểm thử và bằng chứng

| Phiên bản | Kết quả được ghi nhận | Phạm vi và giới hạn |
|---|---|---|
| 1.1.0 | 231 kiểm tra đạt | Backend, concurrency, config/retention, profiles/unread, browser, avatar upload, theme/quote regression, role và local install; báo cáo lịch sử |
| 1.1.1 | 22 kiểm tra avatar/lời chào/text-only + 8 luồng local đạt | Desktop 1366px/mobile 390px; không chạy lại toàn bộ 231 kiểm tra |
| 1.2.0 | 25 UI/workflow + 7 inbox/quyền + 8 role + 8 luồng local = 48 kiểm tra đạt | Inbox mới, tự nhận, Enter/IME, draft, note, mobile, đọc theo visibility, đăng nhập/reload; không phải nghiệm thu production |

Không cộng các số trên thành số kiểm tra độc lập của bản hiện tại: nhiều kiểm tra local được chạy lại qua các phiên bản. PHP lint và JS syntax đã đạt ở các lượt sửa tương ứng. Khi viết tài liệu này, chỉ đối chiếu code, báo cáo và cấu hình; không chạy lại suite hoặc tạo thêm hội thoại.

**Báo cáo:** [1.1.0](../artifacts/vpn-live-chat/ui-acceptance.json) · [1.1.1](../artifacts/vpn-live-chat/avatar-acceptance.json) · [1.2.0](../artifacts/vpn-live-chat/inbox-acceptance.json) · [25 kiểm tra inbox chi tiết](../artifacts/vpn-live-chat/evidence/inbox-simple-report.json).

**Ảnh sau sửa:** [Inbox desktop](../artifacts/vpn-live-chat/evidence/inbox-simple-desktop.png) · [Inbox mobile](../artifacts/vpn-live-chat/evidence/inbox-simple-mobile.png) · [Widget/avatar desktop](../artifacts/vpn-live-chat/evidence/tho-avatar-1366.png) · [Widget/avatar mobile](../artifacts/vpn-live-chat/evidence/tho-avatar-390.png).

Ảnh dùng hội thoại QA và trạng thái tại thời điểm chụp, không phải danh sách khách thật hoặc trạng thái Online hiện tại.

## 7. Vị trí code và dữ liệu để tiếp tục bảo trì

| Đường dẫn trong repo | Vai trò |
|---|---|
| `wp-content/plugins/vpn-live-chat/vpn-live-chat.php` | Entry, phiên bản và hooks |
| `includes/ui.php`, `assets/admin.js`, `assets/admin.css` | Inbox nhân viên, thao tác và giao diện |
| `assets/widget.js`, `chat.css`, `launcher.js`, `launcher.css` | Khung khách, launcher, lời chào và responsive |
| `includes/rest.php`, `service.php`, `security.php`, `store.php` | API, nghiệp vụ, quyền, validation và dữ liệu |
| `includes/profiles.php`, `settings.php`, `schema.php`, `jobs.php` | Profile/cấu hình, schema, outbox/retention |
| `tests/live-chat/` | Kiểm thử, runtime giả lập và báo cáo; chặn truy cập HTTP |
| `artifacts/vpn-live-chat/` | Báo cáo, ảnh, diff và ZIP cũ |
| `wp-content/mu-plugins/hopgiayvpn-live-chat-local-demo.php` | Adapter Turnstile demo chỉ chạy local; không đưa vào production |
| `wp-content/mu-plugins/hopgiayvpn-local-only.php` | Chặn HTTP/mail ngoài trong môi trường local |

Các đường dẫn `includes/` và `assets/` trong bảng là tương đối từ plugin. Database có 12 bảng `{prefix}vpn_chat_*`: sessions, conversations, messages, agent_presence, rate_limits, blocks, outbox, audit, canned, customers, devices, email_codes. Schema 3 thêm hồ sơ khách, thiết bị và mã xác minh email; schema 2 bổ sung `guest_read_seq` và `sender_profile`. Không xóa bảng để reset thử nếu cần giữ lead.

Tham khảo thêm: [lịch sử thay đổi UI và palette](live-chat-ui.md), [hướng dẫn thử local](live-chat-local-preview.md), [vận hành/rollback](../wp-content/plugins/vpn-live-chat/docs/OPERATIONS.md), [dữ liệu](../wp-content/plugins/vpn-live-chat/docs/DATA.md). [Diff hiện tại](../artifacts/vpn-live-chat/ui-update-from-1.0.0.diff) so với ZIP 1.0.0, không phải diff so với Git hoặc ảnh mẫu.

## Cập nhật giao diện admin 1.2.1

- Tin chưa đọc: nền vàng nhạt, vạch cam, tên/tin gần nhất đậm, giờ màu đậm và badge “Chưa đọc”. Tin đã đọc không còn dấu nhấn này sau acknowledgement thật.
- Khách có email: avatar tông xanh, nhãn “Có email” và địa chỉ hiển thị ngay ở danh sách; header có nhãn “Có email liên hệ”. Email vẫn tự khai báo, không được đánh dấu đã xác minh.
- Thiếu email: nhãn “Chưa có email” và avatar trung tính. Chỉ khi thiếu cả tên mới hiển thị “Khách ẩn danh”. Form tạo hội thoại hiện tại bắt buộc tên/email; không mở thêm luồng chat ẩn danh ở bản này.
- Tăng màu cho thanh công cụ/header theo palette xanh, giữ bố cục và luồng trả lời nhanh. Không đổi thứ tự hay quyền truy cập hội thoại.
- Kiểm tra bổ sung: 14 checks đạt trên Chrome desktop/mobile bằng dữ liệu API mô phỏng ở browser, gồm phân biệt unread/email, fallback, acknowledgement và không tràn mobile. Không ghi khách ẩn danh vào database. JS syntax và PHP lint đạt; không chạy lại toàn bộ suite nghiệp vụ cho lượt đổi màu này.
- Bằng chứng: [báo cáo màu admin](../artifacts/vpn-live-chat/evidence/inbox-colors-report.json), [desktop](../artifacts/vpn-live-chat/evidence/inbox-colors-desktop.png), [mobile](../artifacts/vpn-live-chat/evidence/inbox-colors-mobile.png). Các ảnh này minh họa bằng dữ liệu giả, không phải khách thật.

## Cập nhật độ dễ đọc và tương tác — 1.3.0

- Admin: tên khách 17px, preview 15px, email 13px, tin nhắn 17px (mobile 16px), tiêu đề hội thoại 21px; sidebar 380px trên desktop lớn, 340px ở viewport vừa. Chữ metadata/badge 12–14px, tăng vùng chạm/nút gửi lên 52px.
- Màn hình trống có thẻ chào, minh họa bubble bằng HTML/CSS và nút mở hội thoại (ưu tiên chưa đọc trong trang danh sách hiện tại). Minh họa không phải tin khách hoặc phản hồi tự động.
- Widget khách: rộng tối đa 400px, tên header 19px, nội dung/placeholder 16px, metadata 12px, status/fallback 13px; header chuyển sắc theo xanh thương hiệu, bubble rõ hơn, avatar lớn hơn.
- Ba gợi ý Get a quote / Request a sample / Help me choose điền tin đầu vào ô soạn để khách sửa, không tự gửi hoặc tạo hội thoại. Ẩn gợi ý sau khi hội thoại được tạo; vẫn giữ yêu cầu tên/email và bảo mật.
- Khi mở chat mới focus vào panel, tránh cuộn khuất lời chào do tự focus vào form. Escape/focus quay về launcher và luồng hội thoại đang có vẫn giữ.
- Kiểm thử: 25 checks readability/interaction/màu/receipt desktop-mobile bằng fixtures trong browser và widget thật local; chạy lại 22 avatar/text-only và 8 luồng gửi-nhận local, đều đạt. PHP lint/JS syntax đạt. Không chạy toàn bộ backend suites cũ; chưa kiểm tra điện thoại thật hoặc chứng minh cải thiện tỷ lệ tương tác thực tế.
- Bằng chứng mới: [báo cáo](../artifacts/vpn-live-chat/evidence/chat-readability-report.json), [admin desktop](../artifacts/vpn-live-chat/evidence/chat-readability-desktop.png), [hội thoại admin](../artifacts/vpn-live-chat/evidence/chat-readability-conversation.png), [widget desktop](../artifacts/vpn-live-chat/evidence/chat-readability-widget-desktop.png), [widget mobile](../artifacts/vpn-live-chat/evidence/chat-readability-widget-mobile.png).

## 8. Mốc triển khai và việc tiếp theo

| Mốc | Đã làm |
|---|---|
| 1.0.0 | Plugin backend/widget/inbox ban đầu, kiểm thử và ZIP bàn giao |
| 1.1.0 | Đồng bộ theme, profile từng sales, unread/read cursor và cài thử local |
| 1.1.1 | Avatar thật Thọ Nguyễn, lời chào sau khi mở, ô nhập đầu ở đáy và chặn tệp |
| 1.2.0 | Inbox kiểu WhatsApp, tự nhận khi phản hồi, draft riêng, quản lý thu gọn, mobile và receipt đúng visibility |
| 1.2.1 | Tăng độ nổi bật tin chưa đọc và khách có email, fallback cho dữ liệu thiếu tên/email |
| 1.3.0 | Tăng typography/spacing, màn chào admin có CTA, widget lớn hơn và gợi ý tin nhắn có thể sửa |

Thứ tự tiếp theo khi chuẩn bị dùng thật:

1. Chốt cấu hình vận hành, người phụ trách, email, lịch trực và chính sách dữ liệu.
2. Dựng staging tương đương hosting; dùng Turnstile thật, HTTPS, SMTP và scheduler.
3. Chạy hồi quy toàn bộ, kiểm thử thiết bị thật, tải và backup/restore; xử lý các mục chưa đạt.
4. Tạo gói phiên bản mới, triển khai pilot theo phạm vi được giao và theo dõi health/latency/lỗi.

**Trạng thái bàn giao hiện tại: đã dùng thử được trên local; việc đưa lên production và các kiểm chứng vận hành trong mục 3 vẫn còn mở.**

## Dọn dữ liệu mẫu — 05/10/2026

Theo yêu cầu người dùng sau khi xem và tạm chấp nhận giao diện, đã xóa 17 hội thoại kiểm thử do agent tạo (Local installation test, Inbox QA và Avatar preview với email example.invalid), gồm tin nhắn và outbox liên quan; revoke phiên mẫu bằng cơ chế erase của plugin. Giữ lại hội thoại người dùng tự gửi, không reset database hoặc xóa tài khoản/avatar/cấu hình. Báo cáo số lượng và xác minh tại `artifacts/vpn-live-chat/evidence/demo-cleanup-report.json`. Ảnh nghiệm thu vẫn giữ làm bằng chứng lịch sử, không phản ánh danh sách inbox sau dọn. Không chạy lại browser test tạo dữ liệu sau bước này.

## Danh tính hỗ trợ mặc định — cập nhật theo yêu cầu người dùng

Đã bật `shared_identity=true` trên local: header và tin phản hồi mới của các tài khoản nhân viên hiển thị chung **Tho Nguyen**, với avatar hỗ trợ đã chọn. Tên đăng nhập WordPress admin không được đưa ra làm tên hỗ trợ. Trạng thái Online vẫn theo owner/heartbeat/lịch thật; quyền, owner và actor_id không thay đổi. Đã sửa snapshot hiển thị của tin phản hồi cũ do tài khoản admin gửi và còn mang tên admin trong hội thoại local; giữ nội dung, thời gian và actor_id. Danh tính chung là lựa chọn hiển thị theo yêu cầu người dùng, không đổi tên tài khoản WordPress. Đã đối chiếu header theo owner và profile cho tin mới bằng kiểm tra chỉ đọc sau cập nhật; không tạo tin mẫu.

## Header và chấm trạng thái — 1.3.1

Theo yêu cầu người dùng, header khách hiển thị **Tho Nguyen** và dòng chức danh **Sale Manager**, thay dòng phụ VPN Packaging. Footer vẫn là thương hiệu VPN Packaging. Online dùng chấm xanh #4ADE80, Away vàng #FBBF24, Offline xám #CBD5E1; nhãn chữ vẫn giữ và presence vẫn do server/lịch/heartbeat thật. Đã kiểm tra tên, chức danh và computed CSS ba trạng thái trong browser local; không tạo tin mẫu hoặc thay đổi presence database. JS syntax/PHP lint đạt.


## Email không bắt buộc — 1.3.2

Theo yêu cầu mới, bỏ required của email trên widget, giữ ô phía trên với nhãn “Email address (optional)” và hướng dẫn có thể để trống để chat ngay. Backend chấp nhận email thiếu/rỗng/khoảng trắng (lưu chuỗi rỗng); nếu có email vẫn kiểm tra kiểu dữ liệu, độ dài và định dạng. Tên và nội dung tin vẫn bắt buộc. Không dùng địa chỉ email giả để thay thế. Inbox hiển thị Chưa có email khi trống, Có email khi có địa chỉ; một khách có tên nhưng chưa có email không bị gán tên Khách ẩn danh. Phiên/ownership/lịch sử vẫn dựa trên cookie, không dựa trên email. Bản này không thêm API bổ sung email sau khi bắt đầu hội thoại.

Kiểm tra 12 checks trên local: email optional/nhãn, gửi không email, reload, API thiếu/trống/email hợp lệ, từ chối email sai/array, phân loại inbox. Báo cáo `artifacts/vpn-live-chat/evidence/optional-email-report.json`. Đã xóa 5 hội thoại QA ngay sau kiểm tra, giữ hội thoại còn lại của người dùng. PHP lint/JS syntax đạt; không chạy lại toàn bộ suite cũ hoặc tạo thêm dữ liệu mẫu sau dọn. Các mô tả bắt buộc email ở mục lịch sử các phiên bản trước đã được thay thế bởi yêu cầu 1.3.2.


## Tên và email đều tùy chọn — 1.3.3

Bỏ bắt buộc Your name theo yêu cầu người dùng; nhãn Your name (optional), mục Your details (optional). Khách chỉ cần nhập tin để bắt đầu. Backend nhận tên thiếu/rỗng/khoảng trắng là chuỗi rỗng, vẫn từ chối tên sai kiểu/UTF-8/control/độ dài nếu có giá trị. Email giữ tùy chọn như 1.3.2. Không dùng tên/email giả trong dữ liệu; inbox và header dùng fallback Khách ẩn danh khi tên trống. Quyền truy cập vẫn dựa phiên cookie, không dựa tên/email.

9 checks đạt: không required tên/email, gửi hoàn toàn ẩn danh, reload lịch sử, nhãn trong inbox/header, sales phản hồi, API từ chối tên array và chấp nhận thiếu cả tên/email. Đã xóa 2 hội thoại QA sau kiểm tra, giữ dữ liệu người dùng. Báo cáo optional-name-report.json trong evidence; JS syntax/PHP lint đạt. Các mô tả bắt buộc tên trong lịch sử phiên bản trước không còn áp dụng.


## Online cố định và bổ sung email trong hội thoại — 1.4.0

- Bật cấu hình `always_online` trên local theo yêu cầu người dùng: khung khách luôn hiển thị Online/chấm xanh, không hiện thông báo ngoài giờ. Đây là chế độ hiển thị, không xác nhận nhân viên thực sự có mặt; heartbeat/ca trực và trạng thái nhân viên trong inbox không bị thay đổi. Có checkbox tắt/bật trong Cấu hình. Mặc định plugin mới chưa cấu hình vẫn dùng presence thật.
- Sau khi gửi tin đầu, form Email for follow-up (optional) luôn nằm trên ô nhập tin, có nút Save. Khách có thể lưu, sửa hoặc xóa email mà không đóng chat. Tên/email khi bắt đầu vẫn không bắt buộc. Email lưu hiện trong inbox và khôi phục qua phiên sau reload.
- API `guest/contact` kiểm tra origin, session cookie, CSRF, ownership, quota 10/phút và định dạng email. Cập nhật email trong transaction, tăng version; không tạo tin nhắn hay đổi cursor. Guest summary trả email chỉ sau khi quyền hội thoại đã được kiểm tra. Không truy vấn lịch sử theo email.
- Đồng bộ không ghi đè draft email đang sửa. Nhãn Có email/Chưa có email trong inbox tiếp tục theo dữ liệu mới. Email vẫn tự khai báo, không xác minh.
- Kiểm tra 15 checks đạt: Online/chấm xanh, không notice offline, lưu/sửa/xóa email, reload/draft, mobile, email sai, CSRF, cross-session, gửi tiếp và dữ liệu sales inbox. PHP lint/JS syntax đạt. Báo cáo `artifacts/vpn-live-chat/evidence/contact-email-report.json`; ảnh `contact-email-mobile.png`. Đã xóa hội thoại QA sau kiểm tra, giữ hội thoại người dùng. Các mô tả presence thật cho widget và không có API bổ sung email trong các mốc cũ được thay thế bởi lựa chọn 1.4.0 này. Không triển khai production.


## Lời mời Sale Manager và nhận diện qua trình duyệt — 1.4.1

Đã bật lời mời nổi bật cạnh launcher: avatar/tên Tho Nguyen, Sale Manager, nội dung “Need help with your packaging? Chat directly with our Sale Manager.” và CTA Chat with Tho Nguyen. Hiện tối đa một lần mỗi phiên tab, đóng riêng hoặc bấm để mở khung; không tự mở hội thoại/bootstrap khi chỉ xem trang. Kiểm tra 10 checks desktop/mobile đạt, không tạo tin mẫu; ảnh sale-manager-invite-1366.png và sale-manager-invite-390.png trong evidence.

Phần nhận diện dài hạn/đa trình duyệt đã được triển khai trong 1.5.0 bên dưới, sau khi người dùng đồng ý phương án xác minh email.


## Nhận diện khách dài hạn và khôi phục bằng email — 1.5.0

### Đã hoàn thành trên local

- Chat ngay bằng văn bản, không bắt buộc tên/email. Khách ẩn danh có mã riêng dạng **Khách #000123**; hai trình duyệt mới không có thông tin xác minh vẫn là hai hồ sơ riêng.
- Cookie `vpn_chat_visitor` có token ngẫu nhiên, HttpOnly/SameSite=Strict, Secure trên HTTPS, hạn 180 ngày và gia hạn khi bootstrap chat. Database chỉ lưu hash token. Cookie này tách biệt cookie phiên `vpn_chat_session` ngắn hạn; hết phiên vẫn khôi phục hồ sơ qua cookie nhận diện hợp lệ. Không cam kết vĩnh viễn: xóa cookie, chế độ ẩn danh, trình duyệt chặn dữ liệu hoặc hết 180 ngày không quay lại sẽ mất nhận diện tự động.
- Email liên hệ tự nhập vẫn chưa xác minh và không nối khách khác. Nút **Continue a previous conversation** dưới header cho phép gửi mã tới email, nhập 6 số và khôi phục lịch sử trên trình duyệt khác, kể cả chưa gửi tin chat đầu tiên.
- Mã có hạn 10 phút, tối đa 5 lần thử; mã dùng một lần, ràng buộc phiên yêu cầu, lưu HMAC phía server. API không trả mã; request/verify cần origin, phiên, CSRF và quota theo phiên/email/IP. Không tự gộp qua IP, dấu vân tay hoặc tên khách.
- Sau xác minh cùng email, các cuộc chat của phiên ẩn danh hiện tại được gom vào hồ sơ đã xác minh. Các phiên/thiết bị cũ thuộc hồ sơ nguồn chưa xác minh bị revoke; chỉ trình duyệt vừa chứng minh email được cấp quyền sang hồ sơ đích. Token phiên được đổi sau xác minh. Hồ sơ đã xác minh không tự đổi sang một email khác.
- Admin hiển thị một dòng mỗi hồ sơ trong phạm vi bộ lọc/quyền của nhân viên, badge **Đã xác minh**, mã khách, số cuộc chat và trạng thái chưa đọc tổng hợp. Header có menu lịch sử riêng cho khách. Ghi chú nội bộ và hội thoại ngoài quyền của sales không đưa vào lịch sử khách.
- Widget có menu lịch sử dưới header. Chat đã đóng vẫn xem được; Start a new chat tạo hội thoại mới trong cùng hồ sơ. Khách ẩn danh tiếp tục chat bình thường khi không dùng xác minh.
- Block kiểm tra cả hồ sơ/nguồn hội thoại để cookie dài hạn không vượt lệnh chặn. Xóa hội thoại revoke quyền thiết bị/phiên của hồ sơ; khi không còn hội thoại, xóa hồ sơ chứa email đã xác minh. Quản lý vẫn cần xác minh yêu cầu xóa theo quy trình riêng.
- Schema 3: thêm `customers`, `devices`, `email_codes` và `customer_id` vào sessions/conversations; tổng 12 bảng InnoDB. Migration ghép theo phiên cũ, không ghép email chưa xác minh, không thay nội dung chat có thật.

### Cách thử trên local

1. Mở chat và gửi tin không tên/email: admin hiển thị mã khách. Quay lại cùng trình duyệt giữ hồ sơ.
2. Bấm Continue a previous conversation, nhập email và Send verification code.
3. Local không gửi email ra ngoài. Mã được ghi trong **tests/live-chat/runtime/verification-mailbox.json** (tìm email tương ứng; chỉ mở bằng file trên máy, URL bị Apache chặn 403). Đây là hộp thư thử, không phải dữ liệu xuất công khai.
4. Nhập mã ở widget để xác minh. Mở trình duyệt khác và lặp lại cùng email để xem cùng lịch sử.
5. Admin bấm một khách và dùng menu Hội thoại của khách để đổi cuộc chat; vẫn nhập và Enter để trả lời như trước.

### Còn phải kiểm chứng trước production

- SMTP thật: gửi mã tới hộp thư ngoài, khả năng nhận thư/spam, timeout và giới hạn gửi trên hosting. Local adapter `wp-content/mu-plugins/hopgiayvpn-chat-verification-local.php` chỉ chạy local + database hopgiayvpnmoi + localhost; không nằm trong plugin production.
- Hồi quy toàn bộ, tải nhiều khách/agent, scheduler cleanup, HTTPS/CDN và backup/restore schema 3 trên staging. Bộ kiểm tra mới chỉ nghiệm thu chức năng liên quan trên local.
- Khi restore backup phải revoke cả sessions **và devices**, vô hiệu mã xác minh cũ và áp dụng lại deletion ledger trước khi mở API.
- Cookie chỉ giúp cùng trình duyệt. Đổi trình duyệt cần chứng minh cùng email; không có nhận diện tự động vô điều kiện giữa các trình duyệt.

Bằng chứng: `artifacts/vpn-live-chat/evidence/identity-report.json`, `identity-admin.png`. Tin kiểm thử tạo trong lượt này được dọn theo UUID và nội dung đầu có prefix Identity QA; dữ liệu chat thật được giữ.

Kiểm thử 1.5.0: **32 checks** nhận diện/khôi phục/phân quyền/mã xác minh/block và **8 checks** luồng gửi–sales trả lời–reload thực tế đạt; PHP lint và JS syntax đạt. Đã dọn **4 hội thoại QA** sau lượt kiểm tra cuối, giữ **2 hội thoại thật**. Báo cáo dọn: `artifacts/vpn-live-chat/evidence/identity-cleanup-report.json`. SMTP ngoài local và các kiểm chứng production ở trên vẫn chưa nghiệm thu.


## Nhắc để lại email khi không có người trực — 1.5.1

Đã bật trên local nội dung tiếng Anh phù hợp widget: **“Thanks for reaching out! Please leave your email address so we can get back to you as soon as possible.”** Đây là tin tự động có nhãn Automatic message, kèm nút Leave your email để focus đúng ô email (trước hoặc sau khi bắt đầu chat). Email vẫn không bắt buộc.

Server xuất `agent_available` dựa lịch trực và heartbeat thực tế, trước khi áp dụng always_online. Vì vậy header Tho Nguyen / Sale Manager vẫn Online theo cấu hình hiện tại, nhưng lời nhắc email xuất hiện nếu người phụ trách không available, tạm vắng, heartbeat hết hạn hoặc ngoài lịch trực. Sau khi email được lưu hoặc có người available, lời nhắc ẩn; polling không tạo bản sao. Chat đã đóng không nhắc nhập email. Lời nhắc hiển thị trong widget, không giả làm tin nhân viên đã gửi và không thêm tin vào database, unread hay outbox.

Kiểm tra: 4 trạng thái backend (available, away, offline, heartbeat hết hạn), 11 checks browser về điều kiện hiện/ẩn, nhãn tự động, focus ô email, email tùy chọn, không lặp và mobile. Browser sử dụng mock HTTP cho các thay đổi trạng thái, không tạo tin mẫu trong database; backend dùng transaction rollback. Báo cáo: `artifacts/vpn-live-chat/evidence/email-reminder-report.json`. PHP lint/JS syntax đạt.

## Triển khai từ Git — 1.5.1

Cấu hình lưu trong database, không trong Git. Cài mới cần activate plugin, bật widget/nhận chat và paths production, Turnstile thật, HTTPS, avatar production và lịch trực; cập nhật plugin đã cấu hình thì giữ options cũ. Nếu có offline_copy cũ, đổi tại Cấu hình để dùng lời nhắc email mới. Email khách được lưu để sales liên hệ, chưa có tự gửi phản hồi chat qua email khách. SMTP thật phục vụ mã xác minh/nhắc SLA vẫn cần nghiệm thu. Hướng dẫn chi tiết tại wp-content/plugins/vpn-live-chat/README.md mục Deploy bản 1.5.1.

## Lời mời nổi bật và animation — 1.6.0, 06/10/2026

Kiểm tra production trước sửa xác nhận launcher 1.5.1 đã được tải, nhưng public config có greeting_enabled=false và avatar rỗng. Không còn kết luận plugin chưa được kích hoạt của ngày 05/10; đây là trạng thái mới sau khi người dùng cấu hình production.

- Nút góc màn hình có chữ **Chat with Sales**, kích thước dễ bấm; mở chat thì thu lại thành nút đóng gọn.
- Card lời mời có avatar thật Tho Nguyen, chức danh Sale Manager, tiêu đề **Need a quote or help choosing packaging?**, hướng dẫn không cần đăng ký và CTA **Chat with Tho Nguyen**.
- Lời mời đến sau 2,5 giây; hiệu ứng xuất hiện nhẹ và vòng sáng/nudge ở nút chạy 3 lần rồi dừng. Prefers-reduced-motion tắt animation. Không tạo session, tin nhắn hoặc unread giả chỉ vì thấy lời mời.
- Không đánh dấu “đã xem” ngay khi card hiện. Nếu chưa đóng hoặc mở chat, card tiếp tục hiện khi đổi trang. Khi khách đóng/mở chat, lưu dismissal trong tab; không bật lại liên tục. Bản 1.6 dùng khóa dismissal riêng để trạng thái của bản cũ không chặn lời mời mới.
- Migration cấu hình một lần bật greeting_enabled cho bản nâng cấp; marker vpn_chat_invitation_version=1.6.0. Sau đó quản trị viên tắt checkbox thì giữ lựa chọn, không tự bật lại mỗi request. Không tự bật widget/nhận chat hay đổi Turnstile/lịch/trạng thái trực.
- Đóng gói avatar nhỏ 150px (~42KB) tại assets/tho-nguyen.png; khi tên hỗ trợ là Tho Nguyen và chưa có avatar hợp lệ trong Media Library, dùng ảnh này. Avatar tùy chỉnh vẫn được ưu tiên. Không phụ thuộc ID 8869 của local nữa. Build script chấp nhận PNG trong plugin.

Kiểm tra: 25 checks browser với assets thật/mock homepage và API, gồm desktop/mobile, CTA, avatar, delay, dismissal qua navigation, không tạo session/tin tự động, reduced motion và mở widget; 3 checks backend migration bật một lần/giữ lựa chọn admin/avatar fallback bằng transaction rollback. PHP lint và JS syntax đạt. Báo cáo invitation-v160-report.json trong artifacts/vpn-live-chat/evidence; ảnh bằng chứng chỉ lưu local. Không tạo hội thoại mẫu.

Production cần deploy bản 1.6.0 và purge cache HTML/assets/CDN để thấy cấu hình mới. Mã không tự xóa cache CDN từ local; SMTP/Turnstile production vẫn giữ điều kiện vận hành đã nêu ở trên.

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
