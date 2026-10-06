<?php
defined('ABSPATH') || exit;
final class VPN_Chat_Profiles {
    public static function boot(): void {
        add_action('admin_post_vpn_chat_profile',[self::class,'save']);
        add_action('admin_enqueue_scripts',static function($hook){
            if(!in_array($hook,['vpn-live-chat_page_vpn-chat-profile','vpn-live-chat_page_vpn-chat-settings'],true))return;
            if(current_user_can('upload_files')){
                wp_enqueue_media();
                wp_enqueue_script('vpn-chat-profiles',plugins_url('assets/profiles.js',VPN_CHAT_FILE),[],VPN_CHAT_VERSION,true);
            }
        });
    }
    public static function picker(string $field): void {
        if(current_user_can('upload_files'))echo ' <button type="button" class="button vpn-chat-avatar-picker" data-field="'.esc_attr($field).'">Chọn / tải ảnh</button>';
        else echo '<small> Chọn ảnh tải lên bên dưới hoặc dùng ID ảnh đã có do admin cung cấp.</small>';
    }
    public static function screen(): void {
        if(!current_user_can('vpn_chat_agent'))return;
        $id=current_user_can('vpn_chat_manage') ? (absint($_GET['user_id']??0) ?: get_current_user_id()) : get_current_user_id();
        $user=get_user_by('id',$id);
        if(!$user || !user_can($user,'vpn_chat_agent'))wp_die('Không phải tài khoản sales');
        $meta=(array)get_user_meta($id,'vpn_chat_profile',true);
        echo '<div class="wrap"><h1>Profile nhân viên chat</h1><p>Tên và ảnh được xác định từ tài khoản đã đăng nhập. Tin mới lưu bản chụp profile; sửa tên không đổi người gửi trong lịch sử đã lưu.</p>';
        if(current_user_can('vpn_chat_manage')){
            echo '<form method="get"><input type="hidden" name="page" value="vpn-chat-profile"><label>Nhân viên <select name="user_id">';
            foreach(get_users(['capability'=>'vpn_chat_agent','number'=>100]) as $agent)echo '<option value="'.(int)$agent->ID.'" '.selected($id,$agent->ID,false).'>'.esc_html($agent->display_name).'</option>';
            echo '</select></label> ';submit_button('Chọn profile','secondary','submit',false);echo '</form>';
        }
        echo '<form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="vpn_chat_profile"><input type="hidden" name="user_id" value="'.$id.'">';
        wp_nonce_field('vpn_chat_profile');
        echo '<p><label>Tên hiển thị<br><input name="profile_name" maxlength="100" required value="'.esc_attr($meta['name']??$user->display_name).'"></label></p><p><label>Avatar (ID ảnh trong Media Library)<br><input id="vpn-profile-avatar" name="avatar_id" type="number" min="0" value="'.absint($meta['avatar_id']??0).'"></label>';
        self::picker('vpn-profile-avatar');
        $url=self::avatar((int)($meta['avatar_id']??0));
        if($url)echo '<br><img src="'.esc_url($url).'" alt="Avatar hiện tại" width="64" height="64" style="border-radius:50%;object-fit:cover;margin-top:12px">';
        echo '</p><p><label>Tải avatar mới (JPG, PNG, WebP; tối đa 2 MB)<br><input type="file" name="profile_avatar" accept="image/jpeg,image/png,image/webp"></label></p><p>Để ID = 0 và không chọn ảnh mới dùng avatar chữ. Profile hỗ trợ mặc định và chính sách dùng danh tính chung nằm ở Cấu hình.</p>';
        submit_button('Lưu profile');echo '</form></div>';
    }
    public static function save(): void {
        if(!current_user_can('vpn_chat_agent'))wp_die('Không có quyền',403);
        check_admin_referer('vpn_chat_profile');
        $id=absint($_POST['user_id']??0);
        if($id!==get_current_user_id() && !current_user_can('vpn_chat_manage'))wp_die('Không có quyền',403);
        $user=get_user_by('id',$id);
        if(!$user || !user_can($user,'vpn_chat_agent'))wp_die('Không phải tài khoản sales');
        $name=sanitize_text_field(mb_substr((string)wp_unslash($_POST['profile_name']??''),0,100));
        $avatar=absint($_POST['avatar_id']??0);
        if(!$name || ($avatar && !self::avatar($avatar)))wp_die('Tên hoặc ảnh không hợp lệ',400);
        if(isset($_FILES['profile_avatar']) && (int)$_FILES['profile_avatar']['error']!==UPLOAD_ERR_NO_FILE){
            $file=$_FILES['profile_avatar'];
            if((int)$file['error']!==UPLOAD_ERR_OK || (int)$file['size']>2*1024*1024)wp_die('Ảnh không hợp lệ hoặc vượt quá 2 MB',400);
            $image=@getimagesize($file['tmp_name']);
            if(!$image || !in_array($image['mime']??'',['image/jpeg','image/png','image/webp'],true) || $image[0]*$image[1]>12000000)wp_die('Chỉ nhận ảnh JPG/PNG/WebP hợp lệ, tối đa 12 megapixel',400);
            require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/image.php';require_once ABSPATH.'wp-admin/includes/media.php';
            $uploaded=media_handle_upload('profile_avatar',0,[],['test_form'=>false,'mimes'=>['jpg|jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp']]);
            if(is_wp_error($uploaded))wp_die('Không tải được ảnh');
            $avatar=(int)$uploaded;
        }
        update_user_meta($id,'vpn_chat_profile',['name'=>$name,'avatar_id'=>$avatar]);
        VPN_Chat_Store::audit('profile',$id);
        wp_safe_redirect(admin_url('admin.php?page=vpn-chat-profile&user_id='.$id));exit;
    }
    public static function avatar(int $id): string {
        return $id && wp_attachment_is_image($id) ? (string)(wp_get_attachment_image_url($id,'thumbnail') ?: '') : '';
    }
    public static function make(string $name,int $avatar): array {
        $name=sanitize_text_field(mb_substr($name,0,100));
        $name=$name ?: 'VPN Packaging';
        $parts=preg_split('/\s+/u',$name,-1,PREG_SPLIT_NO_EMPTY);
        $initials=mb_strtoupper(mb_substr($parts[0],0,1).(count($parts)>1 ? mb_substr(end($parts),0,1) : ''));
        return ['name'=>$name,'avatar'=>self::avatar($avatar),'initials'=>$initials];
    }
    public static function support(): array {
        $s=VPN_Chat_Settings::get();
        $profile=self::make($s['support_name'],(int)$s['support_avatar_id']);
        if(!$profile['avatar'] && $s['support_name']==='Tho Nguyen')$profile['avatar']=plugins_url('assets/tho-nguyen.png',VPN_CHAT_FILE);
        return $profile;
    }
    public static function user(int $id): array {
        $s=VPN_Chat_Settings::get();
        if($s['shared_identity'])return self::support();
        $user=get_user_by('id',$id);
        if(!$user)return self::make('Former team member',0);
        $profile=(array)get_user_meta($id,'vpn_chat_profile',true);
        return self::make((string)($profile['name']??$user->display_name),(int)($profile['avatar_id']??0));
    }
}
