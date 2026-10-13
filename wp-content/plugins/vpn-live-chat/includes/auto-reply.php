<?php
defined('ABSPATH') || exit;

/** Fixed, reviewed answers only. No external service, generated claims or customer identity lookup. */
final class VPN_Chat_Auto_Reply {
    const LIMIT = 8;

    public static function profile(): array {
        return ['name' => 'VPN Automated Assistant', 'initials' => 'VA', 'avatar' => ''];
    }

    private static function normalize(string $text): string {
        return trim((string) preg_replace('/\s+/u', ' ', remove_accents(mb_strtolower($text, 'UTF-8'))));
    }

    private static function matches(string $text, string $pattern): bool {
        return (bool) preg_match('~(?<![\p{L}\p{N}])(?:' . $pattern . ')(?![\p{L}\p{N}])~u', $text);
    }

    /** Keep the language for short answers; a clear new language overrides earlier messages. */
    private static function language(array $history): string {
        foreach (array_reverse($history) as $row) {
            if ($row['sender'] !== 'guest') { continue; }
            $raw = mb_strtolower($row['body'], 'UTF-8');
            $text = self::normalize($raw);
            if (preg_match('/[ăâđêôơưáàãạảấầẩẫậắằẳẵặéèẹẻẽếềểễệíìịỉĩóòõọỏốồổỗộớờởỡợúùụủũứừửữựýỳỵỷỹ]/u', $raw)
                || self::matches($text, 'xin chao|bao gia|gia bao nhieu|gia the nao|so luong|toi muon|toi can|tui giay|hop giay|mau thu|van chuyen|cam on|nhan vien|tu van|in logo|cho minh|bao lau|toi thieu') || $text === 'gia') {
                return 'vi';
            }
            if (self::matches($text, 'hello|hi|hey|price|pricing|quote|shipping|sample|samples|please|thanks|thank you|i need|i want|how|what|can you|do you|minimum|human|agent')) { return 'en'; }
        }
        return 'en';
    }

    /** Extract only enough history to avoid asking again for already supplied quote details. */
    private static function details(array $history, string $email): array {
        $guest = implode(' ', array_column(array_filter($history, static fn($r) => $r['sender'] === 'guest'), 'body'));
        $text = self::normalize($guest);
        preg_match('~(?<![\p{L}\p{N}])([0-9]{1,7}(?:[,.][0-9]{3})*|[0-9]+(?:\.[0-9]+)?k)\s*(?:pcs|pieces|units|bags|boxes|cai|chiec|hop|tui)(?![\p{L}])~u', $text, $quantity);
        $number = isset($quantity[1]) ? (str_ends_with($quantity[1], 'k') ? (int) ((float) substr($quantity[1], 0, -1) * 1000) : (int) str_replace([',', '.'], '', $quantity[1])) : 0;
        foreach ($history as $i => $row) {
            if ($row['sender'] !== 'guest' || $i === 0 || $history[$i - 1]['sender'] !== 'bot') { continue; }
            if (self::matches(self::normalize($history[$i - 1]['body']), 'how many|many units|bao nhieu') && preg_match('/^\s*([0-9]{1,7}(?:[,.][0-9]{3})*|[0-9]+(?:\.[0-9]+)?k)\s*$/i', $row['body'], $plain)) {
                $value = strtolower($plain[1]);
                $number = str_ends_with($value, 'k') ? (int) ((float) substr($value, 0, -1) * 1000) : (int) str_replace([',', '.'], '', $value);
            }
        }
        return [
            'product' => self::matches($text, 'bags?|boxes|box|packaging|tui|hop|bao bi|cartons?|mailer|magnetic|rigid|kraft'),
            'quantity' => $number,
            'size' => (bool) preg_match('~\d+(?:[.,]\d+)?\s*(?:cm|mm|inches?|inch|in\b)|\d+(?:[.,]\d+)?\s*[x×]\s*\d+~u', $text),
            'destination' => self::matches($text, 'australia|canada|united states|usa|uk|united kingdom|germany|france|singapore|vietnam|viet nam|japan|korea|india|new zealand|netherlands|italy|spain|thailand|malaysia|indonesia|philippines|uae|dubai|south africa|brazil|mexico|hong kong|taiwan|sweden|norway|denmark|finland|switzerland|belgium|poland|ireland')
                || self::matches(mb_strtolower($guest, 'UTF-8'), 'mỹ|úc|anh quốc|đức|nhật bản') || (bool) preg_match('/(?<!\p{L})US(?!\p{L})/u', $guest)
                || (bool) preg_match('~(?:ship(?:ping)? to|deliver(?:y)? to|destination(?: is)?|gui (?:den|ve)|giao (?:den|ve))\s+\p{L}~u', $text),
            'email' => $email !== '' || (bool) preg_match('/[^\s@]+@[^\s@]+\.[^\s@]+/u', $guest),
        ];
    }

    private static function next_question(array $d, bool $vi): string {
        if (!$d['product'] && !$d['quantity']) { return $vi ? 'Bạn cần loại túi/hộp nào và số lượng dự kiến bao nhiêu?' : 'What type of bag or box do you need, and approximately how many units?'; }
        if (!$d['product']) { return $vi ? 'Bạn cần loại túi hay hộp nào?' : 'What type of bag or box do you need?'; }
        if (!$d['quantity']) { return $vi ? 'Bạn dự kiến đặt bao nhiêu chiếc?' : 'Approximately how many units do you need?'; }
        if (!$d['size'] && !$d['destination']) { return $vi ? 'Bạn cho mình kích thước và quốc gia nhận hàng nhé?' : 'What dimensions do you need, and which country will receive the order?'; }
        if (!$d['size']) { return $vi ? 'Bạn cho mình kích thước mong muốn hoặc kích thước sản phẩm bên trong nhé?' : 'What dimensions do you need, or what size is the product going inside?'; }
        if (!$d['destination']) { return $vi ? 'Bạn muốn giao hàng đến quốc gia nào?' : 'Which country will receive the order?'; }
        if (!$d['email']) { return $vi ? 'Bạn có thể để lại email qua nút Add email để sales gửi báo giá; email không bắt buộc để tiếp tục chat.' : 'You can optionally use Add email so sales can follow up with a quote. You can also keep chatting here.'; }
        return $vi ? 'Sales sẽ xem các thông tin bạn đã gửi trong hội thoại này để xác nhận báo giá.' : 'Sales can review the details you shared in this conversation and confirm a quote.';
    }

    /** Pure answer selection, also used for isolated scenario tests. */
    public static function answer(string $body, array $history, string $email = '', int $sent = 0): array {
        $text = self::normalize($body);
        $vi = self::language($history) === 'vi';
        $d = self::details($history, $email);
        $current = self::details([['sender'=>'guest','body'=>$body]], '');
        $handoff = self::matches($text, 'human|real person|live agent|sales (?:person|manager|representative)|speak to|talk to|stop (?:the )?bot|stop (?:auto|automatic)|nguoi that|nhan vien|gap sales|gap nguoi|tat bot|dung tu dong|khong muon bot');
        $order = self::matches($text, 'my order|order (?:status|number|tracking)|tracking number|where is my|refund|complaint|damaged|cancel (?:my )?order|don hang cua|ma don|tinh trang don|tra hang|hoan tien|khieu nai|bi hong|huy don');
        if ($handoff || $order || $sent >= self::LIMIT - 1) {
            $copy = $vi ? 'Mình sẽ dừng trả lời tự động để sales tiếp nhận. Tin nhắn của bạn đã lưu trong inbox; thời gian phản hồi phụ thuộc lúc nhân viên có mặt. Bạn có thể để lại email qua Add email nếu cần rời trang.' : 'I will stop automated replies so sales can take over. Your messages are saved in the inbox; response time depends on staff availability. You can optionally use Add email if you need to leave.';
            if ($order) { $copy = $vi ? 'Việc kiểm tra đơn hàng hoặc khiếu nại cần nhân viên xử lý. Bạn gửi mã đơn hoặc email dùng khi đặt hàng nhé; không gửi thông tin thanh toán. Mình sẽ dừng trả lời tự động để sales tiếp nhận.' : 'Order checks or complaints need a team member. Please share your order reference or the email used for the order, without payment details. I will stop automated replies so sales can take over.'; }
            return ['body' => $copy, 'handoff' => true, 'intent' => $order ? 'existing_order' : 'handoff'];
        }
        $parts = [];
        $intents = [];
        if (self::matches($text, 'moq|minimum(?: order(?: quantity)?)?|toi thieu')) {
            $parts[] = $vi ? 'MOQ chung để lên kế hoạch là 1.000 chiếc; sales cần xác nhận lại theo từng mẫu và thông số.' : 'Our general planning minimum is 1,000 units; sales must confirm the MOQ for your specific product and specification.'; $intents[] = 'moq';
        }
        if (self::matches($text, 'price|pricing|cost|quote|quotation|how much|bao gia|gia|chi phi')) {
            $parts[] = $vi ? 'Giá phụ thuộc kích thước, giấy, cách in, số lượng và nơi giao hàng. Sales sẽ xác nhận báo giá sau khi có thông số.' : 'Pricing depends on dimensions, paper, printing, quantity and delivery destination. Sales will confirm a quote after reviewing the specification.'; $intents[] = 'quote';
        }
        if (self::matches($text, 'samples?|prototype|mau thu|mau that|xem mau')) {
            $parts[] = $vi ? 'Sales có thể xem yêu cầu mẫu thử theo thiết kế của bạn và xác nhận khả năng làm mẫu, chi phí cùng thời gian; mình chưa thể hứa mẫu miễn phí.' : 'Sales can review a sample request for your design and confirm availability, sample cost and timing. Free samples are not confirmed.'; $intents[] = 'sample';
        }
        if (self::matches($text, 'logo|print(?:ing|ed)?|artwork|design|thiet ke')) {
            $parts[] = $vi ? 'VPN nhận yêu cầu in logo và thiết kế riêng. Cách in và màu sắc sẽ được xác nhận theo giấy và artwork; chat hiện chỉ nhận văn bản.' : 'VPN accepts custom logo printing and design requests. Print method and colors depend on the selected paper and artwork; this chat currently accepts text only.'; $intents[] = 'printing';
        }
        if (self::matches($text, 'lead time|production time|turnaround|how long|deadline|urgent|thoi gian|bao lau|gap|kip')) {
            $parts[] = $vi ? 'Lịch làm mẫu, sản xuất và vận chuyển cần sales xác nhận theo thiết kế, số lượng và hạn nhận hàng của bạn. Bạn cần nhận hàng vào ngày nào?' : 'Sample, production and transit timing must be confirmed by sales for your design, quantity and required delivery date. When do you need the order?'; $intents[] = 'timing';
        }
        if (self::matches($text, 'ship(?:ping)?|deliver(?:y)?|freight|export|van chuyen|giao hang|xuat khau')) {
            $parts[] = $vi ? 'Sales sẽ kiểm tra phương án và phí vận chuyển theo số lượng, kích thước và địa chỉ nhận hàng; mình chưa thể xác nhận phí hoặc ngày đến.' : 'Sales will check shipping options and cost using order quantity, dimensions and destination. Freight cost and arrival date are not confirmed yet.'; $intents[] = 'shipping';
        }
        if (self::matches($text, 'fsc|iso|certif(?:ied|icate|ication)|food(?: safe| grade| contact)?|recycl(?:able|ed)|eco|gsm|material|paper type|chung nhan|an toan|thuc pham|tai che|chat lieu|dinh luong')) {
            $parts[] = $vi ? 'Giấy và thông số sẽ được chọn theo mục đích sử dụng. Chứng nhận, an toàn tiếp xúc thực phẩm, khả năng tái chế hoặc tải trọng cần tài liệu và kiểm tra cho đúng mẫu. Bạn định đựng sản phẩm gì?' : 'Paper and specifications are selected for the intended use. Certification, food-contact safety, recyclability and load capacity need product-specific evidence and checks. What will the packaging hold?'; $intents[] = 'specification';
        }
        if (self::matches($text, 'manufacturer|factory|where (?:are|is)|location|vietnam|viet nam|nha may|o dau')) {
            $parts[] = $vi ? 'VPN Packaging là nhà sản xuất bao bì giấy tại Việt Nam, nhận yêu cầu túi giấy và hộp giấy theo thiết kế riêng.' : 'VPN Packaging is a paper packaging manufacturer in Vietnam, handling custom paper bag and paper box requests.'; $intents[] = 'manufacturer';
        }
        if (self::matches($text, 'contact|email|phone|whatsapp|lien he|dien thoai')) {
            $parts[] = $vi ? 'Sales có thể tiếp nhận ngay trong hội thoại này. Bạn có thể dùng Add email hoặc trang Contact của website để để lại yêu cầu.' : 'Sales can take over in this conversation. You can use Add email or the website Contact page to leave your enquiry.'; $intents[] = 'contact';
        }
        if ($d['quantity'] > 0 && $d['quantity'] < 1000 && !in_array('moq', $intents, true)) {
            $parts[] = $vi ? 'Số lượng bạn nêu thấp hơn MOQ chung để lên kế hoạch là 1.000 chiếc; sales cần kiểm tra khả năng nhận đơn, mình chưa thể xác nhận.' : 'Your quantity is below our general planning minimum of 1,000 units. Sales needs to check whether this request can be accepted; it is not confirmed.'; $intents[] = 'below_moq';
        }
        if (!$parts) {
            if (self::matches($text, 'thanks|thank you|cam on')) { $parts[] = $vi ? 'Cảm ơn bạn! Các thông tin đã gửi sẽ được giữ trong hội thoại để sales xem.' : 'Thank you! Your details stay in this conversation for sales to review.'; $intents[] = 'thanks'; }
            elseif (self::matches($text, 'hello|hi|hey|good morning|good afternoon|xin chao|chao')) { $parts[] = $vi ? 'Chào bạn! Mình có thể hỗ trợ các câu hỏi cơ bản về túi giấy và hộp giấy của VPN.' : 'Hi! I can help with basic questions about VPN paper bags and paper boxes.'; $intents[] = 'greeting'; }
            elseif ($current['product'] || $current['quantity'] || $current['size'] || $current['destination'] || $current['email'] || ($d['quantity'] && preg_match('/^[0-9,.k\s]+$/i', $text))) { $parts[] = $vi ? 'Mình đã ghi nhận thông tin bạn gửi để sales kiểm tra.' : 'Your information is saved for sales to review.'; $intents[] = 'details'; }
            else { $parts[] = $vi ? 'Mình chưa nhận diện được câu hỏi này. Bạn có thể hỏi về giá, MOQ, mẫu thử, in logo, vận chuyển hoặc yêu cầu gặp nhân viên.' : 'I could not match that question. You can ask about pricing, MOQ, samples, logo printing or shipping, or ask to speak to a team member.'; $intents[] = 'fallback'; }
        }
        // A multi-topic question stays concise; invite remaining details rather than a wall of text.
        $parts = array_slice($parts, 0, 2);
        if (!in_array('timing', $intents, true) && !in_array('specification', $intents, true)) { $parts[] = self::next_question($d, $vi); }
        return ['body' => implode("\n\n", $parts), 'handoff' => false, 'intent' => implode(',', $intents)];
    }

    /** Called inside the existing conversation transaction immediately after a stored guest message. */
    public static function reply_locked(array $c, string $body): void {
        global $wpdb;
        if (!VPN_Chat_Settings::get()['auto_reply'] || (int) $c['owner_id'] || $c['status'] !== 'unassigned') { return; }
        $metadata = json_decode($c['metadata'], true) ?: [];
        if (!empty($metadata['auto_reply_handoff'])) { return; }
        $table = VPN_Chat_Store::table('messages');
        if ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE conversation_id=%d AND sender='agent' LIMIT 1", $c['id']))) { return; }
        $sent = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE conversation_id=%d AND sender='bot'", $c['id']));
        if ($sent >= self::LIMIT) { return; }
        $history = $wpdb->get_results($wpdb->prepare("SELECT sender,body FROM $table WHERE conversation_id=%d AND sender IN ('guest','bot') ORDER BY seq DESC LIMIT 24", $c['id']), ARRAY_A);
        $answer = self::answer($body, array_reverse($history), (string) $c['email'], $sent);
        VPN_Chat_Service::send_locked($c, $answer['body'], 'guest-' . $c['guest_seq'], 'bot', 0);
        if ($answer['handoff']) {
            $metadata['auto_reply_handoff'] = true;
            VPN_Chat_Store::query($wpdb->prepare('UPDATE ' . VPN_Chat_Store::table('conversations') . ' SET metadata=%s WHERE id=%d', wp_json_encode($metadata), $c['id']));
        }
    }
}
