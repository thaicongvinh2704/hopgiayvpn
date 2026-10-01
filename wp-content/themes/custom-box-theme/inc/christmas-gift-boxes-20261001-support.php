<?php
/** Shared company photographs and release integrity checks for the October batch. */
defined('ABSPATH') || exit;

const VPN_XMAS_20261001_RELEASE = '2026-10-01-christmas-v2';

function vpn_xmas_20261001_source_hash(array $product): string {
    $source = get_template_directory() . '/inc/product-content/christmas-gift-boxes-20261001/' . $product['content_file'];
    return hash('sha256', VPN_XMAS_20261001_RELEASE . wp_json_encode($product) . (string) file_get_contents($source));
}

function vpn_xmas_20261001_company_images(): array {
    return array(
        'worker' => array(
            'file' => 'vpn-christmas-factory-worker.webp',
            'title' => 'VPN packaging worker operating production equipment',
            'alt' => 'Worker wearing a VPN uniform operating printing equipment',
            'caption' => 'Company photograph used on VPN\'s About page: a worker at printing equipment. This is not a production photograph of the Christmas box concept.',
        ),
        'area' => array(
            'file' => 'vpn-christmas-production-area.webp',
            'title' => 'VPN packaging production area',
            'alt' => 'Packaging production area shown on the VPN company About page',
            'caption' => 'Company photograph used on VPN\'s About page: the packaging production area. Final materials and capabilities for your order are confirmed in the project brief.',
        ),
    );
}

function vpn_xmas_20261001_about_url(): string {
    foreach (array('about', 'about-us') as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if ($page && 'publish' === $page->post_status) {
            return get_permalink($page);
        }
    }
    throw new RuntimeException('A published VPN About page is required for the company evidence link.');
}

function vpn_xmas_20261001_company_attachment(array $image): int {
    global $wpdb;
    $relative = '2026/10/' . $image['file'];
    $uploads = wp_upload_dir();
    $source = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
    $target = trailingslashit($uploads['basedir']) . $relative;
    if (!is_readable($source) || !wp_mkdir_p(dirname($target))) {
        throw new RuntimeException('Missing company image or unwritable directory: ' . $relative);
    }
    if (file_exists($target)) {
        if (hash_file('sha256', $source) !== hash_file('sha256', $target)) {
            throw new RuntimeException('A different company upload already exists: ' . $relative);
        }
    } elseif (!copy($source, $target)) {
        throw new RuntimeException('Could not copy company image: ' . $relative);
    }
    $id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT pm.post_id FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_wp_attached_file' AND pm.meta_value = %s AND p.post_type = 'attachment' ORDER BY pm.post_id LIMIT 1",
        $relative
    ));
    if (!$id) {
        $result = wp_insert_attachment(array(
            'post_mime_type' => 'image/webp', 'post_status' => 'inherit', 'post_parent' => 0,
            'post_title' => $image['title'], 'post_excerpt' => $image['caption'],
        ), $target, 0, true);
        if (is_wp_error($result)) {
            throw new RuntimeException($result->get_error_message());
        }
        $id = (int) $result;
    }
    $result = wp_update_post(array('ID' => $id, 'post_title' => $image['title'], 'post_excerpt' => $image['caption']), true);
    if (is_wp_error($result)) {
        throw new RuntimeException($result->get_error_message());
    }
    update_post_meta($id, '_wp_attachment_image_alt', $image['alt']);
    update_post_meta($id, '_vpn_christmas_20261001_company_image', $image['file']);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    if (!wp_get_attachment_metadata($id)) {
        $metadata = wp_generate_attachment_metadata($id, $target);
        if (!is_array($metadata)) {
            throw new RuntimeException('Company image metadata could not be generated: ' . $relative);
        }
        wp_update_attachment_metadata($id, $metadata);
    }
    return $id;
}

function vpn_xmas_20261001_factory_proof(): string {
    $html = '<div class="christmas-company-evidence">';
    foreach (vpn_xmas_20261001_company_images() as $role => $image) {
        $id = vpn_xmas_20261001_company_attachment($image);
        $html .= '<!-- christmas-company-image:' . $role . ' -->';
        $html .= '<figure class="christmas-factory-proof-figure" style="max-width:840px;margin:24px auto;">';
        $html .= wp_get_attachment_image($id, 'large', false, array(
            'loading' => 'lazy', 'decoding' => 'async', 'alt' => $image['alt'],
            'style' => 'display:block;width:100%;height:auto;',
            'sizes' => '(max-width: 767px) calc(100vw - 36px), 840px',
        ));
        $html .= '<figcaption>' . esc_html($image['caption']) . '</figcaption></figure>';
    }
    $html .= '<p>Read <a href="' . esc_url(vpn_xmas_20261001_about_url()) . '">about VPN Packaging and its Vietnam production operations</a> for company background. For a wholesale packaging enquiry, agree the structure and filled sample before approving production.</p></div>';
    return $html;
}

function vpn_xmas_20261001_verify_release(int $id, array $expected): array {
    global $wpdb;
    $failures = array();
    $content = (string) get_post_field('post_content', $id);
    if (vpn_xmas_20261001_source_hash($expected) !== get_post_meta($id, '_vpn_christmas_20261001_source_hash', true)
        || hash('sha256', $content) !== get_post_meta($id, '_vpn_christmas_20261001_content_hash', true)) {
        $failures[] = $expected['slug'] . ': source or saved-content hash mismatch';
    }
    foreach (array('rank_math_title' => 'seo_title', 'rank_math_description' => 'seo_description', 'rank_math_focus_keyword' => 'keyword') as $key => $field) {
        if ($expected[$field] !== get_post_meta($id, $key, true)) {
            $failures[] = $expected['slug'] . ': incorrect ' . $key;
        }
    }
    if ($expected['short'] !== get_post_field('post_excerpt', $id)) {
        $failures[] = $expected['slug'] . ': incorrect short description';
    }
    if (false === stripos(wp_strip_all_tags($content), $expected['keyword'])
        || false === stripos($content, 'Ho Chi Minh City, Vietnam')
        || false === strpos($content, esc_url(vpn_xmas_20261001_about_url()))
        || 2 !== substr_count($content, '<!-- christmas-company-image:')) {
        $failures[] = $expected['slug'] . ': missing main keyword, location or company evidence';
    }
    if (get_permalink($id) !== get_post_meta($id, 'rank_math_canonical_url', true)) {
        $failures[] = $expected['slug'] . ': incorrect environment-specific canonical';
    }
    foreach (vpn_xmas_20261001_company_images() as $image) {
        $relative = '2026/10/' . $image['file'];
        $attachment = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s ORDER BY post_id LIMIT 1", $relative
        ));
        $file = $attachment ? get_attached_file($attachment) : '';
        $source = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
        $metadata = $attachment ? wp_get_attachment_metadata($attachment) : false;
        if (!$attachment || !$file || !is_file($file) || !is_file($source)
            || hash_file('sha256', $file) !== hash_file('sha256', $source)
            || !is_array($metadata) || empty($metadata['width']) || empty($metadata['height'])
            || $image['alt'] !== get_post_meta($attachment, '_wp_attachment_image_alt', true)
            || false === strpos($content, pathinfo($image['file'], PATHINFO_FILENAME))) {
            $failures[] = $expected['slug'] . ': company photograph is not complete: ' . $image['file'];
        }
    }
    return $failures;
}
