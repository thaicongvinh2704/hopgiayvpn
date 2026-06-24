<?php
/**
 * Template Name: Contact Page
 *
 * Professional contact page for VPN Packaging.
 */

get_header();

$theme_uri = get_template_directory_uri();
$phone_display = '(+84) 933 102 653';
$phone_link = 'tel:+84933102653';
$email = 'sales.vpn@hopgiayvpn.com';
$sales_email = 'huy.pq@hopgiayvpn.com';
$factory_name = 'VPN Paper Box Factory';
$factory_address = '1032 An Phu Tay, Hamlet 4, Hung Long Commune, Binh Chanh District, Ho Chi Minh City, Vietnam';
$office_address = $factory_address;
$factory_map_url = 'https://maps.app.goo.gl/Z68geWnrTmx6kaCg6';
$map_embed_url = 'https://maps.google.com/maps?cid=6310512854642764978&z=17&output=embed';
?>

<main class="contact-page">
    <section class="contact-hero">
        <div class="container contact-hero-grid">
            <div class="contact-hero-copy">
                <span class="contact-kicker">Contact VPN Paper Box Factory</span>
                <h1>Talk to a Real Packaging Manufacturer</h1>
                <p>Send your packaging brief, artwork, quantity, or product idea. Our factory team will help you confirm structure, material, finishing, pricing, sampling, and production timeline.</p>
                <div class="contact-hero-actions">
                    <a class="btn-primary" href="#quote">Request a Quote</a>
                    <a class="btn-outline" href="<?php echo esc_url($phone_link); ?>">Call Factory Sales</a>
                </div>
            </div>

            <div class="contact-hero-media">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-2.webp'); ?>" alt="VPN Packaging factory production team" decoding="async">
            </div>
        </div>
    </section>

    <section class="contact-quick-section">
        <div class="container contact-quick-grid">
            <a class="contact-quick-card" href="<?php echo esc_url($phone_link); ?>">
                <i class="fas fa-phone"></i>
                <span>Phone</span>
                <strong><?php echo esc_html($phone_display); ?></strong>
                <em>Sales and production support</em>
            </a>

            <a class="contact-quick-card" href="mailto:<?php echo esc_attr($email . ',' . $sales_email); ?>">
                <i class="fas fa-envelope"></i>
                <span>Email</span>
                <strong><?php echo esc_html($email); ?><br><?php echo esc_html($sales_email); ?></strong>
                <em>Send artwork and specifications</em>
            </a>

            <div class="contact-quick-card">
                <i class="fas fa-clock"></i>
                <span>Working Hours</span>
                <strong>Mon - Sat: 8:00 AM - 6:00 PM</strong>
                <em>Vietnam time zone</em>
            </div>

            <a class="contact-quick-card" href="<?php echo esc_url($factory_map_url); ?>" target="_blank" rel="noopener">
                <i class="fas fa-location-dot"></i>
                <span>Office &amp; Factory</span>
                <strong><?php echo esc_html($factory_name); ?></strong>
                <em>Open in Google Maps</em>
            </a>
        </div>
    </section>

    <?php get_template_part('template-parts/home/quote-form'); ?>

    <section class="contact-process-section">
        <div class="container">
            <div class="contact-section-heading contact-section-heading-center">
                <span class="contact-kicker">How We Work</span>
                <h2>A Clear Contact Process for Professional Packaging Projects</h2>
            </div>
            <div class="contact-process-grid">
                <article>
                    <span>01</span>
                    <h3>Send Your Brief</h3>
                    <p>Tell us your product, quantity, target market, timeline, and packaging goals.</p>
                </article>
                <article>
                    <span>02</span>
                    <h3>Factory Review</h3>
                    <p>Our team checks structure, materials, finishing options, and production feasibility.</p>
                </article>
                <article>
                    <span>03</span>
                    <h3>Quotation & Sampling</h3>
                    <p>Receive a clear quote with recommended specifications and sampling guidance.</p>
                </article>
                <article>
                    <span>04</span>
                    <h3>Production Support</h3>
                    <p>We support artwork confirmation, bulk production, QC, packing, and shipment.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="contact-location-section">
        <div class="container contact-location-grid">
            <div class="contact-location-copy">
                <span class="contact-kicker">Factory &amp; Office</span>
                <h2>Visit or Contact Our Ho Chi Minh City Team</h2>
                <h3><?php echo esc_html($factory_name); ?></h3>
                <div class="contact-address-list">
                    <div class="contact-address-item">
                        <strong>Office &amp; Factory</strong>
                        <p><?php echo esc_html($factory_address); ?></p>
                        <a class="btn-outline" href="<?php echo esc_url($factory_map_url); ?>" target="_blank" rel="noopener">Open Google Map</a>
                    </div>
                </div>
            </div>
            <div class="contact-map">
                <iframe
                    title="VPN Paper Box Factory location map"
                    src="<?php echo esc_url($map_embed_url); ?>"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        </div>
    </section>

    <section class="contact-faq-section">
        <div class="container contact-faq-grid">
            <article>
                <h3>How soon will your team reply?</h3>
                <p>Most packaging requests receive an initial response within one business day, depending on project complexity and artwork details.</p>
            </article>
            <article>
                <h3>Can you support international orders?</h3>
                <p>Yes. VPN Packaging works with overseas buyers and can prepare export-oriented packaging production for different markets.</p>
            </article>
            <article>
                <h3>Do I need a finished dieline?</h3>
                <p>No. You can send product dimensions or reference photos first. Our team can recommend structure and dieline direction.</p>
            </article>
        </div>
    </section>
</main>

<?php get_footer(); ?>
