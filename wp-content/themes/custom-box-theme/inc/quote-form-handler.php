<?php
/**
 * Quote form email handling.
 */

function custom_box_quote_form_recipient() {
    $recipient = get_option('admin_email');

    return apply_filters('custom_box_quote_form_recipient', $recipient);
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
    if (is_front_page() || is_page(array('contact', 'paper-box-manufacturer', 'packaging-landing'))) {
        return true;
    }

    return is_page_template('page-paper-box-manufacturer.php') || is_page_template('page-landing-packaging.php');
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

    wp_add_inline_script(
        'custom-box-google-recaptcha',
        <<<'JS'
window.customBoxRecaptchaOnload = function() {
    var widgets = document.querySelectorAll('.g-recaptcha[data-sitekey]:not([data-widget-id])');

    widgets.forEach(function(widget) {
        if (!window.grecaptcha || !window.grecaptcha.render) {
            return;
        }

        widget.dataset.widgetId = window.grecaptcha.render(widget, {
            sitekey: widget.getAttribute('data-sitekey')
        });
    });
};
JS,
        'before'
    );
}
add_action('wp_enqueue_scripts', 'custom_box_enqueue_quote_form_recaptcha');

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
        return true;
    }

    $secret_key = custom_box_quote_form_recaptcha_secret_key();
    if ('' === $secret_key) {
        return true;
    }

    $token = isset($_POST['g-recaptcha-response']) ? trim((string) wp_unslash($_POST['g-recaptcha-response'])) : '';
    if ('' === $token) {
        return false;
    }

    $body = array(
        'secret'   => $secret_key,
        'response' => $token,
    );

    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $body['remoteip'] = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    }

    $response = wp_remote_post(
        'https://www.google.com/recaptcha/api/siteverify',
        array(
            'timeout' => 8,
            'body'    => $body,
        )
    );

    if (is_wp_error($response)) {
        return false;
    }

    $payload = json_decode((string) wp_remote_retrieve_body($response), true);

    return is_array($payload) && !empty($payload['success']);
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

    update_post_meta($quote_id, '_custom_box_quote_mail_status', $sent ? 'sent' : 'failed');
    update_post_meta($quote_id, '_custom_box_quote_mail_attempted_at', current_time('mysql'));
}
add_action('custom_box_send_queued_quote_email', 'custom_box_send_queued_quote_email');

function custom_box_handle_quote_form() {
    if (
        empty($_POST['custom_box_quote_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['custom_box_quote_nonce'])), 'custom_box_quote_form')
    ) {
        custom_box_quote_form_redirect('invalid');
    }

    $honeypot = isset($_POST['website_url']) ? trim((string) wp_unslash($_POST['website_url'])) : '';
    if ($honeypot) {
        custom_box_quote_form_redirect('success');
    }

    if (!custom_box_quote_form_verify_recaptcha()) {
        custom_box_quote_form_redirect('captcha');
    }

    if (!custom_box_quote_form_recaptcha_enabled() && !custom_box_quote_form_verify_captcha()) {
        custom_box_quote_form_redirect('captcha');
    }

    $product_name = isset($_POST['product_name']) ? sanitize_text_field(wp_unslash($_POST['product_name'])) : '';
    $length = isset($_POST['length']) ? sanitize_text_field(wp_unslash($_POST['length'])) : '';
    $width = isset($_POST['width']) ? sanitize_text_field(wp_unslash($_POST['width'])) : '';
    $depth = isset($_POST['depth']) ? sanitize_text_field(wp_unslash($_POST['depth'])) : '';
    $unit = isset($_POST['unit']) ? sanitize_text_field(wp_unslash($_POST['unit'])) : '';
    $stock_option = isset($_POST['stock_option']) ? sanitize_text_field(wp_unslash($_POST['stock_option'])) : '';
    $material_preference = isset($_POST['material_preference']) ? sanitize_text_field(wp_unslash($_POST['material_preference'])) : '';
    $printing_option = isset($_POST['printing_option']) ? sanitize_text_field(wp_unslash($_POST['printing_option'])) : '';
    $finishing_option = isset($_POST['finishing_option']) ? sanitize_text_field(wp_unslash($_POST['finishing_option'])) : '';
    $quantity = isset($_POST['quantity']) ? sanitize_text_field(wp_unslash($_POST['quantity'])) : '';
    $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';
    $country = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';
    $full_name = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $quote_source = isset($_POST['quote_source']) ? sanitize_key(wp_unslash($_POST['quote_source'])) : '';
    $form_location = isset($_POST['form_location']) ? sanitize_text_field(wp_unslash($_POST['form_location'])) : '';
    $current_page_url = isset($_POST['current_page_url']) ? esc_url_raw(wp_unslash($_POST['current_page_url'])) : '';
    $referrer_url = isset($_POST['referrer_url']) ? esc_url_raw(wp_unslash($_POST['referrer_url'])) : '';
    $utm_source = isset($_POST['utm_source']) ? sanitize_text_field(wp_unslash($_POST['utm_source'])) : '';
    $utm_medium = isset($_POST['utm_medium']) ? sanitize_text_field(wp_unslash($_POST['utm_medium'])) : '';
    $utm_campaign = isset($_POST['utm_campaign']) ? sanitize_text_field(wp_unslash($_POST['utm_campaign'])) : '';
    $utm_term = isset($_POST['utm_term']) ? sanitize_text_field(wp_unslash($_POST['utm_term'])) : '';
    $utm_content = isset($_POST['utm_content']) ? sanitize_text_field(wp_unslash($_POST['utm_content'])) : '';
    $email_subject = isset($_POST['email_subject']) ? sanitize_text_field(wp_unslash($_POST['email_subject'])) : '';
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
            custom_box_quote_form_redirect('missing');
        }
    } elseif (!$product_name || !$full_name || !$email || !is_email($email)) {
        custom_box_quote_form_redirect('missing');
    }

    if (!empty($_FILES['artwork']['name'])) {
        $allowed_extensions = array('png', 'pdf', 'jpg', 'jpeg', 'webp', 'doc', 'docx', 'gif', 'psd', 'cdr', 'eps');
        $file_name = sanitize_file_name(wp_unslash($_FILES['artwork']['name']));
        $file_size = isset($_FILES['artwork']['size']) ? (int) $_FILES['artwork']['size'] : 0;
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_size > 10 * MB_IN_BYTES || !in_array($file_extension, $allowed_extensions, true)) {
            custom_box_quote_form_redirect('file');
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
            custom_box_quote_form_redirect('file');
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

    $quote_id = custom_box_save_quote_request($quote_data, $attachments);
    if (is_wp_error($quote_id)) {
        custom_box_quote_form_redirect('failed');
    }

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
