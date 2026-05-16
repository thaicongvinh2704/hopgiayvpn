<?php
/**
 * Custom single product template.
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
    the_post();

    global $product;

    $product_id = get_the_ID();
    $image_id = $product ? $product->get_image_id() : 0;
    $gallery_ids = $product ? $product->get_gallery_image_ids() : array();
    $product_gallery_ids = array_values(array_filter(array_merge($image_id ? array($image_id) : array(), $gallery_ids)));
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : get_template_directory_uri() . '/assets/images/custom-cardboard-boxes.webp';
    $short_description = $product ? apply_filters('woocommerce_short_description', $product->get_short_description()) : '';
    $primary_product_category = function_exists('custom_box_get_primary_product_category') ? custom_box_get_primary_product_category($product_id) : null;
    $products_url = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');
    $product_specs = function_exists('custom_box_get_product_specifications') ? custom_box_get_product_specifications($product_id) : array();
    $related_ids = $product ? wc_get_related_products($product_id, 8) : array();

    if (count($related_ids) < 5) {
        $fallback_related_ids = wc_get_products(array(
            'status' => 'publish',
            'limit' => 8,
            'exclude' => array_merge(array($product_id), $related_ids),
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'ids',
        ));

        $related_ids = array_slice(array_values(array_unique(array_merge($related_ids, $fallback_related_ids))), 0, 8);
    }
?>

<main class="product-detail-page wc-product-detail-page">

    <section class="product-detail-hero">
        <div class="container product-detail-hero-grid">
            <div class="product-detail-media product-gallery-slider" data-product-gallery>
                <div class="product-gallery-main">
                    <?php if (!empty($product_gallery_ids)) : ?>
                        <?php foreach ($product_gallery_ids as $gallery_index => $gallery_id) : ?>
                            <?php $gallery_url = wp_get_attachment_image_url($gallery_id, 'large'); ?>
                            <?php if ($gallery_url) : ?>
                                <img class="product-gallery-slide <?php echo 0 === $gallery_index ? 'is-active' : ''; ?>" src="<?php echo esc_url($gallery_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?> image <?php echo esc_attr($gallery_index + 1); ?>" decoding="async">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <img class="product-gallery-slide is-active" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" decoding="async">
                    <?php endif; ?>

                    <?php if (count($product_gallery_ids) > 1) : ?>
                        <button class="product-gallery-arrow product-gallery-prev" type="button" aria-label="Previous product image">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="product-gallery-arrow product-gallery-next" type="button" aria-label="Next product image">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (count($product_gallery_ids) > 1) : ?>
                    <div class="product-gallery-strip">
                        <?php foreach ($product_gallery_ids as $gallery_index => $gallery_id) : ?>
                            <?php $gallery_thumb = wp_get_attachment_image_url($gallery_id, 'thumbnail'); ?>
                            <?php if ($gallery_thumb) : ?>
                                <button class="product-gallery-thumb <?php echo 0 === $gallery_index ? 'is-active' : ''; ?>" type="button" data-gallery-index="<?php echo esc_attr($gallery_index); ?>" aria-label="View product image <?php echo esc_attr($gallery_index + 1); ?>">
                                    <img src="<?php echo esc_url($gallery_thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?> thumbnail <?php echo esc_attr($gallery_index + 1); ?>" loading="lazy" decoding="async">
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-detail-content">
                <div class="product-breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
                    <a href="<?php echo esc_url($products_url); ?>"><?php esc_html_e('Products', 'custom-box-theme'); ?></a>
                    <?php if ($primary_product_category) : ?>
                        <?php $primary_product_category_link = get_term_link($primary_product_category); ?>
                        <?php if (!is_wp_error($primary_product_category_link)) : ?>
                            <a href="<?php echo esc_url($primary_product_category_link); ?>"><?php echo esc_html($primary_product_category->name); ?></a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <span><?php the_title(); ?></span>
                </div>

                <h1><?php the_title(); ?></h1>

                <div class="product-intro">
                    <?php
                    if ($short_description) {
                        echo wp_kses_post($short_description);
                    } else {
                        echo '<p>Custom packaging built for product protection, premium presentation, and brand-ready unboxing experiences. Choose your size, material, printing method, and finishing options with direct factory support.</p>';
                    }
                    ?>
                </div>

                <ul class="product-benefit-list">
                    <li><i class="fas fa-check-circle"></i> Luxury-grade durability</li>
                    <li><i class="fas fa-check-circle"></i> Custom size and structure</li>
                    <li><i class="fas fa-check-circle"></i> Premium printing finishes</li>
                    <li><i class="fas fa-check-circle"></i> Eco-friendly material options</li>
                </ul>

                <div class="product-detail-actions">
                    <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-primary">Request Custom Packaging Quote</a>
                    <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-outline">Request Free Sample</a>
                </div>
            </div>
        </div>
    </section>

    <section class="product-specifications-section">
        <div class="container">
            <div class="product-spec-card product-spec-card-wide">
                <h3>Specifications</h3>
                <ul>
                    <?php foreach ($product_specs as $product_spec) : ?>
                        <li>
                            <span><?php echo esc_html($product_spec['label']); ?></span>
                            <strong><?php echo esc_html($product_spec['value']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <section class="product-detail-overview product-story-section">
        <div class="container">
            <div class="product-detail-description product-content-body">
                <h2><?php echo esc_html(get_the_title()); ?> That Balance Presentation and Function</h2>
                <?php if (get_the_content()) : ?>
                    <?php the_content(); ?>
                <?php else : ?>
                    <p>
                        This product can be customized by size, material, printing method, finishing option, and order quantity.
                        Share your artwork or packaging idea and our team will prepare a clear quote for production.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="product-quote-intro">
        <div class="container">
            <h2>Request a Quote for Your Custom<br><?php echo esc_html(get_the_title()); ?> Today</h2>
        </div>
    </section>

    <?php get_template_part('template-parts/home/quote-form'); ?>

    <section class="product-workflow-band">
        <div class="container product-workflow-inner">
            <div class="product-workflow-heading">
                <span>How We Work</span>
                <strong>Your Packaging Order Happens Here</strong>
            </div>
            <div class="product-workflow-steps">
                <div><i class="fas fa-ruler-combined"></i><span>Pick Your Size</span></div>
                <div><i class="fas fa-palette"></i><span>Finalize Design</span></div>
                <div><i class="fas fa-print"></i><span>Start Printing</span></div>
                <div><i class="fas fa-box"></i><span>QC, Packed & Shipped</span></div>
                <div><i class="fas fa-truck"></i><span>Track & Receive</span></div>
            </div>
        </div>
    </section>

    <section class="product-options-section product-customization-section">
        <div class="container">
            <div class="product-section-header">
                <h2>We Know How to Package <?php echo esc_html(get_the_title()); ?> Perfectly</h2>
                <p>Build packaging around your product, brand identity, and fulfillment needs.</p>
            </div>

            <div class="product-options-grid">
                <div class="product-option-card">
                    <i class="fas fa-layer-group"></i>
                    <h3>Material Choices</h3>
                    <p>Select from cardstock, kraft, corrugated, recycled paperboard, and rigid stock.</p>
                </div>
                <div class="product-option-card">
                    <i class="fas fa-print"></i>
                    <h3>Printing Methods</h3>
                    <p>Use digital, offset, CMYK, Pantone, or inside-outside printing for a branded finish.</p>
                </div>
                <div class="product-option-card">
                    <i class="fas fa-star"></i>
                    <h3>Premium Finishing</h3>
                    <p>Add foil stamping, embossing, debossing, spot UV, matte, gloss, or soft touch lamination.</p>
                </div>
                <div class="product-option-card">
                    <i class="fas fa-ruler-combined"></i>
                    <h3>Custom Size</h3>
                    <p>Create boxes around exact product dimensions to improve fit, protection, and presentation.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="product-feedback-section">
        <div class="container">
            <h2>Client Feedback That Matters</h2>
            <div class="product-feedback-card">
                <div class="product-feedback-image">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/feedback1.jpeg'); ?>" alt="Custom packaging client feedback" loading="lazy" decoding="async">
                </div>
                <div class="product-feedback-content">
                    <div class="stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <h3>Fast, Supportive, Great Print Quality</h3>
                    <p>Our packaging arrived with clean finishing, accurate colors, and a premium presentation that matched our launch goals. The quote process was clear from start to finish.</p>
                    <strong>Verified Packaging Client</strong>
                    <span>Custom Boxes Project</span>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/home/faq'); ?>

    <?php if (!empty($related_ids)) : ?>
        <section class="product-related-section">
            <div class="container">
                <div class="product-related-header">
                    <span>Packaging Catalog</span>
                    <h2>Related Packaging Solutions You May Need</h2>
                    <p>Explore similar custom packaging options that can support your product line, campaign, or next bulk order.</p>
                </div>
                <div class="product-related-grid">
                    <?php foreach ($related_ids as $related_id) : ?>
                        <?php
                        $related_product = wc_get_product($related_id);
                        if (!$related_product) {
                            continue;
                        }

                        $related_image = get_the_post_thumbnail_url($related_id, 'medium');
                        if (!$related_image) {
                            $related_image = get_template_directory_uri() . '/assets/images/custom-cardboard-boxes.webp';
                        }
                        ?>
                        <a class="product-category-card product-related-card" href="<?php echo esc_url(get_permalink($related_id)); ?>">
                            <span class="product-category-card-image product-related-image">
                                <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr($related_product->get_name()); ?>" loading="lazy" decoding="async">
                            </span>
                            <span class="product-category-card-title product-related-title"><?php echo esc_html($related_product->get_name()); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/home/footer-cta'); ?>

</main>

<?php
endwhile;

get_footer();
