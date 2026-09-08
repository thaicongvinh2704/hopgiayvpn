<?php
/**
 * Deploys the Custom Corrugated Packaging Boxes technical buyer guide.
 */

defined('ABSPATH') || exit;

final class Custom_Box_Corrugated_Packaging_Boxes_Post_Sync
{
    public const VERSION = '2026-09-08-custom-corrugated-packaging-boxes-v1';
    public const VERSION_OPTION = 'custom_box_corrugated_packaging_boxes_sync_version';
    public const NOTICE_OPTION = 'custom_box_corrugated_packaging_boxes_sync_notice';
    public const MISSING_IMAGES_OPTION = 'custom_box_corrugated_packaging_boxes_missing_images';
    public const MISSING_SLOTS_OPTION = 'custom_box_corrugated_packaging_boxes_missing_slots';
    public const FAILURES_OPTION = 'custom_box_corrugated_packaging_boxes_validation_failures';
    private const IMAGE_DIRECTORY = '2026/09/';

    public static function data(): array
    {
        return array(
            'title' => 'Custom Corrugated Packaging Boxes: Flute, Board Grade, Printing and Cost Guide',
            'slug' => 'custom-corrugated-packaging-boxes',
            'excerpt' => 'Compare E, B, C and EB flute, board construction, printing, cost drivers, MOQ and flat shipping before sourcing custom corrugated packaging boxes.',
            'category' => array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
            'tags' => array(
                'Corrugated Packaging' => 'corrugated-packaging',
                'Custom Packaging' => 'custom-packaging',
                'Shipping Boxes' => 'shipping-boxes',
                'Packaging Materials' => 'packaging-materials',
                'Packaging Printing' => 'packaging-printing',
                'B2B Packaging' => 'b2b-packaging',
            ),
            'seo_title' => 'Custom Corrugated Packaging Boxes: Flute & Cost Guide',
            'seo_description' => 'Compare E, B, C and EB flute, board grades, printing, cost, MOQ and flat shipping before ordering custom corrugated packaging boxes.',
            'focus_keyword' => 'custom corrugated packaging boxes',
            'cta_url' => 'https://hopgiayvpn.com/custom-packaging-boxes-manufacturer/',
        );
    }

    public static function images(): array
    {
        return array(
            'featured' => array(
                'base' => 'custom-corrugated-packaging-boxes-flute-board-printing-guide',
                'alt' => 'Custom corrugated packaging boxes with different flute and board constructions',
                'title' => 'Custom Corrugated Packaging Boxes Guide',
                'caption' => 'Corrugated packaging should be specified around structure, board, printing and distribution requirements—not flute letter alone.',
            ),
            'slot_1' => array(
                'base' => 'corrugated-flute-e-b-c-eb-cross-section',
                'alt' => 'Cross sections of E flute, B flute, C flute and EB double-wall corrugated board',
                'title' => 'E B C and EB Corrugated Flute Comparison',
                'caption' => 'Corrugated flute profiles differ in height and construction, while complete board performance also depends on liner and medium specifications.',
            ),
            'slot_2' => array(
                'base' => 'corrugated-box-load-compression-decision',
                'alt' => 'Corrugated box compression and packed-weight evaluation during packaging QC',
                'title' => 'Corrugated Box Load and Compression Evaluation',
                'caption' => 'Product weight is only one input when selecting corrugated packaging for stacking and distribution.',
            ),
            'slot_3' => array(
                'base' => 'corrugated-box-printing-flexo-digital-litho',
                'alt' => 'Flexographic digital and litho-laminated printing examples on corrugated boxes',
                'title' => 'Corrugated Box Printing Methods',
                'caption' => 'Printing method should be selected together with board surface, artwork and order quantity.',
            ),
            'slot_4' => array(
                'base' => 'flat-pack-corrugated-box-export-packing',
                'alt' => 'Flat packed corrugated box blanks bundled for export shipping',
                'title' => 'Flat Pack Corrugated Boxes for Export',
                'caption' => 'Foldable corrugated structures can reduce empty-packaging transport volume when assembly is practical at destination.',
            ),
            'slot_5' => array(
                'base' => 'corrugated-packaging-rfq-specification-checklist',
                'alt' => 'Corrugated packaging sourcing desk with product weight dimensions board sample and dieline',
                'title' => 'Corrugated Packaging RFQ Specification',
                'caption' => 'A useful RFQ combines packed weight, dimensions, board direction, printing, quantity and distribution requirements.',
            ),
        );
    }

    public static function canonical_content(): string
    {
        $path = __DIR__ . '/post-content/custom-corrugated-packaging-boxes.html';
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
        if (self::VERSION === get_option(self::VERSION_OPTION) && $post && self::is_complete((int) $post->ID)) {
            return;
        }

        $post_id = self::upsert();
        if (is_wp_error($post_id)) {
            self::set_failure_notice($post_id->get_error_message());
            return;
        }

        if (self::is_complete((int) $post_id)) {
            update_option(self::VERSION_OPTION, self::VERSION, false);
            update_option(self::NOTICE_OPTION, array(
                'success' => true,
                'message' => sprintf(
                    'Custom Corrugated Packaging Boxes draft synced: post ID %d, featured image %d, 5 inline figures, category Packaging Guides, 6 tags, and Rank Math fields verified.',
                    (int) $post_id,
                    (int) get_post_thumbnail_id((int) $post_id)
                ),
            ), false);
            return;
        }

        self::set_failure_notice('The sync is incomplete. Missing images, slots, or validation failures: ' . implode(', ', array_merge(
            (array) get_option(self::MISSING_IMAGES_OPTION, array()),
            (array) get_option(self::MISSING_SLOTS_OPTION, array()),
            (array) get_option(self::FAILURES_OPTION, array())
        )));
    }

    private static function set_failure_notice(string $message): void
    {
        delete_option(self::VERSION_OPTION);
        delete_option(self::NOTICE_OPTION);
        update_option(self::NOTICE_OPTION, array('success' => false, 'message' => $message), false);
    }

    public static function upsert()
    {
        $data = self::data();
        $post = self::find_post();
        $content = self::canonical_content();
        if ('' === trim($content)) {
            return new WP_Error('custom_corrugated_content_missing', 'The canonical Custom Corrugated Packaging Boxes content bundle is missing.');
        }

        $payload = array(
            'post_title' => $data['title'],
            'post_name' => $data['slug'],
            'post_type' => 'post',
            'post_excerpt' => $data['excerpt'],
            'post_content' => $content,
        );
        if ($post) {
            $payload['ID'] = (int) $post->ID;
            $payload['post_status'] = in_array($post->post_status, array('publish', 'private'), true)
                ? $post->post_status
                : 'draft';
            if (in_array($post->post_status, array('publish', 'private'), true)
                && '' !== trim((string) $post->post_content)
                && false === strpos((string) $post->post_content, 'IMAGE_SLOT_')) {
                unset($payload['post_content']);
            }
            $result = wp_update_post($payload, true);
        } else {
            $payload['post_status'] = 'draft';
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
            $created = wp_insert_term($data['category']['name'], 'category', array('slug' => $data['category']['slug']));
            $category = is_wp_error($created) ? null : get_term((int) $created['term_id'], 'category');
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

            $marker = '<!-- stable-post-image:' . $key . ' -->';
            $figure = $marker . "\n<figure><img src=\"" . esc_url($url)
                . '" alt="' . esc_attr($image['alt'])
                . '" style="width:100%; height:auto;" loading="lazy" decoding="async"><figcaption>'
                . esc_html($image['caption']) . '</figcaption></figure>';
            $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
            $wrapped_slot_pattern = '/<span\\b[^>]*>\\s*' . preg_quote($slot, '/') . '\\s*<\\/span>/i';
            $marker_pattern = '/' . preg_quote($marker, '/') . '\\s*<figure\\b.*?<\\/figure>/is';

            if (preg_match($marker_pattern, $content)) {
                $content = preg_replace($marker_pattern, $figure, $content, 1);
            } elseif (preg_match($wrapped_slot_pattern, $content)) {
                $content = preg_replace($wrapped_slot_pattern, $figure, $content, 1);
            } elseif (false !== strpos($content, $slot)) {
                $content = str_replace($slot, $figure, $content);
            } else {
                $missing_slots[] = $key;
            }
        }

        if ($post && $content !== (string) $post->post_content) {
            wp_update_post(array('ID' => $post_id, 'post_content' => $content));
        }
        update_option(self::MISSING_IMAGES_OPTION, array_values(array_unique($missing_images)), false);
        update_option(self::MISSING_SLOTS_OPTION, array_values(array_unique($missing_slots)), false);
    }

    public static function find_attachment(string $base): int
    {
        global $wpdb;
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
            '%' . $wpdb->esc_like($base) . '%'
        ));
        foreach ((array) $ids as $id) {
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
            $relative = self::IMAGE_DIRECTORY . $base . '.' . $extension;
            $upload_path = trailingslashit($uploads['basedir']) . $relative;
            $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
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
            update_post_meta((int) $attachment_id, '_wp_attached_file', $relative);
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
        if (!$post || $data['title'] !== $post->post_title || $data['slug'] !== $post->post_name || $data['excerpt'] !== $post->post_excerpt) {
            $failures[] = 'post identity or excerpt';
        }
        if (!$post || !in_array($post->post_status, array('draft', 'publish', 'private'), true)) {
            $failures[] = 'post status';
        }
        $featured_id = get_post_thumbnail_id($post_id);
        $featured_file = $featured_id ? (string) get_post_meta($featured_id, '_wp_attached_file', true) : '';
        if (!$featured_id || $images['featured']['base'] !== pathinfo(wp_basename($featured_file), PATHINFO_FILENAME)) {
            $failures[] = 'featured image';
        }
        foreach ($images as $key => $image) {
            $attachment_id = self::find_attachment($image['base']);
            $attachment = $attachment_id ? get_post($attachment_id) : null;
            if (!$attachment || 'attachment' !== $attachment->post_type || $post_id !== (int) $attachment->post_parent
                || $image['title'] !== $attachment->post_title || $image['caption'] !== $attachment->post_excerpt
                || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) {
                $failures[] = $key . ' attachment metadata';
            }
        }
        $content = $post ? (string) $post->post_content : '';
        if (5 !== substr_count($content, '<!-- stable-post-image:') || 5 !== substr_count($content, '<figure>') || 5 !== substr_count($content, '<img ')) {
            $failures[] = 'inline image counts';
        }
        foreach ($images as $key => $image) {
            if ('featured' === $key) {
                continue;
            }
            $marker = '<!-- stable-post-image:' . $key . ' -->';
            if (1 !== substr_count($content, $marker) || false === strpos($content, $image['base'])) {
                $failures[] = $key . ' marker or filename';
            }
        }
        if (preg_match('/IMAGE_SLOT_[0-9]+/', $content)) {
            $failures[] = 'image placeholders';
        }
        if (false === strpos($content, $data['cta_url'])) {
            $failures[] = 'internal CTA link';
        }
        $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
        if (is_wp_error($categories)) {
            $failures[] = 'exact category';
        } else {
            sort($categories);
            if (array($data['category']['slug']) !== $categories) {
                $failures[] = 'exact category';
            }
        }
        $tags = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs'));
        $expected_tags = array_values($data['tags']);
        sort($expected_tags);
        if (is_wp_error($tags)) {
            $failures[] = 'tags';
        } else {
            sort($tags);
            if ($tags !== $expected_tags) {
                $failures[] = 'exact tags';
            }
        }
        if ($data['seo_title'] !== get_post_meta($post_id, 'rank_math_title', true)
            || $data['seo_description'] !== get_post_meta($post_id, 'rank_math_description', true)
            || $data['focus_keyword'] !== get_post_meta($post_id, 'rank_math_focus_keyword', true)) {
            $failures[] = 'Rank Math metadata';
        }
        if ((array) get_option(self::MISSING_IMAGES_OPTION, array())) {
            $failures[] = 'missing images';
        }
        if ((array) get_option(self::MISSING_SLOTS_OPTION, array())) {
            $failures[] = 'missing slots';
        }
        update_option(self::FAILURES_OPTION, array_values(array_unique($failures)), false);
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
        echo '<div class="' . esc_attr(!empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning') . '"><p>' . esc_html($notice['message']) . '</p></div>';
    }
}

add_action('admin_init', array('Custom_Box_Corrugated_Packaging_Boxes_Post_Sync', 'run'));
add_action('admin_notices', array('Custom_Box_Corrugated_Packaging_Boxes_Post_Sync', 'admin_notice'));
