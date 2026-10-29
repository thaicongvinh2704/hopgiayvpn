# Trang pizza — rà soát và bản sửa local, 29/10/2026

URL: `/custom-pizza-boxes-manufacturer/`. Chủ website đã duyệt bản local để phát hành qua Git. Chủ website tự pull/deploy hosting. Không thay URL, menu chung hoặc dữ liệu sản phẩm.

## Phát hiện

Trang cũ đã có ba ảnh xưởng, pháp nhân, địa chỉ, liên hệ và ghi chú ảnh concept. Vì vậy không kết luận trang hoàn toàn thiếu E-E-A-T. Điểm còn thiếu là thông tin làm mẫu/sản xuất cụ thể, kiểm tra mẫu theo ứng dụng pizza, video xưởng và đường gửi báo giá ngay từ từng kiểu hộp. Schema Service còn ghi phạm vi Vietnam trong khi tiếp nhận người mua quốc tế.

## Đã sửa

- Title: **Custom Pizza Box Manufacturer in Vietnam | VPN**. Description 153 ký tự; H1 và phần mở đầu làm rõ manufacturer, pizzerias, restaurant groups, distributors và đặt sản xuất theo yêu cầu.
- Giữ ba ảnh gốc hiện có; thêm video tour đang dùng trên các trang khác. Thumbnail dùng lại ảnh workshop đã có. Video chỉ nhúng YouTube khi bấm; mô tả rõ đây là hoạt động bao bì chung.
- Thêm điều kiện đã được chủ website xác nhận: MOQ chung 1.000, mẫu 7–15 ngày, sản xuất 7–45 ngày sau duyệt mẫu tùy đơn; hỗ trợ thiết kế, phí theo thiết kế, đơn trên 5.000 pcs hoàn 50% phí mẫu. Thời gian vận chuyển riêng, điều kiện cuối cùng trong báo giá.
- Thêm nội dung kiểm tra: kích thước/gập, artwork/vật liệu, thử theo pizza và tuyến giao hàng, đóng gói/dispatch. Đây là phạm vi cần thống nhất, không phải kết quả thử nghiệm đã có.
- Yêu cầu tiếp xúc thực phẩm dựa trên mục đích sử dụng, có nguồn FDA. Không công bố FDA-approved/FSC/ISO, nhiệt độ, tải trọng hoặc chứng nhận chưa được cung cấp.
- Nút báo giá xanh đậm theo phong cách đã duyệt; từng card chọn đúng mẫu và đồng bộ format trong form. Thay đổi dropdown sau đó sẽ bỏ lựa chọn card cũ để tránh dữ liệu mâu thuẫn. Giữ nguyên trường bắt buộc, endpoint và xác thực.
- Thêm điều hướng nội dung và link hướng dẫn kích thước nếu bài đã public trong DB. FAQ tăng từ 7 lên 10, schema dùng cùng nguồn câu trả lời. Service areaServed đổi thành Worldwide, khớp nội dung hiển thị.
- Ẩn lời chào chat lớn trên landing; ẩn nút chat đóng khi form đang trong vùng xem để tránh che ô nhập. Chat đã mở và thanh liên hệ chung vẫn giữ nguyên chức năng.

## Kiểm tra

- PHP lint cho hai file chạy trang và file kiểm tra; JS syntax; diff whitespace: đạt.
- So sánh HTML trước/sau: header links, canonical, endpoint, required fields, sản phẩm và ảnh xưởng được giữ nguyên; title/meta có keyword; một H1; FAQ/schema khớp; video không tải iframe ban đầu.
- Browser 320/390/768/1280 px: không tràn ngang. Đã sửa xung đột min-height của khung video dùng chung, kiểm tra tỷ lệ desktop/mobile và nội dung cạnh video không bị che.
- Click card rectangular: form hiển thị đúng mẫu và dropdown Rectangular/flatbread. Đổi dropdown xóa lựa chọn card cũ. Video tạo iframe youtube-nocookie sau click. Không gửi form qua browser.
- `tests/pizza-selected-format-local.php`: đạt; xác minh một lead riêng tư, đúng product/source/consent, redirect thành công và mail queued. Mail/queue đều bị intercept, bản ghi thử được xóa chính xác sau kiểm tra.
- Test consent cũ: đạt. Test minimal cũ lưu lead và trả success nhưng hai assertion về mail đồng bộ không đạt: hệ thống hiện có đã dùng mail queued, trong khi test cũ đòi sent ngay. Không thay queue hoặc mail transport để làm assertion cũ đạt; bài kiểm tra selected-format xác minh cơ chế queued hiện hành. Chưa xác minh thư tới inbox hosting.

Ảnh chụp, HTML và checklist: `outputs/pizza-eeat-20261029/`.

## Bằng chứng còn cần bổ sung sau

Ảnh mẫu pizza thực của VPN và ghi chép thử mẫu/đơn thật nếu muốn tăng bằng chứng kinh nghiệm riêng cho dòng pizza. Không dùng ảnh concept làm bằng chứng sản xuất. Bản sửa hiện tại giúp người mua đánh giá và gửi brief rõ hơn; chưa đủ để tuyên bố đã có kiểm nghiệm hoặc bảo đảm thứ hạng/hiển thị AI.

## Nguồn

- https://developers.google.com/search/docs/fundamentals/creating-helpful-content
- https://www.fda.gov/food/packaging-food-contact-substances-fcs/food-types-conditions-use-food-contact-substances
- `docs/pizza-boxes-manufacturer-landing.md` và `assets/images/pizza-box-landing/README.md` của theme.

Bốn file theme chuyển bằng Git/deploy; riêng thay đổi landing này không cần import database. Các sản phẩm vẫn phải tồn tại trong DB hosting như trước. Sau deploy cần xóa cache trang/CDN; file CSS có phiên bản theo filemtime và JS form được nhúng từ file theme.
