<?php
/**
 * Product specification fields for WooCommerce products.
 */

defined('ABSPATH') || exit;

function custom_box_product_spec_default_rows() {
    return array(
        array('label' => 'Feature', 'value' => ''),
        array('label' => 'Industrial Use', 'value' => ''),
        array('label' => 'Paper Type', 'value' => ''),
        array('label' => 'Box Type', 'value' => ''),
        array('label' => 'Shape', 'value' => ''),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => ''),
        array('label' => 'Brand Name', 'value' => 'VPN'),
        array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
        array('label' => 'Accessories', 'value' => ''),
        array('label' => 'Custom Order', 'value' => 'Accept'),
        array('label' => 'Liner Type', 'value' => ''),
        array('label' => 'Logo Printing', 'value' => ''),
        array('label' => 'Printing Handling', 'value' => ''),
        array('label' => 'Color', 'value' => 'CMYK / Customized'),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => ''),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => ''),
        array('label' => 'Product Name', 'value' => ''),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function custom_box_product_spec_fallback_rows() {
    return array(
        array('label' => 'Materials', 'value' => 'SBS, Kraft, Corrugated'),
        array('label' => 'Printing', 'value' => 'CMYK, Pantone, Digital'),
        array('label' => 'Finishing', 'value' => 'Matte, Gloss, Foil, Spot UV'),
        array('label' => 'Sizes', 'value' => 'Fully Custom'),
        array('label' => 'Artwork', 'value' => 'AI, PDF, PSD, EPS'),
    );
}

function custom_box_sanitize_product_specs($labels, $values) {
    $labels = is_array($labels) ? $labels : array();
    $values = is_array($values) ? $values : array();
    $specs = array();

    foreach ($labels as $index => $label) {
        $label = sanitize_text_field(wp_unslash($label));
        $value = isset($values[$index]) ? sanitize_text_field(wp_unslash($values[$index])) : '';

        if ('' === $label || '' === $value) {
            continue;
        }

        $specs[] = array(
            'label' => $label,
            'value' => $value,
        );
    }

    return $specs;
}

function custom_box_get_product_specifications($product_id) {
    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);

    if (is_array($specs) && !empty($specs)) {
        return $specs;
    }

    return custom_box_product_spec_fallback_rows();
}

function custom_box_add_product_specs_meta_box() {
    add_meta_box(
        'custom_box_product_specs',
        __('Product Specifications', 'custom-box-theme'),
        'custom_box_render_product_specs_meta_box',
        'product',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes_product', 'custom_box_add_product_specs_meta_box');

function custom_box_render_product_specs_meta_box($post) {
    $saved_specs = get_post_meta($post->ID, '_custom_box_product_specs', true);
    $rows = (is_array($saved_specs) && !empty($saved_specs)) ? $saved_specs : custom_box_product_spec_default_rows();

    wp_nonce_field('custom_box_save_product_specs', 'custom_box_product_specs_nonce');
    ?>
    <div class="custom-box-product-specs">
        <p class="description"><?php esc_html_e('Enter the technical specifications shown on the product detail page. Empty rows will not be displayed.', 'custom-box-theme'); ?></p>
        <table class="widefat custom-box-product-specs-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Specification', 'custom-box-theme'); ?></th>
                    <th><?php esc_html_e('Value', 'custom-box-theme'); ?></th>
                    <th class="custom-box-product-specs-actions"><?php esc_html_e('Action', 'custom-box-theme'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td><input type="text" name="custom_box_product_spec_label[]" value="<?php echo esc_attr($row['label']); ?>" placeholder="<?php esc_attr_e('Paper Type', 'custom-box-theme'); ?>"></td>
                        <td><input type="text" name="custom_box_product_spec_value[]" value="<?php echo esc_attr($row['value']); ?>" placeholder="<?php esc_attr_e('Kraft Paper', 'custom-box-theme'); ?>"></td>
                        <td><button type="button" class="button custom-box-remove-spec-row"><?php esc_html_e('Remove', 'custom-box-theme'); ?></button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><button type="button" class="button custom-box-add-spec-row"><?php esc_html_e('+ Add Specification', 'custom-box-theme'); ?></button></p>
    </div>
    <?php
}

function custom_box_save_product_specs($post_id) {
    if (
        empty($_POST['custom_box_product_specs_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['custom_box_product_specs_nonce'])), 'custom_box_save_product_specs')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $labels = isset($_POST['custom_box_product_spec_label']) ? $_POST['custom_box_product_spec_label'] : array();
    $values = isset($_POST['custom_box_product_spec_value']) ? $_POST['custom_box_product_spec_value'] : array();
    $specs = custom_box_sanitize_product_specs($labels, $values);

    if (!empty($specs)) {
        update_post_meta($post_id, '_custom_box_product_specs', $specs);
    } else {
        delete_post_meta($post_id, '_custom_box_product_specs');
    }
}
add_action('save_post_product', 'custom_box_save_product_specs');

function custom_box_enqueue_product_specs_admin($hook) {
    if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || 'product' !== $screen->post_type) {
        return;
    }

    wp_register_style('custom-box-product-specs-admin', false, array(), '1.0');
    wp_enqueue_style('custom-box-product-specs-admin');
    wp_add_inline_style(
        'custom-box-product-specs-admin',
        '
        .custom-box-product-specs-table th,
        .custom-box-product-specs-table td { vertical-align: middle; }
        .custom-box-product-specs-table input { width: 100%; }
        .custom-box-product-specs-actions { width: 90px; }
        '
    );

    wp_add_inline_script(
        'jquery-core',
        "
        jQuery(function($) {
            $(document).on('click', '.custom-box-add-spec-row', function(e) {
                e.preventDefault();
                var row = '<tr>' +
                    '<td><input type=\"text\" name=\"custom_box_product_spec_label[]\" placeholder=\"Paper Type\"></td>' +
                    '<td><input type=\"text\" name=\"custom_box_product_spec_value[]\" placeholder=\"Kraft Paper\"></td>' +
                    '<td><button type=\"button\" class=\"button custom-box-remove-spec-row\">Remove</button></td>' +
                    '</tr>';
                $('.custom-box-product-specs-table tbody').append(row);
            });

            $(document).on('click', '.custom-box-remove-spec-row', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
            });
        });
        "
    );
}
add_action('admin_enqueue_scripts', 'custom_box_enqueue_product_specs_admin');
