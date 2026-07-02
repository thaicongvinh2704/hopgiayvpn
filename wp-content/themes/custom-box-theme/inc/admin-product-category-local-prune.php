<?php
/**
 * Admin tool for pruning WooCommerce product categories to the local manifest set.
 */

defined('ABSPATH') || exit;

function custom_box_product_category_local_prune_can_run() {
    return current_user_can('manage_woocommerce') || current_user_can('manage_options');
}

function custom_box_product_category_local_prune_manifest_path() {
    return get_template_directory() . '/inc/product-category-assignment-manifest.json';
}

function custom_box_product_category_local_prune_manifest_categories() {
    $path = custom_box_product_category_local_prune_manifest_path();

    if (!file_exists($path)) {
        return array();
    }

    $data = json_decode((string) file_get_contents($path), true);

    if (!is_array($data) || empty($data['categories']) || !is_array($data['categories'])) {
        return array();
    }

    $categories = array();

    foreach ($data['categories'] as $slug => $category) {
        $slug = sanitize_title($slug);

        if (!$slug) {
            continue;
        }

        $categories[$slug] = array(
            'name' => !empty($category['name']) ? (string) $category['name'] : ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
        );
    }

    ksort($categories);

    return $categories;
}

function custom_box_product_category_local_prune_protected_slugs() {
    $slugs = array_keys(custom_box_product_category_local_prune_manifest_categories());

    if (empty($slugs)) {
        if (function_exists('custom_box_get_official_packaging_category_slugs')) {
            $slugs = array_merge($slugs, custom_box_get_official_packaging_category_slugs());
        }

        if (function_exists('custom_box_category_migration_targets')) {
            $slugs = array_merge($slugs, array_keys(custom_box_category_migration_targets()));
        }
    }

    $slugs[] = 'custom-packaging-boxes';
    $slugs[] = 'uncategorized';

    $default_term_id = (int) get_option('default_product_cat');

    if ($default_term_id) {
        $default_term = get_term($default_term_id, 'product_cat');

        if ($default_term && !is_wp_error($default_term) && !empty($default_term->slug)) {
            $slugs[] = $default_term->slug;
        }
    }

    $slugs = array_map('sanitize_title', $slugs);
    $slugs = array_filter($slugs);
    $slugs = array_values(array_unique($slugs));
    sort($slugs);

    return $slugs;
}

function custom_box_product_category_local_prune_term_depth($term, array $terms_by_id) {
    $depth = 0;
    $parent_id = isset($term->parent) ? (int) $term->parent : 0;

    while ($parent_id && isset($terms_by_id[$parent_id]) && $depth < 30) {
        $depth++;
        $parent_id = (int) $terms_by_id[$parent_id]->parent;
    }

    return $depth;
}

function custom_box_product_category_local_prune_preview() {
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

    $protected_slugs = custom_box_product_category_local_prune_protected_slugs();
    $default_term_id = (int) get_option('default_product_cat');
    $terms_by_id = array();
    $matches = array();

    foreach ($terms as $term) {
        $terms_by_id[(int) $term->term_id] = $term;
    }

    foreach ($terms as $term) {
        $term_id = (int) $term->term_id;

        if ($term_id === $default_term_id || in_array($term->slug, $protected_slugs, true)) {
            continue;
        }

        $parent = !empty($term->parent) && isset($terms_by_id[(int) $term->parent])
            ? $terms_by_id[(int) $term->parent]
            : null;

        $matches[] = array(
            'id'          => $term_id,
            'name'        => $term->name,
            'slug'        => $term->slug,
            'parent'      => (int) $term->parent,
            'parent_slug' => $parent ? $parent->slug : '',
            'count'       => (int) $term->count,
            'depth'       => custom_box_product_category_local_prune_term_depth($term, $terms_by_id),
        );
    }

    usort($matches, function ($a, $b) {
        if ($a['depth'] !== $b['depth']) {
            return $b['depth'] <=> $a['depth'];
        }

        return strcmp($a['slug'], $b['slug']);
    });

    return array(
        'total_terms'      => count($terms),
        'protected_slugs' => $protected_slugs,
        'protected_count' => count($protected_slugs),
        'matches'         => $matches,
        'delete_count'    => count($matches),
        'projected_total' => count($terms) - count($matches),
    );
}

function custom_box_product_category_local_prune_backup(array $matches, array $context = array()) {
    $uploads = wp_upload_dir(null, false);

    if (!empty($uploads['error'])) {
        return new WP_Error('upload_dir_error', $uploads['error']);
    }

    $backup_dir = trailingslashit($uploads['basedir']) . 'custom-box-category-prune-backups';

    if (!wp_mkdir_p($backup_dir)) {
        return new WP_Error('backup_dir_error', 'Could not create product category prune backup directory.');
    }

    $backup = array(
        'created_at_utc' => gmdate('c'),
        'site_url'       => home_url('/'),
        'taxonomy'       => 'product_cat',
        'context'        => $context,
        'categories'     => array(),
    );

    foreach ($matches as $match) {
        $term = get_term((int) $match['id'], 'product_cat');

        if (!$term || is_wp_error($term)) {
            continue;
        }

        $parent = !empty($term->parent) ? get_term((int) $term->parent, 'product_cat') : null;
        $product_ids = get_objects_in_term((int) $term->term_id, 'product_cat');

        if (is_wp_error($product_ids)) {
            $product_ids = array();
        }

        $backup['categories'][] = array(
            'term_id'     => (int) $term->term_id,
            'name'        => $term->name,
            'slug'        => $term->slug,
            'description' => $term->description,
            'parent'      => (int) $term->parent,
            'parent_slug' => ($parent && !is_wp_error($parent)) ? $parent->slug : '',
            'count'       => (int) $term->count,
            'meta'        => get_term_meta((int) $term->term_id),
            'product_ids' => array_map('intval', (array) $product_ids),
        );
    }

    $encoded = wp_json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (!$encoded) {
        return new WP_Error('backup_encode_error', 'Could not encode product category prune backup.');
    }

    $backup_file = trailingslashit($backup_dir) . 'local-product-category-prune-' . gmdate('Ymd_His') . '.json';

    if (false === file_put_contents($backup_file, $encoded . PHP_EOL)) {
        return new WP_Error('backup_write_error', 'Could not write product category prune backup file.');
    }

    return $backup_file;
}

function custom_box_product_category_local_prune_delete(array $matches) {
    $deleted = array();
    $skipped = array();

    foreach ($matches as $match) {
        $result = wp_delete_term((int) $match['id'], 'product_cat');

        if (is_wp_error($result) || false === $result) {
            $skipped[] = $match['slug'];
            continue;
        }

        $deleted[] = $match['slug'];
    }

    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients();
    }

    flush_rewrite_rules(false);

    return array(
        'deleted' => $deleted,
        'skipped' => $skipped,
    );
}

function custom_box_product_category_local_prune_apply($sync_first = true) {
    $sync_result = null;

    if ($sync_first && function_exists('custom_box_local_product_category_sync_apply')) {
        $sync_result = custom_box_local_product_category_sync_apply();

        if (is_array($sync_result) && !empty($sync_result['error'])) {
            return new WP_Error('local_manifest_sync_failed', $sync_result['error']);
        }
    }

    $preview = custom_box_product_category_local_prune_preview();

    if (is_wp_error($preview)) {
        return $preview;
    }

    $backup_file = custom_box_product_category_local_prune_backup(
        $preview['matches'],
        array(
            'sync_first'      => (bool) $sync_first,
            'sync_result'     => $sync_result,
            'total_terms'     => $preview['total_terms'],
            'protected_slugs' => $preview['protected_slugs'],
            'delete_count'    => $preview['delete_count'],
        )
    );

    if (is_wp_error($backup_file)) {
        return $backup_file;
    }

    $delete_result = custom_box_product_category_local_prune_delete($preview['matches']);
    $after_preview = custom_box_product_category_local_prune_preview();

    return array(
        'sync_result'     => $sync_result,
        'backup_file'     => $backup_file,
        'before_total'    => $preview['total_terms'],
        'protected_count' => $preview['protected_count'],
        'selected_count'  => $preview['delete_count'],
        'deleted'         => $delete_result['deleted'],
        'skipped'         => $delete_result['skipped'],
        'after_total'     => is_wp_error($after_preview) ? 0 : $after_preview['total_terms'],
        'remaining_extra' => is_wp_error($after_preview) ? 0 : $after_preview['delete_count'],
    );
}

function custom_box_product_category_local_prune_admin_menu() {
    add_management_page(
        __('Local Product Category Prune', 'custom-box-theme'),
        __('Local Product Category Prune', 'custom-box-theme'),
        'manage_options',
        'custom-box-product-category-local-prune',
        'custom_box_product_category_local_prune_page'
    );
}
add_action('admin_menu', 'custom_box_product_category_local_prune_admin_menu');

function custom_box_product_category_local_prune_admin_post() {
    if (!custom_box_product_category_local_prune_can_run()) {
        wp_die(esc_html__('You do not have permission to prune product categories.', 'custom-box-theme'));
    }

    check_admin_referer('custom_box_product_category_local_prune_apply');

    $sync_first = !empty($_POST['sync_first']);
    $result = custom_box_product_category_local_prune_apply($sync_first);

    if (is_wp_error($result)) {
        $result = array('error' => $result->get_error_message());
    }

    set_transient('custom_box_product_category_local_prune_result_' . get_current_user_id(), $result, 10 * MINUTE_IN_SECONDS);

    wp_safe_redirect(add_query_arg(array(
        'page'       => 'custom-box-product-category-local-prune',
        'prune_done' => '1',
    ), admin_url('tools.php')));
    exit;
}
add_action(
    'admin_post_custom_box_product_category_local_prune_apply',
    'custom_box_product_category_local_prune_admin_post'
);

function custom_box_product_category_local_prune_page() {
    if (!custom_box_product_category_local_prune_can_run()) {
        wp_die(esc_html__('You do not have permission to view this tool.', 'custom-box-theme'));
    }

    $preview = custom_box_product_category_local_prune_preview();
    $result = isset($_GET['prune_done']) ? get_transient('custom_box_product_category_local_prune_result_' . get_current_user_id()) : false;

    if (isset($_GET['prune_done'])) {
        delete_transient('custom_box_product_category_local_prune_result_' . get_current_user_id());
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Local Product Category Prune', 'custom-box-theme'); ?></h1>
        <p><?php esc_html_e('This tool syncs products to the committed local product/category manifest, then deletes WooCommerce product categories that are not part of the local category set. Products are not deleted.', 'custom-box-theme'); ?></p>

        <?php if (is_array($result)) : ?>
            <div class="notice <?php echo !empty($result['error']) ? 'notice-error' : 'notice-success'; ?> is-dismissible">
                <p>
                    <?php if (!empty($result['error'])) : ?>
                        <strong><?php esc_html_e('Prune failed:', 'custom-box-theme'); ?></strong>
                        <?php echo esc_html($result['error']); ?>
                    <?php else : ?>
                        <strong><?php esc_html_e('Prune completed.', 'custom-box-theme'); ?></strong>
                        <?php
                        printf(
                            esc_html__('Selected %1$d category(s), deleted %2$d, skipped %3$d. Category count: %4$d -> %5$d. Remaining extra: %6$d.', 'custom-box-theme'),
                            (int) $result['selected_count'],
                            count($result['deleted']),
                            count($result['skipped']),
                            (int) $result['before_total'],
                            (int) $result['after_total'],
                            (int) $result['remaining_extra']
                        );
                        ?>
                        <br>
                        <?php esc_html_e('Backup file:', 'custom-box-theme'); ?>
                        <code><?php echo esc_html($result['backup_file']); ?></code>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if (is_wp_error($preview)) : ?>
            <div class="notice notice-error"><p><?php echo esc_html($preview->get_error_message()); ?></p></div>
        <?php else : ?>
            <div class="notice notice-info inline">
                <p>
                    <?php
                    printf(
                        esc_html__('Current product categories: %1$d. Local protected set: %2$d. Categories selected for deletion: %3$d. Projected category count after prune: %4$d.', 'custom-box-theme'),
                        (int) $preview['total_terms'],
                        (int) $preview['protected_count'],
                        (int) $preview['delete_count'],
                        (int) $preview['projected_total']
                    );
                    ?>
                </p>
            </div>

            <details style="margin: 16px 0;">
                <summary><?php esc_html_e('Protected local category slugs', 'custom-box-theme'); ?></summary>
                <p><code><?php echo esc_html(implode(', ', $preview['protected_slugs'])); ?></code></p>
            </details>

            <?php if (empty($preview['matches'])) : ?>
                <div class="notice notice-success inline">
                    <p><?php esc_html_e('This site already matches the local product category set. No extra product categories are selected for deletion.', 'custom-box-theme'); ?></p>
                </div>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'custom-box-theme'); ?></th>
                            <th><?php esc_html_e('Name', 'custom-box-theme'); ?></th>
                            <th><?php esc_html_e('Slug', 'custom-box-theme'); ?></th>
                            <th><?php esc_html_e('Products', 'custom-box-theme'); ?></th>
                            <th><?php esc_html_e('Parent', 'custom-box-theme'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview['matches'] as $match) : ?>
                            <tr>
                                <td><?php echo esc_html((string) $match['id']); ?></td>
                                <td><?php echo esc_html($match['name']); ?></td>
                                <td><code><?php echo esc_html($match['slug']); ?></code></td>
                                <td><?php echo esc_html((string) $match['count']); ?></td>
                                <td><code><?php echo esc_html($match['parent_slug']); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 20px;" onsubmit="return window.confirm('<?php echo esc_js(__('Sync product assignments and permanently delete the listed non-local product categories? A JSON backup will be created first.', 'custom-box-theme')); ?>');">
                    <?php wp_nonce_field('custom_box_product_category_local_prune_apply'); ?>
                    <input type="hidden" name="action" value="custom_box_product_category_local_prune_apply">
                    <label>
                        <input type="checkbox" name="sync_first" value="1" checked>
                        <?php esc_html_e('Apply local product/category manifest before pruning', 'custom-box-theme'); ?>
                    </label>
                    <?php
                    submit_button(
                        sprintf(__('Sync and Delete %d Non-Local Product Category(s)', 'custom-box-theme'), (int) $preview['delete_count']),
                        'delete',
                        'submit',
                        true
                    );
                    ?>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}
