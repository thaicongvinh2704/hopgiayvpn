# Bàn giao Mobile UX — hopgiayvpn.com

> Cập nhật: 29/07/2026 — Asia/Ho_Chi_Minh
> Trạng thái kỹ thuật: **Đủ điều kiện bàn giao để review trước triển khai**
> Nhánh làm việc: `mobile-ux-wip-20260728`
> Production/hosting: **Chưa deploy, chưa merge `main`**

## 1. Phạm vi đã hoàn thành

- Hoàn thiện Mobile UX cho các viewport 320×568, 360×800, 390×844 và 412×915.
- Kiểm tra regression tại tablet 768×1024 và desktop 1366×768, 1440×900.
- Hoàn thiện header mobile, menu drawer, thanh chuyển đổi dưới màn hình, footer disclosure, touch target, focus management và reduced motion.
- Hoàn thiện luồng trang chủ, danh mục, sản phẩm, blog, bài viết và liên hệ/form báo giá.
- Sửa ảnh local thiếu hoặc sai biến thể; bổ sung fallback có kích thước thật.
- Tối ưu WebP cho logo, ảnh nhà máy, ảnh hero và logo khách hàng.
- Chuyển video YouTube trang chủ sang click-to-load; không tải iframe trước khi người dùng bấm.
- Minify CSS dùng chung và CSS WooCommerce bằng build script có thể chạy lại.
- Sửa các lỗi Lighthouse accessibility: tương phản màu, thứ tự heading, ARIA không hợp lệ, alt trùng lặp và accessible name của nút video.
- Hoàn thiện audit ảnh, Lighthouse và hai vòng QC độc lập.

## 2. Kết quả kiểm thử cuối

### Playwright — 7 viewport

Mỗi vòng QC chạy trong một tiến trình mới và ghi report riêng:

| Vòng | Tổng ca | Pass | Skip theo điều kiện | Fail |
|---|---:|---:|---:|---:|
| QC pass 1 | 119 | 98 | 21 | 0 |
| QC pass 2 | 119 | 98 | 21 | 0 |

Các ca kiểm tra bao gồm:

- Sáu route đại diện ở mọi viewport.
- Ảnh lỗi/404, kích thước ảnh, overflow ngang và heading H1.
- Header sticky, touch target và mobile conversion bar.
- Focus trap/đóng/mở menu drawer.
- Category disclosure, blog disclosure, TOC và bảng cuộn bằng bàn phím.
- Gallery sản phẩm không autoplay và có trạng thái ARIA.
- Label, validation, error summary và chống gửi trùng form báo giá.
- Video nhà máy chỉ tạo iframe YouTube sau thao tác bấm.
- Tablet và desktop regression.

Report cục bộ:

- `artifacts/mobile-ux-20260728/playwright/qc-pass-1/`
- `artifacts/mobile-ux-20260728/playwright/qc-pass-2/`

### Audit ảnh

- Audit 6 trang đại diện tại 390×844: **0 lỗi ảnh**.
- Ma trận Playwright tiếp tục kiểm tra ảnh sau lazy-load ở cả 7 viewport: **0 fail**.

### Lighthouse mobile 390×844

| Trang | Baseline Perf/A11y | After Perf/A11y | QC 1 Perf/A11y | QC 2 Perf/A11y |
|---|---:|---:|---:|---:|
| Home | 64 / 95 | 69 / 100 | 66 / 100 | 66 / 100 |
| Category | 59 / 98 | 60 / 100 | 60 / 100 | 60 / 100 |
| Product | 59 / 95 | 59 / 100 | 59 / 100 | 59 / 100 |
| Article | 67 / 96 | 73 / 100 | 70 / 100 | 70 / 100 |

LCP trong lượt `after`:

| Trang | Baseline | After | Cải thiện |
|---|---:|---:|---:|
| Home | 8.35 s | 6.55 s | 1.80 s |
| Category | 9.25 s | 8.20 s | 1.05 s |
| Product | 9.24 s | 7.90 s | 1.34 s |
| Article | 6.17 s | 5.42 s | 0.75 s |

Dung lượng tải Lighthouse:

| Trang | Baseline | After | Giảm |
|---|---:|---:|---:|
| Home | 7,752 KiB | 1,374 KiB | 82% |
| Category | 1,537 KiB | 1,347 KiB | 12% |
| Product | 1,398 KiB | 1,209 KiB | 14% |
| Article | 990 KiB | 803 KiB | 19% |

Điểm Performance local còn chịu ảnh hưởng bởi TTFB XAMPP khoảng 1.1–1.4 giây và CSS dùng chung render-blocking. Hai vòng QC cho kết quả ổn định, không có hồi quy; Accessibility đạt 100 ở cả bốn trang.

Report cục bộ:

- `artifacts/mobile-ux-20260728/baseline/lighthouse/`
- `artifacts/mobile-ux-20260728/after/lighthouse/`
- `artifacts/mobile-ux-20260728/qc-pass-1/lighthouse/`
- `artifacts/mobile-ux-20260728/qc-pass-2/lighthouse/`

## 3. Công cụ có thể chạy lại

Chạy trong `tests/mobile-ux`:

```powershell
npm.cmd install
npm.cmd run optimize:images
npm.cmd run build:css
npm.cmd run audit:images -- 390x844
$env:UX_TEST_WORKERS='2'; npx.cmd playwright test --config=playwright.config.mjs
npm.cmd run audit:lighthouse -- qc-pass-1
```

Các file build/audit chính:

- `scripts/optimize-theme-images.mjs`
- `scripts/build-theme-css.mjs`
- `scripts/audit-images.mjs`
- `scripts/run-lighthouse.mjs`

## 4. Giới hạn và lưu ý bàn giao

- URL yêu cầu `/how-much-does-a-cardboard-box-weigh/` vẫn là bài draft trong database local và trả 404.
- Bộ kiểm thử dùng `/how-to-make-paper-bags-stronger/` làm bài đại diện; không tự ý publish bài draft.
- Không có thay đổi nào được deploy lên production.
- Không merge vào `main`; `origin/main` phải tiếp tục giữ nguyên cho đến khi có phê duyệt riêng.
- Worktree có nhiều thay đổi cũ/không liên quan. Chỉ các file Mobile UX được liệt kê và rà soát mới được stage; không reset hoặc đưa các thay đổi khác vào commit.
- Artifacts Lighthouse/Playwright là bằng chứng local, không bắt buộc đưa lên hosting.

## 5. Điều kiện triển khai sau bàn giao

Chỉ triển khai khi chủ dự án phê duyệt riêng:

1. Review commit trên nhánh `mobile-ux-wip-20260728`.
2. Sao lưu production và xác nhận cơ chế rollback.
3. Kiểm tra staging/preview với cache, CDN và cấu hình nén thực tế.
4. Chạy smoke test sau triển khai.
5. Chỉ sau đó mới merge/deploy theo quy trình hosting.
