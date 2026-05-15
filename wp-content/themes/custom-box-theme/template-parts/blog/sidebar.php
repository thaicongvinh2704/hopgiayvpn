<?php
/**
 * Sticky blog conversion sidebar.
 */

defined('ABSPATH') || exit;

$recent_posts = new WP_Query(array(
    'post_type'           => 'post',
    'posts_per_page'      => 4,
    'post__not_in'        => array(get_the_ID()),
    'ignore_sticky_posts' => true,
));

$popular_products = function_exists('custom_box_get_blog_product_recommendations') ? custom_box_get_blog_product_recommendations(4) : array();
$packaging_categories = function_exists('custom_box_get_packaging_categories') ? custom_box_get_packaging_categories(8) : array();
?>

<aside class="blog-conversion-sidebar" aria-label="<?php esc_attr_e('Blog sidebar', 'custom-box-theme'); ?>">
    <section class="blog-sidebar-quote">
        <span><?php esc_html_e('Need a packaging quote?', 'custom-box-theme'); ?></span>
        <h2><?php esc_html_e('Talk to VPN Packaging', 'custom-box-theme'); ?></h2>
        <p><?php esc_html_e('Share size, quantity, artwork, and destination. We will suggest the best production option.', 'custom-box-theme'); ?></p>
        <a class="btn-primary" href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Request Quote', 'custom-box-theme'); ?></a>
    </section>

    <?php if (!empty($packaging_categories)) : ?>
        <section class="blog-sidebar-panel">
            <h2><?php esc_html_e('Packaging Categories', 'custom-box-theme'); ?></h2>
            <ul>
                <?php foreach ($packaging_categories as $category) : ?>
                    <?php $category_link = get_term_link($category); ?>
                    <?php if (!is_wp_error($category_link)) : ?>
                        <li><a href="<?php echo esc_url($category_link); ?>"><?php echo esc_html($category->name); ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if (!empty($popular_products)) : ?>
        <section class="blog-sidebar-panel">
            <h2><?php esc_html_e('Popular Products', 'custom-box-theme'); ?></h2>
            <ul>
                <?php foreach ($popular_products as $product) : ?>
                    <li><a href="<?php echo esc_url(get_permalink($product->get_id())); ?>"><?php echo esc_html($product->get_name()); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <section class="blog-sidebar-download">
        <i class="fas fa-file-arrow-down"></i>
        <h2><?php esc_html_e('Download Catalog', 'custom-box-theme'); ?></h2>
        <p><?php esc_html_e('Review packaging styles, materials, and finishing options for your next order.', 'custom-box-theme'); ?></p>
        <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Request Catalog', 'custom-box-theme'); ?></a>
    </section>

    <?php if ($recent_posts->have_posts()) : ?>
        <section class="blog-sidebar-panel">
            <h2><?php esc_html_e('Recent Posts', 'custom-box-theme'); ?></h2>
            <ul>
                <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
                    <li>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <span><?php echo esc_html(get_the_date('M j, Y')); ?></span>
                    </li>
                <?php endwhile; ?>
            </ul>
        </section>
        <?php wp_reset_postdata(); ?>
    <?php endif; ?>
</aside>
