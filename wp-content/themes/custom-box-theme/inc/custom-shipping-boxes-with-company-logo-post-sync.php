<?php
/** Deploys the custom shipping boxes with company logo buyer guide and images. */

defined('ABSPATH') || exit;

const CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_SYNC_VERSION = '2026-10-07-custom-shipping-boxes-company-logo-v1';
const CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_VERSION_OPTION = 'custom_box_custom_shipping_boxes_company_logo_sync_version';
const CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_NOTICE_OPTION = 'custom_box_custom_shipping_boxes_company_logo_sync_notice';

add_action('admin_init', 'custom_box_sync_custom_shipping_boxes_with_company_logo_post');
add_action('admin_notices', 'custom_box_custom_shipping_boxes_with_company_logo_admin_notice');

function custom_box_custom_shipping_boxes_with_company_logo_post_data(): array
{
    return array(
        'title' => 'Custom Shipping Boxes With Company Logo: Printing, Cost and RFQ Guide',
        'slug' => 'custom-shipping-boxes-with-company-logo',
        'excerpt' => 'Specify custom shipping boxes with a company logo by comparing box style, printing, corrugated construction, dimensions, quantity, samples and delivery requirements in one supplier-ready RFQ.',
        'category' => array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
        'tags' => array(
            'Custom Shipping Boxes' => 'custom-shipping-boxes',
            'Custom Printed Boxes' => 'custom-printed-boxes',
            'Corrugated Shipping Boxes' => 'corrugated-shipping-boxes',
            'Branded Packaging' => 'branded-packaging',
            'Packaging RFQ' => 'packaging-rfq',
        ),
        'seo_title' => 'Custom Shipping Boxes With Logo: Cost & RFQ Guide',
        'seo_description' => 'Compare box styles, printing, board, dimensions, quantities, samples and delivery before ordering custom shipping boxes with your company logo.',
        'focus_keyword' => 'custom shipping boxes with company logo',
    );
}

function custom_box_custom_shipping_boxes_with_company_logo_images(): array
{
    return array(
        'featured' => array(
            'base' => 'custom-shipping-boxes-company-logo',
            'alt' => 'Custom shipping boxes with company logo in corrugated carton and mailer styles',
            'title' => 'Custom Shipping Boxes With Company Logo',
            'caption' => 'Box structure, print coverage and delivery requirements should be specified together before quotation.',
        ),
        'slot_2' => array(
            'base' => 'rsc-vs-corrugated-mailer-box',
            'alt' => 'Comparison of an RSC shipping carton and a branded corrugated mailer box',
            'title' => 'RSC Shipping Carton Versus Corrugated Mailer',
            'caption' => 'Choose the structure according to transport handling, product protection and the intended unboxing experience.',
        ),
        'slot_3' => array(
            'base' => 'shipping-box-print-methods',
            'alt' => 'Flexographic, digital and litho-laminated printing methods for shipping boxes',
            'title' => 'Shipping Box Printing Methods',
            'caption' => 'Printing route should match artwork complexity, board surface, coverage, quantity and supplier equipment.',
        ),
        'slot_4' => array(
            'base' => 'custom-shipping-box-cost-drivers',
            'alt' => 'Cost drivers for custom shipping boxes including size, structure, board, printing and freight',
            'title' => 'Custom Shipping Box Cost Drivers',
            'caption' => 'Dimensions, construction, print coverage, tooling, quantity and delivery basis all affect a comparable quote.',
        ),
        'slot_5' => array(
            'base' => 'shipping-box-rfq-specification',
            'alt' => 'RFQ worksheet for custom shipping boxes with dimensions, board, artwork, quantity and delivery details',
            'title' => 'Shipping Box RFQ Specification',
            'caption' => 'A complete RFQ makes suppliers price the same box and exposes differences hidden by unit price alone.',
        ),
    );
}

function custom_box_custom_shipping_boxes_with_company_logo_content(): string
{
    $path = __DIR__ . '/post-content/custom-shipping-boxes-with-company-logo.html';
    $content = is_readable($path) ? file_get_contents($path) : false;
    return is_string($content) ? trim($content) : '';
}

function custom_box_find_custom_shipping_boxes_with_company_logo_post(string $slug, string $title): ?WP_Post
{
    $post = get_page_by_path($slug, OBJECT, 'post');
    if ($post && 'trash' !== $post->post_status) {
        return $post;
    }
    global $wpdb;
    $post_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status <> 'trash' AND post_title = %s ORDER BY ID DESC LIMIT 1",
        $title
    ));
    return $post_id ? get_post($post_id) : null;
}

function custom_box_sync_custom_shipping_boxes_with_company_logo_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $data = custom_box_custom_shipping_boxes_with_company_logo_post_data();
    $post = custom_box_find_custom_shipping_boxes_with_company_logo_post($data['slug'], $data['title']);
    if (
        CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_SYNC_VERSION === get_option(CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_VERSION_OPTION)
        && $post
        && custom_box_custom_shipping_boxes_with_company_logo_is_complete((int) $post->ID)
    ) {
        return;
    }
    $post_id = custom_box_upsert_custom_shipping_boxes_with_company_logo_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_VERSION_OPTION);
        update_option(CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_NOTICE_OPTION, array('success' => false, 'message' => $post_id->get_error_message()), false);
        return;
    }
    if (custom_box_custom_shipping_boxes_with_company_logo_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_VERSION_OPTION, CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf('Custom shipping boxes with company logo guide synced: post ID %d, status %s, featured image %d and four inline figures verified.', (int) $post_id, (string) get_post_status($post_id), (int) get_post_thumbnail_id($post_id)),
        ), false);
        return;
    }
    delete_option(CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_VERSION_OPTION);
    update_option(CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Custom shipping boxes with company logo guide is incomplete and will retry. Missing images or validation failures: '
            . implode(', ', array_merge((array) get_option('custom_box_custom_shipping_boxes_company_logo_missing_images', array()), (array) get_option('custom_box_custom_shipping_boxes_company_logo_validation_failures', array()))),
    ), false);
}

function custom_box_upsert_custom_shipping_boxes_with_company_logo_post()
{
    $data = custom_box_custom_shipping_boxes_with_company_logo_post_data();
    $post = custom_box_find_custom_shipping_boxes_with_company_logo_post($data['slug'], $data['title']);
    $content = custom_box_custom_shipping_boxes_with_company_logo_content();
    if ('' === $content) {
        return new WP_Error('custom_shipping_boxes_logo_content_missing', 'The custom shipping boxes with company logo content bundle is missing.');
    }
    $payload = array('post_title' => $data['title'], 'post_name' => $data['slug'], 'post_type' => 'post', 'post_excerpt' => $data['excerpt']);
    if ($post) {
        $payload['ID'] = (int) $post->ID;
        $payload['post_status'] = in_array($post->post_status, array('publish', 'private'), true) ? $post->post_status : 'draft';
        $existing = (string) $post->post_content;
        if (!$existing || false !== strpos($existing, 'IMAGE_SLOT_') || false === strpos($existing, 'custom-shipping-boxes-company-logo-image:')) {
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
    custom_box_sync_custom_shipping_boxes_with_company_logo_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_custom_shipping_boxes_with_company_logo_images($post_id);
    return $post_id;
}

function custom_box_sync_custom_shipping_boxes_with_company_logo_terms(int $post_id, array $data): void
{
    $category = get_term_by('slug', $data['category']['slug'], 'category');
    if (!$category || is_wp_error($category)) {
        $created = wp_insert_term($data['category']['name'], 'category', array('slug' => $data['category']['slug']));
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

function custom_box_custom_shipping_boxes_with_company_logo_bundle_path(string $base): string
{
    return get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/10/' . $base . '.webp';
}

function custom_box_find_custom_shipping_boxes_with_company_logo_attachment(string $base): int
{
    global $wpdb;
    $ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC", '%' . $wpdb->esc_like($base) . '%'));
    foreach ($ids as $id) {
        if ($base === pathinfo(wp_basename((string) get_post_meta((int) $id, '_wp_attached_file', true)), PATHINFO_FILENAME)) {
            return (int) $id;
        }
    }
    return 0;
}

function custom_box_ensure_custom_shipping_boxes_with_company_logo_attachment_file(int $attachment_id, string $base): bool
{
    $relative = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
    $uploads = wp_get_upload_dir();
    $target = $relative ? trailingslashit($uploads['basedir']) . $relative : '';
    if ($target && file_exists($target)) {
        return true;
    }
    $relative = '2026/10/' . $base . '.webp';
    $target = trailingslashit($uploads['basedir']) . $relative;
    $bundle = custom_box_custom_shipping_boxes_with_company_logo_bundle_path($base);
    if (!file_exists($target) && file_exists($bundle)) {
        wp_mkdir_p(dirname($target));
        copy($bundle, $target);
    }
    if (!file_exists($target)) {
        return false;
    }
    update_post_meta($attachment_id, '_wp_attached_file', $relative);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata($attachment_id, $target);
    if (is_array($metadata)) {
        wp_update_attachment_metadata($attachment_id, $metadata);
    }
    return true;
}

function custom_box_create_custom_shipping_boxes_with_company_logo_attachment(string $base, int $post_id, array $image): int
{
    $uploads = wp_get_upload_dir();
    if (empty($uploads['basedir']) || empty($uploads['baseurl'])) {
        return 0;
    }
    $relative = '2026/10/' . $base . '.webp';
    $path = trailingslashit($uploads['basedir']) . $relative;
    $bundle = custom_box_custom_shipping_boxes_with_company_logo_bundle_path($base);
    if (!file_exists($path) && file_exists($bundle)) {
        wp_mkdir_p(dirname($path));
        copy($bundle, $path);
    }
    if (!file_exists($path)) {
        return 0;
    }
    $attachment_id = wp_insert_attachment(array(
        'guid' => trailingslashit($uploads['baseurl']) . $relative,
        'post_mime_type' => 'image/webp', 'post_title' => $image['title'], 'post_excerpt' => $image['caption'],
        'post_status' => 'inherit', 'post_parent' => $post_id,
    ), $path, $post_id, true);
    if (is_wp_error($attachment_id)) {
        return 0;
    }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_wp_attached_file', $relative);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $path);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);
    return (int) $attachment_id;
}

function custom_box_sync_custom_shipping_boxes_with_company_logo_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing = array();
    foreach (custom_box_custom_shipping_boxes_with_company_logo_images() as $key => $image) {
        $id = custom_box_find_custom_shipping_boxes_with_company_logo_attachment($image['base']);
        if (!$id) {
            $id = custom_box_create_custom_shipping_boxes_with_company_logo_attachment($image['base'], $post_id, $image);
        } elseif (!custom_box_ensure_custom_shipping_boxes_with_company_logo_attachment_file($id, $image['base'])) {
            $missing[] = $image['base'];
            continue;
        }
        $url = $id ? wp_get_attachment_url($id) : false;
        if (!$id || !$url) {
            $missing[] = $image['base'];
            continue;
        }
        update_post_meta($id, '_wp_attachment_image_alt', $image['alt']);
        wp_update_post(array('ID' => $id, 'post_title' => $image['title'], 'post_excerpt' => $image['caption'], 'post_parent' => $post_id));
        if ('featured' === $key) {
            set_post_thumbnail($post_id, $id);
            continue;
        }
        $marker = '<!-- custom-shipping-boxes-company-logo-image:' . $key . ' -->';
        $figure = $marker . "\n<figure><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $pattern = '/' . preg_quote($marker, '/') . '\\s*<figure\\b.*?<\\/figure>/is';
        if (preg_match($pattern, $content)) {
            $updated = preg_replace($pattern, $figure, $content, 1);
            if (is_string($updated)) {
                $content = $updated;
            }
        } elseif (false !== strpos($content, $marker)) {
            $content = str_replace($marker, $figure, $content);
        } elseif (preg_match('/<!-- IMAGE_SLOT_' . preg_quote(substr($key, 5), '/') . '[^>]*-->/i', $content)) {
            $updated = preg_replace('/<!-- IMAGE_SLOT_' . preg_quote(substr($key, 5), '/') . '[^>]*-->/i', $figure, $content, 1);
            if (is_string($updated)) {
                $content = $updated;
            }
        } else {
            $missing[] = $key . ' slot';
        }
    }
    if ($post && $content !== (string) $post->post_content) {
        wp_update_post(array('ID' => $post_id, 'post_content' => $content));
    }
    update_option('custom_box_custom_shipping_boxes_company_logo_missing_images', array_values(array_unique($missing)), false);
}

function custom_box_custom_shipping_boxes_with_company_logo_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_custom_shipping_boxes_with_company_logo_post_data();
    $images = custom_box_custom_shipping_boxes_with_company_logo_images();
    $failures = array();
    if (!$post || 'post' !== $post->post_type || $data['title'] !== $post->post_title || $data['slug'] !== $post->post_name || $data['excerpt'] !== $post->post_excerpt) {
        $failures[] = 'post identity or excerpt';
    }
    if (!$post || !in_array($post->post_status, array('draft', 'publish', 'private'), true)) {
        $failures[] = 'post status';
    }
    $uploads = wp_get_upload_dir();
    foreach ($images as $key => $image) {
        $id = custom_box_find_custom_shipping_boxes_with_company_logo_attachment($image['base']);
        $attachment = $id ? get_post($id) : null;
        $file = $id ? (string) get_post_meta($id, '_wp_attached_file', true) : '';
        if (!$attachment || 'attachment' !== $attachment->post_type || (int) $post_id !== (int) $attachment->post_parent || $image['base'] !== pathinfo(wp_basename($file), PATHINFO_FILENAME) || $image['title'] !== $attachment->post_title || $image['caption'] !== $attachment->post_excerpt || $image['alt'] !== get_post_meta($id, '_wp_attachment_image_alt', true) || !$id || !wp_get_attachment_url($id) || !file_exists(trailingslashit($uploads['basedir']) . $file)) {
            $failures[] = $key . ' attachment';
        }
    }
    $content = $post ? (string) $post->post_content : '';
    if (4 !== preg_match_all('/<figure\\b/i', $content, $unused) || preg_match('/IMAGE_SLOT_[0-9]+/', $content)) {
        $failures[] = 'inline figures or placeholders';
    }
    foreach ($images as $key => $image) {
        if ('featured' !== $key && false === strpos($content, $image['base'])) {
            $failures[] = $key . ' filename';
        }
    }
    $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    if (is_wp_error($categories) || !in_array($data['category']['slug'], $categories, true)) {
        $failures[] = 'category';
    }
    $tags = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs'));
    $expected = array_values($data['tags']); sort($tags); sort($expected);
    if (is_wp_error($tags) || $tags !== $expected) {
        $failures[] = 'exact tags';
    }
    if ($data['seo_title'] !== (string) get_post_meta($post_id, 'rank_math_title', true) || $data['seo_description'] !== (string) get_post_meta($post_id, 'rank_math_description', true) || $data['focus_keyword'] !== (string) get_post_meta($post_id, 'rank_math_focus_keyword', true)) {
        $failures[] = 'Rank Math metadata';
    }
    $failures = array_merge($failures, (array) get_option('custom_box_custom_shipping_boxes_company_logo_missing_images', array()));
    update_option('custom_box_custom_shipping_boxes_company_logo_validation_failures', array_values(array_unique($failures)), false);
    return !$failures;
}

function custom_box_custom_shipping_boxes_with_company_logo_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $notice = get_option(CUSTOM_BOX_CUSTOM_SHIPPING_BOXES_LOGO_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }
    echo '<div class="' . esc_attr(!empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning') . '"><p>' . esc_html($notice['message']) . '</p></div>';
}
