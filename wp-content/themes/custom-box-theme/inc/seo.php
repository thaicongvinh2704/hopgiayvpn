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

function custom_box_get_quote_product_offer($product) {
    $product_url = $product instanceof WC_Product ? $product->get_permalink() : get_permalink();

    return array(
        '@type'              => 'Offer',
        'name'               => 'Request a Quote',
        'url'                => $product_url,
        'priceCurrency'      => 'USD',
        'price'              => '0',
        'availability'       => 'https://schema.org/InStock',
        'itemCondition'      => 'https://schema.org/NewCondition',
        'priceSpecification' => array(
            '@type'         => 'PriceSpecification',
            'price'         => '0',
            'priceCurrency' => 'USD',
            'description'   => 'Price available upon request based on size, material, printing, finishing, and order quantity.',
        ),
    );
}

function custom_box_add_quote_product_schema_fields($entity, $product) {
    $entity['brand'] = array(
        '@type' => 'Brand',
        'name'  => 'VPN Packaging',
    );

    $entity['offers'] = custom_box_get_quote_product_offer($product);

    if ($product instanceof WC_Product && $product->get_image_id() && empty($entity['image'])) {
        $image = wp_get_attachment_image_src($product->get_image_id(), 'full');

        if (!empty($image)) {
            $entity['image'] = array(
                array(
                    '@type'  => 'ImageObject',
                    'url'    => $image[0],
                    'width'  => (int) $image[1],
                    'height' => (int) $image[2],
                ),
            );
        }
    }

    return $entity;
}

function custom_box_rank_math_json_ld($data) {
    if (!is_array($data)) {
        return $data;
    }

    $remove_article_schema = custom_box_is_low_value_page();
    $is_product_page = function_exists('is_product') && is_product();
    $product = $is_product_page && function_exists('wc_get_product') ? wc_get_product(get_queried_object_id()) : null;
    $has_product_schema = false;

    foreach ($data as $key => $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $types = isset($entity['@type']) ? (array) $entity['@type'] : array();
        $is_product_schema = array_intersect($types, array('Product', 'WooCommerceProduct', 'ProductGroup'));

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

        if ($is_product_page && $product instanceof WC_Product && $is_product_schema) {
            $has_product_schema = true;
            $data[$key] = custom_box_add_quote_product_schema_fields($data[$key], $product);
        }
    }

    if ($is_product_page && $product instanceof WC_Product && !$has_product_schema) {
        $data['schema-customBoxProduct'] = custom_box_add_quote_product_schema_fields(
            array(
                '@type'       => 'Product',
                'name'        => $product->get_name(),
                'description' => wp_strip_all_tags($product->get_short_description() ? $product->get_short_description() : $product->get_description()),
                'sku'         => $product->get_sku() ? $product->get_sku() : '',
            ),
            $product
        );
    }

    return $data;
}
add_filter('rank_math/json_ld', 'custom_box_rank_math_json_ld', 20);
