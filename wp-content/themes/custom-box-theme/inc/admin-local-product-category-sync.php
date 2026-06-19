<?php
/**
 * Sync WooCommerce product categories from the committed local manifest.
 */

defined('ABSPATH') || exit;

function custom_box_local_product_category_sync_can_run() {
    return current_user_can('manage_woocommerce') || current_user_can('manage_options');
}

function custom_box_local_product_category_sync_manifest_path() {
    return get_template_directory() . '/inc/product-category-assignment-manifest.json';
}

function custom_box_local_product_category_sync_load_manifest() {
    $path = custom_box_local_product_category_sync_manifest_path();

    if (!file_exists($path)) {
        return new WP_Error('missing_manifest', 'Product category assignment manifest is missing.');
    }

    $data = json_decode((string) file_get_contents($path), true);

    if (!is_array($data) || empty($data['products']) || empty($data['categories'])) {
        return new WP_Error('invalid_manifest', 'Product category assignment manifest is invalid.');
    }

    return $data;
}

function custom_box_local_product_category_sync_product_by_slug($slug) {
    $product = get_page_by_path($slug, OBJECT, 'product');

    return $product && 'trash' !== get_post_status($product) ? $product : null;
}

function custom_box_local_product_category_sync_current_slugs($product_id) {
    $slugs = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));

    if (is_wp_error($slugs)) {
        return array();
    }

    sort($slugs);
    return $slugs;
}

function custom_box_local_product_category_sync_ensure_categories(array $manifest) {
    $term_ids = array();

    foreach ($manifest['categories'] as $slug => $category) {
        $term = get_term_by('slug', $slug, 'product_cat');

        if (!$term || is_wp_error($term)) {
            $created = wp_insert_term(
                !empty($category['name']) ? $category['name'] : ucwords(str_replace('-', ' ', $slug)),
                'product_cat',
                array('slug' => $slug)
            );

            if (is_wp_error($created)) {
                return $created;
            }

            $term = get_term((int) $created['term_id'], 'product_cat');
        }

        if ($term && !is_wp_error($term)) {
            $term_ids[$slug] = (int) $term->term_id;
        }
    }

    return $term_ids;
}

function custom_box_local_product_category_sync_preview() {
    $manifest = custom_box_local_product_category_sync_load_manifest();

    if (is_wp_error($manifest)) {
        return $manifest;
    }

    $changed = 0;
    $unchanged = 0;
    $missing_products = array();
    $projected_counts = array();

    foreach ($manifest['products'] as $product_slug => $assignment) {
        $product = custom_box_local_product_category_sync_product_by_slug($product_slug);
        $target = isset($assignment['categories']) && is_array($assignment['categories']) ? $assignment['categories'] : array();
        sort($target);

        foreach ($target as $category_slug) {
            $projected_counts[$category_slug] = ($projected_counts[$category_slug] ?? 0) + 1;
        }

        if (!$product) {
            $missing_products[] = $product_slug;
            continue;
        }

        if (custom_box_local_product_category_sync_current_slugs((int) $product->ID) === $target) {
            $unchanged++;
        } else {
            $changed++;
        }
    }

    ksort($projected_counts);

    return array(
        'manifest' => $manifest,
        'changed' => $changed,
        'unchanged' => $unchanged,
        'missing_products' => $missing_products,
        'projected_counts' => $projected_counts,
    );
}

function custom_box_local_product_category_sync_apply() {
    global $wpdb;

    if (!taxonomy_exists('product_cat')) {
        return array('error' => 'WooCommerce product_cat taxonomy is not available.');
    }

    $manifest = custom_box_local_product_category_sync_load_manifest();

    if (is_wp_error($manifest)) {
        return array('error' => $manifest->get_error_message());
    }

    $term_ids = custom_box_local_product_category_sync_ensure_categories($manifest);

    if (is_wp_error($term_ids)) {
        return array('error' => $term_ids->get_error_message());
    }

    $backup_suffix = gmdate('Ymd_His');
    $backup_table = $wpdb->prefix . 'local_product_cat_sync_backup_' . $backup_suffix;
    $created = $wpdb->query(
        "CREATE TABLE {$backup_table} AS
         SELECT tr.object_id, tr.term_taxonomy_id, tr.term_order
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tt.taxonomy = 'product_cat' AND p.post_type = 'product'"
    );

    if (false === $created) {
        return array('error' => 'Could not create the category backup table.');
    }

    $updated = 0;
    $unchanged = 0;
    $missing_products = array();
    $manifest_product_slugs = array_keys($manifest['products']);

    foreach ($manifest['products'] as $product_slug => $assignment) {
        $product = custom_box_local_product_category_sync_product_by_slug($product_slug);

        if (!$product) {
            $missing_products[] = $product_slug;
            continue;
        }

        $target_slugs = isset($assignment['categories']) && is_array($assignment['categories']) ? $assignment['categories'] : array();
        sort($target_slugs);

        if (custom_box_local_product_category_sync_current_slugs((int) $product->ID) === $target_slugs) {
            $unchanged++;
            continue;
        }

        $ids = array();
        foreach ($target_slugs as $slug) {
            if (isset($term_ids[$slug])) {
                $ids[] = $term_ids[$slug];
            }
        }

        $result = wp_set_object_terms((int) $product->ID, $ids, 'product_cat', false);

        if (is_wp_error($result)) {
            return array('error' => $result->get_error_message());
        }

        $updated++;
    }

    $managed_ids = array_values($term_ids);
    $detached_extra_products = 0;
    $all_products = get_posts(array(
        'post_type'      => 'product',
        'post_status'    => array('publish', 'draft', 'pending', 'private'),
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ));

    foreach ($all_products as $product_id) {
        $slug = get_post_field('post_name', $product_id);

        if (in_array($slug, $manifest_product_slugs, true)) {
            continue;
        }

        $current_ids = wp_get_object_terms((int) $product_id, 'product_cat', array('fields' => 'ids'));

        if (is_wp_error($current_ids) || !$current_ids) {
            continue;
        }

        $current_ids = array_map('intval', $current_ids);
        $remaining = array_values(array_diff($current_ids, $managed_ids));

        if (count($remaining) === count($current_ids)) {
            continue;
        }

        $result = wp_set_object_terms((int) $product_id, $remaining, 'product_cat', false);

        if (is_wp_error($result)) {
            return array('error' => $result->get_error_message());
        }

        $detached_extra_products++;
    }

    if ($term_ids) {
        $term_taxonomy_ids = array();
        foreach ($term_ids as $term_id) {
            $term = get_term((int) $term_id, 'product_cat');
            if ($term && !is_wp_error($term)) {
                $term_taxonomy_ids[] = (int) $term->term_taxonomy_id;
            }
        }
        wp_update_term_count_now(array_values(array_unique($term_taxonomy_ids)), 'product_cat');
    }

    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients();
    }

    flush_rewrite_rules(false);

    return array(
        'error' => '',
        'updated' => $updated,
        'unchanged' => $unchanged,
        'missing_products' => count($missing_products),
        'missing_product_slugs' => $missing_products,
        'detached_extra_products' => $detached_extra_products,
        'backup_table' => $backup_table,
    );
}

function custom_box_local_product_category_sync_admin_menu() {
    add_management_page(
        'Local Product Category Sync',
        'Local Product Category Sync',
        'manage_options',
        'custom-box-local-product-category-sync',
        'custom_box_local_product_category_sync_page'
    );
}
add_action('admin_menu', 'custom_box_local_product_category_sync_admin_menu');

function custom_box_local_product_category_sync_admin_post() {
    if (!custom_box_local_product_category_sync_can_run()) {
        wp_die(esc_html__('You do not have permission to sync product categories.', 'custom-box-theme'));
    }

    check_admin_referer('custom_box_local_product_category_sync_apply');
    $result = custom_box_local_product_category_sync_apply();
    set_transient('custom_box_local_product_category_sync_result_' . get_current_user_id(), $result, 10 * MINUTE_IN_SECONDS);

    wp_safe_redirect(add_query_arg(array(
        'page' => 'custom-box-local-product-category-sync',
        'sync_done' => '1',
    ), admin_url('tools.php')));
    exit;
}
add_action('admin_post_custom_box_local_product_category_sync_apply', 'custom_box_local_product_category_sync_admin_post');

function custom_box_local_product_category_sync_page() {
    if (!custom_box_local_product_category_sync_can_run()) {
        wp_die(esc_html__('You do not have permission to view this tool.', 'custom-box-theme'));
    }

    $preview = custom_box_local_product_category_sync_preview();
    $result = isset($_GET['sync_done']) ? get_transient('custom_box_local_product_category_sync_result_' . get_current_user_id()) : false;

    if (isset($_GET['sync_done'])) {
        delete_transient('custom_box_local_product_category_sync_result_' . get_current_user_id());
    }
    ?>
    <div class="wrap">
        <h1>Local Product Category Sync</h1>
        <p>Applies the committed local product/category manifest to WooCommerce. It updates only <code>product_cat</code> relationships, creates a backup table first, and does not delete products.</p>

        <?php if (is_wp_error($preview)) : ?>
            <div class="notice notice-error"><p><?php echo esc_html($preview->get_error_message()); ?></p></div>
        <?php else : ?>
            <?php $manifest = $preview['manifest']; ?>

            <?php if (is_array($result)) : ?>
                <div class="notice <?php echo $result['error'] ? 'notice-error' : 'notice-success'; ?> is-dismissible">
                    <p>
                        <?php if ($result['error']) : ?>
                            <strong>Sync failed:</strong> <?php echo esc_html($result['error']); ?>
                        <?php else : ?>
                            <strong>Sync completed.</strong>
                            Updated <?php echo esc_html((string) $result['updated']); ?> products;
                            <?php echo esc_html((string) $result['unchanged']); ?> already matched;
                            detached <?php echo esc_html((string) $result['detached_extra_products']); ?> extra products from managed categories.
                            Backup: <code><?php echo esc_html($result['backup_table']); ?></code>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="notice notice-info inline">
                <p>
                    Manifest products: <strong><?php echo esc_html((string) count($manifest['products'])); ?></strong>.
                    Categories: <strong><?php echo esc_html((string) count($manifest['categories'])); ?></strong>.
                    Products needing changes: <strong><?php echo esc_html((string) $preview['changed']); ?></strong>.
                    Missing products on this site: <strong><?php echo esc_html((string) count($preview['missing_products'])); ?></strong>.
                    Generated: <code><?php echo esc_html($manifest['generated_at']); ?></code>.
                </p>
            </div>

            <?php if ($preview['missing_products']) : ?>
                <div class="notice notice-warning inline">
                    <p>Some manifest products do not exist on this site: <code><?php echo esc_html(implode(', ', array_slice($preview['missing_products'], 0, 20))); ?></code><?php echo count($preview['missing_products']) > 20 ? '...' : ''; ?></p>
                </div>
            <?php endif; ?>

            <h2>Projected Category Counts</h2>
            <table class="widefat striped" style="max-width: 760px;">
                <thead><tr><th>Category slug</th><th>Products</th></tr></thead>
                <tbody>
                    <?php foreach ($preview['projected_counts'] as $slug => $count) : ?>
                        <tr><td><code><?php echo esc_html($slug); ?></code></td><td><?php echo esc_html((string) $count); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 20px;">
                <?php wp_nonce_field('custom_box_local_product_category_sync_apply'); ?>
                <input type="hidden" name="action" value="custom_box_local_product_category_sync_apply">
                <?php submit_button('Apply Local Category Manifest', 'primary', 'submit', false); ?>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
