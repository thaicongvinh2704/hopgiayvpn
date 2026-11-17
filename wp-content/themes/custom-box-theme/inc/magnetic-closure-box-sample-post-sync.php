<?php
/**
 * Imports the approved magnetic closure box guide as a WordPress draft.
 */

defined('ABSPATH') || exit;

const VPN_MAGNETIC_GUIDE_BATCH = 'magnetic-box-sample-20261117';
const VPN_MAGNETIC_GUIDE_VERSION = '2026-11-17-v1';
const VPN_MAGNETIC_GUIDE_OPTION = 'custom_box_magnetic_guide_sync_version';
const VPN_MAGNETIC_GUIDE_NOTICE = 'custom_box_magnetic_guide_sync_notice';

add_action('admin_init', 'vpn_magnetic_guide_sync');
add_action('admin_notices', 'vpn_magnetic_guide_notice');

function vpn_magnetic_guide_check($value, string $message): void
{
    if (is_wp_error($value)) {
        throw new RuntimeException($message . ': ' . $value->get_error_message());
    }
    if (!$value) {
        throw new RuntimeException($message);
    }
}

function vpn_magnetic_guide_asset(array $asset, int $post_id): int
{
    $marker = VPN_MAGNETIC_GUIDE_BATCH . '/' . $asset['file'];
    $matches = get_posts([
        'post_type' => 'attachment',
        'post_status' => ['inherit', 'private', 'trash'],
        'numberposts' => 2,
        'fields' => 'ids',
        'meta_key' => '_vpn_editorial_asset',
        'meta_value' => $marker,
    ]);
    vpn_magnetic_guide_check(count($matches) <= 1, 'Duplicate image: ' . $asset['file']);
    if ($matches) {
        $id = (int) $matches[0];
    } else {
        $temp = wp_tempnam($asset['file']);
        if (!$temp || !copy($asset['path'], $temp)) {
            if ($temp && is_file($temp)) {
                wp_delete_file($temp);
            }
            throw new RuntimeException('Could not stage image: ' . $asset['file']);
        }
        $id = media_handle_sideload(['name' => $asset['file'], 'tmp_name' => $temp], $post_id, null, wp_slash([
            'post_title' => $asset['title'],
            'post_excerpt' => $asset['caption'],
            'post_content' => $asset['origin'],
            'meta_input' => ['_vpn_editorial_asset' => $marker, '_wp_attachment_image_alt' => $asset['alt']],
        ]));
        if (is_wp_error($id) && is_file($temp)) {
            wp_delete_file($temp);
        }
        vpn_magnetic_guide_check($id, 'Media upload failed');
    }
    $file = get_attached_file($id);
    vpn_magnetic_guide_check(
        (int) get_post_field('post_parent', $id) === $post_id && get_post_status($id) !== 'trash'
        && $file && is_file($file) && hash_equals($asset['sha256'], hash_file('sha256', $file))
        && get_post_field('post_title', $id) === $asset['title']
        && wp_get_attachment_caption($id) === $asset['caption']
        && get_post_meta($id, '_wp_attachment_image_alt', true) === $asset['alt']
        && !empty(wp_get_attachment_metadata($id)['sizes']),
        'Image validation failed: ' . $asset['file']
    );
    return $id;
}

function vpn_magnetic_guide_sync(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    global $wpdb;
    $lock = 'vpn_magnetic_' . md5(DB_NAME . $wpdb->prefix);
    if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== '1') {
        return;
    }
    try {
        vpn_magnetic_guide_write();
        delete_option(VPN_MAGNETIC_GUIDE_NOTICE);
    } catch (Throwable $error) {
        update_option(VPN_MAGNETIC_GUIDE_NOTICE, $error->getMessage(), false);
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}

function vpn_magnetic_guide_write(): void
{
    global $wpdb;
    $slug = 'magnetic-closure-box-sample-approval';
    $base = __DIR__ . '/post-content/' . $slug;
    $post_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_name IN (%s, %s) AND post_type NOT IN ('attachment', 'revision')",
        $slug,
        $slug . '__trashed'
    ));
    vpn_magnetic_guide_check(count($post_ids) <= 1, 'Duplicate article slugs require review.');
    $post = $post_ids ? get_post((int) $post_ids[0]) : null;
    if ($post) {
        vpn_magnetic_guide_check(
            $post->post_type === 'post' && get_post_meta($post->ID, '_vpn_editorial_batch', true) === VPN_MAGNETIC_GUIDE_BATCH,
            'An existing article already uses this slug. It was preserved.'
        );
        if ($post->post_status !== 'draft') {
            return;
        }
        $saved_hash = get_post_meta($post->ID, '_vpn_editorial_render_hash', true);
        if ($post->post_content && ($saved_hash === '' || !hash_equals($saved_hash, hash('sha256', $post->post_content)))) {
            vpn_magnetic_guide_check(get_option(VPN_MAGNETIC_GUIDE_OPTION) === VPN_MAGNETIC_GUIDE_VERSION,
                'The existing draft was edited. It was preserved.');
            return;
        }
    }

    $data = json_decode((string) file_get_contents($base . '.json'), true, 512, JSON_THROW_ON_ERROR);
    $body = str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($base . '.html'));
    vpn_magnetic_guide_check($data['slug'] === $slug && count($data['images']) === 4
        && hash_equals($data['content_sha256'], hash('sha256', $body))
        && substr_count($body, '<!-- IMAGE_SLOT_') === 3, 'Invalid article bundle.');
    $category = get_term_by('slug', $data['category'], 'category');
    vpn_magnetic_guide_check($category, 'The Packaging Production Guide category is missing.');
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    vpn_magnetic_guide_check(defined('RANK_MATH_VERSION'), 'Rank Math is unavailable.');
    vpn_magnetic_guide_check(wp_image_editor_supports(['mime_type' => 'image/webp']), 'WebP image processing is unavailable.');

    foreach ($data['images'] as $slot => &$asset) {
        vpn_magnetic_guide_check($asset['slot'] === $slot && basename($asset['file']) === $asset['file']
            && ($slot === 0 || substr_count($body, '<!-- IMAGE_SLOT_' . $slot . ' -->') === 1), 'Invalid image slot.');
        $asset['path'] = __DIR__ . '/product-sample-deploy-assets/uploads/2026/11/' . $asset['file'];
        vpn_magnetic_guide_check(is_file($asset['path']) && hash_equals($asset['sha256'], hash_file('sha256', $asset['path'])),
            'Bundled image missing or changed: ' . $asset['file']);
    }
    unset($asset);

    $id = $post ? $post->ID : wp_insert_post(wp_slash([
        'post_type' => 'post',
        'post_status' => 'draft',
        'post_name' => $slug,
        'post_title' => $data['title'],
        'post_excerpt' => $data['excerpt'],
        'post_author' => get_current_user_id(),
        'post_category' => [(int) $category->term_id],
        'comment_status' => 'closed',
        'meta_input' => ['_vpn_editorial_batch' => VPN_MAGNETIC_GUIDE_BATCH],
    ]), true);
    vpn_magnetic_guide_check($id, 'Draft creation failed.');

    $media = [];
    foreach ($data['images'] as $slot => $asset) {
        $media[$slot] = vpn_magnetic_guide_asset($asset, $id);
        if ($slot === 0) {
            continue;
        }
        $image = wp_get_attachment_image($media[$slot], 'full', false, [
            'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 870px) 100vw, 820px',
        ]);
        $marker = '<!-- IMAGE_SLOT_' . $slot . ' -->';
        $figure = '<figure class="wp-caption alignnone"><a href="' . esc_url(wp_get_attachment_url($media[$slot]))
            . '" aria-label="' . esc_attr('View full image of ' . $asset['title']) . '">' . $image
            . '</a><figcaption class="wp-caption-text">' . esc_html($asset['caption']) . '</figcaption></figure>';
        $body = str_replace($marker, '<!-- vpn-magnetic-guide-image-' . $slot . ' -->' . $figure, $body);
    }
    $body = wp_kses_post(str_replace('https://hopgiayvpn.com/', home_url('/'), $body));
    vpn_magnetic_guide_check(!str_contains($body, 'IMAGE_SLOT') && substr_count($body, '<figure class="wp-caption alignnone">') === 3
        && substr_count($body, '<img ') === 3, 'Inline figures could not be rendered.');
    if ((int) get_post_thumbnail_id($id) !== $media[0]) {
        set_post_thumbnail($id, $media[0]);
    }
    if (!$post || $post->post_content !== $body || $post->post_title !== $data['title'] || $post->post_excerpt !== $data['excerpt']
        || !has_category((int) $category->term_id, $id)) {
        vpn_magnetic_guide_check(wp_update_post(wp_slash([
            'ID' => $id,
            'post_title' => $data['title'],
            'post_content' => $body,
            'post_excerpt' => $data['excerpt'],
            'post_category' => [(int) $category->term_id],
            'meta_input' => ['_vpn_editorial_render_hash' => hash('sha256', $body)],
        ]), true), 'Draft update failed.');
    }
    $meta = [
        'rank_math_title' => $data['seo_title'],
        'rank_math_description' => $data['meta_description'],
        'rank_math_focus_keyword' => $data['focus_keyword'],
        'rank_math_primary_category' => (int) $category->term_id,
        'rank_math_facebook_title' => $data['seo_title'],
        'rank_math_facebook_description' => $data['meta_description'],
        'rank_math_facebook_image' => wp_get_attachment_url($media[0]),
        'rank_math_facebook_image_id' => (string) $media[0],
    ];
    foreach ($meta as $name => $value) {
        if ((string) get_post_meta($id, $name, true) !== (string) $value) {
            update_post_meta($id, $name, wp_slash($value));
        }
        vpn_magnetic_guide_check((string) get_post_meta($id, $name, true) === (string) $value, 'SEO validation failed: ' . $name);
    }
    vpn_magnetic_guide_check(
        get_post_status($id) === 'draft' && get_post_field('post_name', $id) === $slug
        && get_post_field('post_content', $id) === $body && get_post_field('post_excerpt', $id) === $data['excerpt']
        && (int) get_post_thumbnail_id($id) === $media[0] && has_category((int) $category->term_id, $id),
        'Saved draft failed validation.'
    );
    if (get_option(VPN_MAGNETIC_GUIDE_OPTION) !== VPN_MAGNETIC_GUIDE_VERSION) {
        update_option(VPN_MAGNETIC_GUIDE_OPTION, VPN_MAGNETIC_GUIDE_VERSION, false);
        set_transient('vpn_magnetic_guide_ready', $id, DAY_IN_SECONDS);
    }
}

function vpn_magnetic_guide_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $error = get_option(VPN_MAGNETIC_GUIDE_NOTICE);
    if ($error) {
        echo '<div class="notice notice-warning"><p>' . esc_html('Magnetic box guide: ' . $error) . '</p></div>';
        return;
    }
    $id = (int) get_transient('vpn_magnetic_guide_ready');
    if ($id && get_post_status($id) === 'draft') {
        delete_transient('vpn_magnetic_guide_ready');
        echo '<div class="notice notice-success is-dismissible"><p>Magnetic box guide and images are saved. <a href="'
            . esc_url(get_edit_post_link($id, 'raw')) . '">Review draft</a>.</p></div>';
    }
}
