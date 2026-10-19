<?php
/**
 * Global custom paper bags landing page.
 */

defined('ABSPATH') || exit;

function custom_box_custom_paper_bags_path() {
    return '/custom-paper-bags-manufacturer/';
}

function custom_box_custom_paper_bags_url() {
    return home_url(custom_box_custom_paper_bags_path());
}

function custom_box_paper_bag_status_messages() {
    return array(
        'success' => 'Thank you. Your request has been received. Our sales team will review your requirements and reply by email.',
        'received' => 'Thank you. Your request has been saved for our sales team to review and follow up by email.',
        'missing' => 'Please add a valid email, quantity and delivery country.',
        'quantity' => 'Please enter a whole-number quantity of at least 1,000 bags.',
        'consent' => 'Please agree to the use of your details for this quotation request.',
        'duplicate' => 'We already received a matching request. Please email sales.vpn@hopgiayvpn.com if you need to add details.',
        'invalid' => 'Your form session expired. Please reload the page and try again.',
        'failed' => 'We could not complete your request. Please try again or email sales.vpn@hopgiayvpn.com.',
        'file' => 'Please upload a supported artwork file no larger than 10MB.',
        'spam' => 'Your request could not be verified. Please reload the page and try again.',
        'captcha' => 'Security verification could not be completed. Please reload the page and try again.',
        'rate_limited' => 'Too many requests. Please wait a few minutes before trying again.',
    );
}

/** Resolve real, catalog-visible paper bags by taxonomy and slug, never local database IDs. */
function custom_box_paper_bag_products() {
    static $products;
    if (null !== $products) { return $products; }
    $products = array();
    if (!function_exists('wc_get_products')) { return $products; }
    $available = wc_get_products(array('status' => 'publish', 'limit' => -1,
        'category' => array('paper-bags-with-logo'), 'orderby' => 'date', 'order' => 'DESC'));
    $by_slug = array();
    foreach ($available as $product) {
        if ($product->is_visible() && $product->get_image_id()) { $by_slug[$product->get_slug()] = $product; }
    }
    $preferred = array(
        'custom-flat-handle-kraft-paper-bags',
        'custom-white-paper-shopping-bag-with-brown-rope-handles',
        'custom-printed-flat-bread-paper-bags',
        'custom-twisted-handle-kraft-paper-bags',
        'custom-white-kraft-paper-bags-twisted-handles',
        'custom-birthday-paper-gift-bag-with-present-print',
        'custom-cosmetic-paper-bag',
        'custom-red-snowflake-twisted-handle-christmas-paper-bag',
        'custom-luxury-paper-gift-bag-with-ribbon-handles',
        'custom-square-bottom-bakery-paper-bags',
        'custom-printed-baguette-paper-bags',
        'custom-paper-bread-bags-with-window',
    );
    foreach ($preferred as $slug) {
        if (isset($by_slug[$slug])) { $products[] = $by_slug[$slug]; unset($by_slug[$slug]); }
    }
    return $products = array_merge($products, array_values($by_slug));
}
/** Use the catalog front view without changing product media in the database. */
function custom_box_paper_bag_image_id($product) {
    return $product->get_image_id();
}
/** Keep the hero selection independent of catalog display order. */
function custom_box_paper_bag_hero_products() {
    $products = custom_box_paper_bag_products();
    $by_slug = array();
    foreach ($products as $product) { $by_slug[$product->get_slug()] = $product; }
    $hero = array();
    foreach (array('custom-twisted-handle-kraft-paper-bags', 'custom-white-kraft-paper-bags-twisted-handles', 'custom-printed-flat-bread-paper-bags') as $slug) {
        if (isset($by_slug[$slug])) { $hero[] = $by_slug[$slug]; }
    }
    foreach ($products as $product) {
        if (count($hero) >= 3) { break; }
        if (!in_array($product, $hero, true)) { $hero[] = $product; }
    }
    return $hero;
}
function custom_box_paper_bag_christmas_products($products, $featured_products) {
    $excluded = array_map(static function ($product) { return $product->get_id(); }, $featured_products);
    $seasonal = array();
    foreach ($products as $product) {
        if (!in_array($product->get_id(), $excluded, true)
            && (false !== strpos($product->get_slug(), 'christmas') || has_term('christmas-packaging', 'product_cat', $product->get_id()))) {
            $seasonal[] = $product;
        }
        if (count($seasonal) === 4) { break; }
    }
    return $seasonal;
}
function custom_box_paper_bag_display_title($product) {
    $title = get_the_title($product->get_id());
    // Legacy catalog entries use all caps. Preserve the name, with readable title casing.
    return function_exists('mb_strtoupper') && $title === mb_strtoupper($title, 'UTF-8')
        ? mb_convert_case($title, MB_CASE_TITLE, 'UTF-8') : $title;
}
function custom_box_paper_bag_product_image($product, $hero = false, $priority = false) {
    $image_id = custom_box_paper_bag_image_id($product);
    $front_crop = 'P02-01-kraft-flat-handle-main.webp' === wp_basename((string) get_post_meta($image_id, '_wp_attached_file', true));
    return wp_get_attachment_image($image_id, 'woocommerce_single', false, array(
        'class' => 'attachment-woocommerce_single size-woocommerce_single' . ($front_crop ? ' cpb-photo-front-crop' : ''),
        'alt' => get_the_title($product->get_id()), 'loading' => $priority ? 'eager' : 'lazy',
        'decoding' => 'async', 'fetchpriority' => $priority ? 'high' : 'auto',
        'sizes' => $hero ? '(max-width: 800px) 30vw, 200px' : '(max-width: 600px) 45vw, (max-width: 1000px) 30vw, 282px',
    ));
}
function custom_box_paper_bag_hero_image_url() {
    $products = custom_box_paper_bag_hero_products();
    return $products ? wp_get_attachment_image_url(custom_box_paper_bag_image_id($products[0]), 'woocommerce_single')
        : get_template_directory_uri() . '/assets/images/logo-hop-giay-vpn-hcm.webp';
}

function custom_box_custom_paper_bags_faqs() {
    return array(
        array('question' => 'Do you supply custom printed paper bags wholesale?', 'answer' => 'Yes. VPN manufactures custom printed paper bags for brands, retailers, importers and distributors. The general minimum order is 1,000 bags. Send your email, quantity per design, dimensions, artwork and delivery destination so we can review the specification and quote your order.'),
        array('question' => 'Can I request a quote for a large-volume paper bag order?', 'answer' => 'Yes. Share the total quantity and the quantity for each size or design, together with your required receiving date and delivery locations. We review the sampling, production schedule, packing and shipment scope for the actual order. Order acceptance and timing are confirmed with the quotation; production and transit time are separate.'),
        array('question' => 'What should I compare when choosing custom paper bag suppliers?', 'answer' => 'Compare the same bag specification and quantity: paper, dimensions, handles, print, finish, sample approval, quality checks and packing. Check which sample, production and freight charges are included, and confirm delivery terms, duties and taxes. Send your brief by email so we can explain the scope of the VPN quotation.'),
        array('question' => 'What affects the price of bulk paper bags with logo printing?', 'answer' => 'The quotation depends on dimensions, paper grade, handle construction, artwork and printing, finishing, quantity per design and packing requirements. Freight is reviewed against the delivery destination and shipment scope. Share your planned quantities and email to request a specification-based quote; a higher quantity alone does not determine the final unit price.'),
        array('question' => 'What is the minimum order for custom paper bags?', 'answer' => 'The general minimum order is 1,000 bags. Share your size, paper bag style, quantity and destination so we can confirm the project specification and quotation.'),
        array('question' => 'Can you make paper bags with our logo and custom size?', 'answer' => 'Yes. We manufacture paper bags to your requested dimensions and artwork. Paper, handles, CMYK or Pantone printing and finishes are reviewed for the bag and the product it will carry. Design support is available.'),
        array('question' => 'Can I request a custom sample, and what does it cost?', 'answer' => 'Samples are made to the requested design, and the sample fee depends on the design requirements. Sampling takes 7–15 days depending on the order. Orders over 5,000 pcs qualify for a refund of 50% of the sample fee.'),
        array('question' => 'How long does paper bag production take?', 'answer' => 'Production takes 7–45 days after sample approval, depending on the order. This is production time; transit time is separate and depends on the shipment and destination.'),
        array('question' => 'Can international businesses request a quote?', 'answer' => 'Yes. VPN accepts custom paper bag enquiries from international brands, importers, distributors and retail chains. Production is based in Vietnam. Share your delivery country or region, city, postal code and required receiving date. Shipment arrangements, delivery terms, duties and taxes are confirmed for your destination with the quotation.'),
        array('question' => 'What should I send if I do not know the specifications?', 'answer' => 'Start with your email, estimated quantity, delivery country and the product you want to carry. Dimensions, packed weight, a logo or a reference image help us review the paper, handle and construction options, but you can add these details later.'),
        array('question' => 'Do you accept dimensions in inches?', 'answer' => 'Yes. You can use inches, millimeters or centimeters. Specify finished width × height × gusset; the gusset is the side depth. Include the unit and confirm final dimensions and product fit on the sample.'),
        array('question' => 'How are quality and packing checked?', 'answer' => 'Quality is checked at each production stage against the approved specification. Review print and finish, folds, gluing, handle attachments and carry requirements. Finished bags can be packed in cartons or shrink-wrapped bundles according to the order.'),
        array('question' => 'Are recycled paper or certified materials available?', 'answer' => 'Paper options can be reviewed for your project. State any recycled-content, certification or end-use requirements when requesting a quote so the exact material and supporting documentation can be confirmed. Do not assume all papers, coatings or handles have the same environmental properties.'),
        array('question' => 'Can you make matching paper bags and boxes?', 'answer' => 'Yes. Paper bags and custom paper boxes can be developed together around the product, artwork and quantity. The materials, color direction and finishes are confirmed during the quotation and sample review.'),
    );
}

/**
 * The landing page is code-backed rather than a database Page post. WordPress
 * and SEO plugins still need a complete queried object for body classes,
 * Open Graph and schema generation.
 */
function custom_box_custom_paper_bags_virtual_post() {
    static $virtual_post = null;

    if ($virtual_post instanceof WP_Post) {
        return $virtual_post;
    }

    $now = current_time('mysql');
    $virtual_post = new WP_Post(
        (object) array(
            'ID'                    => 0,
            'post_author'           => 0,
            'post_date'             => $now,
            'post_date_gmt'         => get_gmt_from_date($now),
            'post_content'          => '',
            'post_title'            => 'Custom Paper Bag Manufacturer in Vietnam',
            'post_excerpt'          => custom_box_custom_paper_bags_description(),
            'post_status'           => 'publish',
            'comment_status'        => 'closed',
            'ping_status'           => 'closed',
            'post_password'         => '',
            'post_name'             => 'custom-paper-bags-manufacturer',
            'to_ping'               => '',
            'pinged'                => '',
            'post_modified'         => $now,
            'post_modified_gmt'     => get_gmt_from_date($now),
            'post_content_filtered' => '',
            'post_parent'           => 0,
            'guid'                  => custom_box_custom_paper_bags_url(),
            'menu_order'            => 0,
            'post_type'             => 'page',
            'post_mime_type'        => '',
            'comment_count'         => 0,
            'filter'                => 'raw',
        )
    );

    return $virtual_post;
}

function custom_box_custom_paper_bags_set_query_context($wp_query) {
    if (!$wp_query) {
        return;
    }

    if (empty($wp_query->queried_object)) {
        $wp_query->queried_object = custom_box_custom_paper_bags_virtual_post();
        $wp_query->queried_object_id = 0;
    }

    global $post;
    if (!($post instanceof WP_Post)) {
        $post = $wp_query->queried_object;
        setup_postdata($post);
    }

    $wp_query->is_404 = false;
    $wp_query->is_page = true;
    $wp_query->is_singular = true;
}

function custom_box_is_custom_paper_bags_manufacturer_landing() {
    if (is_admin()) {
        return false;
    }

    return custom_box_current_request_path() === custom_box_custom_paper_bags_path()
        || (function_exists('is_page') && is_page('custom-paper-bags-manufacturer'));
}

function custom_box_custom_paper_bags_add_rewrite() {
    add_rewrite_rule(
        '^custom-paper-bags-manufacturer/?$',
        'index.php?pagename=custom-paper-bags-manufacturer',
        'top'
    );
}
add_action('init', 'custom_box_custom_paper_bags_add_rewrite');

function custom_box_custom_paper_bags_map_request($query_vars) {
    if (isset($query_vars['pagename']) && 'custom-paper-bags-manufacturer' === trim($query_vars['pagename'], '/')) {
        $query_vars['pagename'] = 'custom-paper-bags-manufacturer';
    }

    return $query_vars;
}
add_filter('request', 'custom_box_custom_paper_bags_map_request', 1);

function custom_box_custom_paper_bags_parse_request($wp) {
    if (custom_box_current_request_path() !== custom_box_custom_paper_bags_path()) {
        return;
    }

    $wp->query_vars = array('pagename' => 'custom-paper-bags-manufacturer');
}
add_action('parse_request', 'custom_box_custom_paper_bags_parse_request', 2);

function custom_box_custom_paper_bags_prevent_404($preempt, $wp_query) {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing()) {
        return $preempt;
    }

    custom_box_custom_paper_bags_set_query_context($wp_query);

    status_header(200);

    return true;
}
add_filter('pre_handle_404', 'custom_box_custom_paper_bags_prevent_404', 10, 2);

function custom_box_custom_paper_bags_template($template) {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing()) {
        return $template;
    }

    $landing_template = get_template_directory() . '/page-custom-paper-bags-manufacturer.php';
    if (!file_exists($landing_template)) {
        return $template;
    }

    global $wp_query;
    custom_box_custom_paper_bags_set_query_context($wp_query);

    status_header(200);

    return $landing_template;
}
add_filter('template_include', 'custom_box_custom_paper_bags_template', 20);

function custom_box_custom_paper_bags_body_class($classes) {
    if (custom_box_is_custom_paper_bags_manufacturer_landing()) {
        $classes[] = 'vpn-custom-paper-bags-body';
    }

    return $classes;
}
add_filter('body_class', 'custom_box_custom_paper_bags_body_class', 30);

function custom_box_custom_paper_bags_enqueue_assets() {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing()) {
        return;
    }

    $css_path = get_template_directory() . '/assets/css/custom-paper-bags.css';
    $js_path = get_template_directory() . '/assets/js/custom-paper-bags.js';
    wp_enqueue_style(
        'custom-paper-bags-style',
        get_template_directory_uri() . '/assets/css/custom-paper-bags.css',
        array('main-style', 'responsive-style'),
        file_exists($css_path) ? filemtime($css_path) : '1.0'
    );
    wp_enqueue_script(
        'custom-paper-bags-script',
        get_template_directory_uri() . '/assets/js/custom-paper-bags.js',
        array('main-js'),
        file_exists($js_path) ? filemtime($js_path) : '1.0',
        true
    );
    wp_script_add_data('custom-paper-bags-script', 'defer', true);
    wp_localize_script('custom-paper-bags-script', 'vpnPaperBagQuote', array('messages' => custom_box_paper_bag_status_messages()));
}
add_action('wp_enqueue_scripts', 'custom_box_custom_paper_bags_enqueue_assets', 30);

function custom_box_custom_paper_bags_locale($locale) {
    return custom_box_is_custom_paper_bags_manufacturer_landing() ? 'en_US' : $locale;
}
add_filter('locale', 'custom_box_custom_paper_bags_locale', 30);

function custom_box_custom_paper_bags_language_attributes($attributes) {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing()) {
        return $attributes;
    }

    if (preg_match('/\blang="[^"]*"/', $attributes)) {
        return preg_replace('/\blang="[^"]*"/', 'lang="en-US"', $attributes);
    }

    return trim($attributes . ' lang="en-US"');
}
add_filter('language_attributes', 'custom_box_custom_paper_bags_language_attributes', 30);

function custom_box_custom_paper_bags_title() {
    return 'Custom Paper Bag Manufacturer | Wholesale Logo Bags | VPN';
}

function custom_box_custom_paper_bags_description() {
    return 'Custom paper bag manufacturer and supplier in Vietnam for international brands. Wholesale bags with your logo. MOQ 1,000. Request a quote.';
}

function custom_box_custom_paper_bags_document_title($title) {
    return custom_box_is_custom_paper_bags_manufacturer_landing() ? custom_box_custom_paper_bags_title() : $title;
}
add_filter('pre_get_document_title', 'custom_box_custom_paper_bags_document_title', 30);
add_filter('rank_math/frontend/title', 'custom_box_custom_paper_bags_document_title', 30);

function custom_box_custom_paper_bags_description_filter($description) {
    return custom_box_is_custom_paper_bags_manufacturer_landing() ? custom_box_custom_paper_bags_description() : $description;
}
add_filter('rank_math/frontend/description', 'custom_box_custom_paper_bags_description_filter', 30);

function custom_box_paper_bag_social_image($image) {
    return custom_box_is_custom_paper_bags_manufacturer_landing()
        ? custom_box_paper_bag_hero_image_url() : $image;
}
add_filter('rank_math/opengraph/facebook/image', 'custom_box_paper_bag_social_image', 30);
add_filter('rank_math/opengraph/twitter/image', 'custom_box_paper_bag_social_image', 30);

function custom_box_custom_paper_bags_canonical($canonical) {
    return custom_box_is_custom_paper_bags_manufacturer_landing() ? custom_box_custom_paper_bags_url() : $canonical;
}
add_filter('rank_math/frontend/canonical', 'custom_box_custom_paper_bags_canonical', 30);
add_filter('get_canonical_url', 'custom_box_custom_paper_bags_canonical', 30);

function custom_box_custom_paper_bags_robots($robots) {
    if (custom_box_is_custom_paper_bags_manufacturer_landing()) {
        $robots['index'] = get_option('blog_public') ? 'index' : 'noindex';
        $robots['follow'] = 'follow';
    }

    return $robots;
}
add_filter('rank_math/frontend/robots', 'custom_box_custom_paper_bags_robots', 30);

function custom_box_custom_paper_bags_fallback_meta() {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing() || defined('RANK_MATH_VERSION')) {
        return;
    }

    $url = custom_box_custom_paper_bags_url();
    $image = custom_box_paper_bag_hero_image_url();
    ?>
    <meta name="description" content="<?php echo esc_attr(custom_box_custom_paper_bags_description()); ?>">
    <link rel="canonical" href="<?php echo esc_url($url); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo esc_attr(custom_box_custom_paper_bags_title()); ?>">
    <meta property="og:description" content="<?php echo esc_attr(custom_box_custom_paper_bags_description()); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:image" content="<?php echo esc_url($image); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr(custom_box_custom_paper_bags_title()); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr(custom_box_custom_paper_bags_description()); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($image); ?>">
    <?php
}
add_action('wp_head', 'custom_box_custom_paper_bags_fallback_meta', 1);

function custom_box_custom_paper_bags_preload_hero() {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing()) {
        return;
    }

    $products = custom_box_paper_bag_hero_products();
    if (!$products) { return; }
    printf('<link rel="preload" as="image" href="%s" imagesrcset="%s" imagesizes="(max-width: 800px) 30vw, 200px" fetchpriority="high">' . "\n",
        esc_url(custom_box_paper_bag_hero_image_url()),
        esc_attr(wp_get_attachment_image_srcset(custom_box_paper_bag_image_id($products[0]), 'woocommerce_single') ?: ''));

}
add_action('wp_head', 'custom_box_custom_paper_bags_preload_hero', 2);

function custom_box_paper_bag_schema_nodes() {
    $url = custom_box_custom_paper_bags_url();
    return array(
        'webpage' => array('@type' => 'WebPage', '@id' => $url . '#webpage', 'url' => $url,
            'name' => custom_box_custom_paper_bags_title(), 'description' => custom_box_custom_paper_bags_description(),
            'inLanguage' => 'en-US', 'about' => array('@id' => $url . '#service'),
            'primaryImageOfPage' => array('@type' => 'ImageObject', 'url' => custom_box_paper_bag_hero_image_url())),
        'service' => array('@type' => 'Service', '@id' => $url . '#service', 'url' => $url,
            'name' => 'Custom Paper Bag Manufacturing', 'serviceType' => 'Custom printed paper bag manufacturing',
            'provider' => array('@id' => home_url('/#organization')),
            'areaServed' => 'Worldwide'),
    );
}
add_filter('rank_math/json_ld', static function ($data) {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing() || !is_array($data)) { return $data; }
    $nodes = custom_box_paper_bag_schema_nodes();
    $webpage = $nodes['webpage'];
    foreach ($data as $key => $node) {
        $types = is_array($node) ? (array) ($node['@type'] ?? array()) : array();
        if (in_array('FAQPage', $types, true) || in_array('Service', $types, true)) { unset($data[$key]); }
        if (in_array('WebPage', $types, true)) { $data[$key] = array_merge($node, $webpage); unset($nodes['webpage']); }
        if (array_intersect($types, array('Organization', 'LocalBusiness'))) {
            unset($data[$key]['priceRange']);
            $data[$key]['areaServed'] = $nodes['service']['areaServed'];
        }
    }
    foreach ($nodes as $key => $node) { $data['vpn-paper-bag-' . $key] = $node; }
    return $data;
}, 95);

function custom_box_custom_paper_bags_schema() {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing() || defined('RANK_MATH_VERSION')) { return; }
    $nodes = array_values(custom_box_paper_bag_schema_nodes());
    $organization = function_exists('custom_box_get_business_schema') ? custom_box_get_business_schema() : array(
        '@type' => 'Organization', 'name' => 'VPN Paper Box', 'legalName' => 'Công ty TNHH Quảng Cáo VPN', 'url' => home_url('/'));
    $organization['@id'] = home_url('/#organization');
    unset($organization['priceRange']);
    $organization['areaServed'] = $nodes[1]['areaServed'];
    $nodes[] = $organization;
    echo '<script type="application/ld+json">' . wp_json_encode(array('@context' => 'https://schema.org', '@graph' => $nodes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_custom_paper_bags_schema', 25);
