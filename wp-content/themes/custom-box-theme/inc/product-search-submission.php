<?php
/** Submit product batches to Google through the existing Rank Math Search Console connection. */
defined('ABSPATH') || exit;

function custom_box_product_submission_batches(): array {
    return (array) get_option('custom_box_product_google_batches', array());
}

function custom_box_product_submission_url(string $key): string {
    return add_query_arg('vpn_product_submission_sitemap', $key, home_url('/'));
}

function custom_box_product_submission_eligible(int $id): bool {
    $post = get_post($id);
    if (!$post || 'product' !== $post->post_type || 'publish' !== $post->post_status || $post->post_password) {
        return false;
    }
    if (in_array('noindex', (array) get_post_meta($id, 'rank_math_robots', true), true) || !class_exists('RankMath\\Helper') || !\RankMath\Helper::is_post_indexable($id)) {
        return false;
    }
    $canonical = (string) get_post_meta($id, 'rank_math_canonical_url', true);
    return !$canonical || untrailingslashit($canonical) === untrailingslashit(get_permalink($id));
}

/** Keep batch sitemaps discoverable through the normal, already registered sitemap index. */
function custom_box_product_submission_index(string $xml): string {
    foreach (custom_box_product_submission_batches() as $key => $batch) {
        $xml .= '<sitemap><loc>' . esc_xml(custom_box_product_submission_url($key)) . '</loc><lastmod>' . esc_xml(gmdate('c', $batch['time'])) . '</lastmod></sitemap>' . "\n";
    }
    return $xml;
}
add_filter('rank_math/sitemap/index', 'custom_box_product_submission_index');

/** Public XML contains only published, indexable product URLs; no credentials or admin data. */
function custom_box_product_submission_xml(array $ids): string {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($ids as $id) {
        if (custom_box_product_submission_eligible((int) $id)) {
            $xml .= '<url><loc>' . esc_xml(get_permalink($id)) . '</loc><lastmod>' . esc_xml(get_post_modified_time('c', true, $id)) . '</lastmod></url>';
        }
    }
    return $xml . '</urlset>';
}

function custom_box_product_submission_serve(): void {
    if (!isset($_GET['vpn_product_submission_sitemap'])) {
        return;
    }
    $key = is_string($_GET['vpn_product_submission_sitemap']) ? wp_unslash($_GET['vpn_product_submission_sitemap']) : '';
    $batches = custom_box_product_submission_batches();
    if (!preg_match('/^[a-f0-9]{32}$/D', $key) || !isset($batches[$key])) {
        status_header(404);
        exit;
    }
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    nocache_headers();
    status_header(200);
    header('Content-Type: application/xml; charset=UTF-8');
    echo custom_box_product_submission_xml($batches[$key]['ids']);
    exit;
}
add_action('template_redirect', 'custom_box_product_submission_serve', 0);

function custom_box_product_submission_send(array $ids) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('permission', 'Administrator access is required.');
    }
    $ids = array_values(array_unique(array_filter(array_map('absint', $ids))));
    sort($ids);
    if (!$ids || count($ids) > 200) {
        return new WP_Error('selection', 'Select between 1 and 200 products per request.');
    }
    foreach ($ids as $id) {
        if (!custom_box_product_submission_eligible($id)) {
            return new WP_Error('product', 'Selection includes an unpublished, protected, noindex or noncanonical product. No request was sent.');
        }
    }
    if ('production' !== wp_get_environment_type() || !get_option('blog_public')) {
        return new WP_Error('environment', 'Send requests from the public production website. Local and staging previews do not send to Google.');
    }
    if (!class_exists('RankMath\\Google\\Api') || !class_exists('RankMath\\Google\\Authentication') || !\RankMath\Google\Authentication::is_authorized()) {
        return new WP_Error('connection', 'Connect Google in Rank Math SEO > General Settings > Analytics, then select the Search Console property and retry.');
    }
    $modules = (array) get_option('rank_math_modules', array());
    if (!in_array('sitemap', $modules, true) || !class_exists('RankMath\\Sitemap\\Sitemap')) {
        return new WP_Error('sitemap', 'Enable the Rank Math Sitemap module before submitting.');
    }
    $profile = (array) get_option('rank_math_google_analytic_profile', array());
    $property = isset($profile['profile']) && is_string($profile['profile']) ? $profile['profile'] : '';
    $home = trailingslashit(home_url('/'));
    $host = strtolower((string) wp_parse_url($home, PHP_URL_HOST));
    if (0 === strpos($property, 'sc-domain:')) {
        $domain = strtolower(substr($property, 10));
        $matches = $domain && ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain);
    } else {
        $matches = $property && 0 === strpos($home, trailingslashit($property)) && wp_parse_url($property, PHP_URL_HOST) === wp_parse_url($home, PHP_URL_HOST);
    }
    if (!$matches) {
        return new WP_Error('property', 'Select the Search Console property for this website in Rank Math Analytics before sending.');
    }
    $key = md5(implode(',', $ids));
    $batches = custom_box_product_submission_batches();
    $batch = array('ids' => $ids, 'time' => time(), 'status' => 'pending', 'code' => 0);
    $batches[$key] = $batch;
    // Keep recent batches in the index; all products remain in the standard product sitemap.
    uasort($batches, static function ($a, $b) { return $b['time'] <=> $a['time']; });
    update_option('custom_box_product_google_batches', array_slice($batches, 0, 20, true), false);
    if (!custom_box_flush_rank_math_sitemap_cache()) {
        $batches[$key]['status'] = 'failed';
        update_option('custom_box_product_google_batches', array_slice($batches, 0, 20, true), false);
        return new WP_Error('cache', 'Could not refresh the Rank Math sitemap index. No Google request was sent.');
    }
    $sitemap = home_url('/' . \RankMath\Sitemap\Sitemap::get_sitemap_index_slug() . '.xml');
    $api_url = 'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode($property) . '/sitemaps/' . rawurlencode($sitemap);
    $response_hook = 'rank_math/analytics/handle_vpn_product_submission_response';
    $observe_response = static function ($response) use (&$batch, $api_url) {
        if (($response['http_verb'] ?? '') === 'PUT' && ($response['url'] ?? '') === $api_url) {
            $batch['code'] = (int) ($response['code'] ?? 0);
        }
    };
    $api = \RankMath\Google\Api::get();
    $api->set_workflow('vpn_product_submission');
    add_action($response_hook, $observe_response);
    try {
        $api->add_sitemap($property, $sitemap);
        $batch['status'] = $batch['code'] >= 200 && $batch['code'] < 300 ? 'submitted' : 'failed';
    } catch (Throwable $error) {
        $batch['status'] = 'failed';
    } finally {
        remove_action($response_hook, $observe_response);
        $api->set_workflow('');
    }
    $batches = custom_box_product_submission_batches();
    $batches[$key] = $batch;
    update_option('custom_box_product_google_batches', $batches, false);
    return $key;
}

function custom_box_product_submission_bulk_actions(array $actions): array {
    if (current_user_can('manage_options')) {
        $actions['vpn_google_product_sitemap'] = 'Google: Submit product batch sitemap';
    }
    return $actions;
}
add_filter('bulk_actions-edit-product', 'custom_box_product_submission_bulk_actions');

function custom_box_product_submission_bulk_handle($redirect, $action, $ids) {
    if ('vpn_google_product_sitemap' !== $action) { return $redirect; }
    check_admin_referer('bulk-posts');
    $result = custom_box_product_submission_send($ids);
    if (is_wp_error($result)) {
        set_transient('vpn_google_product_submission_error_' . get_current_user_id(), $result->get_error_message(), 5 * MINUTE_IN_SECONDS);
    }
    return admin_url('tools.php?page=vpn-product-google-submission');
}
add_filter('handle_bulk_actions-edit-product', 'custom_box_product_submission_bulk_handle', 10, 3);

add_action('admin_menu', static function () {
    add_management_page('Product Google Submission', 'Product Google Submission', 'manage_options', 'vpn-product-google-submission', 'custom_box_product_submission_page');
});

add_action('admin_post_vpn_google_mailer_submission', static function () {
    if (!current_user_can('manage_options')) { wp_die('Administrator access is required.', '', array('response' => 403)); }
    check_admin_referer('vpn_google_mailer_submission');
    $ids = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'numberposts' => 6, 'fields' => 'ids', 'meta_key' => '_vpn_sample_import', 'meta_value' => 'mailer-products-20261008'));
    $result = count($ids) === 5 ? custom_box_product_submission_send($ids) : new WP_Error('batch', 'Deploy the five mailer products first using Product Sample Deploy.');
    if (is_wp_error($result)) {
        set_transient('vpn_google_product_submission_error_' . get_current_user_id(), $result->get_error_message(), 5 * MINUTE_IN_SECONDS);
    }
    wp_safe_redirect(admin_url('tools.php?page=vpn-product-google-submission'));
    exit;
});

function custom_box_product_submission_page(): void {
    if (!current_user_can('manage_options')) { return; }
    $error_key = 'vpn_google_product_submission_error_' . get_current_user_id();
    $error = get_transient($error_key);
    delete_transient($error_key);
    echo '<div class="wrap"><h1>Product Google Submission</h1>';
    if ($error) { echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>'; }
    echo '<p>Send a product batch sitemap to Google Search Console. Google acknowledgement confirms sitemap receipt. It does not confirm indexing or send the individual URL Inspection “Request indexing” action.</p>';
    echo '<p>Connect Google and select this website in <a href="' . esc_url(admin_url('admin.php?page=rank-math-options-general#setting-panel-analytics')) . '">Rank Math Analytics</a> once. The existing connection is reused; no API key upload is needed.</p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="vpn_google_mailer_submission">';
    wp_nonce_field('vpn_google_mailer_submission');
    submit_button('Submit 5 Mailer Products to Google', 'primary', 'submit', false);
    echo '</form><p>For other products: select them in <a href="' . esc_url(admin_url('edit.php?post_type=product')) . '">Products</a>, choose <strong>Google: Submit product batch sitemap</strong> under Bulk actions and click Apply (up to 200 per request).</p>';
    echo '<h2>Recent requests</h2><table class="widefat striped"><thead><tr><th>Time (UTC)</th><th>Products</th><th>Google response</th><th>Batch sitemap</th></tr></thead><tbody>';
    foreach (custom_box_product_submission_batches() as $key => $batch) {
        $label = 'submitted' === $batch['status'] ? 'Sitemap received by Google' : ('failed' === $batch['status'] ? 'Not accepted. Check the connection and Search Console permissions, then retry.' : 'Submission pending');
        echo '<tr><td>' . esc_html(gmdate('Y-m-d H:i:s', $batch['time'])) . '</td><td>' . count($batch['ids']) . '</td><td>' . esc_html($label . ' (HTTP ' . $batch['code'] . ')') . '</td><td><a href="' . esc_url(custom_box_product_submission_url($key)) . '" target="_blank" rel="noopener">View XML</a></td></tr>';
    }
    echo '</tbody></table><p>The batch sitemap is linked from the main Rank Math sitemap index. The request registers that index through the official Search Console API. To request individual URLs, use URL Inspection in Search Console.</p></div>';
}
