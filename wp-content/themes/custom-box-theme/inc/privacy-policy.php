<?php
/**
 * Lightweight privacy-policy bootstrap for the quote forms.
 *
 * The page is created once through the WordPress API when no published
 * privacy-policy page exists. It intentionally describes only data flows
 * verified in this theme and does not make regulatory or legal claims.
 */

defined('ABSPATH') || exit;

function custom_box_get_privacy_policy_url() {
    $page_id = (int) get_option('wp_page_for_privacy_policy');

    if ($page_id > 0 && 'publish' === get_post_status($page_id)) {
        $page_url = get_permalink($page_id);
        if (is_string($page_url) && '' !== $page_url) {
            return $page_url;
        }
    }

    return function_exists('get_privacy_policy_url') ? (string) get_privacy_policy_url() : '';
}

function custom_box_privacy_policy_document_title($title) {
    return is_page('privacy-policy') ? 'Privacy Policy | VPN Paper Box' : $title;
}
add_filter('pre_get_document_title', 'custom_box_privacy_policy_document_title', 99);
add_filter('rank_math/frontend/title', 'custom_box_privacy_policy_document_title', 99);

function custom_box_privacy_policy_content() {
    return implode("\n", array(
        '<p><strong>Last updated:</strong> 14 August 2026</p>',
        '<h2>Who we are</h2>',
        '<p>VPN Paper Box is a packaging brand of Công ty TNHH Quảng Cáo VPN. This website is <a href="' . esc_url(home_url('/')) . '">' . esc_html(home_url('/')) . '</a>.</p>',
        '<h2>Information you submit</h2>',
        '<p>When you request a quotation, you may submit your name, work email, company, phone or WhatsApp number, paper bag type, estimated quantity, delivery country or region, packed weight, dimensions, paper or handle preferences, printing, finishing, target schedule, artwork and project notes. The forms may also preserve advertising attribution fields such as UTM values, GCLID, GBRAID or WBRAID when they are present in the visit.</p>',
        '<h2>Artwork and uploaded files</h2>',
        '<p>Artwork uploads are optional. The quote form accepts PNG, PDF, JPG, JPEG, WebP, DOC, DOCX, GIF, PSD, CDR and EPS files up to 10MB. Uploaded files are used to review the quotation request and may be attached to the internal quote enquiry. Please do not upload confidential information that is not needed for the quotation.</p>',
        '<h2>How we use quotation information</h2>',
        '<p>VPN Paper Box uses the information and files you submit to review your paper bag or packaging requirements, prepare a quotation, respond to your enquiry and coordinate the next project steps. Quote submissions are recorded in a private WordPress quote record and may be queued or sent to the sales team by email.</p>',
        '<h2>Analytics and advertising measurement</h2>',
        '<p>The current website uses Google services provided through Site Kit, including Google Analytics and Google Ads measurement, and Microsoft Clarity. These services may measure page visits, campaign attribution, button interactions and successful quotation conversions. The site does not use these measurements to change the quotation content you submit.</p>',
        '<h2>Cookies and similar technologies</h2>',
        '<p>WordPress, WooCommerce and the analytics or advertising services used on the site may use cookies or similar browser storage. Their exact behavior depends on the active site features, browser settings and third-party service settings.</p>',
        '<h2>Service providers</h2>',
        '<p>Quotation information is processed through this WordPress website, its configured email delivery, and the analytics or advertising services described above. The website may also use WordPress and WooCommerce functions to handle forms, uploads and product information.</p>',
        '<h2>Security</h2>',
        '<p>Reasonable technical and administrative measures are used within the website workflow to limit access to quote records and uploaded files. No online transmission or storage method can be guaranteed to be completely secure.</p>',
        '<h2>How long information is kept</h2>',
        '<p>Quote information and related files are kept as needed to review and respond to the enquiry, manage the related project and maintain operational records. The exact retention period can vary by enquiry and business need.</p>',
        '<h2>Questions and enquiries</h2>',
        '<p>For questions about a quotation or the information you submitted, contact <a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a> or <a href="mailto:paperbox@hopgiayvpn.com">paperbox@hopgiayvpn.com</a>. You can also call <a href="tel:+84933102653">+84 933 102 653</a>.</p>',
    ));
}

function custom_box_bootstrap_privacy_policy_page() {
    if (
        !is_admin()
        || !current_user_can('manage_options')
        || (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || wp_doing_cron()
        || (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    $configured_page_id = (int) get_option('wp_page_for_privacy_policy');
    if ($configured_page_id > 0 && 'publish' === get_post_status($configured_page_id)) {
        return;
    }

    $existing_page = get_page_by_path('privacy-policy', OBJECT, 'page');
    if ($existing_page instanceof WP_Post) {
        if ('publish' === $existing_page->post_status) {
            update_option('wp_page_for_privacy_policy', (int) $existing_page->ID);
        }
        return;
    }

    if (!add_option('custom_box_privacy_policy_creation_lock', time(), '', false)) {
        return;
    }

    $page_id = wp_insert_post(
        array(
            'post_title'     => 'Privacy Policy',
            'post_name'      => 'privacy-policy',
            'post_content'   => custom_box_privacy_policy_content(),
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
        ),
        true
    );

    if (!is_wp_error($page_id) && $page_id > 0) {
        update_option('wp_page_for_privacy_policy', (int) $page_id);
    }

    delete_option('custom_box_privacy_policy_creation_lock');
}
add_action('admin_init', 'custom_box_bootstrap_privacy_policy_page', 99);
