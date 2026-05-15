<?php
/**
 * Mid-article product recommendations.
 */

defined('ABSPATH') || exit;

$recommended_products = function_exists('custom_box_get_blog_product_recommendations') ? custom_box_get_blog_product_recommendations(3) : array();
?>

<?php if (!empty($recommended_products)) : ?>
    <section class="blog-product-recommendations" aria-labelledby="blog-products-heading">
        <div class="blog-section-kicker"><?php esc_html_e('Recommended Packaging', 'custom-box-theme'); ?></div>
        <h2 id="blog-products-heading"><?php esc_html_e('Packaging Products Related to This Guide', 'custom-box-theme'); ?></h2>
        <p><?php esc_html_e('Explore production-ready packaging options that can be customized by structure, material, printing, finishing, quantity, and artwork requirements.', 'custom-box-theme'); ?></p>

        <div class="blog-product-grid">
            <?php foreach ($recommended_products as $recommended_product) : ?>
                <?php
                $product_id = $recommended_product->get_id();
                $product_image = get_the_post_thumbnail_url($product_id, 'medium_large');
                if (!$product_image) {
                    $product_image = get_template_directory_uri() . '/assets/images/custom-cardboard-boxes.webp';
                }
                ?>
                <article class="blog-product-card">
                    <a class="blog-product-image" href="<?php echo esc_url(get_permalink($product_id)); ?>">
                        <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($recommended_product->get_name()); ?>" loading="lazy" decoding="async">
                    </a>
                    <div>
                        <h3><a href="<?php echo esc_url(get_permalink($product_id)); ?>"><?php echo esc_html($recommended_product->get_name()); ?></a></h3>
                        <p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($recommended_product->get_short_description()), 16, '...')); ?></p>
                        <a class="blog-product-quote" href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Request Quote', 'custom-box-theme'); ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
