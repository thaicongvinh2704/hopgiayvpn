# Dữ liệu và quyền riêng tư

## Schema version 3

Schema 2 bổ sung `guest_read_seq` trên conversations và `sender_profile` trên messages. Snapshot profile giữ đúng tên/avatar theo từng tin đã gửi; guest read cursor ghi nhận tin sales mà khách đã xem. Migration giữ actor của tin cũ, không đổi mọi tin thành cùng người.

Tất cả bảng tên `{prefix}vpn_chat_*`, charset/collation WordPress, InnoDB. `dbDelta` idempotent, schema version trong option không autoload; v1 additive, không drop bảng khi deactivate/uninstall.

| Bảng | Dữ liệu / index chính |
|---|---|
| sessions | Hash secret, created/last_seen/absolute expiry, revoke/challenge flags; unique hash, expiry |
| conversations | UUID public, session ownership, tên/email, path, allowlist UTM, nhu cầu, owner/status/label/version, seq/read_seq, follow-up; unique UUID, inbox/session/follow-up/updated indexes |
| messages | Text, sender guest/agent/note, actor, sequence, client ID/hash, UTC time; unique conversation+seq và conversation+scope+client ID |
| agent_presence | WP user ID, available/away/offline, heartbeat; primary user ID |
| rate_limits | Hash scope/window, counter, expiry; primary bucket, expiry index |
| blocks | Session scope, reason, actor/time/expiry/revoked; scope index |
| outbox | Internal SLA notification, dedup, due, lease, retry count/state/minimal error; unique dedup, queue index |
| audit | Actor/action/target/time/minimal state metadata; target index, không sao chép body/email vào audit |
| canned | Mẫu do sales duyệt; nội dung không gắn marketing tự động |

Email tự khai báo chưa xác minh không cấp quyền lịch sử. Schema 3 có customers (mã ID, verified_email unique), devices (hash bearer cookie, customer_id, expires/revoked), email_codes (UUID, session_id, email, HMAC code, expires/attempts/used). Mã gửi qua wp_mail hoặc adapter local riêng. Chỉ sau chứng minh mã mới cấp cookie nhận diện/hợp nhất hồ sơ. Quyền đọc chat dựa customer_id đã được cấp trong phiên, không tra email trực tiếp. Source chỉ path bắt đầu `/`, chặn `//`, backslash, query/fragment/control chars. Metadata chỉ `utm_source`, `utm_medium`, `utm_campaign`, mỗi giá trị <=100 ký tự; không lưu full query/referrer. Start fingerprint SHA-256 nằm trong metadata nội bộ để phát hiện retry payload đổi. Secret/CSRF không lưu nguyên văn trong bảng. Các nội dung do sales gõ vào notes/needs/block reason có thể chứa thông tin cá nhân; vẫn cần kiểm soát truy cập/retention.

## Retention và yêu cầu dữ liệu

Retention mặc định **0 = chưa tự xóa**, cần chủ website phê duyệt. Không coi đây là một thời hạn pháp luật. Khi đặt N ngày, worker xóa hội thoại closed/spam có updated_at quá N ngày, tối đa 20 mỗi lần, cùng messages/outbox/conversation audit; các hội thoại đang mở phải được sales xem xét/đóng hoặc quản lý xóa theo yêu cầu. Notes/needs không phát tán qua guest API. Expired session bị revoke; session không còn hội thoại được cleanup; lead vẫn tồn tại sau khi phiên hết hạn.

Manager export JSON có pagination 1000 tin/request, chứa dữ liệu riêng tư và notes cho mục đích quản trị. Giữ file export trong storage công ty được bảo vệ, không đưa lên Media Library/public URL. Đối với yêu cầu cá nhân: xác minh người yêu cầu qua quy trình công ty trước khi export/xóa; không dựa riêng vào email nhập ở chat. Checkbox verified_request khi xóa ghi nhận người quản lý đã thực hiện xác minh, không tự thực hiện email verification.

Xóa transaction loại transcript/outbox/conversation và revoke phiên; audit còn một receipt tối thiểu actor/ID/time. Ghi receipt vào **deletion ledger ngoài chuỗi backup database** với public conversation ID, thời điểm, người xử lý và phạm vi; lưu chứng cứ xác minh riêng hạn chế quyền. Chưa có tự động đồng bộ ledger ra dịch vụ ngoài.

Backup chứa thông tin chat phải mã hóa, hạn chế quyền và có thời hạn công ty chốt. Khi restore backup cũ: chặn truy cập chat trước, tắt hai flags, revoke toàn bộ sessions và devices, vô hiệu toàn bộ email_codes trong bản restore, áp dụng lại deletion ledger cho tất cả IDs đã xóa **trước khi bật UI/API**, xác minh transcript/outbox không còn và xử lý mail pending để không gửi nhắc từ dữ liệu đã xóa. Không dùng restore toàn DB chỉ để rollback plugin; việc đó có thể làm mất orders/quote mới. Không coi xóa trên database hiện tại là xóa tất cả backups; yêu cầu host xác nhận vòng đời backup.

## Notice và consent

Cập nhật privacy/cookie notice trước pilot: mục đích trả lời enquiry B2B, thông tin lưu, người nhận nội bộ, Turnstile/hosting/SMTP processors, cookie phiên thiết yếu chỉ sau khi khách mở chat, thời gian retention đã chốt, cách gửi yêu cầu quyền dữ liệu qua kênh thật. Chủ website quyết định chính sách consent phù hợp triển khai thực tế. Repo có nội dung privacy nhưng chưa xác minh CMP/consent banner active trên hosting. Không tự nối email chat vào subscription, quảng cáo hoặc analytics.

Không có upload/attachment/download public, không E2E encryption, không đồng bộ CRM. HTTPS bảo vệ transport; database và backup vẫn cần bảo vệ ở host.
