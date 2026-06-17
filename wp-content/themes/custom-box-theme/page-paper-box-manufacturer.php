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
            'url'   => $theme_uri . '/assets/images/z7943070206531_9aeff7a9a15c59a3a1c1295e65897b7e.jpg',
            'width' => 1920,
            'height' => 1080,
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
    'success' => 'Đã gửi. Yêu cầu của bạn đã được ghi nhận và đội ngũ của chúng tôi sẽ liên hệ sớm.',
    'failed'  => 'Sorry, we could not send your request right now. Please try again or contact sales.vpn@hopgiayvpn.com.',
    'missing' => 'Please fill in full name, business email, box type, estimated quantity, and delivery country.',
    'invalid' => 'The form session expired. Please refresh the page and try again.',
    'file'    => 'Please upload a valid artwork file under 10MB.',
);

$featured_category_slugs = array(
    'custom-paper-boxes',
    'custom-printed-paper-boxes',
    'rigid-boxes',
    'folding-carton-boxes',
    'magnetic-closure-boxes',
    'drawer-boxes',
    'lid-and-base-boxes',
    'cosmetic-paper-boxes',
    'paper-bags-with-logo',
    'food-paper-boxes',
);

$build_product_category_card = function ($category_item, $group_title = '') {
    $label = isset($category_item[0]) ? (string) $category_item[0] : '';
    $slug = isset($category_item[1]) ? sanitize_title($category_item[1]) : '';
    $image = isset($category_item[2]) ? (string) $category_item[2] : '';

    if (!$slug) {
        return null;
    }

    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term || is_wp_error($term)) {
        return null;
    }

    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
        return null;
    }

    if (!$image && function_exists('custom_box_get_product_category_card_image_url')) {
        $image = custom_box_get_product_category_card_image_url($term, 'medium_large');
    }

    $description = trim(wp_strip_all_tags(term_description($term->term_id, 'product_cat')));
    $description = $description ? wp_trim_words($description, 22, '...') : sprintf('Explore %s packaging options for custom B2B production.', strtolower($term->name));

    return array(
        'name' => $label ? $label : $term->name,
        'slug' => $term->slug,
        'url' => $term_link,
        'image' => $image,
        'description' => $description,
        'quote_type' => $term->name,
        'group' => $group_title,
    );
};

$primary_categories = array();

if (function_exists('custom_box_get_home_packaging_category_groups')) {
    $home_category_items = array();

    foreach (custom_box_get_home_packaging_category_groups() as $category_group) {
        if (!empty($category_group['hidden']) || empty($category_group['items']) || !is_array($category_group['items'])) {
            continue;
        }

        foreach ($category_group['items'] as $category_item) {
            $slug = isset($category_item[1]) ? sanitize_title($category_item[1]) : '';

            if ($slug) {
                $home_category_items[$slug] = array(
                    'item' => $category_item,
                    'group' => $category_group['title'] ?? '',
                );
            }
        }
    }

    foreach ($featured_category_slugs as $featured_slug) {
        if (empty($home_category_items[$featured_slug])) {
            continue;
        }

        $category_item = $home_category_items[$featured_slug]['item'];

        $category_card = $build_product_category_card($category_item, $home_category_items[$featured_slug]['group']);

        if ($category_card) {
            $primary_categories[] = $category_card;
        }
    }
}

$factory_images = array(
    array('z7943073083537_80fea858574510eb5c28efb6511b2bc8.jpg', 'Hands-on production line for rigid boxes and paper packaging assembly.'),
    array('z7943074700018_440d8bf9d030453c89c4b18677406ef1.jpg', 'Factory coordination, planning, and quotation support for international clients.'),
    array('z7943075414782_8e03cb237c0383fe81f3c5d2819b4932.jpg', 'Packaging sample library showing structures, finishes, and brand references.'),
);

$render_paper_box_quote_form = function ($form_id, $title, $location) use ($box_types, $quantity_options, $quote_status, $quote_messages, $page_url) {
    $is_footer = 'footer' === $location;
    $form_subtitle = 'hero' === $location
        ? 'Send your box type, quantity, and delivery country. Our team will suggest structure, material, printing, and finishing.'
        : 'Send your box type, quantity, and delivery country. Our team will help suggest materials, structure, printing, and finishing.';
    $submit_text = 'hero' === $location ? 'Get Free Design & Factory Quote' : 'Get Direct Factory Quote';
    $privacy_text = 'hero' === $location
        ? 'Your information is confidential. We only use it to prepare your packaging quote.'
        : 'Your information is confidential. We only use it to prepare your packaging quotation.';
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
            <p><?php echo esc_html($form_subtitle); ?></p>
        </div>

        <?php if ('hero' === $location) : ?>
            <div class="vpn-lp-form-trust" aria-label="Quote trust points">
                <span>Factory-direct pricing</span>
                <span>Free design support</span>
                <span>Direct factory quote</span>
            </div>
        <?php endif; ?>

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
            <textarea name="message" rows="<?php echo $is_footer ? '4' : '2'; ?>" placeholder="Material, printing, finishing, deadline, artwork status..."></textarea>
            <em class="vpn-lp-field-error"></em>
        </label>

        <button class="vpn-lp-submit" type="submit"><?php echo esc_html($submit_text); ?></button>
        <p class="vpn-lp-privacy-note"><?php echo esc_html($privacy_text); ?></p>
    </form>
    <?php
};
?>

<style>
    .vpn-lp-page { --vpn-lp-blue: #063f7a; --vpn-lp-blue-2: #0b62ad; --vpn-lp-ink: #102033; --vpn-lp-muted: #5b6675; --vpn-lp-line: #dfe7ef; --vpn-lp-soft: #f4f8fb; color: var(--vpn-lp-ink); font-family: inherit; }
    .vpn-lp-page * { box-sizing: border-box; }
    .vpn-lp-wrap { margin: 0 auto; width: min(1180px, calc(100% - 32px)); }
    .vpn-lp-section { padding: 72px 0; }
    .vpn-lp-soft { background: var(--vpn-lp-soft); }
    .vpn-lp-eyebrow { color: var(--vpn-lp-blue); display: inline-flex; font-size: 13px; font-weight: 800; letter-spacing: 0; margin-bottom: 12px; text-transform: uppercase; }
    .vpn-lp-page h1, .vpn-lp-page h2, .vpn-lp-page h3, .vpn-lp-page p { margin-top: 0; }
    .vpn-lp-page h1 { color: #fff; font-size: 58px; letter-spacing: 0; line-height: 1.02; margin-bottom: 18px; }
    .vpn-lp-page h2 { font-size: clamp(28px, 3vw, 42px); letter-spacing: 0; line-height: 1.12; margin-bottom: 16px; }
    .vpn-lp-page h3 { font-size: 20px; line-height: 1.25; margin-bottom: 8px; }
    .vpn-lp-page p { color: var(--vpn-lp-muted); line-height: 1.7; }
    .vpn-lp-hero { background: transparent; overflow: hidden; padding: 72px 0 42px; position: relative; }
    .vpn-lp-hero::before { background-image: url("<?php echo esc_url($image_url('z7943070206531_9aeff7a9a15c59a3a1c1295e65897b7e.jpg')); ?>"); background-position: center; background-repeat: no-repeat; background-size: cover; content: ""; inset: 0; position: absolute; transform: none; z-index: 0; }
    .vpn-lp-hero::after { background-image: radial-gradient(circle at 26% 38%, rgba(39,137,203,.34), transparent 32%), linear-gradient(90deg, rgba(5,35,71,.96) 0%, rgba(6,63,122,.9) 43%, rgba(8,75,131,.52) 67%, rgba(255,255,255,.13) 100%), linear-gradient(180deg, rgba(3,26,55,.2), rgba(3,26,55,.18)); content: ""; inset: 0; position: absolute; z-index: 0; }
    .vpn-lp-hero > .vpn-lp-wrap { position: relative; z-index: 1; }
    .vpn-lp-hero-grid { align-items: start; display: grid; gap: 48px; grid-template-areas: "copy form"; grid-template-columns: minmax(0, 1fr) minmax(390px, 440px); }
    .vpn-lp-hero-copy { grid-area: copy; }
    .vpn-lp-hero-form { grid-area: form; min-width: 0; width: 100%; }
    .vpn-lp-hero-trust { margin-top: 12px; max-width: 640px; }
    .vpn-lp-hero-copy .vpn-lp-eyebrow { background: rgba(255,255,255,.11); border: 1px solid rgba(255,255,255,.22); border-radius: 999px; color: #d8efff; padding: 8px 12px; }
    .vpn-lp-hero-copy p { color: #eaf4fc; font-size: 18px; line-height: 1.62; max-width: 680px; }
    .vpn-lp-hero-offer { background: linear-gradient(135deg, rgba(255,255,255,.18), rgba(64,154,214,.12)); border: 1px solid rgba(210,237,255,.42); border-radius: 14px; box-shadow: inset 0 1px 0 rgba(255,255,255,.18), 0 18px 44px rgba(0,28,62,.18); margin: 22px 0 18px; max-width: 660px; padding: 17px 19px; }
    .vpn-lp-hero-offer-badge { background: rgba(255,255,255,.16); border: 1px solid rgba(232,246,255,.32); border-radius: 999px; color: #dff4ff; display: inline-flex; font-size: 12px; font-weight: 850; line-height: 1; margin-bottom: 9px; padding: 7px 10px; text-transform: uppercase; }
    .vpn-lp-hero-offer strong { color: #fff; display: block; font-size: 40px; font-weight: 950; letter-spacing: 0; line-height: 1; margin-bottom: 5px; }
    .vpn-lp-hero-offer-text { color: #fff; display: block; font-size: 17px; font-weight: 850; line-height: 1.35; }
    .vpn-lp-hero-offer em { color: #e8f6ff; display: block; font-size: 14px; font-style: normal; font-weight: 750; margin-top: 6px; }
    .vpn-lp-hero-offer small { color: #cfe4f4; display: block; font-size: 12px; line-height: 1.45; margin-top: 6px; }
    .vpn-lp-trust-list { display: flex; flex-wrap: wrap; gap: 8px; list-style: none; margin: 0; padding: 0; }
    .vpn-lp-trust-list li { align-items: center; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.16); border-radius: 999px; color: #fff; display: inline-flex; font-size: 12px; font-weight: 750; gap: 7px; line-height: 1.3; min-height: 36px; padding: 7px 11px; }
    .vpn-lp-trust-list i { color: #9ee2b8; font-size: 14px; margin-top: 0; }
    .vpn-lp-hero-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 14px; }
    .vpn-lp-btn { align-items: center; border-radius: 6px; display: inline-flex; font-weight: 850; justify-content: center; min-height: 48px; padding: 13px 18px; text-decoration: none; }
    .vpn-lp-btn-primary { background: #fff; box-shadow: 0 14px 30px rgba(0,31,70,.2); color: var(--vpn-lp-blue); }
    .vpn-lp-btn-secondary { color: #ddecf7; min-height: auto; padding: 0; text-decoration: underline; text-underline-offset: 4px; }
    .vpn-lp-quote-form { background: #fff; border: 1px solid rgba(223,231,239,.96); border-radius: 16px; box-shadow: 0 24px 70px rgba(4,33,71,.28); max-width: 100%; padding: 18px; width: 100%; }
    .vpn-lp-form-head span { color: var(--vpn-lp-blue); display: block; font-size: 12px; font-weight: 850; margin-bottom: 4px; text-transform: uppercase; }
    .vpn-lp-form-head h2 { color: var(--vpn-lp-ink); font-size: 22px; margin-bottom: 4px; }
    .vpn-lp-form-head p { font-size: 13px; line-height: 1.45; margin-bottom: 10px; }
    .vpn-lp-form-trust { display: flex; flex-wrap: wrap; gap: 6px; margin: -2px 0 10px; }
    .vpn-lp-form-trust span { background: #f1f7fc; border: 1px solid #d8e8f4; border-radius: 999px; color: #17466f; font-size: 11px; font-weight: 800; padding: 5px 8px; }
    .vpn-lp-form-grid { display: grid; gap: 8px; grid-template-columns: 1fr 1fr; }
    .vpn-lp-quote-form label { display: block; margin-bottom: 8px; }
    .vpn-lp-quote-form label span { color: #1e3148; display: block; font-size: 12px; font-weight: 850; margin-bottom: 4px; }
    .vpn-lp-quote-form input, .vpn-lp-quote-form select, .vpn-lp-quote-form textarea { background: #fff; border: 1px solid #c6d3e1; border-radius: 8px; color: var(--vpn-lp-ink); font: inherit; font-size: 14px; min-height: 40px; padding: 8px 10px; width: 100%; }
    .vpn-lp-quote-form textarea { min-height: 72px; resize: vertical; }
    .vpn-lp-quote-form input[type="file"] { background: #f9fbfd; font-size: 13px; min-height: 34px; padding: 6px 8px; }
    .vpn-lp-quote-form .vpn-lp-hp { height: 1px; left: -9999px; opacity: 0; position: absolute; width: 1px; }
    .vpn-lp-submit { align-items: center; background: var(--vpn-lp-blue); border: 0; border-radius: 8px; color: #fff; cursor: pointer; display: inline-flex; font-size: 15px; font-weight: 850; justify-content: center; min-height: 42px; padding: 9px 16px; width: 100%; }
    .vpn-lp-submit:hover { background: var(--vpn-lp-blue-2); }
    .vpn-lp-submit[disabled] { cursor: wait; opacity: .72; }
    .vpn-lp-privacy-note { font-size: 12px; line-height: 1.35; margin: 8px 0 0; text-align: center; }
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
    .vpn-lp-home-category-grid { display: grid; gap: 14px; grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .vpn-lp-home-category-card { background: #ffffff; border: 1px solid var(--vpn-lp-line); border-radius: 6px; color: #123243; display: block; overflow: hidden; text-decoration: none; transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
    .vpn-lp-home-category-card:hover { border-color: rgba(42, 106, 146, 0.42); box-shadow: 0 14px 30px rgba(42, 106, 146, 0.14); transform: translateY(-2px); }
    .vpn-lp-home-category-image { align-items: center; aspect-ratio: 450 / 570; background: #eef2f5; display: flex; justify-content: center; overflow: hidden; padding: 0; }
    .vpn-lp-home-category-image img { display: block; height: 100%; object-fit: cover; width: 100%; }
    .vpn-lp-home-category-title { align-items: center; color: #123243; display: flex; font-size: 14px; font-weight: 750; justify-content: center; line-height: 1.25; min-height: 54px; padding: 10px 12px; text-align: center; }
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
    @media (max-width: 1180px) {
        .vpn-lp-page h1 { font-size: 50px; }
        .vpn-lp-hero-grid { gap: 34px; grid-template-columns: minmax(0, 1fr) minmax(370px, 420px); }
        .vpn-lp-hero-offer strong { font-size: 36px; }
    }
    @media (max-width: 980px) {
        .vpn-lp-hero { padding: 56px 0 40px; }
        .vpn-lp-hero::before { background-position: center top; }
        .vpn-lp-hero::after { background-image: linear-gradient(180deg, rgba(5,35,71,.94) 0%, rgba(6,63,122,.86) 58%, rgba(6,63,122,.78) 100%); }
        .vpn-lp-hero-grid, .vpn-lp-split { grid-template-columns: 1fr; }
        .vpn-lp-hero-grid { gap: 24px; grid-template-areas: "copy" "form"; }
        .vpn-lp-hero-copy, .vpn-lp-hero-trust { max-width: 760px; }
        .vpn-lp-hero-form { max-width: 720px; }
        .vpn-lp-page h1 { font-size: 44px; max-width: 760px; }
        .vpn-lp-hero-copy p { max-width: 720px; }
        .vpn-lp-hero-actions { margin-bottom: 0; }
        .vpn-lp-hero-trust { margin-top: 12px; }
        .vpn-lp-trust-grid, .vpn-lp-after-grid, .vpn-lp-factory-grid, .vpn-lp-material-grid { grid-template-columns: repeat(2, 1fr); }
        .vpn-lp-home-category-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .vpn-lp-wrap { width: min(100% - 24px, 1180px); }
        .vpn-lp-section { padding: 50px 0; }
        .vpn-lp-hero { padding: 40px 0 34px; }
        .vpn-lp-hero-grid { gap: 20px; }
        .vpn-lp-trust-grid, .vpn-lp-after-grid, .vpn-lp-home-category-grid, .vpn-lp-factory-grid, .vpn-lp-material-grid, .vpn-lp-form-grid { grid-template-columns: 1fr; }
        .vpn-lp-quote-form { padding: 14px; }
        .vpn-lp-page h1 { font-size: 36px; line-height: 1.08; margin-bottom: 14px; }
        .vpn-lp-hero-copy .vpn-lp-eyebrow { font-size: 11px; line-height: 1.25; padding: 7px 10px; }
        .vpn-lp-hero-copy p { font-size: 16px; }
        .vpn-lp-hero-offer { margin: 18px 0 16px; padding: 15px; }
        .vpn-lp-hero-offer-badge { font-size: 11px; }
        .vpn-lp-hero-offer-text { font-size: 16px; }
        .vpn-lp-hero-offer strong { font-size: 32px; line-height: 1.04; white-space: normal; }
        .vpn-lp-hero-actions { align-items: stretch; flex-direction: column; gap: 12px; margin-bottom: 0; }
        .vpn-lp-btn { width: 100%; }
        .vpn-lp-btn-secondary { justify-content: center; min-height: 44px; }
        .vpn-lp-trust-list { gap: 7px; }
        .vpn-lp-form-head h2 { font-size: 21px; }
        .vpn-lp-form-trust span { flex: 1 1 130px; text-align: center; }
        .vpn-lp-sticky-cta { background: #fff; border-top: 1px solid var(--vpn-lp-line); bottom: 0; display: block; left: 0; padding: 10px 12px; position: fixed; right: 0; z-index: 50; }
        .vpn-lp-sticky-cta a { background: var(--vpn-lp-blue); border-radius: 6px; color: #fff; display: flex; font-weight: 850; justify-content: center; min-height: 48px; padding: 13px 18px; text-decoration: none; }
        body { padding-bottom: 70px; }
    }
    @media (max-width: 420px) {
        .vpn-lp-wrap { width: min(100% - 20px, 1180px); }
        .vpn-lp-hero { padding-top: 32px; }
        .vpn-lp-page h1 { font-size: 31px; }
        .vpn-lp-hero-copy p { font-size: 15px; line-height: 1.55; }
        .vpn-lp-hero-offer { padding: 14px; }
        .vpn-lp-hero-offer strong { font-size: 28px; }
        .vpn-lp-hero-offer em { font-size: 13px; }
        .vpn-lp-trust-list li { font-size: 12px; min-height: 36px; padding: 7px 10px; }
        .vpn-lp-quote-form { border-radius: 12px; padding: 12px; }
        .vpn-lp-quote-form input, .vpn-lp-quote-form select, .vpn-lp-quote-form textarea { font-size: 15px; min-height: 40px; }
        .vpn-lp-quote-form textarea { min-height: 68px; }
        .vpn-lp-quote-form input[type="file"] { min-height: 32px; }
    }
</style>

<main class="vpn-lp-page">
    <section class="vpn-lp-hero">
        <div class="vpn-lp-wrap vpn-lp-hero-grid">
            <div class="vpn-lp-hero-copy">
                <span class="vpn-lp-eyebrow">Paper Box Manufacturer in Vietnam</span>
                <h1>Paper Box Manufacturer in Vietnam</h1>
                <p>Factory-direct custom paper boxes, rigid boxes, carton boxes, gift boxes, and printed paper bags for global B2B bulk orders.</p>
                <div class="vpn-lp-hero-offer" aria-label="Bulk order savings">
                    <span class="vpn-lp-hero-offer-badge">Factory direct savings</span>
                    <span class="vpn-lp-hero-offer-text">
                        <strong>Save up to 40%</strong>
                        on large-volume packaging orders
                        <em>Free design support and factory quote before production.</em>
                        <small>Savings depend on size, material, printing, finishing, and quantity.</small>
                    </span>
                </div>
                <div class="vpn-lp-hero-actions">
                    <a class="vpn-lp-btn vpn-lp-btn-primary vpn-lp-js-quote-cta" href="#paper-box-quote" data-event="quote_cta_click">Get Free Design & Quote</a>
                    <a class="vpn-lp-btn vpn-lp-btn-secondary vpn-lp-js-scroll" href="#packaging-options" data-event="quote_cta_click">View Packaging Options</a>
                </div>
                <ul class="vpn-lp-trust-list vpn-lp-hero-trust">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Free design & dieline support</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Direct factory quotation</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Bulk order & export packing support</span></li>
                </ul>
            </div>
            <div class="vpn-lp-hero-form">
                <?php $render_paper_box_quote_form('paper-box-quote', 'Get Your Free Packaging Quote', 'hero'); ?>
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
                <article class="vpn-lp-step"><strong>3</strong><p>We send a direct factory quotation or sample plan quickly.</p></article>
            </div>
        </div>
    </section>

    <section class="vpn-lp-section" id="packaging-options">
        <div class="vpn-lp-wrap">
            <div class="vpn-lp-head">
                <span class="vpn-lp-eyebrow">Manufacturing Range</span>
                <h2>Paper packaging options for B2B bulk orders</h2>
                <p>Choose a key packaging category, then open the category page or request a quote with size, material, printing, finishing, inserts, and export packing details.</p>
            </div>
            <div class="vpn-lp-home-category-grid">
                <?php foreach ($primary_categories as $category) : ?>
                    <a class="vpn-lp-home-category-card" href="<?php echo esc_url($category['url']); ?>" aria-label="<?php echo esc_attr('View ' . $category['name']); ?>">
                        <span class="vpn-lp-home-category-image<?php echo empty($category['image']) ? ' is-empty' : ''; ?>">
                            <?php if ($category['image']) : ?>
                                <img src="<?php echo esc_url($category['image']); ?>" alt="<?php echo esc_attr($category['name']); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>
                        </span>
                        <span class="vpn-lp-home-category-title"><?php echo esc_html($category['name']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vpn-lp-section vpn-lp-soft">
        <div class="vpn-lp-wrap vpn-lp-split">
            <div>
                <span class="vpn-lp-eyebrow">Why Choose Us</span>
                <h2>More than 9 years of B2B paper packaging production experience</h2>
                <p>VPN Paper Box works as a direct manufacturer trusted by multinational buyers and export-focused brands, so every project is handled with clear communication, consistent quality, and production support from quote to shipment.</p>
                <ul class="vpn-lp-list">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Proven experience supporting multinational cooperation and export packaging workflows.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Direct factory communication for faster decisions on structure, sampling, and print details.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Consistent quality control for repeat orders, brand standards, and international delivery needs.</span></li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Flexible production support for bulk orders, private labels, and coordinated global shipments.</span></li>
                </ul>
            </div>
            <figure class="vpn-lp-image-card">
                <img src="<?php echo esc_url($image_url('vietnam-day-packaging-event-vpn-logo.webp')); ?>" alt="Vietnam Day Packaging Event with VPN branding and international partnership context" width="1600" height="900" loading="lazy" decoding="async">
                <figcaption>VPN Paper Box participates in international industry events and works with multinational partners on custom packaging, export requirements, and brand-driven projects.</figcaption>
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
                button.textContent = 'Đã gửi';
            }
            if (messageWrap) {
                messageWrap.innerHTML = '<p class="vpn-lp-form-message vpn-lp-form-message-success">Đã gửi. Yêu cầu của bạn đã được ghi nhận và đang được xử lý.</p>';
            }
        });

        iframe.addEventListener('load', function() {
            var button = form.querySelector('button[type="submit"]');
            var messageWrap = form.querySelector('.vpn-lp-form-message-wrap');
            var status = '';
            var iframeUrl = '';

            try {
                iframeUrl = iframe.contentWindow.location.href;
                if (iframeUrl.indexOf('/thank-you-packaging-quote/') !== -1) {
                    window.location.href = iframeUrl;
                    return;
                }

                status = new URL(iframeUrl).searchParams.get('quote_status') || '';
            } catch (error) {
                status = '';
            }

            if (!status) {
                return;
            }

            if (messageWrap) {
                if ('success' === status) {
                    messageWrap.innerHTML = '<p class="vpn-lp-form-message vpn-lp-form-message-success">Đã gửi. Yêu cầu của bạn đã được ghi nhận và đội ngũ của chúng tôi sẽ liên hệ sớm.</p>';
                    form.reset();
                    pushEvent('quote_form_submit_success', { form_location: form.dataset.formLocation || '' });
                } else {
                    messageWrap.innerHTML = '<p class="vpn-lp-form-message vpn-lp-form-message-' + status + '">Sorry, we could not send your request right now. Please check the fields and try again.</p>';
                    pushEvent('quote_form_submit_error', { reason: status, form_location: form.dataset.formLocation || '' });
                }
            }

            if (button) {
                button.disabled = false;
                button.textContent = button.dataset.originalText || 'Get Free Design & Factory Quote';
            }
        });
    });
})();
</script>

<?php get_footer(); ?>
