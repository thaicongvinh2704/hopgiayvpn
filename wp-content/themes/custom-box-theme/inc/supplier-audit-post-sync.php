<?php
/**
 * One-off metadata and image sync for the supplier audit checklist blog post.
 */

add_action('admin_init', 'custom_box_sync_supplier_audit_post');

function custom_box_sync_supplier_audit_post()
{
    if (!is_admin() || !current_user_can('edit_posts')) {
        return;
    }

    $post_data = custom_box_supplier_audit_post_map();
    $post = custom_box_find_supplier_audit_post_by_slug($post_data['slug']);

    if (!$post) {
        $post = custom_box_find_supplier_audit_post_by_title($post_data['title']);
    }

    if (!$post) {
        return;
    }

    $post_id = (int) $post->ID;

    custom_box_update_supplier_audit_post_details($post_id);
    custom_box_update_supplier_audit_post_terms($post_id);
    custom_box_update_supplier_audit_post_seo($post_id);
    custom_box_update_supplier_audit_images($post_id);
}

function custom_box_find_supplier_audit_post_by_slug($slug)
{
    global $wpdb;

    $post_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_name = %s AND post_status IN ('draft', 'publish', 'pending', 'private') ORDER BY ID DESC LIMIT 1",
            $slug
        )
    );

    return $post_id ? get_post($post_id) : null;
}

function custom_box_find_supplier_audit_post_by_title($title)
{
    global $wpdb;

    $post_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_title = %s AND post_status IN ('draft', 'publish', 'pending', 'private') ORDER BY ID DESC LIMIT 1",
            $title
        )
    );

    return $post_id ? get_post($post_id) : null;
}

function custom_box_supplier_audit_post_map()
{
    return array(
        'title' => 'Custom Packaging Supplier Audit Checklist for Enterprise Buyers',
        'slug' => 'custom-packaging-supplier-audit-checklist',
        'excerpt' => 'A practical custom packaging supplier audit checklist for enterprise buyers reviewing manufacturing capability, materials, printing, quality control, lead time and export packing.',
        'categories' => array(
            'Packaging Guides',
        ),
        'tags' => array(
            'custom packaging supplier',
            'packaging supplier audit',
            'packaging quality control',
            'custom paper boxes',
            'enterprise packaging buyers',
        ),
    );
}

function custom_box_update_supplier_audit_post_details($post_id)
{
    $post_data = custom_box_supplier_audit_post_map();
    $post = get_post($post_id);

    if (!$post) {
        return;
    }

    $post_update = array(
        'ID' => $post_id,
        'post_title' => $post_data['title'],
        'post_name' => $post_data['slug'],
        'post_excerpt' => $post_data['excerpt'],
    );

    if ('publish' !== $post->post_status) {
        $post_update['post_status'] = 'draft';
    }

    wp_update_post($post_update);
}

function custom_box_update_supplier_audit_post_terms($post_id)
{
    $post_data = custom_box_supplier_audit_post_map();
    $category_ids = array();

    foreach ($post_data['categories'] as $category) {
        $term = term_exists($category, 'category');

        if (!$term) {
            $term = wp_insert_term($category, 'category');
        }

        if (!is_wp_error($term)) {
            $category_ids[] = (int) $term['term_id'];
        }
    }

    if (!empty($category_ids)) {
        wp_set_post_terms($post_id, $category_ids, 'category', false);
    }

    wp_set_post_terms($post_id, $post_data['tags'], 'post_tag', false);
}

function custom_box_update_supplier_audit_post_seo($post_id)
{
    update_post_meta($post_id, 'rank_math_title', 'Custom Packaging Supplier Audit Checklist for Enterprise Buyers');
    update_post_meta($post_id, 'rank_math_description', 'Use this custom packaging supplier audit checklist to evaluate materials, printing, sampling, quality control, lead time, communication and export packing before bulk orders.');
    update_post_meta($post_id, 'rank_math_focus_keyword', 'custom packaging supplier audit checklist');
}

function custom_box_supplier_audit_image_map()
{
    return array(
        'custom-packaging-supplier-audit-checklist.webp' => array(
            'base' => 'custom-packaging-supplier-audit-checklist',
            'alt' => 'Custom packaging supplier audit checklist for enterprise buyers',
            'title' => 'Custom Packaging Supplier Audit Checklist',
            'caption' => 'A practical supplier audit checklist for custom paper packaging buyers.',
        ),
        'custom-packaging-supplier-audit-process.webp' => array(
            'base' => 'custom-packaging-supplier-audit-process',
            'alt' => 'Custom packaging supplier audit process for sourcing teams',
            'title' => 'Custom Packaging Supplier Audit Process',
            'caption' => 'Supplier audit process for reducing custom packaging sourcing risk.',
        ),
        'packaging-material-printing-finishing-audit.webp' => array(
            'base' => 'packaging-material-printing-finishing-audit',
            'alt' => 'Packaging material printing and finishing audit points',
            'title' => 'Packaging Material Printing Finishing Audit',
            'caption' => 'Material, printing and finishing checks for custom packaging projects.',
        ),
        'custom-packaging-quality-control-export-packing.webp' => array(
            'base' => 'custom-packaging-quality-control-export-packing',
            'alt' => 'Custom packaging quality control and export packing inspection',
            'title' => 'Custom Packaging Quality Control Export Packing',
            'caption' => 'Quality control and export packing checks before bulk packaging shipment.',
        ),
    );
}

function custom_box_update_supplier_audit_images($post_id)
{
    $post = get_post($post_id);

    if (!$post) {
        return;
    }

    $found = array();
    $missing = array();

    foreach (custom_box_supplier_audit_image_map() as $filename => $image) {
        $attachment_id = custom_box_find_attachment_by_base_filename($image['base']);

        if (!$attachment_id) {
            $missing[] = $filename;
            continue;
        }

        custom_box_update_attachment_seo_metadata($attachment_id, $image);

        $found[$filename] = array(
            'id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'alt' => $image['alt'],
            'base' => $image['base'],
        );
    }

    update_option('custom_box_supplier_audit_missing_images', $missing, false);

    if (isset($found['custom-packaging-supplier-audit-checklist.webp'])) {
        set_post_thumbnail($post_id, $found['custom-packaging-supplier-audit-checklist.webp']['id']);
    }

    $content = custom_box_insert_supplier_audit_figures((string) $post->post_content, $found);

    if ($content !== $post->post_content) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $content,
        ));
    }
}

function custom_box_insert_supplier_audit_figures($content, $found)
{
    $figure_map = array(
        'custom-packaging-supplier-audit-checklist.webp' => array(
            'pattern' => '~(<h[2-3][^>]*>\s*(?:<[^>]+>)*\s*Custom Packaging Supplier Audit Checklist\s*(?:</[^>]+>)*\s*</h[2-3]>)~i',
        ),
        'packaging-material-printing-finishing-audit.webp' => array(
            'pattern' => '~(<h[2-3][^>]*>\s*(?:<[^>]+>)*\s*3\.\s*Printing and Finishing Capability\s*(?:</[^>]+>)*\s*</h[2-3]>)~i',
        ),
        'custom-packaging-quality-control-export-packing.webp' => array(
            'pattern' => '~(<h[2-3][^>]*>\s*(?:<[^>]+>)*\s*5\.\s*Quality Control Process\s*(?:</[^>]+>)*\s*</h[2-3]>)~i',
        ),
        'custom-packaging-supplier-audit-process.webp' => array(
            'pattern' => '~(<h[2-3][^>]*>\s*(?:<[^>]+>)*\s*How Enterprise Buyers Can Reduce Packaging Sourcing Risk\s*(?:</[^>]+>)*\s*</h[2-3]>)~i',
        ),
    );

    foreach ($figure_map as $filename => $config) {
        if (!isset($found[$filename])) {
            continue;
        }

        if (!preg_match($config['pattern'], $content)) {
            continue;
        }

        $content = custom_box_remove_existing_supplier_audit_figure($content, $found[$filename]['base']);
        $figure = custom_box_supplier_audit_figure($found[$filename]['url'], $found[$filename]['alt'], $found[$filename]['base']);
        $content = preg_replace($config['pattern'], '$1' . $figure, $content, 1);
    }

    return $content;
}

function custom_box_remove_existing_supplier_audit_figure($content, $base)
{
    $quoted_base = preg_quote($base, '~');

    $content = preg_replace('~\s*<figure[^>]*data-vpn-blog-image="' . $quoted_base . '"[^>]*>.*?</figure>\s*~is', "\n", $content);
    $content = custom_box_remove_existing_figure_for_image($content, $base);

    return $content;
}

function custom_box_supplier_audit_figure($url, $alt, $base)
{
    return "\n<figure data-vpn-blog-image=\"" . esc_attr($base) . "\">\n  <img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($alt) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\">\n</figure>\n";
}

function custom_box_supplier_audit_sync_notice()
{
    if (!current_user_can('edit_posts')) {
        return;
    }

    $missing = get_option('custom_box_supplier_audit_missing_images', array());

    if (empty($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Supplier audit post sync:</strong> Missing image(s): ' . esc_html(implode(', ', $missing)) . '</p></div>';
}

add_action('admin_notices', 'custom_box_supplier_audit_sync_notice');
