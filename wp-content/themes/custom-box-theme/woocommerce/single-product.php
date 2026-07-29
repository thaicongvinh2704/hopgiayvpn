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
    $product_gallery_ids = array_values(
        array_filter(
            array_unique(array_merge($image_id ? array($image_id) : array(), $gallery_ids)),
            'wp_attachment_is_image'
        )
    );
    $short_description = $product ? apply_filters('woocommerce_short_description', $product->get_short_description()) : '';
    $primary_product_category = function_exists('custom_box_get_primary_product_category') ? custom_box_get_primary_product_category($product_id) : null;
    $products_url = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');
    $product_specs = function_exists('custom_box_get_product_specifications') ? custom_box_get_product_specifications($product_id) : array();
    $product_long_content = get_the_content();
    $product_long_content_html = $product_long_content ? apply_filters('the_content', $product_long_content) : '';
    $product_hero_bullets = get_post_meta($product_id, '_custom_box_product_hero_bullets', true);
    $product_faq_html = get_post_meta($product_id, '_custom_box_product_faq_html', true);
    $hide_auto_description_heading = (bool) get_post_meta($product_id, '_custom_box_hide_auto_description_heading', true);

    if (!is_array($product_hero_bullets)) {
        $product_hero_bullets = array();
    }

    $product_benefits = array_values(
        array_filter(
            array_map(
                static function ($benefit) {
                    return sanitize_text_field((string) $benefit);
                },
                $product_hero_bullets
            )
        )
    );

    if (empty($product_benefits) && !empty($product_specs)) {
        foreach ($product_specs as $product_spec) {
            if (empty($product_spec['label']) || empty($product_spec['value'])) {
                continue;
            }

            $product_benefits[] = sprintf(
                '%1$s: %2$s',
                sanitize_text_field((string) $product_spec['label']),
                sanitize_text_field((string) $product_spec['value'])
            );

            if (count($product_benefits) >= 4) {
                break;
            }
        }
    }

    $product_benefits = array_slice($product_benefits, 0, 4);
    $product_proof_items = array();

    foreach ($product_specs as $product_spec) {
        $spec_label = isset($product_spec['label']) ? sanitize_text_field((string) $product_spec['label']) : '';
        $spec_value = isset($product_spec['value']) ? sanitize_text_field((string) $product_spec['value']) : '';

        if (
            !$spec_label
            || !$spec_value
            || !preg_match('/minimum|moq|quantity|custom|size/i', $spec_label)
        ) {
            continue;
        }

        $product_proof_items[] = sprintf('%1$s: %2$s', $spec_label, $spec_value);

        if (count($product_proof_items) >= 2) {
            break;
        }
    }

    if (empty($product_proof_items) && $short_description) {
        $short_description_text = trim(wp_strip_all_tags($short_description));

        if ($short_description_text) {
            $product_proof_items[] = wp_trim_words($short_description_text, 24, '…');
        }
    }

    $gallery_main_id = 'product-gallery-main-' . $product_id;
    $product_overview_id = 'product-overview-' . $product_id;
    $product_specifications_id = 'product-specifications-' . $product_id;

    if ($product_long_content_html && function_exists('custom_box_enhance_blog_article_images')) {
        $product_long_content_html = custom_box_enhance_blog_article_images($product_long_content_html);
    }

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
        <div class="container product-detail-hero-grid product-detail-mobile-order" data-product-mobile-order>
            <div class="product-detail-content product-detail-heading" data-product-heading>
                <nav class="product-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'custom-box-theme'); ?>">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
                    <a href="<?php echo esc_url($products_url); ?>"><?php esc_html_e('Products', 'custom-box-theme'); ?></a>
                    <?php if ($primary_product_category) : ?>
                        <?php $primary_product_category_link = get_term_link($primary_product_category); ?>
                        <?php if (!is_wp_error($primary_product_category_link)) : ?>
                            <a href="<?php echo esc_url($primary_product_category_link); ?>"><?php echo esc_html($primary_product_category->name); ?></a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <span aria-current="page"><?php the_title(); ?></span>
                </nav>

                <h1><?php the_title(); ?></h1>

                <?php if (!empty($product_proof_items)) : ?>
                    <p class="product-proof-line" data-product-proof>
                        <?php foreach ($product_proof_items as $proof_index => $product_proof_item) : ?>
                            <?php if ($proof_index > 0) : ?>
                                <span class="product-proof-separator" aria-hidden="true">&middot;</span>
                            <?php endif; ?>
                            <span><?php echo esc_html($product_proof_item); ?></span>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>
            </div>

            <div
                class="product-detail-media product-gallery-slider"
                data-product-gallery
                data-gallery-autoplay="false"
                aria-label="<?php echo esc_attr(sprintf(__('%s image gallery', 'custom-box-theme'), get_the_title())); ?>"
            >
                <div class="product-gallery-main" id="<?php echo esc_attr($gallery_main_id); ?>">
                    <?php if (!empty($product_gallery_ids)) : ?>
                        <?php foreach ($product_gallery_ids as $gallery_index => $gallery_id) : ?>
                            <?php
                            $is_active_gallery_image = 0 === $gallery_index;
                            $gallery_alt = trim((string) get_post_meta($gallery_id, '_wp_attachment_image_alt', true));
                            $gallery_alt = $gallery_alt ? $gallery_alt : get_the_title() . ' image ' . ($gallery_index + 1);
                            $gallery_slide_id = 'product-gallery-slide-' . $product_id . '-' . $gallery_index;
                            $gallery_thumb_id = 'product-gallery-thumb-' . $product_id . '-' . $gallery_index;
                            $gallery_attributes = array(
                                'class'    => 'product-gallery-image',
                                'alt'      => $gallery_alt,
                                'loading'  => $is_active_gallery_image ? 'eager' : 'lazy',
                                'decoding' => 'async',
                                'sizes'    => '(max-width: 767px) calc(100vw - 36px), (max-width: 1200px) 45vw, 620px',
                            );

                            if ($is_active_gallery_image) {
                                $gallery_attributes['fetchpriority'] = 'high';
                            }

                            ?>
                            <div
                                id="<?php echo esc_attr($gallery_slide_id); ?>"
                                class="product-gallery-panel product-gallery-slide <?php echo $is_active_gallery_image ? 'is-active' : ''; ?>"
                                role="<?php echo count($product_gallery_ids) > 1 ? 'tabpanel' : 'group'; ?>"
                                aria-hidden="<?php echo $is_active_gallery_image ? 'false' : 'true'; ?>"
                                <?php echo $is_active_gallery_image ? '' : 'hidden'; ?>
                                <?php if (count($product_gallery_ids) > 1) : ?>
                                    aria-labelledby="<?php echo esc_attr($gallery_thumb_id); ?>"
                                <?php else : ?>
                                    aria-label="<?php echo esc_attr($gallery_alt); ?>"
                                <?php endif; ?>
                                data-gallery-slide="<?php echo esc_attr($gallery_index); ?>"
                                data-gallery-panel="<?php echo esc_attr($gallery_index); ?>"
                            >
                                <?php
                                echo wp_get_attachment_image(
                                    $gallery_id,
                                    'large',
                                    false,
                                    $gallery_attributes
                                );
                                ?>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif (function_exists('wc_placeholder_img')) : ?>
                        <div
                            id="<?php echo esc_attr('product-gallery-slide-' . $product_id . '-0'); ?>"
                            class="product-gallery-panel product-gallery-slide is-active"
                            role="group"
                            aria-label="<?php echo esc_attr(get_the_title()); ?>"
                            aria-hidden="false"
                            data-gallery-slide="0"
                            data-gallery-panel="0"
                        >
                            <?php
                            echo wc_placeholder_img(
                                'woocommerce_single',
                                array(
                                    'class'         => 'product-gallery-image',
                                    'alt'           => get_the_title(),
                                    'loading'       => 'eager',
                                    'fetchpriority' => 'high',
                                    'decoding'      => 'async',
                                )
                            );
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($product_gallery_ids) > 1) : ?>
                        <button
                            class="product-gallery-arrow product-gallery-prev"
                            type="button"
                            aria-label="<?php esc_attr_e('Previous product image', 'custom-box-theme'); ?>"
                            aria-controls="<?php echo esc_attr($gallery_main_id); ?>"
                            data-gallery-action="previous"
                        >
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button
                            class="product-gallery-arrow product-gallery-next"
                            type="button"
                            aria-label="<?php esc_attr_e('Next product image', 'custom-box-theme'); ?>"
                            aria-controls="<?php echo esc_attr($gallery_main_id); ?>"
                            data-gallery-action="next"
                        >
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (count($product_gallery_ids) > 1) : ?>
                    <div class="product-gallery-strip" role="tablist" aria-label="<?php esc_attr_e('Choose a product image', 'custom-box-theme'); ?>">
                        <?php foreach ($product_gallery_ids as $gallery_index => $gallery_id) : ?>
                            <?php
                            $is_active_gallery_thumb = 0 === $gallery_index;
                            $gallery_slide_id = 'product-gallery-slide-' . $product_id . '-' . $gallery_index;
                            $gallery_thumb_id = 'product-gallery-thumb-' . $product_id . '-' . $gallery_index;
                            ?>
                            <button
                                id="<?php echo esc_attr($gallery_thumb_id); ?>"
                                class="product-gallery-thumb <?php echo $is_active_gallery_thumb ? 'is-active' : ''; ?>"
                                type="button"
                                role="tab"
                                data-gallery-index="<?php echo esc_attr($gallery_index); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('View product image %d', 'custom-box-theme'), $gallery_index + 1)); ?>"
                                aria-controls="<?php echo esc_attr($gallery_slide_id); ?>"
                                aria-selected="<?php echo $is_active_gallery_thumb ? 'true' : 'false'; ?>"
                                aria-current="<?php echo $is_active_gallery_thumb ? 'true' : 'false'; ?>"
                                tabindex="<?php echo $is_active_gallery_thumb ? '0' : '-1'; ?>"
                            >
                                <?php
                                echo wp_get_attachment_image(
                                    $gallery_id,
                                    'thumbnail',
                                    false,
                                    array(
                                        'alt'      => '',
                                        'loading'  => 'lazy',
                                        'decoding' => 'async',
                                        'sizes'    => '64px',
                                    )
                                );
                                ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-detail-content product-detail-summary" data-product-summary>
                <?php if (!empty($product_benefits)) : ?>
                <ul class="product-benefit-list">
                    <?php foreach ($product_benefits as $product_benefit) : ?>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> <?php echo esc_html($product_benefit); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div class="product-detail-actions" role="group" aria-label="<?php esc_attr_e('Product quote actions', 'custom-box-theme'); ?>">
                    <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-primary" data-product-primary-action><?php esc_html_e('Request Custom Packaging Quote', 'custom-box-theme'); ?></a>
                    <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-outline" data-product-secondary-action><?php esc_html_e('Request Free Sample', 'custom-box-theme'); ?></a>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($product_specs)) : ?>
        <section class="product-specifications-section">
            <div class="container">
                <details
                    class="product-spec-card product-spec-card-wide product-specifications-disclosure"
                    data-product-specifications
                    data-mobile-collapsed
                    open
                >
                    <summary class="product-specifications-summary">
                        <?php esc_html_e('Specifications', 'custom-box-theme'); ?>
                    </summary>
                    <div id="<?php echo esc_attr($product_specifications_id); ?>" class="product-specifications-content">
                        <dl class="product-specification-list">
                            <?php foreach ($product_specs as $product_spec) : ?>
                                <?php
                                $spec_label = isset($product_spec['label']) ? trim((string) $product_spec['label']) : '';
                                $spec_value = isset($product_spec['value']) ? trim((string) $product_spec['value']) : '';

                                if (!$spec_label || !$spec_value) {
                                    continue;
                                }
                                ?>
                                <div class="product-specification-row">
                                    <dt><span><?php echo esc_html($spec_label); ?></span></dt>
                                    <dd><strong><?php echo esc_html($spec_value); ?></strong></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    </div>
                </details>
            </div>
        </section>
    <?php endif; ?>

    <section class="product-detail-overview product-story-section">
        <div class="container">
            <div class="product-detail-description product-content-body blog-content blog-article-content">
                <?php if ($short_description) : ?>
                    <details class="product-intro product-overview-disclosure" data-product-overview-disclosure>
                        <summary class="product-overview-toggle">
                            <?php esc_html_e('Read full overview', 'custom-box-theme'); ?>
                        </summary>
                        <div id="<?php echo esc_attr($product_overview_id); ?>" class="product-overview-content">
                            <?php echo wp_kses_post($short_description); ?>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if (!$hide_auto_description_heading) : ?>
                    <h2><?php echo esc_html(get_the_title()); ?> That Balance Presentation and Function</h2>
                <?php endif; ?>
                <?php if ($product_long_content_html) : ?>
                    <?php echo wp_kses_post($product_long_content_html); ?>
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
                    <?php
                    $feedback_image_url = get_template_directory_uri() . '/assets/images/feedback1.jpeg';
                    $feedback_image_id = (int) attachment_url_to_postid($feedback_image_url);

                    if ($feedback_image_id) {
                        echo wp_get_attachment_image(
                            $feedback_image_id,
                            'large',
                            false,
                            array(
                                'alt'      => __('Custom packaging client feedback', 'custom-box-theme'),
                                'loading'  => 'lazy',
                                'decoding' => 'async',
                                'sizes'    => '(max-width: 767px) calc(100vw - 36px), 45vw',
                            )
                        );
                    } else {
                        ?>
                        <img
                            src="<?php echo esc_url($feedback_image_url); ?>"
                            width="2400"
                            height="1792"
                            alt="<?php esc_attr_e('Custom packaging client feedback', 'custom-box-theme'); ?>"
                            loading="lazy"
                            decoding="async"
                        >
                        <?php
                    }
                    ?>
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

    <?php if ($product_faq_html) : ?>
        <?php echo wp_kses_post($product_faq_html); ?>
    <?php endif; ?>

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

                        $related_image_id = (int) $related_product->get_image_id();
                        $related_image_path = $related_image_id ? get_attached_file($related_image_id) : '';
                        $related_image_url = $related_image_id ? wp_get_attachment_url($related_image_id) : '';
                        $related_image_size = ($related_image_path && is_file($related_image_path))
                            ? wp_getimagesize($related_image_path)
                            : false;

                        if (!$related_image_url || !$related_image_size) {
                            $related_image_path = get_template_directory() . '/assets/images/Cardboard-Packaging.webp';
                            $related_image_url = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
                            $related_image_size = wp_getimagesize($related_image_path);
                        }
                        ?>
                        <a class="product-category-card product-related-card" href="<?php echo esc_url(get_permalink($related_id)); ?>">
                            <span class="product-category-card-image product-related-image">
                                <img
                                    src="<?php echo esc_url($related_image_url); ?>"
                                    alt=""
                                    width="<?php echo esc_attr(!empty($related_image_size[0]) ? (int) $related_image_size[0] : 506); ?>"
                                    height="<?php echo esc_attr(!empty($related_image_size[1]) ? (int) $related_image_size[1] : 277); ?>"
                                    loading="lazy"
                                    decoding="async"
                                    sizes="(max-width: 379px) calc(100vw - 36px), (max-width: 767px) calc(50vw - 28px), 260px"
                                >
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
