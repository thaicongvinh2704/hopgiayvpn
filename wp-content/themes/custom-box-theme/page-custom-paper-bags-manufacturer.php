<?php
/**
 * Template Name: Global Custom Paper Bags Manufacturer Landing Page
 */

defined('ABSPATH') || exit;

$asset_uri = get_template_directory_uri() . '/assets/images/paper-bag-landing/';
$quote_status = isset($_GET['quote_status']) ? sanitize_key(wp_unslash($_GET['quote_status'])) : '';
$quick_quote_status = $quote_status;
$quick_quote_messages = array(
    'success' => 'Thank you. Your details were sent successfully. We will contact you within 24 hours.',
    'failed' => 'We could not send your request right now. Please try again or email sales.vpn@hopgiayvpn.com.',
    'missing' => 'Please enter your name and a valid business email.',
    'invalid' => 'Your form session expired. Please refresh the page and try again.',
    'spam' => 'Your request could not be verified. Please refresh the page and try again.',
    'rate_limited' => 'Too many requests were sent. Please wait a few minutes and try again.',
);
$is_success = 'success' === $quote_status;
$form_id = 'custom-paper-bags-quote-form';
    $form_error_message = 'We could not send your request right now. Please review the details or contact VPN Paper Box Sales directly.';
if ('duplicate' === $quote_status) {
    $form_error_message = 'VPN Paper Box has already received a similar request. Please wait a moment or contact Sales if you need to add information.';
} elseif ('consent' === $quote_status) {
    $form_error_message = 'Please agree to the privacy notice before sending your request.';
} elseif ('captcha' === $quote_status) {
    $form_error_message = 'The security check could not be completed. Please reload the page and try again.';
} elseif ('missing' === $quote_status) {
    $form_error_message = 'Please complete the required project and contact fields before sending your request.';
} elseif ('file' === $quote_status) {
    $form_error_message = 'The artwork file could not be accepted. Please check the file type and 10MB size limit.';
}

$options = array(
    array('type' => 'Everyday Kraft Paper Bags', 'quote_value' => 'Everyday Kraft Paper Bag', 'description' => 'Natural kraft bags with flat paper handles and simple one-color printing for takeaway, bakery, grocery and everyday retail orders.', 'image' => 'kraft-paper-bag-daily-market.webp', 'width' => 900, 'height' => 900, 'alt' => 'Natural kraft retail paper bag with flat paper handles and one-color printing'),
    array('type' => 'Premium Printed Paper Gift Bags', 'quote_value' => 'Premium Printed Paper Gift Bag', 'description' => 'Printed paper gift bags with rope handles for chocolate, confectionery, premium gifts and branded retail collections.', 'image' => 'printed-paper-gift-bag-chocolate.webp', 'width' => 900, 'height' => 900, 'alt' => 'Dark green marble-pattern printed paper gift bag with rope handles for chocolate packaging'),
    array('type' => 'Patterned Paper Shopping Bags', 'quote_value' => 'Patterned Paper Shopping Bag', 'description' => 'Patterned paper shopping bags with cotton rope handles for gifts, fashion, lifestyle and promotional retail collections.', 'image' => 'patterned-paper-shopping-bag-floral.webp', 'width' => 900, 'height' => 900, 'alt' => 'Patterned cream paper shopping bag with floral print and white rope handles'),
    array('type' => 'Recycled Kraft Paper Bags', 'quote_value' => 'Recycled Kraft Paper Bag', 'description' => 'Textured recycled-kraft bags with twisted paper handles and simple one-color printing for organic, wellness and natural product brands.', 'image' => 'recycled-kraft-bag-root-field.webp', 'width' => 900, 'height' => 900, 'alt' => 'Recycled kraft paper shopping bag with twisted paper handles and one-color print'),
    array('type' => 'Reinforced Retail Paper Bags', 'quote_value' => 'Reinforced Retail Paper Bag', 'description' => 'Thicker paper bags can use rope handles, reinforced handle points and bottom support for boxed skincare, bottles and heavier retail products.', 'image' => 'reinforced-paper-bag-north-co.webp', 'width' => 900, 'height' => 900, 'alt' => 'Reinforced dark green retail paper bag with rope handles and metal eyelets'),
    array('type' => 'Luxury Paper Gift Bags', 'quote_value' => 'Luxury Paper Gift Bag', 'description' => 'Specialty-paper gift bags with ribbon handles; foil detailing can be considered for jewelry, perfume, premium gifts and boutique retail.', 'image' => 'luxury-paper-bag-velora.webp', 'width' => 900, 'height' => 900, 'alt' => 'Charcoal luxury paper gift bag with black ribbon handles and gold foil wordmark'),
);

$customization = array(
    array('title' => 'Paper', 'text' => 'Brown kraft, white kraft, coated or art paper, and specialty paper directions can be reviewed around the product and print target.'),
    array('title' => 'Handles', 'text' => 'Twisted paper, flat paper, cotton or PP rope, ribbon, and die-cut handles create different carry experiences.'),
    array('title' => 'Printing', 'text' => 'One-color, CMYK, and Pantone directions are confirmed against the artwork, paper surface, and required color target.'),
    array('title' => 'Finishing', 'text' => 'Matte or gloss lamination, foil stamping, embossing/debossing, and spot UV can be reviewed where suitable.'),
);

$steps = array(
    array('number' => '01', 'title' => 'Share Your Brief', 'text' => 'Send the size, product weight, quantity, artwork, delivery country, and target schedule when known.'),
    array('number' => '02', 'title' => 'Material & Structure Review', 'text' => 'Review the paper, gusset, handle, top reinforcement, and bottom support direction.'),
    array('number' => '03', 'title' => 'Artwork & Sample Approval', 'text' => 'Confirm the dieline, color, finish, and physical sample when the project requires one.'),
    array('number' => '04', 'title' => 'Production & Quality Checks', 'text' => 'Confirm how print, cutting, folding, gluing, handles, and finished-bag appearance will be checked against the approved specification.'),
    array('number' => '05', 'title' => 'Packing & Delivery Support', 'text' => 'Confirm the packing method and shipment details for the approved project.'),
);

$comparison_rows = array(
    array('need' => 'Cost-efficient retail', 'direction' => 'Kraft or white paper, simple print', 'confirm' => 'Size, load, handle, and order volume'),
    array('need' => 'Premium presentation', 'direction' => 'Coated or specialty paper, rope or ribbon', 'confirm' => 'Finish, color target, and reinforcement'),
    array('need' => 'Heavier contents', 'direction' => 'Stronger stock and reinforced construction', 'confirm' => 'Actual packed weight and carry test'),
    array('need' => 'Campaign or events', 'direction' => 'Die-cut or flat-handle option', 'confirm' => 'Product weight, campaign duration, and artwork'),
);

$paper_bag_category = function_exists('get_term_by') ? get_term_by('slug', 'paper-bags-with-logo', 'product_cat') : false;
$paper_bag_category_link = home_url('/products/paper-bags-with-logo/');
$paper_bag_landing_image_overrides = array(
    'custom-luxury-paper-gift-bag-with-ribbon-handles' => array('file' => 'custom-floral-paper-gift-bag-with-ribbon-handles.webp', 'title' => 'Custom Floral Paper Gift Bag with Ribbon Handles', 'alt' => 'Yellow floral paper gift bag with ribbon handles'),
    'custom-mooncake-gift-box-set-with-paper-bag' => array('file' => 'custom-illustrated-gift-box-set-with-paper-bag.webp', 'title' => 'Custom Illustrated Gift Box Set with Paper Bag', 'alt' => 'Illustrated paper gift bag with ribbon handle for a gift box set'),
    'custom-phone-packaging-box-with-paper-bag' => array('file' => 'custom-perfume-gift-box-with-paper-bag.webp', 'title' => 'Custom Perfume Gift Box with Paper Bag', 'alt' => 'Premium perfume gift box with matching paper bag'),
    'custom-birthday-paper-gift-bag-with-candle-print' => array('file' => 'custom-olive-floral-paper-gift-bag-with-fabric-handles.webp', 'title' => 'Custom Olive Floral Paper Gift Bag with Fabric Handles', 'alt' => 'Olive green floral paper gift bag with white fabric handles and a hanging tag'),
);
if ($paper_bag_category && !is_wp_error($paper_bag_category)) {
    $resolved_category_link = get_term_link($paper_bag_category);
    if (!is_wp_error($resolved_category_link)) {
        $paper_bag_category_link = $resolved_category_link;
    }
}

$paper_bag_product_query = false;
if ($paper_bag_category && !is_wp_error($paper_bag_category) && class_exists('WP_Query')) {
    $paper_bag_category_tax_query = array(
        array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => (int) $paper_bag_category->term_id,
            'include_children' => true,
        ),
    );
    $paper_bag_product_ids = get_posts(array(
        'post_type'           => 'product',
        'post_status'         => 'publish',
        'posts_per_page'      => 8,
        'orderby'             => 'menu_order title',
        'order'               => 'ASC',
        'ignore_sticky_posts' => true,
        'fields'              => 'ids',
        'tax_query'           => $paper_bag_category_tax_query,
    ));
    $paper_bag_phone_product_ids = get_posts(array(
        'post_type'           => 'product',
        'post_status'         => 'publish',
        'name'                => 'custom-phone-packaging-box-with-paper-bag',
        'posts_per_page'      => 1,
        'fields'              => 'ids',
        'tax_query'           => $paper_bag_category_tax_query,
    ));
    $paper_bag_rust_product_ids = get_posts(array(
        'post_type'           => 'product',
        'post_status'         => 'publish',
        'name'                => 'custom-rust-paper-shopping-bag-with-rope-handles',
        'posts_per_page'      => 1,
        'fields'              => 'ids',
        'tax_query'           => $paper_bag_category_tax_query,
    ));
    $paper_bag_replaced_product_ids = get_posts(array(
        'post_type'           => 'product',
        'post_status'         => 'publish',
        'name'                => 'custom-birthday-paper-gift-bag-with-present-print',
        'posts_per_page'      => 1,
        'fields'              => 'ids',
        'tax_query'           => $paper_bag_category_tax_query,
    ));

    if (!empty($paper_bag_phone_product_ids) && !in_array((int) $paper_bag_phone_product_ids[0], array_map('intval', $paper_bag_product_ids), true)) {
        $paper_bag_product_ids[] = (int) $paper_bag_phone_product_ids[0];
    }
    if (!empty($paper_bag_rust_product_ids)) {
        $paper_bag_product_id_list = array_map('intval', $paper_bag_product_ids);
        $paper_bag_replacement_index = !empty($paper_bag_replaced_product_ids)
            ? array_search((int) $paper_bag_replaced_product_ids[0], $paper_bag_product_id_list, true)
            : false;

        if (false !== $paper_bag_replacement_index) {
            $paper_bag_product_ids[$paper_bag_replacement_index] = (int) $paper_bag_rust_product_ids[0];
        } elseif (!in_array((int) $paper_bag_rust_product_ids[0], $paper_bag_product_id_list, true)) {
            $paper_bag_product_ids[] = (int) $paper_bag_rust_product_ids[0];
        }
    }

    if (!empty($paper_bag_product_ids)) {
        $paper_bag_product_query = new WP_Query(array(
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'posts_per_page'      => count($paper_bag_product_ids),
            'post__in'            => array_map('intval', $paper_bag_product_ids),
            'orderby'             => 'post__in',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
        ));
    }
}

$faqs = array(
    array('question' => 'What information is needed for a paper bag quotation?', 'answer' => 'Share the product being carried, finished size, quantity, delivery country, preferred paper or handles, artwork, and target schedule when known.'),
    array('question' => 'What paper and handle options are available?', 'answer' => 'Options may include brown kraft, white kraft, coated or art paper, specialty paper, twisted paper, flat paper, cotton or PP rope, ribbon, and die-cut handles. The final combination depends on the bag structure, load, finish, and order requirements.'),
    array('question' => 'Can you match Pantone colors and custom artwork?', 'answer' => 'We review CMYK and Pantone requirements together with the artwork, paper surface, and print method. The final color direction is confirmed before production.'),
    array('question' => 'Can I request a sample before mass production?', 'answer' => 'Sampling can be arranged before bulk production when the project requires approval of structure, artwork, material, or finish.'),
    array('question' => 'What is the MOQ for custom paper bags?', 'answer' => 'MOQ depends on the bag structure, material, printing, and quantity requirements. The applicable MOQ is confirmed with the project quotation.'),
    array('question' => 'How is the production timeline confirmed?', 'answer' => 'Sampling and production timing depend on the approved specification, quantity, sampling needs, and order schedule.'),
    array('question' => 'Can the bags be packed for international shipping?', 'answer' => 'Carton packing and shipment details can be reviewed against the approved order specification for international B2B projects.'),
    array('question' => 'Can you produce matching paper bags and boxes?', 'answer' => 'Paper bags and paper boxes can be discussed together when a project requires both. The matching scope is confirmed after reviewing the product, quantity, materials, and artwork.'),
);

get_header();
?>
<main id="cpb-main" class="cpb-page" data-quote-success="<?php echo $is_success ? '1' : '0'; ?>">
    <section class="cpb-hero cpb-hero--lead">
        <div class="cpb-shell cpb-hero__grid">
            <div class="cpb-hero__copy">
                <p class="cpb-eyebrow">Custom Paper Bag Manufacturing in Vietnam</p>
                <h1>Custom Paper Bags With Logo for Brands, Retailers &amp; B2B Buyers</h1>
                <p class="cpb-hero__lead">We help brands, retailers, importers and distributors specify paper bags around product weight, finished size, handles, artwork, printing and delivery requirements.</p>
                <ul class="cpb-hero__trust"><li>Kraft, coated and specialty paper options</li><li>Twisted paper, rope, ribbon and die-cut handles</li><li>CMYK, Pantone and premium finishing options</li></ul>
                <div class="cpb-actions"><a class="cpb-button" href="#quote" data-track="paper_bag_quote_cta">Request a Custom Paper Bag Quote</a><a class="cpb-button cpb-button--ghost" href="https://wa.me/84933102653" target="_blank" rel="noopener" data-track="paper_bag_whatsapp_click">Chat on WhatsApp</a></div>
                <p class="cpb-hero__microcopy">Share your size, quantity, artwork and delivery country for a project-specific review.</p>
            </div>
            <aside class="cpb-hero__quick-card" id="cpb-hero-quick-quote" aria-labelledby="cpb-hero-quick-quote-title">
                <div class="cpb-hero__quick-card-head">
                    <h2 id="cpb-hero-quick-quote-title">Get a Quick Quote</h2>
                    <p>Tell us what you need — we aim to reply within one business day.</p>
                </div>

                <div class="cpb-quick-message<?php echo $quick_quote_status && isset($quick_quote_messages[$quick_quote_status]) ? ' cpb-quick-message-' . esc_attr($quick_quote_status) : ''; ?>" role="status" aria-live="polite" <?php echo $quick_quote_status && isset($quick_quote_messages[$quick_quote_status]) ? '' : 'hidden'; ?>><?php echo $quick_quote_status && isset($quick_quote_messages[$quick_quote_status]) ? esc_html($quick_quote_messages[$quick_quote_status]) : ''; ?></div>

                <form class="cpb-quick-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="custom_box_quote_form">
                    <input type="hidden" name="quote_source" value="custom_paper_bags_manufacturer_quick_form">
                    <input type="hidden" name="form_location" value="hero">
                    <input type="hidden" name="form_anchor" value="cpb-hero-quick-quote">
                    <input type="hidden" name="product_type" value="paper_bags">
                    <input type="hidden" name="product_name" value="Custom paper bag quick inquiry">
                    <input type="hidden" name="current_page_url" value="<?php echo esc_url(custom_box_custom_paper_bags_url()); ?>">
                    <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); ?>
                    <?php custom_box_quote_form_anti_spam_fields('quote'); ?>

                    <div class="cpb-quick-fields">
                        <label>
                            <span>Your name</span>
                            <input type="text" name="full_name" autocomplete="name" placeholder="Full name" required>
                        </label>
                        <label>
                            <span>Work email</span>
                            <input type="email" name="email" autocomplete="email" placeholder="name@company.com" required>
                        </label>
                        <label>
                            <span>Paper bag type</span>
                            <select name="stock_option" required>
                                <option value="" selected disabled>Select type</option>
                                <option value="Kraft paper bag">Kraft paper bag</option>
                                <option value="White paper bag">White paper bag</option>
                                <option value="Coated paper bag">Coated paper bag</option>
                                <option value="Recycled kraft bag">Recycled kraft bag</option>
                                <option value="Luxury paper gift bag">Luxury paper gift bag</option>
                                <option value="Other / Not sure">Other / Not sure</option>
                            </select>
                        </label>
                        <label>
                            <span>Quantity</span>
                            <input type="text" name="quantity" inputmode="numeric" placeholder="e.g. 1,000 bags" required>
                        </label>
                        <label>
                            <span>Delivery country</span>
                            <input type="text" name="country" autocomplete="country-name" placeholder="e.g. United States" required>
                        </label>
                        <label>
                            <span>Phone / WhatsApp <small>Optional</small></span>
                            <input type="tel" name="phone" autocomplete="tel" placeholder="+84 123 456 789">
                        </label>
                    </div>

                    <?php custom_box_quote_form_recaptcha_fields(); ?>
                    <button type="submit" data-track="paper_bag_quick_quote_submit">Get My Quote <span aria-hidden="true">→</span></button>
                    <p class="cpb-quick-foot"><span>Free quotation</span><i aria-hidden="true">•</i><span>No obligation</span></p>
                </form>
            </aside>
            <figure class="cpb-hero__media">
                <img src="<?php echo esc_url($asset_uri . 'paper-bag-factory-production-floor.webp'); ?>" alt="Paper bag production floor with workers, converting machines and stacked printed paper bags" width="1400" height="594" fetchpriority="high" decoding="async">
            </figure>
            <div class="cpb-hero__proof" aria-label="Paper bag project proof points">
                <span>Custom sizes, paper stocks &amp; handles</span>
                <span>Artwork and sample review</span>
                <span>Vietnam-based production support</span>
                <span>Carton packing and shipment review</span>
            </div>
        </div>
    </section>

    <section class="cpb-fit-strip" aria-label="B2B paper bag industries"><div class="cpb-shell"><p class="cpb-eyebrow">Built for B2B Paper Bag Projects</p><div class="cpb-chip-list"><span>Retail &amp; Fashion</span><span>Cosmetics &amp; Beauty</span><span>Gifts &amp; Jewelry</span><span>Food &amp; Beverage</span><span>Importers &amp; Distributors</span><span>Packaging Agencies</span></div></div></section>

    <section class="cpb-section" id="options"><div class="cpb-shell"><div class="cpb-section-heading"><p class="cpb-eyebrow">PAPER BAG OPTIONS</p><h2>Choose the Right Paper Bag for Your Product</h2><p>Start with the carry experience, surface, product weight, and print direction. The final construction is confirmed against your brief.</p></div><div class="cpb-option-grid">
        <?php foreach ($options as $option) : ?><article class="cpb-option-card"><div class="cpb-option-card__media"><img src="<?php echo esc_url($asset_uri . $option['image']); ?>" alt="<?php echo esc_attr($option['alt']); ?>" width="<?php echo esc_attr($option['width']); ?>" height="<?php echo esc_attr($option['height']); ?>" loading="lazy" decoding="async"></div><div class="cpb-option-card__body"><h3><?php echo esc_html($option['type']); ?></h3><p><?php echo esc_html($option['description']); ?></p><a href="#quote" class="cpb-text-link" aria-label="<?php echo esc_attr('Request a quote for ' . strtolower($option['type'])); ?>" data-bag-type="<?php echo esc_attr($option['quote_value']); ?>" data-track="paper_bag_quote_cta">Request a Custom Paper Bag Quote <span aria-hidden="true">→</span></a></div></article><?php endforeach; ?>
    </div></div></section>

    <section class="cpb-section cpb-section--soft" id="capabilities"><div class="cpb-shell cpb-capability-grid"><div><p class="cpb-eyebrow">Customization</p><h2>Configure the Details That Drive Performance and Cost</h2><p class="cpb-section-lead">Paper, handles, printing, and finishing should be considered together with the bag's dimensions and packed weight.</p><div class="cpb-custom-grid"><?php foreach ($customization as $item) : ?><article><span class="cpb-icon" aria-hidden="true">✓</span><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['text']); ?></p></article><?php endforeach; ?></div><aside class="cpb-note"><strong>B2B specification note</strong><p>The final construction depends on bag dimensions, packed weight, handle attachment, top reinforcement, bottom support, and shipping method.</p></aside></div><div class="cpb-capability-media"><img src="<?php echo esc_url($asset_uri . 'custom-paper-bags-material-samples.webp'); ?>" alt="Paper bag material samples and folded bag structures on a packaging worktable" width="1600" height="900" loading="lazy" decoding="async"><img src="<?php echo esc_url($asset_uri . 'custom-paper-bags-handles-finishing.webp'); ?>" alt="Paper bag handle types, reinforced eyelets and finishing samples on a worktable" width="1600" height="900" loading="lazy" decoding="async"></div></div></section>

    <section class="cpb-section" id="strength"><div class="cpb-shell"><div class="cpb-section-heading"><p class="cpb-eyebrow">Strength and specification</p><h2>Designed Around the Product You Need to Carry</h2><p>Paper GSM alone does not determine bag strength. Review finished width, height and gusset, loaded weight, handle material and attachment, top reinforcement, bottom card, glue and fold quality, and a loaded carry test where needed.</p></div><div class="cpb-table-scroll"><table class="cpb-compare-table"><caption>Use these directions as a starting point; the final structure is confirmed against the approved project brief.</caption><thead><tr><th scope="col">Buyer need</th><th scope="col">Recommended direction</th><th scope="col">What to confirm</th></tr></thead><tbody><?php foreach ($comparison_rows as $row) : ?><tr><th scope="row"><?php echo esc_html($row['need']); ?></th><td><?php echo esc_html($row['direction']); ?></td><td><?php echo esc_html($row['confirm']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></section>

    <section class="cpb-section cpb-section--ink" id="process"><div class="cpb-shell cpb-process-grid"><div><p class="cpb-eyebrow">Production process</p><h2>From Specification to Packed Paper Bags</h2><p>The exact sequence and timing are confirmed after the bag specification, quantity, sampling needs, and delivery plan are approved.</p><figure class="cpb-process-image"><img src="<?php echo esc_url($asset_uri . 'roll-fed-paper-bag-forming-machine.webp'); ?>" alt="Roll-fed paper bag forming machine processing a kraft paper roll in a factory workshop" width="1600" height="900" loading="lazy" decoding="async"><figcaption>Roll-fed paper bag forming and converting machine in a factory workshop.</figcaption></figure></div><ol class="cpb-steps"><?php foreach ($steps as $step) : ?><li><span><?php echo esc_html($step['number']); ?></span><div><h3><?php echo esc_html($step['title']); ?></h3><p><?php echo esc_html($step['text']); ?></p></div></li><?php endforeach; ?></ol></div></section>

    <section class="cpb-section cpb-section--soft" id="gallery">
        <div class="cpb-shell">
            <div class="cpb-section-heading cpb-section-heading--row">
                <div>
                    <p class="cpb-eyebrow">Paper Bag &amp; Related Packaging Products</p>
                    <h2>Paper Bag &amp; Related Packaging Products</h2>
                    <p>Browse selected paper bags and related packaging formats. Final materials, sizes, handles, printing, and finishing are confirmed for each project.</p>
                </div>
                <a class="cpb-text-link" href="<?php echo esc_url($paper_bag_category_link); ?>">View all paper bag products <span aria-hidden="true">→</span></a>
            </div>

            <?php if ($paper_bag_product_query && $paper_bag_product_query->have_posts()) : ?>
                <div class="product-listing-category-section cpb-product-listing">
                    <div class="custom-product-grid">
                        <?php while ($paper_bag_product_query->have_posts()) : $paper_bag_product_query->the_post(); ?>
                            <?php
                            $paper_bag_product = function_exists('wc_get_product') ? wc_get_product(get_the_ID()) : false;
                            if (!$paper_bag_product) {
                                continue;
                            }
                            $paper_bag_image_id = (int) $paper_bag_product->get_image_id();
                            $paper_bag_product_slug = $paper_bag_product->get_slug();
                            $paper_bag_landing_image = isset($paper_bag_landing_image_overrides[$paper_bag_product_slug]) ? $paper_bag_landing_image_overrides[$paper_bag_product_slug] : false;
                            $paper_bag_display_title = $paper_bag_landing_image ? $paper_bag_landing_image['title'] : get_the_title();
                            $paper_bag_title_id = 'cpb-product-card-title-' . get_the_ID();
                            ?>
                            <article <?php if (function_exists('wc_product_class')) { wc_product_class('custom-product-card', $paper_bag_product); } else { echo 'class="custom-product-card"'; } ?> data-product-card>
                                <a class="custom-product-card-link" href="<?php echo esc_url(get_permalink()); ?>" aria-labelledby="<?php echo esc_attr($paper_bag_title_id); ?>">
                                    <span class="custom-product-image">
                                        <?php if ($paper_bag_landing_image) : ?>
                                            <img src="<?php echo esc_url($asset_uri . $paper_bag_landing_image['file']); ?>" alt="<?php echo esc_attr($paper_bag_landing_image['alt']); ?>" width="900" height="1062" loading="lazy" decoding="async">
                                        <?php elseif ($paper_bag_image_id) : ?>
                                            <?php echo wp_get_attachment_image($paper_bag_image_id, 'full', false, array('alt' => '', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 379px) calc(100vw - 36px), (max-width: 767px) calc(50vw - 28px), (max-width: 1200px) 33vw, 360px')); ?>
                                        <?php else : ?>
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp'); ?>" alt="" width="506" height="277" loading="lazy" decoding="async">
                                        <?php endif; ?>
                                    </span>
                                    <span class="custom-product-body"><h2 id="<?php echo esc_attr($paper_bag_title_id); ?>" class="custom-product-title"><?php echo esc_html($paper_bag_display_title); ?></h2></span>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="product-archive-empty"><p>Paper bag products are being prepared. Visit the category page to see the latest catalog.</p><a class="cpb-text-link" href="<?php echo esc_url($paper_bag_category_link); ?>">Open Paper Bags with Logo category <span aria-hidden="true">→</span></a></div>
            <?php endif; ?>
        </div>
    </section>

    <section class="cpb-section" id="why-vpn"><div class="cpb-shell cpb-why-grid"><div class="cpb-why-image"><img src="<?php echo esc_url($asset_uri . 'custom-paper-bags-quality-inspection.webp'); ?>" alt="Worker checking the folded base of a kraft paper bag with a ruler at a quality inspection table" width="1600" height="900" loading="lazy" decoding="async"><p>Quality checkpoints should be agreed against the approved paper bag specification and order requirements.</p></div><div><p class="cpb-eyebrow">Why VPN Paper Box</p><h2>Why Work With VPN Paper Box?</h2><ul class="cpb-check-list"><li>Vietnam-based paper packaging production support</li><li>Custom size, structure, artwork, and finishing review</li><li>Sampling can be arranged before bulk production when required</li><li>Quality checkpoints for print, folds, handles, and finished appearance</li><li>Carton packing and shipment details reviewed against the approved order</li><li>Paper bags and paper boxes can be discussed together when a project requires both</li></ul></div></div></section>

    <section class="cpb-section cpb-section--soft" id="quote"><div class="cpb-shell cpb-quote-grid"><div class="cpb-quote-intro"><p class="cpb-eyebrow">Project quote</p><h2>Request a Project-Specific Paper Bag Quote</h2><p>Send the details you already have. Our team can review the bag structure, material, printing, and delivery requirements with you.</p><div class="cpb-contact-card"><strong>Prefer a quick conversation?</strong><a href="https://wa.me/84933102653" target="_blank" rel="noopener" data-track="paper_bag_whatsapp_click">Chat on WhatsApp</a><a href="tel:+84933102653" data-track="paper_bag_phone_click">(+84) 933 102 653</a><a href="mailto:sales.vpn@hopgiayvpn.com" data-track="paper_bag_email_click">sales.vpn@hopgiayvpn.com</a></div></div><div class="cpb-form-card">
        <?php if ($is_success) : ?><div class="cpb-success" role="status" aria-live="polite"><span class="cpb-success__icon" aria-hidden="true">✓</span><h3>Quote request received</h3><p>Thank you. VPN Paper Box has received your project details and will review the specification before replying.</p><a href="#options">Review paper bag options</a></div><?php else : ?>
            <?php if ($quote_status && 'success' !== $quote_status) : ?><div class="cpb-alert" role="alert"><?php echo esc_html($form_error_message); ?></div><?php endif; ?><p class="cpb-required"><span aria-hidden="true">*</span> Required fields</p><div class="cpb-form-errors" id="cpb-form-errors" role="alert" aria-live="assertive" tabindex="-1" hidden></div>
            <form id="<?php echo esc_attr($form_id); ?>" class="cpb-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" value="custom_box_quote_form"><input type="hidden" name="quote_source" value="custom_paper_bags_manufacturer"><input type="hidden" name="form_location" value="custom_paper_bags_manufacturer_quote"><input type="hidden" name="product_type" value="paper_bags"><input type="hidden" name="form_anchor" value="quote"><input type="hidden" name="current_page_url" value="<?php echo esc_url(custom_box_custom_paper_bags_url()); ?>"><input type="hidden" name="email_subject" value="Custom paper bag quote request"><input type="hidden" name="referrer_url" value=""><input type="hidden" name="utm_source" value=""><input type="hidden" name="utm_medium" value=""><input type="hidden" name="utm_campaign" value=""><input type="hidden" name="utm_term" value=""><input type="hidden" name="utm_content" value=""><input type="hidden" name="gclid" value=""><input type="hidden" name="gbraid" value=""><input type="hidden" name="wbraid" value="">
                <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); ?><?php custom_box_quote_form_anti_spam_fields('quote'); ?>
                <fieldset><legend>Project details</legend><div class="cpb-form-grid"><div class="cpb-field"><label for="cpb-bag-type">Bag type <span aria-hidden="true">*</span></label><select id="cpb-bag-type" name="product_name" required><option value="">Select a direction</option><?php foreach ($options as $option) : ?><option value="<?php echo esc_attr($option['type']); ?>"><?php echo esc_html($option['type']); ?></option><?php endforeach; ?><option value="Other custom paper bag">Other / not sure</option></select></div><div class="cpb-field"><label for="cpb-quantity">Estimated quantity <span aria-hidden="true">*</span></label><input id="cpb-quantity" name="quantity" type="text" required inputmode="numeric" placeholder="Example: 1,000 bags"></div><div class="cpb-field"><label for="cpb-country">Delivery country or region <span aria-hidden="true">*</span></label><input id="cpb-country" name="country" type="text" required autocomplete="country-name" placeholder="Example: United States"></div><div class="cpb-field"><label for="cpb-weight">Expected packed weight <span>(optional)</span></label><input id="cpb-weight" name="stock_option" type="text" placeholder="Example: 2 kg per bag"></div></div></fieldset>
                <fieldset><legend>Size and preferences <span>(optional)</span></legend><div class="cpb-form-grid cpb-form-grid--four"><div class="cpb-field"><label for="cpb-length">Width</label><input id="cpb-length" name="length" type="text" inputmode="decimal" placeholder="mm"></div><div class="cpb-field"><label for="cpb-width">Height</label><input id="cpb-width" name="width" type="text" inputmode="decimal" placeholder="mm"></div><div class="cpb-field"><label for="cpb-depth">Gusset</label><input id="cpb-depth" name="depth" type="text" inputmode="decimal" placeholder="mm"></div><div class="cpb-field"><label for="cpb-unit">Unit</label><select id="cpb-unit" name="unit"><option value="">Select</option><option value="mm">mm</option><option value="cm">cm</option><option value="inch">inch</option></select></div></div><div class="cpb-form-grid"><div class="cpb-field"><label for="cpb-material">Paper / handle preference</label><select id="cpb-material" name="material_preference"><option value="">Not decided</option><option value="Brown kraft paper">Brown kraft paper</option><option value="White kraft paper">White kraft paper</option><option value="Coated or art paper">Coated or art paper</option><option value="Rope or ribbon handles">Rope or ribbon handles</option><option value="Die-cut handle">Die-cut handle</option></select></div><div class="cpb-field"><label for="cpb-printing">Printing</label><select id="cpb-printing" name="printing_option"><option value="">Not decided</option><option value="One-color">One-color</option><option value="CMYK">CMYK</option><option value="Pantone">Pantone</option></select></div><div class="cpb-field"><label for="cpb-finishing">Finishing</label><select id="cpb-finishing" name="finishing_option"><option value="">Not decided</option><option value="Matte or gloss lamination">Matte or gloss lamination</option><option value="Foil stamping">Foil stamping</option><option value="Embossing or debossing">Embossing or debossing</option><option value="Spot UV">Spot UV</option></select></div><div class="cpb-field"><label for="cpb-timeline">Target schedule</label><input id="cpb-timeline" name="production_timeline" type="text" placeholder="Example: Q4 2026 launch"></div></div></fieldset>
                <fieldset><legend>Your contact details</legend><div class="cpb-form-grid"><div class="cpb-field"><label for="cpb-name">Full name <span aria-hidden="true">*</span></label><input id="cpb-name" name="full_name" type="text" required autocomplete="name"></div><div class="cpb-field"><label for="cpb-email">Work email <span aria-hidden="true">*</span></label><input id="cpb-email" name="email" type="email" required autocomplete="email"></div><div class="cpb-field"><label for="cpb-company">Company name</label><input id="cpb-company" name="company" type="text" autocomplete="organization"></div><div class="cpb-field"><label for="cpb-phone">Phone / WhatsApp</label><input id="cpb-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel"></div></div></fieldset>
                <fieldset><legend>Artwork and project notes <span>(optional)</span></legend><div class="cpb-field"><label for="cpb-artwork">Artwork upload</label><input id="cpb-artwork" name="artwork" type="file" accept=".png,.pdf,.jpg,.jpeg,.webp,.doc,.docx,.gif,.psd,.cdr,.eps"><small>PNG, PDF, JPG, WebP, DOC, PSD, CDR, or EPS; maximum 10MB.</small></div><div class="cpb-field"><label for="cpb-message">Project notes</label><textarea id="cpb-message" name="message" rows="4" placeholder="Product, handle, material, artwork, packing, or delivery details"></textarea></div></fieldset>
                <div class="cpb-consent"><label><input type="checkbox" name="privacy_consent" value="yes" required><span>I agree that VPN Paper Box may use this information to advise on and quote this request. <a href="<?php echo esc_url(function_exists('get_privacy_policy_url') && get_privacy_policy_url() ? get_privacy_policy_url() : home_url('/contact/')); ?>">View the Privacy Policy</a>.</span></label></div>
                <?php if (function_exists('custom_box_quote_form_recaptcha_fields')) : ?><?php custom_box_quote_form_recaptcha_fields(); ?><?php endif; ?><button class="cpb-button cpb-button--submit" type="submit" data-submit-label="Send quote request">Request a Custom Paper Bag Quote</button><p class="cpb-form-note">Your information is used to review and respond to this project-specific request.</p>
            </form>
        <?php endif; ?>
    </div></div></section>

    <section class="cpb-section cpb-section--faq" id="faq"><div class="cpb-shell cpb-faq-grid"><div class="cpb-section-heading"><p class="cpb-eyebrow">Questions before ordering</p><h2>Frequently Asked Questions</h2><p>Clear answers for teams comparing paper bag materials, construction, sampling, and delivery.</p></div><div class="cpb-faq-list"><?php foreach ($faqs as $faq) : ?><details><summary><?php echo esc_html($faq['question']); ?></summary><p><?php echo esc_html($faq['answer']); ?></p></details><?php endforeach; ?></div></div></section>
    <section class="cpb-final-cta"><div class="cpb-shell cpb-final-cta__inner"><div><p class="cpb-eyebrow">Ready to develop your custom paper bag?</p><h2>Send your size, quantity, artwork, and delivery country for a project-specific review.</h2></div><div class="cpb-actions"><a class="cpb-button cpb-button--light" href="#quote" data-track="paper_bag_quote_cta">Request a Custom Paper Bag Quote</a><a class="cpb-button cpb-button--outline-light" href="https://wa.me/84933102653" target="_blank" rel="noopener" data-track="paper_bag_whatsapp_click">Chat on WhatsApp</a></div></div></section>
</main>
<div class="cpb-mobile-bar" aria-label="Quick contact"><a href="tel:+84933102653" data-track="paper_bag_phone_click">Call</a><a href="https://wa.me/84933102653" target="_blank" rel="noopener" data-track="paper_bag_whatsapp_click">WhatsApp</a><a href="#quote" data-track="paper_bag_quote_cta">Request a Custom Paper Bag Quote</a></div>
<?php get_footer(); ?>
