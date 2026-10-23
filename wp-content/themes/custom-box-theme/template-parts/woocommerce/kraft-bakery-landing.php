<?php
/** Wholesale landing for SOS and bread bag enquiries, using existing catalog products. */
defined('ABSPATH') || exit;
$choices = vpn_kraft_bakery_products();
$data = vpn_kraft_category_data();
$countries = function_exists('WC') && WC()->countries ? WC()->countries->get_countries() : array();
$privacy = get_privacy_policy_url() ?: home_url('/privacy-policy/');
$hero_choices = array();
foreach (array('sos', 'bread') as $family) {
    foreach ($choices as $item) {
        if ($family === $item['group']) { $hero_choices[] = $item; break; }
    }
}
get_header();
?>
<main id="main-content" class="kb-page">
    <section class="kb-hero" aria-labelledby="kb-title">
        <div class="container">
            <nav class="product-archive-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><a href="<?php echo esc_url(home_url('/products/paper-bags-with-logo/')); ?>">Paper Bags with Logo</a><span aria-current="page">Custom Kraft Paper Bags</span></nav>
            <div class="kb-hero-grid">
                <div class="kb-hero-copy">
                    <p class="product-eyebrow">Made in Vietnam · Wholesale &amp; repeat orders</p>
                    <h1 id="kb-title">Custom kraft paper bags<br><span>for bread &amp; bakery.</span></h1>
                    <p class="kb-lead">From square-bottom SOS bags to long baguette sleeves, choose the format for your bakery, food-service range or distribution business. Custom sizes and logo printing, made to order.</p>
                    <ul class="kb-proof" aria-label="Order highlights"><li>General MOQ <strong>1,000 bags</strong></li><li><strong>Your logo &amp; size</strong></li><li><strong>Design support</strong></li></ul>
                    <div class="kb-hero-examples">
                        <?php foreach ($hero_choices as $index => $item) : ?>
                            <a href="<?php echo 'sos' === $item['group'] ? '#sos-bags' : '#bread-bags'; ?>"><?php echo vpn_kraft_bakery_image($item, 0 === $index); ?><span><?php echo 'sos' === $item['group'] ? 'SOS &amp; square-bottom bags' : 'Bread &amp; baguette bags'; ?> <span aria-hidden="true">↗</span></span></a>
                        <?php endforeach; ?>
                    </div>
                    <p class="kb-caption">Examples from our product catalog. Paper, printing and finished size are agreed for your order.</p>
                </div>
                <aside class="kb-quote" id="quote" aria-labelledby="kb-quote-title">
                    <p class="product-eyebrow">Tell us what you need</p>
                    <h2 id="kb-quote-title">Get your wholesale bag quote</h2>
                    <p>Leave your email and estimated quantity. Our team will review your enquiry and contact you as soon as possible.</p>
                    <form id="kb-quote-form" aria-describedby="kb-privacy" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                        <input type="hidden" name="action" value="custom_box_quote_form"><input type="hidden" name="quote_source" value="website_email_popup"><input type="hidden" name="product_name" value="Bread, bakery and SOS paper bag wholesale enquiry"><input type="hidden" name="current_page_url" value="<?php echo esc_url(vpn_kraft_category_url()); ?>">
                        <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); ?>
                        <label for="kb-email">Email <span aria-hidden="true">*</span><input id="kb-email" name="email" type="email" required autocomplete="email" inputmode="email" maxlength="254" placeholder="you@company.com"></label>
                        <div class="kb-form-row">
                            <label for="kb-quantity">Estimated quantity <span aria-hidden="true">*</span><select id="kb-quantity" name="quantity" required><option value="">Select quantity</option><?php foreach (array('1,000–4,999', '5,000–9,999', '10,000–49,999', '50,000–99,999', '100,000–499,999', '500,000 or more') as $quantity) : ?><option><?php echo esc_html($quantity); ?></option><?php endforeach; ?></select></label>
                            <label for="kb-country">Delivery country <span>Optional</span><?php if ($countries) : ?><select id="kb-country" name="country" autocomplete="country-name"><option value="">Select country</option><?php foreach ($countries as $country) : ?><option><?php echo esc_html($country); ?></option><?php endforeach; ?></select><?php else : ?><input id="kb-country" name="country" autocomplete="country-name" maxlength="80"><?php endif; ?></label>
                        </div>
                        <label for="kb-style">Bag style<select id="kb-style" name="style"><option value="Not sure yet — help me choose">Not sure yet — help me choose</option><optgroup label="SOS / square-bottom bags"><option value="SOS / square-bottom bags">SOS / square-bottom bags</option><?php foreach ($choices as $item) : if ('sos' !== $item['group']) { continue; } ?><option><?php echo esc_html($item['label']); ?></option><?php endforeach; ?></optgroup><optgroup label="Bread / baguette bags"><option value="Bread / baguette bags">Bread / baguette bags</option><?php foreach ($choices as $item) : if ('bread' !== $item['group']) { continue; } ?><option><?php echo esc_html($item['label']); ?></option><?php endforeach; ?></optgroup></select></label>
                        <details class="kb-details"><summary>Add size or project details <span>Optional</span></summary><div><label for="kb-size">Size &amp; unit<input id="kb-size" name="size" maxlength="100" placeholder="Width × gusset × height, in mm, cm or inches"></label><label for="kb-message">Project details<textarea id="kb-message" name="message" rows="3" maxlength="400" placeholder="Product, print colors, quantity per design and target delivery date"></textarea></label></div></details>
                        <div class="kb-guard" aria-hidden="true"><label for="kb-guard">Leave blank<input id="kb-guard" name="vpn_quote_guard" autocomplete="off" tabindex="-1"></label></div>
                        <div id="kb-status" class="kb-status" role="status" aria-live="polite" hidden></div>
                        <button class="btn-primary kb-submit" type="submit">Request my bag quote <span aria-hidden="true">→</span></button>
                        <p class="kb-privacy" id="kb-privacy">By submitting, you agree to be contacted about this enquiry. <a href="<?php echo esc_url($privacy); ?>">Privacy policy</a>.</p>
                        <noscript><p>Your email can be submitted without JavaScript. Please include your quantity, bag style and destination in Project details.</p></noscript>
                    </form>
                    <div class="kb-success" id="kb-success" hidden tabindex="-1"><span class="kb-success-mark" aria-hidden="true">✓</span><p>Thank you for your enquiry. Our team will review your bag requirements and contact you as soon as possible.</p><p class="kb-success-email"></p><a class="kb-link" href="#bag-styles">Continue exploring bag styles <span aria-hidden="true">→</span></a></div>
                </aside>
            </div>
        </div>
    </section>
    <nav class="kb-jump" aria-label="Explore bakery packaging"><div class="container"><a href="#sos-bags">SOS bags</a><a href="#bread-bags">Bread &amp; baguette bags</a><a href="#order-details">MOQ &amp; production</a><a href="#kraft-workshop">Inside our workshop</a><a href="#bakery-faq">FAQs</a></div></nav>
    <section class="kb-section" id="bag-styles" aria-labelledby="kb-styles-title"><div class="container">
        <div class="kb-section-heading"><p class="product-eyebrow">Choose by what you pack</p><h2 id="kb-styles-title">Two bag families. Made around your product.</h2><p>Start with the opening, base and filled product. Then review the paper and printed sample with your logo.</p></div>
        <?php foreach (array('sos' => array('sos-bags', 'SOS & square-bottom kraft bags', 'For bakery counters, grocery and takeaway packing. Side gussets and a flat base open for filling; this group has no handles.'), 'bread' => array('bread-bags', 'Paper bread bags, baguette sleeves & window bags', 'For loaves, long baguettes and bakery displays. Choose a flat or gusseted format, with a window where specified.')) as $family => $group) : ?>
        <div class="kb-family" id="<?php echo esc_attr($group[0]); ?>"><div class="kb-family-heading"><div><h3><?php echo esc_html($group[1]); ?></h3><p><?php echo esc_html($group[2]); ?></p></div><a class="kb-link" href="#quote" data-kb-style="<?php echo 'sos' === $family ? 'SOS / square-bottom bags' : 'Bread / baguette bags'; ?>">Quote this type <span aria-hidden="true">→</span></a></div>
            <div class="kb-products <?php echo 'bread' === $family ? 'kb-products--four' : ''; ?>">
                <?php foreach ($choices as $item) : if ($family !== $item['group']) { continue; } ?>
                <article class="kb-product" data-kb-product="<?php echo esc_attr($item['product']->get_slug()); ?>"><a class="kb-product-image" href="<?php echo esc_url($item['product']->get_permalink()); ?>"><?php echo vpn_kraft_bakery_image($item); ?></a><div class="kb-product-body"><h4><a href="<?php echo esc_url($item['product']->get_permalink()); ?>"><?php echo esc_html($item['label']); ?></a></h4><p><?php echo esc_html($item['description']); ?></p><div class="kb-product-actions"><a href="<?php echo esc_url($item['product']->get_permalink()); ?>">View details</a><a href="#quote" data-kb-style="<?php echo esc_attr($item['label']); ?>">Get quote <span aria-hidden="true">→</span></a></div></div></article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div></section>
    <section class="kb-section kb-order" id="order-details" aria-labelledby="kb-order-title"><div class="container">
        <div class="kb-section-heading"><p class="product-eyebrow">Plan your wholesale order</p><h2 id="kb-order-title">Clear specifications before bulk production</h2><p>For distributors, multi-site bakeries and repeat orders, include quantity per size and artwork, estimated usage and proposed delivery batches.</p></div>
        <dl class="kb-order-facts"><div><dt>General minimum order</dt><dd>1,000 <span>bags</span></dd><dd class="kb-fact-note">Confirm the quantity per size and design in your quotation.</dd></div><div><dt>Custom sampling</dt><dd>7–15 <span>days</span></dd><dd class="kb-fact-note">Depending on the order. Sample fees depend on the design.</dd></div><div><dt>Production after sample approval</dt><dd>7–45 <span>days</span></dd><dd class="kb-fact-note">Depending on the order. Shipping time is separate.</dd></div></dl>
        <div class="kb-spec-grid"><div><h3>What determines your bag quote?</h3><ul><li>Finished width × gusset × height, with the unit</li><li>Paper choice, print colors and artwork coverage</li><li>Quantity per size and design, including repeat supply</li><li>Window, lining or grease-resistance requirements</li><li>Delivery destination and required receiving date</li></ul><a class="kb-link" href="<?php echo esc_url(vpn_kraft_reference_url('paper-bag-sizes')); ?>">How to measure your paper bag <span aria-hidden="true">→</span></a></div><div><h3>Match the paper to your bakery product</h3><p>Tell us whether the food will touch the paper directly, its packing temperature, grease or moisture exposure and how long it will stay packed. Paper, inks, adhesive and any window or liner are reviewed for that use and destination.</p><p>Review a filled sample for fit, closure and appearance. Brown or white kraft can be considered with your artwork; the finished specification confirms the paper and any performance requirements.</p></div></div>
        <div class="kb-refund"><strong>50% sample-fee refund</strong><p>Orders over 5,000 pieces qualify for a refund of 50% of the sample fee. Design support is available.</p><a class="kb-link" href="#quote">Discuss a sample <span aria-hidden="true">→</span></a></div>
    </div></section>
    <?php get_template_part('template-parts/woocommerce/kraft-workshop', null, array('quote_url' => '#quote')); ?>
    <section class="kb-section" id="bakery-faq" aria-labelledby="kb-faq-title"><div class="container kb-faq-wrap"><div class="kb-section-heading"><p class="product-eyebrow">Before you order</p><h2 id="kb-faq-title">Bread &amp; bakery bag questions</h2></div><div class="kb-faqs"><?php foreach ($data['faqs'] as $faq) : ?><details><summary><?php echo esc_html($faq['question']); ?><span aria-hidden="true">+</span></summary><p><?php echo esc_html($faq['answer']); ?></p></details><?php endforeach; ?></div>
        <div class="kb-bottom-cta"><div><h3>Tell us what your bag needs to carry.</h3><p>Your email, estimated quantity and a product reference are enough to start the conversation.</p></div><a class="btn-primary" href="#quote">Get a wholesale quote <span aria-hidden="true">→</span></a></div>
        <div class="kb-more"><p>Looking for retail carriers or another kraft format?</p><a class="kb-link" href="<?php echo esc_url(add_query_arg('view', 'catalog', vpn_kraft_category_url())); ?>">Browse the complete kraft bag collection <span aria-hidden="true">→</span></a><a class="kb-link" href="<?php echo esc_url(home_url('/custom-paper-bags-manufacturer/')); ?>">Explore all custom paper bag options <span aria-hidden="true">→</span></a></div>
    </div></section>
</main>
<?php get_footer(); ?>
