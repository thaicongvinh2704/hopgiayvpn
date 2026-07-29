<?php
/**
 * Deploys the E-flute corrugated cardboard thickness draft and images.
 */

defined('ABSPATH') || exit;

final class Custom_Box_E_Flute_Cardboard_Thickness_Post_Sync
{
    public const VERSION = '2026-07-29-v1';
    public const VERSION_OPTION = 'custom_box_e_flute_cardboard_thickness_sync_version';
    public const NOTICE_OPTION = 'custom_box_e_flute_cardboard_thickness_sync_notice';
    public const MISSING_IMAGES_OPTION = 'custom_box_e_flute_cardboard_thickness_missing_images';
    public const MISSING_SLOTS_OPTION = 'custom_box_e_flute_cardboard_thickness_missing_slots';
    public const FAILURES_OPTION = 'custom_box_e_flute_cardboard_thickness_validation_failures';

    public static function data(): array
    {
        return array(
            'title' => 'E-Flute Corrugated Cardboard Thickness in MM: A Practical Guide',
            'slug' => 'e-flute-corrugated-cardboard-thickness-mm',
            'excerpt' => 'E-flute corrugated cardboard is commonly around 1.2–1.6 mm thick. Learn why measurements vary, how to measure finished board, and how thickness affects mailer-box fit and performance.',
            'category' => array(
                'name' => 'Packaging Guides',
                'slug' => 'packaging-guides',
            ),
            'tags' => array(
                'E-Flute' => 'e-flute',
                'Corrugated Cardboard' => 'corrugated-cardboard',
                'Board Thickness' => 'board-thickness',
                'Mailer Boxes' => 'mailer-boxes',
                'Packaging Materials' => 'packaging-materials',
                'Corrugated Packaging' => 'corrugated-packaging',
            ),
            'seo_title' => 'E-Flute Corrugated Cardboard Thickness in MM Guide',
            'seo_description' => 'E-flute corrugated cardboard is commonly 1.2–1.6 mm thick. Learn why measurements vary and how to specify board for mailer boxes.',
            'focus_keyword' => 'e-flute corrugated cardboard thickness mm',
            'cta_url' => 'https://hopgiayvpn.com/products/corrugated-mailer-boxes/',
        );
    }

    public static function images(): array
    {
        return array(
            'featured' => array(
                'base' => 'e-flute-corrugated-cardboard-thickness-mm',
                'alt' => 'E-flute corrugated cardboard thickness measured in millimeters',
                'title' => 'E-Flute Thickness Guide',
                'caption' => 'E-flute board is commonly close to 1.5 mm, but the finished caliper should be verified from the production sample.',
            ),
            'slot_1' => array(
                'base' => 'e-flute-height-vs-board-caliper',
                'alt' => 'Cross-section showing E-flute height and total corrugated board caliper',
                'title' => 'Flute Height vs Board Caliper',
                'caption' => 'Flute height excludes the complete thickness contributed by liners and the finished board structure.',
            ),
            'slot_2' => array(
                'base' => 'how-to-measure-e-flute-cardboard',
                'alt' => 'Measuring E-flute corrugated board with a controlled thickness gauge',
                'title' => 'How to Measure E-Flute',
                'caption' => 'Measure flat, uncreased areas at several locations without crushing the corrugated structure.',
            ),
            'slot_3' => array(
                'base' => 'e-flute-thickness-mailer-box-fit',
                'alt' => 'Corrugated mailer lid fit affected by E-flute board thickness',
                'title' => 'Why 0.2 mm Matters',
                'caption' => 'Small board-caliper changes can affect locking tabs, folded layers and lid clearance.',
            ),
            'slot_4' => array(
                'base' => 'e-flute-vs-f-flute-b-flute-thickness',
                'alt' => 'E-flute compared with F-flute and B-flute corrugated board thickness',
                'title' => 'E vs F vs B Flute',
                'caption' => 'Nearby flute profiles differ in wall thickness, print surface and protective behavior.',
            ),
        );
    }

    public static function canonical_content(): string
    {
        $path = __DIR__ . '/post-content/e-flute-corrugated-cardboard-thickness-mm.html';
        $content = is_readable($path) ? file_get_contents($path) : false;

        return is_string($content) ? $content : '';
    }

    public static function find_post(): ?WP_Post
    {
        $data = self::data();
        $post = get_page_by_path($data['slug'], OBJECT, 'post');
        if ($post && 'trash' !== $post->post_status) {
            return $post;
        }

        global $wpdb;
        $post_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status <> 'trash' AND post_title = %s ORDER BY ID DESC LIMIT 1",
            $data['title']
        ));

        return $post_id ? get_post($post_id) : null;
    }

    public static function run(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $post = self::find_post();
        if (
            self::VERSION === get_option(self::VERSION_OPTION)
            && $post
            && self::is_complete((int) $post->ID)
        ) {
            return;
        }

        $post_id = self::upsert();
        if (is_wp_error($post_id)) {
            delete_option(self::VERSION_OPTION);
            update_option(self::NOTICE_OPTION, array(
                'success' => false,
                'message' => $post_id->get_error_message(),
            ), false);
            return;
        }

        if (self::is_complete((int) $post_id)) {
            update_option(self::VERSION_OPTION, self::VERSION, false);
            update_option(self::NOTICE_OPTION, array(
                'success' => true,
                'message' => sprintf(
                    'E-flute thickness draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Guides, 6 tags, and Rank Math fields verified.',
                    (int) $post_id,
                    (int) get_post_thumbnail_id((int) $post_id)
                ),
            ), false);
            return;
        }

        delete_option(self::VERSION_OPTION);
        delete_option(self::NOTICE_OPTION);
        update_option(self::NOTICE_OPTION, array(
            'success' => false,
            'message' => 'E-flute thickness sync is incomplete. Missing images: '
                . implode(', ', (array) get_option(self::MISSING_IMAGES_OPTION, array()))
                . '; missing slots or validation failures: '
                . implode(', ', array_merge(
                    (array) get_option(self::MISSING_SLOTS_OPTION, array()),
                    (array) get_option(self::FAILURES_OPTION, array())
                )),
        ), false);
    }

    public static function upsert()
    {
        $data = self::data();
        $post = self::find_post();
        $content = self::canonical_content();
        if ('' === trim($content)) {
            return new WP_Error(
                'e_flute_content_missing',
                'The canonical E-flute thickness content bundle is missing.'
            );
        }

        $payload = array(
            'post_title' => $data['title'],
            'post_name' => $data['slug'],
            'post_type' => 'post',
            'post_excerpt' => $data['excerpt'],
        );

        if ($post) {
            $payload['ID'] = (int) $post->ID;
            $payload['post_status'] = in_array($post->post_status, array('publish', 'private'), true)
                ? $post->post_status
                : 'draft';
            $existing = (string) $post->post_content;
            if (
                !in_array($post->post_status, array('publish', 'private'), true)
                || '' === trim($existing)
                || false !== strpos($existing, 'IMAGE_SLOT_')
            ) {
                $payload['post_content'] = $content;
            }
            $result = wp_update_post($payload, true);
        } else {
            $payload['post_status'] = 'draft';
            $payload['post_content'] = $content;
            $result = wp_insert_post($payload, true);
        }

        if (is_wp_error($result)) {
            return $result;
        }

        $post_id = (int) $result;
        self::sync_terms($post_id, $data);
        update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
        update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
        update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
        self::sync_images($post_id);

        return $post_id;
    }

    private static function sync_terms(int $post_id, array $data): void
    {
        $category = get_term_by('slug', $data['category']['slug'], 'category');
        if (!$category || is_wp_error($category)) {
            $created = wp_insert_term($data['category']['name'], 'category', array(
                'slug' => $data['category']['slug'],
            ));
            if (!is_wp_error($created)) {
                $category = get_term((int) $created['term_id'], 'category');
            }
        }
        if ($category && !is_wp_error($category)) {
            wp_set_post_categories($post_id, array((int) $category->term_id), false);
        }

        $tag_ids = array();
        foreach ($data['tags'] as $name => $slug) {
            $tag = get_term_by('slug', $slug, 'post_tag');
            if (!$tag || is_wp_error($tag)) {
                $created = wp_insert_term($name, 'post_tag', array('slug' => $slug));
                if (!is_wp_error($created)) {
                    $tag_ids[] = (int) $created['term_id'];
                }
            } else {
                $tag_ids[] = (int) $tag->term_id;
            }
        }
        wp_set_post_terms($post_id, $tag_ids, 'post_tag', false);
    }

    private static function sync_images(int $post_id): void
    {
        $post = get_post($post_id);
        $content = $post ? (string) $post->post_content : '';
        $missing_images = array();
        $missing_slots = array();

        foreach (self::images() as $key => $image) {
            $attachment_id = self::find_attachment($image['base']);
            if (!$attachment_id) {
                $attachment_id = self::create_attachment($image['base'], $post_id, $image);
            }
            $url = $attachment_id ? wp_get_attachment_url($attachment_id) : false;
            if (!$attachment_id || !$url) {
                $missing_images[] = $image['base'];
                continue;
            }

            update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_title' => $image['title'],
                'post_excerpt' => $image['caption'],
                'post_parent' => $post_id,
            ));

            if ('featured' === $key) {
                set_post_thumbnail($post_id, $attachment_id);
                continue;
            }

            $marker = '<!-- e-flute-thickness-image:' . $key . ' -->';
            $figure = $marker
                . "\n<figure><img src=\"" . esc_url($url)
                . '" alt="' . esc_attr($image['alt'])
                . '" style="width:100%; height:auto;" loading="lazy" decoding="async"><figcaption>'
                . esc_html($image['caption'])
                . '</figcaption></figure>';
            $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
            $wrapped_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
            $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

            if (preg_match($marker_pattern, $content)) {
                $content = preg_replace($marker_pattern, $figure, $content, 1);
            } elseif (preg_match($wrapped_pattern, $content)) {
                $content = preg_replace($wrapped_pattern, $figure, $content, 1);
            } elseif (false !== strpos($content, $slot)) {
                $content = str_replace($slot, $figure, $content);
            } else {
                $missing_slots[] = $key;
            }
        }

        if ($post && $content !== (string) $post->post_content) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $content,
            ));
        }

        update_option(
            self::MISSING_IMAGES_OPTION,
            array_values(array_unique($missing_images)),
            false
        );
        update_option(
            self::MISSING_SLOTS_OPTION,
            array_values(array_unique($missing_slots)),
            false
        );
    }

    public static function find_attachment(string $base): int
    {
        global $wpdb;
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
            '%' . $wpdb->esc_like($base) . '%'
        ));

        foreach ($ids as $id) {
            $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
            if ($base === pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
                return (int) $id;
            }
        }

        return 0;
    }

    private static function create_attachment(string $base, int $post_id, array $image): int
    {
        $uploads = wp_upload_dir();
        if (!empty($uploads['error'])) {
            return 0;
        }

        foreach (array('webp', 'png', 'jpg', 'jpeg') as $extension) {
            $candidate_relative = '2026/07/' . $base . '.' . $extension;
            $upload_path = trailingslashit($uploads['basedir']) . $candidate_relative;
            $bundle_path = get_template_directory()
                . '/inc/product-sample-deploy-assets/uploads/'
                . $candidate_relative;

            if (!file_exists($upload_path) && file_exists($bundle_path)) {
                if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
                    continue;
                }
            }
            if (!file_exists($upload_path)) {
                continue;
            }

            $type = wp_check_filetype(wp_basename($upload_path), null);
            $attachment_id = wp_insert_attachment(array(
                'post_mime_type' => $type['type'] ?: 'image/webp',
                'post_title' => $image['title'],
                'post_excerpt' => $image['caption'],
                'post_status' => 'inherit',
                'post_parent' => $post_id,
            ), $upload_path, $post_id, true);
            if (is_wp_error($attachment_id)) {
                return 0;
            }

            require_once ABSPATH . 'wp-admin/includes/image.php';
            update_post_meta((int) $attachment_id, '_wp_attached_file', $candidate_relative);
            $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload_path);
            if (is_array($metadata)) {
                wp_update_attachment_metadata((int) $attachment_id, $metadata);
            }
            update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);

            return (int) $attachment_id;
        }

        return 0;
    }

    public static function is_complete(int $post_id): bool
    {
        $post = get_post($post_id);
        $data = self::data();
        $images = self::images();
        $failures = array();

        if (
            !$post
            || $data['title'] !== $post->post_title
            || $data['slug'] !== $post->post_name
            || $data['excerpt'] !== $post->post_excerpt
        ) {
            $failures[] = 'post identity or excerpt';
        }
        if (!$post || !in_array($post->post_status, array('draft', 'publish', 'private'), true)) {
            $failures[] = 'post status';
        }

        $featured_id = get_post_thumbnail_id($post_id);
        $featured_file = $featured_id
            ? (string) get_post_meta($featured_id, '_wp_attached_file', true)
            : '';
        if (
            !$featured_id
            || $images['featured']['base'] !== pathinfo(wp_basename($featured_file), PATHINFO_FILENAME)
        ) {
            $failures[] = 'featured image';
        }

        foreach ($images as $key => $image) {
            $attachment_id = self::find_attachment($image['base']);
            $attachment = $attachment_id ? get_post($attachment_id) : null;
            if (
                !$attachment
                || 'attachment' !== $attachment->post_type
                || $post_id !== (int) $attachment->post_parent
                || $image['title'] !== $attachment->post_title
                || $image['caption'] !== $attachment->post_excerpt
                || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
            ) {
                $failures[] = $key . ' attachment metadata';
            }
        }

        $content = $post ? (string) $post->post_content : '';
        if (
            4 !== substr_count($content, '<!-- e-flute-thickness-image:')
            || 4 !== substr_count($content, '<figure>')
            || 4 !== substr_count($content, '<img ')
        ) {
            $failures[] = 'inline image counts';
        }
        $previous_marker_position = -1;
        foreach ($images as $key => $image) {
            if ('featured' === $key) {
                continue;
            }

            $marker = '<!-- e-flute-thickness-image:' . $key . ' -->';
            $marker_position = strpos($content, $marker);
            if (
                1 !== substr_count($content, $marker)
                || false === $marker_position
                || $marker_position <= $previous_marker_position
                || false === strpos($content, $image['base'], $marker_position)
            ) {
                $failures[] = $key . ' marker or filename';
            } else {
                $previous_marker_position = $marker_position;
            }
        }
        if (preg_match('/IMAGE_SLOT_[0-9]+/', $content)) {
            $failures[] = 'image placeholders';
        }
        if (false === strpos($content, $data['cta_url'])) {
            $failures[] = 'internal CTA link';
        }

        $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
        if (is_wp_error($categories) || array($data['category']['slug']) !== $categories) {
            $failures[] = 'exact category';
        }

        $tags = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs'));
        $expected_tags = array_values($data['tags']);
        if (is_wp_error($tags)) {
            $failures[] = 'tags';
        } else {
            sort($tags);
            sort($expected_tags);
            if ($tags !== $expected_tags) {
                $failures[] = 'exact tags';
            }
        }

        if (
            $data['seo_title'] !== get_post_meta($post_id, 'rank_math_title', true)
            || $data['seo_description'] !== get_post_meta($post_id, 'rank_math_description', true)
            || $data['focus_keyword'] !== get_post_meta($post_id, 'rank_math_focus_keyword', true)
        ) {
            $failures[] = 'Rank Math metadata';
        }
        if ((array) get_option(self::MISSING_IMAGES_OPTION, array())) {
            $failures[] = 'missing images';
        }
        if ((array) get_option(self::MISSING_SLOTS_OPTION, array())) {
            $failures[] = 'missing slots';
        }

        update_option(
            self::FAILURES_OPTION,
            array_values(array_unique($failures)),
            false
        );

        return !$failures;
    }

    public static function admin_notice(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $notice = get_option(self::NOTICE_OPTION);
        if (!is_array($notice) || empty($notice['message'])) {
            return;
        }

        $class = !empty($notice['success'])
            ? 'notice notice-success is-dismissible'
            : 'notice notice-warning';
        echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
    }
}

add_action('admin_init', array('Custom_Box_E_Flute_Cardboard_Thickness_Post_Sync', 'run'));
add_action('admin_notices', array('Custom_Box_E_Flute_Cardboard_Thickness_Post_Sync', 'admin_notice'));
