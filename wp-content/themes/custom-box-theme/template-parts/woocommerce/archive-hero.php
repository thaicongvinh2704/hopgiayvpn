<?php
/**
 * Product archive hero.
 */

defined('ABSPATH') || exit;

$current_term = isset($args['current_term']) ? $args['current_term'] : null;
$archive_title = isset($args['archive_title']) ? $args['archive_title'] : '';
$archive_description = isset($args['archive_description']) ? $args['archive_description'] : '';
$products_url = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');
$hero_image_url = $current_term && !is_wp_error($current_term) && function_exists('custom_box_get_product_category_asset_image_url')
    ? custom_box_get_product_category_asset_image_url($current_term)
    : '';

if (!$hero_image_url && $current_term && !is_wp_error($current_term) && 'product_cat' === $current_term->taxonomy) {
    $image_id = (int) get_term_meta($current_term->term_id, 'thumbnail_id', true);

    if (!$image_id) {
        $image_id = (int) get_term_meta($current_term->term_id, 'custom_box_category_image_id', true);
    }

    if ($image_id) {
        $term_image_url = wp_get_attachment_image_url($image_id, 'large');

        if ($term_image_url) {
            $hero_image_url = $term_image_url;
        }
    }

    if (!$hero_image_url && function_exists('wc_get_products')) {
        $category_products = wc_get_products(array(
            'status'   => 'publish',
            'limit'    => 1,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'category' => array($current_term->slug),
            'return'   => 'ids',
        ));

        if (!empty($category_products[0])) {
            $product_image_id = get_post_thumbnail_id((int) $category_products[0]);
            $product_image_url = $product_image_id ? wp_get_attachment_image_url($product_image_id, 'large') : '';

            if ($product_image_url) {
                $hero_image_url = $product_image_url;
            }
        }
    }
}

if (!$hero_image_url) {
    $hero_image_url = get_template_directory_uri() . '/assets/images/product-banner1.png';
}
?>

<section class="product-archive-hero product-category-landing-hero">
    <div class="container">
        <div class="product-archive-breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
            <a href="<?php echo esc_url($products_url); ?>"><?php esc_html_e('Products', 'custom-box-theme'); ?></a>
            <?php if ($current_term && !is_wp_error($current_term)) : ?>
                <span><?php echo esc_html($current_term->name); ?></span>
            <?php else : ?>
                <span><?php echo esc_html($archive_title); ?></span>
            <?php endif; ?>
        </div>

        <div class="product-category-hero-grid">
            <div class="product-archive-hero-content">
                <p class="product-eyebrow"><?php esc_html_e('Custom Packaging Catalog', 'custom-box-theme'); ?></p>
                <h1><?php echo esc_html($archive_title); ?></h1>
                <p><?php echo esc_html(wp_strip_all_tags($archive_description)); ?></p>
                <div class="product-category-hero-actions">
                    <a class="btn-primary" href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Get Your Box', 'custom-box-theme'); ?></a>
                    <a class="btn-outline" href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Request Free Sample', 'custom-box-theme'); ?></a>
                </div>
            </div>
            <div class="product-category-hero-image">
                <img src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($archive_title); ?>" decoding="async">
            </div>
        </div>
    </div>
</section>
