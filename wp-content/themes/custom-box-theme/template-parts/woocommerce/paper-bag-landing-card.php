<?php
/** Shared card for the main and seasonal paper bag collections. */
defined('ABSPATH') || exit;
$bag_product = isset($args['product']) ? $args['product'] : null;
if (!$bag_product instanceof WC_Product) { return; }
$product_title = get_the_title($bag_product->get_id());
$product_url = $bag_product->get_permalink();
?>
<article class="cpb-product-card custom-product-card" data-product-id="<?php echo esc_attr($bag_product->get_id()); ?>" data-product-slug="<?php echo esc_attr($bag_product->get_slug()); ?>">
    <a class="cpb-product-media custom-product-image" href="<?php echo esc_url($product_url); ?>" aria-label="<?php echo esc_attr($product_title); ?>"><?php echo custom_box_paper_bag_product_image($bag_product); ?></a>
    <div class="cpb-product-body custom-product-body">
        <h3><a class="custom-product-title" href="<?php echo esc_url($product_url); ?>"><?php echo esc_html(custom_box_paper_bag_display_title($bag_product)); ?></a></h3>
        <div class="cpb-product-actions custom-product-actions">
            <a href="<?php echo esc_url($product_url); ?>" aria-label="<?php echo esc_attr('View details of ' . $product_title); ?>">Details</a>
            <a href="#quote" data-bag-type="<?php echo esc_attr($product_title); ?>" aria-label="<?php echo esc_attr('Request a quote for ' . $product_title); ?>">Get quote</a>
        </div>
    </div>
</article>
