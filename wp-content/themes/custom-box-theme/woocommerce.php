<?php
/**
 * WooCommerce wrapper template.
 */

if (is_product()) {
    require get_template_directory() . '/woocommerce/single-product.php';
    return;
}

if (is_shop() || is_product_taxonomy()) {
    require get_template_directory() . '/woocommerce/archive-product.php';
    return;
}

get_header();
?>

<main class="woocommerce-page-wrapper">
    <?php woocommerce_content(); ?>
</main>

<?php get_footer(); ?>
