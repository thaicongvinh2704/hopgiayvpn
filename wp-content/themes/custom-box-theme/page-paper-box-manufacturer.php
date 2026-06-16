<?php
/**
 * Template Name: Paper Box Manufacturer Landing Page
 *
 * English B2B landing page for paper box manufacturing quote requests.
 */

defined('ABSPATH') || exit;

add_filter('language_attributes', function () {
    return 'lang="en-US"';
});

get_header();

$theme_uri = get_template_directory_uri();

$trust_items = array(
    array('fa-solid fa-tags', 'Factory-direct competitive pricing'),
    array('fa-solid fa-pen-ruler', 'Free design support'),
    array('fa-solid fa-truck-fast', '3-7 day production support'),
    array('fa-solid fa-industry', 'Capacity up to 3 million boxes/month'),
    array('fa-solid fa-globe', 'International buyer support'),
);

$box_types = array(
    'Rigid Boxes',
    'Carton Boxes',
    'Magnetic Boxes',
    'Drawer Boxes',
    'Logo Paper Bags',
    'Cosmetic Packaging',
    'Other Custom Packaging',
);

$quote_status = isset($_GET['quote_status']) ? sanitize_text_field(wp_unslash($_GET['quote_status'])) : '';
$quote_messages = array(
    'success' => 'Thank you. Your quote request has been sent successfully.',
    'failed'  => 'Sorry, we could not send your request right now. Please try again later.',
    'missing' => 'Please fill in your name, email, and product information.',
    'invalid' => 'The form session expired. Please refresh the page and try again.',
    'file'    => 'Please upload a valid artwork file under 10MB.',
);

$render_paper_box_quote_form = function ($form_id, $title) use ($box_types, $quote_status, $quote_messages) {
    ?>
    <form class="pbm-quote-form" id="<?php echo esc_attr($form_id); ?>" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="custom_box_quote_form">
        <input type="hidden" name="product_name" value="Paper Box Manufacturer Landing Page">
        <input type="hidden" name="width" value="">
        <input type="hidden" name="depth" value="">
        <input type="hidden" name="unit" value="mm">
        <input type="hidden" name="printing_option" value="To be advised">
        <input type="hidden" name="finishing_option" value="To be advised">
        <input type="hidden" name="country" value="">
        <input type="hidden" name="phone" value="">
        <input type="hidden" name="custom_box_quote_nonce" value="<?php echo esc_attr(wp_create_nonce('custom_box_quote_form')); ?>">

        <div class="pbm-form-head">
            <span>Factory quotation</span>
            <h2><?php echo esc_html($title); ?></h2>
        </div>

        <?php if ($quote_status && isset($quote_messages[$quote_status])) : ?>
            <p class="pbm-form-message pbm-form-message-<?php echo esc_attr($quote_status); ?>"><?php echo esc_html($quote_messages[$quote_status]); ?></p>
        <?php endif; ?>

        <div class="pbm-form-grid">
            <label>
                <span>Full Name</span>
                <input type="text" name="full_name" autocomplete="name" required>
            </label>
            <label>
                <span>Email</span>
                <input type="email" name="email" autocomplete="email" required>
            </label>
            <label>
                <span>Company</span>
                <input type="text" name="company" autocomplete="organization">
            </label>
            <label>
                <span>Box Type</span>
                <select name="stock_option" required>
                    <option value="">Select box type</option>
                    <?php foreach ($box_types as $box_type) : ?>
                        <option value="<?php echo esc_attr($box_type); ?>"><?php echo esc_html($box_type); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Size (L x W x H)</span>
                <input type="text" name="length" placeholder="Example: 220 x 160 x 70 mm">
            </label>
            <label>
                <span>Estimated Quantity</span>
                <input type="text" name="quantity" placeholder="Example: 5,000 pcs">
            </label>
        </div>
        <label>
            <span>Notes</span>
            <textarea name="message" rows="4" placeholder="Material, paper thickness, printing, finishing, delivery country, deadline..."></textarea>
        </label>
        <button class="pbm-submit" type="submit">Get Quote Now</button>
    </form>
    <?php
};

$benefits = array(
    array('fa-solid fa-building', 'Direct factory production with clearer cost and lead-time control.'),
    array('fa-solid fa-swatchbook', 'Structure, paper, insert, and finishing advice based on your product.'),
    array('fa-solid fa-print', 'Offset printing, digital printing, foil stamping, UV, and lamination support.'),
    array('fa-solid fa-clipboard-check', 'Quality checking before export-ready carton packing.'),
);

$steps = array(
    'Send your request',
    'Choose structure and materials',
    'Approve sample and quotation',
    'Start mass production',
    'Quality check and export packing',
);

$materials = array(
    array('SBS', 'Smooth white paperboard for cosmetic boxes, retail cartons, and sharp full-color printing.'),
    array('Kraft', 'Natural paper feel for eco-positioned packaging, bakery boxes, gifts, and simple retail packs.'),
    array('Rigid', 'Thick board for premium gift boxes, magnetic boxes, drawer boxes, and luxury sets.'),
    array('Cardboard', 'Flexible material for custom paper boxes that need a balance of strength and cost.'),
    array('Corrugated', 'Protective fluted board for shipping, ecommerce packaging, and export-ready cartons.'),
);

$factory_images = array(
    array($theme_uri . '/assets/images/factory-team-and-production.jpg', 'Factory team supporting custom paper box production and order checking.'),
    array($theme_uri . '/assets/images/anh-nha-may-1.jpg', 'Paper box and packaging production area for B2B custom orders.'),
    array($theme_uri . '/assets/images/anh-nha-may-2.png', 'Finishing, assembly, packing, and delivery preparation workflow.'),
    array($theme_uri . '/assets/images/anh-nha-may-fly.png', 'Factory space supporting domestic and export packaging orders.'),
);
?>

<style>
    .pbm-page { --pbm-blue: #063f7a; --pbm-blue-hover: #0b62ad; --pbm-ink: #102033; --pbm-muted: #5b6675; --pbm-line: #dfe7ef; --pbm-soft: #f4f8fb; color: var(--pbm-ink); font-family: inherit; }
    .pbm-page * { box-sizing: border-box; }
    .pbm-wrap { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }
    .pbm-section { padding: 72px 0; }
    .pbm-section-soft { background: var(--pbm-soft); }
    .pbm-eyebrow { display: inline-flex; align-items: center; gap: 8px; color: var(--pbm-blue); font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 0; margin-bottom: 12px; }
    .pbm-page h1, .pbm-page h2, .pbm-page h3, .pbm-page p { margin-top: 0; }
    .pbm-page h1 { font-size: clamp(38px, 5vw, 64px); line-height: 1.02; letter-spacing: 0; margin-bottom: 20px; color: #fff; }
    .pbm-page h2 { font-size: clamp(28px, 3vw, 42px); line-height: 1.12; margin-bottom: 16px; }
    .pbm-page h3 { font-size: 20px; line-height: 1.25; margin-bottom: 8px; }
    .pbm-page p { color: var(--pbm-muted); line-height: 1.7; }
    .pbm-hero { background: linear-gradient(120deg, rgba(6, 63, 122, .94), rgba(7, 47, 89, .9)), url("<?php echo esc_url($theme_uri . '/assets/images/banner-landing-page.webp'); ?>") center/cover; padding: 80px 0 50px; }
    .pbm-hero-grid { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(380px, .82fr); gap: 42px; align-items: center; }
    .pbm-hero-copy .pbm-eyebrow { color: #cfe8ff; }
    .pbm-hero-copy p { color: #e8f2fb; font-size: 18px; max-width: 680px; }
    .pbm-keywords { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 28px; }
    .pbm-keywords span { border: 1px solid rgba(255,255,255,.35); color: #fff; border-radius: 999px; padding: 9px 13px; font-size: 14px; }
    .pbm-quote-form { background: #fff; border: 1px solid var(--pbm-line); border-radius: 8px; box-shadow: 0 18px 50px rgba(12, 42, 77, .16); padding: 24px; }
    .pbm-form-head span { color: var(--pbm-blue); display: block; font-size: 13px; font-weight: 800; margin-bottom: 6px; text-transform: uppercase; }
    .pbm-form-head h2 { color: var(--pbm-ink); font-size: 24px; margin-bottom: 18px; }
    .pbm-form-grid { display: grid; gap: 14px; grid-template-columns: 1fr 1fr; }
    .pbm-quote-form label { display: block; margin-bottom: 14px; }
    .pbm-quote-form label span { color: #26384d; display: block; font-size: 13px; font-weight: 800; margin-bottom: 7px; }
    .pbm-quote-form input, .pbm-quote-form select, .pbm-quote-form textarea { background: #fff; border: 1px solid #cfd9e3; border-radius: 6px; color: var(--pbm-ink); font: inherit; min-height: 46px; padding: 12px 13px; width: 100%; }
    .pbm-quote-form textarea { resize: vertical; }
    .pbm-submit { align-items: center; background: var(--pbm-blue); border: 0; border-radius: 6px; color: #fff; cursor: pointer; display: inline-flex; font-size: 16px; font-weight: 850; justify-content: center; min-height: 48px; padding: 13px 20px; transition: background .2s ease, transform .2s ease; width: 100%; }
    .pbm-submit:hover { background: var(--pbm-blue-hover); transform: translateY(-1px); }
    .pbm-form-message { border-radius: 6px; font-weight: 750; margin-bottom: 16px; padding: 10px 12px; }
    .pbm-form-message-success { background: #e8f7ef; color: #126136; }
    .pbm-form-message-failed, .pbm-form-message-missing, .pbm-form-message-invalid, .pbm-form-message-file { background: #fff0e8; color: #9a3b10; }
    .pbm-trust { padding: 24px 0; border-bottom: 1px solid var(--pbm-line); background: #fff; }
    .pbm-trust-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; }
    .pbm-trust-item { display: flex; align-items: center; gap: 11px; padding: 14px 10px; border: 1px solid var(--pbm-line); border-radius: 8px; min-height: 76px; }
    .pbm-trust-item i, .pbm-benefit i { color: var(--pbm-blue); font-size: 22px; width: 26px; text-align: center; }
    .pbm-trust-item span { font-weight: 800; font-size: 14px; line-height: 1.35; }
    .pbm-head { max-width: 760px; margin-bottom: 32px; }
    .pbm-mini-btn, .pbm-contact-lines a { color: var(--pbm-blue); font-weight: 800; text-decoration: none; white-space: nowrap; }
    .pbm-industries { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
    .pbm-industries span, .pbm-finish span { border: 1px solid #cfdbe8; background: #fff; border-radius: 999px; padding: 10px 14px; font-weight: 700; color: #253a50; }
    .pbm-split { display: grid; grid-template-columns: minmax(0, .95fr) minmax(0, 1fr); gap: 42px; align-items: start; }
    .pbm-list { list-style: none; padding: 0; margin: 22px 0 0; display: grid; gap: 12px; }
    .pbm-list li { display: flex; gap: 10px; align-items: flex-start; color: var(--pbm-muted); line-height: 1.6; }
    .pbm-list i { color: #0f8a4b; margin-top: 5px; }
    .pbm-benefits { display: grid; gap: 14px; }
    .pbm-benefit { display: flex; gap: 14px; padding: 18px; background: #fff; border: 1px solid var(--pbm-line); border-radius: 8px; }
    .pbm-benefit p { margin-bottom: 0; }
    .pbm-process { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; counter-reset: process; padding: 0; margin: 0; }
    .pbm-process li { list-style: none; border: 1px solid var(--pbm-line); border-radius: 8px; padding: 18px; background: #fff; min-height: 150px; position: relative; }
    .pbm-process li:before { counter-increment: process; content: "0" counter(process); display: inline-flex; width: 42px; height: 42px; border-radius: 50%; align-items: center; justify-content: center; background: var(--pbm-blue); color: #fff; font-weight: 800; margin-bottom: 14px; }
    .pbm-process strong { display: block; line-height: 1.35; }
    .pbm-factory-slider { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(280px, 36%); gap: 18px; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 14px; }
    .pbm-slide { scroll-snap-align: start; border-radius: 8px; overflow: hidden; border: 1px solid var(--pbm-line); background: #fff; }
    .pbm-slide img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; display: block; }
    .pbm-slide figcaption { padding: 14px; color: var(--pbm-muted); line-height: 1.5; }
    .pbm-capacity { margin-top: 18px; padding: 18px; border-left: 4px solid var(--pbm-blue); background: #fff; font-weight: 800; }
    .pbm-material-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }
    .pbm-material { background: #fff; border: 1px solid var(--pbm-line); border-radius: 8px; padding: 18px; }
    .pbm-finish { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
    .pbm-final { background: #072f59; color: #fff; }
    .pbm-final h2, .pbm-final p, .pbm-final span, .pbm-final .pbm-eyebrow { color: #fff; }
    .pbm-contact-lines { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 18px; }
    .pbm-contact-lines a { color: #fff; }
    @media (max-width: 980px) {
        .pbm-hero-grid, .pbm-split { grid-template-columns: 1fr; }
        .pbm-trust-grid, .pbm-process, .pbm-material-grid { grid-template-columns: repeat(2, 1fr); }
        .pbm-factory-slider { grid-auto-columns: minmax(260px, 72%); }
    }
    @media (max-width: 640px) {
        .pbm-wrap { width: min(100% - 24px, 1180px); }
        .pbm-section { padding: 50px 0; }
        .pbm-hero { padding: 56px 0 36px; }
        .pbm-hero-grid, .pbm-trust-grid, .pbm-process, .pbm-material-grid { grid-template-columns: 1fr; }
        .pbm-form-grid { grid-template-columns: 1fr; }
        .pbm-quote-form { padding: 18px; }
    }
</style>

<main class="pbm-page">
    <section class="pbm-hero">
        <div class="pbm-wrap pbm-hero-grid">
            <div class="pbm-hero-copy">
                <span class="pbm-eyebrow">Paper Box Manufacturer in Vietnam</span>
                <h1>Paper Box Manufacturer in Vietnam</h1>
                <p>VPN Paper Box produces custom paper boxes, carton boxes, rigid boxes, magnetic boxes, drawer boxes, and printed paper bags directly from our Vietnam factory.</p>
                <p>We support design, dieline checking, material selection, printing, finishing, bulk production, quality control, and export-ready packing for global B2B buyers looking for a reliable <strong>paper box manufacturer</strong> and <strong>packaging boxes manufacturer</strong>.</p>
                <div class="pbm-keywords">
                    <span>Factory-direct production</span>
                    <span>OEM/ODM packaging</span>
                    <span>Global export support</span>
                </div>
            </div>
            <div>
                <?php $render_paper_box_quote_form('paper-box-quote', 'Get Your Paper Box Quote'); ?>
            </div>
        </div>
    </section>

    <section class="pbm-trust" aria-label="Factory trust points">
        <div class="pbm-wrap pbm-trust-grid">
            <?php foreach ($trust_items as $item) : ?>
                <div class="pbm-trust-item">
                    <i class="<?php echo esc_attr($item[0]); ?>" aria-hidden="true"></i>
                    <span><?php echo esc_html($item[1]); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php get_template_part('template-parts/home/packaging-category-groups'); ?>

    <section class="pbm-section pbm-section-soft">
        <div class="pbm-wrap">
            <span class="pbm-eyebrow">Industries Served</span>
            <h2>Packaging for brands, importers, distributors, and agencies</h2>
            <p>We develop custom packaging boxes for industries that need clear branding, stable production quality, reliable protection, and export-ready packing.</p>
            <div class="pbm-industries">
                <span>Cosmetics</span>
                <span>Gifts</span>
                <span>Food & bakery</span>
                <span>Electronics</span>
                <span>Jewelry</span>
                <span>Fashion</span>
            </div>
        </div>
    </section>

    <section class="pbm-section">
        <div class="pbm-wrap pbm-split">
            <div>
                <span class="pbm-eyebrow">Why Choose Us</span>
                <h2>More than 9 years of B2B paper packaging production experience</h2>
                <p>VPN Paper Box works as a direct manufacturer, so buyers can discuss structure, material, sampling, printing, finishing, pricing, and lead time closer to the actual production team.</p>
                <ul class="pbm-list">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Direct production without unnecessary middleman markup.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Free design support, dieline checking, and structure consultation before production.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Competitive factory pricing based on size, paper, printing, finishing, and quantity.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Common production lead time of 3-7 days for many standard paper box projects.</span></li>
                </ul>
            </div>
            <div class="pbm-benefits">
                <?php foreach ($benefits as $benefit) : ?>
                    <div class="pbm-benefit">
                        <i class="<?php echo esc_attr($benefit[0]); ?>" aria-hidden="true"></i>
                        <p><?php echo esc_html($benefit[1]); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="pbm-section pbm-section-soft">
        <div class="pbm-wrap">
            <div class="pbm-head">
                <span class="pbm-eyebrow">Ordering Process</span>
                <h2>From request to export-ready packing</h2>
            </div>
            <ol class="pbm-process">
                <?php foreach ($steps as $step) : ?>
                    <li><strong><?php echo esc_html($step); ?></strong></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="pbm-section">
        <div class="pbm-wrap">
            <div class="pbm-head">
                <span class="pbm-eyebrow">Factory Proof</span>
                <h2>Workshop, production team, and printing workflow</h2>
                <p>Factory images help buyers evaluate production capability before requesting a quotation, sample, or bulk order plan.</p>
            </div>
            <div class="pbm-factory-slider" aria-label="Paper box factory images">
                <?php foreach ($factory_images as $factory_image) : ?>
                    <figure class="pbm-slide">
                        <img src="<?php echo esc_url($factory_image[0]); ?>" alt="<?php echo esc_attr($factory_image[1]); ?>" loading="lazy" decoding="async">
                        <figcaption><?php echo esc_html($factory_image[1]); ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <div class="pbm-capacity">Reference capacity: 3 million carton boxes/month and 1 million rigid boxes/month.</div>
        </div>
    </section>

    <section class="pbm-section pbm-section-soft">
        <div class="pbm-wrap">
            <div class="pbm-head">
                <span class="pbm-eyebrow">Materials and Finishes</span>
                <h2>Paper stock, printing, and finishing options</h2>
                <p>Each project is quoted around product weight, box structure, brand positioning, print effect, protection needs, target market, and order quantity.</p>
            </div>
            <div class="pbm-material-grid">
                <?php foreach ($materials as $material) : ?>
                    <article class="pbm-material">
                        <h3><?php echo esc_html($material[0]); ?></h3>
                        <p><?php echo esc_html($material[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="pbm-finish" aria-label="Printing and finishing techniques">
                <span>Gold/silver foil stamping</span>
                <span>Spot UV</span>
                <span>Matte/gloss lamination</span>
                <span>Offset printing</span>
                <span>Digital printing</span>
            </div>
        </div>
    </section>

    <section class="pbm-section pbm-final">
        <div class="pbm-wrap">
            <span class="pbm-eyebrow">Request a Factory Quote</span>
            <h2>Send your packaging details to a packaging boxes manufacturer in Vietnam</h2>
            <p>For a faster quotation, include product type, box size, quantity, material preference, printing, finishing, artwork status, and destination country.</p>
            <div class="pbm-contact-lines">
                <span>Phone/WhatsApp: <a href="tel:+84933102653">+84 933 102 653</a></span>
                <span>Email: <a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a></span>
            </div>
            <div class="pbm-final-quote">
                <?php $render_paper_box_quote_form('paper-box-quote-bottom', 'Request a Factory Quote'); ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
