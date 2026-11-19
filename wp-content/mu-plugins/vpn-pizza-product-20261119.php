<?php
/**
 * Plugin Name: VPN Custom Pizza Packaging Box Release
 * Description: Applies the approved content, specifications and gallery to the existing pizza box product.
 * Version: 2026.11.19.1
 */

defined('ABSPATH') || exit;

function vpn_pizza_release_20261119_apply(): int {
    $record = get_page_by_path('custom-pizza-packaging-box', OBJECT, 'product');
    if (!$record || (int) $record->ID !== 239 || $record->post_status !== 'publish' || !function_exists('wc_get_product')) {
        throw new RuntimeException('The published pizza box product was not found.');
    }

    $product = wc_get_product($record->ID);
    $directory = __DIR__ . '/vpn-pizza-product-20261119';
    $html = file_get_contents($directory . '/product-content.html');
    $manifest = json_decode(file_get_contents($directory . '/images.json'), true, 512, JSON_THROW_ON_ERROR);
    if (!$product || !$html || count($manifest) !== 6) {
        throw new RuntimeException('The approved pizza box release is incomplete.');
    }

    $id = $product->get_id();
    $content = wp_kses_post(strtr(trim(str_replace("\r\n", "\n", $html)), [
        '{{HOME}}' => untrailingslashit(home_url('/')),
        '{{ASSETS}}' => get_template_directory_uri() . '/assets/images',
    ]));
    $excerpt = 'Square folding pizza box made in Ho Chi Minh City, Vietnam, for bulk orders. Choose the size and logo print, then approve a sample before production.';
    $meta = [
        'rank_math_title' => 'Custom Pizza Packaging Box Made in Vietnam',
        'rank_math_description' => 'Custom printed pizza boxes made in Vietnam for restaurant chains and packaging distributors, with custom sizes and samples for bulk orders.',
        'rank_math_focus_keyword' => 'custom pizza packaging box',
    ];
    $spec_values = [
        'Feature' => 'Square folding box with a hinged lid and custom logo print',
        'Industrial Use' => 'Pizza takeaway and delivery for restaurants, chains and distributors',
        'Paper Type' => 'Corrugated board with kraft or white exterior options. Confirm food-contact requirements by project.',
        'Liner Type' => 'Optional liner, selected to suit the pizza and intended food contact',
        'Size' => 'Made to order. Confirm clear internal length, width and height on the dieline.',
        'Thickness' => 'E-flute or B-flute options. Confirm board grade and thickness on the approved specification.',
        'Minimum Order Quantity (MOQ)' => 'Pizza box range reference: 1,000 boxes. Confirm the minimum per size and artwork in the quote.',
    ];
    $specs = $product->get_meta('_custom_box_product_specs', true);
    if (!is_array($specs) || array_diff_key($spec_values, array_flip(array_column($specs, 'label')))) {
        throw new RuntimeException('The pizza box specification labels differ from the reviewed product.');
    }
    $meta['_custom_box_product_specs'] = array_map(static function (array $row) use ($spec_values): array {
        $row['value'] = $spec_values[$row['label']] ?? $row['value'];
        return $row;
    }, $specs);

    foreach ($manifest as $image) {
        $file = $directory . '/images/' . $image['filename'];
        if (!is_file($file) || hash_file('sha256', $file) !== $image['sha256']) {
            throw new RuntimeException('Missing or invalid gallery asset: ' . $image['filename']);
        }
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachments = [];
    foreach ($manifest as $image) {
        $attachments[] = vpn_pizza_release_20261119_image($id, $directory . '/images/' . $image['filename'], $image);
    }
    $gallery = array_slice($attachments, 1);
    if ((int) $product->get_image_id('edit') !== $attachments[0]) {
        $product->set_image_id($attachments[0]);
    }
    if ($product->get_gallery_image_ids('edit') !== $gallery) {
        $product->set_gallery_image_ids($gallery);
    }
    if (str_replace("\r\n", "\n", $product->get_description('edit')) !== $content) {
        $product->set_description($content);
    }
    if ($product->get_short_description('edit') !== $excerpt) {
        $product->set_short_description($excerpt);
    }
    $meta_changed = false;
    foreach ($meta as $key => $value) {
        if ($product->get_meta($key, true) !== $value) {
            $product->update_meta_data($key, $value);
            $meta_changed = true;
        }
    }
    if ($product->get_changes() || $meta_changed) {
        $product->save();
        wc_delete_product_transients($id);
    }

    $saved = wc_get_product($id);
    if (!$saved || str_replace("\r\n", "\n", $saved->get_description('edit')) !== $content || $saved->get_short_description() !== $excerpt
        || $saved->get_slug() !== $record->post_name || $saved->get_status() !== $record->post_status
        || array_merge([(int) $saved->get_image_id()], $saved->get_gallery_image_ids()) !== $attachments) {
        throw new RuntimeException('Could not verify the saved pizza box product.');
    }
    foreach ($meta as $key => $value) {
        if ($saved->get_meta($key, true) !== $value) {
            throw new RuntimeException('Could not verify product data: ' . $key);
        }
    }
    return $id;
}

function vpn_pizza_release_20261119_image(int $product_id, string $file, array $image): int {
    $marker = '_vpn_pizza_gallery_image_checksum';
    $matches = get_posts([
        'post_type' => 'attachment',
        'post_status' => ['inherit', 'private', 'trash'],
        'post_parent' => $product_id,
        'meta_key' => $marker,
        'meta_value' => $image['sha256'],
        'fields' => 'ids',
        'numberposts' => 2,
    ]);
    if (count($matches) > 1) {
        throw new RuntimeException('Duplicate gallery asset: ' . $image['filename']);
    }

    if ($matches) {
        $id = (int) $matches[0];
    } else {
        $stage = wp_tempnam($image['filename']);
        if (!$stage || !copy($file, $stage)) {
            if ($stage) {
                wp_delete_file($stage);
            }
            throw new RuntimeException('Could not stage gallery asset: ' . $image['filename']);
        }
        $id = media_handle_sideload(['name' => $image['filename'], 'tmp_name' => $stage], $product_id, null, [
            'post_title' => $image['title'],
            'meta_input' => [$marker => $image['sha256'], '_wp_attachment_image_alt' => $image['alt']],
        ]);
        if (is_wp_error($id)) {
            if (is_file($stage)) {
                wp_delete_file($stage);
            }
            throw new RuntimeException($id->get_error_message());
        }
    }

    $attached = get_attached_file($id);
    if (!wp_attachment_is_image($id) || get_post_status($id) === 'trash' || !$attached || !is_file($attached)
        || hash_file('sha256', $attached) !== $image['sha256']) {
        throw new RuntimeException('Gallery asset could not be verified: ' . $image['filename']);
    }
    if (empty(wp_get_attachment_metadata($id)['sizes']['thumbnail'])) {
        $sizes = wp_update_image_subsizes($id);
        if (is_wp_error($sizes) || empty($sizes['sizes']['thumbnail'])) {
            throw new RuntimeException('Responsive gallery images could not be generated: ' . $image['filename']);
        }
    }
    if (get_post_meta($id, '_wp_attachment_image_alt', true) !== $image['alt']) {
        update_post_meta($id, '_wp_attachment_image_alt', $image['alt']);
    }
    return $id;
}

add_action('admin_init', static function (): void {
    $done = 'vpn_pizza_product_20261119_done';
    $error_key = 'vpn_pizza_product_20261119_error';
    if (!current_user_can('manage_options') || !current_user_can('manage_woocommerce') || wp_doing_ajax() || wp_doing_cron()
        || !in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true)
        || get_option($done) || !empty(get_transient($error_key))) {
        return;
    }

    global $wpdb;
    $lock = 'vpn_pizza_product_' . md5(DB_NAME . $wpdb->prefix);
    if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== '1') {
        return;
    }
    try {
        wp_cache_delete($done, 'options');
        wp_cache_delete('notoptions', 'options');
        if (!get_option($done)) {
            @set_time_limit(120);
            vpn_pizza_release_20261119_apply();
            update_option($done, true, false);
            delete_transient($error_key);
        }
    } catch (Throwable $error) {
        set_transient($error_key, $error->getMessage(), 5 * MINUTE_IN_SECONDS);
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}, 110);

add_action('admin_notices', static function (): void {
    if (current_user_can('manage_options') && $message = get_transient('vpn_pizza_product_20261119_error')) {
        echo '<div class="notice notice-warning"><p>' . esc_html('Pizza box update: ' . $message) . '</p></div>';
    }
});
