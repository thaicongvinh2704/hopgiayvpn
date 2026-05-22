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

    $redirect_to = strtok($redirect_to, '#');
    $redirect_to = remove_query_arg('quote_status', $redirect_to);
    $redirect_to = add_query_arg('quote_status', $status, $redirect_to);

    wp_safe_redirect($redirect_to . '#quote');
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
    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $full_name = isset($quote_data['full_name']) ? $quote_data['full_name'] : '';
    $email = isset($quote_data['email']) ? $quote_data['email'] : '';

    $body = array(
        'New quote request',
        '',
        'Product Name: ' . $quote_data['product_name'],
        'Size: ' . trim($quote_data['length'] . ' x ' . $quote_data['width'] . ' x ' . $quote_data['depth'] . ' ' . $quote_data['unit']),
        'Stock Option: ' . $quote_data['stock_option'],
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
        'Page: ' . $quote_data['referer'],
    );

    return array(
        'subject' => sprintf('[%s] New quote request from %s', $site_name, $full_name),
        'body'    => implode("\n", $body),
        'headers' => array(
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $full_name . ' <' . $email . '>',
        ),
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
    $sent = wp_mail(custom_box_quote_form_recipient(), $email['subject'], $email['body'], $email['headers'], $attachments);

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

    $product_name = isset($_POST['product_name']) ? sanitize_text_field(wp_unslash($_POST['product_name'])) : '';
    $length = isset($_POST['length']) ? sanitize_text_field(wp_unslash($_POST['length'])) : '';
    $width = isset($_POST['width']) ? sanitize_text_field(wp_unslash($_POST['width'])) : '';
    $depth = isset($_POST['depth']) ? sanitize_text_field(wp_unslash($_POST['depth'])) : '';
    $unit = isset($_POST['unit']) ? sanitize_text_field(wp_unslash($_POST['unit'])) : '';
    $stock_option = isset($_POST['stock_option']) ? sanitize_text_field(wp_unslash($_POST['stock_option'])) : '';
    $printing_option = isset($_POST['printing_option']) ? sanitize_text_field(wp_unslash($_POST['printing_option'])) : '';
    $finishing_option = isset($_POST['finishing_option']) ? sanitize_text_field(wp_unslash($_POST['finishing_option'])) : '';
    $quantity = isset($_POST['quantity']) ? sanitize_text_field(wp_unslash($_POST['quantity'])) : '';
    $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';
    $country = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';
    $full_name = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $attachments = array();

    if (!$product_name || !$full_name || !$email || !is_email($email)) {
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
        'product_name'     => $product_name,
        'length'           => $length,
        'width'            => $width,
        'depth'            => $depth,
        'unit'             => $unit,
        'stock_option'     => $stock_option,
        'printing_option'  => $printing_option,
        'finishing_option' => $finishing_option,
        'quantity'         => $quantity,
        'company'          => $company,
        'country'          => $country,
        'full_name'        => $full_name,
        'phone'            => $phone,
        'email'            => $email,
        'message'          => $message,
        'referer'          => wp_get_referer(),
    );

    $quote_id = custom_box_save_quote_request($quote_data, $attachments);
    if (is_wp_error($quote_id)) {
        custom_box_quote_form_redirect('failed');
    }

    if (!custom_box_schedule_quote_email($quote_id)) {
        update_post_meta($quote_id, '_custom_box_quote_mail_status', 'schedule_failed');
    }

    custom_box_quote_form_redirect('success');
}
add_action('admin_post_custom_box_quote_form', 'custom_box_handle_quote_form');
add_action('admin_post_nopriv_custom_box_quote_form', 'custom_box_handle_quote_form');
