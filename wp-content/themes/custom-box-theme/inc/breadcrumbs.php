<?php
/**
 * Breadcrumb structured data.
 */

defined('ABSPATH') || exit;

function custom_box_get_products_url() {
    return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/products/');
}

function custom_box_get_primary_product_category($product_id) {
    $product_categories = get_the_terms($product_id, 'product_cat');
    if (empty($product_categories) || is_wp_error($product_categories)) {
        return null;
    }

    usort($product_categories, function ($a, $b) {
        return (int) $b->parent <=> (int) $a->parent;
    });

    return reset($product_categories);
}

function custom_box_get_breadcrumb_schema_items() {
    $items = array(
        array(
            'name' => __('Home', 'custom-box-theme'),
            'url'  => home_url('/'),
        ),
    );

    if (is_home() && !is_front_page()) {
        $blog_page_id = (int) get_option('page_for_posts');
        $items[] = array(
            'name' => __('Blog', 'custom-box-theme'),
            'url'  => $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/'),
        );
    } elseif (is_singular('post')) {
        $blog_page_id = (int) get_option('page_for_posts');
        $items[] = array(
            'name' => __('Blog', 'custom-box-theme'),
            'url'  => $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/'),
        );
        $items[] = array(
            'name' => get_the_title(),
            'url'  => get_permalink(),
        );
    } elseif (function_exists('is_shop') && is_shop()) {
        $items[] = array(
            'name' => __('Products', 'custom-box-theme'),
            'url'  => custom_box_get_products_url(),
        );
    } elseif (is_tax('product_cat')) {
        $shop_url = custom_box_get_products_url();
        $term = get_queried_object();

        $items[] = array(
            'name' => __('Products', 'custom-box-theme'),
            'url'  => $shop_url,
        );

        if ($term && !is_wp_error($term)) {
            $items[] = array(
                'name' => $term->name,
                'url'  => get_term_link($term),
            );
        }
    } elseif (is_singular('product')) {
        $shop_url = custom_box_get_products_url();

        $items[] = array(
            'name' => __('Products', 'custom-box-theme'),
            'url'  => $shop_url,
        );

        $product_category = custom_box_get_primary_product_category(get_the_ID());
        if ($product_category) {
            $category_link = get_term_link($product_category);

            if (!is_wp_error($category_link)) {
                $items[] = array(
                    'name' => $product_category->name,
                    'url'  => $category_link,
                );
            }
        }

        $items[] = array(
            'name' => get_the_title(),
            'url'  => get_permalink(),
        );
    } elseif (is_page_template('page-landing-packaging.php') || is_page('packaging-landing')) {
        $items[] = array(
            'name' => get_the_title() ?: __('Custom Packaging', 'custom-box-theme'),
            'url'  => get_permalink(),
        );
    } elseif (is_page() && !is_front_page()) {
        $items[] = array(
            'name' => get_the_title(),
            'url'  => get_permalink(),
        );
    }

    return array_values(array_filter($items, function ($item) {
        return !empty($item['name']) && !empty($item['url']) && !is_wp_error($item['url']);
    }));
}

function custom_box_output_breadcrumb_schema() {
    if (is_admin()) {
        return;
    }

    $items = custom_box_get_breadcrumb_schema_items();
    if (count($items) < 2) {
        return;
    }

    $schema_items = array();
    foreach ($items as $position => $item) {
        $schema_items[] = array(
            '@type'    => 'ListItem',
            'position' => $position + 1,
            'name'     => wp_strip_all_tags($item['name']),
            'item'     => esc_url_raw($item['url']),
        );
    }

    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $schema_items,
    );

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_output_breadcrumb_schema', 20);
