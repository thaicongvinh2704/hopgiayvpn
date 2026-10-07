<?php
defined('ABSPATH') || exit;
final class VPN_Chat_UI {
    public static function boot(): void {
        add_action('wp_enqueue_scripts', [self::class,'widget']);
        add_action('admin_menu', [self::class,'menu']);
        add_action('admin_enqueue_scripts', [self::class,'assets']);
        add_action('admin_post_vpn_chat_settings', [self::class,'save']);
        add_filter('woocommerce_prevent_admin_access', [self::class,'woo_access']);
        add_filter('login_redirect', [self::class,'login_redirect'],10,3);
    }
    public static function woo_access(bool $prevent): bool {
        global $pagenow;
        $page=(string)($_GET['page']??'');
        if ($pagenow==='admin.php' && ((in_array($page,['vpn-live-chat','vpn-chat-profile'],true) && current_user_can('vpn_chat_agent')) || ($page==='vpn-chat-settings' && current_user_can('vpn_chat_manage')))) { return false; }
        return $prevent;
    }
    public static function login_redirect(string $redirect,string $requested,$user): string {
        if ($user instanceof WP_User && user_can($user,'vpn_chat_agent') && !user_can($user,'edit_posts') && ($requested==='' || untrailingslashit($requested)===untrailingslashit(admin_url()))) {
            return admin_url('admin.php?page=vpn-live-chat');
        }
        return $redirect;
    }
    public static function widget(): void {
        $s = VPN_Chat_Settings::get();
        // The enabled widget covers every frontend page, including legacy pilot installs.
        if (!$s['widget'] || is_admin()) { return; }
        wp_enqueue_style('vpn-chat-launcher',plugins_url('assets/launcher.css',VPN_CHAT_FILE),[],VPN_CHAT_VERSION);
        wp_enqueue_style('vpn-chat-widget',plugins_url('assets/chat.css',VPN_CHAT_FILE),['vpn-chat-launcher'],VPN_CHAT_VERSION);
        wp_enqueue_script('vpn-chat-widget',plugins_url('assets/widget.js',VPN_CHAT_FILE),[],VPN_CHAT_VERSION,true);
        wp_enqueue_script('vpn-chat-launcher',plugins_url('assets/launcher.js',VPN_CHAT_FILE),['vpn-chat-widget'],VPN_CHAT_VERSION,true);
        wp_add_inline_script('vpn-chat-launcher','window.VPNChatLauncher=' . wp_json_encode(['api'=>rest_url('vpn-chat/v1/'), 'bundle'=>add_query_arg('ver',VPN_CHAT_VERSION,plugins_url('assets/widget.js',VPN_CHAT_FILE)),'css'=>add_query_arg('ver',VPN_CHAT_VERSION,plugins_url('assets/chat.css',VPN_CHAT_FILE)), 'presence'=>$s['always_online']?'online':'offline','bottom'=>(int)$s['bottom_offset'],'fallback_url'=>$s['fallback_url'],'support'=>VPN_Chat_Profiles::support(),'greeting_enabled'=>(bool)$s['greeting_enabled'],'greeting_text'=>$s['greeting_text']]) . ';','before');
    }
    public static function menu(): void {
        add_menu_page('VPN Live Chat','VPN Live Chat','vpn_chat_agent','vpn-live-chat',[self::class,'inbox'],'dashicons-format-chat',58);
        add_submenu_page('vpn-live-chat','Cấu hình','Cấu hình','vpn_chat_manage','vpn-chat-settings',[self::class,'settings']);
        add_submenu_page('vpn-live-chat','Profile nhân viên','Profile nhân viên','vpn_chat_agent','vpn-chat-profile',[VPN_Chat_Profiles::class,'screen']);
    }
    public static function assets(string $hook): void {
        if ($hook !== 'toplevel_page_vpn-live-chat') { return; }
        wp_enqueue_style('vpn-chat-admin',plugins_url('assets/admin.css',VPN_CHAT_FILE),[],VPN_CHAT_VERSION);
        wp_enqueue_script('vpn-chat-admin',plugins_url('assets/admin.js',VPN_CHAT_FILE),[],VPN_CHAT_VERSION,true);
        $agents = get_users(['capability'=>'vpn_chat_agent','fields'=>['ID','display_name'],'number'=>100]);
        wp_add_inline_script('vpn-chat-admin','window.VPNChatAdmin=' . wp_json_encode(['api'=>rest_url('vpn-chat/v1/'),'nonce'=>wp_create_nonce('wp_rest'),'manager'=>current_user_can('vpn_chat_manage'),'user'=>get_current_user_id(),'agents'=>$agents]) . ';','before');
    }
    public static function inbox(): void {
        if (!current_user_can('vpn_chat_agent')) { return; }
        echo '<div id="vpn-chat-admin" class="wrap"><div class="vpn-inbox-heading"><div><span class="vpn-section-eyebrow">VPN PACKAGING · LIVE CHAT</span><h1>Hộp thư khách hàng</h1></div><div class="vpn-toolbar"><label class="vpn-presence-label">Trạng thái <select id="vpn-presence"><option value="offline">Offline</option><option value="available">Online</option><option value="away">Tạm vắng</option></select></label><button type="button" id="vpn-notify" title="Bật âm thanh và thông báo">Thông báo</button><span id="vpn-admin-error" role="status" aria-live="polite"></span></div></div><div class="vpn-workspace"><aside class="vpn-sidebar"><div class="vpn-sidebar-tools"><label class="vpn-visually-hidden" for="vpn-search">Tìm khách</label><div class="vpn-search-box"><input id="vpn-search" maxlength="100" type="search" placeholder="Tìm tên hoặc email…"><button id="vpn-search-button" type="button" aria-label="Tìm khách">⌕</button></div><label class="vpn-visually-hidden" for="vpn-filter">Hộp thư</label><select id="vpn-filter"><option value="inbox">Tin nhắn</option><option value="unassigned">Khách mới</option><option value="mine">Của tôi</option><option value="follow_up">Cần theo dõi</option><option value="closed">Đã đóng</option><option value="spam">Spam</option>';
        if (current_user_can('vpn_chat_manage')) { echo '<option value="all">Tất cả</option>'; }
        echo '</select></div><div id="vpn-list" aria-label="Danh sách khách"></div><div class="vpn-pagination"><button id="vpn-prev" type="button" aria-label="Trang trước">‹</button><span id="vpn-page"></span><button id="vpn-next" type="button" aria-label="Trang sau">›</button></div></aside><div id="vpn-empty" class="vpn-empty"><div class="vpn-empty-card"><span class="vpn-section-eyebrow">MỖI TIN NHẮN LÀ MỘT CƠ HỘI</span><div class="vpn-empty-art" aria-hidden="true"><span class="vpn-art-bubble">Xin chào! 👋</span><span class="vpn-art-reply">VPN Packaging sẵn sàng hỗ trợ.</span></div><h2>Bắt đầu một cuộc trò chuyện</h2><p>Chọn khách bên trái để xem nhu cầu và trả lời ngay.<br>Tin chưa đọc được đánh dấu vàng để bạn dễ nhận ra.</p><button id="vpn-open-next" type="button">Mở hội thoại để trả lời <span aria-hidden="true">→</span></button><span class="vpn-empty-hint">Chọn khách · Nhập tin · Nhấn Enter</span></div></div><main id="vpn-detail" hidden><header class="vpn-conversation-header"><button id="vpn-back" type="button" aria-label="Quay lại danh sách khách">‹</button><div id="vpn-customer"></div><button id="vpn-info-toggle" type="button" aria-expanded="false" aria-controls="vpn-conversation-info">Chi tiết</button>' . (current_user_can('vpn_chat_manage') ? '<button id="vpn-delete" type="button" disabled>Xóa hội thoại</button>' : '') . '</header><details id="vpn-conversation-info"><summary>Thông tin khách &amp; quản lý hội thoại</summary><div id="vpn-customer-info"></div><p class="vpn-info-help">Email do khách cung cấp, chưa xác minh. Giờ hẹn theo UTC.</p><button type="button" id="vpn-claim">Nhận chat</button><div class="vpn-fields"><label>Phụ trách <select id="vpn-owner"></select></label><label>Trạng thái <select id="vpn-status"></select></label><label>Cơ hội <select id="vpn-label"></select></label><label>Hẹn theo dõi (UTC) <input id="vpn-follow" type="datetime-local"></label><label>Nhu cầu <textarea id="vpn-needs" maxlength="2000"></textarea></label></div><button type="button" id="vpn-save">Lưu / chuyển</button><button type="button" id="vpn-read">Đánh dấu đã đọc</button>';
        if (current_user_can('vpn_chat_manage')) {
            echo '<details><summary>Dữ liệu &amp; chống spam</summary><label>Lý do chặn <input id="vpn-block-reason" maxlength="240"></label><button type="button" id="vpn-block">Chặn phiên 24 giờ</button><button type="button" id="vpn-export">Export JSON</button></details>';
        }
        echo '</details><div id="vpn-transcript" tabindex="0" role="log" aria-live="polite" aria-label="Lịch sử hội thoại"></div><button type="button" id="vpn-more" hidden>Tải tiếp</button><div class="vpn-reply-area"><form id="vpn-reply" hidden><div class="vpn-compose-line"><label class="vpn-visually-hidden" for="vpn-message">Tin nhắn</label><textarea id="vpn-message" placeholder="Nhập tin nhắn…" maxlength="2000" rows="1" required></textarea><button id="vpn-agent-send" type="submit" aria-label="Gửi tin nhắn">➤</button></div><div class="vpn-compose-options"><label class="vpn-visually-hidden" for="vpn-canned">Câu trả lời mẫu</label><select id="vpn-canned"><option value="">Câu trả lời mẫu</option></select><label><input id="vpn-note" type="checkbox"> Ghi chú riêng</label><span id="vpn-send-status" role="status"></span></div></form><p id="vpn-readonly" hidden>Hội thoại này chưa thể trả lời. Xem Chi tiết để cập nhật trạng thái.</p></div></main></div>';
        if (current_user_can('vpn_chat_manage')) {
            echo '<details class="vpn-admin-tools"><summary>Công cụ quản trị</summary><details><summary>Quản lý câu trả lời mẫu</summary><form id="vpn-canned-form"><label>ID để sửa (để trống tạo mới) <input id="vpn-canned-id" type="number" min="1"></label><label>Tiêu đề <input id="vpn-canned-title" maxlength="100" required></label><label>Nội dung <textarea id="vpn-canned-body" maxlength="2000" required></textarea></label><button>Lưu</button><button type="button" id="vpn-canned-delete">Xóa ID</button></form></details><details><summary>Tình trạng hệ thống &amp; danh sách chặn</summary><button type="button" id="vpn-health-refresh">Kiểm tra</button><pre id="vpn-health"></pre><div id="vpn-blocks"></div></details></details>';
        }
        echo '</div>';
    }
    public static function settings(): void {
        if (!current_user_can('vpn_chat_manage')) { return; }
        $s=VPN_Chat_Settings::get();
        echo '<div class="wrap"><h1>Cấu hình VPN Live Chat</h1><p>Ban đầu tắt. Chống spam bằng giới hạn tần suất trên máy chủ: 2 tin/giây, kèm giới hạn 30 giây, 5 phút và tạo chat mới. Không cần Turnstile. Retention 0 nghĩa là chưa tự xóa; chủ website cần chốt chính sách. Lịch trống nghĩa là ngoài giờ.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="vpn_chat_settings">';
        wp_nonce_field('vpn_chat_settings');
        echo '<h2>Danh tính hỗ trợ và lời chào</h2><p>Tên mặc định khi chưa nhận chat; khi đã nhận, header dùng profile người phụ trách. Mỗi tin sales lưu đúng profile của tài khoản gửi. Chỉ bật danh tính chung nếu đó là chính sách công ty.</p><p><label>Tên hỗ trợ mặc định <input name="support_name" maxlength="100" required value="'.esc_attr($s['support_name']).'"></label></p><p><label>Avatar hỗ trợ (Media Library ID) <input id="vpn-support-avatar" name="support_avatar_id" type="number" min="0" value="'.(int)$s['support_avatar_id'].'"></label>';
        VPN_Chat_Profiles::picker('vpn-support-avatar');
        echo '</p><p><label><input type="checkbox" name="shared_identity" value="1" '.checked($s['shared_identity'],true,false).'> Dùng danh tính hỗ trợ chung cho các tin mới của mọi sales</label></p><p><label><input type="checkbox" name="greeting_enabled" value="1" '.checked($s['greeting_enabled'],true,false).'> Hiện lời mời chat nổi bật với Sale Manager (animation nhẹ, có nút đóng)</label></p><p><label>Lời chào <input style="width:min(600px,100%)" name="greeting_text" maxlength="240" value="'.esc_attr($s['greeting_text']).'"></label></p>';
        foreach (['always_online'=>'Luôn hiển thị Online ở khung khách (không phụ thuộc ca trực)', 'widget'=>'Bật widget trên tất cả các trang phía khách','accept_new'=>'Nhận chat mới'] as $key=>$label) { echo '<p><label><input name="' . esc_attr($key) . '" type="checkbox" value="1" ' . checked($s[$key],true,false) . '> ' . esc_html($label) . '</label></p>'; }
        $fields=['fallback_url'=>'Link quote/email thực tế (https hoặc mailto)','sales_email'=>'Email đội sales nhận nhắc SLA','timezone'=>'Timezone IANA','offline_copy'=>'Thông báo ngoài giờ và SLA dự kiến','max_chars'=>'Ký tự mỗi tin (1–2000)','idle_hours'=>'Phiên idle (giờ, 1–168)','absolute_days'=>'Phiên tối đa (ngày, 1–30)','retention_days'=>'Tự xóa closed/spam sau ngày (0: chưa chốt)','sla_minutes'=>'SLA chưa nhận (phút)','short_limit'=>'Tin mỗi 30 giây','long_limit'=>'Tin mỗi 5 phút','conversation_limit'=>'Hội thoại mỗi 10 phút','heartbeat_seconds'=>'Heartbeat (giây)','presence_seconds'=>'Presence hết hạn (giây)','bottom_offset'=>'Khoảng đáy widget (px)'];
        foreach($fields as $key=>$label) { echo '<p><label>' . esc_html($label) . '<br><input style="width:min(600px,100%)" name="' . esc_attr($key) . '" value="' . esc_attr($s[$key]) . '"></label></p>'; }
        foreach(['holidays'=>'Ngày nghỉ YYYY-MM-DD, mỗi dòng một ngày'] as $key=>$label) { echo '<p><label>' . esc_html($label) . '<br><textarea rows="4" cols="65" name="' . esc_attr($key) . '">' . esc_textarea(implode("\n",$s[$key])) . '</textarea></label></p>'; }
        echo '<p><label>Lịch JSON: ngày ISO 1 (thứ Hai) đến 7. Ví dụ {"1":[["08:00","17:00"]]}<br><textarea rows="5" cols="65" name="hours">' . esc_textarea(wp_json_encode($s['hours'])) . '</textarea></label></p>';
        submit_button('Lưu cấu hình'); echo '</form><h2>Health</h2><pre>' . esc_html(wp_json_encode(VPN_Chat_Jobs::health(),JSON_PRETTY_PRINT)) . '</pre></div>';
    }
    public static function save(): void {
        if(!current_user_can('vpn_chat_manage')) { wp_die('Không có quyền',403); }
        check_admin_referer('vpn_chat_settings');
        $s=VPN_Chat_Settings::get(); $raw=wp_unslash($_POST);
        $s['support_name']=sanitize_text_field(mb_substr((string)($raw['support_name']??$s['support_name']),0,100)) ?: 'Tho Nguyen';
        $s['support_avatar_id']=absint($raw['support_avatar_id']??0);
        if($s['support_avatar_id'] && !VPN_Chat_Profiles::avatar($s['support_avatar_id']))wp_die('Avatar phải là ảnh trong Media Library');
        $s['shared_identity']=!empty($raw['shared_identity']);$s['greeting_enabled']=!empty($raw['greeting_enabled']);
        $s['greeting_text']=sanitize_text_field(mb_substr((string)($raw['greeting_text']??''),0,240));
        foreach(['always_online','widget','accept_new'] as $key) { $s[$key]=!empty($raw[$key]); }
        foreach(['offline_copy'] as $key) { $s[$key]=sanitize_text_field(mb_substr((string)($raw[$key]??''),0,500)); }
        $s['sales_email']=sanitize_email($raw['sales_email']??'');
        $s['fallback_url']=esc_url_raw($raw['fallback_url']??'', ['https','http','mailto']);
        if(!in_array($raw['timezone']??'',timezone_identifiers_list(),true)) { wp_die('Timezone không hợp lệ'); }
        $s['timezone']=$raw['timezone'];
        foreach(['max_chars'=>[1,2000],'idle_hours'=>[1,168],'absolute_days'=>[1,30],'retention_days'=>[0,3650],'sla_minutes'=>[1,1440],'short_limit'=>[1,100],'long_limit'=>[1,1000],'conversation_limit'=>[1,20],'heartbeat_seconds'=>[15,60],'presence_seconds'=>[45,300],'bottom_offset'=>[0,300]] as $key=>$range) { $s[$key]=max($range[0],min($range[1],(int)($raw[$key]??$s[$key]))); }
        $s['presence_seconds']=max($s['presence_seconds'],$s['heartbeat_seconds']*3);
        $s['holidays']=array_values(array_filter(preg_split('/\R/',(string)($raw['holidays']??'')), static fn($d)=>preg_match('/^\d{4}-\d{2}-\d{2}$/D',$d)));
        $hours=json_decode($raw['hours']??'[]',true);
        if(!is_array($hours)) { wp_die('Lịch JSON không hợp lệ'); }
        $valid=[];
        foreach($hours as $day=>$slots) {
            if((int)$day<1 || (int)$day>7 || !is_array($slots)) { wp_die('Ngày trực không hợp lệ'); }
            foreach($slots as $slot) {
                if(!is_array($slot) || count($slot)!==2 || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D',(string)$slot[0]) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D',(string)$slot[1]) || $slot[0]>=$slot[1]) { wp_die('Giờ trực không hợp lệ; chia ca qua đêm thành hai ngày'); }
                $valid[(int)$day][]=$slot;
            }
        }
        $s['hours']=$valid; update_option('vpn_chat_settings',$s,false);
        VPN_Chat_Store::audit('settings',0);
        wp_safe_redirect(admin_url('admin.php?page=vpn-chat-settings')); exit;
    }
}
