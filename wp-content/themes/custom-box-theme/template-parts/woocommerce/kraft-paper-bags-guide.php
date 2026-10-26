<?php
/** Product selection and order guidance using the existing VPN kraft bag range. */
defined('ABSPATH') || exit;
$data = vpn_kraft_category_data();
$quote = home_url('/custom-paper-bags-manufacturer/#quote');
$product_url = static function ($slug) { return home_url('/product/' . $slug . '/'); };
get_template_part('template-parts/woocommerce/kraft-workshop');
?>
<section class="corrugated-mailer-guide vpn-kraft-guide" aria-labelledby="kraft-guide-title">
    <div class="container">
        <div class="corrugated-mailer-guide-intro" id="kraft-bag-styles">
            <p class="product-eyebrow">Choose the format for your product</p>
            <h2 id="kraft-guide-title">One paper family, several ways to pack</h2>
            <p class="corrugated-mailer-answer">Kraft describes the paper, while SOS, flat and handled bags describe the construction. Start with how your product will be packed and carried. Then choose brown or white kraft, dimensions and printing around that use.</p>
            <div class="corrugated-mailer-facts">
                <article><h3>SOS and grocery bags</h3><p>An open top, expandable sides and a flat base make a practical starting point for counter packing. Compare the <a href="<?php echo esc_url($product_url('custom-brown-kraft-grocery-bags')); ?>">brown kraft grocery bag</a> and <a href="<?php echo esc_url($product_url('custom-square-bottom-bakery-paper-bags')); ?>">square-bottom bakery bag</a>.</p></article>
                <article><h3>Bread and bakery formats</h3><p>Match the opening and depth to the loaf or pastry. Explore <a href="<?php echo esc_url($product_url('custom-printed-baguette-paper-bags')); ?>">baguette bags</a>, <a href="<?php echo esc_url($product_url('custom-printed-flat-bread-paper-bags')); ?>">flat bread bags</a> and <a href="<?php echo esc_url($product_url('custom-paper-bread-bags-with-window')); ?>">window bags</a>. <?php if (function_exists('vpn_bakery_category_url') && get_term_by('slug', 'custom-bakery-bread-paper-bags', 'product_cat')) : ?>Compare these with the wider <a href="<?php echo esc_url(vpn_bakery_category_url()); ?>">bakery and bread bag range</a>.<?php endif; ?></p></article>
                <article><h3>Handled carriers</h3><p>For products customers carry by a top grip, compare <a href="<?php echo esc_url($product_url('custom-flat-handle-kraft-paper-bags')); ?>">flat paper handles</a>, <a href="<?php echo esc_url($product_url('custom-twisted-handle-kraft-paper-bags')); ?>">twisted paper handles</a> and <a href="<?php echo esc_url($product_url('custom-die-cut-handle-kraft-paper-bags')); ?>">die-cut handholds</a>.</p></article>
            </div>
        </div>

        <div class="corrugated-mailer-guide-grid">
            <div>
                <h2>Brown or white kraft with your artwork</h2>
                <p>Brown kraft gives the artwork a warm paper background. White kraft provides a lighter printing surface. Compare an actual printed sample when color matching matters: the paper shade and surface affect how ink looks. The <a href="<?php echo esc_url($product_url('custom-white-kraft-paper-bags-twisted-handles')); ?>">white kraft carrier</a> is one existing option alongside the brown range.</p>
                <p>Discuss your logo, artwork coverage, print colors and any window or reinforcement with the bag specification. A reference image shows a design direction; it does not establish paper weight, carrying capacity or food-contact suitability. For the differences between surfaces and finishes, see our <a href="<?php echo esc_url(vpn_kraft_reference_url('kraft-vs-coated-paper-bags')); ?>">kraft versus coated paper bag guide</a>.</p>
                <h3>Size for the filled product</h3>
                <p>Send width × gusset depth × height, the product dimensions and packed weight. Include the opening clearance, how the top will close and whether a box or tray sits inside. The <a href="<?php echo esc_url(vpn_kraft_reference_url('paper-bag-sizes')); ?>">paper bag size guide</a> explains what to measure. Approve the fit and carrying requirements with a filled sample.</p>
            </div>
            <aside class="corrugated-mailer-rfq-card" aria-labelledby="kraft-quote-title">
                <h2 id="kraft-quote-title">Planning a wholesale order?</h2>
                <p>Leave your email and send the details below so our team can review the bag and quotation.</p>
                <ol>
                    <li>Bag style, size and packed product weight</li>
                    <li>Quantity for each size and artwork</li>
                    <li>Logo file, print colors and paper preference</li>
                    <li>Food contact, grease or moisture requirements</li>
                    <li>Delivery destination and required receiving date</li>
                </ol>
                <p>For repeat orders, include your estimated usage and proposed delivery batches. The general MOQ is 1,000 bags; the quotation confirms the scope for your order.</p>
                <a class="btn-primary" href="<?php echo esc_url($quote); ?>">Send Your Bag Brief</a>
            </aside>
        </div>

        <div class="corrugated-mailer-table-wrap" role="region" aria-labelledby="kraft-selection-caption" tabindex="0">
            <table>
                <caption id="kraft-selection-caption">Compare kraft bag styles before requesting a quote</caption>
                <thead><tr><th scope="col">Style</th><th scope="col">Typical starting use</th><th scope="col">What to confirm</th></tr></thead>
                <tbody>
                    <tr><th scope="row">SOS / square bottom, no handles</th><td>Grocery, bakery counters and packaged takeaway items</td><td>Base dimensions, opening, load, paper and bottom seam</td></tr>
                    <tr><th scope="row">Flat or side-gusset bread bag</th><td>Loaves, baguettes and bakery counter orders</td><td>Bread length, expansion, closure and food-contact requirements</td></tr>
                    <tr><th scope="row">Window bag</th><td>Bakery items where product visibility is useful</td><td>Window material, position, adhesion and actual contact conditions</td></tr>
                    <tr><th scope="row">Flat or twisted paper handle</th><td>Retail purchases and sealed takeaway containers</td><td>Handle attachment, grip clearance, filled load and base fit</td></tr>
                    <tr><th scope="row">Die-cut handle</th><td>Compact retail goods or appropriately packed bakery items</td><td>Handhold clearance, reinforcement and lifting load</td></tr>
                </tbody>
            </table>
        </div>

        <div class="corrugated-mailer-guide-grid corrugated-mailer-guide-grid-secondary">
            <div>
                <h2>Specify the paper for food and carrying conditions</h2>
                <p>For bread, pastries or direct food contact, tell us what touches the bag, the food temperature and the expected packing time. Grease resistance, liners and window materials are separate choices. They should be reviewed with inks and adhesives for the intended application and destination.</p>
                <p>For heavier products, paper weight is only one part of the decision. Bag dimensions, folds, base gluing and handle attachments also matter. Our guide to <a href="<?php echo esc_url(home_url('/how-to-make-paper-bags-stronger/')); ?>">making paper bags stronger</a> explains the construction points to review.</p>
                <p>If recycled content or certification is required, include it in the brief so the specific paper and supporting documents can be confirmed. The color or appearance of a bag does not establish those claims.</p>
            </div>
            <div>
                <h2>From a custom sample to production</h2>
                <p>VPN provides design support and makes samples to the agreed design. Sampling takes 7–15 days depending on the order. Sample fees depend on the design; orders over 5,000 pieces qualify for a refund of 50% of the sample fee.</p>
                <p>Production takes 7–45 days after sample approval, depending on the order. Shipping is scheduled separately. Quality is checked at each stage, and finished bags can be packed in cartons or shrink-wrapped bundles according to the order requirements.</p>
                <p>Learn more about our <a href="<?php echo esc_url(home_url('/about/')); ?>">paper packaging business and factory</a>, or use the <a href="<?php echo esc_url($quote); ?>">paper bag manufacturer enquiry form</a> to share your email and project details.</p>
            </div>
        </div>

        <section class="corrugated-mailer-faq" aria-labelledby="kraft-faq-title">
            <h2 id="kraft-faq-title">Questions about custom kraft bags</h2>
            <div class="corrugated-mailer-faq-list">
                <?php foreach ($data['faqs'] as $faq) : ?>
                    <details><summary><?php echo esc_html($faq['question']); ?></summary><p><?php echo esc_html($faq['answer']); ?></p></details>
                <?php endforeach; ?>
            </div>
        </section>
        <div class="corrugated-mailer-source-note"><p>Explore the full <a href="<?php echo esc_url(home_url('/products/paper-bags-with-logo/')); ?>">paper bags with logo collection</a> for other papers and finishes. These product references are starting points for custom orders; final materials, dimensions and performance are agreed in the specification.</p></div>
    </div>
</section>
