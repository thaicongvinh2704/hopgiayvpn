<?php
/**
 * About page for VPN Packaging.
 */

get_header();

$theme_uri = get_template_directory_uri();
$quote_url = home_url('/contact/#quote');
?>

<main class="about-page">
    <section class="about-hero">
        <div class="container about-hero-grid">
            <div class="about-hero-content">
                <div class="about-eyebrow">Packaging Manufacturer + Brand Partner</div>
                <h1>About VPN Packaging Factory | Paper Box Manufacturer in Vietnam</h1>
                <p>Learn more about VPN Packaging Factory, a Vietnam-based paper box manufacturer specializing in custom rigid boxes, gift boxes, cosmetic packaging, and factory-direct packaging solutions for global brands and wholesalers.</p>
                <div class="about-hero-actions">
                    <a class="btn-primary" href="#about-factory">Explore Our Factory</a>
                    <a class="btn-outline" href="<?php echo esc_url($quote_url); ?>">Request Quotation</a>
                </div>
            </div>
            <div class="about-hero-media">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-2.webp'); ?>" alt="VPN Packaging factory team and production area" decoding="async">
            </div>
        </div>
    </section>

    <section class="about-overview">
        <div class="container about-split">
            <div>
                <span class="about-section-kicker">Company Overview</span>
                <h2>Vietnam-Based Packaging Manufacturing for Global Buyers</h2>
                <p>VPN Paper Packaging Factory is a Vietnam-based manufacturer specializing in custom paper packaging solutions for brands worldwide.</p>
                <p>With modern machinery, skilled craftsmanship, and a fully integrated production process, we provide premium rigid boxes, folding cartons, paper bags, and luxury packaging tailored to each client's brand identity.</p>
                <div class="about-check-grid">
                    <span><i class="fas fa-check-circle"></i> Direct factory production</span>
                    <span><i class="fas fa-check-circle"></i> OEM & ODM packaging</span>
                    <span><i class="fas fa-check-circle"></i> Custom structural design</span>
                    <span><i class="fas fa-check-circle"></i> Strict quality control</span>
                </div>
            </div>
            <div class="about-overview-media">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-1.webp'); ?>" alt="VPN Packaging factory worker operating packaging production equipment" loading="lazy" decoding="async">
            </div>
        </div>
        <div class="container about-overview-brochure">
            <?php get_template_part('template-parts/home/company-profile'); ?>
        </div>
    </section>

    <section class="about-stats">
        <div class="container about-stats-grid">
            <?php
            $stats = array(
                array('value' => '2,000 m²+', 'label' => 'Factory Area', 'icon' => 'fa-industry'),
                array('value' => '100+', 'label' => 'Packaging Styles', 'icon' => 'fa-boxes-stacked'),
                array('value' => '500+', 'label' => 'Business Clients', 'icon' => 'fa-handshake'),
                array('value' => '50+', 'label' => 'Product Categories', 'icon' => 'fa-layer-group'),
                array('value' => '24/7', 'label' => 'Customer Support', 'icon' => 'fa-headset'),
            );
            foreach ($stats as $stat) :
                ?>
                <div class="about-stat-card">
                    <i class="fas <?php echo esc_attr($stat['icon']); ?>"></i>
                    <strong><?php echo esc_html($stat['value']); ?></strong>
                    <span><?php echo esc_html($stat['label']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="about-factory" class="about-factory">
        <div class="container">
            <div class="about-section-header">
                <span class="about-section-kicker">Our Factory</span>
                <h2>Real Production Capability Behind Every Packaging Project</h2>
                <p>From printing to finishing, assembly, quality inspection, and packing, our in-house workflow gives brands better control over cost, timing, and final quality.</p>
            </div>
            <div class="about-factory-grid">
                <figure class="about-factory-large">
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/factory-team-and-production.webp'); ?>" alt="VPN Packaging Factory Team & Production" loading="lazy" decoding="async">
                    <figcaption>Factory Team & Production</figcaption>
                </figure>
                <figure>
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/Faq-Section-Image.webp'); ?>" alt="Packaging finishing process" loading="lazy" decoding="async">
                    <figcaption>Packaging Finishing</figcaption>
                </figure>
                <figure>
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-2.webp'); ?>" alt="Rigid box assembly" loading="lazy" decoding="async">
                    <figcaption>Rigid Box Assembly</figcaption>
                </figure>
                <figure>
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-1.webp'); ?>" alt="Custom paper packaging quality inspection" loading="lazy" decoding="async">
                    <figcaption>Quality Inspection</figcaption>
                </figure>
                <figure>
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-3.webp'); ?>" alt="VPN Packaging factory packing and dispatch area" loading="lazy" decoding="async">
                    <figcaption>Packing & Dispatch</figcaption>
                </figure>
            </div>
        </div>
    </section>

    <section class="about-why">
        <div class="container">
            <div class="about-section-header">
                <span class="about-section-kicker">Global B2B Packaging Manufacturer</span>
                <h2>Built for Businesses That Need Reliable Bulk Packaging Production</h2>
            </div>
            <div class="about-card-grid">
                <?php
                $strengths = array(
                    array('title' => 'Large-Scale Manufacturing', 'text' => 'Stable production capacity for wholesale and high-volume packaging orders worldwide.', 'icon' => 'fa-industry'),
                    array('title' => 'Factory-Direct Supply', 'text' => 'Work directly with the manufacturer for better pricing, faster communication, and production control.', 'icon' => 'fa-warehouse'),
                    array('title' => 'Export Packaging Solutions', 'text' => 'Supporting international businesses with packaging tailored for global distribution markets.', 'icon' => 'fa-earth-americas'),
                    array('title' => 'Custom OEM & ODM Packaging', 'text' => 'Fully customized structures, printing, and finishing for established brands and distributors.', 'icon' => 'fa-drafting-compass'),
                    array('title' => 'Consistent Quality Control', 'text' => 'Strict production standards ensure stable quality across every bulk order.', 'icon' => 'fa-clipboard-check'),
                    array('title' => 'Dedicated B2B Support', 'text' => 'Professional support for wholesalers, agencies, importers, and enterprise buyers.', 'icon' => 'fa-headset'),
                );
                foreach ($strengths as $item) :
                    ?>
                    <article class="about-info-card">
                        <i class="fas <?php echo esc_attr($item['icon']); ?>"></i>
                        <h3><?php echo esc_html($item['title']); ?></h3>
                        <p><?php echo esc_html($item['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="about-industries">
        <div class="container">
            <div class="about-section-header">
                <span class="about-section-kicker">Product Expertise</span>
                <h2>Industries We Serve</h2>
            </div>
            <div class="about-industry-grid">
                <?php
                $industries = array(
                    array('Cosmetic Packaging', 'cosmetic-set-packaging-boxes', 'gift-box.webp'),
                    array('Gift Boxes', 'luxury-rigid-gift-boxes', 'gift-box2.webp'),
                    array('Jewelry & Watch Boxes', 'watch-packaging-boxes', 'Rigid-Packaging.webp'),
                    array('Paper Tube Boxes', 'custom-paper-tube-packaging-boxes', 'Kraft-Packaging.webp'),
                    array('Luxury Wine Bottle Packaging Boxes', 'luxury-wine-bottle-packaging-boxes', 'gift-box2.webp'),
                    array('Pizza Packaging Boxes', 'pizza-packaging-boxes', 'Takeout-Boxes_1758880241.webp'),
                    array('Chocolate Gift Boxes', 'custom-chocolate-gift-boxes', 'Cardboard-Packaging.webp'),
                    array('Mooncake Gift Boxes', 'mooncake-gift-packaging-boxes', 'SBS-Paperboard-Packaging.webp'),
                );
                foreach ($industries as $industry) :
                    $industry_term = get_term_by('slug', $industry[1], 'product_cat');
                    $industry_image_id = $industry_term && !is_wp_error($industry_term) ? (int) get_term_meta($industry_term->term_id, 'thumbnail_id', true) : 0;
                    $industry_image_url = $industry_image_id ? wp_get_attachment_image_url($industry_image_id, 'medium_large') : '';
                    if (!$industry_image_url) {
                        $industry_image_url = $theme_uri . '/assets/images/' . $industry[2];
                    }
                    ?>
                    <article class="about-industry-card">
                        <img src="<?php echo esc_url($industry_image_url); ?>" alt="<?php echo esc_attr($industry[0]); ?>" loading="lazy" decoding="async">
                        <span><?php echo esc_html($industry[0]); ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="about-process">
        <div class="container">
            <div class="about-section-header">
                <span class="about-section-kicker">Production Process</span>
                <h2>From Packaging Idea to Finished Delivery</h2>
            </div>
            <ol class="about-process-line">
                <li><span>01</span><strong>Consultation</strong><em>Define product, quantity, budget, and brand direction.</em></li>
                <li><span>02</span><strong>Structural Design</strong><em>Develop size, dieline, material, and finishing options.</em></li>
                <li><span>03</span><strong>Sampling</strong><em>Confirm structure, artwork, and finishing before production.</em></li>
                <li><span>04</span><strong>Mass Production</strong><em>Print, finish, assemble, inspect, and pack.</em></li>
                <li><span>05</span><strong>Delivery</strong><em>Prepare packaging for safe local or international shipment.</em></li>
            </ol>
        </div>
    </section>

    <section class="about-quality">
        <div class="container about-quality-grid">
            <div>
                <span class="about-section-kicker">Quality & Materials</span>
                <h2>Material Options and Finishing That Support Premium Branding</h2>
                <p>Choose from ivory paper, kraft paper, duplex board, art paper, FSC-oriented materials, foil stamping, embossing, spot UV, magnetic rigid box structures, and other premium finishes.</p>
            </div>
            <div class="about-quality-list">
                <span>Material inspection</span>
                <span>Color accuracy</span>
                <span>Structural testing</span>
                <span>Final QC</span>
            </div>
        </div>
    </section>

    <section class="about-trust">
        <div class="container about-trust-grid">
            <div>
                <span class="about-section-kicker">Trusted by Growing Brands</span>
                <h2>Supporting Local and International B2B Packaging Buyers</h2>
                <p>VPN Packaging works with cosmetic brands, coffee businesses, fashion startups, jewelry companies, wholesalers, agencies, and growing retail brands that need reliable manufacturing support.</p>
            </div>
            <blockquote>
                &ldquo;VPN helped us create premium cosmetic packaging with excellent finishing quality and fast turnaround.&rdquo;
                <cite>Procurement Manager, Cosmetic Packaging Client</cite>
            </blockquote>
        </div>
    </section>

    <section class="about-final-cta">
        <div class="container">
            <h2>Let's Build Packaging That Elevates Your Brand</h2>
            <p>Share your product, artwork, quantity, and packaging goals. Our factory team will help you choose the right structure, material, and finishing path.</p>
            <div class="about-hero-actions">
                <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Get Free Quote</a>
                <a class="btn-outline" href="<?php echo esc_url($quote_url); ?>">Contact Our Factory</a>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
