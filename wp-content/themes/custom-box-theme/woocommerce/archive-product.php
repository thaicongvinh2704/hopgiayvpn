<?php
/**
 * Custom WooCommerce shop and product category archive.
 */

defined('ABSPATH') || exit;

get_header();

$current_term = is_product_taxonomy() ? get_queried_object() : null;
$archive_title = $current_term && !is_wp_error($current_term) ? $current_term->name : __('Custom Packaging Products', 'custom-box-theme');
$archive_description = $current_term && !is_wp_error($current_term) && !empty($current_term->description)
    ? $current_term->description
    : __('Explore custom packaging products built for branded presentation, product protection, and flexible production requirements.', 'custom-box-theme');

$parent_term = function_exists('custom_box_get_packaging_parent_category') ? custom_box_get_packaging_parent_category() : false;
$sidebar_categories = array();
$child_categories = array();
$landing_categories = array();
$hub_groups = function_exists('custom_box_get_packaging_hub_groups') ? custom_box_get_packaging_hub_groups(false) : array();
$landing_root_link = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

if ($parent_term && !is_wp_error($parent_term)) {
    $parent_term_link = get_term_link($parent_term);
    if (!is_wp_error($parent_term_link)) {
        $landing_root_link = $parent_term_link;
    }
}

if (function_exists('custom_box_get_packaging_categories')) {
    $sidebar_categories = custom_box_get_packaging_categories(48);
}

if ($current_term && !is_wp_error($current_term)) {
    $is_packaging_parent = $parent_term && !is_wp_error($parent_term) && (int) $current_term->term_id === (int) $parent_term->term_id;

    if ($is_packaging_parent) {
        $landing_categories = $sidebar_categories;
    } else {
        $child_categories = get_terms(array(
            'taxonomy'   => 'product_cat',
            'parent'     => $current_term->term_id,
            'hide_empty' => false,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
        ));

        if (is_wp_error($child_categories)) {
            $child_categories = array();
        }

        $landing_categories = $child_categories;
    }
}

if (is_shop() && $parent_term && !is_wp_error($parent_term)) {
    $archive_title = __('Custom Packaging Product Categories', 'custom-box-theme');
    $archive_description = __('Explore custom packaging product categories including paper boxes, rigid boxes, drawer boxes, food paper boxes, cosmetic packaging boxes, gift boxes, paper bags, and specialty packaging solutions from VPN Paper Box Manufacturer.', 'custom-box-theme');
    $landing_categories = $sidebar_categories;
}

if (is_shop()) {
    $archive_title = __('Custom Packaging Product Categories', 'custom-box-theme');
    $archive_description = __('Explore custom packaging product categories including paper boxes, rigid boxes, drawer boxes, food paper boxes, cosmetic packaging boxes, gift boxes, paper bags, and specialty packaging solutions from VPN Paper Box Manufacturer.', 'custom-box-theme');
}

$show_category_landing = !is_shop() && !empty($landing_categories) && $current_term && !is_wp_error($current_term);
$archive_context = compact(
    'current_term',
    'archive_title',
    'archive_description',
    'sidebar_categories',
    'landing_categories',
    'hub_groups',
    'landing_root_link',
    'parent_term'
);
?>

<main class="product-archive-page">
    <?php get_template_part('template-parts/woocommerce/archive-hero', null, $archive_context); ?>

    <?php if (is_shop()) : ?>
        <?php get_template_part('template-parts/woocommerce/product-category-hub', null, $archive_context); ?>
        <?php
        get_template_part('template-parts/woocommerce/archive-copy', null, array_merge($archive_context, array(
            'copy_variant' => 'categories',
        )));
        ?>
    <?php elseif ($show_category_landing) : ?>
        <?php get_template_part('template-parts/woocommerce/category-grid', null, $archive_context); ?>
        <?php
        get_template_part('template-parts/woocommerce/archive-copy', null, array_merge($archive_context, array(
            'copy_variant' => 'categories',
        )));
        ?>
    <?php else : ?>
        <?php get_template_part('template-parts/woocommerce/product-list', null, $archive_context); ?>
        <?php if ($current_term && !is_wp_error($current_term)) : ?>
            <?php get_template_part('template-parts/woocommerce/related-categories', null, $archive_context); ?>
        <?php endif; ?>
        <?php
        get_template_part('template-parts/woocommerce/archive-copy', null, array_merge($archive_context, array(
            'copy_variant' => 'products',
        )));
        ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/home/faq'); ?>
    <?php get_template_part('template-parts/home/footer-cta'); ?>
</main>

<?php get_footer(); ?>
