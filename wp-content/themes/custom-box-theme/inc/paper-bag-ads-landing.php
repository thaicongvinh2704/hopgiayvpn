<?php
/**
 * English paper bag search-ads landing page.
 */

defined('ABSPATH') || exit;

function custom_box_paper_bag_ads_path() {
    return '/tui-giay-in-theo-yeu-cau/';
}

function custom_box_paper_bag_ads_url() {
    return home_url(custom_box_paper_bag_ads_path());
}

/**
 * This code-backed landing page has no database Page post. Provide the post
 * context expected by WordPress core and SEO plugins before rendering it.
 */
function custom_box_paper_bag_ads_virtual_post() {
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
            'post_title'            => 'Custom Printed Paper Bags',
            'post_excerpt'          => custom_box_paper_bag_ads_description(),
            'post_status'           => 'publish',
            'comment_status'        => 'closed',
            'ping_status'           => 'closed',
            'post_password'         => '',
            'post_name'             => 'tui-giay-in-theo-yeu-cau',
            'to_ping'               => '',
            'pinged'                => '',
            'post_modified'         => $now,
            'post_modified_gmt'     => get_gmt_from_date($now),
            'post_content_filtered' => '',
            'post_parent'           => 0,
            'guid'                  => custom_box_paper_bag_ads_url(),
            'menu_order'            => 0,
            'post_type'             => 'page',
            'post_mime_type'        => '',
            'comment_count'         => 0,
            'filter'                => 'raw',
        )
    );

    return $virtual_post;
}

function custom_box_paper_bag_ads_set_query_context($wp_query) {
    if (!$wp_query) {
        return;
    }

    if (empty($wp_query->queried_object)) {
        $wp_query->queried_object = custom_box_paper_bag_ads_virtual_post();
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

function custom_box_is_paper_bag_ads_landing() {
    if (is_admin()) {
        return false;
    }

    return custom_box_current_request_path() === custom_box_paper_bag_ads_path()
        || (function_exists('is_page') && is_page('tui-giay-in-theo-yeu-cau'));
}

function custom_box_paper_bag_ads_add_rewrite() {
    add_rewrite_rule(
        '^tui-giay-in-theo-yeu-cau/?$',
        'index.php?pagename=tui-giay-in-theo-yeu-cau',
        'top'
    );
}
add_action('init', 'custom_box_paper_bag_ads_add_rewrite');

function custom_box_paper_bag_ads_map_request($query_vars) {
    if (isset($query_vars['pagename']) && 'tui-giay-in-theo-yeu-cau' === trim($query_vars['pagename'], '/')) {
        $query_vars['pagename'] = 'tui-giay-in-theo-yeu-cau';
    }

    return $query_vars;
}
add_filter('request', 'custom_box_paper_bag_ads_map_request', 1);

function custom_box_paper_bag_ads_parse_request($wp) {
    if (custom_box_current_request_path() !== custom_box_paper_bag_ads_path()) {
        return;
    }

    $wp->query_vars = array(
        'pagename' => 'tui-giay-in-theo-yeu-cau',
    );
}
add_action('parse_request', 'custom_box_paper_bag_ads_parse_request', 2);

function custom_box_paper_bag_ads_prevent_404($preempt, $wp_query) {
    if (!custom_box_is_paper_bag_ads_landing()) {
        return $preempt;
    }

    custom_box_paper_bag_ads_set_query_context($wp_query);

    status_header(200);

    return true;
}
add_filter('pre_handle_404', 'custom_box_paper_bag_ads_prevent_404', 10, 2);

function custom_box_paper_bag_ads_template($template) {
    if (!custom_box_is_paper_bag_ads_landing()) {
        return $template;
    }

    $landing_template = get_template_directory() . '/page-paper-bag-ads.php';
    if (!file_exists($landing_template)) {
        return $template;
    }

    global $wp_query;
    custom_box_paper_bag_ads_set_query_context($wp_query);

    status_header(200);

    return $landing_template;
}
add_filter('template_include', 'custom_box_paper_bag_ads_template', 20);

function custom_box_paper_bag_ads_enqueue_assets() {
    if (!custom_box_is_paper_bag_ads_landing()) {
        return;
    }

    $css_path = get_template_directory() . '/assets/css/paper-bag-ads.css';
    $js_path = get_template_directory() . '/assets/js/paper-bag-ads.js';

    wp_enqueue_style(
        'paper-bag-ads-style',
        get_template_directory_uri() . '/assets/css/paper-bag-ads.css',
        array('main-style', 'responsive-style'),
        file_exists($css_path) ? filemtime($css_path) : '1.0'
    );

    wp_enqueue_script(
        'paper-bag-ads-script',
        get_template_directory_uri() . '/assets/js/paper-bag-ads.js',
        array('main-js'),
        file_exists($js_path) ? filemtime($js_path) : '1.0',
        true
    );
    wp_script_add_data('paper-bag-ads-script', 'defer', true);
}
add_action('wp_enqueue_scripts', 'custom_box_paper_bag_ads_enqueue_assets', 30);

function custom_box_paper_bag_ads_locale($locale) {
    return custom_box_is_paper_bag_ads_landing() ? 'en_US' : $locale;
}
add_filter('locale', 'custom_box_paper_bag_ads_locale', 30);

function custom_box_paper_bag_ads_language_attributes($attributes) {
    if (!custom_box_is_paper_bag_ads_landing()) {
        return $attributes;
    }

    if (preg_match('/\blang="[^"]*"/', $attributes)) {
        return preg_replace('/\blang="[^"]*"/', 'lang="en-US"', $attributes);
    }

    return trim($attributes . ' lang="en-US"');
}
add_filter('language_attributes', 'custom_box_paper_bag_ads_language_attributes', 30);

function custom_box_paper_bag_ads_title() {
    return 'Custom Printed Paper Bags | Custom Sizes, Handles & Finishes | VPN';
}

function custom_box_paper_bag_ads_description() {
    return 'Order custom paper bags printed with your logo and made to your specification. VPN supports B2B packaging projects with artwork checks, sampling, and project-based quotations.';
}

function custom_box_paper_bag_ads_document_title($title) {
    return custom_box_is_paper_bag_ads_landing() ? custom_box_paper_bag_ads_title() : $title;
}
add_filter('pre_get_document_title', 'custom_box_paper_bag_ads_document_title', 30);
add_filter('rank_math/frontend/title', 'custom_box_paper_bag_ads_document_title', 30);

function custom_box_paper_bag_ads_rank_math_description($description) {
    return custom_box_is_paper_bag_ads_landing() ? custom_box_paper_bag_ads_description() : $description;
}
add_filter('rank_math/frontend/description', 'custom_box_paper_bag_ads_rank_math_description', 30);

function custom_box_paper_bag_ads_canonical($canonical) {
    return custom_box_is_paper_bag_ads_landing() ? custom_box_paper_bag_ads_url() : $canonical;
}
add_filter('rank_math/frontend/canonical', 'custom_box_paper_bag_ads_canonical', 30);
add_filter('get_canonical_url', 'custom_box_paper_bag_ads_canonical', 30);

function custom_box_paper_bag_ads_robots($robots) {
    if (custom_box_is_paper_bag_ads_landing()) {
        $robots['index'] = 'index';
        $robots['follow'] = 'follow';
    }

    return $robots;
}
add_filter('rank_math/frontend/robots', 'custom_box_paper_bag_ads_robots', 30);

function custom_box_paper_bag_ads_fallback_meta() {
    if (!custom_box_is_paper_bag_ads_landing() || defined('RANK_MATH_VERSION')) {
        return;
    }

    $url = custom_box_paper_bag_ads_url();
    $image = get_template_directory_uri() . '/assets/images/paper-bag-landing/hero-red-shopping-bag.jpeg';
    ?>
    <meta name="description" content="<?php echo esc_attr(custom_box_paper_bag_ads_description()); ?>">
    <link rel="canonical" href="<?php echo esc_url($url); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo esc_attr(custom_box_paper_bag_ads_title()); ?>">
    <meta property="og:description" content="<?php echo esc_attr(custom_box_paper_bag_ads_description()); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:image" content="<?php echo esc_url($image); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr(custom_box_paper_bag_ads_title()); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr(custom_box_paper_bag_ads_description()); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($image); ?>">
    <?php
}
add_action('wp_head', 'custom_box_paper_bag_ads_fallback_meta', 1);

function custom_box_paper_bag_ads_preload_hero() {
    if (!custom_box_is_paper_bag_ads_landing()) {
        return;
    }

    printf(
        '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
        esc_url(get_template_directory_uri() . '/assets/images/paper-bag-landing/hero-red-shopping-bag.jpeg')
    );
}
add_action('wp_head', 'custom_box_paper_bag_ads_preload_hero', 2);

function custom_box_paper_bag_ads_schema() {
    if (!custom_box_is_paper_bag_ads_landing()) {
        return;
    }

    $url = custom_box_paper_bag_ads_url();
    $image = get_template_directory_uri() . '/assets/images/paper-bag-landing/hero-red-shopping-bag.jpeg';
    $faq = array(
        array(
            'question' => 'Does VPN have a fixed MOQ?',
            'answer'   => 'The website does not publish one fixed MOQ for every bag. The minimum quantity must be confirmed against size, material, printing, and finishing requirements.',
        ),
        array(
            'question' => 'Can you make custom bag sizes?',
            'answer'   => 'Yes. VPN presents custom structures and sizes; the team confirms the suitable specification for the product being carried.',
        ),
        array(
            'question' => 'Can I choose the paper and handles?',
            'answer'   => 'You can discuss kraft, white, and other paper choices shown on the category page, together with paper, rope, or ribbon handles depending on the design.',
        ),
        array(
            'question' => 'What if my artwork is not ready?',
            'answer'   => 'Send your logo, expected size, and intended use. VPN presents artwork and dieline checking support; the final approach is confirmed during consultation.',
        ),
        array(
            'question' => 'What affects the paper bag price?',
            'answer'   => 'The quote depends on size, quantity, paper type/GSM, handles, printing, finishing, packing, and delivery destination.',
        ),
        array(
            'question' => 'How long does production take?',
            'answer'   => 'The website does not state one fixed lead time for every order. Send the specification and target date so VPN can confirm a suitable schedule.',
        ),
        array(
            'question' => 'Does VPN support international orders?',
            'answer'   => 'The website presents international B2B support. Delivery terms, packing, and shipping are confirmed for each market.',
        ),
    );

    $faq_entities = array();
    foreach ($faq as $item) {
        $faq_entities[] = array(
            '@type'          => 'Question',
            'name'           => $item['question'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $item['answer'],
            ),
        );
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@graph'   => array(
            array(
                '@type'       => 'WebPage',
                '@id'         => $url . '#webpage',
                'url'         => $url,
                'name'        => custom_box_paper_bag_ads_title(),
                'description' => custom_box_paper_bag_ads_description(),
                'inLanguage'  => 'en-US',
                'primaryImageOfPage' => array('@type' => 'ImageObject', 'url' => $image),
            ),
            array(
                '@type'       => 'Service',
                '@id'         => $url . '#service',
                'name'        => 'Custom Printed Paper Bags',
                'serviceType' => 'Custom paper bag manufacturing and logo printing',
                'provider'    => array('@id' => home_url('/#organization')),
                'areaServed'  => array('Vietnam', 'Worldwide'),
                'url'         => $url,
            ),
            array(
                '@type'       => 'FAQPage',
                '@id'         => $url . '#faq',
                'mainEntity'  => $faq_entities,
            ),
        ),
    );

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_paper_bag_ads_schema', 25);
