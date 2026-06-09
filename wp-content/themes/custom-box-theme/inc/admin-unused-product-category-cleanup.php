<?php
/**
 * Admin tool for deleting unused WooCommerce product categories.
 */

function custom_box_unused_product_category_cleanup_can_run() {
    return current_user_can('manage_woocommerce') || current_user_can('manage_options');
}

function custom_box_unused_product_category_cleanup_protected_slugs() {
    $protected_slugs = array('custom-packaging-boxes');

    if (function_exists('custom_box_category_migration_targets')) {
        $protected_slugs = array_merge(
            $protected_slugs,
            array_keys(custom_box_category_migration_targets())
        );
    }

    return array_values(array_unique($protected_slugs));
}

function custom_box_unused_product_category_cleanup_matches() {
    if (!taxonomy_exists('product_cat')) {
        return new WP_Error(
            'missing_product_category_taxonomy',
            __('WooCommerce product categories are not available.', 'custom-box-theme')
        );
    }

    $terms = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ));

    if (is_wp_error($terms)) {
        return $terms;
    }

    $protected_slugs = custom_box_unused_product_category_cleanup_protected_slugs();
    $default_term_id = (int) get_option('default_product_cat');
    $matches = array();

    foreach ($terms as $term) {
        $term_id = (int) $term->term_id;

        if (in_array($term->slug, $protected_slugs, true) || $term_id === $default_term_id) {
            continue;
        }

        $object_ids = get_objects_in_term($term_id, 'product_cat');

        if (is_wp_error($object_ids) || !empty($object_ids)) {
            continue;
        }

        $children = get_term_children($term_id, 'product_cat');

        if (is_wp_error($children) || !empty($children)) {
            continue;
        }

        $matches[] = array(
            'id'     => $term_id,
            'name'   => $term->name,
            'slug'   => $term->slug,
            'parent' => (int) $term->parent,
            'count'  => (int) $term->count,
        );
    }

    return $matches;
}

function custom_box_unused_product_category_cleanup_delete() {
    $matches = custom_box_unused_product_category_cleanup_matches();

    if (is_wp_error($matches)) {
        return $matches;
    }

    $deleted = array();
    $skipped = array();

    foreach ($matches as $match) {
        $object_ids = get_objects_in_term($match['id'], 'product_cat');
        $children = get_term_children($match['id'], 'product_cat');

        if (
            is_wp_error($object_ids)
            || !empty($object_ids)
            || is_wp_error($children)
            || !empty($children)
        ) {
            $skipped[] = $match['slug'];
            continue;
        }

        $result = wp_delete_term($match['id'], 'product_cat');

        if (is_wp_error($result) || false === $result) {
            $skipped[] = $match['slug'];
            continue;
        }

        $deleted[] = $match['slug'];
    }

    return array(
        'deleted' => $deleted,
        'skipped' => $skipped,
    );
}

function custom_box_unused_product_category_cleanup_admin_menu() {
    add_management_page(
        __('Product Category Cleanup', 'custom-box-theme'),
        __('Product Category Cleanup', 'custom-box-theme'),
        'manage_options',
        'custom-box-unused-product-category-cleanup',
        'custom_box_unused_product_category_cleanup_page'
    );
}
add_action('admin_menu', 'custom_box_unused_product_category_cleanup_admin_menu');

function custom_box_unused_product_category_cleanup_apply() {
    if (!custom_box_unused_product_category_cleanup_can_run()) {
        wp_die(esc_html__('You do not have permission to clean product categories.', 'custom-box-theme'));
    }

    check_admin_referer('custom_box_unused_product_category_cleanup_apply');

    $result = custom_box_unused_product_category_cleanup_delete();
    $args = array('page' => 'custom-box-unused-product-category-cleanup');

    if (is_wp_error($result)) {
        $args['cleanup_error'] = rawurlencode($result->get_error_message());
    } else {
        $args['deleted'] = count($result['deleted']);
        $args['skipped'] = count($result['skipped']);
    }

    wp_safe_redirect(add_query_arg($args, admin_url('tools.php')));
    exit;
}
add_action(
    'admin_post_custom_box_unused_product_category_cleanup_apply',
    'custom_box_unused_product_category_cleanup_apply'
);

function custom_box_unused_product_category_cleanup_page() {
    if (!custom_box_unused_product_category_cleanup_can_run()) {
        wp_die(esc_html__('You do not have permission to view this page.', 'custom-box-theme'));
    }

    $matches = custom_box_unused_product_category_cleanup_matches();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Product Category Cleanup', 'custom-box-theme'); ?></h1>

        <?php if (isset($_GET['deleted'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        esc_html__('Deleted %1$d unused product category(s). Skipped %2$d category(s) that could not be safely deleted.', 'custom-box-theme'),
                        absint($_GET['deleted']),
                        isset($_GET['skipped']) ? absint($_GET['skipped']) : 0
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['cleanup_error'])) : ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html(rawurldecode(wp_unslash($_GET['cleanup_error']))); ?></p>
            </div>
        <?php endif; ?>

        <p><?php esc_html_e('This tool permanently deletes only categories outside the active packaging set that are not assigned to any product and have no child categories. Active categories and the default WooCommerce product category are protected.', 'custom-box-theme'); ?></p>

        <?php if (is_wp_error($matches)) : ?>
            <div class="notice notice-error"><p><?php echo esc_html($matches->get_error_message()); ?></p></div>
        <?php elseif (empty($matches)) : ?>
            <div class="notice notice-info">
                <p><?php esc_html_e('No unused product categories are available for deletion.', 'custom-box-theme'); ?></p>
            </div>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Name', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Slug', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Products', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Action', 'custom-box-theme'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matches as $match) : ?>
                        <tr>
                            <td><?php echo esc_html((string) $match['id']); ?></td>
                            <td><?php echo esc_html($match['name']); ?></td>
                            <td><code><?php echo esc_html($match['slug']); ?></code></td>
                            <td>0</td>
                            <td><?php esc_html_e('Will be permanently deleted', 'custom-box-theme'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 20px;" onsubmit="return window.confirm('<?php echo esc_js(__('Permanently delete the listed unused product categories?', 'custom-box-theme')); ?>');">
                <?php wp_nonce_field('custom_box_unused_product_category_cleanup_apply'); ?>
                <input type="hidden" name="action" value="custom_box_unused_product_category_cleanup_apply">
                <?php
                submit_button(
                    sprintf(__('Delete %d Unused Product Category(s)', 'custom-box-theme'), count($matches)),
                    'delete',
                    'submit',
                    false
                );
                ?>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
