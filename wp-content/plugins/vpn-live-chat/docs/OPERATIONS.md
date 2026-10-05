# Monitoring, rollout và rollback

## Trước pilot

Xác minh PHP/MySQL version, engine, CPU/memory, PHP workers/process limit, I/O, max connections, timeout, cron panel, SMTP/quota, cache/minify/defer/CDN/service worker và consent trên **staging tương đương host**. Repo không chứng minh các giới hạn production. Test real Turnstile với hostname/action đúng, expiry/replay và lỗi mạng; dùng keys staging riêng. Các test local dùng mock có ràng buộc chỉ DB test, không chứng minh giao tiếp thật với Cloudflare.

Đo 5, 10, 20 rồi 30 guest chat cùng traffic website và 3 sales; guest 3s, sales 5s, kéo dài đủ để steady-state, sau đó soak test. Mục tiêu pilot tạm thời 10 guest + 3 sales: p95 API <1s và tin đến trong khoảng 5s khi tab active/mạng bình thường. Poll guest idle có thể tăng 5/10/15s; mục tiêu 5s chỉ khi đang hoạt động. 10/3 + 3/5 ≈3,93 sync requests/s, chưa gồm website/send/bootstrap/heartbeat. WP bootstrap vẫn tốn tài nguyên mỗi request.

Thu latency/error/429, delivery lag, CPU, memory, PHP queue/workers, DB queries/locks/slow log, I/O và cron lag. Không lấy local single-worker PHP server làm năng lực shared hosting. Khi không đạt: giảm pilot/poll frequency theo số liệu hoặc trao đổi host về workers/resources; nếu polling vẫn quá tốn, cân nhắc transport/hosting khác ở dự án sau. Không tự triển khai VPS/WebSocket và không load-test production khi chưa được giao phạm vi.

## Theo dõi hằng ngày

- Health: InnoDB, accepting, cron_disabled, last_runner, pending/failed/oldest_due và SMTP configuration. Alert khi last_runner quá 5 phút trong giờ hoạt động, failed>0, oldest_due bị treo >SLA. Owner phân công cụ thể trước pilot.
- Inbox: hàng chờ quá SLA, follow-up quá hạn, owner nghỉ ca, false-positive block review. Available chỉ hợp lệ trong lịch + heartbeat; không dùng trạng thái WP login để tính online.
- API: đếm status/latency theo route, không ghi cookies/CSRF/nonce/secret/body/email hoặc full query vào security logs. HTTP access log nên redaction cookie/header và giới hạn retention. Tuning quota dựa trên 429/traffic thật, không ban IP shared tùy tiện.
- Bảng rate_limits được cleanup theo expiry. Theo dõi kích thước chat/backups và số open conversations không được retention tự xóa. Backup restore rehearsal trước rollout.

## Rollback

1. Tắt **Nhận chat mới** nếu cần dừng influx nhưng sales vẫn xử lý lead hiện có.
2. Tắt **Widget** và purge page cache trên pilot paths. Namespace private luôn bypass cache; không cần flush toàn site.
3. Nếu plugin lỗi, deactivate: cron plugin dừng, presence offline, **dữ liệu không xóa**. Giữ bản ZIP trước đó; reinstall/reactivate v1 trên schema v1 bảo toàn dữ liệu. Uninstall cũng giữ lead/settings/roles.
4. Không hạ code chưa hiểu schema mới; các migrations sau v1 cần kiểm tra compatibility riêng. Bản v1 không có migration destructive. Không restore toàn DB làm mất đơn/quote mới. Nếu bảng chuyển MyISAM hoặc quyền CREATE/ALTER thiếu: giữ disabled, sửa engine/quyền qua quản trị host rồi chạy activation/health lại.
5. Sau rollback kiểm tra menu, quote, WhatsApp, SEO và logged-in sales access. Lead lưu trước deactivate có thể truy cập sau reinstall; manager export trước một downtime dự kiến nếu cần.

## Troubleshooting

| Hiện tượng | Kiểm tra |
|---|---|
| Widget không hiện | widget flag, exact path/trailing slash, cached HTML, JS errors |
| Fallback/new chat unavailable | accept_new, site key/secret constant, HTTPS, tất cả bảng InnoDB |
| Challenge fails | real hostname/action, token hết hạn/dùng lại, outbound HTTPS, local test đang chặn external |
| 403 Origin/CSRF/nonce | origin của home_url phải đúng domain/scheme/port; frontend/API cùng origin; reload/login lại; cache exclusions |
| 409 claim/update | owner/version đã đổi, sync lại trước sửa; không retry update mù quáng |
| 429 | backoff, nhiều tab cùng phiên, WAF/IP shared; draft vẫn trong bộ nhớ |
| Mail/SLA chậm | SMTP transport, cron scheduler, lease/backoff, failed rows; không resend mỗi poll |
| Có tin mới nhưng không thấy ngay | widget/tab hidden, idle backoff, mạng, PHP queue; mở/focus sync |

Khách không nhận desktop/native push khi widget/browser đóng. Nhắc email dành cho đội sales, không gửi tới email khách. Không có email reply adapter trong MVP.


## 1.5.0 / schema 3

Không hạ code schema 2 để xử lý schema 3 mà chưa kiểm tra tương thích. Khôi phục database phải revoke sessions và devices, vô hiệu email_codes, áp dụng deletion ledger trước khi bật API. Email xác minh dùng wp_mail; nghiệm thu SMTP gửi/nhận trên staging. Adapter hộp thư local ngoài plugin chỉ dùng loopback local. Cookie nhận diện 180 ngày gia hạn khác cookie phiên bảo mật ngắn hạn. Chi tiết trạng thái local tại docs/live-chat-status.md của repo.
