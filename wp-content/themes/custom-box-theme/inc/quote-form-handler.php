<?php
/**
 * Quote form email handling.
 */

function custom_box_quote_form_recipient() {
    return 'sales.vpn@hopgiayvpn.com';
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

    unset(
        $context['secret'],
        $context['secret_key'],
        $context['token'],
        $context['custom_box_math_answer'],
        $context['custom_box_math_payload'],
        $context['custom_box_math_signature']
    );

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

function custom_box_quote_form_anti_spam_context($context) {
    $context = sanitize_key($context);

    return '' === $context ? 'quote' : $context;
}

function custom_box_quote_form_timestamp_min_age() {
    return max(1, (int) apply_filters('custom_box_quote_form_timestamp_min_age', 5));
}

function custom_box_quote_form_timestamp_max_age() {
    return max(custom_box_quote_form_timestamp_min_age(), (int) apply_filters('custom_box_quote_form_timestamp_max_age', DAY_IN_SECONDS));
}

function custom_box_quote_form_timestamp_signature($timestamp, $context) {
    $timestamp = (string) absint($timestamp);
    $context = custom_box_quote_form_anti_spam_context($context);

    return hash_hmac('sha256', $timestamp . '|' . $context, wp_salt('auth'));
}

function custom_box_quote_form_anti_spam_fields($context = 'quote') {
    $context = custom_box_quote_form_anti_spam_context($context);
    $timestamp = time();
    ?>
    <input class="quote-hp custom-box-quote-honeypot" type="text" name="website_url" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;opacity:0;">
    <input type="hidden" name="custom_box_form_started_at" value="<?php echo esc_attr($timestamp); ?>">
    <input type="hidden" name="custom_box_form_context" value="<?php echo esc_attr($context); ?>">
    <input type="hidden" name="custom_box_form_signature" value="<?php echo esc_attr(custom_box_quote_form_timestamp_signature($timestamp, $context)); ?>">
    <?php
}

function custom_box_quote_form_timestamp_check($started_at, $context, $signature) {
    $started_at = absint($started_at);
    $context = custom_box_quote_form_anti_spam_context($context);
    $signature = is_string($signature) ? trim($signature) : '';

    if (!$started_at || '' === $signature) {
        return array(
            'valid'  => false,
            'age'    => 0,
            'reason' => 'timestamp_missing',
        );
    }

    if (!preg_match('/^[a-f0-9]{64}$/', $signature)) {
        return array(
            'valid'  => false,
            'age'    => 0,
            'reason' => 'timestamp_bad_signature',
        );
    }

    $expected_signature = custom_box_quote_form_timestamp_signature($started_at, $context);
    if (!hash_equals($expected_signature, $signature)) {
        return array(
            'valid'  => false,
            'age'    => 0,
            'reason' => 'timestamp_bad_signature',
        );
    }

    $age = time() - $started_at;
    if ($age < 0 || $age > custom_box_quote_form_timestamp_max_age()) {
        return array(
            'valid'  => false,
            'age'    => max(0, $age),
            'reason' => 'timestamp_expired',
        );
    }

    if ($age < custom_box_quote_form_timestamp_min_age()) {
        return array(
            'valid'  => true,
            'age'    => $age,
            'reason' => 'submitted_under_5_seconds',
        );
    }

    return array(
        'valid'  => true,
        'age'    => $age,
        'reason' => 'timestamp_ok',
    );
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

function custom_box_quote_form_add_spam_reason(&$score, &$reasons, $points, $reason) {
    $points = (int) $points;
    $reason = sanitize_key($reason);

    if ($points <= 0 || '' === $reason) {
        return;
    }

    $score += $points;
    $reasons[] = array(
        'reason' => $reason,
        'points' => $points,
    );
}

function custom_box_quote_form_reason_keys($reasons) {
    if (!is_array($reasons)) {
        return array();
    }

    $keys = array();
    foreach ($reasons as $reason) {
        if (is_array($reason) && !empty($reason['reason'])) {
            $keys[] = sanitize_key($reason['reason']);
        } elseif (is_string($reason) && '' !== $reason) {
            $keys[] = sanitize_key($reason);
        }
    }

    return array_values(array_unique(array_filter($keys)));
}

function custom_box_quote_form_extract_quantity_number($quantity) {
    $quantity = str_replace(',', '', (string) $quantity);

    if (!preg_match('/\d+/', $quantity, $matches)) {
        return null;
    }

    return (int) $matches[0];
}

function custom_box_quote_form_message_has_unusual_characters($message) {
    $message = (string) $message;

    if ('' === trim($message)) {
        return false;
    }

    if (preg_match('/[\x{0400}-\x{052F}\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{10A0}-\x{10FF}]/u', $message)) {
        return true;
    }

    return (bool) preg_match('/[^\p{L}\p{N}\s\.,;:!\?@\&%\$#\(\)\+\-\/\'"]{4,}/u', $message);
}

function custom_box_quote_form_message_has_url($message) {
    return (bool) preg_match(
        '/https?:\/\/|www\.|[a-z0-9][a-z0-9-]{1,60}\.(?:com|net|org|ru|cn|xyz|top|club|info|biz|site|online|shop|click|link)(?:[\/\s]|$)/i',
        (string) $message
    );
}

function custom_box_quote_form_email_is_suspicious($email) {
    $email = sanitize_email($email);

    if ('' === $email) {
        return false;
    }

    if (!is_email($email)) {
        return true;
    }

    $parts = explode('@', strtolower($email));
    $local = isset($parts[0]) ? $parts[0] : '';
    $domain = isset($parts[1]) ? $parts[1] : '';

    $disposable_domains = (array) apply_filters(
        'custom_box_quote_form_disposable_email_domains',
        array(
            '10minutemail.com',
            '20minutemail.com',
            'dispostable.com',
            'fakeinbox.com',
            'getnada.com',
            'guerrillamail.com',
            'guerrillamail.net',
            'mailinator.com',
            'moakt.com',
            'sharklasers.com',
            'tempmail.com',
            'temp-mail.org',
            'throwawaymail.com',
            'trashmail.com',
            'yopmail.com',
        )
    );

    foreach ($disposable_domains as $disposable_domain) {
        $disposable_domain = strtolower(trim((string) $disposable_domain));
        if ('' !== $disposable_domain && $domain === $disposable_domain) {
            return true;
        }
    }

    return (bool) preg_match('/^(?:spam|test|asdf|qwerty|noreply|no-reply|robertzet)(?:[._+-]?\d*)?$/i', $local);
}

function custom_box_quote_form_normalize_hard_block_name($value) {
    $value = strtolower(trim((string) $value));

    return preg_replace('/\s+/', '', $value);
}

function custom_box_quote_form_is_known_hard_block_spam_name($value) {
    return 'robertzet' === custom_box_quote_form_normalize_hard_block_name($value);
}

function vpn_calculate_quote_spam_score($data) {
    $data = wp_parse_args(
        is_array($data) ? $data : array(),
        array(
            'product_name'        => '',
            'full_name'          => '',
            'company'            => '',
            'country'            => '',
            'email'              => '',
            'message'            => '',
            'quantity'           => '',
            'honeypot'           => '',
            'secondary_honeypot' => '',
            'form_started_at'    => 0,
            'form_context'       => 'quote',
            'form_signature'     => '',
            'rate_limit'         => array(),
        )
    );

    $score = 0;
    $reasons = array();

    if ('' !== trim((string) $data['honeypot']) || '' !== trim((string) $data['secondary_honeypot'])) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 10, 'honeypot_filled');
    }

    if (
        custom_box_quote_form_is_known_hard_block_spam_name($data['full_name'])
        || custom_box_quote_form_is_known_hard_block_spam_name($data['product_name'])
    ) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 4, 'known_hard_block_spam_name');
    }

    $timestamp_check = custom_box_quote_form_timestamp_check(
        $data['form_started_at'],
        $data['form_context'],
        $data['form_signature']
    );

    if (empty($timestamp_check['valid'])) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 5, $timestamp_check['reason']);
    } elseif ('submitted_under_5_seconds' === $timestamp_check['reason']) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 5, 'submitted_under_5_seconds');
    }

    $name_patterns = (array) apply_filters(
        'custom_box_quote_form_spam_name_patterns',
        array(
            '/\brobert\s*zet\b/i',
            '/\brobertzet\b/i',
        )
    );

    foreach ($name_patterns as $pattern) {
        if (@preg_match($pattern, (string) $data['full_name'])) {
            custom_box_quote_form_add_spam_reason($score, $reasons, 4, 'known_spam_name_pattern');
            break;
        }
    }

    if ('' === trim((string) $data['company'])) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 2, 'company_empty');
    }

    if ('' === trim((string) $data['country'])) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 2, 'country_empty');
    }

    if (custom_box_quote_form_message_has_unusual_characters($data['message'])) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 3, 'message_unusual_characters');
    }

    if (custom_box_quote_form_message_has_url($data['message'])) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 3, 'message_contains_url');
    }

    $quantity_number = custom_box_quote_form_extract_quantity_number($data['quantity']);
    if (null !== $quantity_number && $quantity_number < 100) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 1, 'quantity_below_100');
    }

    $rate_limit = is_array($data['rate_limit']) ? $data['rate_limit'] : array();
    $rate_count = isset($rate_limit['count']) ? (int) $rate_limit['count'] : 0;
    $rate_limit_max = isset($rate_limit['limit']) ? (int) $rate_limit['limit'] : 3;
    if ($rate_count > $rate_limit_max) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 4, 'ip_rate_limit_exceeded');
    }

    if (custom_box_quote_form_email_is_suspicious($data['email'])) {
        custom_box_quote_form_add_spam_reason($score, $reasons, 3, 'suspicious_email');
    }

    return apply_filters(
        'custom_box_quote_form_spam_score',
        array(
            'score'           => $score,
            'reasons'         => $reasons,
            'reason_keys'     => custom_box_quote_form_reason_keys($reasons),
            'timestamp_check' => $timestamp_check,
        ),
        $data
    );
}

function custom_box_quote_form_spam_block_threshold() {
    return (int) apply_filters('custom_box_quote_form_spam_block_threshold', 6);
}

function custom_box_quote_form_spam_suspicious_threshold() {
    return (int) apply_filters('custom_box_quote_form_spam_suspicious_threshold', 3);
}

function custom_box_quote_form_is_blocked_spam($spam_result) {
    $score = isset($spam_result['score']) ? (int) $spam_result['score'] : 0;
    $reason_keys = isset($spam_result['reason_keys']) ? (array) $spam_result['reason_keys'] : array();

    if (in_array('honeypot_filled', $reason_keys, true)) {
        return true;
    }

    if (in_array('known_hard_block_spam_name', $reason_keys, true)) {
        return true;
    }

    if ($score >= custom_box_quote_form_spam_block_threshold()) {
        return true;
    }

    // Fast form posts are usually automated and should fail closed without telling bots.
    return in_array('submitted_under_5_seconds', $reason_keys, true);
}

function custom_box_quote_form_spam_log_limit() {
    return max(10, (int) apply_filters('custom_box_quote_form_spam_log_limit', 200));
}

function custom_box_quote_form_save_spam_log($quote_data, $spam_result) {
    $option_name = 'custom_box_quote_spam_log';
    $log = get_option($option_name, array());

    if (!is_array($log)) {
        $log = array();
    }

    $entry = array(
        'timestamp'     => current_time('mysql'),
        'ip'            => custom_box_quote_form_client_ip(),
        'ip_hash'       => substr(custom_box_quote_form_ip_hash(), 0, 16),
        'name'          => isset($quote_data['full_name']) ? sanitize_text_field($quote_data['full_name']) : '',
        'email'         => isset($quote_data['email']) ? sanitize_email($quote_data['email']) : '',
        'message'       => isset($quote_data['message']) ? sanitize_textarea_field($quote_data['message']) : '',
        'spam_score'    => isset($spam_result['score']) ? (int) $spam_result['score'] : 0,
        'spam_reasons'  => isset($spam_result['reasons']) && is_array($spam_result['reasons']) ? $spam_result['reasons'] : array(),
        'quote_source'  => isset($quote_data['quote_source']) ? sanitize_key($quote_data['quote_source']) : '',
        'form_location' => isset($quote_data['form_location']) ? sanitize_text_field($quote_data['form_location']) : '',
    );

    array_unshift($log, $entry);
    $log = array_slice($log, 0, custom_box_quote_form_spam_log_limit());

    if (false === get_option($option_name, false)) {
        add_option($option_name, $log, '', false);
    } else {
        update_option($option_name, $log, false);
    }

    custom_box_quote_form_log(
        'spam_blocked',
        array(
            'spam_score'   => $entry['spam_score'],
            'spam_reasons' => custom_box_quote_form_reason_keys($entry['spam_reasons']),
        )
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

function custom_box_quote_form_recaptcha_temporarily_disabled() {
    return (bool) apply_filters('custom_box_quote_form_recaptcha_temporarily_disabled', true);
}

function custom_box_quote_form_recaptcha_fields() {
    if (custom_box_quote_form_recaptcha_temporarily_disabled()) {
        return;
    }

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
    if (custom_box_quote_form_recaptcha_temporarily_disabled()) {
        return false;
    }

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

function custom_box_quote_form_math_challenge_lifetime() {
    return max(60, (int) apply_filters('custom_box_quote_form_math_challenge_lifetime', custom_box_quote_form_captcha_lifetime()));
}

function custom_box_quote_form_math_challenge_min_age() {
    return max(0, (int) apply_filters('custom_box_quote_form_math_challenge_min_age', 2));
}

function custom_box_quote_form_math_challenge_signature($payload) {
    return hash_hmac('sha256', $payload, wp_salt('auth'));
}

function custom_box_quote_form_create_math_challenge($context = 'quote') {
    $context = sanitize_key($context);
    if ('' === $context) {
        $context = 'quote';
    }

    $left = wp_rand(2, 9);
    $right = wp_rand(1, 9);
    $now = time();

    $payload = custom_box_quote_form_base64url_encode(
        wp_json_encode(
            array(
                'a'          => $left,
                'b'          => $right,
                'op'         => '+',
                'created_at' => $now,
                'expires_at' => $now + custom_box_quote_form_math_challenge_lifetime(),
                'context'    => $context,
            )
        )
    );

    return array(
        'question'  => sprintf('Security check: %d + %d = ?', $left, $right),
        'payload'   => $payload,
        'signature' => custom_box_quote_form_math_challenge_signature($payload),
    );
}

function custom_box_quote_form_math_challenge_fields($context = 'quote') {
    $challenge = custom_box_quote_form_create_math_challenge($context);
    ?>
    <div class="custom-box-human-check">
        <label class="custom-box-human-check-label">
            <span><?php echo esc_html($challenge['question']); ?></span>
            <input type="text" name="custom_box_math_answer" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
        </label>
        <input type="hidden" name="custom_box_math_payload" value="<?php echo esc_attr($challenge['payload']); ?>">
        <input type="hidden" name="custom_box_math_signature" value="<?php echo esc_attr($challenge['signature']); ?>">
        <label class="custom-box-human-check-hp" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
            Leave this field empty
            <input type="text" name="custom_box_website" tabindex="-1" autocomplete="off">
        </label>
    </div>
    <?php
}

function custom_box_quote_form_log_human_check_fail($reason) {
    $reason = sanitize_key($reason);

    error_log('[Quote Human Check] reason=' . $reason);
    custom_box_quote_form_log('human_check_fail', array('reason' => $reason));
}

function custom_box_quote_form_math_challenge_fail($reason) {
    custom_box_quote_form_log_human_check_fail($reason);

    return array(
        'success' => false,
        'reason'  => $reason,
    );
}

function custom_box_quote_form_verify_math_challenge() {
    $honeypot = custom_box_quote_form_post_text('custom_box_website', 255);
    if ('' !== $honeypot) {
        return custom_box_quote_form_math_challenge_fail('honeypot_filled');
    }

    $answer = custom_box_quote_form_post_text('custom_box_math_answer', 20);
    $payload = custom_box_quote_form_post_text('custom_box_math_payload', 1000);
    $signature = custom_box_quote_form_post_text('custom_box_math_signature', 128);

    if ('' === $answer || '' === $payload || '' === $signature) {
        return custom_box_quote_form_math_challenge_fail('math_missing');
    }

    if (!preg_match('/^[a-f0-9]{64}$/', $signature)) {
        return custom_box_quote_form_math_challenge_fail('math_bad_signature');
    }

    $expected_signature = custom_box_quote_form_math_challenge_signature($payload);
    if (!hash_equals($expected_signature, $signature)) {
        return custom_box_quote_form_math_challenge_fail('math_bad_signature');
    }

    $decoded = custom_box_quote_form_base64url_decode($payload);
    if (false === $decoded) {
        return custom_box_quote_form_math_challenge_fail('math_bad_signature');
    }

    $data = json_decode($decoded, true);
    if (!is_array($data)) {
        return custom_box_quote_form_math_challenge_fail('math_bad_signature');
    }

    $left = isset($data['a']) ? (int) $data['a'] : 0;
    $right = isset($data['b']) ? (int) $data['b'] : 0;
    $operator = isset($data['op']) ? (string) $data['op'] : '';
    $created_at = isset($data['created_at']) ? (int) $data['created_at'] : 0;
    $expires_at = isset($data['expires_at']) ? (int) $data['expires_at'] : 0;

    if ($left < 2 || $left > 9 || $right < 1 || $right > 9 || '+' !== $operator || $created_at <= 0 || $expires_at <= 0 || $created_at >= $expires_at) {
        return custom_box_quote_form_math_challenge_fail('math_bad_signature');
    }

    $now = time();
    if ($now > $expires_at) {
        return custom_box_quote_form_math_challenge_fail('math_expired');
    }

    if ($created_at > ($now - custom_box_quote_form_math_challenge_min_age())) {
        return custom_box_quote_form_math_challenge_fail('math_too_fast');
    }

    if (!preg_match('/^\d+$/', $answer)) {
        return custom_box_quote_form_math_challenge_fail('math_wrong_answer');
    }

    if ((int) $answer !== ($left + $right)) {
        return custom_box_quote_form_math_challenge_fail('math_wrong_answer');
    }

    return array(
        'success' => true,
        'reason'  => 'math_success',
    );
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
    // Temporarily bypassed while the signed math challenge protects quote submissions.
    return array(
        'success' => true,
        'reason'  => 'recaptcha_temporarily_disabled',
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
            'spam_status'         => 'clean',
            'spam_score'          => 0,
            'spam_reasons'        => array(),
        )
    );

    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $full_name = isset($quote_data['full_name']) ? $quote_data['full_name'] : '';
    $email = isset($quote_data['email']) ? $quote_data['email'] : '';
    $spam_status = isset($quote_data['spam_status']) ? sanitize_key($quote_data['spam_status']) : 'clean';
    $spam_score = isset($quote_data['spam_score']) ? (int) $quote_data['spam_score'] : 0;
    $spam_reasons = isset($quote_data['spam_reasons']) && is_array($quote_data['spam_reasons'])
        ? custom_box_quote_form_reason_keys($quote_data['spam_reasons'])
        : array();
    $subject = !empty($quote_data['email_subject'])
        ? $quote_data['email_subject']
        : sprintf('[%s] New quote request from %s', $site_name, $full_name);

    if ('suspicious' === $spam_status && 0 !== strpos($subject, '[Suspicious Quote Lead]')) {
        $subject = '[Suspicious Quote Lead] ' . $subject;
    }

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

    if ('suspicious' === $spam_status) {
        array_unshift(
            $body,
            'Suspicious quote lead warning',
            'Spam Score: ' . $spam_score,
            'Spam Reasons: ' . implode(', ', $spam_reasons),
            ''
        );
    }

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
    update_post_meta($quote_id, '_custom_box_quote_spam_status', isset($quote_data['spam_status']) ? sanitize_key($quote_data['spam_status']) : 'clean');
    update_post_meta($quote_id, '_custom_box_quote_spam_score', isset($quote_data['spam_score']) ? (int) $quote_data['spam_score'] : 0);
    update_post_meta($quote_id, '_custom_box_quote_spam_reasons', isset($quote_data['spam_reasons']) ? $quote_data['spam_reasons'] : array());
    update_post_meta($quote_id, '_custom_box_quote_marketing_sync_allowed', (empty($quote_data['spam_status']) || 'clean' === sanitize_key($quote_data['spam_status'])) ? 'yes' : 'no');

    return $quote_id;
}

function custom_box_schedule_quote_email($quote_id) {
    if ('sent' === get_post_meta((int) $quote_id, '_custom_box_quote_mail_status', true)) {
        return true;
    }

    if (function_exists('as_enqueue_async_action')) {
        return (bool) as_enqueue_async_action('custom_box_send_queued_quote_email', array($quote_id), 'custom-box-theme');
    }

    if (!wp_next_scheduled('custom_box_send_queued_quote_email', array($quote_id))) {
        return wp_schedule_single_event(time() + 10, 'custom_box_send_queued_quote_email', array($quote_id));
    }

    return true;
}

function custom_box_send_queued_quote_email($quote_id) {
    $quote_id = absint($quote_id);
    if (!$quote_id || 'custom_box_quote' !== get_post_type($quote_id)) {
        return false;
    }

    if ('sent' === get_post_meta($quote_id, '_custom_box_quote_mail_status', true)) {
        return true;
    }

    $quote_data = get_post_meta($quote_id, '_custom_box_quote_data', true);
    if (!is_array($quote_data)) {
        update_post_meta($quote_id, '_custom_box_quote_mail_status', 'missing_data');
        return false;
    }

    $attachments = get_post_meta($quote_id, '_custom_box_quote_attachments', true);
    if (!is_array($attachments)) {
        $attachments = array();
    }

    // A stale or unreadable upload makes PHPMailer reject the whole message.
    $attachments = array_values(
        array_filter(
            $attachments,
            static function ($attachment) {
                return is_string($attachment) && is_file($attachment) && is_readable($attachment);
            }
        )
    );

    $email = custom_box_build_quote_email($quote_data);
    $recipient = custom_box_quote_form_recipient();

    if (!is_email($recipient)) {
        update_post_meta($quote_id, '_custom_box_quote_mail_status', 'invalid_recipient');
        update_post_meta($quote_id, '_custom_box_quote_mail_error', 'The configured quote recipient is invalid.');
        return false;
    }

    $mail_error = '';
    $mail_failure_listener = static function ($error) use (&$mail_error) {
        if (is_wp_error($error)) {
            $mail_error = $error->get_error_message();
        }
    };

    add_action('wp_mail_failed', $mail_failure_listener);
    $sent = wp_mail($recipient, $email['subject'], $email['body'], $email['headers'], $attachments);
    remove_action('wp_mail_failed', $mail_failure_listener);

    $attempts = (int) get_post_meta($quote_id, '_custom_box_quote_mail_attempts', true) + 1;

    custom_box_quote_form_log(
        'mail_send_result',
        array(
            'quote_id'       => $quote_id,
            'recipient'      => $recipient,
            'sent'           => (bool) $sent,
            'error'          => $mail_error,
            'attempt'        => $attempts,
        )
    );

    update_post_meta($quote_id, '_custom_box_quote_mail_status', $sent ? 'sent' : 'failed');
    update_post_meta($quote_id, '_custom_box_quote_mail_attempted_at', current_time('mysql'));
    update_post_meta($quote_id, '_custom_box_quote_mail_attempts', $attempts);
    update_post_meta($quote_id, '_custom_box_quote_mail_recipient', $recipient);

    if ($sent) {
        delete_post_meta($quote_id, '_custom_box_quote_mail_error');
    } else {
        update_post_meta(
            $quote_id,
            '_custom_box_quote_mail_error',
            $mail_error ? sanitize_text_field($mail_error) : 'wp_mail returned false without an error message.'
        );
    }

    return (bool) $sent;
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

    // Anti-spam blocking is intentionally disabled. Keep the request data clean
    // so legitimate leads always continue to validation and email delivery.
    $quote_data['spam_status'] = 'clean';
    $quote_data['spam_score'] = 0;
    $quote_data['spam_reasons'] = array();

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

    $quote_data['full_name'] = $full_name;

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

    $sent = custom_box_send_queued_quote_email($quote_id);

    if (!$sent) {
        if (!custom_box_schedule_quote_email($quote_id)) {
            update_post_meta($quote_id, '_custom_box_quote_mail_status', 'schedule_failed');
        }

        custom_box_quote_form_reject(
            'failed',
            500,
            array(
                'quote_id'    => $quote_id,
                'mail_status' => get_post_meta($quote_id, '_custom_box_quote_mail_status', true),
            )
        );
    }

    if ('paper_box_manufacturer' === $quote_source) {
        custom_box_quote_form_redirect_to_thank_you();
    }

    custom_box_quote_form_redirect('success');
}
add_action('admin_post_custom_box_quote_form', 'custom_box_handle_quote_form');
add_action('admin_post_nopriv_custom_box_quote_form', 'custom_box_handle_quote_form');
