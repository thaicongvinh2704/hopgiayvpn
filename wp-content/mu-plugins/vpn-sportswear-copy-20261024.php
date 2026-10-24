<?php
/**
 * Plugin Name: VPN Sportswear Product Editorial Release
 * Description: Scoped, versioned copy and related-product update for the approved sportswear product.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;

function vpn_sportswear_release_data(): array {
    static $data;
    if (null === $data) {
        $data = json_decode((string) file_get_contents(__DIR__ . '/vpn-sportswear-copy-20261024/release.json'), true);
        $data = is_array($data) ? $data : array();
    }
    return $data;
}

function vpn_sportswear_release_content(): string {
    $path = __DIR__ . '/vpn-sportswear-copy-20261024/content.html';
    return is_readable($path) ? str_replace('{{HOME}}', untrailingslashit(home_url('/')), (string) file_get_contents($path)) : '';
}

function vpn_sportswear_release_hash(string $content): string {
    // The same reviewed text can have different local/hosting base URLs.
    $content = str_replace(array(untrailingslashit(home_url('/')), 'https://hopgiayvpn.com'), 'http://localhost/hopgiayvpn', $content);
    return hash('sha256', str_replace("\r\n", "\n", $content));
}

function vpn_sportswear_release_apply(): array {
    if (!current_user_can('manage_options')) { return array('error' => 'Administrator access required.'); }
    $data = vpn_sportswear_release_data();
    $content = vpn_sportswear_release_content();
    if (!$data || !$content) { return array('error' => 'Release files missing.'); }
    $post = get_page_by_path($data['slug'], OBJECT, 'product');
    if (!$post || 'publish' !== $post->post_status) { return array('error' => 'Existing published product required; no product created.'); }
    $id = (int) $post->ID;
    if ($data['version'] === get_post_meta($id, '_vpn_sportswear_release_version', true)) {
        return array('status' => 'already applied', 'id' => $id);
    }
    if (!defined('RANK_MATH_VERSION')) { return array('error' => 'Rank Math is required for the reviewed SEO fields.'); }
    if (!in_array(vpn_sportswear_release_hash($post->post_content), $data['reviewed_before_hashes'], true)
        || $data['reviewed_before_excerpt'] !== $post->post_excerpt
        || $data['reviewed_before_title'] !== get_post_meta($id, 'rank_math_title', true)
        || $data['reviewed_before_description'] !== get_post_meta($id, 'rank_math_description', true)) {
        return array('error' => 'Product differs from reviewed version; preserve it for manual review.', 'id' => $id);
    }
    $category = get_term_by('slug', $data['category'], 'product_cat');
    if (!$category) { return array('error' => 'Fashion category unavailable.'); }
    $meta = array(
        'rank_math_title' => $data['title'], 'rank_math_description' => $data['description'],
        'rank_math_focus_keyword' => $data['keyword'],
        'rank_math_facebook_title' => $data['title'], 'rank_math_facebook_description' => $data['description'],
        'rank_math_twitter_title' => $data['title'], 'rank_math_twitter_description' => $data['description'],
        'rank_math_primary_product_cat' => (int) $category->term_id,
        '_custom_box_product_hero_bullets' => $data['hero_bullets'],
    );
    $before = array('id' => $id, 'content' => $post->post_content, 'excerpt' => $post->post_excerpt,
        'title' => $post->post_title, 'slug' => $post->post_name, 'status' => $post->post_status,
        'date' => $post->post_date, 'date_gmt' => $post->post_date_gmt,
        'categories' => wp_get_post_terms($id, 'product_cat', array('fields' => 'ids')), 'meta' => array());
    foreach ($meta as $key => $value) {
        $before['meta'][$key] = array('exists' => metadata_exists('post', $id, $key), 'value' => get_post_meta($id, $key, true));
    }
    $backup_key = 'vpn_sportswear_release_before_' . $id;
    if (false === get_option($backup_key, false) && !add_option($backup_key, $before, '', false)) {
        return array('error' => 'Could not save before-state.');
    }
    $saved = wp_update_post(wp_slash(array('ID' => $id, 'post_content' => $content, 'post_excerpt' => $data['excerpt'],
        'post_date' => $post->post_date, 'post_date_gmt' => $post->post_date_gmt, 'edit_date' => true)), true);
    if (is_wp_error($saved)) { return array('error' => $saved->get_error_message()); }
    foreach ($meta as $key => $value) { update_post_meta($id, $key, $value); }
    $terms = wp_set_object_terms($id, array((int) $category->term_id), 'product_cat', true);
    if (is_wp_error($terms)) { return array('error' => $terms->get_error_message()); }
    clean_post_cache($id);
    wc_delete_product_transients($id);
    foreach ($meta as $key => $value) {
        if ((string) maybe_serialize(get_post_meta($id, $key, true)) !== (string) maybe_serialize($value)) {
            return array('error' => 'Metadata readback failed: ' . $key);
        }
    }
    $after = get_post($id);
    if ($after->post_content !== $content || $after->post_excerpt !== $data['excerpt']
        || $after->post_status !== $before['status'] || $after->post_name !== $before['slug']
        || $after->post_title !== $before['title'] || $after->post_date !== $before['date']
        || !has_term($data['category'], 'product_cat', $id)) {
        return array('error' => 'Product readback failed.', 'id' => $id);
    }
    update_post_meta($id, '_vpn_sportswear_release_version', $data['version']);
    return array('status' => 'applied', 'id' => $id, 'url' => get_permalink($id), 'backup_option' => $backup_key);
}

add_action('admin_init', static function () {
    if (!current_user_can('manage_options') || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) { return; }
    $result = vpn_sportswear_release_apply();
    if (isset($result['error'])) { update_option('vpn_sportswear_release_notice', $result['error'], false); }
    else { delete_option('vpn_sportswear_release_notice'); }
}, 95);

add_action('admin_notices', static function () {
    if (!current_user_can('manage_options')) { return; }
    $notice = get_option('vpn_sportswear_release_notice');
    if ($notice) { echo '<div class="notice notice-warning"><p>' . esc_html('Sportswear copy update: ' . $notice) . '</p></div>'; }
});

add_filter('woocommerce_related_products', static function ($ids, $product_id, $args) {
    $data = vpn_sportswear_release_data();
    if (($data['slug'] ?? '') !== get_post_field('post_name', $product_id)) { return $ids; }
    $selected = array();
    foreach ($data['related'] as $slug) {
        $post = get_page_by_path($slug, OBJECT, 'product');
        $product = $post && 'publish' === $post->post_status ? wc_get_product($post->ID) : false;
        if ($product && $product->is_visible() && !in_array($post->ID, $args['excluded_ids'] ?? array(), true)) {
            $selected[] = (int) $post->ID;
        }
    }
    return array_slice($selected, 0, (int) ($args['limit'] ?? 8));
}, 20, 3);

/** Include gallery assets in this release even on hosts without the older theme gallery module. */
function vpn_sportswear_workshop_fallback_needed(): bool {
    if (!is_singular('product') || 'custom-sportswear-packaging-box' !== get_post_field('post_name', get_queried_object_id())) { return false; }
    return !function_exists('custom_box_factory_gallery_context') || !custom_box_factory_gallery_context();
}

add_action('wp_enqueue_scripts', static function () {
    if (!vpn_sportswear_workshop_fallback_needed()) { return; }
    $path = 'vpn-sportswear-copy-20261024/assets/factory-gallery.css';
    wp_enqueue_style('vpn-sportswear-workshop', plugins_url($path, __FILE__), array('main-style'), (string) filemtime(__DIR__ . '/' . $path));
}, 35);

add_action('get_template_part_template-parts/home/footer-cta', static function () {
    if (!vpn_sportswear_workshop_fallback_needed()) { return; }
    $assets = plugins_url('vpn-sportswear-copy-20261024/assets/', __FILE__);
    $images = array(
        array('file' => 'vpn-factory-offset-printing-20261009.webp', 'width' => 1280, 'height' => 960,
            'alt' => 'Offset printing machine in the paper packaging workshop', 'caption' => 'Offset printing equipment'),
        array('file' => 'vpn-factory-box-finishing-20261009.webp', 'width' => 1280, 'height' => 720,
            'alt' => 'Workers and red gift boxes at the box finishing workstations', 'caption' => 'Box finishing workstations'),
    );
    ?>
    <section id="manufacturing-workshop" class="vpn-factory-gallery vpn-factory-gallery--two" aria-labelledby="manufacturing-workshop-title">
        <div class="vpn-factory-gallery__inner">
            <div class="vpn-factory-gallery__heading">
                <span class="vpn-factory-gallery__eyebrow">VPN Packaging Workshop</span>
                <h2 id="manufacturing-workshop-title">Printing and Box Finishing at VPN</h2>
                <p>A look at our packaging workshop. For your apparel order, we review garment fit, artwork and the approved sample before bulk production.</p>
            </div>
            <div class="vpn-factory-gallery__grid">
                <?php foreach ($images as $image) : ?>
                    <figure class="vpn-factory-gallery__card">
                        <a href="<?php echo esc_url($assets . $image['file']); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr('View image: ' . $image['caption']); ?>">
                            <img class="vpn-factory-gallery__image" src="<?php echo esc_url($assets . $image['file']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" width="<?php echo (int) $image['width']; ?>" height="<?php echo (int) $image['height']; ?>" loading="lazy" decoding="async">
                        </a>
                        <figcaption><?php echo esc_html($image['caption']); ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <p class="vpn-factory-gallery__cta"><a href="<?php echo esc_url(home_url('/contact/#quote')); ?>">Discuss Your Packaging Project <span aria-hidden="true">&rarr;</span></a></p>
        </div>
    </section>
    <?php
});
