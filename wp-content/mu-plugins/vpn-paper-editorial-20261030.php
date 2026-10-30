<?php
/**
 * Plugin Name: VPN Paper Bag Buying Guides — Approved V2
 * Description: Imports the three reviewed guides and 14 images as drafts on the first administrator visit after deployment.
 * Version: 2026.10.30.2
 */
defined('ABSPATH') || exit;
const VPN_PE30_BATCH = 'paper-bag-buying-20261030';
const VPN_PE30_VERSION = '2026-10-30.v2';
const VPN_PE30_OPTION = 'vpn_paper_editorial_20261030_done';

/** All paths and content come from this fixed reviewed package, never a request. */
function vpn_pe30_package(): array {
    $dir = __DIR__ . '/vpn-paper-editorial-20261030';
    $data = json_decode((string) file_get_contents($dir . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $expected = ['supplier'=>'choose-paper-bag-supplier-bulk-orders','bakery'=>'sos-paper-bags-vs-baguette-bags','approval'=>'paper-bag-sample-approval'];
    if (($data['batch_key'] ?? '') !== VPN_PE30_BATCH || ($data['revision'] ?? '') !== VPN_PE30_VERSION
        || ($data['status'] ?? '') !== 'draft' || count($data['articles'] ?? []) !== 3) {
        throw new RuntimeException('Invalid paper bag editorial release.');
    }
    $keys = [];
    foreach ($data['articles'] as &$row) {
        $key = (string) ($row['key'] ?? '');
        if (!isset($expected[$key]) || isset($keys[$key]) || $row['slug'] !== $expected[$key]
            || $row['status'] !== 'draft' || count($row['images']) !== ('bakery' === $key ? 4 : 5)) {
            throw new RuntimeException('Unexpected article in editorial release.');
        }
        $keys[$key] = true;
        $source = str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($dir . '/' . $key . '/article.html'));
        if (!hash_equals($row['content_sha256'], hash('sha256', $source)) || preg_match('~<h1\b|localhost|file://|C:[/\\\\]~i', $source)) {
            throw new RuntimeException('Article checksum or portable-content validation failed: ' . $key);
        }
        $slots = []; $featured = 0;
        foreach ($row['images'] as $image) {
            $file = (string) $image['filename'];
            if (basename($file) !== $file) { throw new RuntimeException('Invalid editorial asset path.'); }
            $path = $dir . '/' . $key . '/images/' . $file;
            $size = is_file($path) ? wp_getimagesize($path) : false;
            if (!$size || ($size['mime'] ?? '') !== 'image/webp' || $size[0] !== $image['width'] || $size[1] !== $image['height']
                || filesize($path) !== $image['bytes'] || !hash_equals($image['sha256'], hash_file('sha256', $path))) {
                throw new RuntimeException('Image checksum or dimensions failed: ' . $file);
            }
            if ($image['role'] === 'featured' && null === $image['slot']) { $featured++; }
            elseif ($image['role'] === 'inline') {
                $slot = (int) $image['slot'];
                if ($slot < 1 || $slot > 4 || isset($slots[$slot]) || substr_count($source, '<!-- IMAGE_SLOT_' . $slot . ' -->') !== 1) {
                    throw new RuntimeException('Missing or repeated image slot: ' . $key);
                }
                $slots[$slot] = true;
            } else { throw new RuntimeException('Invalid editorial image role.'); }
        }
        if ($featured !== 1 || count($slots) !== count($row['images'])-1
            || preg_match_all('/<!-- IMAGE_SLOT_\d+ -->/', $source) !== count($slots)) {
            throw new RuntimeException('Editorial image inventory incomplete.');
        }
        $row['source'] = $source;
    }
    unset($row);
    return $data;
}

function vpn_pe30_find(array $row): ?WP_Post {
    global $wpdb;
    $ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type NOT IN ('attachment','revision')", $row['slug']));
    if (count($ids) > 1) { throw new RuntimeException('Duplicate slug requires review: ' . $row['slug']); }
    $post = $ids ? get_post((int) $ids[0]) : null;
    if ($post && ($post->post_type !== 'post' || get_post_meta($post->ID, '_vpn_editorial_batch', true) !== VPN_PE30_BATCH)) {
        throw new RuntimeException('Unrelated existing slug preserved: ' . $row['slug']);
    }
    return $post;
}

/** Validate content produced by this importer before setting the completion marker. */
function vpn_pe30_verify(int $id, array $row, array $media): void {
    $post = get_post($id);
    if (!$post || $post->post_status !== 'draft' || $post->post_title !== $row['title'] || $post->post_name !== $row['slug']
        || $post->post_excerpt !== $row['excerpt'] || substr_count($post->post_content, '<figure') !== count($row['images'])-1
        || strpos($post->post_content, 'IMAGE_SLOT') !== false || strpos($post->post_content, 'localhost') !== false && !in_array(wp_parse_url(home_url(), PHP_URL_HOST), ['localhost','127.0.0.1'], true)) {
        throw new RuntimeException('Editorial post readback failed: ' . $row['slug']);
    }
    if ((int) get_post_thumbnail_id($id) !== $media[0]) { throw new RuntimeException('Featured image readback failed.'); }
    foreach (['rank_math_title'=>'seo_title','rank_math_description'=>'meta_description','rank_math_focus_keyword'=>'focus_keyword'] as $meta=>$field) {
        if (get_post_meta($id,$meta,true) !== $row[$field]) { throw new RuntimeException('SEO readback failed: ' . $meta); }
    }
    foreach ($row['images'] as $i=>$image) {
        $file = get_attached_file($media[$i]);
        if (!$file || !is_file($file) || !hash_equals($image['sha256'], hash_file('sha256',$file))
            || get_post_meta($media[$i], '_wp_attachment_image_alt', true) !== $image['alt']
            || get_post_field('post_title',$media[$i]) !== $image['title'] || wp_get_attachment_caption($media[$i]) !== $image['caption']) {
            throw new RuntimeException('Imported image readback failed: ' . $image['filename']);
        }
    }
}

function vpn_pe30_import_one(array $row): array {
    $post = vpn_pe30_find($row);
    // Completed articles, including later publishing, unpublishing or editing, are never overwritten.
    if ($post && get_post_meta($post->ID,'_vpn_editorial_import_complete',true)) {
        if (get_post_meta($post->ID,'_vpn_editorial_source_hash',true) !== $row['content_sha256']) {
            throw new RuntimeException('A different completed article revision exists; preserved: ' . $row['slug']);
        }
        return ['id'=>$post->ID,'status'=>$post->post_status,'result'=>'existing completed article preserved'];
    }
    if ($post && ($post->post_status !== 'draft' || $post->post_title !== $row['title'] || $post->post_excerpt !== $row['excerpt']
        || get_post_meta($post->ID,'_vpn_editorial_source_hash',true) !== $row['content_sha256']
        || ($post->post_content !== '' && hash('sha256',$post->post_content) !== get_post_meta($post->ID,'_vpn_pe30_render_hash',true)))) {
        throw new RuntimeException('Interrupted article was edited; preserved: ' . $row['slug']);
    }
    $id = $post ? (int) $post->ID : wp_insert_post(wp_slash([
        'post_type'=>'post','post_status'=>'draft','post_name'=>$row['slug'],'post_title'=>$row['title'],
        'post_excerpt'=>$row['excerpt'],'post_content'=>'','post_author'=>get_current_user_id(),'comment_status'=>'closed',
        'meta_input'=>['_vpn_editorial_batch'=>VPN_PE30_BATCH,'_vpn_editorial_source_hash'=>$row['content_sha256']],
    ]), true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    $html = $row['source']; $media = [];
    foreach ($row['images'] as $image) {
        $asset = VPN_PE30_BATCH . '/' . $row['key'] . '/' . $image['filename'];
        $found = get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>2,'meta_key'=>'_vpn_editorial_asset','meta_value'=>$asset]);
        if (count($found) > 1) { throw new RuntimeException('Duplicated editorial asset marker; review required.'); }
        $mid = $found ? (int) $found[0]->ID : 0;
        if (!$mid) {
            $temp = wp_tempnam($image['filename']);
            if (!$temp || !copy(__DIR__.'/vpn-paper-editorial-20261030/'.$row['key'].'/images/'.$image['filename'],$temp)) {
                if ($temp) { wp_delete_file($temp); } throw new RuntimeException('Could not stage editorial image.');
            }
            $mid = media_handle_sideload(['name'=>$image['filename'],'tmp_name'=>$temp],$id,null,[
                'post_title'=>$image['title'],'post_excerpt'=>$image['caption'],'post_content'=>'Provenance: '.$image['origin'],
                'post_author'=>get_current_user_id(),'meta_input'=>['_vpn_editorial_asset'=>$asset,'_wp_attachment_image_alt'=>$image['alt']],
            ]);
            if (is_wp_error($mid)) { wp_delete_file($temp); throw new RuntimeException($mid->get_error_message()); }
        }
        $media[] = (int) $mid;
        $file = get_attached_file($mid);
        if (!$file || !is_file($file) || !hash_equals($image['sha256'],hash_file('sha256',$file))) {
            throw new RuntimeException('Existing editorial image missing or changed; preserved.');
        }
        if ($image['role'] === 'featured') { set_post_thumbnail($id,$mid); continue; }
        $img = wp_get_attachment_image($mid,'full',false,['alt'=>$image['alt'],'loading'=>'lazy','decoding'=>'async','sizes'=>'(max-width: 800px) 100vw, 850px']);
        $figure = '<figure class="wp-caption alignnone"><a href="'.esc_url(wp_get_attachment_url($mid)).'" target="_blank" rel="noopener" aria-label="'.esc_attr('View full illustration: '.$image['title']).'">'.$img.'</a><figcaption class="wp-caption-text">'.esc_html($image['caption']).'</figcaption></figure>';
        $html = str_replace('<!-- IMAGE_SLOT_'.$image['slot'].' -->',$figure,$html);
    }
    $html = str_replace('https://hopgiayvpn.com/',trailingslashit(home_url()),$html);
    if (strpos($html,'IMAGE_SLOT') !== false) { throw new RuntimeException('Unresolved editorial image slot.'); }
    $term = get_term_by('slug',$row['category']['slug'],'category');
    if (!$term) {
        $term = wp_insert_term($row['category']['name'],'category',['slug'=>$row['category']['slug']]);
        if (is_wp_error($term)) { throw new RuntimeException($term->get_error_message()); }
        $category = (int) $term['term_id'];
    } else { $category = (int) $term->term_id; }
    foreach (['rank_math_title'=>'seo_title','rank_math_description'=>'meta_description','rank_math_focus_keyword'=>'focus_keyword'] as $meta=>$field) {
        $old = get_post_meta($id,$meta,true);
        if ($old !== '' && $old !== $row[$field]) { throw new RuntimeException('Interrupted draft SEO was edited; preserved.'); }
        update_post_meta($id,$meta,wp_slash($row[$field]));
    }
    update_post_meta($id,'rank_math_facebook_image',wp_get_attachment_url($media[0]));
    update_post_meta($id,'rank_math_facebook_image_id',(string)$media[0]);
    update_post_meta($id,'_vpn_pe30_render_hash',hash('sha256',$html));
    $saved = wp_update_post(wp_slash(['ID'=>$id,'post_content'=>$html,'post_category'=>[$category]]),true);
    if (is_wp_error($saved)) { throw new RuntimeException($saved->get_error_message()); }
    if (get_post_field('post_content',$id) !== $html) { throw new RuntimeException('Content changed during save; draft retained for review.'); }
    vpn_pe30_verify($id,$row,$media);
    update_post_meta($id,'_vpn_pe30_media',$media);
    update_post_meta($id,'_vpn_editorial_import_complete',1);
    return ['id'=>(int)$id,'status'=>'draft','media'=>$media,'result'=>'imported and verified'];
}

/** Called only as an existing administrator; no credentials, impersonation or public endpoint. */
function vpn_pe30_run(): array {
    if (!current_user_can('manage_options')) { throw new RuntimeException('Administrator required.'); }
    if (!in_array('seo-by-rank-math/rank-math.php',(array)get_option('active_plugins'),true)) {
        throw new RuntimeException('Expected Rank Math plugin is not active.');
    }
    $data = vpn_pe30_package();
    require_once ABSPATH.'wp-admin/includes/file.php';
    require_once ABSPATH.'wp-admin/includes/media.php';
    require_once ABSPATH.'wp-admin/includes/image.php';
    $results = [];
    foreach ($data['articles'] as $row) {
        try { $results[$row['key']] = vpn_pe30_import_one($row); }
        catch (Throwable $error) { $results[$row['key']] = ['error'=>$error->getMessage()]; }
    }
    return $results;
}

function vpn_pe30_deploy(): void {
    if (get_option(VPN_PE30_OPTION) === VPN_PE30_VERSION || !current_user_can('manage_options') || wp_doing_ajax()
        || (defined('WP_INSTALLING') && WP_INSTALLING) || (defined('VPN_PE30_SKIP_AUTO') && VPN_PE30_SKIP_AUTO)) { return; }
    $host = strtolower((string) wp_parse_url(home_url(),PHP_URL_HOST));
    if (!in_array($host,['hopgiayvpn.com','www.hopgiayvpn.com'],true)) { return; }
    if ((int)get_option('vpn_pe30_retry',0) > time()) { return; }
    global $wpdb;
    $lock = substr($wpdb->prefix.'vpn_pe30_release',0,64);
    if ((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',$lock)) !== '1') { return; }
    try {
        @set_time_limit(180);
        $results = vpn_pe30_run();
        update_option('vpn_pe30_results',$results,false);
        if (count($results) !== 3 || array_filter($results,static function($r){return isset($r['error']);})) {
            throw new RuntimeException('Some guides need review; see per-article results.');
        }
        update_option(VPN_PE30_OPTION,VPN_PE30_VERSION,false);
        delete_option('vpn_pe30_error'); delete_option('vpn_pe30_retry');
        set_transient('vpn_pe30_notice',true,120);
    } catch (Throwable $error) {
        update_option('vpn_pe30_error',$error->getMessage(),false);
        update_option('vpn_pe30_retry',time()+300,false);
    } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock)); }
}
add_action('admin_init','vpn_pe30_deploy',100);
add_action('admin_notices',static function():void {
    if (!current_user_can('manage_options')) { return; }
    if ($error = get_option('vpn_pe30_error')) {
        $details = [];
        foreach ((array)get_option('vpn_pe30_results',[]) as $key=>$row) { if(isset($row['error'])){$details[]=$key.': '.$row['error'];} }
        echo '<div class="notice notice-error"><p>'.esc_html('Paper bag guides: '.$error.' '.implode(' | ',$details).' Retry after five minutes; existing posts are preserved.').'</p></div>';
    } elseif (get_transient('vpn_pe30_notice')) {
        delete_transient('vpn_pe30_notice');
        echo '<div class="notice notice-success is-dismissible"><p>Paper bag guides: three articles synced. Open Posts to review their current status.</p></div>';
    }
});
