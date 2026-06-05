<?php
/**
 * Admin tool for cleaning known unused pages without using terminal commands.
 */

function custom_box_unused_page_cleanup_can_run() {
    return current_user_can('manage_options');
}

function custom_box_unused_page_cleanup_targets() {
    return array(
        'home-2' => 'Duplicate Home page',
        'trang-mau' => 'Default sample page',
        'trang-mau__trashed' => 'Default sample page',
        'sample-page' => 'Default sample page',
        'sample-page__trashed' => 'Default sample page',
        'ewefwfwfwefwefweefwefwefw' => 'Test page',
        'ewefwfwfwefwefweefwefwefw__trashed' => 'Test page',
        'cart__trashed' => 'Old Cart page',
        'checkout__trashed' => 'Old Checkout page',
        'my-account__trashed' => 'Old My account page',
        'packaging-landing-two' => 'Duplicate packaging landing page',
        'packaging-landing-two__trashed' => 'Duplicate packaging landing page',
    );
}

function custom_box_unused_page_cleanup_protected_ids() {
    $option_names = array(
        'page_on_front',
        'page_for_posts',
        'wp_page_for_privacy_policy',
        'woocommerce_shop_page_id',
        'woocommerce_cart_page_id',
        'woocommerce_checkout_page_id',
        'woocommerce_myaccount_page_id',
        'woocommerce_terms_page_id',
    );

    $protected_ids = array();

    foreach ($option_names as $option_name) {
        $option_value = (int) get_option($option_name);

        if ($option_value > 0) {
            $protected_ids[$option_value] = $option_name;
        }
    }

    return $protected_ids;
}

function custom_box_unused_page_cleanup_matches() {
    $targets = custom_box_unused_page_cleanup_targets();
    $protected_ids = custom_box_unused_page_cleanup_protected_ids();
    $pages = get_posts(array(
        'post_type' => 'page',
        'post_status' => array('publish', 'draft', 'pending', 'private', 'trash'),
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ));
    $matches = array();

    foreach ($pages as $page) {
        if (!isset($targets[$page->post_name])) {
            continue;
        }

        $matches[] = array(
            'id' => (int) $page->ID,
            'title' => get_the_title($page),
            'slug' => $page->post_name,
            'status' => $page->post_status,
            'reason' => $targets[$page->post_name],
            'protected' => isset($protected_ids[$page->ID]),
            'protected_option' => isset($protected_ids[$page->ID]) ? $protected_ids[$page->ID] : '',
        );
    }

    return $matches;
}

function custom_box_unused_page_cleanup_admin_menu() {
    add_management_page(
        'Unused Page Cleanup',
        'Unused Page Cleanup',
        'manage_options',
        'custom-box-unused-page-cleanup',
        'custom_box_unused_page_cleanup_page'
    );
}
add_action('admin_menu', 'custom_box_unused_page_cleanup_admin_menu');

function custom_box_unused_page_cleanup_apply() {
    if (!custom_box_unused_page_cleanup_can_run()) {
        wp_die(esc_html__('You do not have permission to clean unused pages.', 'custom-box-theme'));
    }

    check_admin_referer('custom_box_unused_page_cleanup_apply');

    $deleted = 0;
    $skipped = 0;

    foreach (custom_box_unused_page_cleanup_matches() as $match) {
        if (!empty($match['protected'])) {
            $skipped++;
            continue;
        }

        $post = get_post($match['id']);

        if (!$post || 'page' !== $post->post_type) {
            $skipped++;
            continue;
        }

        if (wp_delete_post($match['id'], true)) {
            $deleted++;
        } else {
            $skipped++;
        }
    }

    wp_safe_redirect(add_query_arg(array(
        'page' => 'custom-box-unused-page-cleanup',
        'deleted' => $deleted,
        'skipped' => $skipped,
    ), admin_url('tools.php')));
    exit;
}
add_action('admin_post_custom_box_unused_page_cleanup_apply', 'custom_box_unused_page_cleanup_apply');

function custom_box_unused_page_cleanup_page() {
    if (!custom_box_unused_page_cleanup_can_run()) {
        wp_die(esc_html__('You do not have permission to view this page.', 'custom-box-theme'));
    }

    $matches = custom_box_unused_page_cleanup_matches();
    $deletable_count = 0;

    foreach ($matches as $match) {
        if (empty($match['protected'])) {
            $deletable_count++;
        }
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Unused Page Cleanup', 'custom-box-theme'); ?></h1>

        <?php if (isset($_GET['deleted'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        esc_html__('Deleted %1$d page(s). Skipped %2$d protected or unavailable page(s).', 'custom-box-theme'),
                        (int) $_GET['deleted'],
                        isset($_GET['skipped']) ? (int) $_GET['skipped'] : 0
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <p><?php esc_html_e('This tool only targets known duplicate, sample, test, or already-trashed legacy pages. It will skip pages assigned as homepage, posts page, privacy policy, WooCommerce shop, cart, checkout, account, or terms page.', 'custom-box-theme'); ?></p>

        <?php if (empty($matches)) : ?>
            <div class="notice notice-info">
                <p><?php esc_html_e('No matching unused pages found.', 'custom-box-theme'); ?></p>
            </div>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Title', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Slug', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Status', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Reason', 'custom-box-theme'); ?></th>
                        <th><?php esc_html_e('Action', 'custom-box-theme'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matches as $match) : ?>
                        <tr>
                            <td><?php echo esc_html((string) $match['id']); ?></td>
                            <td><?php echo esc_html($match['title']); ?></td>
                            <td><code><?php echo esc_html($match['slug']); ?></code></td>
                            <td><?php echo esc_html($match['status']); ?></td>
                            <td><?php echo esc_html($match['reason']); ?></td>
                            <td>
                                <?php if (!empty($match['protected'])) : ?>
                                    <?php echo esc_html(sprintf(__('Skipped: protected by %s', 'custom-box-theme'), $match['protected_option'])); ?>
                                <?php else : ?>
                                    <?php esc_html_e('Will be permanently deleted', 'custom-box-theme'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($deletable_count > 0) : ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 20px;">
                    <?php wp_nonce_field('custom_box_unused_page_cleanup_apply'); ?>
                    <input type="hidden" name="action" value="custom_box_unused_page_cleanup_apply">
                    <?php submit_button(sprintf(__('Delete %d Unused Page(s)', 'custom-box-theme'), $deletable_count), 'delete', 'submit', false); ?>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}
