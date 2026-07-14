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
    'success'      => 'Thank you. Your details were sent successfully. We will contact you within 24 hours.',
    'failed'       => 'We could not send your request right now. Please try again or email sales.vpn@hopgiayvpn.com.',
    'missing'      => 'Please enter your name and a valid business email.',
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
    array('Kraft Paper', 'A natural-looking paper option for eco-positioned brands, bakery packaging, takeaway boxes, and simple retail packs with a warm handmade feel.'),
    array('Art Paper', 'Often used as wrapping paper for rigid boxes when brands need refined color, texture, lamination, foil, or embossing on premium packaging.'),
    array('Duplex Board', 'A cost-conscious paperboard for larger folding cartons and consumer packaging where structure and print value need to stay balanced.'),
    array('Greyboard', 'The core material for rigid boxes, drawer boxes, magnetic boxes, and gift sets that require strong structure and premium presentation.'),
    array('Corrugated Paperboard', 'A stronger option for mailer boxes, ecommerce packaging, export cartons, and projects that need more protection during shipping.'),
    array('Recycled Paper Options', 'Useful for brands that want a more sustainable packaging direction while still checking print quality, stiffness, and product safety requirements.'),
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
    'Quality inspection before packing',
    'Export-ready carton packing',
    'Support for USA, UK, India, Europe, and global B2B buyers',
);

$process_steps = array(
    array('01', 'Share Project Details', 'Send product type, size, quantity, artwork status, and delivery country.'),
    array('02', 'Confirm Material & Structure', 'Choose paper material, box style, insert needs, printing, and finishing direction.'),
    array('03', 'Review Dieline & Artwork', 'Check dieline, logo placement, bleed, color notes, and production details.'),
    array('04', 'Make Sample if Needed', 'Approve structure, material, color direction, and finishing before bulk production.'),
    array('05', 'Start Bulk Production', 'Move into printing, mounting, die-cutting, finishing, assembly, and scheduling.'),
    array('06', 'Inspect & Pack', 'Check finished packaging, count quantities, and prepare export-ready cartons.'),
    array('07', 'Coordinate Delivery', 'Support shipment discussion for importers, distributors, agencies, and B2B buyers.'),
);

$problems = array(
    array('Unclear material selection', 'We compare ivory board, kraft, corrugated board, greyboard, and recycled options around product weight, print effect, and cost.'),
    array('Wrong box structure', 'Our team helps match folding cartons, rigid boxes, inserts, sleeves, or mailers to your product dimensions and selling channel.'),
    array('Color difference after mass production', 'Artwork checking, Pantone notes, printing method discussion, and sample review reduce avoidable color surprises.'),
    array('Weak export packing', 'Carton packing, product arrangement, and box strength are reviewed before international delivery coordination.'),
    array('Slow supplier communication', 'Factory-side communication helps buyers clarify samples, dielines, material choices, and order details with fewer handoffs.'),
    array('Trader price markup', 'Factory-direct discussion keeps quotation, technical advice, sampling, and repeat order control closer to production.'),
);

$comparison_rows = array(
    array('Pricing', 'Factory-direct quotation based on structure, material, finishing, and quantity.', 'Extra margin can be added before the project reaches production.'),
    array('Technical advice', 'Direct discussion about dieline, paperboard, inserts, printing, and finishing.', 'Advice may be delayed or simplified through several handoffs.'),
    array('Sampling', 'Sample details can be coordinated with the production team before bulk orders.', 'Sampling often depends on another factory behind the supplier.'),
    array('Quality control', 'Factory-side checking before packing helps maintain order consistency.', 'Quality feedback may arrive late after goods are already produced.'),
    array('Customization', 'Custom size, structure, paper, printing, finishing, and inserts can be discussed together.', 'Customization is limited by what the middleman can source.'),
    array('Repeat orders', 'Production notes and specifications can be kept for consistent reorder support.', 'Repeat order consistency can vary if production source changes.'),
);

$projects = array(
    array('Wine gift box packaging project', 'Rigid greyboard, printed art paper wrapping, foil logo, and insert support for premium bottle presentation.'),
    array('Cosmetic packaging project', 'Ivory board folding cartons with full-color offset printing, matte lamination, and spot UV details.'),
    array('Food and bakery packaging project', 'Kraft and SBS paper packaging options for bakery products, takeaway presentation, and seasonal gift sets.'),
    array('Retail paper bag and box set', 'Matching paper bags and custom printed boxes for retail stores, promotional campaigns, and brand launches.'),
);

$faqs = array(
    array('What types of custom packaging boxes can VPN Paper Box produce?', 'VPN Paper Box can support custom paper boxes, rigid boxes, folding cartons, cosmetic boxes, food boxes, gift boxes, paper bags, inserts, sleeves, and printed paper packaging for B2B projects.'),
    array('Can you print our logo and brand colors on the packaging?', 'Yes. We can support custom logo printing, CMYK artwork, Pantone color notes, foil stamping, embossing, debossing, spot UV, matte lamination, gloss lamination, and soft-touch effects.'),
    array('What materials are available for custom paper boxes?', 'Common material options include ivory board, kraft paper, art paper, duplex board, greyboard, corrugated paperboard, and recycled paper options depending on the structure and product use.'),
    array('Do you support OEM/ODM packaging production?', 'Yes. VPN Paper Box supports OEM and ODM packaging projects including custom size, structure, material selection, dieline review, printing, finishing, sampling, and bulk production.'),
    array('Can you make samples before bulk production?', 'Yes. Sample support is available when buyers need to confirm structure, size, paper material, color direction, finishing, and inserts before mass production.'),
    array('What information should I send to get a quotation?', 'Please send product type, box size, quantity, material preference, printing and finishing requirements, artwork if available, and delivery country. Photos or reference styles are also helpful.'),
    array('Do you support international B2B orders?', 'Yes. VPN Paper Box supports B2B buyers, importers, distributors, ecommerce brands, and agencies from the USA, UK, India, Europe, and other global markets.'),
    array('What is the difference between rigid boxes and folding cartons?', 'Rigid boxes use thick greyboard and are usually chosen for premium gift or luxury packaging. Folding cartons are lighter, foldable, and often used for cosmetics, food, healthcare, and retail products.'),
);
?>

<main class="vpn-packaging-money-page">
    <section class="vpn-packaging-hero">
        <div class="container vpn-packaging-hero-grid">
            <div class="vpn-packaging-hero-copy">
                <span class="vpn-packaging-eyebrow">VPN Paper Box Manufacturer</span>
                <h1>Custom Packaging Boxes Manufacturer in Vietnam</h1>
                <p>VPN Paper Box Manufacturer produces custom paper boxes, rigid boxes, folding cartons, paper bags and printed packaging for B2B brands, importers, distributors and export buyers.</p>
                <div class="vpn-packaging-actions">
                    <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Request a Factory Quote</a>
                    <a class="btn-outline" href="#packaging-categories">View Packaging Categories</a>
                </div>
            </div>
            <aside class="vpn-packaging-quick-card" id="hero-quick-quote" aria-labelledby="hero-quick-quote-title">
                <div class="vpn-packaging-quick-card-head">
                    <h2 id="hero-quick-quote-title">Get a Quick Quote</h2>
                    <p>Tell us what you need — we'll reply within 24 hours.</p>
                </div>

                <?php if ($quick_quote_status && isset($quick_quote_messages[$quick_quote_status])) : ?>
                    <div class="vpn-packaging-quick-message vpn-packaging-quick-message-<?php echo esc_attr($quick_quote_status); ?>" role="status">
                        <?php echo esc_html($quick_quote_messages[$quick_quote_status]); ?>
                    </div>
                <?php endif; ?>

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
                            <span>Work email</span>
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

                            if (message) {
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
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/banner-landing-page.webp'); ?>" alt="Custom packaging boxes produced by VPN Paper Box in Vietnam" decoding="async" fetchpriority="high">
                </a>
            </figure>
            <div class="vpn-packaging-proof" aria-label="Factory proof points">
                <span>9+ Years Manufacturing Experience</span>
                <span>OEM/ODM Packaging Production</span>
                <span>Factory-Direct Support from Vietnam</span>
                <span>Custom Printing &amp; Finishing</span>
            </div>
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
            <div class="vpn-packaging-factory-gallery">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/factory-team-and-production.webp'); ?>" alt="VPN Packaging factory production team" loading="lazy" decoding="async">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-1.webp'); ?>" alt="Paper packaging production area in Vietnam" loading="lazy" decoding="async">
                <img src="<?php echo esc_url($theme_uri . '/assets/images/anh-nha-may-fly.webp'); ?>" alt="Aerial view of VPN Paper Box factory" loading="lazy" decoding="async">
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

    <section class="vpn-packaging-section">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">Factory vs Middleman</span>
                <h2>Why Work Directly with VPN Paper Box Manufacturer?</h2>
            </div>
            <div class="vpn-packaging-table-wrap">
                <table class="vpn-packaging-table">
                    <thead>
                        <tr>
                            <th>Factor</th>
                            <th>VPN Paper Box Manufacturer</th>
                            <th>Trader / Middleman</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comparison_rows as $row) : ?>
                            <tr>
                                <th><?php echo esc_html($row[0]); ?></th>
                                <td><?php echo esc_html($row[1]); ?></td>
                                <td><?php echo esc_html($row[2]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="vpn-packaging-section vpn-packaging-quote" id="factory-quote">
        <?php get_template_part('template-parts/home/quote-form'); ?>
    </section>

    <section class="vpn-packaging-section">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">Use Cases</span>
                <h2>Packaging Solutions by Use Case</h2>
                <p>These examples show common project directions without overstating customer claims.</p>
            </div>
            <div class="vpn-packaging-project-grid">
                <?php foreach ($projects as $project) : ?>
                    <article>
                        <h3><?php echo esc_html($project[0]); ?></h3>
                        <p><?php echo esc_html($project[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vpn-packaging-section vpn-packaging-muted">
        <div class="container">
            <div class="vpn-packaging-section-head">
                <span class="vpn-packaging-eyebrow">FAQ</span>
                <h2>Custom Packaging Boxes FAQ</h2>
            </div>
            <div class="vpn-packaging-faq-grid">
                <?php foreach ($faqs as $faq) : ?>
                    <article>
                        <h3><?php echo esc_html($faq[0]); ?></h3>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vpn-packaging-final-cta">
        <div class="container vpn-packaging-final-inner">
            <div>
                <h2>Build Custom Packaging with a Vietnam Factory Team</h2>
                <p>Share your product type, box size, quantity, artwork, and delivery country. VPN Paper Box will help you define a production-ready packaging solution.</p>
            </div>
            <a class="btn-primary" href="<?php echo esc_url($quote_url); ?>">Request a Factory Quote</a>
        </div>
    </section>

    <script type="application/ld+json">
        <?php
        $faq_schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(function ($faq) {
                return array(
                    '@type'          => 'Question',
                    'name'           => $faq[0],
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => $faq[1],
                    ),
                );
            }, $faqs),
        );

        echo wp_json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        ?>
    </script>
</main>

<?php get_footer(); ?>
