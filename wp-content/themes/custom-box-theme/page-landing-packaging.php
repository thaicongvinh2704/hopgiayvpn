<?php
/**
 * Template Name: Packaging Landing Page
 *
 * International landing page for custom packaging buyers.
 */

add_filter('language_attributes', function () {
    return 'lang="en-US"';
});

get_header();

$theme_uri = get_template_directory_uri();
$quote_url = '#landing-quote';
$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

$landing_categories = array(
    array('Cosmetic Boxes', 'cosmetic-set-packaging-boxes', 'gift-box.png', 'Premium folding cartons and rigid boxes for skincare, beauty, and fragrance brands.'),
    array('Luxury Gift Boxes', 'luxury-rigid-gift-boxes', 'gift-box2.jpg', 'Rigid gift packaging with magnetic closure, foil stamping, inserts, and custom finishes.'),
    array('Jewelry & Watch Boxes', 'watch-packaging-boxes', 'Rigid-Packaging.webp', 'Protective presentation boxes designed for high-value retail and gifting moments.'),
    array('Food Packaging', 'pizza-packaging-boxes', 'Takeout-Boxes_1758880241.jpg', 'Custom food-safe paper packaging for takeaway, bakery, chocolate, and retail food brands.'),
    array('Paper Tube Boxes', 'custom-paper-tube-packaging-boxes', 'Kraft-Packaging.webp', 'Sustainable paper tube packaging for candles, cosmetics, tea, coffee, and gifts.'),
    array('Paper Bags', 'custom-paper-bags', 'custom-cardboard-boxes.webp', 'Custom printed paper bags with handles, premium paper stocks, and brand-ready finishes.'),
);

$landing_steps = array(
    array('01', 'Share Requirements', 'Send size, quantity, artwork, material preference, and delivery destination.'),
    array('02', 'Design & Quotation', 'Our team recommends structure, material, finishing, and a production-ready price.'),
    array('03', 'Sampling', 'Approve dieline, printing details, materials, and finishing before mass production.'),
    array('04', 'Manufacturing', 'Printing, die cutting, finishing, assembly, quality control, and export packing.'),
    array('05', 'Delivery', 'Your packaging is packed safely and shipped according to the agreed timeline.'),
);

$landing_faqs = array(
    array('What information should I send for a quote?', 'Please send product size, packaging style, order quantity, material preference, printing and finishing requirements, artwork if available, and destination country.'),
    array('Can you support custom structure and dieline design?', 'Yes. We support custom sizing, structural design, dieline preparation, material selection, printing, inserts, and finishing for OEM and ODM packaging projects.'),
    array('Do you provide samples before mass production?', 'Yes. Sampling is available so your team can confirm structure, material, color, and finishing before production begins.'),
    array('What packaging materials can you produce?', 'Common materials include SBS paperboard, kraft paper, corrugated board, rigid greyboard, duplex board, coated paper, and recycled paper options.'),
    array('Do you ship internationally?', 'Yes. VPN Packaging supports local and international B2B buyers with export-ready packing and practical shipping coordination.'),
);
?>

<main class="landing-page">
    <section class="landing-hero">
        <div class="container landing-hero-grid">
            <div class="landing-hero-copy">
                <span class="landing-eyebrow">Vietnam Custom Packaging Manufacturer</span>
                <h1>Custom Packaging Boxes Built for Global Brands</h1>
                <p>Work directly with VPN Packaging Factory for custom paper boxes, rigid boxes, paper bags, and export-ready packaging solutions with flexible materials, printing, and finishing.</p>
                <div class="landing-hero-actions">
                    <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Get Free Quote</a>
                    <a class="btn-outline" href="<?php echo esc_url($shop_url); ?>">View Products</a>
                </div>
                <div class="landing-proof-row" aria-label="Factory highlights">
                    <span><strong>9+</strong> Years Experience</span>
                    <span><strong>50+</strong> Categories</span>
                    <span><strong>OEM</strong> / ODM Support</span>
                </div>
            </div>
            <div class="landing-hero-media">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/product-banner1.png'); ?>" alt="Luxury custom rigid packaging box manufactured by VPN Packaging" decoding="async" fetchpriority="high">
            </div>
        </div>
    </section>

    <section class="landing-trust">
        <div class="container landing-trust-grid">
            <div><i class="fas fa-industry"></i><strong>Direct Factory</strong><span>No middlemen, clearer pricing and faster communication.</span></div>
            <div><i class="fas fa-drafting-compass"></i><strong>Custom Engineering</strong><span>Structure, dieline, inserts, and material guidance.</span></div>
            <div><i class="fas fa-print"></i><strong>Premium Printing</strong><span>Offset, digital, foil, embossing, spot UV, and lamination.</span></div>
            <div><i class="fas fa-clipboard-check"></i><strong>Quality Control</strong><span>Production checks before packing and delivery.</span></div>
        </div>
    </section>

    <section class="landing-section landing-categories" id="landing-products">
        <div class="container">
            <div class="landing-section-header">
                <span class="landing-eyebrow">Packaging Solutions</span>
                <h2>Popular Custom Packaging for International Buyers</h2>
                <p>Choose a proven packaging direction, then customize size, material, printing, finish, inserts, and order quantity around your product.</p>
            </div>
            <div class="landing-category-grid">
                <?php foreach ($landing_categories as $item) : ?>
                    <?php
                    $term = get_term_by('slug', $item[1], 'product_cat');
                    $image_id = $term && !is_wp_error($term) ? (int) get_term_meta($term->term_id, 'thumbnail_id', true) : 0;
                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium_large') : $theme_uri . '/assets/images/' . $item[2];
                    $term_link = $term && !is_wp_error($term) ? get_term_link($term) : $quote_url;
                    if (is_wp_error($term_link)) {
                        $term_link = $quote_url;
                    }
                    ?>
                    <article class="landing-category-card">
                        <a href="<?php echo esc_url($term_link); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($item[0]); ?>" loading="lazy" decoding="async">
                            <span><?php echo esc_html($item[0]); ?></span>
                        </a>
                        <p><?php echo esc_html($item[3]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="landing-section landing-factory">
        <div class="container landing-factory-grid">
            <div class="landing-factory-copy">
                <span class="landing-eyebrow">Why VPN Packaging</span>
                <h2>Factory-Direct Packaging With Practical Export Support</h2>
                <p>Our in-house workflow helps brands control cost, quality, and delivery across custom packaging projects. From design discussion to finished packing, your order is handled by a team that understands B2B production needs.</p>
                <ul class="landing-check-list">
                    <li><i class="fas fa-check-circle"></i> Custom paper boxes, rigid boxes, cartons, bags, and inserts</li>
                    <li><i class="fas fa-check-circle"></i> Material, structure, color, and finishing consultation</li>
                    <li><i class="fas fa-check-circle"></i> Sampling before mass production</li>
                    <li><i class="fas fa-check-circle"></i> Reliable support for brands, importers, distributors, and agencies</li>
                </ul>
            </div>
            <div class="landing-factory-media">
                <figure class="landing-factory-large">
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/factory-team-and-production.jpg'); ?>" alt="VPN Packaging factory production team" loading="lazy" decoding="async">
                </figure>
                <figure>
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/print-finishing-carton-boxex.webp'); ?>" alt="Packaging printing and finishing process" loading="lazy" decoding="async">
                </figure>
                <figure>
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-fly.png'); ?>" alt="Aerial view of VPN Packaging Factory" loading="lazy" decoding="async">
                </figure>
            </div>
        </div>
    </section>

    <section class="landing-section landing-process">
        <div class="container">
            <div class="landing-section-header">
                <span class="landing-eyebrow">Simple Production Workflow</span>
                <h2>From Packaging Idea to Finished Delivery</h2>
            </div>
            <ol class="landing-process-list">
                <?php foreach ($landing_steps as $step) : ?>
                    <li>
                        <span><?php echo esc_html($step[0]); ?></span>
                        <strong><?php echo esc_html($step[1]); ?></strong>
                        <p><?php echo esc_html($step[2]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="landing-section landing-options">
        <div class="container landing-options-grid">
            <div>
                <span class="landing-eyebrow">Materials & Finishing</span>
                <h2>Build the Right Look, Protection, and Brand Feel</h2>
                <p>Every packaging project can be tailored around your product position, retail channel, shipping needs, and budget.</p>
            </div>
            <div class="landing-option-panel">
                <h3>Materials</h3>
                <div class="landing-tags">
                    <span>SBS Paperboard</span><span>Kraft Paper</span><span>Rigid Board</span><span>Corrugated Board</span><span>Duplex Board</span><span>Recycled Paper</span>
                </div>
            </div>
            <div class="landing-option-panel">
                <h3>Finishing</h3>
                <div class="landing-tags">
                    <span>Matte Lamination</span><span>Gloss Lamination</span><span>Foil Stamping</span><span>Embossing</span><span>Spot UV</span><span>Window Patch</span>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section landing-quote-band" id="landing-quote">
        <div class="container landing-quote-intro">
            <span class="landing-eyebrow">Request a Quote</span>
            <h2>Send Your Packaging Requirements</h2>
            <p>Tell us your size, quantity, material, printing, finishing, and delivery country. Our team will recommend a practical production path and quotation.</p>
        </div>
        <?php get_template_part('template-parts/home/quote-form'); ?>
    </section>

    <section class="landing-section landing-faq">
        <div class="container">
            <div class="landing-section-header">
                <span class="landing-eyebrow">Buyer Questions</span>
                <h2>Helpful Details Before You Start</h2>
            </div>
            <div class="landing-faq-grid">
                <?php foreach ($landing_faqs as $faq) : ?>
                    <article>
                        <h3><?php echo esc_html($faq[0]); ?></h3>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="landing-final-cta">
        <div class="container landing-final-inner">
            <h2>Ready to Produce Custom Packaging for Your Brand?</h2>
            <p>VPN Packaging helps international buyers develop practical, premium, and production-ready packaging directly from Vietnam.</p>
            <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Start Your Quote</a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
