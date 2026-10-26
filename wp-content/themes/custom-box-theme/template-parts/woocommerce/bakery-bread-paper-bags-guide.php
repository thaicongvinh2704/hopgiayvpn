<?php
/** Buying guidance for the bakery application, using the existing category design. */
defined('ABSPATH') || exit;
$data = vpn_bakery_category_data();
$quote = home_url('/custom-paper-bags-manufacturer/#quote');
$product_url = static function ($slug) { return home_url('/product/' . $slug . '/'); };
?>
<section class="corrugated-mailer-guide vpn-bakery-guide" aria-labelledby="bakery-guide-title">
    <div class="container">
        <div class="corrugated-mailer-guide-intro" id="bakery-bag-selection">
            <p class="product-eyebrow">Choose for the food you pack</p>
            <h2 id="bakery-guide-title">Bread bags, pastry bags or a carrier?</h2>
            <p class="corrugated-mailer-answer">Bakery paper bags cover several different jobs: holding a single baguette, packing loose pastries at the counter, displaying a loaf through a window, or carrying a boxed order home. Start with that job, then agree the size, paper, printing and food-contact requirements. A bag for carrying a bakery box has different requirements from paper that touches a croissant directly.</p>
            <div class="corrugated-mailer-facts">
                <article><h3>Loaves and baguettes</h3><p>Choose the length and opening around the bread. A <a href="<?php echo esc_url($product_url('custom-printed-baguette-paper-bags')); ?>">baguette sleeve</a> suits a long format, while a <a href="<?php echo esc_url($product_url('custom-kraft-side-gusset-bread-bags')); ?>">side-gusset bag</a> provides room for a wider loaf.</p></article>
                <article><h3>Pastries and counter service</h3><p>Compare flat bags and <a href="<?php echo esc_url($product_url('custom-square-bottom-bakery-paper-bags')); ?>">square-bottom bags</a> for the number of items in each order. For buttery pastries, specify grease resistance separately from the bag shape.</p></article>
                <article><h3>Takeaway and boxed orders</h3><p>A <a href="<?php echo esc_url($product_url('custom-twisted-handle-bakery-paper-bags')); ?>">handled bakery carrier</a> can hold wrapped items or boxes. Check the base against the box footprint and review the filled weight and handle attachments.</p></article>
            </div>
        </div>

        <div class="corrugated-mailer-table-wrap" role="region" aria-labelledby="bakery-style-caption" tabindex="0">
            <table>
                <caption id="bakery-style-caption">Compare the bakery bag formats in our collection</caption>
                <thead><tr><th scope="col">Bag format</th><th scope="col">Typical starting use</th><th scope="col">Confirm before ordering</th></tr></thead>
                <tbody>
                    <tr><th scope="row">Long baguette bag</th><td data-label="Typical starting use">French bread and long loaves</td><td data-label="Confirm before ordering">Bread length, opening clearance, gusset and space for folding the top</td></tr>
                    <tr><th scope="row">Flat or side-gusset bread bag</th><td data-label="Typical starting use">Loaves, rolls and counter orders</td><td data-label="Confirm before ordering">Filled width and depth, seam position and packing method</td></tr>
                    <tr><th scope="row">Paper bread bag with window</th><td data-label="Typical starting use">Showing the loaf or pastry inside</td><td data-label="Confirm before ordering">Window size, position, material and any ventilation requirement</td></tr>
                    <tr><th scope="row">Square-bottom / SOS bag</th><td data-label="Typical starting use">Multiple items packed at the bakery counter</td><td data-label="Confirm before ordering">Base dimensions, opening, filled weight and bottom construction</td></tr>
                    <tr><th scope="row">Greaseproof bakery bag</th><td data-label="Typical starting use">Pastries where grease marks are a concern</td><td data-label="Confirm before ordering">Barrier specification, food type, contact time and packing temperature</td></tr>
                    <tr><th scope="row">Twisted or die-cut handle bag</th><td data-label="Typical starting use">Carrying wrapped products or bakery boxes</td><td data-label="Confirm before ordering">Box fit, hand clearance, reinforcement and filled carrying load</td></tr>
                </tbody>
            </table>
        </div>

        <div class="corrugated-mailer-guide-grid">
            <div>
                <h2>Paper, grease resistance and display windows</h2>
                <p>Brown kraft and white paper offer different backgrounds for your artwork. Compare a printed sample when the logo or brand colors need a close match. Paper weight, surface, folds and seams should be chosen together; a thicker sheet alone does not establish how a finished bag will perform.</p>
                <p>For croissants, doughnuts and other buttery products, review the <a href="<?php echo esc_url($product_url('custom-greaseproof-bakery-paper-bags')); ?>">greaseproof bakery bag</a> as a starting point. Agree the required barrier and trial it with your food. Grease resistance, moisture resistance and food-contact suitability are separate requirements.</p>
                <p>A <a href="<?php echo esc_url($product_url('custom-paper-bread-bags-with-window')); ?>">window bread bag</a> lets the customer see the product. Specify the window panel with the printing layout, seam and closure. If you pack bread while it is warm, include the packing temperature and expected cooling time so ventilation and condensation can be considered in the sample review.</p>
                <p>Looking specifically for kraft across bakery and retail uses? Compare the broader <a href="<?php echo esc_url(vpn_kraft_category_url()); ?>">custom kraft paper bag collection</a>. This bakery collection brings together formats by what they pack, including window and greaseproof options.</p>
            </div>
            <aside class="corrugated-mailer-rfq-card" id="bakery-quote" aria-labelledby="bakery-quote-title">
                <p class="product-eyebrow">For wholesale and repeat orders</p>
                <h2 id="bakery-quote-title">Request your bakery bag quote</h2>
                <p>Leave your email with the details below. Our team will review the requirement and contact you about the next steps.</p>
                <ol>
                    <li>Bread, pastry or boxed product you will pack</li>
                    <li>Bag dimensions and filled product weight</li>
                    <li>Quantity for each size and artwork</li>
                    <li>Logo, print colors and window preference</li>
                    <li>Food-contact and grease-resistance requirements</li>
                    <li>Delivery destination and required receiving date</li>
                </ol>
                <p><strong>General MOQ: 1,000 bags.</strong> The quotation confirms the minimum for your specification. For large-volume supply, include monthly usage and planned delivery batches.</p>
                <a class="btn-primary" href="<?php echo esc_url($quote); ?>">Send Your Bakery Bag Brief</a>
                <p class="vpn-bakery-small">Not sure about the size? Send your product dimensions and a reference photo to start.</p>
            </aside>
        </div>

        <div class="corrugated-mailer-guide-grid corrugated-mailer-guide-grid-secondary" id="bakery-specification">
            <div>
                <h2>Measure the filled bag, then check a sample</h2>
                <p>For a gusseted bag, send <strong>width × opened gusset depth × height</strong> and state the units. For a flat bag, send width × height. Include any extra height needed to fold or close the top. Measure the bread or filled box at its widest points, rather than copying the outside dimensions of an empty bag.</p>
                <p>Check that the opening allows easy packing without crushing the product. For a handled carrier, allow room above the box for a comfortable grip. The <a href="<?php echo esc_url(vpn_kraft_reference_url('paper-bag-sizes')); ?>">paper bag size guide</a> explains the measurements to send.</p>
                <h3>Plan the print around the folds</h3>
                <p>Supply your logo or artwork file and identify the printed sides, colors and coverage. Keep important wording clear of the base folds, gussets, window and handle areas. The sample review should cover artwork placement, color on the chosen paper and readability after the bag is filled.</p>
            </div>
            <div id="bakery-food-contact">
                <h2>Specify direct food contact for your destination</h2>
                <p>Tell us whether food touches the paper directly or is wrapped first. Include the food type, temperature at packing, grease or moisture exposure and expected time in the bag. Review the paper, inks, adhesive and any window or liner for that intended use.</p>
                <p>Request relevant material specifications and any supporting food-contact documentation needed for your destination before approval. Terms such as “kraft,” “white” or “greaseproof” do not replace that review. Agree any testing or documentation required for the specific order.</p>
                <p>For background, the <a href="https://www.fda.gov/food/packaging-food-contact-substances-fcs/food-types-conditions-use-food-contact-substances" target="_blank" rel="noopener">FDA distinguishes food types and conditions of use</a>, while the <a href="https://food.ec.europa.eu/food-safety/chemical-safety/food-contact-materials/general-information-and-contacts_en" target="_blank" rel="noopener">European Commission explains the scope of food-contact materials</a>. These references help frame the brief; they are not certification of the products shown here.</p>
            </div>
        </div>

        <div class="corrugated-mailer-table-wrap" role="region" aria-labelledby="bakery-order-caption" tabindex="0" id="bakery-order-process">
            <table>
                <caption id="bakery-order-caption">From design review to a bulk bakery bag order</caption>
                <thead><tr><th scope="col">Stage</th><th scope="col">What is agreed</th><th scope="col">Timing or terms</th></tr></thead>
                <tbody>
                    <tr><th scope="row">Project review</th><td data-label="What is agreed">Food, dimensions, paper, print, quantity and destination; design support is available</td><td data-label="Timing or terms">Quotation based on your specification</td></tr>
                    <tr><th scope="row">Custom sample</th><td data-label="What is agreed">Fit, artwork, folds, seams and any window or handles</td><td data-label="Timing or terms">7–15 days, depending on the order; sample fee depends on the design</td></tr>
                    <tr><th scope="row">Sample-fee refund</th><td data-label="What is agreed">Refund of 50% of the sample fee</td><td data-label="Timing or terms">For orders over 5,000 pieces</td></tr>
                    <tr><th scope="row">Production after sample approval</th><td data-label="What is agreed">Manufacture to the approved specification, with quality checks at each stage</td><td data-label="Timing or terms">7–45 days, depending on the order</td></tr>
                    <tr><th scope="row">Packing and shipment</th><td data-label="What is agreed">Cartons or shrink-wrapped bundles; packing and delivery arrangements agreed for the destination</td><td data-label="Timing or terms">Transport time is separate from production</td></tr>
                </tbody>
            </table>
        </div>
        <div class="vpn-bakery-repeat">
            <h2>Working with a bakery bag supplier for repeat orders</h2>
            <p>For bakery chains and distributors, share the quantity for each size and design, expected usage and preferred shipment batches. If you use several bag formats, identify which are for direct food contact and which carry boxed products. This gives the quotation a clear scope and helps keep the approved paper, artwork and packing specification consistent across repeat orders.</p>
            <a class="btn-outline" href="<?php echo esc_url($quote); ?>">Discuss Your Supply Requirements</a>
        </div>
    </div>
</section>
<?php
get_template_part('template-parts/woocommerce/kraft-workshop', null, array(
    'quote_url' => $quote,
    'order_intro' => 'For a bakery bag order, we work from your food product, artwork and agreed specification. Use the sample review to check:',
    'order_cta' => 'Discuss Your Bakery Bag Order',
));
?>
<section class="corrugated-mailer-guide vpn-bakery-guide vpn-bakery-faq-section" aria-labelledby="bakery-faq-title">
    <div class="container">
        <div class="corrugated-mailer-faq" id="bakery-faq">
            <p class="product-eyebrow">Before you place an order</p>
            <h2 id="bakery-faq-title">Bakery & bread paper bag questions</h2>
            <div class="corrugated-mailer-faq-list">
                <?php foreach ($data['faqs'] as $faq) : ?>
                    <details><summary><?php echo esc_html($faq['question']); ?></summary><p><?php echo esc_html($faq['answer']); ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="corrugated-mailer-source-note">
            <p>The product images show options from our catalog. Final materials, dimensions and performance are agreed for your order. Explore the full <a href="<?php echo esc_url(home_url('/products/paper-bags-with-logo/')); ?>">paper bag collection</a> or learn about our <a href="<?php echo esc_url(home_url('/custom-paper-bags-manufacturer/')); ?>">custom paper bag manufacturing service</a> for other applications.</p>
        </div>
    </div>
</section>
<section class="about-cta vpn-bakery-bottom-cta">
    <div class="container"><div class="about-content">
        <h2>Tell us what your bakery needs to pack</h2>
        <p>Send your email, product dimensions and estimated quantity. Our team in Vietnam will review the bag format, printing and sample requirements with you before preparing the next steps.</p>
        <div class="about-buttons">
            <a class="btn-primary" href="<?php echo esc_url($quote); ?>">Request Your Bag Quote</a>
            <a class="btn-outline" href="<?php echo esc_url(home_url('/about/')); ?>">Meet VPN Packaging</a>
        </div>
    </div></div>
</section>
