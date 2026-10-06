# Local test harness

Chỉ dùng database `vpn_chat_test` tại loopback port 3311. Tất cả user/email/password/keys là dữ liệu giả. MU test sink chặn external HTTP và mail; Siteverify mock xác minh hostname/action và token reuse trong test table. Không có bypass/mocks trong plugin ZIP. Test scripts reset/xóa **bảng chat test**; không chạy trên database production.

## Windows / XAMPP setup

Trong repo thật (thư mục con chứa wp-admin), dùng PowerShell:

```powershell
New-Item -ItemType Directory -Force tests/live-chat/runtime | Out-Null
C:/xampp/mysql/bin/mysql_install_db.exe --datadir="$PWD/tests/live-chat/runtime/db" --port=3311
C:/xampp/mysql/bin/mysqld.exe --defaults-file="$PWD/tests/live-chat/runtime/db/my.ini" --bind-address=127.0.0.1 --console
```

Mở terminal khác:

```powershell
C:/xampp/mysql/bin/mysql.exe -h 127.0.0.1 -P 3311 -u root -e "CREATE DATABASE vpn_chat_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
./tests/live-chat/setup.ps1
C:/xampp/php/php.exe -S 127.0.0.1:8091 -t tests/live-chat/runtime/wp tests/live-chat/router.php
```

Setup copy WP entry PHP và wp-admin vào runtime (admin phải copy vì dirname trong core; junction admin có thể trỏ sai wp-load). Chỉ wp-includes/plugins/themes dùng junction đọc mã repo; uploads/options/database riêng. Test WP config không dùng config/database gốc. `WP_INSTALLING` trong setup không đồng nghĩa plugin active được load, nên installer explicitly include chat khi cần. Hãy đặt sink trước mọi tích hợp. Các paths PHP/MySQL chỉ là XAMPP local; trên máy khác thay runtime config/paths.

## Suites (chạy tuần tự)

```powershell
C:/xampp/php/php.exe tests/live-chat/integration.php
C:/xampp/php/php.exe tests/live-chat/concurrency.php
C:/xampp/php/php.exe tests/live-chat/config-failures.php
node tests/live-chat/browser.mjs
C:/xampp/php/php.exe tests/live-chat/theme-smoke.php switch
C:/xampp/php/php.exe tests/live-chat/theme-smoke.php
C:/xampp/php/php.exe tests/live-chat/profiles-unread.php
node tests/live-chat/ui-profile.mjs
node tests/live-chat/profile-upload.mjs
node tests/live-chat/theme-browser.mjs
C:/xampp/php/php.exe tests/live-chat/theme-smoke.php restore
C:/xampp/php/php.exe tests/live-chat/load-fixtures.php
node tests/live-chat/load.mjs
python tools/build-vpn-live-chat.py
./tests/live-chat/release-setup.ps1
```

`PLAYWRIGHT_PATH` và `CHROME_PATH` có thể override executable/package paths; mặc định dùng runtime Codex và Chrome đã có trên máy này. Không cần npm trên production. Suite theme activate WooCommerce (theme phụ thuộc) và Rank Math với lựa chọn offline skip registration trong **DB test**, chặn analytics/external browser requests. Không submit quote form; chỉ gọi helpers mock. Original `tools/test-quote-form-security.php` có expectation cũ (tên lý do CAPTCHA/threshold) không khớp code hiện tại; suite mới kiểm tra hành vi hiện hành mà không sửa backend quote.

`integration.php` dùng WP REST dispatch thật, database InnoDB thật; `concurrency.php` mở các PHP processes độc lập và barrier, không fake concurrency. Browser tests đi qua HTTP thật với login cookie/nonce; chỉ Turnstile script/Siteverify và mail bị mock. `load.mjs` dùng 30 guest sessions giả và 3 WP user cookie thật của DB test, 4 tiers tuần tự, không chạy browser suite đồng thời. JSON fixture có bearer credentials giả nên chỉ ở ignored runtime và không phát hành.

Results/screenshots ở ignored `tests/live-chat/results/`; báo cáo bàn giao/số đo được lưu trong docs và artifacts. Local PHP built-in trên Windows chỉ có một worker; benchmark không đại diện staging/shared hosting và không phải soak test. Real browser background throttling/iOS keyboard/CDN/proxy/SMTP production cần staging QA.

Sau test, dừng PHP server và MariaDB riêng trên port 3311; giữ runtime khi cần xem lại. Không xóa junction bằng recursive traversal. Không dừng dịch vụ MySQL của người khác hoặc restore DB gốc.

UI 1.1.0: chạy `profiles-unread.php` để seed profile sales test Tho Nguyen/Linh Ngo trước `ui-profile.mjs`; theme phải là custom-box-theme (lệnh switch ở trên). `profile-upload.mjs` thử upload logo QA trong Media Library **DB test**, rồi reset profile về avatar chữ; không upload ảnh vào local gốc. Receipt, unread monotonic/CSRF/ownership và snapshot nhiều người gửi được kiểm tra riêng. `browser.mjs` nay xác minh minimized chat tiếp tục sync chậm để có badge; hidden tab vẫn dừng sync.

## Kiểm tra cuối cho UI 1.7.2 / schema 3

Sau khi khởi động DB test 3311 và HTTP test 8091 theo hướng dẫn trên, chạy tuần tự:

- `php tests/live-chat/install.php`
- `php tests/live-chat/final-acceptance.php` (có guard database test/port, dọn bảng chat test trước/sau)
- `node tests/live-chat/final-browser.mjs` (HTTP thật, khách và sales, dọn bằng backend suite khi kết thúc)
- `node tests/live-chat/gentle-followup.mjs` (API giả lập, không dùng DB)
- `node tests/live-chat/invitation.mjs` (API giả lập, không dùng DB)

Không chạy backend và browser HTTP cùng lúc. Các browser harness mặc định dùng đường dẫn Node/Playwright/Chrome của máy này. Các suite integration/config-failures/browser cũ ở trên là lịch sử cho phiên bản trước; fixtures chưa chuyển hết sang customer_id/schema 3 nên không dùng chúng thay bộ final-acceptance/final-browser hiện tại. Báo cáo giới hạn kiểm tra và production tại docs/live-chat-final-check.md.

## 1.8.0
Bộ hiện hành final-acceptance.php/final-browser.mjs không cần mock CAPTCHA. rate-worker.php kiểm tra quota đồng thời, chỉ chạy trên database test riêng. Các suites cũ mô tả Turnstile là lịch sử, không dùng để nghiệm thu 1.8.0.
