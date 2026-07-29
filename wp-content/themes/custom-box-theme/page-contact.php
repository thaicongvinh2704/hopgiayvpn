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
$sales_email = 'paperbox@hopgiayvpn.com';
$factory_name = 'VPN Paper Box Factory';
$factory_address = '1032 An Phu Tay, Hamlet 4, Hung Long Commune, Binh Chanh District, Ho Chi Minh City, Vietnam';
$office_address = $factory_address;
$factory_map_url = 'https://maps.app.goo.gl/Z68geWnrTmx6kaCg6';
$map_embed_url = 'https://maps.google.com/maps?cid=6310512854642764978&z=17&output=embed';
$quote_section_id = 'quote';
$quote_section_href = '#' . $quote_section_id;
$hero_image_url = $theme_uri . '/assets/images/anh-nha-may-2-16x9-100kb.webp';
$quote_action_label = 'Request a custom packaging quote';
$phone_action_label = sprintf('Call VPN Paper Box Factory sales at %s', $phone_display);
$email_action_label = sprintf('Email VPN Packaging sales at %1$s or %2$s', $email, $sales_email);
$map_action_label = 'Open VPN Paper Box Factory in Google Maps (opens in a new tab)';
?>

<main id="main-content" class="contact-page" data-contact-page>
    <section id="contact-introduction" class="contact-hero" data-contact-section="hero" aria-labelledby="contact-page-title">
        <div class="container contact-hero-grid">
            <div class="contact-hero-copy">
                <span class="contact-kicker">Contact VPN Paper Box Factory</span>
                <h1 id="contact-page-title">Talk to a Real Packaging Manufacturer</h1>
                <p>Send your packaging brief, artwork, quantity, or product idea. Our factory team will help you confirm structure, material, finishing, pricing, sampling, and production timeline.</p>
                <div class="contact-hero-actions" role="group" aria-label="Primary contact actions">
                    <a
                        class="btn-primary contact-action-link contact-touch-target contact-action-link--quote"
                        href="<?php echo esc_attr($quote_section_href); ?>"
                        data-contact-action="quote"
                        aria-label="<?php echo esc_attr($quote_action_label); ?>"
                    >Request a Quote</a>
                    <a
                        class="btn-outline contact-action-link contact-touch-target contact-action-link--phone"
                        href="<?php echo esc_url($phone_link); ?>"
                        data-contact-action="phone"
                        aria-label="<?php echo esc_attr($phone_action_label); ?>"
                    >Call Factory Sales</a>
                </div>
            </div>

            <div class="contact-hero-media" data-contact-hero-image>
                <img
                    src="<?php echo esc_url($hero_image_url); ?>"
                    srcset="<?php echo esc_url($hero_image_url); ?> 1280w"
                    sizes="(max-width: 1024px) calc(100vw - 36px), 44vw"
                    width="1280"
                    height="720"
                    alt="VPN Packaging factory team operating paper box production equipment"
                    decoding="async"
                    loading="eager"
                    fetchpriority="high"
                >
            </div>
        </div>
    </section>

    <section id="contact-options" class="contact-quick-section" data-contact-section="options" aria-labelledby="contact-options-title">
        <div class="container">
            <h2 id="contact-options-title" class="screen-reader-text">Contact options</h2>
            <div class="contact-quick-grid" role="list">
                <div class="contact-quick-item" role="listitem">
                    <a
                        class="contact-quick-card contact-action-link contact-touch-target contact-action-link--phone"
                        href="<?php echo esc_url($phone_link); ?>"
                        data-contact-action="phone"
                        aria-label="<?php echo esc_attr($phone_action_label); ?>"
                    >
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        <span>Phone</span>
                        <strong><?php echo esc_html($phone_display); ?></strong>
                        <em>Sales and production support</em>
                    </a>
                </div>

                <div class="contact-quick-item" role="listitem">
                    <a
                        class="contact-quick-card contact-action-link contact-touch-target contact-action-link--email"
                        href="mailto:<?php echo esc_attr($email . ',' . $sales_email); ?>"
                        data-contact-action="email"
                        aria-label="<?php echo esc_attr($email_action_label); ?>"
                    >
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <span>Email</span>
                        <strong><?php echo esc_html($email); ?><br><?php echo esc_html($sales_email); ?></strong>
                        <em>Send artwork and specifications</em>
                    </a>
                </div>

                <div class="contact-quick-item" role="listitem">
                    <div class="contact-quick-card">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                        <span>Working Hours</span>
                        <strong>Mon - Sat: 8:00 AM - 6:00 PM</strong>
                        <em>Vietnam time zone</em>
                    </div>
                </div>

                <div class="contact-quick-item" role="listitem">
                    <a
                        class="contact-quick-card contact-action-link contact-touch-target contact-action-link--map"
                        href="<?php echo esc_url($factory_map_url); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        data-contact-action="map"
                        aria-label="<?php echo esc_attr($map_action_label); ?>"
                    >
                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                        <span>Office &amp; Factory</span>
                        <strong><?php echo esc_html($factory_name); ?></strong>
                        <em>Open in Google Maps</em>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="contact-quote-section" data-contact-section="quote">
        <?php
        get_template_part(
            'template-parts/home/quote-form',
            null,
            array('section_id' => $quote_section_id)
        );
        ?>
    </div>

    <section id="contact-process" class="contact-process-section" data-contact-section="process" aria-labelledby="contact-process-title">
        <div class="container">
            <div class="contact-section-heading contact-section-heading-center">
                <span class="contact-kicker">How We Work</span>
                <h2 id="contact-process-title">A Clear Contact Process for Professional Packaging Projects</h2>
            </div>
            <div class="contact-process-grid" role="list">
                <article role="listitem" aria-labelledby="contact-process-step-1">
                    <span aria-hidden="true">01</span>
                    <h3 id="contact-process-step-1"><span class="screen-reader-text">Step 1: </span>Send Your Brief</h3>
                    <p>Tell us your product, quantity, target market, timeline, and packaging goals.</p>
                </article>
                <article role="listitem" aria-labelledby="contact-process-step-2">
                    <span aria-hidden="true">02</span>
                    <h3 id="contact-process-step-2"><span class="screen-reader-text">Step 2: </span>Factory Review</h3>
                    <p>Our team checks structure, materials, finishing options, and production feasibility.</p>
                </article>
                <article role="listitem" aria-labelledby="contact-process-step-3">
                    <span aria-hidden="true">03</span>
                    <h3 id="contact-process-step-3"><span class="screen-reader-text">Step 3: </span>Quotation & Sampling</h3>
                    <p>Receive a clear quote with recommended specifications and sampling guidance.</p>
                </article>
                <article role="listitem" aria-labelledby="contact-process-step-4">
                    <span aria-hidden="true">04</span>
                    <h3 id="contact-process-step-4"><span class="screen-reader-text">Step 4: </span>Production Support</h3>
                    <p>We support artwork confirmation, bulk production, QC, packing, and shipment.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="contact-location" class="contact-location-section" data-contact-section="location" aria-labelledby="contact-location-title">
        <div class="container contact-location-grid">
            <div class="contact-location-copy">
                <span class="contact-kicker">Factory &amp; Office</span>
                <h2 id="contact-location-title">Visit or Contact Our Ho Chi Minh City Team</h2>
                <h3><?php echo esc_html($factory_name); ?></h3>
                <div class="contact-address-list">
                    <div class="contact-address-item">
                        <strong>Office &amp; Factory</strong>
                        <p><?php echo esc_html($factory_address); ?></p>
                        <a
                            class="btn-outline contact-action-link contact-touch-target contact-action-link--map"
                            href="<?php echo esc_url($factory_map_url); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-contact-action="map"
                            aria-label="<?php echo esc_attr($map_action_label); ?>"
                        >Open Google Map</a>
                    </div>
                </div>
            </div>
            <div class="contact-map" data-contact-map>
                <iframe
                    title="VPN Paper Box Factory location map"
                    src="<?php echo esc_url($map_embed_url); ?>"
                    width="600"
                    height="450"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        </div>
    </section>

    <section id="contact-faq" class="contact-faq-section" data-contact-section="faq" aria-labelledby="contact-faq-title">
        <div class="container">
            <div class="contact-section-heading contact-section-heading-center">
                <span class="contact-kicker">Contact Questions</span>
                <h2 id="contact-faq-title">Common Questions Before You Contact Our Factory</h2>
            </div>
            <div class="contact-faq-grid">
                <article aria-labelledby="contact-faq-reply">
                    <h3 id="contact-faq-reply">How soon will your team reply?</h3>
                    <p>Most packaging requests receive an initial response within one business day, depending on project complexity and artwork details.</p>
                </article>
                <article aria-labelledby="contact-faq-international">
                    <h3 id="contact-faq-international">Can you support international orders?</h3>
                    <p>Yes. VPN Packaging works with overseas buyers and can prepare export-oriented packaging production for different markets.</p>
                </article>
                <article aria-labelledby="contact-faq-dieline">
                    <h3 id="contact-faq-dieline">Do I need a finished dieline?</h3>
                    <p>No. You can send product dimensions or reference photos first. Our team can recommend structure and dieline direction.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
