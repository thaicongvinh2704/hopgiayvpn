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

$parent_term = taxonomy_exists('product_cat') ? get_term_by('name', 'Custom Packaging Boxes', 'product_cat') : false;
$sidebar_categories = array();
$child_categories = array();
$landing_categories = array();
$landing_root_link = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

if ($parent_term && !is_wp_error($parent_term)) {
    $parent_term_link = get_term_link($parent_term);
    if (!is_wp_error($parent_term_link)) {
        $landing_root_link = $parent_term_link;
    }

    $sidebar_categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => $parent_term->term_id,
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
    ));

    if (is_wp_error($sidebar_categories)) {
        $sidebar_categories = array();
    }
}

if ($current_term && !is_wp_error($current_term)) {
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

if (is_shop() && $parent_term && !is_wp_error($parent_term)) {
    $archive_title = __('Custom Packaging Boxes Manufacturer', 'custom-box-theme');
    $archive_description = __('Explore all custom packaging products available for branded presentation, product protection, and flexible production requirements.', 'custom-box-theme');
    $child_categories = $sidebar_categories;
}

$show_category_landing = !is_shop() && !empty($landing_categories) && $current_term && !is_wp_error($current_term);
$archive_context = compact(
    'current_term',
    'archive_title',
    'archive_description',
    'sidebar_categories',
    'landing_categories',
    'landing_root_link',
    'parent_term'
);
?>

<main class="product-archive-page">
    <?php get_template_part('template-parts/woocommerce/archive-hero', null, $archive_context); ?>

    <?php if ($show_category_landing) : ?>
        <?php get_template_part('template-parts/woocommerce/category-grid', null, $archive_context); ?>
        <?php
        get_template_part('template-parts/woocommerce/archive-copy', null, array_merge($archive_context, array(
            'copy_variant' => 'categories',
        )));
        ?>
    <?php else : ?>
        <?php get_template_part('template-parts/woocommerce/product-list', null, $archive_context); ?>
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
