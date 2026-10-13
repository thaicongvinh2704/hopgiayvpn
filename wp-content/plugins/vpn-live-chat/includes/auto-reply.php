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

    /** Only a single, valid address explicitly supplied as the guest's contact. Never grants identity/history access. */
    public static function contact_email(string $body): ?string {
        preg_match_all('~[^\s<>";,():]+@[^\s<>";,():]+~u', $body, $matches);
        if (count($matches[0]) !== 1) { return null; }
        $email = trim($matches[0][0], '.!?[]');
        if (mb_strlen($email)>254 || !is_email($email)) { return null; }
        $text = self::normalize($body);
        if (self::matches($text,"(?:do not|don't|dont) (?:use (?:my )?email|email (?:me|us))|khong (?:su dung|dung) email|khong lien he.{0,20}email")) { return null; }
        if (self::matches($text,"(?:supplier|vendor|friend|example).{0,25}email|email.{0,25}(?:supplier|vendor|friend)|email cua (?:doi tac|nha cung cap)|vi du email")) { return null; }
        if (trim($body, " \t\n\r<>.!?[]") !== $email && !preg_match('/^email\s*:/i',trim($body)) && !self::matches($text, 'my email(?: address)?|our email(?: address)?|email (?:me|us|address)|email is|email:|mail (?:me|us) at|reach (?:me|us) at|contact (?:me|us) at|email cua (?:toi|minh)|email (?:la|cua toi)|gui (?:bao gia|thong tin|cho toi) (?:qua|den)')) { return null; }
        return $email;
    }

    public static function capture_email_locked(array $c, string $body): bool {
        if (!VPN_Chat_Settings::get()['auto_reply'] || $c['email'] !== '') { return false; }
        $email = self::contact_email($body);
        if ($email === null) { return false; }
        global $wpdb;
        return VPN_Chat_Store::query($wpdb->prepare('UPDATE '.VPN_Chat_Store::table('conversations').' SET email=%s,version=version+1 WHERE id=%d AND email=%s', $email, $c['id'], '')) > 0;
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
        $number = 0; $destination_answer = false; $company_answer = false;
        foreach ($history as $i => $row) {
            if ($row['sender'] !== 'guest') { continue; }
            $row_text=self::normalize($row['body']);$previous=($i>0&&$history[$i-1]['sender']==='bot')?self::normalize($history[$i-1]['body']):'';
            preg_match_all('~(?<![\p{L}\p{N}])([0-9]{1,7}(?:[,.][0-9]{3})*|[0-9]+(?:\.[0-9]+)?k)\s*(?:pcs|pieces|units|bags|boxes|cai|chiec|hop|tui)(?![\p{L}])~u',$row_text,$quantities);
            $value=$quantities[1]?end($quantities[1]):'';
            if ($value==='' && self::matches($previous, 'how many|many units|bao nhieu') && preg_match('/^\s*([0-9]{1,7}(?:[,.][0-9]{3})*|[0-9]+(?:\.[0-9]+)?k)\s*$/i', $row['body'], $plain)) { $value=strtolower($plain[1]); }
            if ($value !== '') {
                $number = str_ends_with($value, 'k') ? (int) ((float) substr($value, 0, -1) * 1000) : (int) str_replace([',', '.'], '', $value);
            }
            if(self::matches($previous,'which country|quoc gia') && preg_match("/^[\p{L} .'-]{2,60}$/u",trim($row['body'])) && !self::matches($row_text,"don.t know|not sure|khong biet|chua biet|email|quote|price|hi|hello")){$destination_answer=true;}
            if(self::matches($previous,'company or project name|ten cong ty') && mb_strlen($row['body'])<150 && !str_contains($row['body'],'?') && !self::matches($row_text,"don.t know|not sure|khong biet|chua biet|no email|hello|hi")){$company_answer=true;}
        }
        $provided_email = $email !== '';
        foreach ($history as $row) { if ($row['sender']==='guest' && self::contact_email($row['body']) !== null) { $provided_email=true; } }
        return [
            'product' => self::matches($text, 'bags?|boxes|box|packaging|tui|hop|bao bi|cartons?|mailer|magnetic|rigid|kraft'),
            'quantity' => $number,
            'size' => (bool) preg_match('~\d+(?:[.,]\d+)?\s*(?:cm|mm|inches?|inch|in\b)|\d+(?:[.,]\d+)?\s*[x×]\s*\d+~u', $text),
            'size_uncertain' => self::matches($text,"not sure.*(?:size|dimensions)|don.t know.*(?:size|dimensions)|no measurements|chua biet.*kich thuoc|khong biet.*kich thuoc"),
            'destination' => $destination_answer || self::matches($text, 'australia|canada|united states|usa|uk|united kingdom|germany|france|singapore|vietnam|viet nam|japan|korea|india|new zealand|netherlands|italy|spain|thailand|malaysia|indonesia|philippines|uae|dubai|south africa|brazil|mexico|hong kong|taiwan|sweden|norway|denmark|finland|switzerland|belgium|poland|ireland')
                || self::matches(mb_strtolower($guest, 'UTF-8'), 'mỹ|úc|anh quốc|đức|nhật bản') || (bool) preg_match('/(?<!\p{L})US(?!\p{L})/u', $guest)
                || (bool) preg_match('~(?:ship(?:ping)? to|deliver(?:y)? to|destination(?: is)?|gui (?:den|ve)|giao (?:den|ve))\s+\p{L}~u', $text),
            'email' => $provided_email,
            'company' => $company_answer || self::matches($text,'my company|our company|company name|project name|brand name|cong ty|ten cong ty|thuong hieu|du an'),
            'email_declined' => self::matches($text, "no email|do not ask (?:for )?(?:my )?email|don't ask (?:for )?(?:my )?email|(?:do not|don't|dont) (?:use (?:my )?email|email (?:me|us))|khong (?:su dung|dung) email|khong lien he.{0,20}email|(?:do not|don't|dont) want (?:to (?:share|give) )?(?:my )?email|(?:won.t|will not|prefer not to|rather not) (?:share|give) (?:my )?email|not comfortable (?:sharing|giving) (?:my )?email|prefer to chat here|khong (?:muon|can)(?: (?:gui|cho|cung cap|de lai))? email|khong (?:gui|cho|cung cap|de lai) email"),
        ];
    }

    private static function next_question(array $d, bool $vi): string {
        if (!$d['product'] && !$d['quantity']) { return $vi ? 'Bạn đang cần loại túi hoặc hộp nào?' : 'What type of bag or box are you looking for?'; }
        if (!$d['product']) { return $vi ? 'Bạn cần loại túi hay hộp nào?' : 'What type of bag or box do you need?'; }
        if (!$d['quantity']) { return $vi ? 'Bạn dự kiến đặt bao nhiêu chiếc?' : 'Approximately how many units do you need?'; }
        if (!$d['size'] && !$d['size_uncertain']) { return $vi ? 'Bạn cho mình kích thước mong muốn hoặc kích thước sản phẩm bên trong nhé?' : 'What dimensions do you need, or what size is the product going inside?'; }
        if (!$d['destination']) { return $vi ? 'Bạn muốn giao hàng đến quốc gia nào?' : 'Which country will receive the order?'; }
        if(!$d['company']){return $vi ? 'Bạn có thể cho biết tên công ty/dự án để sales tư vấn phù hợp hơn không?' : 'Could you share your company or project name so sales can tailor the advice?';}
        return $vi ? 'Bạn có yêu cầu in, hoàn thiện hoặc hạn nhận hàng nào muốn sales kiểm tra thêm không?' : 'Are there any print, finishing or delivery-date requirements you would like sales to review?';
    }

    /** Every scenario ends with a relevant lead action, without asking again for a supplied email. */
    private static function lead_invitation(array $d, bool $vi, array $intents): string {
        if ($d['email'] || $d['email_declined']) { return ''; }
        if(in_array('invalid_email',$intents,true)){return '';}
        $purpose = $vi ? 'sales tư vấn và gửi báo giá theo nhu cầu của bạn' : 'sales can advise and follow up with a quote for your project';
        if (in_array('existing_order', $intents, true)) { $purpose = $vi ? 'nhân viên liên hệ lại về đơn hàng của bạn' : 'a team member can follow up about your order'; }
        elseif (in_array('sample', $intents, true)) { $purpose = $vi ? 'sales liên hệ về phương án, chi phí và thời gian làm mẫu' : 'sales can follow up with sample options, cost and timing'; }
        elseif (in_array('timing', $intents, true)) { $purpose = $vi ? 'sales kiểm tra và liên hệ về lịch đáp ứng hạn nhận hàng của bạn' : 'sales can review your required delivery date and follow up with timing'; }
        elseif (in_array('shipping', $intents, true)) { $purpose = $vi ? 'sales liên hệ về phương án và chi phí giao hàng' : 'sales can follow up with shipping options and costs'; }
        elseif (in_array('specification', $intents, true)) { $purpose = $vi ? 'sales liên hệ về thông số và tài liệu phù hợp với sản phẩm của bạn' : 'sales can follow up with specifications and relevant product documentation'; }
        elseif (in_array('handoff', $intents, true)) { $purpose = $vi ? 'sales có thể liên hệ ngay cả khi bạn đã rời trang' : 'sales can contact you even after you leave this page'; }
        elseif (in_array('catalogue', $intents, true)) { $purpose = $vi ? 'sales liên hệ với những mẫu tham khảo phù hợp thay vì bạn phải xem toàn bộ danh mục' : 'sales can follow up with product references matched to your project'; }
        elseif (in_array('budget', $intents, true)) { $purpose = $vi ? 'sales kiểm tra phương án giấy, cấu tạo hoặc hoàn thiện phù hợp ngân sách của bạn' : 'sales can follow up with specification alternatives to compare against your budget'; }
        elseif (in_array('design_help', $intents, true) || in_array('size_help', $intents, true)) { $purpose = $vi ? 'sales liên hệ để cùng bạn xác định thiết kế và kích thước phù hợp' : 'sales can help review a suitable structure and size before you finalize the details'; }
        elseif (in_array('finishing', $intents, true) || in_array('color', $intents, true) || in_array('insert', $intents, true) || in_array('artwork', $intents, true)) { $purpose = $vi ? 'sales liên hệ về hướng dẫn artwork và phương án hoàn thiện phù hợp' : 'sales can follow up with artwork guidance and suitable finishing options'; }
        elseif (in_array('ordering', $intents, true) || in_array('reorder', $intents, true)) { $purpose = $vi ? 'sales liên hệ về báo giá và bước đặt hàng theo yêu cầu của bạn' : 'sales can follow up with a quotation and the ordering steps for your request'; }
        elseif (in_array('leaving', $intents, true)) { $purpose = $vi ? 'sales tiếp tục tư vấn khi bạn rảnh mà bạn không cần chờ tại đây' : 'sales can continue helping when you are free, without you waiting on this page'; }
        elseif (in_array('fallback', $intents, true)) { $purpose = $vi ? 'sales liên hệ và giải đáp đúng yêu cầu bạn vừa gửi' : 'sales can follow up on the question you just shared'; }
        return $vi ? 'Bạn gửi email ngay trong chat hoặc dùng Add email để '.$purpose.' nhé? Bạn vẫn có thể tiếp tục chat ở đây.' : 'Please leave your email here or use Add email so '.$purpose.'. You can keep chatting here too.';
    }

    /** Pure answer selection, also used for isolated scenario tests. */
    public static function answer(string $body, array $history, string $email = '', int $sent = 0): array {
        $text = self::normalize($body);
        $vi = self::language($history) === 'vi';
        $d = self::details($history, $email);
        $current = self::details([['sender'=>'guest','body'=>$body]], '');
        $submitted_email = self::contact_email($body);
        if ($submitted_email !== null) {
            $copy = $vi ? 'Cảm ơn bạn đã để lại email. Sales có thể dùng thông tin liên hệ này để tư vấn tiếp về yêu cầu của bạn.' : 'Thank you for sharing your email. Sales can use your contact details to follow up about your project.';
            if ($email !== '' && strcasecmp($submitted_email, $email) !== 0) { $copy = $vi ? 'Bạn đã gửi một email khác. Dùng Edit email để cập nhật email liên hệ đang lưu nhé.' : 'You shared a different email. Please use Edit email to update the saved contact address.'; }
            return ['body'=>$copy."\n\n".self::next_question($d,$vi),'handoff'=>false,'intent'=>'email_received'];
        }
        $handoff = self::matches($text, 'human|real person|live agent|sales (?:person|manager|representative)|speak to|talk to|stop (?:the )?bot|stop (?:auto|automatic)|nguoi that|nhan vien|gap sales|gap nguoi|tat bot|dung tu dong|khong muon bot');
        $order = self::matches($text, 'my order|order (?:status|number|tracking)|tracking number|where is my|refund|complaint|damaged|cancel (?:my )?order|don hang cua|ma don|tinh trang don|tra hang|hoan tien|khieu nai|bi hong|huy don');
        if(self::matches($text,'reorder|repeat.{0,12}order|dat lai') && !self::matches($text,'refund|complaint|damaged|cancel|hoan tien|khieu nai|bi hong|huy don')){$order=false;}
        if ($handoff || $order || $sent >= self::LIMIT - 1) {
            $copy = $vi ? 'Mình sẽ dừng trả lời tự động để sales tiếp nhận. Tin nhắn của bạn đã lưu trong inbox; thời gian phản hồi phụ thuộc lúc nhân viên có mặt.' : 'I will stop automated replies so sales can take over. Your messages are saved in the inbox; response time depends on staff availability.';
            if ($order) { $copy = $vi ? 'Việc kiểm tra đơn hàng hoặc khiếu nại cần nhân viên xử lý. Bạn gửi mã đơn nhé; không gửi thông tin thanh toán. Mình sẽ dừng trả lời tự động để sales tiếp nhận.' : 'Order checks or complaints need a team member. Please share your order reference, without payment details. I will stop automated replies so sales can take over.'; }
            $intent = $order ? 'existing_order' : 'handoff';
            $invitation = self::lead_invitation($d, $vi, [$intent]);
            if ($invitation !== '') { $copy .= "\n\n".$invitation; }
            elseif (!$order) { $copy .= "\n\n".self::next_question($d, $vi); }
            return ['body' => $copy, 'handoff' => true, 'intent' => $intent];
        }
        $parts = [];
        $intents = [];
        // Specific questions take priority over generic price/logo words.
        $specific = [
            'email_privacy' => ['why.{0,25}email|email required|must.{0,20}email|spam|newsletter|privacy|tai sao.{0,20}email|bat buoc.{0,20}email|email.{0,20}bat buoc|bao mat', 'Email chỉ là tùy chọn để sales liên hệ về yêu cầu bạn gửi. Chat không tự đăng ký nhận quảng cáo; bạn vẫn có thể trao đổi tại đây.', 'Email is optional for sales to follow up about your enquiry. This chat does not automatically subscribe you to marketing; you can keep chatting here.'],
            'invalid_email' => ['my email|email is|email address|email cua|email la', 'Email chưa đúng định dạng. Bạn kiểm tra lại và nhập qua Add email để sales có thể liên hệ nhé.', 'That email does not look complete. Please check it and use Add email so sales has a usable contact address.'],
            'leaving' => ['bye|goodbye|got to go|have to go|leaving|talk later|busy now|tam biet|phai di|roi trang|ban qua|noi chuyen sau', 'Bạn không cần chờ tại đây. Các nhu cầu đã gửi được lưu để sales xem và tư vấn tiếp.', 'You do not need to wait here. Your project details are saved for sales to review and follow up.'],
            'budget' => ['too (?:high|expensive)|cheaper|cheap|discount|budget|reduce (?:the )?(?:price|cost)|gia cao|dat qua|giam gia|ngan sach|re hon', 'Sales có thể xem cấu tạo, giấy hoặc cách hoàn thiện khác để so sánh chi phí. Ngân sách hoặc mức giá mục tiêu của bạn là bao nhiêu?', 'Sales can review alternative structures, paper or finishes to compare costs. What budget or target unit price are you working toward?'],
            'catalogue' => ['catalogues?|catalogs?|brochure|product list|price list|photos?|pictures?|bang gia|danh muc|mau tham khao|anh mau|hinh anh', 'Bạn có thể xem mẫu trên website ngay. Bạn quan tâm loại bao bì nào để sales chọn mẫu tham khảo phù hợp?', 'You can browse the website now. Which packaging category interests you so sales can select relevant product references?'],
            'design_help' => ["no design|don.t have (?:a )?design|need (?:a )?design|design help|help with (?:a |my |the )?(?:design|artwork)|help me design|chua co thiet ke|khong co thiet ke|ho tro thiet ke", 'Chưa có thiết kế vẫn có thể gửi yêu cầu. Sales cần xem sản phẩm và mục tiêu trình bày để xác nhận phạm vi hỗ trợ thiết kế.', 'You can enquire before having a finished design. Sales needs to review your product and presentation goals to confirm the design support available.'],
            'size_help' => ["not sure.*(?:size|dimensions)|don.t know.*(?:size|dimensions)|no measurements|what size|which size|exact size|custom size|custom dimensions|chua biet.*kich thuoc|khong biet.*kich thuoc|kich thuoc rieng", 'Chưa biết kích thước hộp/túi vẫn có thể trao đổi. Bạn cho biết sản phẩm bên trong hoặc kích thước sản phẩm để sales xem phương án phù hợp.', 'You can enquire before deciding the packaging size. Share what goes inside or its measurements so sales can review a suitable size.'],
            'artwork' => ['upload|attach|artwork format|file format|send (?:a |my |the )?(?:file|design)|gui file|dinh dang file|tai (?:file|anh)', 'Chat chỉ nhận văn bản. Sales cần xác nhận định dạng artwork và cách gửi file; bạn mô tả thiết kế hoặc sản phẩm trước nhé.', 'This chat accepts text only. Sales can confirm the artwork format and how to send files; start by describing your design or product.'],
            'finishing' => ['foil|emboss(?:ing)?|deboss(?:ing)?|spot uv|laminat(?:ion|ed)|ep kim|dap noi|dap chim|can mang|phu uv', 'Sales có thể xem yêu cầu ép kim, dập hoặc UV theo giấy và artwork. Bạn muốn hiệu ứng nào?', 'Sales can review foil, embossing or UV requests against the paper and artwork. Which effect are you considering?'],
            'color' => ['pantone|brand colou?r|match.*colou?r|mau thuong hieu|mau pantone', 'Màu thương hiệu cần thống nhất màu mục tiêu, cách in và mẫu duyệt. Bạn có mã Pantone hoặc màu tham khảo không?', 'Brand colors need a target color, print method and approved proof. Do you have a Pantone code or color reference?'],
            'insert' => ['inserts?|foam|tray|divider|khay lot|lot mut|vach ngan', 'Khay lót cần xem hình dạng, kích thước và yêu cầu bảo vệ sản phẩm. Bạn định đặt sản phẩm gì bên trong?', 'An insert needs to be reviewed against product shape, size and protection needs. What will it hold?'],
            'ordering' => ['payment|pay|deposit|bank transfer|place (?:an? )?order|how to order|ordering|thanh toan|dat coc|chuyen khoan|dat hang', 'Quy trình bắt đầu bằng thông số và báo giá, sau đó xác nhận duyệt mẫu và điều khoản trước sản xuất. Sales cần xác nhận điều khoản thanh toán cho đơn của bạn.', 'The process starts with specifications and a quotation, followed by approvals and order terms before production. Sales must confirm the payment terms for your order.'],
            'reorder' => ['reorder|repeat order|reproduce|existing box|dat lai|lam lai mau|hop cu', 'Sales cần xem thông số hoặc mã đơn trước để kiểm tra khả năng làm lại đúng mẫu. Bạn có mã đơn hoặc mô tả mẫu cũ không?', 'Sales needs the previous specification or order reference to review a match. Do you have an order reference or description of the existing packaging?'],
            'wholesale' => ['wholesale|distributor|reseller|bulk order|ban si|dai ly|so luong lon', 'VPN nhận yêu cầu bao bì B2B theo thông số và số lượng. Sales sẽ xác nhận giá và điều kiện theo dự án; không có mức chiết khấu tự động.', 'VPN handles B2B packaging enquiries based on specification and quantity. Sales will confirm project pricing and terms; no automatic discount is confirmed.'],
            'shipping_terms' => ['vat|tax|duties|incoterms|fob|cif|ddp|dap|exw|thue|hai quan', 'Phạm vi báo giá, thuế/phí và điều kiện giao hàng cần sales xác nhận bằng văn bản theo địa điểm nhận hàng. Bạn muốn giao đến đâu?', 'The quoted scope, taxes or duties and delivery terms need written confirmation from sales for your destination. Where will the order be delivered?'],
            'product_options' => ['magnetic|rigid|gift boxes|skincare|perfume|candles?|jewelry|jewellery|watches|matching.*bags|pizza|bakery|hop nam cham|my pham|nuoc hoa|nen thom|trang suc|tui dong bo', 'Sales có thể xem yêu cầu túi/hộp theo sản phẩm, cấu tạo và cách trình bày. Mẫu phù hợp cần được xác nhận theo kích thước và mục đích sử dụng.', 'Sales can review a bag or box request against your product, structure and presentation needs. The right option must be confirmed for the dimensions and intended use.'],
        ];
        foreach ($specific as $intent=>[$pattern,$vietnamese,$english]) {
            if ($intent==='invalid_email' && !str_contains($body,'@')) { continue; }
            if ($intent==='invalid_email') {
                preg_match_all('~[^\s<>";,():]+@[^\s<>";,():]+~u',$body,$candidates);
                if(array_filter($candidates[0],static fn($candidate)=>is_email(trim($candidate,'.!?[]')))){continue;}
            }
            if ($intent==='product_options' && self::matches($text,'price|pricing|quote|moq|minimum|sample|samples|logo|shipping|food|fsc|urgent|lead time')) { continue; }
            if (self::matches($text,$pattern) || ($intent==='invalid_email' && preg_match('/^\S*@\S*$/u',trim($body)))) {
                if($intent==='invalid_email'&&$d['email']){$vietnamese='Email mới chưa đúng định dạng. Bạn vẫn có email liên hệ đã lưu; dùng Edit email nếu muốn sửa địa chỉ đó.';$english='The new email looks incomplete. Your saved contact is preserved; use Edit email if you want to update it.';}
                $parts[]= $vi ? $vietnamese : $english; $intents[]=$intent; break;
            }
        }
        $specific_found = (bool) $parts;
        if (!$specific_found && self::matches($text, 'moq|minimum(?: order(?: quantity)?)?|toi thieu')) {
            $parts[] = $vi ? 'MOQ chung để lên kế hoạch là 1.000 chiếc; sales cần xác nhận lại theo từng mẫu và thông số.' : 'Our general planning minimum is 1,000 units; sales must confirm the MOQ for your specific product and specification.'; $intents[] = 'moq';
        }
        if (!$specific_found && self::matches($text, 'prices?|pricing|costs?|quotes?|quotations?|how much|bao gia|gia|chi phi')) {
            $parts[] = $vi ? 'Giá phụ thuộc kích thước, giấy, cách in, số lượng và nơi giao hàng. Sales sẽ xác nhận báo giá sau khi có thông số.' : 'Pricing depends on dimensions, paper, printing, quantity and delivery destination. Sales will confirm a quote after reviewing the specification.'; $intents[] = 'quote';
        }
        if (!$specific_found && self::matches($text, 'samples?|prototype|mau thu|mau that|xem mau')) {
            $parts[] = $vi ? 'Sales có thể xem yêu cầu mẫu thử theo thiết kế của bạn và xác nhận khả năng làm mẫu, chi phí cùng thời gian; mình chưa thể hứa mẫu miễn phí.' : 'Sales can review a sample request for your design and confirm availability, sample cost and timing. Free samples are not confirmed.'; $intents[] = 'sample';
        }
        if (!$specific_found && self::matches($text, 'logo|print(?:ing|ed)?|artwork|design|thiet ke')) {
            $parts[] = $vi ? 'VPN nhận yêu cầu in logo và thiết kế riêng. Cách in và màu sắc sẽ được xác nhận theo giấy và artwork; chat hiện chỉ nhận văn bản.' : 'VPN accepts custom logo printing and design requests. Print method and colors depend on the selected paper and artwork; this chat currently accepts text only.'; $intents[] = 'printing';
        }
        if (!$specific_found && self::matches($text, 'lead time|production time|turnaround|how long|deadline|urgent(?:ly)?|asap|tomorrow|next week|this week|before my launch|thoi gian|bao lau|gap|kip|ngay mai|tuan nay|tuan sau')) {
            $parts[] = $vi ? 'Lịch làm mẫu, sản xuất và vận chuyển cần sales xác nhận theo thiết kế, số lượng và hạn nhận hàng của bạn. Bạn cần nhận hàng vào ngày nào?' : 'Sample, production and transit timing must be confirmed by sales for your design, quantity and required delivery date. When do you need the order?'; $intents[] = 'timing';
        }
        if (!$specific_found && self::matches($text, 'ship(?:ping)?|deliver(?:y)?|freight|export|van chuyen|giao hang|xuat khau')) {
            $parts[] = $vi ? 'Sales sẽ kiểm tra phương án và phí vận chuyển theo số lượng, kích thước và địa chỉ nhận hàng; mình chưa thể xác nhận phí hoặc ngày đến.' : 'Sales will check shipping options and cost using order quantity, dimensions and destination. Freight cost and arrival date are not confirmed yet.'; $intents[] = 'shipping';
        }
        if (!$specific_found && self::matches($text, 'fsc|iso|certif(?:ied|icate|ication)|food(?: safe| grade| contact)?|recycl(?:able|ed)|eco|gsm|material|paper type|chung nhan|an toan|thuc pham|tai che|chat lieu|dinh luong')) {
            $parts[] = $vi ? 'Giấy và thông số sẽ được chọn theo mục đích sử dụng. Chứng nhận, an toàn tiếp xúc thực phẩm, khả năng tái chế hoặc tải trọng cần tài liệu và kiểm tra cho đúng mẫu. Bạn định đựng sản phẩm gì?' : 'Paper and specifications are selected for the intended use. Certification, food-contact safety, recyclability and load capacity need product-specific evidence and checks. What will the packaging hold?'; $intents[] = 'specification';
        }
        if (!$specific_found && self::matches($text, 'manufacturer|factory|where (?:are|is) (?:you|the factory)|location|based in|nha may|o dau')) {
            $parts[] = $vi ? 'VPN Packaging là nhà sản xuất bao bì giấy tại Việt Nam, nhận yêu cầu túi giấy và hộp giấy theo thiết kế riêng.' : 'VPN Packaging is a paper packaging manufacturer in Vietnam, handling custom paper bag and paper box requests.'; $intents[] = 'manufacturer';
        }
        if (!$specific_found && self::matches($text, 'contact|email|phone|whatsapp|lien he|dien thoai')) {
            $parts[] = $vi ? 'Bạn có thể gửi nhu cầu ngay trong hội thoại này để sales tiếp nhận.' : 'You can share your packaging requirements in this conversation for sales to review.'; $intents[] = 'contact';
        }
        if ($d['quantity'] > 0 && $d['quantity'] < 1000 && !in_array('moq', $intents, true)) {
            $parts[] = $vi ? 'Số lượng bạn nêu thấp hơn MOQ chung để lên kế hoạch là 1.000 chiếc; sales cần kiểm tra khả năng nhận đơn, mình chưa thể xác nhận.' : 'Your quantity is below our general planning minimum of 1,000 units. Sales needs to check whether this request can be accepted; it is not confirmed.'; $intents[] = 'below_moq';
        }
        if (!$parts) {
            if (self::matches($text, 'thanks|thank you|cam on')) { $parts[] = $vi ? 'Cảm ơn bạn! Các thông tin đã gửi sẽ được giữ trong hội thoại để sales xem.' : 'Thank you! Your details stay in this conversation for sales to review.'; $intents[] = 'thanks'; }
            elseif (self::matches($text, 'hello|hi|hey|good morning|good afternoon|xin chao|chao')) { $parts[] = $vi ? 'Chào bạn! Mình có thể hỗ trợ các câu hỏi cơ bản về túi giấy và hộp giấy của VPN.' : 'Hi! I can help with basic questions about VPN paper bags and paper boxes.'; $intents[] = 'greeting'; }
            elseif ($current['product'] || $current['quantity'] || $current['size'] || $current['destination'] || $current['email'] || $current['company'] || ($d['quantity'] && preg_match('/^[0-9,.k\s]+$/i', $text)) || (count($history)>1 && $history[count($history)-2]['sender']==='bot' && self::matches(self::normalize($history[count($history)-2]['body']),'which country|quoc gia|company or project name|ten cong ty') && !str_contains($body,'?') && preg_match("/^[\p{L} .'-]{2,100}$/u",trim($body)))) { $parts[] = $vi ? 'Mình đã ghi nhận thông tin bạn gửi để sales kiểm tra.' : 'Your information is saved for sales to review.'; $intents[] = 'details'; }
            else { $parts[] = $vi ? 'Mình chưa nhận diện được câu hỏi này. Bạn có thể hỏi về giá, MOQ, mẫu thử, in logo, vận chuyển hoặc yêu cầu gặp nhân viên.' : 'I could not match that question. You can ask about pricing, MOQ, samples, logo printing or shipping, or ask to speak to a team member.'; $intents[] = 'fallback'; }
        }
        // A multi-topic question stays concise; invite remaining details rather than a wall of text.
        $parts = array_slice($parts, 0, 3);
        if (!str_contains(implode(' ',$parts),'?') && !array_intersect($intents,['size_help','artwork','invalid_email','leaving'])) { $parts[] = self::next_question($d, $vi); }
        $invitation = self::lead_invitation($d, $vi, $intents);
        if ($invitation !== '') { $parts[] = $invitation; }
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
