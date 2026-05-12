<?php
/**
 * Product archive listing view.
 */

defined('ABSPATH') || exit;

$current_term = isset($args['current_term']) ? $args['current_term'] : null;
$sidebar_categories = isset($args['sidebar_categories']) && is_array($args['sidebar_categories']) ? $args['sidebar_categories'] : array();
$landing_root_link = isset($args['landing_root_link']) ? $args['landing_root_link'] : home_url('/shop/');

$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$product_query_args = array(
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
);

if ($current_term && !is_wp_error($current_term)) {
    $product_query_args['tax_query'] = array(
        array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => (int) $current_term->term_id,
            'include_children' => true,
        ),
    );
}

$product_query = new WP_Query($product_query_args);
?>

<section class="product-category-landing-section product-listing-category-section">
    <div class="container product-category-landing-layout product-listing-layout">
        <aside class="product-category-tabs product-listing-tabs">
            <a href="<?php echo esc_url($landing_root_link); ?>">
                <i class="fas fa-border-all"></i>
                <span><?php esc_html_e('All Category', 'custom-box-theme'); ?></span>
            </a>
            <?php if (!empty($sidebar_categories)) : ?>
                <?php foreach ($sidebar_categories as $category) : ?>
                    <?php
                    $category_link = get_term_link($category);
                    if (is_wp_error($category_link)) {
                        continue;
                    }

                    $is_current = $current_term && !is_wp_error($current_term) && (int) $current_term->term_id === (int) $category->term_id;
                    ?>
                    <a class="<?php echo $is_current ? 'is-active' : ''; ?>" href="<?php echo esc_url($category_link); ?>">
                        <i class="fas fa-box-open"></i>
                        <span><?php echo esc_html($category->name); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>

        <div class="product-archive-main product-category-products">
            <div class="product-archive-toolbar">
                <div>
                    <?php if ($product_query->have_posts()) : ?>
                        <p class="woocommerce-result-count">
                            <?php
                            printf(
                                esc_html(_n('Showing %d product', 'Showing %d products', (int) $product_query->found_posts, 'custom-box-theme')),
                                (int) $product_query->found_posts
                            );
                            ?>
                        </p>
                    <?php else : ?>
                        <p><?php esc_html_e('No products found yet.', 'custom-box-theme'); ?></p>
                    <?php endif; ?>
                </div>
                <div class="product-archive-ordering">
                    <?php woocommerce_catalog_ordering(); ?>
                </div>
            </div>

            <?php if ($product_query->have_posts()) : ?>
                <div class="custom-product-grid">
                    <?php while ($product_query->have_posts()) : ?>
                        <?php
                        $product_query->the_post();
                        global $product;

                        if (!$product) {
                            continue;
                        }

                        $product_link = get_permalink();
                        $product_image = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                        if (!$product_image) {
                            $product_image = get_template_directory_uri() . '/assets/images/custom-cardboard-boxes.webp';
                        }
                        ?>

                        <article <?php wc_product_class('custom-product-card', $product); ?>>
                            <a class="custom-product-image" href="<?php echo esc_url($product_link); ?>">
                                <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
                            </a>
                            <div class="custom-product-body">
                                <a class="custom-product-title" href="<?php echo esc_url($product_link); ?>"><?php the_title(); ?></a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <?php
                $pagination = paginate_links(array(
                    'current' => $paged,
                    'total'   => (int) $product_query->max_num_pages,
                    'type'    => 'list',
                ));
                ?>
                <?php if ($pagination) : ?>
                    <div class="product-archive-pagination">
                        <?php echo wp_kses_post($pagination); ?>
                    </div>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="product-archive-empty">
                    <h2><?php esc_html_e('Products are coming soon', 'custom-box-theme'); ?></h2>
                    <p><?php esc_html_e('This category is ready. Add WooCommerce products and assign them here to show them in this grid.', 'custom-box-theme'); ?></p>
                    <a class="btn-primary" href="<?php echo esc_url(home_url('/#quote')); ?>"><?php esc_html_e('Request a Custom Quote', 'custom-box-theme'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
