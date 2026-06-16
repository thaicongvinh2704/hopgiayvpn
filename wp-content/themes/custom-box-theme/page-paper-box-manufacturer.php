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

$theme_uri = get_template_directory_uri();
$page_url = function_exists('custom_box_get_paper_box_manufacturer_page_url')
    ? custom_box_get_paper_box_manufacturer_page_url()
    : home_url('/paper-box-manufacturer/');

add_action('wp_head', function () use ($page_url, $theme_uri) {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        '@id'      => $page_url . '#webpage',
        'url'      => $page_url,
        'name'     => 'Paper Box Manufacturer in Vietnam | Packaging Boxes Manufacturer',
        'description' => 'Vietnam paper box manufacturer for custom paper boxes, rigid boxes, carton boxes, gift boxes, cosmetic packaging and bulk B2B packaging orders.',
        'inLanguage'  => 'en-US',
        'isPartOf' => array(
            '@id' => home_url('/#organization'),
        ),
        'primaryImageOfPage' => array(
            '@type' => 'ImageObject',
            'url'   => $theme_uri . '/assets/images/paper-box-manufacturer-vietnam-factory-hero.webp',
            'width' => 1600,
            'height' => 900,
        ),
    );
    ?>
    <script type="application/ld+json"><?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <?php
}, 30);

get_header();

$image_url = function ($file) use ($theme_uri) {
    return $theme_uri . '/assets/images/' . ltrim($file, '/');
};

$resolve_category_url = function ($slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if ($term && !is_wp_error($term)) {
        $term_link = get_term_link($term);
        if (!is_wp_error($term_link)) {
            return $term_link;
        }
    }

    return home_url('/products/' . trim($slug, '/') . '/');
};

$trust_items = array(
    array('fa-solid fa-tags', 'Factory-direct competitive pricing'),
    array('fa-solid fa-pen-ruler', 'Free design and dieline support'),
    array('fa-solid fa-truck-fast', '3-7 day production support'),
    array('fa-solid fa-industry', 'Capacity up to 3 million boxes/month'),
    array('fa-solid fa-globe', 'International B2B buyer support'),
);

$box_types = array(
    'Rigid Boxes',
    'Folding Carton Boxes',
    'Magnetic Boxes',
    'Drawer Boxes',
    'Logo Paper Bags',
    'Cosmetic Packaging',
    'Food / Bakery Packaging',
    'Electronics Packaging',
    'Other Custom Packaging',
);

$quantity_options = array(
    '5,000 - 10,000 pcs',
    '10,000 - 50,000 pcs',
    '50,000 - 100,000 pcs',
    '100,000 - 500,000 pcs',
    'Full container order',
    'Monthly repeat order',
    'Not sure yet',
);

$quote_status = isset($_GET['quote_status']) ? sanitize_text_field(wp_unslash($_GET['quote_status'])) : '';
$quote_messages = array(
    'success' => 'Thank you. Your quote request has been sent. Our team will contact you within 24 hours.',
    'failed'  => 'Sorry, we could not send your request right now. Please try again or contact sales.vpn@hopgiayvpn.com.',
    'missing' => 'Please fill in full name, business email, box type, estimated quantity, and delivery country.',
    'invalid' => 'The form session expired. Please refresh the page and try again.',
    'file'    => 'Please upload a valid artwork file under 10MB.',
);

$primary_categories = array(
    array('Custom Paper Boxes', 'custom-paper-boxes', 'Printed retail and product packaging boxes customized by size, paper, structure, and finish.', 'Other Custom Packaging', 'paper-packaging-categories-manufacturer.webp'),
    array('Rigid Boxes', 'rigid-boxes', 'Premium thick-board boxes for gifts, cosmetics, electronics, and luxury product sets.', 'Rigid Boxes', 'paper-box-manufacturer-vietnam-factory-hero.webp'),
    array('Folding Carton Boxes', 'folding-carton-boxes', 'Lightweight folding cartons for retail packaging, cosmetics, food, and consumer products.', 'Folding Carton Boxes', 'paper-box-materials-and-finishing-options.webp'),
    array('Magnetic Closure Boxes', 'magnetic-closure-boxes', 'Magnetic gift boxes with strong presentation value for premium brand packaging.', 'Magnetic Boxes', 'custom-packaging-quote-consultation.webp'),
    array('Drawer Boxes', 'drawer-boxes', 'Sliding drawer boxes for gift sets, accessories, cosmetics, and promotional packaging.', 'Drawer Boxes', 'paper-box-factory-production-workflow.webp'),
    array('Cosmetic Paper Boxes', 'cosmetic-paper-boxes', 'Custom skincare, perfume, and beauty packaging with printing and finishing support.', 'Cosmetic Packaging', 'paper-box-materials-and-finishing-options.webp'),
    array('Paper Bags with Logo', 'paper-bags-with-logo', 'Branded paper bags for retail, events, gifting, and coordinated packaging sets.', 'Logo Paper Bags', 'export-ready-paper-packaging-pallets.webp'),
    array('Food / Bakery Boxes', 'food-paper-boxes', 'Food, bakery, chocolate, and takeaway paper packaging with export-ready options.', 'Food / Bakery Packaging', 'paper-packaging-categories-manufacturer.webp'),
);

$more_categories = array(
    array('Custom Printed Paper Boxes', 'custom-printed-paper-boxes'),
    array('Lid and Base Boxes', 'lid-and-base-boxes'),
    array('Paper Tube Packaging', 'paper-tube-packaging'),
    array('Corrugated Mailer Boxes', 'corrugated-mailer-boxes'),
    array('Perfume Packaging Boxes', 'perfume-packaging-boxes'),
    array('Skincare Packaging Boxes', 'skincare-packaging-boxes'),
    array('Jewelry Paper Boxes', 'jewelry-paper-boxes'),
    array('Gift Paper Boxes', 'gift-paper-boxes'),
    array('Chocolate Gift Boxes', 'chocolate-gift-boxes'),
    array('Bakery Packaging Boxes', 'bakery-packaging-boxes'),
    array('Electronics Accessories Packaging', 'electronics-accessories-packaging'),
    array('Sports Packaging Boxes', 'sports-packaging-boxes'),
);

$factory_images = array(
    array('paper-box-factory-production-workflow.webp', 'Paper box factory production workflow for custom B2B packaging orders.'),
    array('custom-packaging-quote-consultation.webp', 'Packaging consultation for box structure, dieline, material, printing, and finishing.'),
    array('export-ready-paper-packaging-pallets.webp', 'Export-ready paper packaging cartons prepared for bulk international delivery.'),
);

$render_paper_box_quote_form = function ($form_id, $title, $location) use ($box_types, $quantity_options, $quote_status, $quote_messages, $page_url) {
    $is_footer = 'footer' === $location;
    ?>
    <form class="vpn-lp-quote-form" id="<?php echo esc_attr($form_id); ?>" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" data-event="quote_form_submit" data-form-location="<?php echo esc_attr($location); ?>" novalidate>
        <input type="hidden" name="action" value="custom_box_quote_form">
        <input type="hidden" name="quote_source" value="paper_box_manufacturer">
        <input type="hidden" name="form_location" value="<?php echo esc_attr($location); ?>">
        <input type="hidden" name="form_anchor" value="<?php echo esc_attr($form_id); ?>">
        <input type="hidden" name="product_name" value="Paper Box Manufacturer Landing Page">
        <input type="hidden" name="width" value="">
        <input type="hidden" name="depth" value="">
        <input type="hidden" name="unit" value="mm">
        <input type="hidden" name="printing_option" value="To be advised">
        <input type="hidden" name="finishing_option" value="To be advised">
        <input type="hidden" name="email_subject" value="[VPN Quote Request] Paper Box Manufacturer Landing Page">
        <input type="hidden" name="current_page_url" value="<?php echo esc_url($page_url); ?>">
        <input type="hidden" name="referrer_url" value="">
        <input type="hidden" name="utm_source" value="">
        <input type="hidden" name="utm_medium" value="">
        <input type="hidden" name="utm_campaign" value="">
        <input type="hidden" name="utm_term" value="">
        <input type="hidden" name="utm_content" value="">
        <input class="vpn-lp-hp" type="text" name="website_url" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="hidden" name="custom_box_quote_nonce" value="<?php echo esc_attr(wp_create_nonce('custom_box_quote_form')); ?>">

        <div class="vpn-lp-form-head">
            <span>Factory quotation</span>
            <h2><?php echo esc_html($title); ?></h2>
            <p>Send your box type, quantity, and delivery country. Our team will help suggest materials, structure, printing, and finishing.</p>
        </div>

        <div class="vpn-lp-form-message-wrap" aria-live="polite">
            <?php if ($quote_status && isset($quote_messages[$quote_status])) : ?>
                <p class="vpn-lp-form-message vpn-lp-form-message-<?php echo esc_attr($quote_status); ?>"><?php echo esc_html($quote_messages[$quote_status]); ?></p>
            <?php endif; ?>
        </div>

        <div class="vpn-lp-form-grid">
            <label>
                <span>Full Name *</span>
                <input type="text" name="full_name" autocomplete="name" required>
                <em class="vpn-lp-field-error"></em>
            </label>
            <label>
                <span>Business Email *</span>
                <input type="email" name="email" autocomplete="email" required>
                <em class="vpn-lp-field-error"></em>
            </label>
            <label>
                <span>Company Name</span>
                <input type="text" name="company" autocomplete="organization">
                <em class="vpn-lp-field-error"></em>
            </label>
            <label>
                <span>Phone / WhatsApp</span>
                <input type="text" name="phone" autocomplete="tel">
                <em class="vpn-lp-field-error"></em>
            </label>
            <label>
                <span>Box Type *</span>
                <select name="stock_option" required>
                    <option value="">Select box type</option>
                    <?php foreach ($box_types as $box_type) : ?>
                        <option value="<?php echo esc_attr($box_type); ?>"><?php echo esc_html($box_type); ?></option>
                    <?php endforeach; ?>
                </select>
                <em class="vpn-lp-field-error"></em>
            </label>
            <label>
                <span>Estimated Quantity *</span>
                <select name="quantity" required>
                    <option value="">Select quantity</option>
                    <?php foreach ($quantity_options as $quantity_option) : ?>
                        <option value="<?php echo esc_attr($quantity_option); ?>"><?php echo esc_html($quantity_option); ?></option>
                    <?php endforeach; ?>
                </select>
                <em class="vpn-lp-field-error"></em>
            </label>
            <label>
                <span>Delivery Country *</span>
                <input type="text" name="country" autocomplete="country-name" required>
                <em class="vpn-lp-field-error"></em>
            </label>
            <label>
                <span>Size / Product Size</span>
                <input type="text" name="length" placeholder="Example: 220 x 160 x 70 mm">
                <em class="vpn-lp-field-error"></em>
            </label>
        </div>

        <label>
            <span>Upload Artwork / Reference Image</span>
            <input type="file" name="artwork" accept=".png,.pdf,.jpg,.jpeg,.webp,.doc,.docx,.gif,.psd,.cdr,.eps">
            <em class="vpn-lp-field-error"></em>
        </label>
        <label>
            <span>Message / Project Details</span>
            <textarea name="message" rows="<?php echo $is_footer ? '4' : '3'; ?>" placeholder="Material preference, printing, finishing, delivery deadline, artwork status..."></textarea>
            <em class="vpn-lp-field-error"></em>
        </label>

        <button class="vpn-lp-submit" type="submit">Get Factory Quote in 24h</button>
        <p class="vpn-lp-privacy-note">Your information is confidential. We only use it to prepare your packaging quotation.</p>
    </form>
    <?php
};
?>

<style>
    .vpn-lp-page { --vpn-lp-blue: #063f7a; --vpn-lp-blue-2: #0b62ad; --vpn-lp-ink: #102033; --vpn-lp-muted: #5b6675; --vpn-lp-line: #dfe7ef; --vpn-lp-soft: #f4f8fb; color: var(--vpn-lp-ink); font-family: inherit; }
    .vpn-lp-page * { box-sizing: border-box; }
    .vpn-lp-wrap { margin: 0 auto; width: min(1180px, calc(100% - 32px)); }
    .vpn-lp-hero .vpn-lp-wrap { width: min(1400px, calc(100% - 32px)); }
    .vpn-lp-section { padding: 72px 0; }
    .vpn-lp-soft { background: var(--vpn-lp-soft); }
    .vpn-lp-eyebrow { color: var(--vpn-lp-blue); display: inline-flex; font-size: 13px; font-weight: 800; letter-spacing: 0; margin-bottom: 12px; text-transform: uppercase; }
    .vpn-lp-page h1, .vpn-lp-page h2, .vpn-lp-page h3, .vpn-lp-page p { margin-top: 0; }
    .vpn-lp-page h1 { color: #fff; font-size: clamp(38px, 5vw, 64px); letter-spacing: 0; line-height: 1.02; margin-bottom: 18px; }
    .vpn-lp-page h2 { font-size: clamp(28px, 3vw, 42px); letter-spacing: 0; line-height: 1.12; margin-bottom: 16px; }
    .vpn-lp-page h3 { font-size: 20px; line-height: 1.25; margin-bottom: 8px; }
    .vpn-lp-page p { color: var(--vpn-lp-muted); line-height: 1.7; }
    .vpn-lp-hero { background: linear-gradient(120deg, rgba(6,63,122,.93), rgba(7,47,89,.86)), url("<?php echo esc_url($image_url('paper-box-manufacturer-vietnam-factory-hero.webp')); ?>") center/cover; padding: 76px 0 46px; }
    .vpn-lp-hero-grid { align-items: center; display: grid; gap: 38px; grid-template-columns: minmax(390px, .9fr) minmax(640px, 1.18fr); }
    .vpn-lp-hero-copy .vpn-lp-eyebrow, .vpn-lp-hero-copy p { color: #e8f2fb; }
    .vpn-lp-hero-copy p { font-size: 18px; max-width: 720px; }
    .vpn-lp-hero-conversion { align-items: stretch; display: grid; gap: 16px; grid-template-columns: minmax(390px, 1fr) minmax(220px, .58fr); }
    .vpn-lp-savings-card { align-self: center; background: rgba(255,255,255,.94); border: 1px solid rgba(255,255,255,.7); border-radius: 8px; box-shadow: 0 18px 50px rgba(12,42,77,.2); color: var(--vpn-lp-ink); overflow: hidden; }
    .vpn-lp-savings-media { background: #eaf3fb; display: block; }
    .vpn-lp-savings-media img { aspect-ratio: 16 / 11; display: block; height: auto; object-fit: cover; width: 100%; }
    .vpn-lp-savings-body { padding: 18px; }
    .vpn-lp-savings-kicker { color: var(--vpn-lp-blue); display: block; font-size: 12px; font-weight: 850; margin-bottom: 8px; text-transform: uppercase; }
    .vpn-lp-savings-value { color: var(--vpn-lp-blue); display: block; font-size: 42px; font-weight: 900; letter-spacing: 0; line-height: 1; margin-bottom: 8px; }
    .vpn-lp-savings-body h2 { color: var(--vpn-lp-ink); font-size: 20px; line-height: 1.2; margin-bottom: 8px; }
    .vpn-lp-savings-body p { color: var(--vpn-lp-muted); font-size: 14px; line-height: 1.6; margin-bottom: 12px; }
    .vpn-lp-savings-list { display: grid; gap: 8px; list-style: none; margin: 0; padding: 0; }
    .vpn-lp-savings-list li { align-items: flex-start; color: #26384d; display: flex; font-size: 13px; font-weight: 750; gap: 8px; line-height: 1.35; }
    .vpn-lp-savings-list i { color: #0f8a4b; margin-top: 3px; }
    .vpn-lp-savings-note { border-top: 1px solid var(--vpn-lp-line); color: #6a7480; display: block; font-size: 12px; line-height: 1.45; margin-top: 14px; padding-top: 12px; }
    .vpn-lp-trust-list { display: grid; gap: 10px; list-style: none; margin: 24px 0 28px; padding: 0; }
    .vpn-lp-trust-list li { align-items: flex-start; color: #fff; display: flex; font-weight: 750; gap: 10px; line-height: 1.45; }
    .vpn-lp-trust-list i { color: #9ee2b8; margin-top: 4px; }
    .vpn-lp-hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }
    .vpn-lp-btn { align-items: center; border-radius: 6px; display: inline-flex; font-weight: 850; justify-content: center; min-height: 48px; padding: 13px 18px; text-decoration: none; }
    .vpn-lp-btn-primary { background: #fff; color: var(--vpn-lp-blue); }
    .vpn-lp-btn-secondary { border: 1px solid rgba(255,255,255,.5); color: #fff; }
    .vpn-lp-quote-form { background: #fff; border: 1px solid var(--vpn-lp-line); border-radius: 8px; box-shadow: 0 18px 50px rgba(12,42,77,.16); padding: 24px; }
    .vpn-lp-form-head span { color: var(--vpn-lp-blue); display: block; font-size: 13px; font-weight: 850; margin-bottom: 6px; text-transform: uppercase; }
    .vpn-lp-form-head h2 { color: var(--vpn-lp-ink); font-size: 24px; margin-bottom: 8px; }
    .vpn-lp-form-head p { font-size: 14px; margin-bottom: 18px; }
    .vpn-lp-form-grid { display: grid; gap: 14px; grid-template-columns: 1fr 1fr; }
    .vpn-lp-quote-form label { display: block; margin-bottom: 14px; }
    .vpn-lp-quote-form label span { color: #26384d; display: block; font-size: 13px; font-weight: 850; margin-bottom: 7px; }
    .vpn-lp-quote-form input, .vpn-lp-quote-form select, .vpn-lp-quote-form textarea { background: #fff; border: 1px solid #cfd9e3; border-radius: 6px; color: var(--vpn-lp-ink); font: inherit; font-size: 15px; min-height: 46px; padding: 12px 13px; width: 100%; }
    .vpn-lp-quote-form textarea { resize: vertical; }
    .vpn-lp-quote-form .vpn-lp-hp { height: 1px; left: -9999px; opacity: 0; position: absolute; width: 1px; }
    .vpn-lp-submit { align-items: center; background: var(--vpn-lp-blue); border: 0; border-radius: 6px; color: #fff; cursor: pointer; display: inline-flex; font-size: 16px; font-weight: 850; justify-content: center; min-height: 50px; padding: 13px 20px; width: 100%; }
    .vpn-lp-submit:hover { background: var(--vpn-lp-blue-2); }
    .vpn-lp-submit[disabled] { cursor: wait; opacity: .72; }
    .vpn-lp-privacy-note { font-size: 13px; margin: 10px 0 0; text-align: center; }
    .vpn-lp-form-message { border-radius: 6px; font-weight: 750; margin-bottom: 16px; padding: 10px 12px; }
    .vpn-lp-form-message-success { background: #e8f7ef; color: #126136; }
    .vpn-lp-form-message-failed, .vpn-lp-form-message-missing, .vpn-lp-form-message-invalid, .vpn-lp-form-message-file { background: #fff0e8; color: #9a3b10; }
    .vpn-lp-field-error { color: #a8321a; display: block; font-size: 12px; font-style: normal; margin-top: 5px; min-height: 0; }
    .vpn-lp-field-invalid input, .vpn-lp-field-invalid select, .vpn-lp-field-invalid textarea { border-color: #d64b2a; }
    .vpn-lp-trust-bar { background: #fff; border-bottom: 1px solid var(--vpn-lp-line); padding: 24px 0; }
    .vpn-lp-trust-grid { display: grid; gap: 14px; grid-template-columns: repeat(5, 1fr); }
    .vpn-lp-trust-item { align-items: center; border: 1px solid var(--vpn-lp-line); border-radius: 8px; display: flex; gap: 11px; min-height: 76px; padding: 14px 10px; }
    .vpn-lp-trust-item i { color: var(--vpn-lp-blue); font-size: 22px; text-align: center; width: 26px; }
    .vpn-lp-trust-item span { font-size: 14px; font-weight: 850; line-height: 1.35; }
    .vpn-lp-after-grid { display: grid; gap: 16px; grid-template-columns: repeat(3, 1fr); }
    .vpn-lp-step { background: #fff; border: 1px solid var(--vpn-lp-line); border-radius: 8px; min-height: 150px; padding: 20px; }
    .vpn-lp-step strong { align-items: center; background: var(--vpn-lp-blue); border-radius: 50%; color: #fff; display: inline-flex; height: 42px; justify-content: center; margin-bottom: 14px; width: 42px; }
    .vpn-lp-head { max-width: 780px; margin-bottom: 32px; }
    .vpn-lp-category-grid { display: grid; gap: 18px; grid-template-columns: repeat(4, 1fr); }
    .vpn-lp-category-card { background: #fff; border: 1px solid var(--vpn-lp-line); border-radius: 8px; overflow: hidden; }
    .vpn-lp-category-card img { aspect-ratio: 16 / 9; display: block; height: auto; object-fit: cover; width: 100%; }
    .vpn-lp-category-body { padding: 16px; }
    .vpn-lp-category-body p { font-size: 14px; margin-bottom: 14px; }
    .vpn-lp-mini-btn { background: var(--vpn-lp-blue); border: 0; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 850; min-height: 40px; padding: 9px 12px; }
    .vpn-lp-more { margin-top: 24px; }
    .vpn-lp-more summary { color: var(--vpn-lp-blue); cursor: pointer; font-weight: 850; }
    .vpn-lp-more-grid { display: grid; gap: 10px; grid-template-columns: repeat(4, 1fr); margin-top: 16px; }
    .vpn-lp-more-grid a { background: #fff; border: 1px solid var(--vpn-lp-line); border-radius: 6px; color: var(--vpn-lp-ink); font-weight: 750; padding: 10px 12px; text-decoration: none; }
    .vpn-lp-split { align-items: start; display: grid; gap: 42px; grid-template-columns: minmax(0,.95fr) minmax(0,1fr); }
    .vpn-lp-image-card { background: #fff; border: 1px solid var(--vpn-lp-line); border-radius: 8px; overflow: hidden; }
    .vpn-lp-image-card img { aspect-ratio: 16 / 9; display: block; height: auto; object-fit: cover; width: 100%; }
    .vpn-lp-image-card figcaption { color: var(--vpn-lp-muted); line-height: 1.55; padding: 14px; }
    .vpn-lp-list { display: grid; gap: 12px; list-style: none; margin: 22px 0 0; padding: 0; }
    .vpn-lp-list li { align-items: flex-start; color: var(--vpn-lp-muted); display: flex; gap: 10px; line-height: 1.6; }
    .vpn-lp-list i { color: #0f8a4b; margin-top: 5px; }
    .vpn-lp-factory-grid { display: grid; gap: 18px; grid-template-columns: repeat(3, 1fr); }
    .vpn-lp-capacity { background: #fff; border-left: 4px solid var(--vpn-lp-blue); font-weight: 850; margin-top: 18px; padding: 18px; }
    .vpn-lp-material-grid { display: grid; gap: 16px; grid-template-columns: repeat(5, 1fr); }
    .vpn-lp-material { background: #fff; border: 1px solid var(--vpn-lp-line); border-radius: 8px; padding: 18px; }
    .vpn-lp-finish { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
    .vpn-lp-finish span { background: #fff; border: 1px solid #cfdbe8; border-radius: 999px; color: #253a50; font-weight: 750; padding: 10px 14px; }
    .vpn-lp-final { background: #072f59; color: #fff; }
    .vpn-lp-final h2, .vpn-lp-final p, .vpn-lp-final span, .vpn-lp-final .vpn-lp-eyebrow { color: #fff; }
    .vpn-lp-contact-lines { display: flex; flex-wrap: wrap; gap: 16px; margin: 18px 0 24px; }
    .vpn-lp-contact-lines a { color: #fff; font-weight: 850; text-decoration: none; }
    .vpn-lp-sticky-cta { display: none; }
    @media (max-width: 1120px) {
        .vpn-lp-hero-grid { grid-template-columns: 1fr; }
        .vpn-lp-hero-conversion { grid-template-columns: minmax(0, 1fr); }
        .vpn-lp-savings-card { align-self: stretch; }
        .vpn-lp-savings-media { display: none; }
    }
    @media (max-width: 980px) {
        .vpn-lp-hero-grid, .vpn-lp-split { grid-template-columns: 1fr; }
        .vpn-lp-trust-grid, .vpn-lp-after-grid, .vpn-lp-factory-grid, .vpn-lp-material-grid { grid-template-columns: repeat(2, 1fr); }
        .vpn-lp-category-grid, .vpn-lp-more-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .vpn-lp-wrap { width: min(100% - 24px, 1180px); }
        .vpn-lp-section { padding: 50px 0; }
        .vpn-lp-hero { padding: 46px 0 36px; }
        .vpn-lp-trust-grid, .vpn-lp-after-grid, .vpn-lp-category-grid, .vpn-lp-more-grid, .vpn-lp-factory-grid, .vpn-lp-material-grid, .vpn-lp-form-grid { grid-template-columns: 1fr; }
        .vpn-lp-quote-form { padding: 18px; }
        .vpn-lp-page h1 { font-size: 38px; }
        .vpn-lp-sticky-cta { background: #fff; border-top: 1px solid var(--vpn-lp-line); bottom: 0; display: block; left: 0; padding: 10px 12px; position: fixed; right: 0; z-index: 50; }
        .vpn-lp-sticky-cta a { background: var(--vpn-lp-blue); border-radius: 6px; color: #fff; display: flex; font-weight: 850; justify-content: center; min-height: 48px; padding: 13px 18px; text-decoration: none; }
        body { padding-bottom: 70px; }
    }
</style>

<main class="vpn-lp-page">
    <section class="vpn-lp-hero">
        <div class="vpn-lp-wrap vpn-lp-hero-grid">
            <div class="vpn-lp-hero-copy">
                <span class="vpn-lp-eyebrow">Paper Box Manufacturer in Vietnam</span>
                <h1>Paper Box Manufacturer in Vietnam</h1>
                <p>Factory-direct custom paper boxes, carton boxes, rigid boxes, magnetic boxes, drawer boxes, and printed paper bags for global B2B buyers.</p>
                <ul class="vpn-lp-trust-list">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Factory-direct production in Vietnam</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>OEM/ODM custom packaging</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Bulk order and export packing support</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Material, dieline, printing, and finishing consultation</span></li>
                </ul>
                <div class="vpn-lp-hero-actions">
                    <a class="vpn-lp-btn vpn-lp-btn-primary vpn-lp-js-quote-cta" href="#paper-box-quote" data-event="quote_cta_click">Get Factory Quote</a>
                    <a class="vpn-lp-btn vpn-lp-btn-secondary vpn-lp-js-scroll" href="#packaging-options" data-event="quote_cta_click">View Packaging Options</a>
                </div>
            </div>
            <div class="vpn-lp-hero-conversion">
                <?php $render_paper_box_quote_form('paper-box-quote', 'Get Your Paper Box Quote', 'hero'); ?>
                <aside class="vpn-lp-savings-card" aria-label="Bulk order savings">
                    <picture class="vpn-lp-savings-media">
                        <img src="<?php echo esc_url($image_url('export-ready-paper-packaging-pallets.webp')); ?>" alt="Bulk paper packaging orders prepared for export packing" width="1600" height="900" loading="eager" decoding="async">
                    </picture>
                    <div class="vpn-lp-savings-body">
                        <span class="vpn-lp-savings-kicker">Bulk order advantage</span>
                        <span class="vpn-lp-savings-value">Up to 40%</span>
                        <h2>Lower unit cost for larger paper box orders</h2>
                        <p>For qualified bulk orders, optimized paper buying, printing setup, and export packing can help reduce unit cost compared with small runs.</p>
                        <ul class="vpn-lp-savings-list">
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Best for repeat orders and container shipments</span></li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Factory quote based on quantity, size, and finish</span></li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Cost-saving options suggested within 24 hours</span></li>
                        </ul>
                        <small class="vpn-lp-savings-note">Savings depend on structure, material, printing, finishing, and order quantity.</small>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="vpn-lp-trust-bar" aria-label="Factory trust points">
        <div class="vpn-lp-wrap vpn-lp-trust-grid">
            <?php foreach ($trust_items as $item) : ?>
                <div class="vpn-lp-trust-item">
                    <i class="<?php echo esc_attr($item[0]); ?>" aria-hidden="true"></i>
                    <span><?php echo esc_html($item[1]); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="vpn-lp-section vpn-lp-soft">
        <div class="vpn-lp-wrap">
            <div class="vpn-lp-head">
                <span class="vpn-lp-eyebrow">After Submit</span>
                <h2>What happens after you send a quote request?</h2>
            </div>
            <div class="vpn-lp-after-grid">
                <article class="vpn-lp-step"><strong>1</strong><p>We review your box type, size, quantity, and delivery country.</p></article>
                <article class="vpn-lp-step"><strong>2</strong><p>We suggest suitable paper, structure, printing, and finishing.</p></article>
                <article class="vpn-lp-step"><strong>3</strong><p>We send a factory quotation or sample plan within 24 hours.</p></article>
            </div>
        </div>
    </section>

    <section class="vpn-lp-section" id="packaging-options">
        <div class="vpn-lp-wrap">
            <div class="vpn-lp-head">
                <span class="vpn-lp-eyebrow">Manufacturing Range</span>
                <h2>Paper packaging options for B2B bulk orders</h2>
                <p>Choose a main packaging type, then request a quote with size, material, printing, finishing, inserts, and export packing details.</p>
            </div>
            <div class="vpn-lp-category-grid">
                <?php foreach ($primary_categories as $category) : ?>
                    <article class="vpn-lp-category-card">
                        <img src="<?php echo esc_url($image_url($category[4])); ?>" alt="<?php echo esc_attr($category[0]); ?>" width="1600" height="900" loading="lazy" decoding="async">
                        <div class="vpn-lp-category-body">
                            <h3><?php echo esc_html($category[0]); ?></h3>
                            <p><?php echo esc_html($category[2]); ?></p>
                            <button class="vpn-lp-mini-btn vpn-lp-js-quote-cta" type="button" data-event="quote_cta_click" data-box-type="<?php echo esc_attr($category[3]); ?>">Request Quote</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <details class="vpn-lp-more">
                <summary>More packaging options</summary>
                <div class="vpn-lp-more-grid">
                    <?php foreach ($more_categories as $category) : ?>
                        <a href="<?php echo esc_url($resolve_category_url($category[1])); ?>"><?php echo esc_html($category[0]); ?></a>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>
    </section>

    <section class="vpn-lp-section vpn-lp-soft">
        <div class="vpn-lp-wrap vpn-lp-split">
            <div>
                <span class="vpn-lp-eyebrow">Why Choose Us</span>
                <h2>More than 9 years of B2B paper packaging production experience</h2>
                <p>VPN Paper Box works as a direct manufacturer, so buyers can discuss structure, material, sampling, printing, finishing, pricing, and lead time closer to the actual production team.</p>
                <ul class="vpn-lp-list">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Direct production without unnecessary middleman markup.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Free design support, dieline checking, and structure consultation before production.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Competitive factory pricing based on size, paper, printing, finishing, and quantity.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Common production lead time of 3-7 days for many standard paper box projects.</span></li>
                </ul>
            </div>
            <figure class="vpn-lp-image-card">
                <img src="<?php echo esc_url($image_url('custom-packaging-quote-consultation.webp')); ?>" alt="Custom packaging quote consultation for international B2B buyers" width="1600" height="900" loading="lazy" decoding="async">
                <figcaption>Our sales and production team helps confirm structure, material, printing, finishing, quantity, and packing details before quotation.</figcaption>
            </figure>
        </div>
    </section>

    <section class="vpn-lp-section">
        <div class="vpn-lp-wrap">
            <div class="vpn-lp-head">
                <span class="vpn-lp-eyebrow">Factory Proof</span>
                <h2>Workshop, production team, and export packing workflow</h2>
                <p>Real factory photos help international buyers evaluate our production capability before requesting a quotation, sample, or bulk order plan.</p>
            </div>
            <div class="vpn-lp-factory-grid" aria-label="Paper box factory images">
                <?php foreach ($factory_images as $factory_image) : ?>
                    <figure class="vpn-lp-image-card">
                        <img src="<?php echo esc_url($image_url($factory_image[0])); ?>" alt="<?php echo esc_attr($factory_image[1]); ?>" width="1600" height="900" loading="lazy" decoding="async">
                        <figcaption><?php echo esc_html($factory_image[1]); ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <div class="vpn-lp-capacity">Reference capacity: 3 million carton boxes/month and 1 million rigid boxes/month.</div>
        </div>
    </section>

    <section class="vpn-lp-section vpn-lp-soft">
        <div class="vpn-lp-wrap vpn-lp-split">
            <div>
                <span class="vpn-lp-eyebrow">Materials and Finishes</span>
                <h2>Paper stock, printing, and finishing options</h2>
                <p>Each project is quoted around product weight, box structure, brand positioning, print effect, protection needs, target market, and order quantity.</p>
                <div class="vpn-lp-finish" aria-label="Printing and finishing techniques">
                    <span>Gold/silver foil stamping</span>
                    <span>Spot UV</span>
                    <span>Matte/gloss lamination</span>
                    <span>Offset printing</span>
                    <span>Digital printing</span>
                </div>
            </div>
            <figure class="vpn-lp-image-card">
                <img src="<?php echo esc_url($image_url('paper-box-materials-and-finishing-options.webp')); ?>" alt="Paper box materials and finishing options" width="1600" height="900" loading="lazy" decoding="async">
                <figcaption>Material, paper thickness, printing, finishing, inserts, and carton packing can be adjusted for your product and market.</figcaption>
            </figure>
        </div>
    </section>

    <section class="vpn-lp-section vpn-lp-final">
        <div class="vpn-lp-wrap">
            <span class="vpn-lp-eyebrow">Request a Factory Quote</span>
            <h2>Send Your Packaging Details for a Factory Quote</h2>
            <p>For a faster quotation, include product type, box size, quantity, material preference, printing, finishing, artwork status, and destination country.</p>
            <div class="vpn-lp-contact-lines">
                <span>Phone/WhatsApp: <a href="tel:+84933102653">+84 933 102 653</a></span>
                <span>Email: <a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a></span>
            </div>
            <?php $render_paper_box_quote_form('paper-box-quote-bottom', 'Send Your Packaging Details for a Factory Quote', 'footer'); ?>
        </div>
    </section>

    <div class="vpn-lp-sticky-cta">
        <a class="vpn-lp-js-quote-cta" href="#paper-box-quote" data-event="quote_cta_click">Get Quote</a>
    </div>
</main>

<script>
(function() {
    var forms = document.querySelectorAll('.vpn-lp-quote-form');
    var ctas = document.querySelectorAll('.vpn-lp-js-quote-cta, .vpn-lp-js-scroll');

    function pushEvent(name, data) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push(Object.assign({ event: name }, data || {}));
    }

    function scrollToForm(boxType) {
        var form = document.getElementById('paper-box-quote');
        if (!form) {
            return;
        }

        if (boxType) {
            var select = form.querySelector('[name="stock_option"]');
            if (select) {
                select.value = boxType;
            }
        }

        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(function() {
            var first = form.querySelector('[name="full_name"]');
            if (first) {
                first.focus({ preventScroll: true });
            }
        }, 450);
    }

    ctas.forEach(function(cta) {
        cta.addEventListener('click', function(event) {
            var target = cta.getAttribute('href') || '';
            pushEvent('quote_cta_click', {
                target: target,
                box_type: cta.getAttribute('data-box-type') || ''
            });

            if (cta.classList.contains('vpn-lp-js-quote-cta')) {
                event.preventDefault();
                scrollToForm(cta.getAttribute('data-box-type') || '');
            }
        });
    });

    function setFieldError(field, message) {
        var label = field.closest('label');
        if (!label) {
            return;
        }

        var error = label.querySelector('.vpn-lp-field-error');
        label.classList.toggle('vpn-lp-field-invalid', Boolean(message));
        if (error) {
            error.textContent = message || '';
        }
    }

    function validateForm(form) {
        var valid = true;
        var required = form.querySelectorAll('[required]');

        required.forEach(function(field) {
            var message = '';
            if (!field.value.trim()) {
                message = 'This field is required.';
            } else if ('email' === field.type && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value.trim())) {
                message = 'Please enter a valid business email.';
            }

            setFieldError(field, message);
            if (message) {
                valid = false;
            }
        });

        return valid;
    }

    forms.forEach(function(form, index) {
        var params = new URLSearchParams(window.location.search);
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(function(key) {
            var input = form.querySelector('[name="' + key + '"]');
            if (input) {
                input.value = params.get(key) || '';
            }
        });

        var pageUrl = form.querySelector('[name="current_page_url"]');
        var referrer = form.querySelector('[name="referrer_url"]');
        if (pageUrl) {
            pageUrl.value = window.location.href;
        }
        if (referrer) {
            referrer.value = document.referrer || '';
        }

        var iframeName = 'vpn_lp_quote_submit_' + index + '_' + Date.now();
        var iframe = document.createElement('iframe');
        iframe.name = iframeName;
        iframe.title = 'Quote form submission';
        iframe.hidden = true;
        iframe.style.display = 'none';
        form.parentNode.appendChild(iframe);

        form.addEventListener('submit', function(event) {
            var button = form.querySelector('button[type="submit"]');
            var messageWrap = form.querySelector('.vpn-lp-form-message-wrap');

            if (!validateForm(form)) {
                event.preventDefault();
                pushEvent('quote_form_submit_error', { reason: 'validation', form_location: form.dataset.formLocation || '' });
                return;
            }

            form.target = iframeName;
            if (button) {
                button.disabled = true;
                button.dataset.originalText = button.textContent;
                button.textContent = 'Sending...';
            }
            if (messageWrap) {
                messageWrap.innerHTML = '<p class="vpn-lp-form-message vpn-lp-form-message-success">Sending your quote request...</p>';
            }
        });

        iframe.addEventListener('load', function() {
            var button = form.querySelector('button[type="submit"]');
            var messageWrap = form.querySelector('.vpn-lp-form-message-wrap');
            var status = '';

            try {
                status = new URL(iframe.contentWindow.location.href).searchParams.get('quote_status') || '';
            } catch (error) {
                status = '';
            }

            if (!status) {
                return;
            }

            if (messageWrap) {
                if ('success' === status) {
                    messageWrap.innerHTML = '<p class="vpn-lp-form-message vpn-lp-form-message-success">Thank you. Your quote request has been sent. Our team will contact you within 24 hours.</p>';
                    form.reset();
                    pushEvent('quote_form_submit_success', { form_location: form.dataset.formLocation || '' });
                } else {
                    messageWrap.innerHTML = '<p class="vpn-lp-form-message vpn-lp-form-message-' + status + '">Sorry, we could not send your request right now. Please check the fields and try again.</p>';
                    pushEvent('quote_form_submit_error', { reason: status, form_location: form.dataset.formLocation || '' });
                }
            }

            if (button) {
                button.disabled = false;
                button.textContent = button.dataset.originalText || 'Get Factory Quote in 24h';
            }
        });
    });
})();
</script>

<?php get_footer(); ?>
