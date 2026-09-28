<?php
/**
 * Template Name: Custom Pizza Boxes Manufacturer Landing Page
 */

defined('ABSPATH') || exit;

$category = function_exists('get_term_by') ? get_term_by('slug', 'pizza-boxes', 'product_cat') : false;
$category_url = home_url('/products/pizza-boxes/');
if ($category && !is_wp_error($category)) {
    $resolved_category_url = get_term_link($category);
    if (!is_wp_error($resolved_category_url)) {
        $category_url = $resolved_category_url;
    }
}
$products = custom_box_pizza_boxes_manufacturer_products();
$business = function_exists('custom_box_get_business_schema') ? custom_box_get_business_schema() : array();
$business_address = isset($business['address']) ? $business['address'] : array();
$factory_asset = 'vpn-printing-operator';
$about_url = home_url('/about/');
$factory_url = function_exists('custom_box_get_packaging_money_page_url') ? custom_box_get_packaging_money_page_url() : home_url('/custom-packaging-boxes-manufacturer/');
$faqs = custom_box_pizza_boxes_manufacturer_faqs();
$guide_post = get_page_by_path('custom-pizza-boxes-with-logo-guide', OBJECT, 'post');
$privacy_url = function_exists('custom_box_get_privacy_policy_url') ? custom_box_get_privacy_policy_url() : '';
$quote_status = isset($_GET['quote_status']) ? sanitize_key(wp_unslash($_GET['quote_status'])) : '';
$quote_messages = array(
    'success' => 'Thank you. Your pizza-box enquiry has been sent. Our sales team will review the details and follow up.',
    'received' => 'Thank you. We have received your pizza-box enquiry. For urgent enquiries, please contact sales.vpn@hopgiayvpn.com.',
    'failed' => 'We could not send your enquiry right now. Please try again or email sales.vpn@hopgiayvpn.com.',
    'missing' => 'Please add your name, email and pizza-box details, then submit the form again.',
    'invalid' => 'Your form session expired. Refresh the page and try again.',
    'spam' => 'Your enquiry could not be verified. Refresh the page and try again.',
    'captcha' => 'We could not verify your form. Refresh the page and try again, or contact our sales team directly.',
    'consent' => 'Please agree to the use of your details for this quotation request.',
    'rate_limited' => 'Too many enquiries were sent. Please wait a few minutes and try again.',
);
$quote_message = isset($quote_messages[$quote_status]) ? $quote_messages[$quote_status] : '';

get_header();
?>
<main class="vpn-pizza-page" id="main-content">
    <section class="vpb-hero" aria-labelledby="vpb-title">
        <div class="vpb-shell">
            <nav class="vpb-breadcrumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">&rsaquo;</span><a href="<?php echo esc_url(home_url('/products/')); ?>">Products</a><span aria-hidden="true">&rsaquo;</span><a href="<?php echo esc_url($category_url); ?>">Pizza Boxes</a><span aria-hidden="true">&rsaquo;</span><span>Custom Manufacturer</span></nav>
            <div class="vpb-hero-grid">
                <div class="vpb-hero-copy">
                    <p class="vpb-eyebrow">Custom Pizza Box Packaging</p>
                    <h1 id="vpb-title">Custom Pizza Boxes for Delivery &amp; Foodservice</h1>
                    <p class="vpb-hero-lead">Custom pizza cartons built around your food, brand and delivery route. Work with VPN Paper Box in Vietnam to review dimensions, board, artwork and samples before bulk production.</p>
                    <div class="vpb-hero-actions"><a class="vpb-button" href="#vpb-quote">Request a Pizza Box Quote <span aria-hidden="true">&rarr;</span></a><a class="vpb-link-button" href="#vpb-products">Explore Pizza Box Formats</a></div>
                    <p class="vpb-microcopy">Custom sizes &amp; logo printing &middot; Reference MOQ: 1,000 boxes</p>
                    <figure class="vpb-hero-photo">
                        <img data-factory-image src="<?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image($factory_asset, 800)); ?>"
                            srcset="<?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image($factory_asset, 480)); ?> 480w, <?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image($factory_asset, 800)); ?> 800w, <?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image($factory_asset, 1280)); ?> 1280w"
                            sizes="(min-width: 1001px) 667px, (min-width: 721px) 50vw, calc(100vw - 28px)"
                            alt="VPN team member operating a printing press at the paper-packaging workshop"
                            width="1280" height="720" loading="eager" fetchpriority="high" decoding="async">
                        <figcaption>VPN team member operating a printing press. <a href="<?php echo esc_url($factory_url); ?>">See our factory capabilities</a>.</figcaption>
                    </figure>
                </div>
                <aside class="vpb-quote-card" id="vpb-quote" aria-labelledby="vpb-quote-title">
                    <div class="vpb-quote-card-head"><p class="vpb-eyebrow">Project enquiry</p><h2 id="vpb-quote-title">Get a Custom Pizza Box Quote</h2><p>Share your starting requirements. We can confirm any open specification questions with you.</p></div>
                    <div class="vpb-form-status quote-form-message vpb-form-status--<?php echo esc_attr($quote_status); ?>" role="status" aria-live="polite"<?php echo $quote_message ? '' : ' hidden'; ?>><?php echo esc_html($quote_message); ?></div>
                    <form class="vpb-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" data-session-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                        <input type="hidden" name="action" value="custom_box_quote_form">
                        <input type="hidden" name="quote_source" value="custom_pizza_boxes_manufacturer">
                        <input type="hidden" name="form_location" value="pizza_boxes_manufacturer_hero">
                        <input type="hidden" name="form_anchor" value="vpb-quote">
                        <input type="hidden" name="product_name" value="Custom pizza box packaging">
                        <input type="hidden" name="current_page_url" value="<?php echo esc_url(custom_box_pizza_boxes_manufacturer_url()); ?>">
                        <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); ?>
                        <?php if (function_exists('custom_box_quote_form_anti_spam_fields')) { custom_box_quote_form_anti_spam_fields('quote'); } ?>
                        <div class="vpb-form-grid">
                            <label><span>Full name <b aria-hidden="true">*</b></span><input type="text" name="full_name" autocomplete="name" placeholder="Your name" required></label>
                            <label><span>Work email <b aria-hidden="true">*</b></span><input type="email" name="email" autocomplete="email" placeholder="name@company.com" required></label>
                            <label><span>Pizza box format</span><select name="stock_option"><option value="Custom pizza box - to be discussed">Select a format</option><option value="Square pizza delivery box">Square pizza box</option><option value="Personal-size pizza box">Personal-size box</option><option value="Rectangular flatbread box">Rectangular / flatbread box</option><option value="Not sure yet">Not sure yet</option></select></label>
                            <label><span>Estimated quantity</span><input type="text" name="quantity" inputmode="numeric" placeholder="e.g. 1,000 boxes"></label>
                            <label><span>Delivery country</span><input type="text" name="country" autocomplete="country-name" placeholder="e.g. United States"></label>
                            <label><span>Phone / WhatsApp <small>Optional</small></span><input type="tel" name="phone" autocomplete="tel" placeholder="+1 234 567 8900"></label>
                            <label class="vpb-form-full"><span>Project notes <small>Size, pizza diameter, artwork or delivery setup</small></span><textarea name="message" rows="3" placeholder="Tell us what you know so far"></textarea></label>
                        </div>
                        <?php if (function_exists('custom_box_quote_form_recaptcha_fields')) { custom_box_quote_form_recaptcha_fields(); } ?>
                        <label class="vpb-consent"><input type="checkbox" name="privacy_consent" value="yes" required><span>I agree that VPN Paper Box may use these details to respond to this quotation request.<?php if ($privacy_url) : ?> <a href="<?php echo esc_url($privacy_url); ?>">Privacy Policy</a>.<?php endif; ?></span></label>
                        <button class="vpb-button vpb-submit" type="submit">Send My Quote Request <span aria-hidden="true">&rarr;</span></button>
                        <p class="vpb-form-foot">No order is placed by sending this enquiry. Requirements and pricing are confirmed separately.</p>
                    </form>
                </aside>
            </div>
        </div>
    </section>

    <section class="vpb-proof-strip" aria-label="Pizza box project considerations"><div class="vpb-shell"><span>Size built around the food item</span><span>Board and structure discussed per route</span><span>Artwork and sample approvals defined by brief</span><span>MOQ reference: 1,000 boxes*</span></div></section>

    <section class="vpb-section" id="vpb-products">
        <div class="vpb-shell">
            <div class="vpb-section-heading vpb-heading-row"><div><p class="vpb-eyebrow">Pizza box formats</p><h2>Browse Pizza Box Concepts</h2><p>Use these formats to start a discussion about fit, opening style and artwork. Product pages contain the individual concept notes and ordering details.</p></div><a class="vpb-text-link" href="<?php echo esc_url($category_url); ?>">View all pizza box products <span aria-hidden="true">&rarr;</span></a></div>
            <?php if ($products) : ?>
                <div class="vpb-product-grid">
                    <?php foreach ($products as $product_post) : $is_concept = (bool) get_post_meta($product_post->ID, '_vpn_pizza_boxes_20260928_slug', true); $product_image_id = (int) get_post_thumbnail_id($product_post->ID); ?>
                        <article class="vpb-product-card">
                            <a class="vpb-product-image" href="<?php echo esc_url(get_permalink($product_post)); ?>" aria-label="View <?php echo esc_attr(get_the_title($product_post)); ?>">
                                <?php if ($product_image_id) { echo wp_get_attachment_image($product_image_id, 'woocommerce_thumbnail', false, array('alt' => get_the_title($product_post) . ($is_concept ? ' concept visualization' : ' product image'), 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(min-width: 1001px) 286px, (min-width: 721px) 30vw, calc((100vw - 40px) / 2)')); } else { ?><span class="vpb-image-placeholder" aria-hidden="true">Pizza box<br>format</span><?php } ?>
                            </a>
                            <div class="vpb-product-body"><p class="vpb-card-kicker"><?php echo $is_concept ? 'Concept visualization' : 'Pizza box product'; ?></p><h3><a href="<?php echo esc_url(get_permalink($product_post)); ?>"><?php echo esc_html(get_the_title($product_post)); ?></a></h3><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt($product_post)), 23, '...')); ?></p><a class="vpb-text-link" href="<?php echo esc_url(get_permalink($product_post)); ?>">View format details <span aria-hidden="true">&rarr;</span></a></div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <p class="vpb-caption">*The current product listings show a reference MOQ of 1,000 boxes; confirm the final minimum and quantity per size or artwork in the written quote. The five concepts added in September 2026 use illustrative images, not verified production samples. Their visuals do not establish dimensions, material grade, coating, food-contact suitability or performance.</p>
                <?php if ($guide_post) : ?><p class="vpb-related-guide">Planning artwork or structure? <a href="<?php echo esc_url(get_permalink($guide_post)); ?>">Read our pizza box artwork, board and food-safety guide <span aria-hidden="true">&rarr;</span></a></p><?php endif; ?>
            <?php else : ?>
                <div class="vpb-empty-state"><p>Pizza box product samples are being prepared for this catalogue. Send us your dimensions and quantity for a project review.</p><a class="vpb-button" href="#vpb-quote">Request a Quote</a></div>
            <?php endif; ?>
        </div>
    </section>

    <section class="vpb-section vpb-factory" id="vpb-factory" aria-labelledby="vpb-factory-title">
        <div class="vpb-shell">
            <div class="vpb-section-heading"><p class="vpb-eyebrow">The people and workshop behind your packaging</p><h2 id="vpb-factory-title">Inside VPN's Paper Packaging Operation</h2><p>Meet the manufacturer behind your enquiry. These photos are also published on our <a href="<?php echo esc_url($about_url); ?>">company and factory pages</a>.</p></div>
            <div class="vpb-factory-layout">
                <div class="vpb-factory-gallery">
                    <figure>
                        <img data-factory-image src="<?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image('vpn-box-assembly-floor')); ?>" srcset="<?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image('vpn-box-assembly-floor', 480)); ?> 480w, <?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image('vpn-box-assembly-floor')); ?> 800w" sizes="(min-width: 1001px) 375px, (min-width: 721px) 45vw, calc(100vw - 28px)" alt="VPN staff working at paper-box assembly and finishing stations" width="800" height="450" loading="lazy" decoding="async">
                        <figcaption><strong>Assembly &amp; finishing</strong>Staff and equipment on the paper-box assembly floor.</figcaption>
                    </figure>
                    <figure>
                        <img data-factory-image src="<?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image('vpn-paper-packaging-workshop')); ?>" srcset="<?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image('vpn-paper-packaging-workshop', 480)); ?> 480w, <?php echo esc_url(custom_box_pizza_boxes_manufacturer_factory_image('vpn-paper-packaging-workshop')); ?> 800w" sizes="(min-width: 1001px) 375px, (min-width: 721px) 45vw, calc(100vw - 28px)" alt="VPN paper-packaging workshop with staff, machines and stacked printed sheets" width="800" height="450" loading="lazy" decoding="async">
                        <figcaption><strong>Production workshop</strong>Paper-packaging workstations and materials in the workshop.</figcaption>
                    </figure>
                </div>
                <aside class="vpb-business-card" aria-labelledby="vpb-business-title">
                    <p class="vpb-eyebrow">Your manufacturing contact</p><h3 id="vpb-business-title">VPN Paper Box</h3>
                    <?php if (!empty($business['legalName'])) : ?><p class="vpb-legal-name"><?php echo esc_html($business['legalName']); ?></p><?php endif; ?>
                    <address>
                        <?php if (!empty($business_address['streetAddress'])) : ?><span><?php echo esc_html($business_address['streetAddress']); ?></span><?php endif; ?>
                        <span><?php echo esc_html(!empty($business_address['addressRegion']) ? $business_address['addressRegion'] . ', Vietnam' : 'Ho Chi Minh City, Vietnam'); ?></span>
                    </address>
                    <a class="vpb-business-contact" href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a>
                    <a class="vpb-business-contact" href="tel:+84933102653">+84 933 102 653</a>
                    <div class="vpb-business-links"><a href="<?php echo esc_url($about_url); ?>">About our company &rarr;</a><?php if (!empty($business['hasMap'])) : ?><a href="<?php echo esc_url($business['hasMap']); ?>" target="_blank" rel="noopener">View factory location &rarr;</a><?php endif; ?></div>
                </aside>
            </div>
            <p class="vpb-caption">The photos show our general paper-packaging operation. Your pizza-box construction, materials and required approvals are confirmed for the individual order.</p>
        </div>
    </section>

    <section class="vpb-section vpb-section--soft" id="vpb-specification"><div class="vpb-shell">
        <div class="vpb-section-heading"><p class="vpb-eyebrow">Plan the specification</p><h2>Build the Box Around the Pizza and the Delivery Route</h2><p>A useful brief connects the product dimensions to how it is packed, carried, stacked and delivered. The options below are discussion points, not performance promises.</p></div>
        <div class="vpb-spec-grid">
            <article><span class="vpb-number">01</span><h3>Internal dimensions</h3><p>Share the maximum finished diameter or length and width, topping height, edge irregularities and any liner or serving board. Confirm usable inside dimensions on the dieline.</p></article>
            <article><span class="vpb-number">02</span><h3>Board and construction</h3><p>Discuss paperboard or corrugated directions against the selected footprint, stacking, handling and shipment plan. Confirm the exact grade, flute or caliper in the written specification.</p></article>
            <article><span class="vpb-number">03</span><h3>Closure and airflow</h3><p>Review lid, tab, corner and opening details. If vents or a barrier are requested, define the location and intended purpose, then assess them on a prototype for the actual menu and route.</p></article>
            <article><span class="vpb-number">04</span><h3>Print and food contact</h3><p>Provide artwork and target colors. Specify which surfaces may contact food and the destination market so the proposed materials, coatings and any liner can be checked before approval.</p></article>
        </div>
        <aside class="vpb-expert-note"><strong>Project-specific review</strong><p>Images and general material names are not proof of food-contact compliance. Confirm the construction and supporting documentation needed for the intended use and market with your packaging supplier and compliance team.</p></aside>
    </div></section>

    <section class="vpb-section vpb-process-section" id="vpb-process"><div class="vpb-shell vpb-process-layout"><div><p class="vpb-eyebrow">How a project moves forward</p><h2>From Brief to Approved Pizza Box</h2><p class="vpb-section-intro">The order scope and schedule are agreed after requirements are reviewed.</p><a class="vpb-button" href="#vpb-quote">Start Your Project Brief <span aria-hidden="true">&rarr;</span></a></div><ol class="vpb-steps"><li><span>01</span><div><h3>Share product and route details</h3><p>Send product measurements, desired quantities, delivery country and any known artwork or timing needs.</p></div></li><li><span>02</span><div><h3>Review structure and quotation</h3><p>Confirm internal dimensions, board, closure, print, accessories, per-version quantities and any open requirements.</p></div></li><li><span>03</span><div><h3>Approve proof and sample scope</h3><p>Review the dieline and print proof; define a physical sample or filled-box trial when the project calls for one.</p></div></li><li><span>04</span><div><h3>Confirm production and delivery plan</h3><p>Proceed against the approved order specification, packing details and written schedule.</p></div></li></ol></div></section>

    <section class="vpb-section vpb-section--soft" id="vpb-quote-checklist"><div class="vpb-shell"><div class="vpb-section-heading"><p class="vpb-eyebrow">Prepare your RFQ</p><h2>What to Include in a Pizza Box Enquiry</h2><p>More detail can reduce back-and-forth. If some items are unknown, send what you have and mark the rest as open.</p></div><div class="vpb-checklist-grid"><ul><li>Pizza, flatbread or food item: maximum finished size and height</li><li>Desired box inside dimensions and opening style, if specified</li><li>Estimated quantity and number of sizes or artwork versions</li><li>Artwork files, target colors and required print coverage</li></ul><ul><li>Delivery country, food-contact surface and applicable market needs</li><li>Delivery bag dimensions, stack height and storage conditions</li><li>Sample, prototype or filled-box trial required before bulk</li><li>Target delivery window, packing and shipping preferences</li></ul></div></div></section>

    <section class="vpb-section vpb-faq" id="vpb-faq"><div class="vpb-shell vpb-faq-layout"><div><p class="vpb-eyebrow">Straight answers</p><h2>Pizza Box Buyer Questions</h2><p>Confirm final order requirements in the written quotation and approved specification.</p></div><div class="vpb-accordion"><?php foreach ($faqs as $faq) : ?><details><summary><?php echo esc_html($faq['question']); ?></summary><p><?php echo esc_html($faq['answer']); ?></p></details><?php endforeach; ?></div></div></section>

    <section class="vpb-final-cta"><div class="vpb-shell vpb-final-inner"><div><p class="vpb-eyebrow">Talk through your box requirements</p><h2>Send the Dimensions. We Will Help Scope the Next Step.</h2><p>Contact VPN Paper Box in Vietnam with your product size, quantity and destination. Our team can review the open packaging details with you.</p></div><div class="vpb-final-actions"><a class="vpb-button vpb-button--light" href="#vpb-quote">Request a Pizza Box Quote <span aria-hidden="true">&rarr;</span></a><a class="vpb-contact-link" href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a><a class="vpb-contact-link" href="https://wa.me/84933102653" target="_blank" rel="noopener">WhatsApp: +84 933 102 653</a></div></div></section>
</main>
<?php get_footer(); ?>
