# Kiểm tra keyword và bằng chứng E-E-A-T — 01/10/2026

**Lưu ý:** đây là audit trước tối ưu v2. Keyword, khối giới thiệu doanh nghiệp, About và ảnh công nhân/xưởng đã được xử lý trong [release v2](christmas-gift-boxes-20261001-release.md). Case study và mẫu sản xuất đã xác minh vẫn có thể bổ sung sau.

Phạm vi: 5 sản phẩm local ID 8814–8818, nội dung nguồn, metadata và ảnh lưu trong WordPress, template trang sản phẩm và trang About. Đây là kiểm tra nội dung; chưa đo search volume, Search Console, thứ hạng hoặc khả năng xuất hiện trong câu trả lời AI.

## Kết luận

Có nền tảng SEO và nội dung hỗ trợ trả lời câu hỏi, nhưng chưa đủ bằng chứng trải nghiệm sản xuất để gọi bộ sản phẩm này là hoàn thiện E-E-A-T. Không có ảnh nhà máy hoặc công nhân trong mô tả 5 trang. Không có case study, mẫu đã sản xuất hoặc người phụ trách kỹ thuật có danh tính được xác minh trong nội dung bộ mới.

## Từ khóa hiện tại

| Sản phẩm | Focus keyword | Khớp nguyên cụm title / meta / short / body nguồn |
| --- | --- | --- |
| Hộp nến burgundy | Christmas candle window box | Có / Có / Có / Có |
| Hộp ngôi nhà | Christmas house window gift box | Không / Có / Không / Không |
| Hộp cửa sổ cây thông | Christmas tree window handle box | Không / Có / Không / Không |
| Hộp xanh buộc nơ | green Christmas rigid gift box | Không / Có / Không / Không |
| Hộp trụ đỏ | round Christmas gift box | Không / Có / Không / Không |

Các trường không khớp nguyên cụm vẫn chứa biến thể cùng nghĩa, ví dụ “Christmas House Window Carry Gift Box” hoặc “Red Round Christmas Gift Cylinder”. Đây không tự động là lỗi SEO; không cần lặp exact-match ở mọi trường. Tuy nhiên nên chọn keyword chính nhất quán với tên sản phẩm, rồi dùng biến thể tự nhiên trong đoạn đầu hoặc heading có liên quan.

Trong thân mô tả nguồn của cả 5 trang, chưa có các nguyên cụm “custom Christmas gift boxes”, “Christmas gift box manufacturer”, “Christmas gift boxes wholesale”, “custom Christmas packaging”; chưa có “Vietnam” hoặc “Ho Chi Minh City”. Việt Nam và TP.HCM đã có trong bảng thông số; footer template cũng có thông tin địa chỉ. Vì vậy chưa thể nói toàn trang thiếu thông tin địa lý, nhưng phần mô tả chưa kết nối rõ nhu cầu đặt hộp với doanh nghiệp sản xuất tại Việt Nam.

Nên phân bổ theo ý định, không đưa tất cả từ khóa vào mọi sản phẩm:

- Hub danh mục Noel: nhóm “custom Christmas gift boxes”, nhà sản xuất/nhà cung cấp tại Việt Nam và đặt theo lô nếu dịch vụ thực tế phù hợp.
- Từng sản phẩm: keyword kết cấu riêng, logo tùy chỉnh, kích thước, insert, ribbon/window và đặt hàng theo thông số.
- Đoạn doanh nghiệp có dẫn chứng: tên VPN, địa điểm, quy trình thực tế và liên kết About/contact. Không chuyển chữ “GEO” thành yêu cầu nhồi địa danh; địa điểm là thông tin doanh nghiệp có ích cho khách B2B.

## GEO/AIO

Có Quick answer, H2/H3, FAQ, checklist duyệt mẫu, nội dung văn bản và liên kết nội bộ. Lần kiểm tra frontend trước đó ghi nhận một Product schema mỗi trang, ảnh tải được và các link nội dung trả 200. Chưa có bằng chứng AI đã trích dẫn hoặc chọn các trang này.

Google nêu SEO cơ bản vẫn áp dụng cho AI Overviews/AI Mode, không yêu cầu schema hoặc file AI riêng; trang phải được index và đủ điều kiện hiển thị snippet. Local đang noindex do blog_public=0, nên không đánh giá khả năng xuất hiện thực tế từ môi trường local. Nguồn: [AI features and your website](https://developers.google.com/search/docs/appearance/ai-features).

## Ảnh nhà máy và công nhân

Các ảnh inline lưu trong 5 sản phẩm chỉ là front-view, material-detail và top-view của từng hộp từ ZIP. Gallery cũng chỉ có 6 ảnh thiết kế mỗi mẫu. Chưa chèn ảnh sản xuất, chưa có liên kết About trong thân mô tả.

Theme có sẵn:

- assets/images/anh-nha-may-1.webp: đang được trang About dùng với alt công nhân vận hành thiết bị sản xuất.
- assets/images/anh-nha-may-2.webp: đang được trang About dùng cho khu vực nhà máy.
- assets/images/factory-team-and-production.jpg: đã xem, gồm ảnh nhóm nhân sự mặc áo có logo VPN và cảnh thao tác trong xưởng.
- template-parts/home/factory-video.php: có khu vực ảnh đội ngũ và nút xem video nhà máy ở trang chủ, không được template sản phẩm hiện tại chèn vào bộ mới.

Việc file tồn tại hoặc được trang About sử dụng chưa tự xác minh người chụp, thời gian chụp, quyền sử dụng hoặc nhà máy thuộc doanh nghiệp. Khi dùng làm bằng chứng, cần nguồn doanh nghiệp xác nhận; caption mô tả đúng cảnh, không gọi đây là công đoạn sản xuất 5 mẫu Noel nếu chưa có dữ liệu chứng minh.

## Phần E-E-A-T cần bổ sung

1. Một khối giới thiệu doanh nghiệp ngắn, liên kết trang About và quy trình lấy mẫu.
2. Ảnh có nguồn xác nhận về xưởng hoặc công nhân, caption cụ thể theo công đoạn thật; giữ ảnh thiết kế tách biệt với ảnh sản xuất.
3. Mẫu vật lý, kiểm tra fit hoặc case study đúng sản phẩm nếu có; ghi rõ phiên bản, bối cảnh và kết quả đã xác minh.
4. Người phụ trách hoặc người rà soát kỹ thuật thực tế nếu doanh nghiệp cung cấp danh tính và vai trò.
5. Chứng nhận, công suất và phản hồi khách hàng chỉ dùng khi có hồ sơ hỗ trợ; không sao chép các con số trên About thành bằng chứng đã được audit.

Ảnh xưởng là bằng chứng hỗ trợ, không phải điều kiện đủ để đạt E-E-A-T. Google nhấn mạnh nguồn, nền tảng về tác giả/doanh nghiệp và bằng chứng chuyên môn trong [hướng dẫn nội dung hữu ích, đáng tin cậy](https://developers.google.com/search/docs/fundamentals/creating-helpful-content).
