<?php
/**
 * Template Name: Paper Box Manufacturer Landing Page
 *
 * Conversion-focused English landing page for B2B paper box quote requests.
 */

defined('ABSPATH') || exit;

add_filter('language_attributes', function () {
    return 'lang="en-US"';
});

add_filter('body_class', function ($classes) {
    $classes[] = 'paper-box-manufacturer-landing';
    return $classes;
});

$theme_uri = get_template_directory_uri();
$page_url = function_exists('custom_box_get_paper_box_manufacturer_page_url')
    ? custom_box_get_paper_box_manufacturer_page_url()
    : home_url('/paper-box-manufacturer/');

$image_url = function ($file) use ($theme_uri) {
    return $theme_uri . '/assets/images/' . ltrim($file, '/');
};

$box_types = array(
    'Custom Paper Boxes',
    'Rigid Boxes',
    'Folding Carton Boxes',
    'Magnetic Closure Boxes',
    'Drawer Boxes',
    'Lid and Base Boxes',
    'Cosmetic Paper Boxes',
    'Gift Paper Boxes',
    'Food Paper Boxes',
    'Paper Bags with Logo',
    'Other Custom Packaging',
);

$quantity_options = array(
    '1,000 - 3,000 boxes',
    '3,000 - 10,000 boxes',
    '10,000 - 50,000 boxes',
    '50,000 - 100,000 boxes',
    '100,000+ boxes',
    'Monthly repeat order',
    'Not sure yet',
);

$material_options = array(
    'Art paper',
    'Kraft paper',
    'Ivory board',
    'Grey board',
    'Corrugated paper',
    'Need recommendation',
);

$printing_options = array(
    'Offset printing',
    'Digital printing',
    'Pantone color matching',
    'No printing / plain box',
    'Need recommendation',
);

$finishing_options = array(
    'Matte lamination',
    'Gloss lamination',
    'Foil stamping',
    'Embossing / debossing',
    'Spot UV',
    'Need recommendation',
);

$trust_items = array(
    array('fa-solid fa-award', '9+ Years Packaging Experience'),
    array('fa-solid fa-boxes-stacked', 'Up to 3 Million Carton Boxes / Month'),
    array('fa-solid fa-cube', '1 Million Rigid Boxes / Month'),
    array('fa-solid fa-earth-asia', 'Export Support for Global B2B Buyers'),
);

$manufacture_category_slugs = array(
    'custom-paper-boxes',
    'rigid-boxes',
    'folding-carton-boxes',
    'magnetic-closure-boxes',
    'drawer-boxes',
    'lid-and-base-boxes',
    'cosmetic-paper-boxes',
    'gift-paper-boxes',
    'food-paper-boxes',
    'paper-bags-with-logo',
);

$category_description_fallbacks = array(
    'custom-paper-boxes'       => 'Custom paper boxes for retail, ecommerce, gift, food, cosmetic, and brand packaging projects.',
    'rigid-boxes'              => 'Premium rigid boxes for gift sets, cosmetics, electronics, jewelry, and launch campaigns.',
    'folding-carton-boxes'     => 'Lightweight folding cartons for high-volume retail SKUs, shelf display, and export packing.',
    'magnetic-closure-boxes'   => 'Magnetic gift boxes with board thickness, insert layout, and finishing matched to your product.',
    'drawer-boxes'             => 'Drawer-style packaging for premium unboxing, product kits, samples, and subscription sets.',
    'lid-and-base-boxes'       => 'Two-piece lid and base boxes for rigid presentation, product protection, and clean branding.',
    'cosmetic-paper-boxes'     => 'Cosmetic paper boxes for skincare, beauty, fragrance, sample kits, and retail display projects.',
    'gift-paper-boxes'         => 'Gift paper boxes for retail gifting, seasonal campaigns, corporate gift sets, and premium presentation.',
    'food-paper-boxes'         => 'Food paper boxes for bakery, confectionery, tea, beverage, takeaway, and premium food gift packaging.',
    'paper-bags-with-logo'     => 'Custom paper bags with logo printing, rope handles, reinforced bottoms, and export carton packing.',
);

$manufacture_cards = array();

if (taxonomy_exists('product_cat')) {
    foreach ($manufacture_category_slugs as $category_slug) {
        $term = get_term_by('slug', $category_slug, 'product_cat');

        if (!$term || is_wp_error($term)) {
            continue;
        }

        $term_link = get_term_link($term);
        $category_url = is_wp_error($term_link) ? '' : $term_link;
        if (function_exists('custom_box_get_flat_product_category_url')) {
            $flat_category_url = custom_box_get_flat_product_category_url($term);
            $category_url = $flat_category_url ? $flat_category_url : $category_url;
        }

        $description = trim(wp_strip_all_tags(term_description($term->term_id, 'product_cat')));
        if (!$description && isset($category_description_fallbacks[$term->slug])) {
            $description = $category_description_fallbacks[$term->slug];
        }

        $manufacture_cards[] = array(
            'title'        => $term->name,
            'description'  => wp_trim_words($description, 20, '...'),
            'image'        => function_exists('custom_box_get_product_category_card_image_url')
                ? custom_box_get_product_category_card_image_url($term, 'medium_large')
                : '',
            'box_type'     => $term->name,
            'category_url' => $category_url,
            'count'        => (int) $term->count,
            'slug'         => $term->slug,
        );
    }
}

$factory_images = array(
    array(
        'image'   => 'paper-box-manufacturer-vietnam-factory-hero.webp',
        'caption' => 'Paper box production and sample development support for B2B packaging buyers.',
    ),
    array(
        'image'   => 'paper-box-factory-production-workflow.webp',
        'caption' => 'Printing, cutting, folding, assembling, and quality checks are planned around each order.',
    ),
    array(
        'image'   => 'anh-nha-may-3-16x9-100kb.webp',
        'caption' => 'Factory floor with paper box production stations, materials, and quality control workflow.',
    ),
    array(
        'image'   => 'export-ready-paper-packaging-pallets.webp',
        'caption' => 'Export-ready cartons, shipping marks, and packing details for global B2B buyers.',
    ),
);

$option_groups = array(
    'Materials' => array('Art paper', 'Kraft paper', 'Ivory board', 'Grey board', 'Corrugated paper'),
    'Printing'  => array('Offset printing', 'Digital printing', 'Pantone color matching'),
    'Finishing' => array('Matte / gloss lamination', 'Foil stamping', 'Embossing / debossing', 'Spot UV'),
    'Inserts'   => array('Paper insert', 'Foam insert', 'EVA insert', 'Molded pulp insert'),
    'Packing'   => array('Export carton', 'Shipping mark', 'Pallet support if required'),
);

$faq_items = array(
    array(
        'question' => 'What is your MOQ for custom paper boxes?',
        'answer'   => 'MOQ depends on box type, size, material, and printing requirements. Please send your box details and estimated quantity so we can recommend the most suitable production option.',
    ),
    array(
        'question' => 'Can you make custom size and structure?',
        'answer'   => 'Yes. We support custom size, box structure, dieline, inserts, printing, and finishing based on your product and packaging requirements.',
    ),
    array(
        'question' => 'Can I get a sample before mass production?',
        'answer'   => 'Yes. We can support sample development before bulk production so you can check structure, size, material, and printing details.',
    ),
    array(
        'question' => 'Do you support international shipping?',
        'answer'   => 'Yes. We support export-ready packing for global B2B buyers and can prepare cartons, shipping marks, and packing details based on your order.',
    ),
    array(
        'question' => 'What information do you need for a fast quotation?',
        'answer'   => 'Please provide box type, size, quantity, material preference, printing/finishing requirements, delivery country, and artwork or reference images if available.',
    ),
    array(
        'question' => 'Can you produce packaging for cosmetics, gifts, food, electronics, and retail products?',
        'answer'   => 'Yes. We manufacture custom paper packaging for cosmetics, gifts, food, retail products, electronics, candles, jewelry, apparel, and other product categories.',
    ),
);

$quote_status = isset($_GET['quote_status']) ? sanitize_text_field(wp_unslash($_GET['quote_status'])) : '';
$quote_messages = array(
    'success' => 'Your request has been received. Our team will reply within 24 hours.',
    'failed'  => 'Sorry, we could not send your request right now. Please try again or contact sales.vpn@hopgiayvpn.com.',
    'missing' => 'Please fill in the required quote fields.',
    'invalid' => 'The form session expired. Please refresh the page and try again.',
    'file'    => 'Please upload a valid artwork file under 10MB.',
    'captcha' => 'Please complete the simple security question correctly.',
    'spam'    => 'Sorry, this request could not be accepted.',
    'rate_limited' => 'Too many quote requests. Please wait a few minutes and try again.',
);

add_action('wp_head', function () use ($page_url, $image_url, $faq_items) {
    $webpage_schema = array(
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
            'url'   => $image_url('paper-box-manufacturer-vietnam-factory-hero.webp'),
            'width' => 1600,
            'height' => 900,
        ),
    );

    $faq_schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        '@id'      => $page_url . '#faq',
        'mainEntity' => array_map(
            function ($item) {
                return array(
                    '@type' => 'Question',
                    'name'  => $item['question'],
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => $item['answer'],
                    ),
                );
            },
            $faq_items
        ),
    );
    ?>
    <link rel="preload" as="image" href="<?php echo esc_url($image_url('paper-box-manufacturer-vietnam-factory-hero.webp')); ?>" fetchpriority="high">
    <script type="application/ld+json"><?php echo wp_json_encode($webpage_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <script type="application/ld+json"><?php echo wp_json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <style id="paper-box-manufacturer-landing-css">
        body.paper-box-manufacturer-landing {
            --pbm-blue: var(--color-primary, #2A6A92);
            --pbm-ink: var(--color-ink, #123243);
            --pbm-line: var(--color-line, #d8e4eb);
        }

        .paper-box-manufacturer-page {
            --pbm-blue: var(--color-primary, #2A6A92);
            --pbm-blue-dark: var(--color-primary-dark, #144b68);
            --pbm-blue-soft: var(--color-primary-soft, #eaf3f8);
            --pbm-accent: var(--color-accent, #F7B24D);
            --pbm-green: #0b7a53;
            --pbm-ink: var(--color-ink, #123243);
            --pbm-muted: var(--color-muted, #50606b);
            --pbm-line: var(--color-line, #d8e4eb);
            --pbm-soft: var(--color-surface-soft, #f4f8fb);
            color: var(--pbm-ink);
            overflow-x: clip;
        }

        .paper-box-manufacturer-page * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        .vpn-lp-minimal-header {
            background: #fff;
            border-bottom: 1px solid var(--pbm-line);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .vpn-lp-minimal-header__inner {
            align-items: center;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            margin: 0 auto;
            min-height: 70px;
            padding: 10px 20px;
            width: min(1180px, 100%);
        }

        .vpn-lp-minimal-header__logo img {
            display: block;
            height: auto;
            max-height: 48px;
            width: auto;
        }

        .vpn-lp-minimal-header__actions {
            align-items: center;
            display: flex;
            gap: 10px;
        }

        .vpn-lp-minimal-header__actions a,
        .vpn-lp-minimal-header__menu {
            align-items: center;
            border-radius: 6px;
            display: inline-flex;
            font-size: 14px;
            font-weight: 850;
            justify-content: center;
            min-height: 42px;
            padding: 10px 14px;
            text-decoration: none;
            white-space: nowrap;
        }

        .vpn-lp-minimal-header__quote {
            background: var(--pbm-blue);
            color: #fff;
        }

        .vpn-lp-quote-text-short {
            display: none;
        }

        .vpn-lp-minimal-header__whatsapp {
            border: 1px solid var(--pbm-line);
            color: var(--pbm-blue);
        }

        .vpn-lp-minimal-header__menu {
            background: #f4f8fb;
            border: 1px solid var(--pbm-line);
            color: var(--pbm-ink);
            cursor: pointer;
            display: none;
            min-width: 42px;
            padding: 0;
        }

        .vpn-lp-minimal-header__drawer {
            display: none;
        }

        .vpn-lp-minimal-header__drawer[hidden] {
            display: none !important;
        }

        .pbm-wrap {
            margin: 0 auto;
            width: min(1180px, calc(100% - 32px));
        }

        .pbm-section {
            padding: 76px 0;
        }

        .pbm-soft {
            background: var(--pbm-soft);
        }

        .pbm-eyebrow {
            color: var(--pbm-blue);
            display: inline-flex;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: 0;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .paper-box-manufacturer-page h1,
        .paper-box-manufacturer-page h2,
        .paper-box-manufacturer-page h3,
        .paper-box-manufacturer-page p {
            margin-top: 0;
        }

        .paper-box-manufacturer-page h1,
        .paper-box-manufacturer-page h2,
        .paper-box-manufacturer-page h3 {
            color: var(--pbm-ink);
            letter-spacing: 0;
        }

        .paper-box-manufacturer-page p {
            color: var(--pbm-muted);
            line-height: 1.68;
        }

        .pbm-head {
            margin-bottom: 30px;
            max-width: 790px;
        }

        .pbm-head h2 {
            font-size: clamp(28px, 3vw, 40px);
            line-height: 1.16;
            margin-bottom: 12px;
        }

        .pbm-hero {
            background: var(--pbm-blue-dark);
            color: #fff;
            overflow: hidden;
            padding: 66px 0;
            position: relative;
        }

        .pbm-hero::before {
            background-image: url("<?php echo esc_url($image_url('paper-box-manufacturer-vietnam-factory-hero.webp')); ?>");
            background-position: center;
            background-size: cover;
            content: "";
            inset: 0;
            opacity: .42;
            position: absolute;
        }

        .pbm-hero::after {
            background: linear-gradient(90deg, rgba(18, 63, 92, .96) 0%, rgba(42, 106, 146, .88) 48%, rgba(42, 106, 146, .54) 100%);
            content: "";
            inset: 0;
            position: absolute;
        }

        .pbm-hero > .pbm-wrap {
            position: relative;
            z-index: 1;
        }

        .pbm-hero-grid {
            align-items: start;
            display: grid;
            gap: 46px;
            grid-template-columns: minmax(0, 1fr) minmax(380px, 440px);
        }

        .pbm-hero h1 {
            color: #fff;
            font-size: clamp(42px, 5vw, 62px);
            line-height: 1.04;
            margin-bottom: 18px;
            max-width: 720px;
        }

        .pbm-hero .pbm-eyebrow {
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 999px;
            color: #e6f4ff;
            padding: 7px 11px;
        }

        .pbm-hero-copy > p {
            color: #edf6ff;
            font-size: 18px;
            line-height: 1.62;
            max-width: 720px;
        }

        .pbm-bullets {
            display: grid;
            gap: 10px;
            list-style: none;
            margin: 22px 0 28px;
            padding: 0;
        }

        .pbm-bullets li {
            align-items: center;
            color: #fff;
            display: flex;
            font-weight: 760;
            gap: 10px;
            line-height: 1.45;
        }

        .pbm-bullets i {
            color: var(--pbm-accent);
        }

        .pbm-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .pbm-btn,
        .pbm-card-quote {
            align-items: center;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            font-weight: 850;
            justify-content: center;
            min-height: 48px;
            padding: 13px 18px;
            text-decoration: none;
        }

        .pbm-btn-primary,
        .pbm-card-quote,
        .pbm-submit {
            background: var(--pbm-blue);
            color: #fff;
        }

        .pbm-hero .pbm-btn-primary {
            background: #fff;
            color: var(--pbm-blue);
        }

        .pbm-btn-secondary {
            border: 1px solid rgba(255, 255, 255, .36);
            color: #fff;
        }

        .pbm-quote-card {
            background: #fff;
            border: 1px solid rgba(223, 231, 239, .94);
            border-radius: 8px;
            box-shadow: 0 22px 55px rgba(0, 22, 45, .28);
            color: var(--pbm-ink);
            padding: 20px;
            scroll-margin-top: 90px;
        }

        .pbm-form-head span {
            color: var(--pbm-blue);
            display: block;
            font-size: 12px;
            font-weight: 850;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .pbm-form-head h2 {
            font-size: 24px;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .pbm-form-head p {
            font-size: 13px;
            line-height: 1.48;
            margin-bottom: 14px;
        }

        .pbm-form-grid {
            display: grid;
            gap: 11px;
            grid-template-columns: 1fr 1fr;
        }

        .pbm-form-grid .pbm-full {
            grid-column: 1 / -1;
        }

        .pbm-quote-form label {
            display: block;
            margin: 0;
        }

        .pbm-quote-form label span {
            color: var(--pbm-ink);
            display: block;
            font-size: 13px;
            font-weight: 780;
            margin-bottom: 5px;
        }

        .pbm-quote-form input,
        .pbm-quote-form select,
        .pbm-quote-form textarea {
            background: #fff;
            border: 1px solid var(--pbm-line);
            border-radius: 6px;
            color: var(--pbm-ink);
            font: inherit;
            font-size: 14px;
            min-height: 42px;
            padding: 9px 10px;
            width: 100%;
        }

        .pbm-quote-form textarea {
            min-height: 96px;
            resize: vertical;
        }

        .pbm-quote-form input[type="file"] {
            background: #f8fbfd;
            min-height: 44px;
        }

        .pbm-quote-form input:focus,
        .pbm-quote-form select:focus,
        .pbm-quote-form textarea:focus {
            border-color: var(--pbm-blue);
            box-shadow: 0 0 0 3px rgba(42, 106, 146, .14);
            outline: none;
        }

        .pbm-hp {
            height: 1px;
            left: -9999px;
            opacity: 0;
            position: absolute;
            width: 1px;
        }

        .pbm-captcha-label {
            max-width: 220px;
        }

        .pbm-captcha-label input {
            max-width: 140px;
        }

        .pbm-captcha-help {
            color: var(--pbm-muted);
            font-size: 12px;
            line-height: 1.45;
            margin: 7px 0 0;
        }

        .pbm-field-error {
            color: #a8321a;
            display: block;
            font-size: 12px;
            font-style: normal;
            margin-top: 5px;
        }

        .pbm-field-invalid input,
        .pbm-field-invalid select,
        .pbm-field-invalid textarea {
            border-color: #d34a2f;
        }

        .pbm-submit {
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 850;
            margin-top: 14px;
            min-height: 48px;
            width: 100%;
        }

        .pbm-submit:hover {
            background: var(--pbm-blue-dark);
        }

        .pbm-submit[disabled] {
            cursor: wait;
            opacity: .72;
        }

        .pbm-quote-form .custom-box-human-check {
            margin: 14px 0 0;
        }

        .pbm-quote-form .custom-box-human-check-label {
            display: grid;
            gap: 7px;
            max-width: 260px;
        }

        .pbm-quote-form .custom-box-human-check-label span {
            color: var(--pbm-ink);
            font-weight: 780;
        }

        .pbm-quote-form .custom-box-human-check-label input {
            max-width: 160px;
        }

        .pbm-privacy-note {
            font-size: 12px;
            line-height: 1.45;
            margin: 10px 0 0;
            text-align: center;
        }

        .pbm-message {
            border-radius: 6px;
            font-weight: 760;
            margin-bottom: 14px;
            padding: 10px 12px;
        }

        .pbm-message-success {
            background: #e8f7ef;
            color: #126136;
        }

        .pbm-message-pending {
            background: #fff7e6;
            color: #7a4b00;
        }

        .pbm-message-failed,
        .pbm-message-missing,
        .pbm-message-invalid,
        .pbm-message-file,
        .pbm-message-captcha,
        .pbm-message-spam,
        .pbm-message-rate_limited {
            background: #fff0e8;
            color: #9a3b10;
        }

        .pbm-form-step {
            border: 0;
            margin: 0;
            padding: 0;
        }

        .pbm-form-step + .pbm-form-step {
            border-top: 1px solid var(--pbm-line);
            margin-top: 14px;
            padding-top: 14px;
        }

        .pbm-form-step legend {
            color: var(--pbm-blue);
            font-size: 12px;
            font-weight: 850;
            margin-bottom: 10px;
            padding: 0;
            text-transform: uppercase;
        }

        .pbm-trust-bar {
            background: #fff;
            border-bottom: 1px solid var(--pbm-line);
            padding: 24px 0;
        }

        .pbm-trust-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(4, 1fr);
        }

        .pbm-trust-item,
        .pbm-step,
        .pbm-product-card,
        .pbm-option-card,
        .pbm-faq-item {
            background: #fff;
            border: 1px solid var(--pbm-line);
            border-radius: 8px;
        }

        .pbm-trust-item {
            align-items: center;
            display: flex;
            gap: 12px;
            min-height: 76px;
            padding: 16px;
        }

        .pbm-trust-item i {
            color: var(--pbm-blue);
            font-size: 22px;
            width: 28px;
        }

        .pbm-trust-item strong {
            color: var(--pbm-ink);
            display: block;
            font-size: 15px;
            line-height: 1.32;
        }

        .pbm-product-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(4, 1fr);
        }

        .pbm-product-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .pbm-product-card img {
            aspect-ratio: 4 / 3;
            display: block;
            height: auto;
            object-fit: cover;
            width: 100%;
        }

        .pbm-product-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 16px;
        }

        .pbm-product-body h3 {
            font-size: 19px;
            line-height: 1.24;
            margin-bottom: 8px;
        }

        .pbm-product-body p {
            font-size: 14px;
            line-height: 1.58;
            margin-bottom: 14px;
        }

        .pbm-card-meta {
            color: var(--pbm-blue);
            display: block;
            font-size: 12px;
            font-weight: 800;
            margin: -4px 0 12px;
        }

        .pbm-card-quote {
            border: 0;
            margin-top: auto;
            min-height: 42px;
            padding: 10px 12px;
            width: 100%;
        }

        .pbm-split {
            align-items: start;
            display: grid;
            gap: 38px;
            grid-template-columns: minmax(0, .92fr) minmax(0, 1fr);
        }

        .pbm-factory-copy {
            position: sticky;
            top: 100px;
        }

        .pbm-list {
            display: grid;
            gap: 11px;
            list-style: none;
            margin: 20px 0 0;
            padding: 0;
        }

        .pbm-list li {
            align-items: flex-start;
            color: var(--pbm-muted);
            display: flex;
            gap: 10px;
            line-height: 1.58;
        }

        .pbm-list i {
            color: var(--pbm-green);
            margin-top: 5px;
        }

        .pbm-factory-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, 1fr);
        }

        .pbm-image-card {
            background: #fff;
            border: 1px solid var(--pbm-line);
            border-radius: 8px;
            overflow: hidden;
        }

        .pbm-image-card img {
            aspect-ratio: 16 / 9;
            display: block;
            height: auto;
            object-fit: cover;
            width: 100%;
        }

        .pbm-image-card figcaption {
            color: var(--pbm-muted);
            font-size: 14px;
            line-height: 1.5;
            padding: 12px 14px;
        }

        .pbm-options-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(5, 1fr);
        }

        .pbm-option-card {
            padding: 18px;
        }

        .pbm-option-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .pbm-option-card ul {
            display: grid;
            gap: 7px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .pbm-option-card li {
            color: var(--pbm-muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .pbm-process-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(3, 1fr);
        }

        .pbm-step {
            padding: 22px;
        }

        .pbm-step strong {
            align-items: center;
            background: var(--pbm-blue);
            border-radius: 50%;
            color: #fff;
            display: inline-flex;
            height: 40px;
            justify-content: center;
            margin-bottom: 16px;
            width: 40px;
        }

        .pbm-step h3 {
            font-size: 19px;
            margin-bottom: 8px;
        }

        .pbm-section-cta {
            margin-top: 24px;
        }

        .pbm-detail-form-shell {
            background: var(--pbm-blue-dark);
            color: #fff;
        }

        .pbm-detail-form-shell h2,
        .pbm-detail-form-shell p,
        .pbm-detail-form-shell .pbm-eyebrow {
            color: #fff;
        }

        .pbm-detail-form-shell .pbm-quote-card {
            box-shadow: none;
        }

        .pbm-contact-lines {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin: 18px 0 24px;
        }

        .pbm-contact-lines a {
            color: #fff;
            font-weight: 850;
            text-decoration: none;
        }

        .pbm-faq-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, 1fr);
        }

        .pbm-faq-item {
            padding: 18px;
        }

        .pbm-faq-item h3 {
            font-size: 18px;
            line-height: 1.35;
            margin-bottom: 8px;
        }

        .pbm-sticky-cta {
            display: none;
        }

        @media (max-width: 1080px) {
            .pbm-hero-grid,
            .pbm-split {
                grid-template-columns: 1fr;
            }

            .pbm-product-grid,
            .pbm-options-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .pbm-factory-copy {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .vpn-lp-minimal-header {
                box-shadow: 0 8px 24px rgba(18, 50, 67, .08);
                min-height: 64px;
            }

            .vpn-lp-minimal-header__inner {
                display: flex;
                gap: 10px;
                justify-content: space-between;
                min-height: 64px;
                padding: 0 16px;
            }

            .vpn-lp-minimal-header__logo img {
                max-height: 40px;
            }

            .vpn-lp-minimal-header__logo {
                flex: 1 1 auto;
                min-width: 0;
            }

            .vpn-lp-minimal-header__actions {
                display: flex;
                flex: 0 0 auto;
                gap: 8px;
            }

            .vpn-lp-minimal-header__whatsapp {
                display: none;
            }

            .vpn-lp-minimal-header__quote {
                font-size: 14px;
                min-height: 40px;
                padding: 0 14px;
            }

            .vpn-lp-quote-text-long {
                display: none;
            }

            .vpn-lp-quote-text-short {
                display: inline;
            }

            .vpn-lp-minimal-header__menu {
                display: inline-flex;
                height: 40px;
                width: 40px;
            }

            .vpn-lp-minimal-header__drawer {
                background: #fff;
                border-top: 1px solid var(--pbm-line);
                box-shadow: 0 20px 42px rgba(18, 50, 67, .16);
                display: none;
                left: 0;
                max-height: calc(100vh - 64px);
                overflow-y: auto;
                padding: 8px 16px 14px;
                position: absolute;
                right: 0;
                top: 100%;
                z-index: 1001;
            }

            .vpn-lp-minimal-header__drawer:not([hidden]) {
                display: block;
            }

            .vpn-lp-minimal-header__drawer a,
            .vpn-lp-minimal-header__drawer button {
                align-items: center;
                background: #fff;
                border: 0;
                border-bottom: 1px solid var(--pbm-line);
                border-radius: 0;
                color: var(--pbm-ink);
                display: flex;
                font: inherit;
                font-size: 14px;
                font-weight: 800;
                justify-content: flex-start;
                min-height: 44px;
                padding: 0 4px;
                text-align: left;
                text-decoration: none;
                width: 100%;
            }

            .vpn-lp-minimal-header__drawer a:last-child,
            .vpn-lp-minimal-header__drawer button:last-child {
                border-bottom: 0;
            }

            .paper-box-manufacturer-page {
                padding-bottom: 86px;
            }

            .pbm-section {
                padding: 46px 0;
            }

            .pbm-hero {
                padding: 24px 0 34px;
            }

            .pbm-hero::after {
                background: linear-gradient(180deg, rgba(18, 63, 92, .95) 0%, rgba(42, 106, 146, .9) 100%);
            }

            .pbm-hero h1 {
                font-size: clamp(31px, 9vw, 42px);
            }

            .pbm-hero-copy > p {
                font-size: 16px;
            }

            .pbm-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .pbm-btn {
                width: 100%;
            }

            .pbm-trust-grid,
            .pbm-process-grid,
            .pbm-factory-grid,
            .pbm-faq-grid {
                grid-template-columns: 1fr;
            }

            .pbm-form-grid {
                grid-template-columns: 1fr;
            }

            .pbm-options-grid {
                grid-template-columns: 1fr;
            }

            .pbm-product-grid {
                gap: 10px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pbm-product-body {
                padding: 12px;
            }

            .pbm-product-body h3 {
                font-size: 15px;
                line-height: 1.28;
            }

            .pbm-product-body p {
                display: -webkit-box;
                font-size: 12px;
                line-height: 1.45;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
                overflow: hidden;
            }

            .pbm-card-meta {
                font-size: 11px;
            }

            .pbm-card-quote {
                font-size: 13px;
                min-height: 40px;
                padding: 9px 8px;
            }

            .pbm-quote-card {
                padding: 16px;
            }

            .pbm-quote-form input,
            .pbm-quote-form select,
            .pbm-quote-form textarea {
                font-size: 16px;
                min-height: 46px;
            }

            .pbm-sticky-cta {
                background: rgba(255, 255, 255, .98);
                border-top: 1px solid var(--pbm-line);
                bottom: 0;
                display: block;
                left: 0;
                padding: 8px;
                position: fixed;
                right: 0;
                transition: transform .18s ease;
                z-index: 999;
            }

            body.pbm-is-form-focused .pbm-sticky-cta {
                pointer-events: none;
                transform: translateY(110%);
            }

            .pbm-sticky-cta-inner {
                display: grid;
                gap: 8px;
                grid-template-columns: .78fr .98fr 1.24fr;
            }

            .pbm-sticky-cta a {
                align-items: center;
                border-radius: 6px;
                display: flex;
                font-size: 15px;
                font-weight: 850;
                justify-content: center;
                min-height: 44px;
                padding: 10px;
                text-decoration: none;
            }

            .pbm-sticky-cta__quote {
                background: var(--pbm-blue);
                color: #fff;
            }

            .pbm-sticky-cta__whatsapp,
            .pbm-sticky-cta__call {
                background: #f2f6fa;
                color: var(--pbm-blue);
            }
        }
    </style>
    <?php
}, 30);

$render_select_options = function ($options) {
    foreach ($options as $option) {
        printf(
            '<option value="%1$s">%2$s</option>',
            esc_attr($option),
            esc_html($option)
        );
    }
};

$render_paper_box_quote_form = function ($form_id, $title, $location) use ($box_types, $quantity_options, $material_options, $printing_options, $finishing_options, $quote_status, $quote_messages, $page_url, $render_select_options) {
    $is_hero = 'hero' === $location;
    $contact_required = $is_hero ? 'false' : 'true';
    ?>
    <div class="pbm-quote-card" id="<?php echo $is_hero ? 'quote-form' : esc_attr($form_id); ?>">
        <form class="pbm-quote-form" id="<?php echo esc_attr($form_id); ?>" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" data-form-location="<?php echo esc_attr($location); ?>" data-contact-required="<?php echo esc_attr($contact_required); ?>" novalidate>
            <input type="hidden" name="action" value="custom_box_quote_form">
            <input type="hidden" name="quote_source" value="paper_box_manufacturer">
            <input type="hidden" name="form_location" value="<?php echo esc_attr($location); ?>">
            <input type="hidden" name="form_anchor" value="<?php echo esc_attr($form_id); ?>">
            <input type="hidden" name="product_name" value="Paper Box Manufacturer Landing Page">
            <input type="hidden" name="full_name" value="Paper box quote lead">
            <input type="hidden" name="width" value="">
            <input type="hidden" name="depth" value="">
            <input type="hidden" name="unit" value="mm">
            <?php if ($is_hero) : ?>
                <input type="hidden" name="material_preference" value="Need recommendation">
                <input type="hidden" name="printing_option" value="Need recommendation">
                <input type="hidden" name="finishing_option" value="Need recommendation">
            <?php endif; ?>
            <input type="hidden" name="email_subject" value="[VPN Quote Request] Paper Box Manufacturer Landing Page">
            <input type="hidden" name="current_page_url" value="<?php echo esc_url($page_url); ?>">
            <input type="hidden" name="referrer_url" value="">
            <input type="hidden" name="utm_source" value="">
            <input type="hidden" name="utm_medium" value="">
            <input type="hidden" name="utm_campaign" value="">
            <input type="hidden" name="utm_term" value="">
            <input type="hidden" name="utm_content" value="">
            <?php custom_box_quote_form_anti_spam_fields('paper_box_manufacturer_' . $location); ?>
            <input type="hidden" name="custom_box_quote_nonce" value="<?php echo esc_attr(wp_create_nonce('custom_box_quote_form')); ?>">

            <div class="pbm-form-head">
                <span>Factory quotation</span>
                <h2><?php echo esc_html($title); ?></h2>
                <p><?php echo $is_hero ? esc_html('Send a few project details. We will reply with structure, material, printing, and factory price guidance.') : esc_html('The more details you provide, the faster we can prepare an accurate factory quotation.'); ?></p>
            </div>

            <div class="pbm-message-wrap" aria-live="polite">
                <?php if ($quote_status && isset($quote_messages[$quote_status])) : ?>
                    <p class="pbm-message pbm-message-<?php echo esc_attr($quote_status); ?>"><?php echo esc_html($quote_messages[$quote_status]); ?></p>
                <?php endif; ?>
            </div>

            <fieldset class="pbm-form-step">
                <legend>Step 1: Packaging Need</legend>
                <div class="pbm-form-grid">
                    <label<?php echo $is_hero ? ' class="pbm-full"' : ''; ?>>
                        <span>Box Type *</span>
                        <select name="stock_option" required>
                            <option value="">Select box type</option>
                            <?php $render_select_options($box_types); ?>
                        </select>
                        <em class="pbm-field-error"></em>
                    </label>

                    <label>
                        <span>Estimated Quantity *</span>
                        <select name="quantity" required>
                            <option value="">Select quantity</option>
                            <?php $render_select_options($quantity_options); ?>
                        </select>
                        <em class="pbm-field-error"></em>
                    </label>

                    <label>
                        <span>Delivery Country *</span>
                        <input type="text" name="country" autocomplete="country-name" required>
                        <em class="pbm-field-error"></em>
                    </label>
                </div>
            </fieldset>

            <?php if (!$is_hero) : ?>
                <fieldset class="pbm-form-step">
                    <legend>Step 2: Box Details</legend>
                    <div class="pbm-form-grid">
                        <label>
                            <span>Size (Optional)</span>
                            <input type="text" name="length" placeholder="Example: 220 x 160 x 70 mm">
                            <em class="pbm-field-error"></em>
                        </label>

                        <label>
                            <span>Material (Optional)</span>
                            <select name="material_preference">
                                <option value="">Select material</option>
                                <?php $render_select_options($material_options); ?>
                            </select>
                            <em class="pbm-field-error"></em>
                        </label>

                        <label>
                            <span>Printing / Finishing (Optional)</span>
                            <select name="printing_option">
                                <option value="">Select printing</option>
                                <?php $render_select_options($printing_options); ?>
                            </select>
                            <em class="pbm-field-error"></em>
                        </label>

                        <label>
                            <span>Finishing Requirement (Optional)</span>
                            <select name="finishing_option">
                                <option value="">Select finishing</option>
                                <?php $render_select_options($finishing_options); ?>
                            </select>
                            <em class="pbm-field-error"></em>
                        </label>

                        <label class="pbm-full">
                            <span>Upload Artwork or Reference Image (Optional)</span>
                            <input type="file" name="artwork" accept=".png,.pdf,.jpg,.jpeg,.webp,.doc,.docx,.gif,.psd,.cdr,.eps">
                            <em class="pbm-field-error"></em>
                        </label>
                    </div>
                </fieldset>
            <?php endif; ?>

            <fieldset class="pbm-form-step">
                <legend><?php echo $is_hero ? esc_html('Step 2: Contact') : esc_html('Step 3: Contact'); ?></legend>
                <div class="pbm-form-grid">
                    <label>
                        <span>Business Email *</span>
                        <input type="email" name="email" autocomplete="email" required>
                        <em class="pbm-field-error"></em>
                    </label>

                    <label>
                        <span>WhatsApp / Phone (Optional)</span>
                        <input type="text" name="phone" autocomplete="tel">
                        <em class="pbm-field-error"></em>
                    </label>

                    <?php if (!$is_hero) : ?>
                        <label>
                            <span>Company Name (Optional)</span>
                            <input type="text" name="company" autocomplete="organization">
                            <em class="pbm-field-error"></em>
                        </label>

                        <label class="pbm-full">
                            <span>Message (Optional)</span>
                            <textarea name="message" rows="4" placeholder="Share product type, deadline, packaging style, insert needs, artwork status, or shipping details."></textarea>
                            <em class="pbm-field-error"></em>
                        </label>
                    <?php endif; ?>
                </div>
            </fieldset>

            <?php custom_box_quote_form_math_challenge_fields('paper_box_manufacturer_' . $location); ?>

            <button class="pbm-submit" type="submit">Get My Factory Quote</button>
            <p class="pbm-privacy-note">Your project details are confidential. We only use them to prepare your packaging quotation.</p>
        </form>
    </div>
    <?php
};

get_header();
?>

<main class="paper-box-manufacturer-page">
    <section class="pbm-hero">
        <div class="pbm-wrap pbm-hero-grid">
            <div class="pbm-hero-copy">
                <span class="pbm-eyebrow">Paper box manufacturer in Vietnam</span>
                <h1>Custom Paper Box Manufacturer in Vietnam</h1>
                <p>Factory-direct custom paper boxes for global B2B buyers, importers, distributors, and brands. We manufacture rigid boxes, folding cartons, gift boxes, cosmetic boxes, food boxes, paper bags, and custom printed packaging with export-ready packing.</p>

                <ul class="pbm-bullets">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Factory-direct pricing</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Custom size, structure, printing, finishing, and inserts</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Free dieline and packaging structure support</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Quote within 24 hours</span></li>
                </ul>

                <div class="pbm-actions">
                    <a class="pbm-btn pbm-btn-primary pbm-js-quote-cta" href="#quote-form" data-cta="hero-get-factory-quote">Get Factory Quote</a>
                    <a class="pbm-btn pbm-btn-secondary pbm-js-box-type-cta" href="#box-type" data-cta="hero-choose-box-type">Choose Box Type</a>
                </div>
            </div>

            <?php $render_paper_box_quote_form('paper-box-quote', 'Get a Factory Quote', 'hero'); ?>
        </div>
    </section>

    <section class="pbm-trust-bar" aria-label="Factory trust proof">
        <div class="pbm-wrap pbm-trust-grid">
            <?php foreach ($trust_items as $item) : ?>
                <div class="pbm-trust-item">
                    <i class="<?php echo esc_attr($item[0]); ?>" aria-hidden="true"></i>
                    <strong><?php echo esc_html($item[1]); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="pbm-section" id="box-type">
        <div class="pbm-wrap">
            <div class="pbm-head">
                <span class="pbm-eyebrow">Choose packaging type</span>
                <h2>What Paper Box Do You Need?</h2>
                <p>Choose the closest box type first. Each card sends buyers back to the quote form so they can request structure, size, material, printing, finishing, inserts, and packing support without leaving the landing page.</p>
            </div>

            <div class="pbm-product-grid">
                <?php foreach ($manufacture_cards as $card) : ?>
                    <article class="pbm-product-card">
                        <img src="<?php echo esc_url($card['image'] ? $card['image'] : $image_url('paper-packaging-categories-manufacturer.webp')); ?>" alt="<?php echo esc_attr($card['title'] . ' product category'); ?>" width="800" height="600" loading="lazy" decoding="async">
                        <div class="pbm-product-body">
                            <h3><?php echo esc_html($card['title']); ?></h3>
                            <span class="pbm-card-meta"><?php echo esc_html(sprintf('%d product samples', (int) $card['count'])); ?></span>
                            <p><?php echo esc_html($card['description']); ?></p>
                            <button class="pbm-card-quote pbm-js-quote-cta" type="button" data-box-type="<?php echo esc_attr($card['box_type']); ?>" data-category-url="<?php echo esc_url($card['category_url']); ?>" data-cta="<?php echo esc_attr('card-' . sanitize_title($card['slug'])); ?>">Request Quote</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="pbm-section pbm-soft" id="factory-proof">
        <div class="pbm-wrap pbm-split">
            <div class="pbm-factory-copy">
                <span class="pbm-eyebrow">Factory proof</span>
                <h2>Real Factory. Real Production. Export-Ready Packaging.</h2>
                <p>VPN Paper Box supports B2B buyers with custom paper packaging production, sample development, bulk manufacturing, and export-ready packing from Vietnam.</p>
                <ul class="pbm-list">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Workshop and sample development support for custom paper boxes and paper bags with logo.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Production planning for rigid boxes, folding cartons, gift boxes, cosmetic boxes, and food boxes.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Export cartons, shipping marks, and packing details prepared for international B2B orders.</span></li>
                </ul>
            </div>

            <div class="pbm-factory-grid" aria-label="Real paper box factory images">
                <?php foreach ($factory_images as $factory_image) : ?>
                    <figure class="pbm-image-card">
                        <img src="<?php echo esc_url($image_url($factory_image['image'])); ?>" alt="<?php echo esc_attr($factory_image['caption']); ?>" width="800" height="500" loading="lazy" decoding="async">
                        <figcaption><?php echo esc_html($factory_image['caption']); ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="pbm-section" id="materials-finishing">
        <div class="pbm-wrap">
            <div class="pbm-head">
                <span class="pbm-eyebrow">Options</span>
                <h2>Custom Materials, Printing & Finishing Options</h2>
                <p>Every factory quote is prepared around your product weight, box structure, brand positioning, order quantity, delivery country, and export packing requirements.</p>
            </div>

            <div class="pbm-options-grid">
                <?php foreach ($option_groups as $group_title => $items) : ?>
                    <article class="pbm-option-card">
                        <h3><?php echo esc_html($group_title); ?></h3>
                        <ul>
                            <?php foreach ($items as $item) : ?>
                                <li><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="pbm-section pbm-soft" id="quote-process">
        <div class="pbm-wrap">
            <div class="pbm-head">
                <span class="pbm-eyebrow">Quote process</span>
                <h2>How to Get a Factory Quote</h2>
                <p>We keep the quotation process practical for buyers comparing a paper box manufacturer, custom paper box manufacturer, paper packaging manufacturer, or packaging boxes manufacturer in Vietnam.</p>
            </div>

            <div class="pbm-process-grid">
                <article class="pbm-step">
                    <strong>1</strong>
                    <h3>Choose your box type</h3>
                    <p>Choose your box type and share your packaging needs so we can match the right structure and production method.</p>
                </article>
                <article class="pbm-step">
                    <strong>2</strong>
                    <h3>Send key quote details</h3>
                    <p>Send size, quantity, material, printing, finishing, artwork status, and delivery country.</p>
                </article>
                <article class="pbm-step">
                    <strong>3</strong>
                    <h3>Receive a factory plan</h3>
                    <p>Receive factory quotation, sample plan, and production timeline for review before ordering.</p>
                </article>
            </div>

            <div class="pbm-section-cta">
                <a class="pbm-btn pbm-btn-primary pbm-js-quote-cta" href="#quote-form" data-cta="process-start-my-quote">Start My Quote</a>
            </div>
        </div>
    </section>

    <section class="pbm-section pbm-detail-form-shell" id="detailed-quote">
        <div class="pbm-wrap pbm-split">
            <div>
                <span class="pbm-eyebrow">Detailed quote</span>
                <h2>Request a Detailed Packaging Quote</h2>
                <p>The more details you provide, the faster we can prepare an accurate factory quotation.</p>
                <div class="pbm-contact-lines">
                    <span>WhatsApp: <a class="pbm-js-whatsapp" href="https://wa.me/84933102653" target="_blank" rel="noopener" data-cta="detail-whatsapp">+84 933 102 653</a></span>
                    <span>Email: <a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a></span>
                </div>
                <ul class="pbm-bullets">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Required: box type, quantity, delivery country, and business email.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Optional: company name, size, material, printing, finishing, artwork, and message.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>We use your details only to prepare your custom packaging quote.</span></li>
                </ul>
            </div>

            <?php $render_paper_box_quote_form('paper-box-quote-bottom', 'Get My Factory Quote', 'footer'); ?>
        </div>
    </section>

    <section class="pbm-section" id="faq">
        <div class="pbm-wrap">
            <div class="pbm-head">
                <span class="pbm-eyebrow">FAQ</span>
                <h2>Custom Paper Box Manufacturer FAQ</h2>
                <p>Quick answers for B2B buyers preparing a packaging quote request.</p>
            </div>

            <div class="pbm-faq-grid">
                <?php foreach ($faq_items as $item) : ?>
                    <article class="pbm-faq-item">
                        <h3><?php echo esc_html($item['question']); ?></h3>
                        <p><?php echo esc_html($item['answer']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="pbm-sticky-cta" aria-label="Mobile quote actions">
        <div class="pbm-sticky-cta-inner">
            <a class="pbm-sticky-cta__call pbm-js-call" href="tel:+84933102653" data-cta="mobile-sticky-call">Call</a>
            <a class="pbm-sticky-cta__whatsapp pbm-js-whatsapp" href="https://wa.me/84933102653" target="_blank" rel="noopener" data-cta="mobile-sticky-whatsapp">WhatsApp</a>
            <a class="pbm-sticky-cta__quote pbm-js-quote-cta" href="#quote-form" data-cta="mobile-sticky-get-quote">Get Quote</a>
        </div>
    </div>
</main>

<script>
(function() {
    function pushEvent(name, data) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push(Object.assign({ event: name }, data || {}));
    }

    function setupMinimalHeaderMenu() {
        var toggle = document.querySelector('[data-pbm-menu-toggle]');
        var menu = document.querySelector('[data-pbm-mobile-menu]');
        var header = document.querySelector('.vpn-lp-minimal-header');

        if (!toggle || !menu) {
            return;
        }

        function setOpen(isOpen) {
            menu.hidden = !isOpen;
            menu.classList.toggle('is-open', isOpen);
            toggle.classList.toggle('is-open', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        setOpen(false);

        toggle.addEventListener('click', function() {
            setOpen(menu.hidden);
        });

        menu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                setOpen(false);
            });
        });

        var copyButton = menu.querySelector('[data-pbm-copy-phone]');
        if (copyButton) {
            copyButton.addEventListener('click', function() {
                var phone = '+84933102653';
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(phone).catch(function() {});
                }
                copyButton.textContent = 'Phone Copied';
                window.setTimeout(function() {
                    copyButton.textContent = 'WeChat / Viber';
                    setOpen(false);
                }, 700);
            });
        }

        document.addEventListener('click', function(event) {
            if (!menu.hidden && header && !header.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', function(event) {
            if ('Escape' === event.key && !menu.hidden) {
                setOpen(false);
                toggle.focus({ preventScroll: true });
            }
        });

        window.addEventListener('resize', function() {
            if (window.matchMedia('(min-width: 769px)').matches) {
                setOpen(false);
            }
        });
    }

    setupMinimalHeaderMenu();

    var forms = document.querySelectorAll('.pbm-quote-form');
    var quoteCtas = document.querySelectorAll('.pbm-js-quote-cta');
    var boxTypeCtas = document.querySelectorAll('.pbm-js-box-type-cta');
    var whatsappLinks = document.querySelectorAll('.pbm-js-whatsapp');
    var callLinks = document.querySelectorAll('.pbm-js-call');
    var statusMessages = {
        success: 'Your request has been received. Our team will reply within 24 hours.',
        failed: 'Sorry, we could not send your request right now. Please try again or contact sales.vpn@hopgiayvpn.com.',
        missing: 'Please fill in the required quote fields.',
        invalid: 'The form session expired. Please refresh the page and try again.',
        file: 'Please upload a valid artwork file under 10MB.',
        captcha: 'Please complete the simple security question correctly.',
        spam: 'Sorry, this request could not be accepted.',
        rate_limited: 'Too many quote requests. Please wait a few minutes and try again.'
    };

    function setFieldError(field, message) {
        var label = field.closest('label');
        if (!label) {
            return;
        }

        var error = label.querySelector('.pbm-field-error');
        label.classList.toggle('pbm-field-invalid', Boolean(message));
        if (error) {
            error.textContent = message || '';
        }
    }

    function setSelectValue(select, value) {
        if (!select || !value) {
            return;
        }

        var matched = Array.prototype.some.call(select.options, function(option) {
            if (option.value === value) {
                select.value = value;
                return true;
            }
            return false;
        });

        if (!matched) {
            select.value = '';
        }
    }

    function scrollToForm(boxType) {
        var anchor = document.getElementById('quote-form');
        var form = document.getElementById('paper-box-quote');
        if (!anchor || !form) {
            return;
        }

        setSelectValue(form.querySelector('[name="stock_option"]'), boxType || '');
        anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });

        window.setTimeout(function() {
            var first = form.querySelector('[name="stock_option"]');
            if (first) {
                first.focus({ preventScroll: true });
            }
        }, 450);
    }

    quoteCtas.forEach(function(cta) {
        cta.addEventListener('click', function(event) {
            event.preventDefault();
            pushEvent('click_get_quote', {
                cta: cta.getAttribute('data-cta') || '',
                box_type: cta.getAttribute('data-box-type') || ''
            });
            pushEvent('quote_cta_click', {
                cta: cta.getAttribute('data-cta') || '',
                box_type: cta.getAttribute('data-box-type') || ''
            });
            if (cta.getAttribute('data-box-type')) {
                pushEvent('choose_box_type', {
                    cta: cta.getAttribute('data-cta') || '',
                    box_type: cta.getAttribute('data-box-type') || ''
                });
            }
            scrollToForm(cta.getAttribute('data-box-type') || '');
        });
    });

    boxTypeCtas.forEach(function(cta) {
        cta.addEventListener('click', function(event) {
            var target = document.getElementById('box-type');
            event.preventDefault();
            pushEvent('choose_box_type', {
                cta: cta.getAttribute('data-cta') || 'choose-box-type'
            });
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    whatsappLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            pushEvent('click_whatsapp', {
                cta: link.getAttribute('data-cta') || ''
            });
            pushEvent('whatsapp_click', {
                cta: link.getAttribute('data-cta') || ''
            });
        });
    });

    callLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            pushEvent('click_call', {
                cta: link.getAttribute('data-cta') || ''
            });
        });
    });

    function validateEmailField(field) {
        if (!field || !field.value.trim()) {
            return true;
        }

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value.trim());
    }

    function validateForm(form) {
        var valid = true;
        var required = form.querySelectorAll('[required]');
        var email = form.querySelector('[name="email"]');
        var phone = form.querySelector('[name="phone"]');

        required.forEach(function(field) {
            var message = '';
            if (!field.value.trim()) {
                message = 'This field is required.';
            } else if ('email' === field.type && !validateEmailField(field)) {
                message = 'Please enter a valid business email.';
            }

            setFieldError(field, message);
            if (message) {
                valid = false;
            }
        });

        if (email && !email.hasAttribute('required') && email.value.trim() && !validateEmailField(email)) {
            setFieldError(email, 'Please enter a valid business email.');
            valid = false;
        }

        if ('true' === form.getAttribute('data-contact-required')) {
            var hasEmail = email && email.value.trim() && validateEmailField(email);
            var hasPhone = phone && phone.value.trim();

            if (!hasEmail && !hasPhone) {
                if (email) {
                    setFieldError(email, 'Enter business email or WhatsApp.');
                }
                if (phone) {
                    setFieldError(phone, 'Enter WhatsApp or business email.');
                }
                valid = false;
            }
        }

        return valid;
    }

    forms.forEach(function(form, index) {
        form.addEventListener('focusin', function() {
            document.body.classList.add('pbm-is-form-focused');
        });

        form.addEventListener('focusout', function() {
            window.setTimeout(function() {
                if (!document.activeElement || !document.activeElement.closest('.pbm-quote-form')) {
                    document.body.classList.remove('pbm-is-form-focused');
                }
            }, 120);
        });

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

        var iframeName = 'pbm_quote_submit_' + index + '_' + Date.now();
        var iframe = document.createElement('iframe');
        var submitted = false;
        iframe.name = iframeName;
        iframe.title = 'Quote form submission';
        iframe.hidden = true;
        iframe.style.display = 'none';
        form.parentNode.appendChild(iframe);

        form.addEventListener('submit', function(event) {
            var button = form.querySelector('button[type="submit"]');
            var messageWrap = form.querySelector('.pbm-message-wrap');

            if (!validateForm(form)) {
                event.preventDefault();
                pushEvent('quote_form_submit_error', {
                    reason: 'validation',
                    form_location: form.getAttribute('data-form-location') || ''
                });
                return;
            }

            form.target = iframeName;
            submitted = true;
            if (button) {
                button.disabled = true;
                button.dataset.originalText = button.textContent;
                button.textContent = 'Sending...';
            }
            if (messageWrap) {
                messageWrap.innerHTML = '<p class="pbm-message pbm-message-pending">Sending your request...</p>';
            }
            pushEvent('form_submit', {
                form_location: form.getAttribute('data-form-location') || ''
            });
            pushEvent('quote_form_submit', {
                form_location: form.getAttribute('data-form-location') || ''
            });
        });

        iframe.addEventListener('load', function() {
            var button = form.querySelector('button[type="submit"]');
            var messageWrap = form.querySelector('.pbm-message-wrap');
            var status = '';
            var iframeUrl = '';

            if (!submitted) {
                return;
            }

            try {
                iframeUrl = iframe.contentWindow.location.href;
                if (iframeUrl.indexOf('/thank-you-packaging-quote/') !== -1) {
                    window.location.href = iframeUrl;
                    return;
                }

                status = new URL(iframeUrl).searchParams.get('quote_status') || '';
                if (!status && iframe.contentWindow.document.body && iframe.contentWindow.document.body.textContent.indexOf('Too many quote requests') !== -1) {
                    status = 'rate_limited';
                }
            } catch (error) {
                status = '';
            }

            if (!status) {
                status = 'failed';
            }

            if (messageWrap) {
                if ('success' === status) {
                    messageWrap.innerHTML = '<p class="pbm-message pbm-message-success">' + statusMessages.success + '</p>';
                    form.reset();
                    pushEvent('quote_form_submit_success', {
                        form_location: form.getAttribute('data-form-location') || ''
                    });
                } else {
                    messageWrap.innerHTML = '<p class="pbm-message pbm-message-' + status + '">' + (statusMessages[status] || statusMessages.failed) + '</p>';
                    pushEvent('quote_form_submit_error', {
                        reason: status,
                        form_location: form.getAttribute('data-form-location') || ''
                    });
                }
            }

            submitted = false;
            if (button) {
                button.disabled = false;
                button.textContent = button.dataset.originalText || 'Get My Factory Quote';
            }
        });
    });
})();
</script>

<?php get_footer(); ?>
