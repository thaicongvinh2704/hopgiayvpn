<?php
/** CLI-only local checks; --exercise-write tests and restores the six approved local mirrors. */
if ('cli' !== PHP_SAPI) { http_response_code(404); exit; }
define('VPN_PAPER_BLOG_CONTEXT_SKIP_AUTO', true);
require dirname(__DIR__) . '/wp-load.php';
if ('local' !== wp_get_environment_type() || 'localhost' !== wp_parse_url(home_url(),PHP_URL_HOST)) { throw new RuntimeException('Local only'); }
add_filter('pre_wp_mail','__return_false');
$exercise = in_array('--exercise-write',$argv,true);
$checks = array();
$data = vpn_paper_blog_context_data();
$count = serialize(wp_count_posts('post'));
foreach ($data['articles'] as $row) {
    $slug = $row['slug'];
    $fixture = '<p><span style="font-size: 110%">' . esc_html($row['anchor_text']) . '</span></p>' . "\n<p>Unrelated text stays intact.</p>";
    $patch = vpn_paper_blog_context_patch($fixture,$row);
    $checks[$slug . ':entity_and_nested_span_anchor'] = ($patch['status'] ?? '') === 'ready';
    $checks[$slug . ':duplicate_anchor_rejected'] = isset(vpn_paper_blog_context_patch($fixture . $fixture,$row)['error']);
    $checks[$slug . ':changed_anchor_rejected'] = isset(vpn_paper_blog_context_patch('<p>A different editorial paragraph.</p>',$row)['error']);
    $checks[$slug . ':changed_example_rejected'] = isset(vpn_paper_blog_context_patch('<p id="'.$row['paragraph_id'].'">An edited example.</p>'.$fixture,$row)['error']);
    $checks[$slug . ':block_content_preserved'] = isset(vpn_paper_blog_context_patch('<!-- wp:paragraph -->'.$fixture.'<!-- /wp:paragraph -->',$row)['error']);
    $again = vpn_paper_blog_context_patch($patch['content'],$row);
    $checks[$slug . ':pure_patch_idempotent'] = ($again['status'] ?? '') === 'already present' && $again['content'] === $patch['content'];
    $post = get_page_by_path($slug,OBJECT,'post');
    if (!$post || '20261026' !== get_post_meta($post->ID,'_vpn_local_paper_blog_mirror',true)) { throw new RuntimeException('Approved local mirror required: '.$slug); }
    $approved = $post->post_content;
    $meta = get_post_meta($post->ID);
    $terms = array(wp_get_post_terms($post->ID,'category',array('fields'=>'ids')),wp_get_post_terms($post->ID,'post_tag',array('fields'=>'ids')));
    $fields = array('post_title','post_name','post_excerpt','post_status','post_author','post_date','post_date_gmt');
    $addition = str_replace('{{HOME}}',untrailingslashit(home_url('/')),$row['addition']);
    if (1 !== substr_count($approved,$addition)) { throw new RuntimeException('Exactly one approved example required: '.$slug); }
    try {
        if ($exercise) {
            $before = str_replace(array("\r\n".$addition,"\n".$addition),'',$approved);
            $planned = vpn_paper_blog_context_patch($before,$row);
            if (($planned['status'] ?? '') !== 'ready' || $planned['content'] !== $approved) { throw new RuntimeException('Patch differs from approved copy: '.$slug); }
            $result = wp_update_post(wp_slash(array('ID'=>$post->ID,'post_content'=>$before,'post_date'=>$post->post_date,'post_date_gmt'=>$post->post_date_gmt,'edit_date'=>true)),true);
            if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
            $result = vpn_paper_blog_context_apply_one($row);
            $checks[$slug . ':real_sync_applies_exact_approved_copy'] = ($result['status'] ?? '') === 'applied' && get_post($post->ID)->post_content === $approved;
            $backup = get_option('vpn_paper_blog_context_20261026_before_'.$post->ID);
            $checks[$slug . ':before_state_backed_up'] = $backup['content'] === $before;
        }
        $result = vpn_paper_blog_context_apply_one($row);
        $checks[$slug . ':real_sync_does_not_duplicate'] = ($result['status'] ?? '') === 'already present' && get_post($post->ID)->post_content === $approved;
        $after = get_post($post->ID);
        $checks[$slug . ':metadata_and_images_unchanged'] = get_post_meta($post->ID) === $meta;
        $checks[$slug . ':categories_tags_unchanged'] = $terms === array(wp_get_post_terms($post->ID,'category',array('fields'=>'ids')),wp_get_post_terms($post->ID,'post_tag',array('fields'=>'ids')));
        foreach ($fields as $field) { $checks[$slug . ':' . $field . '_unchanged'] = $post->$field === $after->$field; }
    } finally {
        if (get_post($post->ID)->post_content !== $approved) {
            wp_update_post(wp_slash(array('ID'=>$post->ID,'post_content'=>$approved,'post_date'=>$post->post_date,'post_date_gmt'=>$post->post_date_gmt,'edit_date'=>true)));
        }
    }
}
$missing = $data['articles'][0]; $missing['slug'] = 'vpn-missing-context-test-never-create';
$checks['missing_post_not_created'] = isset(vpn_paper_blog_context_apply_one($missing)['error']) && !get_page_by_path($missing['slug'],OBJECT,'post');
$checks['post_count_unchanged'] = serialize(wp_count_posts('post')) === $count;
$passed = !in_array(false,$checks,true);
$report = array('passed'=>$passed,'write_path_exercised'=>$exercise,'checks'=>$checks);
$dir = dirname(__DIR__) . '/outputs/paper-bag-blog-links-20261026';
wp_mkdir_p($dir);
file_put_contents($dir.'/deploy-sync-verification.json',wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo wp_json_encode(array('passed'=>$passed,'checks'=>count($checks),'failed'=>array_keys(array_filter($checks,static fn($v)=>!$v)))),PHP_EOL;
exit($passed ? 0 : 1);
