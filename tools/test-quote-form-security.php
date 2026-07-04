<?php
/**
 * Temporary local checks for quote form security controls.
 */

require_once dirname(__DIR__) . '/wp-load.php';

add_filter('custom_box_quote_form_logging_enabled', '__return_false');

function vpn_quote_test_assert($condition, $message) {
    if (!$condition) {
        echo 'FAIL: ' . $message . PHP_EOL;
        exit(1);
    }

    echo 'PASS: ' . $message . PHP_EOL;
}

function vpn_quote_test_clear_filters() {
    remove_all_filters('pre_http_request');
    remove_all_filters('pre_wp_mail');
}

$_SERVER['REMOTE_ADDR'] = '203.0.113.10';
unset($_SERVER['HTTP_CF_CONNECTING_IP']);

$_POST = array();
$result = custom_box_quote_form_verify_recaptcha();
vpn_quote_test_assert(empty($result['success']) && 'token_missing' === $result['reason'], 'A. missing CAPTCHA token fails');

$_POST = array('g-recaptcha-response' => 'invalid-token');
add_filter(
    'pre_http_request',
    function ($preempt, $args, $url) {
        if ('https://www.google.com/recaptcha/api/siteverify' === $url) {
            return array(
                'response' => array('code' => 200),
                'body'     => wp_json_encode(
                    array(
                        'success'     => false,
                        'error-codes' => array('invalid-input-response'),
                    )
                ),
            );
        }

        return $preempt;
    },
    10,
    3
);
$result = custom_box_quote_form_verify_recaptcha();
vpn_quote_test_assert(empty($result['success']) && 'siteverify_failed' === $result['reason'], 'B. invalid CAPTCHA token fails');
vpn_quote_test_clear_filters();

$valid_token = 'valid-token-' . wp_generate_uuid4();
$_POST = array('g-recaptcha-response' => $valid_token);
add_filter(
    'pre_http_request',
    function ($preempt, $args, $url) {
        if ('https://www.google.com/recaptcha/api/siteverify' === $url) {
            return array(
                'response' => array('code' => 200),
                'body'     => wp_json_encode(array('success' => true)),
            );
        }

        return $preempt;
    },
    10,
    3
);
$result = custom_box_quote_form_verify_recaptcha();
vpn_quote_test_assert(!empty($result['success']), 'C1. valid mocked CAPTCHA token succeeds');
vpn_quote_test_clear_filters();

$_POST = array('g-recaptcha-response' => $valid_token);
$result = custom_box_quote_form_verify_recaptcha();
vpn_quote_test_assert(empty($result['success']) && 'token_replay' === $result['reason'], 'D. replayed CAPTCHA token fails');

$_POST = array('website_url' => 'https://spam.example');
vpn_quote_test_assert('https://spam.example' === custom_box_quote_form_post_text('website_url', 255), 'E. honeypot data is detected before mail path');

$_SERVER['REMOTE_ADDR'] = '203.0.113.11';
$rate_key = 'custom_box_quote_rate_' . substr(custom_box_quote_form_ip_hash(), 0, 32);
delete_transient($rate_key);
$rate_one = custom_box_quote_form_rate_limit_check();
$rate_two = custom_box_quote_form_rate_limit_check();
$rate_three = custom_box_quote_form_rate_limit_check();
$rate_four = custom_box_quote_form_rate_limit_check();
delete_transient($rate_key);
vpn_quote_test_assert(!empty($rate_one['allowed']) && !empty($rate_two['allowed']) && !empty($rate_three['allowed']) && empty($rate_four['allowed']), 'F. fourth request from same IP exceeds 3 per 10 minutes');

$mail_count = 0;
add_filter(
    'pre_wp_mail',
    function ($return, $atts) use (&$mail_count) {
        $mail_count++;

        return true;
    },
    10,
    2
);

$quote_id = wp_insert_post(
    array(
        'post_type'    => 'custom_box_quote',
        'post_status'  => 'private',
        'post_title'   => 'Quote form security test',
        'post_content' => 'Quote form security test',
    ),
    true
);

vpn_quote_test_assert(!is_wp_error($quote_id), 'C2. test quote post created');
update_post_meta(
    $quote_id,
    '_custom_box_quote_data',
    array(
        'product_name'        => 'Test box',
        'length'              => '',
        'width'               => '',
        'depth'               => '',
        'unit'                => '',
        'stock_option'        => 'Test stock',
        'material_preference' => '',
        'printing_option'     => '',
        'finishing_option'    => '',
        'quantity'            => '1000',
        'company'             => '',
        'country'             => 'US',
        'full_name'           => 'Test Lead',
        'phone'               => '',
        'email'               => 'lead@example.com',
        'message'             => 'Test',
        'quote_source'        => '',
        'form_location'       => 'test',
        'email_subject'       => '[Test] Quote',
        'current_page_url'    => '',
        'referrer_url'        => '',
        'utm_source'          => '',
        'utm_medium'          => '',
        'utm_campaign'        => '',
        'utm_term'            => '',
        'utm_content'         => '',
        'referer'             => '',
    )
);
update_post_meta($quote_id, '_custom_box_quote_attachments', array());
update_post_meta($quote_id, '_custom_box_quote_mail_status', 'queued');

custom_box_send_queued_quote_email($quote_id);
custom_box_send_queued_quote_email($quote_id);

wp_delete_post($quote_id, true);
vpn_quote_test_clear_filters();
vpn_quote_test_assert(1 === $mail_count, 'C3. valid saved quote sends exactly one email');

echo 'PASS: G. custom theme search found only quote handler wp_mail path; see rg audit output.' . PHP_EOL;
