<?php
/**
 * Custom WooCommerce shop and product category archive.
 */

defined('ABSPATH') || exit;

if (is_search()) {
    locate_template('search.php', true, false);
    return;
}

if (!function_exists('custom_box_get_archive_sidebar_categories')) {
    function custom_box_get_archive_sidebar_categories($current_term = null, $limit = 48) {
        if (!taxonomy_exists('product_cat')) {
            return array();
        }

        if (!$current_term || is_wp_error($current_term)) {
            return function_exists('custom_box_get_packaging_categories')
                ? custom_box_get_packaging_categories($limit)
                : array();
        }

        $terms = array();
        $children = get_terms(array(
            'taxonomy'   => 'product_cat',
            'parent'     => (int) $current_term->term_id,
            'hide_empty' => false,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
        ));

        if (!is_wp_error($children) && !empty($children)) {
            $terms = $children;
        } elseif (!empty($current_term->parent)) {
            $siblings = get_terms(array(
                'taxonomy'   => 'product_cat',
                'parent'     => (int) $current_term->parent,
                'hide_empty' => false,
                'orderby'    => 'term_id',
                'order'      => 'ASC',
            ));

            if (!is_wp_error($siblings) && !empty($siblings)) {
                $terms = $siblings;
            }
        }

        if (empty($terms) && function_exists('custom_box_get_packaging_group_for_term') && function_exists('custom_box_get_product_category_by_slug')) {
            $group = custom_box_get_packaging_group_for_term($current_term);

            if (!empty($group['slugs']) && is_array($group['slugs'])) {
                foreach ($group['slugs'] as $slug) {
                    $term = custom_box_get_product_category_by_slug($slug);

                    if ($term) {
                        $terms[] = $term;
                    }
                }
            }
        }

        if (empty($terms)) {
            $terms = array($current_term);
        }

        $filtered = array();
        $seen = array();

        foreach ($terms as $term) {
            if (!$term || is_wp_error($term) || isset($seen[$term->term_id])) {
                continue;
            }

            $seen[$term->term_id] = true;

            if (
                (int) $term->term_id === (int) $current_term->term_id
                || !function_exists('custom_box_product_category_has_products')
                || custom_box_product_category_has_products($term)
            ) {
                $filtered[] = $term;
            }

            if ($limit > 0 && count($filtered) >= $limit) {
                break;
            }
        }

        return $filtered;
    }
}

get_header();

$current_term = is_product_taxonomy() ? get_queried_object() : null;
$archive_title = $current_term && !is_wp_error($current_term) ? $current_term->name : __('Custom Packaging Products', 'custom-box-theme');
$archive_description = $current_term && !is_wp_error($current_term) && !empty($current_term->description)
    ? $current_term->description
    : __('Explore custom packaging products built for branded presentation, product protection, and flexible production requirements.', 'custom-box-theme');
$archive_eyebrow = '';
$archive_primary_cta = array();
$archive_secondary_cta = array();
$archive_hero_alt = '';
$custom_category_guide = '';

if (
    $current_term
    && !is_wp_error($current_term)
    && function_exists('custom_box_is_corrugated_mailer_boxes_category')
    && custom_box_is_corrugated_mailer_boxes_category($current_term)
) {
    $corrugated_mailer_data = custom_box_corrugated_mailer_boxes_category_data();
    $archive_title = $corrugated_mailer_data['archive_title'];
    $archive_description = $corrugated_mailer_data['hero_description'];
    $archive_eyebrow = $corrugated_mailer_data['hero_eyebrow'];
    $archive_primary_cta = $corrugated_mailer_data['primary_cta'];
    $archive_secondary_cta = $corrugated_mailer_data['secondary_cta'];
    $archive_hero_alt = $corrugated_mailer_data['hero_alt'];
    $custom_category_guide = 'corrugated-mailer-guide';
} elseif (
    $current_term
    && !is_wp_error($current_term)
    && function_exists('custom_box_is_folding_cartons_vietnam_category')
    && custom_box_is_folding_cartons_vietnam_category($current_term)
) {
    $folding_carton_data = custom_box_folding_cartons_vietnam_category_data();
    $archive_title = $folding_carton_data['archive_title'];
    $archive_description = $folding_carton_data['hero_description'];
    $archive_eyebrow = $folding_carton_data['hero_eyebrow'];
    $archive_primary_cta = $folding_carton_data['primary_cta'];
    $archive_secondary_cta = $folding_carton_data['secondary_cta'];
    $archive_hero_alt = $folding_carton_data['hero_alt'];
    $custom_category_guide = 'folding-cartons-vietnam-guide';
} elseif (
    $current_term
    && !is_wp_error($current_term)
    && function_exists('custom_box_is_rigid_box_manufacturer_vietnam_category')
    && custom_box_is_rigid_box_manufacturer_vietnam_category($current_term)
) {
    $rigid_box_data = custom_box_rigid_box_manufacturer_vietnam_category_data();
    $archive_title = $rigid_box_data['archive_title'];
    $archive_description = $rigid_box_data['hero_description'];
    $archive_eyebrow = $rigid_box_data['hero_eyebrow'];
    $archive_primary_cta = $rigid_box_data['primary_cta'];
    $archive_secondary_cta = $rigid_box_data['secondary_cta'];
    $archive_hero_alt = $rigid_box_data['hero_alt'];
    $custom_category_guide = 'rigid-box-manufacturer-vietnam-guide';
}

$parent_term = function_exists('custom_box_get_packaging_parent_category') ? custom_box_get_packaging_parent_category() : false;
$sidebar_categories = array();
$child_categories = array();
$landing_categories = array();
$hub_groups = function_exists('custom_box_get_packaging_hub_groups') ? custom_box_get_packaging_hub_groups(false) : array();
$landing_root_link = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');

if ($parent_term && !is_wp_error($parent_term)) {
    $parent_term_link = get_term_link($parent_term);
    if (!is_wp_error($parent_term_link)) {
        $landing_root_link = $parent_term_link;
    }
}

$sidebar_categories = custom_box_get_archive_sidebar_categories($current_term, 48);

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

$archive_context = compact(
    'current_term',
    'archive_title',
    'archive_description',
    'archive_eyebrow',
    'archive_primary_cta',
    'archive_secondary_cta',
    'archive_hero_alt',
    'custom_category_guide',
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
    <?php else : ?>
        <?php get_template_part('template-parts/woocommerce/product-list', null, $archive_context); ?>
        <?php if ($current_term && !is_wp_error($current_term)) : ?>
            <?php get_template_part('template-parts/woocommerce/related-categories', null, $archive_context); ?>
        <?php endif; ?>
        <?php if ($custom_category_guide && 1 === max(1, (int) get_query_var('paged'), (int) get_query_var('page'))) : ?>
            <?php get_template_part('template-parts/woocommerce/' . $custom_category_guide, null, $archive_context); ?>
        <?php elseif (!$custom_category_guide) : ?>
            <?php
            get_template_part('template-parts/woocommerce/archive-copy', null, array_merge($archive_context, array(
                'copy_variant' => 'products',
            )));
            ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$custom_category_guide) : ?>
        <?php get_template_part('template-parts/home/faq'); ?>
    <?php endif; ?>
    <?php get_template_part('template-parts/home/footer-cta'); ?>
</main>

<?php get_footer(); ?>
