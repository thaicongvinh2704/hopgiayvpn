<?php
/**
 * Product archive listing view.
 */

defined('ABSPATH') || exit;

$current_term = isset($args['current_term']) ? $args['current_term'] : null;
$sidebar_categories = isset($args['sidebar_categories']) && is_array($args['sidebar_categories']) ? $args['sidebar_categories'] : array();
$landing_root_link = isset($args['landing_root_link']) ? $args['landing_root_link'] : (function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/'));
$product_fallback_image_url = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';

$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$default_orderby = function_exists('wc_get_loop_prop') && wc_get_loop_prop('is_search')
    ? 'relevance'
    : apply_filters('woocommerce_default_catalog_orderby', get_option('woocommerce_default_catalog_orderby', 'menu_order'));
$orderby_value = isset($_GET['orderby'])
    ? wc_clean(wp_unslash($_GET['orderby']))
    : $default_orderby;
$catalog_orderby_options = apply_filters(
    'woocommerce_catalog_orderby',
    array(
        'menu_order' => __('Default sorting', 'woocommerce'),
        'popularity' => __('Sort by popularity', 'woocommerce'),
        'rating'     => __('Sort by average rating', 'woocommerce'),
        'date'       => __('Sort by latest', 'woocommerce'),
        'price'      => __('Sort by price: low to high', 'woocommerce'),
        'price-desc' => __('Sort by price: high to low', 'woocommerce'),
    )
);

if (function_exists('wc_review_ratings_enabled') && !wc_review_ratings_enabled()) {
    unset($catalog_orderby_options['rating']);
}

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

if (function_exists('WC') && WC()->query) {
    $ordering_args = WC()->query->get_catalog_ordering_args($orderby_value);

    if (!empty($ordering_args['orderby'])) {
        $product_query_args['orderby'] = $ordering_args['orderby'];
    }

    if (!empty($ordering_args['order'])) {
        $product_query_args['order'] = $ordering_args['order'];
    }

    if (!empty($ordering_args['meta_key'])) {
        $product_query_args['meta_key'] = $ordering_args['meta_key'];
    }
}

$product_query = new WP_Query($product_query_args);
?>

<section class="product-category-landing-section product-listing-category-section">
    <div class="container product-category-landing-layout product-listing-layout">
        <div class="product-archive-main product-category-products product-listing-products" data-product-list>
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
                    <form class="woocommerce-ordering" method="get">
                        <label class="screen-reader-text" for="product-archive-orderby">
                            <?php esc_html_e('Sort products', 'custom-box-theme'); ?>
                        </label>
                        <select
                            id="product-archive-orderby"
                            class="orderby"
                            name="orderby"
                            aria-label="<?php esc_attr_e('Sort products', 'custom-box-theme'); ?>"
                        >
                            <?php foreach ($catalog_orderby_options as $option_id => $option_name) : ?>
                                <option value="<?php echo esc_attr($option_id); ?>" <?php selected($orderby_value, $option_id); ?>>
                                    <?php echo esc_html($option_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="paged" value="1">
                        <?php wc_query_string_form_fields(null, array('orderby', 'submit', 'paged', 'product-page'), '', true); ?>
                    </form>
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
                        $product_image_id = (int) $product->get_image_id();
                        $product_image_path = $product_image_id ? get_attached_file($product_image_id) : '';
                        $product_image_url = $product_image_id ? wp_get_attachment_url($product_image_id) : '';
                        $product_image_size = $product_image_path && is_file($product_image_path) ? wp_getimagesize($product_image_path) : false;

                        if ($product_image_id && (!$product_image_path || !is_file($product_image_path) || !$product_image_url || !$product_image_size)) {
                            $product_image_id = 0;
                        }

                        $product_title_id = 'product-card-title-' . get_the_ID();
                        ?>

                        <article <?php wc_product_class('custom-product-card', $product); ?> data-product-card>
                            <a
                                class="custom-product-card-link"
                                href="<?php echo esc_url($product_link); ?>"
                                aria-labelledby="<?php echo esc_attr($product_title_id); ?>"
                            >
                                <span class="custom-product-image">
                                    <?php
                                    if ($product_image_id) {
                                        ?>
                                        <img
                                            src="<?php echo esc_url($product_image_url); ?>"
                                            srcset="<?php echo esc_url($product_image_url); ?> <?php echo esc_attr((int) $product_image_size[0]); ?>w"
                                            alt=""
                                            width="<?php echo esc_attr((int) $product_image_size[0]); ?>"
                                            height="<?php echo esc_attr((int) $product_image_size[1]); ?>"
                                            loading="lazy"
                                            decoding="async"
                                            sizes="(max-width: 379px) calc(100vw - 36px), (max-width: 767px) calc(50vw - 28px), (max-width: 1200px) 33vw, 360px"
                                        >
                                        <?php
                                    } else {
                                        ?>
                                        <img
                                            src="<?php echo esc_url($product_fallback_image_url); ?>"
                                            alt=""
                                            width="506"
                                            height="277"
                                            loading="lazy"
                                            decoding="async"
                                            sizes="(max-width: 379px) calc(100vw - 36px), (max-width: 767px) calc(50vw - 28px), (max-width: 1200px) 33vw, 360px"
                                        >
                                        <?php
                                    }
                                    ?>
                                </span>
                                <span class="custom-product-body">
                                    <h2 id="<?php echo esc_attr($product_title_id); ?>" class="custom-product-title"><?php the_title(); ?></h2>
                                </span>
                            </a>
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
                    <nav class="product-archive-pagination" aria-label="<?php esc_attr_e('Product results pages', 'custom-box-theme'); ?>">
                        <?php echo wp_kses_post($pagination); ?>
                    </nav>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="product-archive-empty">
                    <h2><?php esc_html_e('Products are coming soon', 'custom-box-theme'); ?></h2>
                    <p><?php esc_html_e('This category is ready. Add WooCommerce products and assign them here to show them in this grid.', 'custom-box-theme'); ?></p>
                    <a class="btn-primary" href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Request a Custom Quote', 'custom-box-theme'); ?></a>
                </div>
            <?php endif; ?>
        </div>

        <details
            class="product-category-tabs-disclosure product-listing-taxonomy"
            data-archive-categories-disclosure
            data-responsive-disclosure
        >
            <summary class="product-category-tabs-summary"><?php esc_html_e('Browse Categories', 'custom-box-theme'); ?></summary>
            <nav class="product-category-tabs product-listing-tabs" aria-label="<?php esc_attr_e('Product categories', 'custom-box-theme'); ?>">
                <a href="<?php echo esc_url($landing_root_link); ?>">
                    <i class="fas fa-border-all" aria-hidden="true"></i>
                    <span><?php esc_html_e('All Categories', 'custom-box-theme'); ?></span>
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
                        <a
                            class="<?php echo $is_current ? 'is-active' : ''; ?>"
                            href="<?php echo esc_url($category_link); ?>"
                            <?php echo $is_current ? 'aria-current="page"' : ''; ?>
                        >
                            <i class="fas fa-box-open" aria-hidden="true"></i>
                            <span><?php echo esc_html($category->name); ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>
        </details>
    </div>
</section>
