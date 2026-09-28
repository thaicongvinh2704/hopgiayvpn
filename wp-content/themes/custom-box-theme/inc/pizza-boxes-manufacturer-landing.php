<?php
/**
 * Code-backed custom pizza box manufacturer landing page.
 */

defined('ABSPATH') || exit;

function custom_box_pizza_boxes_manufacturer_path() {
    return '/custom-pizza-boxes-manufacturer/';
}

function custom_box_pizza_boxes_manufacturer_url() {
    return home_url(custom_box_pizza_boxes_manufacturer_path());
}

/** Shared quote buttons should open the project form on this landing page. */
function custom_box_pizza_boxes_manufacturer_quote_link($url, $path) {
    if ('/contact/#quote' === $path && custom_box_is_pizza_boxes_manufacturer_landing()) {
        return custom_box_pizza_boxes_manufacturer_url() . '#vpb-quote';
    }
    return $url;
}
add_filter('home_url', 'custom_box_pizza_boxes_manufacturer_quote_link', 30, 2);

function custom_box_pizza_boxes_manufacturer_image_url() {
    return get_template_directory_uri() . '/assets/images/pizza-box-landing/vpn-printing-operator-800.webp';
}

/** Share one product query and prime image metadata for all rendered cards. */
function custom_box_pizza_boxes_manufacturer_products() {
    static $products = null;
    if (null !== $products) {
        return $products;
    }

    $products = array();
    $category = get_term_by('slug', 'pizza-boxes', 'product_cat');
    if (!$category || is_wp_error($category)) {
        return $products;
    }

    $products = get_posts(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 8,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
        'no_found_rows' => true,
        'update_post_term_cache' => false,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => (int) $category->term_id,
            'include_children' => true,
        )),
    ));

    $image_ids = array();
    foreach ($products as $product) {
        $image_id = (int) get_post_thumbnail_id($product->ID);
        if ($image_id) {
            $image_ids[] = $image_id;
        }
    }
    if ($image_ids && function_exists('_prime_post_caches')) {
        _prime_post_caches(array_unique($image_ids), false, true);
    }

    return $products;
}

function custom_box_pizza_boxes_manufacturer_factory_image($name, $width = 800) {
    return get_template_directory_uri() . '/assets/images/pizza-box-landing/' . $name . '-' . (int) $width . '.webp';
}

function custom_box_pizza_boxes_manufacturer_hero_preload() {
    if (!custom_box_is_pizza_boxes_manufacturer_landing()) {
        return;
    }
    $name = 'vpn-printing-operator';
    printf(
        '<link rel="preload" as="image" href="%1$s" imagesrcset="%2$s 480w, %1$s 800w, %3$s 1280w" imagesizes="(min-width: 1001px) 667px, (min-width: 721px) 50vw, calc(100vw - 28px)" fetchpriority="high">' . "\n",
        esc_url(custom_box_pizza_boxes_manufacturer_factory_image($name, 800)),
        esc_url(custom_box_pizza_boxes_manufacturer_factory_image($name, 480)),
        esc_url(custom_box_pizza_boxes_manufacturer_factory_image($name, 1280))
    );
}
add_action('wp_head', 'custom_box_pizza_boxes_manufacturer_hero_preload', 2);

function custom_box_pizza_boxes_manufacturer_title() {
    return 'Custom Pizza Boxes for Delivery & Foodservice | VPN Paper Box';
}

function custom_box_pizza_boxes_manufacturer_description() {
    return 'Custom pizza boxes for pizzerias, restaurants and foodservice brands. Review box dimensions, board, print, sample approval and delivery requirements with VPN Paper Box in Vietnam.';
}

function custom_box_pizza_boxes_manufacturer_faqs() {
    return array(
        array(
            'question' => 'What information do you need to quote custom pizza boxes?',
            'answer' => 'Please share the pizza or food item dimensions, box internal size, estimated quantity, artwork, delivery country and target schedule if known. Also mention the delivery bag, stack height, liner or other requirements that affect the structure.',
        ),
        array(
            'question' => 'Can you make square, personal-size and rectangular pizza boxes?',
            'answer' => 'Different footprints can be discussed, including personal-size, square pizza and elongated flatbread formats. The final internal dimensions and closure are set from the actual product and service method.',
        ),
        array(
            'question' => 'Which paper material is suitable for a pizza box?',
            'answer' => 'Paperboard and corrugated directions may be reviewed against the box size, stacking, handling and delivery route. The exact grade, any barrier or liner, and food-contact suitability must be specified and confirmed for the intended market before production.',
        ),
        array(
            'question' => 'Can I print my logo or full-color artwork?',
            'answer' => 'Custom artwork can be prepared on an approved dieline. Print method, color target and finish depend on the selected material; review a proof and, when needed, a physical sample before bulk production.',
        ),
        array(
            'question' => 'Can I approve a sample before placing a bulk order?',
            'answer' => 'A sample or prototype review can be discussed for structure, fit, print and specified materials. The quotation should state what sample is included and what approvals are required before bulk production.',
        ),
        array(
            'question' => 'What is the minimum order quantity and production time?',
            'answer' => 'The current pizza-box product listings show a reference MOQ of 1,000 boxes. Confirm the final MOQ, quantities per size or artwork version, and production schedule in a written quotation after the specification and delivery plan are reviewed.',
        ),
        array(
            'question' => 'Are the pizza-box images verified production samples?',
            'answer' => 'The five pizza-box concepts added to this catalogue use illustrative design images, not verified production samples. The images do not establish exact dimensions, board grade, coating, vents, food-contact status or performance. Confirm those details in the project specification and approved sample.',
        ),
    );
}

function custom_box_pizza_boxes_manufacturer_virtual_post() {
    static $virtual_post = null;

    if ($virtual_post instanceof WP_Post) {
        return $virtual_post;
    }

    $now = current_time('mysql');
    $virtual_post = new WP_Post((object) array(
        'ID' => 0,
        'post_author' => 0,
        'post_date' => $now,
        'post_date_gmt' => get_gmt_from_date($now),
        'post_content' => '',
        'post_title' => custom_box_pizza_boxes_manufacturer_title(),
        'post_excerpt' => custom_box_pizza_boxes_manufacturer_description(),
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'post_password' => '',
        'post_name' => 'custom-pizza-boxes-manufacturer',
        'to_ping' => '',
        'pinged' => '',
        'post_modified' => $now,
        'post_modified_gmt' => get_gmt_from_date($now),
        'post_content_filtered' => '',
        'post_parent' => 0,
        'guid' => custom_box_pizza_boxes_manufacturer_url(),
        'menu_order' => 0,
        'post_type' => 'page',
        'post_mime_type' => '',
        'comment_count' => 0,
        'filter' => 'raw',
    ));

    return $virtual_post;
}

function custom_box_pizza_boxes_manufacturer_set_query_context($wp_query) {
    if (!$wp_query) {
        return;
    }

    if (empty($wp_query->queried_object)) {
        $wp_query->queried_object = custom_box_pizza_boxes_manufacturer_virtual_post();
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

function custom_box_is_pizza_boxes_manufacturer_landing() {
    if (is_admin()) {
        return false;
    }

    return custom_box_current_request_path() === custom_box_pizza_boxes_manufacturer_path()
        || (function_exists('is_page') && is_page('custom-pizza-boxes-manufacturer'));
}

function custom_box_pizza_boxes_manufacturer_add_rewrite() {
    add_rewrite_rule(
        '^custom-pizza-boxes-manufacturer/?$',
        'index.php?pagename=custom-pizza-boxes-manufacturer',
        'top'
    );
}
add_action('init', 'custom_box_pizza_boxes_manufacturer_add_rewrite');

function custom_box_pizza_boxes_manufacturer_map_request($query_vars) {
    if (isset($query_vars['pagename']) && 'custom-pizza-boxes-manufacturer' === trim($query_vars['pagename'], '/')) {
        $query_vars['pagename'] = 'custom-pizza-boxes-manufacturer';
    }

    return $query_vars;
}
add_filter('request', 'custom_box_pizza_boxes_manufacturer_map_request', 1);

function custom_box_pizza_boxes_manufacturer_parse_request($wp) {
    if (custom_box_current_request_path() !== custom_box_pizza_boxes_manufacturer_path()) {
        return;
    }

    $wp->query_vars = array('pagename' => 'custom-pizza-boxes-manufacturer');
}
add_action('parse_request', 'custom_box_pizza_boxes_manufacturer_parse_request', 2);

function custom_box_pizza_boxes_manufacturer_prevent_404($preempt, $wp_query) {
    if (!custom_box_is_pizza_boxes_manufacturer_landing()) {
        return $preempt;
    }

    custom_box_pizza_boxes_manufacturer_set_query_context($wp_query);
    status_header(200);

    return true;
}
add_filter('pre_handle_404', 'custom_box_pizza_boxes_manufacturer_prevent_404', 10, 2);

function custom_box_pizza_boxes_manufacturer_template($template) {
    if (!custom_box_is_pizza_boxes_manufacturer_landing()) {
        return $template;
    }

    $landing_template = get_template_directory() . '/page-pizza-boxes-manufacturer.php';
    if (!file_exists($landing_template)) {
        return $template;
    }

    global $wp_query;
    custom_box_pizza_boxes_manufacturer_set_query_context($wp_query);
    status_header(200);

    return $landing_template;
}
add_filter('template_include', 'custom_box_pizza_boxes_manufacturer_template', 20);

function custom_box_pizza_boxes_manufacturer_body_class($classes) {
    if (custom_box_is_pizza_boxes_manufacturer_landing()) {
        $classes[] = 'vpn-pizza-boxes-manufacturer-body';
    }

    return $classes;
}
add_filter('body_class', 'custom_box_pizza_boxes_manufacturer_body_class', 30);

function custom_box_pizza_boxes_manufacturer_enqueue_assets() {
    if (!custom_box_is_pizza_boxes_manufacturer_landing()) {
        return;
    }

    $css_path = get_template_directory() . '/assets/css/pizza-boxes-manufacturer.css';
    wp_enqueue_style(
        'pizza-boxes-manufacturer-style',
        get_template_directory_uri() . '/assets/css/pizza-boxes-manufacturer.css',
        array('main-style', 'responsive-style'),
        file_exists($css_path) ? filemtime($css_path) : '1.0'
    );
}
add_action('wp_enqueue_scripts', 'custom_box_pizza_boxes_manufacturer_enqueue_assets', 30);

/** Refresh public form tokens independently of any cached landing HTML. */
function custom_box_pizza_boxes_quote_session() {
    nocache_headers();
    if ('POST' !== (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '')) {
        wp_send_json_error(array('message' => 'POST required.'), 405);
    }
    $timestamp = time();
    wp_send_json_success(array(
        'nonce' => wp_create_nonce('custom_box_quote_form'),
        'started_at' => $timestamp,
        'context' => 'quote',
        'signature' => custom_box_quote_form_timestamp_signature($timestamp, 'quote'),
        'minimum_age' => custom_box_quote_form_timestamp_min_age(),
    ));
}
add_action('wp_ajax_custom_box_pizza_quote_session', 'custom_box_pizza_boxes_quote_session');
add_action('wp_ajax_nopriv_custom_box_pizza_quote_session', 'custom_box_pizza_boxes_quote_session');

/** Register before the shared reCAPTCHA submit listener (priority 5). */
function custom_box_pizza_boxes_quote_session_script() {
    if (!custom_box_is_pizza_boxes_manufacturer_landing()) {
        return;
    }
    $script = get_template_directory() . '/assets/js/pizza-boxes-quote.js';
    if (is_readable($script)) {
        echo '<script id="pizza-boxes-quote-session" data-cfasync="false" data-no-optimize="1">' . "\n";
        readfile($script);
        echo "\n</script>\n";
    }
}
add_action('wp_footer', 'custom_box_pizza_boxes_quote_session_script', 4);

function custom_box_pizza_boxes_quote_status_no_cache() {
    if (custom_box_is_pizza_boxes_manufacturer_landing() && isset($_GET['quote_status'])) {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        nocache_headers();
    }
}
add_action('template_redirect', 'custom_box_pizza_boxes_quote_status_no_cache', 0);

/** Keep the shared header icons but load only the glyphs used by the theme. */
function custom_box_pizza_boxes_manufacturer_lean_assets() {
    if (!custom_box_is_pizza_boxes_manufacturer_landing()) {
        return;
    }

    $theme = get_template_directory();
    $shell = $theme . '/assets/css/pizza-landing-shell.css';
    $manifest_path = $theme . '/assets/css/pizza-landing-shell-manifest.json';
    $use_shell = file_exists($shell) && file_exists($manifest_path);
    if ($use_shell) {
        $manifest = json_decode(file_get_contents($manifest_path), true);
        $use_shell = !empty($manifest['sources']);
        foreach (isset($manifest['sources']) ? $manifest['sources'] : array() as $relative => $hash) {
            $source = $theme . '/' . $relative;
            $source_text = file_exists($source) ? file_get_contents($source) : false;
            // Normalize Git's Windows/Linux line endings before comparing.
            if (false === $source_text || !hash_equals($hash, hash('sha256', str_replace(array("\r\n", "\r"), "\n", $source_text)))) {
                $use_shell = false;
                break;
            }
        }
    }
    if ($use_shell) {
        // Preserve inline responsive fixes attached by the shared theme loader.
        $styles = wp_styles();
        $main_inline = $styles->get_data('main-style', 'after');
        $responsive_inline = $styles->get_data('responsive-style', 'after');
        foreach (array('main-style', 'responsive-style') as $handle) {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
        wp_enqueue_style('main-style', get_template_directory_uri() . '/assets/css/pizza-landing-shell.css', array(), filemtime($shell));
        wp_register_style('responsive-style', false, array('main-style'));
        wp_enqueue_style('responsive-style');
        foreach (array_merge((array) $main_inline, (array) $responsive_inline) as $inline_css) {
            if ($inline_css) {
                wp_add_inline_style('responsive-style', $inline_css);
            }
        }
    }

    $icons = get_template_directory() . '/assets/css/pizza-landing-icons.css';
    if ($use_shell && file_exists($icons)) {
        wp_dequeue_style('font-awesome');
        wp_deregister_style('font-awesome');
        wp_enqueue_style('font-awesome', get_template_directory_uri() . '/assets/css/pizza-landing-icons.css', array(), filemtime($icons));
    }

    wp_dequeue_style('custom-box-page-transition');
    wp_dequeue_script('custom-box-page-transition');
    remove_action('wp_body_open', 'custom_box_render_page_transition', 1);
    foreach (array('wp-block-library', 'classic-theme-styles', 'global-styles') as $handle) {
        wp_dequeue_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'custom_box_pizza_boxes_manufacturer_lean_assets', 110);

function custom_box_pizza_boxes_manufacturer_locale($locale) {
    return custom_box_is_pizza_boxes_manufacturer_landing() ? 'en_US' : $locale;
}
add_filter('locale', 'custom_box_pizza_boxes_manufacturer_locale', 30);

function custom_box_pizza_boxes_manufacturer_language_attributes($attributes) {
    if (!custom_box_is_pizza_boxes_manufacturer_landing()) {
        return $attributes;
    }

    if (preg_match('/\blang="[^"]*"/', $attributes)) {
        return preg_replace('/\blang="[^"]*"/', 'lang="en-US"', $attributes);
    }

    return trim($attributes . ' lang="en-US"');
}
add_filter('language_attributes', 'custom_box_pizza_boxes_manufacturer_language_attributes', 30);

function custom_box_pizza_boxes_manufacturer_document_title($title) {
    return custom_box_is_pizza_boxes_manufacturer_landing() ? custom_box_pizza_boxes_manufacturer_title() : $title;
}
add_filter('pre_get_document_title', 'custom_box_pizza_boxes_manufacturer_document_title', 30);
add_filter('rank_math/frontend/title', 'custom_box_pizza_boxes_manufacturer_document_title', 30);

function custom_box_pizza_boxes_manufacturer_description_filter($description) {
    return custom_box_is_pizza_boxes_manufacturer_landing() ? custom_box_pizza_boxes_manufacturer_description() : $description;
}
add_filter('rank_math/frontend/description', 'custom_box_pizza_boxes_manufacturer_description_filter', 30);

function custom_box_pizza_boxes_manufacturer_canonical($canonical) {
    return custom_box_is_pizza_boxes_manufacturer_landing() ? custom_box_pizza_boxes_manufacturer_url() : $canonical;
}
add_filter('rank_math/frontend/canonical', 'custom_box_pizza_boxes_manufacturer_canonical', 30);
add_filter('get_canonical_url', 'custom_box_pizza_boxes_manufacturer_canonical', 30);

function custom_box_pizza_boxes_manufacturer_social_image($image) {
    return custom_box_is_pizza_boxes_manufacturer_landing() ? custom_box_pizza_boxes_manufacturer_image_url() : $image;
}
add_filter('rank_math/opengraph/facebook/image', 'custom_box_pizza_boxes_manufacturer_social_image', 30);
add_filter('rank_math/opengraph/twitter/image', 'custom_box_pizza_boxes_manufacturer_social_image', 30);

function custom_box_pizza_boxes_manufacturer_robots($robots) {
    if (custom_box_is_pizza_boxes_manufacturer_landing()) {
        $robots['index'] = 'index';
        $robots['follow'] = 'follow';
    }

    return $robots;
}
add_filter('rank_math/frontend/robots', 'custom_box_pizza_boxes_manufacturer_robots', 30);

function custom_box_pizza_boxes_manufacturer_fallback_meta() {
    if (!custom_box_is_pizza_boxes_manufacturer_landing() || defined('RANK_MATH_VERSION')) {
        return;
    }

    $url = custom_box_pizza_boxes_manufacturer_url();
    $image = custom_box_pizza_boxes_manufacturer_image_url();
    ?>
    <meta name="description" content="<?php echo esc_attr(custom_box_pizza_boxes_manufacturer_description()); ?>">
    <link rel="canonical" href="<?php echo esc_url($url); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo esc_attr(custom_box_pizza_boxes_manufacturer_title()); ?>">
    <meta property="og:description" content="<?php echo esc_attr(custom_box_pizza_boxes_manufacturer_description()); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <?php if ($image) : ?><meta property="og:image" content="<?php echo esc_url($image); ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr(custom_box_pizza_boxes_manufacturer_title()); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr(custom_box_pizza_boxes_manufacturer_description()); ?>">
    <?php if ($image) : ?><meta name="twitter:image" content="<?php echo esc_url($image); ?>"><?php endif; ?>
    <?php
}
add_action('wp_head', 'custom_box_pizza_boxes_manufacturer_fallback_meta', 1);

function custom_box_pizza_boxes_manufacturer_schema() {
    if (!custom_box_is_pizza_boxes_manufacturer_landing()) {
        return;
    }

    $url = custom_box_pizza_boxes_manufacturer_url();
    $image = custom_box_pizza_boxes_manufacturer_image_url();
    $faq_entities = array();
    foreach (custom_box_pizza_boxes_manufacturer_faqs() as $item) {
        $faq_entities[] = array(
            '@type' => 'Question',
            'name' => $item['question'],
            'acceptedAnswer' => array('@type' => 'Answer', 'text' => $item['answer']),
        );
    }

    $webpage_schema = array(
        '@type' => 'WebPage',
        '@id' => $url . '#webpage',
        'url' => $url,
        'name' => custom_box_pizza_boxes_manufacturer_title(),
        'description' => custom_box_pizza_boxes_manufacturer_description(),
        'inLanguage' => 'en-US',
        'publisher' => array('@id' => home_url('/#organization')),
        'about' => array('@id' => $url . '#service'),
    );
    if ($image) {
        $webpage_schema['primaryImageOfPage'] = array('@id' => $url . '#factory-photo');
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@graph' => array(
            $webpage_schema,
            array(
                '@type' => 'ImageObject',
                '@id' => $url . '#factory-photo',
                'contentUrl' => $image,
                'url' => $image,
                'width' => 800,
                'height' => 450,
                'caption' => 'VPN team member operating a printing press at the paper-packaging workshop.',
                'creditText' => 'VPN Paper Box',
            ),
            array(
                '@type' => 'Service',
                '@id' => $url . '#service',
                'name' => 'Custom Pizza Box Manufacturing',
                'serviceType' => 'Custom pizza box manufacturing',
                'provider' => array('@id' => home_url('/#organization')),
                'areaServed' => 'Vietnam',
                'url' => $url,
            ),
            array('@type' => 'FAQPage', '@id' => $url . '#faq', 'mainEntity' => $faq_entities),
        ),
    );

    if (!defined('RANK_MATH_VERSION') && function_exists('custom_box_get_business_schema')) {
        $schema['@graph'][] = custom_box_get_business_schema();
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_pizza_boxes_manufacturer_schema', 25);
