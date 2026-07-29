# Bàn giao tạm dừng — Mobile UX hopgiayvpn.com

> Cập nhật: 28/07/2026 — Asia/Ho_Chi_Minh
>
> Trạng thái: **Tạm dừng theo yêu cầu của chủ dự án**
>
> Triển khai production: **Chưa triển khai**
>
> Ưu tiên hiện tại: **Hoàn thiện giao diện mobile trước; performance/Lighthouse để sau**

## 1. Tiến độ hiện tại

- Tiến độ toàn bộ phạm vi ban đầu: **ước tính 60%**.
- Riêng phần giao diện và tương tác mobile cốt lõi: **ước tính 80%**.
- Chưa thể đánh dấu hoàn thành vì chưa kiểm tra xong 320 px và 412 px, chưa hoàn tất hai vòng QC, chưa xử lý hết ảnh dữ liệu cũ bị thiếu, và chưa làm giai đoạn performance/Lighthouse.

Đây là ước tính theo khối lượng yêu cầu, không phải phần trăm số file.

## 2. Kết quả đã hoàn thành

### 2.1 Baseline và công cụ kiểm thử

- Đã đọc và phân tích toàn bộ yêu cầu trong file đính kèm.
- Đã xác định theme đang dùng: `wp-content/themes/custom-box-theme`.
- Đã chạy website WordPress/WooCommerce local bằng Apache và MySQL.
- Đã tạo bộ Playwright tại `tests/mobile-ux`.
- Đã tạo 112 trường hợp kiểm thử theo 6 trang đại diện và 7 viewport.
- Đã chụp đủ baseline:
  - 6 loại trang.
  - 7 viewport.
  - Ảnh đầu trang và ảnh toàn trang.
  - Tổng cộng **84 ảnh baseline**.
- Đã lưu metrics baseline trong `artifacts/mobile-ux-20260728/baseline/`.

### 2.2 Nền tảng mobile dùng chung

- Đã chuyển header mobile về một hàng, cao tối đa 64 px.
- Đã giữ thứ tự `logo → tìm kiếm → menu`.
- Đã tạo menu off-canvas có:
  - Overlay.
  - Khóa cuộn nền.
  - Focus trap.
  - Đóng bằng Escape.
  - Đóng bằng overlay/nút đóng.
  - Trả focus về nút mở menu.
  - Nhóm danh mục và thông tin liên hệ.
- Đã thêm thanh chuyển đổi cố định phía dưới mobile gồm Quote và WhatsApp.
- Đã loại thanh này khỏi trang liên hệ.
- Đã bỏ cách che lỗi tràn ngang toàn trang bằng `overflow-x: hidden`.
- Đã bổ sung safe-area, focus-visible, reduced-motion và khoảng đệm cuối trang.
- Đã chuyển các nhóm footer thành disclosure trên mobile.
- Đã tăng vùng chạm footer và form theo chuẩn mobile.

### 2.3 Trang chủ

- Đã thu gọn hero để H1, thông điệp và CTA chính xuất hiện sớm trên màn hình 390×844.
- Đã ưu tiên sáu nhóm bao bì chính trên mobile.
- Đã thêm nút `View All Packaging Categories`.
- Đã sửa logic để danh mục phụ ẩn lúc đầu và chỉ mở khi người dùng yêu cầu.
- Đã bỏ autoplay khỏi slider/gallery dùng chung.
- Đã sửa dot thành button có trạng thái truy cập được.
- Đã chuẩn hóa FAQ theo disclosure.
- Đã giữ một form báo giá chính.

### 2.4 Danh mục sản phẩm

- Đã đưa danh sách sản phẩm lên trước bộ duyệt danh mục trong DOM mobile.
- Đã chuyển bộ duyệt danh mục thành disclosure `Browse Categories`.
- Đã thêm label thực cho bộ sắp xếp.
- Đã sửa lưới mobile, vùng chạm và ảnh sản phẩm có fallback.

### 2.5 Chi tiết sản phẩm

- Đã đưa H1 và bằng chứng tin cậy lên trước gallery trên mobile.
- Đã bỏ autoplay gallery.
- Đã thêm điều khiển Previous/Next, keyboard và trạng thái ARIA cho thumbnail.
- Đã chuyển thông số và overview thành disclosure phù hợp.
- Đã thêm khu vực hành động báo giá rõ ràng.
- Đã chuyển ảnh sản phẩm liên quan sang ảnh gốc có kiểm tra file và fallback local.

Lưu ý: thay đổi cuối cùng của phần ảnh sản phẩm liên quan vừa được ghi vào code nhưng **chưa chạy kiểm thử lại** trước khi tạm dừng.

### 2.6 Blog và bài viết

- Đã chuyển archive blog về card một cột trên mobile.
- Đã giảm link trùng lặp trong card.
- Đã chuyển `Packaging Topics` và `Recent Guides` thành disclosure mobile.
- Đã sửa nhãn pagination và vùng chạm.
- Đã tạo mục lục bài viết đóng mặc định trên mobile.
- Đã bọc bảng trong vùng cuộn ngang có tên truy cập và `tabindex="0"`.
- Đã sửa heading anchor để focus đúng khi dùng mục lục.
- Đã thêm fallback cho hero và ảnh bài liên quan khi file gốc không tồn tại.

### 2.7 Form báo giá và trang liên hệ

- Đã viết lại form theo semantics:
  - Label và ID liên kết thật.
  - Fieldset/legend.
  - Required/optional rõ ràng.
  - Length/Width/Depth/Unit.
  - Nhóm thông số tùy chọn dạng disclosure.
  - Label và hướng dẫn tải file.
- Đã thêm:
  - Error summary.
  - Lỗi inline liên kết qua `aria-describedby`.
  - `aria-invalid`.
  - Focus vào error summary.
  - Live status khi gửi.
  - Khóa gửi trùng.
  - Khôi phục nút sau lỗi.
- Đã giữ luồng reCAPTCHA; kiểm thử gửi trùng dùng mô phỏng token thay vì làm yếu bảo mật.
- Đã sửa trang liên hệ với:
  - Một H1.
  - Ảnh nhà máy local có thật.
  - Các lựa chọn liên hệ rõ ràng.
  - Nhóm nội dung và hành động có semantics.
- Control form hiện đã được nâng lên tối thiểu 48 px trong CSS; thay đổi này đã kiểm tra trực tiếp và cho kết quả 48 px.

## 3. Kết quả kiểm thử tại điểm dừng

### 3.1 Đã đạt ở viewport 390×844

Các kiểm thử trọng tâm sau đã đạt:

- Menu drawer: focus trap và tất cả cách đóng.
- Footer disclosure.
- Sáu danh mục chính và nút View All ở trang chủ.
- Danh mục sản phẩm: label sắp xếp, thứ tự products trước taxonomy, disclosure.
- Blog: sidebar đứng sau bài viết và disclosure đóng trên mobile.
- Gallery sản phẩm: điều khiển có tên, trạng thái active có semantics, không autoplay.
- Bài viết: TOC responsive và bảng cuộn có vùng focus.
- Form: mọi control có label và field group.
- Form: validation inline, error summary và focus.
- Form: chống gửi trùng với luồng reCAPTCHA mô phỏng.
- Không còn tràn ngang ở các trang 390 px trong các lần đo gần nhất.

Kết quả lượt chạy đầy đủ gần nhất trước các bản vá cuối:

- **10/16 kiểm thử đạt**.
- 6 route-contract thất bại trước hết vì control form cao 46 px và link footer `Blog` rộng dưới 44 px.
- Hai lỗi kích thước này đã được sửa sau lượt chạy.
- Kiểm thử riêng trang danh mục sau bản sửa đã đạt.
- Kiểm thử riêng trang sản phẩm sau đó chỉ còn một ảnh related-product dùng thumbnail không tồn tại; code đã được sửa sang ảnh gốc/fallback nhưng chưa chạy lại.

Do tạm dừng ngay sau bản sửa cuối, không được hiểu các thay đổi chưa chạy lại là đã xác nhận đạt.

### 3.2 Ảnh kiểm tra sau thay đổi

Đã có một số ảnh kiểm tra 390 px tại:

`artifacts/mobile-ux-20260728/after/screenshots/`

Quan sát gần nhất:

- Trang chủ: header 64 px, H1/CTA trong vùng đầu, không tràn ngang.
- Danh mục: sản phẩm hiển thị trước disclosure.
- Sản phẩm: H1, proof và gallery theo đúng thứ tự mobile.
- Blog: một cột, sidebar disclosure phía sau.
- Liên hệ: H1/CTA/ảnh nhà máy trong bố cục mobile rõ ràng.

Bộ ảnh `after` chưa đủ toàn bộ 7 viewport.

## 4. Điểm dừng chính xác

File được sửa cuối cùng:

`wp-content/themes/custom-box-theme/woocommerce/single-product.php`

Thay đổi cuối:

- Không dùng thumbnail `medium` có thể bị 404 cho sản phẩm liên quan.
- Dùng file ảnh gốc nếu tồn tại.
- Nếu file gốc không tồn tại, dùng `Cardboard-Packaging.webp`.
- Ghi trực tiếp `width` và `height`.

Việc cần làm đầu tiên khi tiếp tục:

1. Chạy `php -l` cho `header.php`, `footer.php`, `woocommerce/single-product.php`.
2. Chạy lại route-contract trang sản phẩm tại 390×844.
3. Chạy route-contract trang chủ, blog, bài viết và liên hệ tại 390×844.
4. Xử lý các thumbnail blog/bài viết còn trỏ đến biến thể `-768x432` không tồn tại bằng ảnh gốc/fallback.
5. Chỉ khi 390 px đạt toàn bộ mới chuyển sang 320 px và 412 px.

## 5. Phần còn lại chưa làm

### Ưu tiên 1 — hoàn thiện giao diện mobile

- Chạy lại đầy đủ 16 kiểm thử ở 390×844 sau các bản sửa cuối.
- Xử lý hết ảnh local bị thiếu trên blog, bài viết và sản phẩm liên quan.
- Kiểm tra và sửa giao diện ở 320×568.
- Kiểm tra và sửa giao diện ở 412×915.
- Chụp đủ bộ ảnh `after` mobile.
- Kiểm tra:
  - Không tràn ngang thật.
  - Header không quá 64 px.
  - Vùng chạm 44/48 px.
  - Ảnh không vỡ.
  - Bottom bar không che nội dung/footer.
  - Form, drawer, disclosure và gallery hoạt động bằng keyboard.

### Ưu tiên 2 — tablet và desktop regression

- Chạy 768×1024.
- Chạy 1366×768 và 1440×900.
- Xác nhận desktop cũ không bị lệch sau các quy tắc CSS mobile.

### Ưu tiên 3 — performance, làm sau theo yêu cầu mới nhất

- Sửa Lighthouse runner.
- Chạy Lighthouse baseline/final cho bốn trang đại diện.
- Tối ưu LCP, lazy loading, `srcset`, `sizes`, script và ảnh ẩn.
- Lập bảng Lighthouse trước/sau.

### Ưu tiên 4 — QC cuối

- QC vòng 1 và sửa toàn bộ lỗi.
- Mở browser context mới.
- QC vòng 2.
- Cập nhật file này thành bản bàn giao cuối.

## 6. File chính đã thay đổi trong đợt Mobile UX

Các file chính thuộc phạm vi lần sửa này:

- `wp-content/themes/custom-box-theme/header.php`
- `wp-content/themes/custom-box-theme/footer.php`
- `wp-content/themes/custom-box-theme/assets/css/responsive.css`
- `wp-content/themes/custom-box-theme/assets/js/main.js`
- `wp-content/themes/custom-box-theme/front-page.php`
- `wp-content/themes/custom-box-theme/home.php`
- `wp-content/themes/custom-box-theme/single.php`
- `wp-content/themes/custom-box-theme/page-contact.php`
- `wp-content/themes/custom-box-theme/inc/setup.php`
- `wp-content/themes/custom-box-theme/template-parts/home/hero.php`
- `wp-content/themes/custom-box-theme/template-parts/home/packaging-category-groups.php`
- `wp-content/themes/custom-box-theme/template-parts/home/faq.php`
- `wp-content/themes/custom-box-theme/template-parts/home/quote-form.php`
- `wp-content/themes/custom-box-theme/template-parts/blog/hero.php`
- `wp-content/themes/custom-box-theme/template-parts/blog/related.php`
- `wp-content/themes/custom-box-theme/template-parts/blog/author-box.php`
- `wp-content/themes/custom-box-theme/woocommerce/archive-product.php`
- `wp-content/themes/custom-box-theme/woocommerce/single-product.php`
- `wp-content/themes/custom-box-theme/template-parts/woocommerce/archive-hero.php`
- `wp-content/themes/custom-box-theme/template-parts/woocommerce/category-grid.php`
- `wp-content/themes/custom-box-theme/template-parts/woocommerce/product-category-hub.php`
- `wp-content/themes/custom-box-theme/template-parts/woocommerce/product-list.php`
- `tests/mobile-ux/`
- `artifacts/mobile-ux-20260728/`

Worktree đã có nhiều thay đổi từ trước. Không được coi mọi file trong `git diff` là do đợt Mobile UX này tạo ra, và không được reset/ghi đè các thay đổi không liên quan.

## 7. Giới hạn và lưu ý

- URL yêu cầu `/how-much-does-a-cardboard-box-weigh/` đang là bài draft trong database local và trả về 404.
- Bộ kiểm thử dùng `/how-to-make-paper-bags-stronger/` làm bài đại diện.
- Không tự ý publish bài draft.
- Không có thay đổi nào được đưa lên production.
- Lighthouse đang được hoãn đúng theo yêu cầu ưu tiên mobile trước.
- Không xóa artifacts; chúng là bằng chứng baseline và kết quả kiểm thử.

## 8. Tiêu chí hoàn thành cuối

Chỉ được ghi dự án hoàn thành khi:

- Toàn bộ route và tương tác đạt ở mobile 320, 390 và 412 px.
- Không còn ảnh vỡ hoặc URL ảnh đại diện 404 trong sáu trang mẫu.
- Tablet/desktop regression đạt.
- Hai vòng QC hoàn tất.
- Performance/Lighthouse hoàn tất ở giai đoạn sau.
- File bàn giao này được cập nhật bằng kết quả cuối và xác nhận rõ chưa/đã triển khai production.
