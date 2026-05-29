<?php
/**
 * Template Name: Packaging Landing Page 2
 *
 * Dense product-led landing page inspired by marketplace-style conversion layouts.
 */

get_header();

$theme_uri = get_template_directory_uri();
$quote_url = '#landing-two-contact';
$products_url = function_exists('custom_box_get_products_url')
    ? custom_box_get_products_url()
    : (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/products/'));

$landing_two_products = new WP_Query(array(
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'orderby'        => 'date',
    'order'          => 'DESC',
));

$landing_two_logos = array(
    'client-logos/dcons-logo.png',
    'client-logos/iplus-logo.png',
    'client-logos/lcsgroup-logo.png',
    'client-logos/mkt-logo.png',
    'client-logos/saokim-logo.png',
    'client-logos/sharon-logo-.png',
);
?>

<main class="landing-two-page">
    <section class="landing-two-hero">
        <img class="landing-two-hero-image" src="<?php echo esc_url($theme_uri . '/assets/images/banner-landing-page.webp'); ?>" alt="<?php esc_attr_e('Packaging manufacturing showcase banner', 'custom-box-theme'); ?>" fetchpriority="high" decoding="async">
        <div class="container landing-two-hero-inner">
            <div class="landing-two-hero-copy">
                <span><?php esc_html_e('Factory-direct packaging service', 'custom-box-theme'); ?></span>
                <h1><?php esc_html_e('Custom Packaging Solutions Ready for Fast Quotation', 'custom-box-theme'); ?></h1>
                <p><?php esc_html_e('Explore featured box styles, compare practical options, and send your requirements to our production team in one smooth landing flow.', 'custom-box-theme'); ?></p>
                <div class="landing-two-actions">
                    <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>"><?php esc_html_e('Get Consultation', 'custom-box-theme'); ?></a>
                    <a class="btn-outline" href="<?php echo esc_url($products_url); ?>"><?php esc_html_e('View Products', 'custom-box-theme'); ?></a>
                </div>
                <ul class="landing-two-bullets">
                    <li><i class="fas fa-check-circle"></i><?php esc_html_e('Custom sizes, inserts, finishing, and printing', 'custom-box-theme'); ?></li>
                    <li><i class="fas fa-check-circle"></i><?php esc_html_e('Support for brands, resellers, and export buyers', 'custom-box-theme'); ?></li>
                    <li><i class="fas fa-check-circle"></i><?php esc_html_e('Sampling guidance before production', 'custom-box-theme'); ?></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="landing-two-product-section">
        <div class="container">
            <div class="landing-two-toolbar">
                <h2><?php esc_html_e('Featured Packaging Picks', 'custom-box-theme'); ?></h2>
                <a href="<?php echo esc_url($products_url); ?>"><?php esc_html_e('See all products', 'custom-box-theme'); ?></a>
            </div>

            <?php if ($landing_two_products->have_posts()) : ?>
                <div class="landing-two-product-grid">
                    <?php while ($landing_two_products->have_posts()) : ?>
                        <?php
                        $landing_two_products->the_post();
                        global $product;

                        $product_link = get_permalink();
                        $product_image = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                        if (!$product_image) {
                            $product_image = $theme_uri . '/assets/images/custom-cardboard-boxes.webp';
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
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="landing-two-empty">
                    <p><?php esc_html_e('Add WooCommerce products to populate this showcase automatically.', 'custom-box-theme'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="landing-two-subscribe">
        <div class="container landing-two-subscribe-inner">
            <strong><?php esc_html_e('Register for packaging updates', 'custom-box-theme'); ?></strong>
            <form action="<?php echo esc_url($quote_url); ?>" method="get">
                <input type="email" placeholder="<?php esc_attr_e('Enter your email', 'custom-box-theme'); ?>">
                <button type="submit" aria-label="<?php esc_attr_e('Submit email', 'custom-box-theme'); ?>"><i class="fas fa-arrow-right"></i></button>
            </form>
        </div>
    </section>

    <section class="landing-two-content">
        <div class="container landing-two-content-grid">
            <div class="landing-two-copy">
                <h2><?php esc_html_e('Packaging Consultation Built for Faster Decisions', 'custom-box-theme'); ?></h2>
                <p><?php esc_html_e('This second landing layout puts the product catalog first, then moves visitors into education, trust, and consultation. It is ideal when you want a browsing-heavy page that still converts clearly.', 'custom-box-theme'); ?></p>
                <p><?php esc_html_e('Use it for featured packaging ranges, promotional collections, or paid traffic campaigns that need quick scanning before a form submission.', 'custom-box-theme'); ?></p>
                <ul>
                    <li><?php esc_html_e('Dense product discovery with compact cards', 'custom-box-theme'); ?></li>
                    <li><?php esc_html_e('Clear mid-page subscribe and conversion points', 'custom-box-theme'); ?></li>
                    <li><?php esc_html_e('Strong follow-up blocks for trust and consultation', 'custom-box-theme'); ?></li>
                </ul>
            </div>
            <figure class="landing-two-process">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/print-finishing-carton-boxex.webp'); ?>" alt="<?php esc_attr_e('Packaging production workflow', 'custom-box-theme'); ?>" loading="lazy" decoding="async">
            </figure>
        </div>
    </section>

    <section class="landing-two-contact" id="landing-two-contact">
        <div class="container landing-two-contact-grid">
            <div class="landing-two-contact-form">
                <h2><?php esc_html_e('Send Your Consultation Request', 'custom-box-theme'); ?></h2>
                <?php if (isset($_GET['quote_status'])) : ?>
                    <?php
                    $quote_status = sanitize_text_field(wp_unslash($_GET['quote_status']));
                    $quote_messages = array(
                        'success' => 'Thank you. Your quote request has been sent successfully.',
                        'failed'  => 'Sorry, we could not send your request right now. Please try again later.',
                        'missing' => 'Please fill in your name, email, and product name.',
                        'invalid' => 'The form session expired. Please refresh the page and try again.',
                        'file'    => 'Please upload a valid artwork file under 10MB.',
                    );
                    ?>
                    <?php if (isset($quote_messages[$quote_status])) : ?>
                        <div class="quote-form-message quote-form-message-<?php echo esc_attr($quote_status); ?>">
                            <?php echo esc_html($quote_messages[$quote_status]); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <form class="landing-two-full-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="custom_box_quote_form">
                    <input type="hidden" name="product_type" value="boxes">
                    <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); ?>

                    <label>
                        <?php esc_html_e('Product Name', 'custom-box-theme'); ?>
                        <input type="text" name="product_name" placeholder="<?php esc_attr_e('Boxes', 'custom-box-theme'); ?>" value="<?php echo esc_attr(function_exists('custom_box_quote_product_name') ? custom_box_quote_product_name() : ''); ?>" required>
                    </label>

                    <label>
                        <?php esc_html_e('Product Size', 'custom-box-theme'); ?>
                        <div class="landing-two-form-row landing-two-size-row">
                            <input type="number" name="length" min="0" step="any" placeholder="<?php esc_attr_e('Length', 'custom-box-theme'); ?>">
                            <input type="number" name="width" min="0" step="any" placeholder="<?php esc_attr_e('Width', 'custom-box-theme'); ?>">
                            <input type="number" name="depth" min="0" step="any" placeholder="<?php esc_attr_e('Depth', 'custom-box-theme'); ?>">
                            <select name="unit" required>
                                <option value=""><?php esc_html_e('Units', 'custom-box-theme'); ?></option>
                                <option value="cm">CM</option>
                                <option value="mm">MM</option>
                                <option value="inch">Inch</option>
                            </select>
                        </div>
                    </label>

                    <label>
                        <?php esc_html_e('More Information', 'custom-box-theme'); ?>
                        <div class="landing-two-form-row landing-two-option-row">
                            <select name="stock_option">
                                <option value=""><?php esc_html_e('Stock Options', 'custom-box-theme'); ?></option>
                                <option value="12pt SBS Paperboard">12pt SBS Paperboard</option>
                                <option value="14pt C1S / C2S Cardstock">14pt C1S / C2S Cardstock</option>
                                <option value="16pt Premium Paperboard">16pt Premium Paperboard</option>
                                <option value="18pt Coated Cardstock">18pt Coated Cardstock</option>
                                <option value="20pt Thick Cardstock">20pt Thick Cardstock</option>
                                <option value="22pt Rigid Stock">22pt Rigid Stock</option>
                                <option value="24pt Chipboard">24pt Chipboard</option>
                                <option value="Kraft Brown Paperboard">Kraft Brown Paperboard</option>
                                <option value="White Kraft Board">White Kraft Board</option>
                                <option value="Corrugated E-Flute">Corrugated E-Flute</option>
                                <option value="Corrugated B-Flute">Corrugated B-Flute</option>
                                <option value="Corrugated C-Flute">Corrugated C-Flute</option>
                                <option value="Rigid 60-100 pt">Rigid 60-100 pt</option>
                                <option value="Recycled Cardstock">Recycled Cardstock</option>
                                <option value="Textured / Linen">Textured / Linen</option>
                                <option value="Metallic / Pearlescent">Metallic / Pearlescent</option>
                                <option value="Custom Option (other)">Custom Option (other)</option>
                            </select>
                            <select name="printing_option">
                                <option value=""><?php esc_html_e('Printing Options', 'custom-box-theme'); ?></option>
                                <option value="No Printing (Plain)">No Printing (Plain)</option>
                                <option value="1 Color (Single Side)">1 Color (Single Side)</option>
                                <option value="2 Color (Single Side)">2 Color (Single Side)</option>
                                <option value="Full Color CMYK">Full Color CMYK</option>
                                <option value="PMS (Pantone) Printing">PMS (Pantone) Printing</option>
                                <option value="Digital Printing">Digital Printing</option>
                                <option value="Offset Printing">Offset Printing</option>
                                <option value="Inside & Outside Printing">Inside & Outside Printing</option>
                                <option value="Spot Color Printing">Spot Color Printing</option>
                                <option value="Custom Option (other)">Custom Option (other)</option>
                            </select>
                            <select name="finishing_option">
                                <option value=""><?php esc_html_e('Finishing Options', 'custom-box-theme'); ?></option>
                                <option value="Gloss Lamination">Gloss Lamination</option>
                                <option value="Matte Lamination">Matte Lamination</option>
                                <option value="Soft Touch Lamination">Soft Touch Lamination</option>
                                <option value="Spot UV Coating">Spot UV Coating</option>
                                <option value="Aqueous Coating">Aqueous Coating</option>
                                <option value="Foil Stamping">Foil Stamping</option>
                                <option value="Embossing">Embossing</option>
                                <option value="Debossing">Debossing</option>
                                <option value="Die Cutting">Die Cutting</option>
                                <option value="Window Patching">Window Patching</option>
                                <option value="Inner Foil Lining">Inner Foil Lining</option>
                                <option value="Raised Ink">Raised Ink</option>
                                <option value="Custom Option (other)">Custom Option (other)</option>
                            </select>
                        </div>
                    </label>

                    <label>
                        <?php esc_html_e('Quantity', 'custom-box-theme'); ?>
                        <input type="number" name="quantity" placeholder="<?php esc_attr_e('Quantity', 'custom-box-theme'); ?>" min="1">
                    </label>

                    <label>
                        <?php esc_html_e('Upload Your Artwork', 'custom-box-theme'); ?>
                        <input type="file" name="artwork" accept=".png,.pdf,.jpg,.jpeg,.webp,.doc,.docx,.gif,.psd,.cdr,.eps">
                    </label>

                    <label>
                        <?php esc_html_e('Personal Information', 'custom-box-theme'); ?>
                        <input type="text" name="full_name" placeholder="<?php esc_attr_e('Full Name', 'custom-box-theme'); ?>" required>
                        <div class="landing-two-form-row">
                            <input type="text" name="phone" placeholder="<?php esc_attr_e('Contact Number', 'custom-box-theme'); ?>">
                            <input type="email" name="email" placeholder="<?php esc_attr_e('Email', 'custom-box-theme'); ?>" required>
                        </div>
                    </label>

                    <textarea name="message" placeholder="<?php esc_attr_e('Additional Message', 'custom-box-theme'); ?>"></textarea>
                    <button class="btn-primary" type="submit"><?php esc_html_e('Send Request', 'custom-box-theme'); ?></button>
                </form>
            </div>
            <aside class="landing-two-support">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/factory-team-and-production.webp'); ?>" alt="<?php esc_attr_e('Packaging support team', 'custom-box-theme'); ?>" loading="lazy" decoding="async">
                <div>
                    <span><?php esc_html_e('Dedicated support', 'custom-box-theme'); ?></span>
                    <h2><?php esc_html_e('Helpful answers at every order stage', 'custom-box-theme'); ?></h2>
                    <p><?php esc_html_e('From first concept to finished shipment, visitors get a compact visual trust block beside the consultation form.', 'custom-box-theme'); ?></p>
                </div>
            </aside>
        </div>
    </section>

    <section class="landing-two-partners">
        <div class="container">
            <h2><?php esc_html_e('Trusted by Growing Brands', 'custom-box-theme'); ?></h2>
            <div class="landing-two-logo-row">
                <?php foreach ($landing_two_logos as $logo) : ?>
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/' . $logo); ?>" alt="<?php esc_attr_e('Partner logo', 'custom-box-theme'); ?>" loading="lazy" decoding="async">
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
