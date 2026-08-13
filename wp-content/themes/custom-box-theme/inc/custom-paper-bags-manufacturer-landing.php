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
            'post_title'            => 'Custom Paper Bags With Logo',
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
    $woocommerce_css_file = file_exists(get_template_directory() . '/assets/css/woocommerce.min.css') ? 'woocommerce.min.css' : 'woocommerce.css';
    $woocommerce_css_path = get_template_directory() . '/assets/css/' . $woocommerce_css_file;
    $product_archive_fix_path = get_template_directory() . '/assets/css/product-archive-fix.css';

    wp_enqueue_style(
        'woocommerce-theme-style',
        get_template_directory_uri() . '/assets/css/' . $woocommerce_css_file,
        array('main-style'),
        file_exists($woocommerce_css_path) ? filemtime($woocommerce_css_path) : '1.0'
    );
    wp_enqueue_style(
        'product-archive-fix-style',
        get_template_directory_uri() . '/assets/css/product-archive-fix.css',
        array('woocommerce-theme-style', 'responsive-style'),
        file_exists($product_archive_fix_path) ? filemtime($product_archive_fix_path) : '1.0'
    );

    wp_enqueue_style(
        'custom-paper-bags-style',
        get_template_directory_uri() . '/assets/css/custom-paper-bags.css',
        array('main-style', 'woocommerce-theme-style', 'responsive-style', 'product-archive-fix-style'),
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
    return 'Custom Paper Bags With Logo | Vietnam Manufacturer | VPN Paper Box';
}

function custom_box_custom_paper_bags_description() {
    return 'Compare custom paper bag materials, handles, printing and finishing options with VPN Paper Box, a Vietnam-based paper packaging supplier for brands and B2B buyers.';
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

function custom_box_custom_paper_bags_canonical($canonical) {
    return custom_box_is_custom_paper_bags_manufacturer_landing() ? custom_box_custom_paper_bags_url() : $canonical;
}
add_filter('rank_math/frontend/canonical', 'custom_box_custom_paper_bags_canonical', 30);
add_filter('get_canonical_url', 'custom_box_custom_paper_bags_canonical', 30);

function custom_box_custom_paper_bags_robots($robots) {
    if (custom_box_is_custom_paper_bags_manufacturer_landing()) {
        $robots['index'] = 'index';
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
    $image = get_template_directory_uri() . '/assets/images/paper-bag-landing/custom-paper-bags-manufacturing-workshop.webp';
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

    printf(
        '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
        esc_url(get_template_directory_uri() . '/assets/images/paper-bag-landing/custom-paper-bags-manufacturing-workshop.webp')
    );
}
add_action('wp_head', 'custom_box_custom_paper_bags_preload_hero', 2);

function custom_box_custom_paper_bags_schema() {
    if (!custom_box_is_custom_paper_bags_manufacturer_landing()) {
        return;
    }

    $url = custom_box_custom_paper_bags_url();
    $image = get_template_directory_uri() . '/assets/images/paper-bag-landing/custom-paper-bags-manufacturing-workshop.webp';
    $faq = array(
        array('question' => 'What information is needed for a paper bag quotation?', 'answer' => 'Share the product being carried, finished size, quantity, delivery country, preferred paper or handles, artwork, and target schedule when known.'),
        array('question' => 'What paper and handle options are available?', 'answer' => 'Options may include brown kraft, white kraft, coated or art paper, specialty paper, twisted paper, flat paper, cotton or PP rope, ribbon, and die-cut handles. The final combination depends on the bag structure, load, finish, and order requirements.'),
        array('question' => 'Can you match Pantone colors and custom artwork?', 'answer' => 'We review CMYK and Pantone requirements together with the artwork, paper surface, and print method. The final color direction is confirmed before production.'),
        array('question' => 'Can I request a sample before mass production?', 'answer' => 'Sampling can be arranged before bulk production when the project requires approval of structure, artwork, material, or finish.'),
        array('question' => 'What is the MOQ for custom paper bags?', 'answer' => 'MOQ depends on the bag structure, material, printing, and quantity requirements. The applicable MOQ is confirmed with the project quotation.'),
        array('question' => 'How is the production timeline confirmed?', 'answer' => 'Sampling and production timing depend on the approved specification, quantity, sampling needs, and order schedule.'),
        array('question' => 'Can the bags be packed for international shipping?', 'answer' => 'Carton packing and shipment details can be reviewed against the approved order specification for international B2B projects.'),
        array('question' => 'Can you produce matching paper bags and boxes?', 'answer' => 'Paper bags and paper boxes can be discussed together when a project requires both. The matching scope is confirmed after reviewing the product, quantity, materials, and artwork.'),
    );
    $faq_entities = array();
    foreach ($faq as $item) {
        $faq_entities[] = array(
            '@type' => 'Question',
            'name' => $item['question'],
            'acceptedAnswer' => array('@type' => 'Answer', 'text' => $item['answer']),
        );
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@graph' => array(
            array(
                '@type' => 'WebPage',
                '@id' => $url . '#webpage',
                'url' => $url,
                'name' => custom_box_custom_paper_bags_title(),
                'description' => custom_box_custom_paper_bags_description(),
                'inLanguage' => 'en-US',
                'primaryImageOfPage' => array('@type' => 'ImageObject', 'url' => $image),
            ),
            array(
                '@type' => 'Service',
                '@id' => $url . '#service',
                'name' => 'Custom Paper Bags With Logo',
                'serviceType' => 'Custom paper bag manufacturing and logo printing',
                'provider' => array('@id' => home_url('/#organization')),
                'areaServed' => array('Vietnam', 'Worldwide'),
                'url' => $url,
            ),
            array('@type' => 'FAQPage', '@id' => $url . '#faq', 'mainEntity' => $faq_entities),
        ),
    );

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_custom_paper_bags_schema', 25);
