<?php
/**
 * Related category links for product category archives.
 */

defined('ABSPATH') || exit;

$current_term = isset($args['current_term']) ? $args['current_term'] : null;

if (!$current_term || is_wp_error($current_term)) {
    return;
}

$group = function_exists('custom_box_get_packaging_group_for_term') ? custom_box_get_packaging_group_for_term($current_term) : null;
$products_url = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');
$sibling_terms = array();

if (!empty($group['slugs'])) {
    foreach ($group['slugs'] as $slug) {
        if ($slug === $current_term->slug || !function_exists('custom_box_get_product_category_by_slug')) {
            continue;
        }

        $term = custom_box_get_product_category_by_slug($slug);

        if ($term) {
            $sibling_terms[] = $term;
        }
    }
}
?>

<section class="related-packaging-categories-section">
    <div class="container">
        <div class="related-packaging-categories">
            <div class="related-packaging-categories-header">
                <h2><?php esc_html_e('Related Packaging Categories', 'custom-box-theme'); ?></h2>
                <div class="related-packaging-parent-links">
                    <a href="<?php echo esc_url($products_url); ?>"><?php esc_html_e('All Product Categories', 'custom-box-theme'); ?></a>
                    <?php if (!empty($group['title'])) : ?>
                        <a href="<?php echo esc_url(function_exists('custom_box_get_packaging_group_url') ? custom_box_get_packaging_group_url($group['title']) : $products_url); ?>"><?php echo esc_html($group['title']); ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($sibling_terms)) : ?>
                <div class="related-packaging-category-links">
                    <?php foreach ($sibling_terms as $term) : ?>
                        <?php
                        $term_link = get_term_link($term);
                        if (is_wp_error($term_link)) {
                            continue;
                        }
                        ?>
                        <a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html($term->name); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
