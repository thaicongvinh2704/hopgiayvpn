<?php
/** Versioned bakery application category; product URLs and other assignments stay intact. */
defined('ABSPATH') || exit;

function vpn_bakery_category_data(): array {
    static $data;
    if (null === $data) {
        $data = json_decode((string) file_get_contents(__DIR__ . '/bakery-bread-paper-bags-category.json'), true);
        $data = is_array($data) ? $data : array();
    }
    return $data;
}

function vpn_bakery_is_category($term = null): bool {
    if (null === $term) {
        if (!function_exists('is_product_category') || !is_product_category()) { return false; }
        $term = get_queried_object();
    }
    return $term && !is_wp_error($term) && 'product_cat' === ($term->taxonomy ?? '')
        && vpn_bakery_category_data()['slug'] === ($term->slug ?? '');
}

function vpn_bakery_category_url(): string {
    $term = get_term_by('slug', vpn_bakery_category_data()['slug'], 'product_cat');
    $url = $term ? get_term_link($term) : '';
    return $url && !is_wp_error($url) ? $url : home_url('/products/' . vpn_bakery_category_data()['slug'] . '/');
}

/** Resolve by slug, never by environment-specific post IDs. */
function vpn_bakery_product_ids(): array {
    $ids = array();
    foreach (vpn_bakery_category_data()['products'] as $slug) {
        $post = get_page_by_path($slug, OBJECT, 'product');
        if ($post && 'publish' === $post->post_status) { $ids[] = (int) $post->ID; }
    }
    return $ids;
}

function vpn_bakery_sync_category(): array {
    if (!taxonomy_exists('product_cat')) { return array('error' => 'WooCommerce taxonomy unavailable'); }
    $data = vpn_bakery_category_data();
    $parent = get_term_by('slug', $data['parent_slug'], 'product_cat');
    if (!$parent) { return array('error' => 'Paper Bags with Logo category missing'); }
    $posts = array();
    $missing = array();
    foreach ($data['products'] as $slug) {
        $post = get_page_by_path($slug, OBJECT, 'product');
        if (!$post || 'publish' !== $post->post_status || !get_post_thumbnail_id($post->ID)) { $missing[] = $slug; }
        else { $posts[$slug] = $post; }
    }
    if ($missing) { return array('error' => 'Published products or images missing', 'missing' => $missing); }
    $term = get_term_by('slug', $data['slug'], 'product_cat');
    if ($term && !get_term_meta($term->term_id, '_vpn_bakery_category_release', true)) {
        return array('error' => 'An existing unmanaged category uses this slug; review before importing');
    }
    $args = array('name' => $data['name'], 'description' => $data['intro'], 'parent' => (int) $parent->term_id);
    $result = $term ? wp_update_term($term->term_id, 'product_cat', $args)
        : wp_insert_term($data['name'], 'product_cat', array_merge($args, array('slug' => $data['slug'])));
    if (is_wp_error($result)) { return array('error' => $result->get_error_message()); }
    $id = (int) $result['term_id'];
    // Mark ownership immediately so a failed assignment can safely be retried.
    update_term_meta($id, '_vpn_bakery_category_release', $data['version']);
    foreach ($posts as $post) {
        $added = wp_set_object_terms($post->ID, array($id), 'product_cat', true);
        if (is_wp_error($added)) { return array('error' => $added->get_error_message()); }
    }
    $image = (int) get_post_thumbnail_id($posts[$data['hero_product']]->ID);
    $meta = array(
        'rank_math_title' => $data['title'], 'rank_math_description' => $data['description'],
        'rank_math_focus_keyword' => $data['keyword'], 'rank_math_robots' => array('index', 'follow'),
        // Leave canonical storage empty: the filter resolves the correct domain and pagination.
        'rank_math_canonical_url' => '', 'thumbnail_id' => $image, 'custom_box_category_image_id' => $image,
        'rank_math_facebook_title' => $data['title'], 'rank_math_facebook_description' => $data['description'],
        'rank_math_twitter_title' => $data['title'], 'rank_math_twitter_description' => $data['description'],
        'rank_math_facebook_image_id' => $image, 'rank_math_facebook_image' => wp_get_attachment_url($image),
    );
    foreach ($meta as $key => $value) { update_term_meta($id, $key, $value); }
    clean_term_cache($id, 'product_cat');
    $status = array('version' => $data['version'], 'term_id' => $id, 'assigned' => array_map(static function ($post) { return (int) $post->ID; }, array_values($posts)), 'missing' => array());
    update_option('vpn_bakery_category_release', $status, false);
    if (class_exists('RankMath\Sitemap\Cache')) { \RankMath\Sitemap\Cache::invalidate_storage(); }
    return $status;
}

add_action('init', static function () {
    if (defined('VPN_BAKERY_SKIP_SYNC') && VPN_BAKERY_SKIP_SYNC) { return; }
    if ((get_option('vpn_bakery_category_release', array())['version'] ?? '') === vpn_bakery_category_data()['version']) { return; }
    // Avoid repeated imports on visitor requests if a deployment is missing its product data.
    if (get_transient('vpn_bakery_category_sync_attempt')) { return; }
    set_transient('vpn_bakery_category_sync_attempt', 1, 10 * MINUTE_IN_SECONDS);
    $result = vpn_bakery_sync_category();
    update_option('vpn_bakery_category_sync_status', $result, false);
}, 46);

function vpn_bakery_meta_title($value) {
    if (!vpn_bakery_is_category()) { return $value; }
    $title = get_term_meta(get_queried_object_id(), 'rank_math_title', true) ?: vpn_bakery_category_data()['title'];
    $page = max(1, (int) get_query_var('paged'));
    return $page > 1 ? str_replace(' | VPN', ' - Page ' . $page . ' | VPN', $title) : $title;
}
foreach (array('pre_get_document_title', 'rank_math/frontend/title', 'rank_math/opengraph/facebook/title', 'rank_math/opengraph/twitter/title') as $hook) {
    add_filter($hook, 'vpn_bakery_meta_title', 70);
}
foreach (array('rank_math/frontend/description', 'rank_math/opengraph/facebook/description', 'rank_math/opengraph/twitter/description') as $hook) {
    add_filter($hook, static function ($value) {
        return vpn_bakery_is_category() ? (get_term_meta(get_queried_object_id(), 'rank_math_description', true) ?: vpn_bakery_category_data()['description']) : $value;
    }, 70);
}
function vpn_bakery_canonical($value) {
    if (!vpn_bakery_is_category()) { return $value; }
    $page = max(1, (int) get_query_var('paged'));
    return vpn_bakery_category_url() . ($page > 1 ? 'page/' . $page . '/' : '');
}
add_filter('rank_math/frontend/canonical', 'vpn_bakery_canonical', 70);
add_filter('get_canonical_url', 'vpn_bakery_canonical', 70);
add_filter('rank_math/opengraph/facebook/og_url', 'vpn_bakery_canonical', 70);

add_filter('custom_box_archive_product_query_args', static function ($args, $term, $orderby) {
    if (!vpn_bakery_is_category($term) || 'menu_order' !== $orderby) { return $args; }
    $all = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $term->term_id))));
    $args['post__in'] = array_values(array_unique(array_merge(array_intersect(vpn_bakery_product_ids(), $all), $all))) ?: array(0);
    $args['orderby'] = 'post__in';
    unset($args['meta_key']);
    return $args;
}, 15, 3);

add_filter('rank_math/json_ld', static function ($nodes) {
    if (!vpn_bakery_is_category()) { return $nodes; }
    $url = vpn_bakery_canonical('');
    $collection = null;
    foreach ($nodes as $key => &$node) {
        if (!is_array($node)) { continue; }
        if (array_intersect(array('CollectionPage', 'WebPage'), (array) ($node['@type'] ?? array()))) {
            $node['@type'] = 'CollectionPage';
            $node['@id'] = $url . '#webpage';
            $node['url'] = $url;
            $node['name'] = vpn_bakery_category_data()['name'];
            $node['description'] = vpn_bakery_category_data()['description'];
            $collection = $key;
            break;
        }
    }
    unset($node);
    if (null === $collection) {
        $collection = 'vpn_bakery_collection';
        $nodes[$collection] = array('@type' => 'CollectionPage', '@id' => $url . '#webpage', 'url' => $url,
            'name' => vpn_bakery_category_data()['name'], 'description' => vpn_bakery_category_data()['description']);
    }
    $page = max(1, (int) get_query_var('paged'));
    $orderby = isset($_GET['orderby']) && is_string($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'menu_order';
    $args = array('post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 12, 'paged' => $page, 'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => get_queried_object_id())));
    if (function_exists('WC') && WC()->query) { $args = array_merge($args, WC()->query->get_catalog_ordering_args($orderby)); }
    $args = apply_filters('custom_box_archive_product_query_args', $args, get_queried_object(), $orderby);
    $items = array();
    foreach ((new WP_Query($args))->posts as $index => $id) {
        $items[] = array('@type' => 'ListItem', 'position' => ($page - 1) * 12 + $index + 1, 'url' => get_permalink($id), 'name' => get_the_title($id));
    }
    $nodes['vpn_bakery_items'] = array('@type' => 'ItemList', '@id' => $url . '#products', 'itemListElement' => $items);
    $nodes[$collection]['mainEntity'] = array('@id' => $url . '#products');
    $breadcrumbs = array(
        array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url('/')),
        array('@type' => 'ListItem', 'position' => 2, 'name' => 'Products', 'item' => home_url('/products/')),
        array('@type' => 'ListItem', 'position' => 3, 'name' => 'Paper Bags with Logo', 'item' => home_url('/products/paper-bags-with-logo/')),
        array('@type' => 'ListItem', 'position' => 4, 'name' => vpn_bakery_category_data()['name'], 'item' => $url),
    );
    // Keep a single breadcrumb node, matching the visible archive breadcrumb.
    $breadcrumb_key = 'vpn_bakery_breadcrumb';
    foreach ($nodes as $key => $node) {
        if (is_array($node) && 'BreadcrumbList' === ($node['@type'] ?? '')) { $breadcrumb_key = $key; break; }
    }
    $nodes[$breadcrumb_key] = array('@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb', 'itemListElement' => $breadcrumbs);
    $nodes[$collection]['breadcrumb'] = array('@id' => $url . '#breadcrumb');
    return $nodes;
}, 70);

add_action('wp_enqueue_scripts', static function () {
    if (!vpn_bakery_is_category()) { return; }
    foreach (array('kraft-paper-bags-workshop', 'bakery-bread-paper-bags') as $asset) {
        $file = '/assets/css/' . $asset . '.css';
        wp_enqueue_style('vpn-bakery-' . $asset, get_template_directory_uri() . $file, array('product-archive-fix-style'), (string) filemtime(get_template_directory() . $file));
    }
}, 36);
