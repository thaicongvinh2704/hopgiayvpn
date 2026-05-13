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

$landing_categories = array();

$landing_parent_category = taxonomy_exists('product_cat') ? get_term_by('name', 'Custom Packaging Boxes', 'product_cat') : false;

if ($landing_parent_category && !is_wp_error($landing_parent_category)) {
    $product_categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => $landing_parent_category->term_id,
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
        'number'     => 55,
    ));

    if (!empty($product_categories) && !is_wp_error($product_categories)) {
        usort($product_categories, function ($a, $b) {
            $a_featured = (int) get_term_meta($a->term_id, 'custom_box_category_featured', true);
            $b_featured = (int) get_term_meta($b->term_id, 'custom_box_category_featured', true);

            if ($a_featured !== $b_featured) {
                return $b_featured <=> $a_featured;
            }

            return $a->term_id <=> $b->term_id;
        });

        $landing_categories = array_values(array_filter($product_categories, function ($category) {
            return (bool) get_term_meta($category->term_id, 'custom_box_category_featured', true);
        }));

        if (empty($landing_categories)) {
            $landing_categories = $product_categories;
        }
    }
}

$landing_categories = array_slice($landing_categories, 0, 6);

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

$landing_pain_points = array(
    array('Unclear pricing', 'Factory-direct consultation helps you compare material, finish, and quantity options before production.'),
    array('Slow sampling', 'Our team prepares dielines, artwork checks, and samples so your project can move with fewer delays.'),
    array('Weak packaging structure', 'We match paperboard, rigid board, inserts, and carton strength to the product and shipping route.'),
    array('Color and finishing risk', 'Offset printing, foil, embossing, lamination, and spot UV are reviewed before mass production.'),
);

$landing_testimonials = array(
    array('Premium rigid gift boxes arrived with sharp foil details and clean assembly. The team understood export packing and kept our schedule on track.', 'Sophia Bennett', 'Royal Estate Wines'),
    array('VPN helped us choose structure, inserts, and matte finish for our cosmetic launch. Communication was fast and the sample process was clear.', 'Daniel Carter', 'Nova Beauty Supply'),
    array('The final food packaging was sturdy, neatly printed, and ready for retail presentation. Factory support made the bulk order much easier.', 'Michael Laurent', 'Bakery Retail Group'),
);
?>

<main class="landing-page">
    <section class="landing-hero landing-wow">
        <div class="container">
            <a class="landing-banner-frame" href="<?php echo esc_url($quote_url); ?>" aria-label="<?php esc_attr_e('Get your instant packaging quote', 'custom-box-theme'); ?>">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/banner-landing-page.webp'); ?>" alt="VPN Packaging Factory premium packaging factory price banner" decoding="async" fetchpriority="high">
            </a>
            <div class="landing-banner-copy">
                <span class="landing-eyebrow">Vietnam Custom Packaging Manufacturer</span>
                <h1>Custom Packaging Boxes Ready for Your Next Order</h1>
                <p>Build paper boxes, rigid boxes, food packaging, cosmetic boxes, paper bags, and branded retail packaging with direct factory support from Vietnam.</p>
                <div class="landing-hero-actions">
                    <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Start Free Quote</a>
                    <a class="btn-outline" href="#landing-showcase">View Packaging Options</a>
                </div>
                <div class="landing-proof-row" aria-label="Factory highlights">
                    <span><strong>9+</strong> Years Experience</span>
                    <span><strong>50+</strong> Packaging Categories</span>
                    <span><strong>OEM</strong> / ODM Production</span>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-soft-cta">
        <div class="container landing-soft-cta-inner">
            <span>Not sure which box style fits your product?</span>
            <a href="<?php echo esc_url($quote_url); ?>">Send basic details for a fast recommendation</a>
        </div>
    </section>

    <section class="landing-section landing-categories" id="landing-showcase">
        <div class="container">
            <div class="landing-section-header">
                <span class="landing-eyebrow">Packaging Showcase</span>
                <h2>Choose a Packaging Direction Before You Quote</h2>
                <p>Start from a proven packaging type, then customize size, material, printing, finish, inserts, and order quantity around your product.</p>
            </div>
            <div class="landing-category-grid">
                <?php if (!empty($landing_categories) && !is_wp_error($landing_categories)) : ?>
                <?php foreach ($landing_categories as $category) : ?>
                    <?php
                    $image_id = (int) get_term_meta($category->term_id, 'thumbnail_id', true);
                    if (!$image_id) {
                        $image_id = (int) get_term_meta($category->term_id, 'custom_box_category_image_id', true);
                    }

                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium_large') : $theme_uri . '/assets/images/custom-cardboard-boxes.webp';
                    $category_link = get_term_link($category);
                    if (is_wp_error($category_link)) {
                        continue;
                    }

                    $category_description = $category->description
                        ? wp_trim_words(wp_strip_all_tags($category->description), 16)
                        : 'Custom packaging options tailored by size, material, printing, finishing, and order quantity.';
                    ?>
                    <article class="landing-category-card">
                        <a href="<?php echo esc_url($category_link); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" loading="lazy" decoding="async">
                            <span><?php echo esc_html($category->name); ?></span>
                        </a>
                        <p><?php echo esc_html($category_description); ?></p>
                    </article>
                <?php endforeach; ?>
                <?php else : ?>
                    <p class="landing-categories-empty">
                        <?php esc_html_e('Please mark product categories as featured to show them in this showcase.', 'custom-box-theme'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="landing-small-cta">
        <div class="container landing-small-cta-inner">
            <h2>Have size, quantity, or artwork ready?</h2>
            <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Get Production Advice</a>
        </div>
    </section>

    <section class="landing-section landing-factory">
        <div class="container landing-factory-grid">
            <div class="landing-factory-copy">
                <span class="landing-eyebrow">Factory Trust</span>
                <h2>Factory-Direct Packaging With Export Support</h2>
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

    <section class="landing-section landing-mini-form-section">
        <div class="container landing-mini-form-grid">
            <div>
                <span class="landing-eyebrow">Quick Start</span>
                <h2>Send 3 Details and Get Direction</h2>
                <p>Use this short form if you only know the product type and contact details. Our team can help define material, size, quantity, and finishing later.</p>
            </div>
            <form class="landing-mini-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="custom_box_quote_form">
                <input type="hidden" name="product_type" value="boxes">
                <input type="hidden" name="custom_box_quote_nonce" value="<?php echo esc_attr(wp_create_nonce('custom_box_quote_form')); ?>">
                <input type="text" name="product_name" placeholder="Packaging type or product name" required>
                <input type="text" name="full_name" placeholder="Your name" required>
                <input type="email" name="email" placeholder="Email address" required>
                <input type="hidden" name="message" value="Mini landing form request. Please contact this customer for packaging details.">
                <button class="btn-primary" type="submit">Send Quick Request</button>
            </form>
        </div>
    </section>

    <section class="landing-section landing-pain-points">
        <div class="container">
            <div class="landing-section-header">
                <span class="landing-eyebrow">Common Buyer Problems</span>
                <h2>We Help Remove Packaging Order Friction</h2>
            </div>
            <div class="landing-pain-grid">
                <?php foreach ($landing_pain_points as $pain_point) : ?>
                    <article>
                        <i class="fas fa-circle-exclamation"></i>
                        <h3><?php echo esc_html($pain_point[0]); ?></h3>
                        <p><?php echo esc_html($pain_point[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="landing-section landing-quote-band" id="landing-quote">
        <div class="container landing-quote-intro">
            <span class="landing-eyebrow">Main Conversion Form</span>
            <h2>Send Full Packaging Requirements</h2>
            <p>Tell us your size, quantity, material, printing, finishing, artwork, and delivery country. Our team will recommend a practical production path and quotation.</p>
        </div>
        <?php get_template_part('template-parts/home/quote-form'); ?>
    </section>

    <section class="landing-section landing-testimonials">
        <div class="container">
            <div class="landing-section-header">
                <span class="landing-eyebrow">Buyer Feedback</span>
                <h2>Packaging Results from Real Projects</h2>
            </div>
            <div class="landing-testimonial-grid">
                <?php foreach ($landing_testimonials as $testimonial) : ?>
                    <article>
                        <div class="landing-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                        <p>&ldquo;<?php echo esc_html($testimonial[0]); ?>&rdquo;</p>
                        <strong><?php echo esc_html($testimonial[1]); ?></strong>
                        <span><?php echo esc_html($testimonial[2]); ?></span>
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
