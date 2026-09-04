<?php
/**
 * SEO integration fixes.
 */

defined('ABSPATH') || exit;

/**
 * Keep the harmless Schema.org microdata attributes used by visible FAQ
 * sections when post content is sanitized on save or at render time.
 */
function custom_box_allow_visible_schema_attributes($allowed_html, $context) {
    if ('post' !== $context) {
        return $allowed_html;
    }

    foreach (array('section', 'div', 'h2', 'h3', 'p') as $tag) {
        if (!isset($allowed_html[$tag])) {
            $allowed_html[$tag] = array();
        }
        $allowed_html[$tag]['itemscope'] = true;
        $allowed_html[$tag]['itemtype'] = true;
        $allowed_html[$tag]['itemprop'] = true;
    }

    return $allowed_html;
}
add_filter('wp_kses_allowed_html', 'custom_box_allow_visible_schema_attributes', 10, 2);

function custom_box_output_pinterest_domain_verification() {
    $verification = '58c369e38f99c3e9d3b0311b4301f3e4';

    if (is_front_page() && class_exists('RankMath\Helper')) {
        $configured = trim((string) \RankMath\Helper::get_settings('general.pinterest_verify'));
        $custom_webmaster_tags = (string) \RankMath\Helper::get_settings('general.custom_webmaster_tags');

        if (
            $verification === $configured
            || (
                false !== strpos($custom_webmaster_tags, 'p:domain_verify')
                && false !== strpos($custom_webmaster_tags, $verification)
            )
        ) {
            return;
        }
    }

    echo '<meta name="p:domain_verify" content="' . esc_attr($verification) . '">' . "\n";
}
add_action('wp_head', 'custom_box_output_pinterest_domain_verification', 4);

function custom_box_rank_math_outputs_gtag() {
    global $wp_filter;

    if (empty($wp_filter['wp_head']) || !isset($wp_filter['wp_head']->callbacks)) {
        return false;
    }

    foreach ($wp_filter['wp_head']->callbacks as $callbacks) {
        foreach ($callbacks as $callback) {
            $function = isset($callback['function']) ? $callback['function'] : null;

            if (
                is_array($function)
                && isset($function[0], $function[1])
                && is_object($function[0])
                && 'add_gtag_js' === $function[1]
                && is_a($function[0], 'RankMath\\Analytics\\GTag')
            ) {
                return true;
            }
        }
    }

    return false;
}

function custom_box_output_google_tag() {
    if (
        defined('GOOGLESITEKIT_VERSION')
        || defined('GOOGLESITEKIT_PLUGIN_MAIN_FILE')
        || class_exists('Google\\Site_Kit\\Plugin')
    ) {
        return;
    }

    ?>
    <!-- Google tag (gtag.js) -->
    <?php if (!custom_box_rank_math_outputs_gtag()) : ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-8ELLLW3RQ6"></script>
    <?php endif; ?>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-8ELLLW3RQ6');
      gtag('config', 'G-H6E36WMHV6');
    </script>
    <?php
}
add_action('wp_head', 'custom_box_output_google_tag', 5);

function custom_box_output_packaging_quote_ads_conversion() {
    if (!custom_box_is_packaging_quote_thank_you_page()) {
        return;
    }
    ?>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('config', 'AW-18190901085');
      gtag('event', 'conversion', {
        'send_to': 'AW-18190901085/9le8CJTHyMAcEM2G2-FD'
      });
    </script>
    <?php
}
add_action('wp_head', 'custom_box_output_packaging_quote_ads_conversion', 20);

function custom_box_get_packaging_money_page_old_path() {
    return '/packaging-landing/';
}

function custom_box_get_packaging_money_page_new_path() {
    return '/custom-packaging-boxes-manufacturer/';
}

function custom_box_get_packaging_money_page_url() {
    return home_url(custom_box_get_packaging_money_page_new_path());
}

function custom_box_get_paper_box_manufacturer_page_path() {
    return '/paper-box-manufacturer/';
}

function custom_box_get_paper_box_manufacturer_page_url() {
    return home_url(custom_box_get_paper_box_manufacturer_page_path());
}

function custom_box_get_packaging_quote_thank_you_page_path() {
    return '/thank-you-packaging-quote/';
}

function custom_box_get_packaging_quote_thank_you_page_url() {
    return home_url(custom_box_get_packaging_quote_thank_you_page_path());
}

function custom_box_packaging_money_page_permalink($link, $post_id) {
    $post = get_post($post_id);
    if (!$post || 'packaging-landing' !== $post->post_name) {
        return $link;
    }

    return custom_box_get_packaging_money_page_url();
}
add_filter('page_link', 'custom_box_packaging_money_page_permalink', 20, 2);

function custom_box_add_packaging_money_page_rewrite() {
    add_rewrite_rule(
        '^custom-packaging-boxes-manufacturer/?$',
        'index.php?pagename=packaging-landing',
        'top'
    );
}
add_action('init', 'custom_box_add_packaging_money_page_rewrite');

function custom_box_map_packaging_money_page_request($query_vars) {
    if (isset($query_vars['pagename']) && 'custom-packaging-boxes-manufacturer' === trim($query_vars['pagename'], '/')) {
        $query_vars['pagename'] = 'packaging-landing';
    }

    return $query_vars;
}
add_filter('request', 'custom_box_map_packaging_money_page_request', 1);

function custom_box_current_request_path() {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $path = wp_parse_url($request_uri, PHP_URL_PATH);
    $home_path = wp_parse_url(home_url('/'), PHP_URL_PATH);
    $path = '/' . trim((string) $path, '/');

    if ($home_path && '/' !== $home_path) {
        $home_path = '/' . trim($home_path, '/');

        if (0 === strpos($path, $home_path . '/')) {
            $path = substr($path, strlen($home_path));
        } elseif ($path === $home_path) {
            $path = '/';
        }
    }

    return trailingslashit('/' . trim((string) $path, '/'));
}

function custom_box_packaging_money_page_locale($locale) {
    if (is_admin()) {
        return $locale;
    }

    $request_path = custom_box_current_request_path();
    if (
        custom_box_get_packaging_money_page_new_path() === $request_path
        || custom_box_get_packaging_money_page_old_path() === $request_path
    ) {
        return 'en_US';
    }

    return $locale;
}
add_filter('locale', 'custom_box_packaging_money_page_locale', 20);

function custom_box_parse_packaging_money_page_request($wp) {
    if (custom_box_current_request_path() !== custom_box_get_packaging_money_page_new_path()) {
        return;
    }

    $wp->query_vars = array(
        'pagename' => 'packaging-landing',
    );
}
add_action('parse_request', 'custom_box_parse_packaging_money_page_request', 1);

function custom_box_is_packaging_money_page() {
    return !is_admin() && is_page('packaging-landing');
}

function custom_box_is_paper_box_manufacturer_page() {
    if (is_admin()) {
        return false;
    }

    return is_page('paper-box-manufacturer')
        || custom_box_current_request_path() === custom_box_get_paper_box_manufacturer_page_path();
}

function custom_box_is_packaging_quote_thank_you_page() {
    if (is_admin()) {
        return false;
    }

    return is_page('thank-you-packaging-quote')
        || custom_box_current_request_path() === custom_box_get_packaging_quote_thank_you_page_path();
}

function custom_box_load_paper_box_manufacturer_template($template) {
    if (!custom_box_is_paper_box_manufacturer_page()) {
        return $template;
    }

    $landing_template = get_template_directory() . '/page-paper-box-manufacturer.php';

    if (!file_exists($landing_template)) {
        return $template;
    }

    global $wp_query;

    if ($wp_query && $wp_query->is_404()) {
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        status_header(200);
        nocache_headers();
    }

    return $landing_template;
}
add_filter('template_include', 'custom_box_load_paper_box_manufacturer_template', 20);

function custom_box_load_packaging_quote_thank_you_template($template) {
    if (!custom_box_is_packaging_quote_thank_you_page()) {
        return $template;
    }

    $thank_you_template = get_template_directory() . '/page-thank-you-packaging-quote.php';

    if (!file_exists($thank_you_template)) {
        return $template;
    }

    global $wp_query;

    if ($wp_query && $wp_query->is_404()) {
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        status_header(200);
        nocache_headers();
    }

    return $thank_you_template;
}
add_filter('template_include', 'custom_box_load_packaging_quote_thank_you_template', 20);

function custom_box_prevent_packaging_quote_thank_you_404($preempt, $wp_query) {
    if (!custom_box_is_packaging_quote_thank_you_page()) {
        return $preempt;
    }

    if ($wp_query) {
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
    }

    status_header(200);

    return true;
}
add_filter('pre_handle_404', 'custom_box_prevent_packaging_quote_thank_you_404', 10, 2);

function custom_box_redirect_old_packaging_landing_url() {
    if (!custom_box_is_packaging_money_page()) {
        return;
    }

    if (custom_box_current_request_path() !== custom_box_get_packaging_money_page_old_path()) {
        return;
    }

    wp_safe_redirect(custom_box_get_packaging_money_page_url(), 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_old_packaging_landing_url', 1);

function custom_box_redirect_legacy_broken_urls() {
    $path = custom_box_current_request_path();
    
    $redirects = array(
        '/hop-giay-dung-banh-keo/' => home_url('/'),
        '/hop-giay-dung-my-pham/' => home_url('/products/beauty-skincare-packaging/'),
        '/hop-giay-dung-banh-trung-thu/' => home_url('/products/bakery-packaging-boxes/'),
        '/hop-giay-dung-ruou/' => home_url('/products/wine-premium-drink-packaging/'),
        '/hop-giay-dung-ca-phe/' => home_url('/products/tea-and-coffee-packaging-boxes/'),
        '/hop-giay-dung-qua-tet/' => home_url('/products/gift-paper-boxes/'),
        '/hop-giay-dung-trang-suc/' => home_url('/products/jewelry-paper-boxes/'),
        '/hop-giay-dung-tra/' => home_url('/products/tea-and-coffee-packaging-boxes/'),
        '/hop-giay-dung-qua-tang/' => home_url('/products/gift-paper-boxes/'),
    );

    if (isset($redirects[$path])) {
        $target = $redirects[$path];
        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        $query = wp_parse_url($request_uri, PHP_URL_QUERY);
        if ($query) {
            $target .= '?' . $query;
        }
        wp_safe_redirect($target, 301);
        exit;
    }
}
add_action('init', 'custom_box_redirect_legacy_broken_urls', 1);

function custom_box_get_packaging_money_page_title() {
    return 'Custom Packaging Boxes Manufacturer in Vietnam | VPN Paper Box';
}

function custom_box_get_packaging_money_page_description() {
    return 'VPN Paper Box Manufacturer produces custom packaging boxes, rigid boxes, folding cartons, paper bags and printed paper packaging in Vietnam for B2B brands, importers and export buyers. Request a factory quote.';
}

function custom_box_get_paper_box_manufacturer_page_title() {
    return 'Paper Box Manufacturer in Vietnam | Packaging Boxes Manufacturer';
}

function custom_box_get_paper_box_manufacturer_page_description() {
    return 'Vietnam paper box manufacturer for custom paper boxes, rigid boxes, carton boxes, gift boxes, cosmetic packaging and bulk B2B packaging orders.';
}

function custom_box_get_packaging_quote_thank_you_page_title() {
    return 'Thank You for Your Packaging Quote Request | VPN Paper Box';
}

function custom_box_get_packaging_quote_thank_you_page_description() {
    return 'Your custom packaging quote request has been received by VPN Paper Box Manufacturer.';
}

function custom_box_get_home_seo_title() {
    return 'VPN Paper Box Manufacturer | Custom Paper Boxes Factory';
}

function custom_box_get_home_seo_description() {
    return 'VPN Paper Box Manufacturer in Vietnam for custom rigid boxes, gift boxes, cosmetic packaging, paper bags, and factory-direct export packaging solutions.';
}

function custom_box_get_products_hub_title() {
    return 'Custom Packaging Product Categories | Paper Boxes, Rigid Boxes & Paper Bags';
}

function custom_box_get_products_hub_description() {
    return 'Explore custom packaging product categories including paper boxes, rigid boxes, drawer boxes, food paper boxes, cosmetic packaging boxes, gift boxes, paper bags, and specialty packaging solutions from VPN Paper Box Manufacturer.';
}

function custom_box_get_product_category_seo_title($term) {
    if (!$term || is_wp_error($term) || empty($term->name)) {
        return '';
    }

    return sprintf('%s | Custom Packaging Category | VPN Paper Box', wp_strip_all_tags($term->name));
}

function custom_box_get_product_category_seo_description($term) {
    if (!$term || is_wp_error($term) || empty($term->name)) {
        return '';
    }

    return sprintf(
        'Explore %s from VPN Paper Box Manufacturer in Vietnam, with custom size, material, printing, finishing, sampling, and export-ready packaging support.',
        wp_strip_all_tags($term->name)
    );
}

function custom_box_is_public_home() {
    return !is_admin() && is_front_page();
}

function custom_box_home_document_title($title) {
    if (custom_box_is_packaging_money_page()) {
        return custom_box_get_packaging_money_page_title();
    }

    if (custom_box_is_paper_box_manufacturer_page()) {
        return custom_box_get_paper_box_manufacturer_page_title();
    }

    if (custom_box_is_packaging_quote_thank_you_page()) {
        return custom_box_get_packaging_quote_thank_you_page_title();
    }

    if (function_exists('is_shop') && is_shop()) {
        return custom_box_get_products_hub_title();
    }

    if (function_exists('is_product_category') && is_product_category()) {
        $term_title = custom_box_get_product_category_seo_title(get_queried_object());

        if ($term_title) {
            return $term_title;
        }
    }

    if (!custom_box_is_public_home()) {
        return $title;
    }

    return custom_box_get_home_seo_title();
}
add_filter('pre_get_document_title', 'custom_box_home_document_title', 20);
add_filter('rank_math/frontend/title', 'custom_box_home_document_title', 20);

function custom_box_home_rank_math_description($description) {
    if (custom_box_is_packaging_money_page()) {
        return custom_box_get_packaging_money_page_description();
    }

    if (custom_box_is_paper_box_manufacturer_page()) {
        return custom_box_get_paper_box_manufacturer_page_description();
    }

    if (custom_box_is_packaging_quote_thank_you_page()) {
        return custom_box_get_packaging_quote_thank_you_page_description();
    }

    if (is_home() || is_page('blog')) {
        return 'Read custom paper packaging guides from VPN Paper Box Manufacturer, including paper boxes, paper bags, rigid boxes, materials, printing, finishing, and B2B packaging tips.';
    }

    if (is_page('catalog')) {
        return 'Explore VPN Paper Box Manufacturer catalog for custom paper boxes, rigid boxes, folding cartons, paper bags, materials, printing, finishing, and export-ready packaging options.';
    }

    if (function_exists('is_shop') && is_shop()) {
        return custom_box_get_products_hub_description();
    }

    if (function_exists('is_product_category') && is_product_category()) {
        $term_description = custom_box_get_product_category_seo_description(get_queried_object());

        if ($term_description) {
            return $term_description;
        }
    }

    if (!custom_box_is_public_home()) {
        return $description;
    }

    return custom_box_get_home_seo_description();
}
add_filter('rank_math/frontend/description', 'custom_box_home_rank_math_description', 20);

function custom_box_public_canonical($canonical) {
    if (custom_box_is_packaging_money_page()) {
        return custom_box_get_packaging_money_page_url();
    }

    if (custom_box_is_paper_box_manufacturer_page()) {
        return custom_box_get_paper_box_manufacturer_page_url();
    }

    if (custom_box_is_packaging_quote_thank_you_page()) {
        return custom_box_get_packaging_quote_thank_you_page_url();
    }

    if ((function_exists('is_shop') && is_shop()) || is_page('products')) {
        return function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');
    }

    if (function_exists('is_product_category') && is_product_category()) {
        $term = get_queried_object();

        if ($term && !is_wp_error($term)) {
            $term_link = get_term_link($term);

            if (!is_wp_error($term_link)) {
                return $term_link;
            }
        }
    }

    return $canonical;
}
add_filter('rank_math/frontend/canonical', 'custom_box_public_canonical', 20);
add_filter('get_canonical_url', 'custom_box_public_canonical', 20);

function custom_box_packaging_money_page_og_type($type) {
    if (!custom_box_is_packaging_money_page() && !custom_box_is_paper_box_manufacturer_page()) {
        return $type;
    }

    return 'website';
}
add_filter('rank_math/opengraph/type', 'custom_box_packaging_money_page_og_type', 20);

function custom_box_low_value_page_slugs() {
    return array('cart', 'checkout', 'my-account', 'trang-mau', 'home-2');
}

function custom_box_is_low_value_page() {
    if (!is_page()) {
        return false;
    }

    return is_page(custom_box_low_value_page_slugs());
}

function custom_box_rank_math_robots($robots) {
    if (custom_box_is_packaging_quote_thank_you_page()) {
        $robots['index'] = 'noindex';
        $robots['follow'] = 'nofollow';

        return $robots;
    }

    if (!custom_box_is_low_value_page()) {
        return $robots;
    }

    $robots['index'] = 'noindex';
    $robots['follow'] = 'follow';

    return $robots;
}
add_filter('rank_math/frontend/robots', 'custom_box_rank_math_robots');

function custom_box_packaging_quote_thank_you_meta_robots() {
    if (defined('RANK_MATH_VERSION') || !custom_box_is_packaging_quote_thank_you_page()) {
        return;
    }

    echo '<meta name="robots" content="noindex,nofollow">' . "\n";
}
add_action('wp_head', 'custom_box_packaging_quote_thank_you_meta_robots', 1);

function custom_box_rank_math_sitemap_excluded_posts($post_ids) {
    $post_ids = wp_parse_id_list($post_ids);

    foreach (array_merge(custom_box_low_value_page_slugs(), array('thank-you-packaging-quote')) as $slug) {
        $page = get_page_by_path($slug);
        if ($page && 'page' === $page->post_type) {
            $post_ids[] = (int) $page->ID;
        }
    }

    foreach (array('woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id') as $option_name) {
        $page_id = (int) get_option($option_name);
        if ($page_id > 0) {
            $post_ids[] = $page_id;
        }
    }

    return array_values(array_unique(array_filter($post_ids)));
}
add_filter('rank_math/sitemap/posts_to_exclude', 'custom_box_rank_math_sitemap_excluded_posts');

function custom_box_rank_math_sitemap_exclude_low_value_page_slugs($where, $post_type) {
    if ('page' !== $post_type) {
        return $where;
    }

    global $wpdb;

    $slugs = array_map('sanitize_title', array_merge(custom_box_low_value_page_slugs(), array('thank-you-packaging-quote')));
    $slugs = array_filter($slugs);

    if (empty($slugs)) {
        return $where;
    }

    $placeholders = implode(', ', array_fill(0, count($slugs), '%s'));

    return $where . $wpdb->prepare(" AND p.post_name NOT IN ($placeholders)", $slugs);
}
add_filter('rank_math/sitemap/get_posts/where', 'custom_box_rank_math_sitemap_exclude_low_value_page_slugs', 20, 2);
add_filter('rank_math/sitemap/post_count/where', 'custom_box_rank_math_sitemap_exclude_low_value_page_slugs', 20, 2);

function custom_box_product_schema_sku_is_valid($sku) {
    if (!is_scalar($sku)) {
        return false;
    }

    $sku = (string) $sku;
    $length = function_exists('mb_strlen') ? mb_strlen($sku, 'UTF-8') : strlen($sku);

    return $length >= 1 && $length <= 50;
}

function custom_box_remove_invalid_product_schema_sku($entity) {
    if (!is_array($entity)) {
        return $entity;
    }

    if (array_key_exists('sku', $entity) && !custom_box_product_schema_sku_is_valid($entity['sku'])) {
        unset($entity['sku']);
    }

    return $entity;
}

function custom_box_add_quote_product_schema_fields($entity, $product) {
    $entity = custom_box_remove_invalid_product_schema_sku($entity);

    $entity['brand'] = array(
        '@type' => 'Brand',
        'name'  => 'VPN Paper Box',
    );

    if ($product instanceof WC_Product && $product->get_image_id() && empty($entity['image'])) {
        $image = wp_get_attachment_image_src($product->get_image_id(), 'full');

        if (!empty($image)) {
            $entity['image'] = array(
                array(
                    '@type'  => 'ImageObject',
                    'url'    => $image[0],
                    'width'  => (int) $image[1],
                    'height' => (int) $image[2],
                ),
            );
        }
    }

    return $entity;
}

function custom_box_get_business_schema() {
    $logo_url = get_template_directory_uri() . '/assets/images/logo-hop-giay-vpn-hcm.png';
    $custom_logo_id = (int) get_theme_mod('custom_logo');

    if ($custom_logo_id) {
        $custom_logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($custom_logo_url) {
            $logo_url = $custom_logo_url;
        }
    }

    $site_url = home_url('/');
    $factory_map_url = 'https://maps.app.goo.gl/Z68geWnrTmx6kaCg6';

    return array(
        '@type'       => array('Organization', 'LocalBusiness'),
        '@id'         => home_url('/#organization'),
        'name'        => 'VPN Paper Box',
        'legalName'   => 'Công ty TNHH Quảng Cáo VPN',
        'url'         => $site_url,
        'logo'        => array(
            '@type' => 'ImageObject',
            'url'   => $logo_url,
        ),
        'image'       => get_template_directory_uri() . '/assets/images/anh-nha-may-2.webp',
        'description' => 'Vietnam-based paper box and packaging manufacturer specializing in custom paper boxes, rigid boxes, paper bags, and export-ready packaging for brands, importers, distributors, and agencies.',
        'telephone'   => '+84933102653',
        'email'       => 'sales.vpn@hopgiayvpn.com',
        'priceRange'  => '$$',
        'address'     => array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => '1032 An Phu Tay, Hamlet 4, Hung Long Commune',
            'addressLocality' => 'Binh Chanh District',
            'addressRegion'   => 'Ho Chi Minh City',
            'addressCountry'  => 'VN',
        ),
        'hasMap'      => $factory_map_url,
        'areaServed'  => array(
            array(
                '@type' => 'Country',
                'name'  => 'Vietnam',
            ),
            array(
                '@type' => 'Place',
                'name'  => 'Worldwide',
            ),
        ),
        'openingHoursSpecification' => array(
            array(
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => array(
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                ),
                'opens'     => '08:00',
                'closes'    => '18:00',
            ),
        ),
        'contactPoint' => array(
            array(
                '@type'       => 'ContactPoint',
                'telephone'   => '+84933102653',
                'email'       => 'sales.vpn@hopgiayvpn.com',
                'contactType' => 'sales',
                'areaServed'  => 'Worldwide',
                'availableLanguage' => array('English', 'Vietnamese'),
            ),
        ),
        'sameAs'      => array(
            'https://www.facebook.com/people/Vietnam-Paper-Box-Factory/61576428668265/',
            'https://www.youtube.com/@VietnamPaperBoxFactory',
            'https://www.tiktok.com/@paperbox84',
            'https://www.pinterest.com/VPNPaperBox',
            'https://www.linkedin.com/company/vpn-advertising-co/',
            'https://vpnadvertising.trustpass.alibaba.com/',
        ),
        'knowsAbout'  => array(
            'Custom paper boxes',
            'Rigid boxes',
            'Gift packaging',
            'Cosmetic packaging',
            'Paper bags',
            'Packaging printing',
            'Foil stamping',
            'Embossing',
            'Lamination',
        ),
    );
}

function custom_box_rank_math_json_ld($data) {
    if (!is_array($data)) {
        return $data;
    }

    $remove_article_schema = custom_box_is_low_value_page();
    $is_packaging_money_page = custom_box_is_packaging_money_page();
    $is_paper_box_manufacturer_page = custom_box_is_paper_box_manufacturer_page();
    $is_paper_bag_landing = function_exists('custom_box_is_custom_paper_bags_manufacturer_landing') && custom_box_is_custom_paper_bags_manufacturer_landing();
    $is_product_page = function_exists('is_product') && is_product();
    $is_non_article_page = (is_page() || is_front_page() || is_home() || (function_exists('is_product_taxonomy') && is_product_taxonomy())) && !is_singular('post') && !$is_product_page;
    $product = $is_product_page && function_exists('wc_get_product') ? wc_get_product(get_queried_object_id()) : null;
    $has_product_schema = false;
    $paper_bag_organization_key = null;
    $paper_bag_business_schema = $is_paper_bag_landing ? custom_box_get_business_schema() : array();

    foreach ($data as $key => $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $types = isset($entity['@type']) ? (array) $entity['@type'] : array();
        $is_product_schema = array_intersect($types, array('Product', 'WooCommerceProduct', 'ProductGroup'));

        if ($is_paper_bag_landing && in_array('Organization', $types, true)) {
            if (null !== $paper_bag_organization_key) {
                unset($data[$key]);
                continue;
            }

            $paper_bag_organization_key = $key;
            $data[$key] = array_merge($entity, $paper_bag_business_schema);
            $data[$key]['@id'] = home_url('/#organization');
            $data[$key]['name'] = 'VPN Paper Box';
            $data[$key]['legalName'] = 'Công ty TNHH Quảng Cáo VPN';
            $data[$key]['areaServed'] = 'Vietnam';
            if (!empty($data[$key]['contactPoint']) && is_array($data[$key]['contactPoint'])) {
                foreach ($data[$key]['contactPoint'] as $contact_point_key => $contact_point) {
                    if (is_array($contact_point)) {
                        $data[$key]['contactPoint'][$contact_point_key]['areaServed'] = 'Vietnam';
                    }
                }
            }
        }

        if ($is_paper_bag_landing && in_array('WebSite', $types, true)) {
            $data[$key]['name'] = 'VPN Paper Box';
        }

        if ($is_packaging_money_page || $is_paper_box_manufacturer_page) {
            $data[$key]['inLanguage'] = 'en-US';
        }

        if (is_front_page() && in_array('CollectionPage', $types, true)) {
            $data[$key]['@type'] = count($types) > 1
                ? array_values(array_unique(array_merge(array_diff($types, array('CollectionPage')), array('WebPage'))))
                : 'WebPage';
        }

        if ($is_packaging_money_page && in_array('WebPage', $types, true)) {
            $data[$key]['@id'] = custom_box_get_packaging_money_page_url() . '#webpage';
            $data[$key]['url'] = custom_box_get_packaging_money_page_url();
            $data[$key]['name'] = custom_box_get_packaging_money_page_title();
            $data[$key]['description'] = custom_box_get_packaging_money_page_description();
        }

        if ($is_paper_box_manufacturer_page && in_array('WebPage', $types, true)) {
            $data[$key]['@id'] = custom_box_get_paper_box_manufacturer_page_url() . '#webpage';
            $data[$key]['url'] = custom_box_get_paper_box_manufacturer_page_url();
            $data[$key]['name'] = custom_box_get_paper_box_manufacturer_page_title();
            $data[$key]['description'] = custom_box_get_paper_box_manufacturer_page_description();
        }

        if (($remove_article_schema || $is_packaging_money_page || $is_paper_box_manufacturer_page || $is_non_article_page) && array_intersect($types, array('Article', 'BlogPosting'))) {
            unset($data[$key]);
            continue;
        }

        if ($is_non_article_page && in_array('Person', $types, true)) {
            unset($data[$key]);
            continue;
        }

        if (in_array('Person', $types, true) && !empty($entity['sameAs']) && is_array($entity['sameAs'])) {
            $data[$key]['sameAs'] = array_values(array_filter($entity['sameAs'], function ($url) {
                return false === strpos((string) $url, 'localhost');
            }));

            if (empty($data[$key]['sameAs'])) {
                unset($data[$key]['sameAs']);
            }
        }

        if ($is_product_page && $product instanceof WC_Product && $is_product_schema) {
            $has_product_schema = true;
            $data[$key] = custom_box_add_quote_product_schema_fields($data[$key], $product);
        }
    }

    if ($is_product_page && $product instanceof WC_Product && !$has_product_schema) {
        $data['schema-customBoxProduct'] = custom_box_add_quote_product_schema_fields(
            array(
                '@type'       => 'Product',
                'name'        => $product->get_name(),
                'description' => wp_strip_all_tags($product->get_short_description() ? $product->get_short_description() : $product->get_description()),
                'sku'         => $product->get_sku() ? $product->get_sku() : '',
            ),
            $product
        );
    }

    if (!$is_paper_bag_landing) {
        $data['schema-customBoxOrganization'] = custom_box_get_business_schema();
    } elseif (null === $paper_bag_organization_key) {
        $data['schema-customBoxOrganization'] = $paper_bag_business_schema;
        $data['schema-customBoxOrganization']['@id'] = home_url('/#organization');
    }

    if (($is_packaging_money_page || $is_paper_box_manufacturer_page) && isset($data['schema-customBoxOrganization'])) {
        $data['schema-customBoxOrganization']['inLanguage'] = 'en-US';
    }

    return $data;
}
add_filter('rank_math/json_ld', 'custom_box_rank_math_json_ld', 20);
