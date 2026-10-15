<?php
/**
 * Template Name: Packaging Landing Page
 *
 * B2B money page for global custom packaging buyers.
 */

add_filter('language_attributes', function () {
    return 'lang="en-US"';
});

get_header();

$theme_uri = get_template_directory_uri();
$quote_url = '#factory-quote';

$landing_url = function_exists('custom_box_get_packaging_money_page_url')
    ? custom_box_get_packaging_money_page_url()
    : home_url('/custom-packaging-boxes-manufacturer/');

$fallback_products_url = home_url('/products/');
$contact_url = home_url('/contact/');
$quick_quote_status = isset($_GET['quote_status']) ? sanitize_key(wp_unslash($_GET['quote_status'])) : '';
$quick_quote_messages = array(
    'success'      => 'Thank you. Your details were sent successfully. Sales will follow up about your packaging project.',
    'failed'       => 'We could not send your request right now. Please try again or email sales.vpn@hopgiayvpn.com.',
    'missing'      => 'Please enter your name and a valid email address.',
    'invalid'      => 'Your form session expired. Please refresh the page and try again.',
    'spam'         => 'Your request could not be verified. Please refresh the page and try again.',
    'rate_limited' => 'Too many requests were sent. Please wait a few minutes and try again.',
);

$buyer_groups = array(
    'Cosmetic brands',
    'Gift brands',
    'Food & bakery businesses',
    'Retail stores',
    'Distributors',
    'Wholesalers',
    'Importers',
    'Packaging agencies',
    'Ecommerce brands',
);

$materials = array(
    array('Ivory Board', 'A smooth white paperboard for cosmetics, retail boxes, medicine packaging, and products that need clean full-color printing at a practical cost.'),
    array('Kraft Paper', 'A brown or white paper direction for sleeves, bags and selected retail or gift packaging. Confirm the grade, print appearance, barrier and any food-contact requirements for the intended use.'),
    array('Art Paper', 'Often used as wrapping paper for rigid boxes when brands need refined color, texture, lamination, foil, or embossing on premium packaging.'),
    array('Duplex Board', 'A cost-conscious paperboard for larger folding cartons and consumer packaging where structure and print value need to stay balanced.'),
    array('Greyboard', 'The core material for rigid boxes, drawer boxes, magnetic boxes, and gift sets that require strong structure and premium presentation.'),
    array('Corrugated Paperboard', 'A stronger option for mailer boxes, ecommerce packaging, export cartons, and projects that need more protection during shipping.'),
    array('Recycled Paper Options', 'Discuss the available grade, recycled-content documentation, print appearance and stiffness for the chosen structure. Any certification or environmental claim must match the selected material and supporting documents.'),
);

$finishes = array(
    'Offset printing',
    'Digital printing',
    'Pantone color support',
    'Matte lamination',
    'Gloss lamination',
    'Soft-touch lamination',
    'Foil stamping',
    'Embossing',
    'Debossing',
    'Spot UV',
    'Die-cutting',
    'Custom inserts',
);

$capabilities = array(
    'Direct production support from Vietnam',
    'Sample before mass production',
    'Artwork and dieline checking',
    'Material and structure consultation',
    'Quality checks at each production stage',
    'Carton or shrink-wrapped-bundle packing',
    'International B2B enquiries with destination-specific packing and freight discussion',
);

$process_steps = array(
    array('01', 'Share Project Details', 'Send product type, size, quantity, artwork status, and delivery country.'),
    array('02', 'Confirm Material & Structure', 'Choose paper material, box style, insert needs, printing, and finishing direction.'),
    array('03', 'Review Dieline & Artwork', 'Check dieline, logo placement, bleed, color notes, and production details.'),
    array('04', 'Review the Custom Sample', 'Sampling takes 7–15 days depending on the order. Review fit, opening, artwork and finish before approval.'),
    array('05', 'Approve Bulk Production', 'Production takes 7–45 days after sample approval, depending on quantity, structure and finishing.'),
    array('06', 'Check Quality & Pack', 'Check quality at each production stage and pack in cartons or shrink-wrapped bundles as agreed.'),
    array('07', 'Coordinate Delivery', 'Support shipment discussion for importers, distributors, agencies, and B2B buyers.'),
);

$problems = array(
    array('Unclear material selection', 'We compare ivory board, kraft, corrugated board, greyboard, and recycled options around product weight, print effect, and cost.'),
    array('Wrong box structure', 'Our team helps match folding cartons, rigid boxes, inserts, sleeves, or mailers to your product dimensions and selling channel.'),
    array('Color difference after mass production', 'Artwork checking, Pantone notes, printing method discussion, and sample review reduce avoidable color surprises.'),
    array('Weak export packing', 'Carton packing, product arrangement, and box strength are reviewed before international delivery coordination.'),
    array('Slow supplier communication', 'Factory-side communication helps buyers clarify samples, dielines, material choices, and order details with fewer handoffs.'),
    array('Quotations that are hard to compare', 'State the same dimensions, material, quantity, finishing and freight scope in each brief so the quoted requirements can be compared.'),
);

$comparison_rows = vpn_packaging_supplier_comparison();

$projects = array(
    array('Rigid gift boxes', 'Compare magnetic, lid-and-base and drawer structures for gift presentation. Review board thickness, wrapping and inserts with the loaded product.', '/products/rigid-boxes/'),
    array('Cosmetic folding cartons', 'Start with product size, retail label information and surface finish. Confirm carton creases, artwork placement and the assembly method.', '/products/cosmetic-paper-boxes/'),
    array('Drawer packaging with inserts', 'Plan tray movement, sleeve clearance and the arrangement of bottles, watches or gift sets. Review removal space as well as cavity fit.', '/products/drawer-boxes/'),
    array('Corrugated mailers', 'Share product weight, protective inserts and the intended shipping route. The mailer structure and outer packing are selected for the order.', '/products/corrugated-mailer-boxes/'),
    array('Printed paper bags', 'Coordinate bag dimensions, handles and print with the boxed product. Review the paper and handle construction during sampling.', '/products/paper-bags-with-logo/'),
);

$faqs = vpn_packaging_supplier_faqs();
?>

<main class="vpn-packaging-money-page">
    <section class="vpn-packaging-hero">
        <div class="container vpn-packaging-hero-grid">
            <div class="vpn-packaging-hero-copy">
                <nav class="vpn-supplier-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">/</span><span aria-current="page">Custom packaging manufacturer</span></nav>
                <span class="vpn-packaging-eyebrow">VPN Paper Box Manufacturer</span>
                <h1>Custom Packaging Boxes Manufacturer in Vietnam</h1>
                <p>VPN is a custom packaging boxes manufacturer and supplier in Vietnam. We develop paper boxes, rigid boxes, folding cartons, drawer packaging and paper bags around your product, artwork and order requirements.</p>
                <p class="vpn-supplier-hero-note">Comparing packaging suppliers? Start with the box structure, sample process and quotation scope. General MOQ: <strong>1,000 units.</strong></p>
                <div class="vpn-packaging-actions">
                    <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Request a Factory Quote</a>
                    <a class="btn-outline" href="#packaging-categories">View Packaging Categories</a>
                </div>
            </div>
            <aside class="vpn-packaging-quick-card" id="hero-quick-quote" aria-labelledby="hero-quick-quote-title">
                <div class="vpn-packaging-quick-card-head">
                    <h2 id="hero-quick-quote-title">Get a Quick Quote</h2>
                    <p>Leave your email and project details so sales can follow up with packaging options and a quotation.</p>
                </div>

                <div
                    class="vpn-packaging-quick-message<?php echo $quick_quote_status && isset($quick_quote_messages[$quick_quote_status]) ? ' vpn-packaging-quick-message-' . esc_attr($quick_quote_status) : ''; ?>"
                    role="status"
                    aria-live="polite"
                    <?php echo $quick_quote_status && isset($quick_quote_messages[$quick_quote_status]) ? '' : 'hidden'; ?>
                ><?php echo $quick_quote_status && isset($quick_quote_messages[$quick_quote_status]) ? esc_html($quick_quote_messages[$quick_quote_status]) : ''; ?></div>

                <form class="vpn-packaging-quick-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="custom_box_quote_form">
                    <input type="hidden" name="quote_source" value="packaging_landing_quick_form">
                    <input type="hidden" name="form_location" value="hero">
                    <input type="hidden" name="form_anchor" value="hero-quick-quote">
                    <input type="hidden" name="product_name" value="Custom packaging quick inquiry">
                    <input type="hidden" name="current_page_url" value="<?php echo esc_url($landing_url); ?>">
                    <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); ?>
                    <?php custom_box_quote_form_anti_spam_fields('quote'); ?>

                    <div class="vpn-packaging-quick-fields">
                        <label>
                            <span>Your name</span>
                            <input type="text" name="full_name" autocomplete="name" placeholder="Full name" required>
                        </label>
                        <label>
                            <span>Email address</span>
                            <input type="email" name="email" autocomplete="email" placeholder="name@company.com" required>
                        </label>
                        <label>
                            <span>Packaging type</span>
                            <select name="stock_option" required>
                                <option value="" selected disabled>Select type</option>
                                <option value="Folding carton">Folding carton</option>
                                <option value="Rigid box">Rigid box</option>
                                <option value="Corrugated box">Corrugated box</option>
                                <option value="Paper bag">Paper bag</option>
                                <option value="Paper tube">Paper tube</option>
                                <option value="Other / Not sure">Other / Not sure</option>
                            </select>
                        </label>
                        <label>
                            <span>Quantity</span>
                            <input type="text" name="quantity" inputmode="numeric" placeholder="e.g. 1,000 pcs" required>
                        </label>
                        <label>
                            <span>Delivery country</span>
                            <select name="country" autocomplete="country-name" required>
                                <option value="" selected disabled>Select country</option>
                                <option value="United States">United States</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="Australia">Australia</option>
                                <option value="Canada">Canada</option>
                                <option value="Germany">Germany</option>
                                <option value="France">France</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="India">India</option>
                                <option value="Japan">Japan</option>
                                <option value="South Korea">South Korea</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Other">Other</option>
                            </select>
                        </label>
                        <label>
                            <span>Phone / WhatsApp <small>Optional</small></span>
                            <input type="tel" name="phone" autocomplete="tel" placeholder="+84 123 456 789">
                        </label>
                    </div>

                    <?php custom_box_quote_form_recaptcha_fields(); ?>
                    <button type="submit">
                        Get My Quote
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                    <p class="vpn-packaging-quick-foot">
                        <span>Free quotation</span>
                        <i aria-hidden="true">•</i>
                        <span>No obligation</span>
                    </p>
                </form>
                <script>
                    (function() {
                        var form = document.querySelector('.vpn-packaging-quick-form');
                        if (!form || form.dataset.quickQuoteReady) {
                            return;
                        }

                        form.dataset.quickQuoteReady = '1';
                        var iframeName = 'vpn_packaging_quick_quote_' + Date.now();
                        var iframe = document.createElement('iframe');
                        var submitted = false;
                        var button = form.querySelector('button[type="submit"]');
                        var message = form.parentNode.querySelector('.vpn-packaging-quick-message');

                        iframe.name = iframeName;
                        iframe.hidden = true;
                        iframe.style.display = 'none';
                        iframe.title = 'Quick quote submission';
                        form.parentNode.appendChild(iframe);

                        form.addEventListener('submit', function(event) {
                            event.preventDefault();
                            form.target = iframeName;
                            submitted = true;

                            if (message) {
                                message.hidden = false;
                                message.className = 'vpn-packaging-quick-message vpn-packaging-quick-message-pending';
                                message.textContent = 'Sending your request...';
                            }
                            if (button) {
                                button.disabled = true;
                            }

                            HTMLFormElement.prototype.submit.call(form);
                        });

                        iframe.addEventListener('load', function() {
                            var status = '';
                            if (!submitted) {
                                return;
                            }

                            try {
                                status = new URL(iframe.contentWindow.location.href).searchParams.get('quote_status') || '';
                            } catch (error) {
                                status = '';
                            }

                            if (
                                'success' === status &&
                                !window.__vpnGoogleAdsQuoteConversionSent &&
                                typeof window.gtag === 'function'
                            ) {
                                window.gtag('event', 'conversion', {
                                    send_to: 'AW-18190091085/6FzwCNKm0NscEM2G2-FD'
                                });

                                window.__vpnGoogleAdsQuoteConversionSent = true;
                            }

                            if (message) {
                                message.hidden = false;
                                message.className = 'vpn-packaging-quick-message vpn-packaging-quick-message-' + (status || 'failed');
                                message.textContent = 'success' === status
                                    ? 'Thank you. Your quote request has been sent successfully.'
                                    : ('captcha' === status
                                        ? 'Security verification could not be completed. Please reload the page and try again.'
                                        : 'Sorry, we could not send your request right now. Please try again later.');
                            }

                            submitted = false;
                            if (button) {
                                button.disabled = false;
                            }
                        });
                    })();
                </script>
            </aside>
            <figure class="vpn-packaging-hero-media">
                <a href="<?php echo esc_url($quote_url); ?>" aria-label="<?php esc_attr_e('Request a factory quote from VPN Paper Box', 'custom-box-theme'); ?>">
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/banner-landing-page.webp'); ?>" alt="Custom packaging boxes produced by VPN Paper Box in Vietnam" width="1774" height="887" decoding="async" fetchpriority="high">
                </a>
            </figure>
            <div class="vpn-packaging-proof" aria-label="Factory proof points">
                <span>MOQ 1,000 Units</span>
                <span>OEM/ODM Packaging Production</span>
                <span>Factory-Direct Support from Vietnam</span>
                <span>Custom Printing &amp; Finishing</span>
            </div>
        </div>
    </section>

    <nav class="vpn-supplier-jump" aria-label="Packaging supplier information">
        <div class="container"><a href="#supplier-order-facts">Order essentials</a><a href="#packaging-categories">Packaging range</a><a href="#supplier-comparison">Choosing a supplier</a><a href="#supplier-business">About VPN</a><a href="#supplier-faq">FAQ</a><a href="#factory-quote">Request a quote ↗</a></div>
    </nav>

    <section class="vpn-packaging-section vpn-supplier-essentials" id="supplier-order-facts" aria-labelledby="supplier-essentials-title">
        <div class="container">
            <div class="vpn-packaging-section-head"><span class="vpn-packaging-eyebrow">Custom paper packaging from Vietnam</span><h2 id="supplier-essentials-title">What to know before you request a quote</h2><p>VPN supplies made-to-order packaging to brands, importers, distributors and agencies. Confirm the product fit, design and order scope together before approving production.</p></div>
            <dl class="vpn-supplier-facts">
                <div><dt>General minimum order</dt><dd><strong>1,000 units</strong><p>Send the quantity and packaging type for your project quotation.</p></dd></div>
                <div><dt>Custom sample</dt><dd><strong>7–15 days</strong><p>Made to your design requirements; fee and timing depend on the order.</p></dd></div>
                <div><dt>Bulk production</dt><dd><strong>7–45 days</strong><p>After sample approval, depending on the order. Transit time is separate.</p></dd></div>
                <div><dt>Design &amp; quality</dt><dd><strong>Support at each stage</strong><p>Design support is available. Quality is checked at each production stage.</p></dd></div>
            </dl>
            <p class="vpn-supplier-refund"><strong>Sample-fee refund:</strong> orders <strong>over 5,000 pcs</strong> qualify for a refund of <strong>50% of the sample fee</strong>. Sample charges depend on the requested design.</p>
            <p class="vpn-supplier-order-note">Packing: cartons or shrink-wrapped bundles according to the order requirements. Share the destination and target date so production and freight can be discussed separately.</p>
        </div>
    </section>

    <section class="vpn-packaging-section vpn-packaging-buyers">
        <div class="container vpn-packaging-split">
            <div>
                <span class="vpn-packaging-eyebrow">Who We Support</span>
                <h2>Custom Packaging Solutions for B2B Buyers</h2>
                <p>VPN Paper Box works with brands and trade buyers that need reliable custom paper packaging, practical production advice, and export-ready packing from Vietnam.</p>
            </div>
            <div class="vpn-packaging-buyer-grid">
                <?php foreach ($buyer_groups as $buyer_group) : ?>
                    <span><?php echo esc_html($buyer_group); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/home/packaging-category-groups'); ?>

    <section class="vpn-packaging-section vpn-packaging-muted">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">Material Consultation</span>
                <h2>Paper Materials for Custom Packaging Boxes</h2>
                <p>The right material depends on your product weight, box structure, print effect, brand positioning, budget, and shipping route.</p>
            </div>
            <div class="vpn-packaging-material-grid">
                <?php foreach ($materials as $material) : ?>
                    <article>
                        <h3><?php echo esc_html($material[0]); ?></h3>
                        <p><?php echo esc_html($material[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vpn-packaging-section">
        <div class="container vpn-packaging-split vpn-packaging-finishing">
            <div>
                <span class="vpn-packaging-eyebrow">Printing &amp; Finish</span>
                <h2>Custom Printing and Finishing Options</h2>
                <p>From clean retail cartons to premium rigid gift boxes, VPN Paper Box helps buyers choose production details that match the brand style and product use.</p>
                <div class="vpn-packaging-tags">
                    <?php foreach ($finishes as $finish) : ?>
                        <span><?php echo esc_html($finish); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <figure>
                <img src="<?php echo esc_url(content_url('/uploads/2026/05/tao_nhieu_goc_202604181627-Copy-2-768x768.png')); ?>" alt="Rigid paper box sample with custom logo finishing" loading="lazy" decoding="async">
            </figure>
        </div>
    </section>

    <section class="vpn-packaging-section vpn-packaging-factory">
        <div class="container vpn-packaging-split">
            <div>
                <span class="vpn-packaging-eyebrow">Factory Capability</span>
                <h2>Factory-Direct Custom Packaging Production in Vietnam</h2>
                <p>Work directly with a Vietnam packaging manufacturer for clearer material choices, sample discussion, artwork review, quality checking, and export-ready carton packing.</p>
                <ul class="vpn-packaging-check-list">
                    <?php foreach ($capabilities as $capability) : ?>
                        <li><i class="fas fa-check-circle"></i><?php echo esc_html($capability); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="vpn-packaging-factory-gallery vpn-factory-overview">
                <?php foreach (array('entrance', 'workshop') as $factory_image_key) : ?>
                    <?php $factory_image = vpn_packaging_supplier_factory_image($factory_image_key); ?>
                    <?php if ($factory_image) : ?>
                        <figure>
                            <?php echo $factory_image['html']; ?>
                            <figcaption><?php echo esc_html($factory_image['caption']); ?></figcaption>
                        </figure>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vpn-packaging-section">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">Order Workflow</span>
                <h2>How to Start a Custom Packaging Order</h2>
                <p>A clear process helps international B2B buyers control structure, cost, sample approval, bulk production, and export packing.</p>
            </div>
            <ol class="vpn-packaging-process">
                <?php foreach ($process_steps as $step) : ?>
                    <li>
                        <span><?php echo esc_html($step[0]); ?></span>
                        <h3><?php echo esc_html($step[1]); ?></h3>
                        <p><?php echo esc_html($step[2]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="vpn-packaging-section vpn-packaging-muted">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">Buyer Risk Reduction</span>
                <h2>Common Problems We Help B2B Buyers Avoid</h2>
            </div>
            <div class="vpn-packaging-problem-grid">
                <?php foreach ($problems as $problem) : ?>
                    <article>
                        <h3><?php echo esc_html($problem[0]); ?></h3>
                        <p><?php echo esc_html($problem[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vpn-packaging-section" id="supplier-comparison" aria-labelledby="supplier-comparison-title">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">A practical buying checklist</span>
                <h2 id="supplier-comparison-title">What to compare when choosing packaging suppliers</h2>
                <p>A manufacturer produces packaging; a supplier provides it and may manufacture, source or distribute it. VPN manufactures and supplies custom paper packaging in Vietnam. Compare the actual specification, approval process and included charges when assessing your options.</p>
            </div>
            <div class="vpn-packaging-table-wrap">
                <table class="vpn-packaging-table">
                    <caption>Use the same product brief and quantity when comparing packaging quotations.</caption>
                    <thead>
                        <tr>
                            <th scope="col">Decision</th>
                            <th scope="col">What to ask or share</th>
                            <th scope="col">What to confirm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comparison_rows as $row) : ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($row[0]); ?></th>
                                <td><?php echo esc_html($row[1]); ?></td>
                                <td><?php echo esc_html($row[2]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="vpn-supplier-resources"><a href="<?php echo esc_url(home_url('/custom-packaging-supplier-audit-checklist/')); ?>">Packaging supplier audit checklist ↗</a><a href="<?php echo esc_url(home_url('/paper-box-quality-control-checklist/')); ?>">Paper box quality checks ↗</a><a href="<?php echo esc_url(home_url('/how-to-prepare-artwork-for-printed-paper-boxes/')); ?>">Prepare packaging artwork ↗</a></div>
        </div>
    </section>

    <section class="vpn-packaging-section vpn-packaging-quote" id="factory-quote">
        <?php get_template_part('template-parts/home/quote-form', null, array('product_value'=>'Custom paper packaging enquiry','quote_source'=>'packaging_supplier_landing','form_location'=>'manufacturer-page','current_page_url'=>$landing_url)); ?>
    </section>

    <section class="vpn-packaging-section">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">Use Cases</span>
                <h2>Find the packaging structure for your product</h2>
                <p>These are starting directions for a custom design. Choose a structure to explore, then confirm dimensions, material, insert and finish for your order.</p>
            </div>
            <div class="vpn-packaging-project-grid">
                <?php foreach ($projects as $project) : ?>
                    <article>
                        <h3><?php echo esc_html($project[0]); ?></h3>
                        <p><?php echo esc_html($project[1]); ?></p>
                        <a href="<?php echo esc_url(home_url($project[2])); ?>">Explore <?php echo esc_html(strtolower($project[0])); ?> ↗</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vpn-packaging-section vpn-packaging-muted" id="supplier-faq" aria-labelledby="supplier-faq-title">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">FAQ</span>
                <h2 id="supplier-faq-title">Custom packaging manufacturer &amp; supplier FAQ</h2>
            </div>
            <div class="vpn-packaging-faq-grid">
                <?php foreach ($faqs as $faq) : ?>
                    <details>
                        <summary><?php echo esc_html($faq[0]); ?></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php vpn_packaging_supplier_factory_gallery(); ?>

    <section class="vpn-packaging-section vpn-supplier-business" id="supplier-business" aria-labelledby="supplier-business-title">
        <div class="container vpn-packaging-split">
            <div><span class="vpn-packaging-eyebrow">Who you are contacting</span><h2 id="supplier-business-title">VPN Paper Box in Vietnam</h2><p>Custom paper packaging manufacturing and supply for B2B projects. Discuss your product, sample requirements and order with the VPN team.</p><p>Order information updated <time datetime="2026-10-15">15 October 2026</time>. Your final specification, fees, production schedule and freight scope are confirmed in the project quotation.</p><a href="<?php echo esc_url(home_url('/about/')); ?>">About VPN and our production capabilities ↗</a></div>
            <div class="vpn-supplier-contact"><dl><div><dt>Company</dt><dd>VPN Paper Box · Công ty TNHH Quảng Cáo VPN</dd></div><div><dt>Manufacturing location</dt><dd>1032 An Phu Tay, Hamlet 4, Hung Long Commune, Binh Chanh District, Ho Chi Minh City, Vietnam</dd></div><div><dt>Email</dt><dd><a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a></dd></div><div><dt>Phone</dt><dd><a href="tel:+84933102653">+84 933 102 653</a></dd></div></dl><a href="<?php echo esc_url($contact_url); ?>">Contact VPN for your packaging project ↗</a></div>
        </div>
    </section>

    <section class="vpn-packaging-final-cta">
        <div class="container vpn-packaging-final-inner">
            <div>
                <h2>Build Custom Packaging with a Vietnam Factory Team</h2>
                <p>Leave your email with the product type, quantity and destination. Add dimensions, artwork or a reference if available so sales can discuss the packaging and sample options for your project.</p>
            </div>
            <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Request a Factory Quote</a>
        </div>
    </section>

</main>

<?php get_footer(); ?>
