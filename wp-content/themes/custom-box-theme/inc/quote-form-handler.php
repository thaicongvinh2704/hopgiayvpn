<?php
/**
 * Quote form email handling.
 */

function custom_box_quote_form_recipient() {
    $recipient = 'sales.vpn@hopgiayvpn.com';

    return apply_filters('custom_box_quote_form_recipient', $recipient);
}

function custom_box_quote_form_client_ip() {
    $candidates = array('HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR');

    foreach ($candidates as $key) {
        if (empty($_SERVER[$key])) {
            continue;
        }

        $ip = trim((string) wp_unslash($_SERVER[$key]));
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '0.0.0.0';
}

function custom_box_quote_form_ip_hash() {
    return hash_hmac('sha256', custom_box_quote_form_client_ip(), wp_salt('nonce'));
}

function custom_box_quote_form_log($event, $context = array()) {
    if (!apply_filters('custom_box_quote_form_logging_enabled', true)) {
        return;
    }

    unset($context['secret'], $context['secret_key'], $context['g-recaptcha-response'], $context['token']);

    $payload = array_merge(
        array(
            'event'     => sanitize_key($event),
            'timestamp' => current_time('mysql'),
            'ip_hash'   => substr(custom_box_quote_form_ip_hash(), 0, 16),
        ),
        $context
    );

    error_log('[custom_box_quote_form] ' . wp_json_encode($payload));
}

function custom_box_quote_form_reject($status, $http_status = 400, $context = array()) {
    custom_box_quote_form_log(
        'reject_' . $status,
        array_merge(
            array(
                'http_status' => (int) $http_status,
            ),
            $context
        )
    );

    if (429 === (int) $http_status) {
        status_header(429);
        nocache_headers();
        wp_die(
            esc_html__('Too many quote requests. Please wait a few minutes and try again.', 'custom-box-theme'),
            esc_html__('Too Many Requests', 'custom-box-theme'),
            array('response' => 429)
        );
    }

    custom_box_quote_form_redirect($status);
}

function custom_box_quote_form_redirect($status) {
    $redirect_to = wp_get_referer();

    if (!$redirect_to) {
        $redirect_to = home_url('/contact/');
    }

    $anchor = 'quote';
    if (!empty($_POST['form_anchor'])) {
        $anchor = sanitize_html_class(wp_unslash($_POST['form_anchor']));
    }

    $redirect_to = strtok($redirect_to, '#');
    $redirect_to = remove_query_arg('quote_status', $redirect_to);
    $redirect_to = add_query_arg('quote_status', $status, $redirect_to);

    wp_safe_redirect($redirect_to . '#' . $anchor);
    exit;
}

function custom_box_quote_form_redirect_to_thank_you() {
    $thank_you_url = function_exists('custom_box_get_packaging_quote_thank_you_page_url')
        ? custom_box_get_packaging_quote_thank_you_page_url()
        : home_url('/thank-you-packaging-quote/');

    wp_safe_redirect($thank_you_url);
    exit;
}

function custom_box_quote_form_allowed_mimes() {
    return array(
        'png'  => 'image/png',
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    );
}

function custom_box_quote_form_config_value($name, $fallback = '') {
    $value = defined($name) ? constant($name) : getenv($name);

    if (false === $value || '' === trim((string) $value)) {
        return $fallback;
    }

    return trim((string) $value);
}

function custom_box_quote_form_post_text($name, $max_length = 255) {
    $raw = isset($_POST[$name]) ? wp_unslash($_POST[$name]) : '';
    $value = is_array($raw) ? '' : sanitize_text_field($raw);

    return substr($value, 0, max(1, (int) $max_length));
}

function custom_box_quote_form_post_textarea($name, $max_length = 3000) {
    $raw = isset($_POST[$name]) ? wp_unslash($_POST[$name]) : '';
    $value = is_array($raw) ? '' : sanitize_textarea_field($raw);

    return substr($value, 0, max(1, (int) $max_length));
}

function custom_box_quote_form_post_key($name, $max_length = 100) {
    $raw = isset($_POST[$name]) ? wp_unslash($_POST[$name]) : '';
    $value = is_array($raw) ? '' : sanitize_key($raw);

    return substr($value, 0, max(1, (int) $max_length));
}

function custom_box_quote_form_post_url($name, $max_length = 500) {
    $raw = isset($_POST[$name]) ? wp_unslash($_POST[$name]) : '';
    $value = is_array($raw) ? '' : esc_url_raw($raw);

    return substr($value, 0, max(1, (int) $max_length));
}

function custom_box_quote_form_post_email($name) {
    $raw = isset($_POST[$name]) ? wp_unslash($_POST[$name]) : '';

    return is_array($raw) ? '' : sanitize_email($raw);
}

function custom_box_quote_form_rate_limit_check() {
    $limit = (int) apply_filters('custom_box_quote_form_rate_limit_max', 3);
    $window = (int) apply_filters('custom_box_quote_form_rate_limit_window', 10 * MINUTE_IN_SECONDS);
    $key = 'custom_box_quote_rate_' . substr(custom_box_quote_form_ip_hash(), 0, 32);
    $state = get_transient($key);

    if (!is_array($state)) {
        $state = array(
            'count' => 0,
            'first' => time(),
        );
    }

    $state['count'] = isset($state['count']) ? (int) $state['count'] + 1 : 1;
    $state['first'] = isset($state['first']) ? (int) $state['first'] : time();

    set_transient($key, $state, $window);

    $allowed = $state['count'] <= $limit;
    custom_box_quote_form_log(
        'rate_limit_check',
        array(
            'rate_limit_allowed' => $allowed,
            'rate_limit_count'   => $state['count'],
            'rate_limit_limit'   => $limit,
            'rate_limit_window'  => $window,
        )
    );

    return array(
        'allowed' => $allowed,
        'count'   => $state['count'],
        'limit'   => $limit,
        'window'  => $window,
    );
}

function custom_box_quote_form_recaptcha_site_key() {
    return apply_filters(
        'custom_box_quote_form_recaptcha_site_key',
        custom_box_quote_form_config_value('CUSTOM_BOX_RECAPTCHA_SITE_KEY', '6Lfw5EMtAAAAADVk0EfDYCCLCZSZrxLF84eJFlVp')
    );
}

function custom_box_quote_form_recaptcha_secret_key() {
    return apply_filters(
        'custom_box_quote_form_recaptcha_secret_key',
        custom_box_quote_form_config_value('CUSTOM_BOX_RECAPTCHA_SECRET_KEY')
    );
}

function custom_box_quote_form_recaptcha_enabled() {
    return '' !== custom_box_quote_form_recaptcha_site_key();
}

function custom_box_quote_form_recaptcha_fields() {
    if (!custom_box_quote_form_recaptcha_enabled()) {
        return;
    }
    ?>
    <div class="custom-box-recaptcha">
        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr(custom_box_quote_form_recaptcha_site_key()); ?>"></div>
    </div>
    <?php
}

function custom_box_quote_form_should_enqueue_recaptcha() {
    $should_enqueue = is_front_page()
        || is_page(array('contact', 'paper-box-manufacturer', 'packaging-landing'))
        || is_page_template('page-paper-box-manufacturer.php')
        || is_page_template('page-landing-packaging.php')
        || is_singular('product');

    if (function_exists('is_product') && is_product()) {
        $should_enqueue = true;
    }

    return (bool) apply_filters('custom_box_quote_form_should_enqueue_recaptcha', $should_enqueue);
}

function custom_box_enqueue_quote_form_recaptcha() {
    $site_key = custom_box_quote_form_recaptcha_site_key();

    if ('' === $site_key || !custom_box_quote_form_should_enqueue_recaptcha()) {
        return;
    }

    wp_enqueue_script(
        'custom-box-google-recaptcha',
        add_query_arg(
            array(
                'onload' => 'customBoxRecaptchaOnload',
                'render' => 'explicit',
            ),
            'https://www.google.com/recaptcha/api.js'
        ),
        array(),
        null,
        true
    );

}
add_action('wp_enqueue_scripts', 'custom_box_enqueue_quote_form_recaptcha');

function custom_box_print_recaptcha_onload_callback() {
    $site_key = custom_box_quote_form_recaptcha_site_key();

    if ('' === $site_key || !custom_box_quote_form_should_enqueue_recaptcha()) {
        return;
    }
    ?>
    <script id="custom-box-recaptcha-onload-callback" data-cfasync="false" data-no-optimize="1">
    window.customBoxRecaptchaRender = function() {
        var widgets = document.querySelectorAll('.g-recaptcha[data-sitekey]');

        widgets.forEach(function(widget) {
            if (!window.grecaptcha || !window.grecaptcha.render || widget.dataset.widgetId) {
                return;
            }

            widget.dataset.widgetId = String(window.grecaptcha.render(widget, {
                sitekey: widget.getAttribute('data-sitekey')
            }));
        });
    };

    window.customBoxRecaptchaOnload = window.customBoxRecaptchaRender;

    if (window.grecaptcha && window.grecaptcha.render) {
        window.customBoxRecaptchaRender();
    }
    </script>
    <?php
}
add_action('wp_footer', 'custom_box_print_recaptcha_onload_callback', 5);

function custom_box_recaptcha_script_tag($tag, $handle, $src) {
    if ('custom-box-google-recaptcha' !== $handle) {
        return $tag;
    }

    return '<script src="' . esc_url($src) . '" id="custom-box-google-recaptcha-js" data-cfasync="false" data-no-optimize="1" async defer></script>' . "\n";
}
add_filter('script_loader_tag', 'custom_box_recaptcha_script_tag', 9, 3);

function custom_box_quote_form_captcha_lifetime() {
    return (int) apply_filters('custom_box_quote_form_captcha_lifetime', DAY_IN_SECONDS);
}

function custom_box_quote_form_base64url_encode($value) {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function custom_box_quote_form_base64url_decode($value) {
    $value = strtr($value, '-_', '+/');
    $padding = strlen($value) % 4;

    if ($padding) {
        $value .= str_repeat('=', 4 - $padding);
    }

    return base64_decode($value, true);
}

function custom_box_quote_form_captcha_signature($question, $expires, $answer_hash) {
    return hash_hmac(
        'sha256',
        $question . '|' . $expires . '|' . $answer_hash,
        wp_salt('auth')
    );
}

function custom_box_quote_form_create_captcha() {
    $left = wp_rand(2, 9);
    $right = wp_rand(1, 9);
    $answer = (string) ($left + $right);
    $question = sprintf('%d + %d', $left, $right);
    $expires = time() + custom_box_quote_form_captcha_lifetime();
    $answer_hash = hash_hmac('sha256', $answer, wp_salt('secure_auth'));

    $payload = array(
        'question'    => $question,
        'expires'     => $expires,
        'answer_hash' => $answer_hash,
        'signature'   => custom_box_quote_form_captcha_signature($question, $expires, $answer_hash),
    );

    return array(
        'question' => $question,
        'token'    => custom_box_quote_form_base64url_encode(wp_json_encode($payload)),
    );
}

function custom_box_quote_form_verify_captcha() {
    $token = isset($_POST['custom_box_captcha_token']) ? trim((string) wp_unslash($_POST['custom_box_captcha_token'])) : '';
    $answer = isset($_POST['custom_box_captcha_answer']) ? trim((string) wp_unslash($_POST['custom_box_captcha_answer'])) : '';

    if ('' === $token || '' === $answer || !preg_match('/^\d+$/', $answer)) {
        return false;
    }
    $answer = (string) absint($answer);

    $payload = custom_box_quote_form_base64url_decode($token);
    if (false === $payload) {
        return false;
    }

    $payload = json_decode($payload, true);
    if (!is_array($payload)) {
        return false;
    }

    $question = isset($payload['question']) ? (string) $payload['question'] : '';
    $expires = isset($payload['expires']) ? (int) $payload['expires'] : 0;
    $answer_hash = isset($payload['answer_hash']) ? (string) $payload['answer_hash'] : '';
    $signature = isset($payload['signature']) ? (string) $payload['signature'] : '';

    if (!$question || !$expires || !$answer_hash || !$signature || time() > $expires) {
        return false;
    }

    $expected_signature = custom_box_quote_form_captcha_signature($question, $expires, $answer_hash);
    if (!hash_equals($expected_signature, $signature)) {
        return false;
    }

    $submitted_hash = hash_hmac('sha256', $answer, wp_salt('secure_auth'));

    return hash_equals($answer_hash, $submitted_hash);
}

function custom_box_quote_form_verify_recaptcha() {
    if (!custom_box_quote_form_recaptcha_enabled()) {
        return array(
            'success' => false,
            'reason'  => 'site_key_missing',
        );
    }

    $secret_key = custom_box_quote_form_recaptcha_secret_key();
    if ('' === $secret_key) {
        return array(
            'success' => false,
            'reason'  => 'secret_key_missing',
        );
    }

    $token = isset($_POST['g-recaptcha-response']) ? trim((string) wp_unslash($_POST['g-recaptcha-response'])) : '';
    if ('' === $token) {
        custom_box_quote_form_log('token_missing');

        return array(
            'success' => false,
            'reason'  => 'token_missing',
        );
    }

    $token_key = 'custom_box_recaptcha_token_' . substr(hash_hmac('sha256', $token, wp_salt('secure_auth')), 0, 32);
    if (get_transient($token_key)) {
        return array(
            'success' => false,
            'reason'  => 'token_replay',
        );
    }

    $body = array(
        'secret'   => $secret_key,
        'response' => $token,
    );

    $response = wp_remote_post(
        'https://www.google.com/recaptcha/api/siteverify',
        array(
            'timeout' => 8,
            'body'    => $body,
        )
    );

    if (is_wp_error($response)) {
        custom_box_quote_form_log(
            'verification_request_failed',
            array(
                'error_code'    => $response->get_error_code(),
                'error_message' => $response->get_error_message(),
                'token_hash'    => substr(hash_hmac('sha256', $token, wp_salt('secure_auth')), 0, 12),
            )
        );

        return array(
            'success' => false,
            'reason'  => 'siteverify_request_error',
        );
    }

    $payload = json_decode((string) wp_remote_retrieve_body($response), true);
    if (!is_array($payload)) {
        return array(
            'success' => false,
            'reason'  => 'siteverify_invalid_response',
        );
    }

    if (empty($payload['success'])) {
        $error_codes = isset($payload['error-codes']) && is_array($payload['error-codes'])
            ? array_map('sanitize_text_field', $payload['error-codes'])
            : array();

        custom_box_quote_form_log(
            'google_response_error_codes',
            array(
                'error_codes' => $error_codes,
                'token_hash'  => substr(hash_hmac('sha256', $token, wp_salt('secure_auth')), 0, 12),
            )
        );

        return array(
            'success'     => false,
            'reason'      => 'siteverify_failed',
            'error_codes' => $error_codes,
        );
    }

    set_transient($token_key, 1, 10 * MINUTE_IN_SECONDS);
    custom_box_quote_form_log(
        'verification_success',
        array(
            'token_hash' => substr(hash_hmac('sha256', $token, wp_salt('secure_auth')), 0, 12),
        )
    );

    return array(
        'success' => true,
        'reason'  => 'siteverify_success',
    );
}

function custom_box_register_quote_request_post_type() {
    register_post_type(
        'custom_box_quote',
        array(
            'labels'          => array(
                'name'          => __('Quote Requests', 'custom-box-theme'),
                'singular_name' => __('Quote Request', 'custom-box-theme'),
            ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'capability_type' => 'post',
            'supports'        => array('title', 'editor', 'custom-fields'),
            'menu_icon'       => 'dashicons-email-alt',
        )
    );
}
add_action('init', 'custom_box_register_quote_request_post_type');

function custom_box_build_quote_email($quote_data) {
    $quote_data = wp_parse_args(
        $quote_data,
        array(
            'product_name'     => '',
            'length'           => '',
            'width'            => '',
            'depth'            => '',
            'unit'             => '',
            'stock_option'        => '',
            'material_preference' => '',
            'printing_option'     => '',
            'finishing_option'    => '',
            'quantity'            => '',
            'company'             => '',
            'country'             => '',
            'full_name'           => '',
            'phone'               => '',
            'email'               => '',
            'message'             => '',
            'quote_source'        => '',
            'form_location'       => '',
            'email_subject'       => '',
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

    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $full_name = isset($quote_data['full_name']) ? $quote_data['full_name'] : '';
    $email = isset($quote_data['email']) ? $quote_data['email'] : '';
    $subject = !empty($quote_data['email_subject'])
        ? $quote_data['email_subject']
        : sprintf('[%s] New quote request from %s', $site_name, $full_name);

    $body = array(
        'New quote request',
        '',
        'Product Name: ' . $quote_data['product_name'],
        'Form Location: ' . $quote_data['form_location'],
        'Quote Source: ' . $quote_data['quote_source'],
        'Size: ' . trim($quote_data['length'] . ' x ' . $quote_data['width'] . ' x ' . $quote_data['depth'] . ' ' . $quote_data['unit']),
        'Box Type / Stock Option: ' . $quote_data['stock_option'],
        'Material Preference: ' . $quote_data['material_preference'],
        'Printing Option: ' . $quote_data['printing_option'],
        'Finishing Option: ' . $quote_data['finishing_option'],
        'Quantity: ' . $quote_data['quantity'],
        '',
        'Customer Information',
        'Full Name: ' . $full_name,
        'Company: ' . $quote_data['company'],
        'Country / Region: ' . $quote_data['country'],
        'Phone: ' . $quote_data['phone'],
        'Email: ' . $email,
        '',
        'Message:',
        $quote_data['message'],
        '',
        'Tracking',
        'Current Page URL: ' . $quote_data['current_page_url'],
        'Referrer URL: ' . $quote_data['referrer_url'],
        'WordPress Referer: ' . $quote_data['referer'],
        'UTM Source: ' . $quote_data['utm_source'],
        'UTM Medium: ' . $quote_data['utm_medium'],
        'UTM Campaign: ' . $quote_data['utm_campaign'],
        'UTM Term: ' . $quote_data['utm_term'],
        'UTM Content: ' . $quote_data['utm_content'],
    );

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
    );

    if ($email && is_email($email)) {
        $headers[] = 'Reply-To: ' . $full_name . ' <' . $email . '>';
    }

    return array(
        'subject' => $subject,
        'body'    => implode("\n", $body),
        'headers' => $headers,
    );
}

function custom_box_save_quote_request($quote_data, $attachments) {
    $email = custom_box_build_quote_email($quote_data);

    $quote_id = wp_insert_post(
        array(
            'post_type'    => 'custom_box_quote',
            'post_status'  => 'private',
            'post_title'   => sprintf(
                'Quote request from %s - %s',
                $quote_data['full_name'],
                current_time('mysql')
            ),
            'post_content' => $email['body'],
        ),
        true
    );

    if (is_wp_error($quote_id)) {
        return $quote_id;
    }

    update_post_meta($quote_id, '_custom_box_quote_data', $quote_data);
    update_post_meta($quote_id, '_custom_box_quote_attachments', $attachments);
    update_post_meta($quote_id, '_custom_box_quote_mail_status', 'queued');

    return $quote_id;
}

function custom_box_schedule_quote_email($quote_id) {
    if ('sent' === get_post_meta((int) $quote_id, '_custom_box_quote_mail_status', true)) {
        return true;
    }

    if (function_exists('as_enqueue_async_action')) {
        as_enqueue_async_action('custom_box_send_queued_quote_email', array($quote_id), 'custom-box-theme');
        return true;
    }

    if (!wp_next_scheduled('custom_box_send_queued_quote_email', array($quote_id))) {
        return wp_schedule_single_event(time() + 10, 'custom_box_send_queued_quote_email', array($quote_id));
    }

    return true;
}

function custom_box_send_queued_quote_email($quote_id) {
    $quote_id = absint($quote_id);
    if (!$quote_id || 'custom_box_quote' !== get_post_type($quote_id)) {
        return;
    }

    if ('sent' === get_post_meta($quote_id, '_custom_box_quote_mail_status', true)) {
        return;
    }

    $quote_data = get_post_meta($quote_id, '_custom_box_quote_data', true);
    if (!is_array($quote_data)) {
        update_post_meta($quote_id, '_custom_box_quote_mail_status', 'missing_data');
        return;
    }

    $attachments = get_post_meta($quote_id, '_custom_box_quote_attachments', true);
    if (!is_array($attachments)) {
        $attachments = array();
    }

    $email = custom_box_build_quote_email($quote_data);
    $recipient = custom_box_quote_form_recipient();

    if (!empty($quote_data['quote_source']) && 'paper_box_manufacturer' === $quote_data['quote_source']) {
        $recipient = 'sales.vpn@hopgiayvpn.com';
    }

    $sent = wp_mail($recipient, $email['subject'], $email['body'], $email['headers'], $attachments);

    custom_box_quote_form_log(
        'mail_send_result',
        array(
            'quote_id'       => $quote_id,
            'mail_transport' => function_exists('wp_mail_smtp') ? 'wp_mail_smtp' : 'wp_mail',
            'recipient'      => $recipient,
            'sent'           => (bool) $sent,
        )
    );

    update_post_meta($quote_id, '_custom_box_quote_mail_status', $sent ? 'sent' : 'failed');
    update_post_meta($quote_id, '_custom_box_quote_mail_attempted_at', current_time('mysql'));
}
add_action('custom_box_send_queued_quote_email', 'custom_box_send_queued_quote_email');

function custom_box_handle_quote_form() {
    custom_box_quote_form_log('request_received', array('method' => isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : ''));

    if (
        empty($_POST['custom_box_quote_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['custom_box_quote_nonce'])), 'custom_box_quote_form')
    ) {
        custom_box_quote_form_reject('invalid', 400, array('nonce_valid' => false));
    }

    $honeypot = custom_box_quote_form_post_text('website_url', 255);
    custom_box_quote_form_log('honeypot_check', array('honeypot_filled' => '' !== $honeypot));
    if ($honeypot) {
        custom_box_quote_form_reject('spam', 400, array('honeypot_filled' => true));
    }

    $rate_limit = custom_box_quote_form_rate_limit_check();
    if (empty($rate_limit['allowed'])) {
        custom_box_quote_form_reject(
            'rate_limited',
            429,
            array(
                'rate_limit_count'  => $rate_limit['count'],
                'rate_limit_limit'  => $rate_limit['limit'],
                'rate_limit_window' => $rate_limit['window'],
            )
        );
    }

    $recaptcha = custom_box_quote_form_verify_recaptcha();
    custom_box_quote_form_log(
        'captcha_check',
        array(
            'captcha_success'     => !empty($recaptcha['success']),
            'captcha_reason'      => isset($recaptcha['reason']) ? $recaptcha['reason'] : '',
            'captcha_error_codes' => isset($recaptcha['error_codes']) ? $recaptcha['error_codes'] : array(),
        )
    );
    if (empty($recaptcha['success'])) {
        custom_box_quote_form_reject(
            'captcha',
            400,
            array(
                'captcha_success'     => false,
                'captcha_reason'      => isset($recaptcha['reason']) ? $recaptcha['reason'] : '',
                'captcha_error_codes' => isset($recaptcha['error_codes']) ? $recaptcha['error_codes'] : array(),
            )
        );
    }

    $product_name = custom_box_quote_form_post_text('product_name', 200);
    $length = custom_box_quote_form_post_text('length', 120);
    $width = custom_box_quote_form_post_text('width', 80);
    $depth = custom_box_quote_form_post_text('depth', 80);
    $unit = custom_box_quote_form_post_text('unit', 20);
    $stock_option = custom_box_quote_form_post_text('stock_option', 200);
    $material_preference = custom_box_quote_form_post_text('material_preference', 200);
    $printing_option = custom_box_quote_form_post_text('printing_option', 200);
    $finishing_option = custom_box_quote_form_post_text('finishing_option', 200);
    $quantity = custom_box_quote_form_post_text('quantity', 120);
    $company = custom_box_quote_form_post_text('company', 200);
    $country = custom_box_quote_form_post_text('country', 120);
    $full_name = custom_box_quote_form_post_text('full_name', 200);
    $phone = custom_box_quote_form_post_text('phone', 80);
    $email = custom_box_quote_form_post_email('email');
    $message = custom_box_quote_form_post_textarea('message', 3000);
    $quote_source = custom_box_quote_form_post_key('quote_source', 100);
    $form_location = custom_box_quote_form_post_text('form_location', 100);
    $current_page_url = custom_box_quote_form_post_url('current_page_url', 500);
    $referrer_url = custom_box_quote_form_post_url('referrer_url', 500);
    $utm_source = custom_box_quote_form_post_text('utm_source', 150);
    $utm_medium = custom_box_quote_form_post_text('utm_medium', 150);
    $utm_campaign = custom_box_quote_form_post_text('utm_campaign', 150);
    $utm_term = custom_box_quote_form_post_text('utm_term', 150);
    $utm_content = custom_box_quote_form_post_text('utm_content', 150);
    $email_subject = custom_box_quote_form_post_text('email_subject', 200);
    $attachments = array();

    if ('paper_box_manufacturer' === $quote_source) {
        if (!$full_name) {
            $full_name = $company ? $company : 'Paper box quote lead';
        }

        $has_valid_email = $email && is_email($email);
        $has_phone = '' !== trim($phone);

        if (
            !$product_name
            || !$stock_option
            || !$quantity
            || !$country
            || (!$has_valid_email && !$has_phone)
            || ($email && !$has_valid_email)
        ) {
            custom_box_quote_form_reject('missing', 400, array('validation' => 'paper_box_manufacturer_required_fields'));
        }
    } elseif (!$product_name || !$full_name || !$email || !is_email($email)) {
        custom_box_quote_form_reject('missing', 400, array('validation' => 'quote_required_fields'));
    }

    if (!empty($_FILES['artwork']['name'])) {
        $allowed_extensions = array('png', 'pdf', 'jpg', 'jpeg', 'webp', 'doc', 'docx', 'gif', 'psd', 'cdr', 'eps');
        $file_name = sanitize_file_name(wp_unslash($_FILES['artwork']['name']));
        $file_size = isset($_FILES['artwork']['size']) ? (int) $_FILES['artwork']['size'] : 0;
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_size > 10 * MB_IN_BYTES || !in_array($file_extension, $allowed_extensions, true)) {
            custom_box_quote_form_reject('file', 400, array('file_error' => 'invalid_extension_or_size'));
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $uploaded_file = wp_handle_upload(
            $_FILES['artwork'],
            array(
                'test_form' => false,
                'mimes'     => array(
                    'png'  => 'image/png',
                    'pdf'  => 'application/pdf',
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'gif'  => 'image/gif',
                    'psd'  => 'image/vnd.adobe.photoshop',
                    'cdr'  => 'application/octet-stream',
                    'eps'  => 'application/postscript',
                ),
            )
        );

        if (isset($uploaded_file['error'])) {
            custom_box_quote_form_reject('file', 400, array('file_error' => 'upload_failed'));
        }

        if (!empty($uploaded_file['file'])) {
            $attachments[] = $uploaded_file['file'];
        }
    }

    $quote_data = array(
        'product_name'        => $product_name,
        'length'              => $length,
        'width'               => $width,
        'depth'               => $depth,
        'unit'                => $unit,
        'stock_option'        => $stock_option,
        'material_preference' => $material_preference,
        'printing_option'     => $printing_option,
        'finishing_option'    => $finishing_option,
        'quantity'            => $quantity,
        'company'             => $company,
        'country'             => $country,
        'full_name'           => $full_name,
        'phone'               => $phone,
        'email'               => $email,
        'message'             => $message,
        'quote_source'        => $quote_source,
        'form_location'       => $form_location,
        'email_subject'       => $email_subject,
        'current_page_url'    => $current_page_url,
        'referrer_url'        => $referrer_url,
        'utm_source'          => $utm_source,
        'utm_medium'          => $utm_medium,
        'utm_campaign'        => $utm_campaign,
        'utm_term'            => $utm_term,
        'utm_content'         => $utm_content,
        'referer'             => wp_get_referer(),
    );

    custom_box_quote_form_log(
        'validation_success',
        array(
            'quote_source'  => $quote_source,
            'form_location' => $form_location,
            'has_email'     => '' !== $email,
            'has_phone'     => '' !== trim($phone),
            'has_file'      => !empty($attachments),
        )
    );

    $quote_id = custom_box_save_quote_request($quote_data, $attachments);
    if (is_wp_error($quote_id)) {
        custom_box_quote_form_reject('failed', 500, array('save_error' => $quote_id->get_error_code()));
    }

    custom_box_quote_form_log('quote_saved', array('quote_id' => $quote_id));

    custom_box_send_queued_quote_email($quote_id);

    if ('sent' !== get_post_meta($quote_id, '_custom_box_quote_mail_status', true) && !custom_box_schedule_quote_email($quote_id)) {
        update_post_meta($quote_id, '_custom_box_quote_mail_status', 'schedule_failed');
    }

    if ('paper_box_manufacturer' === $quote_source) {
        custom_box_quote_form_redirect_to_thank_you();
    }

    custom_box_quote_form_redirect('success');
}
add_action('admin_post_custom_box_quote_form', 'custom_box_handle_quote_form');
add_action('admin_post_nopriv_custom_box_quote_form', 'custom_box_handle_quote_form');
