<?php
/**
 * Plugin Name: VPN Paper Bag Blog Context Release
 * Description: Adds six approved bakery examples to existing articles, without importing posts or media.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;
const VPN_PAPER_BLOG_CONTEXT_VERSION = '2026-10-26.1';

function vpn_paper_blog_context_data(): array {
    static $data;
    if (null === $data) {
        $path = __DIR__ . '/vpn-paper-blog-context-20261026/patches.json';
        $data = is_readable($path) ? json_decode((string) file_get_contents($path), true) : array();
    }
    return is_array($data) ? $data : array();
}

/** Match text across raw/imported HTML, entities and WordPress smart quotes. */
function vpn_paper_blog_context_text(string $html): string {
    $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strtr($text, array('‘'=>"'",'’'=>"'",'“'=>'"','”'=>'"',"\xc2\xa0"=>' '));
    return trim((string) preg_replace('/\s+/u', ' ', $text));
}

/** Pure patch: preserve every original byte and refuse changed/ambiguous anchors. */
function vpn_paper_blog_context_patch(string $content, array $row): array {
    $addition = str_replace('{{HOME}}', untrailingslashit(home_url('/')), $row['addition']);
    preg_match_all('~<p\b[^>]*>.*?</p>~isu', $content, $paragraphs, PREG_OFFSET_CAPTURE);
    $anchors = array();
    $existing = 0;
    $expected = vpn_paper_blog_context_text($addition);
    foreach ($paragraphs[0] as $paragraph) {
        $text = vpn_paper_blog_context_text($paragraph[0]);
        if ($text === $expected) { $existing++; }
        elseif (preg_match('~\bid\s*=\s*([\'"])' . preg_quote($row['paragraph_id'], '~') . '\1~i', $paragraph[0])) {
            return array('error'=>'The approved example has been edited; preserved for review.');
        }
        if ($text === vpn_paper_blog_context_text($row['anchor_text'])) { $anchors[] = $paragraph; }
    }
    if (1 === $existing) { return array('status'=>'already present','content'=>$content); }
    if ($existing > 1) { return array('error'=>'Duplicate examples require review.'); }
    if (1 !== count($anchors)) { return array('error'=>'Reviewed paragraph missing or ambiguous; article preserved.'); }
    if (has_blocks($content)) { return array('error'=>'Block-based article requires placement review.'); }
    $offset = $anchors[0][1] + strlen($anchors[0][0]);
    preg_match('/^\s*/u', substr($content, $offset), $space);
    $offset += strlen($space[0]);
    $eol = false !== strpos($content, "\r\n") ? "\r\n" : "\n";
    return array('status'=>'ready','content'=>substr($content,0,$offset) . $eol . $addition . substr($content,$offset));
}

function vpn_paper_blog_context_apply_one(array $row): array {
    $post = get_page_by_path($row['slug'], OBJECT, 'post');
    if (!$post || 'publish' !== $post->post_status) { return array('error'=>'Existing published article required; no article created.'); }
    $id = (int) $post->ID;
    $patch = vpn_paper_blog_context_patch($post->post_content, $row);
    if (isset($patch['error'])) { return array('id'=>$id,'error'=>$patch['error']); }
    if ('already present' === $patch['status']) { return array('id'=>$id,'status'=>'already present'); }
    $key = 'vpn_paper_blog_context_20261026_before_' . $id;
    if (false === get_option($key,false) && !add_option($key,array('content'=>$post->post_content,'date'=>$post->post_date,'date_gmt'=>$post->post_date_gmt),'',false)) {
        return array('id'=>$id,'error'=>'Could not save the original article.');
    }
    $saved = wp_update_post(wp_slash(array('ID'=>$id,'post_content'=>$patch['content'],
        'post_date'=>$post->post_date,'post_date_gmt'=>$post->post_date_gmt,'edit_date'=>true)), true);
    if (is_wp_error($saved)) { return array('id'=>$id,'error'=>$saved->get_error_message()); }
    clean_post_cache($id);
    $after = get_post($id);
    if ($after->post_content !== $patch['content']) {
        $restored = wp_update_post(wp_slash(array('ID'=>$id,'post_content'=>$post->post_content,
            'post_date'=>$post->post_date,'post_date_gmt'=>$post->post_date_gmt,'edit_date'=>true)), true);
        $message = !is_wp_error($restored) && get_post($id)->post_content === $post->post_content
            ? 'Readback failed; original content restored.' : 'Readback failed; original content retained in backup option.';
        return array('id'=>$id,'error'=>$message);
    }
    do_action('litespeed_purge_post', $id);
    return array('id'=>$id,'status'=>'applied','url'=>get_permalink($id),'backup_option'=>$key);
}

function vpn_paper_blog_context_apply(): array {
    $results = array();
    foreach (vpn_paper_blog_context_data()['articles'] ?? array() as $row) {
        try { $results[$row['slug']] = vpn_paper_blog_context_apply_one($row); }
        catch (Throwable $error) { $results[$row['slug']] = array('error'=>$error->getMessage()); }
    }
    return $results;
}

// One deployment pass; local test tools can suppress it before loading WordPress.
add_action('init', static function () {
    if (defined('VPN_PAPER_BLOG_CONTEXT_SKIP_AUTO') && VPN_PAPER_BLOG_CONTEXT_SKIP_AUTO) { return; }
    if (wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) { return; }
    if (get_option('vpn_paper_blog_context_20261026_attempt') === VPN_PAPER_BLOG_CONTEXT_VERSION) { return; }
    $data = vpn_paper_blog_context_data();
    if (($data['version'] ?? '') !== VPN_PAPER_BLOG_CONTEXT_VERSION || count($data['articles'] ?? array()) !== 6) { return; }
    $lock = 'vpn_paper_blog_context_20261026_lock';
    $started = (int)get_option($lock,0);
    if ($started && time()-$started > 600) { delete_option($lock); }
    if (!add_option($lock,time(),'',false)) { return; }
    try {
        update_option('vpn_paper_blog_context_20261026_results',vpn_paper_blog_context_apply(),false);
        update_option('vpn_paper_blog_context_20261026_attempt',VPN_PAPER_BLOG_CONTEXT_VERSION,false);
    } finally { delete_option($lock); }
}, 125);

add_action('admin_notices', static function () {
    if (!current_user_can('manage_options')) { return; }
    $errors = array();
    foreach ((array)get_option('vpn_paper_blog_context_20261026_results',array()) as $slug=>$row) {
        if (isset($row['error'])) { $errors[] = $slug . ': ' . $row['error']; }
    }
    if ($errors) { echo '<div class="notice notice-warning"><p>' . esc_html('Paper bag blog update needs review: ' . implode(' | ',$errors)) . '</p></div>'; }
});
