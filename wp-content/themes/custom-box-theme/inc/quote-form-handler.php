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
        $redirect_to = home_url('/#quote');
    }

    $redirect_to = remove_query_arg('quote_status', $redirect_to);
    $redirect_to = add_query_arg('quote_status', $status, $redirect_to);

    wp_safe_redirect($redirect_to . '#quote');
    exit;
}

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

    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $subject = sprintf('[%s] New quote request from %s', $site_name, $full_name);

    $body = array(
        'New quote request',
        '',
        'Product Name: ' . $product_name,
        'Size: ' . trim($length . ' x ' . $width . ' x ' . $depth . ' ' . $unit),
        'Stock Option: ' . $stock_option,
        'Printing Option: ' . $printing_option,
        'Finishing Option: ' . $finishing_option,
        'Quantity: ' . $quantity,
        '',
        'Customer Information',
        'Full Name: ' . $full_name,
        'Company: ' . $company,
        'Country / Region: ' . $country,
        'Phone: ' . $phone,
        'Email: ' . $email,
        '',
        'Message:',
        $message,
        '',
        'Page: ' . wp_get_referer(),
    );

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $full_name . ' <' . $email . '>',
    );

    $sent = wp_mail(custom_box_quote_form_recipient(), $subject, implode("\n", $body), $headers, $attachments);

    custom_box_quote_form_redirect($sent ? 'success' : 'failed');
}
add_action('admin_post_custom_box_quote_form', 'custom_box_handle_quote_form');
add_action('admin_post_nopriv_custom_box_quote_form', 'custom_box_handle_quote_form');
