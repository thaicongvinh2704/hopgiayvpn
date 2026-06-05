<?php
/**
 * Safely report or delete known unused WordPress pages.
 *
 * Default mode is dry-run. To delete, run:
 * php tools/cleanup-unused-pages.php --apply --confirm=DELETE_UNUSED_PAGES
 */

require_once __DIR__ . '/../wp-load.php';

$apply = in_array('--apply', $argv, true);
$confirm = null;

foreach ($argv as $arg) {
    if (0 === strpos($arg, '--confirm=')) {
        $confirm = substr($arg, strlen('--confirm='));
    }
}

if ($apply && 'DELETE_UNUSED_PAGES' !== $confirm) {
    fwrite(STDERR, "Refusing to delete. Add --confirm=DELETE_UNUSED_PAGES with --apply.\n");
    exit(1);
}

$targets = array(
    array(
        'label' => 'Duplicate Home page',
        'slugs' => array('home-2'),
        'titles' => array('Home'),
    ),
    array(
        'label' => 'Default sample page',
        'slugs' => array('trang-mau', 'trang-mau__trashed', 'sample-page', 'sample-page__trashed'),
        'titles' => array('Trang mẫu', 'Sample Page'),
    ),
    array(
        'label' => 'Test page',
        'slugs' => array('ewefwfwfwefwefweefwefwefw', 'ewefwfwfwefwefweefwefwefw__trashed'),
        'titles' => array('èwefwfwfwefwefweefwefwefw'),
    ),
    array(
        'label' => 'Old Cart page',
        'slugs' => array('cart__trashed'),
        'titles' => array('Cart'),
    ),
    array(
        'label' => 'Old Checkout page',
        'slugs' => array('checkout__trashed'),
        'titles' => array('Checkout'),
    ),
    array(
        'label' => 'Old My account page',
        'slugs' => array('my-account__trashed'),
        'titles' => array('My account'),
    ),
    array(
        'label' => 'Duplicate packaging landing page',
        'slugs' => array('packaging-landing-two', 'packaging-landing-two__trashed'),
        'titles' => array('Packaging Landing Page 2'),
    ),
);

$protected_option_names = array(
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

foreach ($protected_option_names as $option_name) {
    $option_value = (int) get_option($option_name);

    if ($option_value > 0) {
        $protected_ids[$option_value] = $option_name;
    }
}

$matches = array();
$pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => array('publish', 'draft', 'pending', 'private', 'trash'),
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
));

foreach ($pages as $post) {
    foreach ($targets as $target) {
        if (!in_array($post->post_name, $target['slugs'], true)) {
            continue;
        }

        $matches[$post->ID] = $target['label'];
    }
}

ksort($matches);

echo $apply ? "Mode: APPLY\n" : "Mode: DRY RUN\n";

if (empty($matches)) {
    echo "No matching unused pages found.\n";
    exit(0);
}

foreach ($matches as $post_id => $label) {
    $post = get_post($post_id);

    if (!$post) {
        continue;
    }

    $line = sprintf(
        '%s %d | %s | slug=%s | status=%s | reason=%s',
        $apply ? 'DELETE?' : 'FOUND',
        $post_id,
        $post->post_title,
        $post->post_name,
        $post->post_status,
        $label
    );

    if (isset($protected_ids[$post_id])) {
        echo "SKIP protected {$line} | option={$protected_ids[$post_id]}\n";
        continue;
    }

    if (!$apply) {
        echo $line . "\n";
        continue;
    }

    $deleted = wp_delete_post($post_id, true);

    if ($deleted) {
        echo str_replace('DELETE?', 'DELETED', $line) . "\n";
    } else {
        echo str_replace('DELETE?', 'FAILED', $line) . "\n";
    }
}
