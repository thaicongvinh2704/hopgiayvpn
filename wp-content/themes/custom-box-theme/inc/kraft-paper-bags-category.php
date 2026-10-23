<?php
/** Kraft bag collection: existing products, scoped SEO and repeatable category setup. */
defined('ABSPATH') || exit;

function vpn_kraft_category_data(): array {
    static $data;
    if (null === $data) {
        $data = json_decode((string) file_get_contents(__DIR__ . '/kraft-paper-bags-category.json'), true);
        $data = is_array($data) ? $data : array();
    }
    return $data;
}

function vpn_kraft_is_category($term = null): bool {
    if (null === $term) {
        if (!function_exists('is_product_category') || !is_product_category()) { return false; }
        $term = get_queried_object();
    }
    return $term && !is_wp_error($term) && 'product_cat' === ($term->taxonomy ?? '')
        && 'custom-kraft-paper-bags' === ($term->slug ?? '');
}

function vpn_kraft_category_url(): string {
    $term = get_term_by('slug', 'custom-kraft-paper-bags', 'product_cat');
    $url = $term ? get_term_link($term) : '';
    return $url && !is_wp_error($url) ? $url : home_url('/products/custom-kraft-paper-bags/');
}

/** The two supporting articles are published live but absent from some local database copies. */
function vpn_kraft_reference_url(string $slug): string {
    $post = get_page_by_path($slug, OBJECT, 'post');
    if ($post && 'publish' === $post->post_status) { return get_permalink($post); }
    if ('local' === wp_get_environment_type() && in_array($slug, array('paper-bag-sizes', 'kraft-vs-coated-paper-bags'), true)) {
        return 'https://hopgiayvpn.com/' . $slug . '/';
    }
    return home_url('/' . $slug . '/');
}

function vpn_kraft_product_ids(): array {
    static $ids;
    if (null !== $ids) { return $ids; }
    $ids = array();
    foreach (vpn_kraft_category_data()['products'] as $item) {
        $post = get_page_by_path($item['slug'], OBJECT, 'product');
        if ($post && 'publish' === $post->post_status && has_term('custom-kraft-paper-bags', 'product_cat', $post->ID)) {
            $ids[] = (int) $post->ID;
        }
    }
    return $ids;
}

/** Runs once per release; uses slugs so local and hosting IDs can differ. */
function vpn_kraft_sync_category(): array {
    if (!taxonomy_exists('product_cat')) { return array('error' => 'WooCommerce taxonomy unavailable'); }
    $data = vpn_kraft_category_data();
    $parent = get_term_by('slug', $data['parent_slug'], 'product_cat');
    if (!$parent) { return array('error' => 'Paper Bags with Logo category missing'); }
    $term = get_term_by('slug', $data['slug'], 'product_cat');
    $args = array('name' => $data['name'], 'description' => $data['intro'], 'parent' => (int) $parent->term_id);
    $result = $term
        ? wp_update_term($term->term_id, 'product_cat', $args)
        : wp_insert_term($data['name'], 'product_cat', array_merge($args, array('slug' => $data['slug'])));
    if (is_wp_error($result)) { return array('error' => $result->get_error_message()); }
    $id = (int) $result['term_id'];
    $missing = array();
    $assigned = array();
    foreach ($data['products'] as $item) {
        $post = get_page_by_path($item['slug'], OBJECT, 'product');
        if (!$post || 'publish' !== $post->post_status) { $missing[] = $item['slug']; continue; }
        $added = wp_set_object_terms($post->ID, array($id), 'product_cat', true);
        if (is_wp_error($added)) { return array('error' => $added->get_error_message()); }
        $assigned[] = (int) $post->ID;
    }
    $hero = get_page_by_path($data['hero_product'], OBJECT, 'product');
    $image_id = $hero ? (int) get_post_thumbnail_id($hero->ID) : 0;
    $meta = array(
        'rank_math_title' => $data['title'], 'rank_math_description' => $data['description'],
        'rank_math_focus_keyword' => $data['keyword'], 'rank_math_robots' => array('index', 'follow'),
        'rank_math_canonical_url' => vpn_kraft_category_url(),
        'rank_math_facebook_title' => $data['title'], 'rank_math_facebook_description' => $data['description'],
        'rank_math_twitter_title' => $data['title'], 'rank_math_twitter_description' => $data['description'],
    );
    if ($image_id) {
        $meta['thumbnail_id'] = $image_id;
        $meta['custom_box_category_image_id'] = $image_id;
        $meta['rank_math_facebook_image_id'] = $image_id;
        $meta['rank_math_facebook_image'] = wp_get_attachment_url($image_id);
    }
    foreach ($meta as $key => $value) { update_term_meta($id, $key, $value); }
    clean_term_cache($id, 'product_cat');
    $status = array('version' => $data['version'], 'term_id' => $id, 'assigned' => $assigned, 'missing' => $missing);
    // Missing products are reported for an explicit rerun, without scanning on every page view.
    update_option('vpn_kraft_category_release', $status, false);
    return $status;
}
add_action('init', static function () {
    if (defined('VPN_KRAFT_SKIP_SYNC') && VPN_KRAFT_SKIP_SYNC) { return; }
    $state = get_option('vpn_kraft_category_release', array());
    if (($state['version'] ?? '') !== vpn_kraft_category_data()['version']) { vpn_kraft_sync_category(); }
}, 45);

function vpn_kraft_meta_title($title) {
    if (!vpn_kraft_is_category()) { return $title; }
    $title = get_term_meta(get_queried_object_id(), 'rank_math_title', true) ?: vpn_kraft_category_data()['title'];
    $page = max(1, (int) get_query_var('paged'));
    return $page > 1 ? str_replace(' | Wholesale', ' - Page ' . $page . ' | Wholesale', $title) : $title;
}
foreach (array('pre_get_document_title', 'rank_math/frontend/title', 'rank_math/opengraph/facebook/title', 'rank_math/opengraph/twitter/title') as $hook) {
    add_filter($hook, 'vpn_kraft_meta_title', 65);
}
foreach (array('rank_math/frontend/description', 'rank_math/opengraph/facebook/description', 'rank_math/opengraph/twitter/description') as $hook) {
    add_filter($hook, static function ($value) {
        return vpn_kraft_is_category() ? (get_term_meta(get_queried_object_id(), 'rank_math_description', true) ?: vpn_kraft_category_data()['description']) : $value;
    }, 65);
}
function vpn_kraft_canonical($canonical) {
    if (!vpn_kraft_is_category()) { return $canonical; }
    $page = max(1, (int) get_query_var('paged'));
    return $page > 1 ? trailingslashit(vpn_kraft_category_url()) . 'page/' . $page . '/' : vpn_kraft_category_url();
}
add_filter('rank_math/frontend/canonical', 'vpn_kraft_canonical', 65);
add_filter('get_canonical_url', 'vpn_kraft_canonical', 65);
add_filter('rank_math/opengraph/facebook/og_url', 'vpn_kraft_canonical', 65);

add_filter('custom_box_archive_product_query_args', static function ($args, $term, $orderby) {
    if (!vpn_kraft_is_category($term) || 'menu_order' !== $orderby) { return $args; }
    // Keep all assigned products visible, including future additions; only prioritize the curated styles.
    $all = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $term->term_id))));
    $args['post__in'] = array_values(array_unique(array_merge(vpn_kraft_product_ids(), $all))) ?: array(0);
    $args['orderby'] = 'post__in';
    unset($args['meta_key']);
    return $args;
}, 10, 3);

add_filter('rank_math/json_ld', static function ($nodes) {
    if (!vpn_kraft_is_category()) { return $nodes; }
    $url = vpn_kraft_canonical('');
    $page_id = $url . '#webpage';
    $collection_key = null;
    foreach ($nodes as $key => &$node) {
        if (!is_array($node)) { continue; }
        if (array_intersect(array('CollectionPage', 'WebPage'), (array) ($node['@type'] ?? array()))) {
            $node['@type'] = 'CollectionPage';
            $node['name'] = vpn_kraft_category_data()['name'];
            $node['description'] = vpn_kraft_category_data()['description'];
            $node['url'] = $url;
            $node['@id'] = $page_id;
            $collection_key = $key;
            break;
        }
    }
    unset($node);
    if (null === $collection_key) {
        $collection_key = 'vpn_kraft_collection';
        $nodes[$collection_key] = array('@type' => 'CollectionPage', '@id' => $page_id, 'url' => $url,
            'name' => vpn_kraft_category_data()['name'], 'description' => vpn_kraft_category_data()['description']);
    }
    $items = array();
    $page = max(1, (int) get_query_var('paged'));
    // Match the same product order and 12-item pagination as the visible catalog.
    $args = apply_filters('custom_box_archive_product_query_args', array('post_type' => 'product', 'post_status' => 'publish',
        'posts_per_page' => 12, 'paged' => $page, 'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => get_queried_object_id()))),
        get_queried_object(), isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'menu_order');
    if (isset($_GET['orderby']) && function_exists('WC') && WC()->query) {
        $ordering = WC()->query->get_catalog_ordering_args(sanitize_key(wp_unslash($_GET['orderby'])));
        $args = array_merge($args, $ordering);
        // get_catalog_ordering_args('menu_order') must not undo the curated default.
        $args = apply_filters('custom_box_archive_product_query_args', $args, get_queried_object(), sanitize_key(wp_unslash($_GET['orderby'])));
    }
    foreach ((new WP_Query($args))->posts as $index => $product_id) {
        $items[] = array('@type' => 'ListItem', 'position' => ($page - 1) * 12 + $index + 1,
            'name' => get_the_title($product_id), 'url' => get_permalink($product_id));
    }
    $nodes['vpn_kraft_items'] = array('@type' => 'ItemList', '@id' => $url . '#products', 'itemListElement' => $items);
    $nodes[$collection_key]['mainEntity'] = array('@id' => $url . '#products');
    return $nodes;
}, 65);
