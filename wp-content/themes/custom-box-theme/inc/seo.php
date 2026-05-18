<?php
/**
 * SEO integration fixes.
 */

defined('ABSPATH') || exit;

function custom_box_low_value_page_slugs() {
    return array('cart', 'checkout', 'my-account', 'trang-mau');
}

function custom_box_is_low_value_page() {
    if (!is_page()) {
        return false;
    }

    return is_page(custom_box_low_value_page_slugs());
}

function custom_box_rank_math_robots($robots) {
    if (!custom_box_is_low_value_page()) {
        return $robots;
    }

    $robots['index'] = 'noindex';
    $robots['follow'] = 'follow';

    return $robots;
}
add_filter('rank_math/frontend/robots', 'custom_box_rank_math_robots');

function custom_box_rank_math_sitemap_excluded_posts($post_ids) {
    $post_ids = wp_parse_id_list($post_ids);

    foreach (custom_box_low_value_page_slugs() as $slug) {
        $page = get_page_by_path($slug);
        if ($page && 'page' === $page->post_type) {
            $post_ids[] = (int) $page->ID;
        }
    }

    foreach (array('woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id') as $option_name) {
        $page_id = (int) get_option($option_name);
        if ($page_id > 0) {
            $post_ids[] = $page_id;
        }
    }

    return array_values(array_unique(array_filter($post_ids)));
}
add_filter('rank_math/sitemap/posts_to_exclude', 'custom_box_rank_math_sitemap_excluded_posts');

function custom_box_rank_math_json_ld($data) {
    if (!is_array($data)) {
        return $data;
    }

    $remove_article_schema = custom_box_is_low_value_page();

    foreach ($data as $key => $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $types = isset($entity['@type']) ? (array) $entity['@type'] : array();

        if (is_front_page() && in_array('CollectionPage', $types, true)) {
            $data[$key]['@type'] = count($types) > 1
                ? array_values(array_unique(array_merge(array_diff($types, array('CollectionPage')), array('WebPage'))))
                : 'WebPage';
        }

        if ($remove_article_schema && array_intersect($types, array('Article', 'BlogPosting'))) {
            unset($data[$key]);
            continue;
        }

        if (in_array('Person', $types, true) && !empty($entity['sameAs']) && is_array($entity['sameAs'])) {
            $data[$key]['sameAs'] = array_values(array_filter($entity['sameAs'], function ($url) {
                return false === strpos((string) $url, 'localhost');
            }));

            if (empty($data[$key]['sameAs'])) {
                unset($data[$key]['sameAs']);
            }
        }
    }

    return $data;
}
add_filter('rank_math/json_ld', 'custom_box_rank_math_json_ld', 20);
