<?php
/** Template Name: Global Custom Paper Bags Manufacturer Landing Page */
defined('ABSPATH') || exit;
$products = custom_box_paper_bag_products();
$delivery_countries = function_exists('WC') && WC()->countries ? WC()->countries->get_countries() : array();
$featured_products = array_slice($products, 0, 12);
$christmas_products = custom_box_paper_bag_christmas_products($products, $featured_products);
$quote_products = array_merge($featured_products, $christmas_products);
$christmas_category = get_term_by('slug', 'christmas-packaging', 'product_cat');
$christmas_url = $christmas_category ? get_term_link($christmas_category) : home_url('/products/paper-bags-with-logo/');
if (is_wp_error($christmas_url)) { $christmas_url = home_url('/products/paper-bags-with-logo/'); }
$paper_bag_category = get_term_by('slug', 'paper-bags-with-logo', 'product_cat');
$category_url = $paper_bag_category ? get_term_link($paper_bag_category) : home_url('/products/paper-bags-with-logo/');
if (is_wp_error($category_url)) { $category_url = home_url('/products/paper-bags-with-logo/'); }
$status = isset($_GET['quote_status']) ? sanitize_key(wp_unslash($_GET['quote_status'])) : '';
$messages = custom_box_paper_bag_status_messages();
$privacy = get_privacy_policy_url() ?: home_url('/privacy-policy/');
get_header();
?>
<main class="cpb-page" id="main-content">
    <section class="cpb-hero" aria-labelledby="cpb-title">
        <div class="cpb-shell cpb-hero-grid">
            <div class="cpb-hero-copy">
                <p class="cpb-eyebrow">Made in Vietnam · For brands worldwide</p>
                <h1 id="cpb-title">Custom paper bag<br><span>manufacturer in Vietnam.</span></h1>
                <p class="cpb-hero-lead">We manufacture custom printed paper bags for importers, distributors, retail chains and brands. Order wholesale bags with your logo, custom sizes and handles, with design support for your product.</p>
                <div class="cpb-hero-facts"><span><strong>1,000 pcs</strong> general MOQ</span><span><strong>Custom design</strong> support available</span></div>
                <div class="cpb-hero-showcase" aria-label="Custom paper bag style examples">
                    <?php foreach (custom_box_paper_bag_hero_products() as $index => $product) : ?>
                    <a href="<?php echo esc_url($product->get_permalink()); ?>" aria-label="<?php echo esc_attr(get_the_title($product->get_id())); ?>"><?php echo custom_box_paper_bag_product_image($product, true, 0 === $index); ?><span>View bag <span aria-hidden="true">→</span></span></a>
                    <?php endforeach; ?>
                </div>
                <p class="cpb-reference-note">From our paper bag collection. Size, artwork and finishing can be customized.</p>
                <a class="cpb-text-link" href="#styles">Explore bag styles <span aria-hidden="true">↓</span></a>
            </div>
            <aside class="cpb-quote-card" id="quote" aria-labelledby="cpb-quote-title">
                <p class="cpb-eyebrow">Start with your project</p>
                <h2 id="cpb-quote-title">Your paper bag quote</h2>
                <p class="cpb-quote-intro">Leave your email and a few details. We will review the options and reply with the next steps.</p>
                <div class="quote-form-message cpb-form-message" id="cpb-form-status" role="status" aria-live="polite" tabindex="-1" <?php echo isset($messages[$status]) ? 'data-status="' . esc_attr($status) . '"' : 'hidden'; ?>><?php echo isset($messages[$status]) ? esc_html($messages[$status]) : ''; ?></div>
                <form id="custom-paper-bags-quote-form" class="cpb-inquiry-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" aria-describedby="cpb-form-privacy">
                    <input type="hidden" name="action" value="custom_box_quote_form">
                    <input type="hidden" name="quote_source" value="custom_paper_bags_manufacturer">
                    <input type="hidden" name="product_type" value="paper_bags">
                    <input type="hidden" name="form_location" value="paper_bags_hero">
                    <input type="hidden" name="form_anchor" value="quote">
                    <input type="hidden" name="response_format" value="">
                    <input type="hidden" name="current_page_url" value="<?php echo esc_url(custom_box_custom_paper_bags_url()); ?>">
                    <?php foreach (array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid', 'referrer_url') as $field) : ?><input type="hidden" name="<?php echo esc_attr($field); ?>" value=""><?php endforeach; ?>
                    <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); custom_box_quote_form_anti_spam_fields('quote'); ?>
                    <label class="cpb-field" for="cpb-email">Email <span aria-hidden="true">*</span><input id="cpb-email" type="email" name="email" required autocomplete="email" maxlength="254" placeholder="Where should we send your quote?"></label>
                    <div class="cpb-form-row">
                        <label class="cpb-field" for="cpb-quantity">Quantity <span aria-hidden="true">*</span><input id="cpb-quantity" type="number" name="quantity" required min="1000" max="100000000" step="1" inputmode="numeric" placeholder="1,000 or more"></label>
                        <label class="cpb-field" for="cpb-country">Country / region <span aria-hidden="true">*</span><?php if ($delivery_countries) : ?><select id="cpb-country" name="country" autocomplete="country-name" required><option value="">Select country</option><?php foreach ($delivery_countries as $country_name) : ?><option value="<?php echo esc_attr($country_name); ?>"><?php echo esc_html($country_name); ?></option><?php endforeach; ?></select><?php else : ?><input id="cpb-country" name="country" autocomplete="country-name" maxlength="120" placeholder="Your delivery country / region" required><?php endif; ?></label>
                    </div>
                    <label class="cpb-field" for="cpb-product">Bag style <span class="cpb-optional">Optional</span><select id="cpb-product" name="product_name"><option value="Custom paper bags">Not sure yet — help me choose</option><?php foreach ($quote_products as $product) : ?><option value="<?php echo esc_attr(get_the_title($product->get_id())); ?>"><?php echo esc_html(get_the_title($product->get_id())); ?></option><?php endforeach; ?></select></label>
                    <details class="cpb-extra-fields"><summary>Add size, artwork or contact details <span>Optional</span></summary>
                        <div class="cpb-extra-fields__inner">
                            <p class="cpb-size-note">Finished size: <strong>width × height × gusset</strong>. The gusset is the side depth. Choose inches, mm or cm.</p>
                            <div class="cpb-dimensions">
                                <?php foreach (array('length' => 'Width', 'width' => 'Height', 'depth' => 'Gusset') as $name => $label) : ?><label class="cpb-field" for="cpb-<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?><input id="cpb-<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" type="number" min="0.01" step="any" inputmode="decimal" placeholder="e.g. 10"></label><?php endforeach; ?>
                                <label class="cpb-field" for="cpb-unit">Unit<select id="cpb-unit" name="unit"><option value="">Select</option><option value="inch">Inches</option><option value="mm">mm</option><option value="cm">cm</option></select></label>
                            </div>
                            <div class="cpb-form-row"><label class="cpb-field" for="cpb-name">Your name<input id="cpb-name" name="full_name" autocomplete="name" maxlength="200"></label><label class="cpb-field" for="cpb-company">Company<input id="cpb-company" name="company" autocomplete="organization" maxlength="200"></label></div>
                            <label class="cpb-field" for="cpb-phone">Phone / WhatsApp<input id="cpb-phone" name="phone" type="tel" autocomplete="tel" maxlength="80" placeholder="Include country code"></label>
                            <label class="cpb-field" for="cpb-artwork">Artwork or reference<input id="cpb-artwork" name="artwork" type="file" accept=".png,.pdf,.jpg,.jpeg,.webp,.doc,.docx,.gif,.psd,.cdr,.eps"><small>PDF, JPG, PNG, WebP, DOC/DOCX, GIF, PSD, CDR or EPS. Max. 10MB.</small></label>
                            <label class="cpb-field" for="cpb-message">What will the bag carry?<textarea id="cpb-message" name="message" rows="3" maxlength="3000" placeholder="Product, packed weight, logo, quantity per design, delivery locations and target date"></textarea></label>
                        </div>
                    </details>
                    <label class="cpb-consent" id="cpb-form-privacy"><input name="privacy_consent" type="checkbox" value="yes" required><span>I agree to the use of my details to review and respond to this quote request. <a href="<?php echo esc_url($privacy); ?>">Privacy policy</a>.</span></label>
                    <?php custom_box_quote_form_recaptcha_fields(); ?>
                    <button class="cpb-button cpb-submit" type="submit">Request my quote <span aria-hidden="true">→</span></button>
                    <p class="cpb-form-foot">Free quotation · No obligation<br>Prefer email? <a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a></p>
                </form>
            </aside>
        </div>
    </section>
    <nav class="cpb-jump" aria-label="Explore paper bags"><div class="cpb-shell"><a href="#styles">Choose a bag</a><?php if ($christmas_products) : ?><a href="#christmas-bags">Christmas bags</a><?php endif; ?><a href="#ordering">MOQ &amp; samples</a><a href="#shipping">International orders</a><a href="#quality">Quality checks</a><a href="#faq">FAQs</a></div></nav>
    <?php get_template_part('template-parts/woocommerce/paper-bag-workshop'); ?>
    <section class="cpb-section" id="styles"><div class="cpb-shell">
        <div class="cpb-section-heading"><div><p class="cpb-eyebrow">Our paper bag collection</p><h2>Explore custom paper bag products</h2></div><p>Browse kraft, shopping, gift and bakery bags from our product catalog. View a product or choose it for your quote.</p></div>
        <div class="cpb-product-grid"><?php foreach ($featured_products as $product) : ?>
            <?php get_template_part('template-parts/woocommerce/paper-bag-landing-card', null, array('product' => $product)); ?>
        <?php endforeach; ?></div>
        <div class="cpb-catalog-link"><p>Need another style? Explore more options in our paper bag category. <?php if (function_exists('vpn_bakery_category_url') && get_term_by('slug', 'custom-bakery-bread-paper-bags', 'product_cat')) : ?>For bread and pastries, compare our <a class="cpb-text-link" href="<?php echo esc_url(vpn_bakery_category_url()); ?>">bakery and bread paper bags</a>.<?php endif; ?></p><a class="cpb-button" href="<?php echo esc_url($category_url); ?>">View all <?php echo esc_html(count($products)); ?> paper bag products →</a></div>
    </div></section>
    <?php if ($christmas_products) : ?>
    <section class="cpb-section cpb-christmas" id="christmas-bags" aria-labelledby="cpb-christmas-title"><div class="cpb-shell">
        <div class="cpb-section-heading"><div><p class="cpb-eyebrow">Seasonal packaging</p><h2 id="cpb-christmas-title">Christmas paper bags for your holiday collection</h2></div><p>Explore festive designs from our Christmas range for bulk holiday gift bag orders. Share your quantity and required delivery date so we can review sampling, production and shipping separately.</p></div>
        <div class="cpb-product-grid"><?php foreach ($christmas_products as $product) : ?>
            <?php get_template_part('template-parts/woocommerce/paper-bag-landing-card', null, array('product' => $product)); ?>
        <?php endforeach; ?></div>
        <div class="cpb-catalog-link"><a class="cpb-button" href="<?php echo esc_url($christmas_url); ?>">Explore Christmas packaging →</a></div>
    </div></section>
    <?php endif; ?>
    <section class="cpb-section cpb-section--soft" id="ordering"><div class="cpb-shell">
        <div class="cpb-section-heading"><div><p class="cpb-eyebrow">Know before you order</p><h2>Order custom paper bags wholesale.</h2></div><p>Planning a large-volume order for your brand, distribution business or retail chain? Send the quantity per size or design. Your quotation is based on the specification, approved sample and order requirements.</p></div>
        <dl class="cpb-order-facts"><div><dt>General minimum order</dt><dd>1,000 <span>pcs</span></dd><dd class="cpb-fact-note">Share your bag style and dimensions to confirm the project specification.</dd></div><div><dt>Custom sampling</dt><dd>7–15 <span>days</span></dd><dd class="cpb-fact-note">Depending on the order. Sample fees depend on the requested design.</dd></div><div><dt>Production after sample approval</dt><dd>7–45 <span>days</span></dd><dd class="cpb-fact-note">Depending on the order. Shipping time is separate.</dd></div></dl>
        <div class="cpb-refund"><strong>50% sample-fee refund</strong><p>Orders <strong>over 5,000 pcs</strong> qualify for a refund of 50% of the sample fee. Design support is available.</p><a class="cpb-text-link" href="#quote">Discuss your project →</a></div>
        <ol class="cpb-steps"><li><span>01</span><h3>Send your brief</h3><p>Start with your email, quantity, destination and bag style.</p></li><li><span>02</span><h3>Review your quote</h3><p>Confirm dimensions, paper, handles, printing, quantity per design and the included charges.</p></li><li><span>03</span><h3>Approve the sample</h3><p>Review the structure, artwork, color direction and finish before production.</p></li><li><span>04</span><h3>Produce &amp; pack</h3><p>Check quality at each stage and confirm packing and shipping arrangements.</p></li></ol>
    </div></section>
    <section class="cpb-section" id="shipping"><div class="cpb-shell cpb-two-column">
        <div><p class="cpb-eyebrow">For international buyers</p><h2>Your paper bag supplier for international orders.</h2><p class="cpb-section-lead">Production is based in Vietnam. Share your quantity, delivery city, postal code and required receiving date. For orders with multiple delivery locations, include each destination so we can review the packing and shipment scope with your quotation.</p><a class="cpb-button" href="#quote" data-track="paper_bag_quote_cta">Get a project-specific quote →</a></div>
        <div class="cpb-info-list"><article><h3>Inches or millimeters</h3><p>Use either unit. State width × height × gusset and the packed product weight; final dimensions are approved with the sample.</p></article><article><h3>Confirm what the quote includes</h3><p>Review sample, printing, finishing, packing and freight charges. Confirm applicable delivery terms, duties and taxes before ordering.</p></article><article><h3>Separate production and transit</h3><p>Production takes 7–45 days after sample approval depending on the order. Transit time depends on the agreed shipment and destination.</p></article><article><h3>Leave an email outside our working hours</h3><p>Our team works Monday–Saturday, 8:00 AM–6:00 PM Vietnam time (UTC+7). Leave your email and project details for follow-up.</p></article></div>
    </div></section>
    <section class="cpb-section cpb-section--ink" id="quality"><div class="cpb-shell">
        <div class="cpb-section-heading"><div><p class="cpb-eyebrow">Paper packaging production in Vietnam</p><h2>Details to approve.<br>Checks at every stage.</h2></div><p>VPN Paper Box is a packaging brand of Công ty TNHH Quảng Cáo VPN. Your approved sample and specifications guide production and quality checks.</p></div>
        <div class="cpb-quality-grid"><div class="cpb-quality-photo">
            <div class="factory-video-frame">
                <button class="factory-video-launch" type="button" data-youtube-video-id="nD0iRaJHgLQ" aria-label="Play the VPN paper packaging factory tour">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/anh-nha-may-2-16x9-100kb.webp'); ?>" alt="" width="1280" height="720" loading="lazy" decoding="async">
                    <span class="factory-video-play" aria-hidden="true"></span><span class="factory-video-launch-label">Watch the factory tour</span>
                </button>
            </div>
            <p>A tour of our wider paper packaging workshop. <a href="https://www.youtube.com/watch?v=nD0iRaJHgLQ" target="_blank" rel="noopener">Watch on YouTube <span aria-hidden="true">↗</span></a></p>
        </div><div class="cpb-quality-checks"><article><h3>Paper, print &amp; finish</h3><p>Confirm the agreed paper grade, artwork, color reference and finish. Recycled content or certification requirements need project-specific confirmation.</p></article><article><h3>Structure &amp; carry requirements</h3><p>Review folds, gluing, handle attachments, reinforcement and loaded carry requirements. GSM alone does not determine bag strength.</p></article><article><h3>Packing for your order</h3><p>Cartons or shrink-wrapped bundles are available according to the order requirements. Agree the packing method and quantities with the specification.</p></article><a class="cpb-text-link" href="<?php echo esc_url(home_url('/about/')); ?>">Meet VPN Paper Box →</a></div></div>
    </div></section>
    <section class="cpb-section" id="faq"><div class="cpb-shell cpb-faq-grid"><div><p class="cpb-eyebrow">Before you request a quote</p><h2>Your paper bag questions, answered.</h2><p class="cpb-section-lead">Not sure about the specifications? Start with your product and quantity. We can discuss the details by email.</p><a class="cpb-text-link" href="#quote">Ask about your project →</a></div><div class="cpb-faq-list"><?php foreach (custom_box_custom_paper_bags_faqs() as $faq) : ?><details><summary><?php echo esc_html($faq['question']); ?></summary><p><?php echo esc_html($faq['answer']); ?></p></details><?php endforeach; ?></div></div></section>
    <section class="cpb-final"><div class="cpb-shell"><p class="cpb-eyebrow">Let’s find the right bag for your brand</p><h2>Your product. Your artwork.<br>A paper bag made around both.</h2><a class="cpb-button" href="#quote" data-track="paper_bag_quote_cta">Request my paper bag quote →</a><p>Or email <a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a></p></div></section>
</main>
<nav class="cpb-mobile-bar" aria-label="Paper bag quick actions"><a class="cpb-button" href="#quote" data-track="paper_bag_quote_cta">Get a quote</a><a href="https://wa.me/84933102653" target="_blank" rel="noopener" data-track="paper_bag_whatsapp_click">WhatsApp</a></nav>
<?php get_footer(); ?>
