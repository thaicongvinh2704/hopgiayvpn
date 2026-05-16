<?php
/**
 * Category admin fields used by the homepage category sections.
 */

function custom_box_enqueue_category_image_admin($hook) {
    if (!in_array($hook, array('edit-tags.php', 'term.php'), true)) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || !in_array($screen->taxonomy, array('category', 'product_cat'), true)) {
        return;
    }

    wp_enqueue_media();
    wp_add_inline_script(
        'jquery-core',
        "
        jQuery(function($) {
            function setCategoryImage(wrapper, imageId, imageUrl) {
                wrapper.find('.custom-box-category-image-id').val(imageId);
                wrapper.find('.custom-box-category-image-preview').html('<img src=\"' + imageUrl + '\" alt=\"\" style=\"max-width:120px;height:auto;display:block;margin:8px 0;border:1px solid #ccd0d4;\" />');
                wrapper.find('.custom-box-category-image-remove').show();
            }

            $(document).on('click', '.custom-box-category-image-upload', function(e) {
                e.preventDefault();
                var button = $(this);
                var wrapper = button.closest('.custom-box-category-image-field');
                var frame = wp.media({
                    title: 'Select Category Image',
                    button: { text: 'Use this image' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    setCategoryImage(wrapper, attachment.id, imageUrl);
                });

                frame.open();
            });

            $(document).on('click', '.custom-box-category-image-remove', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.custom-box-category-image-field');
                wrapper.find('.custom-box-category-image-id').val('');
                wrapper.find('.custom-box-category-image-preview').empty();
                $(this).hide();
            });
        });
        "
    );
}
add_action('admin_enqueue_scripts', 'custom_box_enqueue_category_image_admin');

function custom_box_category_image_add_field() {
    ?>
    <div class="form-field term-category-image-wrap custom-box-category-image-field">
        <label for="custom-box-category-image-id"><?php esc_html_e('Ảnh đại diện danh mục', 'custom-box-theme'); ?></label>
        <input type="hidden" id="custom-box-category-image-id" class="custom-box-category-image-id" name="custom_box_category_image_id" value="">
        <div class="custom-box-category-image-preview"></div>
        <button type="button" class="button custom-box-category-image-upload"><?php esc_html_e('Chọn ảnh', 'custom-box-theme'); ?></button>
        <button type="button" class="button custom-box-category-image-remove" style="display:none;"><?php esc_html_e('Xóa ảnh', 'custom-box-theme'); ?></button>
        <p><?php esc_html_e('Ảnh này sẽ hiển thị trong lưới danh mục ngoài trang chủ.', 'custom-box-theme'); ?></p>
    </div>
    <div class="form-field term-category-featured-wrap">
        <label>
            <input type="checkbox" name="custom_box_category_featured" value="1">
            <?php esc_html_e('Ưu tiên hiển thị ở đầu danh mục', 'custom-box-theme'); ?>
        </label>
        <p><?php esc_html_e('Đánh dấu để danh mục này được đưa lên đầu lưới categories ngoài trang chủ.', 'custom-box-theme'); ?></p>
    </div>
    <?php
}
add_action('category_add_form_fields', 'custom_box_category_image_add_field');
add_action('product_cat_add_form_fields', 'custom_box_category_image_add_field');

function custom_box_category_image_edit_field($term) {
    $image_id = (int) get_term_meta($term->term_id, 'custom_box_category_image_id', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
    $is_featured = (bool) get_term_meta($term->term_id, 'custom_box_category_featured', true);
    ?>
    <tr class="form-field term-category-image-wrap custom-box-category-image-field">
        <th scope="row">
            <label for="custom-box-category-image-id"><?php esc_html_e('Ảnh đại diện danh mục', 'custom-box-theme'); ?></label>
        </th>
        <td>
            <input type="hidden" id="custom-box-category-image-id" class="custom-box-category-image-id" name="custom_box_category_image_id" value="<?php echo esc_attr($image_id); ?>">
            <div class="custom-box-category-image-preview">
                <?php if ($image_url) : ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="" style="max-width:120px;height:auto;display:block;margin:8px 0;border:1px solid #ccd0d4;">
                <?php endif; ?>
            </div>
            <button type="button" class="button custom-box-category-image-upload"><?php esc_html_e('Chọn ảnh', 'custom-box-theme'); ?></button>
            <button type="button" class="button custom-box-category-image-remove" <?php echo $image_url ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Xóa ảnh', 'custom-box-theme'); ?></button>
            <p class="description"><?php esc_html_e('Ảnh này sẽ hiển thị trong lưới danh mục ngoài trang chủ.', 'custom-box-theme'); ?></p>
        </td>
    </tr>
    <tr class="form-field term-category-featured-wrap">
        <th scope="row"><?php esc_html_e('Ưu tiên hiển thị', 'custom-box-theme'); ?></th>
        <td>
            <label>
                <input type="checkbox" name="custom_box_category_featured" value="1" <?php checked($is_featured); ?>>
                <?php esc_html_e('Đưa danh mục này lên đầu lưới categories ngoài trang chủ.', 'custom-box-theme'); ?>
            </label>
        </td>
    </tr>
    <?php
}
add_action('category_edit_form_fields', 'custom_box_category_image_edit_field');
add_action('product_cat_edit_form_fields', 'custom_box_category_image_edit_field');

function custom_box_save_category_image($term_id) {
    if (!isset($_POST['custom_box_category_image_id'])) {
        return;
    }

    $image_id = absint($_POST['custom_box_category_image_id']);
    if ($image_id) {
        update_term_meta($term_id, 'custom_box_category_image_id', $image_id);
        return;
    }

    delete_term_meta($term_id, 'custom_box_category_image_id');
}
add_action('created_category', 'custom_box_save_category_image');
add_action('edited_category', 'custom_box_save_category_image');
add_action('created_product_cat', 'custom_box_save_category_image');
add_action('edited_product_cat', 'custom_box_save_category_image');

function custom_box_save_category_featured($term_id) {
    if (!isset($_POST['custom_box_category_image_id']) && !isset($_POST['custom_box_category_featured'])) {
        return;
    }

    if (!empty($_POST['custom_box_category_featured'])) {
        update_term_meta($term_id, 'custom_box_category_featured', 1);
        return;
    }

    delete_term_meta($term_id, 'custom_box_category_featured');
}
add_action('created_category', 'custom_box_save_category_featured');
add_action('edited_category', 'custom_box_save_category_featured');
add_action('created_product_cat', 'custom_box_save_category_featured');
add_action('edited_product_cat', 'custom_box_save_category_featured');

function custom_box_category_image_columns($columns) {
    $new_columns = array();

    foreach ($columns as $key => $label) {
        if ('name' === $key) {
            $new_columns['custom_box_category_image'] = __('Ảnh', 'custom-box-theme');
            $new_columns['custom_box_category_featured'] = __('Ưu tiên', 'custom-box-theme');
        }

        $new_columns[$key] = $label;
    }

    return $new_columns;
}
add_filter('manage_edit-category_columns', 'custom_box_category_image_columns');
add_filter('manage_edit-product_cat_columns', 'custom_box_category_image_columns');

function custom_box_category_image_column_content($content, $column_name, $term_id) {
    if ('custom_box_category_image' !== $column_name) {
        if ('custom_box_category_featured' === $column_name) {
            return get_term_meta($term_id, 'custom_box_category_featured', true) ? '★' : '&mdash;';
        }

        return $content;
    }

    $image_id = (int) get_term_meta($term_id, 'custom_box_category_image_id', true);
    if (!$image_id) {
        return '&mdash;';
    }

    return wp_get_attachment_image($image_id, array(48, 48), false, array(
        'style' => 'width:48px;height:48px;object-fit:cover;border:1px solid #ccd0d4;',
    ));
}
add_filter('manage_category_custom_column', 'custom_box_category_image_column_content', 10, 3);
add_filter('manage_product_cat_custom_column', 'custom_box_category_image_column_content', 10, 3);
